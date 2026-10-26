<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
$db = $GLOBALS['mysqli'];

$roles = $db->query("SELECT id_rol, rol_descripcion, rol_protegido, rol_estado FROM rol ORDER BY rol_protegido DESC, rol_descripcion")->fetch_all(MYSQLI_ASSOC);
$idRol = (int) ($_GET['rol'] ?? 0);
$rol = null;
foreach ($roles as $r) {
    if ($idRol === 0 || (int) $r['id_rol'] === $idRol) {
        $rol = $r;
        break;
    }
}
$idRol = $rol ? (int) $rol['id_rol'] : 0;
$protegido = $rol && (int) $rol['rol_protegido'] === 1;
$puedeEditar = puede('permiso', 'editar') && !$protegido;

ui_cabecera('Permisos por rol', 'bi-ui-checks-grid', [['Roles', '?module=rol'], ['Permisos']]);

// Páginas agrupadas por módulo, con los permisos actuales del rol
$filas = [];
if ($rol) {
    $s = $db->prepare(
        "SELECT m.id_modulo, m.mod_nombre, p.id_pagina, p.pag_clave, p.pag_nombre, p.pag_acciones,
                COALESCE(pm.perm_leer, 0) l, COALESCE(pm.perm_insertar, 0) i, COALESCE(pm.perm_editar, 0) e, COALESCE(pm.perm_borrar, 0) b
           FROM pagina p
           JOIN modulo m ON m.id_modulo = p.id_modulo AND m.mod_estado = 'activo'
      LEFT JOIN permiso pm ON pm.id_pagina = p.id_pagina AND pm.id_rol = ?
          WHERE p.pag_estado = 'activo'
       ORDER BY m.mod_orden, p.pag_nombre"
    );
    $s->bind_param('i', $idRol);
    $s->execute();
    $filas = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();
}
$modulos = [];
foreach ($filas as $f) {
    $modulos[$f['mod_nombre']][] = $f;
}
$acciones = ['leer' => ['Leer', 'l'], 'insertar' => ['Insertar', 'i'], 'editar' => ['Editar', 'e'], 'borrar' => ['Borrar', 'b']];
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>

        <div class="card mb-3"><div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="module" value="permiso">
                <div class="col-sm-5 col-md-4">
                    <label class="form-label mb-1" for="rol">Rol</label>
                    <select class="form-select" id="rol" name="rol" onchange="this.form.submit()">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int) $r['id_rol'] ?>" <?= (int) $r['id_rol'] === $idRol ? 'selected' : '' ?>>
                                <?= h($r['rol_descripcion']) ?><?= (int) $r['rol_protegido'] ? ' (protegido)' : '' ?><?= $r['rol_estado'] === 'inactivo' ? ' - inactivo' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <noscript><div class="col-auto"><button class="btn btn-secondary">Ver</button></div></noscript>
                <div class="col text-muted small">
                    <i class="bi bi-info-circle"></i> Marca lo que este rol puede hacer en cada página. <strong>Insertar</strong>, <strong>Editar</strong> y <strong>Borrar</strong> requieren <strong>Leer</strong>.
                    Lo que no esté marcado queda denegado.
                </div>
            </form>
        </div></div>

        <?php if ($protegido): ?>
            <div class="alert alert-dark"><i class="bi bi-lock"></i> <strong><?= h($rol['rol_descripcion']) ?></strong> es un rol del sistema: tiene acceso total a todas las páginas y no se puede restringir.</div>
        <?php elseif ($rol && !puede('permiso', 'editar')): ?>
            <div class="alert alert-info"><i class="bi bi-eye"></i> Solo puedes consultar los permisos; no tienes permiso para modificarlos.</div>
        <?php endif; ?>

        <?php if ($rol): ?>
        <form id="formPermisos" action="modules/permiso/proses.php?act=save" method="POST">
            <input type="hidden" name="id_rol" value="<?= $idRol ?>">
            <?php foreach ($modulos as $nombre => $paginas): $mid = md5($nombre); ?>
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-folder2-open"></i> <?= h($nombre) ?></strong>
                        <?php if ($puedeEditar): ?>
                            <span class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" data-modulo="<?= $mid ?>" data-valor="1">Marcar todo</button>
                                <button type="button" class="btn btn-outline-secondary" data-modulo="<?= $mid ?>" data-valor="0">Quitar todo</button>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0"><div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" data-modulo="<?= $mid ?>">
                            <thead>
                                <tr>
                                    <th>Página</th>
                                    <?php foreach ($acciones as $k => [$etq]): ?><th class="text-center" style="width:90px"><?= $etq ?></th><?php endforeach; ?>
                                    <th class="text-center" style="width:70px">Todo</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($paginas as $p): $aplica = explode(',', $p['pag_acciones']); $basica = in_array($p['pag_clave'], PERM_PAGINAS_BASICAS, true); ?>
                                <tr data-pagina="<?= (int) $p['id_pagina'] ?>">
                                    <td><?= h($p['pag_nombre']) ?> <small class="text-muted">?module=<?= h($p['pag_clave']) ?></small><?= $basica ? ' <span class="badge text-bg-secondary" title="Lo tienen todos los roles: sin esto no se podría ni abrir el sistema">Básico</span>' : '' ?></td>
                                    <?php foreach ($acciones as $k => [$etq, $col]): ?>
                                        <td class="text-center">
                                            <?php if (in_array($k, $aplica, true)): ?>
                                                <input class="form-check-input" type="checkbox" data-accion="<?= $k ?>" name="perm[<?= (int) $p['id_pagina'] ?>][<?= $k ?>]" value="1"
                                                    <?= ($protegido || $basica || (int) $p[$col] === 1) ? 'checked' : '' ?> <?= ($puedeEditar && !$basica && puede($p['pag_clave'], $k)) ? '' : 'disabled' ?> aria-label="<?= $etq ?> en <?= h($p['pag_nombre']) ?>">
                                            <?php else: ?>
                                                <span class="text-muted" title="No aplica a esta página">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center">
                                        <input class="form-check-input fila-todo" type="checkbox" <?= ($puedeEditar && !$basica) ? '' : 'disabled' ?> aria-label="Todas las acciones de <?= h($p['pag_nombre']) ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div></div>
                </div>
            <?php endforeach; ?>

            <?php if ($puedeEditar): ?>
                <div class="position-sticky bottom-0 py-2" style="background: var(--bs-body-bg)">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar permisos</button>
                    <a href="?module=rol" class="btn btn-secondary">Volver a roles</a>
                </div>
            <?php endif; ?>
        </form>
        <?php else: ?>
            <div class="alert alert-warning">No hay roles registrados.</div>
        <?php endif; ?>
    </div></div>
