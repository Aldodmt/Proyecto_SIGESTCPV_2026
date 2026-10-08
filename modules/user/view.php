<section class="app-content-header">
    <h1 class="d-flex align-items-center">
        <i class="bi bi-person me-2"></i> Gestión de usuarios
        <a class="btn btn-primary btn-sm ms-auto" href="?module=form_user&form=add" title="Agregar" data-bs-toggle="tooltip">
            <i class="bi bi-plus-lg"></i> Agregar
        </a>
    </h1>
</section>

<section class="app-content">
    <div class="row">
        <div class="col-md-12">
            <!-- Mensajes de alerta -->
            <?php 
                if (isset($_GET['alert'])) {
                    $alerts = [
                        1 => ["success", "¡Éxito!", "El usuario se ha registrado correctamente"],
                        2 => ["success", "¡Éxito!", "El usuario se ha editado correctamente"],
                        3 => ["success", "¡Éxito!", "El usuario ha sido activado correctamente"],
                        4 => ["danger", "¡Éxito!", "El usuario ha sido bloqueado correctamente"],
                        5 => ["danger", "¡Error!", "Asegúrese de que el archivo ingresado sea una imagen"],
                        6 => ["danger", "¡Error!", "El archivo debe ser menor a 1MB"],
                        7 => ["danger", "¡Error!", "Asegúrese de que el tipo de archivo sea: *.jpg, *.jpeg, *.png"],
                        8 => ["danger", "¡Error!", "La contraseña no cumple los requisitos: mínimo 12 caracteres con mayúscula, minúscula, número y símbolo"]
                    ];
                    $alert = $alerts[$_GET['alert']] ?? null;
                    if ($alert) {
                        echo "
                            <div class='alert alert-{$alert[0]} alert-dismissible fade show' role='alert'>
                                <strong>{$alert[1]}</strong> {$alert[2]}
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                            </div>
                        ";
                    }
                }
            ?>

            <!-- Tabla de usuarios -->
            <div class="card">
                <div class="card-body">
                    <table id="dataTables1" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Nro</th>
                                <th class="text-center">Foto</th>
                                <th class="text-center">Nombre de Usuario</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Permisos de acceso</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $nro = 1;
                                $query = mysqli_query($mysqli, "SELECT * FROM usuarios ORDER BY id_user DESC")
                                    or die('error: ' . mysqli_error($mysqli));
                                while ($data = mysqli_fetch_assoc($query)) {
                                    $foto = $data['foto'] ? "images/user/{$data['foto']}" : "images/user/user-default.png";
                                    $statusBtn = $data['status'] === 'activo' 
                                        ? "<a class='btn btn-warning btn-sm me-2' href='modules/user/process.php?act=off&id={$data['id_user']}' title='Bloquear' data-bs-toggle='tooltip'>
                                            <i class='bi bi-slash-circle'></i>
                                           </a>"
                                        : "<a class='btn btn-success btn-sm me-2' href='modules/user/process.php?act=on&id={$data['id_user']}' title='Activar' data-bs-toggle='tooltip'>
                                            <i class='bi bi-check-lg'></i>
                                           </a>";
                                    echo "
                                        <tr>
                                            <td class='text-center'>{$nro}</td>
                                            <td class='text-center'><img src='{$foto}' alt='Foto' class='img-thumbnail' width='45'></td>
                                            <td>{$data['username']}</td>
                                            <td>{$data['name_user']}</td>
                                            <td>{$data['permisos_acceso']}</td>
                                            <td>{$data['status']}</td>
                                            <td class='text-center'>
                                                <div class='d-flex justify-content-center'>
                                                    {$statusBtn}
                                                    <a class='btn btn-primary btn-sm' href='?module=form_user&form=edit&id={$data['id_user']}' title='Editar' data-bs-toggle='tooltip'>
                                                        <i class='bi bi-pencil-square'></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    ";
                                    $nro++;
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
