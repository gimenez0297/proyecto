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
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-group ml-1">
                                    <select id="filtro_producto" name="filtro_producto" class="form-control"></select>
                                </div>
                                <div class="form-group ml-1">
                                    <select id="filtro_tipo_producto" name="filtro_tipo_producto" class="form-control"></select>
                                </div>

                                <button type="button" class="btn btn-primary ml-1" id="imprimir" title="Imprimir Stock"><i class="fas fa-print"></i></button>
                            </div>
                        </div>
                        <table id="tabla"></table>


                        <!-- MODAL LOTES -->
                        <div class="modal fade" id="modal_lotes" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Cargas</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_lotes">
                                                            <div class="alert alert-info" role="alert">
                                                                <i class="fas fa-info-circle"></i> Producto: <span id="lotes_producto"></span>
                                                            </div>
                                                        </div>
                                                        <table id="tabla_lotes"></table>
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
