var url = 'inc/descuentos-funcionarios-data';
var url_listados = 'inc/listados';
var select2Options = {
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
}

$(window).on('keydown', function (event) 
{ 
    switch(event.which) {
        // F1 - Tab anticipos
        case 112:
            event.preventDefault();
           $("#anticipos-tab").tab('show');
        break;
        // F2 - Tab prestamos
        case 113:
            event.preventDefault();
           $("#prestamos-tab").tab('show');
        break;
        // F3 - Tab otros
        case 114:
            event.preventDefault();
           $("#otros-tab").tab('show');
        break;

        case 115:
            event.preventDefault();
           if($("#anticipos").is(':visible')){
                $("#agregar").click();
            }else if($("#prestamos").is(':visible')){
                $("#agregar_prestamos").click();
            }else if($("#otros").is(':visible')){
                $("#agregar_otros").click();
            }
        break;

    }
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

function iconosFilaPrestamo(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado-pre mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar_prestamo" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

function iconosFilaOtros(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado-otro mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar-otro" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
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

// CALENDARIO
var fechaIni2;
var fechaFin2;
var meses2 = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cb2(start, end) {
    fechaIni2 = start.format('DD/MM/YYYY');
    fechaFin2 = end.format('DD/MM/YYYY');
    $('#filtro_fecha2 span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#desde2").val(fechaMYSQL(fechaIni2));
    $("#hasta2").val(fechaMYSQL(fechaFin2));
}

// Rango: Mes actual
cb2(moment().startOf('month'), moment().endOf('month'));

// Rango: dia actual
// cb2(moment(), moment());

$('#filtro_fecha2').daterangepicker({
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
        [meses2[moment().subtract(1, 'month').format("M")]] : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        [meses2[moment().subtract(2, 'month').format("M")]] : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
        [meses2[moment().subtract(3, 'month').format("M")]] : [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')],
        [meses2[moment().subtract(4, 'month').format("M")]] : [moment().subtract(4, 'month').startOf('month'), moment().subtract(4, 'month').endOf('month')],
        [meses2[moment().subtract(5, 'month').format("M")]] : [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')]
    }
}, cb2);

$('#filtro_fecha2').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni2 = picker.startDate.format('DD/MM/YYYY');
    fechaFin2 = picker.endDate.format('DD/MM/YYYY');
    $("#desde2").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta2").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha2').change();
}).on('change', function() {
    $('#tabla_prestamos').bootstrapTable('refresh', { url: url+'?q=ver_prestamos&desde2='+$('#desde2').val()+'&hasta2='+$('#hasta2').val() });
});

// CALENDARIO
var fechaIni3;
var fechaFin3;
var meses3 = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cb3(start, end) {
    fechaIni3 = start.format('DD/MM/YYYY');
    fechaFin3 = end.format('DD/MM/YYYY');
    $('#filtro_fecha3 span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#desde3").val(fechaMYSQL(fechaIni3));
    $("#hasta3").val(fechaMYSQL(fechaFin3));
}

// Rango: Mes actual
cb3(moment().startOf('month'), moment().endOf('month'));

// Rango: dia actual
// cb3(moment(), moment());

$('#filtro_fecha3').daterangepicker({
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
        [meses3[moment().subtract(1, 'month').format("M")]] : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        [meses3[moment().subtract(2, 'month').format("M")]] : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
        [meses3[moment().subtract(3, 'month').format("M")]] : [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')],
        [meses3[moment().subtract(4, 'month').format("M")]] : [moment().subtract(4, 'month').startOf('month'), moment().subtract(4, 'month').endOf('month')],
        [meses3[moment().subtract(5, 'month').format("M")]] : [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')]
    }
}, cb3);

$('#filtro_fecha3').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni3 = picker.startDate.format('DD/MM/YYYY');
    fechaFin3 = picker.endDate.format('DD/MM/YYYY');
    $("#desde3").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta3").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha3').change();
}).on('change', function() {
    $('#tabla_otros').bootstrapTable('refresh', { url: url+'?q=ver_otros&desde3='+$('#desde3').val()+'&hasta3='+$('#hasta3').val() });
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
                    data: { id_anticipo: row.id_anticipo, estado: estado },
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

window.accionesFilaPrestamo = {
    'click .editar_prestamo': function (e, value, row, index) {
        editarDatosPrestamos(row);
    },
    'click .cambiar-estado-pre': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 1 ? 0 : 1);
        
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado-prestamo',
                    type: 'post',
                    data: { id_prestamo: row.id_prestamo, estado: estado },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla_prestamos').bootstrapTable('refresh');
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

window.accionesFilaOtros = {
    'click .editar-otro': function (e, value, row, index) {
        editarDatosOtros(row);
    },
    'click .cambiar-estado-otro': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 1 ? 0 : 1);
        
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado-otro',
                    type: 'post',
                    data: { id_descuento: row.id_descuento, estado: estado },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla_otros').bootstrapTable('refresh');
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
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-160,
    pageSize: Math.floor(($(window).height()-160)/50),
    sortName: "id_anticipo",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_anticipo', title: 'ID Anticipo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_creacion', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

$("#tabla_prestamos").bootstrapTable({
    // url: url + '?q=ver_prestamos',
    toolbar: '#toolbar_prestamos',
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
    height: $(window).height()-160,
    pageSize: Math.floor(($(window).height()-160)/50),
    sortName: 'funcionario',
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_prestamo', title: 'ID Prestamo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'cuota', title: 'Cuota', align: 'right', valign: 'middle', sortable: true, visible: true },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_creacion', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaPrestamo, formatter: iconosFilaPrestamo }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatosPrestamos(row);
});

$("#tabla_otros").bootstrapTable({
    // url: url + '?q=ver_otros',
    toolbar: '#toolbar_otros',
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
    height: $(window).height()-160,
    pageSize: Math.floor(($(window).height()-160)/50),
    sortName: 'descuento',
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_descuento', title: 'ID Descuento', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'descuento', title: 'Descuento', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false},
            { field: 'fecha_creacion', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaOtros, formatter: iconosFilaOtros, width: 200 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatosOtros(row);
});

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla, #tabla_prestamos, #tabla_otros").bootstrapTable('refreshOptions', { 
                height: $(window).height()-160,
                pageSize: Math.floor(($(window).height()-160)/50),
            })
        }, 100);
    });
});

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Anticipos');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
});

