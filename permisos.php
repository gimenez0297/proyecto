<?php include 'header.php'; ?>

<body class="<?php include 'menu-class.php';?> fixed-layout">
    <?php include "preloader.php"; ?>
    <div id="main-wrapper">
        <?php include 'topbar.php'; include 'leftbar.php' ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <div id="toolbar">
                            <div class="form-inline" role="form">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" id="agregar" data-toggle="modal" data-target="#modal">Agregar<sup class="ml-1">[F1]</sup></button>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>
						
                        <!-- MODA PRINCIPAL -->
                        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" data-backdrop="static" aria-hidden="true">
                            <div class="modal-dialog modal-md2 modal-dialog-centered modal-md" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="id_permiso" id="id_permiso">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="concepto" class="label-required">Concepto</label>
                                                            <input class="form-control input-sm upper" type="text" name="concepto" id="concepto" autocomplete="off" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="cantidad" class="label-required">Cantidad</label>
                                                            <input class="form-control input-sm text-right" type="text" name="cantidad" id="cantidad" autocomplete="off" onkeypress="return soloNumeros(event)">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="unidad" class="label-required">Unidad</label>
                                                            <select id="unidad" name="unidad" class="form-control">
                                                                <option value='0'>DIAS</option>
                                                                <option value='1'>HORAS</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="periodo" class="label-required">Periodo</label>
                                                            <select id="periodo" name="periodo" class="form-control">
                                                                <option value='AÑO'>AÑO</option>
                                                                <option value='MES'>MES</option>
                                                                <option value='SEMANA'>SEMANA</option>
                                                                <option value='DIA'>DIA</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="relacionado" class="label-required">Relacionado a</label>
                                                            <select id="relacionado_a" name="relacionado_a" class="form-control">
                                                                <option value='1'>Entrada</option>
                                                                <option value='2'>Salida</option>
                                                                <option value='3'>Intermedia</option>
                                                                <option value='4'>Sin Marcación</option>
                                                                <option value='5'>Vacaciones</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="goce_sueldo" class="label-required">Goce de Sueldo</label>
                                                            <select id="goce_sueldo" name="goce_sueldo" class="form-control">
                                                                <option value='0'>SI</option>
                                                                <option value='1'>NO</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="autenticada" class="label-required">Copia Autenticada / Visación</label>
                                                            <select id="autenticada" name="autenticada" class="form-control">
                                                                <option value='0'>SI</option>
                                                                <option value='1'>NO</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 form-group">
                                                    <label for="validez" class="label-required">Validéz</label>
                                                    <div class="input-group-prepend">
                                                        <input class="form-control input-sm text-right" type="text" name="validez" id="validez" autocomplete="off" value="" onkeyup="separadorMilesOnKey(event,this)" required>
                                                        <span class="input-group-text">Hs.</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-8 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="documentos">Documentos Requeridos</label>
                                                            <select id="documentos" name="documentos[]" class="form-control" multiple="">
                                                                <option value='1'>Certificado Médico</option>
                                                                <option value='2'>Constancia de Estudio</option>
                                                                <option value='3'>Constancia Médica</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-sm-12">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12 col-sm-12">
                                                            <label for="obs">Observación</label>
                                                            <textarea class="form-control input-sm upper" id="obs" name="obs" type="text" autocomplete="off" rows="3"></textarea>
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

                    </div>
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
