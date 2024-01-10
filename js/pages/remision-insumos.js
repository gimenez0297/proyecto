var url = 'inc/remision-insumos-data';
var url_listados = 'inc/listados';

$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla_detalle").bootstrapTable('refreshOptions', { 
                height: alturaTablaDetalle(),
            });
        }, 100);
    });

    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla_solicitudes").bootstrapTable('refreshOptions', {
                height: $(window).height() - 270,
                pageSize: Math.floor(($(window).height() - 270) / 35),
            });
        }, 100);
    });

    MousetrapTableNavigationCell('#tabla_detalle', Mousetrap, true);
    Mousetrap.bindGlobal('f5', (e) => { e.preventDefault(); $('#btn_guardar').click(); });

    Mousetrap.bindGlobal('del', (e) => { 
        e.preventDefault();
        var data = $('#tabla_detalle').bootstrapTable('getSelections')
        var row = data[0];
        if (data) {
            var nombre = row.producto;
            sweetAlertConfirm({
                title: `Eliminar Producto`,
                text: `¿Eliminar Producto: ${nombre}?`,
                confirmButtonText: 'Eliminar',
                confirmButtonColor: 'var(--danger)',
                confirm: function() {
                    $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id);
                    bloquearSelect();
                }
            });
        }
    });
    //noSubmitForm('#formulario');
    enterClick('cantidad', 'agregar-producto');
    resetWindow();
    $('#fecha_inicio').val(moment().format('YYYY-MM-DD'));
    $('#fecha_fin').val(moment().format('YYYY-MM-DD'));
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 - Buscar proveedor
        case 112:
            event.preventDefault();
            $('#btn_buscar').click();
            break;
        // F2 - Focus campo código
        case 113:
            event.preventDefault();
            $('#codigo').focus();
            break;
        // F3 - Focus select productos
        case 114:
            event.preventDefault();
            $('#producto').focus();
            break;
    }
});
$('#sucursal_origen').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    theme: 'bootstrap4-sm',
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
    }
}).on('change', function() {
    let data = $(this).select2('data')[0];
    if (data) {
        $('#btn_solicitudes').prop('disabled', false);
        $('#codigo').prop('disabled', false);
        $('#producto').prop('disabled', false);
        $('#vencimiento').prop('disabled', false);
        $('#cantidad').prop('disabled', false);
        $('#agregar-producto').prop('disabled', false);
    } else {
        $('#btn_solicitudes').prop('disabled', true);
        $('#codigo').prop('disabled', true);
        $('#producto').prop('disabled', true);
        $('#vencimiento').prop('disabled', true);
        $('#cantidad').prop('disabled', true);
        $('#agregar-producto').prop('disabled', true);
    }
}).on('select2:selecting', function() {
    $('#producto').find('option').remove();
    $('#vencimiento').find('option').remove();
    $('#codigo').val('');
})

$('#sucursal_destino').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    theme: 'bootstrap4-sm',
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
    }
}).on('change', function() {
    let data = $(this).select2('data')[0];
    if (data) {
        $.ajax({
            dataType: 'json',
            cache: false,
            url: url,
            type: 'POST',
            data: { q: 'datos-sucursal', id: data.id },
            beforeSend: function() {
                NProgress.start();
            },
            success: function (data) {
                NProgress.done();
                if (data) {
                    $('#ruc_destino').val(data.ruc);
                    $('#razon_social_destino').val(data.razon_social);
                    $('#direccion_destino').val(data.direccion);
                } else {
                    $('#ruc_destino, #razon_social_destino, #direccion_destino').val('');
                }
            },
            error: function (xhr) {
                NProgress.done();
                alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
            }
        });
    } else {
        $('#ruc_destino, #razon_social_destino, #direccion_destino').val('');
    }

});

