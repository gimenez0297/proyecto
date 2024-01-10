var url = 'inc/productos-data';
var url_listados = 'inc/listados';

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which){
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
        // F2 Agregar Proveedor
        case 113:
            event.preventDefault();
            $('#agregar-proveedor').click();
            break;
            
        // F3 Exportar todos los productos a excel
        case 114:
            event.preventDefault();
            if ($('#exportar').attr('disabled') !== 'disabled' && $('#exportar').attr('disabled') !== undefined) {
                $('#exportar').click();
            }
            break;
    }
});

// Modal
var MousetrapModal = new Mousetrap(document.querySelector('#tab-cuatro'));
MousetrapTableNavigationCell('#tabla_cuatro', MousetrapModal);

$('.modal').on('shown.bs.modal', (e) => Mousetrap.pause());
$('.modal').on('hidden.bs.modal', (e) => Mousetrap.unpause());

$('#cantidad_fracciones').on('keyup change', function (e) {
    let $element = $('#precio_fraccionado');
    let cantidad_fracciones = quitaSeparadorMiles($('#cantidad_fracciones').val());
    let precio = quitaSeparadorMiles($('#precio').val());
    let precio_fraccionado = Math.ceil(precio / cantidad_fracciones);

    if (precio > 0 && cantidad_fracciones > 0) {
        $element.val(separadorMiles(precio_fraccionado));
    } else if (cantidad_fracciones == 0) {
        $element.val('');
    }
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir" title="Imprimir"><i class="fa fa-barcode"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 1 ? 0 : 1);
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id_producto: row.id_producto, estado },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function(jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    },
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    },
    'click .imprimir': function(e, value, row, index) {
        $('#modal_imprimir').modal('show');
        public_id_produto = row.id_producto;
        $('#cantidad').val(1);
        $("#id_lote").val(null).trigger('change');
    }
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    showColumns: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "codigo",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'laboratorio', title: 'Laboratorio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true },
            { field: 'origen', title: 'Origen', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'moneda', title: 'Moneda', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'principio', title: 'Principio', align: 'left', valign: 'middle', sortable: true, visible: false },
            //{ field: 'clasificacion', title: 'Clasificación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'unidad_medida', title: 'Medida', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'rubro', title: 'Rubro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'pais', title: 'País', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'conservacion_str', title: 'Conservacion', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'iva_str', title: 'Iva', align: 'left', valign: 'middle', sortable: true, visible: true},
            { field: 'indicaciones', title: 'Indicaciones', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'precio', title: 'Precio', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'usuario_modifica', title: 'Última Modificación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila, width: 120 },
            { field: 'destacar', title: 'Producto Destacado', align: 'left', valign: 'middle', sortable: false, visible: false, switchable: false }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});


$('#formulario_imprimir').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    $('#modal_imprimir').modal('hide');
    var param = {   'id_producto': public_id_produto,
                    'id_lote': $('#id_lote').val(), 
                    cantidad: $('#cantidad').val(), 
                    'imprimir': 'si', 
                    'recargar': 'no' };
    OpenWindowWithPost("imprimir-etiqueta", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=500,height=400", "imprimirEtiqueta", param);
});


// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: $(window).height()-120,
                pageSize: Math.floor(($(window).height()-120)/50),
            });
        }, 100);
    });

    $.fn.modal.Constructor.prototype._enforceFocus = function() {};
    // Configuración dropzone
    Dropzone.autoDiscover = false;
    Dropzone.prototype.defaultOptions.dictFileTooBig = "Archivo muy pesado ({{filesize}}MB). Tamaño Maximo: {{maxFilesize}}MB.";
    Dropzone.prototype.defaultOptions.dictRemoveFile = "Remover Archivo";
    Dropzone.prototype.defaultOptions.dictRemoveFileConfirmation = "¿Remover Archivo?";   
    Dropzone.prototype.defaultOptions.dictCancelUpload = "Cancelar Proceso";
    Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "No puedes subir más archivos.";
    Dropzone.prototype.defaultOptions.dictResponseError = "Servidor no responde.";
    Dropzone.confirm = function(question, accepted, rejected) {
        sweetAlertConfirm({ title: `Eliminar`, text: question, confirm: accepted });
    };

    $('.pop-export').popover({
        title: 'Atención',
        content: `<ul class="pl-2">
                    <li>No cierre la ventana al iniciar la exportación</li>
                </ul>`,
        html: true,
        trigger: 'hover'
});

    // GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
    $("#formulario").validate({ ignore: '' });
    $("#formulario").submit(function(e) {
        e.preventDefault();
        if ($(this).valid() === false) return false;

        if ($("#codigo").val() == "") {
            sweetAlertConfirm({
                title: `Atención`,
                text: 'El sistema creará un código aleatorio para este producto.',
                closeOnConfirm: true,
                confirm : function(){
                    if (myDropzone.getQueuedFiles().length === 0) {
                        var blob = new Blob();
                        blob.upload = { 'chunked': myDropzone.defaultOptions.chunking };
                        myDropzone.uploadFile(blob);
                    } else {
                        myDropzone.processQueue();
                    }
                }
            });
        }else{
            if (myDropzone.getQueuedFiles().length === 0) {
                var blob = new Blob();
                blob.upload = { 'chunked': myDropzone.defaultOptions.chunking };
                myDropzone.uploadFile(blob);
            } else {
                myDropzone.processQueue();
            }
        }
    });

});

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Producto');
    $("#dropurl").val('cargar');
    $('#eliminar').hide();
    $('#cantidad_fracciones').prop('disabled',true);
    $('#precio_fraccionado').prop('disabled',true);
    $('label[for=cantidad_fracciones]').removeClass('label-required');
    $('label[for=precio_fraccionado]').removeClass('label-required');
    $('label[for=copete]').removeClass('label-required');
    $('label[for=etiquetas]').removeClass('label-required');
    $("#etiquetas").val(null).trigger('change');
    $('#copete').val(''); 

    resetForm('#formulario');

    // Tab cuatro
    $('#tabla_cuatro').bootstrapTable('refresh', { url: url+'?q=ver_niveles_stock' });
});

$('#modal').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    $(form).find('checkbox').prop('checked', '').trigger('change');
    $(form).find('.summernote').summernote('reset');

    resetValidateForm(form);

    // Default Paraguay
    $("#procedencia").append(new Option('PARAGUAY', 172, true, true)).trigger('change');

    $('#tabProductos a[href="#datos-basicos"]').tab('show');
    $("#contenDrop").load("./dropzone-productos.html?v="+getRandomInt(1, 9999));
    $('#tabla_proveedores').bootstrapTable('removeAll');
    $('#tabla_cuatro').bootstrapTable('removeAll');
    $('#tabla_principios').bootstrapTable('removeAll');
    $('#productos_clasificaciones').bootstrapTable('removeAll'); //Limpia lo que se guardo en clasificacion para cargar otro

    setTimeout(function() {
            $("#codigo").focus();
        }, 200);
}



