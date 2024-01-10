var url = 'inc/estado-clientes-data';
var url_listados = 'inc/listados';

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
    $('#filtro_sucursal').trigger('change');
    $('#filtro_estado').trigger('change');
    $('#filtro_clientes').trigger('change');
    $("#tabla").bootstrapTable('hideColumn', 'state');
});

function alturaTabla() {
    return bstCalcularAlturaTabla(222, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
  
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-recibos mr-1" title="Ver recibos"><i class="fas fa-list"></i></button>`,
    ].join('');
}


function iconosFilaRecibo(value, row, index) {
  
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm imprimir-recibo mr-1" title="Imprimir Recibo"><i class="fas fa-print"></i></button>`,
    ].join('');
}

// CALENDARIO
var fechaIni;
var fechaFin;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cb(start, end) {
    fechaIni = start.format('DD/MM/YYYY');
    fechaFin = end.format('DD/MM/YYYY');
    $('#filtro_fecha span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#desde").val(fechaMYSQL(fechaIni));
    $("#hasta").val(fechaMYSQL(fechaFin));
}

// Rango: Mes actual
cb(moment().startOf('month'), moment().endOf('month'));

// Rango: dia actual
// cb(moment(), moment());

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
        [meses[moment().subtract(1, 'month').format("M")]] : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        [meses[moment().subtract(2, 'month').format("M")]] : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
        [meses[moment().subtract(3, 'month').format("M")]] : [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')],
        [meses[moment().subtract(4, 'month').format("M")]] : [moment().subtract(4, 'month').startOf('month'), moment().subtract(4, 'month').endOf('month')],
        [meses[moment().subtract(5, 'month').format("M")]] : [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')]
    }
}, cb);

$('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) {
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#desde").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha').change();
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&estado=${$('#filtro_estado').val()}&cliente=${$('#filtro_cliente').val()}` })
});

//Filtros
$('#filtro_sucursal').select2({
    placeholder: 'Sucursal',
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
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&estado=${$('#filtro_estado').val()}&cliente=${$('#filtro_cliente').val()}` })
});

$('#filtro_estado').select2({
    placeholder: 'Estados',
    width: 'style',
    allowClear: false,
    selectOnClose: true,
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&estado=${$(this).val()}&cliente=${$('#filtro_cliente').val()}` })
});

$('#filtro_clientes').select2({
    placeholder: 'Cliente',
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
            return { q: 'clientes-credito', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_cliente, text: obj.razon_social }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&estado=${$('#filtro_estado').val()}&cliente=${$(this).val()}` });
}).on('select2:select', function (e) {
    $("#tabla").bootstrapTable('showColumn', 'state');
}).on('select2:clear', function (e) {
    $("#tabla").bootstrapTable('hideColumn', 'state');
});

$('#metodo').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: true,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'metodos_pagos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_metodo_pago, text: obj.metodo_pago }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function(){
    if(this.value == 3 || this.value == 2){
        $('label[for=detalles]').addClass('label-required');
        $('label[for=detalles]').html(`Nro. de Voucher`);
        $('#detalles').prop('required', true);
    }else{
        $('label[for=detalles]').removeClass('label-required');
        $('label[for=detalles]').html(`Detalles`);
        $('#detalles').prop('required', false);
    }
});


window.accionesFila = {
    'click .ver-recibos': function(e, value, row, index) {
        $('#modal_recibos').modal('show');
        $('#toolbar_recibos_text').html(`Recibos de ${row.razon_social} de la factura ${row.nro_documento}`);
        $('#tabla_recibos').bootstrapTable('refresh', { url: url+'?q=ver_recibos&id_factura='+row.id_factura });
    }
}

window.accionesFilaRecibo = {
    'click .imprimir-recibo': function (e, value, row, index) {
        var param = { id_recibo: row.id_recibo, imprimir : 'no', recargar: 'no' };
        OpenWindowWithPost("imprimir-ticket-pago-credito", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirRecibo", param);
    }
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
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
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: "id_factura",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true, formatter: checkbox },
            { field: 'id_factura', title: 'ID Factura', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_cliente', title: 'ID Cliente', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_venta', title: 'Fecha de Venta', align: 'left', valign: 'middle', sortable: true },
            { field: 'nro_documento', title: 'Nro. Documento', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, },
            { field: 'razon_social', title: 'Razon Social/Nombre', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'usuario', title: 'Vendedor', align: 'left', valign: 'middle', sortable: true },
            { field: 'monto', title: 'Total a Pagar', align: 'left', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'pagado', title: 'Pagado', align: 'left', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'saldo', title: 'Saldo', align: 'left', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 150 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 150 }
        ]
    ]
}).on('check.bs.table', function(row, $elemet) {
    var selections = $("#tabla").bootstrapTable('getSelections');
    $('#pagar').prop('disabled', (selections.length > 0) ? false : true);
}).on('uncheck.bs.table', function(row, $elemet) {
    var selections = $("#tabla").bootstrapTable('getSelections');
    $('#pagar').prop('disabled', (selections.length > 0) ? false : true);
}).on('check-all.bs.table', function(rowsAfter, rowsBefore) {
    var selections = $("#tabla").bootstrapTable('getSelections');
    $('#pagar').prop('disabled', (selections.length > 0) ? false : true);
}).on('uncheck-all.bs.table', function(rowsAfter, rowsBefore) {
    var selections = $("#tabla").bootstrapTable('getSelections');
    $('#pagar').prop('disabled', (selections.length > 0) ? false : true);
}).on('load-success.bs.table', function(data) {
    $('#pagar').prop('disabled', true);
});


// detalles
$("#tabla_recibos").bootstrapTable({
    toolbar: '#toolbar_recibos',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
	height: 480,
    sortName: "id_recepcion_compra",
    sortOrder: 'asc',
    uniqueId: 'id_recepcion_compra',
    columns: [
        [
            { field: 'id_cobro', title: 'ID Cobro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_recibo', title: 'ID Recibo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'metodo_pago', title: 'Metodo Pago', align: 'left', valign: 'middle', sortable: true },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true },
            { field: 'monto', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },    
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaRecibo,  formatter: iconosFilaRecibo, width: 150 }
        ]
    ]
});

function checkbox(value, row, index, field) {
    return (row.estado_str != 'Pendiente') ? { disabled: true } : value;
}

$('#pagar').on('click', function() {
    resetForm('#formulario');
    $('#modalLabel').html('Pagar Credito');
    $('#formulario').attr('action', 'cargar');
    var ventas = $('#tabla').bootstrapTable('getSelections');
    let total = ventas.reduce((acc, value) => acc += quitaSeparadorMiles(value.saldo), 0);
    $('#nombre').val($('#filtro_clientes').select2('data')[0].text);
    $('#monto').val(separadorMiles(total));
    $("#id").val($('#filtro_clientes').select2('data')[0].id);
});

$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    var contenido = JSON.stringify($("#tabla").bootstrapTable('getSelections').map(row => {return {id_provedor: row.id_proveedor, id_factura: row.id_factura}}));
    data.push({name: "creditos_pagados", value: contenido});
    $.ajax({
        url: url + '?q='+$(this).attr("action"),
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
                var param = { id_recibo: data.id_recibo, imprimir : 'si', recargar: 'no' };
                OpenWindowWithPost("imprimir-ticket-pago-credito", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirRecibo", param);
                $('#modal_pagar').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

});




function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}




$('#filtro_fecha').change();
