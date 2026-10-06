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
        $t_p_descrip = trim(mysqli_real_escape_string($mysqli, $_POST['t_p_descrip']));

        // Validar campo vacío
        if ($t_p_descrip == '') {
            echo "<script>alert('La descripción del tipo de producto no puede estar vacía.'); window.history.back();</script>";
            exit;
        }

        // Verificar duplicado
        $check = mysqli_query($mysqli, "SELECT 1 FROM tipo_producto WHERE LOWER(t_p_descrip) = LOWER('$t_p_descrip')");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Ya existe un tipo de producto con esta descripción.'); window.history.back();</script>";
            exit;
        }

        // Insertar registro
        $query = mysqli_query($mysqli, "INSERT INTO tipo_producto (cod_tipo_prod, t_p_descrip) VALUES ($codigo,'$t_p_descrip')")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=tipo_producto&alert=1");
        } else {
            header("Location: ../../main.php?module=tipo_producto&alert=4");
        }
    }

    // -------------------- UPDATE --------------------
    elseif ($accion == 'update' && isset($_POST['Guardar'], $_POST['codigo'])) {
        $codigo = intval($_POST['codigo']);
        $t_p_descrip = trim(mysqli_real_escape_string($mysqli, $_POST['t_p_descrip']));

        // Validar campo vacío
        if ($t_p_descrip == '') {
            echo "<script>alert('La descripción del tipo de producto no puede estar vacía.'); window.history.back();</script>";
            exit;
        }

        // Verificar duplicado excluyendo el mismo registro
        $check = mysqli_query($mysqli, "SELECT 1 FROM tipo_producto WHERE LOWER(t_p_descrip) = LOWER('$t_p_descrip') AND cod_tipo_prod != $codigo");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Ya existe otro tipo de producto con esta descripción.'); window.history.back();</script>";
            exit;
        }

        // Actualizar registro
        $query = mysqli_query($mysqli, "UPDATE tipo_producto SET t_p_descrip = '$t_p_descrip' WHERE cod_tipo_prod = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=tipo_producto&alert=2");
        } else {
            header("Location: ../../main.php?module=tipo_producto&alert=4");
        }
    }

    // -------------------- DELETE --------------------
    elseif ($accion == 'delete' && isset($_GET['cod_tipo_prod'])) {
        $codigo = intval($_GET['cod_tipo_prod']);

        $query = mysqli_query($mysqli, "DELETE FROM tipo_producto WHERE cod_tipo_prod = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=tipo_producto&alert=3");
        } else {
            header("Location: ../../main.php?module=tipo_producto&alert=4");
        }
    }
}
?>