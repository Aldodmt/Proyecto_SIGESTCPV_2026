<?php
session_start();
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('ajuste');   // control de acceso por rol (config/permisos.php)
require_once '../../config/database.php';

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
    exit;
}

$id_user = $_SESSION['id_user'];

if ($_GET['act'] === 'insert') {

    // Recibir datos del formulario
    $codigo = intval($_POST['codigo']);
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivo'];
    $productos_json = $_POST['productos'] ?? '[]';

    $productos = json_decode($productos_json, true);

    if (empty($productos)) {
        die("No se han agregado productos.");
    }

    // Insertar cabecera del ajuste
    $stmt = $mysqli->prepare("INSERT INTO ajuste_com (id_ajuste, fecha_ajuste, motivo, estado, id_user) VALUES (?, ?, ?, 'ACTIVO', ?)");
    $stmt->bind_param("issi", $codigo, $fecha, $motivo, $id_user);
    $stmt->execute();
    $stmt->close();

    // Recorrer productos y guardar detalle y actualizar stock
    foreach ($productos as $p) {
        $id_producto = intval($p['id']);
        $cantidad_ajustada = floatval($p['cantidad']);

        // Traer cantidad anterior desde stock
        $query = $mysqli->prepare("SELECT cantidad FROM stock_prod WHERE cod_producto = ?");
        $query->bind_param("i", $id_producto);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        $cantidad_anterior = $row['cantidad'] ?? 0;
        $query->close();

        // Insertar detalle
        $stmt_det = $mysqli->prepare("INSERT INTO det_ajuste (id_ajuste, cod_producto, cantidad_ajustada, cantidad_anterior) VALUES (?, ?, ?, ?)");
        $stmt_det->bind_param("iiii", $codigo, $id_producto, $cantidad_ajustada, $cantidad_anterior);
        $stmt_det->execute();
        $stmt_det->close();

        // Actualizar stock
        $nueva_cantidad = max($cantidad_anterior - $cantidad_ajustada, 0);
        $stmt_stock = $mysqli->prepare("UPDATE stock_prod SET cantidad = ? WHERE cod_producto = ?");
        $stmt_stock->bind_param("ii", $nueva_cantidad, $id_producto);
        $stmt_stock->execute();
        $stmt_stock->close();
    }

    echo json_encode(['success' => true, 'message' => 'Ajuste guardado correctamente']);
    exit;
}

// Anular ajuste
if ($_GET['act'] === 'anular' && isset($_GET['id_ajuste'])) {
    $id_ajuste = intval($_GET['id_ajuste']);

    // El estado cambia primero y de forma atómica (ACTIVO -> ANULADO). El stock se devuelve solo si se ganó el cambio.
    // Antes se comparaba con 'anulado' en minúscula (nunca coincidía con 'ANULADO') y por eso anular dos veces
    // devolvía el stock dos veces.
    require_once __DIR__ . '/../../config/compras.php';
    try {
        $r = flujo_transicion('ajuste', $id_ajuste, 'cancel', function () use ($mysqli, $id_ajuste) {
            $q = $mysqli->prepare("SELECT cod_producto, cantidad_ajustada FROM det_ajuste WHERE id_ajuste = ?");
            $q->bind_param("i", $id_ajuste);
            $q->execute();
            $det = $q->get_result()->fetch_all(MYSQLI_ASSOC);
            $q->close();
            $u = $mysqli->prepare("UPDATE stock_prod SET cantidad = cantidad + ? WHERE cod_producto = ?");
            foreach ($det as $d) {
                $cant = (int) $d['cantidad_ajustada'];
                $cod = (int) $d['cod_producto'];
                $u->bind_param("ii", $cant, $cod);
                $u->execute();
            }
            $u->close();
        });
    } catch (Throwable $e) {
        $r = ['ok' => false, 'mensaje' => 'No se pudo anular el ajuste: ' . $e->getMessage()];
    }

    // 12 = éxito con mensaje, 9 = error con mensaje (config/ui.php, ui_alertas)
    $url = '../../main.php?module=ajuste&alert=' . ($r['ok'] ? 12 : 9)
        . '&msg=' . urlencode($r['ok'] ? "Ajuste $id_ajuste anulado. El stock fue devuelto." : $r['mensaje']);
    header("Location: $url");
    exit;
}
