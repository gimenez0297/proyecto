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
                                                            <a class="nav-link" id="cargo-tab" data-toggle="tab" href="#cargo" role="tab" aria-controls="cargo" aria-selected="false">CARGO</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="imagenes-tab" data-toggle="tab" href="#imagenes" role="tab" aria-controls="imagenes" aria-selected="false">DOCUMENTOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="fotos-tab" data-toggle="tab" href="#fotos" role="tab" aria-controls="fotos" aria-selected="false">FOTO DE PERFIL</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="contacto-tab" data-toggle="tab" href="#contacto" role="tab" aria-controls="contacto" aria-selected="false">CONTACTO</a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content mt-2" id="tabContent">
                                                        <!-- Tab datos basicos -->
                                                        <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel" aria-labelledby="datos-basicos-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-3 col-sm-12">
                                                                    <label for="ci" class="label-required">Nro. C.I.</label>
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
                                                                    <label for="fecha_nacimiento"  class="label-required">Fecha Nacimiento</label>
                                                                    <input class="form-control input-sm" type="date" name="fecha_nacimiento" id="fecha_nacimiento" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="nacionalidad" class="label-required">Nacionalidad</label>
                                                                    <select id="nacionalidad" name="nacionalidad" class="form-control" required>
                                                                        <option value='1'>PARAGUAYA</option>
                                                                        <option value='2'>PARAGUAYA NACIONALIZADA</option>
                                                                        <option value='3'>EXTRANJERO</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6 d-none nac">
                                                                    <label for="departamento">Departamento</label>
                                                                    <select id="departamento" name="departamento" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6 d-none nac">
                                                                    <label for="distrito">Nac. Distrito</label>
                                                                    <select id="distrito" name="distrito" class="form-control"></select>
                                                                </div>
                                                            </div>

                                                            <div class="form-row">
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="sexo" class="label-required">Sexo</label>
                                                                    <select id="sexo" name="sexo" class="form-control" required>
                                                                        <option value='1'>FEMENINO</option>
                                                                        <option value='2'>MASCULINO</option>
                                                                    </select>

                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="grupo_sanguineo">Grupo Sanguineo</label>
                                                                    <select id="grupo_sanguineo" name="grupo_sanguineo" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="id_estado">Estado Civil</label>
                                                                    <select id="id_estado" name="id_estado" class="form-control"></select>
                                                                </div>
                                                                
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="telefono">Teléfono</label>
                                                                    <input class="form-control input-sm" id="telefono" name="telefono" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="celular">Celular</label>
                                                                    <input class="form-control input-sm" id="celular" name="celular" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-6">
                                                                    <label for="email">E-mail</label>
                                                                    <input class="form-control input-sm" id="email" name="email" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="numero">Numero</label>
                                                                    <input class="form-control input-sm" id="numero" name="numero" type="text" autocomplete="off">
                                                                </div>
                                                            
                                                            </div>
                                                            <div class="row">
                                                                <div class="form-group col-md-12 col-sm-12">
                                                                    <label for="direccion">Dirección</label>
                                                                    <input class="form-control input-sm upper" id="direccion" name="direccion" type="text" autocomplete="off">
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

                                                        <!-- Tab Contacto -->
                                                        <div class="tab-pane fade" id="contacto" role="tabpanel" aria-labelledby="contacto-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="distrito_contacto">Distrito</label>
                                                                    <select id="distrito_contacto" name="distrito_contacto" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="localidad_contacto">Localidad</label>
                                                                    <select id="localidad_contacto" name="localidad_contacto" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-4 col-sm-6">
                                                                    <label for="barrio">Barrio</label>
                                                                    <select id="barrio" name="barrio" class="form-control"></select>
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-6">
                                                                    <label for="persona_contacto">Persona a recurrir en caso de necesidad</label>
                                                                    <input class="form-control input-sm" id="persona_contacto" name="persona_contacto" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="celular_contacto">Celular</label>
                                                                    <input class="form-control input-sm" id="celular_contacto" name="celular_contacto" type="text" autocomplete="off">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Tab Datos Cargo -->
                                                        <div class="tab-pane fade" id="cargo" role="tabpanel" aria-labelledby="cargo-tab">
                                                            <div class="form-row">
                                                                <div class="form-group col-md-6 col-sm-6">
                                                                    <label for="area" class="label-required">Área</label>
                                                                    <select id="area" name="area" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-6 col-sm-6">
                                                                    <label for="cargo_funcional" class="label-required">Cargo Funcional</label>
                                                                    <select id="cargo_funcional" name="cargo_funcional" class="form-control" required></select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="vinculo">Vinculo</label>
                                                                    <select id="vinculo" name="vinculo" class="form-control">
                                                                        <option value='1'>PERMANENTE</option>
                                                                        <option value='2'>CONTRATADO</option>
                                                                        <option value='3'>COMISIONADO</option>
                                                                        <option value='4'>CONTRATADO POR PRODUCTO</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="fecha_ingreso"  class="label-required">Fecha Ingreso</label>
                                                                    <input class="form-control input-sm" type="date" name="fecha_ingreso" id="fecha_ingreso" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="fecha_asuncion"  class="label-required">Fecha Asunción</label>
                                                                    <input class="form-control input-sm" type="date" name="fecha_asuncion" id="fecha_asuncion" autocomplete="off" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="salario" class="label-required">Salario</label>
                                                                    <input class="form-control input-sm" id="salario" name="salario" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="telefono_interno">Tel. Interno</label>
                                                                    <input class="form-control input-sm" id="telefono_interno" name="telefono_interno" type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-md-3 col-sm-6">
                                                                    <label for="id_usuario">Usuario</label>
                                                                    <select id="id_usuario" name="id_usuario" class="form-control"></select>
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