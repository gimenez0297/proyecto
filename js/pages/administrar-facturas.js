var url = 'inc/administrar-facturas-data';
var url_listados = 'inc/listados';

var public_editar_cobros = false;
var public_id_factura;

//Acciones por teclado
Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#agregar').click() });

var MousetrapModalBuscarDoctor = new Mousetrap();
MousetrapModalBuscarDoctor.pause();
MousetrapModalBuscarDoctor.bindGlobal('f2', (e) => { e.preventDefault(); $('#btn_buscar').click(); });

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

    // Mostrar datos en la tabla
    $('#filtro_sucursal').trigger('change');
    $('#filtro_cajero').trigger('change');
    $('#modal_agregar_doctor').on('hide.bs.modal', function(e) {
        MousetrapModalBuscarDoctor.pause();
    });
    
    $('#modal_agregar_doctor').on('show.bs.modal', function(e) {
        MousetrapModalBuscarDoctor.unpause();
    }); 
});

// Documentos
function iconosFilaDocumento(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm ver_doc" title="Ver"><i class="fas fa-file-pdf text-white"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_doc" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_funcionario=${$('#filtro_cajero').val()}` })
});

window.accionesFilaDocumento = {
    'click .eliminar_doc': function(e, value, row, index) {
        let id_ = row.id_;
        sweetAlertConfirm({
            title: `¿Eliminar el archivo?`,
            text: 'Esta acción no se podrá revertir',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_documento',
                    type: 'post',
                    data: { id_ },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_documentos').bootstrapTable('refresh');
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
    },
    'click .ver_doc': function (e, value, row, index) {
        window.open(row.ver);
    }
}

$("#documento").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_documento").val(cleanFileName);
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_funcionario=${$('#filtro_cajero').val()}` })
});

//filtro cajero
$('#filtro_cajero').select2({
    placeholder: 'Vendedor',
    width: '220px',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'cajeros_sucursal', id_sucursal: $('#filtro_sucursal').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.username, text: obj.funcionario }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#filtro_sucursal').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&id_funcionario=${$('#filtro_cajero').val()}` })
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
    dnone = ''
    noimprimir = ''
    if (esAdmin() === false) {
        dnone = 'd-none';
        noimprimir = row.id_factura == row.ultima_factura && row.impresiones == 1 ? '' : 'disabled';
    }

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm cambiar-estado mr-1 ${dnone}" title="Anular" ${disabled}><i class="fas fa-times"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-productos mr-1 ${dnone}" title="Ver Productos"><i class="fas fa-list"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm ver-detalle-dos mr-1" title="Ver Cobros"><i class="fas fa-hand-holding-usd"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir mr-1" title="Imprimir" ${noimprimir}><i class="fas fa-print"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cargar-documento mr-1" title="Cargar Documentos"><i class="fas fa-file-medical-alt"></i>`,
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
        if (row.editar_cobros == 0 && esAdmin() === false) {
            public_editar_cobros = false;
            $('#btn_guardar_detalle_dos').addClass('d-none');
        } else {
            public_editar_cobros = true;
            $('#btn_guardar_detalle_dos').removeClass('d-none');
        }
    },
    'click .cargar-documento': function(e, value, row, index) {
        $('#modal').modal('show');
        $('#tabla_documentos').bootstrapTable('refresh', { url: url+'?q=ver_documentos&id_factura='+row.id_factura });
        $("#hidden_id").val(row.id_factura);

        $('#documento').next('.custom-file-label').html('Seleccionar Archivo');
        resetForm('#formulario_carga_documento');
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
    classes: 'table table-hover table-condensed-sm',
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
            { field: 'numero', title: 'N°', align: 'left', valign: 'middle', sortable: true },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Nombre', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 200 },
            { field: 'nombre_apellido', title: 'Funcionario', align: 'letf', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, width: 200,  visible: false },
            { field: 'ci', title: 'C.I', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false },
            { field: 'condicion', title: 'Condición', align: 'letf', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'center', valign: 'middle', sortable: true, visible: false },
            { field: 'vencimiento', title: 'Vencimiento', align: 'letf', valign: 'middle', sortable: true, formatter: (value) => (value) ? fechaLatina(value) : '-' ,visible: false },
            { field: 'fecha_venta', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'descuento', title: 'Descuento', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: esAdmin(), switchable: esAdmin() },
            { field: 'total_venta', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,visible: esAdmin(), switchable: esAdmin() },
            { field: 'saldo', title: 'Saldo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, visible: false, switchable: esAdmin()  },
            { field: 'fecha', title: 'Registro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'courier', title: 'Courier', align: 'center', valign: 'middle', sortable: true, formatter: bstCondicional, visible: false },
            { field: 'receta', title: 'Receta', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'voucher', title: 'Voucher', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, visible: false },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila }
        ]
    ]
});

