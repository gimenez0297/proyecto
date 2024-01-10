var url = 'inc/stock-data';
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
        // F3 Exportar todos los productos a excel
        case 114:
            event.preventDefault();
            if ($('#btn_exportar').attr('disabled') !== 'disabled') {
                $('#btn_exportar').click();
            }
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
    filtrarTabla();
    
    let data = $(this).select2('data')[0];
    if (data) {
        $('#btn_imprimir').prop('disabled', false);
    } else {
        $('#btn_imprimir').prop('disabled', true);
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
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-lotes mr-1" title="Ver Lotes"><i class="fas fa-list"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .ver-lotes': function(e, value, row, index) {
        verLotes(row);
    }
}

function queryParams(params){
    params.proveedor = $('#proveedor').val();
    params.tipo = $('#tipo').val();
    params.rubro = $('#rubro').val();
    params.procedencia = $('#procedencia').val();
    params.origen = $('#origen').val();
    params.clasificacion = $('#clasificacion').val();
    params.presentacion = $('#presentacion').val();
    params.unidad_medida = $('#unidad_medida').val();
    params.marca = $('#marca').val();
    params.laboratorio = $('#laboratorio').val();
    params.id_sucursal = $('#filtro_sucursal').val();
    params.estado = $('#estado').val();
    return params;
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    queryParams: queryParams,
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
            { field: 'vencimiento_lote', title: 'Vto. Lote', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'vencimiento_canje', title: 'Vto. Canje', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna  },
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'fraccionado', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'cantidad_fracciones', title: 'Fraccionable En', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 100 }
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
            { field: 'stock', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria },
            { field: 'fraccionado', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria },
            { field: 'estado_canje', title: 'Estado Canje', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
            { field: 'estado_lote', title: 'Estado Lote', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
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
    filtrarTabla();
    let data = $(this).select2('data')[0];
    if (data) {
        $('#btn_imprimir').prop('disabled', false);
    } else {
        $('#btn_imprimir').prop('disabled', true);
    }
});



$('#btn_imprimir').on('click', function() {
    let param = obtenerFiltros();
    OpenWindowWithPost("imprimir-stock", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStock", param);
});

$('#filtros_impresion').click(function() {
    $('#modalLabel').html('Filtros');
    setTimeout(function(){
        $("#tipo").focus();
    }, 200);
});

$('#proveedor').select2({
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
            return { q: 'proveedores', term: params.term, page: params.page || 1, tipo_proveedor: 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_proveedor, text: obj.proveedor }; }),
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

$('#filtrar').click(function() {
    filtrarTabla();
    $('#modal').modal('hide');
});

async function exportt(params) {
    return new Promise(resolve => {
      const ventanaDescarga = window.open('exportar-stock.php?'+$.param(params),  '_blank');
      ventanaDescarga.document.title = "Creando archivo...";
      const temporizador = setInterval(() => {
        if (ventanaDescarga.closed) {
          clearInterval(temporizador);
          resolve(true);
        }
      }, 1000); // Comprueba si la ventana se ha cerrado cada segundo
    });
}

$('#btn_exportar').click(async function(e) {
    e.preventDefault();
    let param = obtenerFiltros();

    NProgress.start();
    loading({text: 'Creando Archivo...'});
    $(this).prop('disabled', true).addClass('disabled');

    const resultado = await exportt(param);
    
    NProgress.done();
    Swal.close();
    if (resultado) {
        alertToast("Archivo Creado", "ok", 2000)
    }else{
        alertToast("Ocurri'o un error inesperado", "error", 4000)
    }
    $(this).removeAttr('disabled').removeClass('disabled');
});

function filtrarTabla() {
    let param = obtenerFiltros();
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver`, query: param });
}

function obtenerFiltros() {
    return {
        proveedor       : $('#proveedor').val(),
        tipo            : $('#tipo').val(),
        rubro           : $('#rubro').val(),
        procedencia     : $('#procedencia').val(),
        origen          : $('#origen').val(),
        clasificacion   : $('#clasificacion').val(),
        presentacion    : $('#presentacion').val(),
        unidad_medida   : $('#unidad_medida').val(),
        marca           : $('#marca').val(),
        laboratorio     : $('#laboratorio').val(),
        id_sucursal     : $('#filtro_sucursal').val(),
        estado          : $('#estado').val()
    }
}
