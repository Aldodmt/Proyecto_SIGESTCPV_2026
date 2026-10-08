<?php
require_once '../config/database.php';

$action = $_REQUEST['action'] ?? '';
$x = mysqli_real_escape_string($mysqli, $_REQUEST['x'] ?? '');

$aColumns = array('id_orden_comp', 'id_presupuesto', 'fecha', 'hora', 'p_descrip', 'cantidad', 'precio_unit', 'estado');
$sTable = "v_orden_comp";
$sWhere = "WHERE estado = 'APROBADO' AND id_orden_comp NOT IN (SELECT id_orden_comp FROM compra WHERE id_orden_comp IS NOT NULL)"; // Filtro por defecto para estado aprobado
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
                    <th>Codigo orden</th>
                    <th>Codigo Presupuesto</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Estado</th>
                    <th>Seleccion</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $row['id_orden_comp'] ?></td>
                        <td><?= $row['id_presupuesto'] ?></td>
                        <td><?= $row['p_descrip'] ?></td>
                        <td><?= $row['cantidad'] ?></td>
                        <td><?= $row['precio_unit'] ?></td>
                        <td><?= $row['estado'] ?></td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="seleccionarOrden(<?= $row['id_orden_comp'] ?>)">
                                <i class="bi bi-plus-lg"></i>
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