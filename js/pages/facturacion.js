var url = 'inc/facturacion-data';
var url_clientes = 'inc/clientes-data';
var url_listados = 'inc/listados';
var url_timbrado = 'inc/timbrados-data';
var url_administrar_cajas = 'inc/administrar-cajas-data';
var myMap;

var public_object_producto;
var public_caja_abierta = false;
var public_actualizar_descuentos = true;

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

    $('#nro_nota_credito').mask('999-999-9999999');
    resetWindow();
    enterClick('cantidad', 'agregar-producto');
});

// Acciones por teclado
// Acciones por modal
var MousetrapModalClientes = new Mousetrap();
var MousetrapModalClientesNuevo = new Mousetrap();
var MousetrapModalDelivery = new Mousetrap();
var MousetrapModalCobros = new Mousetrap();

// Acciones por tab
var MousetrapTabVentas = new Mousetrap();
var MousetrapTabProductos = new Mousetrap();
var MousetrapTabProductosClientes = new Mousetrap();

// Se pausan las acciones que no son principales
MousetrapModalClientes.pause();
MousetrapModalClientesNuevo.pause();
MousetrapModalDelivery.pause();
MousetrapModalCobros.pause();

MousetrapTabProductos.pause();
MousetrapTabProductosClientes.pause();

// Acciones principales
Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#nav_1').click() });
Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#nav_2').click() });
Mousetrap.bindGlobal('f3', (e) => { e.preventDefault(); $('#nav_3').click(); });

// Acciones para el tab de ventas
MousetrapTabVentas.bindGlobal('f4', (e) => { e.preventDefault(); $('#btn_detalle_producto').click(); });
MousetrapTabVentas.bindGlobal('f5', (e) => { e.preventDefault(); $('#btn_guardar').click(); });
MousetrapTabVentas.bindGlobal('f6', (e) => { e.preventDefault(); $('#btn_buscar_cliente').click() });
MousetrapTabVentas.bindGlobal('f7', (e) => { e.preventDefault(); $('#btn_detalle_cliente').click() });
MousetrapTabVentas.bindGlobal('f8', (e) => { e.preventDefault(); $('#ruc').focus(); });
MousetrapTabVentas.bindGlobal('f9', (e) => { e.preventDefault(); $('#razon_social').focus(); });
MousetrapTabVentas.bindGlobal('f10', (e) => { e.preventDefault(); $('#descuento_metodo_pago').focus(); });
MousetrapTabVentas.bindGlobal('f11', (e) => { e.preventDefault(); $('#btn_extraer').click(); });
MousetrapTabVentas.bindGlobal('f12', (e) => { e.preventDefault(); ($('#btn_abrir_caja').is(':visible')) ? $('#btn_abrir_caja').click() : $('#btn_cerrar_caja').click(); });
MousetrapTabVentas.bindGlobal('del', (e) => { e.preventDefault(); $('#btn_eliminar').click() });
MousetrapTabVentas.bindGlobal('shift+del', (e) => { e.preventDefault(); $('#eliminar_todo').click() });
MousetrapTabVentas.bindGlobal('alt+s', (e) => { e.preventDefault(); $('#sin_nombre').prop('checked', !$('#sin_nombre').prop('checked')).trigger('change'); });
MousetrapTabVentas.bindGlobal('alt+r', (e) => { e.preventDefault(); $('#receta').prop('checked', !$('#receta').prop('checked')).trigger('change'); });
MousetrapTabVentas.bindGlobal('alt+d', (e) => { e.preventDefault(); $('#delivery').prop('checked', !$('#delivery').prop('checked')).trigger('change'); });
MousetrapTabVentas.bindGlobal('alt+c', (e) => { e.preventDefault(); $('#courier').prop('checked', !$('#courier').prop('checked')).trigger('change'); });

// Acciones para el tab de buscar productos
MousetrapTabProductos.bindGlobal('f5', (e) => { e.preventDefault(); $('#filtro_principios_activos').focus() });
MousetrapTabProductos.bindGlobal('f6', (e) => { e.preventDefault(); ver_detalle_modal_productos(); });
MousetrapTabProductos.bind('b', (e) => { e.preventDefault(); $('#tab_2').find('input[type="search"]').focus(); });
MousetrapTabProductos.bind('r', (e) => { e.preventDefault(); $('#tabla_productos').bootstrapTable('refresh'); });
MousetrapTabProductos.bind('left', (e) => { e.preventDefault(); $('#tabla_productos').bootstrapTable('prevPage'); });
MousetrapTabProductos.bind('right', (e) => { e.preventDefault(); $('#tabla_productos').bootstrapTable('nextPage'); });
MousetrapTabProductos.bindGlobal(['up', 'down'], (e) => { e.preventDefault(); $('#tab_2').find('input[type="search"]').blur(); });

// Acciones para el tab de últimas compras
MousetrapTabProductosClientes.bindGlobal(['up', 'down'], (e) => { e.preventDefault(); $('#tab_3').find('input[type="search"]').blur(); });

// Acciones para el modal de búsqueda de clientes
MousetrapModalClientes.bindGlobal('f5', (e) => { e.preventDefault(); });
MousetrapModalClientes.bind('b', (e) => { e.preventDefault(); $('#modal_clientes').find('input[type="search"]').focus(); });
MousetrapModalClientes.bind('r', (e) => { e.preventDefault(); $('#tabla_clientes').bootstrapTable('refresh'); });
MousetrapModalClientes.bind('left', (e) => { e.preventDefault(); $('#tabla_clientes').bootstrapTable('prevPage'); });
MousetrapModalClientes.bind('right', (e) => { e.preventDefault(); $('#tabla_clientes').bootstrapTable('nextPage'); });
MousetrapModalClientes.bindGlobal(['up', 'down'], (e) => { e.preventDefault(); $('#modal_clientes').find('input[type="search"]').blur(); });

// Acciones para el modal de agregar o editar clientes
MousetrapModalClientesNuevo.bindGlobal('f5', (e) => { e.preventDefault(); });
MousetrapModalClientesNuevo.bind('f1', (e) => { e.preventDefault(); $('#btn_buscar_ruc').click() });

// Acciones para el modal de búsqueda de delivery
MousetrapModalDelivery.bindGlobal('f5', (e) => { e.preventDefault(); });
MousetrapModalDelivery.bind('b', (e) => { e.preventDefault(); $('#modal_delivery').find('input[type="search"]').focus(); });
MousetrapModalDelivery.bind('r', (e) => { e.preventDefault(); $('#tabla_delivery').bootstrapTable('refresh'); });
MousetrapModalDelivery.bind('left', (e) => { e.preventDefault(); $('#modal_delivery').bootstrapTable('prevPage'); });
MousetrapModalDelivery.bind('right', (e) => { e.preventDefault(); $('#modal_delivery').bootstrapTable('nextPage'); });
MousetrapModalDelivery.bindGlobal(['up', 'down'], (e) => { e.preventDefault(); $('#modal_delivery').find('input[type="search"]').blur(); });

