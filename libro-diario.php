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
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal" disabled>Agregar Asiento<sup class="ml-1">[F1]</sup></button>
                                </div>
                                <div class="form-group ml-1">
                                    <select id="libros" name="libros" class="form-control"></select>
                                </div>
                            </div>
                        </div>

                        <table id="tabla"></table>

                        <!-- MODAL DETALLES -->
                        <div class="modal fade" id="modal_detalles" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Asientos</h5>
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
                                                        <table id="tabla_detalles"></table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
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
                                                <div class="col-12 pt-2">
                                                    <h4>Datos del Asiento</h4>
                                                    <hr>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="fecha" class="label-required">Fecha</label>
                                                            <input class="form-control" type="date" name="fecha" id="fecha" autocomplete="off" value="<?php echo date('Y-m-d'); ?>">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="comprobante" class="label-required">Comprobante</label>
                                                            <select id="comprobante" name="comprobante" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12 required">
                                                            <label for="nro" class="label-required">N° Asiento</label>
                                                            <input class="form-control input-sm" type="nro" name="nro" id="nro" autocomplete="off" readonly required>
                                                        </div>
                                                        <div class="form-group col-md-5 col-sm-6">
                                                            <label for="importe" class="label-required">Importe</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="importe" id="importe" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6">
                                                            <label for="descripcion" class="label-required">Descripción</label>
                                                            <textarea class="form-control input-sm upper" type="text" name="descripcion" id="descripcion" autocomplete="off" required></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <h4>Asientos</h4>
                                                    <hr>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="plan">Plan</label>
                                                            <select id="plan" name="plan" class="form-control"></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="concepto">Concepto</label>
                                                            <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off" onfocus="this.select()">
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label for="debe">Debe</label>
                                                            <input class="form-control input-sm text-right" type="text" placeholder="0" name="debe" id="debe" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" onfocus="this.select()">
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-12">
                                                            <label for="haber">Haber</label>
                                                            <input class="form-control input-sm text-right" type="text" placeholder="0" name="haber" id="haber" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0" onfocus="this.select()">
                                                        </div>
                                                        <div class="form-group col-md-1 col-sm-12">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-primary btn-block" id="agregar-asiento">Agregar</button>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <table id="tabla_asientos"></table>
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