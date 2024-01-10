var url = 'inc/administrar-pagos-data';
var id_orden_pago_archivo;

function iconosFila(value, row, index) {
    // Pendiente y Aprobado, los demas estado disabled
    let disabled = (row.estado != 2) ? '' : 'disabled';

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm cambiar-estado mr-1" title="Cambiar Estado" ${disabled}><i class="fas fa-sync-alt"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalles mr-1" title="Ver detalles"><i class="fas fa-list"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm ver-archivos mr-1" title="Ver Archivos"><i class="fas fa-file-invoice"></i></button>',
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` });
});

window.accionesFila = {
    'click .ver-detalles': function(e, value, row, index) {
        if(row.id_proveedor){
            $('#modal_detalles').modal('show');
            $('#toolbar_detalles_text').html(`Orden de Pago N° ${row.numero}`);
            $('#tabla_detalles').bootstrapTable('refresh', { url: url+'?q=ver_detalles&id_pago='+row.id_pago });
        }else if (row.id_proveedor_gasto){
            $('#modal_detalles').modal('show');
            $('#toolbar_detalles_text').html(`Orden de Pago N° ${row.numero}`);
            $('#tabla_detalles').bootstrapTable('refresh', { url: url+'?q=ver_detalles_gasto&id_pago='+row.id_pago });
        }else if (row.id_funcionario){
            $('#modal_liquidacion').modal('show');
            $('#toolbar_liquidaciones_text').html(`Orden de Pago N° ${row.numero}`);
            $('#tabla_liquidaciones').bootstrapTable('refresh', { url: url+'?q=ver_detalles_liquidacion&id_pago='+row.id_pago });
        }else if (row.id_caja_chica_sucursal){
            $('#modal_detalles_caja_chica').modal('show');
            $('#toolbar_detalles_caja_chica_text').html(`Orden de Pago N° ${row.numero}`);
            $('#tabla_detalles_caja_chica').bootstrapTable('refresh', { url: url+'?q=ver_detalles_caja_chica&id_pago='+row.id_pago });
        }
    },
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Pagado" ? "Anulado" : "Pagado");
        let estado = (row.estado == 3 ? 2 : 3);

        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id: row.id_pago, estado, destino: row.destino_pago },
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
        var param = { id_pago: row.id_pago};
        OpenWindowWithPost("imprimir-pagos", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirPagos", param);
    },
    'click .ver-archivos': function(e, value, row, index) {
        id_orden_pago_archivo=row.id_pago;
        $('#id_pago').val(row.id_pago);

        $('#modal_archivos').modal('show');
        $('#toolbar_archivos_text').html(`Orden de Pago N° ${row.numero}`);
        $('#tabla_archivos').bootstrapTable('refresh', { url: url+'?q=ver_archivos&id_orden_pago='+row.id_pago });
        $('#tipo').trigger('change');
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
    sortName: "id_pago",
    sortOrder: 'desc',
    trimOnSearch: false,
    clickToSelect: true,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true, formatter: checkbox },
            { field: 'id_pago', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc_ci', title: 'R.U.C / C.I.', align: 'right', valign: 'middle', sortable: true, width: 80 },
            { field: 'beneficiario', title: 'Beneficiario', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'Total', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'fecha_pago', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 150 },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_carga', title: 'Fecha Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'forma_', title: 'Forma Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'nro_forma', title: 'Nro. Cuenta / Nro. Cheque', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
});

function iconosFilaDocumento(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm ver_doc mr-1" title="Ver"><i class="fas fa-file-pdf text-white"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_doc" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaDocumento = {
    'click .eliminar_doc': function(e, value, row, index) {
        let id = row.id;
        sweetAlertConfirm({
            title: `¿Eliminar el archivo?`,
            text: 'Esta acción no se puede revertir',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_archivo',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_archivos').bootstrapTable('refresh');
                        } else {
                            alertDismissJS(data.mensaje, data.status);
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
    'click .ver_doc': function (e, value, row, index) {
        window.open(row.archivo);
    }
}

$("#archivos").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_documento").val(cleanFileName);
});

$("#tabla_archivos").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 300,
    sortName: "archivo",
    sortOrder: 'asc',
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo_documento', title: 'tipo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'documento', title: 'Documento', align: 'left', valign: 'middle', sortable: true },
            { field: 'nro_documento', title: 'Nro. Documento', align: 'left', valign: 'middle', sortable: true },
            { field: 'archivo', title: 'Blob', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaDocumento,  formatter: iconosFilaDocumento, width: 150 }
        ]
    ]
});

function checkbox(value, row, index, field) {
    return (row.estado != 1) ? { disabled: true } : value;
}
function toggleButons(value, row, index, field) {
    var selections = $('#tabla').bootstrapTable('getSelections');
    $('#btn_pagar, #btn_anular').prop('disabled', (selections.length > 0) ? false : true);
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$('#agregar-archivos').on('click', () => $('#formulario_documento').submit());
$("#formulario_documento").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);

    $.ajax({
        url: url + '?q=cargar_archivo',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        processData: false,
        contentType: false,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            if (data.status == 'ok') {
                $('#tabla_archivos').bootstrapTable('refresh');
                $('#nro_documento').val('');
                $('#archivos').next('.custom-file-label').html('Seleccionar Archivo');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});


$('#tabla')
.on('check.bs.table', toggleButons)
.on('uncheck.bs.table', toggleButons)
.on('check-all.bs.table', toggleButons)
.on('uncheck-all.bs.table', toggleButons)
.on('load-success.bs.table', toggleButons);

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

// Cambiar estado de varias solicitudes a la vez
$('#btn_pagar').on('click', function() {
    cambiarEstado(3, 'Pagado', 'Pagar', 'primary');
});
$('#btn_anular').on('click', function() {
    cambiarEstado(2, 'Anulado', 'Anular', 'danger');
});

function cambiarEstado(estado, estado_str, text, color) {
    var selections = $('#tabla').bootstrapTable('getSelections');
    var text_solicitud = (selections.length == 1) ? 'Orden' : 'Ordenes';
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
                data: { estado, ordenes: JSON.stringify(selections) },
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

// detalles
$("#tabla_detalles").bootstrapTable({
    toolbar: '#toolbar_detalles',
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
    sortName: "id_pago_proveedor",
    sortOrder: 'asc',
    uniqueId: 'id_pago_proveedor',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_pago_proveedor', title: 'ID Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero_documento', title: 'Documento', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'condicion', title: 'Condición', align: 'left', valign: 'middle', sortable: true },
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true },
            { field: 'proveedor', title: 'PROVEEDOR', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'total_costo', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            { field: 'monto', title: 'PAGADO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            
        ]
    ]
});

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
    sortName: "id_liquidacion",
    sortOrder: 'asc',
    uniqueId: 'id_liquidacion',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_liquidacion', title: 'ID Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ci', title: 'C.I', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true },
            { field: 'periodo', title: 'Periodo', align: 'left', valign: 'middle', sortable: true },
            { field: 'neto_cobrar', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            { field: 'monto', title: 'PAGADO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            
        ]
    ]
});

// detalles
$("#tabla_detalles_caja_chica").bootstrapTable({
    toolbar: '#toolbar_detalles_caja_chica',
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
    sortName: "id_caja_chica_sucursal",
    sortOrder: 'asc',
    uniqueId: 'id_caja_chica_sucursal',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_caja_chica_sucursal', title: 'ID Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true},
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'PAGADO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            
        ]
    ]
});

$('#tipo').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
});

$('#filtro_fecha').change();
