<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/database.php";
require_once __DIR__ . '/../../config/compras.php';

$id_usuario = (int) ($_SESSION['id_user'] ?? 0);
if (!$id_usuario) {
    die("No se detectó usuario logueado."); // O redirigir a login
}

$form = $_GET['form'] ?? '';

/*
  Un solo dibujo del formulario para el alta y para el detalle.
  En el ALTA el pedido es un BORRADOR persistido en la base: cada producto que se agrega queda registrado en el
  momento (ajax/agregar_pedidos.php), de modo que un corte de luz, de internet o un F5 no pierde nada.
  El borrador solo se elimina con el botón "Salir" (después de confirmar).
*/
if (!function_exists('pedido_render_form')) {
    function pedido_render_form(array $pedido, bool $alta): void
    {
        $codigo = (int) $pedido['id_pedido'];
        $estado = strtoupper(trim((string) $pedido['estado']));
        // El detalle de productos solo se toca en BORRADOR o MODIFICANDO (la misma regla que valida el servidor)
        $editable = in_array($estado, COMPRAS_FLUJO['pedido']['editable'], true);
        ?>
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                            <li class="breadcrumb-item"><a href="?module=pedido">Pedidos</a></li>
                            <li class="breadcrumb-item active"><?= $alta ? 'Agregar' : 'Detalles del Pedido' ?></li>
                        </ol>
                    </div>
                </div>
                <div class="col-sm-6">
                    <h1><i class="bi bi-pencil-square me-1"></i> <?= $alta ? 'Agregar Pedido' : 'Detalles del Pedido' ?></h1>
                </div>
                <div class="card">
                    <div class="card-header"><strong>Formulario de Pedidos</strong></div>
                    <form id="formPedido" method="POST" autocomplete="off">
                        <div class="card-body">
                            <div class="row mb-3 row">
                                <label class="col-md-2 col-form-label">Código</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                                </div>
                                <label class="col-md-2 col-form-label">Fecha</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="fecha" value="<?= h($pedido['fecha']) ?>" readonly>
                                </div>
                                <label class="col-md-2 col-form-label">Hora</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="hora" value="<?= h($pedido['hora']) ?>" readonly>
                                </div>
                            </div>

                            <!-- Estado del pedido -->
                            <div class="alert alert-info mt-3" id="estadoActual">
                                Estado del pedido: <strong><?= h($estado) ?></strong>
                            </div>
                            <input type="hidden" id="estado_pedido" name="estado" value="<?= h($estado) ?>">
                           <?php if ($alta) { ?>
                                <div class="alert alert-secondary py-2">
                                    <i class="bi bi-cloud-check"></i> Este pedido es un <strong>borrador</strong>: cada producto que agregas se
                                    guarda en el sistema al instante. Si se cierra el navegador o se corta la conexión, al volver a
                                    <em>Agregar</em> continúas desde aquí. Solo el botón <em>Salir</em> lo descarta.
                                </div>
                            <?php } ?>
                            <br>
                            <!-- Botón Agregar Productos -->
                            <div class="row mb-3">
                                <label class="col-md-2 col-form-label">Producto</label>
                                <div class="col-md-10">
                                    <?php if ($editable) { ?>
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#myModal">
                                            <i class="bi bi-plus-lg"></i> Agregar Productos
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- Tabla de productos (el AJAX la reemplaza con la misma estructura) -->
                            <div id="resultados" class="mt-3">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Unidad</th>
                                            <th>Tipo de Impuesto</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (flujo_detalle_pedido($codigo) as $prod) { ?>
                                            <tr>
                                                <td><?= (int) $prod['cod_producto'] ?></td>
                                                <td><?= h($prod['p_descrip']) ?></td>
                                                <td><?= h($prod['cantidad']) ?></td>
                                                <td><?= h($prod['u_descrip']) ?></td>
                                                <td><?= h($prod['tipo_impuesto']) ?></td>
                                                <td>
                                                    <?php if ($editable) { ?>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="eliminar(<?= (int) $prod['cod_producto'] ?>, <?= $codigo ?>)">Eliminar</button>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Botones -->
                        <div class="card-footer">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary" id="btnGuardar">
                                    <i class="bi bi-save"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-primary" id="btnConfirmar" disabled>
                                    <i class="bi bi-check-lg"></i> Confirmar
                                </button>
                                <button type="button" class="btn btn-primary" id="btnModificar" disabled>
                                    <i class="bi bi-pencil-square"></i> Modificar
                                </button>
                                <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                    <i class="bi bi-x-lg"></i> Anular
                                </button>
                                <a href="?module=pedido" class="btn btn-secondary"<?= $alta ? ' id="btnSalir"' : '' ?>>
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
}

if ($form === 'add') {
    // Busca el borrador del usuario o crea uno (una sola vez, aunque abra dos pestañas): ver flujo_borrador_pedido()
    $id_pedido = flujo_borrador_pedido($id_usuario);
    $s = $mysqli->prepare("SELECT * FROM pedido WHERE id_pedido = ?");
    $s->bind_param('i', $id_pedido);
    $s->execute();
    $pedido = $s->get_result()->fetch_assoc();
    $s->close();
    pedido_render_form($pedido, true);
} elseif ($form === 'detalle') {
    $id_pedido = intval($_GET['id_pedido'] ?? 0);
    $s = $mysqli->prepare("SELECT * FROM pedido WHERE id_pedido = ?");
    $s->bind_param('i', $id_pedido);
    $s->execute();
    $pedido = $s->get_result()->fetch_assoc();
    $s->close();

    if (!$pedido) {
        echo "<div class='alert alert-warning'>El pedido solicitado no existe. <a href='?module=pedido'>Volver</a></div>";
        return;
    }
    // Un BORRADOR no es todavía un documento: se continúa desde "Agregar" (y solo lo ve su dueño)
    if (strtoupper($pedido['estado']) === 'BORRADOR') {
        echo "<div class='alert alert-warning'>Este pedido es un borrador sin guardar. Continúalo desde <a href='?module=form_pedido&form=add'>Agregar</a>.</div>";
        return;
    }
    pedido_render_form($pedido, false);
}
?>

<!-- Gestión de pedidos -->
<?= flujo_script_botones('pedido') ?>
<script>
    // Todas las acciones de estado van al servidor, que vuelve a validar el estado actual (config/compras.php).
    // Si el servidor responde que no corresponde, se muestra su mensaje y se recarga para reflejar el estado real.
    function accionPedido(act, extra) {
        let codigo = $('input[name="codigo"]').val();
        $.post("./modules/pedido/proses.php", Object.assign({ act: act, codigo: codigo }, extra || {}), function (response) {
            alert(response.message);
            if (response.success && response.redirect) {
                window.location.href = response.redirect;
            } else {
                location.reload();
            }
        }, "json").fail(function () {
            alert("Error en la solicitud AJAX.");
        });
    }

    $(document).ready(function () {
        // Habilitación de botones según el estado (misma tabla que usa el servidor para validar)
        manejarBotones($('#estado_pedido').val());

        $('#btnGuardar').off('click').on('click', function (e) { e.preventDefault(); accionPedido('save'); });
        $('#btnConfirmar').off('click').on('click', function (e) { e.preventDefault(); accionPedido('confirm'); });
        $('#btnModificar').off('click').on('click', function (e) { e.preventDefault(); accionPedido('modify'); });
        $('#btnAnular').off('click').on('click', function (e) {
            e.preventDefault();
            if (!confirm("¿Estás seguro que deseas anular este pedido? Esta acción no se puede deshacer.")) return;
            accionPedido('cancel', { confirm: true });
        });

        // SALIR (solo en el alta): es la ÚNICA forma de descartar el borrador. Se pide confirmación porque se pierden
        // todos los productos cargados. Cerrar el navegador, refrescar o salirse por el menú NO borra nada.
        $('#btnSalir').off('click').on('click', function (e) {
            e.preventDefault();
            if (!confirm("Si sales ahora se eliminará este pedido en borrador y se perderán todos los productos cargados.\n\n¿Deseas salir y descartar el pedido?")) return;
            $.post("./modules/pedido/proses.php", { act: 'clear_borrador', codigo: $('input[name="codigo"]').val() }, function (response) {
                if (response.success) {
                    window.location.href = "?module=pedido";
                } else {
                    alert('No se pudo descartar el borrador: ' + response.message);
                }
            }, "json").fail(function () {
                alert("Error en la solicitud AJAX.");
            });
        });
    });
</script>



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
        let cantidad = parseFloat($("#cantidad_" + id).val());
        let id_pedido = $('input[name="codigo"]').val();

        if (isNaN(cantidad) || cantidad <= 0) {
            alert("La cantidad debe ser mayor a 0.");
            return;
        }

        $.post("ajax/agregar_pedidos.php", {
            id_pedido: id_pedido,
            id: id,
            cantidad: cantidad,
            op: 'add'
        }, function (data) {
            $("#resultados").html(data); // solo actualiza la tabla
        });
    }

    function eliminar(id_producto, id_pedido) {
        $.post("ajax/agregar_pedidos.php", {
            id_pedido: id_pedido,
            id: id_producto,
            op: 'delete'
        }, function (data) {
            $("#resultados").html(data); // solo actualiza la tabla
        });
    }

    function guardarNuevoProducto() {
        var formData = $("#formNuevoProducto").serialize();
        $.ajax({
            type: "POST",
            url: "./ajax/agregarN_productos.php",
            data: formData,
            beforeSend: function () {
                $(".btn-primary").prop("disabled", true);
            },
            success: function (response) {
                try {
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        $("#modalNuevoProducto").modal("hide");
                        $("#formNuevoProducto")[0].reset();
                        load(1);
                        alert("Producto guardado exitosamente.");
                    } else {
                        alert("Error: " + result.message);
                    }
                } catch (e) {
                    alert("Error al procesar la respuesta del servidor.");
                }
                $(".btn-primary").prop("disabled", false);
            },
            error: function () {
                $(".btn-primary").prop("disabled", false);
                alert("Error al guardar el producto.");
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
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" onclick="load(1)">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modalNuevoProducto">
                                <i class="bi bi-plus-lg"></i> Añadir Producto Nuevo
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

<!-- Modal para Nuevo Producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-labelledby="modalNuevoProductoLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoProductoLabel">Agregar Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoProducto">
                    <div class="mb-3">
                        <label for="nombre_prod" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="nombre_prod" name="nombre_prod" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_producto" class="form-label">Tipo de Producto</label>
                        <select class="form-select" id="tipo_producto" name="tipo_producto" required>
                            <option value="">Seleccione tipo de producto</option>
                            <?php
                            $query_tipo = mysqli_query($mysqli, "SELECT cod_tipo_prod, t_p_descrip FROM tipo_producto ORDER BY t_p_descrip ASC");
                            while ($row = mysqli_fetch_assoc($query_tipo)) {
                                echo "<option value='" . $row['cod_tipo_prod'] . "'>" . $row['t_p_descrip'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="unidad_medida" class="form-label">Unidad de Medida</label>
                        <select class="form-select" id="unidad_medida" name="unidad_medida" required>
                            <option value="">Seleccione unidad de medida</option>
                            <?php
                            $query_umedida = mysqli_query($mysqli, "SELECT id_u_medida, u_descrip FROM u_medida ORDER BY u_descrip ASC");
                            while ($row = mysqli_fetch_assoc($query_umedida)) {
                                echo "<option value='" . $row['id_u_medida'] . "'>" . $row['u_descrip'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_impuesto" class="form-label">Tipo de Impuesto</label>
                        <select class="form-select" id="tipo_impuesto" name="tipo_impuesto" required>
                            <option value="">Seleccione tipo de impuesto</option>
                            <option value="10%">10</option>
                            <option value="5%">5</option>
                            <option value="EXENTAS">Exenta</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="guardarNuevoProducto()">Guardar Producto</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>