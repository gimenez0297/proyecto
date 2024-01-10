var url = 'inc/productos-mas-vendidos-sucursal-data.php';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#filtros').click() });

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

$(document).ready(function () {
    // Sucursales
    $.ajax({
        dataType: 'json',
        async: true,
        cache: false,
        url: url,
        type: 'POST',
        data: { q: 'sucursales'},
        beforeSend: function() {
            NProgress.start();
        },
        success: function (json){
            NProgress.done();
            let cols = [
                { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false, switchable:false },
                { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true },
                { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
                { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'laboratorio', title: 'Laboratorio', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'origen', title: 'Origen', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'moneda', title: 'Moneda', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'principio_activo', title: 'Principio', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'clasificacion', title: 'Clasificación', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'unidad_medida', title: 'Medida', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'rubro', title: 'Rubro', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'pais', title: 'País', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'conservacion', title: 'Conservacion', align: 'left', valign: 'middle', sortable: true, visible: false },
                // { field: 'cantidad_vendida', title: 'Cantidad Vendida', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles, width:100,footerFormatter: bstFooterSumatoria},
            ]
            $.each(json, function(index, value){
                cols.push({ field: `${value.id_sucursal}`, align: 'right', valign: 'middle', title: value.sucursal, sortable: true, width: 90, formatter: separadorMiles, footerFormatter: bstFooterSumatoria});
            });
            // cols.push({ field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 100, switchable:false});
            $('#tabla').bootstrapTable("refreshOptions", { columns: [cols] });
        },
        error: function (jqXhr) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

    setTimeout(function () {
        $("#tabla").bootstrapTable('resetView');
    },100);
});

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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_sucursal=${$('#filtro_sucursal').val()}` });
});

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
        data: function (params) {
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` });
});

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

// $('#exportar').click(function() {
//     $('#modalLabel').html('Filtros para Impresión');
//     $('#formulario').find('select').val(null).trigger('change');
//     // $('#desde').val('');
//     // $('#hasta').val(fecha_actual);
//     setTimeout(function(){
//         $("#fecha_desde").focus();
//         $('#tipo_imp').val(2);
//     }, 200);
// });
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

