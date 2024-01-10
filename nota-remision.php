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
                            <h4>Datos de la Nota de Remisión</h4>
                            <hr>

                            <!-- CABECERA -->
                            <div class="form-row">
                                <div class="form-group-sm col-md-2">
                                    <label for="numero" class="label-sm">Número</label>
                                    <input class="form-control-sm input-sm" type="text" name="numero" id="numero" autocomplete="off" readonly>
                                </div>
                                <div class="form-group-sm col-md-3">
                                    <label for="motivo" class="label-sm label-required">Motivo</label>
                                    <select id="motivo" name="motivo" class="form-control-sm" required></select>
                                </div>
                                <div class="form-group-sm col-md-3">
                                    <label for="sucursal_origen" class="label-sm label-required">Sucursal De Origen</label>
                                    <select id="sucursal_origen" name="sucursal_origen" class="form-control" required></select>
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="fecha_inicio" class="label-sm label-required">Fecha De Salida</label>
                                    <input class="form-control-sm input-sm" type="date" name="fecha_inicio" id="fecha_inicio" autocomplete="off" required>
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="fecha_fin" class="label-sm label-required">Fecha De Llegada</label>
                                    <input class="form-control-sm input-sm" type="date" name="fecha_fin" id="fecha_fin" autocomplete="off" required>
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="sucursal_destino" class="label-sm">Sucursal De Destino</label>
                                    <div class="input-group">
                                        <select id="sucursal_destino" name="sucursal_destino" class="form-control"></select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-primary" id="btn_solicitudes" data-toggle="modal" data-target="#modal_solicitudes" title="Buscar Solicitudes de Deposito" disabled><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="ruc_destino" class="label-sm">R.U.C. / C.I. Destinatario</label>
                                    <input class="form-control-sm input-sm" type="text" name="ruc_destino" id="ruc_destino" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-4">
                                    <label for="razon_social_destino" class="label-sm">Razón Social Destinatario</label>
                                    <input class="form-control-sm input-sm upper" type="text" name="razon_social_destino" id="razon_social_destino" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-4">
                                    <label for="direccion_destino" class="label-sm">Dirección Destino</label>
                                    <input class="form-control-sm input-sm upper" type="text" name="direccion_destino" id="direccion_destino" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="comprobante_venta" class="label-sm">Comprobante De Venta</label>
                                    <input class="form-control-sm input-sm" type="text" name="comprobante_venta" id="comprobante_venta" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="comprobante_venta_nro" class="label-sm">Comprobante De Venta N°</label>
                                    <input class="form-control-sm input-sm" type="text" name="comprobante_venta_nro" id="comprobante_venta_nro" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-3">
                                    <label for="comprobante_timbrado" class="label-sm">Comprobante N° De Timbrado</label>
                                    <input class="form-control-sm input-sm" type="text" name="comprobante_timbrado" id="comprobante_timbrado" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-3">
                                    <label for="fecha_expedicion" class="label-sm">Fecha De Expedición</label>
                                    <input class="form-control-sm input-sm" type="date" name="fecha_expedicion" id="fecha_expedicion" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="ruc_chofer" class="label-sm">R.U.C. / C.I. Chofer</label>
                                    <input class="form-control-sm input-sm" type="text" name="ruc_chofer" id="ruc_chofer" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-3">
                                    <label for="razon_social_chofer" class="label-sm">Nombre Del Conductor</label>
                                    <input class="form-control-sm input-sm upper" type="text" name="razon_social_chofer" id="razon_social_chofer" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-3">
                                    <label for="domicilio_chofer" class="label-sm">Domicilio Del Conductor</label>
                                    <input class="form-control-sm input-sm upper" type="text" name="domicilio_chofer" id="domicilio_chofer" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="marca_vehiculo" class="label-sm">Marca Del Vehículo</label>
                                    <input class="form-control-sm input-sm upper" type="text" name="marca_vehiculo" id="marca_vehiculo" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="rua" class="label-sm">Chapa N°</label>
                                    <input class="form-control-sm input-sm upper" type="text" name="rua" id="rua" autocomplete="off">
                                </div>
                                <div class="form-group-sm col-md-2">
                                    <label for="km" class="label-sm">Km. Recorridos</label>
                                    <input class="form-control-sm input-sm" type="text" name="km" id="km" autocomplete="off">
                                </div>
                            </div>
                            <!-- FIN CABECERA -->

                            <!-- PRODUCTOS -->
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <h4>Productos</h4>
                                    <hr>

                                    <div class="form-row">
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label for="codigo">Código<sup class="ml-1">[F2]</sup></label>
                                            <input class="form-control input-sm text-center" type="text" name="codigo" id="codigo" autocomplete="off" onkeypress="return soloNumeros(event)">
                                        </div>
                                        <div class="form-group col-md-4 col-sm-12">
                                            <label for="producto">Producto<sup class="ml-1">[F3]</sup></label>
                                            <select id="producto" name="producto" class="form-control"></select>
                                        </div>
                                        <div class="form-group col-md-3 col-sm-12">
                                            <label for="lote">Lote<sup class="ml-1">[F4]</sup></label>
                                            <select id="lote" name="lote" class="form-control"></select>
                                        </div>
                                        <div class="form-group col-md-1 col-sm-12">
                                            <label for="cantidad">Cantidad</label>
                                            <input class="form-control input-sm text-right" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1">
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-primary btn-block" id="agregar-producto">Agregar</button>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12">
                                            <table id="tabla_detalle"></table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- FIN PRODUCTOS -->
                            <div class="form-group-sm pt-2">
                                <button type="button" class="btn btn-lg btn-info float-right" id="btn_guardar">Continuar<small><sup class="ml-1">[F5]</sup></small></button>
                            </div>

                        </div>
                    </div>
                </form>

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

                <!-- MODAL SOLICITUDES -->
                <div class="modal fade" id="modal_solicitudes" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-md2 modal-98 modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Solicitudes a deposito</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12 col-sm-12">
                                                <table id="tabla_solicitudes"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="float-right">
                                    <button id="agregar" class="btn btn-success">Agregar</button>
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
