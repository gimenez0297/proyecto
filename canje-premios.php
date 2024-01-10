<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php'; ?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                    <div class="row">
                        <div class="col-12 pt-2">
                            <h4>Datos del Cliente</h4>
                            <hr>

                            <!-- CABECERA -->
                            <div class="form-row">
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="ruc" class="label-required">R.U.C</label>
                                    <div class="input-group">
                                        <input type="hidden" id="id_cliente" name="id_cliente">
                                        <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="btn_buscar_cliente" data-toggle="modal" data-target="#modal_clientes" title="Buscar Cliente"><i class="fa fa-search"></i><sup class="ml-1">[F1]</sup></button>                                 
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-sm-12">
                                    <label for="razon_social">Cliente / Razón Social</label>
                                    <select id="razon_social" name="razon_social" class="form-control"></select>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="numero">Número</label>
                                    <input class="form-control input-sm text-right" type="text" name="numero" id="numero" autocomplete="off" value="0" readonly>
                                </div>
                                <div class="form-group col-md-2 col-sm-12">
                                    <label for="detalle_puntos">Puntos Cliente</label>
                                    <input class="form-control input-sm text-right" type="text" name="detalle_puntos" id="detalle_puntos" autocomplete="off" value="1" readonly>
                                </div>
                                <div class="form-group col-md-12 col-sm-12">
                                    <label for="observacion">Observación</label>
                                    <textarea class="form-control input-sm upper" type="text" name="observacion" id="observacion" autocomplete="off" rows="2"></textarea>
                                </div>
                            </div>
                            <!-- FIN CABECERA -->

                            <!-- PRODUCTOS -->
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <h4>Productos</h4>
                                    <hr>

                                    <div class="form-row">
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label for="codigo">Código<sup class="ml-1">[F2]</sup></label>
                                            <input class="form-control input-sm text-center" type="text" name="codigo" id="codigo" autocomplete="off" onkeypress="return soloNumeros(event)">
                                        </div>
                                        <div class="form-group col-md-6 col-sm-12">
                                            <label for="premio">Premio (F3)</label>
                                            <select id="premio" name="producto" class="form-control"></select>
                                        </div>
                                        <div class="form-group col-md-1 col-sm-12">
                                            <label for="cantidad">Cantidad</label>
                                            <input class="form-control input-sm text-right" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1">
                                        </div>
                                        <div class="form-group col-md-1 col-sm-12">
                                            <label for="puntos">Puntos</label>
                                            <input class="form-control input-sm text-right" type="text" name="puntos" id="puntos" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="1" readonly>
                                        </div>
                                        <div class="form-group col-md-2 col-sm-12">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-primary btn-block" id="agregar-premio">Agregar</button>
                                        </div>
                                        <div class="form-group col-md-12 col-sm-12">
                                            <table id="tabla_productos"></table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- FIN PRODUCTOS -->
                            <div class="form-group-sm pt-2">
                                <button type="button" class="btn btn-lg btn-info float-right" id="btn_guardar">Continuar<small><sup class="ml-1">[F4]</sup></small></button>
                            </div>

                        </div>
                    </div>
                </form>

                <!-- MODAL CLIENTES -->
                <div class="modal fade" id="modal_clientes" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Buscar Cliente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12 col-sm-12">
                                                <div id="toolbar_clientes">
                                                    <div class="alert alert-info" role="alert">
                                                        <i class="fas fa-info-circle"></i> Seleccione un ítem de la lista con doble click
                                                    </div>
                                                </div>
                                                <table id="tabla_clientes"></table>
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