</section>
<script>
    (function () {
        const form = document.getElementById('formPermisos');
        if (!form) return;

        const cajas = fila => Array.from(fila.querySelectorAll('input[data-accion]'));
        const editables = fila => cajas(fila).filter(c => !c.disabled);   // lo que el propio rol no tiene no se puede modificar
        const leer = fila => fila.querySelector('input[data-accion="leer"]');

        // Reglas: insertar/editar/borrar requieren leer; quitar leer quita todo lo demás
        function coherencia(fila, cambiada) {
            const l = leer(fila);
            if (!l) return;
            if (cambiada && cambiada !== l && cambiada.checked) l.checked = true;
            if (cambiada === l && !l.checked) editables(fila).forEach(c => c.checked = false);
            const todas = cajas(fila);
            const t = fila.querySelector('.fila-todo');
            if (t) t.checked = todas.length > 0 && todas.every(c => c.checked);
        }

        form.querySelectorAll('tr[data-pagina]').forEach(function (fila) {
            coherencia(fila, null);
            cajas(fila).forEach(c => c.addEventListener('change', () => coherencia(fila, c)));
            const t = fila.querySelector('.fila-todo');
            if (t) t.addEventListener('change', function () { editables(fila).forEach(c => c.checked = t.checked); coherencia(fila, null); });
        });

        form.querySelectorAll('button[data-modulo]').forEach(function (b) {
            b.addEventListener('click', function () {
                form.querySelectorAll('table[data-modulo="' + b.dataset.modulo + '"] tr[data-pagina]').forEach(function (fila) {
                    editables(fila).forEach(c => c.checked = b.dataset.valor === '1');
                    coherencia(fila, null);
                });
            });
        });
    })();
</script>
