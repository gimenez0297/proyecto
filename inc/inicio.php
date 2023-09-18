<?php
include 'header.php'; 

$meses = ["", "ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SETIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
$mes = $meses[date("n")];
$anio = date('Y');

?>
<script src="dist/js/sparkline/jquery.sparkline.min.js"></script>

</head>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <div class="preloader">
        <div class="loader">
            <div class="loader__figure"></div>
            <p class="loader__label">Cargando Ñamandú...</p>
        </div>
    </div>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid mt-2">
                <?php //include 'titulo.php'; ?>
                <div class="row">
                   
                   <!-- INGRESOS -->
                    <div class="col-lg-4">
                        <div class="card card-body">
                            <h5 class="card-title">Ingresos</h5>
                            <p class="text-muted"><?php echo "$mes $anio"; ?></p>
                            <div class="row">
                                <div class="col-6 m-b-15">
                                    <h4 id="ingresosGs" class="text-info">Gs. 0</h4>
                                </div>
                                <div class="col-6 m-b-15">
                                    <b id="ingresosVentas">0 Ventas</b>
                                </div>
                                <div class="col-12">
                                    <div id="ingresosSparkline" class="text-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                   <!-- EGRESOS -->
                    <div class="col-lg-4">
                        <div class="card card-body">
                            <h5 class="card-title">Egresos</h5>
                            <p class="text-muted"><?php echo "$mes $anio"; ?></p>
                            <div class="row">
                                <div class="col-6 m-b-15">
                                    <h4 id="egresosGs" class="text-danger">Gs. 0</h4>
                                </div>
                                <div class="col-6 m-b-15">
                                    <b></b>
                                </div>
                                <div class="col-12">
                                    <div id="egresosSparkline" class="text-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- UTILIDADES -->
                    <div class="col-lg-4">
                        <div class="card card-body">
                            <h5 class="card-title">Utilidades</h5>
                            <p class="text-muted"><?php echo "$mes $anio"; ?></p>
                            <div class="row">
                                <div class="col-6 m-b-15">
                                    <h4 id="utilidadesGs" class="text-inverse">Gs. 0</h4>
                                </div>
                                <div class="col-6 m-b-15">
                                    <b></b>
                                </div>
                                <div class="col-12">
                                    <div id="utilidadesSparkline" class="text-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div><!-- End row  -->
            </div><!-- End Container fluid  -->
        </div><!-- End Page wrapper  -->
       <?php include 'footer.php'; ?>
    </div><!-- End Wrapper -->

<script>
    
    // INGRESOS
    function ingresosSparkline() {
        $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/dashboard.php',
            type: 'GET',
            data: { q: 'ingresos' },
            beforeSend: function() {
                NProgress.start();
            },
            success: function (json) {
                var ventas = json.ventas;
                var ventas_por_dia = json.ventas_por_dia;
                $('#ingresosGs').html(separadorMiles("Gs. " + ventas.total));
                $('#ingresosVentas').html(ventas.cantidad_ventas + ' ventas');

                var datos_grafico = [];
                $(ventas_por_dia).each(function(index, value) {
                    datos_grafico.push(value.total);
                });

                $('#ingresosSparkline').sparkline(datos_grafico, {
                    type: 'bar',
                    height: '100',
                    barWidth: '4',
                    resize: true,
                    barSpacing: '5',
                    barColor: '#25a6f7'
                });

                NProgress.done();
            },
            error: function (xhr){
                NProgress.done();
                alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
            }
        });
    }
    ingresosSparkline();

    // EGRESOS
    function egresosSparkline() {
        $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/dashboard.php',
            type: 'GET',
            data: { q: 'egresos' },
            beforeSend: function() {
                NProgress.start();
            },
            success: function (json) {
                var egresos = json.egresos;
                var egresos_por_dia = json.egresos_por_dia;
                $('#egresosGs').html(separadorMiles("Gs. " + egresos.monto));

                var datos_grafico = [];
                $(egresos_por_dia).each(function(index, value) {
                    datos_grafico.push(value.monto);
                });

                $('#egresosSparkline').sparkline(datos_grafico, {
                    type: 'bar',
                    height: '100',
                    barWidth: '4',
                    resize: true,
                    barSpacing: '5',
                    barColor: '#f62d51'
                });

                NProgress.done();
            },
            error: function (xhr){
                NProgress.done();
                alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
            }
        });
    }
    egresosSparkline();

    // UTILIDADES
    function utilidadesSparkline() {
        $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/dashboard.php',
            type: 'GET',
            data: { q: 'utilidades' },
            beforeSend: function() {
                NProgress.start();
            },
            success: function (json) {
                var utilidades = json.utilidades;
                var utilidades_por_dia = json.utilidades_por_dia;
                $('#utilidadesGs').html(separadorMiles("Gs. " + utilidades.utilidades));

                var datos_grafico = [];
                $(utilidades_por_dia).each(function(index, value) {
                    datos_grafico.push(value.utilidades);
                });

                $('#utilidadesSparkline').sparkline(datos_grafico, {
                    type: 'bar',
                    height: '100',
                    barWidth: '4',
                    resize: true,
                    barSpacing: '5',
                    barColor: '#2b2b2b'
                });

                NProgress.done();
            },
            error: function (xhr){
                NProgress.done();
                alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
            }
        });
    }
    utilidadesSparkline();

    // RESIZE
    var sparkResize;
    $(window).resize(function (e) {
        clearTimeout(sparkResize);
        sparkResize = setTimeout(function() {
            ingresosSparkline();
            egresosSparkline();
            utilidadesSparkline();
        }, 100);
    });
</script>

</body>
</html>