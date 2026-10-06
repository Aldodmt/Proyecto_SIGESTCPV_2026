<?php
session_start();
header('Content-Type: application/json'); // Forzar JSON
require_once '../../config/database.php';

// Verificar sesión
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'No se detectó usuario logueado.']);
    exit();
}

// Tomar acción
$accion = $_POST['act'] ?? $_GET['act'] ?? '';

// =========================
// INSERTAR PRESUPUESTO
// =========================
if ($accion === 'insert') {
    $codigo = intval($_POST['codigo'] ?? 0);
    $codigo_proveedor = intval($_POST['codigo_proveedor'] ?? 0);
    $fecha_e = $_POST['fecha_e'] ?? '';
    $fecha_v = $_POST['fecha_v'] ?? '';
    $id_pedido = $_POST['id_pedido'] ?? null; // permite null
    $productos_json = $_POST['productos_json'] ?? '';

    // Validaciones básicas
    if (!$codigo || !$codigo_proveedor || !$fecha_e || !$fecha_v) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos para guardar presupuesto']);
        exit;
    }

    if (strtotime($fecha_v) <= strtotime($fecha_e)) {
        echo json_encode(['success' => false, 'message' => 'La fecha de vencimiento debe ser mayor que la fecha de emisión']);
        exit;
    }

    $estado = 'PENDIENTE';

    // Preparar id_pedido para SQL
    $id_pedido_sql = is_numeric($id_pedido) && $id_pedido > 0 ? $id_pedido : "NULL";

    // Insertar cabecera
    $query_cabecera = mysqli_query($mysqli, "INSERT INTO presupuesto 
        (id_presupuesto, fecha_presu, fecha_vencimiento, cod_proveedor, estado, id_pedido, id_user) 
        VALUES ($codigo, '$fecha_e', '$fecha_v', $codigo_proveedor, '$estado', $id_pedido_sql, $id_user)");

    if (!$query_cabecera) {
        echo json_encode(['success' => false, 'message' => 'Error al insertar cabecera: ' . mysqli_error($mysqli)]);
        exit;
    }

    // Insertar detalle
    if (!empty($productos_json)) {
        $productos = json_decode($productos_json, true);
        if (!is_array($productos)) {
            echo json_encode(['success' => false, 'message' => 'Productos inválidos']);
            exit;
        }

        foreach ($productos as $producto) {
            $cod_producto = intval($producto['codigo_producto'] ?? 0);
            $cantidad = floatval($producto['cantidad'] ?? 0);
            $precio_unit = floatval($producto['precio_unitario'] ?? 0);

            if (!$cod_producto || $cantidad <= 0 || $precio_unit <= 0)
                continue;

            $query_detalle = mysqli_query($mysqli, "INSERT INTO det_presu 
                (id_presupuesto, cod_producto, cantidad, precio_unit)
                VALUES ($codigo, $cod_producto, $cantidad, $precio_unit)");

            if (!$query_detalle) {
                echo json_encode(['success' => false, 'message' => 'Error al insertar detalle: ' . mysqli_error($mysqli)]);
                exit;
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Debe agregar al menos un producto']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Presupuesto guardado correctamente', 'estado' => $estado]);
    exit;
}

// =========================
// CONFIRMAR PRESUPUESTO
// =========================
if ($accion === 'confirm') {
    $codigo = intval($_POST['codigo'] ?? 0);
    if (!$codigo) {
        echo json_encode(['success' => false, 'message' => 'Código de presupuesto inválido']);
        exit;
    }

    $query = mysqli_query($mysqli, "SELECT estado FROM presupuesto WHERE id_presupuesto = $codigo");
    if (!$query || mysqli_num_rows($query) === 0) {
        echo json_encode(['success' => false, 'message' => 'Presupuesto no encontrado']);
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    $estado_actual = $row['estado'];

    if ($estado_actual !== 'PENDIENTE') {
        echo json_encode(['success' => false, 'message' => 'Solo se puede aprobar un presupuesto pendiente']);
        exit;
    }

    $update = mysqli_query($mysqli, "UPDATE presupuesto SET estado='APROBADO' WHERE id_presupuesto = $codigo");
    if (!$update) {
        echo json_encode(['success' => false, 'message' => 'Error al aprobar presupuesto: ' . mysqli_error($mysqli)]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Presupuesto aprobado correctamente', 'estado' => 'APROBADO']);
    exit;
}

// =========================
// ANULAR PRESUPUESTO
// =========================
if ($accion === 'cancel') {
    $codigo = intval($_POST['codigo'] ?? 0);
    if (!$codigo) {
        echo json_encode(['success' => false, 'message' => 'Código de presupuesto inválido']);
        exit;
    }

    $query = mysqli_query($mysqli, "SELECT estado FROM presupuesto WHERE id_presupuesto = $codigo");
    if (!$query || mysqli_num_rows($query) === 0) {
        echo json_encode(['success' => false, 'message' => 'Presupuesto no encontrado']);
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    $estado_actual = $row['estado'];

    if (!in_array($estado_actual, ['PENDIENTE', 'APROBADO', 'BORRADOR'])) {
        echo json_encode(['success' => false, 'message' => 'No se puede anular este presupuesto']);
        exit;
    }

    $update = mysqli_query($mysqli, "UPDATE presupuesto SET estado = 'ANULADO', 
                anulado_por = $id_user, 
                anulado_fecha = CURDATE(), 
                anulado_hora = CURTIME() WHERE id_presupuesto = $codigo");
    if (!$update) {
        echo json_encode(['success' => false, 'message' => 'Error al anular presupuesto: ' . mysqli_error($mysqli)]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Presupuesto anulado correctamente', 'estado' => 'ANULADO']);
    exit;
}

// =========================
// ACCIÓN NO RECONOCIDA
// =========================
echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
exit;
