<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('nota_remision', null, 'json');   // control de acceso por rol (config/permisos.php)
require_once "../../config/database.php";
require_once __DIR__ . '/../../config/compras.php';
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

        // Capa de estado: la remisión solo puede emitirse sobre una compra ACTIVO
        $estado_compra = flujo_estado('compra', (int) $cod_compra);
        if ($estado_compra !== 'ACTIVO') {
            echo json_encode(['success' => false, 'message' => 'Solo se puede emitir una remisión sobre una compra ACTIVO (estado actual: ' . ($estado_compra ?? 'inexistente') . ').']);
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
        $codigo = (int) ($_POST['codigo'] ?? 0);
        if ($codigo <= 0) {
            echo json_encode(['success' => false, 'message' => 'No se especificó la nota a anular']);
            exit;
        }
        // Cambio de estado atómico ACTIVO -> ANULADO (config/compras.php): una nota ya anulada no se vuelve a anular
        try {
            $r = flujo_transicion('nota_remision', $codigo, 'cancel');
        } catch (Throwable $e) {
            $r = ['ok' => false, 'mensaje' => 'Error al anular nota: ' . $e->getMessage(), 'estado' => null];
        }
        echo json_encode(['success' => $r['ok'], 'message' => $r['mensaje'], 'estado' => $r['estado']], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        break;
}
?>