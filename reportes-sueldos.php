<?php include 'header.php';?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php";?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php'?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <div id="toolbar">
                            <div class="form-inline" role="form">
                                <div class="form-group">
                                    <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>></select>
                                </div>
                                <div class="form-group ml-1">
                                    <select id="periodo" name="periodo" class="form-control"></select>
                                </div>
                                <button type="button" class="btn btn-primary ml-1" id="btn_exportar" title="Exportar"><i class="fas fa-file-excel mr-1"></i>Exportar</button>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODAL PRODUCTOS -->
                        <div class="modal fade" id="modal_liquidaciones" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Liquidaciones</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_liquidaciones">
                                                            <div class="alert alert-success" role="alert" id="toolbar_liquidaciones_text"></div>
                                                        </div>
                                                        <table id="tabla_liquidaciones"></table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_liquidaciones_des">
                                                            <div class="alert alert-danger" role="alert" id="toolbar_liquidaciones_text_des"></div>
                                                        </div>
                                                        <table id="tabla_liquidaciones_des"></table>
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
    </div>
    <?php include 'footer.php';?>
    </div>
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>

</html>