var url = 'inc/permisos-data';

$('#periodo').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'auto',
    allowClear: false,
    selectOnClose: false,
});

$('#unidad').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'auto',
    allowClear: false,
    selectOnClose: false,
});

$('#relacionado_a').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'auto',
    allowClear: false,
    selectOnClose: false,
});

$('#goce_sueldo').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'auto',
    allowClear: false,
    selectOnClose: false,
});

$('#autenticada').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'auto',
    allowClear: false,
    selectOnClose: false,
});

$('#documentos').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'auto',
    allowClear: false,
    selectOnClose: false,
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
                    data: { id_permiso: row.id_permiso, estado },
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
    sortName: "concepto",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_permiso', title: 'ID ', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'concepto', title: 'Permiso', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width:300 },
            { field: 'cantidad', title: 'Cant.', align: 'right', valign: 'middle', sortable: true},
            { field: 'unidad_str', title: 'Tiempo', align: 'left', valign: 'middle', sortable: true},
            { field: 'periodo', title: 'Periodo', align: 'left', valign: 'middle', sortable: true},
            { field: 'relcionado_a_str', title: 'Relacionado', align: 'left', valign: 'middle', sortable: true},
            { field: 'validez', title: 'Validéz', align: 'right', valign: 'middle', sortable: true},
            { field: 'autenticada_str', title: 'Visación', align: 'left', valign: 'middle', sortable: true},
            { field: 'goce_sueldo_str', title: 'G. Sueldo', align: 'left', valign: 'middle', sortable: true},
            { field: 'documentos', title: 'Documentos', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
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
    $('#modalLabel').html('Agregar Permisos');
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
    var id = $("#id_permiso").val();
    var permiso = $("#concepto").val();
    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar permiso: ${permiso}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, permiso },    
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
    $('#modalLabel').html('Editar Permiso');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_permiso").val(row.id_permiso);
    $("#concepto").val(row.concepto);
    $("#cantidad").val(row.cantidad);
    $("#validez").val(row.validez);
    $("#obs").val(row.observacion);

    $("#unidad").val(row.unidad).trigger('change');
    $("#periodo").val(row.periodo).trigger('change');
    $("#relacionado_a").val(row.relacionado_a).trigger('change');
    $("#goce_sueldo").val(row.goce_sueldo).trigger('change');
    $("#autenticada").val(row.autenticada).trigger('change');

    $("#documentos").val(null).trigger('change');

    $.ajax({
        url: url,
        type: 'POST',
        data: { q: 'ver_permisos_documentos', id_permiso: row.id_permiso },
        dataType: 'json',
        success: function(data) {
            if (data) {
                const dato = [];
                $.each(data, function(key, value) {
                    dato.push(value.id_documento);
                });
                $("#documentos").val(dato).trigger('change');
            }
        }
    });
}
