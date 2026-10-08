<?php
require_once '../config/database.php';

$action = $_REQUEST['action'] ?? '';
$x = mysqli_real_escape_string($mysqli, $_REQUEST['x'] ?? '');

$aColumns = ['id_presupuesto', 'razon_social', 'fecha_presu', 'fecha_vencimiento', 'p_descrip', 'cantidad', 'precio_unit', 'estado'];
$sTable = "v_presu";

// Solo pedidos aprobados que no tengan presupuesto
$sWhere = "WHERE UPPER(estado) = 'APROBADO' AND id_presupuesto NOT IN (SELECT id_presupuesto FROM orden_compra WHERE id_presupuesto IS NOT NULL)";
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
                    <th>Codigo presupuesto</th>
                    <th>Proveedor</th>
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
                        <td><?= $row['id_presupuesto'] ?></td>
                        <td><?= $row['razon_social'] ?></td>
                        <td><?= $row['p_descrip'] ?></td>
                        <td><?= $row['cantidad'] ?></td>
                        <td><?= $row['precio_unit'] ?></td>
                        <td><?= $row['estado'] ?></td>
                        <td>
                            <button class="btn btn-success btn-sm"
                                onclick="seleccionarPresupuesto(<?= $row['id_presupuesto'] ?>)">
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