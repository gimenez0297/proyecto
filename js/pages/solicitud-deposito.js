var url = 'inc/solicitud-deposito-data';
var url_proveedores = 'inc/proveedores-data';
var url_listados = 'inc/listados';

// Acciones por teclado
MousetrapModalProveedores = new Mousetrap();
MousetrapModalFaltantes = new Mousetrap();

MousetrapModalProveedores.pause();
MousetrapModalFaltantes.pause();

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#modal_proveedores').modal('show') });
Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#codigo').focus() });
Mousetrap.bindGlobal('f3', (e) => { e.preventDefault(); $('#producto').focus() });
Mousetrap.bindGlobal('f4', (e) => { e.preventDefault(); $('#btn_guardar').click() });
Mousetrap.bindGlobal('f5', (e) => { e.preventDefault(); $('#faltantes').click() });

MousetrapModalProveedores.bind('b', (e) => { e.preventDefault(); $('#modal_proveedores').find('input[type="search"]').focus(); });
MousetrapModalProveedores.bind('r', (e) => { e.preventDefault(); $('#tabla_proveedores').bootstrapTable('refresh'); });
MousetrapModalProveedores.bind('left', (e) => { e.preventDefault(); $('#tabla_proveedores').bootstrapTable('prevPage'); });
MousetrapModalProveedores.bind('right', (e) => { e.preventDefault(); $('#tabla_proveedores').bootstrapTable('nextPage'); });
MousetrapModalProveedores.bindGlobal(['up', 'down'], (e) => { e.preventDefault(); $('#modal_proveedores').find('input[type="search"]').blur(); });

MousetrapModalFaltantes.bind('b', (e) => { e.preventDefault(); $('#modal_faltantes').find('input[type="search"]').focus(); });
MousetrapModalFaltantes.bind('r', (e) => { e.preventDefault(); $('#tabla_faltantes').bootstrapTable('refresh'); });
MousetrapModalFaltantes.bind('left', (e) => { e.preventDefault(); $('#tabla_faltantes').bootstrapTable('prevPage'); });
MousetrapModalFaltantes.bind('right', (e) => { e.preventDefault(); $('#tabla_faltantes').bootstrapTable('nextPage'); });
MousetrapModalFaltantes.bindGlobal(['up', 'down'], (e) => { e.preventDefault(); $('#modal_faltantes').find('input[type="search"]').blur(); });

MousetrapTableNavigation('#tabla_proveedores', MousetrapModalProveedores, cargarDatosProveedor);
MousetrapTableNavigation('#tabla_faltantes', MousetrapModalFaltantes, agregarVariosProducto);

$('#modal_proveedores').on('shown.bs.modal', () => { Mousetrap.pause(); MousetrapModalProveedores.unpause(); });
$('#modal_proveedores').on('hidden.bs.modal', () => { Mousetrap.unpause(); MousetrapModalProveedores.pause(); });
$('#modal_faltantes').on('shown.bs.modal', () => { Mousetrap.pause(); MousetrapModalFaltantes.unpause(); });
$('#modal_faltantes').on('hidden.bs.modal', () => { Mousetrap.unpause(); MousetrapModalFaltantes.pause(); });

$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla_detalle").bootstrapTable('refreshOptions', {
                height: alturaTablaDetalle(),
            });
        }, 100);
    });

    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla_faltantes").bootstrapTable('refreshOptions', {
                height: $(window).height() - 200,
                pageSize: Math.floor(($(window).height() - 120) / 35),
            });
        }, 100);
    });

    MousetrapTableNavigationCell('#tabla_detalle', Mousetrap, true);

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
                    $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id_producto);
                }
            });
        }
    });

    // noSubmitForm('#formulario');
    enterClick('cantidad', 'agregar-producto');
    resetWindow();
});

$('#deposito').select2({
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
            return { q: 'depositos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
    }
});

// Productos
// BUSQUEDA DE PRODUCTOS
function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_presentacion) {
        $result = $(`<span>${node.text} <small>(${node.presentacion})</small></span>`);
    }
    return $result;
};

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
            $('#deposito').focus().select();
        }
    }, 1);
});

