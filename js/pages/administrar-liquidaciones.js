var url = 'inc/administrar-liquidaciones-data';

function iconosFila(value, row, index) {
    let disabled = (row.estado == 3 || row.estado == 4) ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm cambiar-estado mr-1" title="Cambiar Estado" ${disabled}><i class="fas fa-sync-alt"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-liquidaciones mr-1" title="Ver liquidaciones"><i class="fas fa-list"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-dark btn-sm imprimir-recibo mr-1" title="Imprimir"><i class="fas fa-print"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir-trabajo mr-1" title="Formato del MTESS"><i class="fas fa-print"></i></button>',
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
    'click .ver-liquidaciones': function(e, value, row, index) {
        $('#modal_liquidaciones').modal('show');
        $('#toolbar_liquidaciones_text').html(`INGRESOS`);
        $('#toolbar_liquidaciones_text_des').html(`DESCUENTOS`);
        $('#tabla_liquidaciones').bootstrapTable('refresh', { url: url+'?q=ver_liquidacion_ingreso&id_liquidacion='+row.id_liquidacion });
        $('#tabla_liquidaciones_des').bootstrapTable('refresh', { url: url+'?q=ver_liquidacion_descuento&id_liquidacion='+row.id_liquidacion });
    },
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Aprobado" ? "Anulado" : "Aprobado");
        let estado = (row.estado == 1 ? 2 : 1);
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id: row.id_liquidacion, estado },
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
    },
    'click .imprimir-recibo': function (e, value, row, index) {
        var param = { id_liquidacion: row.id_liquidacion, id_funcionario: row.id_funcionario};
        OpenWindowWithPost("imprimir-liquidaciones", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirLiquidaciones", param);
    },
    'click .imprimir-trabajo': function (e, value, row, index) {
        var param = { id_liquidacion: row.id_liquidacion, id_funcionario: row.id_funcionario};
        OpenWindowWithPost("imprimir-liquidacion-trabajo", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirLiquidaciones", param);
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
    sortName: "id_liquidacion",
    sortOrder: 'desc',
    trimOnSearch: false,
    clickToSelect: true,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true, formatter: checkbox },
            { field: 'id_liquidacion', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'funcionario', title: 'Funcionario', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'periodo', title: 'Periodo', align: 'left', valign: 'middle', sortable: true },
            { field: 'neto_cobrar', title: 'Total', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
});

function checkbox(value, row, index, field) {
    return (row.estado != 0) ? { disabled: true } : value;
}
function toggleButons(value, row, index, field) {
    var selections = $('#tabla').bootstrapTable('getSelections');
    $('#btn_aprobar, #btn_anular').prop('disabled', (selections.length > 0) ? false : true);
}

$('#tabla')
.on('check.bs.table', toggleButons)
.on('uncheck.bs.table', toggleButons)
.on('check-all.bs.table', toggleButons)
.on('uncheck-all.bs.table', toggleButons)
.on('load-success.bs.table', toggleButons)
.on('mouseenter', '.info', function() {
    console.log('mouseenter');
    $(this).attr('title', 'La liquidacion debe estar en "Pendiente" para seleccionarla');
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
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
    }
});

// Cambiar estado de varias liquidaciones a la vez
$('#btn_aprobar').on('click', function() {
    cambiarEstado(1, 'Aprobado', 'Aprobar', 'primary');
});
$('#btn_anular').on('click', function() {
    cambiarEstado(2, 'Anulado', 'Anular', 'danger');
});

function cambiarEstado(estado, estado_str, text, color) {
    var selections = $('#tabla').bootstrapTable('getSelections');
    var text_solicitud = (selections.length == 1) ? 'Liquidación' : 'Liquidaciones';
    sweetAlertConfirm({
        title: `${text} ${text_solicitud}`,
        text: `¿Actualizar el estado de ${selections.length} ${text_solicitud.toLowerCase()} a '${estado_str}'?`,
        closeOnConfirm: false,
        confirmButtonColor: `var(--${color})`,
        confirmButtonText: text,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                url: url + '?q=cambiar-estados',
                type: 'post',
                data: { estado, liquidaciones: JSON.stringify(selections) },
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