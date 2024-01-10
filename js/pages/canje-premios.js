var url = 'inc/canje-premios-data';
var url_clientes = 'inc/clientes-data';
var url_listados = 'inc/listados';



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

    //Actualiza los estados de la tabla clientes puntos en comparacion al periodo_canje establecida en plan puntos
    actualiza_estado_puntos_cliente();

    MousetrapTableNavigationCell('#tabla_productos', Mousetrap, true);

    Mousetrap.bindGlobal('del', (e) => { 
        e.preventDefault();
        var data = $('#tabla_productos').bootstrapTable('getSelections')
        var row = data[0];
        if (data) {
            var nombre = row.premio;
            sweetAlertConfirm({
                title: `Eliminar Premio`,   
                text: `¿Eliminar Producto: ${nombre}?`,
                confirmButtonText: 'Eliminar',
                confirmButtonColor: 'var(--danger)',
                confirm: function() {
                    $('#tabla_productos').bootstrapTable('removeByUniqueId', row.id_premio);
                }
            });
        }
    });
});

//noSubmitForm('#formulario');
resetWindow();
obtenerNumero()

enterClick('cantidad', 'agregar-premio');

// Acciones por teclado
$(window).on('keydown', function (event) {
    switch (event.which) {
        // F1 - Buscar proveedor
        case 112:
            event.preventDefault();
            $('#btn_buscar_cliente').click();
            break;
        // F2 - Focus campo código
        case 113:
            event.preventDefault();
            $('#codigo').focus();
            break;
        // F3 - Focus select productos
        case 114:
            event.preventDefault();
            $('#premio').focus();
            break;
        // F4 - Generar solicitud
        case 115:
            event.preventDefault();
            $('#btn_guardar').click();
            break;
    }
});

// Buscar clientes
Mousetrap(document.querySelector('#ruc')).bind('enter', async function (e) {
    e.preventDefault(); 
    let data = { id_cliente: '', ruc: '', razon_social: '' };
    let ruc = $('#ruc').val();

    if (ruc) {
        // Se busca el cliente en la base de datos del sistema
        let cliente = await buscarCliente(ruc);
        if (cliente.ruc) {
            cargarDatos(cliente);
        } else {
            data.ruc = ruc;
            cargarDatos(data);
        }
    } else {
        // Si no se encuentra al cliente
        cargarDatos(data);
    }
 });

 $('#razon_social').select2({
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
            return { q: 'clientes-venta', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) {
                    return {
                        id: obj.id_cliente,
                        text: obj.razon_social,
                        id_cliente: obj.id_cliente,
                        ruc: obj.ruc,
                        razon_social: obj.razon_social,
                        telefono: obj.telefono,
                        direccion: obj.direccion,
                        obs: obj.obs,
                        referencia: obj.referencia,
                        celular: obj.celular,
                        direccion: obj.direccion,
                        email: obj.email,
                        latitud: obj.latitud,
                        longitud: obj.longitud,
                        id_tipo: obj.id_tipo, 
                        puntos: obj.puntos
                    }
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function () {
    let data = $('#razon_social').select2('data')[0] || {};
    if (data) {
        cargarDatos(data);
    } else {
        $('#id_cliente, #ruc').val('');
    }

});
$("#tabla_clientes").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_clientes',
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
    sortName: "razon_social",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    keyEvents: true,
    paginationParts: ['pageInfo', 'pageList'],
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_cliente', title: 'ID PROVEEDOR', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'razon_social', title: 'CLIENTE / RAZÓN SOCIAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'telefono', title: 'TELÉFONO', align: 'left', valign: 'middle', sortable: true },
            { field: 'direccion', title: 'DIRECCIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'obs', title: 'OBSERVACIONES', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#modal_clientes').on('shown.bs.modal', function (e) {
    $(this).find('input[type="search"]').focus();
    $('#tabla_clientes').bootstrapTable("refresh", { url: url_clientes + `?q=ver` }).bootstrapTable('resetSearch', '');
});

$('#tabla_clientes').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cerrarModalClientes(row);
});

Mousetrap(document.querySelector('#ruc')).bind('enter', function (e) {
    e.preventDefault();
    $('#btn_buscar_ruc').click();
});

