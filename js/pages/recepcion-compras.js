var url = 'inc/recepcion-compras-data';
var url_listados = 'inc/listados';
var url_proveedores = 'inc/proveedores-data';
var url_administrar_compras = 'inc/administrar-compras-data';
var url_lotes = 'inc/lotes-data';

var public_id_orden_compra_producto;
var public_cantidad_pendiente;
var public_costo;
var public_total_costo;
var public_cantidad=1;

noSubmitForm('#formulario');

// Mask
$('#documento').mask('999-999-9999999');

function alturaTablaProductos() {
    return bstCalcularAlturaTabla(450, 300);
}

$(document).ready(function () {
    
    // Altura de tabla automatica
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla_productos").bootstrapTable('refreshOptions', { 
                height: alturaTablaProductos(),
            });
        }, 100);
    });

    // Oculta los campos de vencimiento
    $('#condicion').trigger('change');

    resetWindow();
});

$('#btn_buscar_ruc').on('click', async function(e) {
    var ruc = $('#ruc').val();

    if ($('#modal_factura_detalle').is(':visible') && ruc) {
        var data = await buscarRUC(ruc);
        if (data.ruc) {
            $('#ruc').val(data.ruc+"-"+data.dv);
            $('#entidad').val(data.razon_social);
        } else {
            alertDismissJsSmall('RUC no encontrado', 'error', 2000);
        }
    }
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 - Buscar orden de compra
        case 112:
            event.preventDefault();
            if ($('#modal_factura_detalle').is(':visible')) {
                $('#modal_vencimientos').modal('show');
            }else{
            $('.modal').modal('hide');
            $('#btn_buscar_orden_conpra').click();}
            break;
        // F2 - Focus proveedor
            case 113:
            event.preventDefault();
            $('.modal').modal('hide');
            $('#btn_buscar').click();
            break;
        // F3 - Focus select productos
        case 114:
            event.preventDefault();
            $('.modal').modal('hide');
            $('#producto').focus();
            break;
        // F4 - Guardar recepción de productos
        case 115:
            event.preventDefault();
            $('.modal').modal('hide');
            $('#btn_guardar').click();
            break;
        
    }
});

// Archivos
$("#tabla_archivos").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    sortName: "archivo",
    sortOrder: 'asc',
    showCustomView: true,
    customView: customViewFormatter,
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true },
            { field: 'archivo', title: 'Blob', align: 'left', valign: 'middle', sortable: true },
        ]
    ]
});

$('#tabla_archivos').on('custom-view-post-body.bs.table', function() {
    $('.btn-preview').click(function() {
        let id = $(this).val();
        $('#tabla_archivos').bootstrapTable('removeByUniqueId', id);
    });
});

function customViewFormatter(data) {
    let template = $('#template').html();
    let sin_archivos = '<div class="col-12 text-center pt-4">Ningún archivo seleccionado</div>';
    let border = (data.length > 0) ? '' : 'border';
    let view = (data.length > 0) ? '' : sin_archivos;

    $.each(data, function (i, row) {
        view += template.replace('%PREVIEW%', row.archivo).
            replace('%FANCY%', row.archivo).
            replace('%ID%', row.id);
    });

    return `<div class="row mx-0 ${border}" style="min-height: 480px;">${view}</div>`;
}

var min_archivos = 6;
var min_archivos_error = `La cantidad máxima de archivos que puede cargar es de ${min_archivos}`;

$('#archivos').change(function() {
    let cantidad_archivos = $('#tabla_archivos').bootstrapTable('getData').length;
    if (cantidad_archivos + this.files.length > min_archivos) {
        alertDismissJS(min_archivos_error, 'error');
    } else if (this.files && this.files[0]) {
        $.each(this.files, function(index, file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let base64 = e.target.result;

                $('#tabla_archivos').bootstrapTable('insertRow', {
                index: 1,
                    row: {
                        id: new Date().getTime(),
                        archivo: base64,
                    }
                });
            }

            reader.readAsDataURL(file);
        });
    }

    $(this).val('');
});

