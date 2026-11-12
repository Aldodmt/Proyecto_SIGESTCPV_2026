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

// El presupuesto se genera SIEMPRE a partir de un pedido confirmado (flujo pedido -> presupuesto -> orden -> compra).
if ($form === 'add') {
    // Vista previa del código: el definitivo lo reserva el servidor al guardar (flujo_siguiente_codigo)
    $codigo = (int) mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT COALESCE(MAX(id_presupuesto), 0) + 1 AS id FROM presupuesto"))['id'];
    $fecha_e = date("Y-m-d");
    $fecha_min = date("Y-m-d", strtotime('+1 day'));
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
                                <input type="date" class="form-control" name="fecha_v" value="<?= $fecha_min ?>" min="<?= $fecha_min ?>" required>
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
                                        echo "<option value='" . (int) $row['cod_proveedor'] . "'>" . h($row['razon_social']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Pedido</label>
                            <div class="col-md-10">
                                <button type="button" class="btn btn-info" id="btnAgregarPedidos" data-bs-toggle="modal"
                                    data-bs-target="#myModal">
                                    <i class="bi bi-plus-lg"></i> Seleccionar Pedido
                                </button>
                                <small class="text-muted ms-2">Solo pedidos CONFIRMADOS que todavía no tengan un presupuesto vigente.</small>
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

if ($form === 'detalle') {
    $id_presupuesto = isset($_GET['id_presupuesto']) ? intval($_GET['id_presupuesto']) : 0;

    // Cabecera del presupuesto (el detalle de productos se consulta aparte, más abajo)
    $stmt = $mysqli->prepare("
        SELECT p.id_presupuesto, p.id_pedido, p.fecha_presu, p.fecha_vencimiento, p.estado, pr.razon_social
        FROM presupuesto p
        JOIN proveedor pr ON p.cod_proveedor = pr.cod_proveedor
        WHERE p.id_presupuesto = ?
    ");
    $stmt->bind_param('i', $id_presupuesto);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo "<div class='alert alert-warning'>El presupuesto solicitado no existe. <a href='?module=presupuesto'>Volver</a></div>";
        return;
    }

    $codigo = (int) $data['id_presupuesto'];
    $fecha_e = $data['fecha_presu'];
    $fecha_v = $data['fecha_vencimiento'];
    $estado = strtoupper($data['estado']);
    $proveedor = $data['razon_social'];
    $id_pedido_origen = $data['id_pedido'];
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
                                <input type="date" class="form-control" name="fecha_emision" value="<?= h($fecha_e) ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha Vencimiento</label>
                            <div class="col-md-2">
                                <input type="date" class="form-control" name="fecha_vencimiento" value="<?= h($fecha_v) ?>" readonly>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado del presupuesto: <strong><?= h($estado) ?></strong>
                        </div>
                        <input type="hidden" id="estado_presupuesto" name="estado" value="<?= h($estado) ?>">

                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Proveedor</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" value="<?= h($proveedor) ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Pedido de origen</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" value="<?= h($id_pedido_origen) ?>" readonly>
                            </div>
                        </div>

                        <!-- Tabla de detalle (recién ahora se consultan los productos) -->
                        <?php
                        $detalle = flujo_detalle_presupuesto($codigo);
                        if ($detalle) {
                            $total = 0;
                            echo '<table class="table table-striped mt-3">';
                            echo '<thead><tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio Unitario</th><th class="text-end">Subtotal</th></tr></thead><tbody>';
                            foreach ($detalle as $row) {
                                $subtotal = $row['cantidad'] * $row['precio_unit'];
                                $total += $subtotal;
                                echo "<tr>
                                        <td>" . h($row['p_descrip']) . "</td>
                                        <td class='text-end'>" . h($row['cantidad']) . "</td>
                                        <td class='text-end'>" . flujo_dinero($row['precio_unit']) . "</td>
                                        <td class='text-end'>" . flujo_dinero($subtotal) . "</td>
                                      </tr>";
                            }
                            echo '</tbody><tfoot><tr><td colspan="3" class="text-end fw-bold">TOTAL:</td><td class="text-end fw-bold">' . flujo_dinero($total) . '</td></tr></tfoot></table>';
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
                            <button type="button" class="btn btn-primary" id="btnConfirmar">
                                <i class="bi bi-check-lg"></i> Aprobar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular">
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
                <h5 class="modal-title" id="myModallabel">Buscar Pedidos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form onsubmit="load(1); return false;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="x" placeholder="Buscar por código o usuario">
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

<?= flujo_script_botones('presupuesto') ?>
<script>
    var productos = [];

    // Lee la tabla (cantidades del pedido y precios tecleados) y arma la lista que se envía al servidor.
    // El servidor solo toma de aquí los PRECIOS; productos y cantidades los vuelve a leer del pedido.
    function actualizarProductos() {
        productos = [];
        $('#resultados tbody tr[data-cod]').each(function () {
            productos.push({
                codigo_producto: $(this).data('cod') || 0,
                cantidad: parseFloat($(this).find('.cantidad').text()) || 0,
                precio_unitario: parseFloat($(this).find('.precio_unit').val()) || 0
            });
        });
    }

    function actualizarSubtotales() {
        let total = 0;
        $('#resultados tbody tr[data-cod]').each(function () {
            let subtotal = (parseFloat($(this).find('.cantidad').text()) || 0) * (parseFloat($(this).find('.precio_unit').val()) || 0);
            $(this).find('.subtotal').text(subtotal.toFixed(2));
            total += subtotal;
        });
        $('#total_general').text(total.toFixed(2));
        actualizarProductos();
    }
    // Los inputs de precio se crean dinámicamente: el evento se delega en el contenedor
    $('#resultados').on('input', '.precio_unit', actualizarSubtotales);

    function validarFormulario() {
        if (!$('select[name="codigo_proveedor"]').val()) {
            alert("Debes seleccionar un proveedor.");
            return false;
        }
        if (!$('#id_pedido').val()) {
            alert("Debes seleccionar un pedido confirmado.");
            return false;
        }
        actualizarProductos();
        if (productos.length === 0) {
            alert("El pedido seleccionado no tiene productos.");
            return false;
        }
        if (productos.some(p => p.precio_unitario <= 0)) {
            alert("Todos los productos deben tener un precio unitario mayor a 0.");
            return false;
        }
        return true;
    }

    function load(page, mostrarLoader = true) {
        if (mostrarLoader) $('#loader').show();
        $.get('./ajax/productos_presu.php', { action: 'ajax', page: page, x: $('#x').val() || '' })
            .done(function (data) { $('.outer_div').html(data); })
            .fail(function (xhr, status, error) { alert("Error en la solicitud AJAX: " + error); })
            .always(function () { $('#loader').hide(); });
    }

    function generarTabla() {
        $('#resultados').html(`<table class="table table-striped" data-tipo="pedido">
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
            <tbody></tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                    <td id="total_general" class="fw-bold">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>`);
    }

    function seleccionarPedido(id_pedido) {
        if ($('#resultados tbody tr').length > 0) {
            alert("Ya hay un pedido cargado. Debes quitarlo antes de seleccionar otro.");
            return;
        }
        generarTabla();
        // El detalle se consulta recién ahora; el servidor vuelve a validar que el pedido siga CONFIRMADO y libre
        $.post("ajax/agregar_presu.php", { id_pedido: id_pedido }, function (data) {
            $("#resultados tbody").html(data);
            if ($('#resultados tbody tr[data-error]').length) {
                $('#id_pedido').val('');
                return;
            }
            $('#id_pedido').val(id_pedido);
            actualizarSubtotales();
        });
        bootstrap.Modal.getInstance(document.getElementById('myModal')).hide();
    }

    function eliminarPedido(id_pedido) {
        $('#resultados').html('');
        $('#id_pedido').val('');
        productos = [];
    }

    // Carga la primera página cuando se abre la ventana
    $('#myModal').on('shown.bs.modal', function () {
        $('#x').val('');
        load(1, false);
        $('#x').focus();
    });

    // Acciones de estado del presupuesto ya guardado. El servidor revalida el estado; luego se recarga
    // para mostrar el estado real y los botones que le corresponden.
    function accionPresupuesto(act) {
        let codigo = $('input[name="codigo"]').val();
        $.post("modules/presupuesto/proses.php", { act: act, codigo: codigo }, function (response) {
            alert(response.message);
            location.reload();
        }, "json").fail(function () {
            alert("Error en la solicitud AJAX.");
        });
    }

    $(document).ready(function () {
        manejarBotones($('#estado_presupuesto').val());

        $('#btnGuardar').off('click').on('click', function (e) {
            e.preventDefault();
            if (!validarFormulario()) return;
            $('#productos_json').val(JSON.stringify(productos));

            $.post("modules/presupuesto/proses.php?act=insert", $('#formPresupuesto').serialize(), function (response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = "?module=presupuesto";
                } else {
                    alert("Error al guardar: " + response.message);
                }
            }, "json").fail(function () {
                alert("Error en la solicitud AJAX.");
            });
        });

        $('#btnConfirmar').off('click').on('click', function (e) {
            e.preventDefault();
            accionPresupuesto('confirm');
        });

        $('#btnAnular').off('click').on('click', function (e) {
            e.preventDefault();
            if (!confirm("¿Estás seguro que deseas anular este presupuesto?")) return;
            accionPresupuesto('cancel');
        });
    });
</script>
