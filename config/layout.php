<?php
// Funciones de apoyo de la plantilla: control de acceso por rol, filtrado y dibujo del sidebar.

const ROLES_TODOS = '*';

function route_allows($roles, string $role): bool
{
    return $roles === ROLES_TODOS || in_array($role, (array) $roles, true);
}

// Resuelve ?module= contra config/routes.php. Devuelve [estado, ruta]: ok | notfound | forbidden.
function route_resolve(string $module, array $routes, string $role): array
{
    if (!isset($routes[$module]) || !is_file(__DIR__ . '/../modules/' . $routes[$module]['file'])) {
        return ['notfound', null];
    }
    $route = $routes[$module];
    return route_allows($route['roles'], $role) ? ['ok', $route] : ['forbidden', $route];
}

// Deja solo lo que el rol puede ver; quita grupos vacíos y títulos sin ítems debajo.
function menu_filter(array $items, array $routes, string $role): array
{
    $out = [];
    foreach ($items as $item) {
        if (!empty($item['hidden'])) {
            continue;
        }
        if (isset($item['header'])) {
            $out[] = $item;
        } elseif (isset($item['children'])) {
            $item['children'] = menu_filter($item['children'], $routes, $role);
            if ($item['children']) {
                $out[] = $item;
            }
        } elseif (isset($item['module'])) {
            if (isset($routes[$item['module']]) && route_allows($routes[$item['module']]['roles'], $role)) {
                $out[] = $item;
            }
        } elseif (isset($item['url'])) {
            $out[] = $item;
        }
    }
    // Un título solo se muestra si lo sigue algún ítem
    $final = [];
    foreach ($out as $i => $item) {
        if (isset($item['header']) && (!isset($out[$i + 1]) || isset($out[$i + 1]['header']))) {
            continue;
        }
        $final[] = $item;
    }
    return $final;
}

function menu_contains(array $item, string $key): bool
{
    if (($item['module'] ?? null) === $key) {
        return true;
    }
    foreach ($item['children'] ?? [] as $child) {
        if (menu_contains($child, $key)) {
            return true;
        }
    }
    return false;
}

function menu_render(array $items, string $activeKey, int $level = 0): string
{
    $h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    $html = '';
    foreach ($items as $item) {
        if (isset($item['header'])) {
            $html .= '<li class="nav-header">' . $h($item['header']) . '</li>';
            continue;
        }
        $icon = $item['icon'] ?? 'bi-circle';
        $label = $h($item['label']);

        if (isset($item['children'])) {
            $open = menu_contains($item, $activeKey);
            $html .= '<li class="nav-item' . ($open ? ' menu-open' : '') . '">'
                . '<a href="#" class="nav-link' . ($open ? ' active' : '') . '">'
                . '<i class="nav-icon bi ' . $h($icon) . '"></i>'
                . '<p>' . $label . '<i class="nav-arrow bi bi-chevron-right"></i></p></a>'
                . '<ul class="nav nav-treeview">' . menu_render($item['children'], $activeKey, $level + 1) . '</ul></li>';
        } elseif (isset($item['module'])) {
            $active = $item['module'] === $activeKey;
            $html .= '<li class="nav-item"><a href="?module=' . $h($item['module']) . '" class="nav-link' . ($active ? ' active' : '') . '">'
                . '<i class="nav-icon bi ' . $h($icon) . '"></i><p>' . $label . '</p></a></li>';
        } else {
            $html .= '<li class="nav-item"><a href="' . $h(str_replace(' ', '%20', $item['url'])) . '" target="_blank" rel="noopener" class="nav-link">'
                . '<i class="nav-icon bi ' . $h($icon) . '"></i><p>' . $label . '</p></a></li>';
        }
    }
    return $html;
}
