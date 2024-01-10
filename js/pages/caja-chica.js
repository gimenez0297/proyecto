var url = 'inc/caja-chica-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#btn_buscar').click() });


$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla").bootstrapTable('refreshOptions', {
                height: alturaTabla(),
                pageSize: pageSizeTabla(),
            });
        }, 100);
    });

    $('#documento').mask('999-999-9999999');
});

function alturaTabla() {
    return bstCalcularAlturaTabla(300, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

$('#id_sucursal').select2({
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
            return { q: 'sucursales_caja_chica', term: params.term, page: params.page || 1 }
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
}).on('select2:selecting', function () {
    $('#id_caja').val(null).trigger('change').prop('disabled', false);
});

$('#id_sucursal_gasto').select2({
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

function formatResult(node) {
    let $result = node.text;
    let estado = node.estado_str;
    if (node.loading !== true && node.estado == 1) {
        $result = $(`<div class="d-flex justify-content-between">
                        <div class="overflow-hidden text-truncate">
                            <span>${node.text}</span>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-success">${estado}</span>
                        </div>
                    </div>`);
    } else if (node.loading !== true && node.estado == 2) {
        $result = $(`<div class="d-flex justify-content-between">
                        <div class="overflow-hidden text-truncate">
                            <span>${node.text}</span>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-danger">${estado}</span>
                        </div>
                    </div>`);
    }
    return $result;
};

$('#id_caja').select2({
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
            return { q: 'cajas_chicas_sucursal', term: params.term, page: params.page || 1, id: $('#id_sucursal').val() }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) {
                    return {
                        id: obj.id_caja_chica_sucursal,
                        text: obj.cod_movimiento,
                        estado: obj.estado,
                        estado_str: obj.estado_str,
                        saldo: obj.saldo,
                        sobrante: obj.sobrante,
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    },
    templateResult: formatResult,
    templateSelection: formatResult
}).on('change', function () {
    var data = $('#id_caja').select2('data')[0];
    if (data) {
        $('#saldo').val(separadorMiles(data.sobrante));
    }
    let id = $('#id_caja').val();
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_caja=${id}` })
}).on('select2:selecting', function (event) {
    $(this).find('option').remove();
}).on('select2:close', function () {
    habilitarBotones()
});



$('#tipo_proveedor').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
}).on('change', function () {
    let data = $('#tipo_proveedor').select2('data')[0];
    $('#ruc').val('');
    $('#razon_social').val(null).trigger('change');
    if (data) {
        $('#ruc').prop('readonly', false)
        $('#btn_buscar').prop('disabled', false)
        $('#razon_social').prop('disabled', false)
    } else {
        $('#ruc').prop('readonly', true)
        $('#btn_buscar').prop('disabled', true)
        $('#razon_social').prop('disabled', true)
    }
});

$('#tipo_g').select2({
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

$('#tipo_gasto').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
}).on('change', function () {
    let data = $('#tipo_gasto').val();
    if (data == '2') {
        gastosNoDeducibles();
    } else {
        gastosDeducibles();
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
            return { q: 'proveedores_gastos', term: params.term, page: params.page || 1, tipo_proveedor: $('#tipo_proveedor').val() }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) {
                    return { id: obj.id_proveedor, text: obj.proveedor, ruc: obj.ruc };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function () {
    let data = $('#razon_social').select2('data')[0];
    if (data) {
        $('#ruc').val(data.ruc)
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

$('#id_plan_cuenta').select2({
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
            return { q: 'planes', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_libro_cuenta, text: obj.denominacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

function iconosFila(value, row, index) {
    var data = $('#id_caja').select2('data')[0];
    let disabled = (data.estado != 1) ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar" ${disabled}><i class="fas fa-trash"></i></button>`
    ].join('&nbsp');
}

window.accionesFila = {
    // 'click .editar': function (e, value, row, index) {
    //     editarDatos(row);
    // },
    'click .eliminar': function (e, value, row, index) {
        sweetAlertConfirm({
            title: `Eliminar Gasto`,
            text: `¿Eliminar Gasto de la caja chica??`,
            closeOnConfirm: false,
            confirm: function () {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar-gasto',
                    type: 'post',
                    data: { id: row.id_gasto, id_caja: $('#id_caja').val() },
                    beforeSend: function () {
                        NProgress.start();
                    },
                    success: function (data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla').bootstrapTable('refresh');
                            $('#saldo').val(separadorMiles(data.saldo));
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
}

$('#tabla').bootstrapTable({
    //url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    showColumns: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    pagination: 'true',
    sidePagination: 'server',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: 'id_caja_chica_facturas',
    sortOrder: 'asc',
    trimOnSearch: false,
    showFooter: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_caja_chica_facturas', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'nro_gasto', title: 'Nro Gasto', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'fecha_emision', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'deducible', title: 'Tipo de Gasto', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'R.U.C.', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Razón Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: true, events: accionesFila, formatter: iconosFila, width: 200 }
        ]
    ]
});



$('#btn_cargar').click(function () {
    $('#modalLabel').html('Agregar Gasto');
    $('#formulario').attr('action', 'cargar');
    $('#condicion').val('1').trigger('change');
    //$('#eliminar').hide();
    resetForm('#formulario');
    //ultimo_correlativo_gasto()
    $('#emision').val(moment().format('YYYY-MM-DD'));
});

$('#btn_buscar').on('click', function (e) {
    var ruc = $('#ruc').val();
    var tipo_proveedor = $('#tipo_proveedor').val();

    $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: url_listados,
        timeout: 10000,
        type: 'POST',
        data: { q: 'buscar_proveedor_gasto', ruc, tipo_proveedor },
        beforeSend: function () {
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


    if (datos) {
        $('#razon_social').select2('trigger', 'select', {
            data: { id: datos.id_proveedor, text: datos.proveedor, ruc: datos.ruc }
        });
    } else {
        alertDismissJS('El proveedor no se encuentra cargado', "error");
        return false;
    }

});

$('#iva_10, #iva_5, #exenta').on('keyup change', function (e) {
    var iva_10 = quitaSeparadorMiles($('#iva_10').val());
    var iva_5 = quitaSeparadorMiles($('#iva_5').val());
    var exenta = quitaSeparadorMiles($('#exenta').val());

    var total = iva_10 + iva_5 + exenta;
    $('#monto').val(separadorMiles(total));
})

function gastosNoDeducibles() {
    $('#tipo_proveedor').prop('disabled', true).val(null).trigger('change');
    $('#timbrado').prop('disabled', true).val('');
    $('#documento').prop('disabled', true).val('');
    $('#id_tipo_factura').prop('disabled', true).val(null).trigger('change');
    $('#id_plan_cuenta').prop('disabled', true).val(null).trigger('change');
    $('#tipo_g').prop('disabled', true).val(null).trigger('change');
    $('#ruc').prop('disabled', true).val('');
    $('#monto').prop('readonly', false);
    $('#iva_10').prop('readonly', true);
    $('#iva_5').prop('readonly', true);
    $('#exenta').prop('readonly', true);
    $('#monto').val(0);
    $('#iva_10').val(0);
    $('#iva_5').val(0);
    $('#exenta').val(0);
    $('label[for=tipo_proveedor]').removeClass('label-required');
    $('label[for=ruc]').removeClass('label-required');
    $('label[for=razon_social]').removeClass('label-required');
    $('label[for=timbrado]').removeClass('label-required');
    $('label[for=documento]').removeClass('label-required');
    $('label[for=id_tipo_factura]').removeClass('label-required');
    $('label[for=id_plan_cuenta]').removeClass('label-required');
    $('label[for=iva_10]').removeClass('label-required');
    $('label[for=iva_5]').removeClass('label-required');
    $('label[for=exenta]').removeClass('label-required');
    $('label[for=tipo_g]').removeClass('label-required');
}

function gastosDeducibles() {
    $('#tipo_proveedor').prop('disabled', false).val(null).trigger('change');
    $('#timbrado').prop('disabled', false).val('');
    $('#documento').prop('disabled', false).val('');
    $('#id_tipo_factura').prop('disabled', false).val(null).trigger('change');
    $('#tipo_g').prop('disabled', false).val(null).trigger('change');
    $('#ruc').prop('disabled', false).val('');
    $('#monto').prop('readonly', true);
    $('#iva_10').prop('readonly', false);
    $('#iva_5').prop('readonly', false);
    $('#exenta').prop('readonly', false);
    $('#monto').val(0);
    $('#iva_10').val(0);
    $('#iva_5').val(0);
    $('#exenta').val(0);
    $('label[for=tipo_proveedor]').addClass('label-required');
    $('label[for=ruc]').addClass('label-required');
    $('label[for=razon_social]').addClass('label-required');
    $('label[for=timbrado]').addClass('label-required');
    $('label[for=documento]').addClass('label-required');
    $('label[for=id_tipo_factura]').addClass('label-required');
    $('label[for=iva_10]').addClass('label-required');
    $('label[for=iva_5]').addClass('label-required');
    $('label[for=exenta]').addClass('label-required');
    $('label[for=tipo_g]').addClass('label-required');
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function (e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    data.push({ name: 'sucursal_caja', value: $('#id_sucursal').val() });
    data.push({ name: 'caja_chica', value: $('#id_caja').val() });

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
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
                $('#saldo').val(separadorMiles(data.saldo));
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('#btn_rendir').on('click', function () {
    let id_caja = $('#id_caja').val();
    var param = {
        id_caja: $('#id_caja').val(),
    }
    sweetAlertConfirm({
        title: `Rendir Movimiento`,
        text: `¿Rendir Movimiento?`,
        closeOnConfirm: false,
        confirm: function () {
            $.ajax({
                dataType: 'json',
                url: url + '?q=rendir-caja',
                type: 'post',
                data: { id: id_caja },
                beforeSend: function () {
                    NProgress.start();
                },
                success: function (data, textStatus, jQxhr) {
                    NProgress.done();
                    if (data.status == 'ok') {
                        $('#tabla').bootstrapTable('refresh');
                        $('#saldo').val(0);
                        $('#id_sucursal').val(null).trigger('change');
                        $('#id_caja').val(null).trigger('change');
                    } else {
                        alertDismissJS(data.mensaje, data.status);
                    }

                    alertDismissJS(data.mensaje, data.status, function () {
                        OpenWindowWithPost("imprimir-rendicion", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirRendicion", param);
                    });
                },
                error: function (jqXhr, textStatus, errorThrown) {
                    NProgress.done();
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    })
})

function habilitarBotones() {
    var data = $('#id_caja').select2('data')[0];
    if (data.estado == 1) {
        $('#btn_cargar').prop('disabled', false);
        $('#btn_rendir').prop('disabled', false);
    } else {
        $('#btn_cargar').prop('disabled', true);
        $('#btn_rendir').prop('disabled', true);
    }

}

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('input[type="checkbox"]').trigger('change');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}