// Acciones para el modal de cobro
MousetrapModalCobros.bindGlobal('f1', (e) => { e.preventDefault(); $('#metodo_pago').focus() });
MousetrapModalCobros.bindGlobal('f2', (e) => { e.preventDefault(); $('#entidad').focus() });
MousetrapModalCobros.bindGlobal('f3', (e) => { e.preventDefault(); $('#monto').focus() });
MousetrapModalCobros.bindGlobal('f4', (e) => {
    e.preventDefault();
    if ($('#detalles').is(':visible')) $('#detalles').focus();
    if ($('#nro_nota_credito').is(':visible')) $('#nro_nota_credito').focus();
});
MousetrapModalCobros.bindGlobal('f5', (e) => { e.preventDefault(); $('#btn_imprimir_factura').click() });
MousetrapModalCobros.bindGlobal('del', (e) => { 
    e.preventDefault();
    var data = $('#tabla_cobros').bootstrapTable('getSelections')

    if (data) {
        var row = data[0];
        var nombre = row.metodo_pago;
    
        sweetAlertConfirm({
            title: `Eliminar Cobro`,
            text: `¿Eliminar "${nombre}"?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function () {
                var data = $('#tabla_cobros').bootstrapTable('getData');
                var descuento = data.find((value) => value.id_descuento_metodo_pago)
                if (!descuento) {
                    $('#tabla_cobros').bootstrapTable('removeByUniqueId', row.id_cobro);
                    $('#detalle_descuento').addClass('d-none')
                    $('#metodo_pago').focus().select();
                } else {
                    $('#tabla_cobros').bootstrapTable('removeAll');
                    $('#metodo_pago').focus().select();
                }
            }
        });
    }
});

// Acciones para navegación por teclado dentro de bootstrapTable
MousetrapTableNavigation('#tabla_clientes', MousetrapModalClientes, cerrarModalClientes);
MousetrapTableNavigation('#tabla_delivery', MousetrapModalDelivery, seleccionarDelivery);
MousetrapTableNavigation('#tabla_productos', MousetrapTabProductos, seleccionarProducto);
MousetrapTableNavigationCell('#tabla_detalle', MousetrapTabVentas, true);
MousetrapTableNavigation('#tabla_cobros', MousetrapModalCobros);
MousetrapTableNavigation('#tabla_productos_clientes', MousetrapTabProductosClientes, seleccionarProducto);

// Se desactivan las acciones principales, solo funcionan las asiganadas al modal
$('#modal_cobros').on('shown.bs.modal', () => { Mousetrap.pause(); MousetrapTabVentas.pause(); MousetrapModalCobros.unpause(); });
$('#modal_cobros').on('hidden.bs.modal', () => { Mousetrap.unpause(); MousetrapTabVentas.unpause(); MousetrapModalCobros.pause() });
$('#modal_clientes').on('shown.bs.modal', () => { Mousetrap.pause(); MousetrapTabVentas.pause(); MousetrapModalClientes.unpause() });
$('#modal_clientes').on('hidden.bs.modal', () => { Mousetrap.unpause(); MousetrapTabVentas.unpause(); MousetrapModalClientes.pause() });
$('#modal_clientes_new').on('shown.bs.modal', () => { Mousetrap.pause(); MousetrapTabVentas.pause(); MousetrapModalClientesNuevo.unpause() });
$('#modal_clientes_new').on('hidden.bs.modal', () => { Mousetrap.unpause(); MousetrapTabVentas.unpause(); MousetrapModalClientesNuevo.pause() });
$('#modal_delivery').on('shown.bs.modal', () => { Mousetrap.pause(); MousetrapTabVentas.pause(); MousetrapModalDelivery.unpause() });
$('#modal_delivery').on('hidden.bs.modal', () => { Mousetrap.unpause(); MousetrapTabVentas.unpause(); MousetrapModalDelivery.pause() });
$('#modal_detalles').on('shown.bs.modal', () => { Mousetrap.pause(); MousetrapTabVentas.pause(); MousetrapTabProductos.pause() });
$('#modal_detalles').on('hidden.bs.modal', () => {
    Mousetrap.unpause();
    if ($('#tab_1').is(':visible')) {
        MousetrapTabVentas.unpause();
    } else if ($('#tab_2').is(':visible')) {
        MousetrapTabProductos.unpause();
    }
});
$('#modal_cantidad').on('shown.bs.modal', () => { MousetrapTabProductos.pause(); MousetrapTabProductosClientes.pause(); });
$('#modal_cantidad').on('hidden.bs.modal', () => {
    if ($('#tab_2').is(':visible')) {
        MousetrapTabProductos.unpause();
    } else if ($('#tab_3').is(':visible')) {
        MousetrapTabProductosClientes.unpause();
    }
});

$('#nav_1').on('shown.bs.tab', () => {
    MousetrapTabVentas.unpause();
    MousetrapTabProductos.pause();
    MousetrapTabProductosClientes.pause();
});
$('#nav_2').on('shown.bs.tab', () => {
    MousetrapTabVentas.pause();
    MousetrapTabProductos.unpause();
    MousetrapTabProductosClientes.pause();
});
$('#nav_3').on('shown.bs.tab', () => {
    MousetrapTabVentas.pause();
    MousetrapTabProductos.pause();
    MousetrapTabProductosClientes.unpause();
});
// Fin acciones por teclado

$('#nav_1').on('shown.bs.tab', function() {
    $('#tabla_detalle').bootstrapTable('resetView');
});

// Configuración select2
$('#condicion').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('change', function(e) {
    //let $element = $('#vencimiento');
    //if ($(this).val() == 1) {
        //$('#razon_social').parent().addClass('col-md-7').removeClass('col-md-6');
        //$element.val('').parent().hide();
    //} else {
        //$('#razon_social').parent().addClass('col-md-5').removeClass('col-md-7');
        //$element.val('').parent().show();
        //setTimeout(() => $element.focus(), 10);
    //}
});

$('#sin_nombre').on('change', async function () {
    if ($(this).is(':checked')) {
        let cliente = await buscarCliente('44444401-7');
        cargarDatos(cliente);
    } else {
        $('#id_cliente, #ruc_str').val('');
        $('#ruc').val('').focus();
        $('#razon_social').val(null).trigger('change.select2');
        $('#detalle_puntos').val('0');
        $('#btn_detalle_cliente').prop('disabled', false);
    }
});

// Agregar o editar cliente
$('#btn_detalle_cliente').click(function () {
    $("#id_tipo").append(new Option('Minorista', 1, true, true)).trigger('change');
    $('#tab a[href="#tab-content-1"]').tab('show');
    //$('#tabla_direcciones').bootstrapTable('removeAll');
});

$('#tab-2').on('shown.bs.tab', function (event) {
    var latitud = $('#lat').val();
    var longitud = $('#lng').val();

    if(!latitud){
        latitud ='-25.782007';
        longitud = '-56.449813';
    }

    actualizarMapa(latitud, longitud);
    
});

$('#lat_lng').on('keyup', function(e) {
    let lat_lng = $(this).val();
    let lat_lng_split = lat_lng.split(',');

    if (lat_lng_split.length == 2) {
        let latitud = lat_lng_split[0];
        let longitud = lat_lng_split[1];
        actualizarMapa(latitud, longitud);
    }
});

function actualizarMapa(latitud, longitud) {
    if(myMap){
        myMap.remove()
    }   

    myMap = dibujarMapa(latitud, longitud, 'myMap');  
}

// Direcciones
function iconosFilaDirecciones(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar"><i class="fas fa-pencil-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaDirecciones = {
    'click .eliminar': function (e, value, row, index) {
        $('#tabla_direcciones').bootstrapTable('removeByUniqueId', row.id_cliente_direccion);
    },
    'click .editar': function (e, value, row, index){
        $("#direccion").val(row.direccion);
        $("#referencia").val(row.referencia);
        $("#lat").val(row.latitud);
        $("#lng").val(row.longitud);
        actualizarMapa(row.latitud, row.longitud);
        $('#tabla_direcciones').bootstrapTable('removeByUniqueId', row.id_cliente_direccion);
    }
}

//tabla dirrecciones 
$("#tabla_direcciones").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 130,
    sortName: "id_cliente_direccion",
    sortOrder: 'desc',
    uniqueId: 'id_cliente_direccion',
    columns: [
        [
            { field: 'id_cliente_direccion', title: 'ID Cliente Dirreccion', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'latitud', title: 'Latitud', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'longitud', title: 'Longitud', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'direccion', title: 'Dirección', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'referencia', title: 'Referencia', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'coordenadas', title: 'Coordenadas', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaDirecciones,  formatter: iconosFilaDirecciones, width: 100 }
        ]
    ]
});

$('#agregar-direccion').click(function () {
    let direccion  = $('#direccion').val();
    let referencia = $('#referencia').val();
    let lat_lng   = $('#lat_lng').val();
    let latitud   = $('#lat').val();
    let longitud   = $('#lng').val();

    if (direccion == '') {
        alertDismissJsSmall('Debe agregar una Direccion para cargar.', 'error', 2000, () => $('#direccion').focus());
        return;
    }
    if (lat_lng == '') {
        alertDismissJsSmall('Debe agregar una Coordenadas para cargar.', 'error', 2000, () => $('#lat_lng').focus());
        return;
    }

    setTimeout(function () {
        $('#tabla_direcciones').bootstrapTable('insertRow', {
            index: 1,
            row: {
                id_cliente_direccion: new Date().getTime(),
                latitud: latitud,
                longitud: longitud,
                direccion: direccion,
                referencia: referencia,
                coordenadas: lat_lng
            }
        });
    }, 100);
    $('#direccion').val('');
    $('#referencia').val('');
    latitud = '-25.782007';
    longitud = '-56.449813'
    actualizarMapa(latitud, longitud);
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
            $('#modal_clientes_new').modal('show');
        }
    } else {
        // Si no se encuentra al cliente
        cargarDatos(data);
    }
 });

// Buscar clientes
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

$('#modal_clientes_new').on('shown.bs.modal', function (e) {
    let text = '';
    if ($('#id_cliente').val()) {
        text = 'Editar';
        $('#alert_cliente_nuevo').hide();
    } else {
        text = 'Agregar';
        $('#alert_cliente_nuevo').show();
        $('#btn_buscar_ruc').click();
    }
    $('#modalClientesNewLabel').html(`${text} Cliente`);
    $(this).find('input[type!="hidden"], select, textarea').filter(':first').focus();
});

$('#tabla_clientes').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cerrarModalClientes(row);
});

Mousetrap(document.querySelector('#ruc_str')).bind('enter', function (e) {
    e.preventDefault();
    $('#btn_buscar_ruc').click();
});

$('#btn_buscar_ruc').click(async function() {
    let data = { id_cliente: '', ruc: '', razon_social: '', telefono: '', direccion: '' };;
    let ruc = $('#ruc_str').val();

    if (ruc) {
        // Se busca el cliente por RUC
        buscar_ruc = await buscarRUC(ruc);
        if (buscar_ruc.ruc) {
            data.ruc = buscar_ruc.ruc + '-' + buscar_ruc.dv;
            data.razon_social = buscar_ruc.razon_social;
            data.telefono = buscar_ruc.telefono;
            data.direccion = buscar_ruc.direccion;
            cargarDatos(data);
            $('#razon_social_str').focus();
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
            $('#razon_social_str').focus();
            return;
        }
    }

    // Si no se encuentra al cliente
    data.ruc = ruc;
    $(this).focus();
    cargarDatos(data);
});

function cerrarModalClientes(row) {
    cargarDatos(row);
    $('#razon_social').focus();
    $("#modal_clientes").modal("hide");
}

function cargarDatos(data) {
    resetForm('#formulario_clientes');

    if (data.ruc == '44444401-7') {
        $('#btn_detalle_cliente').prop('disabled', true);
        $('#sin_nombre').prop('checked', true);
        $('#nav_3').addClass('disabled');
    } else if(!data.ruc) {
        $('#btn_detalle_cliente').prop('disabled', false);
        $('#sin_nombre').prop('checked', false);
        $('#nav_3').addClass('disabled');
    } else {
        $('#btn_detalle_cliente').prop('disabled', false);
        $('#sin_nombre').prop('checked', false);
        $('#nav_3').removeClass('disabled');
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

    $('#detalle_puntos').val(separadorMiles(data.puntos || 0));
    $('#tabla_direcciones').bootstrapTable('refresh', { url: url+'?q=obtener-direcciones&id_cliente='+data.id_cliente });
}
// Fin buscar cliente
// Fin agregar o editar cliente

// Delivery
$("#tabla_delivery").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_delivrery',
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
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_funcionario', title: 'ID FUNCIONARIO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'razon_social', title: 'NOMBRE Y APELLIDO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ci', title: 'C.I', align: 'left', valign: 'middle', sortable: true },
            { field: 'celular', title: 'CELULAR', align: 'left', valign: 'middle', sortable: true },
        ]
    ]
});

$('#tabla_delivery').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    seleccionarDelivery(row);
});

function seleccionarDelivery(row) {
    $('#id_delivery').val(row.id_funcionario);
    $('#detalle_delivery').val(row.razon_social);
    $('#delivery').prop('checked', true);
    $('#modal_delivery').modal('hide');
}

$('#modal_delivery').on('shown.bs.modal', function (e) {
    $(this).find('input[type="search"]').focus();
    $('#tabla_delivery').bootstrapTable("refresh", { url: url + `?q=ver_deliverys` }).bootstrapTable('resetSearch', '');
});

$('#delivery').on('change', function () {
    if ($(this).is(':checked')) {
        $('#modal_delivery').modal('show');
        $('#courier').prop('checked', false);
    } else {
        $('#id_delivery, #detalle_delivery').val('');
    }
});
// Fin delivery

// Courier
$('#courier').on('change', function () {
    if ($(this).is(':checked')) {
        $('#id_delivery, #detalle_delivery').val('');
        $('#delivery').prop('checked', false);
    }
});
// Fin courier

// BUSQUEDA DE PRODUCTOS
// Filtros
$('#filtro_principios_activos').select2({
    placeholder: 'Principio Activo [F5]',
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
            return { q: 'principios_activos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_principio, text: obj.principio }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function () {
    $('#tabla_productos').bootstrapTable('refresh', { url: url + `?q=ver_productos&id_principio=${$(this).val()}` });
}).on('select2:close', function () {
    // Se activan las acciones por teclado del modal
    setTimeout(() => MousetrapTabProductos.unpause());
}).on('select2:open', function () {
    // Se desactivan las acciones por teclado del modal
    MousetrapTabProductos.pause();
}).on('change', function () {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}` })
});

$('#nav_2').on('shown.bs.tab', function() {
    $('#filtro_principios_activos').val(null).trigger('change');
    $('#tabla_productos').bootstrapTable('resetSearch', '');
    $('#tabla_productos').bootstrapTable('resetView');
    $('#tab_2').find('input[type="search"]').focus();
});

function alturaTablaProductos() {
    return bstCalcularAlturaTabla(170, 300);
}
function pageSizeTablaProductos() {
    return Math.floor(alturaTablaProductos() / 22) - 6;
}

function rowStyleTablaProductos(row, index) {
    if (row.tipo == 1) {
        if (parseInt(row.stock_fraccionado) == 0 && parseInt(row.stock) == 0 || parseInt(row.cantidad) > parseInt(row.stock) && parseInt(row.cantidad) > parseInt(row.stock_fraccionado)) {
            return { classes: 'text-danger' };
        } else {
            return {};
        }
    }else{
        return {};
    }
}

$("#tabla_productos").bootstrapTable({
    // url: url + '?q=ver_productos_stock',
    toolbar: '#toolbar_productos',
    searchSelector: '#search',
    search: true,
    showRefresh: true,
    showToggle: false,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaProductos(),
    pageSize: pageSizeTablaProductos(),
    sortName: "producto",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    keyEvents: true,
    rowStyle: rowStyleTablaProductos,
    paginationParts: ['pageInfo', 'pageList'],
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_producto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'controlado', title: 'CTRL', align: 'center', valign: 'middle', sortable: false, formatter: bstCondicional},
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: false, visible: true, width: 100 },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 400 },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: false, cellStyle: bstTruncarColumna, width: 150},
            { field: 'laboratorio', title: 'LABORATORIO', align: 'left', valign: 'middle', sortable: false, cellStyle: bstTruncarColumna, width: 150},
            { field: 'principios_activos', title: 'PRINCIPIOS ACTIVOS', align: 'left', valign: 'middle', sortable: false, cellStyle: bstTruncarColumna, width: 150 },
            { field: 'comision', title: 'COMISIÓN', align: 'center', valign: 'middle', sortable: false, formatter: bstFormatterPorcentajeDecimal },
            { field: 'comision_concepto', title: 'COMISIÓN CONCEPTO', align: 'center', valign: 'middle', sortable: true, visible: false },
            // { field: 'cantidad_fracciones', title: 'FRACCIONES', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'stock', title: 'STOCK', align: 'center', valign: 'middle', sortable: false, formatter: separadorMiles },
            { field: 'stock_fraccionado', title: 'S. FRACCIONADO', align: 'center', valign: 'middle', sortable: false, cellStyle: bstTruncarColumna, formatter: separadorMiles, visible:false },
            { field: 'precio', title: 'PRECIO', align: 'right', valign: 'center', sortable: false, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'precio_fraccionado', title: 'P. FRACCIONADO', align: 'right', valign: 'middle', sortable: false, cellStyle: bstTruncarColumna, formatter: separadorMiles, visible:false },
            { field: 'iva_str', title: 'IVA', align: 'right', valign: 'center', sortable: true, visible:false},
            { field: 'tipo', title: 'Tipo', align: 'right', valign: 'center', sortable: true, visible:false},
            // { field: 'observaciones', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#tabla_productos').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    seleccionarProducto(row);
});

