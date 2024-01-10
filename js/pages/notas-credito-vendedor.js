var url = 'inc/notas-credito-vendedor-data.php';
var url_clientes = 'inc/clientes-data';
var url_listados = 'inc/listados';
var url_timbrado = 'inc/timbrados-data';
var url_facturacion = 'inc/facturacion-data';

var public_caja_abierta = false;

resetWindow();

$('#nro_factura').mask('999-999-9999999');

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
    var MousetrapModalFacturas = new Mousetrap(document.querySelector('#modal_proveedores'));

    Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#modal_facturas').modal('show') });
    Mousetrap.bindGlobal('f4', (e) => { e.preventDefault(); $('#btn_guardar').click() });
    MousetrapTableNavigationCell('#tabla_productos', Mousetrap);

    Mousetrap.bindGlobal('alt+d', (e) => { e.preventDefault(); $('#devolucion_importe').prop('checked', !$('#devolucion_importe').prop('checked')).trigger('change'); });

    MousetrapTableNavigation('#tabla_proveedores', MousetrapModalFacturas, cargarDatosFacturas);

    $('.modal').on('shown.bs.modal', (e) => Mousetrap.pause());
    $('.modal').on('hidden.bs.modal', (e) => Mousetrap.unpause());

});

// Buscar proveedor
$('#nro_factura').blur(async function() {
    var factura = $(this).val();
    var data = {};
    if (factura) {
        var buscar_factura = await buscarFactura(factura);
        if (buscar_factura.numero) {
            data = buscar_factura;
            cargarDatosFacturas(data);
            if(data.periodo == 2 ){
                alertDismissJsSmall('La factura seleccionada ya ha superado el periodo para la devolución.', 'error', 2000);
            }
            else if(data.ruc == '44444401-7'){
                alertDismissJsSmall('No puede generar una nota de crédito a las facturas SIN NOMBRE', 'error', 2000);
                cargarDatosFacturas(data);
            }
        } else {
            alertDismissJsSmall("Número de factura no encontrado o 'SIN NOMBRE'", 'error', 2000);
            data = {
                numero:'',
                id_cliente: '',
                razon_social: ''
            }
            cargarDatosFacturas(data);
        }
    }
});



$("#tabla_facturas").bootstrapTable({
   // url: url + '?q=ver',
    toolbar: '#toolbar_facturas',
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
    sortName: "fecha_ac",
    sortOrder: 'desc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    keyEvents: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true, formatter: checkbox_ },
            { field: 'id_factura', title: 'ID FACTURA', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_sucursal', title: 'ID SUCURSAL', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'SUCURSAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha_venta', title: 'FECHA', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C. / C.I.', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'razon_social', title: 'RAZÓN SOCIAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'metodos', title: 'METODOS', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'total_venta', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles, visible: !esCajero(), switchable: !esCajero() },
        ]
    ]
});

function checkbox_(value, row, index, field) {
    return (row.periodo == 2) ? { disabled: true } : value;                  
}

$('#modal_facturas').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_facturas').bootstrapTable("refresh", {url: url+`?q=ver_facturas`}).bootstrapTable('resetSearch', '');
});

$('#tabla_facturas').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    if (row.periodo == 2) {
        setTimeout( ()=> alertDismissJS('La factura seleccionada ya ha superado el periodo para la devolución.', 'error'),400);
        return false;
    }
    else if(row.ruc == '44444401-7'){
        alertDismissJS('No puede generar una nota de crédito a las facturas SIN NOMBRE','error' );
        cargarDatosFacturas(row);
    }
    else {
        cargarDatosFacturas(row);
    }
});

function cargarDatosFacturas(data) {
    $('#nro_factura').val(data.numero);
    $('#ruc').val(data.ruc);
    $('#cliente').val(data.razon_social);
    $('#id_factura').val(data.id_factura);
    $('#periodo').val(data.periodo);
    $("#modal_facturas").modal("hide");

    if(data.id_metodo_pago == 1){
        $("#devolucion_importe").attr('disabled', false);
    }else{
        $("#devolucion_importe").attr('disabled', true);
        $("#devolucion_importe").prop('checked', false).trigger('change');
    }

    $('#tabla_productos').bootstrapTable('refresh', { url: `${url}?q=ver_detalle&id=${data.id_factura}` })
}

// Productos
function alturaTablaProductos() {
    return bstCalcularAlturaTabla(325, 300);
}

