<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
$db = $GLOBALS['mysqli'];
$routes = require __DIR__ . '/../../config/routes.php';

ui_cabecera('Páginas y módulos', 'bi-diagram-3', [['Páginas y módulos']]);

$modulos = $db->query(
    "SELECT m.*, (SELECT COUNT(*) FROM pagina p WHERE p.id_modulo = m.id_modulo) AS paginas
       FROM modulo m ORDER BY m.mod_orden"
)->fetch_all(MYSQLI_ASSOC);
$paginas = $db->query(
    "SELECT p.*, m.mod_nombre FROM pagina p JOIN modulo m ON m.id_modulo = p.id_modulo ORDER BY m.mod_orden, p.pag_nombre"
)->fetch_all(MYSQLI_ASSOC);

// Cuántas pantallas de config/routes.php todavía no están en el catálogo
$enCatalogo = array_column($paginas, 'pag_clave');
$sinRegistrar = 0;
foreach ($routes as $clave => $r) {
    if (isset($r['modulo']) && !in_array($clave, $enCatalogo, true)) {
        $sinRegistrar++;
    }
}
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>

       <!-- <div class="card mb-3">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong>Catálogo de páginas</strong>
                    <div class="text-muted small">
                        Cada pantalla del sistema (<code>config/routes.php</code>) es una <em>página</em> sobre la que se asignan permisos.
                        <?php if ($sinRegistrar > 0): ?>
                            <span class="badge text-bg-warning"><?= $sinRegistrar ?> pantalla(s) nueva(s) sin registrar</span>
                        <?php else: ?>
                            <span class="badge text-bg-success">Catálogo al día</span>
                        <?php endif; ?>
                    </div>
                </div>
                <form action="modules/pagina/proses.php?act=sync" method="POST">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Sincronizar con el sistema</button>
                </form>
            </div>
        </div> -->

        <div class="card mb-3">
            <div class="card-header"><strong><i class="bi bi-folder2"></i> Módulos</strong></div>
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead><tr><th class="text-center" style="width:70px">Orden</th><th>Módulo</th><th class="text-center">Páginas</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($modulos as $m): $esBase = in_array($m['mod_clave'], ['general', 'seguridad'], true); ?>
                        <tr>
                            <td class="text-center"><?= (int) $m['mod_orden'] ?></td>
                            <td><?= h($m['mod_nombre']) ?> <small class="text-muted"><?= h($m['mod_clave']) ?></small></td>
                            <td class="text-center"><?= (int) $m['paginas'] ?></td>
                            <td class="text-center"><?= ui_estado_badge($m['mod_estado']) ?></td>
                            <td class="text-center">
                                <?php if ($esBase): ?>
                                    <span class="text-muted small" title="Sin él nadie podría iniciar ni administrar el sistema">Módulo base</span>
                                <?php elseif (puede('pagina', 'editar')): ?>
                                    <?php if ($m['mod_estado'] === 'activo'): ?>
                                        <a class="btn btn-warning btn-sm" href="modules/pagina/proses.php?act=mod_off&id=<?= (int) $m['id_modulo'] ?>"
                                           onclick="return confirm('¿Desactivar el módulo completo? Ningún rol podrá ver sus páginas hasta reactivarlo.');"><i class="bi bi-slash-circle"></i> Desactivar</a>
                                    <?php else: ?>
                                        <a class="btn btn-success btn-sm" href="modules/pagina/proses.php?act=mod_on&id=<?= (int) $m['id_modulo'] ?>"><i class="bi bi-check-lg"></i> Activar</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="card">
            <div class="card-header"><strong><i class="bi bi-file-earmark-text"></i> Páginas</strong></div>
            <div class="card-body"><div class="table-responsive">
                <table id="dataTables1" class="table table-bordered table-striped table-hover">
                    <thead><tr><th>Módulo</th><th>Página</th><th>Dirección</th><th>Acciones que aplican</th><th class="text-center">Ruta</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($paginas as $p): $id = (int) $p['id_pagina']; $hayRuta = isset($routes[$p['pag_clave']]); ?>
                        <tr>
                            <td><?= h($p['mod_nombre']) ?></td>
                            <td><?= h($p['pag_nombre']) ?></td>
                            <td><code><?= h($p['pag_direccion']) ?></code></td>
                            <td><?php foreach (explode(',', $p['pag_acciones']) as $a): ?><span class="badge text-bg-light border"><?= h($a) ?></span> <?php endforeach; ?></td>
                            <td class="text-center"><?= $hayRuta ? '<i class="bi bi-check-circle text-success" title="Existe en routes.php"></i>' : '<i class="bi bi-exclamation-triangle text-warning" title="Ya no existe en routes.php"></i>' ?></td>
                            <td class="text-center"><?= ui_estado_badge($p['pag_estado']) ?></td>
                            <td class="text-center text-nowrap">
                                <a class="btn btn-primary btn-sm" href="?module=form_pagina&form=edit&id=<?= $id ?>" title="Modificar"><i class="bi bi-pencil-square"></i></a>
                                <?php if ($p['pag_estado'] === 'activo'): ?>
                                    <a class="btn btn-warning btn-sm" href="modules/pagina/proses.php?act=pag_off&id=<?= $id ?>" title="Desactivar"><i class="bi bi-slash-circle"></i></a>
                                <?php else: ?>
                                    <a class="btn btn-success btn-sm" href="modules/pagina/proses.php?act=pag_on&id=<?= $id ?>" title="Activar"><i class="bi bi-check-lg"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        </div>
    </div></div>
</section>
<script>$(function () { $('#dataTables1').DataTable({ pageLength: 25 }); });</script>
