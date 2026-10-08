<?php
require_once '../config/database.php';

if (!isset($_POST['id_orden_comp']))
    exit;

$id_orden_comp = intval($_POST['id_orden_comp']);

// Traer productos de la orden de compra seleccionada junto al proveedor
$sql = mysqli_query($mysqli, "
    SELECT dop.id_orden_comp, prov.cod_proveedor, prov.razon_social, prov.ruc, dop.cod_producto, 
           p.p_descrip, dop.precio_unit, dop.cantidad, p.tipo_impuesto
    FROM detalle_orden_comp dop
    JOIN producto p ON dop.cod_producto = p.cod_producto
    JOIN orden_compra op ON dop.id_orden_comp = op.id_orden_comp
    JOIN proveedor prov ON op.cod_proveedor = prov.cod_proveedor
    WHERE dop.id_orden_comp = $id_orden_comp
");

if (mysqli_num_rows($sql) > 0) {
    $cod_proveedor = null;
    $razon_social = null;
    $ruc = null;
    $productos_json = [];
    ?>
    <?php
    while ($row = mysqli_fetch_assoc($sql)) {
        if ($cod_proveedor === null) {
            $cod_proveedor = $row['cod_proveedor'];
            $razon_social = $row['razon_social'];
            $ruc = $row['ruc'];
        }

        // Valores numéricos "puros"
        $cantidad = (float) $row['cantidad'];
        $precio_unit = (float) $row['precio_unit'];
        $subtotal = $cantidad * $precio_unit;

        $productos_json[] = [
            'cod_producto' => $row['cod_producto'],
            'cantidad' => $cantidad,
            'precio_unit' => $precio_unit,
            'tipo_iva' => $row['tipo_impuesto']
        ];
        ?>
        <tr id="fila_<?= $row['cod_producto'] ?>" data-cod="<?= $row['cod_producto'] ?>"
            data-orden="<?= $row['id_orden_comp'] ?>" data-tipo="orden">
            <td><?= htmlspecialchars($row['id_orden_comp']) ?></td>
            <td><?= htmlspecialchars($row['p_descrip']) ?></td>
            <td class="cantidad" data-valor="<?= $cantidad ?>">
                <?= number_format($cantidad, 2, '.', '') ?>
            </td>
            <td class="precio_unit" data-valor="<?= $precio_unit ?>">
                <?= number_format($precio_unit, 2, '.', '') ?>
            </td>
            <td><?= htmlspecialchars($row['tipo_impuesto']) ?></td>
            <td class="subtotal" data-valor="<?= $subtotal ?>">
                <?= number_format($subtotal, 2, '.', '') ?>
            </td>

            <td class="text-center">
                <button class="btn btn-danger btn-sm" onclick="eliminarOrden(<?= $row['id_orden_comp'] ?>)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    <?php } ?>

    <script>
        var datosProveedor = {
            cod_proveedor: <?= json_encode($cod_proveedor) ?>,
            razon_social: <?= json_encode($razon_social) ?>,
            ruc: <?= json_encode($ruc) ?>,
            productos: <?= json_encode($productos_json) ?>
        };

        console.log("Datos enviados a form.php:", datosProveedor);

        function resetDatosProveedor() {
            datosProveedor.productos = [];
        }
    </script>
    <?php
} else {
    echo "<tr><td colspan='7' class='text-center'>No hay productos en esta orden de compra.</td></tr>";
}
?>