function toggleButonsProductos(value, row, index, field) {
    var selections = $('#tabla_productos').bootstrapTable('getSelections');
    $('.acciones_tabla_productos').prop('disabled', (selections.length > 0) ? false : true);
}

$('#tabla_productos')
    .on('check.bs.table', toggleButonsProductos)
    .on('uncheck.bs.table', toggleButonsProductos)
    .on('check-all.bs.table', toggleButonsProductos)
    .on('uncheck-all.bs.table', toggleButonsProductos)
    .on('load-success.bs.table', toggleButonsProductos);

function seleccionarProducto(row) {
    public_object_producto = row;
    let p = row;

    if (row.tipo == 1) {
        if (parseInt(row.stock_fraccionado) == 0 && parseInt(row.stock) == 0 && p.id_principio) {
            sweetAlertConfirm({
                title: `¿Ver Productos Similares?`,
                text: `El producto ${p.producto} no tiene stock suficiente`,
                confirm: () => ver_productos_similares(p),
                cancel: function (){
                    $('#cargar_entero, #cargar_fraccionado').val('1');
                    $("#modal_cantidad").modal("show");
                }
            });
        } else {
            $('#cargar_entero, #cargar_fraccionado').val('1');
            $("#modal_cantidad").modal("show");
        }
    }else{
        cantidad = 1;
        cargarCantidad({ fraccionado: 0, cantidad })
    }
}

function ver_productos_similares(producto, mensaje) {
    $('#modal_productos').modal('show');
    $('#filtro_principios_activos').select2('trigger', 'select', {
        data: { id: producto.id_principio, text: producto.principios_activos }
    });
}

///// FIN BUSQUEDA PRODUCTOS /////

function rowStyleTablaDetalle(row, index) {
    if (row.tipo == 1) {
        if ((row.fraccionado == 0 && parseInt(row.cantidad) > parseInt(row.stock))
            || (row.fraccionado == 1 && parseInt(row.cantidad) > (parseInt(row.stock_fraccionado) + parseInt(row.cantidad_fracciones)))) {
            return { classes: 'text-danger' };
        } else {
            return {};
        }
    }else{
        return {};
    }
}

function alturaTablaDetalle() {
    return bstCalcularAlturaTabla(430, 300);
}

$('#tabla_detalle').bootstrapTable({
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
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    rowStyle: rowStyleTablaDetalle,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fraccionado', title: 'E/F', align: 'center', valign: 'middle', sortable: false, formatter: fraccionado },
            { field: 'controlado', title: 'CTRL', align: 'center', valign: 'middle', sortable: true, formatter: bstCondicional, width: 80 },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_lote', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: fechaLatina },
            { field: 'stock', title: 'Stock', align: 'right', valign: 'middle', sortable: true, visible: false, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'stock_fraccionado', title: 'Stock Fraccionado', align: 'right', valign: 'middle', sortable: true, visible: false, formatter: separadorMiles },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 100, editable: { type: 'text' } },
            { field: 'precio', title: 'Precio Entero', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'precio_fraccionado', title: 'Precio Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'precio_venta', title: 'Precio', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'remate', title: 'Remate', align: 'right', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo_descuento', title: 'Tipo Descuento', align: 'right', valign: 'middle', sortable: true, visible: false },
            { field: 'descuento_porcentaje', title: 'Descuento %', align: 'right', valign: 'middle', sortable: true, width: 150 },
            { field: 'descuento', title: 'Descuento ₲.', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 150, visible: false },
            { field: 'descuento_fraccionado', title: 'Descuento Fraccionado', align: 'right', valign: 'middle', sortable: true, visible: false, width: 150 },
            { field: 'comision', title: 'Comisión', align: 'right', valign: 'middle', sortable: true, width: 150, visible: false },
            { field: 'comision_concepto', title: 'Comisión Concepto', align: 'right', valign: 'middle', sortable: true, width: 150, visible: false },
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'iva_str', title: 'IVA', align: 'right', valign: 'center', sortable: true, visible: false},
            { field: 'tipo', title: 'Tipo', align: 'right', valign: 'center', sortable: true, visible: false},
        ]
    ]
});

$.extend($('#tabla_detalle').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '-',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:90px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_detalle').on('editable-save.bs.table', function (e, field, row, rowIndex, oldValue, $el) {
    let precio_venta = quitaSeparadorMiles(row.precio_venta);
    let cantidad = quitaSeparadorMiles(row.cantidad);
    let descuento_porcentaje = parseFloat(row.descuento_porcentaje);


    if (!cantidad) {
        //setTimeout(() => alertDismissJS('La cantidad debe ser mayor a cero (0). Favor verifique', 'error'), 100);
        alertDismissJsSmall('La cantidad debe ser mayor a cero (0). Favor verifique', 'error', 2000)

        $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id: row.id, row: { cantidad: oldValue || 1 } });
        return;
    }

    if (row.fraccionado == 0 && row.stock < cantidad) {
        let mensaje = `El stock (${row.stock}) del producto "${row.producto}" es insuficiente`;
        $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id: row.id, row: { cantidad: cantidad || 1 } });

        if (row.id_principio) {
            setTimeout(() =>
                sweetAlertConfirm({
                    title: `¿Ver Productos Similares?`,
                    text: mensaje,
                    confirm: () => ver_productos_similares(row),
                })
                , 100);
            return;
        } else {
            //setTimeout(() => alertDismissJS(mensaje, 'error'), 100);
            alertDismissJsSmall(mensaje, 'error', 2000)

            return;
        }
    }

    if (row.fraccionado == 1) {
        if (cantidad >= row.cantidad_fracciones) {
            $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id: row.id, row: { cantidad: oldValue || 1 } });
            //setTimeout(() => alertDismissJS(`La cantidad cargada iguala o supera la cantidad de fracciones (${row.cantidad_fracciones}) que puede tener el producto "${row.producto}"`, 'error'), 100);
            alertDismissJsSmall(`La cantidad cargada iguala o supera la cantidad de fracciones (${row.cantidad_fracciones}) que puede tener el producto "${row.producto}"`, 'error', 2000)
            return;
        } else if (row.stock_fraccionado < cantidad && row.stock <= 0) {
            let mensaje = `El stock fraccionado(${row.stock_fraccionado}) del producto "${row.producto}" es insuficiente`;
            $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id: row.id, row: { cantidad: cantidad || 1 } });

            if (row.id_principio) {
                setTimeout(() =>
                    sweetAlertConfirm({
                        title: `¿Ver Productos Similares?`,
                        text: mensaje,
                        confirm: () => ver_productos_similares(row),
                    })
                    , 100);
                return;
            } else {
                // setTimeout(() => alertDismissJS(mensaje, 'error'), 100);
                alertDismissJsSmall(mensaje, 'error', 2000)
                return;
            }
        }
    }

    if (row.fraccionado == 1) {
        if (row.descuento_fraccionado == 1) {
            var total_v = cantidad * precio_venta;
            var descuento = (total_v * descuento_porcentaje) / 100;
            var total_final = Math.round((total_v - descuento));
        }else{
            total_final = Math.round((cantidad * precio_venta));
            descuento = 0;
        }
    }else{
        var total_v = cantidad * precio_venta;
        var descuento = (total_v * descuento_porcentaje) / 100;
        var total_final = Math.round((total_v - descuento));
    }
    
    
    $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id: row.id, row: { total: total_final, descuento: descuento } });

});

