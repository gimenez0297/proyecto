<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <style>
        .modal-body {
            min-height: 580px;
        }
        .dropzone {
            min-height: 460px;
        }
        .modal-body.cantidad{
            min-height: 100px
        }
    </style>
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
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F1]</sup></button>
                                    <button type="button" class="btn btn-primary mx-2" id="exportar" style="display: grid; grid-template-columns: 2fr 1fr;">
                                        <span>Exportar</span>
                                        <span><sup class="ml-1">[F3]</sup></span>
                                    </button>
                                    <span class="pop-export text-warning"><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>
                        <!-- MODAL IMPRIMIR CODIGOS -->
                        <div class="modal fade" id="modal_imprimir" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="modalLabelImprimir" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabelImprimir">Imprimir Código</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_imprimir" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body cantidad">
                                            <div class="row">
                                                <div class="col-md-5 col-sm-5">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="cantidad" class="label-required">Cantidad</label>
                                                            <input class="form-control input-sm" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-7 col-sm-7">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="id_lote" class="label-required">Lote</label>
                                                            <select class="form-control" name="id_lote" id="id_lote" required></select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Imprimir</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
						
                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_producto" id="id_producto">
                                        <input type="hidden" name="dropurl" id="dropurl">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <ul class="nav nav-tabs" id="tabProductos" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" id="datos-basicos-tab" data-toggle="tab" href="#datos-basicos" role="tab" aria-controls="datos-basicos" aria-selected="true">DATOS BÁSICOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="principios-tab" data-toggle="tab" href="#principios" role="tab" aria-controls="principios" aria-selected="false">PRINCIPIOS ACTIVOS</a>
                                                        </li>

                                                         <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="clasificacion-tab" data-toggle="tab" href="#clasificaciones" role="tab" aria-controls="clasificaciones" aria-selected="false">CLASIFICACION</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="proveedores-tab" data-toggle="tab" href="#proveedores" role="tab" aria-controls="proveedores" aria-selected="false">PROVEEDORES</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="nav-cuatro" data-toggle="tab" href="#tab-cuatro" role="tab" aria-controls="tab-cuatro" aria-selected="false">NIVELES DE STOCK</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="imagenes-tab" data-toggle="tab" href="#imagenes" role="tab" aria-controls="imagenes" aria-selected="false">DESCRIPCIÓN E IMAGENES</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="seo-tab" data-toggle="tab" href="#seo" role="tab" aria-controls="contact" aria-selected="false">SEO</a>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content mt-2" id="tabProductosContent">
                                                        <!-- Tab datos basicos -->
                                                        <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel" aria-labelledby="datos-basicos-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="codigo">Código</label>
                                                                    <input class="form-control input-sm upper" type="text" name="codigo" id="codigo" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-9 col-sm-12">
                                                                    <label for="producto" class="label-required">Producto</label>
                                                                    <input class="form-control input-sm upper" type="text" name="producto" id="producto" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="tipo" class="label-required">Tipo</label>
                                                                    <select id="tipo" name="tipo" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="rubro">Rubro</label>
                                                                    <select id="rubro" name="rubro" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="procedencia" class="label-required">Procedencia</label>
                                                                    <select id="procedencia" name="procedencia" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="origen" class="label-required">Origen</label>
                                                                    <select id="origen" name="origen" class="form-control" required></select>
                                                                </div>
                                                              
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="presentacion" class="label-required">Presentación</label>
                                                                    <select id="presentacion" name="presentacion" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="unidad_medida" class="label-required">Unidad de Medida</label>
                                                                    <select id="unidad_medida" name="unidad_medida" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="conservacion" class="label-required">Forma de Conservación</label>
                                                                    <select id="conservacion" name="conservacion" class="form-control" required>
                                                                        <option value="1">NORMAL</option>
                                                                        <option value="2">REFRIGERADO</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="marca">Marca</label>
                                                                    <select id="marca" name="marca" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="laboratorio">Laboratorio</label>
                                                                    <select id="laboratorio" name="laboratorio" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="precio" class="label-required">Precio</label>
                                                                    <input class="form-control input-sm text-right" type="text" name="precio" id="precio" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="cantidad_fracciones" class="label-required">Fracciones</label>
                                                                    <input class="form-control input-sm text-right" type="text" name="cantidad_fracciones" 
                                                                    id="cantidad_fracciones" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" disabled required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="precio_fraccionado" class="label-required">Precio Fraccionado</label>
                                                                    <input class="form-control input-sm text-right" type="text" name="precio_fraccionado" 
                                                                    id="precio_fraccionado" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" disabled required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="comision">Comisión</label>
                                                                    <div class="input-group mb-1">
                                                                        <input class="form-control input-sm text-right" id="comision" name="comision" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text">%</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <label for="comision_concepto">Concepto de Comisión</label>
                                                                    <input class="form-control input-sm upper" type="text" name="comision_concepto" id="comision_concepto" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="iva" class="label-required">IVA</label>
                                                                    <div class="input-group mb-1">
                                                                        <select name="iva" id="iva" class="select2" required>
                                                                            <option value="1">EXENTAS</option>
                                                                            <option value="2">5%</option>
                                                                            <option value="3">10%</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="indicaciones">Indicaciones</label>
                                                                    <input class="form-control input-sm upper" type="text" name="indicaciones" id="indicaciones" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="observaciones">Observaciones</label>
                                                                    <textarea class="form-control input-sm upper" name="observaciones" id="observaciones" autocomplete="off" rows="2"></textarea>
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="controlado" name="controlado" value="1">
                                                                        <label class="custom-control-label" for="controlado">Controlado</label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-12">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="descuento_fraccionado" name="descuento_fraccionado" value="1">
                                                                        <label class="custom-control-label" for="descuento_fraccionado">Aplicar descuento a productos fraccionados</label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="fuera_de_plaza" name="fuera_de_plaza" value="1">
                                                                        <label class="custom-control-label" for="fuera_de_plaza">Fuera De Plaza</label>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="fraccionado" name="fraccionado" value="1">
                                                                        <label class="custom-control-label" for="fraccionado">Fraccionado</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Tab imagenes y descripción -->
                                                        <div class="tab-pane fade" id="imagenes" role="tabpanel" aria-labelledby="imagenes-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <div id="contenDrop"></div>
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <label for="descripcion">Descripción</label>
                                                                    <textarea class="form-control input-sm summernote" name="descripcion" id="descripcion" autocomplete="off"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Tab Principios Activos -->
                                                        <div class="tab-pane fade" id="principios" role="tabpanel" aria-labelledby="principios-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-10 col-sm-12">
                                                                    <label for="principio">Principio Activo</label>
                                                                    <select id="principio" name="principio" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label>&nbsp;</label>
                                                                    <button type="button" class="btn btn-primary btn-block" id="agregar-principio">Agregar</button>
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <table id="tabla_principios"></table>
                                                                </div>
                                                            </div>
                                                        </div>


                                                          <!-- Tab Clasificacion -->
                                                        <div class="tab-pane fade" id="clasificaciones" role="tabpanel" aria-labelledby="clasificacion-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-10 col-sm-12">
                                                                   <label for="clasificacion">Clasificación</label>
                                                                    <select id="clasificacion" name="clasificacion" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label>&nbsp;</label>
                                                                    <button type="button" class="btn btn-primary btn-block" id="agregar-clasificacion">Agregar</button>
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <table id="productos_clasificaciones"></table>
                                                                </div>
                                                            </div>
                                                        </div>



                                                        <!-- Tab Proveedores -->
                                                        <div class="tab-pane fade" id="proveedores" role="tabpanel" aria-labelledby="seo-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <label for="proveedor">Proveedor</label>
                                                                    <select id="proveedor" name="proveedor" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label for="codigo_proveedor">Código</label>
                                                                    <input class="form-control input-sm upper" type="text" name="codigo_proveedor" id="codigo_proveedor" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label for="costo">Costo</label>
                                                                    <input class="form-control input-sm text-right" type="text" name="costo" id="costo" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)">
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label>&nbsp;</label>
                                                                    <button type="button" class="btn btn-primary btn-block" id="agregar-proveedor">Agregar<sup class="ml-1">[F2]</sup></button>
                                                                    <!-- <button type="button" class="btn btn-primary btn-block" id="agregar-proveedor">Agregar</button> -->
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <table id="tabla_proveedores"></table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Tab Cuatro -->
                                                        <div class="tab-pane fade" id="tab-cuatro" role="tabpanel" aria-labelledby="nav-cuatro" tabindex="-1">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <table id="tabla_cuatro"></table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Tab SEO -->
                                                        <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="copete">Breve Descripción</label>
                                                                    <input class="form-control input-sm" type="text" name="copete" id="copete" autocomplete="off" disabled="" required>
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="etiquetas">Etiquetas</label>
                                                                    <select id="etiquetas" name="etiquetas[]" class="form-control" multiple disabled=""></select>
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="web" name="web" value="1">
                                                                        <label class="custom-control-label" for="web">Visualizar en Página Web</label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-md-10 col-sm-12">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" class="custom-control-input" id="destacar" name="destacar" value="1">
                                                                        <label class="custom-control-label" for="destacar">Destacar Producto Web</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>		
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button id="guardar" type="submit" class="btn btn-success">Guardar</button>
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
