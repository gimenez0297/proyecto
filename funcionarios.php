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

                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2" role="document">
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
                                                    <ul class="nav nav-tabs" id="tab" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" id="datos-basicos-tab" data-toggle="tab" href="#datos-basicos" role="tab" aria-controls="datos-basicos" aria-selected="true">DATOS BÁSICOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="confidencial-tab" data-toggle="tab" href="#confidencial" role="tab" aria-controls="confidencial" aria-selected="false">CONFIDENCIAL</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="imagenes-tab" data-toggle="tab" href="#imagenes" role="tab" aria-controls="imagenes" aria-selected="false">DOCUMENTOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="fotos-tab" data-toggle="tab" href="#fotos" role="tab" aria-controls="fotos" aria-selected="false">FOTO DE PERFIL</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="sistema-tab" data-toggle="tab" href="#sistema" role="tab" aria-controls="sistema" aria-selected="false">SISTEMA</a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content mt-2" id="tabContent">
                                                        <!-- Tab datos basicos -->
                                                        <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel" aria-labelledby="datos-basicos-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="ci">Nro. C.I.</label>
                                                                    <div class="input-group">
                                                                        <input class="form-control input-sm" type="text" name="ci" id="ci" autocomplete="off" required>
                                                                        <div class="input-group-append">
                                                                            <button type="button" class="btn btn-primary" id="btn_buscar"><i class="fa fa-search"></i><sup class="ml-1">[F2]</sup></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-md-9 col-sm-12">
                                                                    <label for="razon_social" class="label-required">Nombre y Apellido</label>
                                                                    <input class="form-control input-sm upper required" type="text" name="razon_social" id="razon_social" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="telefono">Teléfono</label>
                                                                    <input class="form-control input-sm" id="telefono" name="telefono" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="celular" class="label-required">Celular</label>
                                                                    <input class="form-control input-sm" id="celular" name="celular" type="text" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="id_estado" class="label-required">Estado Civil</label>
                                                                    <select id="id_estado" name="id_estado" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="id_ciudad" class="label-required">Ciudad</label>
                                                                    <select id="id_ciudad" name="id_ciudad" class="form-control" required></select>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="direccion">Dirección</label>
                                                                    <input class="form-control input-sm upper" id="direccion" name="direccion" type="text" autocomplete="off">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="referencia">Referencia</label>
                                                                    <input class="form-control input-sm" type="text" name="referencia" id="referencia" autocomplete="off">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Tab Documentos -->
                                                        <div class="tab-pane fade" id="imagenes" role="tabpanel" aria-labelledby="imagenes-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6 col-sm-6">
                                                                    <label for="documento">Documentos</label>
                                                                    <div class="custom-file">
                                                                        <input type="file" class="custom-file-input" id="documento" name="documento" accept=".pdf">
                                                                        <label class="custom-file-label form-control" for="documento">Seleccionar archivo</label>
                                                                    </div>
                                                                    <input type="hidden" name="change_documento" id="change_documento">
                                                                    <input type="hidden" name="url_documento" id="url_documento">
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="descripcion">Descripción</label>
                                                                    <input class="form-control input-sm" id="descripcion" name="descripcion" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-2 col-sm-12">
                                                                    <label>&nbsp;</label>
                                                                    <button type="button" class="btn btn-primary btn-block" id="agregar-documento">Subir</button>
                                                                </div>
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <table id="tabla_documentos"></table>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <!-- Foto de perfil -->
                                                        <div class="tab-pane fade" id="fotos" role="tabpanel" aria-labelledby="fotos-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6 col-sm-6">
                                                                    <label for="foto">Foto</label>
                                                                    <div class="custom-file">
                                                                        <input type="file" class="custom-file-input" id="foto" name="foto" accept=".png, .jpg">
                                                                        <label class="custom-file-label form-control" for="foto">Seleccionar archivo</label>
                                                                    </div>
                                                                    <input type="hidden" name="change_foto" id="change_foto">
                                                                </div>
                                                                <div class="col-md-12 form-group">
                                                                    <div style="position: relative; display: inline-block;">
                                                                        <button id="eliminarfoto" type="button" class="btn btn-danger btn-propio btn-sm"><i class="fas fa-times"></i></button>
                                                                        <img width='240' class='rounded' src='dist/images/sin-foto.jpg' alt='Sin Foto' id="img_preview">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Tab Sistema -->
                                                        <div class="tab-pane fade" id="sistema" role="tabpanel" aria-labelledby="sistema-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="id_puesto" class="label-required">Puesto</label>
                                                                    <select id="id_puesto" name="id_puesto" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6 comi d-none">
                                                                    <label for="comision">Comisión</label>
                                                                    <input class="form-control input-sm" id="comision" name="comision" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)">
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="id_usuario">Usuario</label>
                                                                    <select id="id_usuario" name="id_usuario" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="id_sucursal" class="label-required">Sucursal</label>
                                                                    <select id="id_sucursal" name="id_sucursal" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="fecha_alta">Fecha de Alta</label>
                                                                    <input class="form-control input-sm" type="date" name="fecha_alta" id="fecha_alta" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="fecha_baja">Fecha de Baja</label>
                                                                    <input class="form-control input-sm" type="date" name="fecha_baja" id="fecha_baja" autocomplete="off">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Tab Datos Confidencial -->
                                                        <div class="tab-pane fade" id="confidencial" role="tabpanel" aria-labelledby="confidencial-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="salario" class="label-required">Salario</label>
                                                                    <input class="form-control input-sm" id="salario" name="salario" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="hijos">Cant. Hijos</label>
                                                                    <input class="form-control input-sm text-right" type="text" name="hijos" id="hijos" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="aporte">Tipo Aporte</label>
                                                                    <select id="aporte" name="aporte" class="form-control">
                                                                        <option value="1">I.P.S</option>
                                                                        <option value="2">FACTURA/SET</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="id_banco">Banco</label>
                                                                    <select id="id_banco" name="id_banco" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="nro_cuenta">N° Cuenta</label>
                                                                    <input class="form-control input-sm" id="nro_cuenta" name="nro_cuenta" type="text" autocomplete="off">
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