$('#agregar-archivos').click(function() {
    let tableData = $('#tabla_archivos').bootstrapTable('getData');
    if (tableData.length >= min_archivos) {
        alertDismissJS(min_archivos_error, 'error');
    } else {
        $('#archivos').click();
    }
});

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
    sortName: "id_orden_compra",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_orden_compra', title: 'ID Orden Compra', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'proveedor', title: 'PROVEEDOR / RAZÓN SOCIAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'total_costo', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'fecha', title: 'FECHA', align: 'left', valign: 'middle', sortable: true },
            { field: 'condicion_str', title: 'CONDICIÓN', align: 'left', valign: 'middle', sortable: true },
            { field: 'observaciones', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true, cellstyle: bstTruncarColumna },
        ]
    ]
});

$('#modal_ordenes_compra').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_ordenes_compra').bootstrapTable("refresh", { url: url_administrar_compras+`?q=ver&pendientes=1` }).bootstrapTable('resetSearch', '');
    keyboard_navigation_for_table_in_modal('#tabla_ordenes_compra', this, cargarDatosOrdenCompra);
}).on('hidden.bs.modal', function(){
    setTimeout(function(){
        $("#deposito").focus();
    }, 1);
});

$('#tabla_ordenes_compra').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosOrdenCompra(row);
});

function cargarDatosOrdenCompra(data) {
    let proveedor = data.proveedor;
    let orden_compra = `${data.numero || 'TODAS'} | ${data.ruc} | ${data.proveedor}`;
    $('#id_proveedor').val(data.id_proveedor);
    $('#orden_compra').val(orden_compra).attr('title', orden_compra);
    $('#condicion').val(data.condicion).trigger('change');
    $('#ruc').val(data.ruc);
    if (data.id_proveedor > 0) {
        $('#proveedor').select2('trigger', 'select', {
            data: { id: data.id_proveedor, text: data.ruc + '  |  ' + data.proveedor }
        });
        $('#ruc').val(data.ruc);
    }
    //$('#razon_social').val(proveedor);
    $(".modal").modal("hide");
    cargarTablaProductos(data.id_proveedor, data.id_orden_compra);

}
// Fin ordenes de compra

// Buscar proveedor
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
            { field: 'obs', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#modal_proveedores').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_proveedores').bootstrapTable("refresh", {url: url_proveedores+`?q=ver&tipo_proveedor=1`}).bootstrapTable('resetSearch', '');
    keyboard_navigation_for_table_in_modal('#tabla_proveedores', this, cargarDatosOrdenCompra);

}).on('hidden.bs.modal', function(){
    setTimeout(function(){
        $("#deposito").focus();
    }, 1);
    
});

$('#tabla_proveedores').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosOrdenCompra(row);
});
// Fin buscar proveedor

