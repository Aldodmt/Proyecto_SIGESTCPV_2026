<?php
// Sidebar generado desde config/menu.php, filtrado por el rol de la sesión.
$menuVisible = menu_filter(require __DIR__ . '/../config/menu.php', $routes, $role);
?>
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="?module=start" class="brand-link">
                    <img src="images/favicon.ico" alt="Sysweb" class="brand-image opacity-75 shadow">
                    <span class="brand-text fw-light">Sysweb</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2" aria-label="Menú principal">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                        <?= menu_render($menuVisible, $activeKey) ?>
                    </ul>
                </nav>
            </div>
        </aside>
