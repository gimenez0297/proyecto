<?php include 'header.php'; ?>

<!-- <style>
    .sortable.desc, .sortable.asc{
        border: solid 1px #00a7b0;
    }
</style> -->

<body class="<?php include 'menu-class.php'; ?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php';
        include 'leftbar.php' ?>
        <div class="page-wrapper contenedor_dinamico">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <div id="toolbar">
                            <div class="form-inline" role="form">

                                <button type="button" class="btn btn-md btn-primary mr-1" id="filtros"  data-toggle="modal" data-target="#modal">Filtros<sup class="ml-1">[F1]</sup></button>
                                <button type="button" class="btn btn-md btn-success mr-1" id="imprimir">Imprimir</button>
                                <button type="button" class="btn btn-md btn-success" id="exportar">Exportar</button>

                            </div>
                        </div>
                       
                        <table id="tabla"></table>

                        <!-- MODAL FILTROS -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
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
                                                        <!-- FECHA -->
                                                        <div class="form-group hola col-md-3 col-sm-12">
                                                            <input type="hidden" id="desde">
                                                            <input type="hidden" id="hasta">
                                                            <label for="filtro_fecha">Rango de Fecha</label>
                                                            <div id="filtro_fecha" class="btn btn-default form-control">
                                                                <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                                                <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                                                            </div>
                                                        </div>

                                                        <!-- SUCURSAL -->
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="filtro_sucursal">Sucursal</label>
                                                            <select id="filtro_sucursal" name="filtro_sucursal" class="form-control"></select>
                                                        </div>

                                                        <!-- PROVEEDOR -->
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="filtro_proveedor">Proveedor Principal</label>
                                                            <select id="filtro_proveedor" name="filtro_proveedor" class="form-control"></select>
                                                        </div>

                                                        <!-- PROVEEDOR -->
                                                        <div class="form-group col-md-3 col-sm-12">
                                                            <label for="filtro_vendedor">Vendedor</label>
                                                            <select id="filtro_vendedor" name="filtro_vendedor" class="form-control"></select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">  
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            <button type="button" class="btn btn-success" id="filtrar">Filtrar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL DETALLE -->
                        <div class="modal fade" id="modal_detalle" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detalles</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12">
                                                <div class="form-row">
                                                    <div class="form-group col-md-12 col-sm-12">
                                                        <div id="toolbar_detalle">
                                                            <div class="alert alert-info" role="alert" id="toolbar_titulo"></div>
                                                        </div>
                                                        <table id="tabla_detalle"></table>
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
