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
						
                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_vacacion" id="id_vacacion">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                            <div class="form-group col-md-6 col-sm-6">
                                                                <label for="id_funcionario" class="label-required">Funcionario</label>
                                                                <select id="id_funcionario" name="id_funcionario" class="form-control" required></select>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6">
                                                                <label for="telefono" class="label-required">Año</label>
                                                                <select id="anho" name="anho" class="form-control" required>
                                                                    <option value="2022">2022</option>
                                                                    <option value="2023">2023</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6">
                                                                <label for="celular">Antiguedad</label>
                                                                <div class="input-group mb-1">
                                                                    <input class="form-control input-sm" id="antiguedad" name="antiguedad" type="text" autocomplete="off" value="0" disabled="disabled">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">AÑOS</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6">
                                                                <label for="id_estado">Corresponde</label>
                                                                <div class="input-group mb-1">
                                                                    <input class="form-control input-sm" id="corresponde" name="corresponde" type="text" autocomplete="off" value="0" readonly>
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">DIAS</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6">
                                                                <label for="id_puesto">Pendiente</label>
                                                                <div class="input-group mb-1">
                                                                    
                                                                    <input class="form-control input-sm" id="pendiente" name="pendiente" type="text" autocomplete="off" value="0" readonly>
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">DIAS</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6">
                                                                <label for="utilizar" class="label-required">A Utilizar</label>
                                                                <div class="input-group mb-1">
                                                                    <input class="form-control input-sm" id="utilizar" name="utilizar" type="text" autocomplete="off" onkeyup="contador_fechas()" required="">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">DIAS</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-md-3 col-sm-6">
                                                                <label for="importe">Monto</label>
                                                                <input class="form-control input-sm" id="importe" name="importe" type="text" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)">
                                                            </div>
                                                        </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="fecha_desde" class="label-required">Fecha Desde</label>
                                                            <input class="form-control input-sm" type="date" name="fecha_desde" id="fecha_desde" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="fecha_hasta">Fecha Hasta</label>
                                                            <input class="form-control input-sm" type="date" name="fecha_hasta" id="fecha_hasta" autocomplete="off" readonly="">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="observacion">Observaciones</label>
                                                            <textarea class="form-control input-sm upper" id="observacion" name="observacion" type="text" autocomplete="off"></textarea>
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
