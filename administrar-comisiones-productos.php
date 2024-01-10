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

                                <button type="button" class="btn btn-md btn-primary mr-1" id="filtros" data-toggle="modal" data-target="#modal">Filtros<sup class="ml-1">[F1]</sup></button>
                                <button type="button" class="btn btn-md btn-info mr-1" id="actualizar" data-toggle="modal" data-target="#modal_comision">Actualizar<sup class="ml-1">[F2]</sup></button>
                            </div>
                        </div>
                        
                        <table id="tabla"></table>

                        <!-- MODAL FILTROS -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button> -->
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-5 col-sm-12">
                                                            <label for="id_producto">Producto<sup class="ml-1">[F1]</sup></label>
                                                            <select id="id_producto" name="id_producto" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="tipo">Tipo<sup class="ml-1">[F2]</sup></label>
                                                            <select id="tipo" name="tipo" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="rubro">Rubro<sup class="ml-1">[F3]</sup></label>
                                                            <select id="rubro" name="rubro" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="procedencia">Procedencia<sup class="ml-1">[F4]</sup></label>
                                                            <select id="procedencia" name="procedencia" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="origen">Origen<sup class="ml-1">[F5]</sup></label>
                                                            <select id="origen" name="origen" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="clasificacion">Clasificación<sup class="ml-1">[F6]</sup></label>
                                                            <select id="clasificacion" name="clasificacion" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3  col-sm-12">
                                                            <label for="presentacion">Presentación<sup class="ml-1">[F7]</sup></label>
                                                            <select id="presentacion" name="presentacion" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="unidad_medida">Unidad de Medida<sup class="ml-1">[F8]</sup></label>
                                                            <select id="unidad_medida" name="unidad_medida" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="marca">Marca<sup class="ml-1">[F9]</sup></label>
                                                            <select id="marca" name="marca" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="laboratorio">Laboratorio<sup class="ml-1">[F10]</sup></label>
                                                            <select id="laboratorio" name="laboratorio" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="con_sin">Comisión<sup class="ml-1">[F11]</sup></label>
                                                            <select id="con_sin" name="con_sin" class="form-control">
                                                                <option value="1">CON COMISIÓN</option>
                                                                <option value="2">SIN COMISIÓN</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">  
                                            <button type="button" class="btn btn-success" id="filtrar">Filtrar<sup class="ml-1">[F12]</sup></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL COMISON -->
                        <div class="modal fade" id="modal_comision"  role="dialog" aria-labelledby="modalLabelComision" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabelComision"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_comision" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <div class="alert alert-warning" role="alert" id="alert_cantidad_registro"></div>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="comision" class="label-required">Comisión</label>
                                                            <input class="form-control input-sm autonumeric text-right"
                                                                type="text" name="comision" id="comision"
                                                                data-allow-decimal-padding="false"
                                                                data-decimal-character=","
                                                                data-digit-group-separator="."
                                                                data-maximum-value="100" autocomplete="off"  required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="concepto">Concepto</label>
                                                            <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-success" id="actuliza">Actualizar<sup class="ml-1">[F12]</sup></button>
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