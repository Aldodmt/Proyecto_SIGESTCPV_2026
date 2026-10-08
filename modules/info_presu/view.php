<?php
$user_gua = $_SESSION['username'] ?? 'Desconocido';
?>

<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a>Filtrar presupuestos</a>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                <i class="bi bi-funnel"></i> Filtrar presupuestos
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
                                <option value="rechazado" <?= ($_POST['estado'] ?? '') == 'rechazado' ? 'selected' : '' ?>>
                                    Rechazado</option>
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
                            <input type="text" class="form-control" name="producto" id="producto"
                                placeholder="Ej: Producto X" value="<?= $_POST['producto'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="pedido_asociado" class="form-label">Pedido Asociado</label>
                            <select class="form-select" name="pedido_asociado" id="pedido_asociado">
                                <option value="" selected>--Todos--</option>
                                <option value="con" <?= ($_POST['pedido_asociado'] ?? '') == 'con' ? 'selected' : '' ?>>
                                    Con pedido asociado
                                </option>
                                <option value="sin" <?= ($_POST['pedido_asociado'] ?? '') == 'sin' ? 'selected' : '' ?>>
                                    Sin pedido asociado
                                </option>
                            </select>
                        </div>

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
            // Capturar valores del formulario
            $fecha_desde = $_POST['fecha_desde'] ?? null;
            $fecha_hasta = $_POST['fecha_hasta'] ?? null;
            $estado = $_POST['estado'] ?? null;
            $razon_social = $_POST['razon_social'] ?? null;
            $ruc = $_POST['ruc'] ?? null;
            $producto = $_POST['producto'] ?? null;
            $pedido_asociado = $_POST['pedido_asociado'] ?? null;

            // Construcción dinámica de la consulta
            $queryStr = "SELECT * FROM v_presu WHERE 1=1";
            if ($fecha_desde)
                $queryStr .= " AND fecha_presu >= '$fecha_desde'";
            if ($fecha_hasta)
                $queryStr .= " AND fecha_presu <= '$fecha_hasta'";
            if ($estado)
                $queryStr .= " AND estado = '$estado'";
            if ($razon_social)
                $queryStr .= " AND razon_social LIKE '%" . mysqli_real_escape_string($mysqli, $razon_social) . "%'";
            if ($ruc)
                $queryStr .= " AND ruc LIKE '%" . mysqli_real_escape_string($mysqli, $ruc) . "%'";
            if ($producto)
                $queryStr .= " AND p_descrip LIKE '%" . mysqli_real_escape_string($mysqli, $producto) . "%'";
            if ($pedido_asociado === 'con')
                $queryStr .= " AND id_pedido IS NOT NULL";
            if ($pedido_asociado === 'sin')
                $queryStr .= " AND id_pedido IS NULL";

            $queryStr .= " ORDER BY fecha_presu DESC";
            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="tablaPresupuestos">
                        <thead>
                            <tr>
                                <th class="text-center">ID Presupuesto</th>
                                <th class="text-center">ID Pedido</th>
                                <th class="text-center">Fecha Emisión</th>
                                <th class="text-center">Fecha Vencimiento</th>
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
                                            <td class='text-center'>{$data['id_presupuesto']}</td>
                                            <td class='text-center'>{$data['id_pedido']}</td>
                                            <td class='text-center'>{$data['fecha_presu']}</td>
                                            <td class='text-center'>{$data['fecha_vencimiento']}</td>
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

        // Cabecera personalizada
        doc.setFontSize(16);
        doc.text("Informe de Presupuestos", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        // Tabla con estilos
        doc.autoTable({
            html: '#tablaPresupuestos',
            startY: 35,
            theme: 'grid',
            headStyles: { fillColor: [22, 160, 133], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [240, 240, 240] },
            styles: { fontSize: 10, cellPadding: 2 }
        });

        // Pie de página con numeración
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Página ${i} de ${pageCount}`, doc.internal.pageSize.getWidth() - 20, doc.internal.pageSize.getHeight() - 10);
        }

        doc.save('InformePresupuestos.pdf');
    });
</script>