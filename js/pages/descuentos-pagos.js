var url = 'inc/descuentos-pagos-data';
var url_listados = 'inc/listados';

// Acciones por teclado
// Acciones principales
Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#agregar').click() });

// Modal
var MousetrapModalTab2 = new Mousetrap();
MousetrapModalTab2.pause();
MousetrapModalTab2.bindGlobal('f1', (e) => { e.preventDefault(); $('#btn_importar_tab_2').click() });
MousetrapModalTab2.bindGlobal('del', (e) => {
    e.preventDefault();
    let selections = $('#tabla_tab_2').bootstrapTable('getSelections');
    if (selections.length > 0) {
        let id = selections[0].id_descuento_pago_filtro;
        eliminarTablaTab2(id);
    }
});

var MousetrapModalTab3 = new Mousetrap();
MousetrapModalTab3.pause();
MousetrapModalTab3.bindGlobal('f1', (e) => { e.preventDefault(); $('#btn_agregar_tab_3').click() });
MousetrapModalTab3.bindGlobal('f2', (e) => { e.preventDefault(); $('#btn_importar_tab_3').click() });
MousetrapModalTab3.bindGlobal('del', (e) => {
    e.preventDefault();
    let selections = $('#tabla_tab_3').bootstrapTable('getSelections');
    if (selections.length > 0) {
        let id = selections[0].id_producto;
        var nombre = selections[0].producto;
        eliminarTablaTab3(id, nombre);
    }
});

var MousetrapModalTab4 = new Mousetrap();
MousetrapModalTab4.pause();
MousetrapModalTab4.bindGlobal('f1', (e) => { e.preventDefault(); $('#codigo_tab_4').focus() });
MousetrapModalTab4.bindGlobal('f2', (e) => { e.preventDefault(); $('#producto_tab_4').focus() });
MousetrapModalTab4.bindGlobal('f3', (e) => { e.preventDefault(); $('#lote_tab_4').focus() });
MousetrapModalTab4.bindGlobal('f4', (e) => { e.preventDefault(); $('#btn_agregar_tab_4').click() });
MousetrapModalTab4.bindGlobal('f5', (e) => { e.preventDefault(); $('#btn_importar_tab_4').click() });
MousetrapModalTab4.bindGlobal('del', (e) => {
    e.preventDefault();
    let selections = $('#tabla_tab_4').bootstrapTable('getSelections');
    if (selections.length > 0) {
        let id = selections[0].id;
        var nombre = selections[0].producto;
        eliminarTablaTab4(id, nombre);
    }
});
MousetrapModalTab4.bindGlobal(['up', 'down'], (e) => { e.preventDefault(); $('#codigo_tab_4, #producto_tab_4').blur(); });

var MousetrapModalBuscar = new Mousetrap();
MousetrapModalBuscar.pause();
MousetrapModalBuscar.bindGlobal('f1', (e) => { e.preventDefault(); $('#filtro_buscar_proveedor').focus(); });
MousetrapModalBuscar.bindGlobal('f2', (e) => { e.preventDefault(); $('#filtro_buscar_origen').focus(); });
MousetrapModalBuscar.bindGlobal('f3', (e) => { e.preventDefault(); $('#filtro_buscar_tipo').focus(); });
MousetrapModalBuscar.bindGlobal('f4', (e) => { e.preventDefault(); $('#filtro_buscar_laboratorio').focus(); });
MousetrapModalBuscar.bindGlobal('f5', (e) => { e.preventDefault(); $('#filtro_buscar_marca').focus(); });
MousetrapModalBuscar.bindGlobal('f6', (e) => { e.preventDefault(); $('#filtro_buscar_rubro').focus(); });

MousetrapTableNavigationCell('#tabla_tab_2', MousetrapModalTab2, true);
MousetrapTableNavigationCell('#tabla_tab_3', MousetrapModalTab3, true);
MousetrapTableNavigationCell('#tabla_tab_4', MousetrapModalTab4, true);
MousetrapTableNavigation('#tabla_buscar', MousetrapModalBuscar, cargarTablaTab3);

$('#modal').on('show.bs.modal', function(e) {
    Mousetrap.pause();
});
$('#modal').on('hide.bs.modal', function(e) {
    Mousetrap.unpause();
    MousetrapModalTab2.pause();
    MousetrapModalTab3.pause();
});
$('#modal_buscar').on('show.bs.modal', function(e) {
    MousetrapModalTab3.pause();
    MousetrapModalBuscar.unpause();
});
$('#modal_buscar').on('hide.bs.modal', function(e) {
    MousetrapModalTab3.unpause();
    MousetrapModalBuscar.pause();
});

