<?php
if ($_GET['form'] == 'add') { ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=ventas">Ventas</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>
            <div class="col-sm-6">
                <h1><i class="bi bi-pencil-square me-1"></i> Agregar Venta</h1>
            </div>
            <div class="card">
                <div class="card-header"><strong>Formulario de Ventas</strong></div>
                <form action="modules/ventas/proses.php?act=insert" method="POST"
                    id="formPresupuesto">
                    <div class="card-body">
                        <?php
                        // Generar código único
                        $query_id = mysqli_query($mysqli, "SELECT MAX(cod_venta) as id FROM venta")
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


                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Cliente</label>
                            <div class="col-md-4">
                                <select class="form-select" name="codigo_cliente" required>
                                    <option value="" disabled selected>-- Seleccionar Cliente --</option>
                                    <?php
                                    $query_prove = mysqli_query($mysqli, "SELECT id_cliente, cli_nombre, cli_apellido FROM clientes ORDER BY id_cliente ASC")
                                        or die("Error: " . mysqli_error($mysqli));
                                    while ($row = mysqli_fetch_assoc($query_prove)) {
                                        echo "<option value='{$row['id_cliente']}'>{$row['cli_nombre']} | {$row['cli_apellido']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <label class="col-md-2 col-form-label">N° de Factura</label>
                            <div class="col-md-4">
                                <?php
                                $query_id = mysqli_query($mysqli, "SELECT MAX(nro_factura) as nro FROM venta")
                                    or die("Error: " . mysqli_error($mysqli));
                                $data_id = mysqli_fetch_assoc($query_id);
                                $nro_factura = $data_id['nro'] + 1 ?? 1;

                                // Formatear el número de factura 
                                $formato_factura = "000-000-" . str_pad($nro_factura, 3, "0", STR_PAD_LEFT);
                                ?>

                                <!-- Campo para mostrar el formato de factura -->
                                <input type="text" class="form-control" value="<?php echo $formato_factura; ?>" readonly>

                                <!-- Campo oculto para enviar el número de factura real -->
                                <input type="hidden" name="nro_factura" value="<?php echo $nro_factura; ?>">
                            </div>

                        </div>
                        <br>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Deposito</label>
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
                            <label class="col-md-2 col-form-label">N° de Timbrado</label>
                            <div class="col-md-4">
                                <?php
                                // Consulta para obtener el número de timbrado y su ID
                                $query_tim = mysqli_query($mysqli, "SELECT id_timbrado, numero_timbrado FROM timbrado ORDER BY id_timbrado DESC LIMIT 1")
                                    or die("Error: " . mysqli_error($mysqli));

                                $dato_timbrado = mysqli_fetch_assoc($query_tim);

                                // Manejo de casos cuando no hay registros en la tabla
                                if ($dato_timbrado) {
                                    $codigo_tim = $dato_timbrado['id_timbrado']; // ID del timbrado actual
                                    $numero_timbrado = $dato_timbrado['numero_timbrado'];
                                }
                                ?>
                                <!-- Campo oculto para almacenar el id_timbrado -->
                                <input type="hidden" name="codigo_tim" value="<?php echo $codigo_tim; ?>">
                                <!-- Campo visible con el número de timbrado -->
                                <input type="text" class="form-control" name="timb" value="<?php echo $numero_timbrado; ?>"
                                    readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Productos</label>
                            <div class="col-md-10">
                                <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                    data-bs-target="#myModal">
                                    <i class="bi bi-plus-lg"></i> Buscar Productos
                                </button>
                            </div>
                        </div>
                        <div id="resultados" class="mt-3"></div>
                        <br>
                        <br>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" name="Guardar">Guardar</button>
                            <a href="?module=ventas" class="btn btn-secondary">Cancelar</a>
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
            url: './ajax/productos_pedidos_v.php',
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
</script>

<script>
    function agregar(id) {
        var precio_compra = $('#precio_' + id).val();
        var cantidad = $('#cantidad_' + id).val();
        if (isNaN(cantidad)) {
            alert('Esto no es un nro');
            document.getElementById('cantidad_' + id).focus();
            return false;
        }
        if (isNaN(precio_compra)) {
            alert('Esto no es un nro');
            document.getElementById('precio_' + id).focus();
            return false;
        }
        //fin de la validacion
        var parametros = { "id": id, "precio_": precio_compra, "cantidad_": cantidad };
        $.ajax({
            type: "POST",
            url: "./ajax/agregar_pedidos_v.php",
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
            url: "./ajax/agregar_pedidos_v.php",
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