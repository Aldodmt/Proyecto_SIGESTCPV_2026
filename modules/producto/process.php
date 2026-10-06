<?php
session_start();
require_once "../../config/database.php";

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
    exit;
}

if (isset($_GET['act'])) {
    $accion = $_GET['act'];

    // -------------------- INSERT --------------------
    if ($accion == 'insert' && isset($_POST['Guardar'])) {
        $codigo = intval($_POST['codigo']);
        $producto = trim(mysqli_real_escape_string($mysqli, $_POST['p_descrip']));
        $tipo_producto = intval($_POST['tipo_producto']);
        $u_medida = intval($_POST['u_medida']);
        $tipo_impuesto = $_POST['tipo_impuesto'];

        // Validar campos obligatorios
        if ($producto == '') {
            echo "<script>alert('Ingrese un producto'); window.history.back();</script>";
            exit;
        }

        // Verificar duplicado
        $check = mysqli_query($mysqli, "SELECT 1 FROM producto WHERE LOWER(p_descrip) = LOWER('$producto')");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Ya existe un producto con esta descripción.'); window.history.back();</script>";
            exit;
        }

        // Insertar registro
        $query = mysqli_query($mysqli, "INSERT INTO producto (cod_producto, cod_tipo_prod, id_u_medida, p_descrip, tipo_impuesto) 
                                        VALUES ($codigo, $tipo_producto, $u_medida, '$producto', '$tipo_impuesto')")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=producto&alert=1");
        } else {
            header("Location: ../../main.php?module=producto&alert=4");
        }
    }

    // -------------------- UPDATE --------------------
    elseif ($accion == 'update' && isset($_POST['Guardar'], $_POST['codigo'])) {
        $codigo = intval($_POST['codigo']);
        $producto = trim(mysqli_real_escape_string($mysqli, $_POST['p_descrip']));
        $tipo_producto = intval($_POST['tipo_producto']);
        $u_medida = intval($_POST['u_medida']);
        $tipo_impuesto = $_POST['tipo_impuesto'];

        // Validar campos obligatorios
        if ($producto == '') {
            echo "<script>alert('Ingrese un producto'); window.history.back();</script>";
            exit;
        }

        // Verificar duplicado excluyendo el mismo registro
        $check = mysqli_query($mysqli, "SELECT 1 FROM producto WHERE LOWER(p_descrip) = LOWER('$producto') AND cod_producto != $codigo");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Ya existe otro producto con esta descripción.'); window.history.back();</script>";
            exit;
        }

        // Actualizar registro
        $query = mysqli_query($mysqli, "UPDATE producto 
                                        SET cod_tipo_prod = $tipo_producto,
                                            id_u_medida = $u_medida,
                                            p_descrip = '$producto',
                                            tipo_impuesto = '$tipo_impuesto'
                                        WHERE cod_producto = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=producto&alert=2");
        } else {
            header("Location: ../../main.php?module=producto&alert=4");
        }
    }

    // -------------------- DELETE --------------------
    elseif ($accion == 'delete' && isset($_GET['cod_producto'])) {
        $codigo = intval($_GET['cod_producto']);

        $query = mysqli_query($mysqli, "DELETE FROM producto WHERE cod_producto = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=producto&alert=3");
        } else {
            header("Location: ../../main.php?module=producto&alert=4");
        }
    }
}
?>