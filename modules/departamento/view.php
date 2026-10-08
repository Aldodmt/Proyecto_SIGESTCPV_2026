<section class="app-content-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Departamento</a></li>
        </ol>
    </nav>
    <hr>
    <h1>
        <i class="bi bi-folder me-1"></i>Datos de Departamento
        <a class="btn btn-primary float-end" href="?module=form_departamento&form=add" title="Agregar"
            data-bs-toggle="tooltip">
            <i class="bi bi-plus-lg"></i>Agregar
        </a>
    </h1>

</section>

<section class="app-content">
    <div class="row">
        <div class="col-md-12">
            <?php
            if (empty($_GET['alert'])) {
                echo "";
            } elseif ($_GET["alert"] == 1) {
                echo "<div class='alert alert-success alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4> <i class='bi bi-check-circle'></i> Exitoso!</h4>
                    Datos registrados correctamente
                </div>";
            } elseif ($_GET["alert"] == 2) {
                echo "<div class='alert alert-success alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4> <i class='bi bi-check-circle'></i> Exitoso!</h4>
                    Datos modificados correctamente
                </div>";
            } elseif ($_GET["alert"] == 3) {
                echo "<div class='alert alert-success alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4> <i class='bi bi-check-circle'></i> Exitoso!</h4>
                    Datos eliminados correctamente
                </div>";
            } elseif ($_GET["alert"] == 4) {
                echo "<div class='alert alert-danger alert-dismissible fade show'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <h4> <i class='bi bi-x-circle'></i> Error!</h4>
                    No se pudo realizar la operación
                </div>";
            }
            ?>

            <div class="card card-primary">
                <div class="card-body">
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
                                                <i class='bi bi-pencil-square'></i>
                                            </a>
                                            <a class='btn btn-danger btn-sm' 
                                            href='modules/departamento/proses.php?act=delete&id_departamento=$id_departamento' 
                                            title='Eliminar datos' data-bs-toggle='tooltip'
                                            onclick=\"return confirm('¿Estás seguro/a de eliminar " . htmlspecialchars(addslashes($dep_descripcion)) . "?');\">
                                            <i class='bi bi-trash'></i>
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