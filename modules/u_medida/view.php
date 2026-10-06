<section class="content-header">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i>Inicio</a></li>
        <li class="breadcrumb-item active">Unidades de medidas</li>
    </ol>
    <hr>
    <h1 class="h3">
        <i class="cil-folder icon-title"></i> Unidades de medida
        <a class="btn btn-primary btn-sm float-end" href="?module=form_u_medida&form=add" title="Agregar"
            data-toggle="tooltip">
            <i class="cil-plus"></i> Agregar
        </a>
    </h1>
</section>

<section class="content">
    <div class="container-fluid">
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
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Lista de unidades de medida</h2>
                        <table class="table table-bordered table-striped table-hover" id="dataTables1">
                            <thead>
                                <tr>
                                    <th class="text-center">Código</th>
                                    <th class="text-center">Unidad de medida</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Obtener datos de la tabla u_medida
                                $query = mysqli_query($mysqli, "SELECT * FROM u_medida")
                                    or die("Error: " . mysqli_error($mysqli));

                                while ($data = mysqli_fetch_assoc($query)) {
                                    $id_u_medida = $data['id_u_medida'];
                                    $u_descrip = $data['u_descrip'];

                                    echo "<tr>
                                    <td class='text-center'>$id_u_medida</td>
                                    <td class='text-center'>$u_descrip</td>
                                    <td class='text-center'>
                                        <a class='btn btn-primary btn-sm me-2' 
                                        href='?module=form_u_medida&form=edit&id=$id_u_medida' 
                                        title='Modificar datos de Unidad de medida' data-bs-toggle='tooltip'>
                                        <i class='cil-pencil'></i>
                                        </a>
                                        <a class='btn btn-danger btn-sm' 
                                        href='modules/u_medida/process.php?act=delete&id_u_medida=$id_u_medida' 
                                        title='Eliminar datos' data-bs-toggle='tooltip'
                                        onclick=\"return confirm('¿Estás seguro/a de eliminar " . htmlspecialchars(addslashes($u_descrip)) . "?');\">
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
    </div>
</section>