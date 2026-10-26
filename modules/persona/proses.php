<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('persona');          // sesión + permiso según act (insert/update/on/off/delete)
$db = $GLOBALS['mysqli'];
$act = $_GET['act'] ?? '';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

// Vuelve al formulario conservando lo tecleado
function persona_error(string $act, int $id, string $msg): void
{
    $_SESSION['old_persona'] = array_intersect_key($_POST, array_flip([
        'per_nombre', 'per_apellido', 'per_ci', 'per_fecha_nac', 'per_telefono', 'per_correo', 'per_direccion',
        'id_genero', 'id_nacionalidad', 'cod_ciudad', 'per_estado']));
    $form = $act === 'update' ? "form=edit&id=$id" : 'form=add';
    header('Location: ../../main.php?module=form_persona&' . $form . '&alert=9&msg=' . urlencode($msg));
    exit();
}

try {
    if ($act === 'insert' || $act === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            proses_volver('persona', 4);
        }
        $limpio = fn($k) => trim(preg_replace('/\s+/', ' ', (string) ($_POST[$k] ?? '')));
        $nombre = $limpio('per_nombre');
        $apellido = $limpio('per_apellido');
        $ci = strtoupper(str_replace([' ', '.'], '', $limpio('per_ci')));
        $fnac = $limpio('per_fecha_nac');
        $tel = $limpio('per_telefono');
        $correo = $limpio('per_correo');
        $dir = $limpio('per_direccion');
        $idGenero = (int) ($_POST['id_genero'] ?? 0);
        $idNac = (int) ($_POST['id_nacionalidad'] ?? 0);
        $ciudad = (int) ($_POST['cod_ciudad'] ?? 0);
        $estado = ($_POST['per_estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';

        // ---- Validaciones (el formulario también valida en el navegador, pero el servidor es quien manda) ----
        if ($nombre === '' || mb_strlen($nombre) > 80) persona_error($act, $id, 'Los nombres son obligatorios (máximo 80 caracteres).');
        if ($apellido === '' || mb_strlen($apellido) > 80) persona_error($act, $id, 'Los apellidos son obligatorios (máximo 80 caracteres).');
        if (!preg_match('/^[A-Z0-9\-]{4,15}$/', $ci)) persona_error($act, $id, 'El documento debe tener de 4 a 15 caracteres (letras, números o guiones).');
        $f = DateTime::createFromFormat('Y-m-d', $fnac);
        if (!$f || $f->format('Y-m-d') !== $fnac) persona_error($act, $id, 'La fecha de nacimiento no es válida.');
        if ($fnac > date('Y-m-d')) persona_error($act, $id, 'La fecha de nacimiento no puede ser futura.');
        if ($fnac < '1900-01-01') persona_error($act, $id, 'La fecha de nacimiento no es razonable.');
        if ($tel !== '' && !preg_match('/^[0-9+()\- ]{6,20}$/', $tel)) persona_error($act, $id, 'El teléfono solo admite números, +, -, paréntesis y espacios (6 a 20 caracteres).');
        if ($correo !== '' && (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150)) persona_error($act, $id, 'El correo electrónico no es válido.');
        if (mb_strlen($dir) > 150) persona_error($act, $id, 'La dirección admite hasta 150 caracteres.');
        if ($idGenero <= 0 || $idNac <= 0 || $ciudad <= 0) persona_error($act, $id, 'Selecciona género, nacionalidad y ciudad.');

        // Documento único (clave candidata)
        $s = $db->prepare("SELECT id_persona FROM persona WHERE per_ci = ? AND id_persona <> ?");
        $s->bind_param('si', $ci, $id);
        $s->execute();
        if ($s->get_result()->fetch_row()) persona_error($act, $id, 'Ya existe una persona registrada con ese documento.');
        $s->close();

        $telDb = $tel === '' ? null : $tel;
        $correoDb = $correo === '' ? null : $correo;
        $dirDb = $dir === '' ? null : $dir;

        if ($act === 'insert') {
            $s = $db->prepare("INSERT INTO persona (per_nombre, per_apellido, per_ci, per_fecha_nac, per_telefono, per_correo, per_direccion, id_genero, id_nacionalidad, cod_ciudad)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $s->bind_param('sssssssiii', $nombre, $apellido, $ci, $fnac, $telDb, $correoDb, $dirDb, $idGenero, $idNac, $ciudad);
            $s->execute();
            proses_volver('persona', 1);
        }

        // Dar de baja a una persona que es funcionario activo dejaría un funcionario "huérfano": se pide darlo de baja antes
        if ($estado === 'inactivo') {
            $s = $db->prepare("SELECT 1 FROM funcionario WHERE id_persona = ? AND fun_estado = 'activo'");
            $s->bind_param('i', $id);
            $s->execute();
            if ($s->get_result()->fetch_row()) persona_error($act, $id, 'La persona es un funcionario activo: primero da de baja al funcionario.');
            $s->close();
        }
        $s = $db->prepare("UPDATE persona SET per_nombre = ?, per_apellido = ?, per_ci = ?, per_fecha_nac = ?, per_telefono = ?, per_correo = ?,
                           per_direccion = ?, id_genero = ?, id_nacionalidad = ?, cod_ciudad = ?, per_estado = ? WHERE id_persona = ?");
        $s->bind_param('sssssssiiisi', $nombre, $apellido, $ci, $fnac, $telDb, $correoDb, $dirDb, $idGenero, $idNac, $ciudad, $estado, $id);
        $s->execute();
        proses_volver('persona', 2);
    }

    if ($act === 'on' || $act === 'off') {
        if ($act === 'off') {
            $s = $db->prepare("SELECT 1 FROM funcionario WHERE id_persona = ? AND fun_estado = 'activo'");
            $s->bind_param('i', $id);
            $s->execute();
            if ($s->get_result()->fetch_row()) proses_volver('persona', 9, 'La persona es un funcionario activo: primero da de baja al funcionario.');
            $s->close();
        }
        $estado = $act === 'on' ? 'activo' : 'inactivo';
        $s = $db->prepare("UPDATE persona SET per_estado = ? WHERE id_persona = ?");
        $s->bind_param('si', $estado, $id);
        $s->execute();
        proses_volver('persona', $act === 'on' ? 8 : 7);
    }

    if ($act === 'delete') {
        $s = $db->prepare("SELECT 1 FROM funcionario WHERE id_persona = ?");
        $s->bind_param('i', $id);
        $s->execute();
        if ($s->get_result()->fetch_row()) proses_volver('persona', 6);
        $s->close();
        $s = $db->prepare("DELETE FROM persona WHERE id_persona = ?");
        $s->bind_param('i', $id);
        $s->execute();
        proses_volver('persona', 3);
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        persona_error($act === 'update' ? 'update' : 'insert', $id, 'Ya existe una persona registrada con ese documento.');
    }
    proses_volver('persona', $e->getCode() === 1451 ? 6 : 4);
}
proses_volver('persona', 4);
