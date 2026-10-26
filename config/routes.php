<?php
// Registro único de rutas: ?module=<clave> -> archivo a incluir.
//
// Dos tipos de entrada:
//   PÁGINA  ($p) : listado o pantalla principal. Es lo que se ve en el sidebar y lo que recibe permisos.
//                  [archivo, título, módulo, acciones aplicables]
//   FORMULARIO ($f): pantalla de alta/edición/detalle. Cuelga de una página con 'menu' => clave_de_la_página.
//
// QUIÉN puede entrar NO se define aquí: lo deciden los permisos de la base de datos
// (Seguridad > Permisos). Al agregar una pantalla nueva basta con registrarla aquí y pulsar
// "Sincronizar con el sistema" en Seguridad > Páginas y módulos: queda sin permisos para nadie hasta que se asignen.
//
// Acciones: leer, insertar, editar, borrar. Por defecto una página admite las cuatro.

$p = fn(string $file, string $title, string $modulo, string $acciones = 'leer,insertar,editar,borrar') =>
    ['file' => $file, 'title' => $title, 'modulo' => $modulo, 'acciones' => $acciones];
$f = fn(string $file, string $title, string $pagina) =>
    ['file' => $file, 'title' => $title, 'menu' => $pagina];

return [
    // ---- General ----
    'start' => $p('start/view.php', 'Inicio', 'general', 'leer'),
    'perfil' => $p('perfil/view.php', 'Mi perfil', 'general', 'leer,editar'),
    'form_perfil' => $f('perfil/form.php', 'Editar perfil', 'perfil'),
    'password' => $p('password/view.php', 'Cambiar contraseña', 'general', 'leer,editar'),

    // ---- Referenciales generales ----
    'departamento' => $p('departamento/view.php', 'Departamentos', 'referenciales'),
    'form_departamento' => $f('departamento/form.php', 'Departamento', 'departamento'),
    'ciudad' => $p('ciudad/view.php', 'Ciudades', 'referenciales'),
    'form_ciudad' => $f('ciudad/form.php', 'Ciudad', 'ciudad'),
    'nacionalidad' => $p('nacionalidad/view.php', 'Nacionalidades', 'referenciales'),
    'form_nacionalidad' => $f('nacionalidad/form.php', 'Nacionalidad', 'nacionalidad'),
    'genero' => $p('genero/view.php', 'Géneros', 'referenciales'),
    'form_genero' => $f('genero/form.php', 'Género', 'genero'),

    // ---- Personal ----
    'persona' => $p('persona/view.php', 'Personas', 'personal'),
    'form_persona' => $f('persona/form.php', 'Persona', 'persona'),
    'funcionario' => $p('funcionario/view.php', 'Funcionarios', 'personal'),
    'form_funcionario' => $f('funcionario/form.php', 'Funcionario', 'funcionario'),
    'cargo' => $p('cargo/view.php', 'Cargos', 'personal'),
    'form_cargo' => $f('cargo/form.php', 'Cargo', 'cargo'),

    // ---- Compras: referenciales ----
    'deposito' => $p('deposito/view.php', 'Depósitos', 'compras'),
    'form_deposito' => $f('deposito/form.php', 'Depósito', 'deposito'),
    'proveedor' => $p('proveedor/view.php', 'Proveedores', 'compras'),
    'form_proveedor' => $f('proveedor/form.php', 'Proveedor', 'proveedor'),
    'producto' => $p('producto/view.php', 'Productos', 'compras'),
    'form_producto' => $f('producto/form.php', 'Producto', 'producto'),
    'tipo_producto' => $p('tipo_producto/view.php', 'Tipos de producto', 'compras'),
    'form_tipo_producto' => $f('tipo_producto/form.php', 'Tipo de producto', 'tipo_producto'),
    'u_medida' => $p('u_medida/view.php', 'Unidades de medida', 'compras'),
    'form_u_medida' => $f('u_medida/form.php', 'Unidad de medida', 'u_medida'),

    // ---- Compras: movimientos ----
    'pedido' => $p('pedido/view.php', 'Pedidos', 'compras'),
    'form_pedido' => $f('pedido/form.php', 'Pedido', 'pedido'),
    'presupuesto' => $p('presupuesto/view.php', 'Presupuestos', 'compras'),
    'form_presupuesto' => $f('presupuesto/form.php', 'Presupuesto', 'presupuesto'),
    'orden_c' => $p('orden_c/view.php', 'Órdenes de compra', 'compras'),
    'form_orden_c' => $f('orden_c/form.php', 'Orden de compra', 'orden_c'),
    'compra' => $p('compras/view.php', 'Compras', 'compras'),
    'form_compra' => $f('compras/form.php', 'Compra', 'compra'),
    'cuenta' => $p('cuenta/view.php', 'Cuentas a pagar', 'compras', 'leer,editar,borrar'),
    'nota_c_d' => $p('nota_c_d/view.php', 'Notas de crédito/débito', 'compras'),
    'form_nota_c_d' => $f('nota_c_d/form.php', 'Nota de crédito/débito', 'nota_c_d'),
    'ajuste' => $p('ajuste/view.php', 'Ajustes de stock', 'compras'),
    'form_ajuste' => $f('ajuste/form.php', 'Ajuste de stock', 'ajuste'),
    'nota_remision' => $p('nota_remision/view.php', 'Notas de remisión', 'compras'),
    'form_remision' => $f('nota_remision/form.php', 'Nota de remisión', 'nota_remision'),
    'stock' => $p('stock/view.php', 'Stock', 'compras', 'leer'),

    // ---- Compras: informes (solo lectura) ----
    'info_producto' => $p('info_producto/view.php', 'Informe de productos', 'compras', 'leer'),
    'info_proveedores' => $p('info_proveedores/view.php', 'Informe de proveedores', 'compras', 'leer'),
    'info_u_medida' => $p('info_u_medida/view.php', 'Informe de unidades de medida', 'compras', 'leer'),
    'info_pedido' => $p('info_pedido/view.php', 'Informe de pedidos', 'compras', 'leer'),
    'info_presu' => $p('info_presu/view.php', 'Informe de presupuestos', 'compras', 'leer'),
    'info_orden' => $p('info_orden/view.php', 'Informe de órdenes de compra', 'compras', 'leer'),
    'info_facturacion_compra' => $p('info_facturacion_compra/view.php', 'Informe de compras', 'compras', 'leer'),
    'info_nota_cd' => $p('info_nota_cd/view.php', 'Informe de notas de crédito/débito', 'compras', 'leer'),
    'info_ajuste' => $p('info_ajuste/view.php', 'Informe de ajustes', 'compras', 'leer'),
    'info_notaR' => $p('info_notaR/view.php', 'Informe de notas de remisión', 'compras', 'leer'),

    // ---- Ventas (implementado a medias; sin entrada visible en el sidebar todavía) ----
    'clientes' => $p('clientes/view.php', 'Clientes', 'ventas'),
    'form_clientes' => $f('clientes/form.php', 'Cliente', 'clientes'),
    'pedido_v' => $p('pedido_v/view.php', 'Pedidos de venta', 'ventas'),
    'form_pedido_v' => $f('pedido_v/form.php', 'Pedido de venta', 'pedido_v'),
    'ventas' => $p('ventas/view.php', 'Ventas', 'ventas'),
    'form_ventas' => $f('ventas/form.php', 'Venta', 'ventas'),
    'info_pedido_v' => $p('info_pedido_v/view.php', 'Informe de pedidos de venta', 'ventas', 'leer'),

    // ---- Seguridad ----
    'user' => $p('user/view.php', 'Usuarios', 'seguridad'),
    'form_user' => $f('user/form.php', 'Usuario', 'user'),
    'rol' => $p('rol/view.php', 'Roles', 'seguridad'),
    'form_rol' => $f('rol/form.php', 'Rol', 'rol'),
    'permiso' => $p('permiso/view.php', 'Permisos por rol', 'seguridad', 'leer,editar'),
    'pagina' => $p('pagina/view.php', 'Páginas y módulos', 'seguridad', 'leer,editar'),
    'form_pagina' => $f('pagina/form.php', 'Página', 'pagina'),
    'accesos' => $p('accesos/view.php', 'Registro de accesos', 'seguridad', 'leer'),
];
