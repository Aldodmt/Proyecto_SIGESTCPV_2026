<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";

// Validar ID de pedido
if (!isset($_POST['id_pedido']) || !is_numeric($_POST['id_pedido'])) {
    echo "<div class='alert alert-danger'>ID de pedido no válido.</div>";
    exit;
}

$id_pedido = intval($_POST['id_pedido']);
$op = $_POST['op'] ?? 'add';

// -------------------------------------------------------------
// AGREGAR PRODUCTO AL PEDIDO
// -------------------------------------------------------------
if ($op === 'add') {
    $id_producto = intval($_POST['id'] ?? 0);
    $cantidad = floatval($_POST['cantidad'] ?? 0);

    if ($id_producto <= 0 || $cantidad <= 0) {
        echo "<div class='alert alert-danger'>Datos inválidos. Verifique el producto y la cantidad.</div>";
        exit;
    }

    if ($cantidad >= 1000) {
        echo "<div class='alert alert-danger'>El producto supera la cantidad maxima permitida.</div>";
        exit;
    }

    // Validar si el producto existe
    $sql_producto = mysqli_query($mysqli, "SELECT cod_producto FROM producto WHERE cod_producto = $id_producto");
    if (mysqli_num_rows($sql_producto) === 0) {
        echo "<div class='alert alert-danger'>El producto no existe.</div>";
        exit;
    }

    // Validar stock disponible (si aplica)
    $sql_stock = mysqli_query($mysqli, "SELECT cantidad FROM stock_prod WHERE cod_producto = $id_producto");
    $row_stock = mysqli_fetch_assoc($sql_stock);
    $stock_disponible = floatval($row_stock['cantidad'] ?? 0);

    /*
    if ($cantidad > $stock_disponible) {
        echo "<div class='alert alert-warning'>No puedes pedir más de lo disponible en stock ($stock_disponible).</div>";
        exit;
    }
    */

    // Verificar si el producto ya existe en el detalle
    $query_check = mysqli_query($mysqli, "
        SELECT cantidad 
        FROM det_pedido 
        WHERE id_pedido = $id_pedido AND cod_producto = $id_producto
    ");

    if (mysqli_num_rows($query_check) > 0) {
        // Actualizar cantidad sumando
        mysqli_query($mysqli, "
            UPDATE det_pedido 
            SET cantidad = cantidad + $cantidad 
            WHERE id_pedido = $id_pedido AND cod_producto = $id_producto
        ");
    } else {
        // Insertar nuevo detalle
        mysqli_query($mysqli, "
            INSERT INTO det_pedido (id_pedido, cod_producto, cantidad)
            VALUES ($id_pedido, $id_producto, $cantidad)
        ");
    }
}

// -------------------------------------------------------------
// ELIMINAR PRODUCTO DEL PEDIDO
// -------------------------------------------------------------
elseif ($op === 'delete') {
    $id_producto = intval($_POST['id'] ?? 0);

    if ($id_producto <= 0) {
        echo "<div class='alert alert-danger'>Producto no válido.</div>";
        exit;
    }

    mysqli_query($mysqli, "
        DELETE FROM det_pedido 
        WHERE id_pedido = $id_pedido AND cod_producto = $id_producto
    ");
}

// Listar productos del pedido
$sql_detalle = mysqli_query($mysqli, "
    SELECT dp.*, p.p_descrip, u.u_descrip, p.tipo_impuesto
    FROM det_pedido dp
    JOIN producto p ON dp.cod_producto = p.cod_producto
    JOIN u_medida u ON p.id_u_medida = u.id_u_medida
    WHERE dp.id_pedido = $id_pedido
");

echo "<table class='table table-striped table-hover align-middle'>
        <thead class='table-primary'>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Tipo de Impuesto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>";

while ($prod = mysqli_fetch_assoc($sql_detalle)) {
    echo "<tr>
        <td>{$prod['cod_producto']}</td>
        <td>{$prod['p_descrip']}</td>
        <td>{$prod['cantidad']}</td>
        <td>{$prod['u_descrip']}</td>
        <td>{$prod['tipo_impuesto']}</td>
        <td>
            <button class='btn btn-danger btn-sm' onclick='eliminar({$prod['cod_producto']}, {$id_pedido})'>
                Eliminar
            </button>
        </td>
    </tr>";
}

echo "</tbody></table>";
?>