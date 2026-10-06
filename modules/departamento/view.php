<section class="content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Departamento</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class=" fa fa-folder icon-title"></i>Datos de Departamento
        <a class="btn btn-primary btn-social pull-right" href="?module=form_departamento&form=add" title="Agregar"
            data_toggle="tooltip">
            <i class="fa fa-plus"></i>Agregar
        </a>
    </h1>

</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
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

            <div class="box box-primary">
                <div class="box-body">
                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <h2>Lista de departamentos</h2>
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Descripcion</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Obtener datos de la tabla
                            $query = mysqli_query($mysqli, "SELECT * FROM departamento")
                                or die("Error: " . mysqli_error($mysqli));
                            while ($data = mysqli_fetch_assoc($query)) {
                                $id_departamento = $data['id_departamento'];
                                $dep_descripcion = $data['dep_descripcion'];

                                echo "<tr>
                                        <td>$id_departamento</td>
                                        <td>$dep_descripcion</td>
                                        <td class='text-center'>
                                            <a class='btn btn-primary btn-sm me-2' 
                                                href='?module=form_departamento&form=edit&id=$id_departamento' 
                                                title='Modificar datos de Departamento' data-bs-toggle='tooltip'>
                                                <i class='cil-pencil'></i>
                                            </a>
                                            <a class='btn btn-danger btn-sm' 
                                            href='modules/departamento/proses.php?act=delete&id_departamento=$id_departamento' 
                                            title='Eliminar datos' data-bs-toggle='tooltip'
                                            onclick=\"return confirm('¿Estás seguro/a de eliminar " . htmlspecialchars(addslashes($dep_descripcion)) . "?');\">
                                            <i class='cil-trash'></i>
                                            </a>
                                        </td>
                                      </tr>";
                            }
                            ?>
                            <?php
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</section>