var url = 'inc/administrar-cajas-data';
var url_listados = 'inc/listados';
var url_cajas = 'inc/cajas-data';

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

    // $('#btn_abrir_caja, #btn_cerrar_caja').prop('disabled', true);
    actualizar_datos(0);
});

// Filtros
// CALENDARIO
var fechaIni;
var fechaFin;

function cb(start, end) {
    fechaIni = start.format('DD/MM/YYYY');
    fechaFin = end.format('DD/MM/YYYY');
    $('#filtro_fecha span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#desde").val(fechaMYSQL(fechaIni));
    $("#hasta").val(fechaMYSQL(fechaFin));
}

// Rango: Mes actual
// cb(moment().startOf('month'), moment().endOf('month'));

// Rango: dia actual
cb(moment(), moment());

$('#filtro_fecha').daterangepicker({
    timePicker: false,
    opens: "right",
    format: 'DD/MM/YYYY',
    locale: {
        applyLabel: 'Aplicar',
        cancelLabel: 'Borrar',
        fromLabel: 'Desde',
        toLabel: 'Hasta',
        customRangeLabel: 'Personalizado',
        daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi','Sa'],
        monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"],
        firstDay: 1
    },
    ranges: {
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
        'Este Mes': [moment().startOf('month'), moment().endOf('month')],
    }
}, cb);

$('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#desde").val(picker.startDate.format('YYYY-MM-DD'));	
    $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha').change();
});

$('#filtro_fecha').on('change', function() {
    $('#filtro_caja').val(null).trigger('change');
});

$('#filtro_sucursal').on('change', function() {
    $('#id_caja').val(null).trigger('change');
});

$('#id_caja').on('change', function() {
    $('#filtro_caja').val(null).trigger('change');
});
// FIN CALENDARIO

$('#filtro_sucursal').select2({
    placeholder: 'Sucursal',
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
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_caja').select2({
    placeholder: 'Caja',
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
            return { q: 'cajas', id_sucursal: $('#filtro_sucursal').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_caja, text: obj.numero }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#filtro_caja').select2({
    placeholder: 'Turno',
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
            return { q: 'cajas', desde: $('#desde').val(), hasta: $('#hasta').val(), id_sucursal: $('#filtro_sucursal').val(), id_caja: $('#id_caja').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_caja_horario, text: `#${obj.id_caja_horario} (${obj.caja})`, estado: obj.estado }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },

        cache: false
    }
}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
    
}).on('change', function() {
    if($(this).val()){
        if($(this).select2('data')[0].estado == 1){
            $('#btn_extraer, #btn_cerrar_caja').prop('disabled', false);
        }else{
            $('#btn_extraer, #btn_cerrar_caja').prop('disabled', true);
        }
    }else{
        $('#btn_extraer, #btn_cerrar_caja').prop('disabled', true);
    }
    actualizar_datos($(this).val());
});

