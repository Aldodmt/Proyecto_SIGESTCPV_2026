<?php
require_once __DIR__ . '/../../config/ui.php';
$db = $GLOBALS['mysqli'];

$editar = ($_GET['form'] ?? '') === 'edit';
$d = ['id_rol' => '', 'rol_descripcion' => '', 'rol_observacion' => '', 'rol_estado' => 'activo', 'rol_protegido' => 0];

if ($editar) {
    $s = $db->prepare("SELECT * FROM rol WHERE id_rol = ?");
    $id = (int) ($_GET['id'] ?? 0);
    $s->bind_param('i', $id);
    $s->execute();
    $d = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$d) {
        echo '<div class="alert alert-warning">El rol no existe.</div>';
        return;
    }
    if ((int) $d['rol_protegido'] === 1) {
        echo '<div class="alert alert-warning"><i class="bi bi-lock"></i> Este es un rol del sistema (protegido): tiene acceso total y no se puede modificar.</div>';
        return;
    }
}
if (!empty($_SESSION['old_rol'])) {
    $d = array_merge($d, $_SESSION['old_rol']);
    unset($_SESSION['old_rol']);
}

ui_cabecera($editar ? 'Modificar rol' : 'Agregar rol', $editar ? 'bi-pencil-square' : 'bi-plus-circle', [['Roles', '?module=rol'], [$editar ? 'Modificar' : 'Agregar']]);
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>
        <div class="card"><div class="card-body">
            <form id="formRol" action="modules/rol/proses.php?act=<?= $editar ? 'update' : 'insert' ?>" method="POST" novalidate>
                <?php if ($editar): ?><input type="hidden" name="id" value="<?= (int) $d['id_rol'] ?>"><?php endif; ?>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="rol_descripcion">Nombre del rol</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="rol_descripcion" name="rol_descripcion" maxlength="50" value="<?= h($d['rol_descripcion']) ?>" required autofocus>
                        <div class="invalid-feedback">Ingresa el nombre del rol (máximo 50 caracteres).</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="rol_observacion">Descripción</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="rol_observacion" name="rol_observacion" maxlength="150" value="<?= h($d['rol_observacion']) ?>"
                               placeholder="Para qué sirve este rol (opcional)">
                    </div>
                </div>
                <?php if ($editar): ?>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="rol_estado">Estado</label>
                        <div class="col-sm-3">
                            <select class="form-select" id="rol_estado" name="rol_estado">
                                <option value="activo" <?= $d['rol_estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $d['rol_estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row"><div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="?module=rol" class="btn btn-secondary">Cancelar</a>
                </div></div>
            </form>
        </div></div>
    </div></div>
</section>
<script>
    document.getElementById('formRol').addEventListener('submit', function (e) {
        const d = document.getElementById('rol_descripcion');
        d.classList.toggle('is-invalid', d.value.trim() === '');
        if (d.value.trim() === '') e.preventDefault();
    });
</script>
