<?php
require_once "../../config/database.php";

$query = mysqli_query($mysqli, "SELECT *FROM proveedor") or die('error'.mysqli_error($mysqli));
$count = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de proveedores</title>
    <link rel="shortcut icon" href="../../images/favicon.ico">
</head>
<body>
    <div>
        <h1 text-align="center">Reporte de proveedores</h1>
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
                    <th height="30" text-align="center" valing="middle"><small>Razon Social</small></th>
                    <th height="30" text-align="center" valing="middle"><small>RUC</small></th>
                    <th height="30" text-align="center" valing="middle"><small>Dirección</small></th>
                    <th height="30" text-align="center" valing="middle"><small>Teléfono</small></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    while ($data = mysqli_fetch_assoc($query)) {
                        $codigo = $data['cod_proveedor'];
                        $razon_social = $data['razon_social'];
                        $ruc = $data['ruc'];
                        $direccion = $data['direccion'];
                        $telefono = $data['telefono'];

                        echo "<tr>
                        <td width='100' text-align='left'>$codigo</td>
                        <td width='150' text-align='left'>$razon_social</td>
                        <td width='100' text-align='left'>$ruc</td>
                        <td width='150' text-align='left'>$direccion</td>
                        <td width='80' text-align='left'>$telefono</td>
                        </tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>