function fraccionado(data) {
    switch (parseInt(data)) {
        case 0: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-success" title="Entero">E</span></b>';
        case 1: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-info" title="Fraccionado">F</span></b>';
    }
}

$('#tabla_detalle').on('post-body.bs.table', function (e, data) {
    $('.total_venta').html(separadorMiles(total_venta()));
});

function toggleButons(value, row, index, field) {
    var selections = $('#tabla_detalle').bootstrapTable('getSelections');
    $('.acciones_tabla_detalle').prop('disabled', (selections.length > 0) ? false : true);
}

$('#tabla_detalle')
    .on('check.bs.table', toggleButons)
    .on('uncheck.bs.table', toggleButons)
    .on('check-all.bs.table', toggleButons)
    .on('uncheck-all.bs.table', toggleButons)
    .on('load-success.bs.table', toggleButons);

// Modal cargar cantidad
$('#modal_cantidad').on('shown.bs.modal', function (e) {
    $('#modalCantidadLabel').html('Cantidad');
    $('#lote').val(null).trigger('change');
    resetForm('#formulario_cantidad');

    if (public_object_producto.fraccion == 1) {
        $('#cargar_fraccionado').parent().parent().show();
        $('#cargar_entero').parent().parent().addClass('col-md-6').removeClass('col-md-12');
    } else {
        $('#cargar_fraccionado').parent().parent().hide();
        $('#cargar_entero').parent().parent().addClass('col-md-12').removeClass('col-md-6');
    }
    
    $.ajax({
        url,
        dataType: 'json',
        type: 'GET',
        contentType: 'application/x-www-form-urlencoded',
        data: { q: 'ver_lotes', id_producto: public_object_producto.id_producto, page: 1 },
        beforeSend: function () {
            NProgress.start();
            loading();
        },
        success: function (data, textStatus, jQxhr) {
            NProgress.done();
            swal.close();

            if (data.total_count > 0) {
                let obj = data.data[0];
                $('#lote').select2('trigger', 'select', {
                    data: {
                        id: obj.id_lote,
                        text: obj.lote,
                        vencimiento: obj.vencimiento,
                        stock: obj.stock,
                        fraccionado: obj.fraccionado,
                        cant: obj.cant
                    }
                });
            } else {
                $('#lote').focus();
                // alertDismissJsSmall('Ningún lote encontrado', 'error', 2000, function(){ $('#lote').focus() });
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            swal.close();
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), "error", 2000, () => $('#lote').focus());
        }
    });
    

    
});

$("#formulario_cantidad").submit(function (e) {
    e.preventDefault();
});

Mousetrap(document.querySelector('#cargar_entero')).bind('enter', cargar_entero);
Mousetrap(document.querySelector('#cargar_fraccionado')).bind('enter', cargar_fraccionado);
$('#btn_cargar_entero').on('click', cargar_entero);
$('#btn_cargar_fraccionado').on('click', cargar_fraccionado);


function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.vencimiento) {
        fraccionado = '';
        if (node.cant > 0 ) {
            fraccionado = `<span class="badge badge-pill badge-info">F: ${node.fraccionado}</span>`;
        }
        $result = $(`<div class="d-flex justify-content-between">
                        <div class="overflow-hidden text-truncate">
                            <span>${node.text}</span>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-success">E: ${node.stock}</span>
                            ${fraccionado}
                            <span class="badge badge-pill badge-primary">${fechaLatina(node.vencimiento)}</span>
                        </div>
                    </div>`);
    }
    return $result;
};

$('#lote').select2({
    dropdownParent: $("#modal_cantidad"),
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
            return { q: 'ver_lotes', id_producto: public_object_producto.id_producto, term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_lote,
                        text: obj.lote,
                        vencimiento: obj.vencimiento,
                        stock: obj.stock,
                        fraccionado: obj.fraccionado,
                        cant: obj.cant,
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
}).on('select2:select', function() {
    if (public_object_producto.fraccion == 1) {
        $('#cargar_fraccionado').focus();
    } else {
        $('#cargar_entero').focus();
    }
});

// Buscar de producto al dar enter en el campo de búsqueda del tab buscar de productos
Mousetrap(document.querySelector('#search')).bind('enter', function(){
    var buscar = $("#search").val();

    if (buscar && !isNaN(buscar)) {
        $.ajax({
            url,
            dataType: 'json',
            type: 'GET',
            contentType: 'application/x-www-form-urlencoded',
            data: { q: 'ver_productos', codigo: buscar, offset: 0, limit: 1, order: 'asc'},
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data, textStatus, jQxhr) {
                NProgress.done();

                if (data.total > 0) {
                    seleccionarProducto(data.rows[0]); 
                } else {
                    alertDismissJsSmall('No se ha encontrado el producto.', 'error', 2000, () => $('#search').focus());
                }
            },
            error: function (jqXhr, textStatus, errorThrown) {
                NProgress.done();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000, function(){$('#search').focus()})
            }
        });
    }

});


function cargar_entero() {
    let p = public_object_producto;
    let lote = $('#lote').select2('data')[0];
    let cantidad = quitaSeparadorMiles($('#cargar_entero').val());

    $('#formulario_cantidad').submit();
    if ($('#formulario_cantidad').valid() === false) return false;

    let stock = parseInt(lote.stock);

    if (stock >= cantidad) {
        cargarCantidad({ fraccionado: 0, cantidad: quitaSeparadorMiles($('#cargar_entero').val()) })
    } else {
        let mensaje = `El stock (${lote.stock}) del lote "${lote.text}" del producto "${p.producto}" es insuficiente`;
        if (p.id_principio) {
            setTimeout(() =>
                sweetAlertConfirm({
                    title: `¿Ver Productos Similares?`,
                    text: mensaje,
                    confirm: () => ver_productos_similares(p),
                    cancel: () => cargarCantidad({ fraccionado: 0, cantidad: quitaSeparadorMiles($('#cargar_entero').val()) })

                })
                , 100);
        } else {
            cargarCantidad({ fraccionado: 0, cantidad })
        }
    }
}

function cargar_fraccionado() {
    let p = public_object_producto;
    let lote = $('#lote').select2('data')[0];
    let cantidad = quitaSeparadorMiles($('#cargar_fraccionado').val());

    $('#formulario_cantidad').submit();
    if ($('#formulario_cantidad').valid() === false) return false;

    let stock = parseInt(lote.stock);
    let stock_fraccionado = parseInt(lote.fraccionado);

    if (parseInt(p.cantidad_fracciones) <= cantidad) {
        alertDismissJsSmall(
            `La cantidad cargada iguala o supera la cantidad de fracciones (${p.cantidad_fracciones}) que puede tener el producto "${p.producto}"`,
            'error', 
            2000
        )
    } else if (stock_fraccionado < cantidad && stock <= 0) {
        let mensaje = `El stock fraccionado(${stock_fraccionado}) del producto "${p.producto}" es insuficiente`;
        if (p.id_principio) {
            setTimeout(() =>
                sweetAlertConfirm({
                    title: `¿Ver Productos Similares?`,
                    text: mensaje,
                    confirm: () => ver_productos_similares(p),
                    cancel: () =>  cargarCantidad({ fraccionado: 1, cantidad: quitaSeparadorMiles($('#cargar_fraccionado').val()) })
                })
                , 100);
        } else {
            cargarCantidad({ fraccionado: 1, cantidad: quitaSeparadorMiles($('#cargar_fraccionado').val()) })
        }
    } else {
        cargarCantidad({ fraccionado: 1, cantidad })
    }


}

/**
 * @param object data
 * * `int` cantidad
 * * `int` fraccionado 0-No, 1-Si
 */
function cargarCantidad(data) {
    let producto = $.extend(public_object_producto, data);
    let lote = $('#lote').select2('data')[0];

    if (producto.tipo == 1) {
        producto.id_lote = lote.id;
        producto.lote = lote.text;
        producto.vencimiento = lote.vencimiento;
        producto.stock = lote.stock;
        producto.stock_fraccionado = lote.stock_fraccionado;
    }else{
        producto.id_lote = 'NULL';
        producto.lote = 0;
        producto.vencimiento = '0000-00-00';
        producto.stock = 0;
        producto.stock_fraccionado = 0;
    }

    agregarProducto(producto);

    $('#modal_cantidad').modal('hide');
    $('#nav_1').tab('show');
}

