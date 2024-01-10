<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php'; ?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="form" role="form">
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label for="id_sucursal">Sucursal</label>
                                    <select id="id_sucursal" class="form-control"></select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="id_caja">Caja</label>
                                    <select id="id_caja" class="form-control" disabled=""></select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="saldo">Saldo</label>
                                    <input type="text" class="form-control text-right readonly_white" name="saldo" id="saldo" readonly>
                                </div>
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" id="btn_cargar" data-toggle='modal' data-target='#modal' disabled>Cargar<i class="fas fa-file-invoice-dollar ml-1"></i></button>
                                </div>
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-success btn-block" id="btn_rendir" disabled>Rendir <i class="fas fa-cash-register ml-1"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <div id="toolbar">
                                    <h4 class="mb-0" style="margin-top: 0.70rem !important">Resumen de Facturas</h4>
                                </div>
                                <table id="tabla"></table>
                            </div>
                        </div>

                        <!-- MODAL DETALLE FACTURA -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md3  modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cargar Gasto</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-1 col-sm-12 required">
                                                            <label for="tipo_gasto" class="label-required">Deducible</label>
                                                            <select id="tipo_gasto" name="tipo_gasto" class="form-control" required>
                                                                <option value='1'>Si</option>
                                                                <option value='2'>No</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="tipo_g" class="label-required">Tipo Gasto</label>
                                                            <select id="tipo_g" name="tipo_g" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="tipo_proveedor" class="label-required">Tipo de proveedor</label>
                                                            <select id="tipo_proveedor" name="tipo_proveedor" class="form-control" required>
                                                                <option value='1'>PRODUCTOS</option>
                                                                <option value='2'>GASTOS</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-2 required">
                                                            <label for="id_sucursal_gasto" class="label-required">Sucursal</label>
                                                            <select id="id_sucursal_gasto" name="id_sucursal_gasto" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group-sm col-md-2 col-sm-12">
                                                            <label for="ruc" class="label-required">R.U.C</label>
                                                            <div class="input-group">
                                                                <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required>
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-primary" id="btn_buscar"><i class="fa fa-search"></i><sup class="ml-1">[F2]</sup></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group-sm col-md-3 col-sm-12">
                                                            <label for="razon_social" class="label-required">Proveedor</label>
                                                            <select class="form-control" name="razon_social" id="razon_social" required></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="id_plan_cuenta" class="label-required">Plan Cuenta</label>
                                                            <select id="id_plan_cuenta" name="id_plan_cuenta" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-6 required">
                                                            <label for="timbrado" class="label-required">Timbrado</label>
                                                            <input class="form-control input-sm upper" type="text" name="timbrado" id="timbrado" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="documento" class="label-required">Documento </label>
                                                            <input class="form-control text_vistoso" placeholder="000-000-0000000" type="text" name="documento" id="documento" maxlength=20 autocomplete="off" required>
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
                                                                    <input type="checkbox" class="custom-control-input" id="check_ire" name="check_ire" value="1">
                                                                    <label class="custom-control-label" for="check_ire" style="font-size: 13px;">IRE</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox mr-1">
                                                                    <input type="checkbox" class="custom-control-input" id="check_irp" name="check_irp" value="1">
                                                                    <label class="custom-control-label" for="check_irp" style="font-size: 13px;">IRP</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group-sm col-md-2 col-sm-12 required">
                                                            <label for="emision" class="label-required">Emision</label>
                                                            <input type="date" class="form-control input-sm" name="emision" id="emision" required>
                                                        </div>
                                                        <div class="form-group col-md-10 col-sm-6 required">
                                                            <label for="concepto" class="label-required">Concepto</label>
                                                            <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 required">
                                                            <label for="monto" class="label-required">Monto</label>
                                                            <input class="form-control input-sm text-right" type="text" name="monto" id="monto" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" readonly required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 required">
                                                            <label for="iva_10" class="label-required">IVA 10%</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_10" id="iva_10" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" readonly required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 required">
                                                            <label for="iva_5" class="label-required">IVA 5%</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_5" id="iva_5" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" readonly required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 required">
                                                            <label for="exenta" class="label-required">Exenta</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="exenta" id="exenta" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" readonly required>
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
                                            <button type="submit" class="btn btn-success">Guardar</button>
                                        </div>
                                    </form>
                                </div>
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