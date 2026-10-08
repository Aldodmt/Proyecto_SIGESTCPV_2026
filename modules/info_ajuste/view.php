<?php
$user_gua = $_SESSION['username'] ?? 'Desconocido';
?>

<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item active">Filtrar Ajustes</li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                <i class="bi bi-funnel"></i> Filtrar Ajustes
            </h1>
            <hr>
        </div>
    </div>
</section>

<section class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="row g-3" id="formFiltrosAjuste">
                        <div class="col-md-3">
                            <label for="fecha_desde" class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" name="fecha_desde" id="fecha_desde"
                                value="<?= $_POST['fecha_desde'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta"
                                value="<?= $_POST['fecha_hasta'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado">
                                <option value="">--Todos los estados--</option>
                                <option value="activo" <?= ($_POST['estado'] ?? '') == 'activo' ? 'selected' : '' ?>>Activo
                                </option>
                                <option value="anulado" <?= ($_POST['estado'] ?? '') == 'anulado' ? 'selected' : '' ?>>
                                    Anulado</option>
                                <option value="pendiente" <?= ($_POST['estado'] ?? '') == 'pendiente' ? 'selected' : '' ?>>
                                    Pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="producto" class="form-label">Producto</label>
                            <select class="form-select" name="producto" id="producto">
                                <option value="">--Todos los productos--</option>
                                <?php
                                $sqlProductos = mysqli_query($mysqli, "SELECT DISTINCT p_descrip FROM v_ajuste ORDER BY p_descrip ASC");
                                while ($row = mysqli_fetch_assoc($sqlProductos)) {
                                    $selected = ($_POST['producto'] ?? '') == $row['p_descrip'] ? 'selected' : '';
                                    echo "<option value='{$row['p_descrip']}' $selected>{$row['p_descrip']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-success" id="btnImprimirAjuste">
                                <i class="bi bi-printer"></i> Imprimir PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            // --- FILTROS ---
            $fecha_desde = $_POST['fecha_desde'] ?? null;
            $fecha_hasta = $_POST['fecha_hasta'] ?? null;
            $estado = $_POST['estado'] ?? null;
            $producto = $_POST['producto'] ?? null;

            $queryStr = "SELECT * FROM v_ajuste WHERE 1=1";

            if ($fecha_desde)
                $queryStr .= " AND fecha_ajuste >= '$fecha_desde'";
            if ($fecha_hasta)
                $queryStr .= " AND fecha_ajuste <= '$fecha_hasta'";
            if ($estado)
                $queryStr .= " AND estado = '" . mysqli_real_escape_string($mysqli, $estado) . "'";
            if ($producto)
                $queryStr .= " AND p_descrip = '" . mysqli_real_escape_string($mysqli, $producto) . "'";

            $queryStr .= " ORDER BY fecha_ajuste DESC";

            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover" id="tablaAjuste">
                        <thead>
                            <tr>
                                <th class="text-center">ID Ajuste</th>
                                <th class="text-center">Usuario</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Cantidad Anterior</th>
                                <th class="text-center">Cantidad Ajustada</th>
                                <th class="text-center">Cantidad Final</th>
                                <th class="text-center">Motivo</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($query && mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                                    $cantidad_final = $data['cantidad_anterior'] - $data['cantidad_ajustada'];
                                    echo "<tr>
                                            <td class='text-center'>{$data['id_ajuste']}</td>
                                            <td class='text-center'>{$data['name_user']}</td>
                                            <td class='text-center'>{$data['fecha_ajuste']}</td>
                                            <td class='text-center'>{$data['p_descrip']}</td>
                                            <td class='text-center'>{$data['cantidad_anterior']}</td>
                                            <td class='text-center'>{$data['cantidad_ajustada']}</td>
                                            <td class='text-center'>{$cantidad_final}</td>
                                            <td class='text-center'>{$data['motivo']}</td>
                                            <td class='text-center'>{$data['estado']}</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No se encontraron resultados.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    document.getElementById('btnImprimirAjuste').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.text("Informe de Ajustes", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        doc.autoTable({
            html: '#tablaAjuste',
            startY: 35,
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

        doc.save('InformeAjuste.pdf');
    });
</script>