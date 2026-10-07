<?php
// Solicitud de recuperación: se identifica al usuario por su nombre de usuario y, si tiene un
// correo asociado, se le envía un enlace con token de un solo uso (30 min).
require_once "../../config/database.php";
require_once "../../config/auth.php";

$inicio = microtime(true);
$ms = fn() => (int) ((microtime(true) - $inicio) * 1000);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['recuperar'])) {
    header("Location: recuperar.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
if ($username === '' || mb_strlen($username) > 150) {
    header("Location: recuperar.php?alert=2"); // usuario vacío o inválido
    exit();
}

$stmt = $mysqli->prepare("SELECT id_user, username, email FROM usuarios WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    auth_log($mysqli, null, $username, 0, 'RECUPERACION', 'Solicitud con usuario inexistente', $ms());
    header("Location: recuperar.php?alert=4");
    exit();
}

$id = (int) $usuario['id_user'];
$email = trim((string) $usuario['email']);

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    auth_log($mysqli, $id, $usuario['username'], 0, 'RECUPERACION', 'Usuario sin correo asociado', $ms());
    header("Location: recuperar.php?alert=5");
    exit();
}

$token = bin2hex(random_bytes(32));
$hash = hash('sha256', $token);
$ip = auth_ip();
$minutos = AUTH_TOKEN_MINUTOS;

// Invalida los enlaces anteriores y guarda el nuevo (solo su hash)
$mysqli->query("UPDATE password_resets SET usado = 1 WHERE id_user = $id AND usado = 0");
$ins = $mysqli->prepare("INSERT INTO password_resets (id_user, token_hash, expira, usado, creado, ip)
                         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL $minutos MINUTE), 0, NOW(), ?)");
$ins->bind_param('iss', $id, $hash, $ip);
$ins->execute();
$ins->close();

$enlace = auth_base_url() . '/modules/recuperar/nueva_contra.php?token=' . $token;

// Correo parcialmente oculto para mostrarlo en pantalla (ej.: al***@gmail.com)
[$local, $dominio] = explode('@', $email, 2);
$oculto = mb_substr($local, 0, 2) . '***@' . $dominio;

try {
    $mail = auth_mailer();
    if (!$mail) {
        throw new Exception('Correo no configurado');
    }
    $mail->addAddress($email);
    $mail->Subject = 'Recuperación de contraseña - Sysweb';
    $mail->Body = "
        <h1>Recuperación de contraseña</h1>
        <p>Hola <strong>" . htmlspecialchars($usuario['username'], ENT_QUOTES, 'UTF-8') . "</strong>, recibimos una solicitud para restablecer tu contraseña.</p>
        <p><a href='$enlace' target='_blank'>Restablecer contraseña</a></p>
        <p>Este enlace vence en $minutos minutos y solo puede usarse una vez.</p>
        <p>Si no hiciste esta solicitud, ignora este mensaje: tu contraseña seguirá igual.</p>";
    $mail->send();
    auth_log($mysqli, $id, $usuario['username'], 0, 'RECUPERACION', 'Enlace de recuperación enviado', $ms());
    header("Location: recuperar.php?alert=1&m=" . urlencode($oculto));
} catch (Throwable $e) {
    auth_log($mysqli, $id, $usuario['username'], 0, 'RECUPERACION', mb_substr('Error al enviar el correo: ' . ($e->getMessage() ?: 'desconocido'), 0, 120), $ms());
    header("Location: recuperar.php?alert=3");
}
exit();
