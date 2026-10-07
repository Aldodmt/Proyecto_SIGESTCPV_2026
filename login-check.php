<?php
require_once "config/database.php";
require_once "config/auth.php";

$inicio = microtime(true);
$es_ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

// Responde en JSON (login por AJAX con validación en tiempo real) o redirige (sin JavaScript).
function responder(int $code, int $left = 0, ?string $redirect = null): void
{
    global $es_ajax;
    if ($es_ajax) {
        [$nivel, $texto] = $redirect !== null ? ['success', 'Acceso correcto. Ingresando...'] : auth_mensaje($code, $left);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => $redirect !== null,
            'nivel' => $nivel,
            'mensaje' => $texto,
            'bloqueado' => $code === 6,
            'redirect' => $redirect,
        ]);
    } else {
        header($redirect !== null ? "Location: $redirect" : "Location: index.php?alert=$code&left=$left");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    responder(3);
}
if (mb_strlen($username) > 150 || mb_strlen($password) > 128) {
    auth_log($mysqli, null, $username, mb_strlen($password), 'FALLIDO', 'Formato inválido', (int) ((microtime(true) - $inicio) * 1000));
    responder(1);
}

$stmt = $mysqli->prepare("SELECT id_user, username, name_user, password, email, permisos_acceso, status, intentos_fallidos FROM usuarios WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$ms = fn() => (int) ((microtime(true) - $inicio) * 1000);
$largo = mb_strlen($password);

// Usuario inexistente
if (!$data) {
    auth_log($mysqli, null, $username, $largo, 'FALLIDO', 'Usuario inexistente', $ms());
    responder(7);
}

// Cuenta ya bloqueada: no se evalúa la contraseña
if ($data['status'] === 'bloqueado') {
    auth_log($mysqli, (int) $data['id_user'], $username, $largo, 'BLOQUEADO', 'Intento sobre cuenta bloqueada', $ms());
    responder(6);
}

// Contraseña correcta
if (auth_verificar($password, $data['password'])) {
    $id = (int) $data['id_user'];

    // Migra MD5 antiguo a bcrypt de forma transparente
    if (auth_necesita_rehash($data['password'])) {
        $nuevo = auth_hash($password);
        $up = $mysqli->prepare("UPDATE usuarios SET password = ?, intentos_fallidos = 0 WHERE id_user = ?");
        $up->bind_param('si', $nuevo, $id);
    } else {
        $up = $mysqli->prepare("UPDATE usuarios SET intentos_fallidos = 0 WHERE id_user = ?");
        $up->bind_param('i', $id);
    }
    $up->execute();
    $up->close();

    ini_set('session.cookie_httponly', '1');
    session_start();
    session_regenerate_id(true);
    $_SESSION['id_user'] = $id;
    $_SESSION['username'] = $data['username'];
    $_SESSION['name_user'] = $data['name_user'];
    $_SESSION['permisos_acceso'] = $data['permisos_acceso'];

    auth_log($mysqli, $id, $username, $largo, 'EXITOSO', 'Inicio de sesión', $ms());
    responder(0, 0, 'main.php?module=start');
}

// Contraseña incorrecta: suma un intento y bloquea al llegar al máximo
$id = (int) $data['id_user'];
$up = $mysqli->prepare("UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id_user = ?");
$up->bind_param('i', $id);
$up->execute();
$up->close();

$sel = $mysqli->prepare("SELECT intentos_fallidos FROM usuarios WHERE id_user = ?");
$sel->bind_param('i', $id);
$sel->execute();
$intentos = (int) $sel->get_result()->fetch_assoc()['intentos_fallidos'];
$sel->close();

if ($intentos >= AUTH_MAX_INTENTOS) {
    $bl = $mysqli->prepare("UPDATE usuarios SET status = 'bloqueado', bloqueado_fecha = NOW() WHERE id_user = ?");
    $bl->bind_param('i', $id);
    $bl->execute();
    $bl->close();

    auth_log($mysqli, $id, $username, $largo, 'FALLIDO', "Contraseña incorrecta (intento $intentos): cuenta bloqueada", $ms());
    auth_alerta_bloqueo($mysqli, $data, auth_ip());
    responder(6);
}

auth_log($mysqli, $id, $username, $largo, 'FALLIDO', "Contraseña incorrecta (intento $intentos de " . AUTH_MAX_INTENTOS . ")", $ms());
responder(8, AUTH_MAX_INTENTOS - $intentos);
