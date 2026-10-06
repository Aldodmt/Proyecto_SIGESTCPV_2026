<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

// Comprobar sesión
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

// Tomar acción
$accion = $_POST['act'] ?? $_GET['act'] ?? '';

// =========================
// INSERT
// =========================
if ($accion === 'insert') {
    $codigo = intval($_POST['codigo']);
    $fecha_e = mysqli_real_escape_string($mysqli, $_POST['fecha_E'] ?? date('Y-m-d'));
    $hora = mysqli_real_escape_string($mysqli, $_POST['hora'] ?? date('H:i:s'));
    $id_presupuesto = intval($_POST['id_presupuesto'] ?? 0);
    $id_user = intval($_SESSION['id_user'] ?? 0);
    $productos_json = $_POST['productos_json'] ?? '';

    if (empty($productos_json)) {
        echo json_encode(['success' => false, 'message' => 'Debe agregar al menos un producto.']);
        exit;
    }

    // =========================
    // Determinar proveedor
    // =========================
    if ($id_presupuesto > 0) {
        // Flujo con presupuesto: obtener proveedor desde presupuesto
        $query_prov = mysqli_query($mysqli, "SELECT cod_proveedor FROM presupuesto WHERE id_presupuesto = $id_presupuesto");
        if ($query_prov && mysqli_num_rows($query_prov) > 0) {
            $row_prov = mysqli_fetch_assoc($query_prov);
            $codigo_proveedor = intval($row_prov['cod_proveedor']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo recuperar el proveedor del presupuesto.']);
            exit;
        }
    } else {
        // Flujo manual: tomar proveedor del select
        $codigo_proveedor = intval($_POST['codigo_proveedor'] ?? 0);
        if ($codigo_proveedor <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debes seleccionar un proveedor para la orden manual.']);
            exit;
        }
    }

    $estado = 'PENDIENTE';

    // Insertar cabecera
    $sql_c = "INSERT INTO orden_compra 
        (id_orden_comp, fecha, estado, hora, id_user, id_presupuesto, cod_proveedor)
        VALUES ($codigo, '$fecha_e', '$estado', '$hora', $id_user, " . ($id_presupuesto > 0 ? $id_presupuesto : "NULL") . ", $codigo_proveedor)";

    if (!mysqli_query($mysqli, $sql_c)) {
        echo json_encode(['success' => false, 'message' => 'Error al insertar cabecera: ' . mysqli_error($mysqli)]);
        exit;
    }

    // Insertar detalle
    $productos = json_decode($productos_json, true);
    if (!is_array($productos)) {
        echo json_encode(['success' => false, 'message' => 'Productos inválidos.']);
        exit;
    }

    foreach ($productos as $p) {
        $cod_producto = mysqli_real_escape_string($mysqli, $p['codigo_producto']);
        $cantidad = floatval($p['cantidad'] ?? 0);
        $precio_unit = floatval($p['precio_unitario'] ?? 0);

        $sql_d = "INSERT INTO detalle_orden_comp 
            (id_orden_comp, cod_producto, precio_unit, cantidad)
            VALUES ($codigo, '$cod_producto', $precio_unit, $cantidad)";
        if (!mysqli_query($mysqli, $sql_d)) {
            echo json_encode(['success' => false, 'message' => 'Error al insertar detalle: ' . mysqli_error($mysqli)]);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Orden de compra guardada correctamente']);
    exit;
}

// =========================
// CONFIRMAR PRESUPUESTO
// =========================
if ($accion === 'confirm') {
    $codigo = intval($_POST['codigo'] ?? 0);
    if (!$codigo) {
        echo json_encode(['success' => false, 'message' => 'Código de orden inválido']);
        exit;
    }

    $query = mysqli_query($mysqli, "SELECT estado FROM orden_compra WHERE id_orden_comp = $codigo");
    if (!$query || mysqli_num_rows($query) === 0) {
        echo json_encode(['success' => false, 'message' => 'Orden de compra no encontrada']);
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    $estado_actual = $row['estado'];

    if ($estado_actual !== 'PENDIENTE') {
        echo json_encode(['success' => false, 'message' => 'Solo se puede aprobar una orden de compra pendiente']);
        exit;
    }

    $update = mysqli_query($mysqli, "UPDATE orden_compra SET estado='APROBADO' WHERE id_orden_comp = $codigo");
    if (!$update) {
        echo json_encode(['success' => false, 'message' => 'Error al aprobar orden de compra: ' . mysqli_error($mysqli)]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Orden de compra aprobada correctamente', 'estado' => 'APROBADO']);
    exit;
}

// =========================
// ANULAR ORDEN DE COMPRA
// =========================
if ($accion === 'cancel') {
    $codigo = intval($_POST['codigo'] ?? 0);
    if (!$codigo) {
        echo json_encode(['success' => false, 'message' => 'Código de orden de compra inválido']);
        exit;
    }

    $query = mysqli_query($mysqli, "SELECT estado FROM orden_compra WHERE id_orden_comp = $codigo");
    if (!$query || mysqli_num_rows($query) === 0) {
        echo json_encode(['success' => false, 'message' => 'Orden de compra no encontrada']);
        exit;
    }

    $row = mysqli_fetch_assoc($query);
    $estado_actual = $row['estado'];

    if (!in_array($estado_actual, ['PENDIENTE', 'APROBADO', 'BORRADOR'])) {
        echo json_encode(['success' => false, 'message' => 'No se puede anular esta orden de compra']);
        exit;
    }

    $id_user = intval($_SESSION['id_user'] ?? 0);

    $update = mysqli_query($mysqli, "UPDATE orden_compra SET estado='ANULADO',
                anulado_por = $id_user, 
                anulado_fecha = CURDATE(), 
                anulado_hora = CURTIME() WHERE id_orden_comp = $codigo");
    if (!$update) {
        echo json_encode(['success' => false, 'message' => 'Error al anular orden de compra: ' . mysqli_error($mysqli)]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Orden de compra anulada correctamente', 'estado' => 'ANULADO']);
    exit;
}

// =========================
// ACCIÓN NO RECONOCIDA
// =========================
echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
exit;
