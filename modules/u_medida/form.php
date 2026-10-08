<?php
if ($_GET['form'] == 'add') { ?>
    <section class="app-content-header">
        <h1>
            <i class="bi bi-pencil-square me-1"></i> Agregar unidad de medida
        </h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=u_medida">Unidades de medida</a></li>
            <li class="breadcrumb-item active">Agregar</li>
        </ol>
    </section>

    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <form role="form" action="modules/u_medida/process.php?act=insert"
                        method="POST">
                        <div class="card-body">
                            <?php
                            // Método para generar código
                            $query_id = mysqli_query($mysqli, "SELECT MAX(id_u_medida) as id FROM u_medida") or die('error' . mysqli_error($mysqli));
                            $count = mysqli_num_rows($query_id);
                            if ($count <> 0) {
                                $data_id = mysqli_fetch_assoc($query_id);
                                $codigo = $data_id['id'] + 1;
                            } else {
                                $codigo = 1;
                            }
                            ?>
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>"
                                    readonly>
                            </div>

                            <div class="mb-3">
                                <label for="u_descrip" class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="u_descrip"
                                    placeholder="Ingrese una unidad de medida" required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" name="Guardar" class="btn btn-primary">Guardar</button>
                                <a href="?module=u_medida" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php } elseif ($_GET['form'] == 'edit') {
    if (isset($_GET['id'])) {
        $query = mysqli_query($mysqli, "SELECT *FROM u_medida WHERE id_u_medida = '$_GET[id]'") or die('error' . mysqli_error($mysqli));
        $data = mysqli_fetch_assoc($query);
    } ?>
    <section class="app-content-header">
        <h1>
            <i class="bi bi-pencil-square me-1"></i> Modificar unidad de medida
        </h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=u_medida">Unidad de medida</a></li>
            <li class="breadcrumb-item active">Modificar</li>
        </ol>
    </section>

    <section class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <form role="form" action="modules/u_medida/process.php?act=update"
                        method="POST">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" name="codigo"
                                    value="<?php echo $data['id_u_medida']; ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="u_descrip" class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="u_descrip"
                                    value="<?php echo $data['u_descrip']; ?>" required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" name="update" class="btn btn-primary">Guardar</button>
                                <a href="?module=u_medida" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php }
