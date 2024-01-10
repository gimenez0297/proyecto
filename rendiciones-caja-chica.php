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
                                    <!-- <button type="button" class="btn btn-primary mr-1" id="btn_aprobar" title="Aprobar Ordenes" disabled><i class="fas fa-check mr-1"></i> Aprobar</button>
                                        <button type="button" class="btn btn-danger" id="btn_rechazar" title="Rechazar Ordenes" disabled><i class="fas fa-times mr-1"></i> Rechazar</button> -->
                                </div>
                                <!-- <div class="form-group mr-3">
                                        <input type="hidden" id="desde">
                                        <input type="hidden" id="hasta">
                                        <div id="filtro_fecha" class="btn btn-default form-control">
                                            <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                            <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                        </div>
                                    </div> -->
                                <div class="form-group">
                                    <select id="filtro_sucursal" name="filtro_sucursal" class="form-control"></select>
                                </div>
                            </div>
                        </div>

                        <table id="tabla"></table>


                        <!-- MODAL PRODUCTOS -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Gastos</h5>
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

                        <!-- MODAL PRODUCTOS -->
                        <div class="modal fade" id="modal_comprobante" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cargar Comprobante</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_comprobante" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <div id="toolbar_detalle">
                                                                <div class="alert alert-info" role="alert" id="toolbar_titulo_comprobante"></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12" required>
                                                            <label for="id_banco" class="label-required">Banco</label>
                                                            <select id="id_banco" name="id_banco" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12" required>
                                                            <label for="id_cuenta" class="label-required">Nro Cuenta</label>
                                                            <select id="id_cuenta" name="id_cuenta" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12" required>
                                                            <label for="fecha_deposito" class="label-required">Fecha Deposito</label>
                                                            <input class="form-control" type="date" name="fecha_deposito" id="fecha_deposito" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12" required>
                                                            <label for="nro_comprobante" class="label-required">Nro de Comprobante</label>
                                                            <input class="form-control" type="text" name="nro_comprobante" id="nro_comprobante" autocomplete="off">
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