$('#id_proveedor_pral').select2({
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
            return { q: 'proveedores_pral', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            console.log(data);
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_proveedor,
                        text: obj.proveedor
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
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

$('#sucursal').select2({
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

window.accionesFila = {
    'click .ver-sucursal': function(e, value, row, index) {

        var sucursal = $("#sucursal").val();
        var desde = $("#desde").val();
        var hasta = $("#hasta").val();

        $('#modal_detalle').modal('show');
        $('#toolbar_titulo').html(`Producto: ${row.producto}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&id_producto='+row.id_producto+'&id_sucursal='+ sucursal+'&desde='+ desde+'&hasta='+ hasta });
    }
}

function iconosFila(value, row, index) {

    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-sucursal mr-1" title="Detalles"><i class="fas fa-list"></i></button>',
    ].join('');
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    showColumns: true,
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed',
    striped: true,
    showFooter: true,
    footerStyle: bstFooterStyle,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height() - 110,
    pageSize: Math.floor(($(window).height() - 110) / 24) - 6,
    sortName: "fp.producto",
    sortOrder: 'asc',
    pageList:[10,50,100,500,1000,'All'],
    trimOnSearch: false,
});

$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle',
    showExport: false,
    search: true,
    showRefresh: true,
    showToggle: true,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 480,
    sortName: "numero",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'numero', title: 'Nro. Factura', align: 'left', valign: 'middle', sortable: true, visible: true, footerFormatter: bstFooterTextTotal },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Razón social', align: 'left', valign: 'middle', sortable: true },
            // { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            // { field: 'total', title: 'Total Venta', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

$('#imprimir').click(function() {
    var tipo                = $('#tipo').val();
    var rubro               = $('#rubro').val();
    var procedencia         = $('#procedencia').val();
    var origen              = $('#origen').val();
    var clasificacion       = $('#clasificacion').val();
    var presentacion        = $('#presentacion').val();
    var unidad_medida       = $('#unidad_medida').val();
    var marca               = $('#marca').val();
    var laboratorio         = $('#laboratorio').val();
    var id_sucursal         = $("#sucursal").val();
    var id_producto         = $('#id_producto').val();
    var id_proveedor_pral   = $('#id_proveedor_pral').val();
    var desde               = $('#desde').val();
    var hasta               = $('#hasta').val();
    var omitir_remates      = $('#omitir_remates').is(':checked') ? 1 : 0;
    var columnas            = JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').map(columna=>columna.field));

    var param = {sucursal: id_sucursal, id_producto: id_producto, desde: desde, hasta: hasta, tipo: tipo, rubro: rubro, procedencia:procedencia, origen: origen, clasificacion: clasificacion, presentacion: presentacion, unidad_medida: unidad_medida, marca: marca, laboratorio:laboratorio, omitir_remates, columnas: columnas, id_proveedor_pral: id_proveedor_pral};

    OpenWindowWithPost("imprimir-productos-mas-vendidos", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "imprimirReportesProductosMasVendidos", param);

    $('#modal').modal('hide');
});

$('#exportar').click(function() {

    var tipo                = $('#tipo').val();
    var rubro               = $('#rubro').val();
    var procedencia         = $('#procedencia').val();
    var origen              = $('#origen').val();
    var clasificacion       = $('#clasificacion').val();
    var presentacion        = $('#presentacion').val();
    var unidad_medida       = $('#unidad_medida').val();
    var marca               = $('#marca').val();
    var laboratorio         = $('#laboratorio').val();
    var id_sucursal         = $("#sucursal").val();
    var id_producto         = $('#id_producto').val();
    var id_proveedor_pral   = $('#id_proveedor_pral').val();
    var desde               = $('#desde').val();
    var hasta               = $('#hasta').val();
    var omitir_remates      = $('#omitir_remates').is(':checked') ? 1 : 0;
    var columnas            = JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').map(columna=>columna.field));
    var titulos             = JSON.stringify($('#tabla').bootstrapTable('getVisibleColumns').filter(columna=>columna.field!='acciones').map(columna=>columna.title));

    var param = {sucursal: id_sucursal, id_producto: id_producto, desde: desde, hasta: hasta, tipo: tipo, rubro: rubro, procedencia:procedencia, origen: origen, clasificacion: clasificacion, presentacion: presentacion, unidad_medida: unidad_medida, marca: marca, laboratorio:laboratorio, omitir_remates, columnas: columnas, titulos:titulos, id_proveedor_pral: id_proveedor_pral};

    window.location.replace(`exportar-productos-mas-vendidos.php?${$.param(param)}`);

    $('#modal').modal('hide');
});

$('#filtrar').click(function() {
    var tipo                = $('#tipo').val();
    var rubro               = $('#rubro').val();
    var procedencia         = $('#procedencia').val();
    var origen              = $('#origen').val();
    var clasificacion       = $('#clasificacion').val();
    var presentacion        = $('#presentacion').val();
    var unidad_medida       = $('#unidad_medida').val();
    var marca               = $('#marca').val();
    var laboratorio         = $('#laboratorio').val();
    var id_sucursal         = $('#sucursal').val();
    var id_producto         = $('#id_producto').val();
    var id_proveedor_pral   = $('#id_proveedor_pral').val();
    var desde               = $('#desde').val();
    var hasta               = $('#hasta').val();
    var omitir_remates      = $('#omitir_remates').is(':checked') ? 1 : 0;

    $('#tabla').bootstrapTable("refresh",{url: url + '?q=ver&desde=' + desde + '&hasta=' + hasta + '&producto=' + id_producto + '&sucursal=' + id_sucursal + '&laboratorio=' + laboratorio + '&marca=' + marca + '&unidad_medida=' + unidad_medida + '&presentacion=' + presentacion + '&clasificacion=' + clasificacion + '&origen=' + origen + '&procedencia=' + procedencia + '&rubro=' + rubro + '&tipo=' + tipo + '&omitir_remates=' + omitir_remates + '&id_proveedor_pral=' + id_proveedor_pral});

    $('#modal').modal('hide');
});

$('#filtro_fecha').change();
