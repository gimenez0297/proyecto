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
                                    <button type="button" class="btn btn-primary mr-1" id="btn_pagar" title="Pagar Pago" disabled><i class="fas fa-check mr-1"></i> Pagar</button>
                                    <button type="button" class="btn btn-danger" id="btn_anular" title="Anular Pago" disabled><i class="fas fa-times mr-1"></i> Anular</button>
                                </div>
                                <div class="form-group ml-1">
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>
						
                        <!-- MODAL DETALLES -->
                        <div class="modal fade" id="modal_detalles" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Facturas</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_detalles">
                                                            <div class="alert alert-info" role="alert" id="toolbar_detalles_text"></div>
                                                        </div>
                                                        <table id="tabla_detalles"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DETALLES CAJA CHICA -->
                        <div class="modal fade" id="modal_detalles_caja_chica" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Deposito</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_detalles_caja_chica">
                                                            <div class="alert alert-info" role="alert" id="toolbar_detalles_caja_chica_text"></div>
                                                        </div>
                                                        <table id="tabla_detalles_caja_chica"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- MODAL DETALLES FUNCIONARIOS -->
                        <div class="modal fade" id="modal_liquidacion" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Liquidaciones</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_liquidaciones">
                                                            <div class="alert alert-info" role="alert" id="toolbar_liquidaciones_text"></div>
                                                        </div>
                                                        <table id="tabla_liquidaciones"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- MODAL ARCHIVOS -->
                        <div class="modal fade" id="modal_archivos" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Archivos Adjuntos</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <form class="form default-validation" id="formulario_documento" method="post" enctype="multipart/form-data" action="">
                                                    <div class="form-row">
                                                        <input type="hidden" id="id_pago" name="id_pago" class="form-control" autocomplete="off">
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="tipo" class="label-required">Tipo</label>
                                                            <select id="tipo" name="tipo" class="form-control" required>
                                                                <option value="1">RECIBO</option>
                                                                <option value="2">TRANSFERENCIA</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12 comprobante">
                                                            <label for="nro_documento" class="label-required">Nro. Documento</label>
                                                            <input type="text" id="nro_documento" name="nro_documento" class="form-control" autocomplete="off">
                                                        </div>
                                                        <div class="form-group col-md-5 col-sm-12">
                                                            <label for="boton" class="label-required">Documento</label>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="archivos" name="archivos" accept=".pdf,.jpeg,.png" required>
                                                                <label class="custom-file-label form-control" for="archivos">Seleccionar archivo</label>
                                                            </div>
                                                            <input type="hidden" name="change_documento" id="change_documento">
                                                            <input type="hidden" name="url_documento" id="url_documento">
                                                           
                                                        </div>
                                                        <div class="form-group col-md-1 col-sm-12">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-primary btn-block" id="agregar-archivos"><i class="fas fa-plus mr-1"></i></button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <div class="form-group col-md-12 col-sm-12">
                                                    <table id="tabla_archivos"></table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
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
