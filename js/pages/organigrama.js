var url = 'inc/organigrama-data';
var url_listados = 'inc/listados';

$('#id_area').select2({
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
            return { q: 'areas', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_area, text: obj.area }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_jefe').select2({
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
            return { q: 'jefe_organigrama', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_jefe, text: obj.jefe }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#filtro_area').select2({
    placeholder: 'Área',
    width: '400px',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'areas', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_area, text: obj.area }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_area=${$(this).val()}` })
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
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
                    data: { id_cargo: row.id_cargo, estado },
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
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_cargo",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_cargo', title: 'ID Area', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'cargo', title: 'Cargo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'jefe', title: 'Superior', align: 'left', valign: 'middle', sortable: true },
            { field: 'area', title: 'Área', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna,visible: false },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'confianza', title: 'Confianza', align: 'left', valign: 'middle', sortable: true, formatter: bstCondicional, width: 50 },
            { field: 'multiple_personas', title: 'Múltiple', align: 'left', valign: 'middle', sortable: true, formatter: bstCondicional, width: 50 },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 100 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 100 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
})

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
    $('#modalLabel').html('Agregar Organigrama');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
});

$('#modal').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

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
    var id = $("#id_cargo").val();
    var cargo = $("#cargo").val();
    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Area: ${area}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, cargo },    
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

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Organigrama');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_cargo").val(row.id_cargo);
    $("#cargo").val(row.cargo);

    if (row.id_area) {
        $('#id_area').select2('trigger', 'select', {
            data: { id: row.id_area, text: row.area }
        });
    }

    if (row.id_jefe) {
        $('#id_jefe').select2('trigger', 'select', {
            data: { id: row.id_jefe, text: row.jefe }
        });
    }

    if (row.confianza == 1) {
        $("#confianza").prop('checked', true).trigger('change');
    }
    if (row.multiple_personas == 1) {
        $("#multiple").prop('checked', true).trigger('change');
    }

}
