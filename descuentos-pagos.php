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
                            </div>
                        </div>
                        <table id="tabla"></table>

                    </div>
                </div>

                <!-- MODA PRINCIPAL -->
                <div class="modal fade" id="modal" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form class="form" id="formulario" method="post" enctype="multipart/form-data" action="">
                                <input type="hidden" name="hidden_id" id="hidden_id">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 col-sm-12">
                                            <ul class="nav nav-tabs" id="tabModal" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="nav_1" data-toggle="tab" href="#tab_1" role="tab" aria-controls="tab_1" aria-selected="true">DATOS BÁSICOS</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="nav_2" data-toggle="tab" href="#tab_2" role="tab" aria-controls="tab_2" aria-selected="false">FILTROS</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="nav_3" data-toggle="tab" href="#tab_3" role="tab" aria-controls="tab_3" aria-selected="false">PRODUCTOS</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="nav_4" data-toggle="tab" href="#tab_4" role="tab" aria-controls="tab_4" aria-selected="false">REMATE</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content mt-2" id="tabModalContent">
                                                <!-- Tab 1 -->
                                                <div class="tab-pane fade show active" id="tab_1" role="tabpanel" aria-labelledby="nav_1">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12">
                                                            <label for="descripcion" class="label-required">Descripción</label>
                                                            <input class="form-control input-sm upper" type="text" name="descripcion" id="descripcion" autocomplete="off" required> 
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label for="hora_inicio" class="label-required">Hora Inicio</label>
                                                            <input class="form-control input-sm" type="time" name="hora_inicio" id="hora_inicio" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label for="hora_fin" class="label-required">Hora Fin</label>
                                                            <input class="form-control input-sm" type="time" name="hora_fin" id="hora_fin" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label for="tipo_configuracion" class="label-required">Tipo</label>
                                                            <select id="tipo_configuracion" name="tipo_configuracion" class="form-control">
                                                                <option value="1">Rango de fechas</option>
                                                                <option value="2">Días</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <input type="hidden" id="fecha_inicio" name="fecha_inicio">
                                                            <input type="hidden" id="fecha_fin" name="fecha_fin">

                                                            <label for="fecha_inicio_fin" class="label-required">Fecha</label>
                                                            <div id="fecha_inicio_fin" class="text-center form-control d-block" tabindex="0">
                                                                <span></span><i class="fas fa-chevron-down ml-2 mr-1"></i>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="dias" class="label-required">Días</label>
                                                            <div class="input-group mt-2">
                                                                <!-- Domingo -->
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="dia_1" name="dias[]" value="1">
                                                                    <label class="custom-control-label" for="dia_1">Dom</label>
                                                                </div>
                                                                <!-- Lunes -->
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="dia_2" name="dias[]" value="2">
                                                                    <label class="custom-control-label" for="dia_2">Lun</label>
                                                                </div>
                                                                <!-- Martes -->
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="dia_3" name="dias[]" value="3">
                                                                    <label class="custom-control-label" for="dia_3">Mar</label>
                                                                </div>
                                                                <!-- Miércoles -->
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="dia_4" name="dias[]" value="4">
                                                                    <label class="custom-control-label" for="dia_4">Miér</label>
                                                                </div>
                                                                <!-- Jueves -->
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="dia_5" name="dias[]" value="5">
                                                                    <label class="custom-control-label" for="dia_5">Jue</label>
                                                                </div>
                                                                <!-- Viernes -->
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="dia_6" name="dias[]" value="6">
                                                                    <label class="custom-control-label" for="dia_6">Vier</label>
                                                                </div>
                                                                <!-- Sábado -->
                                                                <div class="custom-control custom-checkbox mr-2">
                                                                    <input type="checkbox" class="custom-control-input" id="dia_7" name="dias[]" value="7">
                                                                    <label class="custom-control-label" for="dia_7">Sáb</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Tab 2 -->
                                                <div class="tab-pane fade" id="tab_2" role="tabpanel" aria-labelledby="nav_2">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3">
                                                            <label for="metodo">Metodo Pago</label>
                                                            <select id="metodo" name="metodo" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label for="entidad">Entidad</label>
                                                            <select id="entidad" name="entidad" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label for="origen">Origen</label>
                                                            <select id="origen" name="origen" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label for="tipo">Tipo</label>
                                                            <select id="tipo" name="tipo" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label for="laboratorio">Laboratorio</label>
                                                            <select id="laboratorio" name="laboratorio" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label for="marca">Marca</label>
                                                            <select id="marca" name="marca" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label for="rubro">Rubro</label>
                                                            <select id="rubro" name="rubro" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-1">
                                                            <label for="controlado">Controlado</label>
                                                            <select id="controlado" name="controlado" class="form-control">
                                                                <option value="0">No</option>
                                                                <option value="1">Si</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-1">
                                                            <label for="porcentaje">Porcentaje</label>
                                                            <input class="form-control input-sm autonumeric"
                                                            type="text" name="porcentaje" id="porcentaje"
                                                            data-allow-decimal-padding="false"
                                                            data-decimal-character=","
                                                            data-digit-group-separator="."
                                                            data-maximum-value="100"
                                                            value="" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-1">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-primary btn-block" id="btn_agregar_tab_2"><i class="fas fa-plus"></i></button>
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <div id="toolbar_tab_2">
                                                                <div class="form-inline" role="form">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-success mr-1" id="btn_importar_tab_2" data-toggle="modal" data-target="#modal_importar"><i class="fas fa-download"></i></i><sup class="ml-1">[F1]</sup></button>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <table id="tabla_tab_2"></table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Tab 3 -->
                                                <div class="tab-pane fade" id="tab_3" role="tabpanel" aria-labelledby="nav_3">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12">
                                                            <div id="toolbar_tab_3">
                                                                <div class="form-inline" role="form">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-primary mr-1" id="btn_agregar_tab_3" data-toggle="modal" data-target="#modal_buscar"><i class="fa fa-plus"></i><sup class="ml-1">[F1]</sup></button>
                                                                        <button type="button" class="btn btn-success mr-1" id="btn_importar_tab_3" data-toggle="modal" data-target="#modal_importar"><i class="fas fa-download"></i></i><sup class="ml-1">[F2]</sup></button>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <table id="tabla_tab_3"></table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab 4 -->
                                                <div class="tab-pane fade" id="tab_4" role="tabpanel" aria-labelledby="nav_3">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3">
                                                            <label for="codigo_tab_4">Código<sup class="ml-1">[F1]</sup></label>
                                                            <input class="form-control input-sm" type="text" id="codigo_tab_4" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="producto_tab_4">Producto<sup class="ml-1">[F2]</sup></label>
                                                            <select id="producto_tab_4" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="lote_tab_4">Lote<sup class="ml-1">[F3]</sup></label>
                                                            <select id="lote_tab_4" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-1">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-primary btn-block" id="btn_agregar_tab_4"><i class="fa fa-plus"></i><sup class="ml-1">[F4]</sup></button>
                                                        </div>
                                                        <div class="form-group col-md-12">
                                                            <div id="toolbar_tab_4">
                                                                <div class="form-inline" role="form">
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-success" id="btn_importar_tab_4" data-toggle="modal" data-target="#modal_importar"><i class="fas fa-download"></i></i><sup class="ml-1">[F5]</sup></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <table id="tabla_tab_4"></table>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="eliminar" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>		
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="button" id="btn_guardar" class="btn btn-success">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL IMPORTAR -->
                <div class="modal fade" id="modal_importar" tabindex="-1" role="dialog" aria-labelledby="modalImportarLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalImportarLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="type_inport" name="type_inport">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <label for="importar">Campaña</label>
                                                <select id="importar" name="importar" class="form-control"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="button" id="btn_importar" class="btn btn-success">Importar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL BUSCAR -->
                <div class="modal fade" id="modal_buscar" tabindex="-1" role="dialog" aria-labelledby="modalBuscarLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-98" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalBuscarLabel"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_proveedor">Proveedor<sup class="ml-1">[F1]</sup></label>
                                                <select id="filtro_buscar_proveedor" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_origen">Origen<sup class="ml-1">[F2]</sup></label>
                                                <select id="filtro_buscar_origen" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_tipo">Tipo<sup class="ml-1">[F3]</sup></label>
                                                <select id="filtro_buscar_tipo" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_laboratorio">Laboratorio<sup class="ml-1">[F4]</sup></label>
                                                <select id="filtro_buscar_laboratorio" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_marca">Marca<sup class="ml-1">[F5]</sup></label>
                                                <select id="filtro_buscar_marca" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="filtro_buscar_rubro">Rubro<sup class="ml-1">[F6]</sup></label>
                                                <select id="filtro_buscar_rubro" class="form-control"></select>
                                            </div>
                                            <div class="form-group col-md-12 col-sm-12">
                                                <div id="toolbar_buscar">
                                                    <div class="form-inline" role="form">
                                                        <div class="form-group">
                                                            <!-- <select id="filtro_principios_activos" class="form-control"></select>
                                                            <button type="button" class="btn btn-info ml-1 acciones_tabla_productos" id="btn_detalle_modal_productos" title="Detalles Del Producto" disabled=""><i class="fa fa-eye"></i><sup class="ml-1">[F2]</sup></button> -->
                                                        </div>
                                                    </div>
                                                </div>
                                                <table id="tabla_buscar"></table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                <button type="button" id="btn_moda_buscar" class="btn btn-success">Agregar</button>
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