$('#motivo').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    theme: 'bootstrap4-sm',
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'notas_remision_motivos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_nota_remision_motivo, text: obj.nombre_corto, descripcion: obj.descripcion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
    }
}).on('change', function() {
    let data = $(this).select2('data')[0];
    if (data && data.id == 1) {
        $('#sucursal_destino').val(null).trigger('change').prop('disabled', false).prop('required', true);
        $('label[for="sucursal_destino"]').addClass('label-required');
        $('#ruc_destino, #razon_social_destino, #direccion_destino').prop('readonly', true).prop('required', false);
        $('label[for="ruc_destino"], label[for="razon_social_destino"], label[for="direccion_destino"]').removeClass('label-required');
    } else {
        $('#sucursal_destino').val(null).trigger('change').prop('disabled', true).prop('required', false);
        $('label[for="sucursal_destino"]').removeClass('label-required');
        $('#ruc_destino, #razon_social_destino, #direccion_destino').prop('readonly', false).prop('required', true);
        $('label[for="ruc_destino"], label[for="razon_social_destino"], label[for="direccion_destino"]').addClass('label-required');
    }
});

// Productos
// BUSQUEDA DE PRODUCTOS
function formatResult(node) {	
    let $result = node.text;
    if(node.loading !== true && node.stock >= 0){
        $result = $(`<span>${node.text}<small> Stock(${node.stock})</small></span>`);
    }
    return $result;
};

$('#producto').select2({
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
        data: function(params) {
            return { q: 'productos_insumo', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_producto_insumo,
                        text: obj.producto,
                        codigo: obj.codigo,
                        stock: obj.stock,
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    },
	templateResult: formatResult,
    templateSelection: formatResult
}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
}).on('change', function() {
    var data = $('#producto').select2('data')[0] || {};
    $('#codigo').val(data.codigo || '');
    setTimeout(function(){
        if (data.id) {
            $('#vencimiento').val(null).trigger('change').focus();
        }
    },1);
    if (data.id) {
        $.ajax({
            url,
            dataType: 'json',
            type: 'GET',
            contentType: 'application/x-www-form-urlencoded',
            data: { q: 'ver_vencimiento', id_producto_insumo: $('#producto').val(), page: 1 },
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data, textStatus, jQxhr) {
                NProgress.done();
                if (data.total_count > 0) {
                    let obj = data.data[0];
                    $('#vencimiento').select2('trigger', 'select', {
                        data: {
                            id: obj.id_stock_insumo,
                            text: obj.vencimiento,
                            stock: obj.stock
                        }
                    });
                } else {
                    alertDismissJsSmall('Ningún vencimiento encontrado', 'error', 2000, () => $('#vencimiento').focus())
                }
            },
            error: function (jqXhr, textStatus, errorThrown) {
                NProgress.done();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), "error", 2000, () => $('#vencimiento').focus())
            }
        });
    }

}).on('select2:close', function () {
    // Se activan las acciones por teclado de la tabla
    setTimeout(() => Mousetrap.unpause());
}).on('select2:open', function () {
    // Se desactivan las acciones por teclado de la tabla
    Mousetrap.pause();
});

// BUSQUEDA PRODUCTOS POR CODIGO
$('#codigo').keyup(function (e) {
    e.preventDefault();
    var id = $('#sucursal_origen').val();
    var codigo = $(this).val();
    if (e.keyCode === 13 && codigo) {
        $.ajax({
            dataType: 'json',
            cache: false,
            url: url_listados,
            type: 'POST',
            data: { q: 'productos_insumos_codigo', codigo },
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data) {
                NProgress.done();
                if (jQuery.isEmptyObject(data)) {
                    alertDismissJS('Código del insumo no encontrado.', 'error');
                } else {
                    $('#producto').select2('trigger', 'select', {
                        data: { 
                            id: data.id_producto_insumo,
                            text: data.producto,
                            codigo: data.codigo,
                            stock: data.stock,
                        }
                    });
                }
            },
            templateResult: formatResult,
            templateSelection: formatResult,
            error: function (xhr) {
                NProgress.done();
                alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
            }
        });
        
    }
});

function formatResultvencimiento(node) {
    let $result = node.text;
    if (node.loading !== true && node.id) {
        $result = $(`<div class="d-flex justify-content-between">
                        <div class="overflow-hidden text-truncate">
                            <span>${node.text}</span>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-info">${node.stock}</span>
                        </div>
                    </div>`);
    }
    return $result;
};