// Tab 2
$('#nav_2').on('shown.bs.tab', function (event) {
    MousetrapModalTab2.unpause();
    $('#tabla_tab_2').bootstrapTable('resetView');
});
$('#nav_2').on('hidden.bs.tab', function (event) {
    MousetrapModalTab2.pause();
});
// Tab 3
$('#nav_3').on('shown.bs.tab', function (event) {
    MousetrapModalTab3.unpause();
    $('#tabla_tab_3').bootstrapTable('resetView');
});
$('#nav_3').on('hidden.bs.tab', function (event) {
    MousetrapModalTab3.pause();
});
// Tab 4
$('#nav_4').on('shown.bs.tab', function (event) {
    MousetrapModalTab4.unpause();
    $('#tabla_tab_4').bootstrapTable('resetView');
});
$('#nav_4').on('hidden.bs.tab', function (event) {
    MousetrapModalTab4.pause();
});

$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $('#tabla').bootstrapTable('refreshOptions', { 
                height: alturaTabla(),
                pageSize: pageSizeTabla(),
            });
        }, 100);
    });
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo") ? "Inactivo" : "Activo";
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
                    data: { id: row.id_descuento_pago, estado },
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
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    }
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
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
    sortName: "fecha_inicio",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_descuento_pago', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'descripcion', title: 'Descripcion', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'hora_inicio', title: 'Inicio', align: 'left', valign: 'middle', sortable: true },
            { field: 'hora_fin', title: 'Fin', align: 'left', valign: 'middle', sortable: true },
            { field: 'configuracion', title: 'Configuración', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha_inicio', title: 'Fecha Inicio', align: 'left', valign: 'middle', sortable: true, formatter: bstFormatterFecha, visible: false, switchable: false },
            { field: 'fecha_fin', title: 'Fecha Fin', align: 'left', valign: 'middle', sortable: true, formatter: bstFormatterFecha, visible: false, switchable: false },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 120 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

$('#metodo').select2({
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
            return { q: 'metodos_pagos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_metodo_pago, text: obj.metodo_pago, entidad: obj.entidad }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function (results) {
    var data = $('#metodo').select2('data')[0];
    if (data && data.entidad == 1) {
        $('#entidad').prop('disabled', false);
    } else {
        $('#entidad').val(null).trigger('change').prop('disabled', true);
    }
});

$('#entidad').select2({
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
            return { q: 'entidades', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_entidad, text: obj.tipo_entidad }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#tipo_configuracion').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('change', function() {
    let data = $(this).select2('data')[0];
    if (data) {
        if (data.id == 1) {
            $('label[for="dias"]').parent().hide();
            $('#fecha_inicio_fin').parent().show();
            $('input[name="dias[]"]').attr('required', false);
        } else {
            $('label[for="dias"]').parent().show();
            $('#fecha_inicio_fin').parent().hide();
            $('input[name="dias[]"]').attr('required', true);
        }
    }
});

// CALENDARIO
var fechaIni;
var fechaFin;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cb(start, end) {
    fechaIni = start.format('DD/MM/YYYY');
    fechaFin = end.format('DD/MM/YYYY');
    $('#fecha_inicio_fin span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#fecha_inicio").val(fechaMYSQL(fechaIni));
    $("#fecha_fin").val(fechaMYSQL(fechaFin));
}

$('#fecha_inicio_fin').daterangepicker({
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
        'Mañana': [moment().add(1, 'days'), moment().add(1, 'days')],
        'Este Mes': [moment().startOf('month'), moment().endOf('month')],
        [meses[moment().add(1, 'month').format("M")]] : [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
    }
}, cb);

$('#fecha_inicio_fin').on('apply.daterangepicker', function(ev, picker) {
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#fecha_inicio").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#fecha_fin").val(picker.endDate.format('YYYY-MM-DD'));
    $('#fecha_inicio_fin').change();
});

$('#modal').on('shown.bs.modal', function (e) {
    $(this).find('input[type!="hidden"], select, textarea').filter(':first').focus();
});

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Descuento');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    $('#nav_1').tab('show');
    $('#tabla_tab_2, #tabla_tab_3, #tabla_tab_4').bootstrapTable('removeAll');
    resetForm('#formulario');
    $('#tipo_configuracion').val(1).trigger('change');
    // Rango: Mañana
    cb(moment().add(1, 'days'), moment().add(1, 'days'));
});

// ELIMINAR
$('#eliminar').click(function() {
    var id = $("#hidden_id").val();
    var nombre = $("#descripcion").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar el Campaña: ${nombre}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, nombre },	
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

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function editarDatos(row) {
    resetForm('#formulario');
    $('#nav_1').tab('show');

    $('#modalLabel').html('Editar Descuento');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#hidden_id").val(row.id_descuento_pago);
    $("#descripcion").val(row.descripcion);
    $('#tipo_configuracion').val(row.tipo).trigger('change');

    $("#hora_inicio").val(row.hora_inicio);
    $("#hora_fin").val(row.hora_fin);

    if (row.tipo == 1) {
        cb(moment(row.fecha_inicio), moment(row.fecha_fin));
    } else {
        cb(moment().add(1, 'days'), moment().add(1, 'days'));
    }

    if (row.dias) {
        let dias = JSON.parse(row.dias);
        dias.forEach(value => $(`#dia_${value.dia}`).prop('checked', true));
    }

    // Select2
    if (row.id_metodo_pago) {
        $('#metodo').select2('trigger', 'select', {
            data: { id: row.id_metodo_pago, text: row.metodo_pago, entidad: row.metodo_entidad }
        });
    }
    if (row.id_entidad) {
        $('#entidad').select2('trigger', 'select', {
            data: { id: row.id_entidad, text: row.entidad }
        });
    }

    $('#tabla_tab_2').bootstrapTable('refresh', { url: url+'?q=ver_filtros&id='+row.id_descuento_pago });
    $('#tabla_tab_3').bootstrapTable('refresh', { url: url+'?q=ver_productos_descuento&id='+row.id_descuento_pago });
    $('#tabla_tab_4').bootstrapTable('refresh', { url: url+'?q=ver_productos_remate&id='+row.id_descuento_pago });
}

$("#btn_guardar").on('click', () => $('#formulario').submit());
$("#formulario").validate({ ignore: '' });
$("#formulario").submit(function(e) {
    e.preventDefault();
    let tableDataTab2 = $('#tabla_tab_2').bootstrapTable('getData');
    let tableDataTab3 = $('#tabla_tab_3').bootstrapTable('getData');
    let tableDataTab4 = $('#tabla_tab_4').bootstrapTable('getData');

    if ($(this).valid() === false) return false;
    if (tableDataTab2.length == 0 && tableDataTab3.length == 0 && tableDataTab4.length == 0) {
        alertDismissJS('Ningún porcentaje de descuento agregado. Favor complete la tabla de filtros, la tabla de productos o la tabla de remates', 'error', () => $('#nav_2').tab('show'));
        return false;
    }


    var data = $(this).serializeArray();
    data.push({ name: 'detalles', value: JSON.stringify(tableDataTab2) });
    data.push({ name: 'detalles2', value: JSON.stringify(tableDataTab3) });
    data.push({ name: 'detalles3', value: JSON.stringify(tableDataTab4) });

    $.ajax({
        url: url + '?q='+$(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            } else if (data.error == 'detalles') {
                $('#nav_2').tab('show');
            } else if (data.error == 'detalles2') {
                $('#nav_3').tab('show');
            } else if (data.error == 'detalles3') {
                $('#nav_4').tab('show');
            } else {
                $('#nav_1').tab('show');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// Tabla tab 2
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
                results: $.map(data.data, function(obj) { return { id: obj.id_tipo_producto, text: obj.tipo }; }),
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

$('#controlado').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
});

function alturaTablaTab2() {
    return bstCalcularAlturaTabla(420, 300);
}

function iconosFilaTablaTab2(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}

window.accionesFilaTablaTab2 = {
    'click .eliminar': function (e, value, row, index) {
        eliminarTablaTab2(row.id_descuento_pago_filtro);
    }
}

$("#tabla_tab_2").bootstrapTable({
    toolbar: '#toolbar_tab_2',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: false,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaTab2(),
    sortName: "numero",
    sortOrder: 'asc',
    uniqueId: 'id_descuento_pago_filtro',
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true  },
            { field: 'id_descuento_pago_filtro', title: 'ID Descuento Pago Filtro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_metodo_pago', title: 'ID Método Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'metodo_pago', title: 'Metodo Pago', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_entidad', title: 'ID Entidad', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'entidad', title: 'Entidad', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_origen', title: 'ID Origen', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'origen', title: 'Origen', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_tipo', title: 'ID Tipo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_laboratorio', title: 'ID Laboratorio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'laboratorio', title: 'Laboratorio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_marca', title: 'ID Marca', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_rubro', title: 'ID Rubro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'rubro', title: 'Rubro', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'controlado', title: 'Controlado', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: bstCondicional },
            { field: 'porcentaje', title: '% Descuento', align: 'right', valign: 'middle', sortable: true, width: 150, editable: { type: 'text' } },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaTablaTab2, formatter: iconosFilaTablaTab2, width: 120 },
        ]
    ]
});

$('#tabla_tab_2').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
    $('input.autonumericTabla').initNumber(); //autonumeric
});

