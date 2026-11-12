<?php
require_once __DIR__ . '/../config/permisos.php';
guard_endpoint('presupuesto', 'leer', 'fragmento');   // 1.ª capa: permiso del rol (config/permisos.php)
require_once '../config/database.php';
require_once __DIR__ . '/../config/compras.php';

// Ventana de selección: una fila por documento disponible (estado "listo" y sin documento vigente que dependa de él).
// El detalle de productos se carga recién al seleccionar (ajax/agregar_*.php).
flujo_render_selector('pedido', 'seleccionarPedido');
