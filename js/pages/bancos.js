var url = 'inc/bancos-data';

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
                    data: { id_banco: row.id_banco, estado },
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
    sortName: "banco",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_banco', title: 'ID Banco', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'banco', title: 'Banco', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
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
    $('#modalLabel').html('Agregar Bancos');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
});

$('#modal').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
});

function resetForm(form) {
    $(form).trigger('reset');
    $('#tab a[href="#datos-basicos"]').tab('show');
    $('#tabla_cuentas').bootstrapTable('removeAll');
    resetValidateForm(form);
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    if ($('#tabla_cuentas').bootstrapTable('getData').length == 0) {
        alertDismissJS('No ha cargado ninguna cuenta. Favor verifique.', "error");
        return false;
    }

    var data = new FormData(this);
    data.append('tabla_cuentas', JSON.stringify($('#tabla_cuentas').bootstrapTable('getData')));
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
    var id = $("#id_banco").val();
    var banco = $("#banco").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Banco: ${banco}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, banco },	
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
            $('#banco').val(data.razon_social);
        } else {
            var data = await buscarCI(ruc);
            if (data.cedula) {
                $('#ruc').val(data.cedula);
                $('#banco').val(data.nombre);

            } else {
                alertDismissJsSmall('RUC no encontrado', 'error', 2000);
            }
        }
    }
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
    $('#modalLabel').html('Editar Bancos');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_banco").val(row.id_banco);
    $("#banco").val(row.banco);
    $("#ruc").val(row.ruc);

    $('#tabla_cuentas').bootstrapTable('refresh', { url: url+'?q=ver_cuentas&id_banco='+row.id_banco });
}

function iconosFilaCuentas(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado-cuenta mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}

window.accionesFilaCuentas = {
    'click .eliminar': function (e, value, row, index) {
        sweetAlertConfirm({
            title: `Eliminar`,
            text: `¿Esta seguro de eliminar la cuenta seleccionada?`,
            closeOnConfirm: true,
            confirm: function() {
                $('#tabla_cuentas').bootstrapTable('removeByUniqueId', row.id_cuenta);
            }
        });
    },
    'click .cambiar-estado-cuenta': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 0 ? 1 : 0);
        var id_cuenta = row.id_cuenta;
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            // closeOnConfirm: false,
            confirm: function() {
                $('#tabla_cuentas').bootstrapTable('updateByUniqueId', { id: id_cuenta, row: { estado: estado, estado_str:sigteEstado} });
            }
        });
    }
}

$("#tabla_cuentas").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 150,
    sortName: "cuenta",
    sortOrder: 'asc',
    uniqueId: 'id_cuenta',
    columns: [
        [
            { field: 'id_cuenta', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'cuenta', title: 'Cuenta', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado', title: 'id estado', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado},
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaCuentas,  formatter: iconosFilaCuentas, width: 100 }
        ]
    ]
});

$('#agregar-cuenta').click(function() {
    var tableData = $('#tabla_cuentas').bootstrapTable('getData');
    var cuenta = $("#cuenta").val();

    if (tableData.find(value => value.cuenta == cuenta)) {
        alertDismissJS(`La cuenta ${cuenta} ya fue agregado.`, 'error');
        return;
    }

    if($("#cuenta").val() == ''){
        alertDismissJS('Debe escribir una cuenta para continuar.', 'error');
        return;
    }

    setTimeout(function()
        {
            $('#tabla_cuentas').bootstrapTable('insertRow', {
            index: 1,
                row: {
                    id_cuenta: new Date().getTime(),
                    cuenta: cuenta,
                    estado:0,
                    estado_str:'Activo',
                }
            });
        },100);
    
    $('#cuenta').val('');
    $('#cuenta').focus();
});
