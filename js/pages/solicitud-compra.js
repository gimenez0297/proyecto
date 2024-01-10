var url = 'inc/solicitud-compra-data';
var url_proveedores = 'inc/proveedores-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#btn_buscar').click() });
Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#codigo').focus() });
Mousetrap.bindGlobal('f3', (e) => { e.preventDefault(); $('#producto').focus() });
Mousetrap.bindGlobal('f4', (e) => { e.preventDefault(); $('#btn_guardar').click() });
Mousetrap.bindGlobal('f5', (e) => { e.preventDefault(); $('#faltantes').click() });


$(document).ready(function () {
    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla_faltantes").bootstrapTable('refreshOptions', {
                height: $(window).height() - 260,
                pageSize: Math.floor(($(window).height() - 260) / 35),
            });
        }, 100);
    });
    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla_faltantes2").bootstrapTable('refreshOptions', {
                height: $(window).height() - 260,
                pageSize: Math.floor(($(window).height() - 260) / 35),
            });
        }, 100);
    });

    MousetrapTableNavigationCell('#tabla_productos', Mousetrap, true);

    Mousetrap.bindGlobal('del', (e) => { 
        e.preventDefault();
        var data = $('#tabla_productos').bootstrapTable('getSelections')
        var row = data[0];
        if (data) {
            var nombre = row.producto;
            sweetAlertConfirm({
                title: `Eliminar Producto`,
                text: `¿Eliminar Producto: ${nombre}?`,
                confirmButtonText: 'Eliminar',
                confirmButtonColor: 'var(--danger)',
                confirm: function() {
                    $('#tabla_productos').bootstrapTable('removeByUniqueId', row.id_producto);
                }
            });
        }
    });
});
//Stock minimo
$('#stock-minimo-tab').on('shown.bs.tab', function (event) {
    $('#tabla_faltantes').bootstrapTable('resetView');
});
// Faltantes en solicitudes a este deposito
$('#faltantes-solicitud-tab').on('shown.bs.tab', function (event) {
    $('#tabla_faltantes2').bootstrapTable('resetView');
});

//noSubmitForm('#formulario');
resetWindow();
obtenerNumero()

enterClick('cantidad', 'agregar-producto');

// // Acciones por teclado
// $(window).on('keydown', function (event) {
//     switch (event.which) {
//         // F1 - Buscar proveedor
//         case 112:
//             event.preventDefault();
//             $('#btn_buscar').click();
//             break;
//         // F2 - Focus campo código
//         case 113:
//             event.preventDefault();
//             $('#codigo').focus();
//             break;
//         // F3 - Focus select productos
//         case 114:
//             event.preventDefault();
//             $('#producto').focus();
//             break;
//         // F4 - Generar solicitud
//         case 115:
//             event.preventDefault();
//             $('#btn_guardar').click();
//             break;
//     }
// });

// Buscar proveedor
$('#ruc').blur(async function () {
    var ruc = $(this).val();
    var data = {};
    var tipo_proveedor= 1;
    if (ruc) {
        var buscar_proveedor = await buscarProveedor(ruc, tipo_proveedor);
        if (buscar_proveedor.ruc) {
            data = buscar_proveedor;
        } else {
            data = {
                ruc,
                id_proveedor: '',
                proveedor: '',
                nombre_fantasia: ''
            }
            alertDismissJsSmall('Proveedor no encontrado', 'error', 2000);
        }
        cargarDatosProveedor(data);
    }
});

$("#tabla_proveedores").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_proveedores',
    search: true,
    showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 480,
    pageSize: 15,
    sortName: "proveedor",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_proveedor', title: 'ID PROVEEDOR', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'proveedor', title: 'PROVEEDOR / RAZÓN SOCIAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'nombre_fantasia', title: 'NOMBRE DE FANTASIA', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'obs', title: 'OBSERVACIONES', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#modal_proveedores').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_proveedores').bootstrapTable("refresh", { url: url_proveedores + `?q=ver&tipo_proveedor=1` }).bootstrapTable('resetSearch', '');
    keyboard_navigation_for_table_in_modal('#tabla_proveedores', this, cargarDatosProveedor);
});


$('#tabla_proveedores').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosProveedor(row);
});