// Cargar lotes
function iconosFilaLotes(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar"><i class="fas fa-edit"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaLotes = {
    'click .eliminar': function (e, value, row, index) {
        let nombre = row.lote;
        sweetAlertConfirm({
            title: `Eliminar Lote`,
            text: `¿Eliminar Lote: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_lotes').bootstrapTable('removeByUniqueId', row.lote);
            }
        });
    },
    'click .editar': function (e, value, row, index) {
        $('#lote').val(row.lote);
        $('#lote_vencimiento').val(row.vencimiento);
        $('#cantidad').val(separadorMiles(row.cantidad));
        $('#canje').prop('checked', row.canje).trigger('change');
        $('#vencimiento_canje').val(row.vencimiento_canje);
        $('#tabla_lotes').bootstrapTable('removeByUniqueId', row.lote);
    }
}

$("#tabla_lotes").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 400,
    sortName: "lote",
    sortOrder: 'asc',
    uniqueId: 'lote',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal},
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, formatter: fechaLatina },
            { field: 'canje', title: 'Canje', align: 'left', valign: 'middle', sortable: true, formatter: formatterCanje },
            { field: 'vencimiento_canje', title: 'Vencimiento De Canje', align: 'left', valign: 'middle', sortable: true, formatter: (value) => (value) ? fechaLatina(value) : '-' },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterSumatoria, formatter: separadorMiles },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaLotes, formatter: iconosFilaLotes, width: 150 }
        ]
    ]
});

function formatterCanje(value, row, index, field) {
    return (value == 1) ? 'Si' : 'No';
}

$('#formulario_lote').validate({ ignore: ':hidden' });
$('#formulario_lote').submit(function(e) {
    e.preventDefault();

    var tableData = $('#tabla_lotes').bootstrapTable('getData');
    var lote = $('#lote').val();
    var vencimiento = $('#lote_vencimiento').val();
    var cantidad = parseInt(quitaSeparadorMiles($('#cantidad').val()));
    var canje = ($('#canje').is(':checked')) ? 1 : 0;
    var vencimiento_canje = $('#vencimiento_canje').val();
    var total = tableData.reduce((c, i) => c += parseInt(i.cantidad), 0);


    if (!lote) {
        // alertDismissJS('Debe ingresar la descripción del lote', 'error');
    }
    if (!vencimiento) {
        // alertDismissJS('Debe ingresar la fecha de vencimiento del lote', 'error');
    }
    if (total + cantidad > public_cantidad_pendiente) {
        alertDismissJS('El total supera a la cantidad pendiente de recepción', 'error', () => $('#cantidad').focus());
        return false;
    }
    if (canje == 1 && !vencimiento_canje) {
        // alertDismissJS('Debe completar el campo vencimiento de canje', 'error');
    }
    if (cantidad == 0) {
        alertDismissJS('La cantidad debe ser mayor a 0', 'error', () => $('#cantidad').focus()); 
        $('#cantidad').val('');
        return false;
    }else{
        var tableData = $('#tabla_lotes').bootstrapTable('getData');
        var producto = tableData.find(value => value.lote == lote );
        var vencimiento_ = tableData.find(value => value.vencimiento == vencimiento );
        if(producto && vencimiento_){
            cantidad += parseInt(producto.cantidad);
            $('#tabla_lotes').bootstrapTable('removeByUniqueId', lote);
        }
    }

    if ($(this).valid() === false) {
        $('#modal_lotes').animate({ scrollTop: 0 }, 'slow');
        return false;
    }

    if (tableData.find(value => value.lote == lote)) {
        $('#tabla_lotes').bootstrapTable('removeByUniqueId', lote)
    }

    $('#tabla_lotes').bootstrapTable('insertRow', {
        index: 1,
        row: {
            lote,
            vencimiento,
            cantidad,
            canje,
            vencimiento_canje,
        }
    });

    if ($('#lote').prop('disabled') === false) {
        resetForm(this);    
    }else{
        $('#lote').val();
        let lote_ = $('#lote').val();
        resetForm(this);
        $('#lote').val(lote_);
        public_cantidad ++;
    }

    $('#lote').focus();

    // resetForm(this);
    $('#canje').prop('checked', false).trigger('change');
});

$('#modal_lotes').on('shown.bs.modal', function (e) {
    $('#canje').prop('checked', false).trigger('change');
    $('#tabla_lotes').bootstrapTable('resetView');

    if ($('#lote').prop('disabled')) {
        $('#lote_vencimiento').focus();
    } else {
        $('#lote').focus();
    }
});

$('#modal_lotes').on('hidden.bs.modal', function (e) {
    let lotes = $('#tabla_lotes').bootstrapTable('getData');
    let recepcionado = lotes.reduce((c, i) => c += parseInt(quitaSeparadorMiles(i.cantidad)), 0);
    
    let total_costo = 0;
    if (public_cantidad_pendiente == recepcionado) {
        total_costo = public_total_costo;
    } else {
        total_costo = recepcionado * public_costo;
    }

    $('#tabla_productos').bootstrapTable('updateByUniqueId', { id: public_id_orden_compra_producto, row: { lotes: JSON.stringify(lotes), recepcionado, total_costo } })
});

// $('#iva').change(function(){
    
//     var iva = $('#iva').val();
//     var monto =  quitaSeparadorMiles($('#monto').val());

//     if (iva == 1) {
//         iva_5 = Math.round((monto / 21));
//         $('#iva_5').val(separadorMiles(iva_5));
//         $('#iva_10').val('0');
//         $('#extenta').val('0');
//     } else if (iva == 2) {
//         iva_10 = Math.round((monto / 11));
//         $('#iva_10').val(separadorMiles(iva_10));
//         $('#extenta').val('0');
//         $('#iva_5').val('0');
//     }else{
//         $('#extenta').val(separadorMiles(monto));
//         $('#iva_5').val('0');
//         $('#iva_10').val('0');
//     }
// })

$('#btn_cargar_detalle_factura').on('click', function (e) {
    if(!$('#emision').val()){
        $('#emision').val(moment().format('YYYY-MM-DD'));
    }
    
    let data = $('#tabla_productos').bootstrapTable('getData');
    let iva_10 = data.reduce(function (acc, value) {
        if (value.iva_str == '10%') {
            return acc += quitaSeparadorMiles(value.total_costo);
        }
        return acc;
    }, 0);
    let iva_5 = data.reduce(function (acc, value) {
        if (value.iva_str == '5%') {
            return acc += quitaSeparadorMiles(value.total_costo);
        }
        return acc;
    }, 0);
    let exenta = data.reduce(function (acc, value) {
        if (value.iva_str == 'EXENTAS') {
            return acc += quitaSeparadorMiles(value.total_costo);
        }
        return acc;
    }, 0);
    $('#iva_10').val(separadorMiles(iva_10));
    $('#iva_5').val(separadorMiles(iva_5));
    $('#exenta').val(separadorMiles(exenta));
    // let iva_10 = $('#iva_10').val();
    // let iva_5 = $('#iva_5').val();
    // let exenta = $('#exenta').val();

    var total = quitaSeparadorMiles(iva_10) + quitaSeparadorMiles(iva_5) + quitaSeparadorMiles(exenta);
    $('#monto').val(separadorMiles(total));
})

function cargarTablaLotes(row) {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url_lotes,
        cache: false,
        data: { q: 'obtener-lote', id_producto: row.id_producto, public_cantidad:public_cantidad},
        beforeSend: function() { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
            if (data.status == 'ok') {
                resetForm('#formulario_lote');
                public_id_orden_compra_producto = parseInt(row.id_orden_compra_producto);
                public_cantidad_pendiente = parseInt(quitaSeparadorMiles(row.pendiente));
                public_costo = parseInt(quitaSeparadorMiles(row.costo));
                public_total_costo = parseInt(quitaSeparadorMiles(row.costo_tt));
                $('#modal_lotes').modal('show');
                $('#tabla_lotes').bootstrapTable('removeAll');
                $('#tabla_lotes').bootstrapTable('load', JSON.parse(row.lotes || '[]'));
                $('#lote').prop('disabled', false);
                $('#lotes_producto').html(row.producto);
                if (data.lote) {
                    $('#lote').val(data.lote).prop('disabled', true);
                    let lotev = $('#lote').val(data.lote);
                    return lotev;
                }
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function (xhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
        }
    });
}

// Fin cargar lotes

// Productos
function cargarTablaProductos(id_proveedor, id_orden_compra = '') {
    $('#tabla_productos').bootstrapTable('refresh', { url: `${url}?q=ver_productos&id_proveedor=${id_proveedor}&id_orden_compra=${id_orden_compra}` })
}

function iconosFilaProducto(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm lotes" title="Ver lotes"><i class="fas fa-edit"></i></button>',
    ].join('');
}

window.accionesFilaProducto = {
    'click .lotes': function (e, value, row, index) {
        cargarTablaLotes(row);
    },
    'change .finalizado': function (e, value, row, index) {
        let new_value = (value == 1) ? 0 : 1;
        $('#tabla_productos').bootstrapTable('updateByUniqueId', { id: row.id_orden_compra_producto, row: { finalizado: new_value } });
    }
}

$("#tabla_productos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: alturaTablaProductos(),
    sortName: 'id_orden_compra',
    sortOrder: 'asc',
    uniqueId: 'id_orden_compra_producto',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_orden_compra', title: 'ID Orden Compra', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N° Orden Compra', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'id_orden_compra_producto', title: 'ID Orden Compra Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'recepcionado', title: 'Recepcionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'pendiente', title: 'Pendiente', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 150, editable: { type: 'text' } },
            { field: 'costo_ultimo', title: 'Costo Ult.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 150, visible: false},
            { field: 'total_costo', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'costo_tt', title: 'Precio Total', swichable: false, sortable: false, visible: false },
            { field: 'lotes', title: 'Lotes', align: 'center', valign: 'middle', sortable: true, visible: false },
            { field: 'iva_str', title: 'IVA', align: 'center', valign: 'middle', sortable: true, visible: true },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProducto, formatter: iconosFilaProducto, width: 100 }
        ]
    ]
});
$('#tabla_productos').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarTablaLotes(row);
});


$.extend($('#tabla_productos').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '-',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_productos').on('click-cell.bs.table', function(e, field, value, row, $element) {
    setTimeout(() => $element.find('a').editable('toggle'), 10);
});

$('#tabla_productos').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    var tableData = $('#tabla_productos').bootstrapTable('getData');
    var costo = parseInt(quitaSeparadorMiles(row.costo));

    // Se actualizan los costos del producto en todas las solicitudes
    $.each(tableData, function(index, value) {
        if (value.id_producto == row.id_producto) {
            var cantidad = parseInt(quitaSeparadorMiles(value.recepcionado));
            var total_costo =parseInt(costo) * parseInt(cantidad);

            $('#tabla_productos').bootstrapTable('updateByUniqueId', {
                id: value.id_orden_compra_producto,
                row: {
                    costo: separadorMiles(costo),
                    total_costo: separadorMiles(total_costo)
                }
            });
        }
    });
});



$('#formulario').submit(function(e) {
    e.preventDefault();

    let data = $('#tabla_productos').bootstrapTable('getData');
    let total = data.reduce((acc, value) => acc += quitaSeparadorMiles(value.total_costo), 0);

    var tableData = $('#tabla_productos').bootstrapTable('getData');
    var monto_factura = quitaSeparadorMiles($('#monto').val());

    if ($('#id_proveedor').val() == '') {
        // alertDismissJS('Ningún producto agregado. Favor verifique.', "error");
        $('html, body').animate({ scrollTop: 0 }, "slow");
        setTimeout(() => $('#btn_buscar_orden_conpra').focus(), 100);
    } else if (!$('#deposito').val()) {
        // alertDismissJS('Debe seleccionar un depósito', "error");
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if ($('#numero_documento').val() == '') {
        // alertDismissJS('Debe completar el campo número de documento', "error");
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if ($('#condicion').val() == '2' && $('#vencimiento_uno').val() == '') {
        // alertDismissJS('Debe completar el campo vencimiento', "error", () => $('#vencimiento').focus());
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if (tableData.length == 0) {
        alertDismissJS('Ningún producto agregado. Favor verifique.', 'error');
        return false;
    }
    $("#formulario_factura").submit();
    if ($('#formulario_factura').valid() === false) {
        $('#modal_factura_detalle').modal('show');
        return false;
    }

    if (ceiling(monto_factura,500) != ceiling(total,500)) {
        alertDismissJS('El monto de la factura no coincide con el costo total de los productos.', 'error');
        return false;
    }
    if ($(this).valid() === false) return false;

    sweetAlertConfirm({
        title: `¿Guardar Recepción?`,
        confirmButtonText: 'Guardar',
        closeOnConfirm: false,
        confirm: () => {
            
            var f1 = $(this).serializeArray();
            var f2 = $('#formulario_factura').serializeArray()
            var data = f1.concat(f2);
            data.push({ name: 'productos', value: JSON.stringify(tableData) });
            data.push({ name: 'archivos', value: JSON.stringify($('#tabla_archivos').bootstrapTable('getData')) });
            data.push({ name: 'vencimientos', value: JSON.stringify($('#tabla_vencimientos').bootstrapTable('getData')) });

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
                        $('#tabla_productos').bootstrapTable('scrollTo', 'top');
                        alertDismissJS(data.mensaje, data.status, resetWindow);
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
$("#formulario_factura").validate({ ignore: '' });
$("#formulario_factura").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
});

function resetWindow() {
    resetForm('form');
    $('#tabla_productos, #tabla_archivos').bootstrapTable('removeAll');
    $('html, body').animate({ scrollTop: 0 }, "slow");
    obtenerNumero();
}

// Configuración select2
$('#condicion').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('change', function(e) {
    $("#btn_vencimiento").prop('disabled', !($(this).val() == 2));
});

$('#id_tipo_factura').select2({
    dropdownParent: $("#modal_factura_detalle"),
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
                    return { id: obj.id_proveedor, text: obj.ruc + '   |   '  + obj.proveedor, ruc: obj.ruc}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    var data = $('#proveedor').select2('data')[0] || {};
    id_proveedor = data.id;
    id_orden_compra = '';
    $('#tabla_productos').bootstrapTable('refresh', { url: `${url}?q=ver_productos&id_proveedor=${id_proveedor}&id_orden_compra=${id_orden_compra}` })
    $('#ruc').val(data.ruc);
    $('#razon_social').val(data.text);
    $('#id_proveedor').val(data.id || '');
    setTimeout(() => {
        if (data.id) {
            $('#deposito').focus().select();
        } 
    }, 1);
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
        data: function(params) {
            return { q: 'depositos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
    }
});

$('#canje').on('change', function(e) {
    if ($(this).is(':checked')) {
        $('#cantidad, #lote_vencimiento').parent().removeClass('col-md-3').addClass('col-md-2');
        $('#vencimiento_canje').val('').parent().show();
        setTimeout(() => $('#vencimiento_canje').focus());
    } else {
        $('#cantidad, #lote_vencimiento').parent().removeClass('col-md-2').addClass('col-md-3');
        $('#vencimiento_canje').val('').parent().hide();
    }
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

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('input[type="checkbox"]').trigger('change');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function iconosFilaVencimientos(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar_ven mr-1" title="Editar"><i class="fas fa-edit"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_ven" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaVencimientos = {
    'click .eliminar_ven': function (e, value, row, index) {
        let nombre = row.vencimiento;
        sweetAlertConfirm({
            title: `Eliminar Vencimiento`,
            text: `¿Eliminar el Vencimiento: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_vencimientos').bootstrapTable('removeByUniqueId', row.vencimiento);
            }
        });
    },
    'click .editar_ven': function (e, value, row, index) {
        $('#id_vencimiento').val(row.vencimiento);
        $('#tabla_vencimientos').bootstrapTable('removeByUniqueId', row.vencimiento);
    }
}


$("#tabla_vencimientos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: false,
    height: 200,
    sortName: "vencimiento",
    sortOrder: 'asc',
    uniqueId: 'vencimiento',
    columns: [
        [
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, formatter: fechaLatina },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaVencimientos, formatter: iconosFilaVencimientos, width: 150 }
        ]
    ]
});


$('#agregar-vencimiento').click(function() {
    var tableData = $('#tabla_vencimientos').bootstrapTable('getData');
    var vencimiento = $('#id_vencimiento').val();

    if (tableData.find(value => value.vencimiento == vencimiento)) {
        $('#tabla_vencimientos').bootstrapTable('removeByUniqueId', vencimiento)
    }

    $('#tabla_vencimientos').bootstrapTable('insertRow', {
        index: 1,
        row: {
            vencimiento,
        }
    });
    $('#id_vencimiento').val('');
    $('#id_vencimiento').focus();
});

