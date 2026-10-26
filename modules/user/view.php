<?php
require_once __DIR__ . '/../../config/ui.php';
$db = $GLOBALS['mysqli'];

ui_cabecera('Gestión de usuarios', 'bi-people', [['Usuarios']], 'Agregar', '?module=form_user&form=add');

$alertas = [
    1 => ['success', 'El usuario se ha registrado correctamente.'],
    2 => ['success', 'El usuario se ha editado correctamente.'],
    3 => ['success', 'El usuario ha sido activado correctamente.'],
    4 => ['warning', 'El usuario ha sido bloqueado correctamente.'],
    5 => ['danger', 'No se pudo guardar la imagen. Asegúrate de que sea un archivo de imagen válido.'],
    6 => ['danger', 'La imagen debe pesar menos de 1 MB.'],
    7 => ['danger', 'El tipo de archivo debe ser *.jpg, *.jpeg o *.png.'],
    8 => ['danger', 'La contraseña no cumple los requisitos: mínimo ' . 12 . ' caracteres con mayúscula, minúscula, número y símbolo.'],
    9 => ['danger', 'Ese nombre de usuario ya existe.'],
    10 => ['danger', 'Selecciona un rol activo válido.'],
    11 => ['danger', 'Debe quedar al menos un administrador activo: no se puede bloquear ni cambiar el rol del último.'],
    12 => ['danger', 'No puedes bloquear tu propio usuario.'],
    13 => ['danger', 'El funcionario vinculado está dado de baja: reactívalo antes de activar su usuario.'],
    14 => ['danger', 'El funcionario seleccionado no está disponible (ya tiene usuario o está dado de baja).'],
    15 => ['danger', 'El correo electrónico no es válido.'],
    16 => ['danger', 'Completa el nombre de usuario y el nombre.'],
    17 => ['danger', 'No se pudo realizar la operación.'],
];
$a = $alertas[(int) ($_GET['alert'] ?? 0)] ?? null;

$filas = $db->query("SELECT * FROM v_usuarios ORDER BY id_user DESC")->fetch_all(MYSQLI_ASSOC);
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php if ($a): ?>
            <div class="alert alert-<?= $a[0] ?> alert-dismissible fade show" role="alert"><?= h($a[1]) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>
        <?php endif; ?>

        <div class="card"><div class="card-body"><div class="table-responsive">
            <table id="dataTables1" class="table table-striped table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">Nro</th>
                        <th class="text-center">Foto</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Funcionario vinculado</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $f):
                    $foto = $f['foto'] ? 'images/user/' . rawurlencode($f['foto']) : 'images/user/user-default.png'; ?>
                    <tr>
                        <td class="text-center"><?= (int) $f['id_user'] ?></td>
                        <td class="text-center"><img src="<?= h($foto) ?>" alt="Foto" class="img-thumbnail" width="45"></td>
                        <td><?= h($f['username']) ?></td>
                        <td><?= h($f['name_user']) ?></td>
                        <td><?= $f['rol_descripcion'] ? h($f['rol_descripcion']) : '<span class="badge text-bg-danger">Sin rol</span>' ?></td>
                        <td><?= $f['per_nombre_completo'] ? h($f['per_nombre_completo']) . ' <small class="text-muted">(' . h($f['car_descripcion']) . ')</small>' : '<span class="text-muted">Sin vincular</span>' ?></td>
                        <td class="text-center"><?= $f['status'] === 'activo' ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-danger">' . h(ucfirst((string) $f['status'])) . '</span>' ?></td>
                        <td class="text-center text-nowrap">
                            <?php if ($f['status'] === 'activo'): ?>
                                <a class="btn btn-warning btn-sm" href="modules/user/process.php?act=off&id=<?= (int) $f['id_user'] ?>" title="Bloquear"><i class="bi bi-slash-circle"></i></a>
                            <?php else: ?>
                                <a class="btn btn-success btn-sm" href="modules/user/process.php?act=on&id=<?= (int) $f['id_user'] ?>" title="Activar / desbloquear"><i class="bi bi-check-lg"></i></a>
                            <?php endif; ?>
                            <a class="btn btn-primary btn-sm" href="?module=form_user&form=edit&id=<?= (int) $f['id_user'] ?>" title="Editar"><i class="bi bi-pencil-square"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div></div>
    </div></div>
</section>
<script>$(function () { $('#dataTables1').DataTable({ pageLength: 10 }); });</script>
