<?php
require_once __DIR__ . '/../../config/ui.php';
$db = $GLOBALS['mysqli'];

$editar = ($_GET['form'] ?? '') === 'edit';
$vacio = ['id_persona' => '', 'per_nombre' => '', 'per_apellido' => '', 'per_ci' => '', 'per_fecha_nac' => '', 'per_telefono' => '',
    'per_correo' => '', 'per_direccion' => '', 'id_genero' => '', 'id_nacionalidad' => '', 'cod_ciudad' => '', 'id_departamento' => '',
    'per_estado' => 'activo'];
$d = $vacio;

if ($editar) {
    $s = $db->prepare("SELECT * FROM v_persona WHERE id_persona = ?");
    $id = (int) ($_GET['id'] ?? 0);
    $s->bind_param('i', $id);
    $s->execute();
    $d = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$d) {
        echo '<div class="alert alert-warning">La persona no existe.</div>';
        return;
    }
}
// Si el servidor devolvió el formulario por un error de validación, se recuperan los datos tecleados
if (!empty($_SESSION['old_persona'])) {
    $d = array_merge($d, $_SESSION['old_persona']);
    unset($_SESSION['old_persona']);
}

// Listas (solo activos, más el valor ya guardado aunque esté de baja)
$lista = function (string $sql, $actual) use ($db) {
    return $db->query(str_replace(':actual', (int) $actual, $sql))->fetch_all(MYSQLI_ASSOC);
};
$generos = $lista("SELECT id_genero, gen_descripcion FROM genero WHERE gen_estado = 'activo' OR id_genero = :actual ORDER BY gen_descripcion", $d['id_genero']);
$nacs = $lista("SELECT id_nacionalidad, nac_descripcion FROM nacionalidad WHERE nac_estado = 'activo' OR id_nacionalidad = :actual ORDER BY nac_descripcion", $d['id_nacionalidad']);
$deptos = $db->query("SELECT id_departamento, dep_descripcion FROM departamento ORDER BY dep_descripcion")->fetch_all(MYSQLI_ASSOC);
$ciudades = $db->query("SELECT cod_ciudad, descrip_ciudad, id_departamento FROM ciudad ORDER BY descrip_ciudad")->fetch_all(MYSQLI_ASSOC);

// El departamento no se guarda: se deduce de la ciudad (por eso también al volver de un error)
if ($d['id_departamento'] === '' && $d['cod_ciudad'] !== '') {
    foreach ($ciudades as $c) {
        if ((string) $c['cod_ciudad'] === (string) $d['cod_ciudad']) {
            $d['id_departamento'] = $c['id_departamento'];
        }
    }
}

