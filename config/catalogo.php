<?php
/*
  Motor de catálogos (tablas de referencia con una descripción y un estado): Género, Nacionalidad, Cargo...
  Cada módulo es un archivo de una línea que llama a catalogo_view / catalogo_form / catalogo_proses.
  Para sumar un catálogo nuevo basta agregarlo al arreglo de abajo (y su tabla en la base de datos).

  Eliminar vs. dar de baja:
   - "Eliminar" borra físicamente y solo se permite si nadie usa el registro (lo garantiza también la clave foránea).
   - "Dar de baja" lo marca inactivo: deja de ofrecerse en formularios nuevos pero conserva el historial.
*/
require_once __DIR__ . '/ui.php';
require_once __DIR__ . '/permisos.php';

function catalogo_cfg(string $key): array
{
    static $c = [
        'genero' => [
            'tabla' => 'genero', 'pk' => 'id_genero', 'col' => 'gen_descripcion', 'estado' => 'gen_estado',
            'singular' => 'Género', 'plural' => 'Géneros', 'max' => 30, 'icono' => 'bi-gender-ambiguous',
            'usos' => [['persona', 'id_genero']],
        ],
        'nacionalidad' => [
            'tabla' => 'nacionalidad', 'pk' => 'id_nacionalidad', 'col' => 'nac_descripcion', 'estado' => 'nac_estado',
            'singular' => 'Nacionalidad', 'plural' => 'Nacionalidades', 'max' => 60, 'icono' => 'bi-flag',
            'usos' => [['persona', 'id_nacionalidad']],
        ],
        'cargo' => [
            'tabla' => 'cargo', 'pk' => 'id_cargo', 'col' => 'car_descripcion', 'estado' => 'car_estado',
            'singular' => 'Cargo', 'plural' => 'Cargos', 'max' => 60, 'icono' => 'bi-briefcase',
            'usos' => [['funcionario', 'id_cargo']],
        ],
    ];
    return $c[$key];
}