$("#tabla_proveedores").bootstrapTable({
    // url: url + '?q=ver',
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
        $('#modal_proveedores').modal('hide');
        $('#deposito').focus();
    }
}
// Fin buscar proveedor

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
        data: function (params) {
            return { q: 'productos', term: params.term, page: params.page || 1 }
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
                        presentacion: obj.presentacion
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
            error: function (xhr) {
                NProgress.done();
                alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
            }
        });
    }
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
            confirm: function () {
                $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id_producto);
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
    sortName: "codigo",
    sortOrder: 'asc',
    uniqueId: 'id_producto',
    footerStyle: bstFooterStyle,
    singleSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width: 100, editable: { type: 'text' } },
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

$('#tabla_detalle').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
});

$('#tabla_detalle').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id_producto;
    let numero = quitaSeparadorMilesFloat(row[field]);

    // Si la columna quedo en blanco
    if (!numero) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = separadorMiles(numero);
    }

    // Se actualizan los valores
    $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id, row: update_row });
});

$('#agregar-producto').on('click', function () {
    var data = $('#producto').select2('data')[0];

    if (!data) {
        alertDismissJS('Debe seleccionar un producto', 'error');
    } else {
        var producto = {
            id_producto: data.id,
            codigo: data.codigo,
            producto: data.text,
            id_presentacion: data.id_presentacion,
            presentacion: data.presentacion,
        }
        agregarProducto(producto);
        $('#producto').val(null).trigger('change');
        $('#codigo').focus();
    }

});

