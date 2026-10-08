<?php
require_once '../config/database.php';
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
if ($action == 'ajax') {
    $x = mysqli_real_escape_string($mysqli, (strip_tags($_REQUEST['x'], ENT_QUOTES)));
    $aColumns = array('cod_producto', 'cod_tipo_prod', 't_p_descrip', 'id_u_medida', 'u_descrip', 'p_descrip');
    $sTable = "v_producto";
    $sWhere = "";

    if (!empty($_GET['x'])) {
        $sWhere .= "AND (";
        for ($i = 0; $i < count($aColumns); $i++) {
            $sWhere .= $aColumns[$i] . " LIKE '%" . $x . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);
        $sWhere .= ")";
    }

    include 'paginacion.php';
    $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
    $per_page = 5;
    $adjacents = 4;
    $offset = ($page - 1) * $per_page;

    $count_query = mysqli_query($mysqli, "SELECT count(*) AS numeros FROM $sTable $sWhere");
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
                        <th>Tip. Producto</th>
                        <th>Unid. Medida</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio de venta</th>
                        <th style="width:36px;">Seleccion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $precio_v = 0;
                    while ($row = mysqli_fetch_assoc($query)) {
                        $cod_prod = $row['cod_producto'];
                        $cod_tp = $row['cod_tipo_prod'];
                        $tp_descrip = $row['t_p_descrip'];
                        $cod_u = $row['id_u_medida'];
                        $u_descrip = $row['u_descrip'];
                        $p_descrip = $row['p_descrip'];
                        $precio = $row['precio'];
                        $subtotal = $precio * 1.3;
                        ?>
                        <tr>
                            <td><?php echo $cod_prod; ?></td>
                            <td><?php echo $tp_descrip; ?></td>
                            <td><?php echo $u_descrip; ?></td>
                            <td><?php echo $p_descrip; ?></td>

                            <td>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end" id="cantidad_<?php echo $cod_prod; ?>"
                                        value="1">
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end" id="precio_<?php echo $cod_prod; ?>"
                                        value="<?php echo $subtotal; ?>">
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm" onclick="agregar('<?php echo $cod_prod; ?>')">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7">
                            <nav aria-label="Page navigation">
                                <?php echo paginate($reload, $page, $total_pages, $adjacents); ?>
                            </nav>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php }
}
?>