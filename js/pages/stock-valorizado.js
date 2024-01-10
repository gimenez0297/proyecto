var url = 'inc/stock-valorizado-data';
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
    $('#estado').trigger('change');
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
    refreshTabla();
                    
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-lotes mr-1" title="Ver Lotes"><i class="fas fa-list"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .ver-lotes': function(e, value, row, index) {
        verLotes(row);
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
    sortName: "codigo",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_stock', title: 'ID Stock', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'total_entero', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, width: 100,  formatter: iconosFila }
            // { field: 'costo_lote', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'fraccionado', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'precio_fraccionado', title: 'Precio', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'total_fracc', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    verLotes(row);
});

// Lotes
function verLotes(row) {
    $('#lotes_producto').html(row.producto);
    $('#modal_lotes').modal('show');
    $('#tabla_lotes').bootstrapTable('refresh', { url: `${url}?q=ver_lotes&id_producto=${row.id_producto}&estado=${$('#estado').val()}&id_sucursal=${$('#filtro_sucursal').val()}` });
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
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_lote', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'vencimiento_lote', title: 'Vencimiento de Lote', align: 'left', valign: 'middle', sortable: true},
            { field: 'vencimiento_canje', title: 'Vencimiento De Canje', align: 'left', valign: 'middle', sortable: true },
            { field: 'canje_str', title: 'Canje', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'fraccionado', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },            
            { field: 'total_general', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'estado_canje', title: 'Estado Canje', align: 'center', valign: 'middle', sortable: true, formatter: separadorMiles, formatter: bstFormatterEstado  },
            { field: 'estado_lote', title: 'Estado Lote', align: 'center', valign: 'middle', sortable: true, formatter: separadorMiles, formatter: bstFormatterEstado  },
        ]
    ]
});

function formatterCanje(value, row, index, field) {
    return (value == 1) ? 'Si' : 'No';
}

$('#estado').select2({
    placeholder: 'Estado',
    width: '200px',
    allowClear: true,
    selectOnClose: false,
}).on('change', function(){
    refreshTabla();
    let data = $(this).select2('data')[0];
    if (data) {
        $('#btn_imprimir').prop('disabled', false);
    } else {
        $('#btn_imprimir').prop('disabled', true);
    }
});


$('#filtros_impresion').click(function() {
    $('#modalLabel').html('Filtros para Impresión');
    // $('#formulario').find('select').val(null).trigger('change');
    setTimeout(function(){
        $("#tipo").focus();
    }, 200);
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

$('#filtrar').click(function() {
    refreshTabla();
    $('#modal').modal('hide');
});
    $('#btn_imprimir').on('click', function() {
       let param = retornaDatos();
        OpenWindowWithPost("imprimir-stock-valorizado", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStock", param);
    }); 

    function refreshTabla(){
        //introducimos en una variables para poder usar en el refresh

        let data = retornaDatos();

        $('#tabla').bootstrapTable("refresh",{url: url + '?q=ver',query: data }); 

    }
    
    //Tomas los datos de los id y retorna en una variable

    function retornaDatos (){
        var tipo                     = $('#tipo').val();
        var rubro                  = $('#rubro').val();
        var procedencia       = $('#procedencia').val();
        var origen                = $('#origen').val();
        var clasificacion       = $('#clasificacion').val();
        var presentacion     = $('#presentacion').val();
        var unidad_medida = $('#unidad_medida').val();
        var marca                = $('#marca').val();
        var laboratorio        = $('#laboratorio').val();
        var id_sucursal        = $('#filtro_sucursal').val();
        var estado               = $('#estado').val();

        var param = {id_sucursal: id_sucursal, 
            tipo: tipo, 
            rubro: rubro, 
            procedencia:procedencia, 
            origen: origen, 
            clasificacion: clasificacion, 
            presentacion: presentacion, 
            unidad_medida: unidad_medida, 
            marca: marca, 
            laboratorio:laboratorio, 
            estado:estado };

            return param;
    }
