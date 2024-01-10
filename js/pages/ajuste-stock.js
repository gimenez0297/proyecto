var url = 'inc/ajuste-stock-data';
var url_lotes = 'inc/lotes-data';
var url_listados = 'inc/listados';

obtenerNumero();

$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function (e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function () {
            $("#tabla").bootstrapTable('refreshOptions', {
                height: alturaTabla(),
                pageSize: pageSizeTabla(),
            });
        }, 100);
    });

    // Mostrar datos en la tabla
    $('#filtro_sucursal').trigger('change');
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

// Rango: Mes actual
cb(moment().startOf('month'), moment().endOf('month'));

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
}).on('change', function () {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$("#desde").val()}&hasta=${$("#hasta").val()}` })
});


// Filtros
$('#filtro_sucursal').select2({
    placeholder: 'Sucursal',
    width: '200px',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function () {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$("#desde").val()}&hasta=${$("#hasta").val()}` })
});

noSubmitForm('#formulario');

$('#sucursal').select2({
    dropdownParent: $('#modal'),
    placeholder: 'Sucursal',
    width: 'style',
    allowClear: false,
    selectOnClose: true,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'sucursales', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
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
};

// Productos
// BUSQUEDA DE PRODUCTOS
$('#producto').select2({
    dropdownParent: $('#modal'),
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
                        fraccionado: obj.fraccionado,
                        fraccion: obj.fraccion,
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
    $('#lote').val(null).trigger('change');
    var data = $('#producto').select2('data')[0] || {};
    $('#codigo').val(data.codigo || '');
    $('#precio').val(separadorMiles(data.precio) || '');
        
         if (data.fraccion == 1){

            $("#fraccionado").attr('disabled', false);
        }
         if (data.fraccion == 0){

            $("#fraccionado").attr('disabled', true);
            $('#fraccionado').val('0'); 
            $('#fraccionado').trigger('change.fraccionado'); 

        }



    setTimeout(() => {
        if (data.id) {
            $('#lote').focus();
        } else {
            $('#codigo').focus().select();
        }
    }, 1);
});


$('#proveedor').select2({
    dropdownParent: $('#modal_lote'),
    placeholder: 'Proveedor',
    width: 'style',
    allowClear: false,
    selectOnClose: true,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'proveedores_modal', producto: $('#producto').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_proveedor, text: obj.proveedor, costo: obj.costo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    var data = $(this).select2('data')[0];
    var costo = 0;
    if(data){
        costo = $("#proveedor").select2('data')[0].costo;
    }
    $("#costo").val(separadorMiles(costo));
    
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
                    // agregarProducto(data);
                    // $('#codigo').val('').focus();
                    $('#producto').select2('trigger', 'select', {
                        data: { id: data.id_producto, text: data.producto, fraccion: data.fraccion, codigo: data.codigo, precio: data.precio }
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
                $('#tabla_detalles').bootstrapTable('removeByUniqueId', row.id);
            }
        });
    }
}

//aqui
$("#tabla_detalles").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 250,
    sortName: "codigo",
    sortOrder: 'asc',
    uniqueId: 'id',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_lote', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_movimiento', title: 'Movimiento', align: 'left', valign: 'middle', sortable: true, visible: true, formatter: formatterMovimiento },
            { field: 'motivo', title: 'Motivo', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'fraccionado', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProducto, formatter: iconosFilaProducto, width: 100 }
        ]
    ]
});

$('#agregar-producto').on('click', function () {
    var sucursal = $('#sucursal').select2('data')[0];
    var producto = $('#producto').select2('data')[0];
    var movimiento = $('#formulario').find('[name="positivo"]:checked').val();
    var lote = $('#lote').select2('data')[0];

    if (!sucursal) {
        alertDismissJS('Debe seleccionar una sucursal', 'error');
    } else if (!producto) {
        alertDismissJS('Debe seleccionar un producto', 'error');
    } else if (!lote) {
        alertDismissJS('Debe seleccionar un lote', 'error');
    } else {
        var producto = {
            id_producto: producto.id,
            codigo: producto.codigo,
            producto: producto.text,
            id_lote: lote.id,
            lote: lote.text,
            id_movimiento: movimiento,
            motivo: motivo,
        }
        agregarProducto(producto);
        $('#producto').val(null).trigger('change');
        $('#lote').val(null).trigger('change');
        $('#motivo').val('');
    }
});

