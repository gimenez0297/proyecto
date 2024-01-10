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
                                    <select id="filtro_sucursal" name="filtro_sucursal" class="form-control">
                                        <?php echo "<option value=\"$datos_empresa->id_sucursal\">$datos_empresa->sucursal</option>" ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-keyboard="false" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-98" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                        <input type="hidden" name="hidden_id_proveedor" id="hidden_id_proveedor">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <ul class="nav nav-tabs" id="tabDescuentos" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" id="datos-descuentos-tab" data-toggle="tab" href="#datos-descuentos" role="tab" aria-controls="datos-descuentos" aria-selected="true">DATOS DESCUENTOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="proveedor-principal-tab" data-toggle="tab" href="#proveedor-principal" role="tab" aria-controls="proveedor-principal" aria-selected="false">PRODUCTOS</a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content mt-2" id="tabDescuentosContent">
                                                        <div class="tab-pane fade show active" id="datos-descuentos" role="tabpanel" aria-labelledby="datos-basicos-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="metodo_pago">Método De Pago</label>
                                                                    <select id="metodo_pago" name="metodo_pago" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="origen">Origen</label>
                                                                    <select id="origen" name="origen" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="tipo">Tipo</label>
                                                                    <select id="tipo" name="tipo" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="laboratorio">Laboratorio</label>
                                                                    <select id="laboratorio" name="laboratorio" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="marca">Marca</label>
                                                                    <select id="marca" name="marca" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="rubro">Rubro</label>
                                                                    <select id="rubro" name="rubro" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <label for="controlado">Controlado</label>
                                                                    <select id="controlado" name="controlado" class="form-control">
                                                                        <option value="0">No</option>
                                                                        <option value="1">Si</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label for="porcentaje">Porcentaje</label>
                                                                    <input class="form-control input-sm autonumeric"
                                                                    type="text" name="porcentaje" id="porcentaje"
                                                                    data-allow-decimal-padding="false"
                                                                    data-decimal-character=","
                                                                    data-digit-group-separator="."
                                                                    data-maximum-value="100"
                                                                    value="" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label>&nbsp;</label>
                                                                    <button type="button" class="btn btn-primary btn-block" id="agregar_detalle">Agregar<sup class="ml-1">[F1]</sup></button>
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <div id="toolbar_detalle_pro">
                                                                        <div class="form-inline" role="form">
                                                                            <div class="form-group">
                                                                                <div class="alert alert-info" role="alert" id="toolbar_detalle"></div>
                                                                                <button type="button" class="btn btn-success ml-1" id="btn_sucursales_tab_1" data-toggle="modal" data-target="#modal_sucursales"><i class='fas fa-home'></i></i></i><sup class="ml-1">[F2]</sup></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <table id="tabla_detalle"></table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="proveedor-principal" role="tabpanel" aria-labelledby="proveedor-principal-tab">
                                                            <div id="toolbar_1">
                                                                <div class="form-inline" role="form">
                                                                    <div class="form-group">
                                                                        <!-- <div class="alert alert-info" role="alert" id="toolbar_detalles_text"></div> -->
                                                                        <select id="producto" name="producto" class="form-control"></select>
                                                                        <button type="button" class="btn btn-primary ml-1" id="agregar_producto">Agregar<sup class="ml-1">[F1]</sup></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <table id="tabla-proveedor"></table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="button" id="btn_guardar" class="btn btn-success">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal fade" id="modal_sucursales" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Sucursales Destino</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form class="form default-validation" id="formulario_sucursales" method="post" enctype="multipart/form-data" action="">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <div class="form-row">
                                                <div class="form-group col-md-10 col-sm-12">
                                                    <label for="sucursales" class="label-required">Sucursal</label>
                                                    <select id="sucursales" name="sucursales" class="form-control"></select>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-12">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-primary btn-block" id="agregar-sucursal"><i class="fas fa-plus"></i></button>
                                                </div>
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <table id="tabla_sucursales"></table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"> Cerrar</button>
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
