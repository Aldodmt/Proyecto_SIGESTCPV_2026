<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../../config/database.php";
header('Content-Type: application/json');

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'No hay usuario logueado']);
    exit;
}

$act = $_POST['act'] ?? $_GET['act'] ?? '';
$response = ['success' => false, 'message' => 'Acción no válida'];

switch ($act) {
    case 'insert':
        $cod_compra = $_POST['cod_compra'] ?? '';
        $nro_nota = $_POST['nro_nota'] ?? '';
        $fecha = $_POST["fecha_E"] ?? '';
        $fecha_ini = $_POST["fecha_ini_tras"] ?? '';
        $cod_proveedor = $_POST['cod_proveedor'] ?? '';
        $tipo_traslado = $_POST['tipo_traslado'] ?? ''; // Tipo de traslado
        $motivo_traslado = $_POST['motivo_traslado'] ?? ''; // motivo de traslado
        $nombre_chofer = strtoupper($_POST['conductor_empre'] ?? '');
        $ruc_ci = $_POST['ruc_ci'] ?? '';
        $placa_vehiculo = strtoupper($_POST['placa'] ?? '');
        $estado = 'ACTIVO';
        $productos_json = $_POST['productos_json'] ?? '[]';
        $productos = json_decode($productos_json, true);

        if (empty($nro_nota) || empty($tipo_traslado) || empty($motivo_traslado) || empty($nro_nota)) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
            exit;
        }

        try {
            // Insertar cabecera de nota remision
            $stmt = $mysqli->prepare("INSERT INTO notaR_compra
                (cod_compra, cod_proveedor, nro_nota, fecha, fecha_ini_traslado, tipo_traslado, motivo_traslado, nom_transporte, ruc_ci_trans, placa_vehiculo, id_user, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                'iissssssssis',
                $cod_compra,
                $cod_proveedor,
                $nro_nota,
                $fecha,
                $fecha_ini,
                $tipo_traslado,
                $motivo_traslado,
                $nombre_chofer,
                $ruc_ci,
                $placa_vehiculo,
                $id_user,
                $estado
            );
            $stmt->execute();
            $id_nota = $stmt->insert_id;
            $stmt->close();

            // Insertar detalle de nota
            $stmt_det = $mysqli->prepare("INSERT INTO det_notaR_compra (id_notaR, cod_producto, cantidad)
                VALUES (?, ?, ?)");

            foreach ($productos as $p) {
                $cod_prod = intval($p['cod_producto']); // codigo del producto
                $cantidad = floatval($p['cantidad']); // cantidad


                // Insertar detalle siempre
                $stmt_det->bind_param('iii', $id_nota, $cod_prod, $cantidad);
                $stmt_det->execute();
            }

            $stmt_det->close();


            $mysqli->commit();
            echo json_encode(['success' => true, 'message' => 'Nota registrada correctamente']);

        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => 'Error al registrar nota: ' . $e->getMessage()]);
        }
        break;

    case 'cancel':
        $codigo = $_POST['codigo'] ?? null;
        if (!$codigo) {
            echo json_encode(['success' => false, 'message' => 'No se especificó la nota a anular']);
            exit;
        }
        $estado = 'ANULADO';

        // Verificar si ya está anulada
        $estadoCheck = mysqli_query($mysqli, "SELECT estado FROM notaR_compra WHERE id_notaR = $codigo");
        $estado = mysqli_fetch_assoc($estadoCheck)['estado'];
        if ($estado == 'ANULADO') {
            echo json_encode(['status' => 'error', 'message' => 'La nota ya está anulada.']);
            exit;
        }

        // Actualización que dispara el trigger de anulación
        $stmt = $mysqli->prepare("UPDATE notaR_compra SET estado='ANULADO', anulado_por=?, anulado_fecha = CURDATE(), anulado_hora = CURTIME() WHERE id_notaR=?");
        $stmt->bind_param('ii', $id_user, $codigo);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Nota anulada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al anular nota']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        break;
}
?>