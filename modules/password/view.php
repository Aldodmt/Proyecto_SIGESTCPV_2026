<section class="content-header">
    <h1>
        <i class="fa fa-lock icon-title"></i> Modificar contraseña
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i>Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contraseña</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modificar</li>
        </ol>
    </nav>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Agregar los mensajes de errores -->
            <?php
            if (!empty($_GET['alert'])) {
                if ($_GET['alert'] == 1) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <strong><i class='fa-solid fa-circle-xmark'></i> Error:</strong> En contraseña.
                        </div>";
                } elseif ($_GET['alert'] == 2) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <strong><i class='fa-solid fa-circle-xmark'></i> Error:</strong> La nueva contraseña ingresada no coincide.
                        </div>";
                } elseif ($_GET['alert'] == 3) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <strong><i class='fa-solid fa-circle-check'></i> Exitoso:</strong> La nueva contraseña cambiada exitosamente.
                        </div>";
                } elseif ($_GET['alert'] == 4) {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <button type='button' class='btn-close' data-coreui-dismiss='alert' aria-label='Close'></button>
                        <strong>Error:</strong> La nueva contraseña debe tener mínimo 12 caracteres, con mayúscula, minúscula, número y símbolo.
                        </div>";
                }
            }
            ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cambiar Contraseña</h3>
                </div>
                <form role="form" method="POST" action="modules/password/proses.php">
                    <div class="card-body">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="old_pass">Contraseña antigua</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" id="old_pass" name="old_pass"
                                    autocomplete="off" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="new_pass">Contraseña nueva</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" id="new_pass" name="new_pass"
                                    autocomplete="off" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label" for="retype_pass">Repetir nueva contraseña</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" id="retype_pass" name="retype_pass"
                                    autocomplete="off" required>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-primary" name="Guardar">Guardar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>