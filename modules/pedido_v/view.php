<section class="app-content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pedidos ventas</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="bi bi-folder me-1"></i> Datos de Pedidos de Ventas
        <a class="btn btn-primary btn-sm float-end" href="?module=form_pedido_v&form=add" title="Agregar"
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
                        Pedido registrado correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 2) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Exitoso!</h4>
                        Pedido rechazado correctamente.
                    </div>";
                } elseif ($_GET["alert"] == 3) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-exclamation-circle'></i> Error!</h4>
                        No se pudo realizar la operación.
                    </div>";
                } elseif ($_GET["alert"] == 4) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Exitoso!</h4>
                        Pedido aprobado.
                    </div>";
                } elseif ($_GET["alert"] == 5) {
                    echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        <h4><i class='bi bi-check-circle'></i> Error!</h4>
                        No puedes aprobar un pedido rechazado.
                    </div>";
                }
            }
            ?>

            <div class="card">
                <div class="card-body">
                    <h2>Lista de Compras</h2>
                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Usuario</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Hora</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nro = 1;
                            $query = mysqli_query($mysqli, "SELECT * FROM v_pedido_v where estado = 'rechazado' or estado = 'pendiente'  ORDER BY id_pedido_v ASC")
                                or die("Error" . mysqli_error($mysqli));
                            while ($data = mysqli_fetch_assoc($query)) {
                                $cod = $data['id_pedido_v'];
                                $usuario = $data['name_user'];
                                $fecha = $data['fecha_pedido'];
                                $hora = $data['hora'];
                                $cantidad = $data['cantidad'];
                                $prod = $data['p_descrip'];
                                $estado = $data['estado'];
                                echo "<tr>
                                    <td class='text-center'>$cod</td> 
                                    <td class='text-center'>$usuario</td>
                                    <td class='text-center'>$fecha</td>
                                    <td class='text-center'>$hora</td>
                                    <td class='text-center'>$prod</td>
                                    <td class='text-center'>$cantidad</td>
                                    <td class='text-center'>$estado</td>
                                    <td class='text-center' width='80'>
                                        <div class='btn-group' role='group'>
                                            <a data-bs-toggle='tooltip' title='Aprobar pedido' class='btn btn-success btn-sm'
                                                href='modules/pedido_v/proses.php?act=aprobar&id_pedido=$cod'
                                                onclick='return confirm(\"¿Estás seguro/a de aprovar el pedido $cod?\");'>
                                                <i class='bi bi-check-lg'></i>
                                            </a>
                                            <a data-bs-toggle='tooltip' title='Rechazar pedido' class='btn btn-danger btn-sm'
                                                href='modules/pedido_v/proses.php?act=anular&id_pedido=$cod'
                                                onclick='return confirm(\"¿Estás seguro/a de rechzar el pedido $cod?\");'>
                                                <i class='bi bi-x-lg'></i>
                                            </a>
                                            <a data-bs-toggle='tooltip' title='Imprimir factura del pedido' class='btn btn-warning btn-sm'
                                                href='modules/pedido_v/print.php?act=imprimir&id_pedido=$cod' target='_blank'>
                                                <i class='bi bi-printer'></i>
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
</section>