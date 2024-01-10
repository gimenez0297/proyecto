<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <div id="toolbar">
                            <div class="form-inline" role="form">
                                <!-- <div class="form-group mr-1">
                                    <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>></select>
                                </div> -->

                                <button type="button" class="btn btn-primary ml-1" id="filtros_impresion" data-toggle="modal" data-target="#modal">Filtros Avanzados<sup class="ml-1">[F1]</sup></button>
                                <!-- <button type="button" class="btn btn-success ml-2" id="imprimir"><i class="fas fa-print"></i> Imprimir<sup class="ml-1">[F2]</sup></button> -->
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL FILTRO -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered modal-85" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <!-- <div class="form-group col-md-2 col-sm-6">
                                                            <label for="fecha_desde" class="label-required">Fecha Desde</label>
                                                            <input class="form-control input-sm" type="date" name="fecha_desde" id="fecha_desde" autocomplete="off" required="">
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-6">
                                                            <label for="fecha_hasta" class="label-required">Fecha Hasta</label>
                                                            <input class="form-control input-sm" type="date" name="fecha_hasta" id="fecha_hasta" autocomplete="off" required="">
                                                        </div> -->
                                                        <!-- <div class="form-group col-md-6 col-sm-12">
                                                            <label for="deposito">Depósito</label>
                                                            <select id="deposito" name="deposito" class="form-control"></select>
                                                        </div> -->
                                                        
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <?php 
                                                                $option = "";
                                                                $disabled = "";
                                                                if (esAdmin($usuario->id_rol) === false) {
                                                                    $option = "<option value=\"$usuario->id_sucursal\">$datos_empresa->sucursal</option>";
                                                                    $disabled = "disabled";
                                                                }
                                                            ?>
                                                            <label for="deposito">Depósito</label>
                                                            <select id="deposito" name="deposito" class="form-control" <?php echo $disabled; ?>>
                                                                <?php echo $option; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="producto">Producto</label>
                                                            <select id="id_producto" name="id_producto" class="form-control" required=""></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="tipo">Tipo</label>
                                                            <select id="tipo" name="tipo" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="rubro">Rubro</label>
                                                            <select id="rubro" name="rubro" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="procedencia">Procedencia</label>
                                                            <select id="procedencia" name="procedencia" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="origen">Origen</label>
                                                            <select id="origen" name="origen" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="clasificacion">Clasificación</label>
                                                            <select id="clasificacion" name="clasificacion" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="presentacion">Presentación</label>
                                                            <select id="presentacion" name="presentacion" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="unidad_medida">Unidad de Medida</label>
                                                            <select id="unidad_medida" name="unidad_medida" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="marca">Marca</label>
                                                            <select id="marca" name="marca" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="laboratorio">Laboratorio</label>
                                                            <select id="laboratorio" name="laboratorio" class="form-control"></select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">  
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="button" class="btn btn-success" id="filtrar">Filtrar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- MODAL DETALLE -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Producto</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div class="form-inline" role="form">
                                                            <input type="hidden" id="produc_click">
                                                            <div id="toolbar_detalle">
                                                                <div class="form-inline" role="form">
                                                                <div class="form-group">
                                                                    <input type="hidden" id="desde">
                                                                    <input type="hidden" id="hasta">
                                                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group ml-2">
                                                                    <select id="deposito_str" name="deposito_str" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <button type="button" class="btn btn-success ml-2" id="imprimir" disabled><i class="fas fa-print"></i><sup class="ml-1">[F2]</sup></button>
                                                                </div>
                                                                <div class="form-group alert alert-info ml-2" role="alert">
                                                                    <i class="fas fa-info-circle mr-1"></i> Producto: <span id="producto_detalle"></span>
                                                                </div>
                                                                </div>
                                                            </div>
                                                                
                                                        </div>
                                                        <table id="tabla_detalle"></table>
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
            </div>
        </div>
       <?php include 'footer.php'; ?>
    </div>
    <script type="text/javascript">

        var fecha_actual = '<?php echo date("Y-m-d"); ?>';

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
