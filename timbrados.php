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
                            <div class='form-inline' role='form'>
                                <div class=''>
                                    <button type='button' class='btn btn-primary' id='agregar' data-toggle='modal'
                                        data-target='#modal_principal'>Agregar<sup class="ml-1">[F1]</sup></button>
                                </div>
                            </div>
                        </div>
                        <table id="tabla"></table>

                        <!-- MODA PRINCIPAL -->
                        <div id='zoom_modal'></div>
                        <div class="modal fade" id="modal_principal" tabindex="-1" role="dialog"
                            aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel"></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="form default-validation" id="formulario" method="post" enctype="multipart/form-data" action="">
                                        <input type="hidden" name="hidden_id" id="hidden_id">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="tipo_documento" class="label-required">Documento</label>
                                                    <select id="tipo_documento" name="tipo_documento" class="form-control" required>
                                                        <option value='0'>FACTURA</option>
                                                        <option value='1'>NOTA DE CREDITO</option>
                                                        <option value='2'>NOTA DE REMISION</option>
                                                    </select>                                                    
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="id_sucursal" class="label-required">Sucursal</label>
                                                    <select id="id_sucursal" name="id_sucursal" class="form-control" required></select>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 ">
                                                    <label for="id_caja" class="label-required">Cajas</label>
                                                    <select id="id_caja" name="id_caja" class="form-control" required></select>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="ruc" class="label-required">R.U.C.</label>
                                                    <input class="form-control input-sm" type="text" name="ruc" id="ruc" autocomplete="off" required> 
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="timbrado" class="label-required">Timbrado</label>
                                                    <input class="form-control input-sm" 
                                                        type="text" 
                                                        name="timbrado" 
                                                        id="timbrado" 
                                                        onkeypress="return soloNumeros(event);"
                                                        required>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="establecimiento" class="label-required">Cód. Establecimiento</label>
                                                    <input class="form-control input-sm text-left" type="text" name="establecimiento" 
                                                    placeholder = "001" id="establecimiento"
                                                     maxlength="3" 
                                                     onkeypress="return soloNumeros(event);" 
                                                     autocomplete="off" 
                                                     required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="expedicion" class="label-required">Punto de Expedición</label>
                                                    <input class="form-control input-sm text-left" type="text" name="expedicion" placeholder = "002" id="expedicion" maxlength="3" onkeypress="return soloNumeros(event);" required>
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="fecha_inicio" class="label-required">Inicio Vigencia</label>
                                                    <input class="form-control input-sm center" id="fecha_inicio"
                                                     name="fecha_inicio" type="date" autocomplete="off" value = '' required>                                               
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="fecha_fin" class="label-required">Fin Vigencia</label>
                                                    <input class="form-control input-sm center" id="fecha_fin"
                                                     name="fecha_fin" type="date" autocomplete="off" value = '' required>                                                  
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="desde" class="label-required">Desde</label>
                                                    <input class="form-control input-sm text-left" type="text" name="desde" id="desde" 
                                                        maxlength="7" onkeypress="return soloNumeros(event);" placeholder = "0000001" autocomplete="off" required>  
                                                </div>
                                                <div class="form-group col-md-2 col-sm-6 required">
                                                    <label for="hasta" class="label-required">Hasta</label>
                                                    <input class="form-control input-sm text-left" type="text" name="hasta" id="hasta" 
                                                        maxlength="7" onkeypress="return soloNumeros(event);" placeholder = "0000009" autocomplete="off" required> 
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-12 col-sm-6">
                                                    <label for="membrete">Membrete</label>
                                                    <textarea class="form-control" id="membrete" name="membrete" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">	
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
