<?php
require_once __DIR__ . '/../config/permisos.php';
guard_endpoint('presupuesto', 'cualquiera', 'fragmento');   // 1.ª capa: permiso del rol (config/permisos.php)
require_once '../config/database.php';
require_once __DIR__ . '/../config/compras.php';

// Devuelve las filas del detalle de un PEDIDO para armar el presupuesto (el formulario las inserta en su tabla).
if (!isset($_POST['id_pedido']))
    exit;

$id_pedido = intval($_POST['id_pedido']);

// 2.ª capa: solo se puede presupuestar un pedido CONFIRMADO que no tenga ya un presupuesto vigente.
if (($error = flujo_validar_origen('pedido', $id_pedido)) !== null) {
    echo "<tr data-error='1'><td colspan='6' class='text-danger text-center'>" . h($error) . "</td></tr>";
    exit;
}

$items = flujo_detalle_pedido($id_pedido);
if (!$items) {
    echo "<tr data-error='1'><td colspan='6' class='text-center'>No hay productos en este pedido.</td></tr>";
    exit;
}
foreach ($items as $row) { ?>
    <tr id="fila_<?= (int) $row['cod_producto'] ?>" data-cod="<?= (int) $row['cod_producto'] ?>" data-pedido="<?= $id_pedido ?>"
        data-tipo="pedido">
        <td><?= $id_pedido ?></td>
        <td><?= h($row['p_descrip']) ?></td>
        <td class="cantidad"><?= h($row['cantidad']) ?></td>
        <td>
            <input type="number" class="form-control precio_unit" min="0">
        </td>
        <td class="subtotal">0</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPedido('<?= $id_pedido ?>')">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
<?php } ?>
