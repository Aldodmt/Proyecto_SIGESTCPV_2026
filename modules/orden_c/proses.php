<?php
session_start();
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('orden_c', null, 'json');   // 1.ª capa: permiso del rol (config/permisos.php)
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/compras.php';

$id_user = (int) ($_SESSION['id_user'] ?? 0);
if (!$id_user) {
    flujo_error_json('No hay sesión activa');
}

$accion = $_POST['act'] ?? $_GET['act'] ?? '';

try {
    // =========================
    // INSERT  (solo a partir de un presupuesto APROBADO)
    // =========================
    if ($accion === 'insert') {
        $id_presupuesto = (int) ($_POST['id_presupuesto'] ?? 0);

        // La orden nace de un presupuesto: ya no existe la orden "manual" con proveedor y productos sueltos.
        if ($id_presupuesto <= 0) {
            flujo_error_json('Debe seleccionar un presupuesto aprobado para generar la orden de compra.');
        }
        // Capa de estado: presupuesto APROBADO y sin otra orden vigente
        if (($error = flujo_validar_origen('presupuesto', $id_presupuesto)) !== null) {
            flujo_error_json($error);
        }

        // Proveedor y productos/cantidades/precios: salen del PRESUPUESTO en la base, no del navegador
        $s = $mysqli->prepare("SELECT cod_proveedor FROM presupuesto WHERE id_presupuesto = ? LIMIT 1");
        $s->bind_param('i', $id_presupuesto);
        $s->execute();
        $presu = $s->get_result()->fetch_assoc();
        $s->close();
        $items = flujo_detalle_presupuesto($id_presupuesto);
        if (!$presu || !$items) {
            flujo_error_json('El presupuesto seleccionado no tiene productos.');
        }
        $codigo_proveedor = (int) $presu['cod_proveedor'];

        $codigo = flujo_siguiente_codigo('orden_compra', 'id_orden_comp');
        try {
            $s = $mysqli->prepare("INSERT INTO orden_compra (id_orden_comp, fecha, estado, hora, id_user, id_presupuesto, cod_proveedor)
                                   VALUES (?, CURDATE(), 'PENDIENTE', CURTIME(), ?, ?, ?)");
            $s->bind_param('iiii', $codigo, $id_user, $id_presupuesto, $codigo_proveedor);
            $s->execute();
            $s->close();

            $s = $mysqli->prepare("INSERT INTO detalle_orden_comp (id_orden_comp, cod_producto, precio_unit, cantidad) VALUES (?, ?, ?, ?)");
            foreach ($items as $it) {
                $cod = (int) $it['cod_producto'];
                $precio = (float) $it['precio_unit'];
                $cant = (float) $it['cantidad'];
                $s->bind_param('iidd', $codigo, $cod, $precio, $cant);
                $s->execute();
            }
            $s->close();
        } catch (Throwable $e) {
            $mysqli->query("DELETE FROM detalle_orden_comp WHERE id_orden_comp = $codigo");
            $mysqli->query("DELETE FROM orden_compra WHERE id_orden_comp = $codigo");
            flujo_error_json('Error al guardar la orden de compra: ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Orden de compra guardada correctamente', 'estado' => 'PENDIENTE', 'codigo' => $codigo]);
        exit;
    }

    // =========================
    // APROBAR (confirm) / ANULAR (cancel)
    // =========================
    if ($accion === 'confirm' || $accion === 'cancel') {
        $codigo = (int) ($_POST['codigo'] ?? 0);
        if ($codigo <= 0) {
            flujo_error_json('Código de orden inválido');
        }
        // Al aprobar la orden, el presupuesto de origen queda ORDENADO; al anularla, vuelve a APROBADO y disponible.
        $mov = $accion === 'confirm' ? 'consumir' : 'liberar';
        flujo_responder(flujo_transicion('orden_c', $codigo, $accion, fn() => flujo_propagar('orden_c', $codigo, $mov)));
    }

    flujo_error_json('Acción no reconocida');
} catch (Throwable $e) {
    http_response_code(500);
    flujo_error_json('Error interno: ' . $e->getMessage());
}
