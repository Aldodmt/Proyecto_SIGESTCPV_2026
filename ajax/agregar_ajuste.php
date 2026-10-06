<?php
require_once '../config/database.php';

if (!isset($_POST['id_producto'])) {
    exit;
}

$id_producto = intval($_POST['id_producto']);
$cantidad_a = floatval($_POST['cantidad_a'] ?? 0);

if ($cantidad_a <= 0) {
    echo "<div class='alert alert-warning'>Cantidad inválida.</div>";
    exit;
}

// Traer info del producto desde la vista de stock
$sql = mysqli_query($mysqli, "
    SELECT cod_producto, t_p_descrip, u_descrip, p_descrip, cantidad
    FROM v_stock
    WHERE cod_producto = $id_producto
");
if (mysqli_num_rows($sql) == 0) {
    echo "<div class='alert alert-warning'>Producto no encontrado.</div>";
    exit;
}

$row = mysqli_fetch_assoc($sql);
$cantidad_final = $row['cantidad'] - $cantidad_a;
if ($cantidad_final < 0)
    $cantidad_final = 0; // Evitar negativo

?>

<tr data-id="<?= $id_producto ?>" id="fila_<?= $id_producto ?>">
    <td><?= $row['cod_producto'] ?></td>
    <td><?= $row['t_p_descrip'] ?></td>
    <td><?= $row['u_descrip'] ?></td>
    <td><?= $row['p_descrip'] ?></td>
    <td class="cantidad_anterior"><?= $row['cantidad'] ?></td>
    <td class="cantidad_ajustada"><?= $cantidad_a ?></td>
    <td class="cantidad_final"><?= $cantidad_final ?></td>
    <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(<?= $id_producto ?>)">
            <i class="cil-trash"></i>
        </button>
    </td>
</tr>