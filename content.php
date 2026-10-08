<?php
// Enrutador: incluye la pantalla resuelta por main.php (ver config/routes.php).
// Debe incluirse desde main.php para que las vistas compartan $mysqli, $_SESSION, etc.
if ($routeStatus === 'ok') {
    include 'modules/' . $route['file'];
} elseif ($routeStatus === 'forbidden') {
    echo '<div class="alert alert-danger" role="alert"><i class="bi bi-x-circle"></i> No tienes permiso para ver esta sección.</div>';
} else {
    echo '<div class="alert alert-warning" role="alert"><i class="bi bi-exclamation-triangle"></i> La página solicitada no existe. <a href="?module=start">Volver al inicio</a></div>';
}