$("#tabla_productos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
	height: alturaTablaProductos(),
    sortName: 'id_factura',
    sortOrder: 'asc',
    uniqueId: 'id_factura_producto',
    footerStyle: bstFooterStyle,
     columns: [
        [

            { field: 'check', align: 'center', valign: 'middle', checkbox: true, formatter: checkbox_prod },
            { field: 'id_factura_producto', title: 'ID FACTURA PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fraccionado', title: '', align: 'center', valign: 'middle', sortable: false, formatter: fraccionado },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'lote', title: 'LOTE', align: 'left', valign: 'middle', sortable: true },
            { field: 'vencimiento_lote', title: 'VENCIMIENTO', align: 'left', valign: 'middle', sortable: true },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'total_venta', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: !esCajero(), switchable: !esCajero() },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'devuelto', title: 'DEVUELTO', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'devolucion', title: 'DEVOLVER', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterSumatoria, formatter: separadorMiles, width: 150, editable: { type: 'text' } },
        ]
    ]
}).on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    let cantidad = quitaSeparadorMiles(row.cantidad);
    let devuelto = quitaSeparadorMiles(row.devuelto);
    let devolucion = quitaSeparadorMiles(row.devolucion);
    let real = cantidad-devuelto;
    if (devolucion > cantidad || devolucion <= 0) {
        devolucion = real;
    }
    // Se actualizan los valores
    $('#tabla_productos').bootstrapTable('updateByUniqueId', { id: row.id_factura_producto, row: { devolucion: separadorMiles(devolucion) } });
});

function fraccionado(data) {
    switch (parseInt(data)) {
        case 0: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-success" title="Entero">E</span></b>';
        case 1: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-info" title="Fraccionado">F</span></b>';
    }
}

function checkbox_prod(value, row, index, field) {
    if(value == 'true' ){ 
        value = true 
    } 
    return (row.cantidad == row.devuelto || (row.ruc) == '44444401-7' ) ? { disabled: true } : value;
}

$.extend($('#tabla_productos').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_productos').on('post-body.bs.table', function(e, data) {
    // Solo se puede editar los productos que aun no se hayan devuelto en su totalidad
    $.each(data, (index, value) => {
        if (value.cantidad == value.devuelto) {
            setTimeout(() => $('#tabla_productos').find(`tr[data-index=${index}]`).find('.editable').editable('disable'));
        }
    });
});


$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function(e) {
    e.preventDefault();
    let tabla_detalle = $('#tabla_productos').bootstrapTable('getSelections');
    let data = $(this).serializeArray();

    if ($(this).valid() === false) return false;

    if($('#ruc').val() == '44444401-7' ){
        alertDismissJS('No puede generar una nota de crédito a las facturas SIN NOMBRE', 'error');
        return false;
    }
    

    if (tabla_detalle.length == 0 ) {    
        alertDismissJS('Ningún producto seleccionado' , 'error');
        return false;

    }else if($("#periodo").val() == 2){
        
        setTimeout( ()=> alertDismissJS('La factura seleccionada ya ha superado el periodo para la devolución.', 'error'),400);
        $('').val(' ');
        return false;
    }

    sweetAlertConfirm({
        title: `¿Guardar Nota De Crédito?`,
        confirmButtonText: 'Guardar',
        confirm: () => {
            
            // data.push({ name: 'id', value: $("#id_factura").val() });
            data.push({ name: 'detalle', value: JSON.stringify(tabla_detalle) });

            $.ajax({
                url: url + '?q=cargar',
                dataType: 'json',
                type: 'post',
                contentType: 'application/x-www-form-urlencoded',
                data: data,
                beforeSend: function() {
                    NProgress.start();
                },
                success: function(data, textStatus, jQxhr) {
                    NProgress.done();
                    if (data.status == 'ok') {
                        var param = { id: data.id, imprimir : 'si', recargar: 'no' };
                        OpenWindowWithPost("imprimir-nota-credito", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirNotaRemision", param);
                        setTimeout( ()=> resetWindow(),100);
                    } else {
                        alertDismissJS(data.mensaje, data.status);
                    }
                },
                error: function(jqXhr, textStatus, errorThrown) {
                    NProgress.done();
                    alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
                }
            });
        }
    });
});

function resetWindow() {
    $('#nro_factura').val('');
    $('#cliente').val('');
    $('#tabla_productos').bootstrapTable('removeAll');
    $('#devolucion_importe').prop('checked', false).trigger('change');
    $('html, body').animate({ scrollTop: 0 }, "slow");
    $('#nro_factura').focus();
    verificar_estado_caja();
}

function verificar_estado_caja(show_alert = true) {
    $.ajax({
        url: url + '?q=verificar_caja',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: {},
        beforeSend: function () {
            NProgress.start();
        },
        success: function (data, textStatus, jQxhr) {
            NProgress.done();
            public_caja_abierta = data.caja_abierta;

            if (data.status != 'ok' && show_alert === true) {
                alertDismissJS(data.mensaje, data.status, () => $('#ruc').focus());
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            $('#btn_abrir_caja').show().prop('disabled', true);
            $('#btn_cerrar_caja').hide();
            alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
        }
    });
}

