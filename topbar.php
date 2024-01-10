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
                    <a id="btn-notificaciones" class="nav-link dropdown-toggle text-dark waves-effect waves-dark" href="#" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bell" aria-hidden="true" style="color: #767575;"></i>
                        <span class="badge badge-pill badge-danger d-none"></span></b>
                    </a>
					<div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
						<span class="with-arrow"><span class="bg-primary"></span></span>
						<div class="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
							<span class="h3 m-b-0"><i class="fa fa-bell" aria-hidden="true"></i></span>
							<div class="m-l-10">
								<h4 id="noti" class="m-b-0">Notificaciones</h4>
							</div>
						</div>
						<div id="notificaciones" style="font-size: 12px;">
                            <table id="tabla_notificaciones"></table>
                            <template id="templateNotificacion">
                                <a href="#" class="card card-notificacion mb-0 w-100 text-dark" title="%TITLE_DESCRIPCION%">
                                    <div class="card-body card-body-notificacion px-3 py-0">
                                        <h6 class="card-title text-truncate mb-0 pr-5" title="%TITLE_TITULO%"><span class="text-danger mr-1 h3 %IS_NEW%">&#8226;</span>%TITULO%</h6>
                                        <small class="card-subtitle mb-1 text-muted">%FECHA%</small>
                                        <p class="card-text text-truncate">%DESCRIPCION%</p>
                                        <button class="btn btn-sm btn-outline-primary card-button-notificacion %IS_NEW%" value="%ID%" title="Marcar como leído"><i class="fas fa-check"></i></button>
                                    </div>
                                </a>
                            </template>
						</div>
					</div>
				</li>

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
