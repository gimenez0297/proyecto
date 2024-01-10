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
                                    <button type="button" class="btn btn-primary mr-1 cambiar-estados" title="Aprobar Solicitudes" value="1" disabled><i class="fas fa-check mr-1"></i>Aprobar</button>
                                    <button type="button" class="btn btn-danger cambiar-estados" title="Rechazar Solicitudes" value="2" disabled><i class="fas fa-times mr-1"></i>Rechazar</button>
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
						
                        <!-- MODAL PRODUCTOS -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
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
                                                            <div class="alert alert-info" role="alert" id="toolbar_detalle_text"></div>
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


                           <!-- MODAL IMPRESION -->
                           <div class="modal fade" id="modalImprension" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" >Imprimir Solicitud</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formularioImprimir" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_tipo" id="id_tipo">
                                        <input type="hidden" name="id_solicitud_deposito" id="id_solicitud_deposito">
                                        <div class="modal-body">         
                                            <div class="form-group ">
                                                <label for="tipo" class="label-required" required>Tipo de impresión</label>
                                                <div class="input-group">
                                                    <select class="form-control" name="tipo" id="tipo" required>
                                                        <option value="2" selected>TODOS LOS PRODUCTOS</option>
                                                        <option value="1">OMITIR PRODUCTOS SIN STOCK</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group ">
                                                <label for="orden" class="label-required" required>Agrupar por</label>
                                                <div class="input-group">
                                                    <select class="form-control" name="orden" id="orden" required>
                                                        <option value="0"selected>SIN AGRUPACION</option>
                                                        <option value="1">LABORATORIO</option>
                                                        <option value="2">PROVEEDOR</option>
                                                        <option value="3">MARCA</option>
                                                        <option value="4">RUBRO</option>             
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="button" id="btn_imprimir" class="btn btn-success">Imprimir</button>
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
