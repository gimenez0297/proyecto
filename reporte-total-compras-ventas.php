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
                                    <select id="filtro_sucursal" class="form-control"> </select>
                                </div>
                                <div class="form-group ml-1">
                                    <select id="filtro_reporte" name="filtro_reporte" class="form-control">
                                        <option value="1">COMPRAS</option>
                                        <option value="2">VENTAS</option>
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
                                <div class="form-inline ml-1" role="form">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-md btn-success float-left" id="imprimir">Imprimir</button>
                                        <button type="button" class="btn btn-md btn-success float-right ml-1" id="exportar">Exportar</button>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <table id="tabla"></table>

                        

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