$('#vencimiento').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'ver_vencimiento', id_producto_insumo: $('#producto').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_stock_insumo,
                        text: obj.vencimiento,
                        stock: obj.stock,
                    };
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    },
    templateResult: formatResultvencimiento,
    templateSelection: formatResultvencimiento
}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
}).on('select2:select', function() {
    let data = $('#vencimiento').select2('data')[0] || {};
    setTimeout(() => {
        if (data.id) {
            $('#cantidad').focus().select();
        } 
    }, 1);
});
///// FIN BUSQUEDA PRODUCTOS POR CODIGO /////

function alturaTablaDetalle() {
    return bstCalcularAlturaTabla(450, 300);
}

function iconosFilaProducto(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaProducto = {
    'click .eliminar': function (e, value, row, index) {
        var nombre = row.producto;
        sweetAlertConfirm({
            title: `Eliminar Producto`,
            text: `¿Eliminar Producto: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id);
                bloquearSelect();
            }
        });
    }
}

$("#tabla_detalle").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
	height: alturaTablaDetalle(),
    sortName: "id",
    sortOrder: 'desc',
    uniqueId: 'id',
    footerStyle: bstFooterStyle,
    singleSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'Insumo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_stock_insumo', title: 'Stock Insumo', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'stock', title: 'Stock', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' }  },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProducto, formatter: iconosFilaProducto, width: 100 }
        ]
    ]
}).on('click-cell.bs.table', function (e, field, value, row, $element) {
    setTimeout(() => $element.find('a').editable('toggle'), 10);
});

$.extend($('#tabla_detalle').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '-',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:90px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_detalle').on('editable-shown.bs.table', (e, field, row, $el, editable) => editable.input.$input[0].value = row[field] != 0 ? row[field] : '');
$('#tabla_detalle').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id;
    let id_producto= row.id_producto

    // Si la columna quedo en blanco
    if (!row[field]) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = separadorMiles(quitaSeparadorMiles(row[field]));
    }

    var tableData = $('#tabla_detalle').bootstrapTable('getData');

    var cantidad_agg = tableData.reduce((acc, value) => {
        if (value.id_producto == id_producto) {
            acc += quitaSeparadorMiles(value.cantidad);
        }
        return acc;
    }, 0);

    if (cantidad_agg > quitaSeparadorMiles(row.stock)) {
        setTimeout(() => alertDismissJS('No cuenta con stock suficiente del producto.', 'error'), 100);
        update_row[field] = oldValue;
    }
    // Se actualizan los valores
    $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id, row: update_row });

});

$('#agregar-producto').on('click', function() {
    let data = $('#producto').select2('data')[0];
    let data_vencimiento = $('#vencimiento').select2('data')[0];
    
    if (!data) {
        alertDismissJS('Favor seleccione un producto', 'error');
    } else if (!data_vencimiento) {
        alertDismissJS('Favor seleccione un vencimiento', 'error');
    } else {
        var producto = {
            id_producto: data.id,
            id_stock_insumo: data_vencimiento.id_stock_insumo,
            vencimiento: data_vencimiento.text,
            codigo: data.codigo,
            producto: data.text,
            stock: data_vencimiento.stock,
        }
        agregarProducto(producto);
    }
    
});

function agregarProducto(data) {
    var cantidad = quitaSeparadorMiles($('#cantidad').val());

    if (cantidad == 0) {
        setTimeout(() =>  alertDismissJS('La cantidad debe ser mayor a 0', 'error', () => $('#cantidad').focus()), 500); 
    } else {
        // Si se repite un producto se suman las cantidades
        var tableData = $('#tabla_detalle').bootstrapTable('getData');
        var id_producto = data.id_producto;
        var producto = tableData.find(value => value.id_producto == data.id_producto && value.vencimiento == data.vencimiento);

        if (producto) {
            cantidad += parseInt(producto.cantidad);
        }

        // var cantidad_agg = tableData.reduce((acc, value) => {
        //     if (value.id_producto == id_producto) {
        //         acc += quitaSeparadorMiles(value.cantidad);
        //     }
        //     return acc;
        // }, 0);
        
        if (quitaSeparadorMiles(cantidad) > quitaSeparadorMiles(data.stock)) {
            setTimeout(() => alertDismissJS('No puedes agregar el producto. Stock insuficiente', 'error', () => $('#cantidad').focus()), 500);
        }else{
            if (producto) {
                $('#tabla_detalle').bootstrapTable('removeByUniqueId', producto.id);
            }
            $('#tabla_detalle').bootstrapTable('insertRow', {
                index: 1,
                row: {
                    id: new Date().getTime(),
                    id_producto: data.id_producto,
                    id_stock_insumo: data.id_stock_insumo,
                    vencimiento: data.vencimiento,
                    stock: data.stock,
                    codigo: data.codigo,
                    producto: data.producto,
                    cantidad: cantidad,
                }
            });

        }

        $('#cantidad').val(1);
        $('#producto').val(null).trigger('change');
        $('#vencimiento').val(null).trigger('change');
        setTimeout(() => $('#codigo').focus(), 100);
        bloquearSelect();
    }
}

function bloquearSelect(){
    var data = $('#tabla_detalle').bootstrapTable('getData');
    
    if (data.length > 0) {
        $('#sucursal_origen').prop('disabled', true)
    }else{
        $('#sucursal_origen').prop('disabled', false)
    }
}

function checkbox_tabla_buscar(value, row, index, field) {
    let tableData = $('#tabla_detalle').bootstrapTable('getData');
    let data = tableData.find(value => value.id_solicitud_deposito == row.id_solicitud_deposito && value.id_producto == row.id_producto);
    return (data) ? { disabled: true } : value;
}

$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function(e) {
    e.preventDefault();

    resetValidateForm(this);
    if ($(this).valid() === false) {
        $('html, body').animate({ scrollTop: 0 }, "slow");
        return false;
    }
    if ($('#tabla_detalle').bootstrapTable('getData').length == 0) {
        alertDismissJS('Ningún producto agregado. Favor verifique', 'error', () => $('#codigo').focus());
        return false;
    }

    sweetAlertConfirm({
        title: `¿Guardar Nota De Remisión?`,
        confirmButtonText: 'Guardar',
        confirm: () => {
            var data = $(this).serializeArray();
            data.push({ name: 'sucursal_origen', value: $('#sucursal_origen').val() });
            data.push({ name: 'productos', value: JSON.stringify($('#tabla_detalle').bootstrapTable('getData')) });

            $.ajax({
                url: url + '?q=cargar',
                dataType: 'json',
                type: 'post',
                contentType: 'application/x-www-form-urlencoded',
                data: data,
                beforeSend: function() {
                    NProgress.start();
                },
                success: function(data, textStatus, jQxhr){
                    NProgress.done();
                    if (data.status == 'ok') {
                        var param = { id_nota_remision: data.id_nota_remision, imprimir : 'si', recargar: 'no' };
                        OpenWindowWithPost("imprimir-nota-remision", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirNotaRemision", param);
                        resetWindow();
                    } else {
                        alertDismissJS(data.mensaje, data.status);
                    }
                },
                error: function(jqXhr, textStatus, errorThrown){
                    NProgress.done();
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });

});

function resetWindow() {
    resetForm('#formulario');
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    $("#deposito").focus();
    $("#cantidad").val(1);
    $("#producto").val(null).trigger('change');
    $("#vencimiento").val(null).trigger('change');
    $('#motivo').select2('trigger', 'select', {
        data: { id: 1, text: 'TRASLADO', descripcion: 'TRASLADO ENTRE LOCALES DE LA MISMA EMPRESA' }
    });
    $('#tabla_detalle').bootstrapTable('removeAll');
    $('#sucursal_origen').prop('disabled', false)
    obtenerNumero();
}

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function obtenerNumero() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'recuperar-numero' },
        beforeSend: function() { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
            $('#numero').val(data.numero);
        },
        error: function (xhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
        }
    });
}