function catalogo_usos(array $cfg, int $id): int
{
    $db = $GLOBALS['mysqli'];
    $total = 0;
    foreach ($cfg['usos'] as [$tabla, $col]) {
        $s = $db->prepare("SELECT COUNT(*) FROM `$tabla` WHERE `$col` = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $total += (int) $s->get_result()->fetch_row()[0];
        $s->close();
    }
    return $total;
}

/* ------------------------------------------------------------------ Listado */
function catalogo_view(string $key): void
{
    $cfg = catalogo_cfg($key);
    $db = $GLOBALS['mysqli'];
    ui_cabecera('Datos de ' . $cfg['plural'], $cfg['icono'], [[$cfg['plural']]], 'Agregar', "?module=form_$key&form=add");
    echo '<section class="app-content"><div class="row"><div class="col-md-12">';
    ui_alertas();

    $filas = $db->query("SELECT * FROM `{$cfg['tabla']}` ORDER BY `{$cfg['col']}`")->fetch_all(MYSQLI_ASSOC);
    echo '<div class="card"><div class="card-body"><div class="table-responsive">'
        . '<table id="dataTables1" class="table table-bordered table-striped table-hover">'
        . '<thead><tr><th class="text-center">Código</th><th>Descripción</th><th class="text-center">Estado</th><th class="text-center">Acciones</th></tr></thead><tbody>';
    foreach ($filas as $f) {
        $id = (int) $f[$cfg['pk']];
        $activo = $f[$cfg['estado']] === 'activo';
        $usos = catalogo_usos($cfg, $id);
        echo '<tr><td class="text-center">' . $id . '</td><td>' . h($f[$cfg['col']]) . '</td>'
            . '<td class="text-center">' . ui_estado_badge($f[$cfg['estado']]) . '</td><td class="text-center">'
            . "<a class='btn btn-primary btn-sm me-1' href='?module=form_$key&form=edit&id=$id' title='Modificar'><i class='bi bi-pencil-square'></i></a>"
            . ($activo
                ? "<a class='btn btn-warning btn-sm me-1' href='modules/$key/proses.php?act=off&id=$id' title='Dar de baja'><i class='bi bi-slash-circle'></i></a>"
                : "<a class='btn btn-success btn-sm me-1' href='modules/$key/proses.php?act=on&id=$id' title='Reactivar'><i class='bi bi-check-lg'></i></a>")
            . ($usos === 0
                ? "<a class='btn btn-danger btn-sm' href='modules/$key/proses.php?act=delete&id=$id' title='Eliminar' onclick=\"return confirm('¿Eliminar definitivamente este registro?');\"><i class='bi bi-trash'></i></a>"
                : '')
            . '</td></tr>';
    }
    echo '</tbody></table></div></div></div></div></div></section>'
        . '<script>$(function(){ $("#dataTables1").DataTable({ pageLength: 10 }); });</script>';
}

/* --------------------------------------------------------------- Formulario */
function catalogo_form(string $key): void
{
    $cfg = catalogo_cfg($key);
    $db = $GLOBALS['mysqli'];
    $editar = ($_GET['form'] ?? '') === 'edit';
    $fila = ['descripcion' => '', 'estado' => 'activo', 'id' => ''];

    if ($editar) {
        $id = (int) ($_GET['id'] ?? 0);
        $s = $db->prepare("SELECT `{$cfg['pk']}` id, `{$cfg['col']}` descripcion, `{$cfg['estado']}` estado FROM `{$cfg['tabla']}` WHERE `{$cfg['pk']}` = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $fila = $s->get_result()->fetch_assoc();
        $s->close();
        if (!$fila) {
            echo '<div class="alert alert-warning">El registro no existe.</div>';
            return;
        }
    }

    ui_cabecera(($editar ? 'Modificar ' : 'Agregar ') . $cfg['singular'], $editar ? 'bi-pencil-square' : 'bi-plus-circle',
        [[$cfg['plural'], "?module=$key"], [$editar ? 'Modificar' : 'Agregar']]);
    ?>
    <section class="app-content"><div class="row"><div class="col-md-12"><div class="card"><div class="card-body">
        <form action="modules/<?= h($key) ?>/proses.php?act=<?= $editar ? 'update' : 'insert' ?>" method="POST" novalidate>
            <?php if ($editar): ?><input type="hidden" name="id" value="<?= (int) $fila['id'] ?>"><?php endif; ?>
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="descripcion">Descripción</label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="<?= (int) $cfg['max'] ?>"
                           value="<?= h($fila['descripcion']) ?>" required autofocus>
                    <div class="invalid-feedback">Ingresa la descripción (máximo <?= (int) $cfg['max'] ?> caracteres).</div>
                </div>
            </div>
            <?php if ($editar): ?>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="estado">Estado</label>
                    <div class="col-sm-3">
                        <select class="form-select" id="estado" name="estado">
                            <option value="activo" <?= $fila['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $fila['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row"><div class="offset-sm-2 col-sm-10">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="?module=<?= h($key) ?>" class="btn btn-secondary">Cancelar</a>
            </div></div>
        </form>
    </div></div></div></div></section>
    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const d = document.getElementById('descripcion');
            d.classList.toggle('is-invalid', d.value.trim() === '');
            if (d.value.trim() === '') e.preventDefault();
        });
    </script>
    <?php
}

/* ------------------------------------------------------------------ Proceso */
function catalogo_proses(string $key): void
{
    $cfg = catalogo_cfg($key);
    guard_endpoint($key);                       // sesión + permiso según act=insert/update/delete/on/off
    $db = $GLOBALS['mysqli'];
    $act = $_GET['act'] ?? '';
    $t = "`{$cfg['tabla']}`";
    $pk = "`{$cfg['pk']}`";
    $col = "`{$cfg['col']}`";
    $est = "`{$cfg['estado']}`";
    $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

    try {
        if ($act === 'insert' || $act === 'update') {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                proses_volver($key, 4);
            }
            $desc = trim(preg_replace('/\s+/', ' ', (string) ($_POST['descripcion'] ?? '')));
            if ($desc === '' || mb_strlen($desc) > $cfg['max']) {
                proses_volver($key, 9, 'La descripción es obligatoria (máximo ' . $cfg['max'] . ' caracteres).');
            }
            // Sin duplicados (sin distinguir mayúsculas), excluyendo el propio registro al editar
            $s = $db->prepare("SELECT COUNT(*) FROM $t WHERE LOWER($col) = LOWER(?) AND $pk <> ?");
            $s->bind_param('si', $desc, $id);
            $s->execute();
            if ((int) $s->get_result()->fetch_row()[0] > 0) {
                proses_volver($key, 5);
            }
            $s->close();

            if ($act === 'insert') {
                $s = $db->prepare("INSERT INTO $t ($col) VALUES (?)");
                $s->bind_param('s', $desc);
                $s->execute();
                proses_volver($key, 1);
            }
            $estado = ($_POST['estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';
            $s = $db->prepare("UPDATE $t SET $col = ?, $est = ? WHERE $pk = ?");
            $s->bind_param('ssi', $desc, $estado, $id);
            $s->execute();
            proses_volver($key, 2);
        }

        if ($act === 'on' || $act === 'off') {
            $estado = $act === 'on' ? 'activo' : 'inactivo';
            $s = $db->prepare("UPDATE $t SET $est = ? WHERE $pk = ?");
            $s->bind_param('si', $estado, $id);
            $s->execute();
            proses_volver($key, $act === 'on' ? 8 : 7);
        }

        if ($act === 'delete') {
            if (catalogo_usos($cfg, $id) > 0) {
                proses_volver($key, 6);
            }
            $s = $db->prepare("DELETE FROM $t WHERE $pk = ?");
            $s->bind_param('i', $id);
            $s->execute();
            proses_volver($key, 3);
        }
    } catch (mysqli_sql_exception $e) {
        proses_volver($key, $e->getCode() === 1451 ? 6 : 4);   // 1451 = restricción de clave foránea
    }
    proses_volver($key, 4);
}