function actualizar_datos(id_caja) {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_caja=${id_caja}` })
    $('#tabla_resumen').bootstrapTable('refresh', { url: `${url}?q=ver_resumen&id_caja=${id_caja}` })
    $('#tabla_arqueo_cierre').bootstrapTable('refresh', { url: `${url}?q=ver_arqueo_cierre&id_caja=${id_caja}` })
    $('#tabla_servicios').bootstrapTable('refresh', { url: `${url}?q=ver_resumen_servicios&id_caja=${id_caja}` })
    $('#tabla_diferencia').bootstrapTable('refresh', { url: `${url}?q=ver_diferencia&id_caja=${id_caja}` })
}

function alturaTabla() {
    return bstCalcularAlturaTabla(300, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

$('#tabla').bootstrapTable({
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
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: 'metodo_pago',
    sortOrder: 'asc',
    trimOnSearch: false,
    showFooter: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_factura', title: 'ID Factura', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'Número', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'fecha_venta', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'condicion', title: 'Condición', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ruc', title: 'R.U.C.', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Razón Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, visible: false },
            { field: 'descuento', title: 'Descuento', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'total_venta', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'total_pagado', title: 'Pagado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'estado', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
        ]
    ]
});


function alturaTablaResumen() {
    return bstCalcularAlturaTabla(450, 300);
}
function pageSizeTablaResumen() {
    return Math.floor(alturaTablaResumen() / 50);
}

$('#tabla_resumen').bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_resumen',
    showExport: true,
    // search: true,
    // showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaResumen(),
    pageSize: pageSizeTablaResumen(),
    sortName: 'metodo_pago',
    sortOrder: 'asc',
    trimOnSearch: false,
    showFooter: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_metodo_pago', title: 'ID Método Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'metodo_pago', title: 'Método De Pago', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

function alturaTablaArqueoCierre() {
    return bstCalcularAlturaTabla(450, 300);
}
function pageSizeTablaArqueoCierre() {
    return Math.floor(alturaTablaArqueoCierre() / 50);
}

$('#tabla_arqueo_cierre').bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_arqueo_cierre',
    showExport: true,
    showToggle: true,
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaArqueoCierre(),
    pageSize: pageSizeTablaArqueoCierre(),
    sortName: 'metodo_pago',
    sortOrder: 'asc',
    trimOnSearch: false,
    showFooter: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'valor', title: 'Moneda', align: 'right', valign: 'middle', sortable: false, formatter: separadorMiles, footerFormatter: bstFooterTextTotal },
            { field: 'cantidad', title: 'Cantidad', align: 'center', valign: 'middle', sortable: false, formatter: separadorMiles },
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: false, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

function alturaTablaServicios() {
    return bstCalcularAlturaTabla(450, 300);
}
function pageSizeTablaServicios() {
    return Math.floor(alturaTablaServicios() / 50);
}

$('#tabla_servicios').bootstrapTable({
    //url: url + '?q=ver',
    toolbar: '#toolbar_servicios',
    showExport: true,
    // search: true,
    // showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaServicios(),
    pageSize: pageSizeTablaServicios(),
    sortName: 'servicio',
    sortOrder: 'asc',
    trimOnSearch: false,
    showFooter: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_servicio', title: 'ID Servicio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'servicio', title: 'Servicio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

// Abrir y cerrar caja
$('#cajero').select2({
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'ver_cajeros', id_caja: $('#id_caja').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_usuario, text: obj.usuario, estado: obj.estado }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },

        cache: false
    }
}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
});


$('#modal').on('shown.bs.modal', function(e) {
    // Reinicia los valores
    resetForm('#formulario');
    $('.cantidad_moneda, .total').val('0');
    // Primer input de cantidad
    $('#cantidad').focus();
    $('#id_sucursal').val($('#filtro_sucursal').val());
    $('#id').val($('#filtro_caja').val());
    $('#id_caja_modal').val($('#id_caja').val());
    $('#nav_1').tab('show');
});

$('#nav_1').on('shown.bs.tab', function() {
    $('#cantidad').focus();
});
$('#nav_2').on('shown.bs.tab', function() {
    $('#monto_servicio').focus();
});

$('#nav_3').on('shown.bs.tab', function() {
    $('#cantidad_sen').focus();
});

$('#btn_abrir_caja').click(function() {
    $('#formulario').attr('action', 'cargar');
    $('#modalLabel').text('Apertura de Caja');
    $('#btn_submit').text('Abrir Caja');
    $('#observacion').parent().parent().hide();
    $('#cajero').parent().show();
    $('#tabModal').hide();
});

$('#btn_cerrar_caja').click(function() {
    $('#formulario').attr('action', 'cerrar');
    if($('#filtro_caja').val() == null){
        $('#modalLabel').text('Cierre de Caja');
    }else{
        $('#modalLabel').text('Cierre de Caja #' +  $('#filtro_caja').select2('data')[0].id);
    }
    $('#btn_submit').text('Cerrar Caja');
    $('#observacion').parent().parent().show();
    $('#cajero').parent().hide();
    $('#tabModal').show();
});

$('#btn_extraer').click(function() {
    $('#formulario_extraer').attr('action', 'extraer');
    $('#modalLabelExtraer').text('Extracción de Caja');
    $('#observacion').parent().parent().show();
    setTimeout(function(){
        $("#monto_extraccion").focus();
    },200);
    
    resetFormextra('#formulario_extraer');
});

$('.cantidad_moneda').focus(function() { this.select() });

$('.cantidad_moneda').blur(function() { if ($(this).val() == '') $(this).val('0') });

$('.cantidad_moneda').keyup(function() {
    soloNumeros(this);
    // Contenedor de inputs
    let $contenedor = $(this).parent().parent();
    // Valor de la moneda
    let valor_moneda = $contenedor.find('.valor_moneda').val();
    // Total
    let total = quitaSeparadorMiles(valor_moneda) * quitaSeparadorMiles($(this).val());
    // Se agrega el total
    $contenedor.find('.total').val(separadorMiles(total));
    total_caja();
});

function total_caja() {
    let totales = $('#formulario').find('.total');
    let total_caja = Object.values(totales).reduce((acc, cv) => acc + quitaSeparadorMiles(cv.value), 0);
    $('#total_caja').val(separadorMiles(total_caja));
}

// Para avanzar entre los campos dando enter
$('.cantidad_moneda').keydown(function (e) {
    if (e.which === 13) { e.preventDefault(); nextFocus($(this),$('.container-fluid'),1); }
});

$('.cantidad_moneda_sen').focus(function () { this.select() });

$('.cantidad_moneda_sen').blur(function () { if ($(this).val() == '') $(this).val('0') });

$('.cantidad_moneda_sen').keyup(function () {
    soloNumeros(this);
    // Contenedor de inputs
    let $contenedor = $(this).parent().parent();
    // Valor de la moneda
    let valor_moneda = $contenedor.find('.valor_moneda_sen').val();
    // Total
    let total = quitaSeparadorMiles(valor_moneda) * quitaSeparadorMiles($(this).val());
    // Se agrega el total
    $contenedor.find('.total_sen').val(separadorMiles(total));
    total_caja_sen();
});

function total_caja_sen() {
    let totales = $('#formulario').find('.total_sen');
    let total_caja = Object.values(totales).reduce((acc, cv) => acc + quitaSeparadorMiles(cv.value), 0);
    $('#total_caja_sen').val(separadorMiles(total_caja));
}

// Para avanzar entre los campos dando enter
$('.cantidad_moneda_sen').keydown(function (e) {
    if (e.which === 13) { e.preventDefault(); nextFocus($(this), $('.container-fluid'), 1); }
});

$.ajax({
    url: url + '?q=ver_servicios',
    dataType: 'json',
    type: 'post',
    contentType: 'application/x-www-form-urlencoded',
    data: {},
    beforeSend: function() {
        NProgress.start();
    },
    success: function(data, textStatus, jQxhr) {
        NProgress.done();

        let html = '';
        $.each(data, function(index, value) {
            html += `
                <div class="form-group-sm col-5">
                    ${index == 0 ? `<label class="label-sm" for="servicio_str">Servicio</label><br>` : ``}
                    <input type="hidden" name="servicios[]" value="${value.id_servicio}" readonly>
                    <input type="text" class="form-control form-control-sm input-sm" ${index == 0 ? `id="servicio_str"` : ``} title="${value.servicio}" value="${value.servicio}" readonly>
                </div>
                <div class="form-group-sm col-5">
                    ${index == 0 ? `<label class="label-sm" for="monto_servicio">Total</label><br>` : ``}
                    <input type="text" class="form-control form-control-sm input-sm text-right monto_servicio" name="montos_servicios[]" ${index == 0 ? `id="monto_servicio"` : ``} title="Total ${value.servicio}" value="0" onkeyup="separadorMilesOnKey(event,this)" required>
                </div>
            `;
        });
        $('#content_servicios').html(html);

        $('.monto_servicio').focus(function() { this.select() });
        $('.monto_servicio').blur(function() { if ($(this).val() == '') $(this).val('0') });
        $('.monto_servicio').keydown(function (e) {
            if (e.which === 13) { e.preventDefault(); nextFocus($(this),$('.container-fluid'),1); }
        });
        $('.monto_servicio').keyup(function() {
            soloNumeros(this);
            let totales = $('#formulario').find('.monto_servicio');
            let total = Object.values(totales).reduce((acc, cv) => acc + quitaSeparadorMiles(cv.value), 0);
            $('#total_servicios').val(separadorMiles(total));
        });

    },
    error: function(jqXhr, textStatus, errorThrown) {
        NProgress.done();
        alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
    }
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$('#formulario').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
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
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
                actualizar_datos($("#filtro_caja").val());
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
        }
    });
});

// GUARDAR DATOS DE EXTRACCION
$('#formulario_extraer').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url + '?q='+$(this).attr('action'),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: { monto_extraccion: $('#monto_extraccion').val(), observacion: $('#observacion_extr').val(), id_caja: $('#filtro_caja').val()},
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            
            if (data.status == 'ok') {
                $('#modal-extraccion').modal('hide');
                var param = { id_extraccion: data.id_extraccion, imprimir : 'si', recargar: 'no' };
                OpenWindowWithPost("imprimir-ticket-extraccion", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirTicket", param);
                resetWindow();
            }else{
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
        }
    });
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
    }
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function resetFormextra(form) {
    $(form).trigger('reset');
    resetValidateForm(form);
}

$('#tabla_diferencia').bootstrapTable({
    // url: url + '?q=ver',
    toolbar: '#toolbar_diferencia',
    showExport: true,
    showToggle: true,
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaArqueoCierre(),
    pageSize: pageSizeTablaArqueoCierre(),
    // sortName: 'monto',
    // sortOrder: 'asc',
    trimOnSearch: false,
    // showFooter: true,
    // footerStyle: bstFooterStyle,
    columns: [
        [
            // { field: 'id_metodo_pago', title: 'ID Método Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'titulo', title: 'Descripción', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
        ]
    ]
});

