<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include 'preloader.php'; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <?php //include 'titulo.php'; ?>
                <div class="row">
                    <div class="col-12">
                        <div id="toolbar">
                            <div class="form-inline" role="form">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal_principal">Agregar</button>
                                </div>
                                <div class="form-group ml-1">
                                    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                        <button type="button" class="btn btn-success" id="btn_expand_collapse" title="Expandir/Contraer"><i class="fas fa-stream"></i></button>
                                        <div class="btn-group" role="group">
                                            <button id="btn_expand_collapse_nodes" type="button" class="btn btn-success btn-sm dropdown-toggle" title="Más opciones" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                            <div class="dropdown-menu" aria-labelledby="btn_expand_collapse_nodes" id="btn_expand_collapse_nodes_items">
                                                <a class="dropdown-item" href="javascript:void(0)" data-tree-toogle="expandAll">Expandir Todos</a>
                                                <a class="dropdown-item" href="javascript:void(0)" data-tree-toogle="collapseAll">Contraer Todos</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table id="tabla" data-url="inc/administrar-menus-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-classes="table table-hover table-condensed" data-striped="true" data-icons="icons" data-show-fullscreen="true"></table>

                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal_principal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="false">
                            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="hidden_id_menu" id="hidden_id_menu">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6 col-sm-6">
                                                            <label for="menu_padre">Menú Padre</label>
                                                            <select id="menu_padre" name="menu_padre" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-6">
                                                            <label for="menu" class="label-required">Menú</label>
                                                            <input class="form-control input-sm" type="text" name="menu" id="menu" autocomplete="off" required="">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-6">
                                                            <label for="titulo">Título</label>
                                                            <input class="form-control input-sm" type="text" name="titulo" id="titulo" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-6">
                                                            <label for="url">Url</label>
                                                            <input class="form-control input-sm" type="text" name="url" id="url" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-5">
                                                            <label for="icono">Icono</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="icono_preview"></span>
                                                                </div>
                                                                <input class="form-control input-sm" type="text" name="icono" id="icono" autocomplete="off">
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_iconos" title="Buscar Icono"><i class="fa fa-search"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-3">
                                                            <label for="orden" class="label-required">Orden Ubicación</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="orden_preview"></span>
                                                                </div>
                                                                <input class="form-control input-sm" type="text" name="orden" id="orden" autocomplete="off" required="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-4">
                                                            <label for="estado">Estado</label>
                                                            <select id="estado" name="estado" class="form-control">
                                                                <option value="Habilitado">Habilitado</option>
                                                                <option value="Deshabilitado">Deshabilitado</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <p class="pt-3"><b>Nota:</b> Al deshabilitar un menú los submenús que este contenga no serán visualizados por mas que esten habilitados.</p>
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
                        
                    </div><!-- End col-12 -->
                </div><!-- End Page Content -->
            </div><!-- End Container fluid  -->
        </div><!-- End Page wrapper  -->
       <?php include 'footer.php'; ?>
    </div><!-- End Wrapper -->
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
