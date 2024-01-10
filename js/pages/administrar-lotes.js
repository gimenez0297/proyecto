var url = 'inc/administrar-lotes-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#filtros').click() });

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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` })
});


// Filtros
// $('#filtro_sucursal').select2({
//     placeholder: 'Sucursal',
//     width: '200px',
//     allowClear: false,
//     selectOnClose: true,
//     ajax: {
//         url: url_listados,
//         dataType: 'json',
//         delay: 50,
//         cache: false,
//         async: true,
//         data: function(params) {
//             return { q: 'sucursales', term: params.term, page: params.page || 1 }
//         },
//         processResults: function(data, params) {
//             params.page = params.page || 1;
//             return {
//                 results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
//                 pagination: { more: (params.page * 5) <= data.total_count }
//             };
//         },
//         cache: false
//     }
// }).on('change', function() {
//     $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` })
// });

$('#filtros').click(function() {
    $('#modalLabel').html('Filtros');
    //$('#formulario').find('select').val(null).trigger('change');
    //$('#fecha_desde').val('');
    //$('#fecha_hasta').val(fecha_actual);
    setTimeout(function(){
        $("#fecha_desde").focus();
        $('#tipo_imp').val(1);
    }, 200);
});

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
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-productos mr-1" title="Ver Productos"><i class="fas fa-list"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .ver-productos': function(e, value, row, index) {
        $('#modal_detalle').modal('show');
        $('#toolbar_titulo').html(`${row.lote}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_productos&id_lote='+row.id_lote});
    }
}

$("#tabla").bootstrapTable({
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
    sortName: "id_lote",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_lote', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'codigo', title: 'Codigo', align: 'left', valign: 'middle', sortable: true, },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'laboratorio', title: 'Laboratorio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true },
            { field: 'origen', title: 'Origen', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'principio_activo', title: 'Principio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'clasificacion', title: 'Clasificación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'unidad_medida', title: 'Medida', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'rubro', title: 'Rubro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'vencimiento_lote', title: 'Vto. Lote', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'estado_lote', title: 'Estado Lote', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
            { field: 'canje_str', title: 'Canje', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'vencimiento_canje', title: 'Vto. Canje', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
            { field: 'estado_canje', title: 'Estado Canje', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
            { field: 'entero', title: 'Entero', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'fracc', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'usuario', title: 'Usuario Carga', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false  },
            { field: 'fecha', title: 'Fecha Carga', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false  },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 80, switchable:false }
        ]
    ]
});

$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle',
    showExport: false,
    search: true,
    showRefresh: false,
    showToggle: false,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 480,
    sortName: "sucursal",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_lote', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'entero', title: 'Entero', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'fraccionado', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'precio', title: 'PRECIO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'total_venta', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

$("#imprimir").on("click", function (e) {
    var tipo            = $('#tipo').val();
    var rubro           = $('#rubro').val();
    var procedencia     = $('#procedencia').val();
    var origen          = $('#origen').val();
    var clasificacion   = $('#clasificacion').val();
    var presentacion    = $('#presentacion').val();
    var unidad_medida   = $('#unidad_medida').val();
    var marca           = $('#marca').val();
    var laboratorio     = $('#laboratorio').val();
    var id_sucursal     = $('#deposito').val();
    var id_producto     = $('#id_producto').val();
    var desde           = $('#desde').val();
    var hasta           = $('#hasta').val();
    var columnas        = JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').map(columna=>columna.field));

    var param = {sucursal: id_sucursal, id_producto: id_producto, desde: desde, hasta: hasta, tipo: tipo, rubro: rubro, procedencia:procedencia, origen: origen, clasificacion: clasificacion, presentacion: presentacion, 
                unidad_medida: unidad_medida, marca: marca, laboratorio:laboratorio, columnas: columnas};

    // if($("#tipo_imp").val() == 1){
        OpenWindowWithPost("imprimir-productos-proximos-vencer", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "imprimirReportesProductosMasVendidos", param);
    // }else{
        // window.location.replace(`exportar-reporte-productos-mas-vendidos.php?${$.param(param)}`);
    // }

    $('#modal').modal('hide');
});

$("#exportar").on("click", function (e) {
    var tipo            = $('#tipo').val();
    var rubro           = $('#rubro').val();
    var procedencia     = $('#procedencia').val();
    var origen          = $('#origen').val();
    var clasificacion   = $('#clasificacion').val();
    var presentacion    = $('#presentacion').val();
    var unidad_medida   = $('#unidad_medida').val();
    var marca           = $('#marca').val();
    var laboratorio     = $('#laboratorio').val();
    var id_sucursal     = $('#deposito').val();
    var id_producto     = $('#id_producto').val();
    var desde           = $('#desde').val();
    var hasta           = $('#hasta').val();
    var columnas        = JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').map(columna=>columna.field));
    var titulos         = JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').filter(columna=>columna.field!='acciones').map(columna=>columna.title));


    var param = {id_producto: id_producto, desde: desde, hasta: hasta, tipo: tipo, rubro: rubro, procedencia:procedencia, origen: origen, clasificacion: clasificacion, presentacion: presentacion, 
                unidad_medida: unidad_medida, marca: marca, laboratorio:laboratorio, columnas: columnas, titulos:titulos};

    // if($("#tipo_imp").val() == 1){
       // OpenWindowWithPost("imprimir-reporte-productos-mas-vendidos", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "imprimirReportesProductosMasVendidos", param);
    // }else{
        window.location.replace(`exportar-productos-proximos-vencer.php?${$.param(param)}`);
    // }

    $('#modal').modal('hide');
});

$('#filtrar').click(function() {
    var tipo            = $('#tipo').val();
    var rubro           = $('#rubro').val();
    var procedencia     = $('#procedencia').val();
    var origen          = $('#origen').val();
    var clasificacion   = $('#clasificacion').val();
    var presentacion    = $('#presentacion').val();
    var unidad_medida   = $('#unidad_medida').val();
    var marca           = $('#marca').val();
    var laboratorio     = $('#laboratorio').val();
    var id_producto     = $('#id_producto').val();
    var desde           = $('#desde').val();
    var hasta           = $('#hasta').val();

    $('#tabla').bootstrapTable("refresh",{url: url + '?q=ver&desde=' + desde + '&hasta=' + hasta + '&producto=' + id_producto + '&sucursal=' +  laboratorio + '&marca=' + marca + '&unidad_medida=' + unidad_medida + '&presentacion=' + presentacion + '&clasificacion=' + clasificacion + '&origen=' + origen + '&procedencia=' + procedencia + '&rubro=' + rubro + '&tipo=' + tipo});

    $('#modal').modal('hide');
});

$('#filtro_fecha').change();