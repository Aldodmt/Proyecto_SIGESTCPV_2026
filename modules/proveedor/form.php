<?php
if ($_GET['form'] == 'add') { ?>
    <section class="app-content-header">
        <h1><i class="bi bi-pencil-square me-1"></i> Agregar proveedor</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="?module=proveedor">Proveedores</a></li>
            <li class="breadcrumb-item active">Agregar</li>
        </ol>
    </section>

    <section class="app-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form role="form" action="modules/proveedor/process.php?act=insert"
                        method="POST">
                        <div class="card-body">
                            <?php
                            // Generación del código
                            $query_id = mysqli_query($mysqli, "SELECT MAX(cod_proveedor) as id FROM proveedor") or die('error' . mysqli_error($mysqli));
                            $count = mysqli_num_rows($query_id);
                            $codigo = ($count != 0) ? mysqli_fetch_assoc($query_id)['id'] + 1 : 1;
                            ?>
                            <div class="row mb-3">
                                <label class="col-form-label">Código</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>"
                                        readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">Razón Social</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="razon_social"
                                        placeholder="Ingrese la razón social" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">RUC</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="ruc" id="ruc" placeholder="00000000-0"
                                        autocomplete="off" required maxlength="10" required>
                                    <div id="ruc-error" class="text-danger mt-1" style="display:none;">RUC no válido</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">Dirección</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="direccion"
                                        placeholder="Ingrese la dirección" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">Teléfono</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="telefono"
                                        placeholder="Ingrese su número de teléfono" autocomplete="off" maxlength="15"
                                        oninput="soloNumeros(this)" required>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row mb-3">
                                    <div class="offset-sm-2 col-sm-10">
                                        <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                                        <a href="?module=proveedor" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

<?php } elseif ($_GET['form'] == 'edit') {
    if (isset($_GET['id'])) {
        $query = mysqli_query($mysqli, "SELECT * FROM proveedor WHERE cod_proveedor = '$_GET[id]'") or die('error' . mysqli_error($mysqli));
        $data = mysqli_fetch_assoc($query);
    } ?>
    <section class="app-content-header">
        <h1><i class="bi bi-pencil-square me-1"></i> Modificar proveedor</h1>
        <ol class="breadcrumb">
            <li><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li><a href="?module=proveedor">Proveedores</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>

    <section class="app-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form role="form" action="modules/proveedor/process.php?act=update"
                        method="POST">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label class="col-form-label">Código</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="codigo"
                                        value="<?php echo $data['cod_proveedor'] ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">Razón Social</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="razon_social"
                                        value="<?php echo $data['razon_social']; ?>" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">RUC</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="ruc" id="ruc" placeholder="00000000-0"
                                        value="<?php echo $data['ruc']; ?>" autocomplete="off" required maxlength="10">
                                    <div id="ruc-error" class="text-danger mt-1" style="display:none;">RUC no válido</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">Dirección</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="direccion"
                                        value="<?php echo $data['direccion']; ?>" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-form-label">Teléfono</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="telefono"
                                        value="<?php echo $data['telefono']; ?>" autocomplete="off" maxlength="15"
                                        oninput="soloNumeros(this)">
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row mb-3">
                                    <div class="offset-sm-2 col-sm-10">
                                        <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar">
                                        <a href="?module=proveedor" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php } ?>

<script>
    // Permite solo números
    function soloNumeros(e) {
        e.value = e.value.replace(/[^0-9]/g, '');
    }

    // Formatear automáticamente el RUC: 00000000-0
    document.getElementById('ruc').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '').slice(0, 9); // solo 8 + 1 dígito
        let part1 = val.slice(0, 8);
        let part2 = val.slice(8, 9);
        e.target.value = part1 + (part2 ? '-' + part2 : '');
    });

    // Calcular dígito verificador según los 8 primeros dígitos
    function calcularDV(base) {
        let total = 0;
        let factor = 2;
        // Se multiplican los dígitos de derecha a izquierda
        for (let i = base.length - 1; i >= 0; i--) {
            total += parseInt(base[i]) * factor;
            factor++;
            if (factor > 7) factor = 2; // ciclos de 2 a 7
        }
        let resto = total % 11;
        let dv = 11 - resto;
        if (dv === 10) dv = 0;
        if (dv === 11) dv = 1;
        return dv;
    }

    // Validar cualquier RUC de 9 dígitos
    function validarRUC(rucCompleto) {
        let rucNumeros = rucCompleto.replace(/\D/g, '');
        if (rucNumeros.length !== 9) return false;
        let base = rucNumeros.slice(0, 8);
        let dvIngresado = parseInt(rucNumeros[8]);
        let dvCalculado = calcularDV(base);
        return dvIngresado === dvCalculado;
    }

    $(document).ready(function () {
        $("form").on("submit", function (e) {
            let rucVal = $("#ruc").val().trim();
            if (!validarRUC(rucVal)) {
                e.preventDefault();
                $("#ruc-error").text("No se puede guardar. RUC no válido").show();
                $("#ruc").focus();
                return false;
            }
        });

        $("#ruc").on("input", function () {
            let rucVal = $(this).val();
            if (validarRUC(rucVal)) {
                $("#ruc-error").hide();
            } else {
                $("#ruc-error").text("RUC no válido").show();
            }
        });
    });
</script>