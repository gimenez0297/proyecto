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
                                    <button type="button" class="btn btn-md btn-success float-left" id="imprimir">Imprimir</button>
                                    <button type="button" class="btn btn-md btn-success float-right ml-10" id="exportar">Exportar</button>
                                </div>
                            </div>
                        </div>
                        <h4>Filtros</h4>
                        <hr>

                        <!-- Filtros -->
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="pr-2" for="fecha">Fechas: </label><br>
                                <div id="filtro_fecha" class="btn btn-default col-12 form-control d-block">
                                    <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                    <span class="ml-3" style="font-size: 14px;"></span><i class="ml-3 mr-2 fas fa-chevron-down"></i>
                                </div>
                                <input type="hidden" id="desde">
                                <input type="hidden" id="hasta">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="proveedores" style="display: block;">Proveedores</label>
                                <select id="proveedores" name="proveedores" class="form-control"></select>
                            </div>
                            <div class="form-group-cm col-md-1 col-sm-12 ">
                                <button type="button" class="btn btn-primary" id="btn_buscar" style="margin-top:22px;"><i class="fa fa-search"></i> Filtrar</button>
                            </div>
                        </div>

                        <table id="tabla"></table>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>

</html>