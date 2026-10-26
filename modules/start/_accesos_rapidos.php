<?php
// Relleno temporal de la pantalla de inicio: accesos rápidos según los permisos del usuario.
// Cada acceso aparece solo si el rol tiene la acción indicada sobre esa página (config/permisos.php).
// Para cambiar los accesos basta con editar este arreglo.
$accesos = [
    ['pagina' => 'pedido', 'accion' => 'insertar', 'href' => '?module=form_pedido&form=add', 'icon' => 'bi-plus-lg', 'label' => 'Nuevo pedido', 'class' => 'btn-primary'],
    ['pagina' => 'pedido', 'accion' => 'leer', 'href' => '?module=pedido', 'icon' => 'bi-list-ul', 'label' => 'Ver pedidos', 'class' => 'btn-outline-primary'],
    ['pagina' => 'stock', 'accion' => 'leer', 'href' => '?module=stock', 'icon' => 'bi-box-seam', 'label' => 'Consultar stock', 'class' => 'btn-outline-primary'],
    ['pagina' => 'persona', 'accion' => 'insertar', 'href' => '?module=form_persona&form=add', 'icon' => 'bi-person-plus', 'label' => 'Nueva persona', 'class' => 'btn-outline-primary'],
    ['pagina' => 'permiso', 'accion' => 'leer', 'href' => '?module=permiso', 'icon' => 'bi-ui-checks-grid', 'label' => 'Permisos por rol', 'class' => 'btn-outline-secondary'],
    ['pagina' => 'accesos', 'accion' => 'leer', 'href' => '?module=accesos', 'icon' => 'bi-shield-lock', 'label' => 'Registro de accesos', 'class' => 'btn-outline-secondary'],
    ['pagina' => 'user', 'accion' => 'leer', 'href' => '?module=user', 'icon' => 'bi-people', 'label' => 'Usuarios', 'class' => 'btn-outline-secondary'],
    ['pagina' => 'perfil', 'accion' => 'leer', 'href' => '?module=perfil', 'icon' => 'bi-person', 'label' => 'Mi perfil', 'class' => 'btn-outline-secondary'],
    ['pagina' => 'password', 'accion' => 'leer', 'href' => '?module=password', 'icon' => 'bi-key', 'label' => 'Cambiar contraseña', 'class' => 'btn-outline-secondary'],
];
?>
<div class="card">
    <div class="card-body">
        <h2 class="h5 mb-3">Accesos rápidos</h2>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($accesos as $a): ?>
                <?php if (puede($a['pagina'], $a['accion'])): ?>
                    <a href="<?= $a['href'] ?>" class="btn <?= $a['class'] ?>">
                        <i class="bi <?= $a['icon'] ?>"></i> <?= $a['label'] ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
