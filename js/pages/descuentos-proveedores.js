var url = 'inc/descuentos-proveedores-data';
var url_listados = 'inc/listados';

var MousetrapModalTab1 = new Mousetrap();
var MousetrapModalTab2 = new Mousetrap();

MousetrapModalTab1.pause();
MousetrapModalTab2.pause();

MousetrapModalTab1.bind('f1', (e) => { e.preventDefault(); $('#agregar_detalle').click(); });
MousetrapModalTab1.bind('f2', (e) => { e.preventDefault(); $('#btn_sucursales_tab_1').click(); });
MousetrapModalTab2.bind('f1', (e) => { e.preventDefault(); $('#agregar_producto').click(); });
MousetrapModalTab2.bind('del', (e) => { 
    e.preventDefault();
    var data = $('#tabla-proveedor').bootstrapTable('getSelections');
    if (data) $('#tabla-proveedor').bootstrapTable('removeByUniqueId', data[0].id_producto);
});

MousetrapTableNavigationCell('#tabla-proveedor', MousetrapModalTab2, true);
MousetrapTableNavigationCell('#tabla_detalle', MousetrapModalTab1, true);

// Se desactivan y activan las acciones de los tabs
$('#datos-descuentos-tab').on('shown.bs.tab', () => MousetrapModalTab1.unpause());
$('#proveedor-principal-tab').on('shown.bs.tab', () => MousetrapModalTab2.unpause());

$('#datos-descuentos-tab').on('hidden.bs.tab', () => MousetrapModalTab1.pause());
$('#proveedor-principal-tab').on('hidden.bs.tab', () => MousetrapModalTab2.pause());

$('#modal_sucursales').on('shown.bs.modal', () => MousetrapModalTab1.pause());
$('#modal_sucursales').on('hidden.bs.modal', () => MousetrapModalTab1.unpause());

$('#proveedor-principal-tab').on('shown.bs.tab', function (event) {
    $($(this).attr('href')).focus();
});

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

function iconosFilaSucursales(value, row, index) {
    let sucursal_id = $('#filtro_sucursal').val();
    let disabled = row.id_sucursal == sucursal_id ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_sucursal" title="Eliminar" ${disabled} ><i class="fas fa-trash"></i></button>`
    ].join('');
}

