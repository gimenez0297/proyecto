var url = 'inc/reporte-stock-premios-data';
var url_listados = 'inc/listados';

// Acciones por teclado

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#imprimir').click(); });

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
    //Actualiza los estados de la tabla clientes puntos en comparacion al periodo_canje establecida en plan puntos 
    actualiza_estado_puntos_cliente();

    // Mostrar datos en la tabla
    $('#filtro_fecha').change();
    $('#modal_lotes').on('hide.bs.modal', function(e) {
        Mousetrap.pause();
    });
    
    $('#modal_lotes').on('show.bs.modal', function(e) {
        Mousetrap.unpause();
    });
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
//cb(moment().startOf('month'), moment().endOf('month'));
cb(moment(), moment());

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
    $('#tabla_lotes').bootstrapTable('refresh', { url: url+'?q=ver_lotes&id_premio='+$('#produc_click').val()+'&fecha_desde='+$('#desde').val()+'&fecha_hasta='+$('#hasta').val() });
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalles mr-1" title="Ver Lotes"><i class="fas fa-list"></i></button>',
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
    sortName: "codigo",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_stock_premio', title: 'ID Stock', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_premio', title: 'ID Premios', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'premio', title: 'Premio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'descripcion', title: 'Descripcion', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 100 }
        ]
    ]
});

// Lotes
function verDetalles(row) {
    $('#lotes_producto').html(row.premio);
    $('#produc_click').val(row.id_premio);
    $('#modal_lotes').modal('show');
    $('#tabla_lotes').bootstrapTable('refresh', { url: url+'?q=ver_lotes&id_premio='+row.id_premio+'&fecha_desde='+$('#desde').val()+'&fecha_hasta='+$('#hasta').val() });}

$("#tabla_lotes").bootstrapTable({
    toolbar: '#toolbar_lotes',
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
    sortName: "sph.id_stock_premios_historial",
    sortOrder: 'desc',
    pagination: 'true',
    sidePagination: 'server',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_stock_premios_historial', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'center', valign: 'middle', sortable: true,  footerFormatter: bstFooterTextTotal },
            { field: 'detalles', title: 'Documento', align: 'lefth', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'operacion_str', title: 'Tipo', align: 'center', valign: 'middle', sortable: true,  cellStyle: bstTruncarColumna },
            { field: 'entrada', title: 'Entrada', align: 'right', valign: 'right', sortable: true, footerFormatter: bstFooterSumatoria, visible: true },
            { field: 'salida', title: 'Salida', align: 'right', valign: 'right', sortable: true, footerFormatter: bstFooterSumatoria, visible: true },        ]
    ]
});

$('#filtro_producto').select2({
   // dropdownParent: $("#modal"),
    placeholder: 'Premio',
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
            return { q: 'premios', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_premio, text: obj.premio }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_premio=${$('#filtro_producto').val()}&id_tipo_producto=${$('#filtro_tipo_producto').val()}` })
});


$('#imprimir').click(function() {
    var id_premio = $('#produc_click').val();
    var desde            = $('#desde').val();
    var hasta            = $('#hasta').val();

    var param = {id_premio : id_premio , desde: desde, hasta: hasta};
    OpenWindowWithPost("imprimir-reporte-stock-premio", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStockPremio", param);

    $('#modal').modal('hide');
});