// ELIMINAR
$('#eliminar').click(function() {
    var id = $("#id_producto").val();
    var nombre = $("#producto").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Producto: ${nombre}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, nombre },	
                beforeSend: function() {
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal').modal('hide');
                        $('#tabla').bootstrapTable('refresh');
                    }
                },
                error: function (jqXhr) {
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });

});

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Producto');
    $('#eliminar').show();
    $("#dropurl").val('editar');
    $("#id_producto").val(row.id_producto);

    // Tab datos basicos
    $("#producto").val(row.producto);
    $("#codigo").val(row.codigo);
    $("#precio").val(separadorMiles(row.precio));
    $("#precio_fraccionado").val(separadorMiles(row.precio_fraccionado));
    $("#cantidad_fracciones").val(separadorMiles(row.cantidad_fracciones));
    $("#comision").val(separadorMiles(row.comision));
    $("#comision_concepto").val(row.comision_concepto)
    

    // Select2
    if (row.id_rubro) {
        $("#rubro").append(new Option(row.rubro, row.id_rubro, true, true)).trigger('change');
    }
    if (parseInt(row.id_marca) > 0) {
        $("#marca").append(new Option(row.marca, row.id_marca, true, true)).trigger('change');
    }
    if (parseInt(row.id_laboratorio) > 0) {
        $("#laboratorio").append(new Option(row.laboratorio, row.id_laboratorio, true, true)).trigger('change');
    }
    if (row.id_pais) {
        $("#procedencia").append(new Option(row.pais, row.id_pais, true, true)).trigger('change');
    }
    if (row.id_presentacion) {
        $("#presentacion").append(new Option(row.presentacion, row.id_presentacion, true, true)).trigger('change');
    }
    if (row.id_principio) {
        $("#principio_activo").append(new Option(row.principio, row.id_principio, true, true)).trigger('change');
    }
    if (row.id_origen) {
        $("#origen").append(new Option(row.origen, row.id_origen, true, true)).trigger('change');
    }
    if (row.id_tipo_producto) {
        $('#tipo').select2('trigger', 'select', {
            data: { id: row.id_tipo_producto, text: row.tipo, principios_activos: row.principios_activos }
        });
    }
    if (row.id_clasificacion_producto) {
        $("#clasificacion").append(new Option(row.clasificacion, row.id_clasificacion_producto, true, true)).trigger('change');
    }
    if (row.id_unidad_medida) {
        $("#unidad_medida").append(new Option(row.unidad_medida, row.id_unidad_medida, true, true)).trigger('change');
    }
    $("#conservacion").val(row.conservacion).trigger('change');
    $("#iva").val(row.iva).trigger('change');

    $("#indicaciones").val(row.indicaciones);
    $("#observaciones").val(row.observaciones);
    if (row.controlado == 1) {
        $("#controlado").prop('checked', true).trigger('change');
    }
    if (row.descuento_fraccionado == 1) {
        $("#descuento_fraccionado").prop('checked', true).trigger('change');
    }
    if (row.fuera_de_plaza == 1) {
        $("#fuera_de_plaza").prop('checked', true).trigger('change');
    }
     if (row.fraccion == 1) {
        $("#fraccionado").prop('checked', true);
        $("#cantidad_fracciones").prop('disabled', false);
        $("#precio_fraccionado").prop('disabled', false);
        $('label[for=cantidad_fracciones]').addClass('label-required');
        $('label[for=precio_fraccionado]').addClass('label-required');
    }else{
        $("#cantidad_fracciones").prop('disabled', true);
        $("#precio_fraccionado").prop('disabled', true);
        $('label[for=cantidad_fracciones]').removeClass('label-required');
        $('label[for=precio_fraccionado]').removeClass('label-required');
    }


    // Tab imagenes y descripción
    $('#descripcion').summernote('code', row.descripcion);
    // Buscar fotos
    $.ajax({
        url: url,
        type: 'POST',
        data: { q: 'leer_fotos', id_producto: row.id_producto },
        dataType: 'json',
        success: function(response) {
            $.each(response, function(key, value) {
                if (value.name != "") {
                    var mockFile = { name: value.name, size: value.size };
                    myDropzone.emit('addedfile', mockFile);
                    myDropzone.emit('thumbnail', mockFile, value.path);
                    myDropzone.emit('complete', mockFile);
                    myDropzone.files.push(mockFile);
                }
            });
        }
    });

    // Tab principios
    $('#tabla_principios').bootstrapTable('refresh', { url: url+'?q=ver_principios&id_producto='+row.id_producto });

    // Tab Clasificacion
    $('#productos_clasificaciones').bootstrapTable('refresh', { url: url+'?q=ver_clasificacion&id_producto='+row.id_producto });

    // Tab proveedores
    $('#tabla_proveedores').bootstrapTable('refresh', { url: url+'?q=ver_proveedores&id_producto='+row.id_producto });

    // Tab cuatro
    $('#tabla_cuatro').bootstrapTable('refresh', { url: url+'?q=ver_niveles_stock&id='+row.id_producto });

    // Tab SEO
    $("#copete").val(row.copete);

    if (row.web == 1) {
        $("#web").prop('checked', true).trigger('change');
        $("#copete").prop('disabled', false);
        $("#etiquetas").prop('disabled', false);
        $('label[for=copete]').addClass('label-required');
        $('label[for=etiquetas]').addClass('label-required');
    }else{
        $("#copete").prop('disabled', true);
        $("#etiquetas").prop('disabled', true);
        $('label[for=copete]').removeClass('label-required');
        $('label[for=etiquetas]').removeClass('label-required');
    }
    if (row.destacar == 1) {
        $("#destacar").prop('checked', true).trigger('change');
    }
    // Buscar etiquetas
    $.ajax({
        url: url,
        type: 'POST',
        data: { q: 'ver_etiquetas', id_producto: row.id_producto },
        dataType: 'json',
        success: function(data) {
            if (data) {
                $.each(data, function(key, value) {
                    $("#etiquetas").append(new Option(value.etiqueta, value.etiqueta, true, true));
                });
                $("#etiquetas").trigger('change');
            }
        }
    });

    $('#modal').modal('show');
}


