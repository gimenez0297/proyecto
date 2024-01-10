var url = 'inc/administrar-comisiones-productos-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#filtros').click() });
Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#actualizar').click() });

var MousetrapTabla = new Mousetrap();
MousetrapTableNavigationCell('#tabla',MousetrapTabla,true);

var MousetrapModalFiltro = new Mousetrap();
MousetrapModalFiltro.bindGlobal('f1', (e) => { e.preventDefault(); $('#id_producto').focus(); });
MousetrapModalFiltro.bindGlobal('f2', (e) => { e.preventDefault(); $('#tipo').focus(); });
MousetrapModalFiltro.bindGlobal('f3', (e) => { e.preventDefault(); $('#rubro').focus(); });
MousetrapModalFiltro.bindGlobal('f4', (e) => { e.preventDefault(); $('#procedencia').focus(); });
MousetrapModalFiltro.bindGlobal('f5', (e) => { e.preventDefault(); $('#origen').focus(); });
MousetrapModalFiltro.bindGlobal('f6', (e) => { e.preventDefault(); $('#clasificacion').focus(); });
MousetrapModalFiltro.bindGlobal('f7', (e) => { e.preventDefault(); $('#presentacion').focus(); });
MousetrapModalFiltro.bindGlobal('f8', (e) => { e.preventDefault(); $('#unidad_medida').focus(); });
MousetrapModalFiltro.bindGlobal('f9', (e) => { e.preventDefault(); $('#marca').focus(); });
MousetrapModalFiltro.bindGlobal('f10', (e) => { e.preventDefault(); $('#laboratorio').focus(); });
MousetrapModalFiltro.bindGlobal('f11', (e) => { e.preventDefault(); $('#con_sin').focus(); });
MousetrapModalFiltro.bindGlobal('f12', (e) => { e.preventDefault(); $('#filtrar').click(); });
var MousetrapModalComision = new Mousetrap();
MousetrapModalComision.bindGlobal('f12', (e) => { e.preventDefault(); $('#actuliza').click(); });
$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: alturaTabla(),
                pageSize: pageSizeTabla(),
            });
        }, 100);
    });
    $('#con_sin').val(null).trigger('change');
    habilitarBoton();
});

$('#modal').on('show.bs.modal', function(e) {
    Mousetrap.pause();
    MousetrapTabla.pause();
    MousetrapModalFiltro.unpause();
    MousetrapModalComision.pause();
});

$('#modal').on('hide.bs.modal', function(e) {
    Mousetrap.unpause();
    MousetrapTabla.unpause();
    MousetrapModalFiltro.pause();
    MousetrapModalComision.pause();
    habilitarBoton();
});

$('#modal_comision').on('show.bs.modal', function(e) {
    MousetrapTabla.pause();
    MousetrapModalFiltro.pause();
    MousetrapModalComision.unpause();
});

$('#modal_comision').on('hide.bs.modal', function(e) {
    MousetrapTabla.unpause();
    MousetrapModalFiltro.pause();
    MousetrapModalComision.pause();
});

// BUSQUEDA DE PRODUCTOS
function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_presentacion) {
        $result = $(`<span>${node.text} <small>(${node.presentacion})</small></span>`);
    }
    return $result;
};
$('#id_producto').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_producto,
                        text: obj.producto,
                        codigo: obj.codigo,
                        id_presentacion: obj.id_presentacion,
                        presentacion: obj.presentacion
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    },
    templateResult: formatResult,
    templateSelection: formatResult
});

$('#tipo').select2({
    dropdownParent: $("#modal"),
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
});


