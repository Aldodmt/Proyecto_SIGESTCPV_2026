<?php
session_start();
require_once "../../config/database.php";

// Respuesta JSON siempre
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no válida'];

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'No se detectó usuario logueado.']);
    exit();
}

try {
    switch ($_POST['act']) {

        // ====== GUARDAR PEDIDO ======
        case 'save':
            $codigo = intval($_POST['codigo']); // id_pedido que ya existe

            // VALIDAR que haya al menos 1 producto en el pedido
            $check = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM det_pedido WHERE id_pedido=$codigo");
            $row = mysqli_fetch_assoc($check);

            if ($row['total'] == 0) {
                $response = [
                    'success' => false,
                    'message' => 'Debe seleccionar al menos un producto antes de guardar el pedido.'
                ];
                break; // salir del case sin cambiar el estado
            }

            $estado = 'PENDIENTE'; // Cambiamos el estado al guardar
            $update = mysqli_query($mysqli, "UPDATE pedido SET estado='$estado' WHERE id_pedido=$codigo");

            if (!$update) {
                throw new Exception("Error al guardar: " . mysqli_error($mysqli));
            }

            // Aquí agregamos la URL de redirección
            $response = [
                'success' => true,
                'message' => 'Pedido guardado exitosamente',
                'estado' => $estado,
                'redirect' => '?module=pedido' // <-- URL a la interfaz de pedidos
            ];
            break;

        // ====== CONFIRMAR PEDIDO ======
        case 'confirm':
            $codigo = intval($_POST['codigo']);
            $estado = 'CONFIRMADO';
            $update = mysqli_query($mysqli, "UPDATE pedido SET estado='$estado' WHERE id_pedido=$codigo");
            $response = ['success' => (bool) $update, 'message' => $update ? 'Pedido confirmado' : mysqli_error($mysqli), 'estado' => $estado];
            break;

        // ====== MODIFICAR PEDIDO ======
        case 'modify':
            $codigo = intval($_POST['codigo']);
            $estado = 'MODIFICANDO';
            $update = mysqli_query($mysqli, "UPDATE pedido SET estado='$estado' WHERE id_pedido=$codigo");
            $response = ['success' => (bool) $update, 'message' => $update ? 'Pedido en modificación' : mysqli_error($mysqli), 'estado' => $estado];
            break;

        // ====== ANULAR PEDIDO ======
        case 'cancel':
            $codigo = intval($_POST['codigo']);

            if (empty($_POST['confirm'])) {
                // Si no se confirmó explícitamente
                echo json_encode([
                    'success' => false,
                    'message' => 'Se requiere confirmación para anular el pedido'
                ]);
                exit();
            }

            $estado = 'ANULADO';
            $update = mysqli_query($mysqli, "UPDATE pedido SET estado = '$estado', 
                anulado_por = $id_user, 
                anulado_fecha = CURDATE(), 
                anulado_hora = CURTIME() WHERE id_pedido=$codigo");
            $response = ['success' => (bool) $update, 'message' => $update ? 'Pedido anulado' : mysqli_error($mysqli), 'estado' => $estado];
            break;

        // ====== LIMPIAR BORRADORES ======
        case 'clear_borrador':
            // Borrar todos los pedidos BORRADOR de este usuario
            mysqli_begin_transaction($mysqli);
            try {
                $del_det = mysqli_query($mysqli, "DELETE dp FROM det_pedido dp JOIN pedido p ON dp.id_pedido=p.id_pedido WHERE p.estado='BORRADOR' AND p.id_user=$id_user");
                $del_ped = mysqli_query($mysqli, "DELETE FROM pedido WHERE estado='BORRADOR' AND id_user=$id_user");

                if (!$del_det || !$del_ped)
                    throw new Exception("Error al limpiar borradores");

                mysqli_commit($mysqli);
                $response = ['success' => true, 'message' => 'Borradores eliminados'];
            } catch (Exception $e) {
                mysqli_rollback($mysqli);
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
            break;

        default:
            throw new Exception("Acción desconocida: " . $_POST['act']);
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit();
