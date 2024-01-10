var url = 'inc/vacaciones-data';
var url_listados = 'inc/listados';

$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 - Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
    }
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-dark btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>',
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
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    },
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 1 ? 0 : 1);
        
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id_vacacion: row.id_vacacion, estado: estado },
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
        var param = { id_vacacion: row.id_vacacion, id_funcionario: row.id_funcionario};
        OpenWindowWithPost("imprimir-vacacion.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirLiquidaciones", param);
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
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_vacacion",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_vacacion', title: 'ID Vacacion', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'total_vacacion', title: 'Total Vacación / Días', align: 'left', valign: 'middle', sortable: true },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
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

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Vacación');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    $("#id_funcionario").attr('readonly', false);
    resetForm('#formulario');
    $('#fecha_desde').val(moment().format('YYYY-MM-DD'));

    $("#fecha_desde").off('change').on('change', function(){
        $("#fecha_hasta").val(moment(this.value, 'YYYY-MM-DD').add(Number($("#utilizar").val()), 'days').format('YYYY-MM-DD'));
    }).trigger('change');
});

$('#modal').on('shown.bs.modal', function (e) {
    $("#id_funcionario").focus();
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e){
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url + '?q='+$(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr){
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown){
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// ELIMINAR
$('#eliminar').click(function() {
    var id_vacacion = $("#id_vacacion").val();
    var id = $("#id_funcionario").val();
    //var nombre = ('select[id="id_funcionario"] option:selected').text()

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Vacación?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id_vacacion },	
                beforeSend: function() {
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal').modal('hide');
                        $('#tabla').bootstrapTable('refresh');
                    }
                },
                error: function (jqXhr) {
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });
});

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Vacación');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_vacacion").val(row.id_vacacion);
    $("#anho").val(row.anho);
    $("#antiguedad").val(row.antiguedad);
    $("#corresponde").val(row.total_vacacion);
    $("#pendiente").val(row.total_vacacion);
    $("#utilizar").val(row.utilizado);
    $("#importe").val(separadorMiles(row.importe));
    $("#fecha_desde").val(row.fecha_desde);
    $("#fecha_hasta").val(row.fecha_hasta);
    $("#observacion").val(row.observacion);
    $("#id_funcionario").attr('readonly', true);

    if (row.id_funcionario) {
        $("#id_funcionario").append(new Option(row.funcionario, row.id_funcionario, true, true)).trigger('change');
    }
}

function contador_fechas(){
    $("#fecha_hasta").val(moment($("#fecha_desde").val(), 'YYYY-MM-DD').add(Number($("#utilizar").val()), 'days').format('YYYY-MM-DD'));
    if($("#utilizar").val()*1 > $("#pendiente").val()*1){
        alertDismissJS(`La cantidad a utilizar no debe ser mayor al pendiente`, 'error');
        return;
    }
}

$("#fecha_desde").on('change', function(){
        $("#fecha_hasta").val(moment(this.value, 'YYYY-MM-DD').add(Number($("#utilizar").val()), 'days').format('YYYY-MM-DD'));
})

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
}).on('select2:select', function(){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'recuperar_antiguedad', id_funcionario: this.value },   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();
            $("#antiguedad").val(data.antiguedad);
            if(data.antiguedad <= 5){
                $("#corresponde").val(12);
            }else if(data.antiguedad == 6 && data.antiguedad <= 10){
                $("#corresponde").val(18);
            }else if(data.antiguedad > 10){
                $("#corresponde").val(30);
            }

            if(data.antiguedad <= 5 && data.saldo == 0){
                $("#pendiente").val(12);
            }else if(data.antiguedad == 6 && data.antiguedad <= 10 && data.saldo == 0){
                $("#pendiente").val(18);
            }else if(data.antiguedad > 10 && data.saldo == 0){
                $("#pendiente").val(30);
            }else{
                $("#pendiente").val(data.saldo);
            }
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}).on('select2:clearing', function(){
    $("#corresponde").val(0);
    $("#pendiente").val(0);
    $("#antiguedad").val(0);
    $("#utilizar").val(0);
    $("#fecha_desde").val('');
    $("#fecha_hasta").val('');
    $("#importe").val(0);
    $("#anho").val(null).trigger('change');
    $("#observacion").val('');
});

$('#anho').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    tags: true,
    maximumInputLength: 4,
    //minimumResultsForSearch: Infinity,
}).on('select2:select', function(){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar_vacacion', id_funcionario: $("#id_funcionario").val(), anho: this.value },   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();

            $("#pendiente").val($("#corresponde").val());

            if(data.status == 'error' ){
                alertDismissJS(data.mensaje, "error");
            }
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('#filtro_fecha').change();
