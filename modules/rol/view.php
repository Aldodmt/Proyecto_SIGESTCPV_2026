<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/permisos.php';
$db = $GLOBALS['mysqli'];

ui_cabecera('Roles del sistema', 'bi-person-lock', [['Roles']], 'Agregar', '?module=form_rol&form=add');
$filas = $db->query(
    "SELECT r.*,
            (SELECT COUNT(*) FROM usuarios u WHERE u.id_rol = r.id_rol) AS usuarios,
            (SELECT COUNT(*) FROM usuarios u WHERE u.id_rol = r.id_rol AND u.status = 'activo') AS usuarios_activos,
            (SELECT COUNT(*) FROM permiso p WHERE p.id_rol = r.id_rol AND p.perm_leer = 1) AS paginas
       FROM rol r ORDER BY r.rol_protegido DESC, r.rol_descripcion"
)->fetch_all(MYSQLI_ASSOC);
$totalPaginas = (int) $db->query("SELECT COUNT(*) FROM pagina WHERE pag_estado = 'activo'")->fetch_row()[0];
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>
        <div class="card"><div class="card-body"><div class="table-responsive">
            <table id="dataTables1" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">Código</th>
                        <th>Rol</th>
                        <th>Observación</th>
                        <th class="text-center">Usuarios</th>
                        <th class="text-center">Páginas con acceso</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $f): $id = (int) $f['id_rol']; $prot = (int) $f['rol_protegido'] === 1; ?>
                    <tr>
                        <td class="text-center"><?= $id ?></td>
                        <td><?= h($f['rol_descripcion']) ?> <?= $prot ? '<span class="badge text-bg-dark" title="Rol del sistema: acceso total, no editable">Protegido</span>' : '' ?></td>
                        <td><?= h($f['rol_observacion']) ?></td>
                        <td class="text-center"><?= (int) $f['usuarios'] ?></td>
                        <td class="text-center"><?= $prot ? 'Todas' : (int) $f['paginas'] . ' de ' . $totalPaginas ?></td>
                        <td class="text-center"><?= ui_estado_badge($f['rol_estado']) ?></td>
                        <td class="text-center text-nowrap">
                            <?php if (puede('permiso', 'leer')): ?>
                                <a class="btn btn-info btn-sm" href="?module=permiso&rol=<?= $id ?>" title="Ver y asignar permisos"><i class="bi bi-ui-checks-grid"></i></a>
                            <?php endif; ?>
                            <?php if (!$prot): ?>
                                <a class="btn btn-primary btn-sm" href="?module=form_rol&form=edit&id=<?= $id ?>" title="Modificar"><i class="bi bi-pencil-square"></i></a>
                                <?php if ($f['rol_estado'] === 'activo'): ?>
                                    <a class="btn btn-warning btn-sm" href="modules/rol/proses.php?act=off&id=<?= $id ?>" title="Dar de baja"><i class="bi bi-slash-circle"></i></a>
                                <?php else: ?>
                                    <a class="btn btn-success btn-sm" href="modules/rol/proses.php?act=on&id=<?= $id ?>" title="Reactivar"><i class="bi bi-check-lg"></i></a>
                                <?php endif; ?>
                                <?php if ((int) $f['usuarios'] === 0): ?>
                                    <a class="btn btn-danger btn-sm" href="modules/rol/proses.php?act=delete&id=<?= $id ?>" title="Eliminar"
                                       onclick="return confirm('¿Eliminar definitivamente el rol <?= h(addslashes($f['rol_descripcion'])) ?>?');"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div></div>
        <p class="text-muted small mb-0"><i class="bi bi-info-circle"></i> Un rol recién creado solo recibe los permisos básicos (inicio, perfil y cambio de contraseña). El resto se asigna en <strong>Permisos por rol</strong>.</p>
    </div></div>
</section>
<script>$(function () { $('#dataTables1').DataTable({ pageLength: 10 }); });</script>