$('#btn_buscar_ruc').click(async function() {
    let data = { id_cliente: '', ruc: '', razon_social: '', telefono: '', direccion: '' };;
    let ruc = $('#ruc').val();

    if (ruc) {
        // Se busca el cliente por RUC
        buscar_ruc = await buscarRUC(ruc);
        if (buscar_ruc.ruc) {
            data.ruc = buscar_ruc.ruc + '-' + buscar_ruc.dv;
            data.razon_social = buscar_ruc.razon_social;
            data.telefono = buscar_ruc.telefono;
            data.direccion = buscar_ruc.direccion;
            cargarDatos(data);
            $('#razon_social').focus();
            return;
        }

        // Se busca el cliente por Cédula
        buscar_ci = await buscarCI(ruc);
        if (buscar_ci.cedula) {
            data.ruc = buscar_ci.cedula;
            data.razon_social = buscar_ci.apellido + ' ' + buscar_ci.nombre_solo;
            data.telefono = buscar_ci.telefono;
            data.direccion = buscar_ci.direccion;
            cargarDatos(data);
            $('#razon_social').focus();
            return;
        }
    }

    // Si no se encuentra al cliente
    data.ruc = ruc;
    // $(this).focus();
    cargarDatos(data);
});

function cerrarModalClientes(row) {
    cargarDatos(row);
    $('#razon_social').focus();
    $("#modal_clientes").modal("hide");
}

function cargarDatos(data) {

    if (data.ruc == '44444401-7') {
         alertDismissJS('Los clientes Sin Nombre no pueden canjear puntos' , 'error', () => $('#ruc').focus()); 
        $('#ruc').val('');
        $('#razon_social').val(null).trigger('change.select2');
        $('#detalle_puntos').val('');
        $('#observacion').val('');
        return false;
    } 

    if (data.id_cliente) {
        if ($('#razon_social').val() != data.id_cliente) {
            $('#razon_social').select2('trigger', 'select', {
                data: { id: data.id_cliente, text: data.razon_social, id_cliente: data.id_cliente, ruc: data.ruc }
            });
        }
    } else {
        $('#razon_social').val(null).trigger('change.select2');
    }

    $('#id_cliente').val(data.id_cliente);
    $('#ruc').val(data.ruc);
    $('#ruc_str').val(data.ruc);
    $('#razon_social_str').val(data.razon_social);
    $('#telefono').val(data.telefono);
    $('#direccion').val(data.direccion);
    $('#obs').val(data.observacion);
    $('#referencia').val(data.referencia);
    $('#celular').val(data.celular);
    $('#direccion').val(data.direccion);
    $('#email').val(data.email);
    $('#lat').val(data.latitud);
    $('#lng').val(data.longitud);
    if (data.id_tipo) {
        $("#id_tipo").append(new Option(data.tipo, data.id_tipo, true, true)).trigger('change');
    } else {
        $("#id_tipo").append(new Option('Minorista', 1, true, true)).trigger('change');
    }

    $('#detalle_puntos').val(data.puntos || 0);
}


// Fin buscar cliente

// Productos
// BUSQUEDA DE PRODUCTOS
function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_premio) {
        $result = $(`<span>${node.text} <small>(${node.premio})</small></span>`);
    }
    return $result;
};

