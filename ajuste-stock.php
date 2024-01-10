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
                                    <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>></select>
                                    <!-- <button type="button" class="btn btn-warning ml-1" id="actualizar-stock"></i>Actualizar Stock</button> -->
                                </div>
                                <div class="form-group ml-1">
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-group ml-1">
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F1]</sup></button>
                                </div>
                                <div class="form-group ml-1">
                                    <button type="button" class="btn btn-primary" name="imprimir" id="imprimir">Imprimir<sup class="ml-1">[F2]</sup></button>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>
						
                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered modal-90" role="document" style="top: -18px">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="sucursal" class="label-required">Sucursal</label>
                                                            <select id="sucursal" name="sucursal" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-7 col-sm-12">
                                                            <label for="descripcion" class="label-required">Descripción</label>
                                                            <input class="form-control input-sm upper" type="text" name="descripcion" id="descripcion" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label for="numero">Número</label>
                                                            <input class="form-control input-sm text-right" type="text" name="numero" id="numero" autocomplete="off" value="0" readonly>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <h4>Productos</h4>
                                                            <hr>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label for="codigo">Código<sup class="ml-1">[F1]</sup></label>
                                                            <input class="form-control input-sm text-center" type="text" name="codigo" id="codigo" autocomplete="off" onkeypress="return soloNumeros(event)">
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="principio">Producto<sup class="ml-1">[F2]</sup></label>
                                                            <select id="producto" name="producto" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label for="lote">Lote<sup class="ml-1">[F3]</sup></label>
                                                            <div class="input-group">
                                                                <select id="lote" name="lote" class="form-control"></select>
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-primary" id="agregar_lote" title="Agregar Proveedor"><i class="fa fa-plus"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-1 col-sm-12">
                                                            <label for="cantidad">Cantidad</label>
                                                            <input class="form-control input-sm text-right" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-1 col-sm-12">
                                                            <label for="fraccionado">Fraccionado</label>
                                                            <input class="form-control input-sm text-right" type="text" name="fraccionado" id="fraccionado" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-12 centro">
                                                            <div class="custom-control-inline">
                                                                <div class="custom-control custom-radio custom-control-inline check" style="display:flex; justify-content: center; align-items:center;">
                                                                    <input type="radio" id="positivo" name="positivo" class="custom-control-input" value="1">
                                                                    <label class="custom-control-label" for="positivo">Positivo</label>
                                                                </div>
                                                                <div class="custom-control custom-radio custom-control-inline check">
                                                                    <input type="radio" id="negativo" name="positivo" class="custom-control-input positivo" value="2">
                                                                    <label class="custom-control-label" for="negativo">Negativo</label>
                                                                </div>
                                                                <div class="custom-control custom-radio custom-control-inline check">
                                                                    <input type="radio" id="reemplazo" name="positivo" class="custom-control-input positivo" value="3">
                                                                    <label class="custom-control-label" for="reemplazo">Reemplazar</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-8">
                                                            <input class="form-control input-sm upper" type="text" name="motivo" id="motivo" autocomplete="off" placeholder="Motivo del Ajuste">
                                                        </div>
                                                        <div class="form-group col-md-1 col-sm-12">
                                                            <button type="button" class="btn btn-primary btn-block" id="agregar-producto">Agregar</button>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <table id="tabla_detalles"></table>
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

                        <!-- MODAL LOTES -->
                        <div class="modal fade" id="modal_lote" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cargar Lote</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario_lote" method="post" enctype="multipart/form-data" action="">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="lote" class="label-required">Lote</label>
                                                            <input class="form-control input-sm upper" type="text" name="lote" id="descripcion_lote" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="proveedor" class="label-required">Proveedor</label>
                                                            <select id="proveedor" name="proveedor" class="form-control input-sm" required></select>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="costo" class="label-required">Costo</label>
                                                            <input class="form-control input-sm text-right" type="text" name="costo" id="costo" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" maxlength="15" required="">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="vencimiento" class="label-required">Vencimiento</label>
                                                            <input class="form-control input-sm" type="date" name="vencimiento" id="vencimiento" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-6 col-sm-12">
                                                            <label for="vencimiento_canje" class="label-required">Vencimiento Canje</label>
                                                            <input class="form-control input-sm" type="date" name="vencimiento_canje" id="vencimiento_canje" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="canje" name="canje" value="1">
                                                                <label class="custom-control-label" for="canje">Canje</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL PRODUCTOS -->
                        <div class="modal fade" id="modal_detalles" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-90" role="document">
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
                                                        <div id="toolbar_detalles">
                                                            <div class="alert alert-info" role="alert" id="toolbar_detalles_text"></div>
                                                        </div>
                                                        <table id="tabla_ajustes"></table>
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
