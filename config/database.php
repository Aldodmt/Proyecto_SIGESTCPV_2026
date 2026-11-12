<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "sysweb";

// El puerto puede cambiarse con la variable de entorno SYSWEB_DB_PORT (sirve para probar contra una instancia de MySQL
// aislada sin tocar la del panel de XAMPP). Si no está definida se usa el 3306 de siempre.
$port = (int) (getenv('SYSWEB_DB_PORT') ?: 3306);
$mysqli = new mysqli($server, $username, $password, $database, $port);
if ($mysqli->connect_error) {
    die('error' . $mysqli->connect_error);
}
// Conexión siempre en utf8mb4 (tildes y ñ correctos), sin depender de la configuración del servidor
$mysqli->set_charset('utf8mb4');

// Disponible también como global: así funciona igual si este archivo se incluye dentro de una función
// (por ejemplo, desde guard_endpoint() en config/permisos.php).
$GLOBALS['mysqli'] = $mysqli;
?>