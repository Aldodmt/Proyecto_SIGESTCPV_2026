<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

// Comprobar sesión
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

$usuario = intval($_SESSION['id_user'] ?? 0);
if ($usuario <= 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no identificado']);
    exit;
}

// Acción
$accion = $_POST['act'] ?? $_GET['act'] ?? '';

// INSERTAR COMPRA
if ($accion === 'insert') {
    $cod_compra = intval($_POST['codigo']);
    $id_orden_compra = $_POST['id_orden_compra'] ?? null; // Puede ser NULL
    $id_orden_compra = $id_orden_compra !== '' ? intval($id_orden_compra) : null;
    $fecha = mysqli_real_escape_string($mysqli, $_POST['fecha'] ?? date('Y-m-d'));
    $hora = mysqli_real_escape_string($mysqli, $_POST['hora'] ?? date('H:i:s'));
    $cod_proveedor = intval($_POST['cod_proveedor'] ?? 0);
    $nro_factura = mysqli_real_escape_string($mysqli, $_POST['nro_factura'] ?? '');
    $fac_emi = mysqli_real_escape_string($mysqli, $_POST['fac_emi'] ?? '');
    $nro_timbrado = intval($_POST['nro_timbrado'] ?? 0);
    $timb_venci = mysqli_real_escape_string($mysqli, $_POST['timb_venci'] ?? '');
    $tipo_factura = strtoupper($_POST['tipo_fac'] ?? 'IMPRESA');
    $productos_json = $_POST['productos_json'] ?? '';
    $com_condicion = strtoupper($_POST['condicion_c'] ?? '');
    $remision = strtoupper($_POST['remision'] ?? '');
    $cuotas = intval($_POST['cant_cuotas'] ?? 1);
    $intervalo = intval($_POST['id_intervalo'] ?? 1);

    // VALIDACIONES
    if ($cod_proveedor <= 0) {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar un proveedor. sexo']);
        exit;
    }

    if (empty($nro_factura)) {
        echo json_encode(['success' => false, 'message' => 'El número de factura es obligatorio.']);
        exit;
    }

    //Validar fecha de emisión no mayor a la fecha actual
    if (!empty($fac_emi)) {
        $fecha_actual = date('Y-m-d');
        if ($fac_emi > $fecha_actual) {
            echo json_encode(['success' => false, 'message' => 'La fecha de emisión no puede ser mayor a la fecha actual.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Debe ingresar la fecha de emisión de la factura.']);
        exit;
    }

    if ($tipo_factura !== 'ELECTRONICA' && $nro_timbrado <= 0) {
        echo json_encode(['success' => false, 'message' => 'El número de timbrado es obligatorio.']);
        exit;
    }

    if ($tipo_factura !== 'ELECTRONICA' && empty($timb_venci)) {
        echo json_encode(['success' => false, 'message' => 'Debe ingresar la fecha de vencimiento del timbrado.']);
        exit;
    }

    // Validar remision
    $valores_remision = ['CON', 'SIN'];
    if (!in_array($remision, $valores_remision)) {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar si la compra tiene remisión o no.']);
        exit;
    }

    // Validar condicion de pago
    $valores_condicion = ['CONTADO', 'CREDITO'];
    if (!in_array($com_condicion, $valores_condicion)) {
        echo json_encode(['success' => false, 'message' => 'Debe seleccionar una condición de compra válida (CONTADO o CRÉDITO).']);
        exit;
    }

    // Validar cantidad de cuotas (máximo 12)
    if ($cuotas < 1 || $cuotas > 12) {
        echo json_encode(['success' => false, 'message' => 'La cantidad de cuotas debe ser entre 1 y 12.']);
        exit;
    }

    // Validar intervalo de pago (máximo 90 días)
    if ($intervalo < 1 || $intervalo > 90) {
        echo json_encode(['success' => false, 'message' => 'El intervalo de pago debe ser entre 1 y 90 días.']);
        exit;
    }

    // Validar timbrado si no es factura electrónica
    if ($tipo_factura !== 'ELECTRONICA' && $timb_venci <= date('Y-m-d')) {
        echo json_encode(['success' => false, 'message' => 'El timbrado ha vencido.']);
        exit;
    }

    // DETALLE DE PRODUCTOS
    if (!empty($productos_json)) {
        $productos = json_decode($productos_json, true);
        if (!is_array($productos)) {
            echo json_encode(['success' => false, 'message' => 'Productos inválidos.']);
            exit;
        }

        $com_total = 0;

        foreach ($productos as $p) {
            $cod_producto = intval($p['codigo_producto']);
            $cantidad = floatval($p['cantidad'] ?? 0);
            $precio_unit = floatval($p['precio_unitario'] ?? 0);
            $tipo_iva = strtoupper($p['tipo_iva'] ?? 'EXENTA'); // 10%, 5%, EXENTA

            if (!in_array($tipo_iva, ['10%', '5%', 'EXENTA'])) {
                $tipo_iva = 'EXENTA';
            }

            $subtotal = $cantidad * $precio_unit;
            $com_total += $subtotal;

            $exentas = 0;
            $iva5 = 0;
            $iva10 = 0;

            switch ($tipo_iva) {
                case '10%':
                    $iva10 = $subtotal - ($subtotal / 1.10);
                    break;
                case '5%':
                    $iva5 = $subtotal - ($subtotal / 1.05);
                    break;
                default:
                    $exentas = $subtotal;
            }

            $stmt_d = $mysqli->prepare("INSERT INTO detalle_compra 
                (cod_producto, cod_compra, precio, cantidad, tipo_iva, exentas, iva5, iva10)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_d->bind_param("iidisddd", $cod_producto, $cod_compra, $precio_unit, $cantidad, $tipo_iva, $exentas, $iva5, $iva10);
            $stmt_d->execute();
            $stmt_d->close();
        }
    }

    // CABECERA COMPRA
    $estado = 'PENDIENTE';
    $stmt_c = $mysqli->prepare("INSERT INTO compra 
        (cod_compra, cod_proveedor, fecha, estado, hora, id_user, id_orden_comp, fac_numero, fac_emision, tipo_factura, con_sin_remision, timbrado_nro, timb_fecha_venci, com_condicion, total_compra)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_c->bind_param("iisssiissssissd", $cod_compra, $cod_proveedor, $fecha, $estado, $hora, $usuario, $id_orden_compra, $nro_factura, $fac_emi, $tipo_factura, $remision, $nro_timbrado, $timb_venci, $com_condicion, $com_total);
    $stmt_c->execute();
    $stmt_c->close();

    //cambiamos el actualizar stock a "Confirmar"
    // Actualizar stock
    /* $query_stock = $mysqli->prepare("SELECT cantidad FROM stock_prod WHERE cod_producto = ?");
     $query_stock->bind_param("i", $cod_producto);
     $query_stock->execute();
     $res_stock = $query_stock->get_result();

     if ($res_stock->num_rows > 0) {
         $row_stock = $res_stock->fetch_assoc();
         $nueva_cantidad = $row_stock['cantidad'] + $cantidad;
         $stmt_update = $mysqli->prepare("UPDATE stock_prod SET cantidad = ? WHERE cod_producto = ?");
         $stmt_update->bind_param("ii", $nueva_cantidad, $cod_producto);
         $stmt_update->execute();
         $stmt_update->close();
     } else {
         $stmt_insert = $mysqli->prepare("INSERT INTO stock_prod (cod_producto, cantidad) VALUES (?, ?)");
         $stmt_insert->bind_param("ii", $cod_producto, $cantidad);
         $stmt_insert->execute();
         $stmt_insert->close();
     }
     $query_stock->close();*/

    // CALCULAR CUENTAS A PAGAR 
    $cap_fecha_emision = $fecha;

    if ($com_condicion === 'CONTADO') {
        // Fecha vencimiento = misma fecha
        $cap_venci = $cap_fecha_emision;

        // Insertar cuentas_a_pagar (pagado al contado)
        $cap_estado = 'PAGADO';
        $cap_saldo = 0;
        $stmt_cap = $mysqli->prepare("INSERT INTO cuentas_a_pagar (cod_compra, nro_cuota, cap_monto, cap_saldo, cap_fecha_venci, cap_estado)
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_cap->bind_param("iiddss", $cod_compra, $cuotas, $com_total, $cap_saldo, $cap_venci, $cap_estado);
        $stmt_cap->execute();
        $stmt_cap->close();

    } else { // CRÉDITO
        // Convertir total a pagar
        $monto_total = floatval($com_total ?? 0);
        if ($monto_total <= 0) {
            echo json_encode(['success' => false, 'message' => 'El total de la compra no puede ser 0.']);
            exit;
        }

        // Calcular el monto de cada cuota
        $monto_cuota = round($monto_total / $cuotas, 2);

        // Fecha base: la fecha de la compra
        $fecha_base = new DateTime($fecha);

        for ($i = 1; $i <= $cuotas; $i++) {
            // Clonar fecha base
            $fecha_vencimiento = clone $fecha_base;

            // Sumar intervalo multiplicado por (nro de cuota - 1)
            if ($i > 1) {
                $dias_sumar = $intervalo * ($i - 1);
                $fecha_vencimiento->modify("+$dias_sumar days");
            }

            // Preparar datos
            $cap_estado = 'PENDIENTE';
            $cap_saldo = $monto_cuota;

            // Insertar cada cuota en cuentas_a_pagar
            $stmt_cap = $mysqli->prepare("
                INSERT INTO cuentas_a_pagar 
                (cod_compra, nro_cuota, cap_monto, cap_saldo, cap_fecha_venci, cap_estado)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $fecha_venci_str = $fecha_vencimiento->format('Y-m-d');
            $stmt_cap->bind_param("iiddss", $cod_compra, $i, $monto_cuota, $cap_saldo, $fecha_venci_str, $cap_estado);
            $stmt_cap->execute();
            $stmt_cap->close();
        }
    }


    echo json_encode(['success' => true, 'message' => 'Compra registrada correctamente']);
    exit;
}

// CONFIRMAR COMPRA
if ($accion === 'confirm') {
    $cod_compra = intval($_POST['codigo'] ?? 0);
    if (!$cod_compra) {
        echo json_encode(['success' => false, 'message' => 'Código de compra inválido']);
        exit;
    }

    // Verificar si la compra existe y está activa
    $stmt = $mysqli->prepare("SELECT estado FROM compra WHERE cod_compra = ?");
    $stmt->bind_param("i", $cod_compra);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Compra no encontrada']);
        exit;
    }

    $row = $res->fetch_assoc();
    $estado_actual = strtoupper($row['estado']);
    $stmt->close();

    if ($estado_actual !== 'PENDIENTE') {
        echo json_encode(['success' => false, 'message' => 'Solo se pueden confirmar compras pendientes.']);
        exit;
    }

    // Iniciar transacción
    $mysqli->begin_transaction();

    try {
        // Obtener los productos de la compra
        $stmt_det = $mysqli->prepare("
            SELECT cod_producto, cantidad 
            FROM detalle_compra 
            WHERE cod_compra = ?
        ");
        $stmt_det->bind_param("i", $cod_compra);
        $stmt_det->execute();
        $res_det = $stmt_det->get_result();

        // Actualizar el stock por cada producto
        while ($row_det = $res_det->fetch_assoc()) {
            $cod_producto = intval($row_det['cod_producto']);
            $cantidad = floatval($row_det['cantidad']);

            // Intentar actualizar stock existente
            $stmt_upd = $mysqli->prepare("
                UPDATE stock_prod 
                SET cantidad = cantidad + ? 
                WHERE cod_producto = ?
            ");
            $stmt_upd->bind_param("di", $cantidad, $cod_producto);
            $stmt_upd->execute();

            // Si no existía el producto en stock, insertarlo
            if ($stmt_upd->affected_rows === 0) {
                $stmt_ins = $mysqli->prepare("INSERT INTO stock_prod (cod_producto, cantidad) VALUES (?, ?)");
                $stmt_ins->bind_param("id", $cod_producto, $cantidad);
                $stmt_ins->execute();
                $stmt_ins->close();
            }

            $stmt_upd->close();
        }

        $stmt_det->close();

        // Actualizar el estado de la compra
        $stmt_upd_compra = $mysqli->prepare("
            UPDATE compra 
            SET estado = 'ACTIVO'
            WHERE cod_compra = ?
        ");
        $stmt_upd_compra->bind_param("i", $cod_compra);
        $stmt_upd_compra->execute();
        $stmt_upd_compra->close();

        // Confirmar transacción
        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Compra confirmada y stock actualizado correctamente.',
            'estado' => 'ACTIVO'
        ]);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error al confirmar la compra: ' . $e->getMessage()
        ]);
    }

    exit;
}

if ($accion === 'cancel') {
    $cod_compra = intval($_POST['codigo'] ?? 0);
    if (!$cod_compra) {
        echo json_encode(['success' => false, 'message' => 'Código de compra inválido']);
        exit;
    }

    //Verificar si la compra existe
    $stmt = $mysqli->prepare("SELECT estado FROM compra WHERE cod_compra = ?");
    $stmt->bind_param("i", $cod_compra);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Compra no encontrada']);
        exit;
    }

    $row = $res->fetch_assoc();
    $estado_actual = strtoupper($row['estado']);
    $stmt->close();

    //Validar estado
    if ($estado_actual === 'ANULADO') {
        echo json_encode(['success' => false, 'message' => 'La compra ya está anulada']);
        exit;
    }

    if (!in_array($estado_actual, ['PENDIENTE', 'ACTIVA'])) {
        echo json_encode(['success' => false, 'message' => 'Solo se pueden anular compras activas o confirmadas']);
        exit;
    }

    //Verificar si alguna cuenta está PAGADA
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
        echo json_encode(['success' => false, 'message' => 'No se puede anular la compra porque tiene cuentas ya pagadas.']);
        exit;
    }

    //Iniciar transacción
    $mysqli->begin_transaction();

    try {
        // Revertir stock (restar cantidades del inventario)
        $stmt_det = $mysqli->prepare("
            SELECT cod_producto, cantidad 
            FROM detalle_compra 
            WHERE cod_compra = ?
        ");
        $stmt_det->bind_param("i", $cod_compra);
        $stmt_det->execute();
        $res_det = $stmt_det->get_result();

        $stmt_stock = $mysqli->prepare("
            UPDATE stock_prod 
            SET cantidad = GREATEST(cantidad - ?, 0) 
            WHERE cod_producto = ?
        ");

        while ($row_det = $res_det->fetch_assoc()) {
            $cantidad = floatval($row_det['cantidad']);
            $cod_producto = intval($row_det['cod_producto']);
            $stmt_stock->bind_param("ii", $cantidad, $cod_producto);
            $stmt_stock->execute();
        }

        $stmt_stock->close();
        $stmt_det->close();

        // Anular todas las cuentas a pagar de la compra
        $stmt_cap = $mysqli->prepare("
            UPDATE cuentas_a_pagar 
            SET cap_estado = 'ANULADO' 
            WHERE cod_compra = ?
        ");
        $stmt_cap->bind_param("i", $cod_compra);
        $stmt_cap->execute();
        $stmt_cap->close();

        // Marcar la compra como ANULADA
        $stmt_compra = $mysqli->prepare("
            UPDATE compra 
            SET estado = 'ANULADO', 
                anulado_por = ?, 
                anulado_fecha = CURDATE(), 
                anulado_hora = CURTIME()
            WHERE cod_compra = ?
        ");
        $stmt_compra->bind_param("ii", $usuario, $cod_compra);
        $stmt_compra->execute();
        $stmt_compra->close();

        // 🔹 Confirmar transacción
        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Compra anulada correctamente',
            'estado' => 'ANULADO'
        ]);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error al anular la compra: ' . $e->getMessage()
        ]);
    }

    exit;
}

// ACCIÓN NO RECONOCIDA
echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
exit;
