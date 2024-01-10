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
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F1]</sup></button>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_gastos_fijos" id="id_gastos_fijos">
                                        <input type="hidden" name="id_proveedor" id="id_proveedor">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="id_tipo_gasto" class="label-required">Tipo de Gasto</label>
                                                            <select id="id_tipo_gasto" name="id_tipo_gasto" class="form-control" required></select>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-12 required">
                                                            <label for="id_tipo_factura" class="label-required">Tipo de Factura</label>
                                                            <select id="id_tipo_factura" name="id_tipo_factura" class="form-control" required></select>
                                                        </div>



                                                        <div class="form-group-sm col-md-4 col-sm-12">
                                                            <label for="razon_social" class="label-required">Proveedor / Razón Social</label>
                                                            <select class="form-control" name="razon_social" id="razon_social" required></select>
                                                        </div>

                                                        <div class="form-group-sm col-md-4 col-sm-12">
                                                            <label for="ruc" class="label-required">R.U.C</label>
                                                            <div class="input-group">
                                                                <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" readonly required>
                                                                <div class="input-group-append">

                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="descripcion" class="label-required">Concepto</label>
                                                            <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off" required="">
                                                        </div>

                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <label for="descripcion" class="label-required">Nombre</label>
                                                            <input class="form-control input-sm upper" type="text" name="nombre" id="nombre" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-4 col-sm-6 required">
                                                            <label for="monto" class="label-required">Monto</label>
                                                            <input class="form-control input-sm text-right" type="text" name="monto" id="monto" autocomplete="off" readonly required>
                                                        </div>


                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="iva_10">IVA 10%</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_10" id="iva_10" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-3 col-sm-6">
                                                            <label for="iva_5">IVA 5%</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="iva_5" id="iva_5" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-2 col-sm-6">
                                                            <label for="exenta">Exenta</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="exenta" id="exenta" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
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