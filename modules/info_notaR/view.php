<?php
$user_gua = $_SESSION['username'] ?? 'Desconocido';
?>

<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item active">Filtrar Notas de Remision</li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                <i class="bi bi-funnel"></i> Filtrar Notas de Remision
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
                    <form method="POST" class="row g-3" id="formFiltrosNota">
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
                                <option value="" selected>--Todos los estados--</option>
                                <option value="activo" <?= ($_POST['estado'] ?? '') == 'activo' ? 'selected' : '' ?>>Activo
                                </option>
                                <option value="anulado" <?= ($_POST['estado'] ?? '') == 'anulado' ? 'selected' : '' ?>>
                                    Anulado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="razon_social" class="form-label">Razón Social</label>
                            <input type="text" class="form-control" name="razon_social" id="razon_social"
                                placeholder="Ej: Empresa X" value="<?= $_POST['razon_social'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="ruc" class="form-label">RUC</label>
                            <input type="text" class="form-control" name="ruc" id="ruc" placeholder="Ej: 80012345-6"
                                value="<?= $_POST['ruc'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="producto" class="form-label">Producto</label>
                            <select class="form-select" name="producto" id="producto">
                                <option value="">--Todos los productos--</option>
                                <?php
                                $sqlProductos = mysqli_query($mysqli, "SELECT DISTINCT p_descrip FROM v_notaR ORDER BY p_descrip ASC");
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
                            <button type="button" class="btn btn-success" id="btnImprimirNota">
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
            $razon_social = $_POST['razon_social'] ?? null;
            $ruc = $_POST['ruc'] ?? null;
            $producto = $_POST['producto'] ?? null;

            // --- CONSULTA SQL DINÁMICA ---
            $queryStr = "SELECT * FROM v_notaR WHERE 1=1";
            if ($fecha_desde)
                $queryStr .= " AND fecha >= '$fecha_desde'";
            if ($fecha_hasta)
                $queryStr .= " AND fecha <= '$fecha_hasta'";
            if ($estado)
                $queryStr .= " AND estado = '$estado'";
            if ($razon_social)
                $queryStr .= " AND razon_social LIKE '%" . mysqli_real_escape_string($mysqli, $razon_social) . "%'";

            if ($ruc) {
                $ruc_limpio = str_replace('-', '', $ruc);
                $queryStr .= " AND REPLACE(ruc, '-', '') LIKE '%" . mysqli_real_escape_string($mysqli, $ruc_limpio) . "%'";
            }
            if ($producto)
                $queryStr .= " AND p_descrip = '" . mysqli_real_escape_string($mysqli, $producto) . "'";

            $queryStr .= " ORDER BY fecha DESC";

            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="tablaNota">
                        <thead>
                            <tr>
                                <th class="text-center">ID Nota</th>
                                <th class="text-center">ID Compra</th>
                                <th class="text-center">Nro Nota</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Fecha Inicio Traslado</th>
                                <th class="text-center">Razón Social</th>
                                <th class="text-center">RUC</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                                    echo "
                                        <tr>
                                            <td class='text-center'>{$data['id_notaR']}</td>
                                            <td class='text-center'>{$data['cod_compra']}</td>
                                            <td class='text-center'>{$data['nro_nota']}</td>
                                            <td class='text-center'>{$data['fecha']}</td>
                                            <td class='text-center'>{$data['fecha_ini_traslado']}</td>
                                            <td class='text-center'>{$data['razon_social']}</td>
                                            <td class='text-center'>{$data['ruc']}</td>
                                            <td class='text-center'>{$data['p_descrip']}</td>
                                            <td class='text-center'>{$data['cantidad']}</td>
                                            <td class='text-center'>{$data['estado']}</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='14' class='text-center'>No se encontraron resultados.</td></tr>";
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
    document.getElementById('btnImprimirNota').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.text("Informe de Notas de Remision", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        doc.autoTable({
            html: '#tablaNota',
            startY: 35,
            theme: 'grid',
            headStyles: { fillColor: [22, 160, 100], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [240, 240, 240] },
            styles: { fontSize: 10, cellPadding: 2 }
        });

        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Página ${i} de ${pageCount}`, doc.internal.pageSize.getWidth() - 20, doc.internal.pageSize.getHeight() - 10);
        }

        doc.save('InformeNotaRemision.pdf');
    });
</script>