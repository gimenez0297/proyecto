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
                                    <input type="hidden" id="desde">
                                    <input type="hidden" id="hasta">
                                    <div id="filtro_fecha" class="btn btn-default form-control">
                                        <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                        <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="form-inline ml-1" role="form">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-md btn-success float-left" id="imprimir">Imprimir</button>
                                        <button type="button" class="btn btn-md btn-success float-right ml-1" id="exportar">Exportar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table id="tabla"></table>

                        <!-- MODAL DETALLE -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-98" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">INGRESOS Y EGRESOS</h5>
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
                                                            <div class="alert alert-success" role="alert" id="toolbar_ingresos_text"></div>
                                                        </div>
                                                        <table id="tabla_ingresos"></table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_liquidaciones_des">
                                                            <div class="alert alert-danger" role="alert" id="toolbar_egresos_text"></div>
                                                        </div>
                                                        <table id="tabla_egresos"></table>
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
        var fecha_actual = '<?php echo date("Y-m-d"); ?>';
    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>

</html>
