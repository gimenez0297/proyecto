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
                                    <select id="filtro_tipo_proveedor" name="filtro_tipo_proveedor" class="form-control">
                                        <option value='1'>PRODUCTOS</option>
                                        <option value='2'>GASTOS</option>
                                    </select>
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
                                        <input type="hidden" name="id_proveedor" id="id_proveedor">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="ruc" class="label-required">R.U.C</label>
                                                            <div class="input-group">
                                                                <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required>
                                                                <div class="input-group-append">
                                                                   <button type="button" class="btn btn-primary" id="btn_buscar"><i class="fa fa-search"></i> <sup class="ml-1">[F2]</sup></button>
                                                                </div>
                                                              </div>
                                                        </div>
                                                        <div class="form-group col-md-9 col-sm-12">
                                                            <label for="proveedor" class="label-required">Proveedor / Razón Social</label>
                                                            <input class="form-control input-sm upper" type="text" name="proveedor" id="proveedor" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12 required">
                                                            <label for="tipo_proveedor" class="label-required">Tipo de proveedor</label>
                                                            <select id="tipo_proveedor" name="tipo_proveedor[]" class="form-control" multiple="" required>
                                                                <option value='1'>PRODUCTOS</option>
                                                                <option value='2'>GASTOS</option>
                                                            </select>
                                                            </div>
                                                        <div class="form-group col-md-9 col-sm-12">
                                                            <label for="contacto">Nombre de fantasia</label>
                                                            <input class="form-control input-sm" type="text" name="nombre_fantasia" id="nombre_fantasia" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="telefono">Teléfono/s</label>
                                                            <input class="form-control input-sm" id="telefono" name="telefono" type="text" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="contacto">Contacto</label>
                                                            <input class="form-control input-sm" type="text" name="contacto" id="contacto" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="email">E-mail</label>
                                                            <input class="form-control input-sm" id="email" name="email" type="email" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="direccion">Dirección</label>
                                                            <input class="form-control input-sm" id="direccion" name="direccion" type="text" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="obs">Observaciones</label>
                                                            <input class="form-control input-sm upper" id="obs" name="obs" type="text" autocomplete="off">
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
