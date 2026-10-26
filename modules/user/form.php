<?php
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/auth.php';
$db = $GLOBALS['mysqli'];

$editar = ($_GET['form'] ?? '') === 'edit';
$d = ['id_user' => '', 'username' => '', 'name_user' => '', 'email' => '', 'telefono' => '', 'foto' => '', 'id_rol' => '', 'id_funcionario' => ''];

if ($editar) {
    $s = $db->prepare("SELECT * FROM usuarios WHERE id_user = ?");
    $id = (int) ($_GET['id'] ?? 0);
    $s->bind_param('i', $id);
    $s->execute();
    $d = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$d) {
        echo '<div class="alert alert-warning">El usuario no existe.</div>';
        return;
    }
}

// Roles activos (más el actual, aunque esté de baja) y funcionarios disponibles (activos y sin usuario, más el ya vinculado)
$roles = $db->query("SELECT id_rol, rol_descripcion, rol_protegido FROM rol WHERE rol_estado = 'activo' OR id_rol = " . (int) $d['id_rol'] . " ORDER BY rol_protegido DESC, rol_descripcion")->fetch_all(MYSQLI_ASSOC);
$funcionarios = $db->query(
    "SELECT f.id_funcionario, f.per_nombre_completo, f.car_descripcion, f.per_correo, f.per_telefono
       FROM v_funcionario f
      WHERE f.id_funcionario = " . (int) $d['id_funcionario'] . "
         OR (f.fun_estado = 'activo' AND f.id_user IS NULL)
      ORDER BY f.per_nombre_completo")->fetch_all(MYSQLI_ASSOC);

ui_cabecera($editar ? 'Modificar usuario' : 'Agregar usuario', $editar ? 'bi-pencil-square' : 'bi-person-plus',
    [['Usuarios', '?module=user'], [$editar ? 'Modificar' : 'Agregar']]);
?>
<section class="app-content">
    <div class="row"><div class="col-md-12"><div class="card">
        <div class="card-header"><?= $editar ? 'Formulario de edición' : 'Formulario de registro' ?></div>
        <div class="card-body">
            <form id="formUser" method="POST" action="modules/user/process.php?act=<?= $editar ? 'update' : 'insert' ?>" enctype="multipart/form-data" novalidate>
                <?php if ($editar): ?><input type="hidden" name="id_user" value="<?= (int) $d['id_user'] ?>"><?php endif; ?>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="username">Nombre de usuario</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="username" name="username" maxlength="150" value="<?= h($d['username']) ?>" required>
                        <div class="invalid-feedback">Ingresa el nombre de usuario.</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="name_user">Nombre y apellido</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="name_user" name="name_user" maxlength="150" value="<?= h($d['name_user']) ?>" required>
                        <div class="invalid-feedback">Ingresa el nombre.</div>
                    </div>
                </div>

                <?php if (!$editar): ?>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="password">Contraseña</label>
                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="password" name="password" maxlength="128" autocomplete="new-password" required>
                            <div class="form-text">Mínimo <?= AUTH_MIN_PASSWORD ?> caracteres, con mayúscula, minúscula, número y símbolo.</div>
                            <div class="invalid-feedback">La contraseña no cumple los requisitos.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="email">Correo</label>
                    <div class="col-sm-4">
                        <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= h($d['email']) ?>">
                        <div class="form-text">Se usa para recuperar la contraseña y recibir alertas.</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="telefono">Teléfono</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20" value="<?= h($d['telefono']) ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="id_rol">Rol</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="id_rol" name="id_rol" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int) $r['id_rol'] ?>" <?= (string) $r['id_rol'] === (string) $d['id_rol'] ? 'selected' : '' ?>><?= h($r['rol_descripcion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Define qué páginas y acciones tiene el usuario (ver <a href="?module=permiso">Permisos por rol</a>).</div>
                        <div class="invalid-feedback">Selecciona el rol.</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="id_funcionario">Funcionario</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="id_funcionario" name="id_funcionario">
                            <option value="">Sin vincular</option>
                            <?php foreach ($funcionarios as $f): ?>
                                <option value="<?= (int) $f['id_funcionario'] ?>" data-correo="<?= h($f['per_correo']) ?>" data-tel="<?= h($f['per_telefono']) ?>" data-nombre="<?= h($f['per_nombre_completo']) ?>"
                                    <?= (string) $f['id_funcionario'] === (string) $d['id_funcionario'] ? 'selected' : '' ?>><?= h($f['per_nombre_completo']) ?> (<?= h($f['car_descripcion']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Persona del personal a la que pertenece este usuario.</div>
                    </div>
                </div>

                <?php if ($editar): ?>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="foto">Foto</label>
                        <div class="col-sm-6">
                            <input type="file" class="form-control" id="foto" name="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                            <div class="mt-2"><img src="images/user/<?= $d['foto'] ? h(rawurlencode($d['foto'])) : 'user-default.png' ?>" alt="Foto de usuario" class="img-thumbnail" style="width:120px"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row"><div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="?module=user" class="btn btn-secondary">Cancelar</a>
                </div></div>
            </form>
        </div>
    </div></div></div>
</section>
<script>
    (function () {
        const form = document.getElementById('formUser');
        // Al elegir un funcionario se sugieren nombre, correo y teléfono de su ficha (si los campos están vacíos)
        document.getElementById('id_funcionario').addEventListener('change', function () {
            const o = this.options[this.selectedIndex];
            if (!o.value) return;
            [['name_user', 'nombre'], ['email', 'correo'], ['telefono', 'tel']].forEach(function (par) {
                const campo = document.getElementById(par[0]);
                if (campo && campo.value.trim() === '' && o.dataset[par[1]]) campo.value = o.dataset[par[1]];
            });
        });
        form.addEventListener('submit', function (e) {
            let ok = true;
            form.querySelectorAll('input[required], select[required]').forEach(function (el) {
                let valido = el.value.trim() !== '';
                if (el.id === 'password' && valido) {
                    const v = el.value;
                    valido = v.length >= <?= AUTH_MIN_PASSWORD ?> && /[a-z]/.test(v) && /[A-Z]/.test(v) && /\d/.test(v) && /[^a-zA-Z0-9]/.test(v);
                }
                el.classList.toggle('is-invalid', !valido);
                if (!valido) ok = false;
            });
            if (!ok) { e.preventDefault(); form.querySelector('.is-invalid').focus(); }
        });
    })();
</script>
