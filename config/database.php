<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "sysweb";

$mysqli = new mysqli($server, $username, $password, $database);
if ($mysqli->connect_error) {
    die('error' . $mysqli->connect_error);
}
// Conexión siempre en utf8mb4 (tildes y ñ correctos), sin depender de la configuración del servidor
$mysqli->set_charset('utf8mb4');

// Disponible también como global: así funciona igual si este archivo se incluye dentro de una función
// (por ejemplo, desde guard_endpoint() en config/permisos.php).
$GLOBALS['mysqli'] = $mysqli;
?>