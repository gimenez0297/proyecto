var url = 'inc/reporte-puntos-clientes-data';
var url_listados = 'inc/listados';
var url_clientes = 'inc/clientes-data';

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
    $('#estado').trigger('change');
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
cb(moment().startOf('month'), moment().endOf('month'));

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
$('#filtro_sucursal').select2({
    placeholder: 'Sucursal',
    width: '200px',
    allowClear: false,
    selectOnClose: true,
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Ver Detalle"><i class="fas fa-list"></i></button>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        $('#modal').modal('show');
        $('#toolbar_titulo').html(`${row.razon_social}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: `${url}?q=ver_detalle&id_cliente=${row.id_cliente}&estado=${$('#estado').val()}` });
    }
}




$("#tabla").bootstrapTable({
    url: url_clientes + '?q=ver',
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
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/30),
    sortName: "puntos",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_cliente', title: 'ID Cliente', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'razon_social', title: 'Nombre / Razón Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'telefono', title: 'Teléfono', align: 'left', valign: 'middle', sortable: true },
            { field: 'celular',  title: 'Celular',align: 'left', valign: 'middle', sortable: true },
            { field: 'direccion', title: 'Dirección', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'puntos', title: 'Puntos', align: 'right', valign: 'middle', sortable: true, visible: true, formatter: separadorMiles  },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila }
        ]
    ]
});

$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle',
    showExport: false,
    search: true,
    showRefresh: true,
    showToggle: false,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 480,
    sortName: "id_cliente_punto",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_cliente_punto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N° Factura', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'puntos', title: 'Puntos', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            { field: 'utilizados', title: 'Utilizados', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

$('#estado').select2({
    placeholder: 'Estado',
    width: '200px',
    allowClear: true,
    selectOnClose: false,
}).on('change', function(){
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&estado=${$('#estado').val()}` })
    let data = $(this).select2('data')[0];
    if ($(this).val() == 2) {
        $('#btn_imprimir').prop('disabled', false);
    } else {
        $('#btn_imprimir').prop('disabled', true);
    }
});

$('#btn_imprimir').on('click', function() {
    var param = { 
        estado: $('#estado').val() 
    } ;
    OpenWindowWithPost("imprimir-reportes-puntos-clientes", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStock", param);
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
    var id_sucursal = $('#filtro_sucursal').val();
    var estado = $('#estado').val();
                      
    var param = {id_sucursal: id_sucursal, tipo: tipo, rubro: rubro, procedencia:procedencia, origen: origen, clasificacion: clasificacion, presentacion: presentacion, 
                unidad_medida: unidad_medida, marca: marca, laboratorio:laboratorio, estado:estado };
    OpenWindowWithPost("imprimir-reportes-puntos-clientes.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStock", param);

    $('#modal').modal('hide');
});

$('#filtro_fecha').change();
