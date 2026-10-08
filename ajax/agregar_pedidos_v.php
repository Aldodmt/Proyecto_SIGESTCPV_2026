<?php
session_start();
$session_id = session_id();
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}

if (isset($_POST['cantidad_'])) {
    $cantidad_tmp = $_POST['cantidad_'];
}

if (isset($_POST['precio_'])) {
    $precio_tmp = $_POST['precio_'];
}


require_once '../config/database.php';

if (!empty($id)) {
    $insert_tmp = mysqli_query($mysqli, "INSERT INTO tmp_venta (cod_producto, cantidad_tmp, precio_tmp, session_id) VALUES ('$id', $cantidad_tmp, $precio_tmp, '$session_id')");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete = mysqli_query($mysqli, "DELETE FROM tmp_venta WHERE id_tmp = '" . $id . "'");
}

?>
<table class="table table-striped table-hover align-middle">
    <tr>
        <th>Codigo</th>
        <th>Tipo de prod.</th>
        <th>Unid. de Medida</th>
        <th>Producto</th>
        <th class="text-end">Cantidad</th>
        <th class="text-end">Precio</th>
        <th class="text-end">Subtotal</th>
        <th class="text-center" style="width: 36px;">Eliminar</th>
    </tr>
    <?php
    $total = 0;
    $sql = mysqli_query($mysqli, "SELECT * FROM v_producto, tmp_venta WHERE v_producto.cod_producto = tmp_venta.cod_producto and tmp_venta.session_id = '" . $session_id . "'");
    while ($row = mysqli_fetch_array($sql)) {
        $id_tmp = $row['id_tmp'];
        $cod_prod = $row['cod_producto'];
        $cod_tp = $row['cod_tipo_prod'];
        $tp_descrip = $row['t_p_descrip'];
        $cod_u = $row['id_u_medida'];
        $u_descrip = $row['u_descrip'];
        $p_descrip = $row['p_descrip'];
        $cantidad = $row['cantidad_tmp'];

        $precio_venta_ = $row['precio_tmp'];
        $precio_venta_f = number_format($precio_venta_); //Formatear una variable (Poner ,)
        $precio_venta_r = str_replace(",", "", $precio_venta_f); //Reemplazar la coma 
        $precio_total = $precio_venta_r * $cantidad;
        $precio_total_f = number_format($precio_total);
        $precio_total_r = str_replace(",", "", $precio_total_f);

        $total += $precio_total_r; //Sumador total  
        ?>
        <tr>
            <td><?php echo $cod_prod; ?></td>
            <td><?php echo $tp_descrip; ?></td>
            <td><?php echo $u_descrip; ?></td>
            <td><?php echo $p_descrip; ?></td>
            <td><?php echo $cantidad; ?></td>
            <td><?php echo $precio_venta_; ?></td>
            <td><?php echo $precio_total_f; ?></td>
            <td class="text-center">
                <button class="btn btn-danger btn-sm" onclick="eliminar(<?php echo $id_tmp; ?>)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <input type="hidden" class="form-control" name="suma_total" value="<?php echo $total; ?>">
        <?php if (empty($codigo_producto)) {
            $codigo_producto = 0;
        } else {
            $codigo_producto;
        } ?>
        <input type="hidden" class="form-control" name="codigo_producto" value="<?php echo $codigo_producto; ?>">
        <?php if (empty($cantidad)) {
            $cantidad = 0;
        } else {
            $cantidad;
        } ?>
        <input type="hidden" class="form-control" name="cantidad" value="<?php echo $cantidad; ?>">
        <td colspan=4><span class="float-end">Total Gs.</span></td>
        <td><strong><span class="float-end"><?php echo number_format($total); ?></span></strong></td>
    </tr>
</table>