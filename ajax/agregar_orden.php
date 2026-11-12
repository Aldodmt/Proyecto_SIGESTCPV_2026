<?php
require_once __DIR__ . '/../config/permisos.php';
guard_endpoint('orden_c', 'cualquiera', 'fragmento');   // 1.ª capa: permiso del rol (config/permisos.php)
require_once '../config/database.php';
require_once __DIR__ . '/../config/compras.php';

// Devuelve las filas del detalle de un PRESUPUESTO para armar la orden de compra.
if (!isset($_POST['id_presupuesto']))
    exit;

$id_presupuesto = intval($_POST['id_presupuesto']);

// 2.ª capa: solo se puede ordenar un presupuesto APROBADO que no tenga ya una orden vigente.
if (($error = flujo_validar_origen('presupuesto', $id_presupuesto)) !== null) {
    echo "<tr data-error='1'><td colspan='7' class='text-danger text-center'>" . h($error) . "</td></tr>";
    exit;
}

$s = $mysqli->prepare("SELECT v.razon_social FROM presupuesto p JOIN proveedor v ON v.cod_proveedor = p.cod_proveedor WHERE p.id_presupuesto = ? LIMIT 1");
$s->bind_param('i', $id_presupuesto);
$s->execute();
$razon = (string) ($s->get_result()->fetch_row()[0] ?? '');
$s->close();

$items = flujo_detalle_presupuesto($id_presupuesto);
if (!$items) {
    echo "<tr data-error='1'><td colspan='7' class='text-center'>No hay productos en este presupuesto.</td></tr>";
    exit;
}
foreach ($items as $row) {
    $subtotal = $row['cantidad'] * $row['precio_unit']; ?>
    <tr id="fila_<?= (int) $row['cod_producto'] ?>" data-cod="<?= (int) $row['cod_producto'] ?>" data-presu="<?= $id_presupuesto ?>"
        data-tipo="presupuesto">
        <td><?= $id_presupuesto ?></td>
        <td><?= h($razon) ?></td>
        <td><?= h($row['p_descrip']) ?></td>
        <td class="cantidad" data-valor="<?= h($row['cantidad']) ?>"><?= number_format($row['cantidad'], 2) ?></td>
        <td class="precio_unit" data-valor="<?= h($row['precio_unit']) ?>"><?= number_format($row['precio_unit'], 2) ?></td>
        <td class="subtotal" data-valor="<?= $subtotal ?>"><?= number_format($subtotal, 2) ?></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPresupuesto('<?= $id_presupuesto ?>')">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
<?php }
