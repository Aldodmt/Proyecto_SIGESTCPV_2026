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
$query_id = mysqli_query($mysqli, "SELECT MAX(id_nota) as id FROM nota_credito_debito") or die("Error: " . mysqli_error($mysqli));
$data_id = mysqli_fetch_assoc($query_id);
$id_nota = ($data_id['id'] ?? 0) + 1;

$fecha = date("Y-m-d");
$hora = date("H:i:s");

//formulario para agregar
if ($_GET['form'] == 'add') {
    $codigo = $id_nota;
    $estado = 'BORRADOR';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=nota_c_d">Nota Credito o Debito</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Registrar Nota de Credito o Debito</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Nota Credito o Debito</strong></div>
                <form id="formNotaCD" class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Fecha</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_E" value="<?= $fecha ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="hora" value="<?= $hora ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Nro. Nota</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="nro_nota" id="nro_nota" value="" maxlength="8"
                                    placeholder="12345678" required required>
                            </div>
                            <label class="col-md-2 col-form-label">Nro. timbrado</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="nro_timbrado" id="nro_timbrado" maxlength="8"
                                    placeholder="12345678" required>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="proveedor" value="" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">RUC</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="ruc" id="ruc" maxlength="10"
                                    placeholder="00000001-1" required readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Tipo de nota</label>
                            <div class="col-md-2">
                                <select class="form-control" name="tipo_nota" required>
                                    <option value="" disabled selected>Seleccionar Tipo Nota</option>
                                    <option value="CREDITO">-- Credito --</option>
                                    <option value="DEBITO">-- Debito --</option>
                                </select>
                            </div>
                            <label class="col-md-2 col-form-label">Causa de la nota</label>
                            <div class="col-md-2">
                                <select class="form-control" name="causa_nota" required>
                                    <option value="" disabled selected>Seleccionar Causa Nota</option>
                                    <option value="ajuste_p">-- Ajuste de productos --</option>
                                    <option value="ajuste_c">-- Ajuste de cuentas --</option>
                                </select>
                            </div>
                            <label class="col-md-2 col-form-label">Observacion</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="razon" placeholder="Ingrese la razon aquí">
                            </div>
                        </div>
                        <br>
                        <div class="form-group row" id="monto_ajuste" style="display:none;">
                            <label class="col-md-2 col-form-label">Monto a ajustar</label>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="id_monto_ajuste" id="id_monto_ajuste"
                                    min="1" value="1">
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la compra: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_nota_cd" name="estado" value="<?= $estado ?>">

                        <!-- Botón agregar ordenes de compra -->
                        <div class="form-group" id="registrarCompra" style="display:none;">
                            <div class="col-md-10">
                                <?php if ($estado == 'BORRADOR') { ?>
                                    <button type="button" class="btn btn-info" data-coreui-toggle="modal"
                                        data-coreui-target="#myModal">
                                        <i class="fa fa-plus"></i> Seleccionar Registro de Compra
                                    </button>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="form-group" id="grupoAjuste" style="display:none;">
                            <label class="col-md-2">Compra asociada</label>
                            <div class="col-md-6">
                                <select id="selectCompraAjuste" name="compra_ajuste" class="form-control">
                                    <option value="" disabled selected>Seleccione una compra...</option>
                                    <?php
                                    $query_compras = mysqli_query($mysqli, "
                                        SELECT 
                                            c.cod_compra, 
                                            c.fac_numero, 
                                            c.total_compra,
                                            p.razon_social, 
                                            p.ruc
                                        FROM compra c
                                        JOIN proveedor p 
                                            ON c.cod_proveedor = p.cod_proveedor
                                        JOIN cuentas_a_pagar cp 
                                            ON c.cod_compra = cp.cod_compra
                                        WHERE c.estado = 'ACTIVO'
                                        AND cp.cap_estado = 'PENDIENTE'
                                        AND c.cod_compra NOT IN (
                                            SELECT cod_compra 
                                            FROM nota_credito_debito 
                                            WHERE cod_compra IS NOT NULL
                                        )
                                        GROUP BY 
                                            c.cod_compra, 
                                            c.fac_numero, 
                                            p.razon_social, 
                                            p.ruc
                                        ORDER BY 
                                            c.cod_compra DESC
                                    ");
                                    while ($row = mysqli_fetch_assoc($query_compras)) {
                                        echo "
                                            <option 
                                                value='{$row['cod_compra']}'
                                                data-proveedor='{$row['razon_social']}'
                                                data-ruc='{$row['ruc']}'>
                                                Compra #{$row['cod_compra']} - Factura {$row['fac_numero']} - Total de la compra: " . number_format($row['total_compra'], 0, ',', '.') . "
                                            </option>
                                        ";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>



                        <!-- Tabla de datos del proveedor -->
                        <div id="resultados" class="mt-3"></div>
                        <input type="hidden" name="productos_json" id="productos_json">
                        <input type="hidden" id="cod_compra" name="cod_compra">
                        <input type="hidden" id="fac_numero" name="fac_numero">
                        <input type="hidden" id="cod_proveedor" name="cod_proveedor">
                        <input type="hidden" id="origen_ajuste" name="origen_ajuste">

                    </div>

                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=nota_c_d" class="btn btn-secondary">
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
    $id_nota = intval($_GET['id_nota']);

    // Obtener los datos principales de la nota
    $query_nota = mysqli_query($mysqli, "
        SELECT n.id_nota, n.tipo, n.causa, n.nro_nota, n.timbrado, n.fecha_emision,
        n.estado, n.monto_total, n.observacion, c.cod_compra,
        c.fac_numero, p.cod_proveedor, p.razon_social, p.ruc, c.total_compra
        FROM nota_credito_debito n
        JOIN compra c ON n.cod_compra = c.cod_compra
        JOIN proveedor p ON c.cod_proveedor = p.cod_proveedor
        WHERE n.id_nota = $id_nota
        LIMIT 1
    ") or die("Error: " . mysqli_error($mysqli));

    $nota = mysqli_fetch_assoc($query_nota);
    if (!$nota) {
        die("<div class='alert alert-danger'>No se encontró la nota solicitada.</div>");
    }

    $codigo = $nota['id_nota'];
    $tipo = $nota['tipo'];
    $causa = $nota['causa'];
    $nro_nota = $nota['nro_nota'];
    $timbrado = $nota['timbrado'];
    $fecha = $nota['fecha_emision'];
    $estado = $nota['estado'];
    $monto_total = $nota['monto_total'];
    $observacion = $nota['observacion'];
    $fac_numero = $nota['fac_numero'];
    $fac_total = $nota['total_compra'];
    $proveedor = $nota['razon_social'];
    $ruc = $nota['ruc'];
    ?>

    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=nota_c_d">Nota C o D</a></li>
                        <li class="breadcrumb-item active">Detalle de Nota</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Detalle de la Nota de Crédito o Débito</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Datos de la Nota</strong></div>
                <form class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Fecha</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_E" value="<?= $fecha ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">N° Nota</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="nro_nota" value="<?= $nro_nota ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Timbrado</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="nro_timbrado" value="<?= $timbrado ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Tipo</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $tipo ?>" readonly>
                            </div>

                            <label class="col-md-2 col-form-label">Causa</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control"
                                    value="<?= $causa == 'ajuste_p' ? 'Ajuste de Productos' : 'Ajuste de Cuentas' ?>"
                                    readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $proveedor ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">RUC</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="ruc" value="<?= $ruc ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Factura Asociada</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $fac_numero ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Total Factura</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control"
                                    value="<?= number_format($fac_total, 0, ',', '.') ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group row mt-3">
                            <label class="col-md-2 col-form-label">Observación</label>
                            <div class="col-md-10">
                                <textarea class="form-control" readonly><?= $observacion ?></textarea>
                            </div>
                        </div>
                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la compra: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_nota_cd" name="estado" value="<?= $estado ?>">

                        <!-- Detalle de productos -->
                        <h5 class="mt-4">Detalle de Productos</h5>
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                $sql_detalle = mysqli_query($mysqli, "
                                    SELECT d.cod_producto, p.p_descrip, d.cantidad, d.precio_unitario
                                    FROM det_nota_credit_debit d
                                    JOIN producto p ON d.cod_producto = p.cod_producto
                                    WHERE d.id_nota = $id_nota
                                ");
                                while ($row = mysqli_fetch_assoc($sql_detalle)) {
                                    $subtotal = $row['cantidad'] * $row['precio_unitario'];
                                    echo "
                                    <tr>
                                        <td>{$row['cod_producto']}</td>
                                        <td>{$row['p_descrip']}</td>
                                        <td>{$row['cantidad']}</td>
                                        <td>" . number_format($row['precio_unitario'], 0, ',', '.') . "</td>
                                        <td>" . number_format($subtotal, 0, ',', '.') . "</td>
                                    </tr>";
                                    $total += $subtotal;
                                }
                                ?>
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-end mt-3">
                            <label class="me-2 mb-0"><strong>Total Nota:</strong></label>
                            <input type="text" class="form-control text-end"
                                value="<?= number_format($monto_total, 0, ',', '.') ?>" readonly style="width:150px;">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=nota_c_d" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Salir
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>



<!-- Modal Buscar Pedido -->
<div class="modal" id="myModal" tabindex="-1" aria-labelledby="myModallabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModallabel">Buscar compra</h5>
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


<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- CoreUI JS -->
<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.2.2/dist/js/coreui.bundle.min.js"></script>

<!-- Select2 (opcional) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    var productos = [];
    var cod_compra = null;

    function validarFormulario() {
        // Obtener el valor de causa_nota del formulario
        const causaNota = document.querySelector('select[name="causa_nota"]').value;

        // Solo validar productos si es ajuste_p
        if (causaNota === 'ajuste_p' && productos.length === 0) {
            alert("Debes agregar al menos un producto para un ajuste de producción (ajuste_p).");
            return false;
        }

        return true;
    }

    function load(page, mostrarLoader = true) {
        var x = $("#x").val() || '';
        if (mostrarLoader) $("#loader").fadeIn('slow');
        $.ajax({
            url: './ajax/productos_nota_cd.php?action=ajax&page=' + page + '&x=' + x,
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

    function seleccionarCompra(cod_compra) {
        // Verificar si ya hay elementos en la tabla de productos
        if ($('#resultados tbody tr').length > 0) {
            alert("Ya hay productos cargados. Debe limpiar la tabla antes de seleccionar otra orden.");
            return; // salir de la función
        }
        // Limpiar tabla y productos antes de agregar nuevos
        $('#resultados').html('');
        productos = [];

        $.post("ajax/agregar_nota_cd.php", { cod_compra: cod_compra }, function (data) {
            $("#resultados").html(data);

            if (typeof datosProveedor !== 'undefined') {
                // Aplicar datos del proveedor
                $("#cod_compra").val(cod_compra);
                $("input[name='cod_proveedor']").val(datosProveedor.cod_proveedor);
                $("input[name='proveedor']").val(datosProveedor.razon_social);
                $("input[name='ruc']").val(formatearRUC(datosProveedor.ruc));
                $("input[name='nro_timbrado']").val(formatearTimbrado(datosProveedor.timbrado_num));
                $("input[name='fac_numero']").val(datosProveedor.factura_num);
            }
            if (typeof datosProveedor !== 'undefined' && Array.isArray(datosProveedor.productos)) {
                productos = datosProveedor.productos.map(function (p) {
                    return {
                        cod_producto: parseInt(p.cod_producto, 10),
                        cantidad: parseFloat(p.cantidad) || 0,
                        precio_unitario: parseFloat(p.precio_unitario ?? p.precio_unit ?? p.precio) || 0,
                        tipo_iva: String(p.tipo_iva ?? '0').replace('%', '') // dejamos '10' o '5' o '0'
                    };
                });
                console.log("productos inicializados desde datosProveedor:", productos);
            }


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

    function eliminarCompra(cod_producto) {
        // Eliminar fila visualmente
        $('#fila_' + cod_producto).remove();

        // Eliminar del array productos correctamente
        productos = productos.filter(p => p.cod_producto != cod_producto);

        // Actualizar total
        actualizarTotal();
    }


    function actualizarTotal() {
        let total = 0;
        productos.forEach(p => {
            total += p.cantidad * p.precio_unitario;
        });
        $("input[name='total']").val(total.toFixed(2));
    }

    function actualizarProductosDesdeTabla() {
        productos = [];
        $('#resultados tbody tr').each(function () {
            var cod = $(this).data('cod') || 0;
            var cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            var precio = parseFloat($(this).find('.precio_unit').text()) || 0;
            var tipo_iva = $(this).find('.tipo_iva').text().trim() || '0';
            tipo_iva = String(tipo_iva).replace('%', ''); // normalizar
            productos.push({
                cod_producto: cod,
                cantidad: cantidad,
                precio_unitario: precio,
                tipo_iva: tipo_iva
            });
        });
    }

    $('#myModal').on('shown.coreui.modal', function () {
        console.log("Modal abierto");
        $('#x').val('');
        load(1, false);
        $('#x').focus();
    });

    // --- FUNCION GENÉRICA ---
    function actualizarEstado(act) {
        let codigo = $('input[name="codigo"]').val();

        $.post("./modules/nota_c_d/proses.php", { act: act, codigo: codigo }, function (response) {
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

    // --- FUNCION PARA MANEJAR BOTONES SEGUN ESTADO ---
    function manejarBotones(estado) {
        switch (estado) {
            case 'BORRADOR':
                $('#btnGuardar').prop('disabled', false);
                $('#btnAnular').prop('disabled', true);
                break;
            /*case 'PENDIENTE':
            $('#btnGuardar').prop('disabled', true);
            $('#btnConfirmar').prop('disabled', false);
            $('#btnAnular').prop('disabled', true);
            break;*/
            case 'ACTIVO':
                $('#btnGuardar').prop('disabled', true);
                $('#btnAnular').prop('disabled', false);
                break;
            case 'ANULADO':
                $('#btnGuardar, #btnAnular').prop('disabled', true);
                break;
            default:
                $('#btnGuardar').prop('disabled', false);
                $('#btnConfirmar, #btnAnular').prop('disabled', true);
        }
    }

    /*$(document).ready(function () {
        let estado = $('#estado_nota_cd').val().trim().toUpperCase();
        manejarBotones(estado);
    });*/

    $('select[name="causa_nota"]').change(function () {
        let causa = $(this).val();

        //limpieza de la tabla y el array que almacena los productos
        $('#resultados').empty();
        productos = [];

        // Limpiar campos proveedor/ruc
        $("input[name='proveedor']").val('');
        $("input[name='ruc']").val('');

        if (causa === 'ajuste_c') {
            // Ajuste de cuentas
            $('#registrarCompra').hide(); // ocultar botón del modal
            $('#grupoAjuste').show(); // mostrar select de compras
            $('#monto_ajuste').show(); // mostrar campo de monto
            $('#origen_ajuste').val('select'); // origen: select de ajuste_c
        } else if (causa == 'ajuste_p') {
            // Ajuste de productos u otro
            $('#registrarCompra').show(); // mostrar botón del modal
            $('#grupoAjuste').hide(); // ocultar select de compras
            $('#monto_ajuste').hide(); // ocultar campo de monto
            $('#id_monto_ajuste').val(1); // reset monto
            $('#origen_ajuste').val('modal'); // origen: modal de productos
        } else {
            // Ninguno seleccionado
            $("#grupoAjuste").hide();
            $("#registrarCompra").hide();
            $("#monto_ajuste").hide();
        }
    });

    // Ejecutar al cargar la página para mantener consistencia
    $(document).ready(function () {
        $('select[name="causa_nota"]').trigger('change');
    });

    $('#selectCompraAjuste').change(function () {
        let proveedor = $(this).find(':selected').data('proveedor') || '';
        let ruc = $(this).find(':selected').data('ruc') || '';

        // Rellenar los campos del formulario
        $('input[name="proveedor"]').val(proveedor);
        $('#ruc').val(formatearRUC ? formatearRUC(ruc) : ruc);
    });

    // Formato Nro Factura: 001-001-0000001
    /*document.getElementById('nro_factura').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, ''); // quitar todo lo que no sea número
        if (val.length > 7) val = val.slice(0, 13); // limitar
        let part1 = val.slice(0, 3);
        let part2 = val.slice(3, 6);
        let part3 = val.slice(6, 13);
        e.target.value = part1 + (part2 ? '-' + part2 : '') + (part3 ? '-' + part3 : '');
    });*/

    // Formato Nro Nota: solo 8 dígitos
    document.getElementById('nro_nota').addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 8);
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

        actualizarProductosDesdeTabla();

        // Guardar JSON actualizado
        $('#productos_json').val(JSON.stringify(productos));

        // --- NUEVO: mostrar TODOS los datos antes de enviar ---
        let formDataObj = {};
        $('#formNotaCD').serializeArray().forEach(function (field) {
            formDataObj[field.name] = field.value;
        });
        console.log("Datos que se enviarán a proses.php:", formDataObj);

        if (!validarFormulario()) return;

        // RECORRER CADA FILA Y ACTUALIZAR LA CANTIDAD A AJUSTAR
        $('#resultados tbody tr').each(function () {
            const codProd = $(this).data('cod');
            const cantAjuste = parseFloat($(this).find('input[name^="ajuste_cant_nota"]').val()) || 0;

            // Buscar producto en el array productos
            const producto = productos.find(p => p.cod_producto == codProd);
            if (producto) {
                producto.cantidad_ajustar = cantAjuste; // <-- NUEVA PROPIEDAD
            }
        });

        // Guardar el JSON actualizado
        $('#productos_json').val(JSON.stringify(productos));

        console.log("ENVIO productos_json:", JSON.stringify(productos, null, 2));

        let formData = $('#formNotaCD').serialize();

        $.post("modules/nota_c_d/proses.php?act=insert", formData, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la compra: <strong>ACTIVO</strong>');
                $('#estado_compra').val('ACTIVO');
                manejarBotones('ACTIVO');
                window.location.href = "?module=nota_c_d";
            } else {
                alert("Error al guardar: " + response.message);
            }
        }, "json").fail(function (xhr, status, error) {
            console.error("Error AJAX:", status, error, xhr.responseText);
            alert("Error en la solicitud AJAX.");
        });
    });


    /*$('#btnConfirmar').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();

        $.ajax({
            url: "modules/compras/proses.php",
            type: "POST",
            data: { act: 'confirm', codigo: codigo },
            dataType: "json",
            success: function (response) {
                console.log(response); // <- IMPORTANTE para depurar
                if (response.success) {
                    alert(response.message);
                    $('#estadoActual').html('Estado de la compra: <strong>APROBADO</strong>');
                    $('#estado_compra').val('APROBADO');
                    manejarBotones('APROBADO');
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Error en la solicitud AJAX: " + error);
            }
        });
    });*/

    $('#btnAnular').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();
        if (!confirm("¿Estás seguro que deseas anular esta compra?")) return;

        $.post("modules/nota_c_d/proses.php?act=cancel", { codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la compra: <strong>ANULADO</strong>');
                $('#estado_compra').val('ANULADO');
                manejarBotones('ANULADO');
                window.location.href = "?module=nota_c_d";
            } else {
                alert("Error: " + response.message);
            }
        }, "json");
    });

    // --- INICIALIZAR BOTONES SEGUN ESTADO ---
    manejarBotones($('#estado_nota_cd').val());
</script>