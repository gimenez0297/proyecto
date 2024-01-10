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
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_banco" id="id_banco">
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
                                                                    <a class="nav-link" id="usuarios-tab" data-toggle="tab" href="#usuarios" role="tab" aria-controls="usuarios" aria-selected="false">CUENTAS</a>
                                                                </li>
                                                            </ul>
                                                            <div class="tab-content mt-2" id="tabContent">
                                                                <div class="tab-pane fade show active" id="datos-basicos" role="tabpanel" aria-labelledby="datos-basicos-tab">

                                                                    <div class="row">
                                                                        <div class="form-group col-md-12 col-sm-12">
                                                                            <label for="ruc" class="label-required">R.U.C</label>
                                                                            <div class="input-group">
                                                                                <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required>
                                                                                <div class="input-group-append">
                                                                                   <button type="button" class="btn btn-primary" id="btn_buscar"><i class="fa fa-search"></i><sup class="ml-1">[F2]</sup></button>
                                                                                </div>
                                                                              </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-row">
                                                                        <div class="form-group col-md-12 col-sm-12">
                                                                            <label for="banco" class="label-required">Banco</label>
                                                                            <input class="form-control input-sm upper" type="text" name="banco" id="banco" autocomplete="off" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="tab-pane fade" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
                                                                    <div class="form-row">
                                                                        <div class="form-group col-md-9 col-sm-12" required>
                                                                            <label for="cuenta" >Cuenta</label>
                                                                            <input class="form-control input-sm" type="text" name="cuenta" id="cuenta" autocomplete="off">
                                                                        </div>
                                                                        <div class="form-group col-md-3 col-sm-12">
                                                                            <label>&nbsp;</label>
                                                                            <button type="button" class="btn btn-primary btn-block" id="agregar-cuenta">Agregar</button>
                                                                        </div>
                                                                        <div class="form-group col-md-12 col-sm-12 required">
                                                                            <table id="tabla_cuentas"></table>
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
