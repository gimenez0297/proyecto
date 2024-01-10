var url = 'inc/reporte-total-compras-ventas-data';
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&reporte=${$('#filtro_reporte').val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 30);
}


// Filtros
$('#filtro_sucursal').select2({
    placeholder: 'Sucursales',
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&reporte=${$('#filtro_reporte').val()}` })
});

$('#filtro_reporte').select2({
    placeholder: 'Reporte',
    width: '200px',
    allowClear: false,
    selectOnClose: true,
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&reporte=${$(this).val()}` })
});

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: false,
    showRefresh: true,
    showToggle: true,
    showColumns: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    showFooter: true,
    footerStyle: bstFooterStyle,
    mobileResponsive: true,
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: "fecha_emision",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'fecha_emision', title: 'Fecha Emision', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Razón Social/Nombre', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'timbrado', title: 'Timbrado', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 200 },
            { field: 'documento', title: 'Nro. Comprobante', align: 'right', valign: 'middle', sortable: true},
            { field: 'gravada_10', title: 'Gravada 10%(iva incl.)', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
            { field: 'gravada_5', title: 'Gravada 5%(iva incl.)', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
            { field: 'exenta', title: 'Exenta', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
            { field: 'monto', title: 'Imp. Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
            { field: 'condicion', title: 'Condicion', align: 'center', valign: 'middle', sortable: true, visible: true },
        ]
    ]
});

$("#imprimir").on("click", function (e) {
    var param = {
        desde         : $('#desde').val(),
        hasta         : $('#hasta').val(),
        sucursal      : $('#filtro_sucursal').val(),
        reporte       : $('#filtro_reporte').val(),
        columnas      : JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').map(columna=>columna.field)),
    };
      
    OpenWindowWithPost("imprimir-total-compras-ventas", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirRankingClientes", param);
    //resetForm();
});

$("#exportar").on("click", function (e) {
    var param = {
        desde         : $('#desde').val(),
        hasta         : $('#hasta').val(),
        sucursal      : $('#filtro_sucursal').val(),
        reporte       : $('#filtro_reporte').val(),
        columnas      : JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').map(columna=>columna.field)),
        titulos       : JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').map(columna=>columna.title)),
    };
    window.location.replace(`exportar-total-compras-ventas.php?${$.param(param)}`);
    //resetForm();
});