<?php
require_once __DIR__ . '/../../config/ui.php';
$db = $GLOBALS['mysqli'];

ui_cabecera('Datos de Funcionarios', 'bi-person-badge', [['Funcionarios']], 'Agregar', '?module=form_funcionario&form=add');
$filas = $db->query("SELECT * FROM v_funcionario ORDER BY per_nombre_completo")->fetch_all(MYSQLI_ASSOC);
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>
        <div class="card"><div class="card-body"><div class="table-responsive">
            <table id="dataTables1" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">Nro.</th>
                        <th>C.I.</th>
                        <th>Funcionario</th>
                        <th>Cargo</th>
                        <th class="text-center">Fecha de ingreso</th>
                        <th>Usuario del sistema</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $f): $id = (int) $f['id_funcionario']; ?>
                    <tr>
                        <td class="text-center"><?= $id ?></td>
                        <td><?= h($f['per_ci']) ?></td>
                        <td><?= h($f['per_nombre_completo']) ?></td>
                        <td><?= h($f['car_descripcion']) ?></td>
                        <td class="text-center" data-order="<?= h($f['fun_fecha_ingreso']) ?>"><?= date('d/m/Y', strtotime($f['fun_fecha_ingreso'])) ?></td>
                        <td><?= $f['username'] ? h($f['username']) : '<span class="text-muted">Sin usuario</span>' ?></td>
                        <td class="text-center"><?= ui_estado_badge($f['fun_estado']) ?></td>
                        <td class="text-center text-nowrap">
                            <a class="btn btn-primary btn-sm" href="?module=form_funcionario&form=edit&id=<?= $id ?>" title="Modificar"><i class="bi bi-pencil-square"></i></a>
                            <?php if ($f['fun_estado'] === 'activo'): ?>
                                <a class="btn btn-warning btn-sm" href="modules/funcionario/proses.php?act=off&id=<?= $id ?>" title="Dar de baja"
                                   onclick="return confirm('¿Dar de baja a este funcionario?<?= $f['username'] ? ' Su usuario del sistema también será bloqueado.' : '' ?>');"><i class="bi bi-slash-circle"></i></a>
                            <?php else: ?>
                                <a class="btn btn-success btn-sm" href="modules/funcionario/proses.php?act=on&id=<?= $id ?>" title="Reactivar"><i class="bi bi-check-lg"></i></a>
                            <?php endif; ?>
                            <?php if (!$f['username']): ?>
                                <a class="btn btn-danger btn-sm" href="modules/funcionario/proses.php?act=delete&id=<?= $id ?>" title="Eliminar"
                                   onclick="return confirm('¿Eliminar definitivamente a este funcionario?');"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div></div>
    </div></div>
</section>
<script>$(function () { $('#dataTables1').DataTable({ pageLength: 10 }); });</script>
