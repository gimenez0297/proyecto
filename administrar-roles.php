<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include 'preloader.php'; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <?php //include 'titulo.php'; ?>
                <div class="row">
                    <div class="col-12">
						<div id="toolbar">
							<div class="form-inline" role="form">
								<div class="form-group">
									<?php if ($permisos->insertar) { ?><button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal_principal">Agregar Rol</button><?php } ?>
								</div>
							</div>
						</div>
						<table id="tabla" data-url="inc/administrar-roles-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-pagination="true" data-side-pagination="server" data-classes="table table-hover table-condensed" data-striped="true" data-icons="icons" data-show-fullscreen="true"></table>
						
						<!-- MODA PRINCIPAL -->
						<div class="modal fade" id="modal_principal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
							<div class="modal-dialog modal-md modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="modalLabel"></h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
										<input type="hidden" name="hidden_id_rol" id="hidden_id_rol">
										<div class="modal-body">
											<div class="row">
												<div class="col-md-12 col-sm-12">
													<div class="form-row">
														<div class="form-group col-md-8 col-sm-8">
															<label for="rol" class="label-required">Rol</label>
															<input class="form-control input-sm" type="text" name="rol" id="rol" autocomplete="off" required="">
														</div>
														<div class="form-group col-md-4 col-sm-4">
															<label for="estado" class="label-required">Estado</label>
															<select id="estado" name="estado" class="form-control">
                                                                <option value="Activo">Activo</option>
                                                                <option value="Inactivo">Inactivo</option>
                                                            </select>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button id="eliminar" type="button" class="btn btn-danger mr-auto" style="display:none">Eliminar</button>		
											<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
											<button type="submit" class="btn btn-success">Guardar</button>
										</div>
									</form>
								</div>
							</div>
						</div>

						<!-- MODA PERMISOS -->
						<div class="modal fade" id="modal_permisos" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="modalLabelPermisos" aria-hidden="true">
							<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="modalLabelPermisos"></h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<input type="hidden" name="permisos_id_rol" id="permisos_id_rol">
									<div class="modal-body">
										<div class="row">
											<div class="col-md-12 col-sm-12">
												<div id="toolbarPermisos">
													<div class="form-inline" role="form">
														<div class="form-group">
															<h4 id="permisos_rol"></h4>
														</div>
													</div>
												</div>
                                                <table id="tabla_permisos" data-url="" data-toolbar="#toolbarPermisos" data-show-export="false" data-search="false" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-classes="table table-hover table-condensed" data-striped="true" data-icons="icons" data-show-fullscreen="true"></table>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
										<button type="button" class="btn btn-success" id="btn-editar-permisos">Guardar</button>
									</div>
								</div>
							</div>
						</div>
						
					</div><!-- End col-12 -->
                </div><!-- End Page Content -->
            </div><!-- End Container fluid  -->
        </div><!-- End Page wrapper  -->
       <?php include 'footer.php'; ?>
    </div><!-- End Wrapper -->
    <script type="text/javascript">

    </script>
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
