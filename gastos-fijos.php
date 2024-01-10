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
                        <div id="toolbar">

                            <div class="form-inline" role="form">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F1]</sup></button>
                                </div>
                                <div class="form-group ml-1">
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-group ml-1">
                                    <select id="filtro_sucursales" name="filtro_sucursales" class="form-control"></select>
                                </div>
                            </div>

                        </div>
                        
                        <table id="tabla"></table>

                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2  modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <input type="hidden" name="id_recepcion" id="id_recepcion">
                                        <input type="hidden" name="id_proveedor" id="id_proveedor">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="id_tipo_gasto" class="label-required">Gasto</label>
                                                            <select id="id_tipo_gasto" name="id_tipo_gasto" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="gasto_fijo" class="label-required">Tipo Gastos Fijos</label>
                                                            <select id="gasto_fijo" name="gasto_fijo" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-6 required">
                                                            <label for="timbrado" class="label-required">Timbrado</label>
                                                            <input class="form-control input-sm upper text-right" type="text" maxlength="8" placeholder="0" name="timbrado" id="timbrado" autocomplete="off" value="0" required>
                                                        </div>
                                                        <div class="form-group-sm col-md-2 col-sm-12 required">
                                                            <label for="emision" class="label-required">Emision</label>
                                                            <input type="date" class="form-control input-sm" name="emision" id="emision" required>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="id_plan_cuenta" class="label-required">Plan Cuenta</label>
                                                            <select id="id_plan_cuenta" name="id_plan_cuenta" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="nro" class="label-required">N°</label>
                                                            <input class="form-control input-sm" type="nro" name="nro" id="nro" autocomplete="off" readonly required>
                                                        </div>

                                                        <div class="form-group col-md-3 col-sm-12 required">
                                                            <label for="id_sucursal" class="label-required">Sucursal</label>
                                                            <select id="id_sucursal" name="id_sucursal" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group-sm col-md-5 col-sm-12">
                                                            <label for="razon_social">Cliente / Razón Social</label>
                                                            <select class="form-control" name="razon_social" id="razon_social" required></select>
                                                        </div>
                                                        <div class="form-group-sm col-md- col-sm-12">
                                                            <label for="ruc" class="label-required">R.U.C</label>
                                                            <div class="input-group"readonly required>
                                                                <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" readonly required>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="documento" class="label-required">Documento </label>
                                                            <input class="form-control text_vistoso" placeholder="000-000-0000000" type="text" name="documento" id="documento" maxlength=20 autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12 required">
                                                            <label for="id_tipo_factura" class="label-required">Tipo de Factura</label>
                                                            <select id="id_tipo_factura" name="id_tipo_factura" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label for="imputa">Imputa:</label>
                                                            <div class="input-group mt-2">
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input checkshow" id="check_iva" name="check_iva" value="1">
                                                                    <label class="custom-control-label" for="check_iva" style="font-size: 15px;">IVA</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="check_ire" name="check_ire" value="1">
                                                                    <label class="custom-control-label" for="check_ire" style="font-size: 15px;">IRE</label>
                                                                </div>
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="check_irp" name="check_irp" value="1">
                                                                    <label class="custom-control-label" for="check_irp" style="font-size: 15px;">IRP</label>
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
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_10" id="iva_10" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="iva_5" >IVA 5%</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_5" id="iva_5" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="exenta">Exenta</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="exenta" id="exenta" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6">
                                                            <label for="observacion">Observacion</label>
                                                            <textarea class="form-control input-sm upper" type="text" name="observacion" id="observacion" autocomplete="off"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <!-- <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button> -->
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