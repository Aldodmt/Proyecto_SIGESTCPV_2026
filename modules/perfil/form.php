<?php
if (isset($_POST['id_user'])) {
    $query = mysqli_query($mysqli, "SELECT * FROM usuarios WHERE id_user='$_POST[id_user]'")
        or die('error' . mysqli_error($mysqli));
    $data = mysqli_fetch_assoc($query);
}
?>

<section class="app-content-header">
    <h1>
        <i class="bi bi-pencil-square me-1"></i> Modificar perfil de usuario
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=perfil">Perfil de usuario</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modificar perfil de usuario</li>
        </ol>
    </nav>
</section>

<section class="app-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form role="form" class="row g-3" method="POST" action="modules/perfil/process.php?act=update"
                        enctype="multipart/form-data">
                        <input type="hidden" name="id_user" value="<?php echo $data['id_user']; ?>">

                        <div class="mb-3 col-md-6">
                            <label for="username" class="form-label">Nombre de usuario: </label>
                            <input type="text" class="form-control" id="username" name="username"
                                value="<?php echo $data['username']; ?>" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="name_user" class="form-label">Nombre y Apellido: </label>
                            <input type="text" class="form-control" id="name_user" name="name_user"
                                value="<?php echo $data['name_user']; ?>" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="email" class="form-label">Email: </label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo $data['email']; ?>" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="telefono" class="form-label">Teléfono: </label>
                            <input type="text" class="form-control" id="telefono" name="telefono"
                                value="<?php echo $data['telefono']; ?>" required>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="foto" class="form-label">Foto: </label>
                            <input type="file" class="form-control" id="foto" name="foto">
                            <div class="mt-2">
                                <?php
                                if ($data['foto'] == "") { ?>
                                    <img class="img-thumbnail" src="images/user/user-default.png" alt="Foto de usuario">
                                <?php } else { ?>
                                    <img class="img-thumbnail" src="images/user/<?php echo $data['foto']; ?>"
                                        alt="Foto de usuario">
                                <?php } ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                            <a href="?module=perfil" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>