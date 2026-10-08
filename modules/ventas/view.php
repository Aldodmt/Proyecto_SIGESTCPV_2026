<section class="app-content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ventas</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="bi bi-folder me-1"></i> Datos de Ventas
        <a class="btn btn-primary btn-sm float-end" href="?module=form_ventas&form=add" title="Agregar"
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
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Exitoso!</h4>
                        Datos registrados correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 2) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Exitoso!</h4>
                        Datos anulados correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 3) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-exclamation-circle'></i> Error!</h4>
                        No se pudo realizar la operación.
                    </div>";
                } elseif ($_GET["alert"] == 4) {
                    $resto = isset($_GET['diff']) ? $_GET['diff'] : "0";
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-exclamation-circle'></i> Error!</h4>
                        La cantidad a vender supera al stock por $resto unidades.
                    </div>";
                } elseif ($_GET["alert"] == 5) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-exclamation-circle'></i> Error!</h4>
                        El producto no existe en el deposito solicitado.
                    </div>";
                } elseif ($_GET["alert"] == 5) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-exclamation-circle'></i> Error!</h4>
                        El timbrado supero su limite.
                    </div>";
                }
            }
            ?>

            <div class="card">
                <div class="card-body">
                    <h2>Lista de Ventas</h2>
                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="center">ID</th>
                                <th class="center">Usuario</th>
                                <th class="center">Cliente</th>
                                <th class="center">Fecha</th>
                                <th class="center">Hora</th>
                                <th class="center">Producto</th>
                                <th class="center">Deposito</th>
                                <th class="center">Cantidad</th>
                                <th class="center">Precio</th>
                                <th class="center">Total</th>
                                <th class="center">Timbrado</th>
                                <th class="center">Nro. Factura</th>
                                <th class="center">Estado</th>
                                <th class="center">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nro = 1;
                            $query = mysqli_query($mysqli, "SELECT * FROM v_ventas WHERE estado = 'activo' ")
                                or die("Error" . mysqli_error($mysqli));
                            while ($data = mysqli_fetch_assoc($query)) {
                                $cod = $data['cod_venta'];
                                $cli_nom = $data['cli_nombre'];
                                $user = $data['name_user'];
                                $nro_factura = $data['nro_factura'];
                                $formato_factura = "000-000-" . str_pad($nro_factura, 3, "0", STR_PAD_LEFT);
                                $fecha = $data['fecha'];
                                $hora = $data['hora'];
                                $timb = $data['numero_timbrado'];
                                $prod = $data['p_descrip'];
                                $depo = $data['descrip'];
                                $precio = $data['det_precio_unit'];
                                $cantidad = $data['det_cantidad'];
                                $estado = $data['estado'];
                                $total = ($cantidad * $precio);


                                echo "<tr>
                                    <td class='text-center'>$cod</td> 
                                    <td class='text-center'>$user</td>
                                    <td class='text-center'>$cli_nom</td>
                                    <td class='text-center'>$fecha</td>
                                    <td class='text-center'>$hora</td>
                                    <td class='text-center'>$prod</td>
                                    <td class='text-center'>$depo</td>
                                    <td class='text-center'>$cantidad</td>
                                    <td class='text-center'>$precio</td>
                                    <td class='text-center'>$total</td>
                                    <td class='text-center'>$timb</td>
                                    <td class='text-center'>$formato_factura</td>
                                    <td class='text-center'>$estado</td>
                                    <td class='text-center' width='80'>
                                        <div class='btn-group' role='group'>
                                            <a data-bs-toggle='tooltip' title='Anular compra' class='btn btn-danger btn-sm'
                                                href='modules/ventas/proses.php?act=anular&cod_venta=$cod'
                                                onclick='return confirm(\"¿Estás seguro/a de anular la factura $nro_factura?\");'>
                                                <i class='bi bi-trash'></i>
                                            </a>
                                            <a data-bs-toggle='tooltip' title='Imprimir factura de ventas' class='btn btn-warning btn-sm'
                                                href='modules/ventas/print.php?act=imprimir&cod_venta=$cod' target='_blank'>
                                                <i class='bi bi-printer'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>