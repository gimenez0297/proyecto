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
                                        <input type="hidden" name="id_cliente" id="id_cliente">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <ul class="nav nav-tabs" id="tab" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" id="tab-1" data-toggle="tab" href="#tab-content-1" role="tab" aria-controls="datos-basicos" aria-selected="true">DATOS BÁSICOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="tab-2" data-toggle="tab" href="#tab-content-2" role="tab" aria-controls="principios" aria-selected="false">UBICACIONES</a>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content mt-2" id="tabContent">
                                                        <!-- Tab datos basicos -->

                                                        <div class="tab-pane fade show active" id="tab-content-1" role="tabpanel" aria-labelledby="datos-basicos-tab">

                                                            <div class="form-row">
                                                                <div class="form-group col-md-2 col-sm-6">
                                                                    <label for="tipo">Tipo</label>
                                                                    <select id="id_tipo" name="id_tipo" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="ruc" class="label-required">RUC / CI</label>
                                                                    <div class="input-group">
                                                                        <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required>
                                                                        <div class="input-group-append">
                                                                           <button type="button" class="btn btn-primary" id="btn_buscar"><i class="fa fa-search"></i> <sup class="ml-1">[F2]</sup></button>
                                                                        </div>
                                                                      </div>
                                                                </div>
                                                                <div class="form-group col-md-7 col-sm-12">
                                                                    <label for="razon_social" class="label-required">Apellido y Nombre / Razón Social</label>
                                                                    <input class="form-control input-sm upper" type="text" name="razon_social" id="razon_social" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="telefono">Teléfono</label>
                                                                    <input class="form-control input-sm" id="telefono" name="telefono" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="celular">Celular</label>
                                                                    <input class="form-control input-sm" id="celular" name="celular" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <label for="email">E-mail</label>
                                                                    <input class="form-control input-sm" id="email" name="email" type="email" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="obs">Observaciones</label>
                                                                    <textarea class="form-control input-sm upper" id="obs" name="obs" type="text" autocomplete="off"></textarea>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <div class="tab-pane fade show" id="tab-content-2" role="tabpanel" aria-labelledby="datos-basicos-tab">

                                                            <div class="form-row">
                                                                <input type="hidden" id="lat" name="latitud">
                                                                <input type="hidden" id="lng" name="longitud">

                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <div id="myMap" class="myMap" style="width:100%;height:350px;margin:auto"></div>
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <label for="direccion" class="label-required">Dirección</label>
                                                                    <input class="form-control input-sm" id="direccion" name="direccion" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <label for="referencia">Referencia</label>
                                                                    <input class="form-control input-sm" id="referencia" name="referencia" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-12">
                                                                    <label for="lat_lng" class="label-required">Coordenadas</label>
                                                                    <input class="form-control input-sm" id="lat_lng" name="lat_lng" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label>&nbsp;</label>
                                                                    <button type="button" class="btn btn-primary btn-block" id="agregar-direccion">Agregar</button>
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <table id="tabla_direcciones"></table>
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

    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD1NW86t_rj3bXEDBYplwbqE4ufPJohf34" type="text/javascript"></script> -->
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
