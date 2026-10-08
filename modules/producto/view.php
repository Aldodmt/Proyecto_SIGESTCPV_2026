<section class="app-content-header">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
        <li class="breadcrumb-item active">Productos</li>
    </ol>
    <br>
    <hr>
    <h1>
        <i class="bi bi-folder2-open me-1"></i>Datos de productos
        <a class="btn btn-primary btn-sm float-end" href="?module=form_producto&form=add" title="Agregar"
            data-bs-toggle="tooltip">
            <i class="bi bi-plus-lg"></i>Agregar
        </a>
    </h1>
</section>

<section class="app-content">
    <div class="row">
        <div class="col-12">
            <?php
            if (empty($_GET['alert'])) {
                echo "";
            } elseif ($_GET['alert'] == 1) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-hidden='true'></button>
                    <h4><i class='bi bi-check-circle'></i>Exitoso!!!</h4>
                    Datos registrados correctamente </div>";
            } elseif ($_GET['alert'] == 2) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-hidden='true'></button>
                    <h4><i class='bi bi-check-circle'></i>Exitoso!!!</h4>
                    Datos modificados correctamente </div>";
            } elseif ($_GET['alert'] == 3) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-hidden='true'></button>
                    <h4><i class='bi bi-check-circle'></i>Exitoso!!!</h4>
                    Datos eliminados correctamente </div>";
            } elseif ($_GET['alert'] == 4) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4><i class='bi bi-x-circle'></i>Error!!!</h4>
                    No se pudo realizar la operacion </div>";
            }
            ?>
            <div class="card">
                <div class="card-body">
                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <h2>Lista de productos</h2>
                        <thead>
                            <tr>
                                <th class="text-center">Código</th>
                                <th class="text-center">Producto</th>
                                <th class="text-center">Tip. de prod.</th>
                                <th class="text-center">Unid. de medida</th>
                                <th class="text-center">Tipo Impuesto</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Obtener datos de la tabla u_medida
                            $query = mysqli_query($mysqli, "SELECT * FROM v_producto")
                                or die("Error: " . mysqli_error($mysqli));

                            while ($data = mysqli_fetch_assoc($query)) {
                                $cod_producto = $data['cod_producto'];
                                $p_descrip = $data['p_descrip'];
                                $tipo_producto = $data['t_p_descrip'];
                                $u_medida = $data['u_descrip'];
                                $tipo_impuesto = $data['tipo_impuesto'];

                                echo "<tr>
                                    <td class='text-center'>$cod_producto</td>
                                    <td class='text-center'>$p_descrip</td>
                                    <td class='text-center'>$tipo_producto</td>
                                    <td class='text-center'>$u_medida</td>
                                    <td class='text-center'>$tipo_impuesto</td>
                                    <td class='text-center'>
                                        <a class='btn btn-primary btn-sm me-2' 
                                        href='?module=form_producto&form=edit&id=$cod_producto' 
                                        title='Modificar datos de los productos' data-bs-toggle='tooltip'>
                                        <i class='bi bi-pencil-square'></i>
                                        </a>
                                        <a class='btn btn-danger btn-sm' 
                                        href='modules/producto/process.php?act=delete&cod_producto=$cod_producto' 
                                        title='Eliminar datos' data-bs-toggle='tooltip'
                                        onclick=\"return confirm('¿Estás seguro/a de eliminar " . htmlspecialchars(addslashes($p_descrip)) . "?');\">
                                        <i class='bi bi-trash'></i>
                                        </a>
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