var url = 'inc/cargas-premios-data';
var url_listados = 'inc/listados';

// Mask
$('#documento').mask('999-999-9999999');

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#btn_buscar').click() });

$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla_productos").bootstrapTable('refreshOptions', {
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
            var nombre = row.premio;
            sweetAlertConfirm({
                title: `Eliminar Premio`,
                text: `¿Eliminar Premio: ${nombre}?`,
                confirmButtonText: 'Eliminar',
                confirmButtonColor: 'var(--danger)',
                confirm: function() {
                    $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id_premio);
                }
            });
        }
    });

    // noSubmitForm('#formulario');
    resetWindow();
    obtenerNumero()

    enterClick('cantidad', 'agregar-premio');

});

$('#agregar-detalles').click(function() {
    if(!$('#emision').val()){
        $('#emision').val(moment().format('YYYY-MM-DD'));
    }
});

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

$('#id_proveedor').select2({
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
            return { q: 'proveedores_gastos_carga_insumo', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_proveedor, text: obj.proveedor, ruc: obj.ruc}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){

    var data = $('#id_proveedor').select2('data')[0] || {};

    if (data) {
        $('#ruc').val(data.ruc)
    }
});


// Acciones por teclado
$(window).on('keydown', function (event) {
    switch (event.which) {

         // F1 - Detalles de Factura
        case 112:
             event.preventDefault();
            $('#agregar-detalles').click();
            break;

        // F2 - Focus campo código
        case 113:
            event.preventDefault();
            $('#codigo').focus();
            break;

        // F3 - Focus select pemio
        case 114:
            event.preventDefault();
            $('#premios').focus();
            break;

        // F4 - Generar solicitud
        case 115:
            event.preventDefault();
            $('#btn_guardar').click();
            break;
    }
});


// Premios
// BUSQUEDA DE PREMIOS
function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_premio) {
        $result = $(`<span>${node.text} <small>(${node.premio})</small></span>`);
    }
    return $result;
};

