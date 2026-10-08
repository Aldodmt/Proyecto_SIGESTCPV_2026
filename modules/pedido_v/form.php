<?php
if ($_GET['form'] == 'add') { ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=pedido_v">Pedidos ventas</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>
            <div class="col-sm-6">
                <h1><i class="bi bi-pencil-square me-1"></i> Agregar Pedido Ventas</h1>
            </div>
            <div class="card">
                <div class="card-header"><strong>Formulario de Pedidos</strong></div>
                <form action="modules/pedido_v/proses.php?act=insert" method="POST">
                    <div class="card-body">
                        <?php
                        // Generar código único
                        $query_id = mysqli_query($mysqli, "SELECT MAX(id_pedido_v) as id FROM pedido_v")
                            or die("Error: " . mysqli_error($mysqli));
                        $data_id = mysqli_fetch_assoc($query_id);
                        $codigo = $data_id['id'] + 1 ?? 1;
                        ?>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?php echo $codigo; ?>"
                                    readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="fecha" value="<?php echo date("Y-m-d"); ?>"
                                    readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="hora" value="<?php echo date("H:i:s"); ?>"
                                    readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-groug">
                            <label class="col-md-2 col-form-label">Depósito</label>
                            <div class="col-md-4">
                                <select class="form-select" name="codigo_deposito" required>
                                    <option value="" disabled selected>-- Seleccionar Depósito --</option>
                                    <?php
                                    $query_dep = mysqli_query($mysqli, "SELECT cod_deposito, descrip FROM deposito ORDER BY descrip ASC")
                                        or die("Error: " . mysqli_error($mysqli));
                                    while ($row = mysqli_fetch_assoc($query_dep)) {
                                        echo "<option value='{$row['cod_deposito']}'>{$row['descrip']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Producto</label>
                            <div class="col-md-10">
                                <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                    data-bs-target="#myModal">
                                    <i class="bi bi-plus-lg"></i> Agregar Productos
                                </button>
                            </div>
                        </div>
                        <div id="resultados" class="mt-3"></div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" name="Guardar">Guardar</button>
                        <a href="?module=pedido_v" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>


<script>
    $(document).ready(function () {
        load(1);
    });

    function load(page) {
        var x = $("#x").val();
        var parametros = { "action": "ajax", "page": page, "x": x };
        $("#loader").fadeIn('slow');
        $.ajax({
            url: './ajax/productos_pedidos.php',
            data: parametros,
            beforeSend: function (objeto) {
                $('#loader').html('<img src="./images/ajax-loader.gif">Cargando....');
            },
            success: function (data) {
                $(".outer_div").html(data).fadeIn('slow');
                $('#loader').html('');
            }
        });
    }

    function agregar(id) {
        var cantidad = $('#cantidad_' + id).val();
        if (isNaN(cantidad)) {
            alert('Esto no es un número');
            document.getElementById('cantidad_' + id).focus();
            return false;
        }
        // Fin de la validación
        var parametros = { "id": id, "cantidad": cantidad };
        $.ajax({
            type: "POST",
            url: "./ajax/agregar_pedidos.php",
            data: parametros,
            beforeSend: function (objeto) {
                $("#resultados").html("Mensaje: Cargando...");
            },
            success: function (datos) {
                $("#resultados").html(datos);
            }
        });
    }

    function eliminar(id) {
        $.ajax({
            type: "GET",
            url: "./ajax/agregar_pedidos.php",
            data: "id=" + id,
            beforeSend: function (objeto) {
                $("#resultados").html("Mensaje: cargando...");
            },
            success: function (datos) {
                $("#resultados").html(datos);
            }
        });
    }
</script>

<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModallabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModallabel">Buscar Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="x" placeholder="Buscar productos"
                                onkeyup="load(1)">
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary" onclick="load(1)">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
                <div id="loader" class="text-center d-none">
                    <img src="./images/ajax-loader.gif" alt="Cargando..." /> Cargando...
                </div>
                <div class="outer_div"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>