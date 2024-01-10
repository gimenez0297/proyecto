var url = 'inc/rendiciones-caja-chica-data';
var url_listados = 'inc/listados';

// // CALENDARIO
// var fechaIni;
// var fechaFin;
// var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

// function cb(start, end) {
//     fechaIni = start.format('DD/MM/YYYY');
//     fechaFin = end.format('DD/MM/YYYY');
//     $('#filtro_fecha span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

//     $("#desde").val(fechaMYSQL(fechaIni));
//     $("#hasta").val(fechaMYSQL(fechaFin));
// }

// // Rango: Mes actual
// cb(moment().startOf('month'), moment().endOf('month'));

// // Rango: dia actual
// // cb(moment(), moment());

// $('#filtro_fecha').daterangepicker({
//     timePicker: false,
//     opens: "right",
//     format: 'DD/MM/YYYY',
//     locale: {
//         applyLabel: 'Aplicar',
//         cancelLabel: 'Borrar',
//         fromLabel: 'Desde',
//         toLabel: 'Hasta',
//         customRangeLabel: 'Personalizado',
//         daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi','Sa'],
//         monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"],
//         firstDay: 1
//     },
//     ranges: {
//         'Hoy': [moment(), moment()],
//         'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
//         'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
//         'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
//         'Este Mes': [moment().startOf('month'), moment().endOf('month')],
//         [meses[moment().subtract(1, 'month').format("M")]] : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
//         [meses[moment().subtract(2, 'month').format("M")]] : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
//         [meses[moment().subtract(3, 'month').format("M")]] : [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')],
//         [meses[moment().subtract(4, 'month').format("M")]] : [moment().subtract(4, 'month').startOf('month'), moment().subtract(4, 'month').endOf('month')],
//         [meses[moment().subtract(5, 'month').format("M")]] : [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')]
//     }
// }, cb);

// $('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) {
//     fechaIni = picker.startDate.format('DD/MM/YYYY');
//     fechaFin = picker.endDate.format('DD/MM/YYYY');
//     $("#desde").val(picker.startDate.format('YYYY-MM-DD')); 
//     $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));

//     $('#filtro_fecha').change();
// }).on('change', function() {
//     $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val()+'&sucursal='+$('#filtro_sucursal').val() });
// });

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
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&sucursal='+$('#filtro_sucursal').val() });
});

$('#id_banco').select2({
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
            return { q: 'bancos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_banco, text: obj.banco}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_banco').select2({
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
            return { q: 'bancos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_banco, text: obj.banco}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_cuenta').select2({
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
            return { q: 'cuentas-activas', term: params.term, page: params.page || 1, id: $('#id_banco').val() }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_cuenta, text: obj.cuenta}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

function iconosFila(value, row, index) {
    let disabled = (row.estado != 2) ? 'disabled' : '';
    let nocomprobante = (row.estado_deposito != 2) ? 'disabled' : '';
    let concomprobante = (row.estado_deposito == 2) ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalles mr-1" title="Ver detalles"><i class="fas fa-list"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir mr-1" title="Imprimir Rendicion" ${disabled}><i class="fas fa-file-invoice"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cargar-comprobante mr-1" title="Cargar Comprobante de Deposito" ${concomprobante}><i class="fas fa-file"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir-comprobante mr-1" title="Imprimir Comprobante" ${nocomprobante}><i class="fas fa-print"></i></button>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-detalles': function(e, value, row, index) {
        $('#modal_detalle').modal('show');
        $('#toolbar_titulo').html(`Gastos Movimiento N° ${row.cod_movimiento}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalles&id_caja='+row.id_caja_chica_sucursal});
    },
    'click .imprimir': function (e, value, row, index) {
        var param = {
            id_caja : row.id_caja_chica_sucursal
        }
        OpenWindowWithPost("imprimir-rendicion", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirRendicion", param);
    },
    'click .cargar-comprobante': function(e, value, row, index) {
        $('#modal_comprobante').modal('show');
        $('#toolbar_titulo_comprobante').html(`Cargar Comprobante del Movimiento N° ${row.cod_movimiento}`);
        $('#id').val(row.id_caja_chica_sucursal);
        resetForm('#formulario_comprobante');
    },
    'click .imprimir-comprobante': function (e, value, row, index) {
        var param = {
            id_comprobante : row.id_caja_chica_deposito
        }
        OpenWindowWithPost("imprimir-comprobante", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirComprobante", param);
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
    sortName: "id_caja_chica_sucursal",
    sortOrder: 'desc',
    trimOnSearch: false,
    clickToSelect: true,
    columns: [
        [
            { field: 'id_caja_chica_sucursal', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true },
            { field: 'cod_movimiento', title: 'N° Movimiento', align: 'left', valign: 'middle', sortable: true },
            { field: 'saldo', title: 'Saldo Inicial', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'sobrante', title: 'Sobrante', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'fecha_apertura', title: 'Fecha Apertura', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_rendicion', title: 'Fecha Rendicion', align: 'left', valign: 'middle', sortable: true },
            { field: 'usuario_apertura', title: 'Usuario Apertura', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'usuario_rendicion', title: 'Usuario Rendicion', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
});

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
	height: 480,
    sortName: "id_caja_chica_facturas",
    sortOrder: 'asc',
    columns: [
        [
            { field: 'id_caja_chica_facturas', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            //{ field: 'fraccionado', title: '', align: 'center', valign: 'middle', sortable: true, formatter: fraccionado, footerFormatter: bstFooterTextTotal },
            { field: 'nro_gasto', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_emision', title: 'Fecha Emisión', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Razon Social', align: 'left', valign: 'middle', sortable: true },
            { field: 'timbrado', title: 'Timbrado', align: 'left', valign: 'middle', sortable: true },
            { field: 'documento', title: 'Documento', align: 'left', valign: 'middle', sortable: true },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'montomonto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, visible: false ,formatter: separadorMiles },
            { field: 'tipo_gasto', title: 'Tipo Gasto', align: 'left', valign: 'middle', sortable: true },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true },
        ]
    ]
});

$('#formulario_comprobante').submit(function(e) {
    e.preventDefault();

    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();

    $.ajax({
        url: url + '?q=cargar-comprobante',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function(){
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();
            if (data.status == 'ok') {
                $('#modal_comprobante').modal('hide');
                alertDismissJS(data.mensaje, data.status, function(){
                    var param = { id_comprobante: data.id_comprobante};
                    OpenWindowWithPost("imprimir-comprobante", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirComprobante", param);
                });
                $('tabla').bootstrapTable('refresh');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function (xhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
        }
    });

});

function resetForm(form) {
    $(form).trigger('reset');
    //$(form).find('input[type="checkbox"]').trigger('change');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}
