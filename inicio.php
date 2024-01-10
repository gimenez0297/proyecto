<?php 
	include 'header.php'; 
?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid mt-2 cards_dashboard">
                <div class="row">
                   <div class="col-md-4">
                    <input type="hidden" id="id_user" name="id_user" value="<?php echo $usuario->id;?>">
                       <h6 id="telefono_usuario"></h6>
                   </div>
                </div>

                <!-- <div class="card" style="width: 49%;">
                    <div class="card-body">
                        <div class="form-group">
                            <input type="hidden" id="desde">
                            <input type="hidden" id="hasta">
                            <div id="filtro_fecha" class="btn btn-default form-control">
                                <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div id="container"></div>
                    </div>
                </div>
 -->
                <!-- <div class="card" style="width: 49%;">
                    <div class="card-body">
                        <div class="form-group">
                            <input type="hidden" id="desde_sucursal">
                            <input type="hidden" id="hasta_sucursal">
                            <div id="filtro_fecha_sucursal" class="btn btn-default form-control">
                                <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div id="container1"></div>
                    </div>
                </div>
            </div> -->
       <!--      
            <div class="container-fluid mt-2 cards_dashboard">
                <div class="card" style="width: 99%;">
                    <div class="card-body">
                        <div class="form-group ml-1">
                            <input type="hidden" id="desde_anio">
                            <input type="hidden" id="hasta_anio">
                            <div id="filtro_fecha_anio" class="btn btn-default form-control">
                                <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div id="container2"></div>
                    </div>
                </div>
            </div>  -->

          <!--   <div class="container-fluid mt-2 cards_dashboard" id = "ganancia_mes">
                <div class="card" style="width: 99%;">
                    <div class="card-body">
                        <div class="form-group ml-1">
                            <input type="hidden" id="desde_anho">
                            <input type="hidden" id="hasta_anho">
                            <div id="filtro_fecha_anho" class="btn btn-default form-control">
                                <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div id="container3"></div>
                    </div>
                </div>
            </div> -->


            <!-- <div class="container-fluid mt-2 cards_dashboard">
                <div class="card" style="width: 99%;">
                    <div class="card-body">
                        <div class="form-group">
                            <input type="hidden" id="desde_venc">
                            <input type="hidden" id="hasta_venc">
                            <div id="filtro_fecha_venci" class="btn btn-default form-control">
                                <i class="glyphicon glyphicon-calendar fas fa-calendar-alt"></i>&nbsp;
                                <span></span><i class="ml-2 mr-1 fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div style="width: 80%;float: left;">
                            <table id="tabla"></table>
                        </div>
                        <div style="width: 20%;float: right;padding-left: 46px;" class = "seleccionar">
                            <p class="point_select active" id="span_circule_red" value = "1">
                                <span class = "span_circule_red"></span><b>VENCE EN 3 DÍAS</b></p>

                            <p class="point_select active" id="span_circule_amarillo" value = "1">
                                <span class = "span_circule_amarillo"></span><b>VENCE EN 4 DÍAS</b></p>

                            <p class="point_select active" id="span_circule_lila" value = "1">
                                <span class = "span_circule_lila"></span><b>VENCE EN 5 DÍAS</b></p>

                            <p class="point_select active" id="span_circule_verde" value = "1">
                                <span class = "span_circule_verde"></span><b>VENCE EN 7 DÍAS</b></p>

                            <p class="point_select active" id="span_circule_celeste" value = "1">
                                <span class = "span_circule_celeste"></span><b>VENCE EN 30 DÍAS</b></p>
                        </div>
                    </div>
                </div>
            </div> -->
            </div>
        </div>
       <?php include 'footer.php'; ?>
    </div>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
