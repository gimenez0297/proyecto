<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php'; ?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                    <div class="row">
                        <div class="col-12 pt-2">
                            <h4>Datos de la Carga</h4>
                            <hr>

                            <!-- CABECERA -->
                            <div class="form-row">
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="numero">Número</label>
                                    <input class="form-control input-sm text-right" type="text" name="numero" id="numero" autocomplete="off" readonly>
                                </div>
                                <div class="form-group col-md-3 col-sm-12">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" id="agregar-detalles" data-toggle="modal" data-target="#modal_factura_detalle" title="Adjuntar Detalles de la Factura"><i class="fas fa-file-alt mr-1"></i>Detalles de Factura<sup class="ml-1">[F1]</sup></button>
                                </div>
                                <div class="form-group col-md-12 col-sm-12">
                                    <label for="observacion">Observación</label>
                                    <textarea class="form-control input-sm" type="text" name="observacion" id="observacion" autocomplete="off" rows="2"></textarea>
                                </div>
                            </div>
                            <!-- FIN CABECERA -->

                            <!-- PREMIOS -->
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <h4>Premios</h4>
                                    <hr>
                                    <div class="form-row">
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label for="codigo">Código<sup class="ml-1">[F2]</sup></label>
                                            <input class="form-control input-sm text-center" type="text" name="codigo" id="codigo" autocomplete="off" onkeypress="return soloNumeros(event)">
                                        </div>
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="premios">Premios<sup class="ml-1">[F3]</sup></label>
                                            <select id="premios" name="premios" class="form-control"></select>
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label for="cantidad">Cantidad</label>
                                            <input class="form-control input-sm text-right" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1">
                                        </div>
                                        <div class="form-group col-md-2 col-sm-11">
                                            <label for="costo">Costo</label>
                                            <input class="form-control input-sm text-right" type="text" name="costo" id="costo" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1">
                                        </div>
                                        <!-- <div class="form-group col-md-1 col-sm-11">
                                            <label for="puntos">Puntos</label>
                                            <input class="form-control input-sm text-right" type="text" name="puntos" id="puntos" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1">
                                        </div> -->
                                        <!-- <div class="form-group col-md-1 col-md-2">
                                            <label for="vencimiento">Vencimiento</label>
                                            <input class="form-control input-sm text-left" type="date" name="vencimiento" id="vencimiento" autocomplete="off">
                                        </div> -->
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-primary btn-block" id="agregar-premio">Agregar</button>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12">
                                            <table id="tabla_detalle"></table>
                                        </div>

                                    </div>

                                </div>
                            </div>

                            <!-- FIN PREMIOS-->
                            <div class="form-group-sm pt-2">
                                <button type="button" class="btn btn-lg btn-info float-right" id="btn_guardar">Continuar<small><sup class="ml-1">[F4]</sup></small></button>
                            </div>

                        </div>

                    </div>
                </form>

                <!-- MODAL DETALLE FACTURA -->
                <div class="modal fade" id="modal_factura_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-md3  modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Agregar Detalles de Facturas</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form default-validation" id="formulario_factura" method="post" enctype="multipart/form-data" action="">
                                <input type="hidden" name="id" id="id">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group-sm col-md-3 col-sm-12">
                                                    <label for="ruc" class="label-required">R.U.C</label>
                                                    <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" readonly required>
                                                </div>
                                                <div class="form-group-sm col-md-5 col-sm-12">
                                                    <label for="id_proveedor">Proveedor / Razón Social</label>
                                                    <select id="id_proveedor" name="id_proveedor" class="form-control" required></select>
                                                </div>
                                                <div class="form-group-sm col-md-2 col-sm-12 required">
                                                    <label for="emision" class="label-required">Emision</label>
                                                    <input type="date" class="form-control input-sm" name="emision" id="emision" required>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="timbrado" class="label-required">Timbrado</label>
                                                    <input class="form-control input-sm upper" type="text" name="timbrado" id="timbrado" autocomplete="off" required>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="documento" class="label-required">Documento </label>
                                                    <input class="form-control text_vistoso" placeholder="000-000-0000000" type="text" name="documento" id="documento" maxlength=20 autocomplete="off" required>
                                                </div>
                                                <div class="form-group-sm col-md-3 col-sm-12">
                                                    <label for="condicion" class="label-required">Condición:</label>
                                                    <div class="input-group">
                                                        <select class="form-control" name="condicion" id="condicion" required>
                                                            <option value="1">CONTADO</option>
                                                            <option value="2">CRÉDITO</option>
                                                        </select>
                                                        <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary btn-block" id="btn_vencimiento" data-toggle="modal" data-target="#modal_vencimientos" title="Cargar Vencimientos"><i class="fas fa-list mr-1"></i><sup class="ml-1">[F1]</sup></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4 col-sm-12 required">
                                                    <label for="id_tipo_factura" class="label-required">Tipo de Factura</label>
                                                    <select id="id_tipo_factura" name="id_tipo_factura" class="form-control" required></select>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-12">
                                                    <label for="imputa">Imputa:</label>
                                                    <div class="input-group mt-2">
                                                        <div class="custom-control custom-checkbox mr-1">
                                                            <input type="checkbox" class="custom-control-input checkshow" id="check_iva" name="check_iva" value="1">
                                                            <label class="custom-control-label" for="check_iva" style="font-size: 13px;">IVA</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-1">
                                                            <input type="checkbox" class="custom-control-input" id="check_ire" name="check_ire" value="1">
                                                            <label class="custom-control-label" for="check_ire" style="font-size: 13px;">IRE</label>
                                                        </div>
                                                        <div class="custom-control custom-checkbox mr-1">
                                                            <input type="checkbox" class="custom-control-input" id="check_irp" name="check_irp" value="1">
                                                            <label class="custom-control-label" for="check_irp" style="font-size: 13px;">IRP</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-12 col-sm-6 required">
                                                    <label for="concepto" class="label-required">Concepto</label>
                                                    <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off" required>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-6 required">
                                                    <label for="monto" class="label-required">Monto</label>
                                                    <input class="form-control input-sm text-right" type="text" name="monto" id="monto" autocomplete="off" readonly required>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-6">
                                                    <label for="iva_10">IVA 10%</label>
                                                    <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_10" id="iva_10" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                </div>
                                                <div class="form-group col-md-3 col-sm-6">
                                                    <label for="iva_5">IVA 5%</label>
                                                    <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_5" id="iva_5" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                </div>
                                                <div class="form-group col-md-3 col-sm-6">
                                                    <label for="exenta">Exenta</label>
                                                    <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="exenta" id="exenta" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                </div>
                                                <div class="form-group col-md-12 col-sm-6">
                                                    <label for="observacion">Observacion</label>
                                                    <textarea class="form-control input-sm upper" type="text" name="observacion_factura" id="observacion_factura" autocomplete="off"></textarea>
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

                <!-- MODAL VENCIMIENTOS -->
                <div class="modal fade" id="modal_vencimientos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Vencimientos</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="form default-validation" id="formulario_vencimientos" method="post" enctype="multipart/form-data" action="">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group col-md-10 col-sm-12">
                                                    <label for="lote_vencimiento" class="label-required">Vencimiento</label>
                                                    <input class="form-control input-sm" type="date" name="id_vencimiento" id="id_vencimiento" autocomplete="off" required>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-12">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-primary btn-block" id="agregar-vencimiento"><i class="fas fa-plus"></i></button>
                                                </div>
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <table id="tabla_vencimientos"></table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
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