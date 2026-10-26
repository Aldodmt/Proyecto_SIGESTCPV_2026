<?php
require_once __DIR__ . '/../../config/ui.php';
$db = $GLOBALS['mysqli'];

ui_cabecera('Datos de Personas', 'bi-person-vcard', [['Personas']], 'Agregar', '?module=form_persona&form=add');

$filas = $db->query(
    "SELECT v.*, (SELECT COUNT(*) FROM funcionario f WHERE f.id_persona = v.id_persona) AS es_funcionario
       FROM v_persona v ORDER BY v.per_apellido, v.per_nombre"
)->fetch_all(MYSQLI_ASSOC);
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
                        <th>Nombre completo</th>
                        <th class="text-center">Edad</th>
                        <th>Género</th>
                        <th>Nacionalidad</th>
                        <th>Departamento</th>
                        <th>Ciudad</th>
                        <th>Teléfono</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $f): $id = (int) $f['id_persona']; ?>
                    <tr>
                        <td class="text-center"><?= $id ?></td>
                        <td><?= h($f['per_ci']) ?></td>
                        <td><?= h($f['per_nombre_completo']) ?></td>
                        <td class="text-center" data-order="<?= (int) $f['per_edad'] ?>"><?= (int) $f['per_edad'] ?></td>
                        <td><?= h($f['gen_descripcion']) ?></td>
                        <td><?= h($f['nac_descripcion']) ?></td>
                        <td><?= h($f['dep_descripcion']) ?></td>
                        <td><?= h($f['descrip_ciudad']) ?></td>
                        <td><?= h($f['per_telefono']) ?></td>
                        <td class="text-center"><?= ui_estado_badge($f['per_estado']) ?></td>
                        <td class="text-center text-nowrap">
                            <a class="btn btn-primary btn-sm" href="?module=form_persona&form=edit&id=<?= $id ?>" title="Modificar"><i class="bi bi-pencil-square"></i></a>
                            <?php if ($f['per_estado'] === 'activo'): ?>
                                <a class="btn btn-warning btn-sm" href="modules/persona/proses.php?act=off&id=<?= $id ?>" title="Dar de baja"><i class="bi bi-slash-circle"></i></a>
                            <?php else: ?>
                                <a class="btn btn-success btn-sm" href="modules/persona/proses.php?act=on&id=<?= $id ?>" title="Reactivar"><i class="bi bi-check-lg"></i></a>
                            <?php endif; ?>
                            <?php if ((int) $f['es_funcionario'] === 0): ?>
                                <a class="btn btn-danger btn-sm" href="modules/persona/proses.php?act=delete&id=<?= $id ?>" title="Eliminar"
                                   onclick="return confirm('¿Eliminar definitivamente a <?= h(addslashes($f['per_nombre_completo'])) ?>?');"><i class="bi bi-trash"></i></a>
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