// Documentos
$('#tipo').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('select2:select',function(){
    let data = $('#tipo').select2('data')[0];
    if (data.id == 1) {
        $('.doctor').removeClass('d-none');
        $('label[for=doctor]').addClass('label-required');
        $('#doctor').prop('required', true);
    }else{
        $('.doctor').addClass('d-none');
    }
});

$('#doctor').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'doctores_facturas', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_doctor, text: obj.nombre_apellido }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    var data = $('#doctor').select2('data')[0] || {};
    $('#id_doctor').val(data.id);
});

$("#tabla_documentos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: $(window).height()-578,
    sortName: "documento",
    sortOrder: 'asc',
    uniqueId: 'id_',
    columns: [
        [
            { field: 'id_', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'descripcion', title: 'Descripción', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna  },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'file', title: 'File', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'doctor_str', title: 'Doctor', align: 'left', valign: 'left', sortable: true },
            { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo_str', title: 'Tipo', align: 'left', valign: 'left', sortable: true },
            { field: 'ver', title: 'Ver', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaDocumento,  formatter: iconosFilaDocumento, width: 100 }
        ]
    ]
});

$('#documento').change( function(event) {
    var tmppath = URL.createObjectURL(event.target.files[0]); 
    $("#change_documento").val(tmppath);     
});

function getBase64(file) {
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function () {
     //return reader.result;
     $("#url_documento").val(reader.result);
    };
    reader.onerror = function (error) {
    };
 }

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
            { field: 'fraccionado', title: '', align: 'center', valign: 'middle', sortable: true, formatter: fraccionado, footerFormatter: bstFooterTextTotal },
            { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true },
            { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'id_presentacion', title: 'ID PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'PRESENTACIÓN', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'controlado', title: 'CONTROLADO', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: bstCondicional, width: 100 },
            { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: esAdmin(), switchable: esAdmin() },
            { field: 'precio', title: 'PRECIO X UNID.', align: 'right', valign: 'middle', sortable: true, visible: false ,formatter: separadorMiles, visible: esAdmin(), switchable: esAdmin() },
            { field: 'subtotal', title: 'SUB TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: esAdmin(), switchable: esAdmin() },
            { field: 'descuento', title: 'DESCUENTO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, visible: esAdmin(), switchable: esAdmin() },
            { field: 'total_venta', title: 'TOTAL', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});

function fraccionado(data) {
    switch (parseInt(data)) {
        case 0: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-success" title="Entero">E</span></b>';
        case 1: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-info" title="Fraccionado">F</span></b>';
    }
}

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

$('#agregar-archivos').on('click', () => $('#formulario_carga_documento').submit());
$("#formulario_carga_documento").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    var data = new FormData(this);
    $.ajax({
        url: url + '?q=guardar-documento',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        processData: false,
        contentType: false,
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();
            if (data.status == 'ok') {
                $('#tabla_documentos').bootstrapTable('refresh');
                resetForm('#formulario_carga_documento');
                $('#documento').next('.custom-file-label').html('Seleccionar Archivo');
            }else{
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('#filtro_fecha').change();

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

$('#agregar_doctor').click(function () {
    $('#modalLabel').html('Agregar Doctor');
    $('#frm_agregar_doctor').attr('action', 'cargar_doctor');
    $('#eliminar').hide();
    resetForm('#frm_agregar_doctor');
   $('#modal_agregar_doctor').modal('show');

    
});

$('#id_especialidad').select2({
    dropdownParent: $("#modal_agregar_doctor"),
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
        data: function (params) {
            return { q: 'doctores', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_especialidad, text: obj.nombre }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#btn_buscar').on('click', async function(e) {
    var ruc = $('#ruc').val();
    
        var data = await buscarRUC(ruc);
        if (data.ruc) {
            $('#ruc').val(data.ruc+"-"+data.dv);
            $('#nombre_apellido').val(data.razon_social);
        } else {
            alertDismissJsSmall('RUC no encontrado', 'error', 2000);
        }
});

// GUARDAR NUEVO DOCTOR
$("#frm_agregar_doctor").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
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
            alertDismissJS(data.mensaje, data.status, function(){
                let obj = data.data;
                $('#doctor ').select2('trigger', 'select',{
                data:{ id: obj.id, text: obj.nombre_apellido }
            });
                $('#modal_agregar_doctor').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            });
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});
