var url = 'inc/administrar-canjes-premios-data';

function iconosFila(value, row, index) {
    // Anulado
    let disabled = (row.estado == 2) ? 'disabled' : '';

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm cambiar-estado mr-1" title="Anular" ${disabled}><i class="fas fa-times"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-productos mr-1" title="Ver Productos"><i class="fas fa-box"></i></button>',
    ].join('');
}

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
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val() });
});

window.accionesFila = {
    'click .ver-productos': function(e, value, row, index) {
        $('#modal_productos').modal('show');
        $('#toolbar_productos_text').html(`N° ${row.numero}`);
        $('#tabla_productos').bootstrapTable('refresh', { url: url+'?q=ver_productos&id_canje_punto='+row.id_canje_punto });
    },
    'click .cambiar-estado': function(e, value, row, index) {
        let id = row.numero;       
        let estado = 2;
        sweetAlertConfirm({
            title: `Anular`,
            text: `¿Anular carga  N° '${id}'?`,
            closeOnConfirm: false,
            confirmButtonText: 'Anular',
            confirmButtonColor: 'var(--danger)',
            confirm: function() { 
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id: row.id_canje_punto, estado },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function(jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
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
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_canje_punto",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_canje_punto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'center', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Razon Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'Ruc', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'puntos', title: 'Puntos', align: 'right', valign: 'middle', sortable: true , formatter: separadorMiles },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true , formatter: separadorMiles },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true,   width: 200,  formatter: bstFormatterEstado },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, width: 200 ,  formatter: iconosFila }
        ]
    ]
});

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: $(window).height()-120,
                pageSize: Math.floor(($(window).height()-120)/50),
            });
        }, 100);
    });
    //Actualiza los estados de la tabla clientes puntos en comparacion al periodo_canje establecida en plan puntos
    actualiza_estado_puntos_cliente();

});

// Productos
$("#tabla_productos").bootstrapTable({
    toolbar: '#toolbar_productos',
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
    sortName: "codigo",
    sortOrder: 'asc',
    uniqueId: 'id_canje_punto_premio',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_canje_punto_premio', title: 'ID PREMIO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_canje_punto', title: 'ID CARGA', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_premio', title: 'Premio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'premio', title: 'Premio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'puntos', title: 'Puntos', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

function resetWindow() {
    actualiza_estado_puntos_cliente();
    actualizarNotificaciones();
}


$('#filtro_fecha').change();