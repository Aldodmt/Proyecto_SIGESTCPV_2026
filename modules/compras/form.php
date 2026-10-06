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
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=compra">Compra</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Agregar Factura de compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Facturacion</strong></div>
                <form id="formCompra" class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="form-group row">
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
                        <div class="form-group row">
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
                                <select class="form-control" name="tipo_fac" id="tipo_fac" required>
                                    <option value="" disabled selected>Seleccionar Tipo de Factura</option>
                                    <option value="IMPRESA">-- Impresa --</option>
                                    <option value="ELECTRONICA">-- Electronica --</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
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
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Seleccionar Proveedor</label>
                            <div class="col-md-2">
                                <select class="form-control" id="selectProveedor" name="cod_proveedor_manual" disabled>
                                    <option value="" disabled selected>Seleccionar proveedor</option>
                                    <?php
                                    $query_prov = mysqli_query($mysqli, "SELECT cod_proveedor, razon_social, ruc FROM proveedor ORDER BY razon_social ASC");
                                    while ($prov = mysqli_fetch_assoc($query_prov)) {
                                        echo "<option value='{$prov['cod_proveedor']}' data-ruc='{$prov['ruc']}'>{$prov['razon_social']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <label class="col-md-2">RUC</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="ruc" id="ruc" maxlength="10"
                                    placeholder="00000001-1" required readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Con o Sin Nota de Remision</label>
                            <div class="col-md-2">
                                <select class="form-control" name="remision" required>
                                    <option value="" disabled selected>Seleccionar Condicion</option>
                                    <option value="CON">-- Con Remision --</option>
                                    <option value="SIN">-- Sin Remision --</option>
                                </select>
                            </div>
                            <label class="col-md-2 col-form-label">Condicion</label>
                            <div class="col-md-2">
                                <select class="form-control" name="condicion_c" required>
                                    <option value="" disabled selected>Seleccionar Condicion</option>
                                    <option value="CONTADO">-- Contado --</option>
                                    <option value="CREDITO">-- Credito --</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row" id="cuotas_div" style="display:none;">
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

                        <!-- Se encarga de esconder los botones para cargar orden o producto general -->
                        <div class="form-group row align-items-center">
                            <label class="col-md-2 col-form-label">Modo de Compra</label>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="toggleProductos" />
                                    <label class="form-check-label" for="toggleProductos">
                                        Compra de productos sin orden
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10">
                                <!-- Botón de pedidos -->
                                <button type="button" class="btn btn-info" id="btnAgregarOrden" data-coreui-toggle="modal"
                                    data-coreui-target="#myModal">
                                    <i class="fa fa-plus"></i> Agregar Orden
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
                        <input type="hidden" id="id_orden_compra" name="id_orden_compra">
                        <input type="hidden" id="cod_proveedor" name="cod_proveedor">

                    </div>

                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" id="btnConfirmar" class="btn btn-primary">
                                <i class="fa-solid fa-check"></i> Confirmar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=compra" class="btn btn-secondary">
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
if ($_GET['form'] == 'detalle') {
    $cod_compra = intval($_GET['cod_compra']);

    $query_orden = mysqli_query($mysqli, "
        SELECT dc.cod_compra, pro.cod_producto, pro.p_descrip ,dc.precio, dc.cantidad, 
        dc.tipo_iva, prov.cod_proveedor, prov.razon_social, prov.ruc, com.fecha, 
        com.hora, com.id_user, com.estado, orde.id_orden_comp, com.fac_numero, com.fac_emision, 
        com.tipo_factura, com.total_compra, dc.exentas, dc.iva5, dc.iva10, com.com_condicion, 
        com.timbrado_nro,  com.timb_fecha_venci
        
        FROM detalle_compra dc

        JOIN producto pro ON  dc.cod_producto = pro.cod_producto

        JOIN compra com ON  dc.cod_compra = com.cod_compra

        LEFT JOIN orden_compra orde ON  com.id_orden_comp = orde.id_orden_comp

        JOIN proveedor prov ON  com.cod_proveedor = prov.cod_proveedor

        WHERE dc.cod_compra = $cod_compra
    ") or die("Error: " . mysqli_error($mysqli));

    $compra = mysqli_fetch_assoc($query_orden);

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
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=compra">Compra</a></li>
                        <li class="breadcrumb-item active">Detalles de la Compra</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Detalles de la Compra</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de la Compra</strong></div>
                <form id="formCompra" class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="form-group row">
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
                        <div class="form-group row">
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
                        <div class="form-group row">
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
                        <div class="form-group row">
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
                        <div class="form-group row" id="cuotas_div" style="display:none;">
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
                        </div>
                    </div>
                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" id="btnConfirmar" class="btn btn-primary">
                                <i class="fa-solid fa-check"></i> Confirmar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=compra" class="btn btn-secondary">
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
?>


<!-- Modal Buscar Pedido -->
<div class="modal" id="myModal" tabindex="-1" aria-labelledby="myModallabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModallabel">Buscar Ordenes de compra</h5>
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


<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- CoreUI JS -->
<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.2.2/dist/js/coreui.bundle.min.js"></script>

<!-- Select2 (opcional) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    var productos = [];
    var id_orden = null;

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
            url: './ajax/productos_compra.php?action=ajax&page=' + page + '&x=' + x,
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
    //en caso de que la factura sea electronica, se oculta la fecha de vencimiento del timbrado
    $('#tipo_fac').change(function () {
        let tipo = $(this).val();
        if (tipo === 'ELECTRONICA') {
            $('#campoVencimiento').hide(); //esconde el campo date
            $('#labelVencimiento').hide(); //esconde el labels
        } else {
            $('#campoVencimiento').show(); //esconde el campo date
            $('#labelVencimiento').show(); //esconde el label
        }
    });
    // asegura que el html este cargado y acciona bien el onchange del tipo de factura
    $(document).ready(function () {
        $('#tipo_fac').trigger('change');
    });

    // Formatear RUC: 00000001-1
    function formatearRUC(ruc) {
        if (!ruc) return '';
        ruc = String(ruc).replace(/\D/g, ''); // convertimos a string
        if (ruc.length === 9) {
            return ruc.slice(0, 8) + '-' + ruc.slice(8);
        }
        return ruc;
    }

    // Formatear timbrado a 8 dígitos con ceros delante si es necesario
    function formatearTimbrado(timb) {
        timb = timb.replace(/\D/g, '');
        return timb.padStart(8, '0');
    }

    // Script para "Cantidad de Cuotas"
    $('#cant_cuotas').on('input', function () {
        // Eliminar cualquier caracter que no sea número
        this.value = this.value.replace(/[^0-9]/g, '');
        // Limitar a 2 dígitos
        if (this.value.length > 2) {
            this.value = this.value.slice(0, 2);
        }
    });

    // Script para "Intervalo"
    $('#id_intervalo').on('input', function () {
        // Eliminar cualquier caracter que no sea número
        this.value = this.value.replace(/[^0-9]/g, '');
        // Limitar a 2 dígitos
        if (this.value.length > 2) {
            this.value = this.value.slice(0, 2);
        }
    });

    function seleccionarOrden(id_orden) {

        // Verificar si ya hay productos cargados
        if ($('#resultados tbody tr').length > 0) {
            alert("Ya hay productos cargados. Debes eliminar los productos actuales antes de seleccionar otra orden.");
            return;
        }

        // Crear la tabla si no existe
        if ($('#resultados table').length === 0) {
            generarTabla('orden');
        }

        // Limpiar tabla y productos antes de agregar nuevos
        productos = [];

        $.post("ajax/agregar_compra.php", { id_orden_comp: id_orden }, function (data) {
            $('#resultados tbody').append(data);
            $('#id_orden').val(id_orden);
            actualizarProductos(); // recalcula totales, etc.

            if (typeof datosProveedor !== 'undefined') {
                // Aplicar datos del proveedor
                $("#id_orden_compra").val(id_orden);
                $("input[name='cod_proveedor']").val(datosProveedor.cod_proveedor);
                $("input[name='proveedor']").val(datosProveedor.razon_social);
                $("input[name='ruc']").val(formatearRUC(datosProveedor.ruc));

                // --- NUEVO: actualizar el select ---
                let $select = $('#selectProveedor');
                $select.val(datosProveedor.cod_proveedor);
                $select.prop('disabled', true);

                // Guardar productos con tipo_iva 
                productos = datosProveedor.productos.map(p => ({
                    codigo_producto: p.cod_producto,
                    cantidad: p.cantidad,
                    precio_unitario: p.precio_unit,
                    tipo_iva: p.tipo_iva
                }));
            } else {
                productos = [];
            }

            // Asignar eventos onchange a los select de tipo_iva
            /*$('#resultados .tipo_iva').off('change').on('change', function () {
                let cod = $(this).closest('tr').data('cod');
                let tipo = $(this).val();
                let prod = productos.find(p => p.codigo_producto == cod);
                if (prod) prod.tipo_iva = tipo;
            });*/

            // Cerrar modal de forma segura después de cargar datos
            cerrarModalCoreUI();
        }, "html");
    }

    function cerrarModalCoreUI() {
        let modalEl = document.getElementById('myModal');
        let modal = coreui.Modal.getInstance(modalEl) || new coreui.Modal(modalEl);

        // Escuchar evento hidden para limpiar backdrop y scroll
        modalEl.addEventListener('hidden.coreui.modal', function handler() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', '');
            // Eliminar listener para no repetirlo
            modalEl.removeEventListener('hidden.coreui.modal', handler);
        });

        // Cerrar el modal usando la API oficial
        modal.hide();
    }


    function eliminarOrden(id_orden_comp) {
        // Eliminar todas las filas que pertenecen a esta orden
        $('tr[data-orden="' + id_orden_comp + '"]').remove();

        // Actualizar productos en memoria
        actualizarProductos();

        // Si ya no hay filas con data-orden, limpiamos toda la tabla
        if ($('#resultados tbody tr[data-orden]').length === 0) {
            $('#resultados').html(''); // Limpia la tabla completa
            $('#id_orden_comp').val(''); // Limpia el campo oculto de orden
        }
    }

    function actualizarProductos() {
        productos = [];
        $('#resultados tbody tr').each(function () {
            var cod = $(this).data('cod') || 0;
            var cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            var precio = parseFloat($(this).find('.precio_unit').text()) || 0;
            var tipo_iva = $(this).find('.tipo_iva').val() || 'EXENTA';
            productos.push({ codigo_producto: cod, cantidad: cantidad, precio_unitario: precio, tipo_iva: tipo_iva });
        });
    }

    //carga los items del modal cuando se abre
    $('#myModal').on('shown.coreui.modal', function () {
        console.log("Modal abierto");
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

        $.post("./modules/compras/proses.php", { act: act, codigo: codigo }, function (response) {
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
    //actualizar subtotal para orden sin presupuesto
    function actualizarSubtotalesGeneral() {
        $('#resultados tbody tr').each(function () {
            let fila = $(this);
            let cantidad = parseFloat(fila.find('.cantidad').data('valor')) || 0;
            let precio = parseFloat(fila.find('.precio_unit').data('valor')) || 0;
            let subtotal = cantidad * precio;

            fila.find('.subtotal').data('valor', subtotal); // actualizar valor real
            fila.find('.subtotal').text(subtotal.toFixed(2)); // mostrar bonito
        });
    }

    $(document).on('click', '.agregar_producto', function () {
        if ($('#resultados table').data('tipo') === 'orden') {
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
        let impuesto = $fila.data('impuesto') || 'EXENTA';

        // Validar tipo de IVA
        const tiposValidos = ['10%', '5%', 'EXENTA'];
        if (!tiposValidos.includes(impuesto.toUpperCase())) {
            impuesto = 'EXENTA';
        }
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
            tipo_iva: impuesto,
            descripcion: descripcion
        });

        // Agregar a la tabla principal
        let filaTabla = `<tr data-cod="${cod_producto}">
            <td>${descripcion}</td>
            <td class="cantidad" data-valor="${cantidad}">${cantidad.toFixed(2)}</td>
            <td class="precio_unit" data-valor="${precio_unitario}">${precio_unitario.toFixed(2)}</td>
            <td class="tipo_iva">${impuesto}</td>
            <td class="subtotal" data-valor="${(cantidad * precio_unitario).toFixed(2)}">${(cantidad * precio_unitario).toFixed(2)}</td>
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
        }
    });


    // --- FUNCION PARA MANEJAR BOTONES SEGUN ESTADO ---
    function manejarBotones(estado) {
        switch (estado) {
            case 'BORRADOR':
                $('#btnGuardar').prop('disabled', false);
                $('#btnConfirmar').prop('disabled', true);
                $('#btnAnular').prop('disabled', true);
                break;
            case 'PENDIENTE':
                $('#btnGuardar').prop('disabled', true);
                $('#btnConfirmar').prop('disabled', false);
                $('#btnAnular').prop('disabled', true);
                break;
            case 'ACTIVO':
                $('#btnGuardar').prop('disabled', true);
                $('#btnConfirmar').prop('disabled', true);
                $('#btnAnular').prop('disabled', false);
                break;
            case 'ANULADO':
                $('#btnGuardar, #btnAnular, #btnConfirmar').prop('disabled', true);
                break;
            default:
                $('#btnGuardar').prop('disabled', false);
                $('#btnConfirmar, #btnAnular').prop('disabled', true);
        }
    }

    $('select[name="condicion_c"]').change(function () {
        let cond = $(this).val();
        if (cond === 'CREDITO') {
            $('#cuotas_div').show();
        } else {
            $('#cuotas_div').hide();
            $('#cant_cuotas').val(1);
            $('#id_intervalo').val(1);
        }
    });

    // Formato Nro Factura: 001-001-0000001
    document.getElementById('nro_factura').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, ''); // quitar todo lo que no sea número
        if (val.length > 7) val = val.slice(0, 13); // limitar
        let part1 = val.slice(0, 3);
        let part2 = val.slice(3, 6);
        let part3 = val.slice(6, 13);
        e.target.value = part1 + (part2 ? '-' + part2 : '') + (part3 ? '-' + part3 : '');
    });

    // Formato Nro Timbrado: solo 8 dígitos
    document.getElementById('nro_timbrado').addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 8);
    });

    // Formato RUC: 00000001-1
    document.getElementById('ruc').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '').slice(0, 9); // 8 dígitos + 1 dígito final
        let part1 = val.slice(0, 8);
        let part2 = val.slice(8, 9);
        e.target.value = part1 + (part2 ? '-' + part2 : '');
    });

    // --- BOTONES ---
    $('#btnGuardar').off('click').on('click', function (e) {
        e.preventDefault();
        if (!validarFormulario()) return;

        $('#productos_json').val(JSON.stringify(productos));

        // Mostrar en consola qué se está enviando
        console.log("Productos JSON:", $('#productos_json').val());

        let formData = $('#formCompra').serialize();

        $.post("modules/compras/proses.php?act=insert", formData, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la compra: <strong>PENDIENTE</strong>');
                $('#estado_compra').val('PENDIENTE');
                manejarBotones('PENDIENTE');
                window.location.href = "?module=compra";
            } else {
                alert("Error al guardar: " + response.message);
            }
        }, "json").fail(function (xhr, status, error) {
            console.error("Error AJAX:", status, error, xhr.responseText);
            alert("Error en la solicitud AJAX.");
        });
    });

    // --- BOTÓN CONFIRMAR ---
    $('#btnConfirmar').off('click').on('click', function (e) {
        e.preventDefault();
        let codigo = $('input[name="codigo"]').val();
        if (!confirm("¿Deseas confirmar esta compra? Se actualizará el stock.")) return;

        // Enviamos la solicitud AJAX 
        $.post("modules/compras/proses.php?act=confirm", { codigo: codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la compra: <strong>ACTIVO</strong>');
                $('#estado_compra').val('ACTIVO');
                manejarBotones('ACTIVO');
            } else {
                alert("Error al guardar: " + response.message);
            }
        }, "json").fail(function (xhr, status, error) {
            console.error("Error AJAX:", status, error, xhr.responseText);
            alert("Error en la solicitud AJAX.");
        });
    });


    $('#btnAnular').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();
        if (!confirm("¿Estás seguro que deseas anular esta compra?")) return;

        $.post("modules/compras/proses.php", { act: 'cancel', codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la compra: <strong>ANULADO</strong>');
                $('#estado_compra').val('ANULADO');
                manejarBotones('ANULADO');
            } else {
                alert("Error: " + response.message);
            }
        }, "json");
    });

    function generarTabla(tipo) {
        let html = '';
        if (tipo === 'orden') {
            html = `<table class="table table-striped" data-tipo="orden">
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
                        </table>`;
        } else if (tipo === 'general') {
            html = `<table class="table table-striped" data-tipo="general">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Tipo Impuesto</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>`;
        }

        $('#resultados').html(html);
    }

    // Toggle para vaciar el proveedor, ruc y productos[] cada que se activa
    $('#toggleProductos').on('change', function () {
        const toggleActivo = $(this).is(':checked');

        // LIMPIAR productos, proveedor y RUC al cambiar el modo
        productos = [];                     // Vacía el array de productos
        id_orden = null;                    // Resetea id de orden
        $('#resultados').empty();           // Limpia la tabla de detalles
        $('#id_orden_compra').val('');      // Limpia campo oculto
        $('#cod_proveedor').val('');        // Limpia campo oculto
        $('#productos_json').val('');       // Limpia JSON de productos
        $('#ruc').val('');                  // Limpia RUC
        $('#selectProveedor').val('');      // Limpia proveedor

        // Cambiar visibilidad de botones y estado del select
        if (toggleActivo) {
            // Modo productos generales
            $('#btnAgregarOrden').addClass('d-none');
            $('#btnAgregarProductos').removeClass('d-none');
            $('#selectProveedor').prop('disabled', false);
        } else {
            // Modo con orden de compra
            $('#btnAgregarOrden').removeClass('d-none');
            $('#btnAgregarProductos').addClass('d-none');
            $('#selectProveedor').prop('disabled', true);

            // Si ya hay una orden seleccionada, intentar restaurar su proveedor
            let cod_prov = $('input[name="cod_proveedor"]').val();
            let option = $('#selectProveedor option[value="' + cod_prov + '"]');
            if (option.length) {
                $('#selectProveedor').val(cod_prov);
                $('#ruc').val(option.data('ruc'));
            }
        }

        console.log('Modo de compra cambiado. Se vaciaron productos, proveedor y RUC.');
    });

    $('#selectProveedor').change(function () {
        let ruc = $(this).find(':selected').data('ruc') || '';
        $('#ruc').val(formatearRUC(ruc));
        // Obtener el código del proveedor (ya está en el value del option)
        let codProveedor = $(this).val() || '';
        $('#cod_proveedor').val(codProveedor); // enviar al input oculto 
    });

    // --- INICIALIZAR BOTONES SEGUN ESTADO ---
    manejarBotones($('#estado_compra').val());
</script>