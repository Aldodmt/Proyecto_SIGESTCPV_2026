<section class="container-fluid">
    <div class="row mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="?module=start"><i class="cil-home"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a>Stock</a>
            </li>
        </ol>
    </div>

    <div class="row">
        <div class="col">
            <h1 class="display-6">
                <i class="cil-folder"></i> Stock de productos
            </h1>
            <hr>
        </div>
    </div>
</section>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<div class="card">
    <div class="card-body">
        <h2>Lista del stock de productos</h2>
        <div class="table-responsive">
            <table id="dataTables1" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th class="text-center">Codigo</th>
                        <th class="text-center">Producto</th>
                        <th class="text-center">Tip. Producto</th>
                        <th class="text-center">Unid. Medida</th>
                        <th class="text-center">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($mysqli, "SELECT * FROM v_stock ORDER BY cod_producto ASC")
                        or die('error' . mysqli_error($mysqli));

                    while ($data = mysqli_fetch_assoc($query)) {
                        echo "<tr>
                                        <td class='text-center'>{$data['cod_producto']}</td>
                                        <td class='text-center'>{$data['p_descrip']}</td>
                                        <td class='text-center'>{$data['t_p_descrip']}</td>
                                        <td class='text-center'>{$data['u_descrip']}</td>
                                        <td class='text-center'>{$data['cantidad']}</td>
                                    </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<script>
    $(document).ready(function () {
        var table = $('#dataTables1').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            pageLength: 10,
            ordering: true
        });

        // Filtro personalizado
        $('#buscarCompra').on('keyup', function () {
            var value = this.value;

            // Filtrar por varias columnas: 0=codigo, 1=producto, 2=tipo, 3=unidad
            table.columns([0, 1, 2, 3]).search(value).draw();
        });
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</section>