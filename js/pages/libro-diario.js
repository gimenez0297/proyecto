var url = 'inc/libro-diario-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#agregar').click() });

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
});

$('#libros').select2({
    placeholder: 'Libro',
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
            return { q: 'libros-diarios', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_libro_diario_periodo, text: obj.libro }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_libro=${$(this).val()}` })
}).on('select2:select', function(){
    var data = $('#libros').select2('data')[0];

    if (data) {
        $('#agregar').prop('disabled', false)
    }else{
        $('#agregar').prop('disabled', true)
    }
});

$('#comprobante').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'motivo-asiento', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_motivo_asiento, text: obj.descripcion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#plan').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'planes', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { 
                    id: obj.id_libro_cuenta, 
                    text: obj.denominacion,
                    tipo_cuenta: obj.tipo_cuenta}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    let disabled = (row.estado == 0) ? 'disabled' : '';
    let contraasiento = (row.contraasiento) ? 'disabled' : '';

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalles mr-1" title="Ver detalles"><i class="fas fa-list"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm anular mr-1" title="Anular" ${disabled} ${contraasiento}><i class="fas fa-times"></i></button>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-detalles': function(e, value, row, index) {
        $('#modal_detalles').modal('show');
        $('#toolbar_detalles_text').html(`Asiento N° ${row.nro_asiento}`);
        $('#tabla_detalles').bootstrapTable('refresh', { url: url+'?q=ver_detalles&id='+row.id_libro_diario});
    },
    'click .anular': function(e, value, row, index) {
        var nro_asiento = row.nro_asiento;
        sweetAlertConfirm({
            title: `Anular Asiento`,
            text: `¿Anular asiento '${nro_asiento}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=contra-asiento',
                    type: 'post',
                    data: { id: row.id_libro_diario},
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
}

$("#tabla").bootstrapTable({
    //url: url + '?q=ver',
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
    sortName: "id_libro_diario",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_libro_diario', title: 'ID Libro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'nro_asiento', title: 'Nro Asiento', align: 'left', valign: 'middle', sortable: true },
            { field: 'descripcion', title: 'Descripción', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'importe', title: 'Importe', align: 'right', valign: 'middle', sortable: true, visible: true, formatter: separadorMiles},
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
});

function iconosFilaAsiento(value, row, index) {
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>`,
    ].join('');
}

window.accionesFilaAsiento = {
    'click .eliminar': function (e, value, row, index) {
        var nombre = row.denominacion;
        sweetAlertConfirm({
            title: `Eliminar Asiento`,
            text: `¿Eliminar Asiento: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function () {
                $('#tabla_asientos').bootstrapTable('removeByUniqueId', row.id);
            }
        });
    }
}

$("#tabla_asientos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 200,
    sortName: "id",
    pagination: false,
    sortOrder: 'asc',
    showFooter: false,
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_libro_cuenta', title: 'ID Insumo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo_cuenta', title: 'Tipo Cuenta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'denominacion', title: 'Plan', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'debe', title: 'Debe', align: 'right', valign: 'middle', sortable: true, visible: true, formatter: separadorMiles},
            { field: 'haber', title: 'Haber', align: 'right', valign: 'middle', sortable: true, visible: true, formatter: separadorMiles},
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaAsiento, formatter: iconosFilaAsiento, width: 100 }
        ]
    ]
})

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Asiento');
    $('#formulario').attr('action', 'cargar_asiento');
    $('#eliminar').hide();
    resetForm('#formulario');
    $('#tabla_asientos').bootstrapTable('removeAll');;
    ultimo_correlativo_asiento();
});

$('#agregar-asiento').click(function () {
    var plan = $('#plan').select2('data')[0];
    var concepto = $('#concepto').val();
    var debe = $('#debe').val();
    var haber = $('#haber').val();

    if (plan == undefined) {
        alertDismissJsSmall('Debe seleccionar un plan para cargar.', 'error', 2000, () => $('#plan').focus());
        return;
    }

    if (concepto == '') {
        alertDismissJsSmall('Debe agregar un concepto para cargar.', 'error', 2000, () => $('#concepto').focus());
        return;
    }

    setTimeout(function () {
        $('#tabla_asientos').bootstrapTable('insertRow', {
            index: 1,
            row: {
                id: new Date().getTime(),
                id_libro_cuenta: plan.id,
                tipo_cuenta: plan.tipo_cuenta,
                denominacion: plan.text,
                concepto: concepto,
                debe: debe,
                haber: haber
            }
        });
    }, 100);
    $('#plan').val(null).trigger('change');
    $('#concepto').val('');
    $('#debe').val(0);
    $('#haber').val(0);
    $('#plan').focus();
});

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
    showFooter: false,
	height: 480,
    sortName: "id_libro_detalle",
    sortOrder: 'asc',
    columns: [
        [
            { field: 'id_libro_detalle', title: 'ID Libro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'denominacion', title: 'Denominación', align: 'left', valign: 'middle', sortable: true },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'debe', title: 'Debe', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
            { field: 'haber', title: 'Haber', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
        ]
    ]
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    data.push({ name: 'id_libro_diario', value: $('#libros').val()});
    data.push({ name: 'asientos', value: JSON.stringify($('#tabla_asientos').bootstrapTable('getData')) });
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
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function ultimo_correlativo_asiento() {
    $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: url,
        type: 'POST',
        data: { q: 'obtener_numero_asiento' },
        beforeSend: function () {
            //NProgress.start();
        },
        success: function (json) {
            $('#nro').val(json.numero);
        },
        error: function (xhr) {
            //NProgress.done();
        }
    });
}