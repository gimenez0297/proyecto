<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php'; ?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-10">
                        <ul class="nav nav-tabs" id="tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link nav-link-facturacion active" id="nav_1" data-toggle="tab" href="#tab_1" role="tab" aria-controls="tab_1" aria-selected="true">VENTA<sup class="ml-1">[F1]</sup></a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link nav-link-facturacion" id="nav_2" data-toggle="tab" href="#tab_2" role="tab" aria-controls="tab_2" aria-selected="false">BUSCAR PRODUCTOS<sup class="ml-1">[F2]</sup></a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link nav-link-facturacion" id="nav_3" data-toggle="tab" href="#tab_3" role="tab" aria-controls="tab_3" aria-selected="false">ÚLTIMAS COMPRAS<sup class="ml-1">[F3]</sup></a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="tabContent">
                    <!-- Tab 1 -->
                    <div class="tab-pane fade show active" id="tab_1" role="tabpanel" aria-labelledby="nav_1">

                        <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                            <input type="hidden" id="id_cliente" name="id_cliente">
                            <input type="hidden" id="id_delivery" name="id_delivery">
                            <input type="hidden" name="id_nota_credito" id="id_nota_credito">
                            <div class="row">
                                <div class="col-md-9 col-sm-12 pt-2">
                                    <h4>Datos del cliente</h4>
                                    <hr>

                                    <!-- CABECERA -->
                                    <div class="form-row">
                                        <div class="form-group-sm col-md-4 col-sm-12">
                                            <label for="ruc" class="label-required">R.U.C<sup class="ml-1">[F8]</sup></label>
                                            <div class="input-group">
                                                <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required tabindex="-1">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" id="btn_buscar_cliente" data-toggle="modal" data-target="#modal_clientes" title="Buscar Cliente" tabindex="-1"><i class="fa fa-search"></i><sup class="ml-1">[F6]</sup></button>
                                                    <button type="button" class="btn btn-info" id="btn_detalle_cliente" data-toggle="modal" data-target="#modal_clientes_new" title="Agregar Cliente" tabindex="-1"><i class="fa fa-plus"></i><sup class="ml-1">[F7]</sup></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group-sm col-md-6 col-sm-12 razon_social">
                                            <label for="razon_social" class="label-required">Cliente / Razón Social<sup class="ml-1">[F9]</sup></label>
                                            <select id="razon_social" name="razon_social" class="form-control" required></select>
                                        </div>
                                        <div class="form-group-sm col-md-2 col-sm-12">
                                            <label for="descuento_metodo_pago" class="label-required">Método Pago<sup class="ml-1">[F10]</sup></label>
                                            <select class="form-control" name="descuento_metodo_pago" id="descuento_metodo_pago" required></select>
                                        </div>
                                        <div class="form-group-sm col-md-2 col-sm-12 d-none descuento_entidad">
                                            <label for="descuento_entidad" class="label-required">Entidad</label>
                                            <select class="form-control" name="descuento_entidad" id="descuento_entidad" required></select>
                                        </div>
                                        <!-- <div class="form-group-sm col-md-2 col-sm-12">
                                            <label for="condicion" class="label-required">Condición</label>
                                            <select class="form-control" name="condicion" id="condicion" disabled="" required>
                                                <option value="1">Contado</option>
                                                <option value="2">Crédito</option>
                                            </select>
                                        </div> -->
                                        <!-- <div class="form-group-sm col-md-2 col-sm-12">
                                            <label for="vencimiento" class="label-required">Vencimiento</label>
                                            <input type="date" class="form-control input-sm" name="vencimiento" id="vencimiento" required>
                                        </div> -->
                                        <div class="form-group col-md-2 col-sm-12">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input checkshow" id="sin_nombre" name="sin_nombre" value="1">
                                                <label class="custom-control-label" for="sin_nombre"><span class="text-decoration-underline">S</span>in Nombre</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input checkshow" id="receta" name="receta" value="1">
                                                <label class="custom-control-label" for="receta"><span class="text-decoration-underline">R</span>eceta</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="delivery" name="delivery" value="1">
                                                <label class="custom-control-label" for="delivery"><span class="text-decoration-underline">D</span>elivery</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="courier" name="courier" value="1">
                                                <label class="custom-control-label" for="courier"><span class="text-decoration-underline">C</span>ourier</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- FIN CABECERA -->
                                </div>

                                <div class="col-md-3 col-sm-12 pt-2">
                                    <center>
                                        <h4>Total a pagar</h4>
                                    </center>
                                    <hr>
                                    <div class="card" style="background-color:transparent">
                                        <div class="card-body pad-total">
                                            <h2 class="text-center">Gs. <span class="total_venta">0</span></h2>
                                        </div>
                                    </div>
                                </div>

                                <!-- PRODUCTOS -->
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <h4>Productos</h4>
                                            <hr>

                                            <div class="form-row">
                                                <div class="col-md-12 col-sm-12">
                                                    <button type="button" class="btn btn-info acciones_tabla_detalle" id="btn_detalle_producto" title="Detalles Del Producto" disabled=""><i class="fa fa-eye"></i><sup class="ml-1">[F4]</sup></button>
                                                    <button type="button" class="btn btn-danger acciones_tabla_detalle" id="btn_eliminar" title="Eliminar Producto" disabled=""><i class="fa fa-times"></i><sup class="ml-1">[Supr]</sup></button>
                                                    <button type="button" class="btn btn-danger acciones_tabla_detalle" id="eliminar_todo" title="Eliminar Todo" disabled=""><i class="fa fa-trash"></i><sup class="ml-1">[Shift+Supr]</sup></button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- CAJA -->
                                <div class="col-md-2">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <h4>Caja</h4>
                                            <hr>
                                            <div class="form-row">
                                                <div class="col-md-12 col-sm-12">
                                                    <button type="button" class="btn btn-success" id="btn_extraer" data-toggle='modal' data-target='#modal-extraccion' title="Extraer Caja"><i class="fas fa-reply"></i><sup class="ml-1">[F11]</sup></button>
                                                    <button type="button" class="btn btn-primary" id="btn_abrir_caja" data-toggle="modal" data-target="#modal_caja" title="Abrir Caja"><i class="fas fa-cash-register"></i><sup class="ml-1">[F12]</sup></button>
                                                    <button type="button" class="btn btn-danger" id="btn_cerrar_caja" data-toggle="modal" data-target="#modal_caja" title="Cerrar Caja" style="display:none"><i class="fas fa-cash-register"></i><sup class="ml-1">[F12]</sup></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TABLA PRODUCTOS -->
                                <div class="form-group col-12 mt-2">
                                    <table id="tabla_detalle"></table>
                                </div>
                                <!-- FIN PRODUCTOS -->

                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-2 form-group-sm">
                                            <label for="detalle_puntos" class="label-sm">Puntos</label>
                                            <input class="form-control-sm input-sm readonly_white text-center" type="text" name="detalle_puntos" id="detalle_puntos" autocomplete="off" value="0" disabled>
                                        </div>
                                        <div class="col-md-8 form-group-sm">
                                            <label for="detalle_delivery" class="label-sm">Delivery</label>
                                            <input class="form-control-sm input-sm readonly_white upper" type="text" name="detalle_delivery" id="detalle_delivery" autocomplete="off" disabled>
                                        </div>
                                        <div class="col-md-2 form-group-sm">
                                            <button type="button" class="btn btn-lg btn-info btn-block" id="btn_guardar">Continuar<sup class="ml-1">[F5]</sup></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>

                    <!-- Tab 2 -->
                    <div class="tab-pane fade show" id="tab_2" role="tabpanel" aria-labelledby="nav_2">
                        <div class="row">
                            <div class="form-group col-12 mt-2">
                                <div id="toolbar_productos">
                                    <div class="form-inline" role="form">
                                        <div class="form-group mr-1">
                                            <input type="search" id="search" class="form-control" placeholder="Buscar" autocomplete="off">
                                        </div>
                                        <div class="form-group">
                                            <select id="filtro_principios_activos" class="form-control"></select>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-info ml-1 acciones_tabla_productos" id="btn_detalle_modal_productos" title="Detalles Del Producto" disabled=""><i class="fa fa-eye"></i><sup class="ml-1">[F6]</sup></button>
                                        </div>
                                    </div>
                                </div>
                                <table id="tabla_productos"></table>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3 -->
                    <div class="tab-pane fade show" id="tab_3" role="tabpanel" aria-labelledby="nav_3">
                        <div class="row">
                            <div class="form-group col-12 mt-2">
                                <div id="toolbar_productos_clientes">
                                    <div class="form-inline" role="form">
                                        <div class="form-group ml-1">
                                            <input type="hidden" id="desde">
                                            <input type="hidden" id="hasta">
                                            <div id="filtro_fecha" class="btn btn-default form-control">
                                                <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                                <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table id="tabla_productos_clientes"></table>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- MODAL SELEECIONAR CANTIDAD -->
                <div class="modal fade" id="modal_cantidad" role="dialog" aria-labelledby="modalCantidadLabel" tabindex="-1" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalCantidadLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="formulario_cantidad" method="post" enctype="multipart/form-data" action="">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <label for="lote" class="label-required">Lote</label>
                                                    <select class="form-control" name="lote" id="lote" required></select>
                                                </div>
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label for="cargar_entero">Entero</label>
                                                    <div class="input-group">
                                                        <input class="form-control input-sm upper" type="text" name="cargar_entero" id="cargar_entero" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1" onfocus="this.select()" required>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-primary" id="btn_cargar_entero" title="Cargar producto" tabindex="-1"><i class="fa fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6 col-sm-12">
                                                    <label for="cargar_fraccionado">Fracción</label>
                                                    <div class="input-group">
                                                        <input class="form-control input-sm upper" type="text" name="cargar_fraccionado" id="cargar_fraccionado" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1" onfocus="this.select()" required>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-primary" id="btn_cargar_fraccionado" title="Cargar producto fraccionado" tabindex="-1"><i class="fa fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL IMPRIMIR FACTURA -->
                <div class="modal fade" id="modal_cobros" role="dialog" aria-labelledby="modalCobrosLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modal_imprimirLabel">Cobro e Imprimir Factura</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="form default-validation" id="formulario_cobro" method="post" enctype="multipart/form-data" action="">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-12 col-xs-12 text-center">
                                            <label>Total</label>
                                            <p style="font-size:32px;line-height:40px">Gs. <span id="total_venta">0</span></p>
                                            <hr>
                                        </div>
                                        <div class="col-md-6 col-sm-12 col-xs-12 text-center">
                                            <label>Saldo</label>
                                            <p style="font-size:32px;line-height:40px">Gs. <span id="saldo_venta">0</span></p>
                                            <hr>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="form-row pt-2 mb-3">
                                                <div class="form-group-sm col-md-3">
                                                    <label for="metodo_pago" class="label-required">Método<sup class="ml-1">[F1]</sup></label>
                                                    <select id="metodo_pago" name="metodo_pago" class="form-control input-sm" required></select>
                                                    <span id="detalle_descuento" style="display:block;text-align: center;font-weight: bolder;" class="d-none">Descuento <span id="porcentaje">0</span>%</span>
                                                </div>
                                                <div class="form-group-sm col-md-3 entidad d-none">
                                                    <label for="entidad" class="label-required">Entidad<sup class="ml-1">[F2]</sup></label>
                                                    <select id="entidad" name="entidad" class="form-control input-sm" required></select>
                                                    <span id="detalle_descuento_entidad" style="display:block;text-align: center;font-weight: bolder;" class="d-none">Descuento <span id="porcentaje_entidad">0</span>%</span>
                                                </div>
                                                <div class="form-group-sm col-md-2">
                                                    <label for="monto" class="label-required">Monto<sup class="ml-1">[F3]</sup></label>
                                                    <input type="text" class="form-control input-sm text-right" id="monto" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                </div>
                                                <div class="form-group-sm col-md-6 detalles">
                                                    <label for="detalles">Detalles<sup class="ml-1">[F4]</sup></label>
                                                    <input type="text" class="form-control input-sm" name="detalles" id="detalles" autocomplete="off">
                                                </div>
                                                <div class="form-group-sm col-md-6 col-sm-6 nota_credito">
                                                    <label for="nro_nota_credito" class="label-required">N° Nota Crédito<sup class="ml-1">[F4]</sup></label>
                                                    <input type="text" class="form-control input-sm" name="nro_nota_credito" id="nro_nota_credito" autocomplete="off" placeholder="000-000-0000000" maxlength=20 required>
                                                </div>
                                                <div class="form-group-sm col-md-3 puntos">
                                                    <label for="puntos" class="label-required">Puntos</label>
                                                    <input type="text" class="form-control input-sm" name="puntos" id="puntos" autocomplete="off" readonly>
                                                </div>
                                                <div class="form-group-sm col-md-3 puntos">
                                                    <label for="puntos_disponibles" class="label-required">Puntos Disponibles</label>
                                                    <input type="text" class="form-control input-sm" name="puntos_disponibles" id="puntos_disponibles" autocomplete="off" readonly>
                                                </div>
                                                <div class="form-group-sm col-md-1 col-sm-6">
                                                    <label>&nbsp;</label>
                                                    <button id="btn_agregar" type="submit" class="btn btn-info btn-block" title="Añadir cobro a la lista"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <table id="tabla_cobros"></table>
                                        </div>
                                    </div>
                                    <!-- <div class="row">
                                        <div class="col-md-12 col-sm-12 col-xs-12 text-center">
                                            <label>Vuelto</label>
                                            <p style="font-size:32px;line-height:40px">Gs. <span id="vuelto">0</span></p>
                                        </div>
                                    </div> -->
                                </form>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-success btn-lg" id="btn_imprimir_factura">Imprimir Factura<sup class="ml-1">[F5]</sup></button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- MODAL CLIENTES -->
                <div class="modal fade" id="modal_clientes" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Buscar Cliente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12 col-sm-12">
                                                <div id="toolbar_clientes">
                                                    <div class="alert alert-info" role="alert">
                                                        <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                    </div>
                                                </div>
                                                <table id="tabla_clientes"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- MODAL AGREGAR CLIENTES -->
                <div class="modal fade" id="modal_clientes_new" role="dialog" aria-labelledby="modalClientesNewLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-md2 modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalClientesNewLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="formulario_clientes" method="post" enctype="multipart/form-data" action="">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="form-group col-md-12 col-sm-12">
                                            <div class="alert alert-warning" role="alert" id="alert_cliente_nuevo"><i class="fas fa-user-plus mr-1"></i>Cliente no registrado. Favor complete sus datos.</div>
                                        </div>
                                        <div class="col-md-12 col-sm-12">
                                            <ul class="nav nav-tabs" id="tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="tab-1" data-toggle="tab" href="#tab-content-1" role="tab" aria-controls="datos-basicos" aria-selected="true">DATOS BÁSICOS</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="tab-2" data-toggle="tab" href="#tab-content-2" role="tab" aria-controls="principios" aria-selected="false">UBICACIONES</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content mt-2" id="tabContent">
                                                <!-- Tab datos basicos -->
                                                <div class="tab-pane fade show active" id="tab-content-1" role="tabpanel" aria-labelledby="datos-basicos-tab">

                                                    <div class="form-row">
                                                        <div class="form-group col-md-2 col-sm-6">
                                                            <label for="tipo">Tipo</label>
                                                            <select id="id_tipo" name="id_tipo" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="ruc" class="label-required">RUC / CI</label>
                                                            <div class="input-group">
                                                                <input class="form-control input-sm" type="text" name="ruc_str" id="ruc_str" autocomplete="off" required>
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-primary" id="btn_buscar_ruc" tabindex="-1"><i class="fa fa-search"></i> <sup class="ml-1">[F1]</sup></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-7 col-sm-12">
                                                            <label for="razon_social" class="label-required">Apellido y Nombre / Razón Social</label>
                                                            <input class="form-control input-sm" type="text" name="razon_social_str" id="razon_social_str" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="telefono">Teléfono</label>
                                                            <input class="form-control input-sm" id="telefono" name="telefono" type="text" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="celular">Celular</label>
                                                            <input class="form-control input-sm" id="celular" name="celular" type="text" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="email">E-mail</label>
                                                            <input class="form-control input-sm" id="email" name="email" type="email" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="obs">Observaciones</label>
                                                            <textarea class="form-control input-sm" id="obs" name="obs" type="text" autocomplete="off"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade show" id="tab-content-2" role="tabpanel" aria-labelledby="datos-basicos-tab">

                                                    <div class="form-row">
                                                        <input type="hidden" id="lat" name="latitud">
                                                        <input type="hidden" id="lng" name="longitud">

                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <div id="myMap" class="myMap" style="width:100%;height:350px;margin:auto"></div>
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="direccion" class="label-required">Dirección</label>
                                                            <input class="form-control input-sm" id="direccion" name="direccion" type="text" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="referencia">Referencia</label>
                                                            <input class="form-control input-sm" id="referencia" name="referencia" type="text" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="lat_lng" class="label-required">Coordenadas</label>
                                                            <input class="form-control input-sm" id="lat_lng" name="lat_lng" type="text" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-primary btn-block" id="agregar-direccion">Agregar</button>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <table id="tabla_direcciones"></table>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-success">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL DETALLES -->
                <div class="modal fade" id="modal_detalles" tabindex="-1" role="dialog" aria-labelledby="modalVerLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-md2 modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modal_imprimirLabel">Detalles del Producto</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="formulario_detalles" method="post" enctype="multipart/form-data" action="">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group col-md-4 col-sm-12">
                                                    <label for="laboratorio">Laboratorio</label>
                                                    <div class="input-group">
                                                        <input class="form-control input-sm" type="text" name="laboratorio" id="laboratorio" autocomplete="off" readonly="">
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-8">
                                                    <label for="proveedor">Proveedor</label>
                                                    <input class="form-control input-sm" type="text" name="proveedor" id="proveedor" autocomplete="off" readonly="">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="origen">Origen</label>
                                                    <input class="form-control input-sm" id="origen" name="origen" type="text" autocomplete="off" readonly="">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="tipo">Tipo</label>
                                                    <input class="form-control input-sm" id="tipo" name="tipo" type="text" autocomplete="off" readonly="">
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="costo">Precio</label>
                                                    <input class="form-control input-sm text-right" id="costo" name="costo" type="text" autocomplete="off" readonly="">
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="costo">Precio Fraccionado</label>
                                                    <input class="form-control input-sm text-right" id="detalle_precio_fraccionado" name="detalle_precio_fraccionado" type="text" autocomplete="off" readonly="">
                                                </div>
                                                <div class="form-group col-md-2 col-sm-12">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="fuera_de_plaza" name="fuera_de_plaza" value="1">
                                                        <label class="custom-control-label" for="fuera_de_plaza">Fuera De Plaza</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-7 col-sm-12">
                                                    <h4>SUCURSALES</h4>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <table id="tabla_sucursales"></table>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-5 col-sm-12">
                                                    <h4>PRINCIPIOS ACTIVOS</h4>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <table id="tabla_principios"></table>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL DELIVERY -->
                <div class="modal fade" id="modal_delivery" role="dialog" aria-labelledby="modalLabelDelivery" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-md" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Buscar Delivery</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12 col-sm-12">
                                                <div id="toolbar_delivrery">
                                                    <div class="alert alert-info" role="alert">
                                                        <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                    </div>
                                                </div>
                                                <table id="tabla_delivery"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL CAJA -->
                <div class="modal fade" id="modal_caja" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="formulario_caja" method="post" enctype="multipart/form-data" action="">
                                <input type="hidden" name="id" id="id">
                                <div class="modal-body">

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <ul class="nav nav-tabs mb-2" id="tabModal" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="mc_nav_1" data-toggle="tab" href="#mc_tab_1" role="tab" aria-controls="mc_tab_1" aria-selected="true">ARQUEO DE CAJA</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="mc_nav_2" data-toggle="tab" href="#mc_tab_2" role="tab" aria-controls="tab_2" aria-selected="false">SERVICIOS</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="mc_nav_3" data-toggle="tab" href="#mc_tab_3" role="tab" aria-controls="tab_3" aria-selected="false">SENCILLO</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="tabModalContent">
                                                <!-- Tab 1 -->
                                                <div class="tab-pane fade show active" id="mc_tab_1" role="tabpanel" aria-labelledby="mc_nav_1">
                                                    <div class="form-row text-center alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <label class="label-sm" for="valor">Moneda</label><br>
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" id="valor" title="100.000 Gs." value="100.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <label class="label-sm" for="cantidad">Cantidad</label><br>
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" id="cantidad" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <label class="label-sm" for="total">Total</label><br>
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" id="total" title="Total (100.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="50.000 Gs." value="50.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (50.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="20.000 Gs." value="20.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (20.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="10.000 Gs." value="10.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (10.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="5.000 Gs." value="5.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (5.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="2.000 Gs." value="2.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (2.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="1.000 Gs." value="1.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (1.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="500 Gs." value="500" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (500 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="100 Gs." value="100" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (100 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="50 Gs." value="50" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total(50 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-end pt-3">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right" name="total_caja" id="total_caja" title="Total" value="0" readonly>
                                                        </div>
                                                    </div>

                                                </div>

                                                <!-- Tab 2 -->
                                                <div class="tab-pane fade" id="mc_tab_2" role="tabpanel" aria-labelledby="mc_nav_2">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="form-row text-center alingn-center justify-content-between" id="content_servicios"></div>
                                                        <div class="form-row alingn-center justify-content-end pt-3">
                                                            <div class="form-group-sm col-5">
                                                                <input type="text" class="form-control form-control-sm input-sm text-right" name="total_servicios" id="total_servicios" title="Total" value="0" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab 3 -->
                                                <div class="tab-pane fade show" id="mc_tab_3" role="tabpanel" aria-labelledby="mc_nav_3">
                                                    <div class="form-row text-center alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <label class="label-sm" for="valor">Moneda</label><br>
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" id="valor_sen" title="100.000 Gs." value="100.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <label class="label-sm" for="cantidad">Cantidad</label><br>
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" id="cantidad_sen" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <label class="label-sm" for="total">Total</label><br>
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" id="total_sen" title="Total (100.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="50.000 Gs." value="50.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (50.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="20.000 Gs." value="20.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (20.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="10.000 Gs." value="10.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (10.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="5.000 Gs." value="5.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (5.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="2.000 Gs." value="2.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (2.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="1.000 Gs." value="1.000" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (1.000 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="500 Gs." value="500" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (500 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="100 Gs." value="100" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (100 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-between">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="50 Gs." value="50" readonly>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total(50 Gs.)" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-row alingn-center justify-content-end pt-3">
                                                        <div class="form-group-sm col-3">
                                                            <input type="text" class="form-control form-control-sm input-sm text-right" name="total_caja_sen" id="total_caja_sen" title="Total" value="0" readonly>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" id="btn_submit_caja" class="btn btn-success">Abrir</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL EXTRACCION -->
                <div class="modal fade" id="modal-extraccion" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabelExtraer"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="formulario_extraer" method="post" enctype="multipart/form-data" action="">
                                <input type="hidden" name="id" id="id">
                                <div class="modal-body">

                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group-sm col-md-12 col-sm-12">
                                                    <label for="monto_extraccion" class="label-required">Monto</label>
                                                    <input type="text" class="form-control input-sm text-right" id="monto_extraccion" name="monto_extraccion" autocomplete="off" onkeypress="return soloNumeros(event)" onkeyup="separadorMilesOnKey(event,this)" required>
                                                </div>
                                                <div class="form-group-sm col-md-12 col-sm-12">
                                                    <label for="observacion">Observación</label>
                                                    <textarea type="text" class="form-control input-sm" name="observacion_extr" id="observacion_extr"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" id="btn_extraer" class="btn btn-success">Extraer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>
    <script type="text/javascript">

    </script>
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD1NW86t_rj3bXEDBYplwbqE4ufPJohf34" type="text/javascript"> -->
    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>

</html>
