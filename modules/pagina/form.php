<?php
require_once __DIR__ . '/../../config/ui.php';
$db = $GLOBALS['mysqli'];

$s = $db->prepare("SELECT * FROM pagina WHERE id_pagina = ?");
$id = (int) ($_GET['id'] ?? 0);
$s->bind_param('i', $id);
$s->execute();
$d = $s->get_result()->fetch_assoc();
$s->close();
if (!$d) {
    echo '<div class="alert alert-warning">La página no existe.</div>';
    return;
}
$modulos = $db->query("SELECT id_modulo, mod_nombre FROM modulo ORDER BY mod_orden")->fetch_all(MYSQLI_ASSOC);

ui_cabecera('Modificar página', 'bi-pencil-square', [['Páginas y módulos', '?module=pagina'], ['Modificar']]);
?>
<section class="app-content">
    <div class="row"><div class="col-md-12">
        <?php ui_alertas(); ?>
        <div class="card"><div class="card-body">
            <form action="modules/pagina/proses.php?act=update" method="POST">
                <input type="hidden" name="id" value="<?= (int) $d['id_pagina'] ?>">
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Clave de ruta</label>
                    <div class="col-sm-4"><input type="text" class="form-control" value="<?= h($d['pag_clave']) ?>" readonly></div>
                    <div class="col-sm-6 form-text">Viene de <code>config/routes.php</code>; no se cambia desde aquí.</div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="pag_nombre">Nombre</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="pag_nombre" name="pag_nombre" maxlength="80" value="<?= h($d['pag_nombre']) ?>" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="id_modulo">Módulo</label>
                    <div class="col-sm-4">
                        <select class="form-select" id="id_modulo" name="id_modulo" required>
                            <?php foreach ($modulos as $m): ?>
                                <option value="<?= (int) $m['id_modulo'] ?>" <?= (int) $m['id_modulo'] === (int) $d['id_modulo'] ? 'selected' : '' ?>><?= h($m['mod_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="pag_estado">Estado</label>
                    <div class="col-sm-3">
                        <select class="form-select" id="pag_estado" name="pag_estado">
                            <option value="activo" <?= $d['pag_estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $d['pag_estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                        <div class="form-text">Una página inactiva no aparece en el menú ni se puede abrir.</div>
                    </div>
                </div>
                <div class="row"><div class="offset-sm-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="?module=pagina" class="btn btn-secondary">Cancelar</a>
                </div></div>
            </form>
        </div></div>
    </div></div>
</section>