// Principios activos
function iconosFilaPrincipios(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaPrincipio = {
    'click .eliminar': function (e, value, row, index) {
        $('#tabla_principios').bootstrapTable('removeByUniqueId', row.id_principio);
    }
}

$("#tabla_principios").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 415,
    sortName: "principio",
    sortOrder: 'asc',
    uniqueId: 'id_principio',
    columns: [
        [
            { field: 'id_principio', title: 'ID Principio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'principio', title: 'Principio Activo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaPrincipio,  formatter: iconosFilaPrincipios, width: 100 }
        ]
    ]
});


// Clasificacion
function iconosFilaClasificacion(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaClasificacion = {
    'click .eliminar': function (e, value, row, index) {
        $('#productos_clasificaciones').bootstrapTable('removeByUniqueId', row.id_clasificacion);
    }
}

$("#productos_clasificaciones").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 415,
    sortName: "Clasificación",
    sortOrder: 'asc',
    uniqueId: 'id_clasificacion',
    columns: [
        [
            { field: 'id_clasificacion', title: 'ID Clasificacion', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'clasificacion', title: 'Clasificación', align: 'left', valign: 'middle', sortable: true },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaClasificacion,  formatter: iconosFilaClasificacion, width: 100 }
            
        ]
    ]
});

//VALIDACIONES PARA AGREGAR CLASIFICACION
$('#agregar-clasificacion').on('click', function(){
    var data = $('#clasificacion').select2('data')[0];
    var tableData = $('#productos_clasificaciones').bootstrapTable('getData');

    if (!data){
        alertDismissJS('Debe seleccionar una clasificacion','error');
        return;
    }
    if (tableData.find(value => value.id_clasificacion == data.id)) {
        alertDismissJS(`La Clasificacion ${data.text} ya fue agregado`, 'error');
        return;
    }

    $('#productos_clasificaciones').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id_clasificacion: data.id,
            clasificacion: data.text,
        }
    });

    $('#clasificacion').val(null).trigger('change');
});


//VALIDACIONES PARA AGREGAR PRINCIPIO ACTIVO
$('#agregar-principio').on('click', function() {
    var data = $('#principio').select2('data')[0];
    var tableData = $('#tabla_principios').bootstrapTable('getData');

    if (!data) {
        alertDismissJS('Debe seleccionar un principio activo', 'error');
        return;
    }

    if (tableData.find(value => value.id_principio == data.id)) {
        alertDismissJS(`El principio activo ${data.text} ya fue agregado`, 'error');
        return;
    }

    $('#tabla_principios').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id_principio: data.id,
            principio: data.text,
        }
    });

    $('#principio').val(null).trigger('change');
    
});

