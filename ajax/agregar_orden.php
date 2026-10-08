<?php
require_once '../config/database.php';

if (!isset($_POST['id_presupuesto']))
    exit;

$id_presupuesto = intval($_POST['id_presupuesto']);

$sql = mysqli_query($mysqli, "
    SELECT 
        dp.id_presupuesto,
        pr.cod_proveedor,
        pr.razon_social,
        dp.cod_producto,
        p.p_descrip,
        dp.cantidad,
        dp.precio_unit
    FROM det_presu dp
    JOIN producto p ON dp.cod_producto = p.cod_producto
    JOIN presupuesto pre ON dp.id_presupuesto = pre.id_presupuesto
    JOIN proveedor pr ON pre.cod_proveedor = pr.cod_proveedor
    WHERE dp.id_presupuesto = $id_presupuesto
");

if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
        $subtotal = $row['cantidad'] * $row['precio_unit'];
        ?>
        <tr id="fila_<?= $row['cod_producto'] ?>" data-cod="<?= $row['cod_producto'] ?>" data-presu="<?= $id_presupuesto ?>"
            data-tipo="presupuesto">

            <td><?= htmlspecialchars($row['id_presupuesto']) ?></td>
            <td><?= htmlspecialchars($row['razon_social']) ?></td>
            <td><?= htmlspecialchars($row['p_descrip']) ?></td>

            <!-- Usamos data-* para valores originales -->
            <td class="cantidad" data-valor="<?= $row['cantidad'] ?>"><?= number_format($row['cantidad'], 2) ?></td>
            <td class="precio_unit" data-valor="<?= $row['precio_unit'] ?>">
                <?= number_format($row['precio_unit'], 2) ?>
            </td>
            <td class="subtotal" data-valor="<?= $subtotal ?>"><?= number_format($subtotal, 2) ?></td>

            <td class="text-center">
                <button class="btn btn-danger btn-sm" onclick="eliminarPresupuesto('<?= $id_presupuesto ?>')">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>No hay productos en este presupuesto.</td></tr>";
}
?>