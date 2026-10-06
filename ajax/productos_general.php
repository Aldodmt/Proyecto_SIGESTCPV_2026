<?php
require_once '../config/database.php';

$action = $_REQUEST['action'] ?? '';

if ($action == 'ajax') {
    $x = mysqli_real_escape_string($mysqli, strip_tags($_REQUEST['x'], ENT_QUOTES));
    $aColumns = ['cod_producto', 'cod_tipo_prod', 'id_u_medida', 'u_descrip', 'p_descrip', 'tipo_impuesto'];
    $sTable = "v_producto";
    $sWhere = "";

    if (!empty($x)) {
        $sWhere = "WHERE (";
        foreach ($aColumns as $col) {
            $sWhere .= "$col LIKE '%$x%' OR ";
        }
        $sWhere = substr($sWhere, 0, -4) . ")";
    }

    // Paginación
    include 'paginacion.php';
    $page = $_REQUEST['page'] ?? 1;
    $per_page = 5;
    $adjacents = 4;
    $offset = ($page - 1) * $per_page;

    $count_query = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM $sTable $sWhere");
    $total = mysqli_fetch_assoc($count_query)['total'];
    $total_pages = ceil($total / $per_page);
    $reload = './index.php';

    $query = mysqli_query($mysqli, "SELECT * FROM $sTable $sWhere LIMIT $offset, $per_page");

    if ($total > 0) { ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Código</th>
                        <th>Tipo Producto</th>
                        <th>Unidad Medida</th>
                        <th>Descripción</th>
                        <th>Tipo Impuesto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                        <tr data-cod="<?= $row['cod_producto'] ?>" data-impuesto="<?= $row['tipo_impuesto'] ?>"
                            data-tipo="sin_pedido">
                            <td><?= $row['cod_producto'] ?></td>
                            <td><?= $row['cod_tipo_prod'] ?></td>
                            <td><?= $row['id_u_medida'] ?></td>
                            <td><?= htmlspecialchars($row['p_descrip'], ENT_QUOTES) ?></td>
                            <td><?= $row['tipo_impuesto'] ?></td>
                            <td>
                                <input type="number" class="form-control cantidad_input" value="1" min="1">
                            </td>
                            <td>
                                <input type="number" class="form-control precio_input" value="0" min="0">
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm agregar_producto">
                                    <i class="cil-plus"></i>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <nav aria-label="Page navigation">
                                <?= paginate($reload, $page, $total_pages, $adjacents); ?>
                            </nav>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php }
}
?>