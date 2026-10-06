<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once "config/database.php";

$id_usuario = $_SESSION['id_user'] ?? null;
if (!$id_usuario) {
    die("No se detectó usuario logueado.");
}

// Generar código único para orden de compra
$query_id = mysqli_query($mysqli, "SELECT MAX(id_orden_comp) as id FROM orden_compra") or die("Error: " . mysqli_error($mysqli));
$data_id = mysqli_fetch_assoc($query_id);
$id_orden_c = ($data_id['id'] ?? 0) + 1;

$fecha = date("Y-m-d");
$hora = date("H:i:s");

//formulario para agregar
if ($_GET['form'] == 'add') {
    $codigo = $id_orden_c;
    $estado = 'BORRADOR';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=orden_c">Orden de Compra</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Agregar Orden de Compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Orden de Compra</strong></div>
                <form id="formOrdenCompra" class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_E" value="<?= $fecha ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="hora" value="<?= $hora ?>" readonly>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la Orden: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_orden" name="estado" value="<?= $estado ?>">

                        <div class="form-group d-none" id="proveedores">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-4">
                                <select class="form-control" name="codigo_proveedor" required>
                                    <option value="" disabled selected>-- Seleccionar Proveedor --</option>
                                    <?php
                                    $query_prove = mysqli_query($mysqli, "SELECT cod_proveedor, razon_social FROM proveedor ORDER BY razon_social ASC");
                                    while ($row = mysqli_fetch_assoc($query_prove)) {
                                        echo "<option value='{$row['cod_proveedor']}'>{$row['razon_social']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row align-items-center">
                            <label class="col-md-2 col-form-label">Modo de Orden de compra</label>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="toggleProductos" />
                                    <label class="form-check-label" for="toggleProductos">
                                        Orden de productos sin presupuesto
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10">
                                <!-- Botón de pedidos -->
                                <button type="button" class="btn btn-info" id="btnAgregarPresupuestos"
                                    data-coreui-toggle="modal" data-coreui-target="#myModal">
                                    <i class="fa fa-plus"></i> Agregar Presupuestos
                                </button>

                                <!-- Botón de productos generales -->
                                <button type="button" class="btn btn-info d-none" id="btnAgregarProductos"
                                    data-coreui-toggle="modal" data-coreui-target="#myModalProductos">
                                    <i class="fa fa-plus"></i> Agregar Productos
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de presupuestos asociados -->
                        <div id="resultados" class="mt-3"></div>
                        <input type="hidden" name="productos_json" id="productos_json">
                        <input type="hidden" id="id_presupuesto" name="id_presupuesto">

                    </div>

                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar" disabled>
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=orden_c" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Salir
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

// FORMULARIO DETALLE ORDEN
if ($_GET['form'] == 'detalle') {
    $id_orden_c = intval($_GET['id_orden_comp']);
    $query_orden = mysqli_query($mysqli, "
        SELECT o.*, p.cod_proveedor, pr.razon_social, pr.ruc
        FROM orden_compra o
        LEFT JOIN presupuesto p ON o.id_presupuesto = p.id_presupuesto
        LEFT JOIN proveedor pr ON o.cod_proveedor = pr.cod_proveedor
        WHERE o.id_orden_comp = $id_orden_c
    ") or die("Error: " . mysqli_error($mysqli));

    $orden = mysqli_fetch_assoc($query_orden);

    $codigo = $orden['id_orden_comp'];
    $fecha_e = $orden['fecha'];
    $hora = $orden['hora'];
    $estado = $orden['estado'];
    $id_presupuesto = $orden['id_presupuesto'];
    $cod_proveedor = $orden['cod_proveedor'] ?? '';
    $razon_social = $orden['razon_social'] ?? '';
    $ruc = $orden['ruc'] ?? '';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=orden_c">Orden de Compra</a></li>
                        <li class="breadcrumb-item active">Detalles de la Orden de Compra</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Detalles de la Orden de Compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Orden de Compra</strong></div>
                <form id="formOrdenCompra" class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="fecha_E" value="<?= $fecha_e ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="hora" value="<?= $hora ?>" readonly>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la orden de compra: <strong><?= strtoupper($estado) ?></strong>
                        </div>
                        <input type="hidden" id="estado_orden" name="estado" value="<?= $estado ?>">
                        <input type="hidden" name="productos_json" id="productos_json">

                        <!-- Proveedor -->
                        <div class="form-group row mt-3">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="<?= $razon_social ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">RUC</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="<?= $ruc ?>" readonly>
                            </div>
                        </div>

                        <!-- Tabla de Detalles de la Orden -->
                        <div id="resultados" class="mt-3">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Código Producto</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    // Traer los detalles de la orden directamente
                                    $sql_detalle = mysqli_query($mysqli, "
                                        SELECT d.cod_producto, p.p_descrip, d.cantidad, d.precio_unit
                                        FROM detalle_orden_comp d
                                        JOIN producto p ON d.cod_producto = p.cod_producto
                                        WHERE d.id_orden_comp = $id_orden_c
                                    ");

                                    if (mysqli_num_rows($sql_detalle) > 0) {
                                        while ($det = mysqli_fetch_assoc($sql_detalle)) {
                                            $subtotal = $det['cantidad'] * $det['precio_unit'];
                                            echo "<tr>
                                            <td>{$det['cod_producto']}</td>
                                            <td>{$det['p_descrip']}</td>
                                            <td class='text-end'>" . number_format($det['cantidad'], 2) . "</td>
                                            <td class='text-end'>" . number_format($det['precio_unit'], 2) . "</td>
                                            <td class='text-end'>" . number_format($subtotal, 2) . "</td>
                                        </tr>";
                                            $total += $subtotal;
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No hay detalles cargados para esta orden.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <div class="d-flex justify-content-end align-items-center mt-2">
                                <label class="me-2 mb-0"><strong>Total:</strong></label>
                                <input type="text" class="form-control text-end" name="total"
                                    value="<?= number_format($total, 2) ?>" readonly style="width: 120px;">
                            </div>
                        </div>

                    </div>

                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar" <?= $estado !== 'BORRADOR' ? '' : 'disabled' ?>>
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" <?= $estado === 'ANULADO' ? 'disabled' : '' ?>>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=orden_c" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Salir
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
} ?>


<!-- Modal Buscar Pedido -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModallabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModallabel">Buscar Presupuestos</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="x">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" onclick="load(1, true)">
                                <i class="cil-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
                <div id="loader" class="text-center" style="display:none;">
                    <img src="./images/ajax-loader.gif" alt="Cargando..." /> Cargando...
                </div>
                <div class="outer_div"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Productos Generales -->
<div class="modal fade" id="myModalProductos" tabindex="-1" aria-labelledby="myModalProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalProductosLabel">Agregar Productos Generales</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="xProductos" placeholder="Buscar productos...">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" onclick="loadProductos(1, true)">
                                <i class="cil-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
                <div id="loaderProductos" class="text-center" style="display:none;">
                    <img src="./images/ajax-loader.gif" alt="Cargando..." /> Cargando...
                </div>
                <div class="outer_div_productos"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    var productos = [];
    var id_presupuesto = null;

    function validarFormulario() {

        if (productos.length === 0) {
            alert("Debes agregar al menos un producto.");
            return false;
        }

        return true;
    }

    function load(page, mostrarLoader = true) {
        var x = $("#x").val() || '';
        if (mostrarLoader) $("#loader").fadeIn('slow');
        $.ajax({
            url: './ajax/productos_orden.php?action=ajax&page=' + page + '&x=' + x,
            beforeSend: function () {
                if (mostrarLoader) $('#loader').show();
            },
            success: function (data) {
                $(".outer_div").html(data).fadeIn('slow');
                $('#loader').hide();
            },
            error: function (xhr, status, error) {
                alert("Error en la solicitud AJAX: " + error);
            }
        });
    }

    function loadProductos(page, mostrarLoader = true) {
        var x = $("#xProductos").val() || '';
        if (mostrarLoader) $("#loaderProductos").fadeIn('slow');
        $.ajax({
            url: './ajax/productos_general.php?action=ajax&page=' + page + '&x=' + x,
            beforeSend: function () {
                if (mostrarLoader) $('#loaderProductos').show();
            },
            success: function (data) {
                $(".outer_div_productos").html(data).fadeIn('slow');
                $('#loaderProductos').hide();
            },
            error: function (xhr, status, error) {
                alert("Error en la solicitud AJAX: " + error);
            }
        });
    }

    function seleccionarPresupuesto(id_presupuesto) {
        // Validar si ya hay productos cargados
        if ($('#resultados tbody tr').length > 0) {
            alert("Ya hay productos cargados. Debes eliminar los productos actuales antes de seleccionar otro pedido.");
            return; // Salir de la función
        }
        //para evitar que se cargue o cualquiera lgmt ya estoy haciendo aca
        if ($('#resultados table').data('tipo') === 'general') {
            alert("Ya hay productos generales cargados. No se puede seleccionar un presupuesto.");
            return;
        }
        // Generar tabla de presupuestos si no existe
        if ($('#resultados table').length === 0) {
            generarTabla('presupuesto');
        }

        $.post("ajax/agregar_orden.php", { id_presupuesto: id_presupuesto }, function (data) {
            // Agrego las filas devueltas al tbody de la tabla
            $('#resultados tbody').append(data);
            $('#id_presupuesto').val(id_presupuesto);

            actualizarProductos();
        });

        CoreUI.Modal.getInstance(document.getElementById('myModal')).hide();
    }



    function eliminarPresupuesto(id_presupuesto) {
        // Eliminar filas del presupuesto
        $('tr[data-presu="' + id_presupuesto + '"]').remove();
        actualizarProductos();

        // Verificar si quedó alguna fila de pedido
        if ($('#resultados tbody tr[data-presu]').length === 0) {
            $('#resultados').html(''); // limpio toda la tabla
            $('#id_presupuesto').val('');   // reseteo el id del presupuesto
        }
    }

    function actualizarProductos() {
        productos = [];
        $('#resultados tbody tr').each(function () {
            var cod = $(this).data('cod') || 0;
            var cantidad = parseFloat($(this).find('.cantidad').data('valor')) || 0;
            var precio = parseFloat($(this).find('.precio_unit').data('valor')) || 0;
            productos.push({ codigo_producto: cod, cantidad: cantidad, precio_unitario: precio });
        });
    }


    //actualizar subtotal para orden con presupuestos
    function actualizarSubtotales() {
        $('#resultados tbody tr').each(function () {
            let cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            let precio = parseFloat($(this).find('.precio_unit').val()) || 0;
            $(this).find('.subtotal').text((cantidad * precio).toFixed(2));
        });
        actualizarTotalGeneral();
    }
    //actualizar subtotal para orden sin presupuesto
    function actualizarSubtotalesGeneral() {
        $('#resultados tbody tr').each(function () {
            let cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            let precio = parseFloat($(this).find('.precio_unit').text()) || 0;
            $(this).find('.subtotal').text((cantidad * precio).toFixed(2));
        });
        actualizarTotalGeneral();
    }

    function actualizarTotalGeneral() {
        let total = 0;
        $('#resultados tbody tr').each(function () {
            let subtotal = parseFloat($(this).find('.subtotal').text()) || 0;
            total += subtotal;
        });
        $('#total_general').text(total.toFixed(2));
    }

    //carga los items del modal cuando se abre
    $('#myModal').on('shown.coreui.modal', function () {
        $('#x').val('');
        load(1, false);
        $('#x').focus();
    });

    //lo mismo que arriba pero 2 
    $('#myModalProductos').on('shown.coreui.modal', function () {
        $('#xProductos').val('');
        loadProductos(1, false);
        $('#xProductos').focus();
    });

    // --- FUNCION GENÉRICA ---
    function actualizarEstado(act) {
        let codigo = $('input[name="codigo"]').val();

        $.post("./modules/orden_c/proses.php", { act: act, codigo: codigo }, function (response) {
            console.log("Respuesta:", response);

            if (response.success) {
                alert(response.message);
                setTimeout(function () { location.reload(); }, 500);
            } else {
                alert("Error: " + response.message);
            }
        }, "json").fail(function () {
            alert("Error en la solicitud AJAX.");
        });
    }

    $(document).on('click', '.agregar_producto', function () {
        if ($('#resultados table').data('tipo') === 'presupuesto') {
            alert("Ya hay un pedido cargado. No se pueden agregar productos generales.");
            return; // Salir de la función sin agregar
        }

        if ($('#resultados table').length === 0) {
            generarTabla('general'); // genero tabla si no existe
        }

        let $fila = $(this).closest('tr');
        let cod_producto = $fila.data('cod');
        let descripcion = $fila.find('td:nth-child(4)').text();
        let cantidad = parseFloat($fila.find('.cantidad_input').val()) || 1;
        let precio_unitario = parseFloat($fila.find('.precio_input').val()) || 0;
        let impuesto = $fila.data('impuesto') || null;
        console.log('Cantidad:', cantidad, 'Precio unitario:', precio_unitario);


        // Validaciones
        if (cantidad <= 0) {
            alert("La cantidad debe ser mayor a cero.");
            return;
        }
        if (precio_unitario <= 0) {
            alert("El precio unitario debe ser mayor a cero.");
            return;
        }
        // Evitar duplicados
        if (productos.some(p => p.codigo_producto == cod_producto)) {
            alert("Este producto ya fue agregado.");
            return;
        }

        // Agregar al array
        productos.push({
            codigo_producto: cod_producto,
            cantidad: cantidad,
            precio_unitario: precio_unitario,
            impuesto: impuesto,
            descripcion: descripcion
        });

        // Agregar a la tabla principal
        let subtotal = (cantidad * precio_unitario).toFixed(2);
        console.log('Subtotal calculado:', subtotal);
        let filaTabla = `<tr data-cod="${cod_producto}">
            <td>${descripcion}</td>
            <td class="cantidad">${cantidad}</td>
            <td class="precio_unit">${precio_unitario}</td>
            <td class="subtotal">${subtotal}</td>
            <td><button type="button" class="btn btn-sm btn-danger btnEliminar"><i class="cil-trash"></i></button></td>
        </tr>`;

        $('#resultados tbody').append(filaTabla);



        // Actualizar subtotales
        actualizarSubtotalesGeneral();
    });

    // Evento eliminar
    $('#resultados').on('click', '.btnEliminar', function () {
        let $tr = $(this).closest('tr');
        let cod_producto = $tr.data('cod');
        $tr.remove();
        productos = productos.filter(p => p.codigo_producto != cod_producto);

        if ($('#resultados tbody tr').length === 0) {
            $('#resultados').html(''); // limpio la tabla completamente
        } else {
            actualizarSubtotalesGeneral();
            actualizarTotalGeneral();
        }
    });

    // --- FUNCION PARA MANEJAR BOTONES SEGUN ESTADO ---
    function manejarBotones(estado) {
        switch (estado) {
            case 'BORRADOR':
                $('#btnGuardar').prop('disabled', false);
                $('#btnConfirmar, #btnAnular').prop('disabled', true);
                break;
            case 'PENDIENTE':
                $('#btnGuardar').prop('disabled', true);
                $('#btnConfirmar').prop('disabled', false);
                $('#btnAnular').prop('disabled', true);
                break;
            case 'APROBADO':
            case 'CONFIRMADO':
                $('#btnGuardar, #btnConfirmar').prop('disabled', true);
                $('#btnAnular').prop('disabled', false);
                break;
            case 'ANULADO':
                $('#btnGuardar, #btnConfirmar, #btnModificar, #btnAnular').prop('disabled', true);
                break;
            default:
                $('#btnGuardar').prop('disabled', false);
                $('#btnConfirmar, #btnAnular').prop('disabled', true);
        }
    }

    // --- BOTONES ---
    $('#btnGuardar').off('click').on('click', function (e) {
        e.preventDefault();
        if (!validarFormulario()) return;

        $('#productos_json').val(JSON.stringify(productos));

        // Mostrar en consola qué se está enviando
        console.log("Productos JSON:", $('#productos_json').val());

        let formData = $('#formOrdenCompra').serialize();

        $.post("modules/orden_c/proses.php?act=insert", formData, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la orden: <strong>PENDIENTE</strong>');
                $('#estado_orden').val('PENDIENTE');
                manejarBotones('PENDIENTE');
                window.location.href = "?module=orden_c";
            } else {
                alert("Error al guardar: " + response.message);
            }
        }, "json").fail(function (xhr, status, error) {
            console.error("Error AJAX:", status, error, xhr.responseText);
            alert("Error en la solicitud AJAX.");
        });
    });

    $('#btnConfirmar').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();

        $.ajax({
            url: "modules/orden_c/proses.php",
            type: "POST",
            data: { act: 'confirm', codigo: codigo },
            dataType: "json",
            success: function (response) {
                console.log(response); // <- IMPORTANTE para depurar
                if (response.success) {
                    alert(response.message);
                    $('#estadoActual').html('Estado del orden: <strong>APROBADO</strong>');
                    $('#estado_orden').val('APROBADO');
                    manejarBotones('APROBADO');
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Error en la solicitud AJAX: " + error);
            }
        });
    });

    $('#btnAnular').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();
        if (!confirm("¿Estás seguro que deseas anular esta orden?")) return;

        $.post("modules/orden_c/proses.php", { act: 'cancel', codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la orden: <strong>ANULADO</strong>');
                $('#estado_orden').val('ANULADO');
                manejarBotones('ANULADO');
            } else {
                alert("Error: " + response.message);
            }
        }, "json");
    });

    function generarTabla(tipo) {
        let html = '';
        if (tipo === 'presupuesto') {
            html = `<table class="table table-striped" data-tipo="presupuesto">
                            <thead>
                                <tr>
                                    <th>Codigo Presupuesto</th>
                                    <th>Proveedor</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>`;
        } else if (tipo === 'general') {
            html = `<table class="table table-striped" data-tipo="general">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                    <td id="total_general" class="fw-bold">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
                </table>`;
        }

        $('#resultados').html(html);
    }

    // Toggle switch para cambiar entre presupuests y Productos generales
    $('#toggleProductos').on('change', function () {
        if ($(this).is(':checked')) {
            // Mostrar productos generales, ocultar presupuestos
            $('#btnAgregarPresupuestos').addClass('d-none');
            $('#proveedores').removeClass('d-none');
            $('#btnAgregarProductos').removeClass('d-none');
        } else {
            // Mostrar presupuestos, ocultar productos generales
            $('#btnAgregarPresupuestos').removeClass('d-none');
            $('#proveedores').addClass('d-none');
            $('#btnAgregarProductos').addClass('d-none');
        }
    });

    // --- INICIALIZAR BOTONES SEGUN ESTADO ---
    manejarBotones($('#estado_orden').val());
</script>