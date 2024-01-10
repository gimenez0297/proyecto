<?php include 'header.php';

?>

<body class="<?php include 'menu-class.php'; ?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div id="mensaje"></div>
                        <!-- MODA PRINCIPAL -->
                        <div class="card" id="card-acumulacion" tabindex="-1">
                            <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="editar">
                                <div class="card-body">
                                    <div class="col-12 pt-2">
                                        <h4> Acumulación</h4>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12 form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="acumular_credito" name="acumular_credito" value="1">
                                                    <label class="custom-control-label" for="acumular_credito">Posibilidad de Acumular Puntos con Ventas a Crédito</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 form-group">
                                                <label for="configuracion_acumulacion" class="label-required">Método de Acumulación</label>
                                                <select class="form-control" id="configuracion_acumulacion" name="configuracion_acumulacion">
                                                    <option value="1">Monto de compra para todos los productos</option>
                                                    <option value="2">Monto de compra para productos seleccionados</option>
                                                    <option value="3">Monto de compra para todos los productos excepto seleccionados</option>
                                                    <option value="4">Por cantidad de compras</option>
                                                    <option value="5">Por cantidad de productos comprados</option>
                                                    <option value="6">Especificar puntos a productos seleccionados</option>
                                                </select>
                                            </div>

                                            <div class="col-md-5 form-group pr-0 cantidad_acumulacion">
                                                <label for="cantidad_acumulacion" class="label-required">Monto</label>
                                                <div class="input-group mb-1">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="span_ac">Gs</span>
                                                    </div>
                                                    <input class="form-control input-sm" type="text" name="cantidad_acumulacion" id="cantidad_acumulacion" autocomplete="off" value="<?php echo $cantidad; ?>" onkeyup="separadorMilesOnKey(event,this)" required>
                                                </div>
                                            </div>

                                            <div class="col-md-1 form-group px-0 logo_acumulacion">
                                                <label for="logo"></label>
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text form-control border-logo-intercambio"><i class="fas fa-exchange-alt"></i></span>
                                                </div>
                                            </div>

                                            <div class="col-md-6 form-group pl-0 puntos_acumulacion">
                                                <label for="puntos_acumulacion" class="label-required">Puntos</label>
                                                <div class="input-group mb-1">
                                                    <input class="form-control input-sm text-right" type="text" name="puntos_acumulacion" id="puntos_acumulacion" autocomplete="off" value="<?php echo $puntos; ?>" required>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Pts</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" id="acumulacion">
                                            <div class="col-md-12 form-group">
                                                <div id="toolbar_acumulacion">
                                                    <div class="form-inline" role="form">
                                                        <div class="form-group">
                                                            <button type="button" class="btn btn-primary agregar" id="buscar_producto_acumulacion" data-toggle="modal" data-target="#modal_buscar" title="Agregar Producto"><i class="fa fa-plus"></i><sup class="ml-1">[F1]</sup></button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <table id="tabla_acumulacion"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                        </div>
                        <div class="card" id="card-canjeo" tabindex="-1">
                                <div class="card-body">
                                    <div class="col-12 pt-2">
                                        <h4>Canjeo</h4>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12 form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="canjeo_credito" name="canjeo_credito" value="1">
                                                    <label id="canjeo_credito" class="custom-control-label" for="canjeo_credito">Posibilidad de Canjear Puntos con Ventas a Crédito</label>
                                                </div>
                                            </div>
                                            <div class="col-md-10 form-group">
                                                <label for="configuracion_canjeo" class="label-required">Método de Canjeo</label>
                                                <select class="form-control" id="configuracion_canjeo" name="configuracion_canjeo">
                                                    <option value="1">Monto de compra para todos los productos</option>
                                                    <option value="2">Monto de compra para productos seleccionados</option>
                                                    <option value="3">Monto de compra para todos los productos excepto seleccionados</option>
                                                    <option value="4">Por cantidad de compras</option>
                                                    <option value="5">Por cantidad de productos comprados</option>
                                                    <option value="6">Especificar puntos a productos seleccionados</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 form-group">
                                                <label for="periodo_vencimiento" class="label-required">Periodo Vencimiento</label>
                                                <div class="input-group mb-1">
                                                    <input class="form-control input-sm text-right" type="text" name="periodo_vencimiento" id="periodo_vencimiento" autocomplete="off" value="<?php echo $periodo_devolucion; ?>" onkeyup="separadorMilesOnKey(event,this)" required>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">DIAS</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5 form-group pr-0 cantidad_canjeo">
                                                <label for="cantidad_canjeo" class="label-required">Monto</label>
                                                <div class="input-group mb-1">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" id="span_cj">Gs</span>
                                                    </div>
                                                    <input class="form-control input-sm" type="text" name="cantidad_canjeo" id="cantidad_canjeo" autocomplete="off" value="<?php echo $monto; ?>" onkeyup="separadorMilesOnKey(event,this)" required>
                                                </div>
                                            </div>
                                            <div class="col-md-1 form-group px-0 logo_canjeo">
                                                <label for="logo_canjeo"></label>
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text form-control border-logo-intercambio"><i class="fas fa-exchange-alt"></i></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 form-group pl-0 puntos_canjeo">
                                                <label for="puntos_canjeo" class="label-required">Puntos</label>
                                                <div class="input-group mb-1">
                                                    <input class="form-control input-sm text-right" type="text" name="puntos_canjeo" id="puntos_canjeo" autocomplete="off" value="<?php echo $puntos; ?>" required>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Pts</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" id="canjeo">
                                            <div class="col-md-12 form-group">
                                                <div id="toolbar_canjeo">
                                                    <div class="form-inline" role="form">
                                                        <div class="form-group">
                                                            <button type="button" class="btn btn-primary agregar" id="buscar_producto_canjeo" data-toggle="modal" data-target="#modal_buscar" title="Agregar Producto"><i class="fa fa-plus"></i><sup class="ml-1">[F2]</sup></button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <table id="tabla_canjeo"></table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button id="guardar" type="submit" class="btn btn-success">Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL BUSCAR -->
                <div class="modal fade" id="modal_buscar" tabindex="-1" role="dialog" aria-labelledby="modalBuscarLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-98" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalBuscarLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <input class="form-control" type="hidden" id="tipo_dato" >
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_proveedor">Proveedor<sup class="ml-1">[F1]</sup></label>
                                                <select id="filtro_buscar_proveedor" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_origen">Origen<sup class="ml-1">[F2]</sup></label>
                                                <select id="filtro_buscar_origen" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_tipo">Tipo<sup class="ml-1">[F3]</sup></label>
                                                <select id="filtro_buscar_tipo" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_laboratorio">Laboratorio<sup class="ml-1">[F4]</sup></label>
                                                <select id="filtro_buscar_laboratorio" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_marca">Marca<sup class="ml-1">[F5]</sup></label>
                                                <select id="filtro_buscar_marca" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_rubro">Rubro<sup class="ml-1">[F6]</sup></label>
                                                <select id="filtro_buscar_rubro" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-12 col-sm-12">
                                                <div id="toolbar_buscar">
                                                    <div class="form-inline" role="form">
                                                        <div class="form-group">
                                                            <button type="button" class="btn btn-success mr-1" id="btn_incluir" title="Cargar Tabla"><i class="fas fa-download"></i><sup class="ml-1">[F7]</sup></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table id="tabla_buscar"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="button" id="btn_modal_agregar" class="btn btn-success">Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>
    <script type="text/javascript"></script>

    <script src="<?php echo $js_pagina; ?>"></script>

</body>

</html>