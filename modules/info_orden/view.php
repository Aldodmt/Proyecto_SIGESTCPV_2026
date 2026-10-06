<?php
$user_gua = $_SESSION['username'] ?? 'Desconocido';
?>

<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start"><i class="cil-home"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item active">Filtrar Orden de Compra</li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                <i class="cil-filter"></i> Filtrar Orden de Compra
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
                                <option value="aprobado" <?= ($_POST['estado'] ?? '') == 'aprobado' ? 'selected' : '' ?>>
                                    Aprobado</option>
                                <option value="anulado" <?= ($_POST['estado'] ?? '') == 'anulado' ? 'selected' : '' ?>>
                                    Anulado</option>
                                <option value="pendiente" <?= ($_POST['estado'] ?? '') == 'pendiente' ? 'selected' : '' ?>>
                                    Pendiente</option>
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
                                $sqlProductos = mysqli_query($mysqli, "SELECT DISTINCT p_descrip FROM v_presu ORDER BY p_descrip ASC");
                                while ($row = mysqli_fetch_assoc($sqlProductos)) {
                                    $selected = ($_POST['producto'] ?? '') == $row['p_descrip'] ? 'selected' : '';
                                    echo "<option value='{$row['p_descrip']}' $selected>{$row['p_descrip']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="presupuesto_asociado" class="form-label">Presupuesto Asociado</label>
                            <select class="form-select" name="presupuesto_asociado" id="presupuesto_asociado">
                                <option value="" selected>--Todos--</option>
                                <option value="con" <?= ($_POST['presupuesto_asociado'] ?? '') == 'con' ? 'selected' : '' ?>>
                                    Con presupuesto asociado
                                </option>
                                <option value="sin" <?= ($_POST['presupuesto_asociado'] ?? '') == 'sin' ? 'selected' : '' ?>>
                                    Sin presupuesto asociado
                                </option>
                            </select>
                        </div>

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
            // Capturar valores
            $fecha_desde = $_POST['fecha_desde'] ?? null;
            $fecha_hasta = $_POST['fecha_hasta'] ?? null;
            $estado = $_POST['estado'] ?? null;
            $razon_social = $_POST['razon_social'] ?? null;
            $ruc = $_POST['ruc'] ?? null;
            $producto = $_POST['producto'] ?? null;
            $presupuesto_asociado = $_POST['presupuesto_asociado'] ?? null;

            // Construcción dinámica
            $queryStr = "SELECT * FROM v_orden_comp WHERE 1=1";
            if ($fecha_desde)
                $queryStr .= " AND fecha >= '$fecha_desde'";
            if ($fecha_hasta)
                $queryStr .= " AND fecha <= '$fecha_hasta'";
            if ($estado)
                $queryStr .= " AND estado = '$estado'";
            if ($razon_social)
                $queryStr .= " AND razon_social LIKE '%" . mysqli_real_escape_string($mysqli, $razon_social) . "%'";
            if ($ruc)
                $queryStr .= " AND ruc LIKE '%" . mysqli_real_escape_string($mysqli, $ruc) . "%'";
            if ($producto)
                $queryStr .= " AND p_descrip = '" . mysqli_real_escape_string($mysqli, $producto) . "'";
            if ($presupuesto_asociado === 'con')
                $queryStr .= " AND id_presupuesto IS NOT NULL";
            if ($presupuesto_asociado === 'sin')
                $queryStr .= " AND id_presupuesto IS NULL";


            $queryStr .= " ORDER BY fecha DESC";
            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="tablaOrdenCompra">
                        <thead>
                            <tr>
                                <th class="text-center">ID Orden Compra</th>
                                <th class="text-center">ID Presupuesto</th>
                                <th class="text-center">Fecha Emisión</th>
                                <th class="text-center">Hora</th>
                                <th class="text-center">Razón Social</th>
                                <th class="text-center">RUC</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Precio Unitario</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                                    $total = $data['cantidad'] * $data['precio_unit'];
                                    echo "
                                        <tr>
                                            <td class='text-center'>{$data['id_orden_comp']}</td>
                                            <td class='text-center'>{$data['id_presupuesto']}</td>
                                            <td class='text-center'>{$data['fecha']}</td>
                                            <td class='text-center'>{$data['hora']}</td>
                                            <td class='text-center'>{$data['razon_social']}</td>
                                            <td class='text-center'>{$data['ruc']}</td>
                                            <td class='text-center'>{$data['p_descrip']}</td>
                                            <td class='text-center'>{$data['cantidad']}</td>
                                            <td class='text-center'>{$data['precio_unit']}</td>
                                            <td class='text-center'>{$total}</td>
                                            <td class='text-center'>{$data['estado']}</td>
                                        </tr>
                                    ";
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='11' class='text-center'>No se encontraron resultados.</td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- jsPDF + AutoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
    document.getElementById('btnImprimir').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.text("Informe de Ordenes de Compra", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        doc.autoTable({
            html: '#tablaOrdenCompra',
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

        doc.save('InformeOrdenCompra.pdf');
    });
</script>