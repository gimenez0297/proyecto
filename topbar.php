<?php
	//Consulta está en header.php
	$nombre_usuario = $datos_empresa->nombre_apellido;
	$usu_foto = $datos_empresa->foto;
	$logo_horizontal = $datos_sistema->logo_horizontal;
	$logo_close = $datos_sistema->logo_close; 

	$nombre = cortar_titulo($nombre_usuario, 20);

?>
<header class="topbar">
	<nav id="nav_header" class="navbar top-navbar navbar-expand-md navbar-dark ">
		<!-- ============================================================== -->
		<!-- Logo -->
		<!-- ============================================================== -->
		<!--<div class="navbar-header">
			<a class="navbar-brand" href="index">
				<b><img src="<?php echo $logo_horizontal; ?>" alt="homepage" class="dark-logo" /></b>
				<span>
					<img src="<?php echo $logo_horizontal; ?>" alt="homepage" class="dark-logo" />
					<img src="dist/images/logo-light-text.png" class="light-logo" alt="homepage" />
				</span> 
			</a>
		</div>-->
		<div class="navbar-collapse alinear-objetos">
			<ul class="navbar-nav margin-r">
				<li class="nav-item"> <a class="nav-link nav-toggler waves-effect waves-light" href="javascript:void(0)"><i class="fas fa-bars d-none" id="open"></i><i class="ti-close" id="close"></i></a></li>
				<li class="nav-item">
					<span class="titulo line-center"><?php echo $titulo; ?></span>
					
				</li>
			</ul>
			<?php echo $menu_central; ?>
			<ul class="navbar-nav my-lg-0">
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="<?php echo $usu_foto ?>" alt="user" class="img-circle" width="30"></a>
					<div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
						<span class="with-arrow"><span class="bg-primary"></span></span>
						<div class="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
							<div class=""><img src="<?php echo $usu_foto ?>" alt="user" class="img-circle" width="60"></div>
							<div class="m-l-10">
								<h4 class="m-b-0"><?php echo $nombre.$intro; ?></h4>
							</div>
						</div>
						<a class="dropdown-item text-danger" href="./logout"><i class="fa fa-power-off m-r-5 m-l-5"></i> Cerrar Sesión</a>
					</div>
				</li>
			</ul>
		</div>
	</nav>
</header>
