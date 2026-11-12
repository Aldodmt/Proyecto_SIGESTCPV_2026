<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/permisos.php';
guard_endpoint('pedido', 'cualquiera', 'fragmento');   // 1.ª capa: permiso del rol (config/permisos.php)
require_once "../config/database.php";
require_once __DIR__ . '/../config/compras.php';

// Validar ID de pedido
if (!isset($_POST['id_pedido']) || !is_numeric($_POST['id_pedido'])) {
    echo "<div class='alert alert-danger'>ID de pedido no válido.</div>";
    exit;
}

$id_pedido = intval($_POST['id_pedido']);
$op = $_POST['op'] ?? 'add';

// 2.ª capa: el detalle solo se toca mientras el pedido es BORRADOR o MODIFICANDO.
// Antes cualquiera podía agregar o quitar productos de un pedido ya CONFIRMADO llamando a este archivo a mano.
$error = flujo_validar_editable('pedido', $id_pedido);
if ($error !== null) {
    echo "<div class='alert alert-danger'>" . h($error) . "</div>";
    exit;
}

// Un borrador es la hoja de trabajo de su creador: nadie más puede agregarle ni quitarle productos.
$s = $mysqli->prepare("SELECT id_user, UPPER(estado) FROM pedido WHERE id_pedido = ?");
$s->bind_param('i', $id_pedido);
$s->execute();
[$duenio, $estado_actual] = $s->get_result()->fetch_row();
$s->close();
if ($estado_actual === 'BORRADOR' && (int) $duenio !== (int) ($_SESSION['id_user'] ?? 0)) {
    echo "<div class='alert alert-danger'>Este pedido es un borrador de otro usuario.</div>";
    exit;
}

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
    $s = $mysqli->prepare("SELECT cod_producto FROM producto WHERE cod_producto = ?");
    $s->bind_param('i', $id_producto);
    $s->execute();
    $existe = $s->get_result()->num_rows > 0;
    $s->close();
    if (!$existe) {
        echo "<div class='alert alert-danger'>El producto no existe.</div>";
        exit;
    }

    // Si el producto ya está en el detalle se suma la cantidad; si no, se inserta
    $s = $mysqli->prepare("SELECT cantidad FROM det_pedido WHERE id_pedido = ? AND cod_producto = ?");
    $s->bind_param('ii', $id_pedido, $id_producto);
    $s->execute();
    $ya = $s->get_result()->num_rows > 0;
    $s->close();

    if ($ya) {
        $s = $mysqli->prepare("UPDATE det_pedido SET cantidad = cantidad + ? WHERE id_pedido = ? AND cod_producto = ?");
        $s->bind_param('dii', $cantidad, $id_pedido, $id_producto);
    } else {
        $s = $mysqli->prepare("INSERT INTO det_pedido (id_pedido, cod_producto, cantidad) VALUES (?, ?, ?)");
        $s->bind_param('iid', $id_pedido, $id_producto, $cantidad);
    }
    $s->execute();
    $s->close();
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

    $s = $mysqli->prepare("DELETE FROM det_pedido WHERE id_pedido = ? AND cod_producto = ?");
    $s->bind_param('ii', $id_pedido, $id_producto);
    $s->execute();
    $s->close();
}

// Listar productos del pedido
$detalle = flujo_detalle_pedido($id_pedido);

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

foreach ($detalle as $prod) {
    echo "<tr>
        <td>" . (int) $prod['cod_producto'] . "</td>
        <td>" . h($prod['p_descrip']) . "</td>
        <td>" . h($prod['cantidad']) . "</td>
        <td>" . h($prod['u_descrip']) . "</td>
        <td>" . h($prod['tipo_impuesto']) . "</td>
        <td>
            <button class='btn btn-danger btn-sm' onclick='eliminar(" . (int) $prod['cod_producto'] . ", {$id_pedido})'>
                Eliminar
            </button>
        </td>
    </tr>";
}

echo "</tbody></table>";