window.accionesFilaSucursales = {
    'click .eliminar_sucursal': function (e, value, row, index) {
        let id = $('#filtro_sucursal').val();
        if (id == row.id_sucursal) {
            alertDismissJS('No se puede eliminar la sucursal', "error");
            return false;
        }
        let nombre = row.sucursal;
        sweetAlertConfirm({
            title: `Eliminar Sucursal`,
            text: `¿Eliminar el Sucursal: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_sucursales').bootstrapTable('removeByUniqueId', row.id_sucursal);
            }
        });
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
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_proveedor",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_proveedor', title: 'ID Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'proveedor', title: 'Proveedor', align: 'left', valign: 'middle', sortable: true, visible: true , cellStyle: bstTruncarColumna},
            { field: 'nombre_fantasia', title: 'Nombre Fantasia', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'ruc', title: 'RUC', align: 'center', valign: 'middle', sortable: true, visible: true, cellStyle: bstTruncarColumna},
            { field: 'contacto', title: 'Contacto', align: 'left', valign: 'middle', sortable: true, visible:false},
            { field: 'direccion', title: 'Dirección', align: 'left', valign: 'middle', sortable: true, visible:false },
            { field: 'telefono', title: 'Telefono', align: 'left', valign: 'middle', sortable: true, visible:true },
            { field: 'email', title: 'E-mail', align: 'left', valign: 'middle', sortable: true, visible:false },
            { field: 'obs', title: 'Obs', align: 'left', valign: 'middle', sortable: true, visible:false },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'center', valign: 'middle', sortable: true, visible:false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 120 },
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

$("#proveedor-principal-tab").on('shown.bs.tab', function(){
    $("#tabla-proveedor").bootstrapTable('resetView', {});
})


$("#tabla_sucursales").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: false,
    height: 200,
    uniqueId: 'id_sucursal',
    columns: [
        [
            { field: 'id_sucursal', title: 'ID Sucursal', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true},
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaSucursales, formatter: iconosFilaSucursales, width: 150 }
        ]
    ]
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
            $("#tabla-proveedor").bootstrapTable('refreshOptions', { 
                height: $(window).height()-270,
                pageSize: Math.floor(($(window).height()-270)/50),
            });
        }, 100);
    });

    // METODOS PAGOS
    $.ajax({
        dataType: 'json',
        async: true,
        cache: false,
        url: url,
        type: 'POST',
        data: { q: 'metodos_pagos'},
        beforeSend: function() {
            NProgress.start();
        },
        success: function (json){
            NProgress.done();
            let cols = [
                [
                    { title: 'Producto', align: 'center', valign: 'middle', sortable: false, colspan: 11 },
                ],
                [
                    { field: 'check', align: 'center', valign: 'middle', checkbox: true },
                    { field: 'id_producto_proveedor', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
                    { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
                    { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, visible: true, cellStyle: bstTruncarColumna },
                    { field: 'id_proveedor', title: 'ID Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false},
                    { field: 'proveedor', title: 'Proveedor', align: 'left', valign: 'middle', sortable: true, visible:false},
                    { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, visible:true, cellStyle: bstTruncarColumna },
                    { field: 'codigo', title: 'Codigo', align: 'left', valign: 'middle', sortable: true, visible:true },
                    { field: 'costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, visible:true, formatter: separadorMiles },
                    { field: 'precio', title: 'P. Público', align: 'right', valign: 'middle', sortable: true, visible:true, formatter: separadorMiles },
                    { field: 'id_presentacion', sortable: false, visible:false },
                ]
            ];
            $.each(json, function(index, value) {
                cols[0].push({ align: 'center', valign: 'middle', title: value.sigla, sortable: false, colspan: 2 });
                cols[1].push({ field: `m_${value.id_metodo_pago}`, align: 'center', valign: 'middle', title: '%', sortable: true, width: 90, editable: { type: 'text' } });
                cols[1].push({ field: `p_desc_m_${value.id_metodo_pago}`, align: 'right', valign: 'middle', title: `P.`, sortable: true, width: 90, formatter: separadorMiles });
            });
            cols[0].push({ field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProducto, formatter: iconosFilaProducto, rowspan: 2 });
            $('#tabla-proveedor').bootstrapTable("refreshOptions", { columns: cols });
        },
        error: function (jqXhr) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

    setTimeout(function () {
        $("#tabla-proveedor").bootstrapTable('resetView');
    },100);
});

// Filtros
function alturaTablaDetalle() {
    return bstCalcularAlturaTabla(420, 420);
}
function iconosFilaDes(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}

window.accionesFilaDes = {
    'click .eliminar': function (e, value, row, index) {
        sweetAlertConfirm({
            title: `Eliminar`,
            text: `¿Esta seguro de eliminar el descuento seleccionado?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id_);
            }
        });
    }
}

$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle_pro',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: false,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    //showFooter: true,
    height: alturaTablaDetalle(),
    sortName: "numero",
    sortOrder: 'asc',
    uniqueId: 'id_',
    footerStyle: bstFooterStyle,
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [   
            { field: 'check', align: 'center', valign: 'middle', checkbox: true  },
            { field: 'id_', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_metodo_pago', title: 'ID Metodo Pago', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'metodo_pago', title: 'Metodo Pago', align: 'left', valign: 'middle', sortable: true },
            { field: 'id_origen', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'origen', title: 'Origen', align: 'right', valign: 'middle', sortable: true },
            { field: 'id_tipo', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo', title: 'Tipo', align: 'right', valign: 'middle', sortable: true},
            { field: 'id_laboratorio', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'laboratorio', title: 'Laboratorio', align: 'right', valign: 'middle', sortable: true},
            { field: 'id_marca', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'marca', title: 'Marca', align: 'right', valign: 'middle', sortable: true },
            { field: 'id_rubro', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'rubro', title: 'Rubro', align: 'right', valign: 'middle', sortable: true },
            { field: 'controlado', title: 'Controlado', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: bstCondicional },
            { field: 'porcentaje', title: '%', align: 'right', valign: 'middle', sortable: true, width: 150, editable: { type: 'text' } },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaDes,  formatter: iconosFilaDes, width: 120 },
        ]
    ]
});

