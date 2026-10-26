<?php
require_once __DIR__ . '/../../../config/permisos.php';
guard_endpoint('presupuesto', 'leer', 'fragmento');   // control de acceso por rol (config/permisos.php)
include '../../../config/database.php';

if (isset($_POST['id_pedido'])) {
    $id_pedido = $_POST['id_pedido'];

    $query = "SELECT cod_producto, p_descrip FROM v_pedido WHERE estado = 'aprobado' AND id_pedido = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();

    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    echo json_encode($productos);
}
?>