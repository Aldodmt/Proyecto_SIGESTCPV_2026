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
                <a>Filtrar pedidos</a>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-12">
                <i class="bi bi-funnel"></i> Filtrar pedidos
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
                        <div class="col-md-4">
                            <label for="fecha_desde" class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" name="fecha_desde" id="fecha_desde">
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta">
                        </div>
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado">
                                <option value="" selected>--Todos los estados--</option>
                                <option value="confirmado">Confirmados</option>
                                <option value="anulado">Anulados</option>
                                <option value="pendiente">Pendiente</option>
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

            // Construir la consulta dinámicamente
            $queryStr = "SELECT * FROM v_pedido WHERE 1=1"; // siempre true
            if ($fecha_desde)
                $queryStr .= " AND fecha >= '$fecha_desde'";
            if ($fecha_hasta)
                $queryStr .= " AND fecha <= '$fecha_hasta'";
            if ($estado)
                $queryStr .= " AND estado = '$estado'";

            $query = mysqli_query($mysqli, $queryStr) or die('Error: ' . mysqli_error($mysqli));
            ?>

            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h4">Resultados</h2>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover" id="tablaPedidos">
                        <thead>
                            <tr>
                                <th class="text-center">ID pedido</th>
                                <th class="text-center">Usuario</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Hora</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($query) > 0) {
                                while ($data = mysqli_fetch_assoc($query)) {
                                    echo "<tr>
                                            <td class='text-center'>{$data['id_pedido']}</td>
                                            <td class='text-center'>{$data['name_user']}</td>
                                            <td class='text-center'>{$data['fecha']}</td>
                                            <td class='text-center'>{$data['hora']}</td>
                                            <td class='text-center'>{$data['p_descrip']}</td>
                                            <td class='text-center'>{$data['cantidad']}</td>
                                            <td class='text-center'>{$data['estado']}</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='7' class='text-center'>No se encontraron resultados.</td>
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

<?php
?>

<script>
    document.getElementById('btnImprimir').addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Cabecera personalizada
        doc.setFontSize(16);
        doc.text("Informe de Pedidos", 14, 20);
        doc.setFontSize(10);
        const fechaActual = new Date().toLocaleDateString();
        doc.text(`Fecha: ${fechaActual}`, 14, 28);
        doc.text("Generado por: <?php echo $user_gua; ?>", 150, 28, { align: "right" });

        // Tabla con estilos
        doc.autoTable({
            html: '#tablaPedidos',
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

        doc.save('InformePedidos.pdf');
    });
</script>