var url = 'inc/reporte-costo-insumos-data';
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
    $('#filtro_fecha').change();
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#filtros_impresion').click();
            break;
    }
});

// CALENDARIO
var fechaIni;
var fechaFin;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cb(start, end) {
    if (end.format('DD/MM/YYYY') == '31/12/9999') {
        // El filtro no se aplica al cargar la página, se coloca de esta manera para no confundir
        $('#filtro_fecha span').html('DD/MM/YYYY al DD/MM/YYYY');
        $("#desde").val('');
        $("#hasta").val('');
    } else {
        fechaIni = start.format('DD/MM/YYYY');
        fechaFin = end.format('DD/MM/YYYY');
        $('#filtro_fecha span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));
    }
}

cb(moment(), moment('9999-12-31'));

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
        'Sin filtro': [moment(), moment('9999-12-31')],
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

    // Se coloca esta fehca para evitar que coincida con las fechas cargadas por el usuario
    if (fechaFin == '31/12/9999') {
        $('#filtro_fecha span').html('DD/MM/YYYY al DD/MM/YYYY');
        $("#desde").val('');
        $("#hasta").val('');
    } else {
        $('#filtro_fecha span').html(picker.startDate.format('DD/MM/YYYY') + ' al ' + picker.endDate.format('DD/MM/YYYY'));
        $("#desde").val(picker.startDate.format('YYYY-MM-DD'));
        $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));
    }

    $('#filtro_fecha').change();
}).on('change', function() {
    $('#tabla_detalle').bootstrapTable('refresh', { url: `${url}?q=ver_detalle&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_sucursal=${$('#id').val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalles mr-1" title="Ver Detalle"><i class="fas fa-list"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .ver-detalles': function(e, value, row, index) {
        verDetalles(row);
    }
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: false,
    search: true,
    showRefresh: true,
    showToggle: false,
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
    sortName: "id_sucursal",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_sucursal', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true},
            // { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true},
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'costo_total', title: 'Total Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            // { field: 'estado', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 100 }
        ]
    ]
});

// Lotes
function verDetalles(row) {
    $('#detalle_producto').html(row.sucursal);
    $('#id').val(row.id_sucursal);
    $('#modal_detalle').modal('show');
    $('#tabla_detalle').bootstrapTable('refresh', { url: `${url}?q=ver_detalle&id_sucursal=${row.id_sucursal}` });
}

$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle',
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
    sortName: "fecha",
    sortOrder: 'desc',
    pagination: 'true',
    sidePagination: 'server',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_producto_insumo', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'codigo', title: 'Codigo', align: 'left', valign: 'middle', sortable: true},
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria, formatter: separadorMiles },
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria, formatter: separadorMiles },
        ]
    ]
});

$('#imprimir').click(function() {
    var id_sucursal      = $('#id').val();
    var desde            = $('#desde').val();
    var hasta            = $('#hasta').val();

    var param = {id_sucursal : id_sucursal, desde: desde, hasta:hasta};
    OpenWindowWithPost("imprimir-costo-insumos", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStockInsumo", param);

    $('#modal').modal('hide');
});