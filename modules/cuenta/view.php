<section class="content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cuentas a pagar</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="fa fa-folder icon-title"></i> Datos de cuentas a pagar
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
                        Presupuesto registrado correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 2) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='fa fa-check-circle'></i> Exitoso!</h4>
                        Cuenta deshabilitada correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 3) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='fa fa-exclamation-circle'></i> Error!</h4>
                        No se pudo realizar la operación.
                    </div>";
                } elseif ($_GET["alert"] == 4) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='fa fa-check-circle'></i> Exitoso!</h4>
                        Cuenta activada correctamente.
                    </div>";
                }
            }
            ?>
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <div class="card">
                <div class="card-body">
                    <h2>Lista de Cuentas a Pagar</h2>
                    <div class="mb-3">
                        <label>Buscar Cuentas:</label>
                        <input type="text" id="buscarCompra" class="form-control"
                            placeholder="Filtrar por código o producto">
                    </div>
                    <div class="table-responsive">
                        <table id="dataTables1" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">ID compra</th>
                                    <th class="text-center">Proveedor</th>
                                    <th class="text-center">nro_cuota</th>
                                    <th class="text-center">monto</th>
                                    <th class="text-center">saldo</th>
                                    <th class="text-center">Fecha de venicimiento</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nro = 1;
                                $query = mysqli_query($mysqli, "SELECT * FROM v_cuentas  ORDER BY cap_cod ASC")
                                    or die("Error" . mysqli_error($mysqli));
                                while ($data = mysqli_fetch_assoc($query)) {
                                    $cod = $data['cap_cod'];
                                    $cod_c = $data['cod_compra'];
                                    $razon = $data['razon_social'];
                                    $nro_cuota = $data['nro_cuota'];
                                    $monto = $data['cap_monto'];
                                    $saldo = $data['cap_saldo'];
                                    $fecha_venci = $data['cap_fecha_venci'];
                                    $estado = $data['cap_estado'];

                                    //por si  acaso
                                    /*$precio_compra_ = $row['precio_tmp'];
            $precio_compra_f = number_format($precio_compra_); //Formatear una variable (Poner ,)
            $precio_compra_r = str_replace(",", "", $precio_compra_f); //Reemplazar la coma 
            $precio_total = $precio_compra_r * $cantidad;
            $precio_total_f = number_format($precio_total);
            $precio_total_r = str_replace(",", "", $precio_total_f);*/

                                    echo "<tr>
                                    <td class='text-center'>$cod</td>
                                    <td class='text-center'>$cod_c</td>
                                    <td class='text-center'>$razon</td>
                                    <td class='text-center'>$nro_cuota</td>
                                    <td class='text-center'>$monto</td>
                                    <td class='text-center'>$saldo</td>
                                    <td class='text-center'>$fecha_venci</td>
                                    <td class='text-center'>$estado</td>
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
                    var value = this.value.toLowerCase();

                    table.rows().every(function () {
                        var id = this.data()[0].toString().toLowerCase();      // Columna ID
                        var prov = this.data()[2].toString().toLowerCase();    // Columna Proveedor
                        var estado = this.data()[7].toString().toLowerCase();
                        var id_com = this.data()[1].toString().toLowerCase();

                        if (id.includes(value) || prov.includes(value) || estado.includes(value) || id_com.includes(value)) {
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