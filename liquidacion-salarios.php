<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <form class="form default-validation" id="formulario_liq" method="post" enctype="multipart/form-data" action="">
                <div class="row">
                    <div class="col-12 pt-2">
                        <h4>Datos del Funcionario</h4>
                        <hr>

                        <!-- CABECERA -->
                        <div class="form-row">
                            <div class="form-group col-md-2 col-sm-12">
                                <label for="ruc" class="label-required">C.I.</label>
                                <div class="input-group">
                                    <input type="hidden" id="id_funcionario" name="id_funcionario">
                                    <input class="form-control input-sm" type="text" name="ci" id="ci" autocomplete="off" required="">
                                    <div class="input-group-append">
                                       <button type="button" class="btn btn-primary" id="btn_buscar" data-toggle="modal" data-target="#modal_funcionarios" title="Buscar Funcionario"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                    </div>
                                  </div>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="funcionario" class="label-required">Funcionario</label>
                                <input class="form-control" id="funcionario" name="funcionario" readonly="">
                            </div>
                            <div class="form-group col-md-2 col-sm-12">
                                <label for="periodo" class="label-required">Periodo</label>
                                <select id="periodo" name="periodo" class="form-control" required=""></select>
                            </div>

                            <div class="form-group col-md-2 col-sm-12">
                                <label for="neto"></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Total</span>
                                    </div>
                                    <input class="form-control text-right totales" type="text" name="neto" id="neto" autocomplete="off" value="0" readonly="">
                                </div>
                            </div>
                        </div>
                        <!-- FIN CABECERA -->
                        <div class="form-row">
                            <div class="form-group col-md-2 col-sm-6">
                                <label for="forma">Forma de Pago</label>
                                <select id="forma" name="forma" class="form-control">
                                    <option value="1">EFECTIVO</option>
                                    <option value="2">CHEQUE</option>
                                    <option value="3">TRANSFERENCIA</option>
                                </select>
                            </div>

                            <div class="form-group col-md-2 col-sm-12 cheques d-none">
                                <label for="cheque">Nro. Cheque</label>
                                <input class="form-control input-sm" type="text" name="cheque" id="cheque" autocomplete="off">
                            </div>
                            <div class="form-group col-md-2 col-sm-12 cuentas d-none">
                                <label for="cuenta">Nro. Cuenta</label>
                                <input class="form-control input-sm" type="text" name="cuenta" id="cuenta" autocomplete="off">
                                <input class="form-control input-sm" type="hidden" name="nro_cuenta" id="nro_cuenta" autocomplete="off">
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <h4>INGRESOS</h4>
                                <hr>
                                <div class="form-row">
                                    <div class="form-group col-md-2 col-sm-12">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-block" id="agregar_ingreso" data-toggle="modal" data-target="#modal" title="Agregar Ingreso">Agregar</button>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <table id="tabla_ingresos"></table>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6 col-sm-12">
                                <h4>DESCUENTOS</h4>
                                <hr>
                                <div class="form-row">
                                    <div class="form-group col-md-2 col-sm-12">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-block" id="agregar_descuento" data-toggle="modal" data-target="#modal_descuento" title="Agregar Descuento">Agregar</button>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <table id="tabla_descuento"></table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- FIN PRODUCTOS -->
                        <div class="form-group-sm pt-2">
                            <button type="submit" class="btn btn-lg btn-info float-right" id="btn_guardar">Guardar <small><sup class="ml-1">[F4]</sup></small></button>
                        </div>
                    </div>
                </div>
            </form>

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

            <!-- MODAL INGRESO -->
            <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Agregar Ingreso</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="form_ingreso" method="post" enctype="multipart/form-data" action="">
                                <!-- <input type="hidden" name="id_ingreso" id="id_ingreso"> -->
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <label for="nombre" class="label-required">Concepto</label>
                                                    <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12">
                                            <label for="importe" class="label-required">Monto</label>
                                            <input class="form-control input-sm text-right" id="importe" name="importe" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required="">
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12">
                                            <label for="observacion">Observaciones</label>
                                            <textarea class="form-control input-sm" id="observacion" name="observacion" type="text" autocomplete="off"></textarea>
                                        </div>
                                            
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>       
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-success">Cargar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL DESCUENTO -->
                <div class="modal fade" id="modal_descuento" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Agregar Descuento</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="form_descuento" method="post" enctype="multipart/form-data" action="">
                                <!-- <input type="hidden" name="id_descuento" id="id_descuento"> -->
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <label for="nombre" class="label-required">Concepto</label>
                                                    <input class="form-control input-sm upper" type="text" name="concepto_des" id="concepto_des" autocomplete="off" required="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12">
                                            <label for="importe" class="label-required">Monto</label>
                                            <input class="form-control input-sm text-right" id="importe_des" name="importe_des" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required="">
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12">
                                            <label for="observacion">Observaciones</label>
                                            <textarea class="form-control input-sm" id="observacion_des" name="observacion_des" type="text" autocomplete="off"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>       
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-success">Cargar</button>
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
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
