var url = 'inc/movimiento-bancario-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#agregar').click() });

$('#tipo_movimiento').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
});

$('#id_banco').select2({
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
        data: function (params) {
            return { q: 'bancos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_banco, text: obj.banco }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

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


function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
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
    sortName: "id_movimiento_bancario",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_movimiento_bancario', title: 'ID movimiento bancario', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_comprobante', title: 'Fecha Movimiento', align: 'left', valign: 'middle', sortable: true, width: 100},
            { field: 'tipo_movimiento_str', title: 'Tipo Movimiento', align: 'left', valign: 'middle', sortable: true,width: 100 },
            { field: 'nro_comprobante', title: 'Nro Comprobante', align: 'left', valign: 'middle', sortable: true },
            { field: 'banco', title: 'Banco', align: 'left', valign: 'middle', sortable: true },
            { field: 'importe', title: 'Importe', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: true },
            { field: 'fecha_creacion', title: 'Fecha Creación', align: 'right', valign: 'middle', sortable: true,visible: false },
            { field: 'usuario', title: 'Usuario', align: 'right', valign: 'middle', sortable: true,visible: false },
            //{ field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Movimiento Bancario');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
    $('#fecha').val(moment().format('YYYY-MM-DD'));
});

$('#modal').on('shown.bs.modal', function (e) {
    $("#formulario input[type!='hidden']:first").focus();
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
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

// ELIMINAR
$('#eliminar').click(function() {
    var id = $("#id").val();
    var nombre = $("#concepto").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar el Movimiento Bancario: ${nombre}?`,
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

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Movimiento');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id").val(row.id_movimiento_bancario);
    $("#fecha").val(fechaMYSQL(row.fecha_comprobante));
    $("#tipo_movimiento").val(row.tipo_movimiento).trigger('change');
    $("#nro_comprobante").val(row.nro_comprobante);
    $("#importe").val(separadorMiles(row.importe));
    $("#concepto").val(row.concepto);
    $("#observacion").val(row.observacion);

    if (row.id_banco > 0) {
        $('#id_banco').select2('trigger', 'select', {
            data: { id: row.id_banco, text: row.banco}
        });
    }
    
}