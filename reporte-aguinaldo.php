<?php
include 'header.php'; 

$meses = ["", "ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SETIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
$mes = $meses[date("n")];
$anio = date('Y');

?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php";?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php'?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <div id="toolbar">
                            <div class="form-inline" role="form">
                                <div class="form-group">
                                    <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>></select>
                                </div>
                                <div class="form-group ml-1">
                                    <input type="hidden" id="periodo">
                                    <div id="filtro_fecha_anho" class="btn btn-default form-control">
                                    <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                    <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-md btn-primary ml-1 float-left" id="imprimir">Imprimir</button>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL PRODUCTOS -->
                         <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-sm modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detalles de Cobro Mensual</h5>
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
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php';?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>

</html>