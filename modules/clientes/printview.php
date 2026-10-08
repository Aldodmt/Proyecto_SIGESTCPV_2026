<?php
require_once "../../config/database.php";

$query = mysqli_query($mysqli, "SELECT *FROM v_clientes")
    or die("Error: " . mysqli_error($mysqli));

$count = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de clientes</title>
    <link rel="stylesheet" type="txt/css" href="../../images/favicon.ico">
</head>

<body>
    <div align="center">
        <img src="../../images/asuncion.jpg" style="width:200px; height:100px;">
    </div>
    <div>
        Reporte de ciudad
    </div>
    <div align="center">
        Cantidad: <?php echo $count; ?>
    </div>
    <hr>
    <div id="tabla">
        <table width="100%" border="0.3" cellpadding="0" cellspacing="0" align="center">
            <thead style="background:#e8ecee">
                <tr class="table-title">
                    <th height="20" align="center" valing="middle"><small>Ci o Ruc</small></th>
                    <th height="20" align="center" valing="middle"><small>Dpto.</small></th>
                    <th height="20" align="center" valing="middle"><small>Ciudad</small></th>
                    <th height="20" align="center" valing="middle"><small>Nombre</small></th>
                    <th height="20" align="center" valing="middle"><small>Apellido</small></th>
                    <th height="20" align="center" valing="middle"><small>Direccion</small></th>
                    <th height="20" align="center" valing="middle"><small>Telefono</small></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($data = mysqli_fetch_assoc($query)) {
                    $ci_ruc = $data['ci_ruc'];
                    $dep_descripcion = $data['dep_descripcion'];
                    $descrip_ciudad = $data['descrip_ciudad'];
                    $cli_nombre = $data['cli_nombre'];
                    $cli_apellido = $data['cli_apellido'];
                    $cli_direccion = $data['cli_direccion'];
                    $cli_telefono = $data['cli_telefono'];

                    echo "<tr>
                    <td width='100' align='left'>$ci_ruc</td>
                    <td width='100' align='left'>$dep_descripcion</td>
                    <td width='100' align='left'>$descrip_ciudad</td>   
                    <td width='100' align='left'>$cli_nombre</td>
                    <td width='100' align='left'>$cli_apellido</td>
                    <td width='100' align='left'>$cli_direccion</td>
                    <td width='100' align='left'>$cli_telefono</td>
                    
                    </tr>";
                }
                ?>

            </tbody>
        </table>
    </div>
</body>

</html>