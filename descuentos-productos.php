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
						
                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="descripcion">Descripcón</label>
                                                            <input class="form-control input-sm upper" type="text" name="descripcion" id="descripcion" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="proveedor">Proveedor</label>
                                                            <select id="proveedor" name="proveedor" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="producto">Producto</label>
                                                            <select id="producto" name="producto" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="metodo_pago">Método De Pago</label>
                                                            <select id="metodo_pago" name="metodo_pago" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="marca">Marca</label>
                                                            <select id="marca" name="marca" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="clasificacion">Clasificación</label>
                                                            <select id="clasificacion" name="clasificacion" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="porcentaje">Porcentaje</label>
                                                            <input class="form-control input-sm" type="text" name="porcentaje" id="porcentaje" autocomplete="off" onkeypress="return soloNumeros(event)" maxlength="3">
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="fecha_inicio">Fecha De Inicio</label>
                                                            <input class="form-control input-sm" type="date" name="fecha_inicio" id="fecha_inicio" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="fecha_fin">Fecha De Fin</label>
                                                            <input class="form-control input-sm" type="date" name="fecha_fin" id="fecha_fin" autocomplete="off">
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
