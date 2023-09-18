<?php 
	include 'inc/funciones.php';
	$tit_tmp4 = "{$pag_padre}";
	$tit_tmp3 = str_replace("-"," ",$tit_tmp4);
	$tit_tmp2 = explode(".php",$tit_tmp3);
	$tit_tmp = $tit_tmp2[0];

    // Para evitar caché en archivos css y js
    $v = random_int(1, 999999);

	$pagina = basename($_SERVER['PHP_SELF']);
	$js_pagina = str_replace('.php', '', $pagina);
	$js_pagina = "js/pages/$js_pagina.js?v=$v";

	$datos_sistema = configuracionSistema();
	$nombre_sistema = $datos_sistema->nombre_sistema;
	$favicon = $datos_sistema->favicon;
	if ($tit_tmp <> "index"){
		if (verificaLogin($pagina)){
			$datos_empresa = datosSucursal($auth->getUsername());
			$establecimiento = $datos_empresa->nombre_empresa;
			$permisos = permisos($auth->getUsername());
			$titulo = $permisos->titulo_pagina." - <span style='font-size:16px'>".$establecimiento."</span>";
			$title = $permisos->titulo_pagina." - ".$nombre_sistema;
		}else {
            header('Location: '.url());
            exit;
        }
	}else{
		$titulo = ucwords($tit_tmp);
		$title = $nombre_sistema;
	}

    $usuario = datosUsuario($auth->getUsername());
	
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $favicon; ?>">
    <title><?php echo $title; ?></title>
	<style type="text/css"><?php include 'colores.php'; ?></style>
	<script src="dist/plugins/jquery/jquery-3.2.1.min.js"></script>
	<!-- Bootstrap popper Core JavaScript -->
	<script src="dist/plugins/popper/popper.min.js"></script>
    <script src="dist/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="dist/plugins/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="dist/plugins/waves/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js?v=2"></script>
	<script src="dist/plugins/jquery-easing/jquery.easing.min.js"></script>
	<script src="dist/plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- Sweet-Alert 2 -->
	<link href="dist/plugins/sweetalert2/sweetalert2.css" rel="stylesheet" type="text/css">
    <script src="dist/plugins/sweetalert2/sweetalert2.js"></script>
	<!-- TOAST -->
	<link href="dist/plugins/toast-master/css/jquery.toast.css" rel="stylesheet">
	<script src="dist/plugins/toast-master/js/jquery.toast.js"></script>
	<!-- Nprogress -->
	<script src="dist/plugins/nprogress/nprogress.js"></script>
	<link href="dist/plugins/nprogress/nprogress.css?v=1" rel="stylesheet">
	<!-- Select2 -->
	<link href="dist/plugins/select2/css/select2.min.css" rel="stylesheet">
	<link href="dist/plugins/select2/css/select2-bootstrap4.min.css" rel="stylesheet">
	<script src="dist/plugins/select2/js/select2.full.min.js"></script>
	<script src="dist/plugins/select2/js/select2-dropdownPosition.js"></script>
	<script src="dist/plugins/select2/js/i18n/es.js"></script>
	<!-- Sparkline -->
    <script src="dist/plugins/sparkline/jquery.sparkline.min.js"></script>
	<!-- BootstrapTable -->
    <link rel="stylesheet" href="dist/plugins/bootstrap-table/bootstrap-table.css">
    <script src="dist/plugins/bootstrap-table/bootstrap-table.js"></script>
    <script src="dist/plugins/bootstrap-table/extensions/export/bootstrap-table-export.js"></script>
    <script src="dist/plugins/bootstrap-table/extensions/export/tableExport.js"></script>
    <script src="dist/plugins/bootstrap-table/locale/bootstrap-table-es-CL.min.js"></script>
    <script src="dist/plugins/bootstrap-table/extensions/mobile/bootstrap-table-mobile.js"></script>
    <!-- BootstrapTable keyEvents -->
    <script src="dist/plugins/bootstrap-table/extensions/key-events/bootstrap-table-key-events.js"></script>
    <!-- BootstrapTable Editable -->
    <link rel="stylesheet" href="dist/plugins/bootstrap-table/extensions/editable/css/bootstrap-editable.css">
    <script src="dist/plugins/bootstrap-table/extensions/editable/bootstrap-editable.js"></script>
    <script src="dist/plugins/bootstrap-table/extensions/editable/bootstrap-table-editable.js"></script>
    <!-- BootstrapTable Group By -->
    <link rel="stylesheet" href="dist/plugins/bootstrap-table/extensions/group-by-v2/bootstrap-table-group-by.css">
    <script src="dist/plugins/bootstrap-table/extensions/group-by-v2/bootstrap-table-group-by.js"></script>
    <!-- BootstrapTable CustomView -->
    <script src="dist/plugins/bootstrap-table/extensions/custom-view/bootstrap-table-custom-view.js"></script>
    <!-- BootstrapTable Autorefresh -->
    <script src="dist/plugins/bootstrap-table/extensions/auto-refresh/bootstrap-table-auto-refresh.js"></script>
    <!-- JQuery TreeGrid -->
    <link rel="stylesheet" href="dist/plugins/jquery-treegrid/css/jquery.treegrid.css">
    <script src="dist/plugins/jquery-treegrid/js/jquery.treegrid.js"></script>
    <!-- BootstrapTable Treegrid -->
    <script src="dist/plugins/bootstrap-table/extensions/treegrid/bootstrap-table-treegrid.js"></script>
    <!-- JQuery Cookie -->
    <script src="dist/plugins/jquery-cookie/jquery.cookie-1.4.1.min.js"></script>
    <!-- Bootstrap-select -->
    <link href="dist/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
    <script src="dist/plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
    <script src="dist/plugins/bootstrap-select/js/defaults-es_ES.js"></script>
	<!-- JQuery Mask -->
    <script src="dist/plugins/jquery-mask/jquery.mask.min.js"></script>
	<!-- Summernote -->
	<link href="dist/plugins/summernote/summernote-bs4.min.css" rel="stylesheet">
    <script src="dist/plugins/summernote/summernote-bs4.min.js"></script>
    <script src="dist/plugins/summernote/lang/summernote-es-ES.min.js"></script>
    <!-- Dropzone -->
    <link href="dist/plugins/dropzone/dropzone.css" rel="stylesheet" type="text/css">
    <script src="dist/plugins/dropzone/dropzone.js"></script>
    <!-- AutoNumeric -->
    <script src="dist/plugins/autonumeric/j_autoNumeric.min.js"></script>
    <script src="dist/plugins/autonumeric/j_numberMask.js"></script>
    <!-- FancyBox -->
    <link rel="stylesheet" href="dist/plugins/fancy/jquery.fancybox.min.css">
    <script src="dist/plugins/fancy/jquery.fancybox.min.js"></script>
    <!-- JQuery Validate -->
    <script src="dist/plugins/jquery-validate/jquery.validate.min.js"></script>
    <script src="dist/plugins/jquery-validate/localization/messages_es.js"></script>
    <!-- Mousetrap -->
    <script src="dist/plugins/mousetrap/mousetrap.min.js"></script>
    <script src="dist/plugins/mousetrap/plugins/global-bind/mousetrap-global-bind.js"></script>
    <script src="dist/plugins/mousetrap/plugins/pause/mousetrap-pause.js"></script>
    <!-- daterangepicker -->
    <script type="text/javascript" src="dist/plugins/daterangepicker/moment.min.js"></script>
    <script type="text/javascript" src="dist/plugins/daterangepicker/daterangepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="dist/plugins/daterangepicker/daterangepicker.css" />
    <!-- Socket IO -->
    <script src="dist/js/socket.io.js"></script>
    <!-- LEAFLET -->    
    <link rel="stylesheet" href="dist/plugins/leaflet/css/leaflet.css"> 
    <!-- LEAFLET MAPA JS -->
    <script src="dist/plugins/leaflet/js/leaflet.js"></script>
	<!-- Custom -->
    <link href="dist/css/style.css?v=<?php echo $v; ?>" rel="stylesheet">
	<link href="dist/css/custom.css?v=<?php echo $v; ?>" rel="stylesheet">
	<script src="js/notificaciones.js?v=<?php echo $v; ?>"></script>
	<script src="js/custom.js?v=<?php echo $v; ?>"></script>
	<script src="js/bootstrapTableUtils.js?v=<?php echo $v; ?>"></script>
	<script src="dist/plugins/momentjs/moment.js?v=<?php echo $v; ?>"></script>
    <!-- Highcharts -->
    <script src="dist/plugins/highcharts/highcharts.js"></script>
    <script src="dist/plugins/highcharts/modules/series-label.js"></script>

    <script>
        const getUser = () => '<?php echo $usuario->username; ?>';
        const getIdRol = () => '<?php echo $usuario->id_rol; ?>';
        function esAdmin() {
            return getIdRol() == 1;
        }
        function esCajero(){
            return getIdRol() == 4;
        }
    </script>
    
</head>
