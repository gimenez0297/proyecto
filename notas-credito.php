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


                                <div class="form-group ml-1">
                                    <?php 
                                        $option = "";
                                        $disabled = "";
                                        if (esAdmin($usuario->id_rol) === false) {
                                            $option = "<option value=\"$usuario->id_sucursal\">$datos_empresa->sucursal</option>";
                                            $disabled = "disabled";
                                        }
                                    ?>
                                    <select id="filtro_sucursal" class="form-control" <?php echo $disabled; ?>>
                                        <?php echo $option; ?>
                                    </select>
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
                            <div class="modal-dialog modal-md2 modal-98" role="document">
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
                                                <div class="col-md-6">
                                                    <table id="tabla_principal"></table>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <table id="tabla_detalle"></table>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="cantidad">Cantidad A Devolver</label>
                                                            <input class="form-control input-sm text-center readonly" type="text" name="cantidad" id="cantidad" autocomplete="off" value="0" readonly>
                                                        </div>

                                                        <div class="form-group col-md-6">
                                                            <label for="total">Total</label>
                                                            <input class="form-control input-sm text-center readonly" type="text" name="total" id="total" autocomplete="off" value="0" readonly>
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
