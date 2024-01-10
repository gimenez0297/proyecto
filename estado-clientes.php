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
                                    <button type="button" class="btn btn-md btn-success float-left" id="pagar" data-toggle="modal" data-target="#modal_pagar">Pagar</button>
                                </div>
                            </div>
                        </div>

                        <h4>Filtros</h4>
                        <hr>
                        <!-- Filtros -->
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="filtro_sucursal" style="display: block;">Sucursal</label>
                                <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>></select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="pr-2" for="fecha">Fechas</label><br>
                                <div id="filtro_fecha" class="btn btn-default col-12 form-control d-block">
                                    <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                    <span class="ml-3" style="font-size: 14px;"></span><i class="ml-3 mr-2 fas fa-chevron-down"></i>
                                </div>
                                <input type="hidden" id="desde">
                                <input type="hidden" id="hasta">
                            </div>
                            <div class="form-group-cm col-md-2 col-sm-12">
                                <label for="filtro_estado" style="display: block;">Estados</label>
                                <select id="filtro_estado" name="filtro_estado" class="form-control">
                                    <option value="3">Todos</option>
                                    <option value="1">Pagado</option>
                                    <option value="2">Pendiente</option>
                                </select>
                            </div>
                            <div class="form-group-cm col-md-5 col-sm-12 ">
                                <label for="filtro_clientes" style="display: block;">Clientes</label>
                                <select id="filtro_clientes" name="filtro_clientes" class="form-control" required></select>
                            </div>
                        </div>

                        <table id="tabla"></table>

                        <div class="modal fade" id="modal_pagar" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id" id="id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6 col-sm-6">
                                                            <label for="nombre">Cliente</label>
                                                            <input class="form-control input-sm upper" type="text" name="nombre" id="nombre" autocomplete="off" readonly>
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-6">
                                                            <label for="monto">Total a Pagar</label>
                                                            <input class="form-control input-sm upper" type="text" name="monto" id="monto" autocomplete="off" readonly>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 required">
                                                            <label for="fecha_pago" class="label-required">Fecha</label>
                                                            <input class="form-control input-sm upper" type="date" name="fecha_pago" id="fecha_pago" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 required">
                                                            <label for="metodo" class="label-required">Metodo de Pago</label>
                                                            <select id="metodo" name="metodo" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 required monto_pagar">
                                                            <label for="monto_pagar" class="label-required">Monto a Pagar</label>
                                                            <input class="form-control input-sm upper" type="text" name="monto_pagar" id="monto_pagar" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" required>
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6 detalles">
                                                            <label for="detalles">Detalles</label>
                                                            <input class="form-control input-sm" type="text" name="detalles" id="detalles" autocomplete="off" required="">
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

                        <!-- MODAL FACTURAS -->
                        <div class="modal fade" id="modal_recibos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Recibos</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_recibos">
                                                            <div class="alert alert-info" role="alert" id="toolbar_recibos_text"></div>
                                                        </div>
                                                        <table id="tabla_recibos"></table>
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
        <?php include 'footer.php'; ?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>

</html>