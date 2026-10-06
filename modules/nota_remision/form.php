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
$query_id = mysqli_query($mysqli, "SELECT MAX(id_notaR) as id FROM notaR_compra") or die("Error: " . mysqli_error($mysqli));
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
                        <li class="breadcrumb-item"><a href="?module=nota_remision">Nota de Remision</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Registrar Nota de Remision</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Formulario de Nota de Remision</strong></div>
                <form id="formNotaR" class="form-horizontal" method="POST" autocomplete="off">
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
                            <label class="col-md-2 col-form-label">Nro. Nota</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="nro_nota" id="nro_nota" maxlength="15"
                                    placeholder="001-001-0000001" required>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Inicio de Traslado</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_ini_tras" id="fecha_ini_tras" required>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Tipo de traslado</label>
                            <div class="col-md-2">
                                <select class="form-control" name="tipo_traslado" required>
                                    <option value="" disabled selected>Seleccionar Tipo Traslado</option>
                                    <option value="POR COMPRA">-- Por compra --</option>
                                    <option value="por DEVOLUCION">-- Por Devolucion --</option>
                                </select>
                            </div>
                            <label class="col-md-2 col-form-label">Motivo de Traslado</label>
                            <div class="col-md-2">
                                <select class="form-control" name="motivo_traslado" required>
                                    <option value="" disabled selected>Seleccionar Motivo del Traslado</option>
                                    <option value="PROVEEDOR">-- Entrega de Proveedor --</option>
                                    <option value="ENVIO SUCURSALES">-- Envio a Sucursales --</option>
                                </select>
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
                            <label class="col-md-2 col-form-label">Nombre del Conductor o Empresa</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="conductor_empre" value="" required>
                            </div>
                            <label class="col-md-2 col-form-label">CI o RUC del conductor</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="ruc_ci" id="ruc_ci" maxlength="10"
                                    placeholder="00000001-1" required>
                            </div>
                            <label class="col-md-2 col-form-label">Placa del Vehiculo</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="placa" id="placa" placeholder="ABC-123"
                                    maxlength="10" required>
                            </div>
                        </div>
                        <br>
                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la compra: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_notaR" name="estado" value="<?= $estado ?>">

                        <!-- Botón agregar ordenes de compra -->
                        <div class="form-group">
                            <div class="col-md-10">
                                <?php if ($estado == 'BORRADOR') { ?>
                                    <button type="button" class="btn btn-info" data-coreui-toggle="modal"
                                        data-coreui-target="#myModal">
                                        <i class="fa fa-plus"></i> Seleccionar Registro de Compra
                                    </button>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Tabla de datos del proveedor -->
                        <div id="resultados" class="mt-3"></div>
                        <input type="hidden" name="productos_json" id="productos_json">
                        <input type="hidden" id="cod_compra" name="cod_compra">
                        <input type="hidden" id="cod_proveedor" name="cod_proveedor">

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
                            <a href="?module=nota_remision" class="btn btn-secondary">
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
    $id_nota = intval($_GET['id_notaR']);

    // Obtener los datos principales de la nota
    $query_nota = mysqli_query($mysqli, "
        SELECT n.id_notaR, c.cod_compra, prov.cod_proveedor, prov.ruc, prov.razon_social,n.nro_nota ,n.fecha, n.fecha_ini_traslado, n.tipo_traslado, n.motivo_traslado, n.nom_transporte, n.ruc_ci_trans, n.placa_vehiculo, n.id_user, n.estado,n.anulado_por, n.anulado_fecha, n.anulado_hora, pro.cod_producto, pro.p_descrip, u.id_u_medida, u.u_descrip, det.cantidad
        FROM  notaR_compra n
        JOIN det_notaR_compra det ON det.id_notaR = n.id_notaR
        JOIN compra c ON c.cod_compra = n.cod_compra 
        JOIN proveedor prov ON c.cod_proveedor = prov.cod_proveedor
        JOIN producto pro ON det.cod_producto = pro.cod_producto
        JOIN u_medida u ON u.id_u_medida = pro.id_u_medida 
        WHERE n.id_notaR = $id_nota
        LIMIT 1
    ") or die("Error: " . mysqli_error($mysqli));

    $nota = mysqli_fetch_assoc($query_nota);
    if (!$nota) {
        die("<div class='alert alert-danger'>No se encontró la nota solicitada.</div>");
    }

    $codigo = $nota['id_notaR'];
    $razon_social = $nota['razon_social'];
    $ruc_p = $nota['ruc'];
    $nro_nota = $nota['nro_nota'];
    $fecha = $nota['fecha'];
    $fecha_ini = $nota['fecha_ini_traslado'];
    $tipo_tras = $nota['tipo_traslado'];
    $motivo_tras = $nota['motivo_traslado'];
    $nom_trans = $nota['nom_transporte'];
    $ruc_ci = $nota['ruc_ci_trans'];
    $vehiculo_trans = $nota['placa_vehiculo'];
    $id_user = $nota['id_user'];
    $estado = $nota['estado'];
    ?>

    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=nota_remision">Nota Remision</a></li>
                        <li class="breadcrumb-item active">Detalle de Nota de Remision</li>
                    </ol>
                </div>
            </div>

            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Detalle de la Nota de Remision</h1>
            </div>

            <div class="card">
                <div class="card-header"><strong>Datos de la Nota de Remision</strong></div>
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
                            <label class="col-md-2 col-form-label">Nro. Nota</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="nro_nota" value="<?= $nro_nota ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Inicio de Traslado</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" value="<?= $fecha_ini ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Tipo de traslado</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $tipo_tras ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Motivo de Traslado</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $motivo_tras ?>" readonly>
                            </div>
                        </div>

                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $razon_social ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">RUC</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="ruc" value="<?= $ruc_p ?>" readonly>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Nombre del Conductor o Empresa</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $nom_trans ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">CI o RUC del conductor</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="ruc_ci" value="<?= $ruc_ci ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Placa del Vehiculo</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= $vehiculo_trans ?>" readonly>
                            </div>
                        </div>
                        <!-- Estado -->
                        <!-- Estado -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado de la compra: <strong><?= $estado ?></strong>
                        </div>
                        <input type="hidden" id="estado_notaR" name="estado" value="<?= $estado ?>">

                        <!-- Detalle de productos -->
                        <h5 class="mt-4">Detalle de Productos</h5>
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                $sql_detalle = mysqli_query($mysqli, "
                                    SELECT d.cod_producto, p.p_descrip , d.cantidad 
                                    FROM det_notaR_compra d
                                    JOIN producto p ON d.cod_producto = p.cod_producto
                                    WHERE d.id_notaR =  $id_nota
                                ");
                                while ($row = mysqli_fetch_assoc($sql_detalle)) {
                                    echo "
                                    <tr>
                                        <td>{$row['cod_producto']}</td>
                                        <td>{$row['p_descrip']}</td>
                                        <td>{$row['cantidad']}</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
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
                            <a href="?module=nota_remision" class="btn btn-secondary">
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
                <h5 class="modal-title" id="myModallabel">Buscar Compras</h5>
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
            url: './ajax/productos_notaR.php?action=ajax&page=' + page + '&x=' + x,
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
        ruc = ruc.replace(/\D/g, ''); // solo números
        if (ruc.length === 9) {
            return ruc.substr(0, 8) + '-' + ruc.substr(8, 1);
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

        $.post("ajax/agregar_notaR.php", { cod_compra: cod_compra }, function (data) {
            $("#resultados").html(data);

            if (typeof datosProveedor !== 'undefined') {
                // Aplicar datos del proveedor
                $("#cod_compra").val(cod_compra);
                $("input[name='cod_proveedor']").val(datosProveedor.cod_proveedor);
                $("input[name='proveedor']").val(datosProveedor.razon_social);
                $("input[name='ruc']").val(formatearRUC(datosProveedor.ruc));
            }
            /*if (typeof datosProveedor !== 'undefined' && Array.isArray(datosProveedor.productos)) {
                productos = datosProveedor.productos.map(function (p) {
                    return {
                        cod_producto: parseInt(p.cod_producto, 10),
                        cantidad: parseFloat(p.cantidad) || 0,
                        precio_unitario: parseFloat(p.precio_unitario ?? p.precio_unit ?? p.precio) || 0,
                        tipo_iva: String(p.tipo_iva ?? '0').replace('%', '') // dejamos '10' o '5' o '0'
                    };
                });
                console.log("productos inicializados desde datosProveedor:", productos);
            }*/


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

    function eliminarCompra() {
        //Eliminar todas las filas de la tabla
        $('table tbody').empty();

        //Vaciar el array de productos si existe
        if (typeof productos !== 'undefined') productos = [];

        //Limpiar campos del proveedor
        $("input[name='proveedor']").val('');
        $("input[name='ruc']").val('');

    }


    /*function actualizarTotal() {
        let total = 0;
        productos.forEach(p => {
            total += p.cantidad * p.precio_unitario;
        });
        $("input[name='total']").val(total.toFixed(2));
    }*/

    function actualizarProductosDesdeTabla() {
        productos = [];
        $('#resultados tbody tr').each(function () {
            var cod = $(this).data('cod') || 0;
            var cantidad = parseFloat($(this).find('.cantidad').text()) || 0;
            productos.push({
                cod_producto: cod,
                cantidad: cantidad
            });
        });
    }

    $('#myModal').on('shown.coreui.modal', function () {
        $('#x').val('');
        load(1, false);
        $('#x').focus();
    });

    // --- FUNCION GENÉRICA ---
    function actualizarEstado(act) {
        let codigo = $('input[name="codigo"]').val();

        $.post("./modules/notaR/proses.php", { act: act, codigo: codigo }, function (response) {
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
        let estado = $('#estado_notaR').val().trim().toUpperCase();
        manejarBotones(estado);
    });*/

    /*$('select[name="causa_nota"]').change(function () {
        let cond = $(this).val();
        if (cond === 'ajuste_c') {
            $('#monto_ajuste').show();
        } else {
            $('#monto_ajuste').hide();
            $('#id_monto_ajuste').val(1);
        }
    });*/

    // Formato Nro Nota: 001-001-0000001
    document.getElementById('nro_nota').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, ''); // quitar todo lo que no sea número
        if (val.length > 7) val = val.slice(0, 13); // limitar
        let part1 = val.slice(0, 3);
        let part2 = val.slice(3, 6);
        let part3 = val.slice(6, 13);
        e.target.value = part1 + (part2 ? '-' + part2 : '') + (part3 ? '-' + part3 : '');
    });

    // Formato RUC: 00000001-1
    document.getElementById('ruc').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '').slice(0, 9); // 8 dígitos + 1 dígito final
        let part1 = val.slice(0, 8);
        let part2 = val.slice(8, 9);
        e.target.value = part1 + (part2 ? '-' + part2 : '');
    });

    // Formato RUC (opcional si es ci)
    document.getElementById('ruc_ci').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '').slice(0, 9); // 8 dígitos + 1 dígito final
        let part1 = val.slice(0, 8);
        let part2 = val.slice(8, 9);
        e.target.value = part1 + (part2 ? '-' + part2 : '');
    });

    // Limite de escritura a las placa de auto
    /*document.getElementById('ruc_ci').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '').slice(0, 9); // 8 dígitos + 1 dígito final
        let part1 = val.slice(0, 8);
        let part2 = val.slice(8, 9);
        e.target.value = part1 + (part2 ? '-' + part2 : '');
    });*/

    // --- BOTONES ---
    $('#btnGuardar').off('click').on('click', function (e) {
        e.preventDefault();

        actualizarProductosDesdeTabla();

        if (!validarFormulario()) return;

        // Guardar JSON actualizado
        $('#productos_json').val(JSON.stringify(productos));

        let formData = $('#formNotaR').serialize();

        $.post("modules/nota_remision/proses.php?act=insert", formData, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la compra: <strong>ACTIVO</strong>');
                $('#estado_notaR').val('ACTIVO');
                manejarBotones('ACTIVO');
                window.location.href = "?module=nota_remision";
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
        console.log("Botón clickeado, codigo:", codigo);
        if (!confirm("¿Estás seguro que deseas anular esta compra?")) return;

        $.post("modules/nota_remision/proses.php", { act: 'cancel', codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado de la compra: <strong>ANULADO</strong>');
                $('#estado_notaR').val('ANULADO');
                manejarBotones('ANULADO');
                window.location.href = "?module=nota_remision";
            } else {
                alert("Error: " + response.message);
            }
        }, "json");
    });

    // --- INICIALIZAR BOTONES SEGUN ESTADO ---
    manejarBotones($('#estado_notaR').val());
</script>