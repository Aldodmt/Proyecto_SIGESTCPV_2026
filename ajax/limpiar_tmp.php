<?php
session_start();
require_once '../config/database.php';

$session_id = session_id();
error_log("LIMPIAR tmp FEIAIIIIIIIIII con session_id=$session_id");
mysqli_query($mysqli, "DELETE FROM tmp WHERE session_id = '$session_id' AND estado_tmp = 'BORRADOR'");

?>