$.extend($('#tabla_tab_2').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: `<input type="text" id="porcentaje-tabla" 
    class="form-control input-sm autonumericTabla"
    style="width:140px"
    data-allow-decimal-padding="false"
    data-decimal-character=","
    data-digit-group-separator="."
    data-maximum-value="100"
    value=""
    autocomplete="off">`,
    display: function(value) {
        let text2 = new Intl.NumberFormat(['ban', 'id']).format(value) +'%';
        $(this).text(text2);
    }
});


$('#tabla_tab_2').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id_descuento_pago_filtro;
    let numerotabla2 = quitaSeparadorMilesFloat(row[field]);

    // Si la columna quedo en blanco
    if (!row[field]) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = numerotabla2;
    }

    if (field == 'porcentaje' && numerotabla2 > 100) {
        update_row[field] = oldValue;
        setTimeout(() => alertDismissJS(`El porcentaje de descuento supera el 100%"`, 'error'), 100);
    }

    // Se actualizan los valores
    $('#tabla_tab_2').bootstrapTable('updateByUniqueId', { id, row: update_row });
});

$('#btn_agregar_tab_2').click(function() {
    let id_metodo_pago = $('#metodo').val();
    let metodo_pago = $('#metodo option:selected').text().replace(/\d+ - /, '');
    let id_entidad = $('#entidad').val();
    let entidad = $('#entidad option:selected').text();
    let id_origen = $('#origen').val();
    let origen = $('#origen option:selected').text();
    let id_tipo = $('#tipo').val();
    let tipo = $('#tipo option:selected').text();
    let id_laboratorio = $('#laboratorio').val();
    let laboratorio = $('#laboratorio option:selected').text();
    let id_marca = $('#marca').val();
    let marca = $('#marca option:selected').text();
    let id_rubro = $('#rubro').val();
    let rubro = $('#rubro option:selected').text();
    let controlado = $('#controlado').val();
    let porcentaje = quitaSeparadorMilesFloat($('#porcentaje').val());

    if (isNaN(porcentaje) || porcentaje == 0) {
        alertDismissJS('Debe de ingresar un porcentaje mayor a 0%.', 'error', () => $('#porcentaje').focus());
        return;
    }

    if (porcentaje  > 100) {
        alertDismissJS('El porcentaje de descuento no puede ser mayor a 100%.', 'error', () => $('#porcentaje').focus());
        return;
    }

    if (!id_metodo_pago && !id_origen && !id_tipo && !id_laboratorio && !id_marca && !id_rubro && !controlado) {
        alertDismissJS('Debe seleccionar al menos un ítem para continuar.', 'error', () => $('#origen').focus());
        return;
    }

    if (id_metodo_pago && !id_entidad && $('#metodo').select2('data')[0].entidad == 1) {
        alertDismissJS('Debe seleccionar una Entidad para continuar.', 'error', () => $('#entidad').focus());
        return;
    }

    let tableData = $('#tabla_tab_2').bootstrapTable('getData');
    let data = tableData.find(value => value.id_metodo_pago == id_metodo_pago && value.id_entidad == id_entidad && value.id_origen == id_origen && value.id_tipo == id_tipo && value.id_laboratorio == id_laboratorio && value.id_marca == id_marca && value.id_rubro == id_rubro && value.controlado == controlado);
    if (data) {
        alertDismissJS('Combinación de filtros ya cargada', 'error');
        return;
    }

    $("#tabla_tab_2").bootstrapTable('insertRow', {
        index: 0,
        row: {
            id_descuento_pago_filtro: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
            id_metodo_pago,
            metodo_pago,
            id_entidad,
            entidad,
            id_origen,
            origen,
            id_tipo,
            tipo,
            id_laboratorio,
            laboratorio,
            id_marca,
            marca,
            id_rubro,
            rubro,
            controlado,
            porcentaje,
        }
    });

    $('#tab_2').find('select').val(null).trigger('change');
    $('#porcentaje').val('');
    setTimeout(() => $('#metodo').focus(), 100);
});

