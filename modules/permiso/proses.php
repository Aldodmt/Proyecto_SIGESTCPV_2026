<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('permiso', 'editar');         // guardar la matriz es "editar" sobre la página de permisos
$db = $GLOBALS['mysqli'];

function permiso_volver(int $rol, int $alert, string $msg = ''): void
{
    $url = '../../main.php?module=permiso&rol=' . $rol . '&alert=' . $alert . ($msg !== '' ? '&msg=' . urlencode($msg) : '');
    header('Location: ' . $url);
    exit();
}

if (($_GET['act'] ?? '') !== 'save' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    permiso_volver((int) ($_POST['id_rol'] ?? 0), 4);
}
$idRol = (int) ($_POST['id_rol'] ?? 0);
$post = is_array($_POST['perm'] ?? null) ? $_POST['perm'] : [];

$s = $db->prepare("SELECT * FROM rol WHERE id_rol = ?");
$s->bind_param('i', $idRol);
$s->execute();
$rol = $s->get_result()->fetch_assoc();
$s->close();
if (!$rol) permiso_volver($idRol, 4);
if ((int) $rol['rol_protegido'] === 1) permiso_volver($idRol, 11);

// Estado actual (para detectar qué cambia) y páginas activas con sus acciones aplicables
$s = $db->prepare(
    "SELECT p.id_pagina, p.pag_clave, p.pag_acciones,
            COALESCE(pm.perm_leer, 0) l, COALESCE(pm.perm_insertar, 0) i, COALESCE(pm.perm_editar, 0) e, COALESCE(pm.perm_borrar, 0) b
       FROM pagina p LEFT JOIN permiso pm ON pm.id_pagina = p.id_pagina AND pm.id_rol = ?
      WHERE p.pag_estado = 'activo'"
);
$s->bind_param('i', $idRol);
$s->execute();
$paginas = $s->get_result()->fetch_all(MYSQLI_ASSOC);
$s->close();

$nuevo = [];
$cambios = 0;
$ignorados = 0;
$colDe = ['leer' => 'l', 'insertar' => 'i', 'editar' => 'e', 'borrar' => 'b'];
foreach ($paginas as $p) {
    $aplica = explode(',', $p['pag_acciones']);
    $marca = $post[$p['id_pagina']] ?? [];
    $n = [];
    foreach ($colDe as $accion => $col) {
        $n[$accion] = in_array($accion, $aplica, true) && !empty($marca[$accion]) ? 1 : 0;
    }
    if ($n['insertar'] || $n['editar'] || $n['borrar']) {
        $n['leer'] = in_array('leer', $aplica, true) ? 1 : 0;    // sin "leer" no se puede usar la página
    }

    // Páginas básicas: todos los roles las tienen completas; lo que llegue del formulario no cuenta
    if (in_array($p['pag_clave'], PERM_PAGINAS_BASICAS, true)) {
        foreach ($colDe as $accion => $col) {
            $n[$accion] = in_array($accion, $aplica, true) ? 1 : 0;
        }
        $nuevo[(int) $p['id_pagina']] = [$p['pag_clave'], $n];
        continue;
    }

    // Delegación acotada: solo se puede cambiar lo que uno mismo tiene. Así nadie escala privilegios (concediendo algo
    // que no posee) ni altera los permisos de otros sobre páginas a las que él no accede. Lo demás se conserva tal cual.
    foreach ($colDe as $accion => $col) {
        if (!puede($p['pag_clave'], $accion)) {
            if ($n[$accion] !== (int) $p[$col]) {
                $ignorados++;
            }
            $n[$accion] = (int) $p[$col];
        } elseif ($n[$accion] !== (int) $p[$col]) {
            $cambios++;
        }
    }
    $nuevo[(int) $p['id_pagina']] = [$p['pag_clave'], $n];
}

// Anti-bloqueo: quien edita no puede dejar a su propio rol sin acceso a esta pantalla
if ($idRol === (int) ($_SESSION['id_rol'] ?? 0)) {
    foreach ($nuevo as [$clave, $n]) {
        if ($clave === 'permiso' && (!$n['leer'] || !$n['editar'])) {
            permiso_volver($idRol, 9, 'No puedes quitarle a tu propio rol el acceso a la pantalla de permisos.');
        }
    }
}

try {
    $db->begin_transaction();
    $up = $db->prepare("INSERT INTO permiso (id_rol, id_pagina, perm_leer, perm_insertar, perm_editar, perm_borrar) VALUES (?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE perm_leer = VALUES(perm_leer), perm_insertar = VALUES(perm_insertar),
                                                perm_editar = VALUES(perm_editar), perm_borrar = VALUES(perm_borrar)");
    foreach ($nuevo as $idPagina => [$clave, $n]) {
        $up->bind_param('iiiiii', $idRol, $idPagina, $n['leer'], $n['insertar'], $n['editar'], $n['borrar']);
        $up->execute();
    }
    $db->commit();
} catch (mysqli_sql_exception $e) {
    $db->rollback();
    permiso_volver($idRol, 4);
}
ui_auditar("Permisos del rol {$rol['rol_descripcion']} modificados ($cambios cambio(s)" . ($ignorados ? ", $ignorados ignorado(s) por falta de permiso propio" : '') . ')');
if ($ignorados > 0) {
    permiso_volver($idRol, 12, "Permisos guardados. $ignorados cambio(s) se ignoraron porque tu propio rol no tiene esos permisos.");
}
permiso_volver($idRol, 10);
