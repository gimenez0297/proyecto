var url = 'inc/orden-compra-data.php';
var url_proveedores = 'inc/proveedores-data';
var url_listados = 'inc/listados';
// var url_administrar_compras= 'inc/administrar-compras-data';

resetWindow();

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla_productos").bootstrapTable('refreshOptions', { 
                height: alturaTablaProductos(),
            });
        }, 100);
    });

    // Acciones por teclado
    var MousetrapModalProveedores = new Mousetrap(document.querySelector('#modal_proveedores'));
    var MousetrapModalOrdenesCompra = new Mousetrap(document.querySelector('#modal_solicitudes'));

    Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#modal_solicitudes').modal('show') });
    Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#modal_proveedores').modal('show') });;
    Mousetrap.bindGlobal('f3', (e) => { e.preventDefault(); $('#btn_guardar').click() });

    MousetrapTableNavigationCell('#tabla_productos', Mousetrap);
    // MousetrapTableNavigationCell('#tabla_ordenes_compra', Mousetrap);

    MousetrapTableNavigation('#tabla_proveedores', MousetrapModalProveedores, cargarDatosProveedor);
    MousetrapTableNavigation('#tabla_ordenes_compra', MousetrapModalOrdenesCompra, cargarDatoSolicitudCompra);

    $('.modal').on('shown.bs.modal', (e) => Mousetrap.pause());
    $('.modal').on('hidden.bs.modal', (e) => Mousetrap.unpause());

});

// Buscar proveedor
$('#ruc').blur(async function() {
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
    keyEvents: true,
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
    $('#tabla_proveedores').bootstrapTable("refresh", {url: url_proveedores+`?q=ver&tipo_proveedor=1`}).bootstrapTable('resetSearch', '');
});


$('#tabla_proveedores').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosProveedor(row);
});

function cargarDatosProveedor(data) {
    $('#ruc').val(data.ruc);
    if (data.id_proveedor > 0) {
        $('#proveedor').select2('trigger', 'select', {
            data: { id: data.id_proveedor, text: data.ruc  +  '  |  '  + data.proveedor }
        });
        $('#ruc').val(data.ruc);
    }
    //$('#proveedor').val(data.proveedor).attr('title', data.proveedor);
    $('#nombre_fantasia').val(data.nombre_fantasia).attr('title', data.nombre_fantasia);
    $('#id_proveedor').val(data.id_proveedor);
    $("#modal_proveedores").modal("hide");
    // $('#tabla_productos').bootstrapTable('refresh', { url: `${url}?q=ver_productos&id_proveedor=${data.id_proveedor}` })
}

$('#proveedor').on('change', function (e){
    var data = $('#proveedor').select2('data')[0] || {};
    var id = data.id;
    // $('#tabla_productos').bootstrapTable('refresh', { url: `${url}?q=ver_productos&id_proveedor=${id}` })
})
// Fin buscar proveedor

// Productos
function alturaTablaProductos() {
    return bstCalcularAlturaTabla(430, 250);
}

function checkbox(data) {
    let checked = (data) ? 'checked' : '';
    return `<input type="checkbox" class="finalizar" ${checked}>`;
}

window.accionesFilaProducto = {
    'change .finalizar': function (e, value, row, index) {
        let new_value = (row.finalizar == 1) ? 0 : 1;
        $('#tabla_productos').bootstrapTable('updateByUniqueId', { id: row.id_solicitud_compra_producto, row: { finalizar: new_value } });
    }
}

$("#tabla_productos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
	height: alturaTablaProductos(),
    /* sortName: 'id_solicitud_compra',
    sortOrder: 'asc', */
    uniqueId: 'id_solicitud_compra_producto',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_solicitud_compra', title: 'ID Solicitud', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N° Solicitud', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'id_solicitud_compra_producto', title: 'ID Solicitud Compra Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'stock', title: 'Stock Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width: 100, editable: { type: 'text' } },
            { field: 'pendiente', title: 'Pendiente', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'solicitado', title: 'Solicitado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: false },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 150,footerFormatter: bstFooterSumatoria, width:100  },
            { field: 'costo_ultimo', title: 'Costo Ult.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 150,footerFormatter: bstFooterSumatoria },
            { field: 'porcentaje', title: 'Dif. %', align: 'right', valign: 'middle', sortable: true, width: 150 },
            { field: 'total_costo', title: 'T. Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width:100, editable: { type: 'text' } },
            { field: 'porc_desc', title: 'Desc. %', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'precio_venta', title: 'P. Público', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 150,footerFormatter: bstFooterSumatoria },
            { field: 'finalizar', title: 'Finalizar', align: 'center', valign: 'middle', sortable: false, formatter: checkbox, events: accionesFilaProducto  },
        ]
    ]
});

