<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";
require_once __DIR__ . '/../../config/compras.php';

$id_usuario = $_SESSION['id_user'] ?? null;
if (!$id_usuario) {
    die("No se detectó usuario logueado.");
}

$form = $_GET['form'] ?? '';

// La orden de compra se genera SIEMPRE a partir de un presupuesto aprobado (flujo pedido -> presupuesto -> orden -> compra).
if ($form === 'add') {
    // Vista previa del código: el definitivo lo reserva el servidor al guardar (flujo_siguiente_codigo)
    $codigo = (int) mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COALESCE(MAX(id_orden_comp), 0) + 1 AS id FROM orden_compra"))['id'];
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");
    $estado = 'BORRADOR';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=orden_c">Orden de Compra</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="bi bi-pencil-square me-1"></i> Agregar Orden de Compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Orden de Compra</strong></div>
                <form id="formOrdenCompra" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="row mb-3 row">
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

                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Presupuesto</label>
                            <div class="col-md-10">
                                <button type="button" class="btn btn-info" id="btnAgregarPresupuestos"
                                    data-bs-toggle="modal" data-bs-target="#myModal">
                                    <i class="bi bi-plus-lg"></i> Seleccionar Presupuesto
                                </button>
                                <small class="text-muted ms-2">Solo presupuestos APROBADOS que todavía no tengan una orden vigente. El proveedor y los productos se toman del presupuesto.</small>
                            </div>
                        </div>

                        <!-- Detalle del presupuesto elegido -->
                        <div id="resultados" class="mt-3"></div>
                        <input type="hidden" id="id_presupuesto" name="id_presupuesto">
                    </div>

                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar">
                                <i class="bi bi-check-lg"></i> Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular">
                                <i class="bi bi-x-lg"></i> Anular
                            </button>
                            <a href="?module=orden_c" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Salir
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
if ($form === 'detalle') {
    $id_orden_c = intval($_GET['id_orden_comp'] ?? 0);

    // Cabecera de la orden (el detalle de productos se consulta aparte, más abajo)
    $stmt = $mysqli->prepare("
        SELECT o.*, pr.razon_social, pr.ruc
        FROM orden_compra o
        LEFT JOIN proveedor pr ON o.cod_proveedor = pr.cod_proveedor
        WHERE o.id_orden_comp = ?
    ");
    $stmt->bind_param('i', $id_orden_c);
    $stmt->execute();
    $orden = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$orden) {
        echo "<div class='alert alert-warning'>La orden de compra solicitada no existe. <a href='?module=orden_c'>Volver</a></div>";
        return;
    }

    $codigo = (int) $orden['id_orden_comp'];
    $fecha_e = $orden['fecha'];
    $hora = $orden['hora'];
    $estado = strtoupper($orden['estado']);
    $id_presupuesto = $orden['id_presupuesto'];
    $razon_social = $orden['razon_social'] ?? '';
    $ruc = $orden['ruc'] ?? '';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=orden_c">Orden de Compra</a></li>
                        <li class="breadcrumb-item active">Detalles de la Orden de Compra</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="bi bi-pencil-square me-1"></i> Detalles de la Orden de Compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Orden de Compra</strong></div>
                <form id="formOrdenCompra" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="fecha_E" value="<?= h($fecha_e) ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="hora" value="<?= h($hora) ?>" readonly>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la orden de compra: <strong><?= h($estado) ?></strong>
                        </div>
                        <input type="hidden" id="estado_orden" name="estado" value="<?= h($estado) ?>">

                        <!-- Proveedor y origen -->
                        <div class="row mb-3 row mt-3">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="<?= h($razon_social) ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">RUC</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= h($ruc) ?>" readonly>
                            </div>
                            <label class="col-md-1 col-form-label">Presup.</label>
                            <div class="col-md-1">
                                <input type="text" class="form-control" value="<?= h($id_presupuesto) ?>" readonly>
                            </div>
                        </div>

                        <!-- Tabla de Detalles de la Orden (recién ahora se consultan los productos) -->
                        <div id="resultados" class="mt-3">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Código Producto</th>
                                        <th>Descripción</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Precio Unitario</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    $detalle = flujo_detalle_orden($codigo);
                                    if ($detalle) {
                                        foreach ($detalle as $det) {
                                            $subtotal = $det['cantidad'] * $det['precio_unit'];
                                            $total += $subtotal;
                                            echo "<tr>
                                            <td>" . (int) $det['cod_producto'] . "</td>
                                            <td>" . h($det['p_descrip']) . "</td>
                                            <td class='text-end'>" . number_format($det['cantidad'], 2) . "</td>
                                            <td class='text-end'>" . number_format($det['precio_unit'], 2) . "</td>
                                            <td class='text-end'>" . number_format($subtotal, 2) . "</td>
                                        </tr>";
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
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar">
                                <i class="bi bi-check-lg"></i> Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular">
                                <i class="bi bi-x-lg"></i> Anular
                            </button>
                            <a href="?module=orden_c" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Salir
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
} ?>


<!-- Modal Buscar Presupuesto -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModallabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModallabel">Buscar Presupuestos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form onsubmit="load(1); return false;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="x" placeholder="Buscar por código o proveedor">
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

<?= flujo_script_botones('orden_c') ?>
<script>
    function load(page, mostrarLoader = true) {
        if (mostrarLoader) $('#loader').show();
        $.get('./ajax/productos_orden.php', { action: 'ajax', page: page, x: $('#x').val() || '' })
            .done(function (data) { $('.outer_div').html(data); })
            .fail(function (xhr, status, error) { alert("Error en la solicitud AJAX: " + error); })
            .always(function () { $('#loader').hide(); });
    }

    function generarTabla() {
        $('#resultados').html(`<table class="table table-striped" data-tipo="presupuesto">
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
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold">TOTAL:</td>
                    <td id="total_general" class="fw-bold">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>`);
    }

    function seleccionarPresupuesto(id_presupuesto) {
        if ($('#resultados tbody tr').length > 0) {
            alert("Ya hay un presupuesto cargado. Debes quitarlo antes de seleccionar otro.");
            return;
        }
        generarTabla();
        // El detalle se consulta recién ahora; el servidor vuelve a validar que siga APROBADO y libre
        $.post("ajax/agregar_orden.php", { id_presupuesto: id_presupuesto }, function (data) {
            $('#resultados tbody').html(data);
            if ($('#resultados tbody tr[data-error]').length) {
                $('#id_presupuesto').val('');
                return;
            }
            $('#id_presupuesto').val(id_presupuesto);
            let total = 0;
            $('#resultados tbody tr[data-cod]').each(function () {
                total += parseFloat($(this).find('.subtotal').data('valor')) || 0;
            });
            $('#total_general').text(total.toFixed(2));
        });
        bootstrap.Modal.getInstance(document.getElementById('myModal')).hide();
    }

    function eliminarPresupuesto(id_presupuesto) {
        $('#resultados').html('');
        $('#id_presupuesto').val('');
    }

    // Carga la primera página cuando se abre la ventana
    $('#myModal').on('shown.bs.modal', function () {
        $('#x').val('');
        load(1, false);
        $('#x').focus();
    });

    // Acciones de estado de la orden ya guardada. El servidor revalida el estado; luego se recarga
    // para mostrar el estado real y los botones que le corresponden.
    function accionOrden(act) {
        let codigo = $('input[name="codigo"]').val();
        $.post("modules/orden_c/proses.php", { act: act, codigo: codigo }, function (response) {
            alert(response.message);
            location.reload();
        }, "json").fail(function () {
            alert("Error en la solicitud AJAX.");
        });
    }

    $(document).ready(function () {
        manejarBotones($('#estado_orden').val());

        $('#btnGuardar').off('click').on('click', function (e) {
            e.preventDefault();
            if (!$('#id_presupuesto').val()) {
                alert("Debes seleccionar un presupuesto aprobado.");
                return;
            }
            // Solo viaja el código del presupuesto: proveedor, productos y precios los lee el servidor de la base
            $.post("modules/orden_c/proses.php?act=insert", { id_presupuesto: $('#id_presupuesto').val() }, function (response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = "?module=orden_c";
                } else {
                    alert("Error al guardar: " + response.message);
                }
            }, "json").fail(function (xhr, status, error) {
                console.error("Error AJAX:", status, error, xhr.responseText);
                alert("Error en la solicitud AJAX.");
            });
        });

        $('#btnConfirmar').off('click').on('click', function (e) {
            e.preventDefault();
            accionOrden('confirm');
        });

        $('#btnAnular').off('click').on('click', function (e) {
            e.preventDefault();
            if (!confirm("¿Estás seguro que deseas anular esta orden?")) return;
            accionOrden('cancel');
        });
    });
</script>
