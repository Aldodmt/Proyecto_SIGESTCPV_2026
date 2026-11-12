<?php
session_start();
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('compra', null, 'json');   // 1.ª capa: permiso del rol (config/permisos.php)
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/compras.php';
require_once __DIR__ . '/../../config/iva.php';

$usuario = (int) ($_SESSION['id_user'] ?? 0);
if ($usuario <= 0) {
    flujo_error_json('Usuario no identificado');
}

$accion = $_POST['act'] ?? $_GET['act'] ?? '';

try {
    /* =====================================================================================
       INSERTAR COMPRA  (solo a partir de una orden de compra APROBADA)
       ===================================================================================== */
    if ($accion === 'insert') {
        $id_orden_compra = (int) ($_POST['id_orden_compra'] ?? 0);
        $nro_factura = trim((string) ($_POST['nro_factura'] ?? ''));
        $fac_emi = (string) ($_POST['fac_emi'] ?? '');
        $nro_timbrado = (int) ($_POST['nro_timbrado'] ?? 0);
        $timb_venci = (string) ($_POST['timb_venci'] ?? '');
        $tipo_factura = strtoupper((string) ($_POST['tipo_fac'] ?? 'IMPRESA'));
        $com_condicion = strtoupper((string) ($_POST['condicion_c'] ?? ''));
        $remision = strtoupper((string) ($_POST['remision'] ?? ''));
        $cuotas = (int) ($_POST['cant_cuotas'] ?? 1);
        $intervalo = (int) ($_POST['id_intervalo'] ?? 1);

        // La compra nace de una orden: ya no existe la compra "sin orden" con proveedor y productos sueltos.
        if ($id_orden_compra <= 0) {
            flujo_error_json('Debe seleccionar una orden de compra aprobada para registrar la compra.');
        }
        // Capa de estado: orden APROBADA y sin otra compra vigente
        if (($error = flujo_validar_origen('orden_c', $id_orden_compra)) !== null) {
            flujo_error_json($error);
        }

        // VALIDACIONES de la factura
        if ($nro_factura === '') {
            flujo_error_json('El número de factura es obligatorio.');
        }
        if ($fac_emi === '') {
            flujo_error_json('Debe ingresar la fecha de emisión de la factura.');
        }
        $f = DateTime::createFromFormat('Y-m-d', $fac_emi);
        if (!$f || $f->format('Y-m-d') !== $fac_emi) {
            flujo_error_json('La fecha de emisión no es válida.');
        }
        if ($fac_emi > date('Y-m-d')) {
            flujo_error_json('La fecha de emisión no puede ser mayor a la fecha actual.');
        }
        if (!in_array($tipo_factura, ['IMPRESA', 'ELECTRONICA'], true)) {
            flujo_error_json('Debe seleccionar el tipo de factura.');
        }
        if ($tipo_factura !== 'ELECTRONICA') {
            if ($nro_timbrado <= 0) {
                flujo_error_json('El número de timbrado es obligatorio.');
            }
            if ($timb_venci === '') {
                flujo_error_json('Debe ingresar la fecha de vencimiento del timbrado.');
            }
            if ($timb_venci <= date('Y-m-d')) {
                flujo_error_json('El timbrado ha vencido.');
            }
        }
        if (!in_array($remision, ['CON', 'SIN'], true)) {
            flujo_error_json('Debe seleccionar si la compra tiene remisión o no.');
        }
        if (!in_array($com_condicion, ['CONTADO', 'CREDITO'], true)) {
            flujo_error_json('Debe seleccionar una condición de compra válida (CONTADO o CRÉDITO).');
        }
        if ($cuotas < 1 || $cuotas > 12) {
            flujo_error_json('La cantidad de cuotas debe ser entre 1 y 12.');
        }
        if ($intervalo < 1 || $intervalo > 90) {
            flujo_error_json('El intervalo de pago debe ser entre 1 y 90 días.');
        }
        if ($com_condicion === 'CONTADO') {
            $cuotas = 1;
        }

        // Proveedor y productos/cantidades/precios/IVA: salen de la ORDEN y del producto en la base, no del navegador
        $s = $mysqli->prepare("SELECT cod_proveedor FROM orden_compra WHERE id_orden_comp = ? LIMIT 1");
        $s->bind_param('i', $id_orden_compra);
        $s->execute();
        $orden = $s->get_result()->fetch_assoc();
        $s->close();
        $items = flujo_detalle_orden($id_orden_compra);
        if (!$orden || !$items) {
            flujo_error_json('La orden de compra seleccionada no tiene productos.');
        }
        $cod_proveedor = (int) $orden['cod_proveedor'];

        $com_total = 0.0;
        foreach ($items as &$it) {
            $iva = strtoupper(trim((string) $it['tipo_impuesto']));
            $it['tipo_iva'] = in_array($iva, ['10%', '5%'], true) ? $iva : 'EXENTA';
            $subtotal = (float) $it['cantidad'] * (float) $it['precio_unit'];
            $it['subtotal'] = $subtotal;
            $it['exentas'] = $it['tipo_iva'] === 'EXENTA' ? $subtotal : 0.0;
            $it['iva5'] = $it['tipo_iva'] === '5%' ? $subtotal - ($subtotal / 1.05) : 0.0;
            $it['iva10'] = $it['tipo_iva'] === '10%' ? $subtotal - ($subtotal / 1.10) : 0.0;
            $com_total += $subtotal;
        }
        unset($it);
        if ($com_total <= 0) {
            flujo_error_json('El total de la compra no puede ser 0.');
        }

        $cod_compra = flujo_siguiente_codigo('compra', 'cod_compra');   // código reservado en el servidor
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $estado = 'PENDIENTE';
        $timb_db = $tipo_factura === 'ELECTRONICA' || $timb_venci === '' ? null : $timb_venci;
        try {
            $s = $mysqli->prepare("INSERT INTO compra
                (cod_compra, cod_proveedor, fecha, estado, hora, id_user, id_orden_comp, fac_numero, fac_emision, tipo_factura, con_sin_remision,
                 timbrado_nro, timb_fecha_venci, com_condicion, total_compra)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $s->bind_param('iisssiissssissd', $cod_compra, $cod_proveedor, $fecha, $estado, $hora, $usuario, $id_orden_compra, $nro_factura,
                $fac_emi, $tipo_factura, $remision, $nro_timbrado, $timb_db, $com_condicion, $com_total);
            $s->execute();
            $s->close();

            $s = $mysqli->prepare("INSERT INTO detalle_compra (cod_producto, cod_compra, precio, cantidad, tipo_iva, exentas, iva5, iva10)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($items as $it) {
                $cod = (int) $it['cod_producto'];
                $precio = (float) $it['precio_unit'];
                $cant = (float) $it['cantidad'];
                $s->bind_param('iiddsddd', $cod, $cod_compra, $precio, $cant, $it['tipo_iva'], $it['exentas'], $it['iva5'], $it['iva10']);
                $s->execute();
            }
            $s->close();

            // IVA de la factura: un resumen (5 %, 10 %, exentas) ligado a la compra, con la fecha de emisión de la factura.
            // Los montos salen de las líneas ya calculadas arriba a partir del impuesto de cada producto en la base.
            iva_registrar_compra($cod_compra, $fac_emi, iva_sumar(array_map(
                fn($l) => ['iva_5' => $l['iva5'], 'iva_10' => $l['iva10'], 'iva_exentas' => $l['exentas']], $items)));

            // CUENTAS A PAGAR
            $s = $mysqli->prepare("INSERT INTO cuentas_a_pagar (cod_compra, nro_cuota, cap_monto, cap_saldo, cap_fecha_venci, cap_estado)
                                   VALUES (?, ?, ?, ?, ?, ?)");
            if ($com_condicion === 'CONTADO') {
                // Al contado: una sola cuota, vence hoy y queda pagada
                $nro = 1;
                $saldo = 0.0;
                $cap_estado = 'PAGADO';
                $s->bind_param('iiddss', $cod_compra, $nro, $com_total, $saldo, $fecha, $cap_estado);
                $s->execute();
            } else {
                $monto_cuota = round($com_total / $cuotas, 2);
                $cap_estado = 'PENDIENTE';
                for ($i = 1; $i <= $cuotas; $i++) {
                    $venci = (new DateTime($fecha))->modify('+' . ($intervalo * ($i - 1)) . ' days')->format('Y-m-d');
                    $s->bind_param('iiddss', $cod_compra, $i, $monto_cuota, $monto_cuota, $venci, $cap_estado);
                    $s->execute();
                }
            }
            $s->close();
        } catch (Throwable $e) {
            // MyISAM no tiene ROLLBACK: se deshace a mano lo que alcanzó a insertarse
            $mysqli->query("DELETE FROM iva_compra WHERE cod_compra = $cod_compra AND id_nota IS NULL");
            $mysqli->query("DELETE FROM cuentas_a_pagar WHERE cod_compra = $cod_compra");
            $mysqli->query("DELETE FROM detalle_compra WHERE cod_compra = $cod_compra");
            $mysqli->query("DELETE FROM compra WHERE cod_compra = $cod_compra");
            flujo_error_json('Error al registrar la compra: ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Compra registrada correctamente', 'estado' => $estado, 'codigo' => $cod_compra]);
        exit;
    }

    /* =====================================================================================
       CONFIRMAR COMPRA: PENDIENTE -> ACTIVO y suma el stock
       ===================================================================================== */
    if ($accion === 'confirm') {
        $cod_compra = (int) ($_POST['codigo'] ?? 0);
        if ($cod_compra <= 0) {
            flujo_error_json('Código de compra inválido');
        }
        // El estado cambia primero y de forma atómica (solo un clic gana). El stock se suma solo si se ganó el cambio,
        // por eso un doble clic o dos pestañas ya no pueden sumar el stock dos veces.
        $r = flujo_transicion('compra', $cod_compra, 'confirm', function () use ($mysqli, $cod_compra) {
            // La orden de origen queda COMPRADA (ya siguió el flujo). Si falla algo después, se vuelve a liberar.
            flujo_propagar('compra', $cod_compra, 'consumir');
            try {
            $s = $mysqli->prepare("SELECT cod_producto, cantidad FROM detalle_compra WHERE cod_compra = ?");
            $s->bind_param('i', $cod_compra);
            $s->execute();
            $det = $s->get_result()->fetch_all(MYSQLI_ASSOC);
            $s->close();
            // stock_prod tiene PK(cod_producto): el alta y la suma van en una sola sentencia
            $u = $mysqli->prepare("INSERT INTO stock_prod (cod_producto, cantidad) VALUES (?, ?)
                                   ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)");
            foreach ($det as $d) {
                $cod = (int) $d['cod_producto'];
                $cant = (int) round((float) $d['cantidad']);
                $u->bind_param('ii', $cod, $cant);
                $u->execute();
            }
            $u->close();
            } catch (Throwable $e) {
                flujo_propagar('compra', $cod_compra, 'liberar');
                throw $e;
            }
        });
        flujo_responder($r);
    }

    /* =====================================================================================
       ANULAR COMPRA: ACTIVO -> ANULADO, resta el stock y anula las cuentas a pagar
       ===================================================================================== */
    if ($accion === 'cancel') {
        $cod_compra = (int) ($_POST['codigo'] ?? 0);
        if ($cod_compra <= 0) {
            flujo_error_json('Código de compra inválido');
        }
        // Regla de negocio previa: no se anula una compra con cuotas ya pagadas
        $s = $mysqli->prepare("SELECT COUNT(*) FROM cuentas_a_pagar WHERE cod_compra = ? AND cap_estado = 'PAGADO'");
        $s->bind_param('i', $cod_compra);
        $s->execute();
        $pagadas = (int) $s->get_result()->fetch_row()[0];
        $s->close();
        if ($pagadas > 0) {
            flujo_error_json('No se puede anular la compra porque tiene cuentas ya pagadas.');
        }

        $r = flujo_transicion('compra', $cod_compra, 'cancel', function () use ($mysqli, $cod_compra) {
            // La orden de origen vuelve a quedar APROBADA y disponible para generar otra compra
            flujo_propagar('compra', $cod_compra, 'liberar');
            try {
            $s = $mysqli->prepare("SELECT cod_producto, cantidad FROM detalle_compra WHERE cod_compra = ?");
            $s->bind_param('i', $cod_compra);
            $s->execute();
            $det = $s->get_result()->fetch_all(MYSQLI_ASSOC);
            $s->close();
            // Revertir el stock que sumó la confirmación (solo se llega aquí si la compra estaba ACTIVO)
            $u = $mysqli->prepare("UPDATE stock_prod SET cantidad = GREATEST(cantidad - ?, 0) WHERE cod_producto = ?");
            foreach ($det as $d) {
                $cant = (int) round((float) $d['cantidad']);
                $cod = (int) $d['cod_producto'];
                $u->bind_param('ii', $cant, $cod);
                $u->execute();
            }
            $u->close();
            $c = $mysqli->prepare("UPDATE cuentas_a_pagar SET cap_estado = 'ANULADO' WHERE cod_compra = ?");
            $c->bind_param('i', $cod_compra);
            $c->execute();
            $c->close();
            iva_anular($cod_compra);   // el IVA de la factura deja de contar (queda ANULADO, no se borra)
            } catch (Throwable $e) {
                flujo_propagar('compra', $cod_compra, 'consumir');
                throw $e;
            }
        });
        flujo_responder($r);
    }

    flujo_error_json('Acción no reconocida');
} catch (Throwable $e) {
    http_response_code(500);
    flujo_error_json('Error interno: ' . $e->getMessage());
}