$.extend($('#tabla_productos').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '-',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:90px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_productos').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
});

$('#tabla_productos').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    var tableData = $('#tabla_productos').bootstrapTable('getData');
    var costo_tabla = parseInt(quitaSeparadorMiles(row.costo));

    if (!row.cantidad) {
        $('#tabla_productos').bootstrapTable('updateByUniqueId', { id: row.id_solicitud_compra_producto, row: { cantidad: 0 } });
    }

    if (quitaSeparadorMiles(row.cantidad) > quitaSeparadorMiles(row.pendiente)) {
        $('#tabla_productos').bootstrapTable('updateByUniqueId', { id: row.id_solicitud_compra_producto, row: { cantidad: oldValue || 0 } });
        alertDismissJsSmall('La cantidad supera la cantidad de productos pendientes', 'error', 2000)
        return;
    }


    // Se actualizan los costos del producto en todas las solicitudes
    $.each(tableData, function(index, value) {
        if (value.id_producto == row.id_producto) {
            var cantidad = parseInt(quitaSeparadorMiles(value.cantidad));
            var costo_uni = parseInt(quitaSeparadorMiles(value.costo));
            var total_costo = parseInt(quitaSeparadorMiles(value.total_costo));
            var precio_venta = parseInt(quitaSeparadorMiles(value.precio_venta));

            var costo_fin = 0;
            var co_ultimo = parseInt(quitaSeparadorMiles(value.costo_ultimo));

            if (total_costo != 0) {
                var costo = parseInt(total_costo) / parseInt(cantidad);
                var costo_f = Math.floor(costo);
                var co_ultimo = costo_f;

                if ( costo_f == 'Infinity' ) {
                    costo_f = costo_tabla;
                } 
               
                if(parseInt(quitaSeparadorMiles(value.costo_ultimo)) == 0){
                    var porcentaje = (costo_f - co_ultimo)/co_ultimo;
                    var co_ultimo = 0;

                }else{
                    var co_ultimo =quitaSeparadorMiles(value.costo_ultimo);
                    var porcentaje = ((costo_f - quitaSeparadorMiles(value.costo_ultimo))/quitaSeparadorMiles(value.costo_ultimo)*100);
                    
                    //asi queria igor
                    if(costo_f > precio_venta){
                        alertDismissJsSmall('El costo del producto no puede ser mayor al precio público', 'error', 2000)
                    }
                }

            }else{
                var total_costo = parseInt(costo_tabla) * parseInt(cantidad);
                var costo_f = costo_tabla;

                var porcentaje = 0;

            }          

            $('#tabla_productos').bootstrapTable('updateByUniqueId', {
                id: value.id_solicitud_compra_producto,
                row: {
                    costo: separadorMiles(costo_f),
                    total_costo: separadorMiles(total_costo),
                    costo_ultimo: separadorMiles(parseInt(co_ultimo)),
                    porcentaje: parseFloat(porcentaje.toFixed(2)),
                }
            });
        }
    });
});

