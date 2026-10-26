<?php
session_start();

require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('cuenta');   // control de acceso por rol (config/permisos.php)
require_once '../../config/database.php';

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=alert=3'>";
} else {
    if ($_GET['act'] == 'anular') {
        if (isset($_GET['id_cuenta'])) {
            $codigo = $_GET['id_cuenta'];
            $query = mysqli_query($mysqli, "UPDATE cuentas_a_pagar SET estado = 'inactivo' WHERE id_cuenta= $codigo")
                or die("Error: " . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=cuenta&alert=2");
            } else {
                header("Location: ../../main.php?module=cuenta&alert=3");
            }
        }
    } elseif ($_GET['act'] == 'aprobar') {
        if (isset($_GET['id_cuenta'])) {
            $codigo = $_GET['id_cuenta'];
            $query = mysqli_query($mysqli, "UPDATE cuentas_a_pagar SET estado = 'activo' WHERE id_cuenta= $codigo")
                or die("Error: " . mysqli_error($mysqli));

            if ($query) {
                header("Location: ../../main.php?module=cuenta&alert=4");
            } else {
                header("Location: ../../main.php?module=cuenta&alert=3");
            }
        }
    }

}
?>