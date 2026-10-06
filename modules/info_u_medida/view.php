<?php
$user_gua = $_SESSION['username'] ?? 'Desconocido';
?>

<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start"><i class="cil-home"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a>Filtrar Unidades de Medida</a>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                <i class="cil-filter"></i> Filtrar Unidades de Medida
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
                    <form method="POST" class="row g-3" id="formFiltros">

                        <!-- Filtros -->
                        <div class="col-md-6">
                            <label for="id_u_medida" class="form-label">Código de Unidad</label>
                            <input type="text" class="form-control" name="id_u_medida" id="id_u_medida"
                                placeholder="Ej: 1">
                        </div>

                        <div class="col-md-6">
                            <label for="u_descrip" class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="u_descrip" id="u_descrip"
                                placeholder="Ej: 1 Litro">
                        </div>

                        <!-- Botones -->
                        <div class="col-md-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="cil-magnifying-glass"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-success" id="btnImprimir">
                                <i class="cil-print"></i> Imprimir PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            // Capturar filtros
            $id_u_medida = $_POST['id_u_medida'] ?? '';
            $u_descrip = $_POST['u_descrip'] ?? '';

            // Construir consulta
            $queryStr = "SELECT * FROM u_medida WHERE 1=1";

            if ($id_u_medida != '') {
                $queryStr .= " AND id_u_medida LIKE '%" . mysqli_real_escape_string($mysqli, $id_u_medida) . "%'";
            }

            if ($u_descrip != '') {
                $queryStr .= " AND u_descrip LIKE '%" . mysqli_real_escape_string($mysqli, $u_descrip) . "%'";
            }

            $queryStr .= " ORDER BY id_u_medida ASC";

            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="tablaUMedida">
                        <thead>
                            <tr>
                                <th class="text-center">Código</th>
                                <th class="text-center">Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                                    echo "<tr>
                                            <td class='text-center'>{$data['id_u_medida']}</td>
                                            <td class='text-center'>{$data['u_descrip']}</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' class='text-center'>No se encontraron resultados.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    document.getElementById('btnImprimir').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.text("Informe de Unidades de Medida", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        doc.autoTable({
            html: '#tablaUMedida',
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

        doc.save('Informe_Unidades_Medida.pdf');
    });
</script>