<?php
// Estructura del sidebar. Se puede anidar a cualquier profundidad con 'children'.
//
//   ['header' => 'TEXTO']                          -> título de sección
//   ['label' => 'X', 'module' => 'clave']          -> enlace a una ruta de config/routes.php
//   ['label' => 'X', 'url' => 'archivo.pdf']       -> enlace directo (se abre en otra pestaña)
//   ['label' => 'X', 'icon' => 'bi-...', 'children' => [ ... ]]   -> grupo desplegable
//   'hidden' => true                               -> definido pero sin mostrar (p. ej. Ventas)
//
// La visibilidad sale de los PERMISOS de la base de datos (Seguridad > Permisos): un ítem aparece
// si el rol del usuario tiene permiso "leer" sobre esa página, y un grupo aparece si tiene al menos
// un hijo visible.
//
// Para sumar un módulo nuevo: copiar el bloque "Compras", cambiar etiqueta/iconos/rutas, registrar
// sus rutas en config/routes.php y pulsar "Sincronizar con el sistema" en Seguridad > Páginas y módulos.

return [
    ['label' => 'Inicio', 'icon' => 'bi-house-door', 'module' => 'start'],

    ['header' => 'MÓDULOS'],

    // ===== Módulo de Compras =====
    ['label' => 'Compras', 'icon' => 'bi-cart3', 'children' => [
        ['label' => 'Referenciales', 'icon' => 'bi-journal-text', 'children' => [
           // ['label' => 'Depósito', 'module' => 'deposito'], NO UTILIZO DEPOSITO EN MI SISTEMA 
            ['label' => 'Proveedor', 'module' => 'proveedor'],
            ['label' => 'Producto', 'module' => 'producto'],
            ['label' => 'Tipo de producto', 'module' => 'tipo_producto'],
            ['label' => 'Unidad de medida', 'module' => 'u_medida'],
        ]],
        ['label' => 'Movimientos', 'icon' => 'bi-arrow-left-right', 'children' => [
            ['label' => 'Pedidos', 'module' => 'pedido'],
            ['label' => 'Presupuestos', 'module' => 'presupuesto'],
            ['label' => 'Órdenes de compra', 'module' => 'orden_c'],
            ['label' => 'Compras', 'module' => 'compra'],
            ['label' => 'Cuentas a pagar', 'module' => 'cuenta'],
            ['label' => 'Notas de crédito/débito', 'module' => 'nota_c_d'],
            ['label' => 'Ajustes de stock', 'module' => 'ajuste'],
            ['label' => 'Notas de remisión', 'module' => 'nota_remision'],
            ['label' => 'Stock', 'module' => 'stock'],
        ]],
        ['label' => 'Informes', 'icon' => 'bi-bar-chart-line', 'children' => [
            ['label' => 'Productos', 'module' => 'info_producto'],
            ['label' => 'Proveedores', 'module' => 'info_proveedores'],
            ['label' => 'Unidades de medida', 'module' => 'info_u_medida'],
            ['label' => 'Pedidos', 'module' => 'info_pedido'],
            ['label' => 'Presupuestos', 'module' => 'info_presu'],
            ['label' => 'Órdenes de compra', 'module' => 'info_orden'],
            ['label' => 'Compras', 'module' => 'info_facturacion_compra'],
            ['label' => 'Notas de crédito/débito', 'module' => 'info_nota_cd'],
            ['label' => 'Ajustes', 'module' => 'info_ajuste'],
            ['label' => 'Notas de remisión', 'module' => 'info_notaR'],
        ]],
    ]],

    // ===== Módulo de Ventas (oculto hasta que se retome) =====
    ['label' => 'Ventas', 'icon' => 'bi-shop', 'hidden' => true, 'children' => [
        ['label' => 'Clientes', 'module' => 'clientes'],
        ['label' => 'Pedidos de venta', 'module' => 'pedido_v'],
        ['label' => 'Ventas', 'module' => 'ventas'],
        ['label' => 'Informe de pedidos', 'module' => 'info_pedido_v'],
    ]],

    // ===== Personal =====
   /* ['label' => 'Personal', 'icon' => 'bi-person-vcard', 'children' => [
        ,
    ]],*/

    ['header' => 'GENERAL'],
    ['label' => 'Referenciales generales', 'icon' => 'bi-geo-alt', 'children' => [
        ['label' => 'Departamento', 'module' => 'departamento'],
        ['label' => 'Ciudad', 'module' => 'ciudad'],
        ['label' => 'Nacionalidad', 'module' => 'nacionalidad'],
        ['label' => 'Género', 'module' => 'genero'],
        ['label' => 'Personas', 'module' => 'persona']
    ]],

    ['header' => 'SEGURIDAD'],
    ['label' => 'Seguridad', 'icon' => 'bi-shield-lock', 'children' => [
        ['label' => 'Usuarios', 'module' => 'user'],
        ['label' => 'Roles', 'module' => 'rol'],
        ['label' => 'Permisos por rol', 'module' => 'permiso'],
        ['label' => 'Páginas y módulos', 'module' => 'pagina'],
        ['label' => 'Registro de accesos', 'module' => 'accesos'],
        ['label' => 'Funcionarios', 'module' => 'funcionario'],
        ['label' => 'Cargos', 'module' => 'cargo']
    ]],

    ['header' => 'CUENTA'],
    ['label' => 'Cambiar contraseña', 'icon' => 'bi-key', 'module' => 'password'],
    ['label' => 'Manual de usuario', 'icon' => 'bi-question-circle', 'url' => 'modules/manual/Manual de usuario.pdf'],
];
