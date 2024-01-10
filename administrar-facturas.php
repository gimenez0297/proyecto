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
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-group ml-1">
                                    <?php 
                                        $option = "";
                                        $disabled = "";
                                        if (esAdmin($usuario->id_rol) === false) {
                                            $option = "<option value=\"$usuario->id_sucursal\">$datos_empresa->sucursal</option>";
                                            $disabled = "disabled";
                                        }
                                    ?>
                                    <select id="filtro_sucursal" class="form-control" <?php echo $disabled; ?>>
                                        <?php echo $option; ?>
                                    </select>
                                </div>
                                <div class="form-group ml-1">
                                    <?php 
                                        $option = "";
                                        $disabled = "";
                                        if (esCajero($usuario->id_rol) === true) {
                                            $option = "<option value=\"$usuario->id\">$datos_empresa->username</option>";
                                            $disabled = "disabled";
                                        }
                                    ?>
                                    <select id="filtro_cajero" class="form-control " <?php echo $disabled; ?>>
                                        <?php echo $option; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL PRODUCTOS -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-98" role="document">
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
                                                        <div id="toolbar_detalle">
                                                            <div class="alert alert-info" role="alert" id="toolbar_titulo"></div>
                                                        </div>
                                                        <table id="tabla_detalle"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL COBROS -->
                        <div class="modal fade" id="modal_detalle_dos" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cobros</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_detalle_dos">
                                                            <div class="alert alert-info" role="alert" id="toolbar_detalle_dos_titulo"></div>
                                                        </div>
                                                        <table id="tabla_detalle_dos"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        <button id="btn_guardar_detalle_dos" type="button" class="btn btn-success">Guardar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DOCUMENTOS -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        
                                        <h5 class="modal-title">Documentos</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <form class="form default-validation" id="formulario_carga_documento" method="post" enctype="multipart/form-data" action="">
                                                <div class="form-row">
                                                    <div class="form-group col-md-6 col-sm-12">
                                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                                        <label for="documento" class="label-required">Documento</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" id="documento" name="documento" accept="image/png,image/jpeg" required>
                                                            <label class="custom-file-label form-control" for="documento">Seleccionar archivo</label>
                                                        </div>
                                                        <!-- <input type="hidden" name="change_documento" id="change_documento">
                                                        <input type="hidden" name="url_documento" id="url_documento"> -->
                                                    </div>
                                                    <div class="form-group col-md-6 col-sm-12">
                                                        <label for="descripcion" class="label-required">Descripción</label>
                                                        <input class="form-control input-sm text-uppercase" id="descripcion" name="descripcion" type="text" autocomplete="off" required>
                                                    </div>
                                                    <div class="form-group col-md-3 col-sm-12">
                                                        <label for="tipo" class="label-required">Tipo</label>
                                                        <select id="tipo" name="tipo" class="form-control" required>
                                                            <option value="1">RECETA</option>
                                                            <option value="2">COURIER</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-3 col-sm-12 doctor d-none">
                                                        <label for="doctor">Doctor</label>
                                                        <div class="input-group">
                                                                <select id="doctor" name="doctor" class="form-control"></select>
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-primary" id="agregar_doctor" title="Agregar Doctor" data-toggle="modal_agregar_doctor" data-target="#modal_agregar_doctor"><i class="fa fa-plus"></i></button>
                                                                </div>   
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-1 col-sm-12">
                                                            <label for="boton"></label>
                                                            <button type="button" class="btn btn-primary" id="agregar-archivos"><i class="fas fa-plus mr-1"></i>Agregar</button>
                                                    </div>
                                                    </form>
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <table id="tabla_documentos"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                    
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                    
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <!-- <button type="button" id="btn_guardar_documentos" class="btn btn-success">Guardar</button> -->
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                     <!-- MODAL DOCTOR -->
                     <div class="modal fade" id="modal_agregar_doctor" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="frm_agregar_doctor" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-6">
                                                            <label for="ruc" >RUC</label>
                                                                <div class="input-group">
                                                                    <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off">
                                                                    <div class="input-group-append">
                                                                        <button type="button" class="btn btn-primary" id="btn_buscar"><i class="fa fa-search"></i> <sup class="ml-1">[F2]</sup></button>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6 required">
                                                            <label for="registro" class="label-required">Nro. Registro</label>
                                                            <input class="form-control input-sm upper" type="text" name="registro" id="registro" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6 required">
                                                            <label for="nombre_apellido" class="label-required">Nombre y Apellido</label>
                                                            <input class="form-control input-sm upper" type="text" name="nombre_apellido" id="nombre_apellido" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6">
                                                            <label for="id_especialidad">Especialidad</label>
                                                            <select id="id_especialidad" name="id_especialidad" class="form-control"></select>
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
