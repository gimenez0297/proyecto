var url = 'inc/premios-data';
var url_listados = 'inc/listados';


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
                    data: { id_premio: row.id_premio, estado },
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
    sortName: "codigo",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_premio', title: 'ID Premio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true },
            { field: 'premio', title: 'Premio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'descripcion', title: 'Descripcion', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'puntos', title: 'Puntos', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
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
    $('#modalLabel').html('Agregar Premio ');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');

    $.ajax({
        dataType: 'json',
        async: false,
        type: 'post',
        url: url,
        cache: false,
        data: { q: 'ver_codigo'},   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();
            $("#codigo").val(data.codigo);
            if(data.status == 'error' ){
                alertDismissJS(data.mensaje, "error");
            }
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
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
    var id_premio = $("#id_premio").val();
    var premio = $("#premio").val();


    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Este Premio: ${premio}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id_premio, premio },	
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
    $('#modalLabel').html('Editar Premio');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');
    
    //Tab datos

    $("#id_premio").val(row.id_premio);
    $("#premio").val(row.premio);
    $("#codigo").val(row.codigo);
    $("#descripcion").val(row.descripcion);
    $("#costo").val(row.costo);
    $("#puntos").val(row.puntos);


    //Select2

    // if (row.producto == 1) {
    //     $("#producto").prop('checked', true).trigger('change');
    // }
    // if (row.id_tipo_insumo) {
    //     $('#tipo').select2('trigger', 'select', {
    //         data: { id: row.id_tipo_insumo, text: row.nombre}
    //     });
    // }


}

// $('#tipo').select2({
//     dropdownParent: $("#modal"),
//     placeholder: 'Seleccionar',
//     width: 'style',
//     allowClear: false,
//     selectOnClose: false,
//     ajax: {
//         url: url_listados,
//         dataType: 'json',
//         delay: 50,
//         cache: false,
//         async: true,
//         data: function(params) {
//             return { q: 'tipo_insumo', term: params.term, page: params.page || 1 }
//         },
//         processResults: function(data, params) {
//             params.page = params.page || 1;
//             return {
//                 results: $.map(data.data, function(obj) { return { id: obj.id_tipo_insumo, text: obj.nombre }; }),
//                 pagination: { more: (params.page * 5) <= data.total_count }
//             };
//         },
//         cache:false
//     }
// });



