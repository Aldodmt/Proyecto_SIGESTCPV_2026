<?php
$user_gua = $_SESSION['username'] ?? 'Desconocido';
?>

<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a>Filtrar proveedores</a>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                Filtrar proveedores
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

                        <!-- Filtro por código -->
                        <div class="col-md-4">
                            <label for="cod_proveedor" class="form-label">Código de Proveedor</label>
                            <input type="text" class="form-control" name="cod_proveedor" id="cod_proveedor"
                                placeholder="Ej: 1001">
                        </div>

                        <!-- Filtro por razón social -->
                        <div class="col-md-4">
                            <label for="razon_social" class="form-label">Razón Social</label>
                            <input type="text" class="form-control" name="razon_social" id="razon_social"
                                placeholder="Ej: Distribuidora XYZ">
                        </div>

                        <!-- Filtro por RUC -->
                        <div class="col-md-4">
                            <label for="ruc" class="form-label">RUC</label>
                            <input type="text" class="form-control" name="ruc" id="ruc" placeholder="Ej: 80012345-6">
                        </div>

                        <!-- Botones -->
                        <div class="col-md-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary">
                                Filtrar
                            </button>
                            <button type="button" class="btn btn-success" id="btnImprimir">
                                Imprimir PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            // Capturar filtros
            $cod_proveedor = $_POST['cod_proveedor'] ?? '';
            $razon_social = $_POST['razon_social'] ?? '';
            $ruc = $_POST['ruc'] ?? '';

            // Construir consulta base
            $queryStr = "SELECT * FROM proveedor WHERE 1=1";

            // Filtros dinámicos
            if ($cod_proveedor != '') {
                $queryStr .= " AND cod_proveedor LIKE '%" . mysqli_real_escape_string($mysqli, $cod_proveedor) . "%'";
            }

            if ($razon_social != '') {
                $queryStr .= " AND razon_social LIKE '%" . mysqli_real_escape_string($mysqli, $razon_social) . "%'";
            }

            if ($ruc != '') {
                $queryStr .= " AND ruc LIKE '%" . mysqli_real_escape_string($mysqli, $ruc) . "%'";
            }

            $queryStr .= " ORDER BY razon_social ASC";

            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="tablaProveedores">
                        <thead>
                            <tr>
                                <th class="text-center">Código</th>
                                <th class="text-center">Razón Social</th>
                                <th class="text-center">RUC</th>
                                <th class="text-center">Dirección</th>
                                <th class="text-center">Teléfono</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                                    echo "<tr>
                                            <td class='text-center'>{$data['cod_proveedor']}</td>
                                            <td class='text-center'>{$data['razon_social']}</td>
                                            <td class='text-center'>{$data['ruc']}</td>
                                            <td class='text-center'>{$data['direccion']}</td>
                                            <td class='text-center'>{$data['telefono']}</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No se encontraron resultados.</td></tr>";
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
        doc.text("Informe de Proveedores", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        doc.autoTable({
            html: '#tablaProveedores',
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

        doc.save('InformeProveedores.pdf');
    });
</script>