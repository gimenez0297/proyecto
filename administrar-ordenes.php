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
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary ml-1" id="btn_abrir_caja" data-toggle="modal" data-target="#modal_caja" title="Abrir Caja"><i class="fas fa-cash-register"></i><sup class="ml-1">[F12]</sup></button>
                                    <button type="button" class="btn btn-danger ml-1" id="btn_cerrar_caja" data-toggle="modal" data-target="#modal_caja" title="Cerrar Caja" style="display:none"><i class="fas fa-cash-register"></i><sup class="ml-1">[F12]</sup></button>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL DETALLE -->
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

                        <!-- MODAL ANULACION -->
                        <div class="modal fade" id="modal_anular" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabelAnular"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_anular" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-6 required">
                                                            <label for="motivo" class="label-required">Motivo</label>
                                                            <input class="form-control input-sm upper" type="text" name="motivo" id="motivo" autocomplete="off" required>
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

                        <!-- MODAL CAJA -->
                        <div class="modal fade" id="modal_caja" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_caja" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">

                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <ul class="nav nav-tabs mb-2" id="tabModal" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" id="mc_nav_1" data-toggle="tab" href="#mc_tab_1" role="tab" aria-controls="mc_tab_1" aria-selected="true">ARQUEO DE CAJA</a>
                                                        </li>
                                                        <!-- <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="mc_nav_2" data-toggle="tab" href="#mc_tab_2" role="tab" aria-controls="tab_2" aria-selected="false">SERVICIOS</a>
                                                </li> -->
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="mc_nav_3" data-toggle="tab" href="#mc_tab_3" role="tab" aria-controls="tab_3" aria-selected="false">SENCILLO</a>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content" id="tabModalContent">
                                                        <!-- Tab 1 -->
                                                        <div class="tab-pane fade show active" id="mc_tab_1" role="tabpanel" aria-labelledby="mc_nav_1">
                                                            <div class="form-row text-center alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <label class="label-sm" for="valor">Moneda</label><br>
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" id="valor" title="100.000 Gs." value="100.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <label class="label-sm" for="cantidad">Cantidad</label><br>
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" id="cantidad" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <label class="label-sm" for="total">Total</label><br>
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" id="total" title="Total (100.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="50.000 Gs." value="50.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (50.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="20.000 Gs." value="20.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (20.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="10.000 Gs." value="10.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (10.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="5.000 Gs." value="5.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (5.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="2.000 Gs." value="2.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (2.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="1.000 Gs." value="1.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (1.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="500 Gs." value="500" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (500 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="100 Gs." value="100" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total (100 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]" title="50 Gs." value="50" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda" name="cantidad[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total" name="total[]" title="Total(50 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-end pt-3">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right" name="total_caja" id="total_caja" title="Total" value="0" readonly>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <!-- Tab 2 -->
                                                        <!-- <div class="tab-pane fade" id="mc_tab_2" role="tabpanel" aria-labelledby="mc_nav_2">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="form-row text-center alingn-center justify-content-between" id="content_servicios"></div>
                                                        <div class="form-row alingn-center justify-content-end pt-3">
                                                            <div class="form-group-sm col-5">
                                                                <input type="text" class="form-control form-control-sm input-sm text-right" name="total_servicios" id="total_servicios" title="Total" value="0" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> -->

                                                        <!-- Tab 3 -->
                                                        <div class="tab-pane fade show" id="mc_tab_3" role="tabpanel" aria-labelledby="mc_nav_3">
                                                            <div class="form-row text-center alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <label class="label-sm" for="valor">Moneda</label><br>
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" id="valor_sen" title="100.000 Gs." value="100.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <label class="label-sm" for="cantidad">Cantidad</label><br>
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" id="cantidad_sen" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <label class="label-sm" for="total">Total</label><br>
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" id="total_sen" title="Total (100.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="50.000 Gs." value="50.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (50.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="20.000 Gs." value="20.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (20.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="10.000 Gs." value="10.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (10.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="5.000 Gs." value="5.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (5.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="2.000 Gs." value="2.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (2.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="1.000 Gs." value="1.000" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (1.000 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="500 Gs." value="500" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (500 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="100 Gs." value="100" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total (100 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-between">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]" title="50 Gs." value="50" readonly>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-center cantidad_moneda_sen" name="cantidad_sen[]" value="0" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                                                </div>
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right total_sen" name="total_sen[]" title="Total(50 Gs.)" value="0" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row alingn-center justify-content-end pt-3">
                                                                <div class="form-group-sm col-3">
                                                                    <input type="text" class="form-control form-control-sm input-sm text-right" name="total_caja_sen" id="total_caja_sen" title="Total" value="0" readonly>
                                                                </div>
                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" id="btn_submit_caja" class="btn btn-success">Abrir</button>
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