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
                            <h4>Datos del Proveedor</h4>
                            <hr>

                            <!-- CABECERA -->
                            <div class="form-row">
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="ruc" class="label-required">R.U.C</label>
                                    <div class="input-group">
                                        <input type="hidden" id="id_proveedor" name="id_proveedor">
                                        <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="btn_buscar" data-toggle="modal" data-target="#modal_proveedores" title="Buscar Proveedor"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label for="proveedor">Proveedor / Razón Social</label>
                                    <select id="proveedor" name="proveedor" class="form-control"></select>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="numero">Número</label>
                                    <input class="form-control input-sm text-right" type="text" name="numero" id="numero" autocomplete="off" value="0" readonly>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="faltantes"></label>
                                    <button type="button" class="btn btn-danger btn-block" id="faltantes">Faltantes<sup class="ml-1">[F5]</sup></button>
                                </div>
                                <div class="form-group col-md-5 col-sm-12 d-none">
                                    <label for="contacto">Nombre De Fantasia</label>
                                    <input class="form-control input-sm" type="text" name="nombre_fantasia" id="nombre_fantasia" autocomplete="off" readonly>
                                </div>
                                <div class="form-group col-md-12 col-sm-12">
                                    <label for="nombre_fantasia">Observación</label>
                                    <textarea class="form-control input-sm upper" type="text" name="observacion" id="observacion" autocomplete="off" rows="2"></textarea>
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
                                        <div class="form-group col-md-6 col-sm-12">
                                            <label for="principio">Producto<sup class="ml-1">[F3]</sup></label>
                                            <select id="producto" name="producto" class="form-control"></select>
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label for="cantidad">Cantidad</label>
                                            <input class="form-control input-sm text-right" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1">
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-primary btn-block" id="agregar-producto">Agregar</button>
                                        </div>
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

                <!-- MODAL PRODUCTOS FALTANTES -->
                <div class="modal fade" id="modal_faltantes" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-98" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Productos Faltantes</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <ul class="nav nav-tabs" id="tabFaltantes" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="stock-minimo-tab" data-toggle="tab" href="#stock-minimo" role="tab" aria-controls="stock-minimo" aria-selected="true">STOCK MINIMO</a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="faltantes-solicitud-tab" data-toggle="tab" href="#faltantes-solicitud" role="tab" aria-controls="faltantes-solicitud" aria-selected="false">FALTANTES EN SOLICITUDES A ESTE DEPOSITO</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content mt-2" id="tabFaltantesContent">
                                            <div class="tab-pane fade show active" id="stock-minimo" role="tabpanel" aria-labelledby="stock-minimo-tab">
                                                <div id="toolbar_productos_faltantes">
                                                    <div class="form-inline" role="form">
                                                        <div class="form-group">
                                                            <select id="filtro_proveedores" class="form-control filtro_proveedores"></select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table id="tabla_faltantes"></table>
                                            </div>
                                            <div class="tab-pane fade" id="faltantes-solicitud" role="tabpanel" aria-labelledby="faltantes-solicitud-tab">
                                                <div id="toolbar_productos_faltantes2">
                                                    <div class="form-inline" role="form">
                                                        <div class="form-group">
                                                            <select id="filtro_proveedores2" class="form-control filtro_proveedores"></select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table id="tabla_faltantes2"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button id="agregar" class="btn btn-success">Agregar</button>
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