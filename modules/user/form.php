<?php
if ($_GET['form'] == "add") { ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <h2 class="text-primary">
                    <i class="cil-user-plus"></i> Agregar usuario
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?module=start">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=user">Usuario</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Agregar</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Formulario de Registro</div>
                    <div class="card-body">
                        <form method="POST" action="modules/user/process.php?act=insert" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nombre de usuario</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="name_user" class="form-label">Nombre y apellido</label>
                                <input type="text" class="form-control" id="name_user" name="name_user" required>
                            </div>
                            <div class="mb-3">
                                <label for="permisos_acceso" class="form-label">Permisos de acceso</label>
                                <select class="form-select" id="permisos_acceso" name="permisos_acceso" required>
                                    <option value="" selected>Seleccione...</option>
                                    <option value="Super Admin">Administrador de sistemas</option>
                                    <option value="Compras">Usuario de compras</option>
                                    <option value="Ventas">Usuario de ventas</option>
                                    <option value="Produccion">Usuario de produccion</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="?module=user" class="btn btn-secondary ms-2">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } elseif ($_GET['form'] == 'edit') {
    if (isset($_GET['id'])) {
        $query = mysqli_query($mysqli, "SELECT * FROM usuarios WHERE id_user='$_GET[id]'") or die('Error: ' . mysqli_error($mysqli));
        $data = mysqli_fetch_assoc($query);
    } ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <h2 class="text-primary">
                    <i class="cil-pencil"></i> Modificar usuario
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?module=start">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=user">Usuario</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Modificar</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Formulario de Edición</div>
                    <div class="card-body">
                        <form method="POST" action="modules/user/process.php?act=update" enctype="multipart/form-data">
                            <input type="hidden" name="id_user" value="<?php echo $data['id_user']; ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nombre de usuario</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?php echo $data['username']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="name_user" class="form-label">Nombre y apellido</label>
                                <input type="text" class="form-control" id="name_user" name="name_user"
                                    value="<?php echo $data['name_user']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo $data['email']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="12"
                                    value="<?php echo $data['telefono']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto</label>
                                <input type="file" class="form-control" id="foto" name="foto">
                                <div class="mt-2">
                                    <img src="images/user/<?php echo $data['foto'] ? $data['foto'] : 'user-default.png'; ?>"
                                        alt="Foto de usuario" class="img-thumbnail" style="width: 150px;">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="permisos_acceso" class="form-label">Permisos de acceso</label>
                                <select class="form-select" id="permisos_acceso" name="permisos_acceso" required>
                                    <option value="<?php echo $data['permisos_acceso']; ?>" selected>
                                        <?php echo $data['permisos_acceso']; ?>
                                    </option>
                                    <option value="Super Admin">Administrador de sistemas</option>
                                    <option value="Compras">Usuario de compras</option>
                                    <option value="Ventas">Usuario de ventas</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="?module=user" class="btn btn-secondary ms-2">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }
?>