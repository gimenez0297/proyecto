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
                            <h4>Datos Facturas</h4>
                            <hr>

                            <!-- CABECERA -->
                            <div class="form-row">
                                <div class="form-group col-md-3 col-sm-12">

                                    <label for="nro_factura" class="label-required">Nro. Factura</label>
                                    <div class="input-group">
                                        <input type="hidden" id="id_factura" name="id_factura">
                                        <input type="hidden" id="periodo" name="periodo">
                                        <input class="form-control input-sm" placeholder="000-000-0000000" type="text" name="nro_factura" id="nro_factura" maxlength=20 autocomplete="off" required>
                                        <div class="input-group-append">
                                           <button type="button" class="btn btn-primary" id="btn_buscar" data-toggle="modal" data-target="#modal_facturas" title="Buscar Facturas"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                        </div>
                                      </div>
                                </div>

                                <div class="form-group col-md-5 col-sm-12">
                                    <label for="cliente">Cliente / Razón Social</label>
                                    <input class="form-control input-sm" type="text" name="cliente" id="cliente" autocomplete="off" readonly>
                                    <input type="hidden" id="ruc" name="ruc">
                                </div>

                                <div class="form-group col-md-5 col-sm-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="devolucion_importe" name="devolucion_importe" value="1" disabled="disabled">
                                        <label class="custom-control-label" for="devolucion_importe"><span class="text-decoration-underline">D</span>evolución Importe</label>
                                    </div>
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
                                <button type="button" class="btn btn-lg btn-info float-right" id="btn_guardar">Continuar<small><sup class="ml-1">[F4]</sup></small></button>
                            </div>
                            
                        </div>
                    </div>
                </form>

                <!-- MODA PROVEEDORES -->
                <div class="modal fade" id="modal_facturas" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Buscar Facturas</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12 col-sm-12">
                                                <div id="toolbar_facturas">
                                                    <div class="alert alert-info" role="alert">
                                                        <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                    </div>
                                                </div>
                                                <table id="tabla_facturas"></table>
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
