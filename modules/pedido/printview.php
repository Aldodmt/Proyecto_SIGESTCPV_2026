<?php
require_once "../../config/database.php";
if ($_GET['act'] == 'imprimir') {
    if (isset($_GET['id_pedido'])) {
        $codigo = $_GET['id_pedido'];
        //Cabecera de compra
        $cabecera_compra = mysqli_query($mysqli, "SELECT * FROM v_pedido WHERE id_pedido = $codigo")
            or die('Error' . mysqli_error($mysqli));

        while ($data = mysqli_fetch_assoc($cabecera_compra)) {
            $cod = $data['id_pedido'];
            $fecha = $data['fecha'];
            $hora = $data['hora'];
            $usuario = $data['name_user'];
        }
        //Detalle de compra
        $detalle_compra = mysqli_query($mysqli, "SELECT * FROM v_pedido WHERE id_pedido = $codigo")
            or die("Error" . mysqli_error($mysqli));

    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pedido de compra</title>
</head>

<body>
    <div align="center">
        <img src="../../images/asuncion.jpg" style="width:200px; height:100px;">
    </div>
    <div align="center">
        <strong>Registro de pedido de compra</strong> <br>
        <label><strong>ID pedido: </strong><?php echo $cod; ?></label><br>
        <label><strong>usuario: </strong><?php echo $usuario; ?></label><br>
        <label><strong>Fecha: </strong><?php echo $fecha; ?></label><br>
        <label><strong>Hora: </strong><?php echo $hora; ?></label><br>
    </div>
    <hr>
    <div align="center">
        <table width="100%" border="0.3" cellpadding="0" cellspacing="0" align="center">
            <thead style="background:#e8ecee">
                <tr class="tabla-title">
                    <th height="20" align="center" valign="middle"><small>Tipo de producto</small></th>
                    <th height="20" align="center" valign="middle"><small>Unidad de Medida</small></th>
                    <th height="20" align="center" valign="middle"><small>Producto</small></th>
                    <th height="20" align="center" valign="middle"><small>Cantidad</small></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($data2 = mysqli_fetch_assoc($detalle_compra)) {
                    $tp = $data2['t_p_descrip'];
                    $u = $data2['u_descrip'];
                    $p_descrip = $data2['p_descrip'];
                    $cantidad = $data2['cantidad'];

                    echo "<tr>
                                  <td width='100' align='left'>$tp</td>
                                  <td width='100' align='left'>$u</td>
                                  <td width='150' align='left'>$p_descrip</td>
                                  <td width='100' align='left'>$cantidad</td>
                                  </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <hr>
</body>

</html>