<?php
require_once "../../config/database.php";

$query = mysqli_query($mysqli, "SELECT cod_ciudad, descrip_ciudad,
 dep.id_departamento, dep.dep_descripcion 
FROM ciudad ciu
JOIN departamento dep 
WHERE ciu.id_departamento = dep.id_departamento")
    or die('Error' . mysqli_error($mysqli));
$count = mysqli_num_rows($query);

?>
<!DOCTYPE HTML>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Ciudades</title>
    <link rel="stylesheet" type="text/css" href="../../images/favicon.ico">
</head>

<body>
    <div align="center">
        <img src="../../images/asuncion.jpg" style="width:200px; height:100px;">
    </div>
    <div>
        Reporte de Ciudad
    </div>
    <div align="center">
        Cantidad: <?php echo $count; ?>
    </div>
    <hr>
    <div>
        <table width="100%" border="0.3" cellpading="0" cellspace="0" align="center">
            <thead style="background: #e8ecee">
                <tr class="table-title">
                    <th height="10" align="center" valing="middle"><small>Codigo</small></th>
                    <th height="50" align="center" valing="middle"><small>Departamento</small></th>
                    <th height="50" align="center" valing="middle"><small>Ciudad</small></th>
                </tr>

            </thead>
            <tbody>
                <?php
                while ($data = mysqli_fetch_assoc($query)) {
                    $codigo = $data['cod_ciudad'];
                    $dep_descripcion = $data['dep_descripcion'];
                    $descrip_ciudad = $data['descrip_ciudad'];

                    echo "<tr>
                <td width='100' align='left'>$codigo</td>
                <td width='150' align='left'>$dep_descripcion</td>
                <td width='150' align='left'>$descrip_ciudad</td>
                </tr>";

                }
                ?>

            </tbody>
        </table>
    </div>
</body>

</html>