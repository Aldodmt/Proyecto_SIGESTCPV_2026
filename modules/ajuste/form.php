<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once "config/database.php";

$id_usuario = $_SESSION['id_user'] ?? null;
if (!$id_usuario) {
    die("No se detectó usuario logueado.");
}

if ($_GET['form'] == 'add') { ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=ajuste">Ajustes</a></li>
                        <li class="breadcrumb-item active">Agregar</li>
                    </ol>
                </div>
            </div>
            <div class="col-sm-6">
                <h1><i class="bi bi-pencil-square me-1"></i> Agregar un ajuste</h1>
            </div>
            <div class="card">
                <div class="card-header"><strong>Formulario de Ajustes</strong></div>
                <form id="formAjuste" autocomplete="off">
                    <div class="card-body">
                        <?php
                        // Generar código único
                        $query_id = mysqli_query($mysqli, "SELECT MAX(id_ajuste) as id FROM ajuste_com")
                            or die("Error: " . mysqli_error($mysqli));
                        $data_id = mysqli_fetch_assoc($query_id);
                        $codigo = $data_id['id'] + 1 ?? 1;
                        ?>
                        <div class="row mb-3 row">
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
                        </div>
                        <br>
                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Producto</label>
                            <div class="col-md-10">
                                <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                    data-bs-target="#myModal">
                                    <i class="bi bi-plus-lg"></i> Agregar Producto
                                </button>
                            </div>
                        </div>
                        <br>
                        <div id="resultados" class="mt-3"></div>
                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label">Motivo</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="motivo">
                            </div>
                        </div>
                        <br>

                    </div>
                    <input type="hidden" id="productos_json" name="productos">
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
                        <a href="?module=ajuste" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php }

