var url = 'inc/recepciones-compras-data';
var url_listados = 'inc/listados';
var public_id_recepcion_compra;
var public_id_produto;

function iconosFila(value, row, index) {
    // Anulado
    let disabled = (row.estado == 2) ? 'disabled' : '';

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm cambiar-estado mr-1" title="Anular" ${disabled}><i class="fas fa-times"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-productos mr-1" title="Ver Productos"><i class="fas fa-box"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm ver-archivos mr-1" title="Ver Archivos"><i class="fas fa-file-invoice"></i></button>',
    ].join('');
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
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val()+'&sucursal='+$('#filtro_sucursal').val() });
});

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
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val()+'&sucursal='+$('#filtro_sucursal').val() });
});

window.accionesFila = {
    'click .ver-productos': function(e, value, row, index) {
        $('#modal_productos').modal('show');
        $('#toolbar_productos_text').html(`Recepción De Compra N° ${row.numero}`);
        $('#tabla_productos').bootstrapTable('refresh', { url: url+'?q=ver_productos&id_recepcion_compra='+row.id_recepcion_compra });
    },
    'click .ver-archivos': function(e, value, row, index) {
        public_id_recepcion_compra = row.id_recepcion_compra;

        $('#modal_archivos').modal('show');
        $('#toolbar_archivos_text').html(`Recepción De Compra N° ${row.numero}`);
        $('#tabla_archivos').bootstrapTable('refresh', { url: url+'?q=ver_archivos&id_recepcion_compra='+row.id_recepcion_compra });
    },
    'click .cambiar-estado': function(e, value, row, index) {
        let id = row.id_recepcion_compra;
        let estado = 2;
        sweetAlertConfirm({
            title: `Anular Recepción`,
            text: `¿Anular la recepción N° '${id}'?`,
            closeOnConfirm: false,
            confirmButtonText: 'Anular',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id, estado },
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
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_recepcion_compra",
    sortOrder: 'desc',
    trimOnSearch: false,
    clickToSelect: true,
    columns: [
        [
            { field: 'id_recepcion_compra', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'numero_gasto', title: 'N° Gasto', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'proveedor', title: 'Proveedor / Razón Social', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'total_costo', title: 'Total', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'condicion_str', title: 'Condición', align: 'left', valign: 'middle', sortable: true },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
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
        }, 100);
    });
});

        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir" title="Imprimir"><i class="fa fa-barcode"></i></button>'

function iconosFilaProducto(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir" title="Imprimir"><i class="fa fa-barcode"></i></button>'
    ].join('');
}

window.accionesFilaProducto = {
    'click .imprimir': function(e, value, row, index) {
        $('#modal_imprimir').modal('show');
        public_id_produto = row.id_producto;
        $('#cantidad').val(row.cantidad);
        $("#id_lote").val(null).trigger('change');
    }
}

// Productos
$("#tabla_productos").bootstrapTable({
    toolbar: '#toolbar_productos',
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
    uniqueId: 'id_producto',
    columns: [
        [
            { field: 'id_producto', title: 'ID PRODUCTO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_orden_compra', title: 'ID ORDEN COMPRA', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero', title: 'N° ORDEN COMPRA', align: 'left', valign: 'middle', sortable: true, visible: true, footerFormatter: bstFooterTextTotal },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'lote', title: 'LOTE', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
            { field: 'costo', title: 'COSTO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
            { field: 'total', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaProducto, formatter: iconosFilaProducto, width: 120 }
        ]
    ]
});

// Imprimir
$('#modal_imprimir').on('shown.bs.modal', function (e) {
    $('#formulario input[type!="hidden"]:first').focus();
});

$('#formulario_imprimir').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    $('#modal_imprimir').modal('hide');
    var param = { 'id_producto': public_id_produto, cantidad: $('#cantidad').val(),'id_lote': $('#id_lote').val(),'imprimir': 'si', 'recargar': 'no' };
    OpenWindowWithPost("imprimir-etiqueta", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=500,height=400", "imprimirEtiqueta", param);
});

// Archivos
$("#tabla_archivos").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    sortName: "archivo",
    sortOrder: 'asc',
    showCustomView: true,
    customView: customViewFormatter,
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true },
            { field: 'archivo', title: 'Blob', align: 'left', valign: 'middle', sortable: true },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true },
        ]
    ]
});

$('#tabla_archivos').on('custom-view-post-body.bs.table', function() {
    $('.btn-preview').click(function() {
        let id = $(this).val();
        sweetAlertConfirm({
            title: `¿Eliminar el archivo?`,
            text: 'Esta acción no se puede revertir',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_archivo',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_archivos').bootstrapTable('refresh');
                        } else {
                            alertDismissJS(data.mensaje, data.status);
                        }
                    },
                    error: function(jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    });
});

function customViewFormatter(data) {
    let template = $('#template').html();
    let sin_archivos = '<div class="col-12 text-center pt-4">Ningún archivo seleccionado</div>';
    let border = (data.length > 0) ? '' : 'border';
    let view = (data.length > 0) ? '' : sin_archivos;

    $.each(data, function (i, row) {
        view += template.replace('%PREVIEW%', `${row.archivo}?v=${getRandomInt(100000, 999999)}`).
            replace('%FANCY%', `${row.archivo}?v=${getRandomInt(100000, 999999)}`).
            replace('%ID%', row.id);
    });

    return `<div class="row mx-0 ${border}" style="min-height: 480px;">${view}</div>`;
}


var min_archivos = 6;
var min_archivos_error = `La cantidad máxima de archivos que puede cargar es de ${min_archivos}`;
$('#agregar-archivos').click(function() {
    let tableData = $('#tabla_archivos').bootstrapTable('getData');
    if (tableData.length >= min_archivos) {
        alertDismissJS(min_archivos_error, 'error');
    } else {
        $('#archivos').click();
    }
});

$('#archivos').change(function() {
    let cantidad_archivos = $('#tabla_archivos').bootstrapTable('getData').length;
    if (cantidad_archivos + this.files.length > min_archivos) {
        alertDismissJS(min_archivos_error, 'error');
    } else if (this.files && this.files[0]) {
        $.each(this.files, function(index, file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let archivo = e.target.result;

                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cargar_archivo',
                    type: 'post',
                    data: { id: public_id_recepcion_compra, archivo },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_archivos').bootstrapTable('refresh');
                        } else {
                            alertDismissJS(data.mensaje, data.status);
                        }
                    },
                    error: function(jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }

            reader.readAsDataURL(file);
        });
    }

    $(this).val('');
});

function formatResult(node) {
    let $result = node.text;
    if (node.loading !== true && node.vencimiento) {
        $result = $(`<div class="d-flex justify-content-between">
                        <div class="overflow-hidden text-truncate">
                            <span>${node.text}</span>
                        </div>
                        <div>
                            <span class="badge badge-pill badge-success">${fechaLatina(node.vencimiento)}</span>
                        </div>
                    </div>`);
    }
    return $result;
};


$('#id_lote').select2({
    dropdownParent: $("#modal_imprimir"),
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
            return { q: 'ver_lotes', id_producto: public_id_produto, term: params.term, page: params.page || 1 }
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
})

$('#filtro_fecha').change();