$('#agregar_prestamos').click(function() {
    $('#modalLabel_pre').html('Agregar Préstamos');
    $('#formulario_pres').attr('action', 'cargar_prestamo');
    $('#eliminar_pre').hide();
    resetForm('#formulario_pres');
});

$('#agregar_otros').click(function() {
    $('#modalLabel_otros').html('Agregar Otros Descuentos');
    $('#formulario_otros').attr('action', 'cargar_otro');
    $('#eliminar_otros').hide();
    resetForm('#formulario_otros');
});

$('#modal').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
    $("#id_funcionario").select2($.extend({}, select2Options, {dropdownParent: $(this)}));
});

$('#modal_prestamos').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
    $("#id_funcionario_pre").select2($.extend({}, select2Options, {dropdownParent: $(this)}));

});

$('#modal_otros').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
    $("#id_funcionario_otro").select2($.extend({}, select2Options, {dropdownParent: $(this)}));
});

$('#prestamos-tab').on('shown.bs.tab', function (event) {
  $('#tabla_prestamos').bootstrapTable('resetView');
})

$('#otros-tab').on('shown.bs.tab', function (event) {
  $('#tabla_otros').bootstrapTable('resetView');
})

function resetForm(form) {
    $(form).trigger('reset');
    $(form).removeClass('was-validated');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e){
    e.preventDefault();
    if (this.checkValidity() === false) {
        $('#formulario').addClass('was-validated');
        if ($(this).valid() === false) return false;
        return false;
    }
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

$("#formulario_pres").submit(function(e){
    e.preventDefault();
    if (this.checkValidity() === false) {
        $('#formulario_pres').addClass('was-validated');
        if ($(this).valid() === false) return false;
        return false;
    }
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
                $('#modal_prestamos').modal('hide');
                $('#tabla_prestamos').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown){
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$("#formulario_otros").submit(function(e){
    e.preventDefault();
    if (this.checkValidity() === false) {
        $('#formulario_otros').addClass('was-validated');
        if ($(this).valid() === false) return false;
        return false;
    }
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
                $('#modal_otros').modal('hide');
                $('#tabla_otros').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown){
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// ELIMINAR ANTICIPO
$('#eliminar').click(function() {
    var id = $("#id_anticipo").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Anticipo?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id},	
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

// ELIMINAR PRESTAMO
$('#eliminar_pre').click(function() {
    var id = $("#id_prestamo").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Préstamo?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar_prestamo', id}, 
                beforeSend: function() {
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal_prestamos').modal('hide');
                        $('#tabla_prestamos').bootstrapTable('refresh');
                    }
                },
                error: function (jqXhr) {
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });
});

// ELIMINAR OTROS
$('#eliminar_otros').click(function() {
    var id = $("#id_descuento").val();
    var nombre= $("#descuento").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Descuento: ${nombre}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar_otros', id, nombre}, 
                beforeSend: function() {
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal_otros').modal('hide');
                        $('#tabla_otros').bootstrapTable('refresh');
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
    $('#modalLabel').html('Editar Anticipos');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_anticipo").val(row.id_anticipo);
    $("#monto").val(separadorMiles(row.monto));
    $("#obs").val(row.observacion);
    $("#fecha").val(row.fecha_tabla);

    if (row.id_funcionario) {
        $("#id_funcionario").append(new Option(row.funcionario, row.id_funcionario, true, true)).trigger('change');
    }

}

function editarDatosPrestamos(row) {
    resetForm('#formulario_pres');
    $('#modalLabel_pre').html('Editar Préstamos');
    $('#formulario_pres').attr('action', 'editar-prestamo');
    $('#eliminar_pre').show();
    $('#modal_prestamos').modal('show');

    $("#id_prestamo").val(row.id_prestamo);
    $("#monto_pre").val(separadorMiles(row.monto));
    $("#cuota").val(row.cuota);
    $("#obs_pre").val(row.observacion);
    $("#fecha_prestamo").val(row.fecha_tabla);

    if (row.id_funcionario) {
        $("#id_funcionario_pre").append(new Option(row.funcionario, row.id_funcionario, true, true)).trigger('change');
    }
}

function editarDatosOtros(row) {
    resetForm('#formulario_otros');
    $('#modalLabel_otros').html('Editar Descuentos');
    $('#formulario_otros').attr('action', 'editar-otros');
    $('#eliminar_otros').show();
    $('#modal_otros').modal('show');

    $("#id_descuento").val(row.id_descuento);
    $("#descuento").val(row.descuento);
    $("#monto_otro").val(separadorMiles(row.monto));
    $("#obs_otro").val(row.observacion);
    $("#fecha_otro").val(row.fecha_tabla);

    if (row.id_funcionario) {
        $("#id_funcionario_otro").append(new Option(row.funcionario, row.id_funcionario, true, true)).trigger('change');
    }

}

$('#filtro_fecha').change();
$('#filtro_fecha2').change();
$('#filtro_fecha3').change();