function eliminarTablaTab2(id) {
    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Esta seguro de eliminar el descuento seleccionado?`,
        confirmButtonText: 'Eliminar',
        confirmButtonColor: 'var(--danger)',
        confirm: function() {
            $('#tabla_tab_2').bootstrapTable('removeByUniqueId', id);
        }
    });
}

// Tabla tab 3
function alturaTablaTab3() {
    return bstCalcularAlturaTabla(278, 300);
}

function iconosFilaTablaTab3(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}

window.accionesFilaTablaTab3 = {
    'click .eliminar': function (e, value, row, index) {
        let id = row.id_producto;
        let nombre = row.producto;
        eliminarTablaTab3(id, nombre);
    }
}

$("#tabla_tab_3").bootstrapTable({
    toolbar: '#toolbar_tab_3',
    showExport: true,
    search: true,
    showRefresh: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    height: alturaTablaTab3(),
    sortName: "producto",
    sortOrder: 'desc',
    trimOnSearch: false,
    uniqueId: 'id_producto',
    keyEvents: true,
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'descuento_fraccionado', title: 'Fraccionado', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'precio', title: 'Precio', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'porcentaje', title: '% Descuento', align: 'center', valign: 'middle', sortable: true, width: 150, editable: { type: 'text' } },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaTablaTab3, formatter: iconosFilaTablaTab3, width: 120 },
        ]
    ]
});

$('#tabla_tab_3').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
    $('input.autonumericTabla').initNumber(); //autonumeric
});

function toggleButonsTab3(value, row, index, field) {
    var selections = $('#tabla_tab_3').bootstrapTable('getSelections');
    $('.acciones_tab_3').prop('disabled', (selections.length > 0) ? false : true);
}

$('#tabla_tab_3')
    .on('check.bs.table', toggleButonsTab3)
    .on('uncheck.bs.table', toggleButonsTab3)
    .on('check-all.bs.table', toggleButonsTab3)
    .on('uncheck-all.bs.table', toggleButonsTab3)
    .on('load-success.bs.table', toggleButonsTab3);


$.extend($('#tabla_tab_3').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: `<input type="text" id="porcen-tabla" 
    class="form-control input-sm autonumericTabla"
    style="width:140px"
    data-allow-decimal-padding="false"
    data-decimal-character=","
    data-digit-group-separator="."
    data-maximum-value="100"
    value=""
    autocomplete="off">`,
    display: function(value) {
        let text3 = new Intl.NumberFormat(['ban', 'id']).format(value) +'%';
        $(this).text(text3);
    }
});

$('#tabla_tab_3').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id_producto;
    let numerotabla3 = quitaSeparadorMilesFloat(row[field]);

    // Si la columna quedo en blanco
    if (!row[field]) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = numerotabla3;
    }

    if (field == 'porcentaje' && numerotabla3 > 100) {
        update_row[field] = oldValue;
        setTimeout(() => alertDismissJS(`El porcentaje de descuento supera el 100%`, 'error'), 100);
    }

    // Se actualizan los valores
    $('#tabla_tab_3').bootstrapTable('updateByUniqueId', { id, row: update_row });
});

function eliminarTablaTab3(id, nombre) {
    sweetAlertConfirm({
        title: `Eliminar Producto`,
        text: `¿Eliminar "${nombre}"?`,
        confirmButtonText: 'Eliminar',
        confirmButtonColor: 'var(--danger)',
        confirm: function () {
            $('#tabla_tab_3').bootstrapTable('removeByUniqueId', id);
        }
    });
}

// Tabla tab 4
// BUSQUEDA PRODUCTOS POR CODIGO
$('#codigo_tab_4').keyup(function (e) {
    e.preventDefault();
    var codigo = $(this).val();
    if (e.keyCode === 13 && codigo) {
        $.ajax({
            dataType: 'json',
            cache: false,
            url: url_listados,
            type: 'POST',
            data: { q: 'buscar_producto_por_codigo', codigo },
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data) {
                NProgress.done();
                if (jQuery.isEmptyObject(data)) {
                    alertDismissJS('Código del producto no encontrado.', 'error');
                } else {
                    $('#producto_tab_4').select2('trigger', 'select', {
                        data: { 
                            id: data.id_producto,
                            text: data.producto,
                            codigo: data.codigo,
                            id_presentacion: data.id_presentacion,
                            presentacion: data.presentacion,
                            descuento_fraccionado: data.descuento_fraccionado == 1 ? 'Si' : 'No',
                            precio: data.precio,
                            costo: data.costo
                        }
                    });
                }
            },
            error: function (xhr) {
                NProgress.done();
                alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
            }
        });
    }
});
///// FIN BUSQUEDA PRODUCTOS POR CODIGO /////
function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_presentacion) {
        $result = $(`<span>${node.text} <small>(${node.presentacion})</small></span>`);
    }
    return $result;
}

$('#producto_tab_4').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    templateResult: formatResult,
    templateSelection: formatResult,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_producto,
                        text: obj.producto,
                        codigo: obj.codigo,
                        id_presentacion: obj.id_presentacion,
                        presentacion: obj.presentacion,
                        descuento_fraccionado: obj.descuento_fraccionado == 1 ? 'Si' : 'No',
                        precio: obj.precio,
                        costo: obj.costo
                    }
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:open', function() {
    MousetrapModalTab4.pause();
}).on('select2:close', function() {
    MousetrapModalTab4.unpause();
}).on('select2:select', function() {
    let data = $(this).select2('data')[0];
    $('#codigo_tab_4').val(data.codigo || '');

    if (data.id) {
        $.ajax({
            url: url_listados,
            dataType: 'json',
            type: 'GET',
            contentType: 'application/x-www-form-urlencoded',
            data: { q: 'lotes', id_producto: data.id, page: 1 },
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data, textStatus, jQxhr) {
                NProgress.done();
                if (data.total_count > 0) {
                    let obj = data.data[0];
                    $('#lote_tab_4').select2('trigger', 'select', {
                        data: {
                            id: obj.id_lote,
                            text: obj.lote,
                            vencimiento: obj.vencimiento,
                        }
                    });
                } else {
                    alertDismissJsSmall('Ningún lote encontrado', 'error', 2000, () => $('#lote_tab_4').focus())
                }
            },
            error: function (jqXhr, textStatus, errorThrown) {
                NProgress.done();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), "error", 2000, () => $('#lote_tab_4').focus())
            }
        });
    }
}).on('select2:clear', function() {
    $('#codigo_tab_4').val('');
    $('#lote_tab_4').val(null).trigger('change');
});

function formatResultLote(node) {
    let $result = node.text;
    if (node.loading !== true && node.vencimiento) {
        $result = $(`<div class="d-flex justify-content-between">
                        <div class="overflow-hidden text-truncate">
                            <span>${node.text}</span>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-success">${fechaLatina(node.vencimiento)}</span>
                        </div>
                    </div>`);
    }
    return $result;
};

$('#lote_tab_4').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'lotes', id_producto: $('#producto_tab_4').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_lote,
                        text: obj.lote,
                        vencimiento: obj.vencimiento,
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    },
    templateResult: formatResultLote,
    templateSelection: formatResultLote
}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
});

$('#btn_agregar_tab_4').on('click', function(e) {
    let data = $('#producto_tab_4').select2('data')[0];
    let dataLote = $('#lote_tab_4').select2('data')[0];

    if (!data) {
        alertDismissJsSmall('Favor seleccione un producto', 'error', 2000, () => $('#producto_tab_4').focus());
        return false;
    }
    if (!dataLote) {
        alertDismissJsSmall('Favor seleccione un lote', 'error', 2000, () => $('#lote_tab_4').focus());
        return false;
    }

    // Si se repite un producto
    let tableData = $('#tabla_tab_4').bootstrapTable('getData');
    let producto = tableData.find(value => value.id_producto == data.id && value.id_lote == dataLote.id);
    if (producto) {
        alertDismissJsSmall(`Producto ${producto.producto}, Lote ${producto.lote} ya cargado`, 'error', 2000, () => $('#producto_tab_4').focus());
        return false;
    }

    $('#tabla_tab_4').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id: new Date().getTime(),
            id_producto: data.id,
            codigo: data.codigo,
            producto: data.text,
            id_presentacion: data.id_presentacion,
            presentacion: data.presentacion,
            id_lote: dataLote.id,
            lote: dataLote.text,
            vencimiento: dataLote.vencimiento,
            descuento_fraccionado: data.descuento_fraccionado,
            precio: data.precio,
            porcentaje: 0
        }
    });

    $('#codigo_tab_4').val('');
    $('#producto_tab_4').val(null).trigger('change');
    $('#lote_tab_4').val(null).trigger('change');
    setTimeout(() => $('#codigo_tab_4').focus(), 100);
});

function alturaTablaTab4() {
    return bstCalcularAlturaTabla(278, 300);
}

function iconosFilaTablaTab4(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}

window.accionesFilaTablaTab4 = {
    'click .eliminar': function (e, value, row, index) {
        let id = row.id;
        let nombre = row.producto;
        eliminarTablaTab4(id, nombre);
    }
}
$("#tabla_tab_4").bootstrapTable({
    toolbar: '#toolbar_tab_4',
    showExport: true,
    search: true,
    showRefresh: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    height: alturaTablaTab4(),
    sortName: "codigo",
    sortOrder: 'desc',
    trimOnSearch: false,
    uniqueId: 'id',
    keyEvents: true,
    singleSelect: true,
    // clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_lote', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: fechaLatina },
            { field: 'descuento_fraccionado', title: 'Fraccionado', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'precio', title: 'Precio', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'porcentaje', title: '% Descuento', align: 'center', valign: 'middle', sortable: true, width: 150, editable: { type: 'text' } },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaTablaTab4, formatter: iconosFilaTablaTab4, width: 120 },
        ]
    ]
});

$('#tabla_tab_4').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
    $('input.autonumericTabla').initNumber(); //autonumeric
});

function toggleButonsTab3(value, row, index, field) {
    var selections = $('#tabla_tab_4').bootstrapTable('getSelections');
    $('.acciones_tab_4').prop('disabled', (selections.length > 0) ? false : true);
}

$('#tabla_tab_4')
    .on('check.bs.table', toggleButonsTab3)
    .on('uncheck.bs.table', toggleButonsTab3)
    .on('check-all.bs.table', toggleButonsTab3)
    .on('uncheck-all.bs.table', toggleButonsTab3)
    .on('load-success.bs.table', toggleButonsTab3);


$.extend($('#tabla_tab_4').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: `<input type="text" id="porcen-tabla" 
    class="form-control input-sm autonumericTabla"
    style="width:140px"
    data-allow-decimal-padding="false"
    data-decimal-character=","
    data-digit-group-separator="."
    data-maximum-value="100"
    value=""
    autocomplete="off">`,
    display: function(value) {
        let text = new Intl.NumberFormat(['ban', 'id']).format(value) +'%';
        $(this).text(text);
    }
});

$('#tabla_tab_4').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id;
    let numero = quitaSeparadorMilesFloat(row[field]);

    // Si la columna quedo en blanco
    if (!row[field]) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = numero;
    }

    if (field == 'porcentaje' && numero > 100) {
        update_row[field] = oldValue;
        setTimeout(() => alertDismissJS(`El porcentaje de descuento supera el 100%`, 'error'), 100);
    }

    // Se actualizan los valores
    $('#tabla_tab_4').bootstrapTable('updateByUniqueId', { id, row: update_row });
});

function eliminarTablaTab4(id, nombre) {
    sweetAlertConfirm({
        title: `Eliminar Producto`,
        text: `¿Eliminar "${nombre}"?`,
        confirmButtonText: 'Eliminar',
        confirmButtonColor: 'var(--danger)',
        confirm: function () {
            $('#tabla_tab_4').bootstrapTable('removeByUniqueId', id);
        }
    });
}

// Buscar
$('#filtro_buscar_proveedor').select2({
    dropdownParent: $("#modal_buscar"),
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
            return { q: 'proveedores', term: params.term, page: params.page || 1, tipo_proveedor: 1  }
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
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_origen').select2({
    dropdownParent: $("#modal_buscar"),
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
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_tipo').select2({
    dropdownParent: $("#modal_buscar"),
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
                results: $.map(data.data, function(obj) { return { id: obj.id_tipo_producto, text: obj.tipo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', actualizarTablaBuscar);


$('#filtro_buscar_laboratorio').select2({
    dropdownParent: $("#modal_buscar"),
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
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_marca').select2({
    dropdownParent: $("#modal_buscar"),
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
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_rubro').select2({
    dropdownParent: $("#modal_buscar"),
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
}).on('change', actualizarTablaBuscar);

function actualizarTablaBuscar() {
    let id_proveedor = $('#filtro_buscar_proveedor').val();
    let id_origen = $('#filtro_buscar_origen').val();
    let id_tipo = $('#filtro_buscar_tipo').val();
    let id_laboratorio = $('#filtro_buscar_laboratorio').val();
    let id_marca = $('#filtro_buscar_marca').val();
    let id_rubro = $('#filtro_buscar_rubro').val();

    $('#tabla_buscar').bootstrapTable('refresh', { url: `${url}?q=ver_productos&id_proveedor=${id_proveedor}&id_origen=${id_origen}&id_tipo=${id_tipo}&id_laboratorio=${id_laboratorio}&id_marca=${id_marca}&id_rubro=${id_rubro}` });
}

$('#modal_buscar').on('shown.bs.modal', function (e) {
    let $tabla = $('#tabla_buscar');

    $('#modalBuscarLabel').html('Buscar Producto');
    $tabla.bootstrapTable('resetView');
    $tabla.bootstrapTable('resetSearch', '');
    $(this).find('select').val(null).trigger('change.select2');
    actualizarTablaBuscar();
    // $tabla.parents('.bootstrap-table').find('input[type="search"]').focus();
});

function alturaTablaBuscar() {
    return bstCalcularAlturaTabla(300, 300);
}
function pageSizeTablaBuscar() {
    return Math.floor(alturaTablaBuscar() / 35);
}

$("#tabla_buscar").bootstrapTable({
    toolbar: '#toolbar_buscar',
    search: true,
    showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaBuscar(),
    pageSize: pageSizeTablaBuscar(),
    sortName: "producto",
    sortOrder: 'asc',
    trimOnSearch: false,
    keyEvents: true,
    // singleSelect: true,
    clickToSelect: true,
    keyEvents: true,
    paginationParts: ['pageInfo', 'pageList'],
    rowStyle: rowStyleTablaBuscar,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true  },
            { field: 'id_producto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'laboratorio', title: 'LABORATORIO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'descuento_fraccionado', title: 'FRACCIONADO', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'principios_activos', title: 'PRINCIPIOS ACTIVOS', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'observaciones', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
}).on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarTablaTab3([row]);
});

function rowStyleTablaBuscar(row, index) {
    let id = row.id_producto;
    let tableData = $('#tabla_tab_3').bootstrapTable('getData');
    let data = tableData.find(value => value.id_producto == id);
    if (data) {
        return { classes: 'text-muted' };
    } else {
        return {};
    }
}

function cargarTablaTab3(data) {
    let tableData = $('#tabla_tab_3').bootstrapTable('getData');
    let repetidos = [];
    let map_data = [];

    $.each(data, function (index, value) {
        let id = value.id_producto;
        let row = tableData.find(value => value.id_producto == id);
        if (row) repetidos.push(row); 
        map_data.push({
            id_producto: value.id_producto,
            codigo: value.codigo,
            producto: value.producto,
            presentacion: value.presentacion,
            descuento_fraccionado: value.descuento_fraccionado,
            precio: value.precio,
            porcentaje: 0
        });
    });

    if (repetidos.length > 0) {
        alertDismissJS(`Producto '${repetidos[0].producto}' ya agregado`, 'error');
    } else {
        $('#tabla_tab_3').bootstrapTable('append', map_data);
        $('#modal_buscar').modal('hide');
    }
}

$('#btn_moda_buscar').on('click', function() {
    let selections = $('#tabla_buscar').bootstrapTable('getSelections');
    cargarTablaTab3(selections);
});

// Importar
$('#btn_importar_tab_2').on('click', function(e) {
    $('#type_inport').val('2');
});
$('#btn_importar_tab_3').on('click', function(e) {
    $('#type_inport').val('3');
});
$('#btn_importar_tab_4').on('click', function(e) {
    $('#type_inport').val('4');
});

$('#modal_importar').on('shown.bs.modal', function (e) {
    $('#modalImportarLabel').html('Importar Configuración');
    $('#importar').val(null).trigger('change');
    $(this).find('input[type!="hidden"], select, textarea').filter(':first').focus();

    let type = $('#type_inport').val();
    switch (type) {
        case '2': MousetrapModalTab2.pause(); break;
        case '3': MousetrapModalTab3.pause(); break;
    }
});
$('#modal_importar').on('hidden.bs.modal', function (e) {
    let type = $('#type_inport').val();
    switch (type) {
        case '2': MousetrapModalTab2.unpause(); break;
        case '3': MousetrapModalTab3.unpause(); break;
    }
});

$('#importar').select2({
    dropdownParent: $("#modal_importar"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'ver_campanas', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_descuento_pago, text: obj.descripcion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#btn_importar').on('click', function(e) {
    let type = $('#type_inport').val();
    let id = $('#importar').val();
    let tableData;

    if (!id) return false;

    switch (type) {
        case '2':
            tableData = $('#tabla_tab_2').bootstrapTable('getData');

            if (tableData.length > 0) {
                setTimeout(() => {
                    sweetAlertConfirm({
                        title: `Reemplazar Configuración`,
                        text: `¿Esta seguro de realizar esta acción?`,
                        confirm: function() {
                            $('#tabla_tab_2').bootstrapTable('refresh', { url: url+'?q=ver_filtros&id='+id });
                            $('#modal_importar').modal('hide');
                        }
                    });
                }, 100);
            } else {
                $('#tabla_tab_2').bootstrapTable('refresh', { url: url+'?q=ver_filtros&id='+id });
                $('#modal_importar').modal('hide');
            }
        break;
        case '3':
            tableData = $('#tabla_tab_3').bootstrapTable('getData');

            if (tableData.length > 0) {
                setTimeout(() => {
                    sweetAlertConfirm({
                        title: `Reemplazar Productos`,
                        text: `¿Esta seguro de realizar esta acción?`,
                        confirm: function() {
                            $('#tabla_tab_3').bootstrapTable('refresh', { url: url+'?q=ver_productos_descuento&id='+id });
                            $('#modal_importar').modal('hide');
                        }
                    });
                }, 100);
            } else {
                $('#tabla_tab_3').bootstrapTable('refresh', { url: url+'?q=ver_productos_descuento&id='+id });
                $('#modal_importar').modal('hide');
            }
        break;
        case '4':
            tableData = $('#tabla_tab_4').bootstrapTable('getData');

            if (tableData.length > 0) {
                setTimeout(() => {
                    sweetAlertConfirm({
                        title: `Reemplazar Productos`,
                        text: `¿Esta seguro de realizar esta acción?`,
                        confirm: function() {
                            $('#tabla_tab_4').bootstrapTable('refresh', { url: url+'?q=ver_productos_remate&id='+id });
                            $('#modal_importar').modal('hide');
                        }
                    });
                }, 100);
            } else {
                $('#tabla_tab_4').bootstrapTable('refresh', { url: url+'?q=ver_productos_remate&id='+id });
                $('#modal_importar').modal('hide');
            }
        break;
    }
});

$('#modal_buscar').find('select').on('select2:close', function(){
    $('#modal_buscar').focus();
});
