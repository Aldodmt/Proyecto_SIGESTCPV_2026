<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('funcionario');
$db = $GLOBALS['mysqli'];
$act = $_GET['act'] ?? '';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

const FUNCIONARIO_EDAD_MIN = 18;     // regla de negocio: el funcionario debe ser mayor de edad al ingresar

function funcionario_error(string $act, int $id, string $msg): void
{
    $_SESSION['old_funcionario'] = array_intersect_key($_POST, array_flip(['id_persona', 'id_cargo', 'fun_fecha_ingreso', 'fun_estado']));
    $form = $act === 'update' ? "form=edit&id=$id" : 'form=add';
    header('Location: ../../main.php?module=form_funcionario&' . $form . '&alert=9&msg=' . urlencode($msg));
    exit();
}

// Baja del funcionario: se bloquea el usuario que tenga vinculado (el que se va no conserva acceso).
// Devuelve el nombre del usuario bloqueado o null.
function funcionario_bloquear_usuario(mysqli $db, int $idFuncionario): ?string
{
    $s = $db->prepare("SELECT id_user, username FROM usuarios WHERE id_funcionario = ? AND status = 'activo'");
    $s->bind_param('i', $idFuncionario);
    $s->execute();
    $u = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$u) {
        return null;
    }
    $b = $db->prepare("UPDATE usuarios SET status = 'bloqueado', bloqueado_fecha = NOW() WHERE id_user = ?");
    $b->bind_param('i', $u['id_user']);
    $b->execute();
    ui_auditar('Usuario ' . $u['username'] . ' bloqueado por baja del funcionario #' . $idFuncionario);
    return $u['username'];
}

try {
    if ($act === 'insert' || $act === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            proses_volver('funcionario', 4);
        }
        $idPersona = (int) ($_POST['id_persona'] ?? 0);
        $idCargo = (int) ($_POST['id_cargo'] ?? 0);
        $ingreso = trim((string) ($_POST['fun_fecha_ingreso'] ?? ''));
        $estado = ($_POST['fun_estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';

        if ($act === 'update') {            // la persona de un funcionario no se cambia
            $s = $db->prepare("SELECT id_persona FROM funcionario WHERE id_funcionario = ?");
            $s->bind_param('i', $id);
            $s->execute();
            $row = $s->get_result()->fetch_assoc();
            $s->close();
            if (!$row) proses_volver('funcionario', 4);
            $idPersona = (int) $row['id_persona'];
        }

        if ($idPersona <= 0 || $idCargo <= 0) funcionario_error($act, $id, 'Selecciona la persona y el cargo.');
        $f = DateTime::createFromFormat('Y-m-d', $ingreso);
        if (!$f || $f->format('Y-m-d') !== $ingreso) funcionario_error($act, $id, 'La fecha de ingreso no es válida.');
        if ($ingreso > date('Y-m-d')) funcionario_error($act, $id, 'La fecha de ingreso no puede ser futura.');

        $s = $db->prepare("SELECT per_fecha_nac, per_estado FROM persona WHERE id_persona = ?");
        $s->bind_param('i', $idPersona);
        $s->execute();
        $p = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$p) funcionario_error($act, $id, 'La persona seleccionada no existe.');
        if ($act === 'insert' && $p['per_estado'] !== 'activo') funcionario_error($act, $id, 'La persona está dada de baja.');
       /* $minimo = (new DateTime($p['per_fecha_nac']))->modify('+' . FUNCIONARIO_EDAD_MIN . ' years')->format('Y-m-d'); NO TIENE SENTIDO COMPARAR LAS EDADES MINIMAS EN PERSONAS SE TIENE QUE HACER ESO 
        if ($ingreso < $minimo) funcionario_error($act, $id, 'Al ingresar, la persona debía tener al menos ' . FUNCIONARIO_EDAD_MIN . ' años (fecha de ingreso mínima: ' . date('d/m/Y', strtotime($minimo)) . ').');
*/
        if ($act === 'insert') {
            $s = $db->prepare("INSERT INTO funcionario (id_persona, id_cargo, fun_fecha_ingreso) VALUES (?, ?, ?)");
            $s->bind_param('iis', $idPersona, $idCargo, $ingreso);
            $s->execute();
            proses_volver('funcionario', 1);
        }

        $s = $db->prepare("UPDATE funcionario SET id_cargo = ?, fun_fecha_ingreso = ?, fun_estado = ? WHERE id_funcionario = ?");
        $s->bind_param('issi', $idCargo, $ingreso, $estado, $id);
        $s->execute();
        if ($estado === 'inactivo' && ($u = funcionario_bloquear_usuario($db, $id))) {
            proses_volver('funcionario', 12, "Funcionario dado de baja. Su usuario \"$u\" fue bloqueado.");
        }
        proses_volver('funcionario', 2);
    }

    if ($act === 'on' || $act === 'off') {
        $estado = $act === 'on' ? 'activo' : 'inactivo';
        $s = $db->prepare("UPDATE funcionario SET fun_estado = ? WHERE id_funcionario = ?");
        $s->bind_param('si', $estado, $id);
        $s->execute();
        if ($act === 'off' && ($u = funcionario_bloquear_usuario($db, $id))) {
            proses_volver('funcionario', 12, "Funcionario dado de baja. Su usuario \"$u\" fue bloqueado.");
        }
        proses_volver('funcionario', $act === 'on' ? 8 : 7);
    }

    if ($act === 'delete') {
        $s = $db->prepare("SELECT 1 FROM usuarios WHERE id_funcionario = ?");
        $s->bind_param('i', $id);
        $s->execute();
        if ($s->get_result()->fetch_row()) proses_volver('funcionario', 6);
        $s->close();
        $s = $db->prepare("DELETE FROM funcionario WHERE id_funcionario = ?");
        $s->bind_param('i', $id);
        $s->execute();
        proses_volver('funcionario', 3);
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        funcionario_error($act === 'update' ? 'update' : 'insert', $id, 'Esa persona ya está registrada como funcionario.');
    }
    proses_volver('funcionario', $e->getCode() === 1451 ? 6 : 4);
}
proses_volver('funcionario', 4);
