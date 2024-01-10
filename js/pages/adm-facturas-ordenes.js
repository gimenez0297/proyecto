var url = 'inc/adm-facturas-ordenes-data';
var url_listados = 'inc/listados';

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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    // Procesado parcial o Total
    let disabled = row.estado == 2 ? 'disabled' : '';

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm cambiar-estado mr-1" title="Anular" ${disabled}><i class="fas fa-times"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-productos mr-1" title="Ver Productos"><i class="fas fa-list"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm ver-detalle-dos mr-1" title="Ver Cobros"><i class="fas fa-hand-holding-usd"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>`,
    ].join('');
}

window.accionesFila = {
    'click .ver-productos': function(e, value, row, index) {
        $('#modal_detalle').modal('show');
        $('#toolbar_titulo').html(`Factura N° ${row.numero}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_productos&id_factura='+row.id_factura });
    },
    'click .ver-detalle-dos': function(e, value, row, index) {
        $('#modal_detalle_dos').modal('show');
        $('#toolbar_detalle_dos_titulo').html(`Factura N° ${row.numero}`);
        $('#tabla_detalle_dos').bootstrapTable('refresh', { url: url+'?q=ver_cobros&id='+row.id_factura });

        public_id_factura = row.id_factura;
        if (row.editar_cobros == 0) {
            public_editar_cobros = false;
            $('#btn_guardar_detalle_dos').addClass('d-none');
        } else {
            public_editar_cobros = true;
            $('#btn_guardar_detalle_dos').removeClass('d-none');
        }
    },
    'click .cambiar-estado': function(e, value, row, index) {
        sweetAlertConfirm({
            title: `Anular Factura`,
            text: `¿Anular la factura N° ${row.numero}?`,
            confirmButtonText: 'Anular',
            confirmButtonColor: 'var(--danger)',
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=anular',
                    type: 'post',
                    data: { id: row.id_factura },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
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
    'click .imprimir': function (e, value, row, index) {
        var param = { id_factura: row.id_factura, imprimir : 'no', recargar: 'no' };
        OpenWindowWithPost("imprimir-ticket", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=500,height=650", "imprimirFactura", param);
        $('#tabla').bootstrapTable('refresh');
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
    classes: 'table table-hover',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: "id_factura",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_factura', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_orden', title: 'N° Orden', align: 'left', valign: 'middle', sortable: true, formatter: zerofill },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Nombre', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 200 },
            { field: 'nombre_apellido', title: 'Funcionario', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 200,  visible: false },
            { field: 'ci', title: 'C.I', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'condicion', title: 'Condición', align: 'letf', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_venta', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'total_venta', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,visible: esAdmin(), switchable: esAdmin() },
            { field: 'fecha', title: 'Registro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'voucher', title: 'Voucher', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, visible: true },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila }
        ]
    ]
});

// Detalle
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
    sortName: "codigo",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_producto', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible:true, switchable: true },
            { field: 'precio', title: 'PRECIO X UNID.', align: 'right', valign: 'middle', sortable: true, visible: false ,formatter: separadorMiles, visible: true, switchable: true},
            { field: 'subtotal', title: 'SUB TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: true, switchable:true },
            { field: 'total_venta', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

// Cobros
$("#tabla_detalle_dos").bootstrapTable({
    toolbar: '#toolbar_detalle_dos',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
	height: 480,
    sortName: "id_cobro",
    sortOrder: 'asc',
    uniqueId: 'id_cobro',
    showFooter: esAdmin(),
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_cobro', title: 'ID COBRO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'metodo_pago', title: 'MÉTODO DE PAGO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal },
            { field: 'detalles', title: 'Detalles', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 150, editable: { type: 'text' } },
            { field: 'numero_recibo', title: 'N° RECIBO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: zerofill },
            { field: 'monto', title: 'MONTO', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: esAdmin(), switchable: esAdmin() },
        ]
    ]
}).on('click-cell.bs.table', function (e, field, value, row, $element) {
    setTimeout(() => $element.find('a').editable('toggle'), 10);
});

$.extend($('#tabla_detalle_dos').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px">'
});

$('#tabla_detalle_dos').on('post-body.bs.table', function(e, data) {
    // Solo se puede editar los métodos de pago. Se bloquean el editable si no es método de pago
    $.each(data, (index, value) => {
        if (public_editar_cobros == false && esAdmin() === false) {
            setTimeout(() => $('#tabla_detalle_dos').find(`tr[data-index=${index}]`).find('.editable').editable('disable'));
        }
    });
});

$('#btn_guardar_detalle_dos').on('click', function(e) {
    let $tabla = $('#tabla_detalle_dos');
    let data = JSON.stringify($tabla.bootstrapTable('getData'));

    $.ajax({
        url: url + '?q=editar_cobros',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: { id_factura: public_id_factura, data },
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal_detalle_dos').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('#filtro_fecha').change();