<?php
session_start();

require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('pedido_v');   // control de acceso por rol (config/permisos.php)
require_once '../../config/database.php';

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=alert=3'>";
} else {
    if ($_GET['act'] == 'insert') {
        if (isset($_POST['Guardar'])) {
            $codigo = $_POST['codigo'];
            $codigo_deposito = $_POST['codigo_deposito'];
            //Insertar detalle de pedido de venta

            $sql = mysqli_query($mysqli, "SELECT * FROM producto, tmp WHERE producto.cod_producto = tmp.id_producto");
            while ($row = mysqli_fetch_array($sql)) {
                $codigo_producto = $row['id_producto'];
                $cantidad = $row['cantidad_tmp'];
                $insert_detalle = mysqli_query($mysqli, "INSERT INTO det_pedido_v (id_pedido_v,cod_producto,  cod_deposito,  cantidad) VALUES ( $codigo, $codigo_producto, $codigo_deposito, $cantidad)") or die('Error: ' . mysqli_error($mysqli));
            }
            //Insertar cabecera de compra 
            //Definir valores
            $fecha = $_POST['fecha'];
            $hora = $_POST['hora'];
            $estado = 'pendiente';
            $usuario = $_SESSION['id_user'];
            //Insertar
            $query = mysqli_query($mysqli, "INSERT INTO pedido_v (id_pedido_v, fecha_pedido,  hora ,estado, id_user) VALUES ($codigo, '$fecha', '$hora', '$estado', $usuario)")
                or die("Error" . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=pedido_v&alert=1");
            } else {
                header("Location: ../../main.php?module=pedido_v&alert=3");
            }
        }
    } elseif ($_GET['act'] == 'anular') {
        if (isset($_GET['id_pedido'])) {
            $codigo = $_GET['id_pedido'];
            //Anular cabecera de pedido (cambiar estado a rechazado)
            $query = mysqli_query($mysqli, "UPDATE pedido_v SET estado = 'rechazado' WHERE id_pedido_v= $codigo")
                or die("Error: " . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=pedido_v&alert=2");
            } else {
                header("Location: ../../main.php?module=pedido_v&alert=3");
            }
        }
    } elseif ($_GET['act'] == 'aprobar') {
        if (isset($_GET['id_pedido'])) {
            $codigo = $_GET['id_pedido'];

            // Verificar si el estado no es 'anulado'
            $result = mysqli_query($mysqli, "SELECT estado FROM pedido_v WHERE id_pedido_v = $codigo");
            $row = mysqli_fetch_assoc($result);

            if ($row['estado'] === 'rechazado') {
                header("Location: ../../main.php?module=pedido_v&alert=5");
            } else {
                $query = mysqli_query($mysqli, "UPDATE pedido_v SET estado = 'aprobado' WHERE id_pedido_v = $codigo")
                    or die("Error: " . mysqli_error($mysqli));

                if ($query) {
                    header("Location: ../../main.php?module=pedido_v&alert=4");
                } else {
                    header("Location: ../../main.php?module=pedido_v&alert=3");
                }
            }
        }
    }

}

?>