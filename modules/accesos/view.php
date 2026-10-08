<?php
// Consulta de intentos de acceso (solo Super Admin)
if (($_SESSION['permisos_acceso'] ?? '') !== 'Super Admin') {
    echo "<div class='alert alert-danger mt-3'>No tienes permiso para ver esta sección.</div>";
    return;
}

// Filtros: por defecto, los últimos 7 días
$fechaOk = fn($f) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) && checkdate((int) substr($f, 5, 2), (int) substr($f, 8, 2), (int) substr($f, 0, 4));
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-7 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');
if (!$fechaOk($desde)) $desde = date('Y-m-d', strtotime('-7 days'));
if (!$fechaOk($hasta)) $hasta = date('Y-m-d');
if ($desde > $hasta) { [$desde, $hasta] = [$hasta, $desde]; }

$resultado = $_GET['resultado'] ?? '';
$validos = ['EXITOSO', 'FALLIDO', 'BLOQUEADO', 'RECUPERACION'];
if (!in_array($resultado, $validos, true)) $resultado = '';
$usuarioFiltro = trim($_GET['usuario'] ?? '');

$sql = "SELECT id_log, username, password_enmascarada, fecha_hora, ip, resultado, motivo, tiempo_ms, user_agent
        FROM log_accesos WHERE fecha_hora >= ? AND fecha_hora < DATE_ADD(?, INTERVAL 1 DAY)";
$tipos = 'ss';
$params = [$desde, $hasta];
if ($resultado !== '') { $sql .= " AND resultado = ?"; $tipos .= 's'; $params[] = $resultado; }
if ($usuarioFiltro !== '') { $sql .= " AND username LIKE ?"; $tipos .= 's'; $params[] = '%' . $usuarioFiltro . '%'; }
$sql .= " ORDER BY fecha_hora DESC, id_log DESC LIMIT 5000";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$tot = ['EXITOSO' => 0, 'FALLIDO' => 0, 'BLOQUEADO' => 0, 'RECUPERACION' => 0];
$sumaMs = 0;
$nMs = 0;
foreach ($filas as $f) {
    if (isset($tot[$f['resultado']])) $tot[$f['resultado']]++;
    if ($f['resultado'] !== 'RECUPERACION') { $sumaMs += (int) $f['tiempo_ms']; $nMs++; }
}
$promMs = $nMs ? round($sumaMs / $nMs) : 0;
$colores = ['EXITOSO' => 'success', 'FALLIDO' => 'danger', 'BLOQUEADO' => 'warning', 'RECUPERACION' => 'info'];
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item active">Registro de accesos</li>
        </ol>
    </div>
    <h1 class="display-6"><i class="bi bi-shield-lock"></i> Registro de intentos de acceso</h1>
    <hr>
</section>


<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="module" value="accesos">
            <div class="col-md-3">
                <label class="form-label" for="desde">Desde</label>
                <input type="date" class="form-control" id="desde" name="desde" value="<?= $h($desde) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="hasta">Hasta</label>
                <input type="date" class="form-control" id="hasta" name="hasta" value="<?= $h($hasta) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="resultado">Resultado</label>
                <select class="form-select" id="resultado" name="resultado">
                    <option value="">Todos</option>
                    <?php foreach ($validos as $v): ?>
                        <option value="<?= $v ?>" <?= $resultado === $v ? 'selected' : '' ?>><?= ucfirst(strtolower($v)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="usuario">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" value="<?= $h($usuarioFiltro) ?>" placeholder="Buscar">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Consultar</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-6 col-md-2"><div class="card text-center"><div class="card-body py-2"><div class="fs-4 text-success"><?= $tot['EXITOSO'] ?></div><small>Exitosos</small></div></div></div>
    <div class="col-6 col-md-2"><div class="card text-center"><div class="card-body py-2"><div class="fs-4 text-danger"><?= $tot['FALLIDO'] ?></div><small>Fallidos</small></div></div></div>
    <div class="col-6 col-md-2"><div class="card text-center"><div class="card-body py-2"><div class="fs-4 text-warning"><?= $tot['BLOQUEADO'] ?></div><small>Sobre cuentas bloqueadas</small></div></div></div>
    <div class="col-6 col-md-2"><div class="card text-center"><div class="card-body py-2"><div class="fs-4 text-info"><?= $tot['RECUPERACION'] ?></div><small>Recuperaciones</small></div></div></div>
    <div class="col-12 col-md-4"><div class="card text-center"><div class="card-body py-2"><div class="fs-4"><?= $promMs ?> ms</div><small>Tiempo promedio de procesamiento del login</small></div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <h2>Intentos de acceso (<?= count($filas) ?>)</h2>
        <?php if (count($filas) >= 5000): ?>
            <div class="alert alert-warning">Se muestran los 5.000 más recientes. Acota el rango de fechas para ver el resto.</div>
        <?php endif; ?>
        <div class="table-responsive">
            <table id="tablaAccesos" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">Fecha y hora</th>
                        <th class="text-center">Usuario</th>
                        <th class="text-center">Contraseña</th>
                        <th class="text-center">IP</th>
                        <th class="text-center">Resultado</th>
                        <th class="text-center">Detalle</th>
                        <th class="text-center">Tiempo</th>
                        <th class="text-center">Navegador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td class="text-center" data-order="<?= $h($f['fecha_hora']) ?>"><?= date('d/m/Y H:i:s', strtotime($f['fecha_hora'])) ?></td>
                            <td><?= $h($f['username']) ?></td>
                            <td class="text-center"><code><?= $h($f['password_enmascarada']) ?></code></td>
                            <td class="text-center"><?= $h($f['ip']) ?></td>
                            <td class="text-center"><span class="badge text-bg-<?= $colores[$f['resultado']] ?? 'secondary' ?>"><?= $h($f['resultado']) ?></span></td>
                            <td><?= $h($f['motivo']) ?></td>
                            <td class="text-center"><?= (int) $f['tiempo_ms'] ?> ms</td>
                            <td><small><?= $h(mb_substr($f['user_agent'], 0, 60)) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#tablaAccesos').DataTable({
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
            pageLength: 25,
            order: [[0, 'desc']]
        });
    });
</script>
