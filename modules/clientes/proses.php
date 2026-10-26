<?php

session_start();

require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('clientes');   // control de acceso por rol (config/permisos.php)
require_once "../../config/database.php";

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=alert=3'>";
} else {
    if ($_GET['act'] == 'insert') {
        if (isset($_POST['Guardar'])) {
            $codigo = $_POST['codigo'];
            $cod_ciudad = $_POST['codigo_ciudad'];
            $ci_ruc = $_POST['ci_ruc'];
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $direccion = $_POST['direccion'];
            $telefono = $_POST['telefono'];

            if (!empty($_POST['direccion'])) {
                $direccion = $_POST['direccion'];
            } else {
                $direccion = 'No se encuentras registros';
            }

            if (!empty($_POST['telefono'])) {
                $telefono = $_POST['telefono'];
            } else {
                $telefono = 000;
            }


            $query = mysqli_query($mysqli, "INSERT INTO clientes (id_cliente, cod_ciudad, ci_ruc, cli_nombre, cli_apellido, cli_direccion, cli_telefono)
            VALUES ($codigo, '$cod_ciudad', '$ci_ruc', '$nombre', '$apellido', '$direccion', $telefono)")
                or die('Error' . mysqli_error($mysqli));
            if ($query) {
                header("Location: ../../main.php?module=clientes&alert=1");
            } else {
                header("Location: ../../main.php?module=clientes&alert=4");
            }
        }
    } elseif ($_GET['act'] == 'update') {
        if (isset($_POST['Guardar'])) {
            if (isset($_POST['codigo'])) {
                $codigo = $_POST['codigo'];
                $cod_ciudad = $_POST['codigo_ciudad'];
                $ci_ruc = $_POST['ci_ruc'];
                $nombre = $_POST['nombre'];
                $apellido = $_POST['apellido'];
                $direccion = $_POST['direccion'];
                $telefono = $_POST['telefono'];

                if (!empty($_POST['direccion'])) {
                    $direccion = $_POST['direccion'];
                } else {
                    $direccion = 'No se encuentras registros';
                }

                if (!empty($_POST['telefono'])) {
                    $telefono = $_POST['telefono'];
                } else {
                    $telefono = 000;
                }

                $query = mysqli_query($mysqli, "UPDATE clientes SET cod_ciudad = $cod_ciudad,
                                                            ci_ruc = $ci_ruc,
                                                            cli_nombre = '$nombre',
                                                            cli_apellido = '$apellido',
                                                            cli_direccion = '$direccion',
                                                            cli_telefono = $telefono
                                                            WHERE id_cliente = $codigo")
                    or die("Error" . mysqli_error($mysqli));
                if ($query) {
                    header("Location: ../../main.php?module=clientes&alert=2");
                } else {
                    header("Location: ../../main.php?module=clientes&alert=4");
                }
            }
        }
    } elseif ($_GET['act'] == 'delete') {
        if (isset($_GET['id_cliente'])) {
            $codigo = $_GET['id_cliente'];

            $query = mysqli_query($mysqli, "DELETE FROM clientes
                                            WHERE id_cliente = $codigo")
                or die("Error: " . mysqli_error($mysqli));
            echo $query;
            if ($query) {
                header("Location: ../../main.php?module=clientes&alert=3");
            } else {
                header("Location: ../../main.php?module=clientes&alert=4");
            }
        }
    }
}

?>