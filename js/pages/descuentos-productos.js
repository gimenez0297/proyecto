var url = 'inc/descuentos-productos-data';
var url_listados = 'inc/listados';

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt mr-1"></i>Editar</button>'
    ].join('');
}

window.accionesFila = {
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo") ? "Inactivo" : "Activo";
        let estado = (row.estado == 1) ? 0 : 1;
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id: row.id_descuento_producto, estado },
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
    sortName: "fecha_inicio",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_descuento_producto', title: 'ID Descuento Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_inicio', title: 'Inicio', align: 'left', valign: 'middle', sortable: true, formatter: fechaLatina },
            { field: 'fecha_fin', title: 'Fin', align: 'left', valign: 'middle', sortable: true, formatter: fechaLatina },
            { field: 'porcentaje', title: 'Porcentaje', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterPorcentaje },
            { field: 'descripcion', title: 'Descripcón', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'clasificacion', title: 'Clasificación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 120 }
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
    $('#modalLabel').html('Agregar Descuento');
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
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();
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
    var id = $("#hidden_id").val();
    var nombre = $("#descripcion").val();

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
    $('#modalLabel').html('Editar Descuento');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#hidden_id").val(row.id_descuento_producto);
    $("#descripcion").val(row.descripcion);
    $("#porcentaje").val(row.porcentaje);
    $("#fecha_inicio").val(row.fecha_inicio);
    $("#fecha_fin").val(row.fecha_fin);

    // Select2
    if (row.id_producto) {
        $("#producto").append(new Option(row.producto, row.id_producto, true, true)).trigger('change');
    }
    if (row.id_marca) {
        $("#marca").append(new Option(row.marca, row.id_marca, true, true)).trigger('change');
    }
    if (row.id_clasificacion) {
        $("#clasificacion").append(new Option(row.clasificacion, row.id_clasificacion, true, true)).trigger('change');
    }
}


$('#producto').select2({
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
            return { q: 'productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_producto, text: obj.producto }; }),
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

$('#clasificacion').select2({
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
            return { q: 'clasificaciones_productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_clasificacion_producto, text: obj.clasificacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#proveedor').select2({
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
            return { q: 'proveedores', term: params.term, page: params.page || 1 }
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
});

$('#metodo_pago').select2({
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
                results: $.map(data.data, function(obj) { return { id: obj.id_metodo_pago, text: obj.metodo_pago }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

