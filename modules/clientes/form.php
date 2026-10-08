<?php
if ($_GET['form'] == 'add') { ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="card">
                <div class="card-header">
                    <h5>
                        <i class="cui-pencil"></i> Agregar Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i></i> Inicio</a></li>
                            <li class="breadcrumb-item"><a href="?module=clientes">Clientes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Agregar</li>
                        </ol>
                    </nav>

                    <form action="modules/clientes/proses.php?act=insert" method="POST">
                        <?php
                        $query_id = mysqli_query($mysqli, "SELECT MAX(id_cliente) as id FROM clientes") or die("Error " . mysqli_error($mysqli));
                        $count = mysqli_num_rows($query_id);
                        $codigo = ($count <> 0) ? mysqli_fetch_assoc($query_id)['id'] + 1 : 1;
                        ?>
                        <div class="row mb-3">
                            <label>Código</label>
                            <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>" readonly>
                        </div>

                        <div class="row mb-3">
                            <label>Ciudad</label>
                            <select class="form-select" name="codigo_ciudad" required>
                                <option value="" disabled selected>--Seleccionar ciudad--</option>
                                <?php
                                $query_ciu = mysqli_query($mysqli, "SELECT ciu.cod_ciudad, dep.dep_descripcion, ciu.descrip_ciudad FROM ciudad ciu JOIN departamento dep ON ciu.id_departamento = dep.id_departamento ORDER BY ciu.cod_ciudad ASC") or die("Error " . mysqli_error($mysqli));
                                while ($data_ciu = mysqli_fetch_assoc($query_ciu)) {
                                    echo "<option value=\"$data_ciu[cod_ciudad]\">$data_ciu[dep_descripcion] | $data_ciu[descrip_ciudad]</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <label>Ruc-Ci</label>
                            <input type="text" class="form-control" name="ci_ruc" placeholder="Ingresa un ruc o ci"
                                required>
                        </div>

                        <div class="row mb-3">
                            <label>Nombre</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ingresa tu nombre" required>
                        </div>

                        <div class="row mb-3">
                            <label>Apellido</label>
                            <input type="text" class="form-control" name="apellido" placeholder="Ingresa tu apellido"
                                required>
                        </div>

                        <div class="row mb-3">
                            <label>Dirección</label>
                            <input type="text" class="form-control" name="direccion">
                        </div>

                        <div class="row mb-3">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" name="telefono">
                        </div>

                        <div class="row mb-3">
                            <button type="submit" class="btn btn-primary" name="Guardar">Guardar</button>
                            <a href="?module=clientes" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php } elseif ($_GET['form'] == 'edit') {
    if (isset($_GET['id'])) {
        $query = mysqli_query($mysqli, "SELECT * FROM v_clientes WHERE id_cliente = '$_GET[id]'") or die("Error " . mysqli_error($mysqli));
        $data = mysqli_fetch_assoc($query);
    } ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="card">
                <div class="card-header">
                    <h5>
                        <i class="cui-pencil"></i> Modificar Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i></i> Inicio</a></li>
                            <li class="breadcrumb-item"><a href="?module=clientes">Clientes</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Modificar</li>
                        </ol>
                    </nav>

                    <form action="modules/clientes/proses.php?act=update" method="POST">
                        <div class="row mb-3">
                            <label>Código</label>
                            <input type="text" class="form-control" name="codigo" value="<?php echo $data['id_cliente']; ?>"
                                readonly>
                        </div>

                        <div class="row mb-3">
                            <label>Ciudad</label>
                            <select class="form-select" name="codigo_ciudad" required>
                                <option value="<?php echo $data['cod_ciudad']; ?>"><?php echo $data['descrip_ciudad']; ?>
                                </option>
                                <?php
                                $query_ciu = mysqli_query($mysqli, "SELECT ciu.cod_ciudad, dep.dep_descripcion, ciu.descrip_ciudad FROM ciudad ciu JOIN departamento dep ON ciu.id_departamento = dep.id_departamento ORDER BY ciu.cod_ciudad ASC") or die("Error " . mysqli_error($mysqli));
                                while ($data_ciu = mysqli_fetch_assoc($query_ciu)) {
                                    echo "<option value=\"$data_ciu[cod_ciudad]\">$data_ciu[dep_descripcion] | $data_ciu[descrip_ciudad]</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <label>Ruc-Ci</label>
                            <input type="text" class="form-control" name="ci_ruc" value="<?php echo $data['ci_ruc']; ?>"
                                required>
                        </div>

                        <div class="row mb-3">
                            <label>Nombre</label>
                            <input type="text" class="form-control" name="nombre" value="<?php echo $data['cli_nombre']; ?>"
                                required>
                        </div>

                        <div class="row mb-3">
                            <label>Apellido</label>
                            <input type="text" class="form-control" name="apellido"
                                value="<?php echo $data['cli_apellido']; ?>" required>
                        </div>

                        <div class="row mb-3">
                            <label>Dirección</label>
                            <input type="text" class="form-control" name="direccion"
                                value="<?php echo $data['cli_direccion']; ?>">
                        </div>

                        <div class="row mb-3">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" name="telefono"
                                value="<?php echo $data['cli_telefono']; ?>">
                        </div>

                        <div class="row mb-3">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <a href="?module=clientes" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php }
?>