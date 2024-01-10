var url= 'inc/puntos-clientes-data';
var url_listados = 'inc/listados';

var $tabla_elegida;

// Acciones por teclado
// Acciones principales
Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#buscar_producto_acumulacion').click() });
Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#buscar_producto_canjeo').click() });

var MousetrapModalBuscar = new Mousetrap();
MousetrapModalBuscar.bindGlobal('f1', (e) => { e.preventDefault(); $('#filtro_buscar_proveedor').focus(); });
MousetrapModalBuscar.bindGlobal('f2', (e) => { e.preventDefault(); $('#filtro_buscar_origen').focus(); });
MousetrapModalBuscar.bindGlobal('f3', (e) => { e.preventDefault(); $('#filtro_buscar_tipo').focus(); });
MousetrapModalBuscar.bindGlobal('f4', (e) => { e.preventDefault(); $('#filtro_buscar_laboratorio').focus(); });
MousetrapModalBuscar.bindGlobal('f5', (e) => { e.preventDefault(); $('#filtro_buscar_marca').focus(); });
MousetrapModalBuscar.bindGlobal('f6', (e) => { e.preventDefault(); $('#filtro_buscar_rubro').focus(); });
MousetrapModalBuscar.bindGlobal('f7', (e) => { e.preventDefault(); $('#btn_incluir').click(); });
MousetrapTableNavigation('#tabla_buscar', MousetrapModalBuscar, cargarTabla);

var MousetrapAcumulacion = new Mousetrap(document.querySelector('#card-acumulacion'));
MousetrapTableNavigationCell('#tabla_acumulacion', MousetrapAcumulacion,true);
MousetrapAcumulacion.bindGlobal('del', (e) => {
    e.preventDefault();
    let selections = $('#tabla_acumulacion').bootstrapTable('getSelections');
    if (selections.length > 0) {
        let id = selections[0].id_producto;
        $('#tabla_acumulacion').bootstrapTable('removeByUniqueId', id)
    }
});

var MousetrapCanjeo = new Mousetrap(document.querySelector('#card-canjeo'));
MousetrapTableNavigationCell('#tabla_canjeo', MousetrapCanjeo,true);
MousetrapCanjeo.bindGlobal('del', (e) => {
    e.preventDefault();
    let selections = $('#tabla_canjeo').bootstrapTable('getSelections');
    if (selections.length > 0) {
        let id = selections[0].id_producto;
        $('#tabla_canjeo').bootstrapTable('removeByUniqueId', id)
    }
});

$('#modal_buscar').on('show.bs.modal', function(e) {
    Mousetrap.pause();
    MousetrapModalBuscar.unpause();
});

$('#modal_buscar').on('hide.bs.modal', function(e) {
    Mousetrap.unpause();
    MousetrapModalBuscar.pause();
});

$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $('#tabla').bootstrapTable('refreshOptions', { 
                height: alturaTabla(),
                pageSize: pageSizeTabla(),
            });
        }, 100);
    });
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

window.accionesFila = {
    'click .eliminar': function (e, value, row, index) {
        //eliminar(row.id_descuento_pago_filtro);
        this.$el.bootstrapTable('removeByUniqueId', row.id_producto)
    }
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-xs eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}


