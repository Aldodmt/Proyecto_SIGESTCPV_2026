<?php
require_once '../config/database.php';
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';
if ($action == 'ajax') {
    $x = mysqli_real_escape_string($mysqli, (strip_tags($_REQUEST['x'], ENT_QUOTES)));
    $aColumns = array('cod_producto', 'cod_tipo_prod', 'id_u_medida', 'p_descrip', 'tipo_impuesto');
    $sTable = "v_producto";
    $sWhere = "";
    if ($_GET['x'] != "") {
        $sWhere = "WHERE (";
        for ($i = 0; $i < count($aColumns); $i++) {
            $sWhere .= $aColumns[$i] . " LIKE  '%" . $x . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);
        $sWhere .= ')';
    }
    // Paginación
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
                        <th>Tipo de Impuesto</th>
                        <th>Stock Disponible</th>
                        <th class="text-end">Cantidad</th>
                        <th style="width:36px;">Seleccion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($query)) {
                        $id_producto = $row['cod_producto'];
                        $tipo_impuesto = $row['tipo_impuesto'];
                        $stock = mysqli_query($mysqli, "
                            SELECT cantidad from stock_prod where cod_producto = $id_producto
                        ");
                        $cant = mysqli_fetch_assoc($stock);
                        $descirp_producto = $row['p_descrip'];
                        $stock_existente = $cant['cantidad'] ?? 0; // Obtener stock existente
            
                        $codigo_tproducto = $row['cod_tipo_prod'];
                        $sql_tproducto = mysqli_query($mysqli, "SELECT t_p_descrip FROM tipo_producto WHERE cod_tipo_prod='$codigo_tproducto'");
                        $rw_tproducto = mysqli_fetch_assoc($sql_tproducto);
                        $tproducto_nombre = $rw_tproducto['t_p_descrip'];

                        $id_u_medida = $row['id_u_medida'];
                        $sql_umedida = mysqli_query($mysqli, "SELECT u_descrip FROM u_medida WHERE id_u_medida='$id_u_medida'");
                        $rw_u_medida = mysqli_fetch_assoc($sql_umedida);
                        $u_medida_nombre = $rw_u_medida['u_descrip']; ?>
                        <tr>
                            <td><?php echo $id_producto; ?></td>
                            <td><?php echo $tproducto_nombre; ?></td>
                            <td><?php echo $u_medida_nombre; ?></td>
                            <td><?php echo $descirp_producto; ?></td>
                            <td><?php echo $tipo_impuesto; ?></td>
                            <td><?php echo $stock_existente; ?></td> <!-- Mostrar stock -->
                            <td>
                                <div class="input-group">
                                    <input type="text" class="form-control text-end" id="cantidad_<?php echo $id_producto; ?>"
                                        value="1">
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm" onclick="agregar('<?php echo $id_producto; ?>')">
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