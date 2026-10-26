<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('pagina', 'editar');            // todo lo de este archivo es "editar" sobre Páginas y módulos
$db = $GLOBALS['mysqli'];
$act = $_GET['act'] ?? '';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

// Páginas que no pueden desactivarse: sin ellas nadie podría entrar ni administrar el sistema
const PAGINAS_BASE = ['start', 'pagina', 'permiso', 'rol', 'user'];
const MODULOS_BASE = ['general', 'seguridad'];

try {
    /* ---- Sincronizar el catálogo con config/routes.php ---- */
    if ($act === 'sync') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') proses_volver('pagina', 4);
        $routes = require __DIR__ . '/../../config/routes.php';
        $mods = array_column($db->query("SELECT id_modulo, mod_clave FROM modulo")->fetch_all(MYSQLI_ASSOC), 'id_modulo', 'mod_clave');
        $existentes = array_column($db->query("SELECT pag_clave, pag_acciones FROM pagina")->fetch_all(MYSQLI_ASSOC), 'pag_acciones', 'pag_clave');

        $nuevas = $actualizadas = 0;
        $ins = $db->prepare("INSERT INTO pagina (pag_clave, pag_nombre, pag_direccion, id_modulo, pag_acciones) VALUES (?, ?, ?, ?, ?)");
        $upd = $db->prepare("UPDATE pagina SET pag_acciones = ? WHERE pag_clave = ?");
        foreach ($routes as $clave => $r) {
            if (!isset($r['modulo'])) continue;                       // los formularios cuelgan de su página
            if (!isset($mods[$r['modulo']])) continue;                // módulo desconocido: se ignora (hay que crearlo antes)
            if (!array_key_exists($clave, $existentes)) {
                $dir = '?module=' . $clave;
                $ins->bind_param('sssis', $clave, $r['title'], $dir, $mods[$r['modulo']], $r['acciones']);
                $ins->execute();
                $nuevas++;
            } elseif ($existentes[$clave] !== $r['acciones']) {
                $upd->bind_param('ss', $r['acciones'], $clave);        // las acciones aplicables las define el código
                $upd->execute();
                $actualizadas++;
            }
        }
        ui_auditar("Catálogo de páginas sincronizado: $nuevas nueva(s), $actualizadas actualizada(s)");
        proses_volver('pagina', 12, $nuevas || $actualizadas
            ? "Sincronización lista: $nuevas página(s) nueva(s) y $actualizadas actualizada(s). Las nuevas quedan sin permisos hasta asignarlos en Permisos por rol."
            : 'El catálogo ya estaba al día.');
    }

    /* ---- Editar una página ---- */
    if ($act === 'update') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') proses_volver('pagina', 4);
        $nombre = trim(preg_replace('/\s+/', ' ', (string) ($_POST['pag_nombre'] ?? '')));
        $idModulo = (int) ($_POST['id_modulo'] ?? 0);
        $estado = ($_POST['pag_estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';
        if ($nombre === '' || mb_strlen($nombre) > 80) proses_volver('pagina', 9, 'El nombre es obligatorio (máximo 80 caracteres).');

        $s = $db->prepare("SELECT pag_clave FROM pagina WHERE id_pagina = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $p = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$p) proses_volver('pagina', 4);
        if ($estado === 'inactivo' && in_array($p['pag_clave'], PAGINAS_BASE, true)) {
            proses_volver('pagina', 9, 'Esta página es esencial para administrar el sistema y no se puede desactivar.');
        }
        $s = $db->prepare("UPDATE pagina SET pag_nombre = ?, id_modulo = ?, pag_estado = ? WHERE id_pagina = ?");
        $s->bind_param('sisi', $nombre, $idModulo, $estado, $id);
        $s->execute();
        ui_auditar("Página modificada: {$p['pag_clave']} ($estado)");
        proses_volver('pagina', 2);
    }

    /* ---- Activar / desactivar una página ---- */
    if ($act === 'pag_on' || $act === 'pag_off') {
        $s = $db->prepare("SELECT pag_clave FROM pagina WHERE id_pagina = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $p = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$p) proses_volver('pagina', 4);
        if ($act === 'pag_off' && in_array($p['pag_clave'], PAGINAS_BASE, true)) {
            proses_volver('pagina', 9, 'Esta página es esencial para administrar el sistema y no se puede desactivar.');
        }
        $estado = $act === 'pag_on' ? 'activo' : 'inactivo';
        $s = $db->prepare("UPDATE pagina SET pag_estado = ? WHERE id_pagina = ?");
        $s->bind_param('si', $estado, $id);
        $s->execute();
        ui_auditar("Página {$p['pag_clave']}: $estado");
        proses_volver('pagina', $act === 'pag_on' ? 8 : 7);
    }

    /* ---- Activar / desactivar un módulo completo ---- */
    if ($act === 'mod_on' || $act === 'mod_off') {
        $s = $db->prepare("SELECT mod_clave FROM modulo WHERE id_modulo = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $m = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$m) proses_volver('pagina', 4);
        if ($act === 'mod_off' && in_array($m['mod_clave'], MODULOS_BASE, true)) {
            proses_volver('pagina', 9, 'Los módulos General y Seguridad son la base del sistema y no se pueden desactivar.');
        }
        $estado = $act === 'mod_on' ? 'activo' : 'inactivo';
        $s = $db->prepare("UPDATE modulo SET mod_estado = ? WHERE id_modulo = ?");
        $s->bind_param('si', $estado, $id);
        $s->execute();
        ui_auditar("Módulo {$m['mod_clave']}: $estado");
        proses_volver('pagina', $act === 'mod_on' ? 8 : 7);
    }
} catch (mysqli_sql_exception $e) {
    proses_volver('pagina', 4);
}
proses_volver('pagina', 4);
