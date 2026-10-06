<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<script>console.log('form.php session_id: " . session_id() . "');</script>";

require_once "config/database.php";

$id_usuario = $_SESSION['id_user'] ?? null;

if (!$id_usuario) {
    die("No se detectó usuario logueado."); // O redirigir a login
}


if ($_GET['form'] == 'add') {
    $session_id = session_id();

    // Verificar si hay pedido BORRADOR del usuario
    $query_borrador = mysqli_query($mysqli, "SELECT id_pedido FROM pedido WHERE id_user=$id_usuario AND estado='BORRADOR'");

    if (mysqli_num_rows($query_borrador) > 0) {
        $pedido = mysqli_fetch_assoc($query_borrador);
        $id_pedido = $pedido['id_pedido'];
    } else {
        mysqli_query($mysqli, "INSERT INTO pedido (fecha, hora, estado, id_user) VALUES (CURDATE(), CURTIME(), 'BORRADOR', $id_usuario)");
        $id_pedido = mysqli_insert_id($mysqli);
    }

    // Luego usas $id_pedido como tu código
    $codigo = $id_pedido;
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");
    $estado = 'BORRADOR';
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=pedido">Pedidos</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>
            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Agregar Pedido</h1>
            </div>
            <div class="card">
                <div class="card-header"><strong>Formulario de Pedidos</strong></div>
                <form class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <!--<?php
                        // Generar código único
                        /*$query_id = mysqli_query($mysqli, "SELECT MAX(id_pedido) as id FROM pedido")
                            or die("Error: " . mysqli_error($mysqli));
                        $data_id = mysqli_fetch_assoc($query_id);
                        $codigo = $data_id['id'] + 1 ?? 1;*/
                        ?>-->
                        <div class="form-group row">
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

                        <!-- Estado del pedido -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado del pedido: <strong>BORRADOR</strong>
                        </div>
                        <input type="hidden" id="estado_pedido" name="estado" value="BORRADOR">
                        <br>
                        <!-- Botón Agregar Productos -->
                        <div class="form-group">
                            <label class="col-md-2 col-form-label">Producto</label>
                            <div class="col-md-10">
                                <?php if ($estado !== 'CONFIRMADO' && $estado !== 'ANULADO' && $estado !== 'PENDIENTE') { ?>
                                    <button type="button" class="btn btn-info" data-coreui-toggle="modal"
                                        data-coreui-target="#myModal">
                                        <i class="fa fa-plus"></i> Agregar Productos
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                        <!-- Tabla de productos -->
                        <div id="resultados" class="mt-3">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <?php if ($estado !== 'CONFIRMADO' && $estado !== 'ANULADO')
                                            echo '<th>Acciones</th>'; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_detalle = mysqli_query($mysqli, "
                                    SELECT dp.*, p.p_descrip, u.u_descrip
                                    FROM det_pedido dp
                                    JOIN producto p ON dp.cod_producto = p.cod_producto
                                    JOIN u_medida u ON p.id_u_medida = u.id_u_medida
                                    WHERE dp.id_pedido = $codigo
                                ");
                                    while ($prod = mysqli_fetch_assoc($sql_detalle)) {
                                        echo "<tr>
                                        <td>{$prod['cod_producto']}</td>
                                        <td>{$prod['p_descrip']}</td>
                                        <td>{$prod['cantidad']}</td>
                                        <td>{$prod['u_descrip']}</td>";
                                        if ($estado !== 'CONFIRMADO' && $estado !== 'ANULADO' && $estado !== 'PENDIENTE') {
                                            echo "<td><button class='btn btn-danger btn-sm' onclick='eliminar({$prod['cod_producto']}, {$codigo})'>Eliminar</button></td>";
                                        } else {
                                            echo "<td></td>"; // para mantener la columna vacía si no hay botón
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar" disabled>
                                <i class="fas fa-check"></i> Confirmar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnModificar" disabled>
                                <i class="fas fa-edit"></i> Modificar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=pedido" class="btn btn-secondary" id="btnSalir">
                                <i class="fas fa-arrow-left"></i> Salir
                            </a>
                        </div>
                    </div>
                </form>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            </div>
        </div>
    </div>
<?php }
if ($_GET['form'] == 'detalle') {
    $session_id = session_id();
    // Consultar datos del pedido
    $id_pedido = intval($_GET['id_pedido']);
    $query_pedido = mysqli_query($mysqli, "
        SELECT p.* 
        FROM pedido p
        WHERE p.id_pedido = $id_pedido
    ") or die("Error: " . mysqli_error($mysqli));

    $pedido = mysqli_fetch_assoc($query_pedido);

    // Guardamos los datos en variables
    $codigo = $pedido['id_pedido'];
    $fecha = $pedido['fecha'];
    $hora = $pedido['hora'];
    $estado = $pedido['estado'];
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="cil-home"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=pedido">Pedidos</a></li>
                        <li class="breadcrumb-item active">Detalles del Pedido</li>
                    </ol>
                </div>
            </div>
            <div class="col-sm-6">
                <h1><i class="fa fa-edit icon-title"></i> Detalles del Pedido</h1>
            </div>
            <div class="card">
                <div class="card-header"><strong>Formulario de Pedidos</strong></div>
                <form id="formPedido" class="form-horizontal" method="POST" autocomplete="off">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Código</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="codigo" value="<?= $codigo ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Fecha</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="fecha" value="<?= $fecha ?>" readonly>
                            </div>
                            <label class="col-md-2 col-form-label">Hora</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="hora" value="<?= $hora ?>" readonly>
                            </div>
                        </div>

                        <!-- Estado del pedido -->
                        <div class="alert alert-info mt-3" id="estadoActual">
                            Estado del pedido: <strong><?= strtoupper($estado) ?></strong>
                        </div>
                        <input type="hidden" id="estado_pedido" name="estado" value="<?= $estado ?>">

                        <br>
                        <!-- Tabla de productos -->
                        <div class="form-group">
                            <label class="col-md-2 col-form-label">Producto</label>
                            <div class="col-md-10">
                                <?php if ($estado !== 'CONFIRMADO' && $estado !== 'ANULADO' && $estado !== 'PENDIENTE') { ?>
                                    <button type="button" class="btn btn-info" data-coreui-toggle="modal"
                                        data-coreui-target="#myModal">
                                        <i class="fa fa-plus"></i> Agregar Productos
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                        <div id="resultados" class="mt-3">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Unidad</th>
                                        <?php if ($estado !== 'CONFIRMADO' && $estado !== 'ANULADO') { ?>
                                            <th>Acciones</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Productos ya guardados en det_pedido
                                    $sql_detalle = mysqli_query($mysqli, "
                                    SELECT dp.*, p.p_descrip, u.u_descrip
                                    FROM det_pedido dp
                                    JOIN producto p ON dp.cod_producto = p.cod_producto
                                    JOIN u_medida u ON p.id_u_medida = u.id_u_medida
                                    WHERE dp.id_pedido = $codigo
                                ");

                                    while ($prod = mysqli_fetch_assoc($sql_detalle)) {
                                        echo "<tr>
                                        <td>{$prod['cod_producto']}</td>
                                        <td>{$prod['p_descrip']}</td>
                                        <td>{$prod['cantidad']}</td>
                                        <td>{$prod['u_descrip']}</td>";
                                        if ($estado !== 'CONFIRMADO' && $estado !== 'ANULADO' && $estado !== 'PENDIENTE') {
                                            echo "<td>
                                            <button class='btn btn-danger btn-sm' onclick='eliminar({$prod['cod_producto']}, {$codigo})'>Eliminar</button>
                                        </td>";
                                        } else {
                                            echo "<td></td>"; // Mantener la celda vacía si no hay botón
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Botones -->
                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnConfirmar" disabled>
                                <i class="fas fa-check"></i> Confirmar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnModificar" disabled>
                                <i class="fas fa-edit"></i> Modificar
                            </button>
                            <button type="button" class="btn btn-danger" id="btnAnular" disabled>
                                <i class="fas fa-times"></i> Anular
                            </button>
                            <a href="?module=pedido" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Salir
                            </a>
                        </div>
                    </div>
                </form>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            </div>
        </div>
    </div>
<?php } ?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Gestión de pedidos -->
<script>

    $(document).ready(function () {
        // --- FUNCION GENÉRICA ---
        function actualizarEstado(act) {
            let codigo = $('input[name="codigo"]').val();

            $.post("./modules/pedido/proses.php", { act: act, codigo: codigo }, function (response) {
                console.log("Respuesta:", response);

                if (response.success) {
                    alert(response.message);

                    // Esperar un poquito y refrescar la página
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else {
                    alert("Error: " + response.message);
                }
            }, "json")
                .fail(function () {
                    alert("Error en la solicitud AJAX.");
                });
        }

        // --- BOTONES ---
        $('#btnConfirmar').off('click').on('click', function (e) {
            e.preventDefault();
            actualizarEstado('confirm');
        });

        $('#btnModificar').off('click').on('click', function (e) {
            e.preventDefault();
            actualizarEstado('modify');
        });

        $('#btnAnular').off('click').on('click', function (e) {
            e.preventDefault();
            actualizarEstado('cancel');
        });

        //manejo de la habilitacion de los botones
        let estado = $('#estado_pedido').val();

        switch (estado) {
            case 'BORRADOR':
                $('#btnGuardar').prop('disabled', false);
                $('#btnConfirmar, #btnModificar, #btnAnular').prop('disabled', true);
                break;

            case 'PENDIENTE':
                $('#btnGuardar').prop('disabled', true);
                $('#btnConfirmar').prop('disabled', false);
                $('#btnModificar').prop('disabled', false);
                $('#btnAnular').prop('disabled', true);
                break;

            case 'CONFIRMADO':
                $('#btnGuardar, #btnConfirmar').prop('disabled', true);
                $('#btnModificar').prop('disabled', true);
                $('#btnAnular').prop('disabled', false);
                break;

            case 'MODIFICANDO':
                $('#btnGuardar').prop('disabled', false);
                $('#btnModificar').prop('disabled', true);
                $('#btnConfirmar').prop('disabled', true);
                break;

            case 'ANULADO':
                $('#btnGuardar, #btnConfirmar, #btnModificar, #btnAnular').prop('disabled', true);
                break;

            default: // Si por error no hay estado
                $('#btnGuardar').prop('disabled', false);
                $('#btnConfirmar, #btnModificar, #btnAnular').prop('disabled', true);
        }
    });

    // GUARDAR
    $('#btnGuardar').off('click').on('click', function (e) {
        e.preventDefault();

        let codigo = $('input[name="codigo"]').val();

        $.post('./modules/pedido/proses.php', { act: 'save', codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado del pedido: <strong>' + response.estado + '</strong>');
                $('#estado_pedido').val(response.estado);
                window.location.href = "?module=pedido";
            } else {
                alert('Error: ' + response.message);
            }
        }, "json");
    });

    // SALIR
    $('#btnSalir').off('click').on('click', function (e) {
        e.preventDefault();
        $.post("./modules/pedido/proses.php", { act: 'clear_borrador' }, function (response) {
            if (response.success) {
                window.location.href = "?module=pedido";
            } else {
                alert('Error al limpiar borradores: ' + response.message);
            }
        }, "json");
    });

    // CONFIRMAR
    $('#btnConfirmar').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();
        $.post("./modules/pedido/proses.php", { act: 'confirm', codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado del pedido: <strong>' + response.estado + '</strong>');
                $('#estado_pedido').val(response.estado);
            } else {
                alert('Error: ' + response.message);
            }
        }, "json");
    });

    // MODIFICAR
    $('#btnModificar').off('click').on('click', function () {
        let codigo = $('input[name="codigo"]').val();
        $.post("./modules/pedido/proses.php", { act: 'modify', codigo }, function (response) {
            if (response.success) {
                alert(response.message);
                $('#estadoActual').html('Estado del pedido: <strong>' + response.estado + '</strong>');
                $('#estado_pedido').val(response.estado);
            } else {
                alert('Error: ' + response.message);
            }
        }, "json");
    });

    // ANULAR
    $(document).ready(function () {
        $('#btnAnular').off('click').on('click', function (e) {
            e.preventDefault(); // prevenir cualquier acción previa

            let codigo = $('input[name="codigo"]').val();

            // MOSTRAR CONFIRMACIÓN ANTES DE ENVIAR
            if (!confirm("¿Estás seguro que deseas anular este pedido? Esta acción no se puede deshacer.")) {
                return; // el usuario canceló
            }

            // SOLO SI CONFIRMA, enviamos al backend
            $.post("./modules/pedido/proses.php", { act: 'cancel', codigo: codigo, confirm: true }, function (response) {
                if (response.success) {
                    alert(response.message);
                    $('#estadoActual').html('Estado del pedido: <strong>' + response.estado + '</strong>');
                    $('#estado_pedido').val(response.estado);
                } else {
                    alert('Error: ' + response.message);
                }
            }, "json");
        });
    });
    // --- Limpiar borradores al recargar/cerrar la página ---
    /*window.addEventListener('beforeunload', function (e) {
        const data = new FormData();
        data.append('act', 'clear_borrador');
        navigator.sendBeacon("./modules/pedido/proses.php", data);
    });*/
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
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
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
                                <i class="cil-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" data-coreui-toggle="modal"
                                data-coreui-target="#modalNuevoProducto">
                                <i class="fa fa-plus"></i> Añadir Producto Nuevo
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
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoProducto">
                    <div class="mb-3">
                        <label for="nombre_prod" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="nombre_prod" name="nombre_prod" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_producto" class="form-label">Tipo de Producto</label>
                        <select class="form-control" id="tipo_producto" name="tipo_producto" required>
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
                        <select class="form-control" id="unidad_medida" name="unidad_medida" required>
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
                        <select class="form-control" id="tipo_impuesto" name="tipo_impuesto" required>
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
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>