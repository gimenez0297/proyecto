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
                                <div class="form-group mr-1">
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <select id="filtro_sucursal" name="filtro_sucursal" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>
						
                        <!-- MODAL PRODUCTOS -->
                        <div class="modal fade" id="modal_productos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Productos</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_productos">
                                                            <div class="alert alert-info" role="alert" id="toolbar_productos_text"></div>
                                                        </div>
                                                        <table id="tabla_productos"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL IMPRIMIR CODIGOS -->
                        < <div class="modal fade" id="modal_imprimir" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="modalLabelImprimir" aria-hidden="true">
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

                        <!-- MODAL ARCHIVOS -->
                        <div class="modal fade" id="modal_archivos" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Archivos Adjuntos</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">

                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12 d-flex justify-content-between">
                                                        <div class="alert alert-info" role="alert" id="toolbar_archivos_text"></div>
                                                        <input type="file" class="d-none" name="archivos" id="archivos" multiple accept="image/png,image/jpeg">
                                                        <button type="button" class="btn btn-primary" id="agregar-archivos"><i class="fas fa-plus mr-1"></i>Agregar</button>
                                                    </div>
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <table id="tabla_archivos"></table>

                                                        <template id="template">
                                                            <div class="col-md-4 col-sm-12 mt-3">
                                                                <div class="card border position-relative">
                                                                        <div class="w-100 text-right position-absolute">
                                                                            <button class="btn btn-outline-danger btn-preview" title="Eliminar" value="%ID%"><span class="fa fa-times"></span></button>
                                                                        </div>
                                                                        <div class="text-center" style="height:200px;overflow:hidden;">
                                                                            <a data-fancybox='gallery' href="%FANCY%">
                                                                                <img src="%PREVIEW%" alt="" class="mx-auto img-fluid h-100">
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>

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
