<?php
require_once __DIR__ . '/../config/permisos.php';
guard_endpoint('compra', 'cualquiera', 'fragmento');   // 1.ª capa: permiso del rol (config/permisos.php)
require_once '../config/database.php';
require_once __DIR__ . '/../config/compras.php';

// Devuelve las filas del detalle de una ORDEN DE COMPRA y los datos del proveedor para armar la compra.
if (!isset($_POST['id_orden_comp']))
    exit;

$id_orden_comp = intval($_POST['id_orden_comp']);

// 2.ª capa: solo se puede facturar una orden APROBADA que no tenga ya una compra vigente.
if (($error = flujo_validar_origen('orden_c', $id_orden_comp)) !== null) {
    echo "<tr data-error='1'><td colspan='7' class='text-danger text-center'>" . h($error) . "</td></tr>";
    exit;
}

$s = $mysqli->prepare("SELECT v.cod_proveedor, v.razon_social, v.ruc FROM orden_compra o JOIN proveedor v ON v.cod_proveedor = o.cod_proveedor
                        WHERE o.id_orden_comp = ? LIMIT 1");
$s->bind_param('i', $id_orden_comp);
$s->execute();
$prov = $s->get_result()->fetch_assoc();
$s->close();

$items = flujo_detalle_orden($id_orden_comp);
if (!$prov || !$items) {
    echo "<tr data-error='1'><td colspan='7' class='text-center'>No hay productos en esta orden de compra.</td></tr>";
    exit;
}

$productos_json = [];
foreach ($items as $row) {
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
    <tr id="fila_<?= (int) $row['cod_producto'] ?>" data-cod="<?= (int) $row['cod_producto'] ?>"
        data-orden="<?= $id_orden_comp ?>" data-tipo="orden">
        <td><?= $id_orden_comp ?></td>
        <td><?= h($row['p_descrip']) ?></td>
        <td class="cantidad" data-valor="<?= $cantidad ?>"><?= number_format($cantidad, 2, '.', '') ?></td>
        <td class="precio_unit" data-valor="<?= $precio_unit ?>"><?= number_format($precio_unit, 2, '.', '') ?></td>
        <td><?= h($row['tipo_impuesto']) ?></td>
        <td class="subtotal" data-valor="<?= $subtotal ?>"><?= number_format($subtotal, 2, '.', '') ?></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarOrden(<?= $id_orden_comp ?>)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
<?php } ?>

<script>
    var datosProveedor = {
        cod_proveedor: <?= json_encode($prov['cod_proveedor']) ?>,
        razon_social: <?= json_encode($prov['razon_social']) ?>,
        ruc: <?= json_encode($prov['ruc']) ?>,
        productos: <?= json_encode($productos_json) ?>
    };

    function resetDatosProveedor() {
        datosProveedor.productos = [];
    }
</script>
