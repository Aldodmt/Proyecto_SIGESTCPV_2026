<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('rol');
$db = $GLOBALS['mysqli'];
$act = $_GET['act'] ?? '';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

function rol_error(string $act, int $id, string $msg): void
{
    $_SESSION['old_rol'] = array_intersect_key($_POST, array_flip(['rol_descripcion', 'rol_observacion', 'rol_estado']));
    $form = $act === 'update' ? "form=edit&id=$id" : 'form=add';
    header('Location: ../../main.php?module=form_rol&' . $form . '&alert=9&msg=' . urlencode($msg));
    exit();
}

// Los roles protegidos (Super Admin) no se editan, no se dan de baja ni se eliminan: evita quedarse sin administrador
function rol_cargar(mysqli $db, int $id): ?array
{
    $s = $db->prepare("SELECT * FROM rol WHERE id_rol = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    return $r ?: null;
}

try {
    if ($act === 'insert' || $act === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            proses_volver('rol', 4);
        }
        $nombre = trim(preg_replace('/\s+/', ' ', (string) ($_POST['rol_descripcion'] ?? '')));
        $obs = trim((string) ($_POST['rol_observacion'] ?? ''));
        $estado = ($_POST['rol_estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';
        if ($nombre === '' || mb_strlen($nombre) > 50) rol_error($act, $id, 'El nombre del rol es obligatorio (máximo 50 caracteres).');
        if (mb_strlen($obs) > 150) rol_error($act, $id, 'La descripción admite hasta 150 caracteres.');

        $s = $db->prepare("SELECT 1 FROM rol WHERE LOWER(rol_descripcion) = LOWER(?) AND id_rol <> ?");
        $s->bind_param('si', $nombre, $id);
        $s->execute();
        if ($s->get_result()->fetch_row()) rol_error($act, $id, 'Ya existe un rol con ese nombre.');
        $s->close();
        $obsDb = $obs === '' ? null : $obs;

        if ($act === 'insert') {
            $db->begin_transaction();
            $s = $db->prepare("INSERT INTO rol (rol_descripcion, rol_observacion) VALUES (?, ?)");
            $s->bind_param('ss', $nombre, $obsDb);
            $s->execute();
            $nuevo = $db->insert_id;
            // Permisos básicos que todo usuario necesita (módulo General); el resto queda denegado
            $s = $db->prepare("INSERT INTO permiso (id_rol, id_pagina, perm_leer, perm_insertar, perm_editar, perm_borrar)
                               SELECT ?, p.id_pagina, FIND_IN_SET('leer', p.pag_acciones) > 0, FIND_IN_SET('insertar', p.pag_acciones) > 0,
                                      FIND_IN_SET('editar', p.pag_acciones) > 0, FIND_IN_SET('borrar', p.pag_acciones) > 0
                                 FROM pagina p JOIN modulo m ON m.id_modulo = p.id_modulo WHERE m.mod_clave = 'general'");
            $s->bind_param('i', $nuevo);
            $s->execute();
            $db->commit();
            ui_auditar("Rol creado: $nombre");
            proses_volver('rol', 1);
        }

        $r = rol_cargar($db, $id);
        if (!$r) proses_volver('rol', 4);
        if ((int) $r['rol_protegido'] === 1) proses_volver('rol', 11);
        if ($estado === 'inactivo') {
            $s = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE id_rol = ? AND status = 'activo'");
            $s->bind_param('i', $id);
            $s->execute();
            $n = (int) $s->get_result()->fetch_row()[0];
            $s->close();
            if ($n > 0) rol_error($act, $id, "No se puede dar de baja: el rol tiene $n usuario(s) activo(s). Reasígnalos primero.");
        }
        $db->begin_transaction();
        $s = $db->prepare("UPDATE rol SET rol_descripcion = ?, rol_observacion = ?, rol_estado = ? WHERE id_rol = ?");
        $s->bind_param('sssi', $nombre, $obsDb, $estado, $id);
        $s->execute();
        // El resto del sistema todavía lee el texto usuarios.permisos_acceso: se mantiene sincronizado con el nombre del rol
        $s = $db->prepare("UPDATE usuarios SET permisos_acceso = ? WHERE id_rol = ?");
        $s->bind_param('si', $nombre, $id);
        $s->execute();
        $db->commit();
        ui_auditar("Rol modificado: {$r['rol_descripcion']} -> $nombre ($estado)");
        proses_volver('rol', 2);
    }

    if ($act === 'on' || $act === 'off' || $act === 'delete') {
        $r = rol_cargar($db, $id);
        if (!$r) proses_volver('rol', 4);
        if ((int) $r['rol_protegido'] === 1) proses_volver('rol', 11);

        if ($act === 'delete') {
            $s = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE id_rol = ?");
            $s->bind_param('i', $id);
            $s->execute();
            if ((int) $s->get_result()->fetch_row()[0] > 0) proses_volver('rol', 6);
            $s->close();
            $s = $db->prepare("DELETE FROM rol WHERE id_rol = ?");      // sus filas de permiso se eliminan en cascada
            $s->bind_param('i', $id);
            $s->execute();
            ui_auditar('Rol eliminado: ' . $r['rol_descripcion']);
            proses_volver('rol', 3);
        }
        if ($act === 'off') {
            $s = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE id_rol = ? AND status = 'activo'");
            $s->bind_param('i', $id);
            $s->execute();
            $n = (int) $s->get_result()->fetch_row()[0];
            $s->close();
            if ($n > 0) proses_volver('rol', 9, "No se puede dar de baja: el rol tiene $n usuario(s) activo(s). Reasígnalos primero.");
        }
        $estado = $act === 'on' ? 'activo' : 'inactivo';
        $s = $db->prepare("UPDATE rol SET rol_estado = ? WHERE id_rol = ?");
        $s->bind_param('si', $estado, $id);
        $s->execute();
        ui_auditar('Rol ' . ($act === 'on' ? 'reactivado' : 'dado de baja') . ': ' . $r['rol_descripcion']);
        proses_volver('rol', $act === 'on' ? 8 : 7);
    }
} catch (mysqli_sql_exception $e) {
    if ($db->errno) { @$db->rollback(); }
    proses_volver('rol', $e->getCode() === 1451 ? 6 : 4);
}
proses_volver('rol', 4);
