<section class="app-content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Notas</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="bi bi-folder me-1"></i> Datos de Notas
        <a class="btn btn-primary btn-sm float-end" href="?module=form_nota_c_d&form=add" title="Agregar"
            data-bs-toggle="tooltip">
            <i class="bi bi-plus-lg"></i> Agregar
        </a>
    </h1>
</section>

<section class="app-content">
    <div class="row">
        <div class="col-md-12">
            <?php
            if (!empty($_GET['alert'])) {
                if ($_GET["alert"] == 1) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Exitoso!</h4>
                        Nota anulada correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 2) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-exclamation-circle'></i> Error!</h4>
                        No se pudo realizar la operación.
                    </div>";
                } elseif ($_GET["alert"] == 3) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Exitoso!</h4>
                        Nota aprobada correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 4) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Exitoso!</h4>
                        Nota registrada correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 5) {
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Error!</h4>
                        No puedes aprobar una nota anulada.
                    </div>";
                } elseif ($_GET["alert"] == 6) {
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Error!</h4>
                        La nota ya esta anulada.
                    </div>";
                }
            }
            ?>

                                                <div class="card">
                <div class="card-body">
                    <h2>Lista de Notas</h2>
                    <div class="mb-3">
                        <label>Buscar Nota:</label>
                        <input type="text" id="buscarCompra" class="form-control"
                            placeholder="Filtrar por código o producto">
                    </div>
                    <div class="table-responsive">
                        <table id="dataTables1" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Nro. Factura</th>
                                    <th class="text-center">Proveedor</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Monto</th>
                                    <th class="text-center">Razon</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nro = 1;
                                $query = mysqli_query($mysqli, "SELECT * FROM v_nota ORDER BY id_nota ASC")
                                    or die("Error" . mysqli_error($mysqli));
                                while ($data = mysqli_fetch_assoc($query)) {
                                    $cod = $data['id_nota'];
                                    $fac_cod = $data['fac_numero'];
                                    $prov = $data['razon_social'];
                                    $prod = $data['p_descrip'];
                                    $fecha = $data['fecha_emision'];
                                    $tipo = $data['tipo'];
                                    $monto = $data['monto_nota'];
                                    $razon = $data['observacion'];
                                    $estado = $data['estado'];
                                    echo "<tr>
                                    <td class='text-center'>$cod</td>
                                    <td class='text-center'>$fac_cod</td> 
                                    <td class='text-center'>$prov</td>
                                    <td class='text-center'>$fecha</td>
                                    <td class='text-center'>$tipo</td>
                                    <td class='text-center'>$monto</td>
                                    <td class='text-center'>$razon</td>
                                    <td class='text-center'>$estado</td>
                                    <td class='text-center' width='80'>
                                        <div class='btn-group' role='group'>
                                            <a data-bs-toggle='tooltip' title='Activar nota' class='btn btn-success btn-sm'
                                                href='?module=form_nota_c_d&form=detalle&id_nota=$cod'
                                                onclick='return confirm(\"¿Estás seguro/a de ver los detalles de la nota $cod?\");'>
                                                <i class='bi bi-file-text'></i>
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
                        </section>