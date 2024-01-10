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
                            <h4>Datos De La Orden De Compra</h4>
                            <hr>

                            <!-- CABECERA -->
                            <div class="form-row">
                                <div class="form-group col-md-8 col-sm-12">
                                    <label for="orden_compra" class="label-required">Orden De Compra | Proveedor</label>
                                    <div class="input-group">
                                        <input type="hidden" id="id_proveedor" name="id_proveedor">
                                        <select id="proveedor" name="proveedor" class="form-control"></select>
                                        <div class="input-group-append">
                                           <button type="button" class="btn btn-primary" id="btn_buscar_orden_conpra" data-toggle="modal" data-target="#modal_ordenes_compra" title="Buscar Orden De Compra"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                        </div>
                                        <div class="input-group-append">
                                           <button type="button" class="btn btn-info" id="btn_buscar" data-toggle="modal" data-target="#modal_proveedores" title="Buscar Proveedor"><i class="fa fa-search"></i><sup class="ml-1">[F2]</sup></button>
                                        </div>
                                      </div>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="deposito" class="label-required">Depósito</label>
                                    <select id="deposito" name="deposito" class="form-control" required></select>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="numero">Número</label>
                                    <input class="form-control input-sm text-right" type="text" name="numero" id="numero" autocomplete="off" value="0" readonly>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <button type="button" class="btn btn-primary btn-block" id="btn_cargar_detalle_factura" data-toggle="modal" data-target="#modal_factura_detalle" title="Adjuntar Detalles de la Factura"><i class="fas fa-file-alt mr-1"></i>Detalles de Factura</button>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <button type="button" class="btn btn-primary btn-block" id="btn_cargar_archivos" data-toggle="modal" data-target="#modal_archivos" title="Adjuntar Archivos"><i class="fas fa-file-invoice mr-1"></i> Adjuntar Archivos</button>
                                </div>
                                <div class="form-group col-md-12 col-sm-12">
                                    <label for="observacion">Observación</label>
                                    <textarea class="form-control input-sm upper" type="text" name="observacion" id="observacion" autocomplete="off" rows="2"></textarea>
                                </div>
                            </div>
                            <!-- FIN CABECERA -->

                            <!-- PRODUCTOS -->
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <h4>Productos</h4>
                                    <div class="form-row">
                                        <div class="form-group col-md-12 col-sm-12">
                                            <table id="tabla_productos"></table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- FIN PRODUCTOS -->
                            <div class="form-group-sm pt-2">
                                <button type="submit" class="btn btn-lg btn-info float-right" id="btn_guardar">Continuar<small><sup class="ml-1">[F4]</sup></small></button>
                            </div>

                        </div>
                    </div>
                </form>

                <!-- MODAL LOTES -->
                <div class="modal fade" id="modal_lotes" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Lotes</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="form default-validation" id="formulario_lote" method="post" enctype="multipart/form-data" action="">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <div class="alert alert-info" role="alert">
                                                        <i class="fas fa-info-circle"></i> Producto: <span id="lotes_producto"></span>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-5 col-sm-12">
                                                    <label for="lote" class="label-required">Lote</label>
                                                    <input class="form-control input-sm" type="text" name="lote" id="lote" autocomplete="off" required>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="lote_vencimiento" class="label-required">Vencimiento</label>
                                                    <input class="form-control input-sm" type="date" name="lote_vencimiento" id="lote_vencimiento" autocomplete="off" required>
                                                </div>
                                                <div class="form-group col-md-3 col-sm-12">
                                                    <label for="cantidad" class="label-required">Cantidad</label>
                                                    <input class="form-control input-sm text-right" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-12">
                                                    <label for="vencimiento_canje" class="label-required">Vencimiento De Canje</label>
                                                    <input class="form-control input-sm" type="date" name="vencimiento_canje" id="vencimiento_canje" autocomplete="off" required>
                                                </div>
                                                <div class="form-group col-md-1 col-sm-12">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-primary btn-block" id="agregar-lote"><i class="fas fa-plus"></i></button>
                                                </div>
                                                <div class="form-group col-md-1 col-sm-12 d-flex align-items-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="canje" name="canje" value="1">
                                                        <label class="custom-control-label" for="canje">Canje</label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <table id="tabla_lotes"></table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                            
                <!-- MODAL PROVEEDORES -->
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

                <!-- MODAL ORDENES DE COMPRRA -->
                <div class="modal fade" id="modal_ordenes_compra" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Buscar Orden De Compra</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12 col-sm-12">
                                                <div id="toolbar_ordenes_compra">
                                                    <div class="alert alert-info" role="alert">
                                                        <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                    </div>
                                                </div>
                                                <table id="tabla_ordenes_compra"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                    <form class="form" id="formulario_factura" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group-sm col-md-3 col-sm-12">
                                                            <label for="ruc" class="label-required">R.U.C</label>
                                                            <div class="input-group">
                                                                <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" readonly required>
                                                            </div>
                                                        </div>
                                                        <div class="form-group-sm col-md-7 col-sm-12">
                                                            <label for="razon_social">Cliente / Razón Social</label>
                                                            <input class="form-control input-sm" type="text" name="razon_social" id="razon_social" autocomplete="off" readonly required>
                                                        </div>
                                                        <div class="form-group-sm col-md-2 col-sm-12 required">
                                                            <label for="emision" class="label-required">Emision</label>
                                                            <input type="date" class="form-control input-sm" name="emision" id="emision" required>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-6 required">
                                                            <label for="timbrado" class="label-required">Timbrado</label>
                                                            <input class="form-control input-sm upper" type="text" name="timbrado" id="timbrado" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
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
                                                                <button type="button" class="btn btn-primary btn-block" id="btn_vencimiento" data-toggle="modal" data-target="#modal_vencimientos" title="Cargar Vencimientos"><i class="fas fa-list mr-1"></i><sup class="ml-1">[F1]</sup</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--<div class="form-group col-md-3 col-sm-12 vencimientos">
                                                            <label for=""></label>
                                                            <button type="button" class="btn btn-primary btn-block" id="btn_vencimiento" data-toggle="modal" data-target="#modal_vencimientos" title="Cargar Vencimientos"><i class="fas fa-list mr-1"></i>Vencimientos</button>
                                                        </div>-->
                                                        <div class="form-group col-md-3 col-sm-12 required">
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
                                                                    <input type="checkbox" class="custom-control-input" id="check_ire" name="check_ire" value= "1">
                                                                    <label class="custom-control-label" for="check_ire" style="font-size: 13px;">IRE</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox mr-1">
                                                                    <input type="checkbox" class="custom-control-input" id="check_irp" name="check_irp" value= "1">
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
                                                            <label for="iva_10" >IVA 10%</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_10" id="iva_10" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" readonly>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="iva_5" >IVA 5%</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_5" id="iva_5" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" readonly>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="exenta">Exenta</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="exenta" id="exenta" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" readonly>
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

                <!-- MODAL ARCHIVOS -->
                <div class="modal fade" id="modal_archivos" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Adjuntar Archivos</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">

                                        <div class="form-row">
                                            <div class="form-group col-md-12 col-sm-12 text-right">
                                                <input type="file" class="d-none" name="archivos" id="archivos" multiple accept="image/png,image/jpeg">
                                                <button type="button" class="btn btn-primary" id="agregar-archivos"><i class="fas fa-plus mr-1"></i>Agregar</button>
                                            </div>
                                            <div class="form-group col-md-12 col-sm-12">
                                                <table id="tabla_archivos"></table>

                                                <template id="template">
                                                    <div class="col-md-4 col-sm-12 mt-3">
                                                        <div class="card border position-relative">
                                                                <div class="w-100 text-right position-absolute">
                                                                    <button class="btn btn-outline-danger btn-preview" title="Eliminar" value="%ID%"><span class="fa fa-times"></span></button>
                                                                </div>
                                                                <div class="text-center" style="height:200px;overflow:hidden;">
                                                                    <div class="text-center" style="height:200px;overflow:hidden;">
                                                                        <a data-fancybox='gallery' href="%FANCY%">
                                                                            <img src="%PREVIEW%" alt="" class="mx-auto img-fluid h-100">
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        </template>
                                                    </div>
                                                

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
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
