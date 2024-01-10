var url = 'inc/administrar-ordenes-data';
var url_listados = 'inc/listados';
var url_timbrado = 'inc/timbrados-data';
var public_caja_abierta = false;

$(document).ready(function () {
    // Altura de tabla automatica
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: alturaTabla(),
                pageSize: pageSizeTabla(),
            });
        }, 100);
    });

    $('#filtro_fecha').change();
    verificarTimbradoActivo();
    verificar_estado_caja();
});

//Acciones por teclado
Mousetrap.bindGlobal('f12', (e) => { e.preventDefault(); ($('#btn_abrir_caja').is(':visible')) ? $('#btn_abrir_caja').click() : $('#btn_cerrar_caja').click(); });

// Filtros
$('#id_sucursal').select2({
    placeholder: 'Sucursal',
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

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

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

// // Rango: Mes actual
// cb(moment().startOf('month'), moment().endOf('month'));

// Rango: dia actual
cb(moment(), moment());

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
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val() });
});

function iconosFila(value, row, index) {
    let disabled = row.estado == 1 ||  row.estado == 2 ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm facturar mr-1" title="Facturar Orden" ${disabled}><i class="fas fa-print"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm anular mr-1" title="Anular Orden" ${disabled}><i class="fas fa-times"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Ver Productos"><i class="fas fa-list"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        ver_detalle(row);
    },
    'click .facturar': function(e,value, row, index){
        let numero = zerofill(row.id_orden);
        sweetAlertConfirm({
            title: `Facturar Orden`,
            text: `¿Facturar Orden N° '${numero}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=facturar',
                    type: 'post',
                    data: { id: row.id_orden},
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            var param = { id_factura: data.id_factura, imprimir: 'si', recargar: 'no' };
                            OpenWindowWithPost("imprimir-ticket", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=500,height=650", "imprimirFactura", param);
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function(jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    },
    'click .anular': function(e,value, row, index){
        let numero = zerofill(row.id_orden);
        sweetAlertConfirm({
            title: `Anular Orden`,
            text: `¿Anular Orden N° '${numero}'?`,
            closeOnConfirm: true,
            confirm: function() {
                $('#modalLabelAnular').html('Anular Orden');
                $('#formulario_anular').attr('action', 'anular_orden');
                $('#modal_anular').modal('show');
                $('#id').val(row.id_orden)
                resetForm('#formulario_anular');
            }
        });
    }
}

$("#tabla").bootstrapTable({
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
    sortName: 'id_orden',
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_orden', title: 'N° Orden', align: 'left', valign: 'middle', sortable: true, formatter: zerofill },
            { field: 'id_cliente', title: 'ID Cliente', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Nombre', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'metodo_pago', title: 'Metodo Pago', align: 'letf', valign: 'middle', sortable: true, visible: true },
            { field: 'ciudad', title: 'Ciudad', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'direccion', title: 'Dirección', align: 'letf', valign: 'middle', sortable: true},
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'cantidad', title: 'Cantidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'total_delivery', title: 'Delivery', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'contraentrega_monto', title: 'Contraentrega', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'subtotal', title: 'Sub-Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'vuelto', title: 'Vuelto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    ver_detalle(row);
});

// Detalles

function alturaTablaDetalle() {
    return bstCalcularAlturaTabla(200, 300);
}
$("#tabla_detalle").bootstrapTable({
    toolbar: '#toolbar_detalle',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
	height: alturaTablaDetalle(),
    sortName: "codigo",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_producto', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'precio', title: 'PRECIO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'total', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

function ver_detalle(row) {
    let numero = zerofill(row.id_orden);
    $('#modal_detalle').modal('show');
    $('#toolbar_detalle_text').html(`N° Orden ${numero}`);
    $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&id='+row.id_orden });
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

// Para avanzar entre los campos dando enter
$('.cantidad_moneda').keydown(function (e) {
    if (e.which === 13) { e.preventDefault(); nextFocus($(this), $('.container-fluid'), 1); }
});

function total_caja() {
    let totales = $('#formulario_caja').find('.total');
    let total_caja = Object.values(totales).reduce((acc, cv) => acc + quitaSeparadorMiles(cv.value), 0);
    $('#total_caja').val(separadorMiles(total_caja));
}

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

$('#formulario_anular').submit(function (e) {
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
                $('#modal_anular').modal('hide');
                $('#tabla').bootstrapTable('refresh');
                alertDismissJsSmall(data.mensaje, data.status, 2000)
            }else{
                alertDismissJsSmall(data.mensaje, data.status,3000)
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000)
        }
    });
});

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
            alertDismissJsSmall(data.mensaje, data.status, 2000)
        },
        error: function (xhr) {
            NProgress.done();
        }
    });
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
                alertDismissJsSmall(data.mensaje, data.status, 3000)
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

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}