$('#rubro').select2({
    dropdownParent: $("#modal"),
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

$('#procedencia').select2({
    dropdownParent: $("#modal"),
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

$('#origen').select2({
    dropdownParent: $("#modal"),
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

$('#clasificacion').select2({
    dropdownParent: $("#modal"),
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

$('#presentacion').select2({
    dropdownParent: $("#modal"),
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

$('#unidad_medida').select2({
    dropdownParent: $("#modal"),
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

$('#marca').select2({
    dropdownParent: $("#modal"),
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
    dropdownParent: $("#modal"),
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

$('#con_sin').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
});

// METODOS PAGOS
$(document).ready(function () {
    $.ajax({
        dataType: 'json',
        async: true,
        cache: false,
        url: url_listados,
        type: 'POST',
        data: { q: 'ver_metodos_pagos'},
        beforeSend: function() {
            NProgress.start();
        },
        success: function (json){
            NProgress.done();
            let cols = [
                [
                    { title: 'Producto', align: 'center', valign: 'middle', sortable: true, colspan: 20 },
                ],
                [
                    { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
                    { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
                    { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
                    { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'laboratorio', title: 'Laboratorio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false},
                    { field: 'origen', title: 'Origen', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'moneda', title: 'Moneda', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
                    { field: 'principio', title: 'Principio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'clasificacion', title: 'Clasificación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'unidad_medida', title: 'Medida', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'rubro', title: 'Rubro', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'pais', title: 'País', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'conservacion_str', title: 'Conservacion', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'iva_str', title: 'Iva', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false},
                    { field: 'indicaciones', title: 'Indicaciones', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    // { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
                    { field: 'precio', title: 'Precio', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
                    // { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
                    {  field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                    { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
                ]
            ];
            $.each(json, function(index, value){
                cols[0].push({ align: 'center', valign: 'middle', title: value.sigla, sortable: true, colspan: 2 });
                cols[1].push({ field: `m_${value.id_metodo_pago}`, align: 'right', valign: 'middle', title: '%', sortable: true, formatter: separadorMiles, width: 90 });
                cols[1].push({ field: `p_desc_m_${value.id_metodo_pago}`, align: 'right', valign: 'middle', title: `P.`, sortable: true, formatter: separadorMiles, width: 90 });
            });
            $('#tabla').bootstrapTable("refreshOptions", { columns: cols });
        },
        error: function (jqXhr) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

    setTimeout(function () {
        $("#tabla").bootstrapTable('resetView');
    }, 100);
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 33);
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
    //classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: "codigo",
    sortOrder: 'desc',
    trimOnSearch: false,
    // columns: [[]]
});

$('#tabla').on('editable-shown.bs.table', (e, field, row, $el, editable) => editable.input.$input[0].value = row[field] != 0 ? row[field] : '');

$('#tabla').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el){
    let numero = row.comision;
    // Si la columna quedo en blanco
    if (!numero) {
        update_row[field] = 0;
    } 

    if (numero > 100) {
        update_row[field] = oldValue;
    }
    
    $.ajax({
        url: url,
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: {
            q: 'editar-comision',
            id_producto: row.id_producto,
            comision : row.comision
        },
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();

            if (data.status == 'ok') {
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
})

$.extend($('#tabla').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)" maxlength="3">',

});

$('#filtros').click(function() {
    $('#modalLabel').html('Filtros');
});

$('#actualizar').click(function() {
    $('#modalLabelComision').html('Actualizar Comisión');
    $('#formulario_comision').attr('action', 'actualizar-comision');
    var total = $('#tabla').bootstrapTable('getOptions').totalRows;
    $('#alert_cantidad_registro').html(`<i class="fas fa-info mr-2"></i> Se actualizarán ${total} registros`)
    $('#comision').val('');
    setTimeout(() => $('#comision').focus(), 500);
});


$('#filtrar').click(function() {
    var tipo            = $('#tipo').val();
    var rubro           = $('#rubro').val();
    var procedencia     = $('#procedencia').val();
    var origen          = $('#origen').val();
    var clasificacion   = $('#clasificacion').val();
    var presentacion    = $('#presentacion').val();
    var unidad_medida   = $('#unidad_medida').val();
    var marca           = $('#marca').val();
    var laboratorio     = $('#laboratorio').val();
    var id_producto     = $('#id_producto').val();
    var comision        = $('#con_sin').val();

    $('#tabla').bootstrapTable("refresh",{url: url + '?q=ver&producto=' + id_producto + '&laboratorio=' + laboratorio + '&marca=' + marca + '&unidad_medida=' + unidad_medida + '&presentacion=' + presentacion + '&clasificacion=' + clasificacion + '&origen=' + origen + '&procedencia=' + procedencia + '&rubro=' + rubro + '&tipo=' + tipo + '&con_sin=' + comision});

    $('#modal').modal('hide');
    habilitarBoton();
});

function habilitarBoton(){
    var tipo            = $('#tipo').val();
    var rubro           = $('#rubro').val();
    var procedencia     = $('#procedencia').val();
    var origen          = $('#origen').val();
    var clasificacion   = $('#clasificacion').val();
    var presentacion    = $('#presentacion').val();
    var unidad_medida   = $('#unidad_medida').val();
    var marca           = $('#marca').val();
    var laboratorio     = $('#laboratorio').val();
    var id_producto     = $('#id_producto').val();
    var comision        = $('#con_sin').val();

    if(tipo == null && rubro == null && procedencia == null && origen == null && clasificacion == null && presentacion == null && unidad_medida== null && marca== null && laboratorio== null && id_producto== null && comision == null){
        $('#actualizar').prop('disabled',true);
    }else{
        $('#actualizar').prop('disabled',false);
    }
}

$('#formulario_comision').submit(function(e) {
    e.preventDefault();
    
    if ($(this).valid() === false) return false;
    var tipo            = $('#tipo').val();
    var rubro           = $('#rubro').val();
    var procedencia     = $('#procedencia').val();
    var origen          = $('#origen').val();
    var clasificacion   = $('#clasificacion').val();
    var presentacion    = $('#presentacion').val();
    var unidad_medida   = $('#unidad_medida').val();
    var marca           = $('#marca').val();
    var laboratorio     = $('#laboratorio').val();
    var producto        = $('#id_producto').val();
    var con_sin         = $('#con_sin').val();
    var data = $(this).serializeArray();
    data.push({ name: 'tipo', value: tipo},{name : 'rubro', value : rubro},{name : 'procedencia', value : procedencia},{name : 'origen', value : origen},{name : 'clasificacion', value : clasificacion},{name : 'presentacion', value : presentacion},{name : 'unidad_medida', value : unidad_medida},{name : 'marca', value : marca},{name : 'laboratorio', value : laboratorio},{name : 'producto', value : producto},{name : 'con_sin', value : con_sin});
    $.ajax({
        url: url + '?q='+$(this).attr('action'),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            if (data.status == 'ok') {
                alertDismissJS(data.mensaje, data.status, function(){
                    $('#modal_comision').modal('hide');
                    $('#tabla').bootstrapTable('refresh');
                })
            }else{
                alertDismissJS(data.mensaje, data.status)
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
        }
    });
});
