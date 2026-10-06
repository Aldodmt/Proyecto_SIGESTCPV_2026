<section class="content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Presupuestos</li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="fa fa-folder icon-title"></i> Datos de Presupuestos
        <a class="btn btn-primary btn-sm float-end" href="?module=form_presupuesto&form=add" title="Agregar"
            data-coreui-toggle="tooltip">
            <i class="cil-plus"></i> Agregar
        </a>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php
            if (!empty($_GET['alert'])) {
                $alerts = [
                    1 => ['success', 'Presupuesto registrado correctamente.'],
                    2 => ['danger', 'Presupuesto rechazado correctamente.'],
                    3 => ['danger', 'No se pudo realizar la operación.'],
                    4 => ['success', 'Presupuesto aprobado.'],
                    5 => ['warning', 'No puedes aprobar un presupuesto rechazado.'],
                    6 => ['warning', 'El presupuesto ya está rechazado.']
                ];
                if (isset($alerts[$_GET['alert']])) {
                    [$type, $msg] = $alerts[$_GET['alert']];
                    echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                            <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                            $msg
                          </div>";
                }
            }
            ?>

            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

            <div class="card">
                <div class="card-body">
                    <h2>Lista de Presupuestos</h2>
                    <div class="mb-3">
                        <label>Buscar Presupuesto:</label>
                        <input type="text" id="buscarPresupuesto" class="form-control"
                            placeholder="Filtrar por código, proveedor o producto">
                    </div>

                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">ID presupuesto</th>
                                <th class="text-center">ID pedido</th>
                                <th class="text-center">Proveedor</th>
                                <th class="text-center">Fecha Emitida</th>
                                <th class="text-center">Fecha de Vencimiento</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Precio Unitario</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($mysqli, "SELECT * FROM v_presu ORDER BY id_presupuesto ASC")
                                or die("Error " . mysqli_error($mysqli));

                            while ($data = mysqli_fetch_assoc($query)) {
                                $cod = $data['id_presupuesto'];
                                $cod_p = $data['id_pedido'];
                                $razon = $data['razon_social'];
                                $fecha = $data['fecha_presu'];
                                $fecha_v = $data['fecha_vencimiento'];
                                $prod = $data['p_descrip'];
                                $cantidad = $data['cantidad'];
                                $precio = $data['precio_unit'];
                                $total = $cantidad * $precio;
                                $estado = $data['estado'];

                                echo "<tr>
                                    <td class='text-center'>$cod</td>
                                    <td class='text-center'>$cod_p</td>
                                    <td class='text-center'>$razon</td>
                                    <td class='text-center'>$fecha</td>
                                    <td class='text-center'>$fecha_v</td>
                                    <td class='text-center'>$prod</td>
                                    <td class='text-center'>$cantidad</td>
                                    <td class='text-center'>$precio</td>
                                    <td class='text-center'>$total</td>
                                    <td class='text-center'>$estado</td>
                                    <td class='text-center' width='80'>
                                        <div class='btn-group' role='group'>
                                            <a data-coreui-toggle='tooltip' title='Detalle de Presupuesto' class='btn btn-success btn-sm'
                                                href='?module=form_presupuesto&form=detalle&id_presupuesto=$cod
                                                onclick='return confirm(\"¿Estás seguro/a de ver los detalles del presupuesto $cod?\");'>
                                                <i class='cil-description'></i>
                                            </a>
                                        </div>
                                    </td>
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
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
                pageLength: 10,
                ordering: true
            });

            $('#buscarPresupuesto').on('keyup', function () {
                table.search(this.value).draw();
            });
        });
    </script>
</section>