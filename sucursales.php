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
                            <div class="modal-dialog modal-dialog-centered" role="document">
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
                                                    <div class="form-row">
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label for="ruc">R.U.C</label>
                                                            <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="nombre_empresa" class="label-required">Nombre De La Empresa</label>
                                                            <input class="form-control input-sm upper" type="text" name="nombre_empresa" id="nombre_empresa" autocomplete="off" required="">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="razon_social" class="label-required">Razón Social</label>
                                                            <input class="form-control input-sm upper" type="text" name="razon_social" id="razon_social" autocomplete="off" required="">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="sucursal" class="label-required">Sucursal</label>
                                                            <input class="form-control input-sm upper" type="text" name="sucursal" id="sucursal" autocomplete="off" required="">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="direccion">Dirección</label>
                                                            <input class="form-control input-sm" type="text" name="direccion" id="direccion" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="distrito">Ciudad</label>
                                                            <select id="distrito" name="distrito" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="telefono">Teléfono</label>
                                                            <input class="form-control input-sm" type="text" name="telefono" id="telefono" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="email">Email</label>
                                                            <input class="form-control input-sm" type="text" name="email" id="email" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="deposito" name="deposito" value="1">
                                                                <label class="custom-control-label" for="deposito">Depósito</label>
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
