<?php
session_start();
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('ventas');   // control de acceso por rol (config/permisos.php)
require_once '../../config/database.php';

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=alert=3'>";
}

// Validación del timbrado
$hoy = date('Y-m-d');
$sql_vencimiento = $mysqli->prepare("SELECT * FROM timbrado WHERE fecha_fin = ?");
$sql_vencimiento->bind_param("s", $hoy);
$sql_vencimiento->execute();
$result_vencimiento = $sql_vencimiento->get_result();

if ($result_vencimiento->num_rows > 0) {
    echo "<script>alert('El timbrado ya ha vencido. Por favor, renueve el timbrado antes de continuar.'); window.history.back();</script>";
    exit;
}
if ($_GET['act'] == 'insert') {
    if ($_GET['act'] == 'insert') {
        if (isset($_POST['Guardar'])) {
            $codigo = $_POST['codigo'];
            $codigo_deposito = $_POST['codigo_deposito'];
            $id_cli = $_POST['codigo_cliente'];
            $fecha = $_POST['fecha'];
            $hora = $_POST['hora'];
            $nro_factura = $_POST['nro_factura'];
            $usuario = $_SESSION['id_user'];
            $estado = 'activo';
            $codigo_timb = $_POST['codigo_tim'];

            $sql_fact = $mysqli->prepare("SELECT rango_fin FROM timbrado where id_timbrado = ?");
            $sql_fact->bind_param("i", $codigo_timb);
            $sql_fact->execute();
            $result_fact = $sql_fact->get_result();
            $datos = mysqli_fetch_array($result_fact);
            $rango_fin = $datos['rango_fin'];

            if ($nro_factura >= $rango_fin) {
                header("Location: ../../main.php?module=compras&alert=4");
                exit;
            }

            // Validar productos en carrito temporal
            $query_tmp = $mysqli->query("SELECT * FROM tmp_venta");
            while ($item = $query_tmp->fetch_assoc()) {
                $codigo_producto = $item['cod_producto'];
                $cantidad = $item['cantidad_tmp'];
                $precio = $item['precio_tmp'];

                // Verificar si el producto está en el depósito
                $stmt_existe = $mysqli->prepare("SELECT cantidad FROM stock WHERE cod_producto = ? AND cod_deposito = ?");
                $stmt_existe->bind_param("ii", $codigo_producto, $codigo_deposito);
                $stmt_existe->execute();
                $result_existe = $stmt_existe->get_result();
                $stock_row = $result_existe->fetch_assoc();

                if (!$stock_row) {
                    // El producto no está en el depósito seleccionado
                    header("Location: ../../main.php?module=ventas&alert=5");
                    exit();
                }

                $stock_disponible = $stock_row['cantidad'];

                // Verificar si la cantidad supera el stock disponible
                if ($cantidad > $stock_disponible) {
                    $diferencia = $cantidad - $stock_disponible;
                    header("Location: ../../main.php?module=ventas&alert=4&diff=$diferencia");
                    exit();
                }

                // Insertar en detalle de venta
                $stmt_detalle = $mysqli->prepare("INSERT INTO det_venta (cod_producto, cod_venta, cod_deposito, det_precio_unit, det_cantidad) VALUES (?, ?, ?, ?, ?)");
                $stmt_detalle->bind_param("iiidi", $codigo_producto, $codigo, $codigo_deposito, $precio, $cantidad);
                $stmt_detalle->execute();
                $stmt_detalle->close();

                // Actualizar stock
                $stmt_update_stock = $mysqli->prepare("UPDATE stock SET cantidad = cantidad - ? WHERE cod_producto = ? AND cod_deposito = ?");
                $stmt_update_stock->bind_param("iii", $cantidad, $codigo_producto, $codigo_deposito);
                $stmt_update_stock->execute();
                $stmt_update_stock->close();
            }

            //Actualizar el numero de timbrado en timbrado

            $stmt_timbrado = $mysqli->prepare("UPDATE timbrado 
                SET rango_inicio = ?
                WHERE id_timbrado = ?");
            $stmt_timbrado->bind_param("ii", $nro_factura, $codigo_tim);
            $stmt_timbrado->execute();
            $stmt_timbrado->close();

            // Insertar en tabla `venta`
            $stmt_venta = $mysqli->prepare("INSERT INTO venta (cod_venta, id_cliente, id_user, fecha, estado, hora, nro_factura, id_timbrado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_venta->bind_param("iiisssii", $codigo, $id_cli, $usuario, $fecha, $estado, $hora, $nro_factura, $codigo_timb);
            if ($stmt_venta->execute()) {
                header("Location: ../../main.php?module=ventas&alert=1");
            } else {
                header("Location: ../../main.php?module=ventas&alert=3");
            }
            $stmt_venta->close();
        }
    }
} elseif ($_GET['act'] == 'anular') {

    if (isset($_GET['cod_venta'])) {
        $codigo = $_GET['cod_venta'];

        // Anular cabecera de compra (cambiar estado a anulado)
        $stmt7 = $mysqli->prepare("UPDATE venta SET estado = 'anulado' WHERE cod_venta = ?");
        $stmt7->bind_param("i", $codigo);
        $stmt7->execute();
        $stmt7->close();

        // Consultar detalle de compra
        $stmt8 = $mysqli->prepare("SELECT * FROM det_venta WHERE cod_venta = ?");
        $stmt8->bind_param("i", $codigo);
        $stmt8->execute();
        $result2 = $stmt8->get_result();
        while ($row = $result2->fetch_assoc()) {
            $codigo_producto = $row['cod_producto'];
            $codigo_deposito = $row['cod_deposito'];
            $cantidad = $row['det_cantidad'];

            // Actualizar stock (restando la cantidad)
            $stmt9 = $mysqli->prepare("UPDATE stock SET cantidad = cantidad + ? WHERE cod_producto = ? AND cod_deposito = ?");
            $stmt9->bind_param("iii", $cantidad, $codigo_producto, $codigo_deposito);
            $stmt9->execute();
            $stmt9->close();
        }

        if ($stmt7) {
            header("Location: ../../main.php?module=ventas&alert=2");
        } else {
            header("Location: ../../main.php?module=ventas&alert=3");
        }
    }
}
?>