$('#tabla_detalle').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
    $('input.autonumericTabla').initNumber(); //autonumeric
});

$.extend($('#tabla_detalle').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: `<input type="text" id="porcentaje-tabla" 
    class="form-control input-sm autonumericTabla"
    style="width:140px"
    data-allow-decimal-padding="false"
    data-decimal-character=","
    data-digit-group-separator="."
    data-maximum-value="100"
    value=""
    autocomplete="off">`,
    display: function(value) {
        let text2 = new Intl.NumberFormat(['ban', 'id']).format(value);
        if(isNaN(value)){
            text2 = 0;
        }
        $(this).text(text2 +'%');
    }
});

$('#tabla_detalle').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id_;
    let numerotabla = quitaSeparadorMilesFloat(row[field]);

    // Si la columna quedo en blanco
    if (!numerotabla) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = numerotabla;
    }

    if (field == 'porcentaje' && numerotabla > 100) {
        update_row[field] = oldValue;
        setTimeout(() => alertDismissJS(`El porcentaje de descuento supera el 100%`, 'error'), 100);
    }

    // Se actualizan los valores
    $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id, row: update_row });
});

$('#agregar_detalle').click(function() {
    let id_metodo       = $('#metodo_pago').val();
    let metodo          = $('select[name="metodo_pago"] option:selected').text().split(" - ")[1];
    let id_origen       = $('#origen').val();
    let origen          = $('select[name="origen"] option:selected').text();
    let id_tipo         = $('#tipo').val();
    let tipo            = $('select[name="tipo"] option:selected').text();
    let id_laboratorio  = $('#laboratorio').val();
    let laboratorio     = $('select[name="laboratorio"] option:selected').text();
    let id_marca        = $('#marca').val();
    let marca           = $('select[name="marca"] option:selected').text();
    let id_rubro        = $('#rubro').val();
    let rubro           = $('select[name="rubro"] option:selected').text();
    let porcentaje      = quitaSeparadorMilesFloat($('#porcentaje').val());
    let controlado      = $('#controlado').val();

    if(porcentaje == null || porcentaje == 0){
        alertDismissJS('Debe de ingresar un porcentaje mayor a 0%.', 'error');
        return;
    }

    if(porcentaje > 100){
        alertDismissJS(`El porcentaje de descuento supera el 100%`, 'error');
        return;
    }

    if(id_metodo == null && id_origen == null && id_tipo == null && id_laboratorio == null && id_marca == null && id_rubro == null && controlado == null){
        alertDismissJS('Debe de seleccionar al menos un ítem para continuar.', 'error');
        return;
    }

    let tableData = $('#tabla_detalle').bootstrapTable('getData');
    let data = tableData.find(value => value.id_metodo_pago == id_metodo && value.id_origen == id_origen && value.id_tipo == id_tipo && value.id_laboratorio == id_laboratorio && value.id_marca == id_marca && value.id_rubro == id_rubro && value.controlado == controlado);
    if (data) {
        alertDismissJS('Combinación de filtros ya cargada', 'error');
        return;
    }

    $("#tabla_detalle").bootstrapTable('insertRow', {
        index: 0,
        row: {
            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
            id_metodo_pago: id_metodo,
            metodo_pago: metodo,
            id_origen: id_origen,
            origen: origen,
            id_tipo: id_tipo,
            tipo: tipo,
            id_laboratorio: id_laboratorio,
            laboratorio:laboratorio, 
            id_marca: id_marca,
            marca:marca, 
            id_rubro: id_rubro,
            rubro:rubro, 
            controlado,
            porcentaje:porcentaje, 
        }
    });

    $("#porcentaje").val('');
    $("#formulario").find('select').val(null).trigger('change');
    $("#metodo_pago").focus();
});




function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
    $('#tabDescuentos a[href="#datos-descuentos"]').tab('show').trigger('shown.bs.tab');
}


