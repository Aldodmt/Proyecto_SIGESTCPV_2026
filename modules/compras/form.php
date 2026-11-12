<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once "config/database.php";
require_once __DIR__ . '/../../config/compras.php';
require_once __DIR__ . '/../../config/iva.php';

$id_usuario = $_SESSION['id_user'] ?? null;
if (!$id_usuario) {
    die("No se detectó usuario logueado.");
}

// Generar código único para orden de compra
$query_id = mysqli_query($mysqli, "SELECT MAX(cod_compra) as id FROM compra") or die("Error: " . mysqli_error($mysqli));
$data_id = mysqli_fetch_assoc($query_id);
$id_compra = ($data_id['id'] ?? 0) + 1;

$fecha = date("Y-m-d");
$hora = date("H:i:s");

//formulario para agregar
if ($_GET['form'] == 'add') {
    $codigo = $id_compra;
    $estado = 'BORRADOR';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=compra">Compra</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="bi bi-pencil-square me-1"></i> Agregar Factura de compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Facturacion</strong></div>
                <form id="formCompra" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="hora" value="<?= $hora ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Orden de compra</label>
                            <div class="col-md-10">
                                <button type="button" class="btn btn-info" id="btnAgregarOrden" data-bs-toggle="modal"
                                    data-bs-target="#myModal">
                                    <i class="bi bi-plus-lg"></i> Seleccionar Orden
                                </button>
                                <small class="text-muted ms-2">Es lo primero que se hace: elige una orden APROBADA que todavía no tenga una compra vigente. El proveedor y los productos se toman de la orden.</small>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="proveedor" name="proveedor" readonly
                                    placeholder="Se completa al elegir la orden">
                            </div>
                            <label class="col-md-2">RUC</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="ruc" id="ruc" maxlength="10"
                                    placeholder="00000001-1" required readonly>
                            </div>
                        </div>
                        <br>
                        <!-- Tabla de presupuestos asociados -->
                        <div id="resultados" class="mt-3"></div>
                        <input type="hidden" name="productos_json" id="productos_json">
                        <input type="hidden" id="id_orden_compra" name="id_orden_compra">
                        <input type="hidden" id="cod_proveedor" name="cod_proveedor">
                        <p class="text-muted mb-2" id="avisoFactura"><i class="bi bi-arrow-up-circle"></i> Selecciona primero la orden de compra: se completa el proveedor y se habilitan los datos de la factura.</p>
                        <fieldset id="datosFactura" disabled>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Nro. Factura</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="nro_factura" id="nro_factura" maxlength="15"
                                    placeholder="001-001-0000001" required>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fac_emi" value="" required>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Tipo de Factura</label>
                            <div class="col-md-2">
                                <select class="form-select" name="tipo_fac" id="tipo_fac" required>
                                    <option value="" disabled selected>Seleccionar Tipo de Factura</option>
                                    <option value="IMPRESA">-- Impresa --</option>
                                    <option value="ELECTRONICA">-- Electronica --</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Nro. timbrado</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="nro_timbrado" id="nro_timbrado" maxlength="8"
                                    placeholder="12345678" required>
                            </div>

                            <label class="col-md-2 col-form-label" id="labelVencimiento">Fecha de vencimiento de
                                timbrado</label>
                            <div class="col-md-2" id="campoVencimiento">
                                <input type="date" class="form-control" name="timb_venci">
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Con o Sin Nota de Remision</label>
                            <div class="col-md-2">
                                <select class="form-select" name="remision" required>
                                    <option value="" disabled selected>Seleccionar Condicion</option>
                                    <option value="CON">-- Con Remision --</option>
                                    <option value="SIN">-- Sin Remision --</option>
                                </select>
                            </div>
                            <label class="col-md-2 col-form-label">Condicion</label>
                            <div class="col-md-2">
                                <select class="form-select" name="condicion_c" required>
                                    <option value="" disabled selected>Seleccionar Condicion</option>
                                    <option value="CONTADO">-- Contado --</option>
                                    <option value="CREDITO">-- Credito --</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3 row" id="cuotas_div" style="display:none;">
                            <label class="col-md-2 col-form-label">Cantidad de Cuotas</label>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="cant_cuotas" id="cant_cuotas" min="1"
                                    maxlength="2" value="1">
                            </div>
                            <label class="col-md-2 col-form-label">Intervalo</label>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="id_intervalo" id="id_intervalo" min="1"
                                    maxlength="2" value="1">
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la compra: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_compra" name="estado" value="<?= $estado ?>">

                        </fieldset>

                    </div>

                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            <button type="button" id="btnConfirmar" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Confirmar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="bi bi-x-lg"></i> Anular
                            </button>
                            <a href="?module=compra" class="btn btn-secondary">
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
if ($_GET['form'] == 'detalle') {
    $cod_compra = intval($_GET['cod_compra']);

    // Cabecera de la compra (los productos se consultan aparte, más abajo)
    $stmt = $mysqli->prepare("
        SELECT com.*, prov.razon_social, prov.ruc
        FROM compra com
        JOIN proveedor prov ON com.cod_proveedor = prov.cod_proveedor
        WHERE com.cod_compra = ?
    ");
    $stmt->bind_param('i', $cod_compra);
    $stmt->execute();
    $compra = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$compra) {
        echo "<div class='alert alert-warning'>La compra solicitada no existe. <a href='?module=compra'>Volver</a></div>";
        return;
    }

    $codigo = $compra['cod_compra'];
    $fecha_e = $compra['fecha'];
    $hora = $compra['hora'];
    $estado = $compra['estado'];
    $nro_fact = $compra['fac_numero'];
    $fac_emi = $compra['fac_emision'];
    $tipo_fac = $compra['tipo_factura'];
    $total_compra = $compra['total_compra'];
    $com_condicion = $compra['com_condicion'];
    $tim_num = $compra['timbrado_nro'];
    $timb_venci = $compra['timb_fecha_venci'];
    $cod_proveedor = $compra['cod_proveedor'];
    $razon_social = $compra['razon_social'];
    $ruc = $compra['ruc'];
    ?>

    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=compra">Compra</a></li>
                        <li class="breadcrumb-item active">Detalles de la Compra</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="bi bi-pencil-square me-1"></i> Detalles de la Compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de la Compra</strong></div>
                <form id="formCompra" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name='codigo' value="<?= $codigo ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" value="<?= $fecha_e ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $hora ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Nro. Factura</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="nro_factura" value="<?= $nro_fact ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Emisión</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $fac_emi ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Tipo de Factura</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $tipo_fac ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Nro. timbrado</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="nro_timbrado" value="<?= $tim_num ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label" id="labelVencimiento">Fecha de vencimiento de
                                timbrado</label>
                            <div class="col-md-2" id="campoVencimiento">
                                <input type="text" class="form-control"
                                    value="<?= $tipo_fac != 'ELECTRONICA' ? $timb_venci : '' ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="row mb-3 row">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="proveedor" value="<?= $razon_social ?>"
                                    readonly>
                            </div>
                            <label class="col-md-2 col-form-label">RUC</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="ruc" id="ruc" value="<?= $ruc ?>"
                                    maxlength="10" placeholder="00000001-1" required readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Condición</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $com_condicion ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <!-- Todavia no voy a hacer pero voy  ahacer 
                        <div class="row mb-3 row" id="cuotas_div" style="display:none;">
                            <label class="col-md-2 col-form-label">Cantidad de Cuotas</label>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="cant_cuotas" id="cant_cuotas" min="1"
                                    value="">
                            </div>
                            <label class="col-md-2 col-form-label">Intervalo</label>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="id_intervalo" id="id_intervalo" min="1"
                                    value="1">
                            </div>
                        </div> -->

                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la orden de compra: <strong><?= strtoupper($estado) ?></strong>
                        </div>
                        <input type="hidden" id="estado_compra" name="estado" value="<?= strtoupper($estado) ?>">
                        <div id="resultados" class="mt-3">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Código Producto</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>IVA</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total = 0;
                                    $sql_detalle = mysqli_query($mysqli, "
                                        SELECT dc.cod_producto, pro.p_descrip, dc.cantidad, dc.precio, dc.tipo_iva
                                        FROM detalle_compra dc
                                        JOIN producto pro ON dc.cod_producto = pro.cod_producto
                                        WHERE dc.cod_compra = $cod_compra
                                    ");
                                    while ($det = mysqli_fetch_assoc($sql_detalle)) {
                                        $subtotal = $det['cantidad'] * $det['precio'];
                                        echo "<tr>
                                                <td>{$det['cod_producto']}</td>
                                                <td>{$det['p_descrip']}</td>
                                                <td>{$det['cantidad']}</td>
                                                <td>{$det['precio']}</td>
                                                <td>{$det['tipo_iva']}</td>
                                                <td>{$subtotal}</td>
                                            </tr>";
                                        $total += $subtotal;
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end align-items-center mt-2">
                                <label class="me-2 mb-0"><strong>Total:</strong></label>
                                <input type="text" class="form-control" value="<?= $total_compra ?>" readonly
                                    style="width: 120px;">
                            </div>
                            <?php iva_render_bloque(iva_filas((int) $codigo)); ?>
                        </div>
                    </div>
                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            <button type="button" id="btnConfirmar" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Confirmar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="bi bi-x-lg"></i> Anular
                            </button>
                            <a href="?module=compra" class="btn btn-secondary">
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
?>


<!-- Modal Buscar Pedido -->
<div class="modal" id="myModal" tabindex="-1" aria-labelledby="myModallabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModallabel">Buscar Ordenes de compra</h5>
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

<?= flujo_script_botones('compra') ?>
<script>
    // La compra se arma SIEMPRE desde una orden de compra aprobada: proveedor, productos, cantidades, precios e IVA
    // los lee el servidor de la base. Del navegador solo viajan los datos de la factura y el código de la orden.
    function load(page, mostrarLoader = true) {
        if (mostrarLoader) $('#loader').show();
        $.get('./ajax/productos_compra.php', { action: 'ajax', page: page, x: $('#x').val() || '' })
            .done(function (data) { $('.outer_div').html(data); })
            .fail(function (xhr, status, error) { alert("Error en la solicitud AJAX: " + error); })
            .always(function () { $('#loader').hide(); });
    }

    //en caso de que la factura sea electronica, se oculta la fecha de vencimiento del timbrado
    $('#tipo_fac').change(function () {
        if ($(this).val() === 'ELECTRONICA') {
            $('#campoVencimiento').hide();
            $('#labelVencimiento').hide();
        } else {
            $('#campoVencimiento').show();
            $('#labelVencimiento').show();
        }
    });

    // Formatear RUC: 00000001-1
    function formatearRUC(ruc) {
        if (!ruc) return '';
        ruc = String(ruc).replace(/\D/g, '');
        if (ruc.length === 9) {
            return ruc.slice(0, 8) + '-' + ruc.slice(8);
        }
        return ruc;
    }

    // "Cantidad de cuotas" e "Intervalo": solo números, máximo 2 dígitos
    $('#cant_cuotas, #id_intervalo').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
    });

    $('select[name="condicion_c"]').change(function () {
        if ($(this).val() === 'CREDITO') {
            $('#cuotas_div').show();
        } else {
            $('#cuotas_div').hide();
            $('#cant_cuotas').val(1);
            $('#id_intervalo').val(1);
        }
    });

    // Formato Nro Factura: 001-001-0000001
    $('#nro_factura').on('input', function () {
        let val = this.value.replace(/\D/g, '').slice(0, 13);
        let part1 = val.slice(0, 3);
        let part2 = val.slice(3, 6);
        let part3 = val.slice(6, 13);
        this.value = part1 + (part2 ? '-' + part2 : '') + (part3 ? '-' + part3 : '');
    });

    // Formato Nro Timbrado: solo 8 dígitos
    $('#nro_timbrado').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });

    function seleccionarOrden(id_orden) {
        if ($('#resultados tbody tr').length > 0) {
            alert("Ya hay una orden cargada. Debes quitarla antes de seleccionar otra.");
            return;
        }
        generarTabla();
        // El detalle se consulta recién ahora; el servidor vuelve a validar que la orden siga APROBADA y libre
        $.post("ajax/agregar_compra.php", { id_orden_comp: id_orden }, function (data) {
            $('#resultados tbody').html(data);
            if ($('#resultados tbody tr[data-error]').length || typeof datosProveedor === 'undefined') {
                $('#id_orden_compra').val('');
                $('#datosFactura').prop('disabled', true);
                $('#avisoFactura').show();
                return;
            }
            $('#id_orden_compra').val(id_orden);
            $('#datosFactura').prop('disabled', false);   // recién ahora se puede completar la factura
            $('#avisoFactura').hide();
            $("input[name='cod_proveedor']").val(datosProveedor.cod_proveedor);
            $('#proveedor').val(datosProveedor.razon_social);
            $('#ruc').val(formatearRUC(datosProveedor.ruc));
            let total = 0;
            $('#resultados tbody tr[data-cod]').each(function () {
                total += parseFloat($(this).find('.subtotal').data('valor')) || 0;
            });
            $('#total_general').text(total.toFixed(2));
        }, "html");
        cerrarModal();
    }

    function cerrarModal() {
        let modalEl = document.getElementById('myModal');
        let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', '');
            modalEl.removeEventListener('hidden.bs.modal', handler);
        });
        modal.hide();
    }

    function eliminarOrden(id_orden_comp) {
        $('#resultados').html('');
        $('#id_orden_compra').val('');
        $('#datosFactura').prop('disabled', true);
        $('#avisoFactura').show();
        $("input[name='cod_proveedor']").val('');
        $('#proveedor').val('');
        $('#ruc').val('');
    }

    // Carga la primera página cuando se abre la ventana
    $('#myModal').on('shown.bs.modal', function () {
        $('#x').val('');
        load(1, false);
        $('#x').focus();
    });

    function generarTabla() {
        $('#resultados').html(`<table class="table table-striped" data-tipo="orden">
            <thead>
                <tr>
                    <th>Codigo Orden</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Tipo Impuesto</th>
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

    // Acciones de estado de la compra ya guardada. El servidor revalida el estado; luego se recarga
    // para mostrar el estado real y los botones que le corresponden.
    function accionCompra(act) {
        let codigo = $('input[name="codigo"]').val();
        $.post("modules/compras/proses.php", { act: act, codigo: codigo }, function (response) {
            alert(response.message);
            location.reload();
        }, "json").fail(function (xhr, status, error) {
            console.error("Error AJAX:", status, error, xhr.responseText);
            alert("Error en la solicitud AJAX.");
        });
    }

    $(document).ready(function () {
        $('#tipo_fac').trigger('change');
        manejarBotones($('#estado_compra').val());

        $('#btnGuardar').off('click').on('click', function (e) {
            e.preventDefault();
            if (!$('#id_orden_compra').val()) {
                alert("Debes seleccionar una orden de compra aprobada.");
                return;
            }
            $.post("modules/compras/proses.php?act=insert", $('#formCompra').serialize(), function (response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = "?module=compra";
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
            if (!confirm("¿Deseas confirmar esta compra? Se actualizará el stock.")) return;
            accionCompra('confirm');
        });

        $('#btnAnular').off('click').on('click', function (e) {
            e.preventDefault();
            if (!confirm("¿Estás seguro que deseas anular esta compra?")) return;
            accionCompra('cancel');
        });
    });
</script>
