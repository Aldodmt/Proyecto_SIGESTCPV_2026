<section class="content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ciudad</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="cil-folder icon-title"></i>Datos de Ciudad
        <a class="btn btn-primary btn-social pull-right" href="?module=form_ciudad&form=add" title="Agregar"
            data-toggle="tooltip">
            <i class="cil-plus"></i>Agregar
        </a>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-12">
            <?php
            if (empty($_GET['alert'])) {
                echo "";
            } elseif ($_GET["alert"] == 1) {
                echo "<div class='alert alert-success alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'>&times;</button>
                    <h4> <i class='cil-check-circle'></i> Exitoso!</h4>
                    Datos registrados correctamente
                </div>";
            } elseif ($_GET["alert"] == 2) {
                echo "<div class='alert alert-success alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'>&times;</button>
                    <h4> <i class='cil-check-circle'></i> Exitoso!</h4>
                    Datos modificados correctamente
                </div>";
            } elseif ($_GET["alert"] == 3) {
                echo "<div class='alert alert-success alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'>&times;</button>
                    <h4> <i class='cil-check-circle'></i> Exitoso!</h4>
                    Datos eliminados correctamente
                </div>";
            } elseif ($_GET["alert"] == 4) {
                echo "<div class='alert alert-danger alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'>&times;</button>
                    <h4> <i class='cil-x-circle'></i> Error!</h4>
                    No se pudo realizar la operación
                </div>";
            }
            ?>

            <div class="card">
                <div class="card-body">
                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <h2>Lista de ciudad</h2>
                        <thead>
                            <tr>
                                <th class="text-center">Código</th>
                                <th class="text-center">Descripción</th>
                                <th class="text-center">Departamento</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Obtener datos de la tabla ciudad con su departamento
                            $query = mysqli_query($mysqli, "SELECT ciu.cod_ciudad, ciu.descrip_ciudad, dep.id_departamento, dep.dep_descripcion 
    FROM ciudad ciu
    JOIN departamento dep ON ciu.id_departamento = dep.id_departamento
    ORDER BY ciu.cod_ciudad ASC")
                                or die("Error: " . mysqli_error($mysqli));

                            while ($data = mysqli_fetch_assoc($query)) {
                                $cod_ciudad = $data['cod_ciudad'];
                                $descrip_ciudad = $data['descrip_ciudad'];
                                $dep_descripcion = $data['dep_descripcion'];

                                echo "<tr>
                                        <td class='text-center'>$cod_ciudad</td>
                                        <td class='text-center'>$descrip_ciudad</td>
                                        <td class='text-center'>$dep_descripcion</td>
                                        <td class='text-center'>
                                            <a class='btn btn-primary btn-sm me-2' 
                                            href='?module=form_ciudad&form=edit&id=$cod_ciudad' 
                                            title='Modificar datos de Ciudad' data-bs-toggle='tooltip'>
                                            <i class='cil-pencil'></i>
                                            </a>
                                            <a class='btn btn-danger btn-sm' 
                                            href='modules/ciudad/proses.php?act=delete&cod_ciudad=$cod_ciudad' 
                                            title='Eliminar datos' data-bs-toggle='tooltip'
                                            onclick=\"return confirm('¿Estás seguro/a de eliminar " . htmlspecialchars(addslashes($descrip_ciudad)) . "?');\">
                                            <i class='cil-trash'></i>
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