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
                                <div class="form-group col-md-8 col-sm-12">
                                    <label for="orden_compra" class="label-required">RUC | Proveedor</label>
                                    <div class="input-group">
                                        <input type="hidden" id="id_proveedor" name="id_proveedor">
                                        <select id="proveedor" name="proveedor" class="form-control"></select>
                                        <div class="input-group-append">
                                           <button type="button" class="btn btn-primary" id="btn_buscar_solicitud" data-toggle="modal" data-target="#modal_solicitudes" title="Buscar Solicitud De Compra"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                        </div>
                                        <div class="input-group-append">
                                           <button type="button" class="btn btn-info" id="btn_buscar" data-toggle="modal" data-target="#modal_proveedores" title="Buscar Proveedor"><i class="fa fa-search"></i><sup class="ml-1">[F2]</sup></button>
                                        </div>
                                      </div>
                                </div>
                                <div class="form-group col-md-4 col-sm-12 d-none">
                                    <label for="nombre_fantasia">Nombre De Fantasia</label>
                                    <input class="form-control input-sm" type="text" name="nombre_fantasia" id="nombre_fantasia" autocomplete="off" readonly>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="condicion">Condición</label>
                                    <select id="condicion" name="condicion" class="form-control">
                                        <option value="1">Contado</option>
                                        <option value="2">Crédito</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="numero">Número</label>
                                    <input class="form-control input-sm text-right" type="text" name="numero" id="numero" autocomplete="off" value="0" readonly>
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
                                <button type="button" class="btn btn-lg btn-info float-right" id="btn_guardar">Continuar<small><sup class="ml-1">[F3]</sup></small></button>
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

                <!-- MODAL ORDENES DE COMPRRA -->
                <div class="modal fade" id="modal_solicitudes" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Buscar Solicitud de Compra</h5>
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


            </div>
        </div>
       <?php include 'footer.php'; ?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
