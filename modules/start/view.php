<?php
// Los accesos de "Formulario de movimiento" (tarjetas de cada movimiento) están ocultos por ahora.
// Poner en true para volver a mostrarlos; mientras tanto se muestra _accesos_rapidos.php.
$mostrarMovimientos = false;
if ($_SESSION['permisos_acceso'] == 'Super Admin') { ?>
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="bi bi-house-door me-1"></i> Inicio
        </h1>
        <ol class="breadcrumb ms-auto">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i></a></li>
        </ol>
    </div>
    <section class="app-content">
        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <p style="font-size: 15px;">
                        <i class="bi bi-person"></i> Bienvenido/a <strong><?php echo $_SESSION['name_user']; ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <?php include __DIR__ . "/_accesos_rapidos.php"; ?>
        <?php if ($mostrarMovimientos) { ?>
        <h2>Formulario de movimiento</h2>
        <div class="row">
            <!-- bloque 1 pedido -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Pedidos</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Pedido</li>
                            <li>De Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=pedido" class="btn btn-light" title="Registrar pedidos" data-bs-toggle="tooltip"><i
                                class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 1 pedido -->

            <!-- bloque 2 presupuesto -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Presupuesto</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Presupuesto</li>
                            <li>De Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=presupuesto" class="btn btn-light" title="Registrar presupuesto"
                            data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 2 presupuesto -->

            <!-- bloque 3 orden de compra -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Orden de compra</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Orden de compra</li>
                            <li>De Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=orden_c" class="btn btn-light" title="Registrar ordenes de compra"
                            data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 3 orden de compra -->

            <!-- bloque 4 compras -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Compras</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Compra</li>
                            <li>Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=compra" class="btn btn-light" title="Registrar compras" data-bs-toggle="tooltip"><i
                                class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 4 compras -->

            <!-- bloque 5 cuentas a pagar -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Cuentas a pagar</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Cuentas a Pagar</li>
                            <li>De Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=cuenta" class="btn btn-light" title="Registrar cuentas" data-bs-toggle="tooltip"><i
                                class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 5 cuentas a pagar -->

            <!-- bloque 6 Nota de CD -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Nota credito o debito</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Nota credito o debito</li>
                            <li>De Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=nota_c_d" class="btn btn-light" title="Registrar notas" data-bs-toggle="tooltip"><i
                                class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 6 Nota de CD -->

            <!-- bloque 7 Ajuste  -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Ajuste de Stock</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Ajuste de stock</li>
                            <li>De Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=ajuste" class="btn btn-light" title="Registrar Ajustes" data-bs-toggle="tooltip"><i
                                class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 7 Ajuste  -->

            <!-- bloque 8 Nota de Remision-->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Nota de Remision</strong></h5>
                        <ul>
                            <li>Registrar</li>
                            <li>Nota de Remision</li>
                            <li>De Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=nota_remision" class="btn btn-light" title="Registrar Ajustes"
                            data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 8 Nota de Remision-->

            <!-- bloque 9 stock -->
            <div class="col-lg-4 col-6">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><strong>Stock de productos</strong></h5>
                        <ul>
                            <li>Visualizar</li>
                            <li>Stock</li>
                            <li>Productos</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="?module=stock" class="btn btn-light" title="Ver stock de productos"
                            data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                    </div>
                </div>
            </div>
            <!-- fin bloque 9 stock -->
        <?php } ?>
    </section>
    <?php
} elseif ($_SESSION['permisos_acceso'] == 'Compras') {

    // Definir módulos según usuario
    if ($_SESSION['name_user'] == 'UserCompra') {
        $modulos_permitidos = ['presupuesto', 'compra', 'nota_c_d', 'nota_remision', 'stock'];
    } else { // Jefa de Compra
        $modulos_permitidos = ['pedidos', 'presupuesto', 'orden_c', 'compra', 'cuenta', 'nota_c_d', 'ajuste', 'nota_remision', 'stock'];
    }
    ?>

    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="bi bi-house-door me-1"></i> Inicio
        </h1>
        <ol class="breadcrumb ms-auto">
            <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i></a></li>
        </ol>
    </div>
    <section class="app-content">
        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <p style="font-size: 15px;">
                        <i class="bi bi-person"></i> Bienvenido/a <strong><?php echo $_SESSION['name_user']; ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <?php include __DIR__ . "/_accesos_rapidos.php"; ?>
        <?php if ($mostrarMovimientos) { ?>
        <section class="app-content-header">
            <h2>Formulario de movimiento</h2>
            <div class="row">

                <?php if (in_array('pedidos', $modulos_permitidos)) { ?>
                    <!-- bloque 1 pedido -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-dark mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Pedidos</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Pedido</li>
                                    <li>De Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=pedido" class="btn btn-light" title="Registrar pedidos"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                    <!-- fin bloque 1 pedido -->
                <?php } ?>

                <?php if (in_array('presupuesto', $modulos_permitidos)) { ?>
                    <!-- bloque Presupuesto -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Presupuesto</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Presupuesto</li>
                                    <li>De Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=presupuesto" class="btn btn-light" title="Registrar presupuesto"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (in_array('orden_c', $modulos_permitidos)) { ?>
                    <!-- bloque Orden de Compra -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-dark mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Orden de compra</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Orden de compra</li>
                                    <li>De Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=orden_c" class="btn btn-light" title="Registrar ordenes de compra"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (in_array('compra', $modulos_permitidos)) { ?>
                    <!-- bloque Registro de Compra -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-dark mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Compras</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Compra</li>
                                    <li>Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=compra" class="btn btn-light" title="Registrar Compra" data-bs-toggle="tooltip"><i
                                        class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>



                <?php if (in_array('cuenta', $modulos_permitidos)) { ?>
                    <!-- bloque Cuentas a Pagar -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Cuentas a pagar</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Cuentas a Pagar</li>
                                    <li>De Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=cuenta" class="btn btn-light" title="Gestionar Cuentas"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (in_array('nota_c_d', $modulos_permitidos)) { ?>
                    <!-- bloque Nota de CD -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-dark mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Nota credito o debito</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Nota credito o debito</li>
                                    <li>De Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=nota_c_d" class="btn btn-light" title="Registrar notas de cd"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (in_array('ajuste', $modulos_permitidos)) { ?>
                    <!-- bloque Ajuste -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-dark mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Ajuste de inventario</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Ajuste de inventario</li>
                                    <li>De Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=ajuste" class="btn btn-light" title="Registrar Ajustes"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (in_array('nota_remision', $modulos_permitidos)) { ?>
                    <!-- bloque Nota de Remision -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Nota de Remision</strong></h5>
                                <ul>
                                    <li>Registrar</li>
                                    <li>Nota de Remision</li>
                                    <li>De Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=nota_remision" class="btn btn-light" title="Registrar Ajustes"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (in_array('stock', $modulos_permitidos)) { ?>
                    <!-- bloque Stock -->
                    <div class="col-lg-4 col-6">
                        <div class="card text-white bg-dark mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Stock de productos</strong></h5>
                                <ul>
                                    <li>Visualizar</li>
                                    <li>Stock</li>
                                    <li>Productos</li>
                                </ul>
                            </div>
                            <div class="card-footer">
                                <a href="?module=stock" class="btn btn-light" title="Ver stock de productos"
                                    data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </section>
        <?php } ?>

        <?php
} elseif ($_SESSION['permisos_acceso'] == 'Ventas') { ?>
        <section class="app-content-header">
            <h1>
                <i class="bi bi-house-door me-1"></i>Inicio
            </h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i></a></li>
            </ol>
        </section>
        <!--<section class="app-content">
            <div class="row">
                <div class="col-lg-12 col-12">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <p style="font-size: 15px;">
                            <i class="bi bi-person"></i> Bienvenido/a <strong><?php echo $_SESSION['name_user']; ?></strong>
                        </p>
                    </div>
                </div>
            </div> -->

        <!-- <h2>Formulario de movimiento</h2>
            <div class="row"> -->
        <!-- bloque Ventas -->
        <!-- <div class="col-lg-4 col-6">
                    <div class="card text-white bg-secondary mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><strong>Ventas</strong></h5>
                            <ul>
                                <li>Registrar</li>
                                <li>Ventas de</li>
                                <li>Productos</li>
                            </ul>
                        </div>
                        <div class="card-footer">
                            <a href="?module=compras" class="btn btn-light" title="Registrar compras"
                                data-bs-toggle="tooltip"><i class="bi bi-plus-lg"></i></a>
                        </div>
                    </div>
                </div>
-->
        <!-- fin bloque Ventas -->

        <!-- bloque Stock -->
        <div class="col-lg-4 col-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title"><strong>Stock de productos</strong></h5>
                    <ul>
                        <li>Visualizar</li>
                        <li>Stock</li>
                        <li>Productos</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="?module=stock" class="btn btn-light" title="Ver stock de productos" data-bs-toggle="tooltip"><i
                            class="bi bi-plus-lg"></i></a>
                </div>
            </div>
        </div>
        <!-- fin bloque Stock -->
        </div>
    </section>
<?php } ?>