function agregarProducto(data) {
    let id_metodo_pago = $('#descuento_metodo_pago').val();
    let id_entidad = $('#descuento_entidad').val();
    let tableData = $('#tabla_detalle').bootstrapTable('getData');
    let cantidad = data.cantidad;
    let producto = tableData.find(value => value.id_producto == data.id_producto && value.fraccionado == data.fraccionado && value.id_lote == data.id_lote);

    let precio_venta = (data.fraccionado == 1) ? parseInt(data.precio_fraccionado) : parseInt(data.precio);

    let total_venta = cantidad * precio_venta;

    if(data.controlado == 1){
        $("#receta").prop('checked', true).trigger('change');
    }

    // Si se repite un producto se suman las cantidades
    if (producto) {
        cantidad += parseInt(producto.cantidad);
        $('#tabla_detalle').bootstrapTable('removeByUniqueId', producto.id);
    }
    
    $('#tabla_detalle').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id: new Date().getTime(),
            id_producto: data.id_producto,
            codigo: data.codigo,
            producto: data.producto,
            id_presentacion: data.id_presentacion,
            presentacion: data.presentacion,
            id_principio: data.id_principio,
            principios_activos: data.principios_activos,
            id_lote: data.id_lote,
            lote: data.lote,
            vencimiento: data.vencimiento,
            cantidad_fracciones: data.cantidad_fracciones,
            stock: data.stock,
            stock_fraccionado: data.stock_fraccionado,
            fraccionado: data.fraccionado,
            precio: data.precio,
            precio_fraccionado: data.precio_fraccionado,
            precio_venta: precio_venta,
            cantidad: cantidad,
            descuento_porcentaje: 0,
            descuento_fraccionado: data.descuento_fraccionado,
            total: total_venta,
            remate: 0,
            tipo_descuento: '',
            descuento: 0,
            comision: data.comision,
            comision_concepto:data.comision_concepto,
            iva_str: data.iva_str,
            controlado: data.controlado,
            tipo: data.tipo
        }
    });

    if (id_metodo_pago) {
        actualizarDescuentos([{ id_metodo_pago, id_entidad }]);
    }

    $('#modal_cantidad').modal('hide');
}

