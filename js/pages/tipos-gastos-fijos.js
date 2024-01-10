var url = 'inc/tipos-gastos-fijos-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#btn_buscar').click() });

function iconosFila(value, row, index) {
    return [
        //'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
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
                    data: { id_nombre: row.id_gasto_fijo_sub_tipo, estado },
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

$('#id_tipo_gasto').select2({
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
            return { q: 'tipos_gastos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_tipo_gasto, text: obj.nombre }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_tipo_factura').select2({
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
            return { q: 'tipos_comprobantes', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_tipo_comprobante, text: obj.nombre_comprobante }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});





$('#razon_social').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'proveedores_gastos', term: params.term, page: params.page || 1, tipo_proveedor: 2}
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { 
                    return { id: obj.id_proveedor, text: obj.proveedor, ruc: obj.ruc}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    let data = $('#razon_social').select2('data')[0];
    if (data) {
        $('#ruc').val(data.ruc)
        $('#id_proveedor').val(data.id)
    }
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
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_gasto_fijo_sub_tipo",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [ 
            { field: 'id_tipo_gastos', title: 'Tipo Gasto', align: 'left', valign: 'middle', sortable: false, sortable: true, visible: false },
            { field: 'descripcion', title: 'Nombre Gasto', align: 'left', valign: 'middle', sortable: false, sortable: true, visible: true, cellStyle: bstTruncarColumna },
            { field: 'id_proveedor', title: 'ID Proveedor', align: 'center', valign: 'middle', sortable: false, sortable: true, visible: false },
            { field: 'proveedor', title: 'Proveedor', align: 'left', valign: 'middle', sortable: false, sortable: true, visible: true, cellStyle: bstTruncarColumna },
            { field: 'id_tipo_factura', title: 'ID Tipo Factura', align: 'center', valign: 'middle', sortable: false, sortable: true, visible: false },
            { field: 'nombre_comprobante', title: 'Nombre Comprobante', align: 'left', valign: 'middle', sortable: false, sortable: true, visible: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'Ruc', align: 'right', valign: 'middle', sortable: false, sortable: true, visible: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'Monto', align: 'center', valign: 'middle', sortable: false, sortable: true, visible: false },
            { field: 'iva_10', title: 'Iva 10', align: 'center', valign: 'middle', sortable: false, sortable: true, visible: false },
            { field: 'iva_5', title: 'Iva 5', align: 'center', valign: 'middle', sortable: false, sortable: true, visible: false },
            { field: 'exenta', title: 'Exenta', align: 'center', valign: 'middle', sortable: false, sortable: true, visible: false },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: false, sortable: true, visible: true, cellStyle: bstTruncarColumna },
            { field: 'nombre', title: 'Nombre', align: 'left', valign: 'middle', sortable: false, sortable: true, visible: true, cellStyle: bstTruncarColumna },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: true, events: accionesFila,  formatter: iconosFila, width: 150 }
            


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
    $('#modalLabel').html('Agregar Gasto Fijo');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
});

$('#modal').on('shown.bs.modal', function (e) {
    $("#id_tipo_gasto").focus();
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

$('#btn_buscar').on('click', function(e) {
    var ruc = $('#ruc').val();
    var tipo_proveedor = 2;
    $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: url_listados,
        timeout: 10000,
        type: 'POST',
        data: { q:'buscar_proveedor_gasto', ruc, tipo_proveedor},
        beforeSend: function(){
            NProgress.start();
        },
        success: function (data) {
            NProgress.done();
            datos = data;
        },
        error: function (jqXhr) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
    
    if (datos){
        $('#razon_social').select2('trigger', 'select', {
            data: { id: datos.id_proveedor, text: datos.proveedor,ruc: datos.ruc }
        });
    }else{
        alertDismissJS('El proveedor no se encuentra cargado', "error");
        return false;
    }
});

/*SUMATORIA DE LOS INPUT PARA EL MONTO*/

$('#iva_10, #iva_5, #exenta').on('keyup change', function (e) {
    var iva_10 = quitaSeparadorMiles($('#iva_10').val());
    var iva_5 = quitaSeparadorMiles($('#iva_5').val());
    var exenta = quitaSeparadorMiles($('#exenta').val());
    var total = iva_10 + iva_5 + exenta;
    $('#monto').val(separadorMiles(total));
})

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
    var id = $("#id_gastos_fijos").val();
    //var descripcio = $("#nombre").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Gastos Fijos?`,
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
    $('#modalLabel').html('Editar Gastos Fijos');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_gastos_fijos").val(row.id_gastos_fijos);
    $("#id_tipo_gasto").val(row.id_tipo_gasto);
    $("#id_tipo_factura").val(row.id_tipo_factura);
    $("#concepto").val(row.concepto);
    $("#nombre").val(row.nombre);
    $("#iva_10").val(row.iva_10);
    $("#iva_5").val(row.iva_5);
    $("#exenta").val(row.exenta);
    $("#monto").val(row.monto);
    if (row.id_tipo_gastos> 0) {
        $('#id_tipo_gasto').select2('trigger', 'select', {
            data: { id: row.id_tipo_gastos, text: row.descripcion}
        });
    }
    if (row.id_tipo_factura> 0) {
        $('#id_tipo_factura').select2('trigger', 'select', {
            data: { id: row.id_tipo_factura, text: row.nombre_comprobante}
        });
    }
    if (row.id_proveedor > 0) {
        $('#razon_social').select2('trigger', 'select', {
            data: { id: row.id_proveedor, text: row.proveedor, ruc:row.ruc }
        });
    }
}
