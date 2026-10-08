<?php
// Plantilla principal (AdminLTE): valida sesión y rol, arma head + header + sidebar + contenido + footer.
session_start();

if (empty($_SESSION['id_user']) || empty($_SESSION['username'])) {
    header('Location: index.php?alert=3');
    exit();
}

require_once 'config/database.php';
require_once 'config/layout.php';

$routes = require 'config/routes.php';
$role = $_SESSION['permisos_acceso'] ?? '';
$module = $_GET['module'] ?? 'start';

[$routeStatus, $route] = route_resolve($module, $routes, $role);
$pageTitle = $route['title'] ?? 'Sysweb';
$activeKey = $route['menu'] ?? $module;

require 'layout/head.php';
require 'layout/header.php';
require 'layout/sidebar.php';
?>
        <main class="app-main">
            <div class="container-fluid py-3">
                <?php include 'content.php'; ?>
            </div>
        </main>
<?php require 'layout/footer.php'; ?>
