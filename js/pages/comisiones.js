var url = 'inc/comisiones-data';
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
$(window).on('keydown', function(event) {
    switch (event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#imprimir').click();
            break;
            // F2 Buscar RUC / CI
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&periodo=${$('#periodo').val()}` })
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
        // `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm ver-detalle mr-1" title="Detalles"><i class="fas fa-list"></i>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        $('#modal').modal('show');
        $('#toolbar_titulo').html(`${row.vendedor}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&funcionario='+row.vendedor+'&periodo='+row.periodo });
    },
    'click .imprimir': function (e, value, row, index) {
        var param = { id_extraccion: row.id_extraccion, imprimir : 'si', recargar: 'no' };
        OpenWindowWithPost("imprimir-ticket-extraccion", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirTicket", param);
        //resetWindow();
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
    sortName: "id_factura",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_factura', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'vendedor', title: 'Vendedor', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'periodo', title: 'Periodo', align: 'left', valign: 'middle', sortable: true },
            { field: 'total_venta', title: 'Total Venta', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            // { field: 'tot_3', title: 'Productos 3%', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            // { field: 'tot_5', title: 'Productos 5%', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            // { field: 'sin_comi', title: 'Productos S/C.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            // { field: 'com3', title: '3 %', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            // { field: 'com5', title: '5 %', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'monto_comision', title: 'Total Com.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 80 }
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
    sortName: "id_funcionario",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_funcionario', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'comision_concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'comision', title: 'Comisión', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'monto_comision', title: 'Total Productos', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            { field: 'porcentaje_com', title: 'Monto Comisión', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

$('#periodo').select2({
    placeholder: 'Periodo',
    width: '200px',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'periodo', term: params.term, page: params.page || 1 }
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

$("#imprimir").on("click", function (e) {

    if($('#periodo').val() == null){
        alertDismissJS('No ha seleccionado el periodo. Favor verifique.', 'error',()=>$("#periodo").focus());
        return;
    }else{
        var param = {
            sucursal       : $('#filtro_sucursal').val(),
            periodo       : $('#periodo').val(),
        };
        window.location.replace(`exportar-comision.php?${$.param(param)}`);
        }
  });

$('#filtro_fecha').change();