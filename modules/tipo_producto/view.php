<section class="app-content-header">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
        <li class="breadcrumb-item active">Tipo de producto</li>
    </ol>
    <br>
    <hr>
    <h1>
        <i class="bi bi-folder me-1"></i> Datos de tipos de productos
        <a class="btn btn-primary btn-icon float-end" href="?module=form_tipo_producto&form=add" title="Agregar"
            data-bs-toggle="tooltip">
            <i class="bi bi-plus-lg"></i> Agregar
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
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4><i class='bi bi-check-circle'></i> Exitoso!!!</h4>
                    Datos registrados correctamente </div>";
            } elseif ($_GET['alert'] == 2) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4><i class='bi bi-check-circle'></i> Exitoso!!!</h4>
                    Datos modificados correctamente </div>";
            } elseif ($_GET['alert'] == 3) {
                echo "<div class='alert alert-success alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4><i class='bi bi-check-circle'></i> Exitoso!!!</h4>
                    Datos eliminados correctamente </div>";
            } elseif ($_GET['alert'] == 4) {
                echo "<div class='alert alert-danger alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4><i class='bi bi-x-circle'></i> Error!!!</h4>
                    No se pudo realizar la operación </div>";
            }
            ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Lista de tipos de productos</h5>
                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Código</th>
                                <th class="text-center">Tipo de producto</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Obtener datos de la tabla u_medida
                            $query = mysqli_query($mysqli, "SELECT * FROM tipo_producto")
                                or die("Error: " . mysqli_error($mysqli));

                            while ($data = mysqli_fetch_assoc($query)) {
                                $cod_tp = $data['cod_tipo_prod'];
                                $tp_descrip = $data['t_p_descrip'];

                                echo "<tr>
                                    <td class='text-center'>$$cod_tp</td>
                                    <td class='text-center'>$tp_descrip</td>
                                    <td class='text-center'>
                                        <a class='btn btn-primary btn-sm me-2' 
                                        href='?module=form_tipo_producto&form=edit&id=$cod_tp' 
                                        title='Modificar datos de los tipos de productos' data-bs-toggle='tooltip'>
                                        <i class='bi bi-pencil-square'></i>
                                        </a>
                                        <a class='btn btn-danger btn-sm' 
                                        href='modules/tipo_producto/process.php?act=delete&cod_tipo_prod=$cod_tp' 
                                        title='Eliminar datos' data-bs-toggle='tooltip'
                                        onclick=\"return confirm('¿Estás seguro/a de eliminar " . htmlspecialchars(addslashes($tp_descrip)) . "?');\">
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