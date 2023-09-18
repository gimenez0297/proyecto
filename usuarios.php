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
									<button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal_principal">Agregar<sup class="ml-1">[F1]</sup></button>
								</div>
							</div>
						</div>
						<table id="tabla" data-url="inc/usuarios-data.php?q=ver" data-toolbar="#toolbar" data-show-export="true" data-search="true" data-show-refresh="true" data-show-toggle="true" data-show-columns="true" data-search-align="right" data-buttons-align="right" data-toolbar-align="left" data-classes="table table-hover table-condensed" data-striped="true" data-icons="icons" data-show-fullscreen="true"></table>
						
						<!-- MODA PRINCIPAL -->
						<div class="modal" id="modal_principal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
							<div class="modal-dialog modal-md modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="modalLabel"></h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
										<input type="hidden" name="hidden_id_usuario" id="hidden_id_usuario">
										<div class="modal-body">
											<div class="row">
												<div class="col-md-12 col-sm-12">
													<div class="form-row">
														<div class="form-group col-md-3 col-sm-12">
															<label for="ci" class="label-required">Cédula de Identidad</label>
															<div class="input-group">
															<input class="form-control input-sm" type="text" name="ci" id="ci" autocomplete="off" required>
															
																<div class="input-group-append">
                                                                    <button class="btn btn-success" type="button" id="buscar_ci" title="Buscar"><i id="spin_sea" class="fas fa-search"></i></button>
                                                                </div>
															</div>
														</div>
														<div class="form-group col-md-9 col-sm-12">
															<label for="nombre" class="label-required">Nombre y Apellido</label>
															<input class="form-control input-sm" type="text" name="nombre" id="nombre" autocomplete="off" required>
														</div>
														
														<div class="form-group col-md-3 col-sm-12">
															<label for="telefono">Teléfono / Celular</label>
															<input class="form-control input-sm" id="telefono" name="telefono" type="text" autocomplete="off">
														</div>
														<div class="form-group col-md-5 col-sm-12">
															<label for="direccion">Dirección</label>
															<input class="form-control input-sm" id="direccion" name="direccion" type="text" autocomplete="off">
														</div>
														<div class="form-group col-md-4 col-sm-12">
															<label for="email">E-mail</label>
															<input class="form-control input-sm" id="email" name="email" type="email" autocomplete="off">
														</div>
													</div>
													<div class="form-row">
														<div class="form-group col-md-4 col-sm-12">
															<label for="dpto">Departamento/Área</label>
															<select id="dpto" name="dpto" class="form-control">
																<option value="">&nbsp</option>
																<option value="Sistemas">Sistemas</option>
																<option value="Gerencia">Gerencia</option>
																<option value="Ventas">Ventas</option>
																<option value="Operaciones">Operaciones</option>
																<option value="Contabilidad">Contabilidad</option>
															</select>
														</div>
														<div class="form-group col-md-4 col-sm-12">
															<label for="cargo">Cargo</label>
															<input class="form-control input-sm" id="cargo" name="cargo" type="text">
														</div>
														<div class="form-group col-md-4 col-sm-12">
															<label for="sucursal" class="label-required">Sucursal</label>
															<select id="sucursal" name="sucursal" class="form-control" required></select>
														</div>
													</div>
													<div class="form-row">
														<div class="form-group col-md-4 col-sm-12">
															<label for="rol" class="label-required">Rol del Sistema</label>
															<select id="rol" name="rol" class="form-control" required></select>
															<!--<select id="rol" name="rol[]" class="form-control input-sm selectpicker show-tick" data-actions-box="false" data-style="form-control input-sm" data-size="5" style="padding: 0;" multiple required>
															</select>-->
														</div>
														<div class="form-group col-md-4 col-sm-12">
															<label for="usuario" class="label-required">Nombre de Usuario</label>
															<input class="form-control input-sm" id="usuario" name="usuario" type="text" autocomplete="off" required>
														</div>
														<div id="passConten" class="form-group col-md-4 col-sm-12">
															<label for="password" class="label-required">Contraseña</label>
															<input class="form-control input-sm" id="password" name="password" type="text" autocomplete="off" required>
														</div>
														<div id="estado_cont" class="form-group col-md-4 col-sm-12">
															<label for="estado">Estado</label>
															<select id="estado" name="estado" class="form-control">
																<option value="0">Activo</option>
																<option value="3">Bloqueado</option>
																<option value="4">Contraseña Expirada</option>
															</select>
														</div>

                                                        <div class="form-group col-md-4 col-sm-12">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="ver_notificaciones" name="ver_notificaciones" value="1" checked>
                                                                <label class="custom-control-label" for="ver_notificaciones">Notificaciones</label>
                                                            </div>
                                                        </div>
														<div id="contenC" class="form-group col-md-4 col-sm-12">
															<div class="custom-control custom-checkbox">
															  <input type="checkbox" class="custom-control-input" name="expira" id="expira" checked>
															  <label class="custom-control-label" for="expira">Cambiar contraseña en el primer inicio</label>
															</div>
														</div>
													</div>
												</div>
											</div>
										
										</div>
										<div class="modal-footer">
											<button id="restablecer" type="button" class="btn btn-warning mr-auto" style="display:none">Restablecer Contraseña</button>
											<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
											<button type="submit" class="btn btn-success">Guardar</button>
										</div>
									</form>
								</div>
							</div>
						</div>
						
					</div><!-- End col-12 -->
                </div><!-- End Page Content -->
            </div><!-- End Container fluid  -->
        </div><!-- End Page wrapper  -->
       <?php include 'footer.php'; ?>
    </div><!-- End Wrapper -->
    <script src="<?php echo $js_pagina; ?>"></script>
</body>
</html>