// Cobros
$('#metodo_pago').select2({
    dropdownParent: $("#modal_cobros"),
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
            return { q: 'metodos_pagos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            let cobros = $('#tabla_cobros').bootstrapTable('getData');
            return {
                results: $.map(data.data, function (obj) {
                    let disabled = obj.id_metodo_pago == 9 && cobros.length > 0;
                    return {
                        id: obj.id_metodo_pago,
                        text: obj.metodo_pago,
                        entidad: obj.entidad,
                        disabled
                    }
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function () {
    let data = $('#metodo_pago').select2('data')[0];
    let required = false;
    let label_text = 'Detalles';
    let $label = $('label[for=detalles]');
    let $input = $('#detalles');

    resetValidateForm('#formulario_cobro');

    if ((this.value == 2 || this.value == 3)) {
        if ($('#delivery').is(':checked') === false) {
            required = true;
        }
        label_text = 'N° de Voucher';
    }

    $label.html(`${label_text}<sup class="ml-1">[F4]</sup>`);
    $input.prop('required', required);
    if (required) {
        $label.addClass('label-required');
    } else {
        $label.removeClass('label-required');
    }

    if (data.entidad == 1) {
        $('#monto').prop('readonly', false);
        $('.nota_credito').addClass('d-none');
        $('.entidad, .detalles').removeClass('d-none');
        $('.puntos').addClass('d-none');
        $('.detalles').removeClass('col-md-6 col-sm-6');
        $('.detalles').addClass('col-md-3 col-sm-6');
        $("#nro_nota_credito").val('');
    } else if (this.value == 7) {
        $('#monto').prop('readonly', true);
        $('.entidad').addClass('d-none');
        $('.nota_credito').removeClass('d-none');
        $('.puntos').addClass('d-none');
        $('.detalles').addClass('d-none');
        $("#entidad").val(null).trigger('change');
    } else if (this.value == 9) {
        $('#monto').prop('readonly', true);
        $('.entidad').addClass('d-none');
        $('.nota_credito').addClass('d-none');
        $('.puntos').removeClass('d-none');
        $('.detalles').addClass('d-none');
        $("#entidad").val(null).trigger('change');
        $('#puntos_disponibles').val($('#detalle_puntos').val());
        totalPuntosVenta();
    } else {
        $('#monto').prop('readonly', false);
        $('.entidad').addClass('d-none');
        $('.nota_credito').addClass('d-none');
        $('.puntos').addClass('d-none');
        $('.detalles').removeClass('d-none');
        $('.detalles').removeClass('col-md-3 col-sm-6');
        $('.detalles').addClass('col-md-6 col-sm-6');
        $("#entidad").val(null).trigger('change');
        $("#nro_nota_credito").val('');
    }

    // Descuentos
    modal_cobros_actualizar();
    $('#descuento_metodo_pago').select2('trigger', 'select', {
        data: { id: data.id, text: data.text, entidad: data.entidad }
    });
}).on('select2:close', function () {
    $('#modal_cobros').find('input[type="search"]').focus();
    // Se activan las acciones por teclado del modal
    setTimeout(() => MousetrapModalCobros.unpause());
}).on('select2:open', function () {
    public_actualizar_descuentos = true;
    // Se desactivan las acciones por teclado del modal
    MousetrapModalCobros.pause();
});

$('#entidad').select2({
    dropdownParent: $("#modal_cobros"),
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
            return { q: 'entidades', term: params.term, page: params.page || 1, id: $('#metodo_pago').val() }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_entidad, text: obj.tipo_entidad } }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function (results) {
    let data = $('#entidad').select2('data')[0];
    resetValidateForm('#formulario_cobro');

    // Descuentos
    $('#descuento_entidad').select2('trigger', 'select', {
        data: { id: data.id, text: data.text }
    });
}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
}).on('select2:close', function () {
    $('#modal_cobros').find('input[type="search"]').focus();
    // Se activan las acciones por teclado del modal
    setTimeout(() => MousetrapModalCobros.unpause());
}).on('select2:open', function () {
    public_actualizar_descuentos = true;
    // Se desactivan las acciones por teclado del modal
    MousetrapModalCobros.pause();
});

function alturaTablaCobros() {
    return bstCalcularAlturaTabla(400, 200);
}

function iconosFilaCobros(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaCobro = {
    'click .eliminar': function (e, value, row, index) {
        var nombre = row.metodo_pago;
        sweetAlertConfirm({
            title: `Eliminar Cobro`,
            text: `¿Eliminar "${nombre}"?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function () {
                var data = $('#tabla_cobros').bootstrapTable('getData');
                var descuento = data.find((value) => value.id_descuento_metodo_pago)
                if (!descuento) {
                    $('#tabla_cobros').bootstrapTable('removeByUniqueId', row.id_cobro);
                    $('#detalle_descuento').addClass('d-none')
                    $('#metodo_pago').focus();
                } else {
                    $('#tabla_cobros').bootstrapTable('removeAll');
                    $('#metodo_pago').focus();
                }
            }
        });
    }
}

$('#tabla_cobros').bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaCobros(),
    sortName: 'metodo',
    sortOrder: 'asc',
    uniqueId: 'id_cobro',
    columns: [
        [   
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_cobro', align: 'left', valign: 'middle', title: 'ID', sortable: false, visible: false },
            { field: 'id_descuento_metodo_pago', align: 'left', valign: 'middle', title: 'ID Descuento', sortable: false, visible: false },
            { field: 'id_entidad', align: 'left', valign: 'middle', title: 'ID Entidad', sortable: false, visible: false },
            { field: 'id_metodo_pago', align: 'left', valign: 'middle', title: 'ID Método', sortable: false, visible: false },
            { field: 'metodo_pago', align: 'left', valign: 'middle', title: 'Método', sortable: false },
            { field: 'monto', align: 'right', valign: 'middle', title: 'Monto', sortable: false, formatter: separadorMiles },
            { field: 'detalles', align: 'left', valign: 'middle', title: 'Detalles', sortable: false },
            { field: 'borrar', align: 'center', valign: 'middle', title: 'Borrar', sortable: false, events: accionesFilaCobro, formatter: iconosFilaCobros }
        ]
    ]
});

$('#tabla_cobros').on('post-body.bs.table', modal_cobros_actualizar);

$("#formulario_cobro").submit(async function (e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var monto_descuento = 0;
    let $tabla_cobros = $('#tabla_cobros');
    let metodo = $('#metodo_pago').select2('data')[0];
    let descuento = $('#entidad').select2('data')[0];
    let monto = quitaSeparadorMiles($('#monto').val());
    let detalles = $('#detalles').val();
    let nro_nota_credito = $('#nro_nota_credito').val();
    let puntos = quitaSeparadorMiles($('#puntos').val());
    let puntos_disponibles = quitaSeparadorMiles($('#puntos_disponibles').val());
    let tableData = $tabla_cobros.bootstrapTable('getData');

    let verificar_metodo = tableData.find(value => value.id_metodo_pago == metodo.id);

    if (!descuento) {
        id_entidad = 0 
    }else{
        id_entidad = descuento.id
    }

    if (verificar_metodo) {
        //setTimeout(function (){ alertDismissJS('Método agregado a la lista. Favor verifique', 'error', () => $('#metodo_pago').focus()),100});
        alertDismissJsSmall('Método agregado a la lista. Favor verifique', 'error', 2000, () => $('#metodo_pago').focus())
    } else if (monto == 0) {
       // setTimeout(function (){ alertDismissJS('El monto no puede ser cero(0)', 'error', () => $('#metodo_pago').focus()),100});
        alertDismissJsSmall('El monto no puede ser cero(0)', 'error', 2000, () => $('#monto').focus())
    } else if (total_cobros() + monto - monto_descuento > total_venta()) {
       //setTimeout(function (){ alertDismissJS('El total cobrado supera el total de la venta', 'error', () => $('#metodo_pago').focus()),100});
       alertDismissJsSmall('El total cobrado supera el total de la venta', 'error', 2000, () => $('#monto').focus())
    } else {

        if (metodo.id == 7) {
            let nota_credito = await buscar_nota_credito(nro_nota_credito);
            if (!nota_credito) {
                alertDismissJsSmall('Nota De Crédito no encontrada', 'error', 2000, () => $('#nro_nota_credito').focus())
                return false;
            }

            $('#id_nota_credito').val(nota_credito.id_nota_credito);
            monto = nota_credito.total;
            detalles = nro_nota_credito;
        }
        if (metodo.id == 9) {
            if (puntos_disponibles < puntos) {
                alertDismissJsSmall('El cliente no posee los puntos suficientes para realizar el canje', 'error', 2000, () => $('#metodo_pago').focus());
                return false;
            }
            detalles = `${puntos} puntos utilizados`;
        }

        $tabla_cobros.bootstrapTable('insertRow', {
            index: 0,
            row: {
                id_cobro: new Date().getTime(),
                id_metodo_pago: metodo.id,
                id_entidad: id_entidad,
                metodo_pago: metodo.text,
                detalles: detalles,
                monto: monto
            }
        });
        setTimeout(() => resetForm(this));
        $('#detalle_descuento').addClass('d-none')
        $('#detalle_descuento_entidad').addClass('d-none')
        $('.nota_credito').addClass('d-none');
        $('.entidad').addClass('d-none');
        $('.puntos').addClass('d-none');
        $('.detalles').removeClass('d-none');
        $('.detalles').removeClass('col-md-3 col-sm-6');
        $('.detalles').addClass('col-md-6 col-sm-6');
        $('label[for=detalles]').removeClass('label-required');
        $('label[for=detalles]').html(`Detalles<sup class="ml-1">[F4]</sup>`);
        $('#detalles').prop('required', false);
    }
});

$('#modal_cobros').on('hide.bs.modal', function (e) {
    public_actualizar_descuentos = true;
});

$('#modal_cobros').on('shown.bs.modal', function (e) {
    $('#tabla_cobros').bootstrapTable('resetView');
});
$('#modal_cobros').on('show.bs.modal', function (e) {
    public_actualizar_descuentos = false;

    resetForm('#formulario_cobro');

    $('#detalles_descuento').addClass('d-none');
    $('#detalle_descuento_entidad').addClass('d-none');
    $('#tabla_cobros').bootstrapTable('removeAll');

    $('.nota_credito').addClass('d-none');
    $('.entidad').addClass('d-none');
    $('.detalles').removeClass('d-none');
    $('.detalles').removeClass('col-md-3 col-sm-6');
    $('.detalles').addClass('col-md-6 col-sm-6');
    $('label[for=detalles]').removeClass('label-required');
    $('label[for=detalles]').html(`Detalles<sup class="ml-1">[F4]</sup>`);
    $('#detalle_descuento').addClass('d-none')
    $('#detalle_descuento_entidad').addClass('d-none')

    let metodo_pago = $('#descuento_metodo_pago').select2('data')[0];
    let entidad = $('#descuento_entidad').select2('data')[0];

    $('#metodo_pago').select2('trigger', 'select', {
        data: { id: metodo_pago.id, text: metodo_pago.text, entidad: metodo_pago.entidad }
    });

    if (entidad) {
        $('#entidad').select2('trigger', 'select', {
            data: { id: entidad.id, text: entidad.text }
        });
    }

});

function modal_cobros_actualizar() {
    let total = total_venta();
    let cobros = total_cobros();
    let saldo = total - cobros;
    let saldo_venta = (saldo > 0) ? saldo : 0;
    
    $('#monto').val(separadorMiles(saldo_venta));
    $('#saldo_venta').html(separadorMiles(saldo_venta));
    $('#total_venta').html(separadorMiles(total));

    if (saldo_venta == 0) {
        $('#metodo_pago').prop('disabled', true);
        $('#monto').prop('disabled', true);
        $('#detalles').prop('disabled', true);
        $('#entidad').prop('disabled', true)
        $('#metodo_pago').val(null).trigger('change');
        $("#monto").val(0);
    } else {
        $('#metodo_pago').prop('disabled', false);
        $('#monto').prop('disabled', false);
        $('#detalles').prop('disabled', false);
        $('#entidad').prop('disabled', false)
    }
    
    setTimeout(() => {
        let metodo_pago = $('#metodo_pago').select2('data')[0];
        if (metodo_pago) {
            if (metodo_pago.id == 7) {
                $('#nro_nota_credito').focus().select();
            } else if (metodo_pago.id == 9) {
                $('#btn_agregar').focus().select();
            } else if (metodo_pago.entidad == 1 && !$('#entidad').val()) {
                $('#entidad').focus();
            } else {
                $('#monto').focus().select();
            }
        } else if (saldo_venta == 0) {
            $("#btn_imprimir_factura").focus();
        } else {
            $('#metodo_pago').focus();
        }
    }, 150);
}

function buscar_nota_credito(numero) {
    var datos = {};
    return $.ajax({
        dataType: 'json',
        cache: false,
        url,
        timeout: 10000,
        type: 'POST',
        data: { q:'buscar_nota_credito', numero },
        beforeSend: function(){
            NProgress.start();
            loading();
        },
        success: function (data) {
            NProgress.done();
            swal.close();
        },
        error: function (jqXhr) {
            NProgress.done();
            swal.close();
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)
        }
    });
}
// Fin cobros

$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function (e) {
    e.preventDefault();
    let data = $('#tabla_detalle').bootstrapTable('getData');
    let stock_insuficiente = data.find(function (row){
        if (row.tipo == 1) {
            if (parseInt(row.stock_fraccionado) == 0 && parseInt(row.stock) == 0 || parseInt(row.cantidad) > parseInt(row.stock) && parseInt(row.cantidad) > parseInt(row.stock_fraccionado)) {
             return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    })


    if ($(this).valid() === false) {
        $('html, body').animate({ scrollTop: 0 }, 'slow');
        return false;
    } else if (public_caja_abierta === false) {
        //alertDismissJS('La caja se encuentra cerrada', 'error', () => $('#btn_abrir_caja').click());
        alertDismissJsSmall('La caja se encuentra cerrada', 'error', 2000, () => $('#btn_abrir_caja').click())
        return false;
    } else if (!$('#id_cliente').val()) {
        alertDismissJsSmall('Cliente no registrado', 'error', 2000, () => $('#modal_clientes_new').modal('show'))
        return false;
    } else if ($('#delivery').is(':checked') && $('#id_delivery').val() == '') {
        alertDismissJsSmall('Ningún delivery seleccionado', 'error', 2000,() => $('#modal_delivery').modal('show'))
        return false;
    } else if ($('#tabla_detalle').bootstrapTable('getData').length == 0) {
        alertDismissJsSmall('Ningún producto agregado. Favor verifique.', 'error', 2000)
        return false;
    }else if(stock_insuficiente){
        alertDismissJsSmall(`El producto ${stock_insuficiente.producto} no cuenta con stock suficiente` , 'error', 2000)
        return false;
    }

    if ($('#condicion').val() == 2) {
        sweetAlertConfirm({
            title: `¿Finalizar compra e imprimir factura?`,
            confirm: cargar
        });
    } else {
        $('#modal_cobros').modal('show');
    }
});

$('#btn_imprimir_factura').on('click', function (e) {
    sweetAlertConfirm({
        title: `¿Finalizar compra e imprimir factura?`,
        confirm: () => {
            $('#modal_cobros').modal('hide');
            cargar();
        }
    });
});


function cargar() {
    var data = $('#formulario').serializeArray();
    data.push({ name: 'productos', value: JSON.stringify($('#tabla_detalle').bootstrapTable('getData')) });
    data.push({ name: 'cobros', value: JSON.stringify($('#tabla_cobros').bootstrapTable('getData')) });

    $.ajax({
        url: url + '?q=cargar',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function () {
            NProgress.start();
            loading();
        },
        success: function (data, textStatus, jQxhr) {
            NProgress.done();
            Swal.close();
            if (data.status == 'ok') {
                var param = { id_factura: data.id_factura, imprimir: 'si', recargar: 'no' };
                OpenWindowWithPost("imprimir-ticket", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=500,height=650", "imprimirFactura", param);
                resetWindow();
            } else {
                //alertDismissJS(data.mensaje, data.status);
                alertDismissJsSmall(data.mensaje, data.status, 2000)

            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            Swal.close();
            //alertDismissJS($(jqXhr.responseText).text().trim(), "error");
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)

        }
    });
}

function total_venta() {
    let data = $('#tabla_detalle').bootstrapTable('getData');
    let total = data.reduce(function (acc, value) {
        let total = quitaSeparadorMiles(value.total);
        return acc += total;
    }, 0);
    return total;
}

function total_cobros() {
    let data = $('#tabla_cobros').bootstrapTable('getData');
    let total = data.reduce((acc, value) => acc += quitaSeparadorMiles(value.monto), 0);
    return total;
}

function total_cobros() {
    let data = $('#tabla_cobros').bootstrapTable('getData');
    let total = data.reduce((acc, value) => acc += quitaSeparadorMiles(value.monto), 0);
    return total;
}

function resetWindow() {
    resetForm('#formulario');
    resetForm('#formulario');
    $('#condicion').val('1').trigger('change');
    $('#tabla_detalle').bootstrapTable('removeAll');
    $('#tabla_cobros').bootstrapTable('removeAll');
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    $('#ruc').focus();
    $('#receta, #delivery').prop('checked', false).trigger('change');
    $('#descuento_metodo_pago').val(null).trigger('change');
    $('.razon_social').removeClass('col-md-4').addClass('col-md-6');
    $('.descuento_entidad').addClass('d-none');
    verificarTimbradoActivo();
    verificar_estado_caja();
    actualizarNotificaciones();
    //Actualiza los estados los estados de la tabla clientes puntos en comparacion al periodo_canje establecida en plan puntos
    actualiza_estado_puntos_cliente();
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
            if (data.caja_abierta) {
                $('#btn_abrir_caja').hide();
                $('#btn_cerrar_caja').show();
            } else {
                $('#btn_abrir_caja').show();
                $('#btn_cerrar_caja').hide();
            }
            if (data.status != 'ok' && show_alert === true) {
                alertDismissJsSmall(data.mensaje, data.status, 3000, function(){$('#ruc').focus()})
                //alertDismissJS(data.mensaje, data.status, () => $('#ruc').focus());
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            $('#btn_abrir_caja').show().prop('disabled', true);
            $('#btn_cerrar_caja').hide();
            //alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)
        }
    });
}


function verificarTimbradoActivo() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url_timbrado,
        cache: false,
        data: { q: 'verificar-timbrado' },
        beforeSend: function () { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
            //alertDismissJS(data.mensaje, data.status, () => $('#ruc').focus());
            alertDismissJsSmall(data.mensaje, data.status, 2000, () => $('#ruc').focus())
        },
        error: function (xhr) {
            NProgress.done();
        }
    });
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
            //alertDismissJS(data.mensaje, data.status);
            alertDismissJsSmall(data.mensaje, data.status, 2000)

        }
    });
}

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

$('#id_tipo').select2({
    dropdownParent: $("#modal_clientes_new"),
    placeholder: 'Seleccione',
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
            return { q: 'clientes_tipos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_cliente_tipo, text: obj.tipo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
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
}).on('select2:close', function () {
    // Se activan las acciones por teclado
    setTimeout(() => MousetrapTabVentas.unpause());
}).on('select2:open', function () {
    // Se activan las acciones por teclado
    MousetrapTabVentas.pause();
});

$('#formulario_clientes').submit(function (e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    let data = $(this).serializeArray();
    data.push({ name: 'tabla_direcciones', value: JSON.stringify($('#tabla_direcciones').bootstrapTable('getData')) });
    $.ajax({
        url: url + '?q=cargar_clientes',
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
                let cliente = buscarCliente($("#ruc_str").val());
                cliente.then(function(data){
                    cargarDatos(data);
                    $('#modal_clientes_new').modal('hide');
                });
            }else{
                alertDismissJsSmall(data.mensaje, data.status, 1500, function(){$('#ruc_str').focus()})
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000, function(){$('#ruc_str').focus()})
        }
    });
});

$('#tabla_sucursales').bootstrapTable({
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 250,
    sortName: 'sucursal',
    sortOrder: 'asc',
    uniqueId: 'id_sucursal',
    columns: [
        [
            { field: 'id_sucursal', align: 'left', valign: 'middle', title: 'ID', sortable: false, visible: false },
            { field: 'sucursal', align: 'left', valign: 'middle', title: 'Sucursal', sortable: false, cellStyle: bstTruncarColumna, width: 140 },
            { field: 'direccion', align: 'left', valign: 'middle', title: 'Dirección', sortable: false, cellStyle: bstTruncarColumna },
            { field: 'stock', align: 'right', valign: 'middle', title: 'Cantidad', sortable: false, width: 80 },
            { field: 'stock_fraccionado', align: 'right', valign: 'middle', title: 'Fraccionado', sortable: false, width: 50 },
        ]
    ]
});

$('#tabla_principios').bootstrapTable({
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 250,
    sortName: 'principio',
    sortOrder: 'asc',
    uniqueId: 'id_principio',
    columns: [
        [
            { field: 'id_principio', align: 'left', valign: 'middle', title: 'ID', sortable: false, visible: false },
            { field: 'nombre', align: 'left', valign: 'middle', title: 'Principio', sortable: false, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#btn_eliminar').on('click', function (e) {
    var id = $('#tabla_detalle').bootstrapTable('getSelections')[0].id;
    var producto = $('#tabla_detalle').bootstrapTable('getSelections')[0].producto;

    sweetAlertConfirm({
        title: `Eliminar Producto`,
        text: `¿Eliminar "${producto}"?`,
        confirmButtonText: 'Eliminar',
        confirmButtonColor: 'var(--danger)',
        confirm: function () {
            $('#tabla_detalle').bootstrapTable('removeByUniqueId', id);
            if ($('#tabla_detalle').bootstrapTable('getData').length == 0) {
                $("#btn_detalle_producto,#btn_eliminar").attr("disabled", true);
            }
        }
    });
});

$('#eliminar_todo').on('click', function (e) {
    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Está seguro de eliminar todos los productos?`,
        confirmButtonText: 'Eliminar',
        confirmButtonColor: 'var(--danger)',
        confirm: function () {
            $('#tabla_detalle').bootstrapTable('removeAll');
        }
    });
});

$('#btn_detalle_producto').on('click', function () {
    let selected = $('#tabla_detalle').bootstrapTable('getSelections')[0];
    if (selected.id_producto) {
        ver_detalles_producto(selected.id_producto);
    }
});

$('#btn_detalle_modal_productos').on('click', ver_detalle_modal_productos);

// Evita que se modifique el valor del checkbox
$('#fuera_de_plaza').on('change', function (e) {
    $(this).prop('checked', !$(this).is(':checked'));
});

function ver_detalle_modal_productos() {
    let selected = $('#tabla_productos').bootstrapTable('getSelections')[0];
    if (selected.id_producto) {
        ver_detalles_producto(selected.id_producto);
    }
}

function ver_detalles_producto(id_producto) {
    $('#modal_detalles').modal('show');
    $('#tabla_sucursales').bootstrapTable('refresh', { url: url + '?q=stock_por_sucursal&id_producto=' + id_producto });
    $('#tabla_principios').bootstrapTable('refresh', { url: url + '?q=ver_principios&id_producto=' + id_producto });

    $.ajax({
        url: url + '?q=ver_detalle',
        dataType: 'json',
        type: 'GET',
        contentType: 'application/x-www-form-urlencoded',
        data: { id_producto },
        beforeSend: function () {
            NProgress.start();
        },
        success: function (data, textStatus, jQxhr) {
            NProgress.done();
            $("#laboratorio").val(data.laboratorio);
            $("#proveedor").val(data.proveedor);
            $("#origen").val(data.origen);
            $("#tipo").val(data.tipo);
            $("#costo").val(separadorMiles(data.precio));
            $("#detalle_precio_fraccionado").val(separadorMiles(data.precio_fraccionado));
            $("#fuera_de_plaza").prop('checked', (data.fuera_de_plaza == 1) ? true : false);
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            //alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)
        }
    });
}

///// Abrir y cerrar caja ////
$('#modal_caja').on('shown.bs.modal', function (e) {
    // Reinicia los valores
    resetForm('#formulario_caja');
    $('.cantidad_moneda, .total').val('0');
    // Primer input de cantidad
    $('#cantidad').focus();
    $('#mc_nav_1').tab('show');
});

$('#mc_nav_1').on('shown.bs.tab', function() {
    $('#cantidad').focus();
});
$('#mc_nav_2').on('shown.bs.tab', function() {
    $('#monto_servicio').focus();
});
$('#mc_nav_3').on('shown.bs.tab', function() {
    $('#cantidad_sen').focus();
});

$('#btn_abrir_caja').click(function () {
    $('#formulario_caja').attr('action', 'abrir_caja');
    $('#modalLabel').text('Apertura de Caja');
    $('#btn_submit_caja').text('Abrir Caja');
    $('#tabModal').hide();
});

$('#btn_cerrar_caja').click(function () {
    $('#formulario_caja').attr('action', 'cerrar_caja');
    $('#modalLabel').text('Cierre de Caja');
    $('#btn_submit_caja').text('Cerrar Caja');
    $('#tabModal').show();
});

$('.cantidad_moneda').focus(function () { this.select() });

$('.cantidad_moneda').blur(function () { if ($(this).val() == '') $(this).val('0') });

$('.cantidad_moneda').keyup(function () {
    soloNumeros(this);
    // Contenedor de inputs
    let $contenedor = $(this).parent().parent();
    // Valor de la moneda
    let valor_moneda = $contenedor.find('.valor_moneda').val();
    // Total
    let total = quitaSeparadorMiles(valor_moneda) * quitaSeparadorMiles($(this).val());
    // Se agrega el total
    $contenedor.find('.total').val(separadorMiles(total));
    total_caja();
});

function total_caja() {
    let totales = $('#formulario_caja').find('.total');
    let total_caja = Object.values(totales).reduce((acc, cv) => acc + quitaSeparadorMiles(cv.value), 0);
    $('#total_caja').val(separadorMiles(total_caja));
}

// Para avanzar entre los campos dando enter
$('.cantidad_moneda').keydown(function (e) {
    if (e.which === 13) { e.preventDefault(); nextFocus($(this), $('.container-fluid'), 1); }
});

$('.cantidad_moneda_sen').focus(function () { this.select() });

$('.cantidad_moneda_sen').blur(function () { if ($(this).val() == '') $(this).val('0') });

$('.cantidad_moneda_sen').keyup(function () {
    soloNumeros(this);
    // Contenedor de inputs
    let $contenedor = $(this).parent().parent();
    // Valor de la moneda
    let valor_moneda = $contenedor.find('.valor_moneda_sen').val();
    // Total
    let total = quitaSeparadorMiles(valor_moneda) * quitaSeparadorMiles($(this).val());
    // Se agrega el total
    $contenedor.find('.total_sen').val(separadorMiles(total));
    total_caja_sen();
});

function total_caja_sen() {
    let totales = $('#formulario_caja').find('.total_sen');
    let total_caja = Object.values(totales).reduce((acc, cv) => acc + quitaSeparadorMiles(cv.value), 0);
    $('#total_caja_sen').val(separadorMiles(total_caja));
}

// Para avanzar entre los campos dando enter
$('.cantidad_moneda_sen').keydown(function (e) {
    if (e.which === 13) { e.preventDefault(); nextFocus($(this), $('.container-fluid'), 1); }
});

$.ajax({
    url: url_administrar_cajas + '?q=ver_servicios',
    dataType: 'json',
    type: 'post',
    contentType: 'application/x-www-form-urlencoded',
    data: {},
    beforeSend: function() {
        NProgress.start();
    },
    success: function(data, textStatus, jQxhr) {
        NProgress.done();

        let html = '';
        $.each(data, function(index, value) {
            html += `
                <div class="form-group-sm col-5">
                    ${index == 0 ? `<label class="label-sm" for="servicio_str">Servicio</label><br>` : ``}
                    <input type="hidden" name="servicios[]" value="${value.id_servicio}" readonly>
                    <input type="text" class="form-control form-control-sm input-sm" ${index == 0 ? `id="servicio_str"` : ``} title="${value.servicio}" value="${value.servicio}" readonly>
                </div>
                <div class="form-group-sm col-5">
                    ${index == 0 ? `<label class="label-sm" for="monto_servicio">Total</label><br>` : ``}
                    <input type="text" class="form-control form-control-sm input-sm text-right monto_servicio" name="montos_servicios[]" ${index == 0 ? `id="monto_servicio"` : ``} title="Total ${value.servicio}" value="0" onkeyup="separadorMilesOnKey(event,this)" required>
                </div>
            `;
        });
        $('#content_servicios').html(html);

        $('.monto_servicio').focus(function() { this.select() });
        $('.monto_servicio').blur(function() { if ($(this).val() == '') $(this).val('0') });
        $('.monto_servicio').keydown(function (e) {
            if (e.which === 13) { e.preventDefault(); nextFocus($(this),$('.container-fluid'),1); }
        });
        $('.monto_servicio').keyup(function() {
            soloNumeros(this);
            let totales = $('#formulario_caja').find('.monto_servicio');
            let total = Object.values(totales).reduce((acc, cv) => acc + quitaSeparadorMiles(cv.value), 0);
            $('#total_servicios').val(separadorMiles(total));
        });

    },
    error: function(jqXhr, textStatus, errorThrown) {
        NProgress.done();
        //alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
        alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)

    }
});


$('#formulario_caja').submit(function (e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url + '?q=' + $(this).attr('action'),
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
                $('#modal_caja').modal('hide');
                verificar_estado_caja(false);
                alertDismissJsSmall(data.mensaje, data.status, 1000, function(){$('#cantidad').focus()})
            }else{
                alertDismissJsSmall(data.mensaje, data.status,3000, function(){$('#cantidad').focus()})
            }
            
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            //setTimeout(() => alertDismissJS($(jqXhr.responseText).text().trim(), 'error'), 100);
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)

        }
    });
});

