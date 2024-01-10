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
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F1]</sup></button>
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
						
                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_reposo" id="id_reposo">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <ul class="nav nav-tabs" id="tab" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" id="datos-basicos-tab" data-toggle="tab" href="#datos-basicos" role="tab" aria-controls="datos-basicos" aria-selected="true">DATOS BÁSICOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="archivos-tab" data-toggle="tab" href="#archivos" role="tab" aria-controls="archivos" aria-selected="false">DOCUMENTO</a>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content mt-2" id="tabContent">
                                                        <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel" aria-labelledby="datos-basicos-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6 col-sm-6">
                                                                    <label for="id_funcionario" class="label-required">Funcionario</label>
                                                                    <select id="id_funcionario" name="id_funcionario" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="fecha_desde" class="label-required">Fecha Desde</label>
                                                                    <input class="form-control input-sm" type="date" name="fecha_desde" id="fecha_desde" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="fecha_hasta" class="label-required">Fecha Hasta</label>
                                                                    <input class="form-control input-sm" type="date" name="fecha_hasta" id="fecha_hasta" autocomplete="off" required>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="observacion">Observaciones</label>
                                                                    <textarea class="form-control input-sm upper" id="observacion" name="observacion" type="text" autocomplete="off"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- DOCUMENTO -->
                                                        <div class="tab-pane fade" id="archivos" role="tabpanel" aria-labelledby="archivos-tab">
                                                         <div class="form-row">
                                                                <div class="form-group col-lg-12">
                                                                    <div class="d-flex">
                                                                        <label for="archivo" class="label-required mr-auto">Documento: </label>
                                                                        <i class="fas fa-exclamation-circle text-warning accept-extensions" data-toggle="popover"></i>
                                                                    </div>
                                                                    <div class="input-group">
                                                                        <div class="custom-file">
                                                                            <input type="file" class="custom-file-input" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png" multiple="false">
                                                                            <input type="hidden" value="0" id="change_archivo" name="change_archivo">
                                                                            <label class="custom-file-label form-control" for="archivo">Seleccionar Archivo...</label>
                                                                        </div>
                                                                        <div class="input-group-append">
                                                                            <a href="#" class="btn btn-success disabled" id="ver_archivo" type="button" title="Ver Archivo" data-action="view" disabled><i class="fas fa-binoculars p-button-file"></i></a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                                                    <button type="button" class="btn btn-danger btn-propio btn-sm eliminar_archivo" title="Eliminar Archivo" data-action="delete" id="eliminar_archivo" style="display:none;"><i class="fas fa-times"></i></button>
                                                                    <img class='rounded file_preview' src='dist/images/sin-foto.jpg' style="width:100%;" alt='Sin Foto' id="img_preview">
                                                                    <iframe src="" id="pdf_preview" class="rounded file_preview" style="display:none;width:100%;height: 200px;overflow: auto;"></iframe>
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
                                            <button type="submit" class="btn btn-success">Guardar</button>
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
