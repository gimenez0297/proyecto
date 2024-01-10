<?php include 'header.php';?>

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
                        </div>
                        <h4>Filtros</h4>
                        <hr>

                        <!-- Filtros -->
                        <div class="form-row">
                            <div class="form-group">
                                <label class="pr-2" for="filtro_sucursal">Sucursal </label><br>
                                <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="pr-2" for="fecha">Fechas </label><br>
                                <div id="filtro_fecha" class="btn btn-default col-12 form-control d-block">
                                    <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                    <span class="ml-3" style="font-size: 14px;"></span><i class="ml-3 mr-2 fas fa-chevron-down"></i>
                                </div>
                                <input type="hidden" id="desde">
                                <input type="hidden" id="hasta">
                            </div>
                            <div class="form-group-cm col-md-5 col-sm-12 ">
                                <label for="cajeros" style="display: block;">Cajeros</label>
                                <select id="cajeros" name="cajeros" class="form-control" required></select>
                            </div>
                            <div class="form-group-cm col-md-1 col-sm-12 ">
                                <button type="button" class="btn btn-primary" id="btn_imprimir" style="margin-top:22px;"><i class="fa fa-print"></i> Imprimir</button>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL METODOS -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Métodos de Pago</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <input type="hidden" id="id_caja" name="id_caja">
                                            <div class="col-md-12 col-sm-12">
                                                <ul class="nav nav-tabs" id="tabModal" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link active" id="nav_1" data-toggle="tab" href="#tab_1" role="tab" aria-controls="tab_1" aria-selected="true">MÉTODOS DE PAGOS</a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" id="nav_2" data-toggle="tab" href="#tab_2" role="tab" aria-controls="tab_2" aria-selected="false">CIERRE DE CAJA</a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" id="nav_3" data-toggle="tab" href="#tab_3" role="tab" aria-controls="tab_3" aria-selected="false">SERVICIOS</a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" id="nav_4" data-toggle="tab" href="#tab_4" role="tab" aria-controls="tab_4" aria-selected="false">RESUMEN</a>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <a class="nav-link" id="nav_5" data-toggle="tab" href="#tab_5" role="tab" aria-controls="tab_5" aria-selected="false">VOUCHERS</a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-2" id="tabModalContent">

                                                    <!-- Tab 1 -->
                                                    <div class="tab-pane fade show active" id="tab_1" role="tabpanel" aria-labelledby="nav_1">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <table id="tabla_resumen"></table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 2 -->
                                                    <div class="tab-pane fade show" id="tab_2" role="tabpanel" aria-labelledby="nav_2">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <table id="tabla_arqueo_cierre"></table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3 -->
                                                    <div class="tab-pane fade show" id="tab_3" role="tabpanel" aria-labelledby="nav_3">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <table id="tabla_servicios"></table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 4 -->
                                                    <div class="tab-pane fade show" id="tab_4" role="tabpanel" aria-labelledby="nav_4">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <table id="tabla_diferencia"></table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 5 -->
                                                    <div class="tab-pane fade show" id="tab_5" role="tabpanel" aria-labelledby="nav_5">
                                                        <div class="form-row">
                                                            <div id="toolbar_vouchers">
                                                                <div class="form-inline" role="form">
                                                                    <div class="form-group">
                                                                        <!-- <div class="alert alert-info" role="alert" id="toolbar_detalles_text"></div> -->
                                                                        <select id="metodo_pago" name="metodo_pago" class="form-control"></select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <table id="tabla_vouchers"></table>
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
        </div>
    </div>
    <?php include 'footer.php';?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>

</html>
