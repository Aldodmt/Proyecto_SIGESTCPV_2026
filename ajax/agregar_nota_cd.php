<?php
require_once '../config/database.php';

if (!isset($_POST['cod_compra']))
    exit;

$cod_compra = intval($_POST['cod_compra']);

// Traer productos de la orden de compra seleccionada
$sql = mysqli_query($mysqli, "
    SELECT  com.cod_compra, com.cod_proveedor, prov.razon_social, prov.ruc, com.fac_numero,
    det.cod_producto, pro.p_descrip, det.precio, det.cantidad, det.tipo_iva, det.exentas, det.iva5, det.iva10, 
    com.timbrado_nro
    FROM compra com
    JOIN detalle_compra det ON com.cod_compra = det.cod_compra
    JOIN producto pro ON det.cod_producto = pro.cod_producto
    JOIN proveedor prov ON com.cod_proveedor = prov.cod_proveedor
    WHERE com.cod_compra = $cod_compra;
");

if (mysqli_num_rows($sql) > 0) {
    $cod_proveedor = null;
    $razon_social = null;
    $ruc = null;
    $factura_num = null;
    $timbrado_num = null;

    $productos_json = []; // Array para enviar productos a JS

    ?>
    <table class="table table-striped table-hover align-middle">
        <thead class="table-primary">
            <tr>
                <th>Codigo Compra</th>
                <th>Nro. Factura</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Tipo IVA</th>
                <th>Cantidad a ajustar</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            while ($row = mysqli_fetch_assoc($sql)) {
                if ($cod_proveedor === null) {
                    $cod_proveedor = $row['cod_proveedor'];
                    $razon_social = $row['razon_social'];
                    $ruc = $row['ruc'];
                    $factura_num = $row['fac_numero'];
                    $timbrado_num = $row['timbrado_nro'];
                }

                // Por defecto, cada producto comienza con tipo_iva "EXENTA"
                //$tipo_iva_default = "EXENTA";
        
                $productos_json[] = [
                    'cod_producto' => $row['cod_producto'],
                    'cantidad' => $row['cantidad'],
                    'precio_unitario' => $row['precio'],
                    'tipo_iva' => $row['tipo_iva']
                ];
                ?>
                <tr id="fila_<?= $row['cod_producto'] ?>" data-cod="<?= $row['cod_producto'] ?>">
                    <td><?= $row['cod_compra'] ?></td>
                    <td><?= $row['fac_numero'] ?></td>
                    <td><?= $row['p_descrip'] ?></td>
                    <td class="cantidad"><?= $row['cantidad'] ?></td>
                    <td class="precio_unit"><?= $row['precio'] ?></td>
                    <td><?= $row['cantidad'] * $row['precio'] ?></td>
                    <td class="tipo_iva"><?= $row['tipo_iva'] ?></td>
                    <td>
                        <input type="number" min="0" class="form-control form-control-sm text-center"
                            name="ajuste_cant_nota[<?= $row['cod_producto'] ?>]" placeholder="0">
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="eliminarCompra('<?= $row['cod_producto'] ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
                $total += $row['cantidad'] * $row['precio'];
            }
            ?>
        </tbody>
    </table>
    <div class="d-flex justify-content-end align-items-center mt-2">
        <label class="me-2 mb-0"><strong>Total:</strong></label>
        <input type="text" class="form-control" name="total" value="<?= $total ?>" readonly style="width: 120px;">
    </div>

    <?php
    // Traer el timbrado activo del proveedor (solo 1)
    /*$sql_timbrado = mysqli_query($mysqli, "
        SELECT * FROM timbrado_compra
        WHERE cod_proveedor = $cod_proveedor
        AND tim_estado = 'ACTIVO'
        ORDER BY tim_cod DESC
        LIMIT 1
    ");

    if (mysqli_num_rows($sql_timbrado) > 0) {
        $timbrado = mysqli_fetch_assoc($sql_timbrado);
        $tim_numero = $timbrado['tim_numero'];
        $tim_fecha_ini = $timbrado['tim_fecha_ini'];
        $tim_fecha_fin = $timbrado['tim_fecha_fin'];
    } else {
        $tim_numero = '';
        $tim_fecha_ini = '';
        $tim_fecha_fin = '';
    }*/
    ?>

    <script>
        // Sobrescribir o declarar solo si no existe
        if (typeof datosProveedor !== 'undefined') {
            // Solo sobrescribir valores existentes
            datosProveedor.productos = [];
            datosProveedor.cod_proveedor = <?= json_encode($cod_proveedor) ?>;
            datosProveedor.razon_social = <?= json_encode($razon_social) ?>;
            datosProveedor.ruc = <?= json_encode($ruc) ?>;
            datosProveedor.timbrado_num = <?= json_encode($timbrado_num) ?>;
            datosProveedor.factura_num = <?= json_encode($factura_num) ?>;
            datosProveedor.productos = <?= json_encode($productos_json) ?>;
        } else {
            // Declarar por primera vez
            var datosProveedor = {
                cod_proveedor: <?= json_encode($cod_proveedor) ?>,
                razon_social: <?= json_encode($razon_social) ?>,
                ruc: <?= json_encode($ruc) ?>,
                timbrado_num: <?= json_encode($timbrado_num) ?>,
                factura_num: <?= json_encode($factura_num) ?>,
                productos: <?= json_encode($productos_json) ?>
            };
        }

        console.log("Datos enviados a form.php:", datosProveedor);
        console.log("Contenido de productos_json:", <?= json_encode($productos_json, JSON_PRETTY_PRINT) ?>);

        // Limpiar productos antes de agregar nuevos
        function resetDatosProveedor() {
            datosProveedor.productos = [];
        }

        // Función para actualizar el tipo_iva cuando el usuario cambia el select
        /*function actualizarTipoIVA(cod_producto, valor) {
            const producto = datosProveedor.productos.find(p => p.cod_producto == cod_producto);
            if (producto) producto.tipo_iva = valor;
        }*/
    </script>

    <?php
} else {
    echo "<div class='alert alert-warning'>No hay productos en este pedido.</div>";
}
?>