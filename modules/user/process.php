<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/permisos.php';
require_once __DIR__ . '/../../config/ui.php';
guard_endpoint('user');                         // sesión + permiso según act (insert/update/on/off)
$db = $GLOBALS['mysqli'];
$act = $_GET['act'] ?? '';

function user_volver(int $alert): void
{
    header('Location: ../../main.php?module=user&alert=' . $alert);
    exit();
}

function user_cargar(mysqli $db, int $id): ?array
{
    $s = $db->prepare("SELECT u.*, r.rol_protegido FROM usuarios u LEFT JOIN rol r ON r.id_rol = u.id_rol WHERE u.id_user = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    return $r ?: null;
}

// ¿Quedaría al menos un administrador (rol protegido) activo si este usuario deja de serlo?
function user_quedan_admins(mysqli $db, int $excluirId): bool
{
    $s = $db->prepare("SELECT COUNT(*) FROM usuarios u JOIN rol r ON r.id_rol = u.id_rol
                        WHERE r.rol_protegido = 1 AND r.rol_estado = 'activo' AND u.status = 'activo' AND u.id_user <> ?");
    $s->bind_param('i', $excluirId);
    $s->execute();
    $n = (int) $s->get_result()->fetch_row()[0];
    $s->close();
    return $n > 0;
}

// Valida y devuelve [nombre_rol, id_funcionario|null] o termina con el alerta correspondiente
function user_validar_rol_funcionario(mysqli $db, int $idRol, ?int $idFuncionario, int $idUser): array
{
    $s = $db->prepare("SELECT rol_descripcion FROM rol WHERE id_rol = ? AND rol_estado = 'activo'");
    $s->bind_param('i', $idRol);
    $s->execute();
    $rol = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$rol) user_volver(10);

    if ($idFuncionario) {
        // Debe estar activo y sin otro usuario (salvo el propio)
        $s = $db->prepare("SELECT 1 FROM funcionario f WHERE f.id_funcionario = ? AND f.fun_estado = 'activo'
                              AND NOT EXISTS (SELECT 1 FROM usuarios u WHERE u.id_funcionario = f.id_funcionario AND u.id_user <> ?)");
        $s->bind_param('ii', $idFuncionario, $idUser);
        $s->execute();
        if (!$s->get_result()->fetch_row()) user_volver(14);
        $s->close();
    }
    return [$rol['rol_descripcion'], $idFuncionario ?: null];
}

// Foto de perfil: debe ser una imagen real (jpg/png) de hasta 1 MB; se guarda con un nombre generado
function user_guardar_foto(array $f, int $idUser): ?string
{
    if (empty($f['name'])) {
        return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) user_volver(5);
    if ($f['size'] > 1000000) user_volver(6);
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $info = @getimagesize($f['tmp_name']);
    if (!in_array($ext, ['jpg', 'jpeg', 'png'], true) || !$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) user_volver(7);
    $nombre = 'u' . $idUser . '_' . bin2hex(random_bytes(4)) . '.' . ($info[2] === IMAGETYPE_PNG ? 'png' : 'jpg');
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/../../images/user/' . $nombre)) user_volver(5);
    return $nombre;
}

try {
    /* ------------------------------------------------------------------ Alta */
    if ($act === 'insert') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') user_volver(17);
        $username = trim((string) ($_POST['username'] ?? ''));
        $name = trim((string) ($_POST['name_user'] ?? ''));
        $plain = (string) ($_POST['password'] ?? '');
        $email = trim((string) ($_POST['email'] ?? ''));
        $tel = trim((string) ($_POST['telefono'] ?? ''));
        $idRol = (int) ($_POST['id_rol'] ?? 0);
        $idFun = (int) ($_POST['id_funcionario'] ?? 0) ?: null;

        if ($username === '' || $name === '' || mb_strlen($username) > 150 || mb_strlen($name) > 150) user_volver(16);
        if (auth_validar_politica($plain)) user_volver(8);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) user_volver(15);
        [$rolNombre, $idFun] = user_validar_rol_funcionario($db, $idRol, $idFun, 0);

        $hash = auth_hash($plain);
        $emailDb = $email === '' ? null : $email;
        $telDb = $tel === '' ? null : mb_substr($tel, 0, 39);
        $s = $db->prepare("INSERT INTO usuarios (username, password, name_user, email, telefono, permisos_acceso, id_rol, id_funcionario, status, intentos_fallidos)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo', 0)");
        $s->bind_param('ssssssii', $username, $hash, $name, $emailDb, $telDb, $rolNombre, $idRol, $idFun);
        $s->execute();
        ui_auditar("Usuario creado: $username (rol $rolNombre)");
        user_volver(1);
    }

    /* ----------------------------------------------------------- Modificación */
    if ($act === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') user_volver(17);
        $id = (int) ($_POST['id_user'] ?? 0);
        $u = user_cargar($db, $id);
        if (!$u) user_volver(17);

        $username = trim((string) ($_POST['username'] ?? ''));
        $name = trim((string) ($_POST['name_user'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $tel = trim((string) ($_POST['telefono'] ?? ''));
        $idRol = (int) ($_POST['id_rol'] ?? 0);
        $idFun = (int) ($_POST['id_funcionario'] ?? 0) ?: null;

        if ($username === '' || $name === '' || mb_strlen($username) > 150 || mb_strlen($name) > 150) user_volver(16);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) user_volver(15);
        [$rolNombre, $idFun] = user_validar_rol_funcionario($db, $idRol, $idFun, $id);

        // No dejar al sistema sin administradores: si este usuario lo es y se le quita el rol, debe quedar otro
        if ((int) $u['rol_protegido'] === 1 && (int) $u['id_rol'] !== $idRol && $u['status'] === 'activo' && !user_quedan_admins($db, $id)) user_volver(11);

        $s = $db->prepare("SELECT 1 FROM usuarios WHERE username = ? AND id_user <> ?");
        $s->bind_param('si', $username, $id);
        $s->execute();
        if ($s->get_result()->fetch_row()) user_volver(9);
        $s->close();

        $foto = user_guardar_foto($_FILES['foto'] ?? [], $id);
        $emailDb = $email === '' ? null : $email;
        $telDb = $tel === '' ? null : mb_substr($tel, 0, 39);
        if ($foto !== null) {
            $s = $db->prepare("UPDATE usuarios SET username = ?, name_user = ?, email = ?, telefono = ?, permisos_acceso = ?, id_rol = ?, id_funcionario = ?, foto = ? WHERE id_user = ?");
            $s->bind_param('sssssiisi', $username, $name, $emailDb, $telDb, $rolNombre, $idRol, $idFun, $foto, $id);
        } else {
            $s = $db->prepare("UPDATE usuarios SET username = ?, name_user = ?, email = ?, telefono = ?, permisos_acceso = ?, id_rol = ?, id_funcionario = ? WHERE id_user = ?");
            $s->bind_param('sssssiii', $username, $name, $emailDb, $telDb, $rolNombre, $idRol, $idFun, $id);
        }
        $s->execute();
        if ((int) $u['id_rol'] !== $idRol) {
            ui_auditar("Rol de {$u['username']} cambiado: " . ($u['permisos_acceso'] ?: 'sin rol') . " -> $rolNombre");
        }
        user_volver(2);
    }

    /* ------------------------------------------------- Activar / bloquear */
    if ($act === 'on' || $act === 'off') {
        $id = (int) ($_GET['id'] ?? 0);
        $u = user_cargar($db, $id);
        if (!$u) user_volver(17);

        if ($act === 'off') {
            if ($id === (int) $_SESSION['id_user']) user_volver(12);
            if ((int) $u['rol_protegido'] === 1 && $u['status'] === 'activo' && !user_quedan_admins($db, $id)) user_volver(11);
            $s = $db->prepare("UPDATE usuarios SET status = 'bloqueado', bloqueado_fecha = NOW() WHERE id_user = ?");
            $s->bind_param('i', $id);
            $s->execute();
            ui_auditar("Usuario bloqueado: {$u['username']}");
            user_volver(4);
        }

        // Un usuario no se reactiva si su funcionario está dado de baja (el que se fue no vuelve a tener acceso por esta vía)
        if ($u['id_funcionario']) {
            $s = $db->prepare("SELECT fun_estado FROM funcionario WHERE id_funcionario = ?");
            $s->bind_param('i', $u['id_funcionario']);
            $s->execute();
            $f = $s->get_result()->fetch_assoc();
            $s->close();
            if ($f && $f['fun_estado'] !== 'activo') user_volver(13);
        }
        // Al desbloquear también se reinician los intentos fallidos
        $s = $db->prepare("UPDATE usuarios SET status = 'activo', intentos_fallidos = 0, bloqueado_fecha = NULL WHERE id_user = ?");
        $s->bind_param('i', $id);
        $s->execute();
        ui_auditar("Usuario activado: {$u['username']}");
        user_volver(3);
    }
} catch (mysqli_sql_exception $e) {
    user_volver($e->getCode() === 1062 ? 9 : 17);
}
user_volver(17);
