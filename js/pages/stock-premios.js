var url = 'inc/stock-premios-data';
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
        $('#toolbar_titulo').html(`${row.lote}`);
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
    showFooter: true,
    footerStyle: bstFooterStyle,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: "id_premio",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_stock_premio', title: 'ID Stock', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_premio', title: 'ID Premio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal },
            { field: 'premio', title: 'Premio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'costo_unitario', title: 'Costo U.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles  },
            { field: 'costo_total', title: 'Costo T.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,  footerFormatter: bstFooterSumatoria  },
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    verDetalles(row);
});

// Lotes
function verDetalles(row) {
    $('#lotes_producto').html(row.producto);
    $('#modal_lotes').modal('show');
    $('#tabla_lotes').bootstrapTable('refresh', { url: `${url}?q=ver_lotes&id_premio=${row.id_premio}`});
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
    sortName: 'id_premio',
    sortOrder: '',
    pagination: 'true',
    sidePagination: 'server',
    columns: [
        [
            { field: 'id_premio', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            // { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, },
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria, formatter: separadorMiles },
            { field: 'costo_unitario', title: 'Costo U.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'costo_total', title: 'Costo T.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'estado', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
        ]
    ]
});



$('#imprimir').click(function() {
    // var producto_        = $('#filtro_producto').val();
    // var tipo_producto_   = $('#filtro_tipo_producto').val();


    // var param = {premio : premio_};
    OpenWindowWithPost("imprimir-stock-premio", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStockpremio");

    $('#modal').modal('hide');
});