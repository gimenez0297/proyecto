var url = 'inc/proveedores-data';

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

$('#tipo_proveedor').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#filtro_tipo_proveedor').select2({
    placeholder: 'Tipo',
    width: '200px',
    allowClear: true,
    selectOnClose: false,
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&tipo_proveedor='+$(this).val() });
});

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
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_proveedor",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_proveedor', title: 'ID Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'proveedor', title: 'Proveedor / Razón Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'nombre_fantasia', title: 'Nombre De Fantasia', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'tipo_proveedor', title: 'ID Tipo Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo', title: 'Tipo Proveedor', align: 'center', valign: 'middle', sortable: true },
            { field: 'contacto', title: 'Contacto', align: 'left', valign: 'middle', sortable: true },
            { field: 'telefono', title: 'Teléfono', align: 'left', valign: 'middle', sortable: true },
            { field: 'direccion', title: 'Dirección', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'email', title: 'E-mail', align: 'left', valign: 'middle', sortable: true },
            { field: 'obs', title: 'Observación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'usuario', align: 'left', valign: 'middle', title: 'Usuario Carga', sortable: true, visible: false },
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
    $('#modalLabel').html('Agregar Proveedor');
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
    $('#filtro_tipo_proveedor').val(null).trigger('change');
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
    var id = $("#id_proveedor").val();
    var nombre = $("#proveedor").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Proveedor: ${nombre}?`,
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

$('#btn_buscar').on('click', async function(e) {
    var ruc = $('#ruc').val();

    if ($('#modal').is(':visible') && ruc) {
        var data = await buscarRUC(ruc);
        if (data.ruc) {
            $('#ruc').val(data.ruc+"-"+data.dv);
            $('#proveedor').val(data.razon_social);
            $('#telefono').val(data.telefono);
            $('#direccion').val(data.direccion);
        } else {
            alertDismissJsSmall('RUC no encontrado', 'error', 2000);
        }
    }
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which){
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
        // F2 Buscar RUC / CI
        case 113:
            event.preventDefault();
            $('#btn_buscar').click();
            break;
    }
});

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Proveedor');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_proveedor").val(row.id_proveedor);
    $("#ruc").val(row.ruc);
    $("#proveedor").val(row.proveedor);
    $("#nombre_fantasia").val(row.nombre_fantasia);
    $("#contacto").val(row.contacto);
    $("#telefono").val(row.telefono);
    $("#direccion").val(row.direccion);
    $("#email").val(row.email);
    $("#obs").val(row.obs);

    $("#tipo_proveedor").val(null).trigger('change');

    $.ajax({
        url: url,
        type: 'POST',
        data: { q: 'ver_tipos_proveedor', id_proveedor: row.id_proveedor },
        dataType: 'json',
        success: function(data) {
            if (data) {
                const dato = [];
                $.each(data, function(key, value) {
                    dato.push(value.tipo_proveedor);
                });
                $("#tipo_proveedor").val(dato).trigger('change');
            }
        }
    });
}

