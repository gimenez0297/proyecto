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
                            <!-- <div class="form-inline" role="form">
                                <div class="form-group">
                                    <select id="proveedores" name="proveedores" class="form-control"></select>
                                </div>
                                <div class="form-group ml-1">
                                    <button type="button" class="btn btn-primary" id="imprimir" title="Imprimir Gastos"><i class="fas fa-print"></i></button>
                                </div>
                            </div> -->
                        </div>


                        <table id="tabla"></table>

                        <!-- MODAL FACTURAS -->
                        <div class="modal fade" id="modal_detalles" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Facturas</h5>
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