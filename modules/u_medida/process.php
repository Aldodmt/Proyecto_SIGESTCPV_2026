<?php
session_start();
require_once "../../config/database.php";

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php'>";
    exit;
}

if (isset($_GET['act'])) {
    $accion = $_GET['act'];

    // -------------------- INSERT --------------------
    if ($accion == 'insert' && isset($_POST['Guardar'])) {
        $codigo = intval($_POST['codigo']);
        $u_descrip = trim(mysqli_real_escape_string($mysqli, $_POST['u_descrip']));

        if ($u_descrip == '') {
            echo "<script>alert('La descripción no puede estar vacía.'); window.history.back();</script>";
            exit;
        }

        $check = mysqli_query($mysqli, "SELECT 1 FROM u_medida WHERE LOWER(u_descrip) = LOWER('$u_descrip')");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Ya existe esta unidad de medida.'); window.history.back();</script>";
            exit;
        }

        $query = mysqli_query($mysqli, "INSERT INTO u_medida (id_u_medida, u_descrip) VALUES ($codigo, '$u_descrip')")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=u_medida&alert=1");
        } else {
            header("Location: ../../main.php?module=u_medida&alert=4");
        }
    }

    // -------------------- UPDATE --------------------
    elseif ($accion == 'update' && isset($_POST['Guardar'], $_POST['codigo'])) {
        $codigo = intval($_POST['codigo']);
        $u_descrip = trim(mysqli_real_escape_string($mysqli, $_POST['u_descrip']));

        if ($u_descrip == '') {
            echo "<script>alert('La descripción no puede estar vacía.'); window.history.back();</script>";
            exit;
        }

        $check = mysqli_query($mysqli, "
            SELECT 1 FROM u_medida 
            WHERE LOWER(u_descrip) = LOWER('$u_descrip') 
            AND id_u_medida != $codigo
        ");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Ya existe otra unidad de medida con esta descripción.'); window.history.back();</script>";
            exit;
        }

        $query = mysqli_query($mysqli, "UPDATE u_medida SET u_descrip = '$u_descrip' WHERE id_u_medida = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=u_medida&alert=2");
        } else {
            header("Location: ../../main.php?module=u_medida&alert=4");
        }
    }

    // -------------------- DELETE --------------------
    elseif ($accion == 'delete' && isset($_GET['id_u_medida'])) {
        $codigo = intval($_GET['id_u_medida']);

        $query = mysqli_query($mysqli, "DELETE FROM u_medida WHERE id_u_medida = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=u_medida&alert=3");
        } else {
            header("Location: ../../main.php?module=u_medida&alert=4");
        }
    }
}
?>