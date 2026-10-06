<?php
session_start();
require_once "../../config/database.php";

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
    exit;
}

// ✅ Solo elimina caracteres no numéricos
function limpiarTelefono($telefono)
{
    return preg_replace("/[^0-9]/", "", $telefono);
}

// ✅ Elimina todo lo que no sea número (quita guion)
function limpiarRUC($ruc)
{
    return preg_replace("/[^0-9]/", "", $ruc);
}

if ($_GET['act'] == 'insert') {
    if (isset($_POST['Guardar'])) {
        $codigo = intval($_POST['codigo']);
        // ✅ Solo escapamos comillas y caracteres especiales peligrosos
        $razon_social = mysqli_real_escape_string($mysqli, trim($_POST['razon_social']));
        $ruc = limpiarRUC($_POST['ruc']);
        $direccion = !empty($_POST['direccion']) ? mysqli_real_escape_string($mysqli, trim($_POST['direccion'])) : "No registrado";
        $telefono = !empty($_POST['telefono']) ? limpiarTelefono($_POST['telefono']) : "000";

        $query = mysqli_query($mysqli, "INSERT INTO proveedor (cod_proveedor, razon_social, ruc, direccion, telefono)
                                        VALUES ($codigo, '$razon_social', '$ruc', '$direccion', '$telefono')")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=proveedor&alert=1");
        } else {
            header("Location: ../../main.php?module=proveedor&alert=4");
        }
    }

} elseif ($_GET['act'] == 'update') {
    if (isset($_POST['Guardar']) && isset($_POST['codigo'])) {
        $codigo = intval($_POST['codigo']);
        $razon_social = mysqli_real_escape_string($mysqli, trim($_POST['razon_social']));
        $ruc = limpiarRUC($_POST['ruc']);
        $direccion = !empty($_POST['direccion']) ? mysqli_real_escape_string($mysqli, trim($_POST['direccion'])) : "No registrado";
        $telefono = !empty($_POST['telefono']) ? limpiarTelefono($_POST['telefono']) : "000";

        if (strlen($ruc) < 8 || strlen($ruc) > 9) {
            header("Location: ../../main.php?module=proveedor&alert=4");
            exit;
        }

        $query = mysqli_query($mysqli, "UPDATE proveedor SET 
                                            razon_social = '$razon_social',
                                            ruc = '$ruc',
                                            direccion = '$direccion',
                                            telefono = '$telefono'
                                        WHERE cod_proveedor = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=proveedor&alert=2");
        } else {
            header("Location: ../../main.php?module=proveedor&alert=4");
        }
    }

} elseif ($_GET['act'] == 'delete') {
    if (isset($_GET['cod_proveedor'])) {
        $codigo = intval($_GET['cod_proveedor']);
        $query = mysqli_query($mysqli, "DELETE FROM proveedor WHERE cod_proveedor = $codigo")
            or die('Error: ' . mysqli_error($mysqli));

        if ($query) {
            header("Location: ../../main.php?module=proveedor&alert=3");
        } else {
            header("Location: ../../main.php?module=proveedor&alert=4");
        }
    }
}
?>