// Faltantes
// CALENDARIO
var fechaIni;
var fechaFin;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cb(start, end) {
    fechaIni = start.format('DD/MM/YYYY');
    fechaFin = end.format('DD/MM/YYYY');
    $('#filtro_fecha span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#desde").val(fechaMYSQL(fechaIni));
    $("#hasta").val(fechaMYSQL(fechaFin));
}

// Rango: Últimos 30 Días
cb(moment().subtract(29, 'days'), moment());

// Rango: Mes actual
// cb(moment().startOf('month'), moment().endOf('month'));

// Rango: dia actual
// cb(moment(), moment());

$('#filtro_fecha').daterangepicker({
    timePicker: false,
    opens: "right",
    format: 'DD/MM/YYYY',
    locale: {
        applyLabel: 'Aplicar',
        cancelLabel: 'Borrar',
        fromLabel: 'Desde',
        toLabel: 'Hasta',
        customRangeLabel: 'Personalizado',
        daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi','Sa'],
        monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"],
        firstDay: 1
    },
    ranges: {
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
        'Este Mes': [moment().startOf('month'), moment().endOf('month')],
        [meses[moment().subtract(1, 'month').format("M")]] : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        [meses[moment().subtract(2, 'month').format("M")]] : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
        [meses[moment().subtract(3, 'month').format("M")]] : [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')],
        [meses[moment().subtract(4, 'month').format("M")]] : [moment().subtract(4, 'month').startOf('month'), moment().subtract(4, 'month').endOf('month')],
        [meses[moment().subtract(5, 'month').format("M")]] : [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')]
    }
}, cb);

$('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) {
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#desde").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha').change();
}).on('change', function() {
    let desde = $('#desde').val();
    let hasta = $('#hasta').val();
    let id_proveedor = $('#proveedor').val();
    $('#tabla_faltantes').bootstrapTable('refresh', { url: `${url}?q=faltantes&desde=${desde}&hasta=${hasta}` });
});
// FIN CALENDARIO

$("#tabla_faltantes").bootstrapTable({
    // url: url + '?q=faltantes',
    toolbar: '#toolbar_faltantes',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    pagination: 'true',
    search: true,
    showFooter: false,
    height: $(window).height()-200,
    pageSize: Math.floor(($(window).height()-120)/35),
    sortName: "producto",
    sortOrder: 'asc',
    uniqueId: 'id_producto',
    sidePagination: 'server',
    singleSelect: false,
    footerStyle: bstFooterStyle,
    keyEvents: true,
    showToggle: true,
    showRefresh: true,
    clickToSelect: true,
    // singleSelect: true,
    pageList: [10, 25, 50, 100, 200, 'All'],
    rowStyle: rowStyleTablaFaltantes,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_producto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad_ventas', title: 'VENTAS', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'stock', title: 'STOCK ACTUAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'minimo', title: 'STOCK MINIMO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'maximo', title: 'STOCK MAXIMO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'recomendado', title: 'RECOMENDADO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    agregarVariosProducto([row]);
    $('#modal_faltantes').modal('hide');
});

function rowStyleTablaFaltantes(row, index) {
    let id = row.id_producto;

    let tableData = $('#tabla_detalle').bootstrapTable('getData');
    let data = tableData.find(value => value.id_producto == id);
    if (data) {
        return { classes: 'text-muted' };
    } else {
        return {};
    }
}

$('#agregar').on('click', function () {
    var data = $('#tabla_faltantes').bootstrapTable('getSelections');
    agregarVariosProducto(data);
    $('#modal_faltantes').modal('hide');
});


$('#faltantes').on('click', function () {
    $('#modal_faltantes').modal('show');
    $('#filtro_fecha').trigger('change');
});

$('#modal_faltantes').on('shown.bs.modal', function (e) {
    $('#modal_faltantes').find("input[type='search']").focus();
});

function agregarVariosProducto(data) {
    $.each(data, function (index, value) {
        var tableData = $('#tabla_detalle').bootstrapTable('getData');
        var productoAgregar = value.id_producto;
        var producto = tableData.find(value => value.id_producto == productoAgregar)
        if (producto) {
            alertDismissJS('Producto ya agregado', 'error');
        } else {
            $('#tabla_detalle').bootstrapTable('insertRow', {
                index: 1,
                row: {
                    id_producto: value.id_producto,
                    codigo: value.codigo,
                    producto: value.producto,
                    //id_presentacion: data.id_presentacion,
                    presentacion: value.presentacion,
                    cantidad: value.recomendado,
                }
            });
            $('#modal_faltantes').modal('hide');
        }
    });
    setTimeout(() => $('#codigo').focus(), 100);
}


function agregarProducto(data) {
    var cantidad = quitaSeparadorMiles($('#cantidad').val());

    if (cantidad == 0) {
        alertDismissJS('La cantidad debe ser mayor a 0', 'error');
    } else {
        // Si se repite un producto se suman las cantidades
        var tableData = $('#tabla_detalle').bootstrapTable('getData');
        var producto = tableData.find(value => value.id_producto == data.id_producto);
        if (producto) {
            cantidad += parseInt(producto.cantidad);
            $('#tabla_detalle').bootstrapTable('removeByUniqueId', data.id_producto);
        }

        $('#tabla_detalle').bootstrapTable('insertRow', {
            index: 1,
            row: {
                id_producto: data.id_producto,
                codigo: data.codigo,
                producto: data.producto,
                id_presentacion: data.id_presentacion,
                presentacion: data.presentacion,
                cantidad: cantidad,
            }
        });

        $('#cantidad').val(1);
        setTimeout(() => $('#codigo').focus(), 100);
    }
}

$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function (e) {
    e.preventDefault();

    if ($(this).valid() === false) {
        $('html, body').animate({ scrollTop: 0 }, "slow");
        return false;
    }
    
    var data = $(this).serializeArray();
    data.push({ name: 'productos', value: JSON.stringify($('#tabla_detalle').bootstrapTable('getData')) });

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
                $('#tabla_detalle').bootstrapTable('scrollTo', 'top');            
                    var param = { id_solicitud_deposito: data.id_solicitud_deposito };
                    OpenWindowWithPost("imprimir-solicitud-deposito.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirSolicitudDeposito", param);
                    resetWindow();
            } else {
                alertDismissJsSmall(data.mensaje, data.status, 2000)
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

});

function resetWindow() {
    resetForm('#formulario');
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    $("#ruc").focus();
    $("#ruc").val();
    $("#cantidad").val(1);
    $("#producto").val(null).trigger('change');
    $("#proveedor").val(null).trigger('change');
    $("#deposito").val(null).trigger('change');
    $('#tabla_detalle').bootstrapTable('removeAll');
    obtenerNumero();
}

function resetForm(form) {
    $(form).trigger('reset');
    resetValidateForm(form);
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
