<?php
require_once __DIR__ . '/../../config/ui.php';
$db = $GLOBALS['mysqli'];

$editar = ($_GET['form'] ?? '') === 'edit';
$d = ['id_funcionario' => '', 'id_persona' => '', 'id_cargo' => '', 'fun_fecha_ingreso' => '', 'fun_estado' => 'activo', 'per_nombre_completo' => '', 'per_ci' => ''];

if ($editar) {
    $s = $db->prepare("SELECT * FROM v_funcionario WHERE id_funcionario = ?");
    $id = (int) ($_GET['id'] ?? 0);
    $s->bind_param('i', $id);
    $s->execute();
    $d = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$d) {
        echo '<div class="alert alert-warning">El funcionario no existe.</div>';
        return;
    }
}
if (!empty($_SESSION['old_funcionario'])) {
    $d = array_merge($d, $_SESSION['old_funcionario']);
    unset($_SESSION['old_funcionario']);
}

// Al agregar: solo personas activas que todavía no son funcionarios
$personas = $editar ? [] : $db->query(
    "SELECT v.id_persona, v.per_nombre_completo, v.per_ci FROM v_persona v
      LEFT JOIN funcionario f ON f.id_persona = v.id_persona
      WHERE v.per_estado = 'activo' AND f.id_funcionario IS NULL ORDER BY v.per_nombre_completo")->fetch_all(MYSQLI_ASSOC);
$cargos = $db->query("SELECT id_cargo, car_descripcion FROM cargo WHERE car_estado = 'activo' OR id_cargo = " . (int) $d['id_cargo'] . " ORDER BY car_descripcion")->fetch_all(MYSQLI_ASSOC);

ui_cabecera($editar ? 'Modificar funcionario' : 'Agregar funcionario', $editar ? 'bi-pencil-square' : 'bi-person-plus',
    [['Funcionarios', '?module=funcionario'], [$editar ? 'Modificar' : 'Agregar']]);
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>
        <div class="card"><div class="card-body">
            <?php if (!$editar && !$personas): ?>
                <div class="alert alert-info mb-0">
                    No hay personas disponibles: para registrar un funcionario primero hay que cargar a la persona.
                    <a href="?module=form_persona&form=add" class="alert-link">Registrar una persona</a>
                </div>
            <?php else: ?>
            <form id="formFuncionario" action="modules/funcionario/proses.php?act=<?= $editar ? 'update' : 'insert' ?>" method="POST" novalidate>
                <?php if ($editar): ?><input type="hidden" name="id" value="<?= (int) $d['id_funcionario'] ?>"><?php endif; ?>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="id_persona">Persona</label>
                    <div class="col-sm-6">
                        <?php if ($editar): ?>
                            <input type="text" class="form-control" value="<?= h($d['per_nombre_completo']) ?> (C.I. <?= h($d['per_ci']) ?>)" readonly>
                        <?php else: ?>
                            <select class="form-select" id="id_persona" name="id_persona" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($personas as $p): ?>
                                    <option value="<?= (int) $p['id_persona'] ?>" <?= (string) $p['id_persona'] === (string) $d['id_persona'] ? 'selected' : '' ?>><?= h($p['per_nombre_completo']) ?> (C.I. <?= h($p['per_ci']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecciona la persona.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="id_cargo">Cargo</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="id_cargo" name="id_cargo" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($cargos as $c): ?>
                                <option value="<?= (int) $c['id_cargo'] ?>" <?= (string) $c['id_cargo'] === (string) $d['id_cargo'] ? 'selected' : '' ?>><?= h($c['car_descripcion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecciona el cargo.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="fun_fecha_ingreso">Fecha de ingreso</label>
                    <div class="col-sm-3">
                        <input type="date" class="form-control" id="fun_fecha_ingreso" name="fun_fecha_ingreso" value="<?= h($d['fun_fecha_ingreso']) ?>"
                               min="1980-01-01" max="<?= date('Y-m-d') ?>" required>
                        <div class="invalid-feedback">Fecha de ingreso válida, no futura.</div>
                    </div>
                </div>
                <?php if ($editar): ?>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="fun_estado">Estado</label>
                        <div class="col-sm-3">
                            <select class="form-select" id="fun_estado" name="fun_estado">
                                <option value="activo" <?= $d['fun_estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $d['fun_estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <div class="form-text">Al pasar a inactivo se bloquea su usuario del sistema, si tiene.</div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row mt-4"><div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="?module=funcionario" class="btn btn-secondary">Cancelar</a>
                </div></div>
            </form>
            <script>
                document.getElementById('formFuncionario').addEventListener('submit', function (e) {
                    let ok = true;
                    this.querySelectorAll('select[required], input[required]').forEach(function (el) {
                        const valido = el.checkValidity() && el.value.trim() !== '';
                        el.classList.toggle('is-invalid', !valido);
                        if (!valido) ok = false;
                    });
                    if (!ok) e.preventDefault();
                });
            </script>
            <?php endif; ?>
        </div></div>
    </div></div>
</section>
