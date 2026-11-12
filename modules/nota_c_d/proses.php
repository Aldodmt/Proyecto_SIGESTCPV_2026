<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('nota_c_d', null, 'json');   // control de acceso por rol (config/permisos.php)
require_once "../../config/database.php";
require_once __DIR__ . '/../../config/compras.php';
require_once __DIR__ . '/../../config/iva.php';
header('Content-Type: application/json');

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'No hay usuario logueado']);
    exit;
}

$act = $_GET['act'] ?? '';
$response = ['success' => false, 'message' => 'Acción no válida'];

switch ($act) {
    case 'insert':
        $codigo = $_POST['codigo'];
        $origen = $_POST['origen_ajuste'] ?? '';
        $cod_compra = null;

        // Determinar origen (modal = ajuste_p, select = ajuste_c)
        if ($origen === 'modal') {
            $cod_compra = $_POST['cod_compra'] ?? null;
        } elseif ($origen === 'select') {
            $cod_compra = $_POST['compra_ajuste'] ?? null;
        }
        // Traer la factura asociada a la compra, si existe
        if ($cod_compra) {
            $stmt_fac = $mysqli->prepare("SELECT fac_numero FROM compra WHERE cod_compra = ?");
            $stmt_fac->bind_param("i", $cod_compra);
            $stmt_fac->execute();
            $res_fac = $stmt_fac->get_result()->fetch_assoc();
            $stmt_fac->close();

            if ($res_fac) {
                $fac_numero = $res_fac['fac_numero']; // Sobrescribe lo enviado si es necesario
            }
        }
        // Datos generales
        $nro_nota = $_POST['nro_nota'] ?? '';
        $fecha = $_POST["fecha_E"] ?? '';
        $nro_timbrado = $_POST['nro_timbrado'] ?? '';
        $cod_proveedor = $_POST['cod_proveedor'] ?? '';
        $tipo_nota = $_POST['tipo_nota'] ?? ''; // CREDITO o DEBITO
        $causa_nota = $_POST['causa_nota'] ?? ''; // ajuste_p o ajuste_c
        $razon = $_POST['razon'] ?? '';
        $estado = 'ACTIVO';
        $monto_ajuste = floatval($_POST['id_monto_ajuste'] ?? 0);
        $productos_json = $_POST['productos_json'] ?? '[]';
        $productos = json_decode($productos_json, true);

        // Validaciones 
        if (empty($nro_nota)) {
            echo json_encode(['success' => false, 'message' => 'Falta el número de nota']);
            exit;
        }

        if (empty($nro_timbrado)) {
            echo json_encode(['success' => false, 'message' => 'Falta el número de timbrado']);
            exit;
        }

        if (empty($tipo_nota)) {
            echo json_encode(['success' => false, 'message' => 'Falta el tipo de nota (CREDITO o DEBITO)']);
            exit;
        }

        if (empty($causa_nota)) {
            echo json_encode(['success' => false, 'message' => 'Falta la causa de la nota (ajuste_p o ajuste_c)']);
            exit;
        }

        if (empty($fac_numero)) {
            echo json_encode(['success' => false, 'message' => 'Falta el número de factura relacionada']);
            exit;
        }

        // Validación de productos solo si es ajuste_p
        if ($causa_nota === 'ajuste_p' && empty($productos)) {
            echo json_encode(['success' => false, 'message' => 'Faltan los productos para el ajuste']);
            exit;
        }

        // Capa de estado: la nota solo puede emitirse sobre una compra ACTIVO (confirmada y no anulada)
        $estado_compra = flujo_estado('compra', (int) $cod_compra);
        if ($estado_compra !== 'ACTIVO') {
            echo json_encode(['success' => false, 'message' => 'Solo se puede emitir una nota sobre una compra ACTIVO (estado actual: ' . ($estado_compra ?? 'inexistente') . ').']);
            exit;
        }

        $mysqli->begin_transaction();

        // Verificar que no haya cuotas pagadas
        $stmt_check = $mysqli->prepare("
            SELECT COUNT(*) AS pagadas 
            FROM cuentas_a_pagar 
            WHERE cod_compra = ? AND cap_estado = 'PAGADO'
        ");
        $stmt_check->bind_param("i", $cod_compra);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if ($res_check['pagadas'] > 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede cargar la nota porque tiene cuentas ya pagadas.']);
            exit;
        }

        try {
            // Calcular total del ajuste
            $total_ajuste = 0;
            if ($causa_nota === 'ajuste_p') {
                foreach ($productos as $p) {
                    $cantidad_ajustar = floatval($p['cantidad_ajustar']);
                    $precio = floatval($p['precio_unitario']);
                    $total_ajuste += $cantidad_ajustar * $precio;
                }
            }

            $monto_total = ($causa_nota === 'ajuste_p') ? $total_ajuste : $monto_ajuste;

            // Insertar cabecera de nota
            $stmt = $mysqli->prepare("
                INSERT INTO nota_credito_debito 
                (id_nota, cod_compra, fac_numero, tipo, causa, nro_nota, timbrado, fecha_emision, estado, id_user, monto_total, observacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'iisssiissids',
                $codigo,
                $cod_compra,
                $fac_numero,
                $tipo_nota,
                $causa_nota,
                $nro_nota,
                $nro_timbrado,
                $fecha,
                $estado,
                $id_user,
                $monto_total,
                $razon
            );
            $stmt->execute();
            $id_nota = $stmt->insert_id;
            $stmt->close();

            // IVA de la nota: se acumula por línea (ajuste de productos) o se prorratea (ajuste de cuentas, sin productos)
            $iva_lineas = [];

            // Insertar detalle SOLO si es ajuste_p
            if ($causa_nota === 'ajuste_p' && !empty($productos)) {
                $stmt_det = $mysqli->prepare("
                    INSERT INTO det_nota_credit_debit 
                    (id_nota, cod_producto, cantidad, precio_unitario, tipo_iva, exentas, iva5, iva10)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                // Preparar stock
                $stmt_stock_sel = $mysqli->prepare("SELECT cantidad FROM stock_prod WHERE cod_producto = ?");
                $stmt_stock_upd = $mysqli->prepare("UPDATE stock_prod SET cantidad = ? WHERE cod_producto = ?");

                foreach ($productos as $p) {
                    $cod_prod = intval($p['cod_producto']);
                    $cantidad_ajustar = floatval($p['cantidad_ajustar']);
                    $precio = floatval($p['precio_unitario']);
                    $tipo_iva = isset($p['tipo_iva']) ? trim(strval($p['tipo_iva'])) : '0';
                    $tipo_iva = rtrim($tipo_iva, '%');
                    $subtotal = $cantidad_ajustar * $precio;

                    // Calcular IVA
                    $iva10 = $iva5 = $exentas = 0.0;
                    if ($tipo_iva === '10') {
                        $iva10 = $subtotal - ($subtotal / 1.10);
                    } elseif ($tipo_iva === '5') {
                        $iva5 = $subtotal - ($subtotal / 1.05);
                    } else {
                        $exentas = $subtotal;
                    }

                    $iva_lineas[] = ['iva_5' => $iva5, 'iva_10' => $iva10, 'iva_exentas' => $exentas];

                    // Insertar detalle
                    $stmt_det->bind_param('iiididdd', $id_nota, $cod_prod, $cantidad_ajustar, $precio, $tipo_iva, $exentas, $iva5, $iva10);
                    $stmt_det->execute();

                    // Actualizar stock según tipo de nota
                    $stmt_stock_sel->bind_param('i', $cod_prod);
                    $stmt_stock_sel->execute();
                    $res = $stmt_stock_sel->get_result();
                    $stock_actual = $res->fetch_assoc()['cantidad'] ?? 0;

                    if ($tipo_nota === 'CREDITO') {
                        $nuevo_stock = $stock_actual - $cantidad_ajustar;
                    } else {
                        $nuevo_stock = $stock_actual + $cantidad_ajustar;
                    }

                    $stmt_stock_upd->bind_param('ii', $nuevo_stock, $cod_prod);
                    $stmt_stock_upd->execute();
                }

                $stmt_det->close();
                $stmt_stock_sel->close();
                $stmt_stock_upd->close();
            }

            // Registrar el IVA de la nota (crédito resta, débito suma) con la fecha de emisión de la nota
            $iva_nota = $causa_nota === 'ajuste_p' ? iva_sumar($iva_lineas) : iva_prorratear((int) $cod_compra, (float) $monto_total);
            iva_registrar_nota((int) $id_nota, (int) $cod_compra, (string) $tipo_nota, $fecha !== '' ? (string) $fecha : date('Y-m-d'), $iva_nota);

            $mysqli->commit();
            echo json_encode(['success' => true, 'message' => 'Nota registrada correctamente']);

        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => 'Error al registrar nota: ' . $e->getMessage()]);
        }
        break;


    case 'cancel':
        $codigo = (int) ($_POST['codigo'] ?? 0);
        if ($codigo <= 0) {
            echo json_encode(['success' => false, 'message' => 'No se especificó la nota a anular']);
            exit;
        }
        // El estado cambia primero, de forma atómica (ACTIVO -> ANULADO): solo un clic gana. El trigger
        // trg_nota_credito_debito_anulacion recalcula las cuotas en ese mismo UPDATE. El stock se revierte solo
        // si se ganó el cambio, así una doble anulación ya no lo mueve dos veces (antes se movía antes de comprobar).
        try {
            $r = flujo_transicion('nota_c_d', $codigo, 'cancel', function () use ($mysqli, $codigo) {
                $s = $mysqli->prepare("SELECT tipo FROM nota_credito_debito WHERE id_nota = ?");
                $s->bind_param('i', $codigo);
                $s->execute();
                $tipo = (string) ($s->get_result()->fetch_row()[0] ?? '');
                $s->close();

                $s = $mysqli->prepare("SELECT cod_producto, cantidad FROM det_nota_credit_debit WHERE id_nota = ?");
                $s->bind_param('i', $codigo);
                $s->execute();
                $detalle = $s->get_result()->fetch_all(MYSQLI_ASSOC);
                $s->close();

                // CREDITO: al anular vuelve a sumar; DEBITO: al anular resta (misma regla que tenía el sistema)
                $signo = $tipo === 'CREDITO' ? '+' : ($tipo === 'DEBITO' ? '-' : null);
                if ($signo !== null) {
                    $u = $mysqli->prepare("UPDATE stock_prod SET cantidad = cantidad $signo ? WHERE cod_producto = ?");
                    foreach ($detalle as $d) {
                        $cant = (float) $d['cantidad'];
                        $cod = (int) $d['cod_producto'];
                        $u->bind_param('di', $cant, $cod);
                        $u->execute();
                    }
                    $u->close();
                }
                iva_anular(null, $codigo);   // el IVA de la nota deja de contar (queda ANULADO)
            });
        } catch (Throwable $e) {
            $r = ['ok' => false, 'mensaje' => 'Error al anular nota: ' . $e->getMessage(), 'estado' => null];
        }
        echo json_encode(['success' => $r['ok'], 'message' => $r['mensaje'], 'estado' => $r['estado']], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        break;
}
?>