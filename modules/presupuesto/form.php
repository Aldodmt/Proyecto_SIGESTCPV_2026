<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";

$id_usuario = $_SESSION['id_user'] ?? null;
if (!$id_usuario) {
    die("No se detectó usuario logueado.");
}

// Generar código único para presupuesto
$query_id = mysqli_query($mysqli, "SELECT MAX(id_presupuesto) as id FROM presupuesto") or die("Error: " . mysqli_error($mysqli));
$data_id = mysqli_fetch_assoc($query_id);
$id_presupuesto = ($data_id['id'] ?? 0) + 1;

$fecha_e = date("Y-m-d");
$fecha_v = date("Y-m-d");
$estado = 'PENDIENTE'; // Estado inicial

//formulario para agregar
if ($_GET['form'] == 'add') {
    $codigo = $id_presupuesto;
    $fecha_e = date("Y-m-d");
    $fecha_v = date("Y-m-d");
    $estado = 'BORRADOR';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=presupuesto">Presupuestos</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>
            <h1><i class="bi bi-pencil-square me-1"></i> Agregar Presupuesto</h1>
            <div class="card">
                <div class="card-header"><strong>Formulario de Presupuestos</strong></div>
                <form id="formPresupuesto" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_e" value="<?= $fecha_e ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Vencimiento</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_v" value="<?= $fecha_v ?>" required>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado del presupuesto: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_presupuesto" name="estado" value="<?= $estado ?>">

                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-4">
                                <select class="form-select" name="codigo_proveedor" required>
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

                        <div class="row mb-3 row align-items-center">
                            <label class="col-md-2 col-form-label">Modo de Presupuesto</label>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="toggleProductos" />
                                    <label class="form-check-label" for="toggleProductos">
                                        Presupuestar productos sin pedido
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-10">
                                <!-- Botón de pedidos -->
                                <button type="button" class="btn btn-info" id="btnAgregarPedidos" data-bs-toggle="modal"
                                    data-bs-target="#myModal">
                                    <i class="bi bi-plus-lg"></i> Agregar Pedidos
                                </button>

                                <!-- Botón de productos generales -->
                                <button type="button" class="btn btn-info d-none" id="btnAgregarProductos"
                                    data-bs-toggle="modal" data-bs-target="#myModalProductos">
                                    <i class="bi bi-plus-lg"></i> Agregar Productos
                                </button>
                            </div>
                        </div>

                        <div id="resultados" class="mt-3"></div>
                        <input type="hidden" id="productos_json" name="productos_json">
                        <input type="hidden" id="id_pedido" name="id_pedido">
                    </div>

                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar"><i class="bi bi-save"></i>
                                Guardar</button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar"><i class="bi bi-check-lg"></i>
                                Aprobar</button>
                            <button type="button" class="btn btn-danger" id="btnAnular"><i class="bi bi-x-lg"></i>
                                Anular</button>
                            <a href="?module=presupuesto" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>
                                Salir</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php }
