<?php
// Registro único de rutas: ?module=<clave> -> archivo a incluir.
// Para agregar una pantalla nueva basta con añadir una línea aquí (y, si debe verse en el
// sidebar, una entrada en config/menu.php). No hay que tocar content.php ni main.php.
//
//  file   : archivo dentro de modules/
//  title  : título de la pestaña
//  roles  : roles que pueden entrar ('*' = cualquier usuario con sesión)
//  menu   : clave del ítem del sidebar que debe verse activo (por defecto, la propia ruta)

const ROLES_COMPRAS = ['Super Admin', 'Compras'];
const ROLES_VENTAS = ['Super Admin', 'Ventas'];
const ROLES_ADMIN = ['Super Admin'];

return [
    // ---- General (todos los usuarios) ----
    'start' => ['file' => 'start/view.php', 'title' => 'Inicio', 'roles' => '*'],
    'password' => ['file' => 'password/view.php', 'title' => 'Cambiar contraseña', 'roles' => '*'],
    'perfil' => ['file' => 'perfil/view.php', 'title' => 'Mi perfil', 'roles' => '*'],
    'form_perfil' => ['file' => 'perfil/form.php', 'title' => 'Editar perfil', 'roles' => '*', 'menu' => 'perfil'],
    'departamento' => ['file' => 'departamento/view.php', 'title' => 'Departamentos', 'roles' => '*'],
    'form_departamento' => ['file' => 'departamento/form.php', 'title' => 'Departamento', 'roles' => '*', 'menu' => 'departamento'],
    'ciudad' => ['file' => 'ciudad/view.php', 'title' => 'Ciudades', 'roles' => '*'],
    'form_ciudad' => ['file' => 'ciudad/form.php', 'title' => 'Ciudad', 'roles' => '*', 'menu' => 'ciudad'],

    // ---- Administración ----
    'user' => ['file' => 'user/view.php', 'title' => 'Usuarios', 'roles' => ROLES_ADMIN],
    'form_user' => ['file' => 'user/form.php', 'title' => 'Usuario', 'roles' => ROLES_ADMIN, 'menu' => 'user'],
    'accesos' => ['file' => 'accesos/view.php', 'title' => 'Registro de accesos', 'roles' => ROLES_ADMIN],

    // ---- Módulo de Compras: referenciales ----
    'deposito' => ['file' => 'deposito/view.php', 'title' => 'Depósitos', 'roles' => ROLES_COMPRAS],
    'form_deposito' => ['file' => 'deposito/form.php', 'title' => 'Depósito', 'roles' => ROLES_COMPRAS, 'menu' => 'deposito'],
    'proveedor' => ['file' => 'proveedor/view.php', 'title' => 'Proveedores', 'roles' => ROLES_COMPRAS],
    'form_proveedor' => ['file' => 'proveedor/form.php', 'title' => 'Proveedor', 'roles' => ROLES_COMPRAS, 'menu' => 'proveedor'],
    'producto' => ['file' => 'producto/view.php', 'title' => 'Productos', 'roles' => ROLES_COMPRAS],
    'form_producto' => ['file' => 'producto/form.php', 'title' => 'Producto', 'roles' => ROLES_COMPRAS, 'menu' => 'producto'],
    'tipo_producto' => ['file' => 'tipo_producto/view.php', 'title' => 'Tipos de producto', 'roles' => ROLES_COMPRAS],
    'form_tipo_producto' => ['file' => 'tipo_producto/form.php', 'title' => 'Tipo de producto', 'roles' => ROLES_COMPRAS, 'menu' => 'tipo_producto'],
    'u_medida' => ['file' => 'u_medida/view.php', 'title' => 'Unidades de medida', 'roles' => ROLES_COMPRAS],
    'form_u_medida' => ['file' => 'u_medida/form.php', 'title' => 'Unidad de medida', 'roles' => ROLES_COMPRAS, 'menu' => 'u_medida'],

    // ---- Módulo de Compras: movimientos ----
    'pedido' => ['file' => 'pedido/view.php', 'title' => 'Pedidos', 'roles' => ROLES_COMPRAS],
    'form_pedido' => ['file' => 'pedido/form.php', 'title' => 'Pedido', 'roles' => ROLES_COMPRAS, 'menu' => 'pedido'],
    'presupuesto' => ['file' => 'presupuesto/view.php', 'title' => 'Presupuestos', 'roles' => ROLES_COMPRAS],
    'form_presupuesto' => ['file' => 'presupuesto/form.php', 'title' => 'Presupuesto', 'roles' => ROLES_COMPRAS, 'menu' => 'presupuesto'],
    'orden_c' => ['file' => 'orden_c/view.php', 'title' => 'Órdenes de compra', 'roles' => ROLES_COMPRAS],
    'form_orden_c' => ['file' => 'orden_c/form.php', 'title' => 'Orden de compra', 'roles' => ROLES_COMPRAS, 'menu' => 'orden_c'],
    'compra' => ['file' => 'compras/view.php', 'title' => 'Compras', 'roles' => ROLES_COMPRAS],
    'form_compra' => ['file' => 'compras/form.php', 'title' => 'Compra', 'roles' => ROLES_COMPRAS, 'menu' => 'compra'],
    'cuenta' => ['file' => 'cuenta/view.php', 'title' => 'Cuentas a pagar', 'roles' => ROLES_COMPRAS],
    'nota_c_d' => ['file' => 'nota_c_d/view.php', 'title' => 'Notas de crédito/débito', 'roles' => ROLES_COMPRAS],
    'form_nota_c_d' => ['file' => 'nota_c_d/form.php', 'title' => 'Nota de crédito/débito', 'roles' => ROLES_COMPRAS, 'menu' => 'nota_c_d'],
    'ajuste' => ['file' => 'ajuste/view.php', 'title' => 'Ajustes de stock', 'roles' => ROLES_COMPRAS],
    'form_ajuste' => ['file' => 'ajuste/form.php', 'title' => 'Ajuste de stock', 'roles' => ROLES_COMPRAS, 'menu' => 'ajuste'],
    'nota_remision' => ['file' => 'nota_remision/view.php', 'title' => 'Notas de remisión', 'roles' => ROLES_COMPRAS],
    'form_remision' => ['file' => 'nota_remision/form.php', 'title' => 'Nota de remisión', 'roles' => ROLES_COMPRAS, 'menu' => 'nota_remision'],
    'stock' => ['file' => 'stock/view.php', 'title' => 'Stock', 'roles' => ROLES_COMPRAS],

    // ---- Módulo de Compras: informes ----
    'info_producto' => ['file' => 'info_producto/view.php', 'title' => 'Informe de productos', 'roles' => ROLES_COMPRAS],
    'info_proveedores' => ['file' => 'info_proveedores/view.php', 'title' => 'Informe de proveedores', 'roles' => ROLES_COMPRAS],
    'info_u_medida' => ['file' => 'info_u_medida/view.php', 'title' => 'Informe de unidades de medida', 'roles' => ROLES_COMPRAS],
    'info_pedido' => ['file' => 'info_pedido/view.php', 'title' => 'Informe de pedidos', 'roles' => ROLES_COMPRAS],
    'info_presu' => ['file' => 'info_presu/view.php', 'title' => 'Informe de presupuestos', 'roles' => ROLES_COMPRAS],
    'info_orden' => ['file' => 'info_orden/view.php', 'title' => 'Informe de órdenes de compra', 'roles' => ROLES_COMPRAS],
    'info_facturacion_compra' => ['file' => 'info_facturacion_compra/view.php', 'title' => 'Informe de compras', 'roles' => ROLES_COMPRAS],
    'info_nota_cd' => ['file' => 'info_nota_cd/view.php', 'title' => 'Informe de notas de crédito/débito', 'roles' => ROLES_COMPRAS],
    'info_ajuste' => ['file' => 'info_ajuste/view.php', 'title' => 'Informe de ajustes', 'roles' => ROLES_COMPRAS],
    'info_notaR' => ['file' => 'info_notaR/view.php', 'title' => 'Informe de notas de remisión', 'roles' => ROLES_COMPRAS],

    // ---- Módulo de Ventas (implementado a medias; sin entrada visible en el sidebar todavía) ----
    'clientes' => ['file' => 'clientes/view.php', 'title' => 'Clientes', 'roles' => ROLES_VENTAS],
    'form_clientes' => ['file' => 'clientes/form.php', 'title' => 'Cliente', 'roles' => ROLES_VENTAS, 'menu' => 'clientes'],
    'pedido_v' => ['file' => 'pedido_v/view.php', 'title' => 'Pedidos de venta', 'roles' => ROLES_VENTAS],
    'form_pedido_v' => ['file' => 'pedido_v/form.php', 'title' => 'Pedido de venta', 'roles' => ROLES_VENTAS, 'menu' => 'pedido_v'],
    'ventas' => ['file' => 'ventas/view.php', 'title' => 'Ventas', 'roles' => ROLES_VENTAS],
    'form_ventas' => ['file' => 'ventas/form.php', 'title' => 'Venta', 'roles' => ROLES_VENTAS, 'menu' => 'ventas'],
    'info_pedido_v' => ['file' => 'info_pedido_v/view.php', 'title' => 'Informe de pedidos de venta', 'roles' => ROLES_VENTAS],
];