$('#premio').select2({
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
                        puntos: obj.puntos,
                        costo: obj.costo
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
    var data = $('#premio').select2('data')[0] || {};
    $('#codigo').val(data.codigo || '');
    $('#puntos').val(separadorMiles(data.puntos) || '');
    $('#costo').val(separadorMiles(data.costo) || '');

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
                    $('#premio').select2('trigger', 'select', {
                        data: { 
                            id: data.id_premio,
                            text: data.premio,
                            codigo: data.codigo,
                            puntos: data.puntos,
                            costo: data.costo
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
                $('#tabla_productos').bootstrapTable('removeByUniqueId', row.id_premio);
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
    sortName: "codigo",
    sortOrder: 'asc',
    uniqueId: 'id_premio',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_premio', title: 'ID Premio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'premio', title: 'Premio', align: 'left', valign: 'middle', sortable: true },
            { field: 'puntos', title: 'Puntos', align: 'right', valign: 'middle' },
            { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', visible: false},
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria,editable: { type: 'text' } },
            { field: 'total_costo', title: 'Total Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible:false},
            { field: 'monto', title: 'Total Puntos', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible:true},
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
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_productos').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    var cantidad = quitaSeparadorMiles(row.cantidad);
    var costo = quitaSeparadorMiles(row.costo);
    var costo_uni = quitaSeparadorMiles(row.costo);
    var puntos = quitaSeparadorMiles(row.puntos);
    var puntos_uni = quitaSeparadorMiles(row.puntos);


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

    if(row.cantidad && row.puntos){
       update_row['monto'] = parseInt(cantidad) * parseInt(puntos_uni);
    }else{
        update_row['monto'] = 0;
    }

    if(row.cantidad && row.costo){
        update_row['total_costo'] = parseInt(cantidad) * parseInt(costo_uni);
     }else{
         update_row['total_costo'] = 0;
     }
  
    // Se actualizan los valores
    $('#tabla_productos').bootstrapTable('updateByUniqueId', { id, row: update_row });

});


$('#agregar-premio').on('click', function () {
    var data = $('#premio').select2('data')[0];
    var costo = $('#costo').val();
    var puntos = $('#puntos').val();
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
            puntos: puntos,
            costo: costo,
            // vencimiento: vencimiento,
        }
        agregarProducto(premio);
        $('#premio').val(null).trigger('change');
        $('#codigo').focus(); 
        // $('#vencimiento').val(null).trigger('change');
    }

});


function agregarProducto(data) {
    var cantidad = quitaSeparadorMiles($('#cantidad').val());
    var costo = quitaSeparadorMiles($('#costo').val());
    var monto = quitaSeparadorMiles($('#costo').val());
    var total_cantidad = quitaSeparadorMiles($('#costo').val());
    var cantidad_carga =  quitaSeparadorMiles($('#cantidad').val());
    var puntos = quitaSeparadorMiles($('#puntos').val());

    var puntos_carga = cantidad*puntos;

    var total = costo * cantidad;

    if (cantidad == 0) {
        setTimeout(() =>  alertDismissJS('La cantidad debe ser mayor a 0', 'error', () => $('#cantidad').focus()), 500); 
    } else {
        // Si se repite un producto se suman las cantidades
        var tableData = $('#tabla_productos').bootstrapTable('getData');
        var premio = tableData.find(value => value.id_premio == data.id_premio);
        if (premio) {
            costo =+ parseInt(premio.costo);
            cantidad += parseInt(premio.cantidad);
            puntos = parseInt(premio.puntos);

            total= costo*cantidad;
            puntos_carga =cantidad*puntos;


            $('#tabla_productos').bootstrapTable('removeByUniqueId', data.id_premio);
        }

        $('#tabla_productos').bootstrapTable('insertRow', {
            index: 1,
            row: {
                id_premio: data.id_premio,
                codigo: data.codigo,
                premio: data.premio,
                puntos: puntos,
                costo:costo,
                cantidad: cantidad,
                monto: puntos_carga,
                total_costo: total,
                // presentacion: data.presentacion,
            }
        });

        $('#cantidad').val(1);
        $('#puntos').val(1);
        $('#premio').val(null).trigger('change');
        setTimeout(() => $('#btn_guardar').focus(), 100);
    }
}

$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function (e) {
    e.preventDefault();
    let tableData = $('#tabla_productos').bootstrapTable('getData');

    let producto_cantidad_cero = tableData.find(value => parseInt(quitaSeparadorMiles(value.cantidad)) === 0);
    

    if($('#detalle_puntos').val() == 0  ){
        alertDismissJS("El cliente no tiene puntos suficientes para realizar el canje", "error");
        return false;
    }

    if ($('#id_cliente').val() == '') {
        //alertDismissJS('Ningún producto agregado. Favor verifique.', "error");
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if ($('#tabla_productos').bootstrapTable('getData').length == 0) {
        alertDismissJS('Ningún premio agregado. Favor verifique.', 'error', () => $('#codigo').focus());
        return false;
    }else if (producto_cantidad_cero) {
        alertDismissJS(`El premio "${producto_cantidad_cero.premio}" tiene cantidad 0. Favor verifique.`, "error");
        return false;
    }

    if ($(this).valid() === false) return false;

    sweetAlertConfirm({
        title: `¿Guardar Canje?`,
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
    $("#puntos").val(1);
    $("#premio").val(null).trigger('change');
    $("#razon_social").val(null).trigger('change');
    $('#tabla_productos').bootstrapTable('removeAll');
    $('html, body').animate({ scrollTop: 0 }, "slow");
    $("#ruc").focus();
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

            $('#numero').val(data.numero);
        },
        error: function (xhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
        }
    });
}
