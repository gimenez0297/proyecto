var url = 'inc/notas-credito-data';
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
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` })
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    // Anulado o utilizado
    let disabled = (row.estado == 2 || row.estado == 1) ? 'disabled' : '';
    
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm anular mr-1" title="Anular" ${disabled}><i class="fas fa-times"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>`,
    ].join('');
}

window.accionesFila = {
    'click .anular': function(e, value, row, index) {
        let id = row.id_nota_credito;
        let title = 'Anular Nota De Crédito';
        let text = `¿Anular la nota de crédito N° ${row.numero}?`;

        sweetAlertConfirm({
            title,
            text,
            confirmButtonText: 'Anular',
            confirmButtonColor: 'var(--danger)',
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=anular',
                    type: 'post',
                    data: { id },
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
        var param = { id: row.id_nota_credito, imprimir : 'si', recargar: 'no' };
        OpenWindowWithPost("imprimir-nota-credito", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirNotaRemision", param);
    }
}

$('#tabla').bootstrapTable({
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
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: 'fecha',
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_nota_credito', title: 'ID Nota De Crédito', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'numero', title: 'N° Nota Crédito', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'nro_factura_origen', title: 'N° Factura', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'nro_factura_destino', title: 'N° Factura Destino', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C. / C.I.', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'razon_social', title: 'Razón Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 150 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$('#formulario').submit(function(e) {
    e.preventDefault();
    let tabla_principal = $('#tabla_principal').bootstrapTable('getSelections');
    let tabla_detalle = $('#tabla_detalle').bootstrapTable('getSelections');
    //let data = $(this).serializeArray();
    let data = [];

    if ($(this).valid() === false) return false;

    if (tabla_principal.length == 0) {
        alertDismissJS('Ninguna factura seleccionada. Favor verifique.', 'error');
        return false;
    }
    if (tabla_detalle.length == 0) {
        alertDismissJS('Ningún producto seleccionado. Favor verifique.', 'error');
        return false;
    }

    sweetAlertConfirm({
        title: `¿Guardar Nota De Crédito?`,
        confirmButtonText: 'Guardar',
        confirm: () => {
            let id = tabla_principal[0].id_factura;
            data.push({ name: 'id', value: id });
            data.push({ name: 'detalle', value: JSON.stringify(tabla_detalle) });

            $.ajax({
                url: url + '?q='+$(this).attr('action'),
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
                        var param = { id: data.id, imprimir : 'si', recargar: 'no' };
                        OpenWindowWithPost("imprimir-nota-credito", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirNotaRemision", param);
                        $('#modal').modal('hide');
                        $('#tabla').bootstrapTable('refresh');
                    } else {
                        alertDismissJS(data.mensaje, data.status);
                    }
                },
                error: function(jqXhr, textStatus, errorThrown) {
                    NProgress.done();
                    alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
                }
            });
        }
    });

});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
    }
});

function resetForm(form) {
    $(form).trigger('reset');
    resetValidateForm(form);
}

