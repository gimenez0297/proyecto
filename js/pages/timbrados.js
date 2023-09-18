var url = 'inc/timbrados-data.php';
var url_listados = 'inc/listados';

function verificarTimbrado() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar-timbrado' },
        beforeSend: function () { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
        },
        error: function (xhr) {
            NProgress.done();
        }
    });
};


$('#id_sucursal').select2({
    dropdownParent: $("#modal_principal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        async: true,
        data: function (params) {
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { 
                    return { id: obj.id_sucursal, text: obj.sucursal, deposito: obj.deposito }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on("select2:select", function(e){
    var data = $('#id_sucursal').select2('data')[0];

    if (data.deposito == 1) {
        $('#id_caja').prop('required', false);
        $('label[for="id_caja"]').removeClass('label-required');
    }else{
        $('#id_caja').prop('required', true);
        $('label[for="id_caja"]').addClass('label-required');
    }
    
});


$('#id_caja').select2({
    dropdownParent: $("#modal_principal"),
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
            return { q: 'cajas-select-timbrado', term: params.term, page: params.page || 1, id: $('#id_sucursal').val() }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_caja, text: obj.numero }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#tipo_documento').select2({
    dropdownParent: $("#modal_principal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
});
// .on("select2:select", function(e){
//     var data = $('#tipo_documento').select2('data')[0];
//     if (data.id !=0) {
//         $('#id_caja').prop('disabled', true);
//     }else{
//         $('#id_caja').prop('disabled', false);
//     }
// });

function iconos(value, row, index) {
    let disabled = (row.estado != '2') ? '' : 'disabled';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"${disabled}><i class="fas fa-sync-alt"></i></button> `,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos" ${disabled}><i class="fas fa-pencil-alt"></i></button> `
    ].join('');
}

window.acciones = {
    'click .cambiar-estado': function (e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 1 ? 0 : 1);
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function () {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id_timbrado: row.id_timbrado, estado },
                    beforeSend: function () {
                        NProgress.start();
                    },
                    success: function (data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function (jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    },
    'click .editar': function (e, value, row, index) {
        editar_tabla(row);
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
    classes: 'table table-hover table-condensed',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height() - 130,
    pageSize: Math.floor(($(window).height() - 130) / 50),
    sortName: "id_timbrado",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'ruc', title: 'R.U.C.', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'timbrado', title: 'Timbrado', align: 'center', valign: 'middle', sortable: true },
            { field: 'cod_establecimiento', title: 'Establecimiento', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'punto_de_expedicion', title: 'Expedición', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'nro_factura_desde', title: 'Documento Desde', align: 'center', valign: 'middle', sortable: true },
            { field: 'nro_factura_hasta', title: 'Documento Hasta', align: 'center', valign: 'middle', sortable: true },
            { field: 'inicio_vigencia_str', title: 'Inicio', align: 'center', valign: 'middle', sortable: true },
            { field: 'fin_vigencia_str', title: 'Fin', align: 'center', valign: 'middle', sortable: true },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true },
            { field: 'numero', title: 'Caja', align: 'left', valign: 'middle', sortable: true },
            { field: 'tipo_str', title: 'Tipo', align: 'center', valign: 'middle', sortable: true },
            { field: 'membrete', title: 'Membrete', align: 'center', valign: 'middle', sortable: true, visible: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'acciones', title: 'Acciones', align: 'center', width: 200, valign: 'middle', sortable: false, events: acciones, formatter: iconos },
        ]
    ]
}).on('load-success.bs.table', function (e, data, status, jqXHR) {
    $(this).bootstrapTable('resetView', {});
});

$(document).ready(function () {
    $('input.autonumeric').initNumber();

    $('#membrete').summernote({
        placeholder: 'Diseñe el membrete del documento',
        tabsize: 2,
        height: 200,
        lang: 'es-ES'
    });
});


$('#agregar').click(function () {
    $('#modalLabel').html('Agregar Timbrado');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
});


function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    $('#id_caja').prop('required', true);
    $('label[for="id_caja"]').addClass('label-required');
    resetValidateForm(form);
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function (e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url + '?q=' + $(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function () {
            NProgress.start();
        },
        success: function (data, textStatus, jQxhr) {
            NProgress.done();
            alertDismissJS(data.mensaje, data.status, data.id);
            if (data.status == 'ok') {
                $('#modal_principal').modal('hide');
                $('#tabla').bootstrapTable('refresh').bootstrapTable('refresh', {});
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});


function editar_tabla(row) {
    resetForm('#formulario');
    $('#formulario').attr('action', 'editar');
    $('#modalLabel').html('Editar Timbrado');
    $('#modal_principal').modal('show');

    if (row.id_sucursal > 0) {
        $('#id_sucursal').select2('trigger', 'select', {
            data: { id: row.id_sucursal, text: row.sucursal }
        });
    }

    if (row.id_caja > 0) {
        $('#id_caja').select2('trigger', 'select', {
            data: { id: row.id_caja, text: row.numero }
        });
    }

    $("#tipo_documento").val(row.tipo).trigger('change');
    $('#ruc').val(row.ruc);
    $('#timbrado').val(row.timbrado);
    $('#establecimiento').val(row.cod_establecimiento);
    $('#expedicion').val(row.punto_de_expedicion);
    $('#fecha_inicio').val(row.inicio_vigencia);
    $('#fecha_fin').val(row.fin_vigencia);
    $('#desde').val(row.desde);
    $('#hasta').val(row.hasta);
    $('#hidden_id').val(row.id_timbrado);
    $('#membrete').summernote('code', row.membrete);
}

$("#modal_principal").on("hidden.bs.modal", function () {
    $(".ui-helper-hidden-accessible").remove();
    $('.modal-body input[type=text], .modal-body input[type=date]').val('');
    $('#membrete').summernote('code', '');
    // $('#id_caja').prop('disabled', false);
});



// Acciones por teclado
$(window).on('keydown', function (event) {
    switch (event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
    }
});
