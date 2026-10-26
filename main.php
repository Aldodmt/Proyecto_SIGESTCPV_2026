<?php
// Plantilla principal (AdminLTE): valida sesión y permisos, arma head + header + sidebar + contenido + footer.
session_start();

if (empty($_SESSION['id_user']) || empty($_SESSION['username'])) {
    header('Location: index.php?alert=3');
    exit();
}

require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/permisos.php';
require_once 'config/layout.php';

// El modelo de permisos necesita las tablas de la migración v2 (rol, modulo, pagina, permiso)
if (!permisos_tablas_ok($mysqli)) {
    http_response_code(503);
    $base = '';
    $authTitle = 'Falta actualizar la base de datos';
    require 'layout/auth_head.php';
    echo '<p class="login-box-msg">Falta actualizar la base de datos</p>'
        . '<div class="alert alert-warning">Este sistema ahora controla el acceso con <strong>roles y permisos</strong>, '
        . 'pero la base de datos todavía no tiene esas tablas.</div>'
        . '<p class="small mb-1">Aplica la migración y vuelve a entrar:</p>'
        . '<code class="d-block small mb-3">database/migraciones/v1_a_v2_personas_permisos.sql</code>'
        . '<a class="btn btn-secondary w-100" href="logout.php">Cerrar sesión</a>';
    require 'layout/auth_foot.php';
    echo '</body></html>';
    exit();
}

// Se revalida al usuario en cada petición: si lo bloquearon, lo dieron de baja o le cambiaron el rol, rige al instante
$usuarioSesion = permisos_iniciar($mysqli);
if (!$usuarioSesion || $usuarioSesion['status'] !== 'activo') {
    $alerta = $usuarioSesion ? 6 : 3;
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?alert=' . $alerta);
    exit();
}

$routes = require 'config/routes.php';
$role = $_SESSION['permisos_acceso'] ?? '';
$module = $_GET['module'] ?? 'start';

[$routeStatus, $route] = route_resolve($module, $routes, $_GET);
$pageTitle = $route['title'] ?? 'Sysweb';
$activeKey = isset($route) ? route_pagina($module, $route) : $module;

require 'layout/head.php';
require 'layout/header.php';
require 'layout/sidebar.php';
?>
        <main class="app-main">
            <div class="container-fluid py-3">
                <?php
                // Lo que el usuario no puede hacer (editar, borrar...) se quita de la pantalla; el bloqueo real lo hace el servidor
                ob_start();
                include 'content.php';
                echo permisos_recortar(ob_get_clean(), $routes);
                ?>
            </div>
        </main>
<?php require 'layout/footer.php'; ?>
