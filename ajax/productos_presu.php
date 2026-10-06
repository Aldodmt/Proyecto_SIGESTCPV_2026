<?php
require_once '../config/database.php';

$action = $_REQUEST['action'] ?? '';
$x = mysqli_real_escape_string($mysqli, $_REQUEST['x'] ?? '');

$aColumns = ['id_pedido', 'name_user', 'fecha', 'hora', 'p_descrip', 'cantidad', 'estado'];
$sTable = "v_pedido";

// Solo pedidos confirmados que no tengan presupuesto
$sWhere = "WHERE UPPER(estado) = 'CONFIRMADO' AND id_pedido NOT IN (SELECT id_pedido FROM presupuesto where id_pedido is not null)";
if (!empty($x)) {
    $sWhere .= " AND (";
    foreach ($aColumns as $col) {
        $sWhere .= "$col LIKE '%$x%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ")";
}

// Paginación
include 'paginacion.php';
$page = $_REQUEST['page'] ?? 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

$count_query = mysqli_query($mysqli, "SELECT COUNT(*) AS numeros FROM $sTable $sWhere");
$row = mysqli_fetch_assoc($count_query);
$numeros = $row['numeros'];
$total_pages = ceil($numeros / $per_page);
$reload = './index.php';

$sql = "SELECT * FROM $sTable $sWhere LIMIT $offset, $per_page";
$query = mysqli_query($mysqli, $sql);

if ($numeros > 0) { ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Codigo</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Estado</th>
                    <th>Seleccion</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $row['id_pedido'] ?></td>
                        <td><?= $row['name_user'] ?></td>
                        <td><?= $row['fecha'] ?></td>
                        <td><?= $row['hora'] ?></td>
                        <td><?= $row['p_descrip'] ?></td>
                        <td><?= $row['cantidad'] ?></td>
                        <td><?= $row['estado'] ?></td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="seleccionarPedido(<?= $row['id_pedido'] ?>)">
                                <i class="cil-plus"></i>
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <nav aria-label="Page navigation">
            <?= paginate($reload, $page, $total_pages, 4) ?>
        </nav>
    </div>
<?php }
?>