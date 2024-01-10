var url = 'inc/asistencias-data';
var url_listados = 'inc/listados';

// Mask
$('#llegada').mask('99:99', { placeholder: '99:99' });
$('#salida').mask('99:99', { placeholder: '99:99' });

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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` })
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Detalles"><i class="fas fa-list"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        $('#modal_detalle').modal('show');
        $('#toolbar_titulo').html(`${row.ci}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=horarios&ci='+row.ci+'&fecha='+row.fecha  });
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
    pageSize: Math.floor(($(window).height()-120)/30),
    sortName: "id_asistencia",
    sortOrder: 'desc',
    trimOnSearch: false,
    clickToSelect: true,
    columns: [
        [
            { field: 'id_asistencia', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'dia', title: 'Día', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_asist', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'funcionario', title: 'Funcionario', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'llegada', title: 'Entrada', align: 'right', valign: 'middle', sortable: true },
            { field: 'salida', title: 'Salida', align: 'right', valign: 'middle', sortable: true},
            { field: 'normal', title: 'Normal', align: 'right', valign: 'middle', sortable: true},
            { field: 'trabajo', title: 'Horas Trab.', align: 'right', valign: 'middle', sortable: true },
            { field: 'extra', title: 'Extra', align: 'right', valign: 'middle', sortable: true},
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false},
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Horarios', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 80 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

function checkbox(value, row, index, field) {
    return (row.estado != 0) ? { disabled: true } : value;
}

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: $(window).height()-120,
                pageSize: Math.floor(($(window).height()-120)/30),
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
            $('#btn_importar').click();
        break;

        // F2 Buscar RUC / CI
        case 113:
            event.preventDefault();
            $('#btn_agregar').click();
            break;
    }
});

$('#btn_importar').on('click', function() {
    $('#modal_asistencias').modal('show');
    $('#documento').next('.custom-file-label').html('Seleccionar Archivo');
});

$('#btn_agregar').on('click', function() {
    $('#modal').modal('show');
    $('#modalLabel').html('Agregar Asistencia');
    resetForm('#formulario_asistencia');
    $('#fecha').val(moment().format('YYYY-MM-DD'));
});

$('#btn_anular').on('click', function() {
    cambiarEstado(2, 'Anulado', 'Anular', 'danger');
});

function cambiarEstado(estado, estado_str, text, color) {
    var selections = $('#tabla').bootstrapTable('getSelections');
    var text_solicitud = (selections.length == 1) ? 'Liquidación' : 'asistencias';
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
                data: { estado, asistencias: JSON.stringify(selections) },
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

$("#documento").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_documento").val(cleanFileName);
});

$('#id_funcionario').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
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
            return { q: 'funcionarios', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_funcionario, text: obj.funcionario }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});



$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    var data = new FormData(this);
    $.ajax({
        url: url + '?q=procesar',
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
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal_asistencias').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

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
    height: 300,
    sortName: "id_asistencia",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_asistencia', title: 'ID Método Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'llegada', title: 'Llegada', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'salida', title: 'Salida', align: 'right', valign: 'middle', sortable: true },
        ]
    ]
});


$("#formulario_asistencia").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    var data = new FormData(this);
    $.ajax({
        url: url + '?q=cargar',
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
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});


$('#normal').focus(function(){

var salida = $('#salida').val();
var llegada = $('#llegada').val();

var hora_llegada = llegada.split(':');
var hora_llegada2 = parseInt(hora_llegada[0]) * 3600;
var minuto_llegada2 = parseInt(hora_llegada[1]) * 60;

var hora_salida = salida.split(':')
var hora_salida2 = parseInt(hora_salida[0]) * 3600;
var minuto_salida2 = parseInt(hora_salida[1]) * 60;

var horas_total_trabajo = (hora_salida2 - hora_llegada2) / 3600;
var minutos_total_trabajo = (minuto_salida2-minuto_llegada2) / 60;

    if (minutos_total_trabajo < 0) {
        minutos_total_trabajo = minutos_total_trabajo * -1;
    }

    if (horas_total_trabajo) {
        if (horas_total_trabajo >= 9) {
            horas_total_trabajo = horas_total_trabajo -1
            horas_extras = (horas_total_trabajo - 9)
            minutos_extras = (minutos_total_trabajo - 0)
            $('#normal').val(horas_total_trabajo+":"+minutos_total_trabajo);
            $('#extra').val(horas_extras+":"+minutos_extras);
        } else{
            $('#normal').val(horas_total_trabajo+":"+minutos_total_trabajo);
            $('#extra').val('0');
        }
    }
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

$('#filtro_fecha').change();