ui_cabecera($editar ? 'Modificar persona' : 'Agregar persona', $editar ? 'bi-pencil-square' : 'bi-person-plus',
    [['Personas', '?module=persona'], [$editar ? 'Modificar' : 'Agregar']]);
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>
        <div class="card"><div class="card-body">
            <form id="formPersona" action="modules/persona/proses.php?act=<?= $editar ? 'update' : 'insert' ?>" method="POST" novalidate>
                <?php if ($editar): ?><input type="hidden" name="id" value="<?= (int) $d['id_persona'] ?>"><?php endif; ?>

                <h2 class="h6 text-secondary text-uppercase mb-3">Datos personales</h2>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="per_nombre">Nombres</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="per_nombre" name="per_nombre" maxlength="80" value="<?= h($d['per_nombre']) ?>" required>
                        <div class="invalid-feedback">Ingresa los nombres.</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="per_apellido">Apellidos</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="per_apellido" name="per_apellido" maxlength="80" value="<?= h($d['per_apellido']) ?>" required>
                        <div class="invalid-feedback">Ingresa los apellidos.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="per_ci">C.I. / Documento</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="per_ci" name="per_ci" maxlength="15" value="<?= h($d['per_ci']) ?>" required
                               pattern="[A-Za-z0-9.\-]{4,15}" placeholder="Ej.: 4.123.456">
                        <div class="invalid-feedback">Documento de 4 a 15 caracteres (letras, números, puntos o guiones).</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="per_fecha_nac">Fecha de nacimiento</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" id="per_fecha_nac" name="per_fecha_nac" value="<?= h($d['per_fecha_nac']) ?>"
                               min="1900-01-01" max="<?= date('Y-m-d') ?>" required>
                        <div class="invalid-feedback">Fecha válida, no futura.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="id_genero">Género</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="id_genero" name="id_genero" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($generos as $g): ?>
                                <option value="<?= (int) $g['id_genero'] ?>" <?= (string) $g['id_genero'] === (string) $d['id_genero'] ? 'selected' : '' ?>><?= h($g['gen_descripcion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecciona el género.</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="id_nacionalidad">Nacionalidad</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="id_nacionalidad" name="id_nacionalidad" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($nacs as $n): ?>
                                <option value="<?= (int) $n['id_nacionalidad'] ?>" <?= (string) $n['id_nacionalidad'] === (string) $d['id_nacionalidad'] ? 'selected' : '' ?>><?= h($n['nac_descripcion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecciona la nacionalidad.</div>
                    </div>
                </div>

                <h2 class="h6 text-secondary text-uppercase mt-4 mb-3">Contacto y ubicación</h2>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="id_departamento">Departamento</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="id_departamento" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($deptos as $dp): ?>
                                <option value="<?= (int) $dp['id_departamento'] ?>" <?= (string) $dp['id_departamento'] === (string) $d['id_departamento'] ? 'selected' : '' ?>><?= h($dp['dep_descripcion']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecciona el departamento.</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="cod_ciudad">Ciudad</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="cod_ciudad" name="cod_ciudad" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($ciudades as $c): ?>
                                <option value="<?= (int) $c['cod_ciudad'] ?>" data-dep="<?= (int) $c['id_departamento'] ?>" <?= (string) $c['cod_ciudad'] === (string) $d['cod_ciudad'] ? 'selected' : '' ?>><?= h($c['descrip_ciudad']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecciona la ciudad.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="per_telefono">Teléfono</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="per_telefono" name="per_telefono" maxlength="20" value="<?= h($d['per_telefono']) ?>"
                               pattern="[0-9+()\- ]{6,20}" placeholder="Ej.: 0981 123 456">
                        <div class="invalid-feedback">Teléfono de 6 a 20 caracteres (números, +, -, paréntesis).</div>
                    </div>
                    <label class="col-sm-2 col-form-label" for="per_correo">Correo</label>
                    <div class="col-sm-4">
                        <input type="email" class="form-control" id="per_correo" name="per_correo" maxlength="150" value="<?= h($d['per_correo']) ?>" placeholder="correo@ejemplo.com">
                        <div class="invalid-feedback">Correo electrónico no válido.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="per_direccion">Dirección</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="per_direccion" name="per_direccion" maxlength="150" value="<?= h($d['per_direccion']) ?>">
                    </div>
                </div>
                <?php if ($editar): ?>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label" for="per_estado">Estado</label>
                        <div class="col-sm-3">
                            <select class="form-select" id="per_estado" name="per_estado">
                                <option value="activo" <?= $d['per_estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= $d['per_estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row mt-4"><div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="?module=persona" class="btn btn-secondary">Cancelar</a>
                </div></div>
            </form>
        </div></div>
    </div></div>
</section>
<script>
    (function () {
        const dep = document.getElementById('id_departamento');
        const ciu = document.getElementById('cod_ciudad');
        const opciones = Array.from(ciu.options);

        // Ciudad depende del departamento: solo se ofrecen las ciudades del departamento elegido
        function filtrarCiudades(conservar) {
            const actual = conservar ? ciu.value : '';
            ciu.innerHTML = '';
            opciones.forEach(function (o) {
                if (o.value === '' || o.dataset.dep === dep.value) ciu.appendChild(o);
            });
            ciu.value = actual;
            if (ciu.value !== actual) ciu.value = '';
        }
        dep.addEventListener('change', function () { filtrarCiudades(false); });
        filtrarCiudades(true);

        // Validación en el navegador (el servidor vuelve a validar todo)
        const form = document.getElementById('formPersona');
        form.addEventListener('submit', function (e) {
            let ok = true;
            form.querySelectorAll('input, select').forEach(function (el) {
                const valido = el.checkValidity() && (!el.required || el.value.trim() !== '');
                el.classList.toggle('is-invalid', !valido);
                if (!valido) ok = false;
            });
            if (!ok) { e.preventDefault(); form.querySelector('.is-invalid').focus(); }
        });
        form.querySelectorAll('input, select').forEach(function (el) {
            el.addEventListener('input', function () { el.classList.toggle('is-invalid', !el.checkValidity()); });
        });
    })();
</script>
