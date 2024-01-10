var url = 'inc/solicitudes-depositos-data';

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

    $('#filtro_fecha').change();

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
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val() });
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    // Procesado parcial o Total
    let disabled = (row.estado == 3 || row.estado == 4) ? 'disabled' : '';

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm cambiar-estado mr-1" title="Cambiar Estado" ${disabled}><i class="fas fa-sync-alt"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Ver Productos"><i class="fas fa-list"></i></button>',
        `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        ver_detalle(row);
    },
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Aprobado" ? "Rechazado" : "Aprobado");
        let estado = (row.estado == 1) ? 2 : 1;
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id: row.id_solicitud_deposito, estado },
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
    'click .imprimir': function (e, value, row, index) {
        imprimirSolicitud(row);
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
    sortName: "numero",
    sortOrder: 'desc',
    trimOnSearch: false,
    clickToSelect: true,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true, formatter: checkbox },
            { field: 'id_solicitud_deposito', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'sucursal', title: 'Sucursal', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'proveedor', title: 'Proveedor', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'deposito', title: 'Depósito', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'observacion', title: 'Observación', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'usuario', title: 'Usuario', ali: 'left', valign: 'middle', sortable: true },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, width: 200 },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 150 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 150 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    ver_detalle(row);
});

function checkbox(value, row, index, field) {
    return (row.estado != 0) ? { disabled: true } : value;
}
function toggleButons(value, row, index, field) {
    var selections = $('#tabla').bootstrapTable('getSelections');
    $('.cambiar-estados').prop('disabled', (selections.length > 0) ? false : true);
}

$('#tabla')
.on('check.bs.table', toggleButons)
.on('uncheck.bs.table', toggleButons)
.on('check-all.bs.table', toggleButons)
.on('uncheck-all.bs.table', toggleButons)
.on('load-success.bs.table', toggleButons);

// Cambiar estado de varias solicitudes a la vez
$('.cambiar-estados').on('click', function() {
    var selections = $('#tabla').bootstrapTable('getSelections');
    var text = (selections.length == 1) ? 'Solicitud' : 'Solicitudes';
    let estado = $(this).val();
    let estado_str = '', text_estado = '', color = '';

    switch (estado) {
        case '1':
            estado_str = 'Aprobado';
            text_estado = 'Aprobar';
            color = 'primary';
            break;
        case '2':
            estado_str = 'Rechazado';
            text_estado = 'Rechazar';
            color = 'danger';
            break;
        default:
            return;
    }

    sweetAlertConfirm({
        title: `${text_estado} ${text}`,
        text: `¿Actualizar el estado de ${selections.length} ${text.toLowerCase()} a '${estado_str}'?`,
        closeOnConfirm: false,
        confirmButtonColor: `var(--${color})`,
        confirmButtonText: text_estado,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                url: url + '?q=cambiar-estados',
                type: 'post',
                data: { estado, rows: JSON.stringify(selections) },
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
});

// Detalles
$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle',
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
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_producto', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'transito', title: 'TRANSITO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'recibido', title: 'RECIBIDO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'pendiente', title: 'PENDIENTE', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

function ver_detalle(row) {
    $('#modal_detalle').modal('show');
    $('#toolbar_detalle_text').html(`Solicitud N° ${row.numero}`);
    $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&id='+row.id_solicitud_deposito });
}

$('#tipo').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
});
$('#orden').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
});

//recoje los datos del formulario y de la tabla
function imprimirSolicitud(row){ 
    $('#modalImprension').modal('show');
    $('#id_solicitud_deposito').val(row.id_solicitud_deposito);
    
}
//recoje los datos por medio de los id y asigna a un objeto
function retornaDatos(){
    var tipo = $('#tipo').val();
    var orden = $('#orden').val();
    var id_solicitud_deposito = $('#id_solicitud_deposito').val();

    var param = { 
        tipo: tipo,
        orden: orden,
        id_solicitud_deposito: id_solicitud_deposito
    };

    return param;     
}

$('#btn_imprimir').on('click',function(e) {    
    e.preventDefault(); 
    
    $('#modalImprension').modal('hide');

    let data = retornaDatos();
    
    OpenWindowWithPost("imprimir-solicitud-deposito.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirSolicitudDeposito", data );
    
});