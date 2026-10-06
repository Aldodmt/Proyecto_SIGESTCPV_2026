<?php
session_start();
require_once '../../config/database.php';

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
    exit;
}

$id_user = $_SESSION['id_user'];

if ($_GET['act'] === 'insert') {

    // Recibir datos del formulario
    $codigo = intval($_POST['codigo']);
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivo'];
    $productos_json = $_POST['productos'] ?? '[]';

    $productos = json_decode($productos_json, true);

    if (empty($productos)) {
        die("No se han agregado productos.");
    }

    // Insertar cabecera del ajuste
    $stmt = $mysqli->prepare("INSERT INTO ajuste_com (id_ajuste, fecha_ajuste, motivo, estado, id_user) VALUES (?, ?, ?, 'ACTIVO', ?)");
    $stmt->bind_param("issi", $codigo, $fecha, $motivo, $id_user);
    $stmt->execute();
    $stmt->close();

    // Recorrer productos y guardar detalle y actualizar stock
    foreach ($productos as $p) {
        $id_producto = intval($p['id']);
        $cantidad_ajustada = floatval($p['cantidad']);

        // Traer cantidad anterior desde stock
        $query = $mysqli->prepare("SELECT cantidad FROM stock_prod WHERE cod_producto = ?");
        $query->bind_param("i", $id_producto);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        $cantidad_anterior = $row['cantidad'] ?? 0;
        $query->close();

        // Insertar detalle
        $stmt_det = $mysqli->prepare("INSERT INTO det_ajuste (id_ajuste, cod_producto, cantidad_ajustada, cantidad_anterior) VALUES (?, ?, ?, ?)");
        $stmt_det->bind_param("iiii", $codigo, $id_producto, $cantidad_ajustada, $cantidad_anterior);
        $stmt_det->execute();
        $stmt_det->close();

        // Actualizar stock
        $nueva_cantidad = max($cantidad_anterior - $cantidad_ajustada, 0);
        $stmt_stock = $mysqli->prepare("UPDATE stock_prod SET cantidad = ? WHERE cod_producto = ?");
        $stmt_stock->bind_param("ii", $nueva_cantidad, $id_producto);
        $stmt_stock->execute();
        $stmt_stock->close();
    }

    echo json_encode(['success' => true, 'message' => 'Ajuste guardado correctamente']);
    exit;
}

// Anular ajuste
if ($_GET['act'] === 'anular' && isset($_GET['id_ajuste'])) {
    $id_ajuste = intval($_GET['id_ajuste']);

    // Verificar si ya está anulado
    $res = $mysqli->query("SELECT estado FROM ajuste_com WHERE id_ajuste = $id_ajuste");
    $row = $res->fetch_assoc();
    if (!$row)
        header("Location: ../../main.php?module=ajuste&alert=2");

    if ($row['estado'] === 'anulado') {
        header("Location: ../../main.php?module=ajuste&alert=6");
        exit;
    }

    // Marcar como anulado
    $stmt = $mysqli->prepare("UPDATE ajuste_com SET estado = 'ANULADO', anulado_por = ?, anulado_fecha = CURDATE(), anulado_hora = CURTIME() WHERE id_ajuste = ?");
    $stmt->bind_param("ii", $id_user, $id_ajuste);
    $stmt->execute();
    $stmt->close();

    // Restaurar stock
    $query_det = $mysqli->prepare("SELECT cod_producto, cantidad_ajustada FROM det_ajuste WHERE id_ajuste = ?");
    $query_det->bind_param("i", $id_ajuste);
    $query_det->execute();
    $res_det = $query_det->get_result();
    while ($row_det = $res_det->fetch_assoc()) {
        $cod_producto = $row_det['cod_producto'];
        $cant_ajustada = $row_det['cantidad_ajustada'];

        // Actualizar stock
        $stmt_stock = $mysqli->prepare("UPDATE stock_prod SET cantidad = cantidad + ? WHERE cod_producto = ?");
        $stmt_stock->bind_param("ii", $cant_ajustada, $cod_producto);
        $stmt_stock->execute();
        $stmt_stock->close();
    }
    $query_det->close();

    header("Location: ../../main.php?module=ajuste&alert=1");
    exit;
}
?>