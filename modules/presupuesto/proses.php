<?php
session_start();
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('presupuesto', null, 'json');   // 1.ª capa: permiso del rol (config/permisos.php)
header('Content-Type: application/json'); // Forzar JSON
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/compras.php';

// Verificar sesión
$id_user = (int) ($_SESSION['id_user'] ?? 0);
if (!$id_user) {
    flujo_error_json('No se detectó usuario logueado.');
}

$accion = $_POST['act'] ?? $_GET['act'] ?? '';

try {
    // =========================
    // INSERTAR PRESUPUESTO  (solo a partir de un pedido CONFIRMADO)
    // =========================
    if ($accion === 'insert') {
        $id_pedido = (int) ($_POST['id_pedido'] ?? 0);
        $codigo_proveedor = (int) ($_POST['codigo_proveedor'] ?? 0);
        $fecha_v = (string) ($_POST['fecha_v'] ?? '');

        // El presupuesto nace de un pedido: sin pedido no hay presupuesto.
        if ($id_pedido <= 0) {
            flujo_error_json('Debe seleccionar un pedido confirmado para generar el presupuesto.');
        }
        if ($codigo_proveedor <= 0) {
            flujo_error_json('Debe seleccionar un proveedor.');
        }
        $f = DateTime::createFromFormat('Y-m-d', $fecha_v);
        if (!$f || $f->format('Y-m-d') !== $fecha_v) {
            flujo_error_json('La fecha de vencimiento no es válida.');
        }
        if ($fecha_v <= date('Y-m-d')) {
            flujo_error_json('La fecha de vencimiento debe ser mayor que la fecha de emisión');
        }

        $s = $mysqli->prepare("SELECT 1 FROM proveedor WHERE cod_proveedor = ?");
        $s->bind_param('i', $codigo_proveedor);
        $s->execute();
        $existe = $s->get_result()->num_rows > 0;
        $s->close();
        if (!$existe) {
            flujo_error_json('El proveedor seleccionado no existe.');
        }

        // Capa de estado: el pedido debe estar CONFIRMADO y no tener otro presupuesto vigente
        if (($error = flujo_validar_origen('pedido', $id_pedido)) !== null) {
            flujo_error_json($error);
        }

        // Productos y cantidades: salen del PEDIDO (base de datos). Del navegador solo se aceptan los precios.
        $items = flujo_detalle_pedido($id_pedido);
        if (!$items) {
            flujo_error_json('El pedido seleccionado no tiene productos.');
        }
        $enviados = json_decode((string) ($_POST['productos_json'] ?? ''), true);
        $precios = [];
        foreach (is_array($enviados) ? $enviados : [] as $p) {
            $precios[(int) ($p['codigo_producto'] ?? 0)] = (float) ($p['precio_unitario'] ?? 0);
        }
        foreach ($items as $it) {
            if (($precios[(int) $it['cod_producto']] ?? 0) <= 0) {
                flujo_error_json('Todos los productos deben tener un precio unitario mayor a 0 (falta: ' . $it['p_descrip'] . ').');
            }
        }

        $codigo = flujo_siguiente_codigo('presupuesto', 'id_presupuesto');   // código reservado en el servidor
        try {
            $s = $mysqli->prepare("INSERT INTO presupuesto (id_presupuesto, fecha_presu, fecha_vencimiento, cod_proveedor, estado, id_pedido, id_user)
                                   VALUES (?, CURDATE(), ?, ?, 'PENDIENTE', ?, ?)");
            $s->bind_param('isiii', $codigo, $fecha_v, $codigo_proveedor, $id_pedido, $id_user);
            $s->execute();
            $s->close();

            $s = $mysqli->prepare("INSERT INTO det_presu (id_presupuesto, cod_producto, cantidad, precio_unit) VALUES (?, ?, ?, ?)");
            foreach ($items as $it) {
                $cod = (int) $it['cod_producto'];
                $cant = (float) $it['cantidad'];
                $precio = $precios[$cod];
                $s->bind_param('iidd', $codigo, $cod, $cant, $precio);
                $s->execute();
            }
            $s->close();
        } catch (Throwable $e) {
            // MyISAM no tiene ROLLBACK: se deshace a mano lo que alcanzó a insertarse
            $mysqli->query("DELETE FROM det_presu WHERE id_presupuesto = $codigo");
            $mysqli->query("DELETE FROM presupuesto WHERE id_presupuesto = $codigo");
            flujo_error_json('Error al guardar el presupuesto: ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Presupuesto guardado correctamente', 'estado' => 'PENDIENTE', 'codigo' => $codigo]);
        exit;
    }

    // =========================
    // APROBAR (confirm) / ANULAR (cancel): la validez de cada acción la decide flujo_transicion según el estado
    // =========================
    if ($accion === 'confirm' || $accion === 'cancel') {
        $codigo = (int) ($_POST['codigo'] ?? 0);
        if ($codigo <= 0) {
            flujo_error_json('Código de presupuesto inválido');
        }
        // Al aprobar, el pedido de origen queda PRESUPUESTADO (ya siguió el flujo y no admite más acciones);
        // al anular el presupuesto, el pedido vuelve a quedar CONFIRMADO y disponible. Si el pedido no está en el
        // estado que corresponde, flujo_propagar() lanza una excepción y el cambio del presupuesto se deshace.
        $mov = $accion === 'confirm' ? 'consumir' : 'liberar';
        flujo_responder(flujo_transicion('presupuesto', $codigo, $accion, fn() => flujo_propagar('presupuesto', $codigo, $mov)));
    }

    flujo_error_json('Acción no reconocida');
} catch (Throwable $e) {
    http_response_code(500);
    flujo_error_json('Error interno: ' . $e->getMessage());
}
