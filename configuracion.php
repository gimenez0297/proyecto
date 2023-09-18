<?php include 'header.php';
    $datos_pagopar = configuracionSistema(1);

    $nombre_sistema = $datos_pagopar->nombre_sistema;
    $subtitulo_sistema = $datos_pagopar->subtitulo_sistema;
    $numero_patronal = $datos_pagopar->numero_patronal;
    $periodo_devolucion = $datos_pagopar->periodo_devolucion;
    $utilidad=$datos_pagopar->utilidad;
    $limite=$datos_pagopar->limite_producto;
    $limite_caja=$datos_pagopar->limite_caja;
    $limite_egreso=separadorMiles($datos_pagopar->limite_egreso);
    $alerta_nro_timbrado=$datos_pagopar->alerta_nro_timbrado;
?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div id="mensaje"></div>
                        <!-- MODA PRINCIPAL -->
                        <div class="card" id="card-sistema" tabindex="-1">
                            <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="editar">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 pt-2">
                                            <h4>SISTEMA</h4>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-4 form-group">
                                                    <label for="nombre_sistema" class="label-required">Nombre Sistema <a  href="#" data-toggle="popover"  data-placement="right" title="Descripción" data-trigger="focus | hover"
                                                                data-content="Nombre asignado al sistema."> <i class="fa fa-info-circle" ></i>
                                                            </a></label>
                                                    <div class="input-group">
                                                        <input class="form-control input-sm" type="text" name="nombre_sistema" id="nombre_sistema" autocomplete="off" value="<?php echo $nombre_sistema; ?>" required>
                                                        <div class="input-group-prepend">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 form-group">
                                                    <label for="numero_patronal" class="label-required">Número Patronal <a  href="#" data-toggle="popover"  data-placement="right" title="Descripción" data-trigger="focus | hover"
                                                                data-content="Código identificador para el M.T.E.S.S por parte del empleador."> <i class="fa fa-info-circle" ></i>
                                                            </a></label>
                                                    <div class="input-group">
                                                        <input class="form-control input-sm text-right" type="text" name="numero_patronal" id="numero_patronal" autocomplete="off" value="<?php echo $numero_patronal; ?>" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 form-group">
                                                    <label for="limite_egreso" class="label-required">Límite De Egresos <a  href="#" data-toggle="popover"  data-placement="right" title="Descripción" data-trigger="focus | hover"
                                                                data-content="Se establece el importe máximo de salida de capital. Se notificará al momento de sobrepasar el límite establecido."> <i class="fa fa-info-circle" ></i>
                                                            </a></label>
                                                    <div class="input-group-prepend">
                                                        <input class="form-control input-sm text-right" type="text" name="limite_egreso" id="limite_egreso" autocomplete="off" value="<?php echo $limite_egreso; ?>" onkeyup="separadorMilesOnKey(event,this)" required>
                                                        <span class="input-group-text">GS.</span>
                                                    </div>
                                                </div>
                                               
                                                <div class="col-md-4 form-group pt-2">
                                                    <label for="utilidad" class="label-required">Utilidad <a  href="#" data-toggle="popover"  data-placement="right" title="Descripción" data-trigger="focus | hover"
                                                                data-content="Cuando la venta de un producto este por debajo del límite establecido, genera comisión para el vendedor."> <i class="fa fa-info-circle" ></i>
                                                            </a></label>
                                                    <div class="input-group mb-1 ">
                                                    <input class="form-control input-sm autonumeric text-right"
                                                                type="text" name="utilidad" id="utilidad"
                                                                data-allow-decimal-padding="false"
                                                                data-decimal-character=","
                                                                data-digit-group-separator="."
                                                                data-maximum-value="100"
                                                                value="<?php echo $utilidad; ?>" autocomplete="off"  required>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 form-group pt-2">
                                                    <label for="alerta_nro_timbrado" class="label-required">Mínimo Timbrado <a  href="#" data-toggle="popover"  data-placement="right" title="Descripción" data-trigger="focus | hover"
                                                                data-content="Se establece la cantidad mínima de números disponibles. Se notificará al momento de estar por debajo del límite establecido."> <i class="fa fa-info-circle" ></i>
                                                        </a></label>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">CANT.</span>
                                                        <input class="form-control input-sm text-right" type="text" name="alerta_nro_timbrado" id="alerta_nro_timbrado" autocomplete="off" value="<?php echo $alerta_nro_timbrado; ?>" onkeypress="return soloNumeros(event)" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 form-group pt-2">
                                                    <label for="limite_caja" class="label-required">Limite de conexión <a  href="#" data-toggle="popover" data-placement="right" title="Descripción" data-trigger="focus | hover"
                                                                data-content="Se establece la cantidad de días desde el último cierre de caja que se mantiene la conexión entre el sistema y la máquina asignada a la caja."> <i class="fa fa-info-circle" ></i>
                                                            </a></label>
                                                    <div class="input-group mb-1">
                                                        <input class="form-control input-sm text-right" type="text" name="limite_caja" id="limite_caja" autocomplete="off" value="<?php echo $limite_caja; ?>" onkeyup="separadorMilesOnKey(event,this)" required>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">DÍAS</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 form-group pt-2">
                                                    <label for="periodo_devolucion" class="label-required">Periodo Devolución <a  href="#" data-toggle="popover" title="Descripción"   data-placement="right" data-trigger="focus | hover"
                                                                data-content="Se establece la cantidad de días disponibles que tiene el cliente para la devolución de los productos desde el momento que realizo la compra"> <i class="fa fa-info-circle" ></i>
                                                            </a></label>
                                                    <div class="input-group mb-1">
                                                        <div class="input-group-prepend">
                                                            <input class="form-control input-sm text-right" type="text" name="periodo_devolucion" id="periodo_devolucion" autocomplete="off" value="<?php echo $periodo_devolucion; ?>" onkeyup="separadorMilesOnKey(event,this)" required>
                                                            <span class="input-group-text">DÍAS</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="card" id="card-web" tabindex="-1">
                            <div class="card-body">
                                <div class="row">
                                        <div class="col-12 pt-2">
                                        <h4>WEB</h4>
                                        <hr>
                                            <div class="row">
                                                <div class="col-md-4 form-group">
                                                    <label for="limite" class="label-required">Productos Destacados <a  href="#" data-toggle="popover" data-placement="right"  title="Descripción" data-trigger="focus | hover"
                                                                data-content="Cantidad de productos destacados en la web."> <i class="fa fa-info-circle" ></i>
                                                        </a></label>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">CANT.</span>
                                                        <input class="form-control input-sm text-right" type="text" name="limite" id="limite" autocomplete="off" value="<?php echo $limite; ?>" required>
                                                    </div>    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Modificar</button>
                                </div>
                            </form>
                        </div>
                    </div><!-- End col-12 -->
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
