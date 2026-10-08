<?php
// Relleno temporal de la pantalla de inicio: accesos rápidos según el rol del usuario.
// Cada acceso aparece solo si el rol puede entrar a esa ruta (config/routes.php).
// Para cambiar los accesos basta con editar este arreglo.
$accesos = [
    ['module' => 'form_pedido', 'extra' => '&form=add', 'icon' => 'bi-plus-lg', 'label' => 'Nuevo pedido', 'class' => 'btn-primary'],
    ['module' => 'pedido', 'icon' => 'bi-list-ul', 'label' => 'Ver pedidos', 'class' => 'btn-outline-primary'],
    ['module' => 'stock', 'icon' => 'bi-box-seam', 'label' => 'Consultar stock', 'class' => 'btn-outline-primary'],
    ['module' => 'accesos', 'icon' => 'bi-shield-lock', 'label' => 'Registro de accesos', 'class' => 'btn-outline-secondary'],
    ['module' => 'user', 'icon' => 'bi-people', 'label' => 'Usuarios', 'class' => 'btn-outline-secondary'],
    ['module' => 'perfil', 'icon' => 'bi-person', 'label' => 'Mi perfil', 'class' => 'btn-outline-secondary'],
    ['module' => 'password', 'icon' => 'bi-key', 'label' => 'Cambiar contraseña', 'class' => 'btn-outline-secondary'],
];
?>
<div class="card">
    <div class="card-body">
        <h2 class="h5 mb-3">Accesos rápidos</h2>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($accesos as $a): ?>
                <?php if (isset($routes[$a['module']]) && route_allows($routes[$a['module']]['roles'], $role)): ?>
                    <a href="?module=<?= $a['module'] ?><?= $a['extra'] ?? '' ?>" class="btn <?= $a['class'] ?>">
                        <i class="bi <?= $a['icon'] ?>"></i> <?= $a['label'] ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
