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
                            <div class="form-group">
                                    <select class="form-control" name="estado" id="estado">
                                            <option value="" disabled selected>Estado</option>
                                            <option value="2">ACTIVO</option>
                                            <option value="1">VENCIDO</option>
                                    </select>
                                </div>
                                
                                <div class="form-group ml-1">
                                    <?php 
                                        $option = "";
                                        $disabled = "";
                                        if (esAdmin($usuario->id_rol) === false) {
                                            $option = "<option value=\"$usuario->id_sucursal\">$datos_empresa->sucursal</option>";
                                            $disabled = "disabled";
                                        }
                                    ?>
                                    <select id="filtro_sucursal" class="form-control" <?php echo $disabled; ?>>
                                        <?php echo $option; ?>
                                    </select>
                                </div>

                                <button type="button" class="btn btn-primary ml-1" id="filtros_impresion" data-toggle="modal" data-target="#modal">Filtros<sup class="ml-1">[F1]</sup></button>
                                <button type="button" class="btn btn-primary ml-1" id="btn_exportar" title="Exportar Stock">
                                    <span>Exportar</span>
                                    <span><sup class="ml-1">[F3]</sup></span>
                                </button>
                                <button type="button" class="btn btn-primary ml-1" id="btn_imprimir" title="Imprimir Stock"><i class="fas fa-print"></i></button>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL IMPRESION -->
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
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="proveedor">Proveedor</label>
                                                            <select id="proveedor" name="proveedor" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="tipo">Tipo</label>
                                                            <select id="tipo" name="tipo" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="rubro">Rubro</label>
                                                            <select id="rubro" name="rubro" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="procedencia">Procedencia</label>
                                                            <select id="procedencia" name="procedencia" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="origen">Origen</label>
                                                            <select id="origen" name="origen" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="clasificacion">Clasificación</label>
                                                            <select id="clasificacion" name="clasificacion" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="presentacion">Presentación</label>
                                                            <select id="presentacion" name="presentacion" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="unidad_medida">Unidad de Medida</label>
                                                            <select id="unidad_medida" name="unidad_medida" class="form-control" ></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="marca">Marca</label>
                                                            <select id="marca" name="marca" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
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
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_lotes">
                                                            <div class="alert alert-info" role="alert">
                                                                <i class="fas fa-info-circle"></i> Producto: <span id="lotes_producto"></span>
                                                            </div>
                                                        </div>
                                                        <table id="tabla_lotes"></table>
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

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
