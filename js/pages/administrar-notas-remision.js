var url = 'inc/administrar-notas-remision-data';
var public_estado;

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

});

// Acciones por teclado
var MousetrapModalDetalle = new Mousetrap(document.querySelector('#modal_detalle'));
var MousetrapModalDetalleInsumo = new Mousetrap(document.querySelector('#modal_detalle_insumo'));

MousetrapTableNavigationCell('#tabla_detalle', MousetrapModalDetalle);
MousetrapTableNavigationCell('#tabla_insumos', MousetrapModalDetalleInsumo);
$('.modal').on('shown.bs.modal', (e) => Mousetrap.pause());
$('.modal').on('hidden.bs.modal', (e) => Mousetrap.unpause());


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
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val() });
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    // Anulado
    let disabled = row.estado == 2 || row.estado == 3 || row.estado == 4 ? 'disabled' : '';

    return [
        '<div class = "d-flex justify-content-center">',
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm anular mr-1" title="Anular" ${disabled}><i class="fas fa-times"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Ver Productos"><i class="fas fa-list"></i></button>',
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>`,
        '</div>'
    ].join('');
}

window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        if(row.tipo_remision == 0){
            ver_detalle(row);
        }else{
            ver_detalle_insumos(row);
        }
    },
    'click .anular': function(e, value, row, index) {
        let numero = row.numero;
        sweetAlertConfirm({
            title: `Anular Nota de Remisión`,
            text: `¿Anular Nota de Remisión N° '${numero}'?`,
            closeOnConfirm: false,
            confirmButtonText: 'Anular',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=anular',
                    type: 'post',
                    data: { id: row.id_nota_remision },
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
        var param = { id_nota_remision: row.id_nota_remision, imprimir : 'no', recargar: 'no' };
        OpenWindowWithPost("imprimir-nota-remision", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirNotaRemision", param);
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
    sortName: 'fecha_emision',
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_nota_remision', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'motivo', title: 'Motivo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'sucursal_origen', title: 'Sucursal Origen', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'sucursal_destino', title: 'Sucursal Destino', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'razon_social_destino', title: 'Destinatario', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'observacion', title: 'Observación', align: 'letf', valign: 'middle', sortable: true, visible: false, cellStyle: bstTruncarColumna },
            { field: 'usuario', title: 'Usuario Emisor', ali: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_emision', title: 'Fecha de Emision', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 200 },
            { field: 'usuario_recepcion', title: 'Usuario Recepcion', ali: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_recepcion', title: 'Fecha de Recepcion', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 200 },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 150 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 150 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    if(row.tipo == 0){
        ver_detalle(row);
    }else{
        ver_detalle_insumos(row);
    }
});

// Detalles
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
	height: 440,
    sortName: "codigo",
    sortOrder: 'asc',
    uniqueId: 'id_nota_remision_producto',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_producto', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'numero', title: 'N° Solicitud Deposito', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'cantidad_recibida', title: 'RECEPCIONADO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width: 150, editable: { type: 'text' } },
            { field: 'observacion', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true, width: 250, editable: { type: 'text', tpl: '<input type="text" style="width:240px">' } }
        ]
    ]
});

$.extend($('#tabla_detalle').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_detalle').on('post-body.bs.table', function(data) {
    if (public_estado == 1) {
        setTimeout(() => $('#tabla_detalle').find('.editable').editable('enable'));
    } else {
        setTimeout(() => $('#tabla_detalle').find('.editable').editable('disable'));
    }
});

$('#tabla_detalle').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
});

$('#tabla_detalle').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    if (field == 'cantidad_recibida' && !row.cantidad_recibida) {
        $('#tabla_detalle').bootstrapTable('updateByUniqueId', { id: row.id_nota_remision_producto, row: { cantidad_recibida: 0 } });
    }
});



$('#btn_guardar').on('click', function(e){
    e.preventDefault();
    sweetAlertConfirm({
        text: `¿Guardar Recepción?`,
        confirmButtonText: 'Guardar',
        confirmButtonColor: 'var(--primary)',
        closeOnConfirm: false,
        confirm: function() {
            $('#formulario').submit();
        }
    })
});

  $('#formulario').submit(function(e) {
                e.preventDefault();
                if ($(this).valid() === false) return false;
            
                var data = $(this).serializeArray();
                data.push({ name: 'detalle', value: JSON.stringify($('#tabla_detalle').bootstrapTable('getData')) });
            
                $.ajax({
                    url: url + '?q=recepcionar',
                    dataType: 'json',
                    type: 'post',
                    contentType: 'application/x-www-form-urlencoded',
                    data: data,
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr){
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#modal_detalle').modal('hide');
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function(jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            });


// $('#formulario').submit(function(e) {
//     e.preventDefault();
//     if ($(this).valid() === false) return false;

//     var data = $(this).serializeArray();
//     data.push({ name: 'detalle', value: JSON.stringify($('#tabla_detalle').bootstrapTable('getData')) });

//     $.ajax({
//         url: url + '?q=recepcionar',
//         dataType: 'json',
//         type: 'post',
//         contentType: 'application/x-www-form-urlencoded',
//         data: data,
//         beforeSend: function() {
//             NProgress.start();
//         },
//         success: function(data, textStatus, jQxhr){
//             NProgress.done();
//             alertDismissJS(data.mensaje, data.status);
//             if (data.status == 'ok') {
//                 $('#modal_detalle').modal('hide');
//                 $('#tabla').bootstrapTable('refresh');
//             }
//         },
//         error: function(jqXhr, textStatus, errorThrown) {
//             NProgress.done();
//             alertDismissJS($(jqXhr.responseText).text().trim(), "error");
//         }
//     });
// });

function ver_detalle(row) {
    $('#modal_detalle').modal('show');
    $('#toolbar_detalle_text').html(`Nota de Remisión N° ${row.numero}`);

    if (row.estado == 1) {
        $('#btn_guardar').show();
        $('#observacion').prop('readonly', false);
    } else {
        $('#btn_guardar').hide();
        $('#observacion').prop('readonly', true);
    }

    public_estado = row.estado;
    $('#id').val(row.id_nota_remision);
    $('#observacion').val(row.observacion);

    $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&id='+row.id_nota_remision });
}

// Detalles
$("#tabla_insumos").bootstrapTable({
    toolbar: '#toolbar_detalle_text_insumo',
    showExport: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 440,
    sortName: "codigo",
    sortOrder: 'asc',
    uniqueId: 'id_nota_remision_insumo',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_producto_insumo', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'cantidad_recibida', title: 'RECEPCIONADO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width: 150, editable: { type: 'text' } },
            { field: 'observacion', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true, width: 250, editable: { type: 'text', tpl: '<input type="text" style="width:240px">' } }
        ]
    ]
});

$.extend($('#tabla_insumos').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_insumos').on('post-body.bs.table', function(data) {
    if (public_estado == 1) {
        setTimeout(() => $('#tabla_insumos').find('.editable').editable('enable'));
    } else {
        setTimeout(() => $('#tabla_insumos').find('.editable').editable('disable'));
    }
});

$('#tabla_insumos').on('editable-shown.bs.table', (e, field, row, $el, editable) => {
    editable.input.$input[0].value = (isNaN(parseInt(row[field])) || row[field] == 0) ? '' : row[field];
});

$('#tabla_insumos').on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    if (field == 'cantidad_recibida' && !row.cantidad_recibida) {
        $('#tabla_insumos').bootstrapTable('updateByUniqueId', { id: row.id_nota_remision_insumo, row: { cantidad_recibida: 0 } });
    }
});

$('#btn_guardar_insumo').on('click', () => $('#formulario_insumos').submit());
$('#formulario_insumos').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    var data = $(this).serializeArray();
    data.push({ name: 'detalle', value: JSON.stringify($('#tabla_insumos').bootstrapTable('getData')) });

    $.ajax({
        url: url + '?q=recepcionar_insumo',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr){
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal_detalle_insumo').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

function ver_detalle_insumos(row) {
    $('#modal_detalle_insumo').modal('show');
    $('#toolbar_detalle_text_insumo').html(`Nota de Remisión N° ${row.numero}`);

    if (row.estado == 1) {
        $('#btn_guardar_insumo').show();
        $('#observacion_insumo').prop('readonly', false);
    } else {
        $('#btn_guardar_insumo').hide();
        $('#observacion_insumo').prop('readonly', true);
    }

    public_estado = row.estado;
    $('#id_remision_insumo').val(row.id_nota_remision);
    $('#observacion_insumo').val(row.observacion);

    $('#tabla_insumos').bootstrapTable('refresh', { url: url+'?q=ver_detalle_insumo&id='+row.id_nota_remision });
}