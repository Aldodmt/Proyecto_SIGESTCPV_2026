<?php
require_once "../../config/database.php";
if ($_GET['act'] == 'imprimir') {
    if (isset($_GET['id_orden'])) {

        $codigo = $_GET['id_orden'];
        // Cabecera de compra
        $cabecera_compra = mysqli_query($mysqli, "SELECT * FROM v_orden_comp WHERE id_orden_comp = $codigo")
            or die('Error' . mysqli_error($mysqli));

        while ($data = mysqli_fetch_assoc($cabecera_compra)) {
            $cod = $data['id_orden_comp'];
            $cod_p = $data['id_presupuesto'];
            $fecha = $data['fecha'];
            $hora = $data['hora'];
            $usuario = $data['name_user'];
        }
        // Detalle de compra
        $detalle_compra = mysqli_query($mysqli, "SELECT * FROM v_orden_comp WHERE id_orden_comp=$codigo")
            or die("Error" . mysqli_error($mysqli));
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header img {
            max-width: 150px;
            height: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .details {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .details strong {
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-left: 50px;
        }

        table thead {
            background-color: #f4f4f4;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            font-weight: bold;
            text-align: center;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="../../images/asuncion.jpg" alt="Logo">
        </div>
        <div class="details">
            <h2>Registro de orden de compra</h2>
            <p><strong>ID orden de compra:</strong> <?php echo $cod; ?></p>
            <p><strong>ID presupuesto:</strong> <?php echo $cod_p; ?></p>
            <p><strong>Fecha:</strong> <?php echo $fecha; ?></p>
            <p><strong>Hora:</strong> <?php echo $hora; ?></p>
            <p><strong>Usuario:</strong> <?php echo $usuario; ?></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Tipo de producto</th>
                    <th>Unidad de Medida</th>
                    <th>Producto</th>
                    <th>Cantidad aprobada</th>
                    <th>Precio unitario</th>
                </tr>
            </thead>
            <tbody>
                <?php

                while ($data2 = mysqli_fetch_assoc($detalle_compra)) {
                    $tp = $data2['t_p_descrip'];
                    $u = $data2['u_descrip'];
                    $p_descrip = $data2['p_descrip'];
                    $cantidad = $data2['cantidad'];
                    $precio = $data2['precio_unit'];
                    echo "<tr>
                            <td>$tp</td>
                            <td>$u</td>
                            <td>$p_descrip</td>
                            <td style='text-align: center;'>$cantidad</td>
                            <td style='text-align: center;'>$precio</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>