$('#agregar-sucursal').click(function() {
    var tableData = $('#tabla_sucursales').bootstrapTable('getData');
    var sucursal = $('#sucursales').select2('data')[0];

    if (tableData.find(value => value.id_sucursal == sucursal.id)) {
        alertDismissJS("Sucursal ya agregada", 'error');
        return false;
    }

    $('#tabla_sucursales').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id_sucursal: sucursal.id,
            sucursal: sucursal.text,
        }
    });
    $('#sucursales').val(null).trigger('change');
    $('#sucursales').focus();
});


// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").validate({ ignore: '' });
$('#btn_guardar').on('click', () => $('#formulario').submit());
$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    var contenido = JSON.stringify($("#tabla-proveedor").bootstrapTable('getData'));
    data.push({name: "proveedor_principal", value: contenido});
    data.push({name: "detalles", value: JSON.stringify($('#tabla_detalle').bootstrapTable('getData')) });
    data.push({ name: 'sucursales', value: JSON.stringify($('#tabla_sucursales').bootstrapTable('getData')) });
    $.ajax({
        url: url + '?q='+$(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Descuento');
    $('#formulario').attr('action', 'editar');
    $('#modal').modal('show');

    $("#hidden_id").val(row.id_descuento_proveedor);
    $("#hidden_id_proveedor").val(row.id_proveedor);
    $("#porcentaje").val(row.porcentaje);

    $('#toolbar_detalles_text').html(`${row.proveedor}`);
    $('#toolbar_detalle').html(`${row.proveedor}`);

    // Select2
    if (row.id_metodo_pago) {
        $("#metodo_pago").append(new Option(row.metodo_pago, row.id_metodo_pago, true, true)).trigger('change');
    }
    if (row.id_origen) {
        $("#origen").append(new Option(row.origen, row.id_origen, true, true)).trigger('change');
    }
    if (row.id_tipo_producto) {
        $("#tipo").append(new Option(row.tipo, row.id_tipo_producto, true, true)).trigger('change');
    }
    if (row.id_laboratorio) {
        $("#laboratorio").append(new Option(row.laboratorio, row.id_laboratorio, true, true)).trigger('change');
    }
    if (row.id_marca) {
        $("#marca").append(new Option(row.marca, row.id_marca, true, true)).trigger('change');
    }
    if (row.id_rubro) {
        $("#rubro").append(new Option(row.rubro, row.id_rubro, true, true)).trigger('change');
    }

    $('#tabla_detalle').bootstrapTable('refresh', { url: url + '?q=sucursales_select&id_proveedor='+ $('#hidden_id_proveedor').val() +'&id_sucursal='+ $("#filtro_sucursal").val() });
    $('#tabla-proveedor').bootstrapTable('refresh', { url: url + '?q=ver-proveedor&proveedor='+ $('#hidden_id_proveedor').val() +'&sucursal='+ $("#filtro_sucursal").val() });
}

$('#marca').select2({
    dropdownParent: $("#modal"),
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
});

$('#tipo').select2({
    dropdownParent: $("#modal"),
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
});

$('#laboratorio').select2({
    dropdownParent: $("#modal"),
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
});

$('#metodo_pago').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'metodos_pagos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_metodo_pago, text: obj.metodo_pago }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#origen').select2({
    dropdownParent: $("#modal"),
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
});

$('#rubro').select2({
    dropdownParent: $("#modal"),
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
});

$('#sucursales').select2({
    dropdownParent: $("#modal_sucursales"),
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
            return { q: 'sucursales_descuento', term: params.term, page: params.page || 1, id: $('#filtro_sucursal').val() }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#filtro_sucursal').select2({
    placeholder: 'Sucursal',
    width: '200px',
    allowClear: false,
    selectOnClose: true,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.id_presentacion) {
        $result = $(`<span>${node.text} <small>(${node.presentacion})</small></span>`);
    }
    return $result;
}

$('#producto').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar Producto',
    width: '400px',
    allowClear: false,
    selectOnClose: false,
    templateResult: formatResult,
    templateSelection: formatResult,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'productos_proveedor', term: params.term, page: params.page || 1, id: $('#hidden_id_proveedor').val() }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) {
                    return {
                        id: obj.id_producto,
                        text: obj.producto,
                        codigo: obj.codigo,
                        id_presentacion: obj.id_presentacion,
                        presentacion: obj.presentacion,
                        precio: obj.precio,
                        costo: obj.costo
                    }
                }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#agregar_producto').on('click', function(e) {
    let data = $('#producto').select2('data')[0];

    if (!data) {
        alertDismissJS('Favor seleccione un producto', 'error', () => $('#producto').focus());
        return false;
    }

    // Si se repite un producto
    let tableData = $('#tabla-proveedor').bootstrapTable('getData');
    let producto = tableData.find(value => value.id_producto == data.id);
    if (producto) {
        alertDismissJS(`Producto '${data.text}' ya cargado`, 'error');
        return false;
    }

    $('#tabla-proveedor').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id_proveedor: $('#hidden_id_proveedor').val(),
            id_producto: data.id,
            codigo: data.codigo,
            producto: data.text,
            id_presentacion: data.id_presentacion,
            presentacion: data.presentacion,
            precio: data.precio,
            costo: data.cotsto
        }
    });

    $('#producto').val(null).trigger('change');

});