// DETALLE DE UN AJUSTE (solo lectura): el listado muestra únicamente la cabecera; los productos se ven aquí.
if (($_GET['form'] ?? '') == 'detalle') {
    require_once __DIR__ . '/../../config/ui.php';
    $id_ajuste = intval($_GET['id_ajuste'] ?? 0);

    $stmt = $mysqli->prepare("SELECT a.id_ajuste, a.fecha_ajuste, a.motivo, a.estado, u.name_user
                                FROM ajuste_com a JOIN usuarios u ON u.id_user = a.id_user WHERE a.id_ajuste = ?");
    $stmt->bind_param('i', $id_ajuste);
    $stmt->execute();
    $aj = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$aj) {
        echo "<div class='alert alert-warning'>El ajuste solicitado no existe. <a href='?module=ajuste'>Volver</a></div>";
        return;
    }

    $stmt = $mysqli->prepare("SELECT d.cod_producto, p.p_descrip, um.u_descrip, d.cantidad_anterior, d.cantidad_ajustada
                                FROM det_ajuste d
                                JOIN producto p ON p.cod_producto = d.cod_producto
                                JOIN u_medida um ON um.id_u_medida = p.id_u_medida
                               WHERE d.id_ajuste = ? ORDER BY d.cod_producto");
    $stmt->bind_param('i', $id_ajuste);
    $stmt->execute();
    $detalle = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    ?>
    <div class="container-fluid">
        <div class="fade-in">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="breadcrumb-item"><a href="?module=ajuste">Ajustes</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </div>
            </div>
            <div class="col-sm-6">
                <h1><i class="bi bi-eye me-1"></i> Detalles del ajuste</h1>
            </div>
            <div class="card">
                <div class="card-header"><strong>Ajuste de stock N° <?= (int) $aj['id_ajuste'] ?></strong></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-md-2 col-form-label">Fecha</label>
                        <div class="col-md-2"><input type="text" class="form-control" value="<?= h($aj['fecha_ajuste']) ?>" readonly></div>
                        <label class="col-md-2 col-form-label">Usuario</label>
                        <div class="col-md-2"><input type="text" class="form-control" value="<?= h($aj['name_user']) ?>" readonly></div>
                        <label class="col-md-2 col-form-label">Estado</label>
                        <div class="col-md-2 pt-2"><?php require_once __DIR__ . '/../../config/compras.php'; echo flujo_estado_badge((string) $aj['estado']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-md-2 col-form-label">Motivo</label>
                        <div class="col-md-10"><input type="text" class="form-control" value="<?= h($aj['motivo']) ?>" readonly></div>
                    </div>
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Código</th><th>Producto</th><th>Unidad</th>
                                <th class="text-end">Cantidad anterior</th><th class="text-end">Cantidad ajustada</th><th class="text-end">Cantidad final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $d) { ?>
                                <tr>
                                    <td><?= (int) $d['cod_producto'] ?></td>
                                    <td><?= h($d['p_descrip']) ?></td>
                                    <td><?= h($d['u_descrip']) ?></td>
                                    <td class="text-end"><?= (int) $d['cantidad_anterior'] ?></td>
                                    <td class="text-end"><?= (int) $d['cantidad_ajustada'] ?></td>
                                    <td class="text-end"><?= (int) $d['cantidad_anterior'] - (int) $d['cantidad_ajustada'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="?module=ajuste" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Salir</a>
                </div>
            </div>
        </div>
    </div>
    <?php
    return;   // el detalle es de solo lectura: no hace falta el script del formulario de alta
}
?>


<script>
    $(document).ready(function () {
        load(1);
    });

    function load(page) {
        var x = $("#x").val();
        var parametros = { "action": "ajax", "page": page, "x": x };
        $("#loader").fadeIn('slow');
        $.ajax({
            url: './ajax/productos_ajuste.php',
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

    let productos = []; // Array de productos agregados

    function agregar(id) {
        id = parseInt(id); // Aseguramos que sea número
        let cantidad = parseFloat($('#cantidad_a' + id).val());
        if (isNaN(cantidad) || cantidad <= 0) {
            alert('Cantidad inválida');
            $('#cantidad_a' + id).focus();
            return;
        }

        // Evitar duplicados
        if (productos.some(p => p.id === id)) {
            alert('Producto ya agregado');
            return;
        }

        $.post('./ajax/agregar_ajuste.php', { id_producto: id, cantidad_a: cantidad }, function (fila) {
            // Si no hay tabla, crearla
            if ($('#resultados table').length === 0) {
                $('#resultados').html('<table class="table table-bordered"><thead><tr><th>Código</th><th>Tipo</th><th>Unidad</th><th>Producto</th><th>Cant. Anterior</th><th>Cant. Ajuste</th><th>Cant. Final</th><th>Acción</th></tr></thead><tbody></tbody></table>');
            }

            $('#resultados tbody').append(fila);

            // Guardar en array
            productos.push({ id: id, cantidad: cantidad });
        });
    }

    function eliminarProducto(id) {
        id = parseInt(id); // Normalizar
        // Eliminar del array primero
        productos = productos.filter(p => p.id !== id);

        // Eliminar del DOM
        $('#fila_' + id).fadeOut(300, function () {
            $(this).remove();

            // Si ya no hay filas, borrar tabla completa
            if ($('#resultados tbody tr').length === 0) {
                $('#resultados').html('');
            }
        });
    }

    $('#btnGuardar').off('click').on('click', function (e) {
        e.preventDefault();

        // Validaciones
        if (productos.length === 0) {
            alert("Agrega al menos un producto.");
            return;
        }

        let motivo = $('input[name="motivo"]').val().trim();
        if (motivo === "") {
            alert("Ingresa el motivo del ajuste.");
            $('input[name="motivo"]').focus();
            return;
        }

        // Convertir array de productos a JSON
        $('#productos_json').val(JSON.stringify(productos));

        let formData = $('#formAjuste').serialize();

        $.post("modules/ajuste/proses.php?act=insert", formData, function (response) {
            // Como tu proses.php redirige con header, podemos simplemente recargar
            window.location.href = "?module=ajuste";
        }).fail(function (xhr, status, error) {
            console.error("Error AJAX:", status, error, xhr.responseText);
            alert("Error en la solicitud AJAX.");
        });
    });

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
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary" onclick="load(1)">
                                <i class="bi bi-search"></i> Buscar
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