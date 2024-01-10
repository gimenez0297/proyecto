var url = 'inc/cajas-data';
var url_listados = 'inc/listados';

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_caja mr-1" title="Eliminar Conexión"><i class="fas fa-minus-circle"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm asignar" title="Asignar Máquina"><i class="fas fa-cash-register"></i></button>'
    ].join('');
}

$('#filtro_sucursal').select2({
    placeholder: 'Sucursal',
    width: '200px',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&sucursal='+$('#filtro_sucursal').val() });
});

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
                    data: { id_caja: row.id_caja, estado },
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
    },
    'click .asignar': function (e, value, row, index) {
        asignarMaquina(row);
    }
    ,
    'click .eliminar_caja': function (e, value, row, index) {
            var id = row.id_caja;
            var numero = row.numero;
        
            sweetAlertConfirm({
                title: `Eliminar`,
                text: `¿Eliminar Conexión: ${numero}?`,
                closeOnConfirm: false,
                confirm: function () {
                    $.ajax({
                        dataType: 'json',
                        async: false,
                        type: 'POST',
                        url: url + '?q=eliminar_caja',
                        cache: false,
                        data: { id },
                        beforeSend: function () {
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
    sortName: "numero",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_caja', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N° Caja', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'tope_efectivo', title: 'Tope Efectivo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'efectivo_inicial', title: 'Sencillo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'ultima_conexion', title: 'Conexión', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200, visible: false },
            { field: 'estado_con', title: 'Conexión', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'descripcion', title: 'Descripción', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
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
    $('#modalLabel').html('Agregar Cajas');
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
    $('#tab a[href="#datos-basicos"]').tab('show');
    $('#tabla_usuarios').bootstrapTable('removeAll');
    resetValidateForm(form);
}

//usuarios
function iconosFilaDocumento(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado-usu mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}

window.accionesFilaDocumento = {
    'click .eliminar': function (e, value, row, index) {
        sweetAlertConfirm({
            title: `Eliminar`,
            text: `¿Esta seguro de eliminar al usuario seleccionado?`,
            closeOnConfirm: true,
            confirm: function() {
                $('#tabla_usuarios').bootstrapTable('removeByUniqueId', row.id_usuario);
            }
        });
    },
    'click .cambiar-estado-usu': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Habilitado" ? "Deshabilitado" : "Habilitado");
        let estado = (row.estado == 0 ? 1 : 0);
        var id_usuario = row.id_usuario;
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            // closeOnConfirm: false,
            confirm: function() {
                $('#tabla_usuarios').bootstrapTable('updateByUniqueId', { id: id_usuario, row: { estado: estado, estado_str:sigteEstado} });
            }
        });
    }
}

$("#tabla_usuarios").bootstrapTable({
   
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 250,
    sortName: "usuario",
    sortOrder: 'asc',
    uniqueId: 'id_usuario',
    columns: [
        [
            { field: 'id_usuario', title: 'ID usuario', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado', title: 'id estado', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado},
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaDocumento,  formatter: iconosFilaDocumento, width: 100 }
        ]
    ]
});

$('#agregar-usuario').click(function() {
    var tableData = $('#tabla_usuarios').bootstrapTable('getData');
    var id_usuario = $("#id_usuario").val();
    var usuario = $('select[name="id_usuario"] option:selected').text();

    if (tableData.find(value => value.id_usuario == id_usuario)) {
        alertDismissJS(`El usuario ${usuario} ya fue agregado.`, 'error');
        return;
    }

    if($("#id_usuario").val() == null){
        alertDismissJS('Debe seleccionar un usuario.', 'error');
        return;
    }

    setTimeout(function()
        {
            $('#tabla_usuarios').bootstrapTable('insertRow', {
            index: 1,
                row: {
                    id_usuario: id_usuario,
                    usuario: usuario,
                    estado:0,
                    estado_str:'Habilitado',
                }
            });
        },100);
    
    $('#id_usuario').val(null).trigger('change');
    
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    if ($('#tabla_usuarios').bootstrapTable('getData').length == 0) {
        alertDismissJS('Ningún usuario ha sido cargado. Favor verifique.', "error");
        return false;
    }

    var data = new FormData(this);
    data.append('tabla_usuarios', JSON.stringify($('#tabla_usuarios').bootstrapTable('getData')));
    $.ajax({
        url: url + '?q='+$(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        processData: false,
        contentType: false,
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
    var id = $("#id_caja").val();
    var numero = $("#numero").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar la caja N°: ${numero}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, numero },	
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
    $('#modalLabel').html('Editar Caja');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_caja").val(row.id_caja);
    $("#numero").val(row.numero);
    $("#observacion").val(row.observacion);
    $("#efectivo").val(separadorMiles(row.efectivo_inicial));
    $("#tope").val(separadorMiles(row.tope_efectivo));

    if (row.id_sucursal) {
        $("#id_sucursal").append(new Option(row.sucursal, row.id_sucursal, true, true)).trigger('change');
    }

    $('#tabla_usuarios').bootstrapTable('refresh', { url: url+'?q=ver_usuarios&id_caja='+row.id_caja });
}

function asignarMaquina(row) {
    $("#id_caja_asignacion").val(row.id_caja);
    $("#descripcion").val(row.descripcion);
    $('#modalAsignar').modal('show');
    
}

$('#guardarAsignar').on('click', () => $('#formularioAsignar').submit());
$("#formularioAsignar").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    
    if ($('#descripcion').val() == '') {
        alertDismissJS('Favor Ingresar una descripcion.', "error");
        return false;
        
    }
    // data.append($('#descripcion').val())
    $.ajax({
        url: url + '?q=asignar',
        dataType: 'json',
        async: false,
        type: 'POST',
        cache:false,
        contentType: 'application/x-www-form-urlencoded',
        data:  { id:$('#id_caja_asignacion').val(), descripcion: $('#descripcion').val() },
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, status, xhr) {
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modalAsignar').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operacion" + $(jqXhr.responseText).text().trim(), "error");
        }
    });

})

$('#id_sucursal').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_usuario').select2({
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
        data: function (params) {
            return { q: 'usuarios_cajeros', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_usuario, text: obj.nombre_usuario }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function(){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar_usuario', numero: $("#numero").val(), id_sucursal: $("#id_sucursal").val(), id_usuario: this.value },   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();

            if(data.status == 'error' ){
                alertDismissJS(data.mensaje, "error");
            }
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});