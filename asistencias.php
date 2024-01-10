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
                                    <button type="button" class="btn btn-primary mr-1" id="btn_importar" title="Importar Excel"><i class="fas fa-file-excel mr-1"></i> Importar<sup class="ml-1">[F1]</sup></button>
                                    <button type="button" class="btn btn-primary mr-1" id="btn_agregar" title="Agregar Asistencia"></i> Agregar<sup class="ml-1">[F2]</sup></button>
                                </div>
                                <div class="form-group ml-1">
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>
						
                        <!-- MODAL IMPORTAR -->
                        <div class="modal fade" id="modal_asistencias" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Importar</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_marca" id="id_marca">
                                        <input type="hidden" name="change_logo" id="change_logo">
                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                        <div class="modal-body">

                                            <div class="row">
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <label for="documento" class="label-required">Archivo</label>
                                                    <div class="custom-file">
                                                      <input type="file" class="custom-file-input" id="documento" name="documento" required="">
                                                      <label class="custom-file-label form-control" for="documento">Seleccionar archivo</label>
                                                    </div>
                                                    <input type="hidden" name="change_documento" id="change_documento">
                                                    <input type="hidden" name="url_documento" id="url_documento">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>       
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-success" id="agregar-asistencia">Agregar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL AGREGAR-->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_asistencia" method="post" enctype="multipart/form-data" action="">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                            <div class="form-group col-md-9 col-sm-6 required">
                                                                <label for="id_funcionario" class="label-required">Funcionario</label>
                                                                <select id="id_funcionario" name="id_funcionario" class="form-control"></select required>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6 required">
                                                                <label for="fecha" class="label-required">Fecha</label>
                                                                <input class="form-control input-sm" type="date" name="fecha" id="fecha" autocomplete="off" required>
                                                            </div>

                                                            <div class="form-group col-md-3 col-sm-6 required">
                                                                <label for="llegada" class="label-required">Llegada</label>
                                                                <div class="input-group mb-1">
                                                                    <input class="form-control input-sm" id="llegada" name="llegada" type="time" autocomplete="off" required>
                                                                    
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6 required">
                                                                <label for="salida" class="label-required">Salida</label>
                                                                <div class="input-group mb-1">
                                                                    <input class="form-control input-sm" id="salida" name="salida" type="time" autocomplete="off" required>
                                                                    
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6 ">
                                                                <label for="normal">Normal Trab.</label>
                                                                <div class="input-group mb-1">
                                                                    <input class="form-control input-sm" id="normal" name="normal" type="text" autocomplete="off" value="0" > 
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6 ">
                                                                <label for="extra">Horas Extra</label>
                                                                <input class="form-control input-sm" id="extra" name="extra" type="text" autocomplete="off" >
                                                            </div>
                                                        </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="observacion">Observaciones</label>
                                                            <textarea class="form-control input-sm upper" id="observacion" name="observacion" type="text" autocomplete="off"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>       
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-success">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL METODOS -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-sm modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Horarios</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <!-- <div id="toolbar_detalle">
                                                            <div class="alert alert-info" role="alert" id="toolbar_titulo"></div>
                                                        </div> -->
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

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
