<?php
// Informe general de referenciales: se elige el referencial y aparecen sus filtros. Ver config/informes.php.
require_once __DIR__ . '/../../config/ui.php';
require_once __DIR__ . '/../../config/informes.php';

$user_gua = $_SESSION['username'] ?? 'Desconocido';
$catalogo = informe_ref_cfg();

// Referencial elegido (lista blanca: si no existe en el arreglo, se ignora)
$ref = (string) ($_GET['ref'] ?? '');
$cfg = $catalogo[$ref] ?? null;

// Agrupa las opciones del selector por grupo (General / Personal / Compras)
$grupos = [];
foreach ($catalogo as $clave => $c) {
    $grupos[$c['grupo']][$clave] = $c['titulo'];
}

$resultado = null;
if ($cfg) {
    $valores = [];
    foreach ($cfg['filtros'] as $f) {
        $valores[$f[0]] = $_GET['f_' . $f[0]] ?? '';
    }
    $resultado = informe_ref_ejecutar($ref, $valores);
}
?>
<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item active">Informe de referenciales</li>
        </ol>
    </div>
    <div class="row">
        <div class="col">
            <h1 class="display-12"><i class="bi bi-funnel"></i> Informe de referenciales</h1>
            <hr>
        </div>
    </div>
</section>

<section class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3" id="formFiltros">
                        <input type="hidden" name="module" value="info_referencial">

                        <div class="col-md-4">
                            <label for="ref" class="form-label">Referencial</label>
                            <!-- Al cambiar de referencial se recarga la pantalla para mostrar sus filtros (sin conservar los del anterior) -->
                            <select class="form-select" name="ref" id="ref" onchange="this.form.querySelectorAll('[name^=f_]').forEach(e => e.disabled = true); this.form.submit();">
                                <option value="">-- Seleccione un referencial --</option>
                                <?php foreach ($grupos as $grupo => $items) { ?>
                                    <optgroup label="<?= h($grupo) ?>">
                                        <?php foreach ($items as $clave => $titulo) { ?>
                                            <option value="<?= h($clave) ?>" <?= $clave === $ref ? 'selected' : '' ?>><?= h($titulo) ?></option>
                                        <?php } ?>
                                    </optgroup>
                                <?php } ?>
                            </select>
                        </div>

                        <?php if ($cfg) { ?>
                            <div class="col-12"><hr class="my-1"><span class="text-muted small">Filtros de <?= h(mb_strtolower($cfg['titulo'])) ?></span></div>
                            <?php foreach ($cfg['filtros'] as $f) {
                                [$nombre, $etiqueta, $tipo] = $f;
                                $valor = (string) ($_GET['f_' . $nombre] ?? ''); ?>
                                <div class="col-md-3">
                                    <label for="f_<?= h($nombre) ?>" class="form-label"><?= h($etiqueta) ?></label>
                                    <?php if ($tipo === 'select' || $tipo === 'estado') { ?>
                                        <select class="form-select" name="f_<?= h($nombre) ?>" id="f_<?= h($nombre) ?>">
                                            <option value="">Todos</option>
                                            <?php foreach (informe_ref_opciones($f) as [$v, $t]) { ?>
                                                <option value="<?= h($v) ?>" <?= (string) $v === $valor ? 'selected' : '' ?>><?= h($t) ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } else { ?>
                                        <input type="<?= $tipo === 'numero' ? 'number' : 'text' ?>" class="form-control" name="f_<?= h($nombre) ?>"
                                            id="f_<?= h($nombre) ?>" value="<?= h($valor) ?>" <?= $tipo === 'numero' ? 'min="0"' : '' ?>>
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <div class="col-md-12 text-end mt-2">
                                <a class="btn btn-secondary" href="?module=info_referencial&ref=<?= h($ref) ?>"><i class="bi bi-eraser"></i> Limpiar</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                                <button type="button" class="btn btn-success" id="btnImprimir"><i class="bi bi-printer"></i> Imprimir PDF</button>
                            </div>
                        <?php } ?>
                    </form>
                </div>
            </div>

            <?php if (!$cfg) { ?>
                <div class="alert alert-info mt-4"><i class="bi bi-info-circle"></i> Seleccione un referencial para ver sus filtros y resultados.</div>
            <?php } else { ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h2 class="h4 mb-0">Resultados: <?= h($cfg['titulo']) ?>
                            <span class="badge text-bg-secondary ms-2"><?= count($resultado['filas']) ?> registro(s)</span></h2>
                        <?php if ($resultado['aplicados']) { ?>
                            <div class="small text-muted mt-1">Filtros aplicados:
                                <?= h(implode(' · ', array_map(fn($a) => $a[0] . ' = ' . $a[1], $resultado['aplicados']))) ?></div>
                        <?php } ?>
                        <?php if ($resultado['ignorados']) { ?>
                            <div class="small text-warning mt-1">Se ignoró un valor no válido en: <?= h(implode(', ', $resultado['ignorados'])) ?></div>
                        <?php } ?>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover" id="tablaInforme">
                            <thead>
                                <tr>
                                    <?php foreach ($cfg['cols'] as [$titulo]) { ?>
                                        <th class="text-center"><?= h($titulo) ?></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado['filas']) {
                                    foreach ($resultado['filas'] as $fila) { ?>
                                        <tr>
                                            <?php foreach ($cfg['cols'] as [, $clave]) { ?>
                                                <td class="text-center"><?= h($fila[$clave] ?? '') ?></td>
                                            <?php } ?>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr><td colspan="<?= count($cfg['cols']) ?>" class="text-center">No se encontraron resultados.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<?php if ($cfg) { ?>
    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script>
        document.getElementById('btnImprimir').addEventListener('click', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.setFontSize(16);
            doc.text(<?= json_encode('Informe de ' . mb_strtolower($cfg['titulo']), JSON_UNESCAPED_UNICODE) ?>, 14, 20);
            doc.setFontSize(10);
            doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 14, 28);
            doc.text(<?= json_encode('Generado por: ' . $user_gua, JSON_UNESCAPED_UNICODE) ?>, 196, 28, { align: "right" });
            <?php if ($resultado['aplicados']) { ?>
                doc.text(<?= json_encode('Filtros: ' . implode(' · ', array_map(fn($a) => $a[0] . ' = ' . $a[1], $resultado['aplicados'])), JSON_UNESCAPED_UNICODE) ?>, 14, 34);
            <?php } ?>

            doc.autoTable({
                html: '#tablaInforme',
                startY: 40,
                theme: 'grid',
                headStyles: { fillColor: [22, 160, 133], textColor: 255, fontStyle: 'bold' },
                alternateRowStyles: { fillColor: [240, 240, 240] },
                styles: { fontSize: 10, cellPadding: 2 }
            });

            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.text(`Página ${i} de ${pageCount}`, doc.internal.pageSize.getWidth() - 20, doc.internal.pageSize.getHeight() - 10);
            }

            doc.save(<?= json_encode('Informe_' . $ref . '.pdf') ?>);
        });
    </script>
<?php } ?>