async function exportt() {
    return new Promise(resolve => {
      const ventanaDescarga = window.open('exportar-productos.php',  '_blank');
      ventanaDescarga.document.title = "Creando archivo...";
      const temporizador = setInterval(() => {
        if (ventanaDescarga.closed) {
          clearInterval(temporizador);
          resolve(true);
        }
      }, 1000); // Comprueba si la ventana se ha cerrado cada segundo
    });
  }
  
  $('#exportar').on('click', async function(e) {
    e.preventDefault();
    $(this).prepend(`
        <span class="spin-export" style="width:100%;">
            <span class="loader__figure"></span>
        </span>
    `);
    $(this).prop('disabled', true).removeAttr('style').prop('style', 'display: grid; grid-template-columns:1fr 3fr 1fr;');

    const resultado = await exportt();

    $(this).removeAttr('disabled').removeAttr('style').prop('style', 'display: grid; grid-template-columns: 2fr 1fr;');;
    $('.spin-export').remove();
    alertToast('Exportado Correctamente', 'ok', 3000);

  });

// Proveedores
function iconosFilaProveedor(value, row, index) {
    var disabled = row.proveedor_principal == 1 ? "disabled" : "";
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-proveedor-principal mr-1" title="Cambiar Proveedor Principal" ${disabled}><i class="fas fa-sync-alt"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar-proveedor" title="Eliminar" ${disabled}><i class="fas fa-trash"></i></button>`,
    ].join('');
}

window.accionesFilaProveedor = {
    'click .cambiar-proveedor-principal': function(e, value, row, index) {
        sweetAlertConfirm({
            title: `Cambiar Principal`,
            text: `¿Esta seguro de cambiar el estado principal del proveedor seleccionado?`,
            closeOnConfirm: true,
            confirm: function() {
                let proveedores = $("#tabla_proveedores").bootstrapTable('getData');
                $.each(proveedores, function(i, val) {
                   $("#tabla_proveedores").bootstrapTable('updateByUniqueId', {
                        id: val.id_proveedor,
                        row: {
                            proveedor_principal: 0,
                        }
                    }); 
                });
                $("#tabla_proveedores").bootstrapTable('updateByUniqueId', {
                    id: row.id_proveedor,
                    row: {
                        proveedor_principal: 1,
                    }
                });
            }
        });
    },
    'click .eliminar-proveedor': function (e, value, row, index) {
        sweetAlertConfirm({
            title: `Eliminar`,
            text: `¿Esta seguro de eliminar el proveedor seleccionado?`,
            closeOnConfirm: true,
            confirm: function() {
                $('#tabla_proveedores').bootstrapTable('removeByUniqueId', row.id_proveedor);
            }
        });
    }
}

$("#tabla_proveedores").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 415,
    sortName: "proveedor",
    sortOrder: 'asc',
    uniqueId: 'id_proveedor',
    columns: [
        [
            { field: 'id_proveedor', title: 'ID Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'proveedor', title: 'Proveedor', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'nombre_fantasia', title: 'Nombre De Fantasia', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'contacto', title: 'Contacto', align: 'left', valign: 'middle', sortable: true },
            { field: 'telefono', title: 'Teléfono', align: 'left', valign: 'middle', sortable: true },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'proveedor_principal', title: 'Principal', align: 'center', valign: 'middle', sortable: true, events: accionesFilaProveedor, formatter: principal },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProveedor,  formatter: iconosFilaProveedor, width: 100 }
        ]
    ]
});

function principal(data) {
    switch (parseInt(data)) {
        case 1: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-success" title="Proveedor Principal">Sí</span></b>';
        case 0: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-danger" title="No Proveedor Principal">No</span></b>';
    }
};

$('#agregar-proveedor').on('click', function() {
    var data = $('#proveedor').select2('data')[0];
    var tableData = $('#tabla_proveedores').bootstrapTable('getData');
    var codigo = $('#codigo_proveedor').val();
    var costo = parseInt(quitaSeparadorMiles($('#costo').val()) || 0);
    var principal = tableData.length == 0 ? 1: 0;

    if (!data) {
        alertDismissJS('Debe seleccionar un proveedor', 'error');
        return;
    }

    if (tableData.find(value => value.id_proveedor == data.id)) {
        alertDismissJS(`El proveedor ${data.text} ya fue agregado`, 'error');
        return;
    }

    $('#tabla_proveedores').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id_proveedor: data.id,
            ruc: data.ruc,
            proveedor: data.text,
            nombre_fantasia: data.nombre_fantasia,
            contacto: data.contacto,
            telefono: data.telefono,
            codigo,
            costo,
            proveedor_principal: principal,
        }
    });

    $('#proveedor').val(null).trigger('change');
    $('#costo').val(0);
    $('#codigo_proveedor').val('');
    
});

// Tab cuatro
$('#nav-cuatro').on('shown.bs.tab', function (event) {
    $($(this).attr('href')).focus();
})

$("#tabla_cuatro").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 500,
    sortName: "sucursal",
    sortOrder: 'asc',
    uniqueId: 'id_sucursal',
    columns: [
        [
            { field: 'id_sucursal', title: 'ID Sucursal', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'minimo', title: 'Mínimo', align: 'right', valign: 'middle', sortable: true, width: 150, editable: { type: 'text' }, formatter: separadorMiles },
            { field: 'maximo', title: 'Máximo', align: 'right', valign: 'middle', sortable: true, width: 150, editable: { type: 'text' }, formatter: separadorMiles },
        ]
    ]
});

$.extend($('#tabla_cuatro').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_cuatro').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};

    // Si la columna quedo en blanco
    if (!row[field]) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = separadorMiles(quitaSeparadorMiles(row[field]));
    }

    // Se actualizan los valores
    $('#tabla_cuatro').bootstrapTable('updateByUniqueId', { id: row.id_sucursal, row: update_row });
});

// Configuración summernote
$('.summernote').summernote({
    // placeholder: 'Descripción del Producto.',
    tabsize: 2,
    height: 410,
    lang: 'es-ES',
    toolbar: [
        ['style', ['bold', 'italic', 'underline', 'clear']],
        ['font', ['strikethrough', 'superscript', 'subscript']],
        ['fontsize', ['fontsize']],
        ['para', ['ul', 'ol', 'paragraph']],
    ]
});

// Configuración select2
$("#etiquetas").select2({
    tags: true,
    width: 'style',
    //tokenSeparators: [',', ' ']
});

$('#conservacion').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
});

$('#iva').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: 10,
});


$('#rubro').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'rubros', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_rubro, text: obj.rubro }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#marca').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'marcas', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_marca, text: obj.marca }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#laboratorio').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'laboratorios', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_laboratorio, text: obj.laboratorio }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#procedencia').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'paises', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_pais, text: obj.pais }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#presentacion').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'presentaciones', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_presentacion, text: obj.presentacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#principio').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'principios_activos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_principio, text: obj.principio }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#origen').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'origenes', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_origen, text: obj.origen }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#tipo').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'tipos_productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_tipo_producto, text: obj.tipo, principios_activos: obj.principios_activos }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
    }
}).on('change', function() {
    var data = $(this).select2('data')[0];
    if (data && data.principios_activos === '1') {
        $('#tabProductos a[href="#principios"]').show();
    } else {
        $('#tabProductos a[href="#principios"]').hide();
    }
});

$('#clasificacion').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'clasificaciones_productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_clasificacion_producto, text: obj.clasificacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#unidad_medida').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'unidades_medidas', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_unidad_medida, text: obj.unidad_medida }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#proveedor').select2({
    // dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'proveedores', term: params.term, page: params.page || 1, tipo_proveedor: 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_proveedor, text: obj.proveedor, ruc: obj.ruc, nombre_fantasia: obj.nombre_fantasia, contacto: obj.contacto, telefono: obj.telefono }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});



$('#fraccionado').on('change', async function () {

       if ($(this).is(':checked')) {

          sweetAlertConfirm({
        title: `Atención`,
        text: '¿Esta seguro de Fraccionar el produto?',
        closeOnConfirm: true,

       cancel: function (){
        $('#fraccionado').prop('checked', false);
        $("#cantidad_fracciones").prop('disabled', true);
        $("#precio_fraccionado").prop('disabled', true);
        $('label[for=cantidad_fracciones]').removeClass('label-required');
        $('label[for=precio_fraccionado]').removeClass('label-required');

        $('#cantidad_fracciones').val(''); // limpia el input al cargar un valor y no se guarda
        $('#cantidad_fracciones').trigger('change.cantidad_fracciones'); //

        $('#precio_fraccionado').val(''); //limpia el input al cargar un valor y no se guarda
        $('#precio_fraccionado').trigger('change.precio_fraccionado');             
        }
    });
        $("#cantidad_fracciones").prop('disabled', false);
        $("#precio_fraccionado").prop('disabled', false);
        $('label[for=cantidad_fracciones]').addClass('label-required');
        $('label[for=precio_fraccionado]').addClass('label-required');

        $('#cantidad_fracciones').val(''); //limpia el input al cargar un valor y no se guarda
        $('#cantidad_fracciones').trigger('change.cantidad_fracciones'); // 

        $('#precio_fraccionado').val(''); // limpia el input al cargar un valor y no se guarda
        $('#precio_fraccionado').trigger('change.precio_fraccionado'); // 
        
    } else {
        $("#cantidad_fracciones").prop('disabled', true);
        $("#precio_fraccionado").prop('disabled', true);
        $('label[for=cantidad_fracciones]').removeClass('label-required');
        $('label[for=precio_fraccionado]').removeClass('label-required');

        $('#cantidad_fracciones').val(''); // limpia el input al cargar un valor y no se guarda
        $('#cantidad_fracciones').trigger('change.cantidad_fracciones'); //

        $('#precio_fraccionado').val(''); //limpia el input al cargar un valor y no se guarda
        $('#precio_fraccionado').trigger('change.precio_fraccionado');
    
    }
});

$('#web').on('change', async function () {
    if ($(this).is(':checked')) {
        $("#copete").prop('disabled', false);
        $("#etiquetas").prop('disabled', false);
        $('label[for=copete]').addClass('label-required');
        $('label[for=etiquetas]').addClass('label-required');
        // $('#copete').val(''); 
    } else {
        $("#copete").prop('disabled', true);
        $("#etiquetas").prop('disabled', true);
        $('label[for=copete]').removeClass('label-required');
        $('label[for=etiquetas]').removeClass('label-required');
        $("#etiquetas").val(null).trigger('change');
        $('#copete').val(''); 
    }
});

// IMPRESION (lotes)

function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.vencimiento) {
        $result = $(`<div class="d-flex justify-content-between">
                        <div class="overflow-hidden text-truncate">
                            <span>${node.text}</span>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-success">${fechaLatina(node.vencimiento)}</span>
                        </div>
                    </div>`);
    }
    return $result;
};


$('#id_lote').select2({
    dropdownParent: $("#modal_imprimir"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'ver_lotes', id_producto: public_id_produto, term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_lote,
                        text: obj.lote,
                        vencimiento: obj.vencimiento,
                        stock: obj.stock,
                        fraccionado: obj.fraccionado,
                        cant: obj.cant,
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    },
    templateResult: formatResult,
    templateSelection: formatResult
})