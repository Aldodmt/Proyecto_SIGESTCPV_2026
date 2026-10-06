<?php
require_once '../config/database.php';

if (!isset($_POST['id_pedido']))
    exit;

$id_pedido = intval($_POST['id_pedido']);

// Traer productos del pedido seleccionado
$sql = mysqli_query($mysqli, "
    SELECT dp.id_pedido, dp.cod_producto, p.p_descrip, dp.cantidad
    FROM det_pedido dp
    JOIN producto p ON dp.cod_producto = p.cod_producto
    WHERE dp.id_pedido = $id_pedido
");

if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
        ?>
        <tr id="fila_<?= $row['cod_producto'] ?>" data-cod="<?= $row['cod_producto'] ?>" data-pedido="<?= $id_pedido ?>"
            data-tipo="pedido">
            <td><?= $row['id_pedido'] ?></td>
            <td><?= $row['p_descrip'] ?></td>
            <td class="cantidad"><?= $row['cantidad'] ?></td>
            <td>
                <input type="number" class="form-control precio_unit" min="0">
            </td>
            <td class="subtotal">0</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="eliminarPedido('<?= $id_pedido ?>')">
                    <i class="cil-trash"></i>
                </button>
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No hay productos en este pedido.</td></tr>";
}
?>
<script>
    // Recalcular subtotales para las filas cargadas
    document.querySelectorAll('.precio_unit').forEach(function (input) {
        input.addEventListener('input', function () {
            const row = input.closest('tr');
            const cantidad = parseInt(row.querySelector('.cantidad').textContent) || 0;
            const precio = parseFloat(input.value) || 0;
            row.querySelector('.subtotal').textContent = (cantidad * precio).toFixed(2);
        });
    });
</script>