///// Fin abrir y cerrar caja ////

$('#btn_extraer').click(function() {
    $('#formulario_extraer').attr('action', 'extraer');
    $('#modalLabelExtraer').text('Extracción de Caja');
    $('#observacion').parent().parent().show();
    setTimeout(function(){
        $("#monto_extraccion").focus();
    },200);
    
    resetFormextra('#formulario_extraer');
});

function resetFormextra(form) {
    $(form).trigger('reset');
    resetValidateForm(form);
}

// GUARDAR DATOS DE EXTRACCION
$('#formulario_extraer').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url + '?q='+$(this).attr('action'),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: { monto_extraccion: $('#monto_extraccion').val(), observacion: $('#observacion_extr').val(), id_caja: $('#filtro_caja').val()},
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            
            if (data.status == 'ok') {
                $('#modal-extraccion').modal('hide');
                var param = { id_extraccion: data.id_extraccion, imprimir : 'si', recargar: 'no' };
                OpenWindowWithPost("imprimir-ticket-extraccion", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirTicket", param);
                resetWindow();
            }else{
                //alertDismissJS(data.mensaje, data.status);
                alertDismissJsSmall(data.mensaje, data.status, 2000, function(){$('#monto_extraccion').focus()})
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000, function(){$('#monto_extraccion').focus()})
        }
    });
});

