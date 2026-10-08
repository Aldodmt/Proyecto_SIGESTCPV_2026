<?php
require_once "../../config/database.php";

$query = mysqli_query($mysqli, "SELECT *FROM v_producto") or die('error'.mysqli_error($mysqli));
$count = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Productos</title>
    <link rel="shortcut icon" href="../../images/favicon.ico">
</head>
<body>
    <div>
        <h1 text-align="center">Reporte de productos</h1>
    </div>
    <div text-align="center">
        Cantidad: <?php echo $count; ?>
    </div>
    <hr>
    <div>
        <table width="100%" border="0.3" cellpadding="0" cellspacing="0" text-align="center">
            <thead style="background:#e8ecee">
                <tr class="table-title">
                    <th height="10" text-align="center" valing="middle"><small>Código</small></th>
                    <th height="30" text-align="center" valing="middle"><small>Producto</small></th>
                    <th height="30" text-align="center" valing="middle"><small>Tipo de producto</small></th>
                    <th height="30" text-align="center" valing="middle"><small>Unidad de medida</small></th>
                    <th height="30" text-align="center" valing="middle"><small>precio</small></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    while ($data = mysqli_fetch_assoc($query)) {
                        $codigo = $data['cod_producto'];
                        $p_descrip = $data['p_descrip'];
                        $t_p_descrip = $data['t_p_descrip'];
                        $u_descrip = $data['u_descrip'];
                        $precio = $data['precio'];

                        echo "<tr>
                        <td width='100' text-align='left'>$codigo</td>
                        <td width='150' text-align='left'>$p_descrip</td>
                        <td width='100' text-align='left'>$t_p_descrip</td>
                        <td width='150' text-align='left'>$u_descrip</td>
                        <td width='80' text-align='left'>$precio</td>
                        </tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>