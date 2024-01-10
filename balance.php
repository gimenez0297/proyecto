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
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar Cuenta Padre<sup class="ml-1">[F1]</sup></button>
                                </div>
                                <div class="form-group ml-1">
                                    <select id="id_libro" name="libro" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3 col-sm-6 required">
                                                            <label for="cuenta_pa" class="label-required">Cuenta</label>
                                                            <input class="form-control input-sm upper text-right" type="text" name="cuenta_pa" id="cuenta_pa" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                        </div>
                                                        <div class="form-group col-md-9 col-sm-6 required">
                                                            <label for="denominacion_padre" class="label-required">Denominacion</label>
                                                            <input class="form-control input-sm upper" type="text" name="denominacion_padre" id="denominacion_padre" autocomplete="off" required>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-success">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL AGREGAR CUENTA -->
                        <div class="modal fade" id="modal_cuenta" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabelCuenta"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_cuenta" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_padre" id="id_padre">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4 col-sm-6">
                                                            <label for="cuenta_padre" class="label-required">Cuenta Padre</label>
                                                            <input class="form-control input-sm upper" type="text" name="cuenta_padre" id="cuenta_padre" autocomplete="off" required disabled>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-6 required">
                                                            <label for="cuenta" class="label-required">Cuenta</label>
                                                            <input class="form-control input-sm upper text-right" type="text" name="cuenta" id="cuenta" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-6 required">
                                                            <label for="denominacion" class="label-required">Denominacion</label>
                                                            <input class="form-control input-sm upper" type="text" name="denominacion" id="denominacion" autocomplete="off" required>
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