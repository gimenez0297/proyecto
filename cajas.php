<?php include 'header.php';?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php";?>
    <div id="main-wrapper">
        <?php include 'topbar.php';include 'leftbar.php'?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <div id="toolbar">
                            <div class="form-inline" role="form">
                                <div class="form-group mr-1">
                                    <select id="filtro_sucursal" name="filtro_sucursal" class="form-control"></select>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F1]</sup></button>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_caja" id="id_caja">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="col-md-12 col-sm-12">
                                                            <ul class="nav nav-tabs" id="tab" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <a class="nav-link active" id="datos-basicos-tab" data-toggle="tab" href="#datos-basicos" role="tab" aria-controls="datos-basicos" aria-selected="true">DATOS BÁSICOS</a>
                                                                </li>
                                                                <li class="nav-item" role="presentation">
                                                                    <a class="nav-link" id="usuarios-tab" data-toggle="tab" href="#usuarios" role="tab" aria-controls="usuarios" aria-selected="false">USUARIOS</a>
                                                                </li>
                                                            </ul>

                                                            <div class="tab-content mt-2" id="tabContent">
                                                                <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel" aria-labelledby="datos-basicos-tab">
                                                                    <div class="form-row">
                                                                        <div class="form-group col-md-12 col-sm-12">
                                                                            <label for="numero" class="label-required">Número</label>
                                                                            <input class="form-control input-sm upper" type="text" name="numero" id="numero" autocomplete="off" required="">
                                                                        </div>
                                                                        <div class="form-group col-md-12 col-sm-12" required>
                                                                            <label for="id_sucursal" class="label-required">Sucursal</label>
                                                                            <select id="id_sucursal" name="id_sucursal" class="form-control" required></select>
                                                                        </div>
                                                                        <div class="form-group col-md-12 col-sm-12" required>
                                                                            <label for="efectivo">Sencillo</label>
                                                                            <input class="form-control input-sm text-right" type="text" name="efectivo" id="efectivo" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                                        </div>
                                                                        <div class="form-group col-md-12 col-sm-12" required>
                                                                            <label for="tope">Tope Efectivo</label>
                                                                            <input class="form-control input-sm text-right" type="text" name="tope" id="tope" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                                        </div>
                                                                        <div class="form-group col-md-12 col-sm-12 required">
                                                                            <label for="observacion">Observación</label>
                                                                            <textarea class="form-control input-sm upper" rows="3" type="text" name="observacion" id="observacion" autocomplete="off"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="tab-pane fade" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
                                                                    <div class="form-row">
                                                                        <div class="form-group col-md-9 col-sm-12" required>
                                                                            <label for="id_usuario" >Usuario</label>
                                                                            <select id="id_usuario" name="id_usuario" class="form-control"></select>
                                                                        </div>
                                                                        <div class="form-group col-md-3 col-sm-12">
                                                                            <label>&nbsp;</label>
                                                                            <button type="button" class="btn btn-primary btn-block" id="agregar-usuario">Agregar</button>
                                                                        </div>
                                                                        <div class="form-group col-md-12 col-sm-12 required">
                                                                            <table id="tabla_usuarios"></table>
                                                                        </div>
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
                                            <button type="submit" class="btn btn-success">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                           <!-- MODAL ASIGNAR MAQUINA -->
                        <div class="modal fade" id="modalAsignar" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" >Asignar Máquina</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formularioAsignar" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_caja_asignacion" id="id_caja_asignacion">
                                        <div class="modal-body">  
                                                        <div class="col-md-12 col-sm-12">
                                                                <div class="form-row">
                                                                        <div class="form-group col-md-12 col-sm-12 ">
                                                                            <label for="descripcion" class="label-required">Descripción</label>
                                                                            <input class="form-control input-sm" type="text" name="descripcion" id="descripcion" autocomplete="off" required=""></input>
                                                                        </div>
                                                                    </div>
                                                        </div>
                                            </div>
                                        <div class="modal-footer">
                                            <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="button" id="guardarAsignar" class="btn btn-success">Asignar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       <?php include 'footer.php';?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
