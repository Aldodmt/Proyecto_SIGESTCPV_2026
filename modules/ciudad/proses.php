<?php
session_start();
require_once "../../config/database.php";

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
    exit;
} else {
    // ---------- INSERT ----------
    if ($_GET['act'] == 'insert') {
        if (isset($_POST['Guardar'])) {
            $codigo = $_POST['codigo'];
            $departamento = $_POST['departamento'];
            $descrip_ciudad = mysqli_real_escape_string($mysqli, trim($_POST['descrip_ciudad']));


            // Validar que la descripción no esté vacía
            if ($descrip_ciudad == '') {
                echo "<script>alert('La descripción de la ciudad no puede estar vacía.'); window.history.back();</script>";
                exit;
            }

            //Verificar duplicado
            $check = mysqli_query($mysqli, "
                SELECT * FROM ciudad 
                WHERE LOWER(descrip_ciudad) = LOWER('$descrip_ciudad')
            ");

            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('La ciudad \"$descrip_ciudad\" ya está registrada.'); window.history.back();</script>";
                exit;
            }

            $query = mysqli_query($mysqli, "
                INSERT INTO ciudad (cod_ciudad, id_departamento, descrip_ciudad)
                VALUES ($codigo, $departamento, '$descrip_ciudad')
            ") or die('Error: ' . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=ciudad&alert=1");
            } else {
                header("Location: ../../main.php?module=ciudad&alert=4");
            }
        }
    }

    // ---------- UPDATE ----------
    elseif ($_GET['act'] == 'update') {
        if (isset($_POST['Guardar'])) {
            $codigo = $_POST['codigo'];
            $departamento = $_POST['departamento'];
            $descrip_ciudad = mysqli_real_escape_string($mysqli, trim($_POST['descrip_ciudad']));


            // Validar que la descripción no esté vacía
            if ($dep_descripcion == '') {
                echo "<script>alert('La descripción de la ciudad no puede estar vacía.'); window.history.back();</script>";
                exit;
            }

            //Verificar duplicado 
            $check = mysqli_query($mysqli, "
                SELECT * FROM ciudad 
                WHERE LOWER(descrip_ciudad) = LOWER('$descrip_ciudad') 
                AND cod_ciudad != $codigo
            ");

            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('Ya existe otra ciudad con ese nombre.'); window.history.back();</script>";
                exit;
            }

            $query = mysqli_query($mysqli, "
                UPDATE ciudad 
                SET descrip_ciudad = '$descrip_ciudad',
                    id_departamento = $departamento
                WHERE cod_ciudad = $codigo
            ") or die('Error: ' . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=ciudad&alert=2");
            } else {
                header("Location: ../../main.php?module=ciudad&alert=4");
            }
        }
    }

    // ---------- DELETE ----------
    elseif ($_GET['act'] == 'delete') {
        if (isset($_GET['cod_ciudad'])) {
            $codigo = $_GET['cod_ciudad'];

            $query = mysqli_query($mysqli, "DELETE FROM ciudad WHERE cod_ciudad = $codigo")
                or die("Error: " . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=ciudad&alert=3");
            } else {
                header("Location: ../../main.php?module=ciudad&alert=4");
            }
        }
    }
}
?>