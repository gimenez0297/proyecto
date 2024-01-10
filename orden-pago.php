<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                <div class="row">
                    <div class="col-12 pt-2">
                        <h4>Datos del Proveedor</h4>
                        <hr>

                        <!-- CABECERA -->
                        <div class="form-row">
                            <div class="form-group-sm col-md-3 col-sm-12 required">
                                <label for="fecha" class="label-required">Fecha</label>
                                <input type="date" class="form-control input-sm" name="fecha" id="fecha" required>
                            </div>
                            <div class="form-group col-md-2 col-sm-12 d-none">
                                <input class="form-control input-sm" type="text" name="hidden_editable_index" id="hidden_editable_index" autocomplete="off">
                            </div>
                            <div class="form-group col-md-3 col-sm-12">
                                <label for="destino_pago">Destino Pago</label>
                                <select id="destino_pago" name="destino_pago" class="form-control">
                                    <option value="1">Proveedores</option>
                                    <option value="2">Funcionarios</option>
                                    <option value="3">Gastos</option>
                                    <option value="4">Caja Chica</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3 col-sm-12">
                                <label for="forma_pago">Forma de Pago</label>
                                <select id="forma_pago" name="forma_pago" class="form-control">
                                    <option value="1">Transferencia</option>
                                    <option value="2">Cheque</option>
                                    <option value="3">Efectivo</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3 col-sm-12">
                                <label for="numero">Número</label>
                                <input class="form-control input-sm text-right" type="text" name="numero" id="numero" autocomplete="off" value="0" readonly>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 proveedor">
                                <label for="ruc" class="label-required">Proveedor / Razón Social</label>
                                <div class="input-group">
                                    <input type="hidden" id="id_proveedor" name="id_proveedor">
                                    <input class="form-control input-sm" type="hidden" name="ruc" id="ruc" autocomplete="off" readonly required>
                                    <select id="proveedor" name="proveedor" class="form-control"></select>
                                    <div class="input-group-append">
                                       <button type="button" class="btn btn-primary" id="btn_buscar_proveedor" data-toggle="modal" data-target="#modal_proveedores" title="Buscar Proveedor"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                    </div>
                                  </div>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 funcionario">
                                <label for="ruc" class="label-required">Funcionario</label>
                                <div class="input-group">
                                    <input type="hidden" id="id_funcionario" name="id_funcionario">
                                    <input class="form-control input-sm d-none" type="text" name="ci" id="ci" autocomplete="off">
                                    <select id="funcionario" name="funcionario" class="form-control"></select>
                                    <div class="input-group-append">
                                       <button type="button" class="btn btn-primary" id="btn_buscar" data-toggle="modal" data-target="#modal_funcionarios" title="Buscar Funcionario"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                    </div>
                                  </div>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 gastos">
                                <label for="ruc" class="label-required">Gastos</label>
                                <div class="input-group">
                                    <!-- <input type="hidden" id="proveedor" name="proveedor"> -->
                                    <input class="form-control input-sm d-none" type="text" name="ci" id="ci" autocomplete="off">
                                    <select id="proveedor_gasto" name="proveedor_gasto" class="form-control"></select>
                                    <div class="input-group-append">
                                       <button type="button" class="btn btn-primary" id="btn_buscar_gastos" data-toggle="modal" data-target="#modal_gastos" title="Buscar Proveedor Gastos"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                    </div>
                                  </div>
                            </div>

                            <div class="form-group col-md-3 col-sm-12 sucursal d-none">
                                <label for="id_sucursal">Sucursal</label>
                                <select id="id_sucursal" name="id_sucursal" class="form-control"></select>
                            </div>

                            <div class="form-group col-md-3 col-sm-12 sucursal d-none">
                                <label for="nro_movimiento">Nro de Movimiento</label>
                                <input class="form-control input-sm text-right" type="text" name="nro_movimiento" id="nro_movimiento" autocomplete="off" value="0" readonly>
                            </div>

                            <div class="form-group col-md-6 col-sm-6 banco">
                                <label for="id_banco" class="label-required">Banco</label>
                                <select id="id_banco" name="id_banco" class="form-control" required></select>
                            </div>
                            <div class="form-group col-md-3 col-sm-12 cheques d-none">
                                <label for="cheque" class="label-required">Nro. Cheque</label>
                                <input class="form-control input-sm" type="text" name="cheque" id="cheque" autocomplete="off" required="">
                            </div>
                            <div class="form-group col-md-3 col-sm-12 cuentas d-none">
                                <label for="cuenta" class="label-required">Nro. Cuenta</label>
                                <select id="cuenta" name="cuenta" class="form-control" required></select>
                            </div>
                            <div class="form-group col-md-3 col-sm-12 cuentas destino d-none">
                                <label for="cuenta_destino" class="label-required">Nro. Cuenta Destino</label>
                                <input class="form-control input-sm" type="text" name="cuenta_destino" id="cuenta_destino" autocomplete="off" required="">
                            </div>
                            <div class="form-group col-md-2 col-sm-12 monto_cc d-none">
                                <label for="monto_cc" class="label-required">Monto</label>
                                <input class="form-control input-sm text-right" type="text" name="monto_cc" id="monto_cc" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required="" readonly>
                            </div>
                            <!-- <div class="form-group col-md-2 col-sm-6 funcionario">
                                <label for="monto" class="label-required">Monto</label>
                                <input class="form-control input-sm" id="monto" name="monto" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required="">
                            </div> -->
                            <div class="form-group col-md-6 col-sm-12 concepto">
                                <label for="concepto">Concepto</label>
                                <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="observacion">Observación</label>
                                <textarea class="form-control input-sm upper" type="text" name="observacion" id="observacion" autocomplete="off" rows="2"></textarea>
                            </div>
                        </div>
                        <!-- FIN CABECERA -->

                        <!-- PRODUCTOS -->
                        <div class="row tabla_proveedor">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-row">
                                    <div class="form-group col-md-12 col-sm-12">
                                        <div id="toolbar_facturas">
                                            <h4 class="mb-0" style="margin-top: 0.70rem !important">Facturas</h4>
                                        </div>
                                        <table id="tabla_detalles"></table>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- FUNCIONARIOS -->
                        <div class="row tabla_funcionario">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-row">
                                    <div class="form-group col-md-12 col-sm-12">
                                        <div id="toolbar_liquidaciones">
                                            <h4 class="mb-0" style="margin-top: 0.70rem !important">Liquidaciones</h4>
                                        </div>
                                        <table id="tabla_liquidaciones"></table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- GASTOS -->
                        <div class="row tabla_gastos">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-row">
                                    <div class="form-group col-md-12 col-sm-12">
                                        <div id="toolbar_gastos">
                                            <h4 class="mb-0" style="margin-top: 0.70rem !important">Gastos</h4>
                                        </div>
                                        <table id="tabla_detalles_gastos"></table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- FIN PRODUCTOS -->
                        <div class="form-group-sm pt-2">
                            <button type="button" class="btn btn-lg btn-info float-right" id="btn_guardar">Continuar<small><sup class="ml-1">[F4]</sup></small></button>
                        </div>

                        <!-- MODA PROVEEDORES -->
                        <div class="modal fade" id="modal_proveedores" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Buscar Proveedor</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_proveedores">
                                                            <div class="alert alert-info" role="alert">
                                                                <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                            </div>
                                                        </div>
                                                        <table id="tabla_proveedores"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL FUNCIONARIO -->
                        <div class="modal fade" id="modal_funcionarios" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Buscar Funcionario</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_funcionarios">
                                                         <div class="alert alert-info" role="alert">
                                                                <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                            </div>
                                                        </div>
                                                        <table id="tabla_funcionarios"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- MODAL GASTOS -->
                        <div class="modal fade" id="modal_gastos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Buscar Gastos</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_proveedor_gastos">
                                                         <div class="alert alert-info" role="alert">
                                                                <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                            </div>
                                                        </div>
                                                        <table id="tabla_proveedor_gastos"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            </div>
        </div>
       <?php include 'footer.php'; ?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
