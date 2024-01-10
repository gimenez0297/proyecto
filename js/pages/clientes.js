var url = 'inc/clientes-data';
var url_listados = 'inc/listados';
var myMap;


function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    }
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    showColumns: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_cliente",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_cliente', title: 'ID Cliente', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'razon_social', title: 'Nombre / Razón Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'telefono', title: 'Teléfono', align: 'left', valign: 'middle', sortable: true },
            { field: 'celular',  title: 'Celular',align: 'left', valign: 'middle', sortable: true },
            { field: 'email', title: 'E-mail', align: 'left', valign: 'middle', sortable: true },
            { field: 'obs', title: 'Observación', align: 'left', valign: 'middle', sortable: true },
            // { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: $(window).height()-120,
                pageSize: Math.floor(($(window).height()-120)/50),
            });
        }, 100);
    });
});

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Cliente');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    $('#tab a[href="#tab-content-1"]').tab('show');
    $('#tabla_direcciones').bootstrapTable('removeAll')
    resetForm('#formulario');
});

$('#modal').on('shown.bs.modal', function (e) {
    $('#formulario').find('input[type!="hidden"], select, textarea').filter(':first').focus();
});

$('#tab-2').on('shown.bs.tab', function (event) {
    let latitud = $('#lat').val();
    let longitud = $('#lng').val();

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

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$('#formulario').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    var data = new FormData(this);
    data.append('tabla_direcciones', JSON.stringify($('#tabla_direcciones').bootstrapTable('getData')));
    $.ajax({
        url: url + '?q='+$(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        processData: false,
        contentType: false,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr){
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown){
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// ELIMINAR
$('#eliminar').click(function() {
    var id = $("#id_cliente").val();
    var nombre = $("#razon_social").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Cliente: ${nombre}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, nombre },	
                beforeSend: function() {
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal').modal('hide');
                        $('#tabla').bootstrapTable('refresh');
                    }
                },
                error: function (jqXhr) {
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });
});

$('#btn_buscar').on('click', async function(e) {
    let ruc = $('#ruc').val();

    if ($('#modal').is(':visible') && ruc) {
        let data = await buscarRUC(ruc);
        if (data.ruc) {
            $('#ruc').val(data.ruc+"-"+data.dv);
            $('#razon_social').val(data.razon_social);
            $('#telefono').val(data.telefono);
            $('#direccion').val(data.direccion);
        } else {
            let data = await buscarCI(ruc);
            if (data.cedula) {
                $('#ruc').val(data.cedula);
                $('#razon_social').val(data.apellido + ' ' + data.nombre_solo);
                $('#telefono').val(data.telefono);
                $('#direccion').val(data.direccion);
            } else {
                alertDismissJsSmall('RUC / CI no encontrado', 'error', 2000, () => $('#ruc').focus().select());
            }
        }
    }
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which){
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
        // F2 Buscar RUC / CI
        case 113:
            event.preventDefault();
            $('#btn_buscar').click();
            break;
    }
});

function editarDatos(row) {
    resetForm('#formulario');
    $('#tab a[href="#tab-content-1"]').tab('show');
    $('#modalLabel').html('Editar Cliente');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_cliente").val(row.id_cliente);
    $("#razon_social").val(row.razon_social);
    $("#ruc").val(row.ruc);
    $("#telefono").val(row.telefono);
    $("#celular").val(row.celular);
    $("#direccion").val(row.direccion);
    $("#email").val(row.email);
    $("#obs").val(row.obs);
    $('#referencia').val(row.referencia);
    if (row.latitud && row.longitud) {
        $('#lat').val(row.latitud);
        $('#lng').val(row.longitud);
    }
    if (row.id_tipo) {
        $("#id_tipo").append(new Option(row.tipo, row.id_tipo, true, true)).trigger('change');
    }

    $('#tabla_direcciones').bootstrapTable('refresh', { url: url+'?q=obtener-direcciones&id_cliente='+row.id_cliente });

}

$('#id_tipo').select2({
    dropdownParent: $("#modal"),
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
        data: function(params) {
            return { q: 'clientes_tipos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_cliente_tipo, text: obj.tipo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});


function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    $("#id_tipo").append(new Option('Minorista', 1, true, true)).trigger('change');
    $('#lat').val('-25.782007');
    $('#lng').val('-56.449813');
    resetValidateForm(form);
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


