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

                        <!-- MODAL PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-sm" role="document">
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
                                                        <div class="form-group col-md-12 col-sm-6 required">
                                                            <label for="codigo" class="label-required">Codigo</label>
                                                            <input class="form-control input-sm upper" type="text" name="codigo" id="codigo" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6 required">
                                                            <label for="servicio" class="label-required">Servicio</label>
                                                            <input class="form-control input-sm upper" type="text" name="servicio" id="servicio" autocomplete="off" required>
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6 required">
                                                            <label for="precio" class="label-required">Precio</label>
                                                            <input class="form-control input-sm upper text-right" type="text" placeholder="0" name="precio" id="precio" autocomplete="off" onkeyup="separadorMilesOnKey(event,this)" value="0">
                                                        </div>
                                                        <div class="form-group col-md-12 col-sm-6 required">
                                                            <label for="iva" class="label-required">Iva</label>
                                                            <select id="iva" name="iva" class="form-control" required>
                                                                <option value="1">EXENTAS</option>
                                                                <option value="2">5%</option>
                                                                <option value="3">10%</option>
                                                            </select>
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