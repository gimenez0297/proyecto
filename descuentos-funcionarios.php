<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="tab" role="tablist">
                            <li class="nav-item mr-1" role="presentation">
                                <a class="nav-link active" id="anticipos-tab" data-toggle="tab" href="#anticipos" role="tab" aria-controls="anticipos" aria-selected="true">ANTICIPOS<sup class="ml-1">[F1]</sup></a>
                            </li>
                            <li class="nav-item mr-1" role="presentation">
                                <a class="nav-link" id="prestamos-tab" data-toggle="tab" href="#prestamos" role="tab" aria-controls="prestamos" aria-selected="false">PRÉSTAMOS<sup class="ml-1">[F2]</sup></a>
                            </li>
                            <li class="nav-item mr-1" role="presentation">
                                <a class="nav-link" id="otros-tab" data-toggle="tab" href="#otros" role="tab" aria-controls="otros" aria-selected="false">OTROS<sup class="ml-1">[F3]</sup></a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-12">
                    <!-- Tab Anticipos -->
                    <div class="tab-content" id="tabContent">
                        <div class="tab-pane fade show active" id="anticipos" role="tabpanel" aria-labelledby="anticipos-tab">

                            <div id="toolbar">
                                <div class="form-inline" role="form">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F4]</sup></button>
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
                            
                            <!-- MODAL PRINCIPAL -->
                            <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel"></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="" novalidate>
                                            <input type="hidden" name="id_anticipo" id="id_anticipo">
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="id_funcionario" class="label-required">Funcionario</label>
                                                                <select id="id_funcionario" name="id_funcionario" class="form-control" required></select>
                                                            </div>
                                                            <div class="form-group col-md-6 col-sm-12">
                                                                <label for="monto" class="label-required">Monto</label>
                                                                <input class="form-control input-sm text-right" type="text" name="monto" id="monto" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                            </div>
                                                            <div class="form-group col-md-6 col-sm-6">
                                                                <label for="fecha">Fecha</label>
                                                                <input class="form-control input-sm" type="date" name="fecha" id="fecha" autocomplete="off" value="<?php echo date("Y-m-d");?>">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="obs">Observaciones</label>
                                                                <textarea class="form-control input-sm upper" id="obs" name="obs" type="text" autocomplete="off"></textarea>
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
                        <!-- Tab Prestamos -->
                        <div class="tab-pane fade" id="prestamos" role="tabpanel" aria-labelledby="prestamos-tab">
                            <div id="toolbar_prestamos">
                                <div class="form-inline" role="form">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="agregar_prestamos" data-toggle="modal" data-target="#modal_prestamos">Agregar<sup class="ml-1">[F4]</sup></button>
                                    </div>
                                    <div class="form-group ml-1">
                                    <input type="hidden" id="desde2">
                                    <input type="hidden" id="hasta2">
                                    <div id="filtro_fecha2" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <table id="tabla_prestamos"></table>
                            
                            <!-- MODAL PRINCIPAL -->
                            <div class="modal fade" id="modal_prestamos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel_pre"></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form class="form default-validation" id="formulario_pres" method="post" enctype="multipart/form-data" action="" novalidate>
                                            <input type="hidden" name="id_prestamo" id="id_prestamo">
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="id_funcionario_pre" class="label-required">Funcionario</label>
                                                                <select id="id_funcionario_pre" name="id_funcionario_pre" class="form-control" required></select>
                                                            </div>
                                                            <div class="form-group col-md-4 col-sm-12">
                                                                <label for="monto_pre" class="label-required">Monto</label>
                                                                <input class="form-control input-sm text-right" type="text" name="monto_pre" id="monto_pre" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-12">
                                                                <label for="cuota" class="label-required">Cant. Cuota</label>
                                                                <input class="form-control input-sm text-right" type="text" name="cuota" id="cuota" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1" required>
                                                            </div>
                                                            <div class="form-group col-md-5 col-sm-6">
                                                                <label for="fecha_prestamo">Fecha</label>
                                                                <input class="form-control input-sm" type="date" name="fecha_prestamo" id="fecha_prestamo" autocomplete="off" value="<?php echo date("Y-m-d");?>">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="obs_pre">Observaciones</label>
                                                                <textarea class="form-control input-sm upper" id="obs_pre" name="obs_pre" type="text" autocomplete="off"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button id="eliminar_pre" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>       
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-success">Guardar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tab Otros -->
                        <div class="tab-pane fade" id="otros" role="tabpanel" aria-labelledby="otros-tab">
                            <div id="toolbar_otros">
                                <div class="form-inline" role="form">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary" id="agregar_otros" data-toggle="modal" data-target="#modal_otros">Agregar<sup class="ml-1">[F4]</sup></button>
                                    </div>
                                    <div class="form-group ml-1">
                                    <input type="hidden" id="desde3">
                                    <input type="hidden" id="hasta3">
                                    <div id="filtro_fecha3" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <table id="tabla_otros"></table>
                            
                            <!-- MODAL PRINCIPAL -->
                            <div class="modal fade" id="modal_otros" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel_otros"></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form class="form default-validation" id="formulario_otros" method="post" enctype="multipart/form-data" action="" novalidate>
                                            <input type="hidden" name="id_descuento" id="id_descuento">
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-12 col-sm-12">
                                                        <div class="row">
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="descuento" class="label-required">Descuento</label>
                                                                <input class="form-control input-sm upper" id="descuento" name="descuento" type="text" autocomplete="off" required>
                                                            </div>
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="id_funcionario_otro" class="label-required">Funcionario</label>
                                                                <select id="id_funcionario_otro" name="id_funcionario_otro" class="form-control" required></select>
                                                            </div>
                                                            <div class="form-group col-md-6 col-sm-12">
                                                                <label for="monto_otro" class="label-required">Monto</label>
                                                                <input class="form-control input-sm text-right" type="text" name="monto_otro" id="monto_otro" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" required>
                                                            </div>
                                                            <div class="form-group col-md-6 col-sm-6">
                                                                <label for="fecha_otro">Fecha</label>
                                                                <input class="form-control input-sm" type="date" name="fecha_otro" id="fecha_otro" autocomplete="off" value="<?php echo date("Y-m-d");?>">
                                                            </div>
                                                            <div class="form-group col-md-12 col-sm-12">
                                                                <label for="obs_otro">Observaciones</label>
                                                                <textarea class="form-control input-sm upper" id="obs_otro" name="obs_otro" type="text" autocomplete="off"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button id="eliminar_otros" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>       
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
            </div>
        </div>
       <?php include 'footer.php'; ?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
