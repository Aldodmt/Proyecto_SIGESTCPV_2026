<?php
if($_GET['form']=='add'){ ?>
    <section class="section">
        <h2 class="section-title">
            <i class="bi bi-pencil-square me-1"></i> Agregar Depósito
        </h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=deposito">Depósito</a></li>
            <li class="breadcrumb-item active">Más</li>
        </ol>
    </section>

    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <form role="form" action="modules/deposito/process.php?act=insert" method="POST">
                        <div class="card-body">
                            <?php
                            // Método para generar código
                            $query_id = mysqli_query($mysqli, "SELECT MAX(cod_deposito) as id FROM deposito") or die('error'.mysqli_error($mysqli));
                            $count = mysqli_num_rows($query_id);
                            if($count <> 0){
                                $data_id = mysqli_fetch_assoc($query_id);
                                $codigo = $data_id['id'] + 1;
                            }else{
                                $codigo = 1;
                            }
                            ?>
                            <div class="row mb-3 row">
                                <label class="col-sm-2 col-form-label">Código</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3 row">
                                <label class="col-sm-2 col-form-label">Descripción</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="descrip" placeholder="Ingrese un depósito" required>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row mb-3 row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                                        <a href="?module=deposito" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php }
elseif($_GET['form']=='edit'){
    if(isset($_GET['id'])){
        $query = mysqli_query($mysqli, "SELECT * FROM deposito WHERE cod_deposito = '$_GET[id]'") or die('error'.mysqli_error($mysqli));
        $data = mysqli_fetch_assoc($query);
    } ?>
    <section class="section">
        <h2 class="section-title">
            <i class="bi bi-pencil-square me-1"></i> Modificar Depósito
        </h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=deposito">Depósito</a></li>
            <li class="breadcrumb-item active">Modificar</li>
        </ol>
    </section>

    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <form role="form" action="modules/deposito/process.php?act=update" method="POST">
                        <div class="card-body">
                            <div class="row mb-3 row">
                                <label class="col-sm-2 col-form-label">Código</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="codigo" value="<?php echo $data['cod_deposito']; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3 row">
                                <label class="col-sm-2 col-form-label">Descripción</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="descrip" value="<?php echo $data['descrip']; ?>" required>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row mb-3 row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                                        <a href="?module=deposito" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php }
?>