$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function(e) {
    e.preventDefault();

    let tableData = $('#tabla_productos').bootstrapTable('getSelections');
    let producto_costo_cero = tableData.find(value => parseInt(quitaSeparadorMiles(value.costo)) === 0 && !value.finalizar);
    let producto_cantidad_cero = tableData.find(value => parseInt(quitaSeparadorMiles(value.cantidad)) === 0 && !value.finalizar);

    if ($('#id_proveedor').val() == '') {
        alertDismissJsSmall('Ningún proveedor agregado. Favor verifique', 'error', 2000)
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if (tableData.length == 0) {
        alertDismissJsSmall('Ningún producto agregado. Favor verifique', 'error', 2000)
        return false;
    } else if (producto_cantidad_cero) {
        alertDismissJS(`El producto "${producto_cantidad_cero.producto}" tiene cantidad 0. Favor verifique.`, "error");
        return false;
    } else if (producto_costo_cero) {
        alertDismissJS(`El producto "${producto_costo_cero.producto}" tiene costo 0. Favor verifique.`, "error");
        return false;
    }

    if ($(this).valid() === false) return false;

    sweetAlertConfirm({
        title: `¿Guardar Orden De Compra?`,
        confirmButtonText: 'Guardar',
        closeOnConfirm: false,
        confirm: () => {
            var data = $(this).serializeArray();
            data.push({ name: 'productos', value: JSON.stringify(tableData) });
            // setTimeout(function (){Swal.showLoading();},100) 

            $.ajax({
                url: url + '?q=cargar',
                dataType: 'json',
                type: 'post',
                contentType: 'application/x-www-form-urlencoded',
                data: data,
                beforeSend: function() {
                    NProgress.start();
                    loading();
                },
                success: function(data, textStatus, jQxhr){
                    NProgress.done();
                    if (data.status == 'ok') {
                        $('#tabla_productos').bootstrapTable('scrollTo', 'top');
                        alertDismissJS(data.mensaje, data.status, function(){
                            var param = { id_orden_compra: data.id_orden_compra};
                            OpenWindowWithPost("imprimir-compras.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirLiquidaciones", param);
                            resetWindow();
                        });
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
    $('input, textarea').val('');
    $('#condicion').val('1').trigger('change');
    $("#proveedor").val(null).trigger('change');
    $('#tabla_productos').bootstrapTable('removeAll');
    $('html, body').animate({ scrollTop: 0 }, "slow");
    $('#ruc').focus();
    obtenerNumero();
}

// Configuración select2
$('#condicion').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
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
                    return { id: obj.id_proveedor, text: obj.ruc + '   |   ' + obj.proveedor, ruc: obj.ruc}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    var data = $('#proveedor').select2('data')[0] || {};
    // $('#ruc').val(data.ruc || '');
    $('#id_proveedor').val(data.id || '');
});

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

// Buscar orden de compra
$("#tabla_ordenes_compra").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_ordenes_compra',
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
    sortName: "id_solicitud_compra",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    keyEvents: true,
    rowStyle: rowStyleTablaBuscar,
    columns: [
        [
            // { field: 'state', align: 'center', valign: 'middle', checkbox: true, formatter: checkbox },
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_solicitud_compra', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'proveedor', title: 'Proveedor / Razón Social', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'observacion', title: 'Observación', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
        ]
    ]
}).on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatoSolicitudCompra(row);
});

function rowStyleTablaBuscar(row, index) {
    let id = row.id_solicitud_compra;

    let tableData = $('#tabla_productos').bootstrapTable('getData');
    let data = tableData.find(value => value.id_solicitud_compra == id);
    if (data) {
        return { classes: 'text-muted' };
    } else {
        return {};
    }
}
$('#modal_solicitudes').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_ordenes_compra').bootstrapTable("refresh", {url: url+`?q=ver`}).bootstrapTable('resetSearch', '');
});
    function cargarDatoSolicitudCompra(data) {
        let id_solicitud_compra = data.id_solicitud_compra;
        
        if (id_solicitud_compra > 0) {
            let solicitud_compra = data.id_solicitud_compra;
            let numero = data.numero;
            let tableData = $('#tabla_productos').bootstrapTable('getData');
            let solicitud = tableData.find(value => value.id_solicitud_compra == solicitud_compra && value.numero == numero);

            if (solicitud) {
                alertDismissJsSmall('La solicitud ya fue agregada', 'error', 2000, () => $('#btn_buscar_solicitud').focus())
                return false;
            }
        
            $(".modal").modal("hide");
            $.ajax({
                dataType: 'json',
                cache: false,
                url: url,
                type: 'POST',
                data: { q: 'ver_productos', id_solicitud_compra },
                beforeSend: function () {
                    NProgress.start();
                },
                success: function (data) {
                    NProgress.done();
                    if (jQuery.isEmptyObject(data)) {
                        alertDismissJsSmall('No se pudo encontrar el proveedor', 'error', 1000)
                        return;
                    }
                    $('#tabla_productos').bootstrapTable('append', data)
                    $('#tabla_productos').bootstrapTable('scrollTo', 'bottom')   
                },
                error: function (xhr) {
                    NProgress.done();
                    alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
                }
            });
        }
    }

// Fin ordenes de compra
