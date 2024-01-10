<?php include 'header.php';?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php";?>
    <div id="main-wrapper">
        <?php include 'topbar.php';include 'leftbar.php'?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h4>Filtros</h4>
                        <hr>
                        <div class="form" role="form">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <label for="fecha">Fechas</label><br>
                                    <div id="filtro_fecha" class="btn btn-default col-12 form-control d-block">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span class="ml-3" style="font-size: 14px;"></span><i class="ml-3 mr-2 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filtro_sucursal">Sucursal</label>
                                    <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>>
                                        <?php echo "<option value=\"$datos_empresa->id_sucursal\">$datos_empresa->sucursal</option>" ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label  for="id_caja">Caja</label>
                                    <select id="id_caja" class="form-control"></select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filtro_caja">Turno</label>
                                    <select id="filtro_caja" class="form-control"></select>
                                </div>
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block" id="btn_abrir_caja" data-toggle='modal' data-target='#modal'>Abrir</button>
                                </div>
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-block" id="btn_cerrar_caja" data-toggle='modal' data-target='#modal' disabled="">Cerrar</button>
                                </div>
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-success btn-block" id="btn_extraer" data-toggle='modal' data-target='#modal-extraccion' disabled="">Extraer</button>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <div id="toolbar">
                                    <h4 class="mb-0" style="margin-top: 0.70rem !important">Resumen de Ventas</h4>
                                </div>
                                <table id="tabla"></table>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <div id="toolbar_resumen">
                                    <h4 class="mb-0" style="margin-top: 0.70rem !important">Métodos de Pagos</h4>
                                </div>
                                <table id="tabla_resumen"></table>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <div id="toolbar_arqueo_cierre">
                                    <h4 class="mb-0" style="margin-top: 0.70rem !important">Cierre de Caja</h4>
                                </div>
                                <table id="tabla_arqueo_cierre"></table>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <div id="toolbar_servicios">
                                    <h4 class="mb-0" style="margin-top: 0.70rem !important">Servicios</h4>
                                </div>
                                <table id="tabla_servicios"></table>
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <div id="toolbar_diferencia">
                                    <h4 class="mb-0" style="margin-top: 0.70rem !important">Resumen</h4>
                                </div>
                                <table id="tabla_diferencia"></table>
                            </div>
                        </div>

                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <input type="hidden" name="id_sucursal" id="id_sucursal">
                                        <input type="hidden" name="id_caja" id="id_caja_modal">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <ul class="nav nav-tabs mb-2" id="tabModal" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" id="nav_1" data-toggle="tab" href="#tab_1" role="tab" aria-controls="tab_1" aria-selected="true">ARQUEO DE CAJA</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="nav_2" data-toggle="tab" href="#tab_2" role="tab" aria-controls="tab_2" aria-selected="false">SERVICIOS</a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" id="nav_3" data-toggle="tab" href="#tab_3" role="tab" aria-controls="tab_3" aria-selected="false">SENCILLO</a>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content" id="tabModalContent">
                                                        <!-- Tab 1 -->
                                                        <div class="tab-pane fade show active" id="tab_1" role="tabpanel" aria-labelledby="nav_1">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="cajero" class="label-required">Cajero</label>
                                                                <select id="cajero" name="cajero" class="form-control" required></select>
                                                            </div>
                                                            <div class="col-md-12 col-sm-12">
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
                                                                        <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda" name="valor[]"  title="5.000 Gs." value="5.000" readonly>
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
                                                                <div class="form-row">
                                                                    <div class="form-group-sm col-md-12 col-sm-12">
                                                                        <label class="label-sm" for="observacion">Observación</label>
                                                                        <textarea type="text" class="form-control input-sm" name="observacion" id="observacion"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Tab 2 -->
                                                        <div class="tab-pane fade" id="tab_2" role="tabpanel" aria-labelledby="nav_2">
                                                            <div class="col-md-12 col-sm-12">
                                                                <div class="form-row text-center alingn-center justify-content-between" id="content_servicios"></div>
                                                                <div class="form-row alingn-center justify-content-end pt-3">
                                                                    <div class="form-group-sm col-5">
                                                                        <input type="text" class="form-control form-control-sm input-sm text-right" name="total_servicios" id="total_servicios" title="Total" value="0" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Tab 3 -->
                                                <div class="tab-pane fade show" id="tab_3" role="tabpanel" aria-labelledby="nav_3">
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
                                                            <input type="text" class="form-control form-control-sm input-sm text-right valor_moneda_sen" name="valor_sen[]"  title="5.000 Gs." value="5.000" readonly>
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
                                            <button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" id="btn_submit" class="btn btn-success">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL EXTRACCION -->
                        <div class="modal fade" id="modal-extraccion" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabelExtraer"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_extraer" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">

                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group-sm col-md-12 col-sm-12">
                                                            <label for="monto_extraccion" class="label-required">Monto</label>
                                                            <input type="text" class="form-control input-sm text-right" id="monto_extraccion" name="monto_extraccion" autocomplete="off" onkeypress="return soloNumeros(event)" onkeyup="separadorMilesOnKey(event,this)" required>
                                                        </div>
                                                        <div class="form-group-sm col-md-12 col-sm-12">
                                                            <label for="observacion">Observación</label>
                                                            <textarea type="text" class="form-control input-sm" name="observacion_extr" id="observacion_extr"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" id="btn_extraer" class="btn btn-success">Extraer</button>
                                        </div>
                                    </form>
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
