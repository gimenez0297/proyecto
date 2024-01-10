var url = 'inc/reportes-cajas-data';
var url_caja = 'inc/administrar-cajas-data';
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

    // Mostrar datos en la tabla
    $('#filtro_sucursal').trigger('change');
});

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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_cajero=${$('#cajeros').val()}` })
});


// Filtros
$('#filtro_sucursal').select2({
    placeholder: 'Sucursal',
    width: '200px',
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_cajero=${$('#cajeros').val()}` })
});

$('#metodo_pago').select2({
    dropdownParent: $("#modal_detalle"),
    placeholder: 'Seleccionar',
    width: '200px',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'ver_metodos_tarjetas', term: params.term, page: params.page || 1 }
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
}).on('change', function() {
    var id_caja = $('#id_caja').val();
    $('#tabla_vouchers').bootstrapTable('refresh', { url: `${url_caja}?q=ver_vouchers&id_caja=${id_caja}&metodo_pago=${$(this).val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 40);
}

function iconosFila(value, row, index) {
    // Procesado parcial o Total
    let disabled = row.estado == 2 ? 'disabled' : '';

    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Detalles"><i class="fas fa-list"></i></button>',
        // `<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cargar-documento mr-1" title="Cargar Documentos"><i class="fas fa-file-medical-alt"></i>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        $('#modal_detalle').modal('show');
        $('#id_caja').val(row.id_caja_horario);
        $('#tabModal a[href="#tab_1"]').tab('show');

        let id_caja_horario = row.id_caja_horario;

        $('#tabla_resumen').bootstrapTable('refresh', { url: `${url_caja}?q=ver_resumen&id_caja=${id_caja_horario}` })
        $('#tabla_arqueo_cierre').bootstrapTable('refresh', { url: `${url_caja}?q=ver_arqueo_cierre&id_caja=${id_caja_horario}` })
        $('#tabla_servicios').bootstrapTable('refresh', { url: `${url_caja}?q=ver_resumen_servicios&id_caja=${id_caja_horario}` })
        $('#tabla_diferencia').bootstrapTable('refresh', { url: `${url_caja}?q=ver_diferencia&id_caja=${id_caja_horario}` })
        $('#tabla_vouchers').bootstrapTable('refresh', { url: `${url_caja}?q=ver_vouchers&id_caja=${id_caja_horario}` })
    },
    'click .imprimir': function (e, value, row, index) {
        var param = { id_extraccion: row.id_extraccion, imprimir : 'si', recargar: 'no' };
        OpenWindowWithPost("imprimir-ticket-extraccion", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirTicket", param);
        //resetWindow();
    }
}

$("#tabla").bootstrapTable({
    // url: url + '?q=ver',
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
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: "id_caja_horario",
    sortOrder: 'desc',
    pageList: [10, 25, 50, 100, 1000,'All'],
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_caja_horario', title: 'Turno', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'Caja', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'cajero', title: 'Cajero', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'apertura', title: 'Inicio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'cierre', title: 'Fin', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto_apertura', title: 'Apertura', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'total_efectivo', title: 'Efectivo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'sobre', title: 'Sobre', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'venta_sistema', title: 'Vta. Sist.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'diferencia', title: 'Diferencia', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'monto_servicios', title: 'Servicios', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 80 }
        ]
    ]
});

function alturaTablaResumen() {
    return bstCalcularAlturaTabla(250, 300);
}
function pageSizeTablaResumen() {
    return Math.floor(alturaTablaResumen() / 50);
}

$('#tabla_resumen').bootstrapTable({
    // url: url + '?q=ver',
    toolbar: '#toolbar_resumen',
    showExport: true,
    // search: true,
    showRefresh: true,
    // showToggle: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    // mobileResponsive: true,
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

$('#tabla_arqueo_cierre').bootstrapTable({
    // url: url + '?q=ver',
    toolbar: '#toolbar_arqueo_cierre',
    showExport: true,
    showRefresh: true,
    // showToggle: true,
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    // mobileResponsive: true,
    height: alturaTablaResumen(),
    pageSize: pageSizeTablaResumen(),
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

$('#tabla_servicios').bootstrapTable({
    //url: url + '?q=ver',
    toolbar: '#toolbar_servicios',
    showExport: true,
    // search: true,
    showRefresh: true,
    // showToggle: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    // mobileResponsive: true,
    height: alturaTablaResumen(),
    pageSize: pageSizeTablaResumen(),
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

$('#tabla_diferencia').bootstrapTable({
    // url: url + '?q=ver',
    toolbar: '#toolbar_diferencia',
    showRefresh: true,
    showExport: true,
    // showToggle: true,
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    // mobileResponsive: true,
    height: alturaTablaResumen(),
    pageSize: pageSizeTablaResumen(),
    // sortName: 'monto',
    // sortOrder: 'asc',
    trimOnSearch: false,
    // showFooter: true,
    // footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'titulo', title: 'Descripción', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
        ]
    ]
});

$('#tabla_vouchers').bootstrapTable({
    // url: url + '?q=ver',
    toolbar: '#toolbar_vouchers',
    showRefresh: true,
    showExport: true,
    // showToggle: true,
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    // mobileResponsive: true,
    height: alturaTablaResumen(),
    pageSize: pageSizeTablaResumen(),
    // sortName: 'monto',
    // sortOrder: 'asc',
    trimOnSearch: false,
    showFooter: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal},
            { field: 'metodo_pago', title: 'Metodo Pago', align: 'left', valign: 'middle', sortable: true},
            { field: 'entidad', title: 'Entidad', align: 'left', valign: 'middle', sortable: true},
            { field: 'detalles', title: 'Detalles', align: 'left', valign: 'middle', sortable: true},
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
        ]
    ]
});


$('#nav_1').on('shown.bs.tab', function (event) {
    $('#tabla_resumen').bootstrapTable('resetView');
});
$('#nav_2').on('shown.bs.tab', function (event) {
    $('#tabla_arqueo_cierre').bootstrapTable('resetView');
});
$('#nav_3').on('shown.bs.tab', function (event) {
    $('#tabla_servicios').bootstrapTable('resetView');
});
$('#nav_4').on('shown.bs.tab', function (event) {
    $('#tabla_diferencia').bootstrapTable('resetView');
});
$('#nav_5').on('shown.bs.tab', function (event) {
    $('#tabla_vouchers').bootstrapTable('resetView');
    $('#metodo_pago').val(null).trigger('change');
});


$('#btn_imprimir').on('click', function() {
    var param = { id_sucursal: $('#filtro_sucursal').val(), desde: $('#desde').val(), hasta: $('#hasta').val(), cajero: $('#cajeros').val()};
    OpenWindowWithPost("imprimir-reporte-cajas", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirProductos", param);
});


$('#cajeros').select2({
    placeholder: 'Seleccione',
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
            return { q: 'cajeros', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_funcionario, text: obj.funcionario }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$("#filtro_sucursal").val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_cajero=${$(this).val()}` })
});



$('#filtro_fecha').change();
