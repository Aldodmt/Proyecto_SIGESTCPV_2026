<?php
require 'config/database.php';
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    echo "<meta http-equiv='refresh' content='0; url=index.php?alert=3'>";
} else {
    if ($_GET['module'] == 'start') {
        include 'modules/start/view.php';

    } elseif ($_GET['module'] == 'password') {
        include "modules/password/view.php";

    } elseif ($_GET['module'] == 'user') {
        include "modules/user/view.php";

    } elseif ($_GET['module'] == 'form_user') {
        include "modules/user/form.php";

    } elseif ($_GET['module'] == 'perfil') {
        include "modules/perfil/view.php";

    } elseif ($_GET['module'] == 'form_perfil') {
        include "modules/perfil/form.php";

    } elseif ($_GET['module'] == 'departamento') {
        include "modules/departamento/view.php";

    } elseif ($_GET['module'] == 'form_departamento') {
        include "modules/departamento/form.php";

    } elseif ($_GET['module'] == 'ciudad') {
        include "modules/ciudad/view.php";

    } elseif ($_GET['module'] == 'form_ciudad') {
        include "modules/ciudad/form.php";

    } elseif ($_GET['module'] == 'clientes') {
        include "modules/clientes/view.php";

    } elseif ($_GET['module'] == 'form_clientes') {
        include "modules/clientes/form.php";

    } elseif ($_GET['module'] == 'compra') {
        include "modules/compras/view.php";

    } elseif ($_GET['module'] == 'form_compra') {
        include "modules/compras/form.php";

    } elseif ($_GET['module'] == 'stock') {
        include "modules/stock/view.php";

    } elseif ($_GET['module'] == 'ventas') {
        include "modules/ventas/view.php";

    } elseif ($_GET['module'] == 'form_ventas') {
        include "modules/ventas/form.php";

    } elseif ($_GET['module'] == 'deposito') {
        include "modules/deposito/view.php";

    } elseif ($_GET['module'] == 'form_deposito') {
        include "modules/deposito/form.php";

    } elseif ($_GET['module'] == 'tipo_producto') {
        include "modules/tipo_producto/view.php";

    } elseif ($_GET['module'] == 'form_tipo_producto') {
        include "modules/tipo_producto/form.php";

    } elseif ($_GET['module'] == 'u_medida') {
        include "modules/u_medida/view.php";

    } elseif ($_GET['module'] == 'form_u_medida') {
        include "modules/u_medida/form.php";

    } elseif ($_GET['module'] == 'producto') {
        include "modules/producto/view.php";

    } elseif ($_GET['module'] == 'form_producto') {
        include "modules/producto/form.php";

    } elseif ($_GET['module'] == 'proveedor') {
        include "modules/proveedor/view.php";

    } elseif ($_GET['module'] == 'form_proveedor') {
        include "modules/proveedor/form.php";

    } elseif ($_GET['module'] == 'pedido') {
        include "modules/pedido/view.php";

    } elseif ($_GET['module'] == 'form_pedido') {
        include "modules/pedido/form.php";

    } elseif ($_GET['module'] == 'presupuesto') {
        include "modules/presupuesto/view.php";

    } elseif ($_GET['module'] == 'form_presupuesto') {
        include "modules/presupuesto/form.php";

    } elseif ($_GET['module'] == 'orden_c') {
        include "modules/orden_c/view.php";

    } elseif ($_GET['module'] == 'form_orden_c') {
        include "modules/orden_c/form.php";

    } elseif ($_GET['module'] == 'cuenta') {
        include "modules/cuenta/view.php";

    } elseif ($_GET['module'] == 'nota_c_d') {
        include "modules/nota_c_d/view.php";

    } elseif ($_GET['module'] == 'form_nota_c_d') {
        include "modules/nota_c_d/form.php";

    } elseif ($_GET['module'] == 'ajuste') {
        include "modules/ajuste/view.php";

    } elseif ($_GET['module'] == 'form_ajuste') {
        include "modules/ajuste/form.php";

    } elseif ($_GET['module'] == 'nota_remision') {
        include "modules/nota_remision/view.php";

    } elseif ($_GET['module'] == 'form_remision') {
        include "modules/nota_remision/form.php";

    } elseif ($_GET['module'] == 'pedido_v') {
        include "modules/pedido_v/view.php";

    } elseif ($_GET['module'] == 'form_pedido_v') {
        include "modules/pedido_v/form.php";

    } elseif ($_GET['module'] == 'info_pedido') {
        include "modules/info_pedido/view.php";

    } elseif ($_GET['module'] == 'info_presu') {
        include "modules/info_presu/view.php";

    } elseif ($_GET['module'] == 'info_orden') {
        include "modules/info_orden/view.php";

    } elseif ($_GET['module'] == 'info_nota') {
        include "modules/info_nota/view.php";

    } elseif ($_GET['module'] == 'info_ajuste') {
        include "modules/info_ajuste/view.php";

    } elseif ($_GET['module'] == 'info_pedido_v') {
        include "modules/info_pedido_v/view.php";

    } elseif ($_GET['module'] == 'info_facturacion_compra') {
        include "modules/info_facturacion_compra/view.php";

    } elseif ($_GET['module'] == 'info_nota_cd') {
        include "modules/info_nota_cd/view.php";

    } elseif ($_GET['module'] == 'info_notaR') {
        include "modules/info_notaR/view.php";

    } elseif ($_GET['module'] == 'info_producto') {
        include "modules/info_producto/view.php";

    } elseif ($_GET['module'] == 'info_proveedores') {
        include "modules/info_proveedores/view.php";

    } elseif ($_GET['module'] == 'info_u_medida') {
        include "modules/info_u_medida/view.php";

    }


}
?>