function alturaTablaProveedor() {
    return bstCalcularAlturaTabla(265, 575);
}

function iconosFilaProducto(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaProducto = {
    'click .eliminar': function(e, value, row, index) {
        var nombre = row.producto;
        sweetAlertConfirm({
            title: `Eliminar Producto`,
            text: `¿Eliminar "${nombre}"?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function () {
                $('#tabla-proveedor').bootstrapTable('removeByUniqueId', row.id_producto);
            }
        });
    }
}

$('#tabla-proveedor').bootstrapTable({
    url: url + '?q=ver-proveedor',
    toolbar: '#toolbar_1',
    showExport: false,
    search: true,
    showRefresh: true,
    showToggle: false,
    showColumns: false,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    showFullscreen: false,
    mobileResponsive: true,
    height: alturaTablaProveedor(),
    sortName: "pp.id_producto_proveedor",
    sortOrder: 'desc',
    trimOnSearch: false,
    uniqueId: 'id_producto',
});

$('#tabla-proveedor').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
    $('input.autonumericTabla').initNumber(); //autonumeric
});

$.extend($('#tabla-proveedor').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: `<input type="text" id="porcen-tabla" 
        class="form-control input-sm autonumericTabla"
        style="width:80px"
        data-allow-decimal-padding="false"
        data-decimal-character=","
        data-digit-group-separator="."
        data-maximum-value="100"
        value=""
        autocomplete="off">`,
    display: function(value) {
        let text = new Intl.NumberFormat(['ban', 'id']).format(value);
        if(isNaN(value)){
            text = 0;
        }
        $(this).text(text +'%');
    }
});

$('#tabla-proveedor').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    // Columnas a actualizar
    let update_row = {};
    let id = row.id_producto;
    let precio = quitaSeparadorMiles(row.precio);
    let numero = quitaSeparadorMilesFloat(row[field]);

    // Si la columna quedo en blanco
    if (!numero) {
        update_row[field] = 0;
    } else {
        // Se quitan los ceros a la izquierda
        update_row[field] = numero;
    }

    if (numero > 0 && precio > 0) {
        update_row[`p_desc_${field}`] = separadorMiles(Math.round(precio - (precio * numero) / 100));
    } else {
        update_row[`p_desc_${field}`] = separadorMiles(precio);
    }
  
    // Se actualizan los valores
    $('#tabla-proveedor').bootstrapTable('updateByUniqueId', { id, row: update_row });
});

$('#modal').on('shown.bs.modal', function (e) {
    $(this).find('input[type!="hidden"], select, textarea').filter(':first').focus();
    $('#tabla-proveedor').bootstrapTable('resetView');
    $('#tabla_detalle').bootstrapTable('resetView');
    let sucursal = $('#filtro_sucursal').select2('data')[0];
    $('#tabla_sucursales').bootstrapTable('insertRow', {
        index: 0,
        row: {
            id_sucursal: sucursal.id,
            sucursal: sucursal.text,
        }
    });
});

$('#modal').on('hidden.bs.modal', function (e) {
    $('#tabla_sucursales').bootstrapTable('removeAll');
});

$('#modal_sucursales').on('show.bs.modal', function (e) {
    //$('#tabla_sucursales').bootstrapTable('hideRow', {index:0});
});

$('#controlado').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
});


