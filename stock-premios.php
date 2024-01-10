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
                                <!-- <div class="form-group mr-1">
                                    <select id="filtro_sucursal" class="form-control" <?php echo $usuario->id_rol != 1 ? 'disabled' : ''; ?>></select>
                                </div> -->
                                <!-- <div class="form-group ml-1">
                                    <select class="form-select" name="estado" id="estado">
                                            <option value="" disabled selected>Estado</option>
                                            <option value="2">ACTIVO</option>
                                            <option value="1">VENCIDO</option>
                                        </select>
                                </div> -->
                                <!-- <div class="form-group ml-1">
                                    <select id="filtro_tipo_producto" name="filtro_tipo_producto" class="form-control"></select>
                                </div> -->

                                <button type="button" class="btn btn-primary ml-1" id="imprimir" title="Imprimir Stock"><i class="fas fa-print"></i></button>
                        
                                <!-- <button type="button" class="btn btn-primary ml-1" id="filtros_impresion" data-toggle="modal" data-target="#modal">Filtros<sup class="ml-1">[F1]</sup></button> -->
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
                                                                <i class="fas fa-info-circle"></i> Premio: <span id="lotes_producto"></span>
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
