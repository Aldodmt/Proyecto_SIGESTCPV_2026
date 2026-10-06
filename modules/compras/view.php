<section class="content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compras</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="fa fa-folder icon-title"></i> Datos de Compras
        <a class="btn btn-primary btn-sm float-end" href="?module=form_compra&form=add" title="Agregar"
            data-coreui-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php
            if (!empty($_GET['alert'])) {
                if ($_GET["alert"] == 1) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='fa fa-check-circle'></i> Exitoso!</h4>
                        Datos registrados correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 2) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='fa fa-check-circle'></i> Exitoso!</h4>
                        Datos anulados correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 3) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='fa fa-exclamation-circle'></i> Error!</h4>
                        No se pudo realizar la operación.
                    </div>";
                } elseif ($_GET["alert"] == 4) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='fa fa-exclamation-circle'></i> Error!</h4>
                        El timbrado supero su limite.
                    </div>";
                }
            }
            ?>
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <div class="card">
                <div class="card-body">
                    <h2>Lista de Compras</h2>
                    <div class="mb-3">
                        <label>Buscar Compra:</label>
                        <input type="text" id="buscarCompra" class="form-control"
                            placeholder="Filtrar por código o producto">
                    </div>
                    <div class="table-responsive">
                        <table id="dataTables1" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">ID orden de compra</th>
                                    <th class="text-center">Nro. Factura</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Proveedor</th>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Precio</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nro = 1;
                                $query = mysqli_query($mysqli, "SELECT * FROM v_compras ORDER BY cod_compra ASC")
                                    or die("Error" . mysqli_error($mysqli));
                                while ($data = mysqli_fetch_assoc($query)) {
                                    $cod = $data['cod_compra'];
                                    $cod_orden = $data['id_orden_comp'];
                                    $fac_nro = $data['fac_numero'];
                                    $proveedor = $data['razon_social'];
                                    $fecha = $data['fecha'];
                                    $hora = $data['hora'];
                                    $prod = $data['p_descrip'];
                                    $cantidad = $data['cantidad'];
                                    $precio = $data['precio'];
                                    $estado = $data['estado'];

                                    echo "<tr>
                                    <td class='text-center'>$cod</td>
                                    <td class='text-center'>$cod_orden</td> 
                                    <td class='text-center'>$fecha</td>
                                    <td class='text-center'>$fac_nro</td>
                                    <td class='text-center'>$proveedor</td>
                                    <td class='text-center'>$prod</td>
                                    <td class='text-center'>$cantidad</td>
                                    <td class='text-center'>$precio</td>
                                    <td class='text-center'>$estado</td>
                                    <td class='text-center' width='80'>
                                        <div class='btn-group' role='group'>
                                            <a data-coreui-toggle='tooltip' title='Detalles de compra' class='btn btn-success btn-sm'
                                                href='?module=form_compra&form=detalle&cod_compra=$cod'
                                                onclick='return confirm(\"¿Estás seguro/a de ver los detalles de la compra?\");'>
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
                var value = this.value.toLowerCase();

                table.rows().every(function () {
                    var id = this.data()[0].toString().toLowerCase();      // Columna ID
                    var prod = this.data()[4].toString().toLowerCase();    // Columna Producto

                    if (id.includes(value) || prod.includes(value)) {
                        $(this.node()).show();
                    } else {
                        $(this.node()).hide();
                    }
                });
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</section>