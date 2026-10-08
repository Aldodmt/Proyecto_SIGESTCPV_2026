<?php
if ($_GET['form'] == 'add') { ?>
  <section class="app-content-header">
    <h1>
      <i class="bi bi-pencil-square me-1"></i> Agregar Departamento
    </h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page"><a href="?module=departamento">Departamentos</a></li>
        <li class="breadcrumb-item"><a>Agregar</a></li>
      </ol>
    </nav>
  </section>

  <section class="app-content">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <form role="form" action="modules/departamento/proses.php?act=insert" method="POST">
              <div class="row mb-3">
                <?php
                //metodo para generar codigo
                $query_id = mysqli_query($mysqli, "SELECT MAX(id_departamento) as id FROM departamento")
                  or die('Error' . mysqli_error($mysqli));
                $count = mysqli_num_rows($query_id);
                if ($count <> 0) {
                  $data_id = mysqli_fetch_assoc($query_id);
                  $codigo = $data_id['id'] + 1;
                } else {
                  $codigo = 1;
                }
                ?>
                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Codigo</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>" readonly>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Descripcion</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="dep_descripcion" pleaceholder="Ingresa un departamento"
                      required>
                  </div>
                </div>
                <br>

                <div class="mt-3">
                  <div class="row mb-3">
                    <div class="offset-sm-2 col-sm-10">
                      <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                      <a href="?module=departamento" class="btn btn-secondary">Cancelar</a>
                    </div>
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
    $query = mysqli_query($mysqli, "SELECT *FROM departamento WHERE id_departamento = '$_GET[id]'")
      or die('Error' . mysqli_error($mysqli));
    $data = mysqli_fetch_assoc($query);
  } ?>
  <section class="app-content-header">
    <h1>
      <i class="bi bi-pencil-square me-1"></i> Modificar Departamento
    </h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i>Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page"><a href="?module=departamento">Departamentos</a></li>
        <li class="breadcrumb-item"><a>Modificar</a></li>
      </ol>
    </nav>
  </section>


  <section class="app-content">
    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <form role="form" action="modules/departamento/proses.php?act=update" method="POST">
            <div class="card-body">
              <?php
              //metodo para generar codigo
            
              ?>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Codigo</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" name="codigo" value="<?php echo $data['id_departamento']; ?>"
                    readonly>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Descripcion</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" name="dep_descripcion"
                    value="<?php echo $data['dep_descripcion']; ?>" required>
                </div>
              </div>
              <br>

              <div class="mt-3">
                <div class="row mb-3">
                  <div class="offset-sm-2 col-sm-10">
                    <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                    <a href="?module=departamento" class="btn btn-secondary">Cancelar</a>
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