$('#premios').select2({
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
            return { q: 'premios', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) {
                    return {
                        id: obj.id_premio,
                        text: obj.premio,
                        codigo: obj.codigo,
                        costo: obj.costo,
                        // puntos: obj.puntos
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
    var data = $('#premios').select2('data')[0] || {};
    $('#codigo').val(data.codigo || '');
    $('#costo').val(separadorMiles(data.costo) || '');
    // $('#puntos').val(data.puntos || '');
    // $('#vencimiento').val(data.vencimiento || '');


    setTimeout(() => {
        if (data.id) {
            $('#cantidad').val(1).focus();
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
            data: { q: 'buscar_premio_por_codigo', codigo },
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data) {
                NProgress.done();
                if (jQuery.isEmptyObject(data)) {
                    alertDismissJS('Código del premio no encontrado.', 'error');
                } else {
                    $('#premios').select2('trigger', 'select', {
                        data: { 
                            id: data.id_premio,
                            text: data.premio,
                            codigo: data.codigo,
                            costo:data.costo,
                            // puntos:data.puntos,
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
///// FIN BUSQUEDA PREMIOS POR CODIGO /////

$("#formulario_factura").validate({ ignore: '' });
$("#formulario_factura").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
});


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
        var nombre = row.premio;
        sweetAlertConfirm({
            title: `Eliminar Premio`,
            text: `¿Eliminar Premio: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function () {
                $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id_premio);
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
    sortName: "premios",
    sortOrder: 'asc',
    uniqueId: 'id_premio',
    singleSelect: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_premio', title: 'ID Premio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'premio', title: 'Premio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' }, width: 150  },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' }, width: 150  },
            { field: 'monto', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible:true},
            { field: 'cantidad_carga', title: 'Cantidad Carga', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' },visible:false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProducto, formatter: iconosFilaProducto, width: 100 }
            // { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, formatter: (value) => (value) ? fechaLatina(value) : '-'  },
            // { field: 'puntos', title: 'Puntos', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' }, width: 150  },
            // { field: 'puntos_carga', title: 'Puntos Carga', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' },visible:false },
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
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_detalle').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    var cantidad = quitaSeparadorMiles(row.cantidad);
    var costo_uni = quitaSeparadorMiles(row.costo);
    // var puntos = quitaSeparadorMiles(row.puntos);
    // var puntos_uni = quitaSeparadorMiles(row.puntos);


    // Columnas a actualizar
    let update_row = {};
    let id = row.id_premio;
    let numero = quitaSeparadorMiles(row[field]);

    // Si la columna quedo en blanco
    if (!numero) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = separadorMiles(numero);
    }

    if(row.cantidad && row.costo){
       update_row['monto'] = parseInt(cantidad) * parseInt(costo_uni);
    }else{
        update_row['monto'] = 0;
    }
  
    // Se actualizan los valores
    $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id, row: update_row });

});


$('#agregar-premio').on('click', function () {
    var data = $('#premios').select2('data')[0];
    var costo = $('#costo').val();
    // var puntos = $('#puntos').val();
    var cantidad = $('#cantidad').val();
    // var vencimiento = $('#vencimiento').val();
    
    if (!data) {
        alertDismissJS('Debe seleccionar un premio', 'error');
    }else if(cantidad == '' || cantidad == 0){
        setTimeout(() =>  alertDismissJS('La cantidad debe ser mayor a 0', 'error', () => $('#cantidad').val(1).focus()), 500); 
    }else if(costo == '' || costo == 0){
        setTimeout(() =>  alertDismissJS('El costo debe ser mayor a 0', 'error', () => $('#costo').val(1).focus()), 500); 
    }else{
        var premio = {
            id_premio: data.id,
            premio: data.text,
            codigo: data.codigo,
            cantidad: cantidad,
            costo: costo,
            // puntos: puntos,
            // vencimiento: vencimiento,
        }
        agregarProducto(premio);
        $('#premios').val(null).trigger('change');
        $('#codigo').focus(); 
        // $('#vencimiento').val(null).trigger('change');
    }

});

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

    if (vencimiento == '' ) {
        alertDismissJS('No puede agregar fechas vacias. Favor verifique.', 'error');
        return false;
    }

    if (tableData.find(value => value.vencimiento == vencimiento)) {
        $('#tabla_vencimientos').bootstrapTable('removeByUniqueId', vencimiento)
    }

    $('#tabla_vencimientos').bootstrapTable('insertRow', {
        index: 1,
        row: {
            vencimiento
        }
    });
    $('#id_vencimiento').val('');
    $('#id_vencimiento').focus();
});

function agregarProducto(data) {
    var cantidad = quitaSeparadorMiles($('#cantidad').val());
    var costo = quitaSeparadorMiles($('#costo').val());
    var monto = quitaSeparadorMiles($('#costo').val());
    var cantidad_carga =  quitaSeparadorMiles($('#cantidad').val());
    // var puntos = quitaSeparadorMiles($('#puntos').val());
    // var puntos_carga =  quitaSeparadorMiles($('#puntos').val());

    var total = costo * cantidad;

    if (cantidad == 0) {
        setTimeout(() =>  alertDismissJS('La cantidad debe ser mayor a 0', 'error', () => $('#cantidad').focus()), 500); 
    } else {
        // Si se repite un producto se suman las cantidades y se multiplican el costo y la cantidad
        var tableData = $('#tabla_detalle').bootstrapTable('getData');
        var premio = tableData.find(value => value.id_premio == data.id_premio);
        if (premio) {
            cantidad += parseInt(premio.cantidad);
            total = costo*cantidad;
            cantidad_carga = cantidad;
            // puntos += parseInt(premio.puntos);
            // puntos_carga = puntos;
            $('#tabla_detalle').bootstrapTable('removeByUniqueId', data.id_premio);
        }

        $('#tabla_detalle').bootstrapTable('insertRow', {
            index: 1,
            row: {
                id_premio: data.id_premio,
                codigo: data.codigo,
                premio: data.premio,
                costo:costo,
                cantidad: cantidad,
                monto:total,
                // puntos:puntos,
                // vencimiento: data.vencimiento,
            }
        });

        $('#cantidad').val(1);
        $('#premios').val(null).trigger('change');
        setTimeout(() => $('#btn_guardar').focus(), 100);
    }
}


$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function (e) {
    e.preventDefault();
    let tableData = $('#tabla_detalle').bootstrapTable('getData');

    let total = tableData.reduce((acc, value) => acc += quitaSeparadorMiles(value.monto), 0);
    var monto_factura = quitaSeparadorMiles($('#monto').val());
   
    $("#formulario_factura").submit();
    if ($('#formulario_factura').valid() === false) {
        $('#modal_factura_detalle').modal('show');
        return false;
    }

   
    if (tableData.length == 0) {
        alertDismissJS('Ningún premio agregado. Favor verifique.', 'error', () => $('#codigo').focus());
        return false;
    }
   
    if (ceiling(monto_factura,50) != ceiling(total,50)) {
        alertDismissJS('El monto de la factura no coincide con el costo total de los premios.', 'error');
        return false;
    }

    if ($(this).valid() === false) return false;

    sweetAlertConfirm({
        title: `¿Guardar Carga?`,
        confirmButtonText: 'Guardar',
        closeOnConfirm: false,
        confirm: () => {
            var f1 = $(this).serializeArray();
            var f2 = $('#formulario_factura').serializeArray()
            var data = f1.concat(f2);
            //var data = $(this).serializeArray();
            data.push({ name: 'premio', value: JSON.stringify($('#tabla_detalle').bootstrapTable('getData')) });
            data.push({ name: 'vencimientos', value: JSON.stringify($('#tabla_vencimientos').bootstrapTable('getData')) });

            $.ajax({
                url: url + '?q=cargar',
                dataType: 'json',
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                data: data,
                beforeSend: function () {
                    NProgress.start();
                },
                success: function (data, textStatus, jQxhr) {
                    NProgress.done();
                    if (data.status == 'ok') {
                        $('#tabla_detalle').bootstrapTable('scrollTo', 'top');
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



$('#iva_10, #iva_5, #exenta').on('keyup change', function (e) {
    var iva_10 = quitaSeparadorMiles($('#iva_10').val());
    var iva_5 = quitaSeparadorMiles($('#iva_5').val());
    var exenta = quitaSeparadorMiles($('#exenta').val());
    
    var total = iva_10 + iva_5 + exenta;
    $('#monto').val(separadorMiles(total));
})

function resetWindow() {
    resetForm('#formulario');
    resetForm('#formulario_factura');
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    $("#numero").focus();
    $("#observacion").val("");
    $("#premios").val(null).trigger('change');
    $("#cantidad").val(1);
    $("#costo").val(1);
    $("#codigo").val('');
    $('#tabla_detalle').bootstrapTable('removeAll');
    obtenerNumero();
}

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('input[type="checkbox"]').trigger('change');
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
        beforeSend: function () { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
            // $('#cantidad').val(data.cantidad);
            $('#numero').val(data.numero);
        },
        error: function (xhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
        }
    });
}