// Descuentos
$('#descuento_metodo_pago').select2({
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
            return { q: 'metodos_pagos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_metodo_pago, text: obj.metodo_pago, entidad: obj.entidad} }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function () {
    let data = $('#descuento_metodo_pago').select2('data')[0];
    let entidad = $('#descuento_entidad').select2('data')[0];

    $('#descuento_entidad').val(null).trigger('change')
    if (data && data.entidad == 1) {
        $('.razon_social').addClass('col-md-4').removeClass('col-md-6');
        $('.descuento_entidad').removeClass('d-none');
        setTimeout(() => $('#descuento_entidad').focus(), 100);
    } else {
        $('.razon_social').removeClass('col-md-4').addClass('col-md-6');
        $('.descuento_entidad').addClass('d-none');
    }

    let tableData = $('#tabla_cobros').bootstrapTable('getData');
    let metodos_pagos = tableData.map(value => { return { id_metodo_pago: value.id_metodo_pago, id_entidad: value.id_entidad } });

    if (data && data.entidad == 0) {
        let id_metodo_pago = data.id;
        metodos_pagos.push({ id_metodo_pago, id_entidad: '' });
        if (public_actualizar_descuentos) actualizarDescuentos(metodos_pagos);
    } else if (!entidad) {
        let tableData = $('#tabla_detalle').bootstrapTable('getData');
        let productos = tableData.map(function(row) {
            let total_venta = parseInt(row.cantidad) * parseInt(row.precio_venta);

            row.remate = 0;
            row.tipo_descuento = '';
            row.descuento_porcentaje = 0;
            row.total = total_venta;
            return row;
        });

        $('#tabla_detalle').bootstrapTable('load', productos);
    }
}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
}).on('select2:close', function () {
    // Se activan las acciones por teclado
    setTimeout(() => { if (!$('.modal').is(':visible')) MousetrapTabVentas.unpause() });
}).on('select2:open', function () {
    // Se desactivan las acciones por teclado
    MousetrapTabVentas.pause();
});

$('#descuento_entidad').select2({
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
            return { q: 'entidades', term: params.term, page: params.page || 1, id: $('#metodo_pago').val() }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_entidad, text: obj.tipo_entidad } }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function (results) {
    var data = $('#descuento_entidad').select2('data')[0];
    let tableData = $('#tabla_cobros').bootstrapTable('getData');
    let metodos_pagos = tableData.map(value => { return { id_metodo_pago: value.id_metodo_pago, id_entidad: value.id_entidad } });

    if (data) {
        let id_metodo_pago = $('#descuento_metodo_pago').val();
        let id_entidad = data.id;

        metodos_pagos.push({ id_metodo_pago, id_entidad });
        if (public_actualizar_descuentos) actualizarDescuentos(metodos_pagos);
    }

}).on('select2:selecting', function(event) {
    $(this).find('option').remove();
}).on('select2:close', function () {
    // Se activan las acciones por teclado
    setTimeout(() => { if (!$('.modal').is(':visible')) MousetrapTabVentas.unpause() });
}).on('select2:open', function () {
    // Se desactivan las acciones por teclado
    MousetrapTabVentas.pause();
});

function actualizarDescuentos(metodos_pagos) {
    let tableData = $('#tabla_detalle').bootstrapTable('getData');
    let data = tableData.map(value => { return { id_producto: value.id_producto, id_lote: value.id_lote } });
    let productos = data.reduce(function(acc, row) {
        if(!acc.some(value => value.id_prod == row.id_prod && value.id_lote == row.id_lote)) {
            acc.push(row);
        }
        return acc
    }, []);

    if (productos.length > 0) {
        $.ajax({
            url: url + '?q=ver_descuentos',
            dataType: 'json',
            type: 'post',
            contentType: 'application/x-www-form-urlencoded',
            data: { metodos_pagos: JSON.stringify(metodos_pagos), productos: JSON.stringify(productos) },
            beforeSend: function () {
                NProgress.start();
            },
            success: function (data, textStatus, jQxhr) {
                NProgress.done();
                if (data.status == 'ok') {
                    let productos_descuentos = tableData.map(function(row) {
                        let producto = data.data.descuentos.find(value => value.id_producto == row.id_producto && value.id_lote == row.id_lote);
                        let total_venta = parseInt(row.cantidad) * parseInt(row.precio_venta);
                        let porcentaje_descuento = 0;

                        if (producto && (row.fraccionado == 0 || (row.fraccionado == 1 && row.descuento_fraccionado == 1))) {
                            porcentaje_descuento = parseFloat(producto.porcentaje);
                        }

                        row.descuento_porcentaje = porcentaje_descuento;

                        if (porcentaje_descuento > 0) {
                            row.total = total_venta - Math.round((total_venta * porcentaje_descuento) / 100);
                            row.remate = producto.remate;
                            row.tipo_descuento = producto.tipo;
                        } else {
                            row.total = total_venta;
                            row.remate = 0;
                            row.tipo_descuento = '';
                        }
                        return row;
                    });

                    $('#tabla_detalle').bootstrapTable('load', productos_descuentos);
                } else {
                    //alertDismissJS(data.mensaje, data.status);
                    alertDismissJsSmall(data.mensaje, data.status, 2500)

                }

                modal_cobros_actualizar();
            },
            error: function (jqXhr, textStatus, errorThrown) {
                NProgress.done();
                // setTimeout(() => alertDismissJS($(jqXhr.responseText).text().trim(), "error"), 100);
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)
            }
        });
    }
}

function totalPuntosVenta() {
    let tableData = $('#tabla_detalle').bootstrapTable('getData');
    let data = tableData.map(value => {
        return {
            id_producto: value.id_producto,
            cantidad: value.cantidad,
            precio_venta: value.precio_venta,
            descuento: value.descuento_porcentaje,
            total: value.total
        }
    });

    if (data.length > 0) {
        $.ajax({
            url: url + '?q=ver_total_puntos_venta',
            dataType: 'json',
            type: 'post',
            contentType: 'application/x-www-form-urlencoded',
            data: { productos: JSON.stringify(data) },
            beforeSend: function () {
                NProgress.start();
                loading();
            },
            success: function (data, textStatus, jQxhr) {
                NProgress.done();
                $('#puntos').val(separadorMiles(data.puntos));
                if (data.status == 'ok') {
                    swal.close();
                } else {
                    alertDismissJsSmall(data.mensaje, data.status, 2500)
                }
            },
            error: function (jqXhr, textStatus, errorThrown) {
                NProgress.done();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)
            }
        });
    }
}

$('#nav_3').on('shown.bs.tab', function() {
    $('#tabla_productos').bootstrapTable('resetView');
    $('#tabla_productos_clientes').bootstrapTable('resetSearch', '');
    $('#tabla_productos_clientes').bootstrapTable('refresh', { url: `${url}?q=ver_productos_clientes&id_cliente=${$('#id_cliente').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` });
    $('#tab_3').find('input[type="search"]').focus();
});

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
    $('#tabla_productos_clientes').bootstrapTable('refresh', { url: `${url}?q=ver_productos_clientes&id_cliente=${$('#id_cliente').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` });
});


function alturaTablaProductosClientes() {
    return bstCalcularAlturaTabla(170, 300);
}
function pageSizeTablaProductosClientes() {
    return Math.floor(alturaTablaProductosClientes() / 22) - 6;
}

$("#tabla_productos_clientes").bootstrapTable({
    url: url + '?q=ver_productos_stock',
    toolbar: '#toolbar_productos_clientes',
    // searchSelector: '#search',
    search: true,
    showRefresh: true,
    showToggle: false,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: alturaTablaProductosClientes(),
    pageSize: pageSizeTablaProductosClientes(),
    sortName: "producto",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    keyEvents: true,
    // rowStyle: rowStyleTablaProductos,
    paginationParts: ['pageInfo', 'pageList'],
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'fraccionado', title: 'E/F', align: 'center', valign: 'middle', sortable: false, formatter: fraccionado },
            { field: 'fecha', title: 'FECHA', align: 'left', valign: 'middle', visible: true },
            { field: 'numero', title: 'NÚMERO.', align: 'right.', valign: 'middle', visible: true },
            { field: 'id_producto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'descuento', title: 'DESCUENTO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'total_venta', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'condicion', title: 'CONDICIÓN', align: 'right', valign: 'center', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'vendedor', title: 'VENDEDOR', align: 'center', valign: 'center', sortable: true, visible:false},
        ]
    ]
});

$('#tabla_productos_clientes').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    seleccionarProducto(row);
});

