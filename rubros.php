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
                            </div>
                        </div>
                        <table id="tabla"></table>
                        <div id='zoom_modal'></div>
						
                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_rubro" id="id_rubro">
                                        <input type="hidden" name="change_logo" id="change_logo">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="rubro" class="label-required">Rubro</label>
                                                            <input class="form-control input-sm upper" type="text" name="rubro" id="rubro" autocomplete="off" required="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-9 col-sm-5">
                                                    <label for="icono" class="label-required">Icono</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="icono_preview"></span>
                                                        </div>
                                                        <input class="form-control input-sm" type="text" name="icono" id="icono" autocomplete="off" disabled required>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-primary iconos" data-toggle="modal" data-target="#modal_iconos" title="Buscar Icono"><i class="fa fa-search"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="orden" class="label-required">Orden</label>
                                                            <input class="form-control input-sm text-right" type="text" name="orden" id="orden" autocomplete="off" required="" value="0" disabled required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <label for="logo_" class="label-required">Logo</label>
                                                    <div class="custom-file">
                                                      <input type="file" class="custom-file-input" id="logo" name="logo" disabled="" required>
                                                      <label class="custom-file-label" for="logo">Elejir Imagen</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 form-group text-center">
                                                     <div style="position: relative; display: inline-block;">
                                                        <button id="eliminarfoto" type="button" class="btn btn-danger btn-propio btn-sm"><i class="fas fa-times"></i></button>
                                                        <img width='150' class='rounded' src='dist/images/sin-foto.jpg' alt='Sin Foto' id="img_preview">
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-12 col-sm-12">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="web" name="web" value="1">
                                                        <label class="custom-control-label" for="web">Visualizar en Página Web</label>
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

                        <!-- MODA ICONOS -->
                        <div class="modal fade" id="modal_iconos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="false">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Buscar Icono</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_iconos">
                                                            <span class="h4 text-primary">Haga click sobre un icono para seleccionarlo</span>
                                                        </div>
                                                        <table id="tabla_iconos" data-toolbar="#toolbar_iconos" data-search="true" data-show-refresh="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-classes="table table-hover table-condensed" data-striped="true" data-icons="icons"></table>

                                                        <style>
                                                            .card:hover {
                                                                cursor: pointer;
                                                                background-color: var(--primary);
                                                                color: #fff;
                                                            }
                                                            .card .lead {
                                                                font-size: 12px;
                                                            }
                                                        </style>

                                                        <template id="menuTemplate">
                                                            <div class="col-md-2 col-sm-12 mt-1">
                                                                <div class="card card-icono" data-icon='%ICON%'>
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12 text-center h2">
                                                                                %CODE%
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <!-- <h3 class="mb-0 text-truncated">%NAME%</h3> -->
                                                                                <p class="lead text-center text-truncated h8">%CLASS%</p>
                                                                            </div>
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
