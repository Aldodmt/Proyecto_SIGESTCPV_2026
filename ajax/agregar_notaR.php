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

    $productos_json = []; // Array para enviar productos a JS

    ?>
    <table class="table table-striped table-hover align-middle">
        <thead class="table-primary">
            <tr>
                <th>Codigo Compra</th>
                <th>Codigo Producto</th>
                <th>Producto</th>
                <th>Cantidad</th>
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
                }

                // Por defecto, cada producto comienza con tipo_iva "EXENTA"
                //$tipo_iva_default = "EXENTA";
        
                $productos_json[] = [
                    'cod_producto' => $row['cod_producto'],
                    'cantidad' => $row['cantidad']
                ];
                ?>
                <tr id="fila_<?= $row['cod_producto'] ?>" data-cod="<?= $row['cod_producto'] ?>">
                    <td><?= $row['cod_compra'] ?></td>
                    <td><?= $row['cod_producto'] ?></td>
                    <td><?= $row['p_descrip'] ?></td>
                    <td class="cantidad"><?= $row['cantidad'] ?></td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="eliminarCompra('<?= $row['cod_producto'] ?>')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

    <script>
        // Sobrescribir o declarar solo si no existe
        if (typeof datosProveedor !== 'undefined') {
            // Solo sobrescribir valores existentes
            datosProveedor.productos = [];
            datosProveedor.cod_proveedor = <?= json_encode($cod_proveedor) ?>;
            datosProveedor.razon_social = <?= json_encode($razon_social) ?>;
            datosProveedor.ruc = <?= json_encode($ruc) ?>;
            datosProveedor.productos = <?= json_encode($productos_json) ?>;
        } else {
            // Declarar por primera vez
            var datosProveedor = {
                cod_proveedor: <?= json_encode($cod_proveedor) ?>,
                razon_social: <?= json_encode($razon_social) ?>,
                ruc: <?= json_encode($ruc) ?>,
                productos: <?= json_encode($productos_json) ?>
            };
        }

        console.log("Datos enviados a form.php:", datosProveedor);
        console.log("Contenido de productos_json:", <?= json_encode($productos_json, JSON_PRETTY_PRINT) ?>);

        // Limpiar productos antes de agregar nuevos
        function resetDatosProveedor() {
            datosProveedor.productos = [];
        }
    </script>

    <?php
} else {
    echo "<div class='alert alert-warning'>No hay productos en este pedido.</div>";
}
?>