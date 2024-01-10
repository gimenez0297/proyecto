var url = 'inc/reporte-stock-insumos-data';
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

// Rango: Mes actual
//cb(moment().startOf('month'), moment().endOf('month'));
cb(moment(), moment('9999-12-31'));

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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_producto=${$('#filtro_producto').val()}&id_tipo_producto=${$('#filtro_tipo_producto').val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
        // Procesado parcial o Total
        let disabled = row.estado == 2 ? 'disabled' : '';
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
            { field: 'id_stock_insumo', title: 'ID Stock', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto_insumo', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'tipo_insumo', title: 'Tipo Insumo', align: 'left', valign: 'middle', sortable: true},
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true},
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'estado', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            // { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 100 }
        ]
    ]
});

// Lotes
function verDetalles(row) {
    $('#lotes_producto').html(row.producto);
    $('#modal_lotes').modal('show');
    $('#tabla_lotes').bootstrapTable('refresh', { url: `${url}?q=ver_lotes&id_producto_insumo=${row.id_producto_insumo}` });
}

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
    sortName: "vencimiento",
    sortOrder: 'desc',
    pagination: 'true',
    sidePagination: 'server',
    columns: [
        [
            { field: 'id_carga_insumo_producto', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true },
            { field: 'estado', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
            { field: 'precio', title: 'Costo U.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'precio', title: 'Costo T.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria, formatter: separadorMiles },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria, formatter: separadorMiles },
        ]
    ]
});

$('#filtro_producto').select2({
   // dropdownParent: $("#modal"),
    placeholder: 'Producto',
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
            return { q: 'productos_insumo', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_producto_insumo, text: obj.producto }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_producto=${$('#filtro_producto').val()}&id_tipo_producto=${$('#filtro_tipo_producto').val()}` })
});

$('#filtro_tipo_producto').select2({
    //dropdownParent: $("#modal"),
    placeholder: 'Tipos Productos',
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
            return { q: 'tipo_insumo', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_tipo_insumo, text: obj.nombre }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_producto=${$('#filtro_producto').val()}&id_tipo_producto=${$('#filtro_tipo_producto').val()}` })
});


$('#imprimir').click(function() {
    var producto_        = $('#filtro_producto').val();
    var tipo_producto_   = $('#filtro_tipo_producto').val();
    var desde            = $('#desde').val();
    var hasta            = $('#hasta').val();


    var param = {producto : producto_, tipo_producto : tipo_producto_, desde: desde, hasta:hasta};
    OpenWindowWithPost("imprimir-stock-insumo", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStockInsumo", param);

    $('#modal').modal('hide');
});