// Buscar
$('#filtro_buscar_proveedor').select2({
    dropdownParent: $("#modal_buscar"),
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
        data: function(params) {
            return { q: 'proveedores', term: params.term, page: params.page || 1 , tipo_proveedor: 1}
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_proveedor, text: obj.proveedor }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_origen').select2({
    dropdownParent: $("#modal_buscar"),
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
        data: function(params) {
            return { q: 'origenes', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_origen, text: obj.origen }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_tipo').select2({
    dropdownParent: $("#modal_buscar"),
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
        data: function(params) {
            return { q: 'tipos_productos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_tipo_producto, text: obj.tipo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_laboratorio').select2({
    dropdownParent: $("#modal_buscar"),
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
        data: function(params) {
            return { q: 'laboratorios', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_laboratorio, text: obj.laboratorio }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_marca').select2({
    dropdownParent: $("#modal_buscar"),
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
        data: function(params) {
            return { q: 'marcas', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_marca, text: obj.marca }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', actualizarTablaBuscar);

$('#filtro_buscar_rubro').select2({
    dropdownParent: $("#modal_buscar"),
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
        data: function(params) {
            return { q: 'rubros', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_rubro, text: obj.rubro }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', actualizarTablaBuscar);

//MOSTRAR DATOS EN EL INPUT


    $.ajax({
        url: 'inc/puntos-clientes-data?q=ver',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data:{ } ,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done( );
            $.each(data, function(key, value) {
               if(value.tipo==1){
                $("#cantidad_acumulacion").val(separadorMiles(value.cantidad));
                $("#puntos_acumulacion").val(separadorMiles(value.puntos));
                $("#configuracion_acumulacion").val(value.configuracion).trigger('change');
                $('#acumular_credito').prop('checked', (value.ventas_credito == '1')).trigger('change');
                $('#tabla_acumulacion').bootstrapTable('refresh', { url: `${url}?q=ver_productos_cargados&id=${1}` });
                cambiarNombreAcumulacion(value.configuracion);
                mostrarTablaAcumulacion(value.configuracion);
               }else{
                $("#cantidad_canjeo").val(separadorMiles(value.cantidad));
                $("#puntos_canjeo").val(separadorMiles(value.puntos));
                $("#configuracion_canjeo").val(value.configuracion).trigger('change');
                $('#canjeo_credito').prop('checked', (value.ventas_credito == '1')).trigger('change');
                $('#tabla_canjeo').bootstrapTable('refresh', { url: `${url}?q=ver_productos_cargados&id=${2}`});
                $('#periodo_vencimiento').val(separadorMiles(value.periodo_canje));
                cambiarNombreCanjeo(value.configuracion);
                mostrarTablaCanjeo(value.configuracion);
               }
            });        
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

$('#configuracion_acumulacion').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('select2:select', function () {
    var data = $('#configuracion_acumulacion').select2('data')[0];
    cambiarNombreAcumulacion(data.id);
    mostrarTablaAcumulacion(data.id);
    $('#cantidad_acumulacion').val('1')
    $('#puntos_acumulacion').val('1')
}).on('change',function (){
    $('#tabla_acumulacion').bootstrapTable('removeAll')
}).on('select2:open', function () {
    // Se desactivan las acciones por teclado del modal
    MousetrapAcumulacion.pause();
    MousetrapCanjeo.pause();
}).on('select2:close', function () {
    MousetrapCanjeo.unpause();
    MousetrapAcumulacion.unpause();
});

$('#configuracion_canjeo').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('select2:select', function () {
    var data = $('#configuracion_canjeo').select2('data')[0];
    cambiarNombreCanjeo(data.id);
    mostrarTablaCanjeo(data.id);
    $('#cantidad_canjeo').val('1')
    $('#puntos_canjeo').val('1')
}).on('change',function (){
    $('#tabla_canjeo').bootstrapTable('removeAll')
}).on('select2:open', function () {
    // Se desactivan las acciones por teclado del modal
    MousetrapAcumulacion.pause();
    MousetrapCanjeo.pause();
}).on('select2:close', function () {
    MousetrapCanjeo.unpause();
    MousetrapAcumulacion.unpause();
});



function cambiarNombreCanjeo($id){
    if ($id == 4 || $id == 5) {
        $('label[for=cantidad_canjeo]').html(`Cantidad`);
        $('#span_cj').html(`Cant.`)
    }else{
        $('label[for=cantidad_canjeo]').html(`Monto`);
        $('#span_cj').html(`Gs.`)
    }
}

function cambiarNombreAcumulacion($id){
    if ($id == 4 || $id == 5) {
        $('label[for=cantidad_acumulacion]').html(`Cantidad`);
        $('#span_ac').html(`Cant.`)
    }else{
        $('label[for=cantidad_acumulacion]').html(`Monto`);
        $('#span_ac').html(`Gs.`)
    }
}

function mostrarTablaCanjeo($id){
    if ($id == 2 || $id == 3) {
        $("#canjeo").removeClass('d-none');
        // $("#toolbar_canjeo").removeClass('d-none');
        $("#tabla_canjeo").bootstrapTable('hideColumn','puntos');
        $(".puntos_canjeo").removeClass("d-none");
        $(".logo_canjeo").removeClass("d-none");
        $(".cantidad_canjeo").removeClass("d-none");
    }else if($id == 6){
        $("#canjeo").removeClass('d-none');
        // $("#toolbar_canjeo").removeClass('d-none');
        $("#tabla_canjeo").bootstrapTable('showColumn','puntos');
        $(".puntos_canjeo").addClass("d-none");
        $(".logo_canjeo").addClass("d-none");
        $(".cantidad_canjeo").addClass("d-none");
    }
    else{
        $("#canjeo").addClass('d-none');
        // $("#toolbar_canjeo").addClass('d-none');
        $("#tabla_canjeo").bootstrapTable('hideColumn','puntos');
        $(".puntos_canjeo").removeClass("d-none");
        $(".logo_canjeo").removeClass("d-none");
        $(".cantidad_canjeo").removeClass("d-none");
    }
}

function mostrarTablaAcumulacion($id){
    if ($id == 2 || $id == 3) {
        $("#acumulacion").removeClass('d-none');
        // $("#toolbar_acumulacion").removeClass('d-none');
        $("#tabla_acumulacion").bootstrapTable('hideColumn','puntos');
        $(".puntos_acumulacion").removeClass("d-none");
        $(".logo_acumulacion").removeClass("d-none");
        $(".cantidad_acumulacion").removeClass("d-none");
    }else if($id == 6){
        $("#acumulacion").removeClass('d-none');
        $("#toolbar_acumulacion").removeClass('d-none');
        $("#tabla_acumulacion").bootstrapTable('showColumn','puntos');
        $(".puntos_acumulacion").addClass("d-none");
        $(".logo_acumulacion").addClass("d-none");
        $(".cantidad_acumulacion").addClass("d-none");
    }
    else{
        $("#acumulacion").addClass('d-none');
        // $("#toolbar_acumulacion").addClass('d-none');
        $("#tabla_acumulacion").bootstrapTable('hideColumn','puntos');
        $(".puntos_acumulacion").removeClass("d-none");
        $(".logo_acumulacion").removeClass("d-none");
        $(".cantidad_acumulacion").removeClass("d-none");
    }
}

function alturaTablaAcumulacion() {
    return bstCalcularAlturaTabla(350, 420);
}

$("#tabla_acumulacion").bootstrapTable({
    toolbar: '#toolbar_acumulacion',
    showExport: false,
    search: false,
    showRefresh: false,
    //searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    height: alturaTablaAcumulacion(),
    sortName: "producto",
    sortOrder: 'desc',
    trimOnSearch: false,
    uniqueId: 'id_producto',
    keyEvents: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true  },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'puntos', title: 'Pts', align: 'center', valign: 'middle', sortable: true, visible:false,width: 150, editable: { type: 'text' } },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila, width: 120 },
        ]
    ]
});

$('#tabla_acumulacion').on('editable-shown.bs.table', (e, field, row, $el, editable) => editable.input.$input[0].value = row[field] != 0 ? row[field] : '');

$.extend($('#tabla_acumulacion').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">',
});

function alturaTablaCanjeo() {
    return bstCalcularAlturaTabla(350, 420);
}

$("#tabla_canjeo").bootstrapTable({
    toolbar: '#toolbar_canjeo',
    showExport: false,
    search: false,
    showRefresh: false,
    //searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    height: alturaTablaCanjeo(),
    sortName: "producto",
    sortOrder: 'desc',
    trimOnSearch: false,
    uniqueId: 'id_producto',
    keyEvents: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true  },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'puntos', title: 'Pts', align: 'center', valign: 'middle', sortable: true, visible:false,width: 150, editable: { type: 'text' } },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila, width: 120 },
        ]
    ]
});

$('#tabla_canjeo').on('editable-shown.bs.table', (e, field, row, $el, editable) => editable.input.$input[0].value = row[field] != 0 ? row[field] : '');

$.extend($('#tabla_canjeo').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '-',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">',
});



function alturaTablaBuscar() {
    return bstCalcularAlturaTabla(300, 300);
}

function pageSizeTablaBuscar() {
    return Math.floor(alturaTablaBuscar() / 35);
}

$("#tabla_buscar").bootstrapTable({
    toolbar: '#toolbar_buscar',
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
    height: alturaTablaBuscar(),
    pageSize: pageSizeTablaBuscar(),
    sortName: "producto",
    sortOrder: 'asc',
    trimOnSearch: false,
    keyEvents: true,
    // singleSelect: true,
    clickToSelect: true,
    keyEvents: true,
    paginationParts: ['pageInfo', 'pageList'],
    rowStyle: rowStyleTablaBuscar,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true  },
            { field: 'id_producto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'laboratorio', title: 'LABORATORIO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
}); 

function rowStyleTablaBuscar(row, index) {
    let id = row.id_producto;
    let tableData = $tabla_elegida.bootstrapTable('getData');
    let data = tableData.find(value => value.id_producto == id);
    if (data) {
        return { classes: 'text-muted' };
    } else {
        return {};
    }
}


$('#modal_buscar').on('shown.bs.modal', function (e) {
    $('#modalBuscarLabel').html('Buscar Producto');
    $('#tabla_buscar').bootstrapTable('resetView');
    $('#tabla_buscar').bootstrapTable('resetSearch', '');
    $(this).find('select').val(null).trigger('change');
    //$tabla.parents('.bootstrap-table').find('input[type="search"]').focus();
    actualizarTablaBuscar();
});

function actualizarTablaBuscar() {
    let id_proveedor = $('#filtro_buscar_proveedor').val();
    let id_origen = $('#filtro_buscar_origen').val();
    let id_tipo = $('#filtro_buscar_tipo').val();
    let id_laboratorio = $('#filtro_buscar_laboratorio').val();
    let id_marca = $('#filtro_buscar_marca').val();
    let id_rubro = $('#filtro_buscar_rubro').val();

    $('#tabla_buscar').bootstrapTable('refresh', { url: `${url}?q=ver_productos&id_proveedor=${id_proveedor}&id_origen=${id_origen}&id_tipo=${id_tipo}&id_laboratorio=${id_laboratorio}&id_marca=${id_marca}&id_rubro=${id_rubro}` });
}

function cargarTabla(data) {
    let tableData = $tabla_elegida.bootstrapTable('getData');
    let repetidos = [];
    let map_data = [];

    $.each(data, function (index, value) {
        let id = value.id_producto;
        let row = tableData.find(value => value.id_producto == id);
        if (row) repetidos.push(row); 
        map_data.push({
            id_producto: value.id_producto,
            codigo: value.codigo,
            producto: value.producto,
            presentacion: value.presentacion,
            puntos: 0
        });
    });

    if (repetidos.length > 0) {
        alertDismissJS(`Producto '${repetidos[0].producto}' ya agregado`, 'error');
    } else {
        $tabla_elegida.bootstrapTable('append', map_data);
        $('#modal_buscar').modal('hide');
    }
}

//GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();
    var data = $(this).serializeArray();

    var select_acumulacion = $('#configuracion_acumulacion').select2('data')[0];
    var select_canjeo = $('#configuracion_canjeo').select2('data')[0];

    if(select_acumulacion.id == 2 || select_acumulacion.id == 3 || select_acumulacion.id == 6){
        var tableDataAcumulacion = $('#tabla_acumulacion').bootstrapTable('getData');
        if(tableDataAcumulacion.length == 0){
            alertDismissJS(`Favor agregar producto/s en la tabla de acumulación.`, 'error');
            return;
        }else{
            data.push({ name: 'productos_acumulacion', value: JSON.stringify(tableDataAcumulacion) });
        }
    }

    if(select_canjeo.id == 2 || select_canjeo.id == 3 || select_canjeo.id == 6){
        var tableDataCanjeo = $('#tabla_canjeo').bootstrapTable('getData');
        if(tableDataCanjeo.length == 0){
            alertDismissJS(`Favor agregar producto/s en la tabla de canjeo.`, 'error');
            return;
        }else{
            data.push({ name: 'productos_canjeo', value: JSON.stringify(tableDataCanjeo) });
        }
    }

    if ($(this).valid() === false) return false;
    
    $.ajax({
        url: 'inc/puntos-clientes-data?q=editar',
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
                alertDismissJS(data.mensaje, data.status, function(){ 
                    location.reload(); 
                });
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('.agregar').on('click', function() {
$tabla_elegida = $('#' + $(this).parents('.bootstrap-table').find('.fixed-table-body').find('table').attr('id'));
})


$('#btn_modal_agregar').on('click', function() {
    let selections = $('#tabla_buscar').bootstrapTable('getSelections'); 
    cargarTabla(selections);
});

$('#modal_buscar').find('select').on('select2:close', function(){
    $('#modal_buscar').focus();
});

$('#buscar_producto_acumulacion').on('click', function() {
    $("#tipo_dato").val(1);
});

$('#buscar_producto_canjeo').on('click', function() {
    $("#tipo_dato").val(2);
});

$('#btn_incluir').on('click', function() {

    var id_proveedor    = $("#filtro_buscar_proveedor").val();
    var id_origen       = $("#filtro_buscar_origen").val();
    var id_tipo         = $("#filtro_buscar_tipo").val();
    var id_laboratorio  = $("#filtro_buscar_laboratorio").val();
    var id_marca        = $("#filtro_buscar_marca").val();
    var id_rubro        = $("#filtro_buscar_rubro").val();

    var tipo_dato       = $("#tipo_dato").val();

    if(id_proveedor == null && id_origen == null && id_tipo == null && id_laboratorio == null && id_marca == null && id_rubro == null){
        setTimeout(() => alertDismissJS('Debe de seleccionar al menos un ítem para continuar.', 'error'), 100);
        return;
    }else{

        if(tipo_dato == 1){
            $.ajax({
                dataType: 'json',
                url: url + '?q=recuperar_busqueda',
                type: 'post',
                data: { id_proveedor: id_proveedor, id_origen: id_origen, id_tipo: id_tipo , id_laboratorio: id_laboratorio, id_marca: id_marca, id_rubro: id_rubro},
                beforeSend: function() {
                    NProgress.start();
                },
                success: function(data) {
                    NProgress.done();
                    var detalle = data.datos;
                    // $("#tabla_acumulacion").bootstrapTable('append', data)

                    console.log(detalle);
                    if(detalle){
                        $.each(detalle, function (i, v) {
                            $("#tabla_acumulacion").bootstrapTable('insertRow', {
                                index: 0,
                                row: {
                                    id_producto     : v['id_producto'],
                                    codigo          : v['codigo'],
                                    producto        : v['producto'],
                                    presentacion    : v['presentacion'],
                                    puntos          : 0
                                }
                            });
                        });
                        $('#modal_buscar').modal('hide');
                    }

                },
                error: function(jqXhr, textStatus, errorThrown) {
                    NProgress.done();
                }
            });
        }else{
            $.ajax({
                dataType: 'json',
                url: url + '?q=recuperar_busqueda',
                type: 'post',
                data: { id_proveedor: id_proveedor, id_origen: id_origen, id_tipo: id_tipo , id_laboratorio: id_laboratorio, id_marca: id_marca, id_rubro: id_rubro},
                beforeSend: function() {
                    NProgress.start();
                },
                success: function(data) {
                    NProgress.done();
                    var detalle = data.datos;
                    // $("#tabla_acumulacion").bootstrapTable('append', data)

                    console.log(detalle);
                    if(detalle){
                        $.each(detalle, function (i, v) {
                            $("#tabla_canjeo").bootstrapTable('insertRow', {
                                index: 0,
                                row: {
                                    id_producto     : v['id_producto'],
                                    codigo          : v['codigo'],
                                    producto        : v['producto'],
                                    presentacion    : v['presentacion'],
                                    puntos          : 0
                                }
                            });
                        });
                        $('#modal_buscar').modal('hide');
                    }

                },
                error: function(jqXhr, textStatus, errorThrown) {
                    NProgress.done();
                }
            });
        }
        
    }

});
