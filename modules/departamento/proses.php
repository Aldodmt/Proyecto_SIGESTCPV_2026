<?php
session_start();
require_once "../../config/database.php";

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
} else {
    if ($_GET['act'] == 'insert') {
        if (isset($_POST['Guardar'])) {
            $codigo = $_POST['codigo'];
            $dep_descripcion = mysqli_real_escape_string($mysqli, $_POST['dep_descripcion']);


            // Validar que la descripción no esté vacía
            if ($dep_descripcion == '') {
                echo "<script>alert('La descripción del departamento no puede estar vacía.'); window.history.back();</script>";
                exit;
            }

            // Verificar duplicado 
            $check = mysqli_query($mysqli, "
                SELECT * FROM departamento
                WHERE LOWER(dep_descripcion) = LOWER('$dep_descripcion')
            ");

            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('Este departamento ya existe.'); window.history.back();</script>";
                exit;
            }

            // Insertar si no hay duplicado
            $query = mysqli_query($mysqli, "
                INSERT INTO departamento (id_departamento, dep_descripcion)
                VALUES ($codigo, '$dep_descripcion')
            ") or die('Error: ' . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=departamento&alert=1");
            } else {
                header("Location: ../../main.php?module=departamento&alert=4");
            }
        }
    } elseif ($_GET['act'] == 'update') {
        if (isset($_POST['Guardar']) && isset($_POST['codigo'])) {
            $codigo = $_POST['codigo'];
            $dep_descripcion = mysqli_real_escape_string($mysqli, $_POST['dep_descripcion']);


            // Validar que la descripción no esté vacía
            if ($dep_descripcion == '') {
                echo "<script>alert('La descripción del departamento no puede estar vacía.'); window.history.back();</script>";
                exit;
            }

            // Verificar duplicado excluyendo el mismo registro que se actualiza
            $check = mysqli_query($mysqli, "
                SELECT * FROM departamento
                WHERE LOWER(dep_descripcion) = LOWER('$dep_descripcion')
                AND id_departamento != $codigo
            ");

            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('Ya existe otro departamento con esta descripción.'); window.history.back();</script>";
                exit;
            }

            $query = mysqli_query($mysqli, "
                UPDATE departamento 
                SET dep_descripcion = '$dep_descripcion'
                WHERE id_departamento = $codigo
            ") or die('Error: ' . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=departamento&alert=2");
            } else {
                header("Location: ../../main.php?module=departamento&alert=4");
            }
        }
    } elseif ($_GET['act'] == 'delete') {
        if (isset($_GET['id_departamento'])) {
            $codigo = $_GET['id_departamento'];

            $query = mysqli_query($mysqli, "
                DELETE FROM departamento
                WHERE id_departamento = $codigo
            ") or die('Error: ' . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=departamento&alert=3");
            } else {
                header("Location: ../../main.php?module=departamento&alert=4");
            }
        }
    }
}
?>