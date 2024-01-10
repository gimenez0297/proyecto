var url = 'inc/reporte-despidos-data';
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
    $('#periodo').trigger('change');
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&periodo=${$("#periodo").val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 30);
}

function iconosFila(value, row, index) {
    let disabled = (row.periodo) ? '' : 'disabled';
    let title = (row.periodo) ? 'Ver detalle' : 'No disponible';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-liquidaciones mr-1" title="${title}" ${disabled}><i class="fas fa-list"></i></button>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-liquidaciones': function(e, value, row, index) {
        $('#modal_liquidaciones').modal('show');
        $('#toolbar_liquidaciones_text').html(`INGRESOS`);
        $('#toolbar_liquidaciones_text_des').html(`DESCUENTOS`);
        $('#tabla_liquidaciones').bootstrapTable('refresh', { url: url+'?q=ver_liquidacion_ingreso&id_liquidacion='+row.id_liquidacion });
        $('#tabla_liquidaciones_des').bootstrapTable('refresh', { url: url+'?q=ver_liquidacion_descuento&id_liquidacion='+row.id_liquidacion });
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
    sortName: "id_funcionario",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_funcionario', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_liquidacion', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha_alta', title: 'Ingreso', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'fecha_baja', title: 'Salida', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'periodo', title: 'Periodo', align: 'left', valign: 'middle', sortable: true },
            { field: 'neto_cobrar', title: 'Neto Cobro', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
});

$('#periodo').select2({
    placeholder: 'Periodo',
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
            return { q: 'periodo_anho', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.periodo, text: obj.periodo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$("#filtro_sucursal").val()}&periodo=${$(this).val()}` })
});

$('#btn_exportar').on('click', function() {
   var param = {
        sucursal       : $('#filtro_sucursal').val(),
        periodo       : $('#periodo').val(),
    };
    window.location.replace(`exportar-despidos.php?${$.param(param)}`);
    // resetForm();
});

// liquidaciones
$("#tabla_liquidaciones").bootstrapTable({
    toolbar: '#toolbar_liquidaciones',
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
    sortName: "conceto",
    sortOrder: 'asc',
    uniqueId: 'id_liquidacion_ingreso',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_liquidacion_ingreso', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'concepto', title: 'CONCEPTO', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'importe', title: 'IMPORTE', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'observacion', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true},
        ]
    ]
});

$("#tabla_liquidaciones_des").bootstrapTable({
    toolbar: '#toolbar_liquidaciones_des',
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
    sortName: "conceto",
    sortOrder: 'asc',
    uniqueId: 'id_liquidacion_descuento',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_liquidacion_descuento', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'concepto', title: 'CONCEPTO', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'importe', title: 'IMPORTE', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'observacion', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true },
        ]
    ]
});

$('#filtro_fecha').change();