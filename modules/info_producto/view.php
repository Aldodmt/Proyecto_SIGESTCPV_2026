<?php
$user_gua = $_SESSION['username'] ?? 'Desconocido';

// Traer listas para los selects
$unidadQuery = mysqli_query($mysqli, "SELECT DISTINCT id_u_medida, u_descrip FROM u_medida ORDER BY u_descrip");
$tipoQuery = mysqli_query($mysqli, "SELECT DISTINCT cod_tipo_prod, t_p_descrip FROM tipo_producto ORDER BY t_p_descrip");
?>

<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a>Filtrar productos</a>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                <i class="bi bi-funnel"></i> Filtrar productos
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

                        <!-- Filtros de texto -->
                        <div class="col-md-3">
                            <label for="cod_producto" class="form-label">Código de Producto</label>
                            <input type="text" class="form-control" name="cod_producto" id="cod_producto"
                                placeholder="Ej: 101">
                        </div>

                        <div class="col-md-3">
                            <label for="p_descrip" class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="p_descrip" id="p_descrip"
                                placeholder="Ej: Yogurt Natural">
                        </div>

                        <!-- Select de unidad de medida -->
                        <div class="col-md-3">
                            <label for="id_u_medida" class="form-label">Unidad de Medida</label>
                            <select class="form-select" name="id_u_medida" id="id_u_medida">
                                <option value="">Todos</option>
                                <?php while ($u = mysqli_fetch_assoc($unidadQuery)) { ?>
                                    <option value="<?= $u['id_u_medida'] ?>"><?= htmlspecialchars($u['u_descrip']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Select de tipo de producto -->
                        <div class="col-md-3">
                            <label for="cod_tipo_prod" class="form-label">Tipo de Producto</label>
                            <select class="form-select" name="cod_tipo_prod" id="cod_tipo_prod">
                                <option value="">Todos</option>
                                <?php while ($t = mysqli_fetch_assoc($tipoQuery)) { ?>
                                    <option value="<?= $t['cod_tipo_prod'] ?>"><?= htmlspecialchars($t['t_p_descrip']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="col-md-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-success" id="btnImprimir">
                                <i class="bi bi-printer"></i> Imprimir PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            // Capturar filtros
            $cod_producto = $_POST['cod_producto'] ?? '';
            $p_descrip = $_POST['p_descrip'] ?? '';
            $id_u_medida = $_POST['id_u_medida'] ?? '';
            $cod_tipo_prod = $_POST['cod_tipo_prod'] ?? '';

            // Armar consulta
            $queryStr = "SELECT * FROM v_producto WHERE 1=1";

            if ($cod_producto != '') {
                $queryStr .= " AND cod_producto LIKE '%" . mysqli_real_escape_string($mysqli, $cod_producto) . "%'";
            }

            if ($p_descrip != '') {
                $queryStr .= " AND p_descrip LIKE '%" . mysqli_real_escape_string($mysqli, $p_descrip) . "%'";
            }

            if ($id_u_medida != '') {
                $queryStr .= " AND id_u_medida = " . intval($id_u_medida);
            }

            if ($cod_tipo_prod != '') {
                $queryStr .= " AND cod_tipo_prod = " . intval($cod_tipo_prod);
            }

            $queryStr .= " ORDER BY p_descrip ASC";

            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="tablaProductos">
                        <thead>
                            <tr>
                                <th class="text-center">Código Producto</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Unidad de Medida</th>
                                <th class="text-center">Tipo de Producto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                                    echo "<tr>
                                            <td class='text-center'>{$data['cod_producto']}</td>
                                            <td class='text-center'>{$data['p_descrip']}</td>
                                            <td class='text-center'>{$data['u_descrip']}</td>
                                            <td class='text-center'>{$data['t_p_descrip']}</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No se encontraron resultados.</td></tr>";
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
        doc.text("Informe de Productos", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        doc.autoTable({
            html: '#tablaProductos',
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

        doc.save('InformeProductos.pdf');
    });
</script>