function cargarDatosProveedor(data) {
    if (data.id_proveedor > 0) {
        $('#proveedor').select2('trigger', 'select', {
            data: { id: data.id_proveedor, text: data.proveedor }
        });
        $('#ruc').val(data.ruc);
    }
    //$('#proveedor').val(data.proveedor).attr('title', data.proveedor);
    $('#nombre_fantasia').val(data.nombre_fantasia).attr('title', data.nombre_fantasia);
    $('#id_proveedor').val(data.id_proveedor);
    $("#modal_proveedores").modal("hide");
    $('#codigo').focus();
}
// Fin buscar proveedor

// Productos
// BUSQUEDA DE PRODUCTOS
function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_presentacion) {
        $result = $(`<span>${node.text} <small>(${node.presentacion})</small></span>`);
    }
    return $result;
};

$('#producto').select2({
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
        data: function (params) {
            return { q: 'productos-solicitud', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) {
                    return {
                        id: obj.id_producto,
                        text: obj.producto,
                        codigo: obj.codigo,
                        id_presentacion: obj.id_presentacion,
                        presentacion: obj.presentacion,
                        stock: obj.stock,
                        recomendado: obj.recomendado,
                        minimo: obj.minimo,
                        maximo: obj.maximo
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
    var data = $('#producto').select2('data')[0] || {};
    $('#codigo').val(data.codigo || '');
    setTimeout(() => {
        if (data.id) {
            $('#cantidad').focus().select();
        }
    }, 1);
}).on('select2:close', function () {
    // Se activan las acciones por teclado de la tabla
    setTimeout(() => Mousetrap.unpause());
}).on('select2:open', function () {
    // Se desactivan las acciones por teclado de la tabla
    Mousetrap.pause();
});

$('#proveedor').select2({
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
            return { q: 'proveedores', term: params.term, page: params.page || 1, tipo_proveedor: 1 }
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
    var data = $('#proveedor').select2('data')[0] || {};
    $('#ruc').val(data.ruc || '');
    $('#id_proveedor').val(data.id || '');
    setTimeout(() => {
        if (data.id) {
            $('#codigo').focus().select();
        }
    }, 1);
});

// BUSQUEDA PRODUCTOS POR CODIGO
$('#codigo').keyup(function (e) {
    e.preventDefault();
    var codigo = $(this).val();
    if (e.keyCode === 13 && codigo) {
        $.ajax({
            dataType: 'json',
            cache: false,
            url: url_listados,
            type: 'POST',
            data: { q: 'buscar_producto_por_codigo', codigo },
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data) {
                NProgress.done();
                if (jQuery.isEmptyObject(data)) {
                    alertDismissJS('Código del producto no encontrado.', 'error');
                } else {
                    $('#producto').select2('trigger', 'select', {
                        data: { 
                            id: data.id_producto,
                            text: data.producto,
                            codigo: data.codigo,
                            id_presentacion: data.id_presentacion,
                            presentacion: data.presentacion,
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
///// FIN BUSQUEDA PRODUCTOS POR CODIGO /////

$('.filtro_proveedores').select2({
    placeholder: 'Proveedor',
    dropdownParent: $('#modal_faltantes'),
    width: '300px',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'proveedores', term: params.term, page: params.page || 1, tipo_proveedor: 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_proveedor, text: obj.proveedor }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});
$('#filtro_proveedores').on('change', function () {
    $('#tabla_faltantes').bootstrapTable('refresh', { url: url + `?q=stock-minimo&id_proveedor=${$(this).val()}` });
});
$('#filtro_proveedores2').on('change', function () {
    $('#tabla_faltantes2').bootstrapTable('refresh', { url: url + `?q=faltantes-solicitudes&id_proveedor=${$(this).val()}` });
});


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
            confirm: function () {
                $('#tabla_productos').bootstrapTable('removeByUniqueId', row.id_producto);
            }
        });
    }
}

$("#tabla_productos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 400,
    /* sortName: "codigo",
    sortOrder: 'asc', */
    uniqueId: 'id_producto',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'stock', title: 'Stock Actual', align: 'left', valign: 'middle', sortable: true },
            { field: 'minimo', title: 'Stock Minimo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'maximo', title: 'Stock Maximo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' } },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProducto, formatter: iconosFilaProducto, width: 100 }
        ]
    ]
}).on('click-cell.bs.table', function (e, field, value, row, $element) {
    setTimeout(() => $element.find('a').editable('toggle'), 10);
});

$.extend($('#tabla_productos').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '-',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:90px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_productos').on('editable-shown.bs.table', (e, field, row, $el, editable) => editable.input.$input[0].value = row[field] != 0 ? row[field] : '');

$('#tabla_productos').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id_producto;

    // Si la columna quedo en blanco
    if (!row[field]) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = separadorMiles(quitaSeparadorMiles(row[field]));
    }

    // Se actualizan los valores
    $('#tabla_productos').bootstrapTable('updateByUniqueId', { id, row: update_row });
});

$("#tabla_faltantes").bootstrapTable({
    url: url + '?q=stock-minimo',
    toolbar: '#toolbar_productos_faltantes',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    pagination: 'true',
    search: true,
    showFooter: false,
    height: $(window).height() - 260,
    pageSize: Math.floor(($(window).height() - 260) / 35),
    sortName: "producto",
    sortOrder: 'asc',
    uniqueId: 'id_producto',
    sidePagination: 'server',
    singleSelect: false,
    footerStyle: bstFooterStyle,
    keyEvents: true,
    showToggle: true,
    showRefresh: true,
    //paginationParts: ['pageInfo', 'pageList'],
    pageList: [10, 25, 50, 100, 200, 'All'],
    clickToSelect: true,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_producto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'stock', title: 'STOCK ACTUAL', align: 'left', valign: 'middle', sortable: true },
            { field: 'minimo', title: 'STOCK MINIMO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'maximo', title: 'STOCK MAXIMO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'recomendado', title: 'RECOMENDADO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});


$("#tabla_faltantes2").bootstrapTable({
    url: url + '?q=faltantes-solicitudes',
    toolbar: '#toolbar_productos_faltantes2',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    pagination: 'true',
    search: true,
    showFooter: false,
    height: $(window).height() - 260,
    pageSize: Math.floor(($(window).height() - 260) / 35),
    sortName: "producto",
    sortOrder: 'asc',
    uniqueId: 'id_producto',
    sidePagination: 'server',
    singleSelect: false,
    footerStyle: bstFooterStyle,
    keyEvents: true,
    showToggle: true,
    showRefresh: true,
    //paginationParts: ['pageInfo', 'pageList'],
    pageList: [10, 25, 50, 100, 200, 'All'],
    clickToSelect: true,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_producto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N° SOLICITUD', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'proveedor', title: 'PROVEEDOR', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'sucursal', title: 'SUCURSAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'stock', title: 'STOCK ACTUAL', align: 'left', valign: 'middle', sortable: true },
            { field: 'pendiente', title: 'PENDIENTE', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'recomendado', title: 'RECOMENDADO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#faltantes').on('click', function () {
    $('#modal_faltantes').modal('show');
    $('#tabla_faltantes').bootstrapTable('refresh');
    $('#tabla_faltantes2').bootstrapTable('refresh')
});

$('#agregar').on('click', function () {
    var tabla1 = $('#tabla_faltantes').bootstrapTable('getSelections');
    var tabla2 = $('#tabla_faltantes2').bootstrapTable('getSelections');
    var data = tabla1.concat(tabla2);
    agregarVariosProducto(data);
    $(".filtro_proveedores").val(null).trigger('change');
    $('#modal_faltantes').modal('hide');
});

$('#faltantes-solicitud-tab').on('shown.bs.tab', function (event) {
    $('#tabla_faltantes2').bootstrapTable('resetView');
});

$('#agregar-producto').on('click', function () {
    var data = $('#producto').select2('data')[0];
    var recomendado = data.stock;

    if (recomendado < 0) {
        recomendado = 0;
    }

    if (!data) {
        alertDismissJS('Debe seleccionar un producto', 'error');
    } else {
        var producto = {
            id_producto: data.id,
            codigo: data.codigo,
            producto: data.text,
            id_presentacion: data.id_presentacion,
            presentacion: data.presentacion,
            stock: data.stock,
            minimo: data.minimo,
            maximo: data.maximo,
        }
        agregarProducto(producto);
    }

});

function agregarVariosProducto(data) {
    $.each(data, function (index, value) {
        var tableData = $('#tabla_productos').bootstrapTable('getData');
        var cantidad = value.recomendado;
        var id_producto = value.id_producto;
        var producto = tableData.find(value => value.id_producto == id_producto);

        if (producto) {
            cantidad = quitaSeparadorMiles(producto.cantidad) + quitaSeparadorMiles(value.recomendado);
            $('#tabla_productos').bootstrapTable('removeByUniqueId', producto.id_producto);
        }
        if (cantidad < 0) {
            cantidad = 0;
        }

        $('#tabla_productos').bootstrapTable('insertRow', {
            index: ($('#tabla_productos').bootstrapTable('getOptions').totalRows + 1),
            row: {
                id_producto: value.id_producto,
                codigo: value.codigo,
                producto: value.producto,
                id_presentacion: value.id_presentacion,
                presentacion: value.presentacion,
                cantidad: cantidad,
                stock: value.stock,
                minimo:value.minimo,
                maximo:value.maximo,
            }
        });

    })
}

function agregarProducto(data) {
    var cantidad = quitaSeparadorMiles($('#cantidad').val());

    if (cantidad == 0) {
        setTimeout(() =>  alertDismissJS('La cantidad debe ser mayor a 0', 'error', () => $('#cantidad').focus()), 500); 
    } else {
        // Si se repite un producto se suman las cantidades
        var tableData = $('#tabla_productos').bootstrapTable('getData');
        var producto = tableData.find(value => value.id_producto == data.id_producto);
        if (producto) {
            cantidad += parseInt(producto.cantidad);
            $('#tabla_productos').bootstrapTable('removeByUniqueId', data.id_producto);
        }

        $('#tabla_productos').bootstrapTable('insertRow', {
            index: ($('#tabla_productos').bootstrapTable('getOptions').totalRows + 1),
            row: {
                id_producto: data.id_producto,
                codigo: data.codigo,
                producto: data.producto,
                id_presentacion: data.id_presentacion,
                presentacion: data.presentacion,
                cantidad: cantidad,
                stock: data.stock,
                minimo: data.minimo,
                maximo: data.maximo,
                recomendado: data.recomendado,
                cantidad: cantidad
            }
        });

        $('#cantidad').val(1);
        $('#producto').val(null).trigger('change');
        setTimeout(() => $('#codigo').focus(), 100);
    }
}

$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function (e) {
    e.preventDefault();
    let tableData = $('#tabla_productos').bootstrapTable('getSelections');
    let producto_cantidad_cero = tableData.find(value => parseInt(quitaSeparadorMiles(value.cantidad)) === 0);


    if ($('#id_proveedor').val() == '') {
        //alertDismissJS('Ningún producto agregado. Favor verifique.', "error");
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if ($('#tabla_productos').bootstrapTable('getData').length == 0) {
        alertDismissJS('Ningún producto agregado. Favor verifique.', 'error', () => $('#codigo').focus());
        return false;
    }else if (producto_cantidad_cero) {
        alertDismissJS(`El producto "${producto_cantidad_cero.producto}" tiene cantidad 0. Favor verifique.`, "error");
        return false;
    }

    if ($(this).valid() === false) return false;

    sweetAlertConfirm({
        title: `¿Guardar Solicitud?`,
        confirmButtonText: 'Guardar',
        closeOnConfirm: false,
        confirm: () => {
            var data = $(this).serializeArray();
            data.push({ name: 'productos', value: JSON.stringify($('#tabla_productos').bootstrapTable('getData')) });

            $.ajax({
                url: url + '?q=cargar',
                dataType: 'json',
                type: 'post',
                contentType: 'application/x-www-form-urlencoded',
                data: data,
                beforeSend: function () {
                    NProgress.start();
                },
                success: function (data, textStatus, jQxhr) {
                    NProgress.done();
                    if (data.status == 'ok') {
                        $('#tabla_productos').bootstrapTable('scrollTo', 'top');
                        alertDismissJS(data.mensaje, data.status, resetWindow);
                    } else {
                        alertDismissJS(data.mensaje, data.status);
                    }
                },
                error: function (jqXhr, textStatus, errorThrown) {
                    NProgress.done();
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });

});

function resetWindow() {
    $("input, textarea").val('');
    $("#cantidad").val(1);
    $("#producto").val(null).trigger('change');
    $("#proveedor").val(null).trigger('change');
    $('#tabla_productos').bootstrapTable('removeAll');
    $('html, body').animate({ scrollTop: 0 }, "slow");
    $("#ruc").focus();
    obtenerNumero();
}

function obtenerNumero() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'recuperar-numero' },
        beforeSend: function () { NProgress.start(); },
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
