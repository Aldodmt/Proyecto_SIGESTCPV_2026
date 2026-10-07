<?php
// Utilidades de autenticación compartidas: login, recuperación y cambio de contraseña.

const AUTH_MAX_INTENTOS = 3;
const AUTH_TOKEN_MINUTOS = 30;
const AUTH_MIN_PASSWORD = 12;

function auth_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return $ip === '::1' ? '127.0.0.1' : $ip;
}

// ---------- Contraseñas ----------

function auth_hash(string $plain): string
{
    return password_hash($plain, PASSWORD_DEFAULT);
}

// Las contraseñas antiguas están en MD5; se aceptan una vez y se migran a bcrypt al iniciar sesión.
function auth_es_legacy(string $stored): bool
{
    return password_get_info($stored)['algo'] === null;
}

function auth_verificar(string $plain, ?string $stored): bool
{
    if ($stored === null || $stored === '') {
        return false;
    }
    if (auth_es_legacy($stored)) {
        return hash_equals(strtolower($stored), md5($plain));
    }
    return password_verify($plain, $stored);
}

function auth_necesita_rehash(string $stored): bool
{
    return auth_es_legacy($stored) || password_needs_rehash($stored, PASSWORD_DEFAULT);
}

// Devuelve la lista de requisitos incumplidos (vacía si la contraseña es válida).
function auth_validar_politica(string $p): array
{
    $errores = [];
    if (mb_strlen($p) < AUTH_MIN_PASSWORD)
        $errores[] = 'mínimo ' . AUTH_MIN_PASSWORD . ' caracteres';
    if (mb_strlen($p) > 128)
        $errores[] = 'máximo 128 caracteres';
    if (!preg_match('/[a-z]/', $p))
        $errores[] = 'una letra minúscula';
    if (!preg_match('/[A-Z]/', $p))
        $errores[] = 'una letra mayúscula';
    if (!preg_match('/\d/', $p))
        $errores[] = 'un número';
    if (!preg_match('/[^a-zA-Z0-9]/', $p))
        $errores[] = 'un símbolo (por ejemplo ! @ # $ %)';
    return $errores;
}

// ---------- Registro de accesos ----------

function auth_log(mysqli $db, ?int $id_user, string $username, int $largo_pass, string $resultado, string $motivo, int $ms): void
{
    // La contraseña ingresada nunca se guarda en claro: solo asteriscos con su largo.
    $mask = str_repeat('*', max(0, min($largo_pass, 32)));
    $user = mb_substr($username, 0, 150);
    $ip = auth_ip();
    $ua = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $stmt = $db->prepare("INSERT INTO log_accesos (id_user, username, password_enmascarada, fecha_hora, ip, user_agent, resultado, motivo, tiempo_ms)
                          VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssssi', $id_user, $user, $mask, $ip, $ua, $resultado, $motivo, $ms);
    $stmt->execute();
    $stmt->close();
}

// ---------- Mensajes del login (compartidos entre index.php y login-check.php) ----------

// Devuelve [nivel, texto]. $left = intentos restantes (solo para el código 8).
function auth_mensaje(int $code, int $left = 0): array
{
    switch ($code) {
        case 1:
            return ['danger', 'Los datos ingresados no tienen un formato válido.'];
        case 2:
            return ['success', 'Has cerrado tu sesión correctamente.'];
        case 3:
            return ['warning', 'Por favor, ingresa tu usuario y contraseña.'];
        case 4:
            return ['success', 'Tu contraseña fue restablecida. Ya puedes iniciar sesión.'];
        case 5:
            return ['warning', 'Ocurrió algo inesperado. Inténtalo nuevamente.'];
        case 6:
            return ['danger', 'Tu cuenta está bloqueada por exceder los ' . AUTH_MAX_INTENTOS . ' intentos fallidos. Contacta al administrador para desbloquearla.'];
        case 7:
            return ['danger', 'El usuario ingresado no está registrado.'];
        case 8:
            $txt = $left === 1 ? 'Te queda 1 intento' : "Te quedan $left intentos";
            return ['danger', "Contraseña incorrecta. $txt antes de que la cuenta se bloquee."];
    }
    return ['warning', 'Ocurrió algo inesperado. Inténtalo nuevamente.'];
}

// ---------- Correo ----------

function auth_config_mail(): array
{
    $cfgFile = __DIR__ . '/mail.php';
    return is_file($cfgFile) ? require $cfgFile : [];
}

function auth_mailer(): ?PHPMailer\PHPMailer\PHPMailer
{
    $cfg = auth_config_mail();
    if (!$cfg) {
        return null;
    }
    require_once __DIR__ . '/../PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $cfg['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $cfg['username'];
    $mail->Password = $cfg['password'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $cfg['port'];
    $mail->Timeout = 8;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($cfg['username'], $cfg['from_name']);
    $mail->isHTML(true);
    return $mail;
}

function auth_base_url(): string
{
    $cfg = auth_config_mail();
    return rtrim($cfg['base_url'] ?? 'http://localhost/Proyecto2', '/');
}

// Avisa por correo del bloqueo: al usuario afectado (si tiene correo) y a los Super Admin.
function auth_alerta_bloqueo(mysqli $db, array $usuario, string $ip): bool
{
    $destinos = [];
    if (!empty($usuario['email']) && filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) {
        $destinos[] = $usuario['email'];
    }
    $res = $db->query("SELECT email FROM usuarios WHERE permisos_acceso = 'Super Admin' AND status = 'activo' AND email IS NOT NULL AND email <> ''");
    while ($r = $res->fetch_assoc()) {
        if (filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
            $destinos[] = $r['email'];
        }
    }
    $destinos = array_unique($destinos);
    if (!$destinos) {
        return false;
    }

    try {
        $mail = auth_mailer();
        if (!$mail) {
            return false;
        }
        foreach ($destinos as $d) {
            $mail->addAddress($d);
        }
        $u = htmlspecialchars($usuario['username'], ENT_QUOTES, 'UTF-8');
        $fecha = date('d/m/Y H:i:s');
        $mail->Subject = 'Alerta de seguridad: cuenta bloqueada en Sysweb';
        $mail->Body = "<h2>Cuenta bloqueada</h2>
            <p>El usuario <strong>$u</strong> fue bloqueado tras " . AUTH_MAX_INTENTOS . " intentos fallidos de acceso.</p>
            <ul><li>Fecha y hora: $fecha</li><li>Dirección IP: " . htmlspecialchars($ip) . "</li></ul>
            <p>Si no fuiste tú, revisa el registro de accesos del sistema. Un administrador puede desbloquear la cuenta.</p>";
        $mail->send();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

// ---------- Recuperación de contraseña ----------

// Devuelve el id_user dueño del token vigente, o null si es inválido, usado o vencido.
function auth_token_valido(mysqli $db, string $token): ?int
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    $hash = hash('sha256', $token);
    $stmt = $db->prepare("SELECT id_user FROM password_resets WHERE token_hash = ? AND usado = 0 AND expira > NOW()");
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int) $row['id_user'] : null;
}
