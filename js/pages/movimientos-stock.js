var url = 'inc/movimientos-stock-data';
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
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#filtros_impresion').click();
            break;
        // F2 Imprimir
        case 113:
            event.preventDefault();
            $('#imprimir').click();
            break;
    }
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
    let filtro_sucur = '';
    if($('#deposito_str').val() != null){
        filtro_sucur = $('#deposito_str').val();
    }else{
        filtro_sucur = '';
    }
    $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&id_producto='+$('#produc_click').val()+'&fecha_desde='+$('#desde').val()+'&fecha_hasta='+$('#hasta').val()+'&id_sucursal='+filtro_sucur });
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 29);
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-lotes mr-1" title="Ver Lotes"><i class="fas fa-list"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .ver-lotes': function(e, value, row, index) {
        verDetalle(row);
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
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true},
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'fraccionado', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: true, events: accionesFila,  formatter: iconosFila, width: 100, visible: true }
        ]
    ]
});

// Lotes
function verDetalle(row) {
    let filtro_sucursal = '';
    let filtro_depositoid = '';
    let filtro_depositotext = '';
    if($('#deposito').val() != null){
        let filtro_deposito = $('#deposito').select2('data');
        filtro_depositoid = filtro_deposito[0].id;
        filtro_depositotext = filtro_deposito[0].text;
        $('#deposito_str').select2('trigger', 'select', {
            data: { id: filtro_depositoid, text: filtro_depositotext }
        });
    }
    $('#produc_click').val(row.id_producto);
    $('#producto_detalle').html(row.producto);
    if($('#deposito_str').val() != null){
        filtro_sucursal = $('#deposito_str').val();
    }else{
        filtro_sucursal = '';
    }
    $('#modal_detalle').modal('show');
    $('#tabla_detalle').bootstrapTable('refresh', { url: `${url}?q=ver_detalle&id_producto=${$('#produc_click').val()}&id_sucursal=${filtro_sucursal}&fecha_desde=${$('#desde').val()}&fecha_hasta=${$('#hasta').val()}` });
}

$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle',
    showExport: true,
    search: false,
    showRefresh: true,
    showToggle: true,
    classes: 'table table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: false,
    height: 480,
    sortName: "sh.id_stock_historial",
    sortOrder: 'desc',
    pagination: 'true',
    sidePagination: 'server',
    columns: [
        [
            { field: 'id_stock_historial', title: 'ID Stock Histo.', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_mov', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'detalles', title: 'Documento', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'tipo_str', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'sucursal', title: 'Depósito', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'entrada', title: 'Cant. / Ent.', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'salida', title: 'Cant. / Sal.', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'entrada_frac', title: 'Frac. / Ent.', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'salida_frac', title: 'Frac. / Sal.', align: 'left', valign: 'middle', sortable: true, visible: true }
        ]
    ]
});

function formatterCanje(value, row, index, field) {
    return (value == 1) ? 'Si' : 'No';
}

$('#filtros_impresion').click(function() {
    $('#modalLabel').html('Filtros');
});

// BUSQUEDA DE PRODUCTOS
function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_presentacion) {
        $result = $(`<span>${node.text} <small>(${node.presentacion})</small></span>`);
    }
    return $result;
};

$('#id_producto').select2({
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
            return { q: 'productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_producto,
                        text: obj.producto,
                        codigo: obj.codigo,
                        id_presentacion: obj.id_presentacion,
                        presentacion: obj.presentacion
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    },
    templateResult: formatResult,
    templateSelection: formatResult
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

$('#deposito').select2({
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
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
    }
});

$('#deposito_str').select2({
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
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
    }
}).on('change', function(){
    let filtro_sucursal_str = '';
    if($('#deposito_str').val() != null){
        filtro_sucursal_str = $('#deposito_str').val();
        $('#imprimir').prop('disabled', false);
    }else{
        filtro_sucursal_str = '';
        $('#imprimir').prop('disabled', true);
    }
    $('#tabla_detalle').bootstrapTable('refresh', { url: `${url}?q=ver_detalle&id_producto=${$('#produc_click').val()}&id_sucursal=${filtro_sucursal_str}&fecha_desde=${$('#desde').val()}&fecha_hasta=${$('#hasta').val()}` });
});

$('#imprimir').click(function() {
    var tipo        = $('#tipo').val();
    var rubro       = $('#rubro').val();
    var procedencia = $('#procedencia').val();
    var origen      = $('#origen').val();
    var clasificacion = $('#clasificacion').val();
    var presentacion = $('#presentacion').val();
    var unidad_medida = $('#unidad_medida').val();
    var marca       = $('#marca').val();
    var laboratorio = $('#laboratorio').val();
    var id_sucursal = $('#deposito_str').val();
    var id_producto = $('#produc_click').val();
    var desde = $('#desde').val();
    var hasta = $('#hasta').val();

    var param = {id_sucursal: id_sucursal, id_producto: id_producto, desde: desde, hasta: hasta, tipo: tipo, rubro: rubro, procedencia:procedencia, origen: origen, clasificacion: clasificacion, presentacion: presentacion, 
                unidad_medida: unidad_medida, marca: marca, laboratorio:laboratorio};
    OpenWindowWithPost("imprimir-movimiento-stock", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStock", param);

});

$('#filtrar').click(function() {
    let filtro_sucursal = '';
    let filtro_producto = '';
    let filtro_tipo = '';
    let filtro_rubro = '';
    let filtro_procedencia = '';
    let filtro_origen = '';
    let filtro_clasificacion = '';
    let filtro_presentacion = '';
    let filtro_medida = '';
    let filtro_marca = '';
    let filtro_laboratorio = '';
    if($('#deposito').val() != null){
        filtro_sucursal = $('#deposito').val();
    }else{
        filtro_sucursal = '';
    }
    if($('#tipo').val() != null){
        filtro_tipo = $('#tipo').val();
    }else{
        filtro_tipo = '';
    }
    if($('#rubro').val() != null){
        filtro_rubro = $('#rubro').val();
    }else{
        filtro_rubro = '';
    }
    if($('#procedencia').val() != null){
        filtro_procedencia = $('#procedencia').val();
    }else{
        filtro_procedencia = '';
    }
    if($('#origen').val() != null){
        filtro_origen = $('#origen').val();
    }else{
        filtro_origen = '';
    }
    if($('#clasificacion').val() != null){
        filtro_clasificacion = $('#clasificacion').val();
    }else{
        filtro_clasificacion = '';
    }
    if($('#presentacion').val() != null){
        filtro_presentacion = $('#presentacion').val();
    }else{
        filtro_presentacion = '';
    }
    if($('#unidad_medida').val() != null){
        filtro_medida = $('#unidad_medida').val();
    }else{
        filtro_medida = '';
    }
    if($('#marca').val() != null){
        filtro_marca = $('#marca').val();
    }else{
        filtro_marca = '';
    }
    if($('#laboratorio').val() != null){
        filtro_laboratorio = $('#laboratorio').val();
    }else{
        filtro_laboratorio = '';
    }
    if($('#id_producto').val() != null){
        filtro_producto = $('#id_producto').val();
    }else{
        filtro_producto = '';
    }
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#deposito').val()}&tipo=${filtro_tipo}&rubro=${filtro_rubro}&procedencia=${filtro_procedencia}&origen=${filtro_origen}&clasificacion=${filtro_clasificacion}&presentacion=${filtro_presentacion}&unidad_medida=${filtro_medida}&marca=${filtro_marca}&laboratorio=${filtro_laboratorio}&id_producto=${filtro_producto}` });
    $('#modal').modal('hide');
});

