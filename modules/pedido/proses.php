<?php
session_start();
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('pedido', null, 'json');   // 1.ª capa: ¿este rol puede hacer esta acción? (config/permisos.php)
require_once "../../config/database.php";
require_once __DIR__ . '/../../config/compras.php';

header('Content-Type: application/json');

$id_user = (int) ($_SESSION['id_user'] ?? 0);
if (!$id_user) {
    flujo_error_json('No se detectó usuario logueado.');
}

// 2.ª capa: ¿el pedido está en un estado desde el que esta acción es válida? (flujo_transicion, config/compras.php)
try {
    $act = $_POST['act'] ?? '';
    $codigo = (int) ($_POST['codigo'] ?? 0);

    switch ($act) {
        // ====== GUARDAR PEDIDO: BORRADOR / MODIFICANDO -> PENDIENTE ======
        case 'save':
            if ($codigo <= 0) {
                flujo_error_json('Código de pedido inválido.');
            }
            // Primero el estado (si no corresponde, flujo_transicion lo explica); recién después la regla de contenido:
            // debe tener al menos un producto. Se cuenta en el servidor: lo que muestre la pantalla no cuenta.
            if (in_array(flujo_estado('pedido', $codigo), COMPRAS_FLUJO['pedido']['transiciones']['save']['desde'], true)) {
                $s = $mysqli->prepare("SELECT COUNT(*) FROM det_pedido WHERE id_pedido = ?");
                $s->bind_param('i', $codigo);
                $s->execute();
                $items = (int) $s->get_result()->fetch_row()[0];
                $s->close();
                if ($items === 0) {
                    flujo_error_json('Debe seleccionar al menos un producto antes de guardar el pedido.');
                }
            }
            // Al guardar un borrador, la fecha y hora del pedido pasan a ser las del momento de guardarlo
            // (el borrador pudo abrirse días antes). Al guardar desde MODIFICANDO se conservan las originales.
            flujo_responder(flujo_transicion('pedido', $codigo, 'save', function ($previo) use ($mysqli, $codigo) {
                if ($previo === 'BORRADOR') {
                    $u = $mysqli->prepare("UPDATE pedido SET fecha = CURDATE(), hora = CURTIME() WHERE id_pedido = ?");
                    $u->bind_param('i', $codigo);
                    $u->execute();
                    $u->close();
                }
            }), ['redirect' => '?module=pedido']);

        // ====== CONFIRMAR / MODIFICAR / ANULAR ======
        case 'confirm':
        case 'modify':
            flujo_responder(flujo_transicion('pedido', $codigo, $act));

        case 'cancel':
            if (empty($_POST['confirm'])) {
                flujo_error_json('Se requiere confirmación para anular el pedido');
            }
            flujo_responder(flujo_transicion('pedido', $codigo, 'cancel'));

        // ====== DESCARTAR EL BORRADOR (botón Salir, tras confirmar) ======
        case 'clear_borrador':
            // Solo el BORRADOR indicado y solo si es del usuario. Nunca toca pedidos ya guardados ni borradores ajenos.
            $s = $mysqli->prepare("DELETE dp FROM det_pedido dp JOIN pedido p ON dp.id_pedido = p.id_pedido
                                    WHERE UPPER(p.estado) = 'BORRADOR' AND p.id_user = ? AND p.id_pedido = ?");
            $s->bind_param('ii', $id_user, $codigo);
            $s->execute();
            $s->close();
            $s = $mysqli->prepare("DELETE FROM pedido WHERE UPPER(estado) = 'BORRADOR' AND id_user = ? AND id_pedido = ?");
            $s->bind_param('ii', $id_user, $codigo);
            $s->execute();
            $borrados = $s->affected_rows;
            $s->close();
            echo json_encode($borrados === 1
                ? ['success' => true, 'message' => 'Borrador descartado']
                : ['success' => false, 'message' => 'No existe un borrador propio con ese código (quizás ya fue guardado o descartado).']);
            exit();

        default:
            flujo_error_json('Acción desconocida: ' . $act);
    }
} catch (Throwable $e) {
    // mysqli lanza excepciones desde PHP 8.1: se responde JSON para que el formulario muestre el error
    http_response_code(500);
    flujo_error_json('Error interno: ' . $e->getMessage());
}