function formatterMovimiento(value, row, index, field) {
    if(value == 1){
        return 'POSITIVO';
    }else if(value == 2){
        return 'NEGATIVO';
    }else if(value == 3){
        return 'REEMPLAZO';
    }
}

$('#agregar_lote').click(function () {
    if (!$('#producto').val()) {
        alertDismissJS('Debe seleccionar un producto', 'error', () => $('#producto').focus());
    } else {
        let id_producto = $('#producto').val();

        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: url_lotes,
            cache: false,
            data: { q: 'obtener-lote', id_producto },
            beforeSend: function () { NProgress.start(); },
            success: function (data, status, xhr) {
                NProgress.done();
                if (data.status == 'ok') {
                    resetForm('#formulario_lote');
                    $('#modal_lote').modal('show');
                    $('#descripcion_lote').prop('readonly', false);
                    if (data.lote) {
                        $('#descripcion_lote').val(data.lote).prop('readonly', true);
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
});

$('#modal_lote').on('shown.bs.modal', function (e) {
    $(this).find('input[type!="hidden"], select, textarea').filter(':first').focus();
});

$('#canje').on('change', function (e) {
    if ($(this).is(':checked')) {
        $('#vencimiento').parent().removeClass('col-md-12').addClass('col-md-6');
        $('#vencimiento_canje').val('').parent().show();
        setTimeout(() => $('#vencimiento_canje').focus());
    } else {
        $('#vencimiento').parent().removeClass('col-md-6').addClass('col-md-12');
        $('#vencimiento_canje').val('').parent().hide();
    }
});

// GUARDAR LOTE
$("#formulario_lote").submit(function (e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    if($("#costo").val() == 0) {
        setTimeout( ()=> alertDismissJS('El costo debe ser mayor a cero(0).' , 'error'),400);
        return false;
    }

    var data = $(this).serializeArray();
    data.push({ name: 'id_producto', value: $('#producto').val() });

    $.ajax({
        url: url_lotes + '?q=cargar',
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
                alertDismissJS(data.mensaje, data.status, function () {
                    let obj = data.data;
                    $('#lote').select2('trigger', 'select', {
                        data: { id: obj.id, text: obj.lote, vencimiento: obj.vencimiento }
                    });
                    $('#modal_lote').modal('hide');
                    $('#cantidad').focus();
                });
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('#lote_automatico').change(function () {
    $element = $('#lote_prefijo');
    if ($(this).is(':checked')) {
        $element.val('').parent().show();
        setTimeout(() => $element.focus());
    } else {
        $element.val('').parent().hide();
    }
});


function agregarProducto(data) {
    var cantidad = quitaSeparadorMiles($('#cantidad').val());
    var fraccionado = quitaSeparadorMiles($('#fraccionado').val());
    var motivo = $('#motivo').val();

    $('#tabla_detalles').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id: new Date().getTime(),
            id_producto: data.id_producto,
            codigo: data.codigo,
            producto: data.producto,
            id_lote: data.id_lote,
            lote: data.lote,
            id_movimiento: data.id_movimiento,
            cantidad: cantidad,
            fraccionado: fraccionado,
            motivo: motivo,
            //total_precio: parseInt(data.precio) * cantidad,
        }
    });

    $('#cantidad').val(0);
    $('#fraccionado').val(0);

    disabeldSucursal();
}

function disabeldSucursal() {
    let tableData = $('#tabla_detalles').bootstrapTable('getData');
    $('#sucursal').prop('disabled', (tableData.length > 0));
}

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    // Pendiente y Aprobado, los demas estado disabled
    let disabled = (row.estado == 0) ? '' : 'disabled';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm anular mr-1" title="Anular" ${disabled}><i class="fas fa-times"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm aprobar mr-1" title="Aprobar" ${disabled}><i class="fas fa-check"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver" title="Ver detalle"><i class="fas fa-list"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .ver': function (e, value, row, index) {
        ver_ajuste(row);
    }, 'click .aprobar': function (e, value, row, index) {
        let sigteEstado = "Procesado";
        let estado = 1;
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function () {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=aprobar',
                    type: 'post',
                    data: { id: row.id_ajuste, estado },
                    beforeSend: function () {
                        NProgress.start();
                    },
                    success: function (data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function (jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    }, 'click .anular': function (e, value, row, index) {
        let sigteEstado = "Anulado";
        let estado = 2 ;
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function () {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=anular',
                    type: 'post',
                    data: { id: row.id_ajuste, estado },
                    beforeSend: function () {
                        NProgress.start();
                    },
                    success: function (data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function (jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    }
}

$("#tabla").bootstrapTable({
    // url: url + '?q=ver',
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
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: "numero",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_ajuste', title: 'ID Ajuste', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'descripcion', title: 'Descripción', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: true },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila, width: 150 }
        ]
    ]
}).on('dbl-click-row.bs.table', function (e, row, $element) {
    ver_ajuste(row);
});

function ver_ajuste(row) {
    $('#modal_detalles').modal('show');
    $('#toolbar_detalles_text').html(`Ajuste de stock N° ${row.numero}`);
    if(row.estado == 0){
         $('#tabla_ajustes').bootstrapTable('showColumn', 'cantidad_actual');
         $('#tabla_ajustes').bootstrapTable('showColumn', 'fraccionado_actual');
         $('#tabla_ajustes').bootstrapTable('showColumn', 'diferencia_actual');
         $('#tabla_ajustes').bootstrapTable('showColumn', 'diferencia_actual_fraccionado');

         $('#tabla_ajustes').bootstrapTable('hideColumn', 'cantidad_anterior');
         $('#tabla_ajustes').bootstrapTable('hideColumn', 'fraccionado_anterior');
         $('#tabla_ajustes').bootstrapTable('hideColumn', 'diferencia_anterior');
         $('#tabla_ajustes').bootstrapTable('hideColumn', 'diferencia_anterior_fraccionado');
    }else{
        $('#tabla_ajustes').bootstrapTable('hideColumn', 'cantidad_actual');
        $('#tabla_ajustes').bootstrapTable('hideColumn', 'fraccionado_actual');
        $('#tabla_ajustes').bootstrapTable('hideColumn', 'diferencia_actual');
        $('#tabla_ajustes').bootstrapTable('hideColumn', 'diferencia_actual_fraccionado');

        $('#tabla_ajustes').bootstrapTable('showColumn', 'cantidad_anterior');
        $('#tabla_ajustes').bootstrapTable('showColumn', 'fraccionado_anterior');
        $('#tabla_ajustes').bootstrapTable('showColumn', 'diferencia_anterior');
        $('#tabla_ajustes').bootstrapTable('showColumn', 'diferencia_anterior_fraccionado');
    }
    $('#tabla_ajustes').bootstrapTable('refresh', { url: url + '?q=ver_detalles&id=' + row.id_ajuste });
}

$('#agregar').click(function () {
    $('#modalLabel').html('Agregar Ajuste');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    $('#sucursal').prop('disabled', false);
    $('#tabla_detalles').bootstrapTable('removeAll');
    resetForm('#formulario');
    $('#positivo').prop('checked', true).trigger('change');
});

$('#modal').on('shown.bs.modal', function (e) {
    $('#formulario').find('input[type!="hidden"], select, textarea').filter(':first').focus();
    $('#tabla_detalles').bootstrapTable('resetView');
});


function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('input[type="checkbox"]').trigger('change');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
    obtenerNumero();
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function (e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    var data = $(this).serializeArray();
    data.push({ name: 'sucursal', value: $('#sucursal').val() });
    data.push({ name: 'detalles', value: JSON.stringify($('#tabla_detalles').bootstrapTable('getData')) });

    $.ajax({
        url: url + '?q=' + $(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function () {
            NProgress.start();
        },
        success: function (data, textStatus, jQxhr) {
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// ELIMINAR
$('#eliminar').click(function () {
    var id = $("#hidden_id").val();
    var nombre = $("#descripcion").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Ajuste: ${nombre}?`,
        closeOnConfirm: false,
        confirm: function () {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, nombre },
                beforeSend: function () {
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

// Acciones por teclado
$(window).on('keydown', function (event) {
    switch (event.which) {
        // F1 Agregar, si el modal esta visible focus al select de lotes
        case 112:
            event.preventDefault();
            if ($('#modal').is(':visible')) {
                $('#codigo').focus();
            } else {
                $('#agregar').click();
            }
            break;
        // F2 focus a codigo
        case 113:
            event.preventDefault();
            $('#producto').focus();
            break;
        // F3 focus a producto
        case 114:
            event.preventDefault();
            $('#lote').focus();
            break;
        // F4 focus a producto
        case 113:
            event.preventDefault();
            $('#imprimir').click();
            break;
    }
});

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Ver Ajuste');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#hidden_id").val(row.id_ajuste);
    $("#sucursal").append(new Option(row.sucursal, row.id_sucursal, true, true)).trigger('change').prop('disabled', true);
    $("#descripcion").val(row.descripcion);
    $(".detalles").hide();
    $('#positivo').prop('checked', true).trigger('change');

    $('#tabla_detalles').bootstrapTable('refresh', { url: `${url}?q=ver_detalles&id=${row.id_ajuste}` });
}

$('#lote').select2({
    dropdownParent: $('#modal'),
    placeholder: 'Seleccionar',
    width: 'resolve',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'lotes', id_producto: $('#producto').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_lote, text: obj.lote, vencimiento: obj.vencimiento }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function () {
    var data = $(this).select2('data')[0] || {};
    setTimeout(() => {
        if (data.id) {
            $('#cantidad').focus();
        }
    }, 1);
});

$('#movimiento').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    tags: true,
    maximumInputLength: 4,
    //minimumResultsForSearch: Infinity,
});

$("#tabla_ajustes").bootstrapTable({
    toolbar: '#toolbar_detalles',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 450,
    sortName: "codigo",
    sortOrder: 'asc',
    uniqueId: 'id',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { title: 'Producto', align: 'center', valign: 'middle', sortable: false, colspan: 8 },
            { title: 'Cantidad Entero', align: 'center', valign: 'middle', sortable: false, colspan: 6 },
            { title: 'Cantidad Fraccionado', align: 'center', valign: 'middle', sortable: false, colspan: 6 },
        ],
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_producto', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_movimiento', title: 'Movimiento', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_lote', title: 'ID Lote', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'Código', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'movimiento', title: 'Movimiento', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'cantidad', title: 'Ajuste', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'cantidad_anterior', title: 'Stock Anterior', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'cantidad_actual', title: 'Stock Actual', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'final_entero', title: 'Stock Final', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'diferencia_actual', title: 'Diferencia' , align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'diferencia_anterior', title: 'Diferencia', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'fraccionado', title: 'Ajuste', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'fraccionado_anterior', title: 'Stock Anterior', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'fraccionado_actual', title: 'Stock Actual', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'final_fraccionado', title: 'Stock Final', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'diferencia_actual_fraccionado', title: 'Diferencia', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'diferencia_anterior_fraccionado', title: 'Diferencia', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

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

$('#imprimir').click(function () {
    var id_sucursal      = $('#filtro_sucursal').val();
    var desde            = $('#desde').val();
    var hasta            = $('#hasta').val();
    var id                 =$('#filtro').val();

    var param = {id_sucursal : id_sucursal, desde: desde, hasta:hasta};
    OpenWindowWithPost("imprimir-ajuste-stock", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirStockInsumo", param);
});

$('#filtro_fecha').change();