if ($_GET['form'] == 'detalle') {
    $id_presupuesto = isset($_GET['id_presupuesto']) ? intval($_GET['id_presupuesto']) : 0;

    if ($id_presupuesto <= 0) {
        echo "<div class='alert alert-danger'>ID de presupuesto no válido.</div>";
        exit;
    }

    // Consulta la cabecera del presupuesto
    $query = mysqli_query($mysqli, "
        SELECT p.id_presupuesto, p.fecha_presu, p.fecha_vencimiento, p.estado, pr.razon_social
        FROM presupuesto p
        JOIN proveedor pr ON p.cod_proveedor = pr.cod_proveedor
        WHERE p.id_presupuesto = $id_presupuesto
    ");
    $data = mysqli_fetch_assoc($query);

    $codigo = $data['id_presupuesto'];
    $fecha_e = $data['fecha_presu'];
    $fecha_v = $data['fecha_vencimiento'];
    $estado = $data['estado'];
    $proveedor = $data['razon_social'];
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=presupuesto">Presupuestos</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </div>
            </div>
            <div class="col-sm-6">
                <h1><i class="bi bi-eye me-1"></i> Detalles del Presupuesto</h1>
            </div>
            <div class="card">
                <div class="card-header"><strong>Formulario de Presupuesto</strong></div>
                <form method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_emision" value="<?= $fecha_e ?>"
                                    readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Vencimiento</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_vencimiento" value="<?= $fecha_v ?>"
                                    readonly>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado del presupuesto: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_presupuesto" name="estado" value="<?= $estado ?>">

                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="<?= $proveedor ?>" readonly>
                            </div>
                        </div>

                        <!-- Tabla de detalle -->
                        <?php
                        $query_detalle = mysqli_query($mysqli, "
                            SELECT dp.cod_producto, p.p_descrip, dp.cantidad, dp.precio_unit
                            FROM det_presu dp
                            JOIN producto p ON dp.cod_producto = p.cod_producto
                            WHERE dp.id_presupuesto = $codigo
                        ");

                        if (mysqli_num_rows($query_detalle) > 0) {
                            echo '<table class="table table-striped mt-3">';
                            echo '<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr></thead><tbody>';
                            while ($row = mysqli_fetch_assoc($query_detalle)) {
                                $subtotal = $row['cantidad'] * $row['precio_unit'];
                                echo "<tr>
                                        <td>{$row['p_descrip']}</td>
                                        <td>{$row['cantidad']}</td>
                                        <td>{$row['precio_unit']}</td>
                                        <td>{$subtotal}</td>
                                      </tr>";
                            }
                            echo '</tbody></table>';
                        } else {
                            echo "<div class='alert alert-warning mt-3'>No hay productos en este presupuesto.</div>";
                        }
                        ?>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar" disabled>
                                <i class="bi bi-check-lg"></i> Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="bi bi-x-lg"></i> Anular
                            </button>
                            <a href="?module=presupuesto" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Salir
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>


<!-- Modal Buscar Pedido -->
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
                            <input type="text" class="form-control" id="x">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" onclick="load(1, true)">
                                <i class="bi bi-search"></i> Buscar
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="xProductos" placeholder="Buscar productos...">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" onclick="loadProductos(1, true)">
                                <i class="bi bi-search"></i> Buscar
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    var productos = [];
    var id_pedido = null;

    function validarFormulario() {
        let proveedor = $('select[name="codigo_proveedor"]').val();
        if (!proveedor) {
            alert("Debes seleccionar un proveedor.");
            return false;
        }

        if (productos.length === 0) {
            alert("Debes agregar al menos un producto.");
            return false;
        }

        let preciosInvalidos = productos.some(p => p.precio_unitario <= 0);
        if (preciosInvalidos) {
            alert("Todos los productos deben tener un precio unitario mayor a 0.");
            return false;
        }

        return true;
    }

    function load(page, mostrarLoader = true) {
        var x = $("#x").val() || '';
        if (mostrarLoader) $("#loader").fadeIn('slow');
        $.ajax({
            url: './ajax/productos_presu.php?action=ajax&page=' + page + '&x=' + x,
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

    function seleccionarPedido(id_pedido) {
        // Validar si ya hay productos cargados
        if ($('#resultados tbody tr').length > 0) {
            alert("Ya hay productos cargados. Debes eliminar los productos actuales antes de seleccionar otro pedido.");
            return; // Salir de la función
        }
        // Generar la tabla (solo si no existe)
        if ($('#resultados table').length === 0) {
            generarTabla('pedido');
        } else {
            // Limpiar solo el tbody
            $('#resultados tbody').html('');
        }

        $.post("ajax/agregar_presu.php", { id_pedido: id_pedido }, function (data) {
            $("#resultados tbody").html(data);

            $('#id_pedido').val(id_pedido);
            actualizarProductos();
            $('.precio_unit').off('input').on('input', actualizarSubtotales);
        });

        bootstrap.Modal.getInstance(document.getElementById('myModal')).hide();
    }


    function eliminarPedido(id_pedido) {
        // Eliminar filas del pedido
        $('tr[data-pedido="' + id_pedido + '"]').remove();
        actualizarProductos();

        // Verificar si quedó alguna fila de pedido
        if ($('#resultados tbody tr[data-pedido]').length === 0) {
            // Reiniciar tabla sin tipo definido
            $('#resultados').html(''); // limpio toda la tabla
            $('#id_pedido').val('');   // reseteo el id del pedido
        }
    }

    function actualizarProductos() {
        productos = [];
        $('#resultados tbody tr').each(function () {
            var cod = $(this).data('cod') || 0;
            var cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            var precio = parseFloat($(this).find('.precio_unit').val()) || 0;
            productos.push({ codigo_producto: cod, cantidad: cantidad, precio_unitario: precio });
        });
    }

    //actualizar subtotal para presupuesto con pedido
    function actualizarSubtotales() {
        $('#resultados tbody tr').each(function () {
            let cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            let precio = parseFloat($(this).find('.precio_unit').text()) || 0;
            $(this).find('.subtotal').text((cantidad * precio).toFixed(2));
        });
        actualizarTotalGeneral();
    }
    //actualizar subtotal para presupuesto sin pedido
    function actualizarSubtotalesGeneral() {
        $('#resultados tbody tr').each(function () {
            let cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            let precio = parseFloat($(this).find('.precio_unit').text()) || 0;
            $(this).find('.subtotal').text((cantidad * precio).toFixed(2));
        });
        actualizarTotalGeneral();
    }
    //actualiza el total general 
    function actualizarTotalGeneral() {
        let total = 0;
        $('#resultados tbody tr').each(function () {
            let subtotal = parseFloat($(this).find('.subtotal').text()) || 0;
            total += subtotal;
        });
        $('#total_general').text(total.toFixed(2));
    }

    //carga los items del modal cuando se abre
    $('#myModal').on('shown.bs.modal', function () {
        $('#x').val('');
        load(1, false);
        $('#x').focus();
    });
    //lo mismo que arriba pero 2 
    $('#myModalProductos').on('shown.bs.modal', function () {
        $('#xProductos').val('');
        loadProductos(1, false);
        $('#xProductos').focus();
    });

    // --- FUNCION GENÉRICA ---
    function actualizarEstado(act) {
        let codigo = $('input[name="codigo"]').val();

        $.post("./modules/presupuesto/proses.php", { act: act, codigo: codigo }, function (response) {
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
        if ($('#resultados table').data('tipo') === 'pedido') {
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
            <td><button type="button" class="btn btn-sm btn-danger btnEliminar"><i class="bi bi-trash"></i></button></td>
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
        let formData = $('#formPresupuesto').serialize();

        $.post("modules/presupuesto/proses.php?act=insert", formData, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado del presupuesto: <strong>PENDIENTE</strong>');
                $('#estado_presupuesto').val('PENDIENTE');
                manejarBotones('PENDIENTE');
                window.location.href = "?module=presupuesto";
            } else {
                alert("Error al guardar: " + response.message);
            }
        }, "json").fail(function () {
            alert("Error en la solicitud AJAX.");
        });
    });

    $('#btnConfirmar').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();

        $.ajax({
            url: "modules/presupuesto/proses.php",
            type: "POST",
            data: { act: 'confirm', codigo: codigo },
            dataType: "json",
            success: function (response) {
                console.log(response); // <- IMPORTANTE para depurar
                if (response.success) {
                    alert(response.message);
                    $('#estadoActual').html('Estado del presupuesto: <strong>APROBADO</strong>');
                    $('#estado_presupuesto').val('APROBADO');
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
        if (!confirm("¿Estás seguro que deseas anular este presupuesto?")) return;

        $.post("modules/presupuesto/proses.php", { act: 'cancel', codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado del presupuesto: <strong>ANULADO</strong>');
                $('#estado_presupuesto').val('ANULADO');
                manejarBotones('ANULADO');
            } else {
                alert("Error: " + response.message);
            }
        }, "json");
    });

    function generarTabla(tipo) {
        let html = '';
        if (tipo === 'pedido') {
            html = `<table class="table table-striped" data-tipo="pedido">
                    <thead>
                        <tr>
                            <th>Codigo Pedido</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tfoot>
                            <tr>
                            <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                            <td id="total_general" class="fw-bold">0.00</td>
                            <td></td>
                            </tr>
                        </tfoot>
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

    // Toggle switch para cambiar entre Pedidos y Productos generales
    $('#toggleProductos').on('change', function () {
        if ($(this).is(':checked')) {
            // Mostrar productos generales, ocultar pedidos
            $('#btnAgregarPedidos').addClass('d-none');
            $('#btnAgregarProductos').removeClass('d-none');
        } else {
            // Mostrar pedidos, ocultar productos generales
            $('#btnAgregarPedidos').removeClass('d-none');
            $('#btnAgregarProductos').addClass('d-none');
        }
    });

    // --- INICIALIZAR BOTONES SEGUN ESTADO ---
    manejarBotones($('#estado_presupuesto').val());
</script>