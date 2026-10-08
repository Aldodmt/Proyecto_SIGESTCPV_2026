<?php
if ($_GET['form'] == 'add') { ?>
    <section class="app-content-header">
        <h1>
            <i class="bi bi-pencil-square me-1"></i>Agregar ciudad
        </h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=producto">Productos</a></li>
            <li class="breadcrumb-item active">Agregar</li>
        </ol>
    </section>

    <section class="app-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form role="form" action="modules/producto/process.php?act=insert"
                            method="POST">
                            <div class="row mb-3">
                                <?php
                                // Método para generar código
                                $query_id = mysqli_query($mysqli, "SELECT MAX(cod_producto) as id FROM producto") or die('error' . mysqli_error($mysqli));
                                $count = mysqli_num_rows($query_id);
                                if ($count <> 0) {
                                    $data_id = mysqli_fetch_assoc($query_id);
                                    $codigo = $data_id['id'] + 1;
                                } else {
                                    $codigo = 1;
                                }
                                ?>
                                <label class="col-sm-2 col-form-label">Código</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>"
                                        readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Producto</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="p_descrip"
                                        placeholder="Ingrese un producto" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Tipo de producto</label>
                                <div class="col-sm-5">
                                    <select class="form-select" name="tipo_producto"
                                        data-placeholder="--Seleccione el tipo de producto--" autocomplete="off" required>
                                        <option value=""></option>
                                        <?php
                                        $query_tp = mysqli_query($mysqli, "SELECT *FROM tipo_producto") or die('error' . mysqli_error($mysqli));
                                        while ($data_tp = mysqli_fetch_assoc($query_tp)) {
                                            echo "<option value=\"$data_tp[cod_tipo_prod]\">$data_tp[t_p_descrip]</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Unidad de medida</label>
                                <div class="col-sm-5">
                                    <select class="form-select" name="u_medida"
                                        data-placeholder="--Seleccione la unidad de medida--" autocomplete="off" required>
                                        <option value=""></option>
                                        <?php
                                        $query_um = mysqli_query($mysqli, "SELECT *FROM u_medida") or die('error' . mysqli_error($mysqli));
                                        while ($data_um = mysqli_fetch_assoc($query_um)) {
                                            echo "<option value=\"$data_um[id_u_medida]\">$data_um[u_descrip]</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Tipo de Impuesto</label>
                                <div class="col-sm-5">
                                    <select class="form-select" name="tipo_impuesto"
                                        data-placeholder="--Seleccione el tipo de impuesto--" autocomplete="off" required>
                                        <option value=""></option>
                                        <option value="10%">10</option>
                                        <option value="5%">5</option>
                                        <option value="EXENTA">Exenta</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row mb-3 row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                                        <a href="?module=producto" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } elseif ($_GET['form'] == 'edit') {
    if (isset($_GET['id'])) {
        $query = mysqli_query($mysqli, "SELECT *FROM v_producto WHERE cod_producto = '$_GET[id]'") or die('error' . mysqli_error($mysqli));
        $data = mysqli_fetch_assoc($query);
    } ?>
    <section class="app-content-header">
        <h1>
            <i class="bi bi-pencil-square me-1"></i>Modificar producto
        </h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=producto">Productos</a></li>
            <li class="breadcrumb-item active">Modificar</li>
        </ol>
    </section>

    <section class="app-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form role="form" action="modules/producto/process.php?act=update"
                            method="POST">
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Código</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="codigo"
                                        value="<?php echo $data['cod_producto']; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Producto</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="p_descrip"
                                        value="<?php echo $data['p_descrip']; ?>" required>
                                </div>
                            </div>

                            <!-- Combo buscador -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Tipo de producto</label>
                                <div class="col-sm-5">
                                    <select class="form-select" name="tipo_producto" required>
                                        <option value="">--Seleccione un tipo de producto--</option>
                                        <?php
                                        $query_tp = mysqli_query($mysqli, "SELECT * FROM tipo_producto") or die('error' . mysqli_error($mysqli));
                                        while ($data_tp = mysqli_fetch_assoc($query_tp)) {
                                            $selected = ($data_tp['cod_tipo_prod'] == $data['cod_tipo_prod']) ? 'selected' : '';
                                            echo "<option value='{$data_tp['cod_tipo_prod']}' $selected>{$data_tp['t_p_descrip']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Combo buscador -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Unidad de medida</label>
                                <div class="col-sm-5">
                                    <select class="form-select" name="u_medida" required>
                                        <option value="">--Seleccione una unidad de medida--</option>
                                        <?php
                                        $query_um = mysqli_query($mysqli, "SELECT * FROM u_medida") or die('error' . mysqli_error($mysqli));
                                        while ($data_um = mysqli_fetch_assoc($query_um)) {
                                            $selected = ($data_um['id_u_medida'] == $data['id_u_medida']) ? 'selected' : '';
                                            echo "<option value='{$data_um['id_u_medida']}' $selected>{$data_um['u_descrip']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">Tipo de Impuesto</label>
                                <div class="col-sm-5">
                                    <select class="form-select" name="tipo_impuesto"
                                        data-placeholder="--Seleccione el tipo de impuesto--" required>
                                        <option value="">--Seleccione el tipo de impuesto--</option>
                                        <option value="10%" <?= (isset($data['tipo_impuesto']) && $data['tipo_impuesto'] == '10%') ? 'selected' : '' ?>>10%</option>
                                        <option value="5%" <?= (isset($data['tipo_impuesto']) && $data['tipo_impuesto'] == '5%') ? 'selected' : '' ?>>5%</option>
                                        <option value="EXENTA" <?= (isset($data['tipo_impuesto']) && $data['tipo_impuesto'] == 'EXENTA') ? 'selected' : '' ?>>Exenta</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row mb-3">
                                    <div class="offset-sm-2 col-sm-10">
                                        <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                                        <a href="?module=producto" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php }
