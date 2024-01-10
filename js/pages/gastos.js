var url = 'inc/gastos-data';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#agregar').click() });
Mousetrap.bindGlobal('f2', (e) => { e.preventDefault(); $('#btn_buscar').click() });
Mousetrap.bindGlobal('f3', (e) => { e.preventDefault(); $('#btn_vencimiento').click() });

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

$(document).ready(function () { 
    $('#documento').mask('999-999-9999999');
});



function ultimo_correlativo_gasto() {
    $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: url,
        type: 'POST',
        data: { q: 'ultimo_correlativo_presupuesto' },
        beforeSend: function () {
            //NProgress.start();
        },
        success: function (json) {
            $('#nro').val(json.nro_gasto);
        },
        error: function (xhr) {
            //NProgress.done();
        }
    });
}


$('#id_tipo_gasto').select2({
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
        data: function (params) {
            return { q: 'tipos_gastos', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_tipo_gasto, text: obj.nombre }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_sucursal').select2({
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

$('#sucursales').select2({
    placeholder: 'Sucursales',
    width: '150px',
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
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&proveedor=${$('#proveedores').val()}` })
});

$('#proveedores').select2({
    placeholder: 'Proveedores',
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
            return { q: 'proveedores-compras', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.ruc, text: obj.proveedor }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$('#sucursales').val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}&proveedor=${$(this).val()}` })
});


$('#condicion').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
}).on('change', function(e) {
    $("#btn_vencimiento").prop('disabled', !($(this).val() == 2));
});

$('#id_tipo_factura').select2({
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
        data: function (params) {
            return { q: 'tipos_comprobantes', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_tipo_comprobante, text: obj.nombre_comprobante }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_plan_cuenta').select2({
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
        data: function (params) {
            return { q: 'planes', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_libro_cuenta, text: obj.denominacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#razon_social').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        async: true,
        data: function (params) {
            return { q: 'proveedores_gastos', term: params.term, page: params.page || 1, tipo_proveedor: $('#tipo_proveedor').val()}
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { 
                    return { id: obj.id_proveedor, text: obj.proveedor, ruc: obj.ruc}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    let data = $('#razon_social').select2('data')[0];
    if (data) {
        $('#ruc').val(data.ruc)
    }
});

$('#tipo_proveedor').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
}).on('change', function(){
    let data = $('#tipo_proveedor').select2('data')[0];
    $('#ruc').val('');
    $('#razon_social').val(null).trigger('change');
    if (data) {
        $('#ruc').prop('readonly',false)
        $('#btn_buscar').prop('disabled',false)
        $('#razon_social').prop('disabled',false)
    }else{
        $('#ruc').prop('readonly',true)
        $('#btn_buscar').prop('disabled',true)
        $('#razon_social').prop('disabled',true)
    }
});

window.accionesFila = {
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    },
    'click .ver-detalles': function(e, value, row, index) {
        if(row.id_recepcion_compra){
            $('#modal_detalles').modal('show');
            $('#toolbar_detalles_text').html(`Gasto N° ${row.nro_gasto}`);
            $('#tabla_detalles').bootstrapTable('refresh', { url: url+'?q=ver_detalle_recepcion&id='+row.id_recepcion_compra });
        }else{
            $('#modal_detalles').modal('show');
            $('#toolbar_detalles_text').html(`Gasto N° ${row.nro_gasto}`);
            $('#tabla_detalles').bootstrapTable('refresh', { url: url+'?q=ver_detalles_gasto&id='+row.id_gasto });
        }
    }
}

$("#tabla").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: false,
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
    height: $(window).height() - 130,
    pageSize: Math.floor(($(window).height() - 130) / 50),
    sortName: "id_gasto",
    sortOrder: 'desc',
    trimOnSearch: false,
    showFooter: true,
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_gasto', title: 'ID Gasto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_tipo_gasto', title: 'ID Gastos', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_sucursal', title: 'ID Sucursal', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_sub_tipo_gasto', title: 'ID Sub Tipo Gasto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_proveedor', title: 'ID Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_recepcion_compra', title: 'ID Recepcion Compra', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'nro_gasto', title: 'Nro Gasto', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal  },
            { field: 'nombre', title: 'Tipo de Gasto', align: 'left', valign: 'middle', sortable: true },
            { field: 'deducible_str', title: 'Deducible / No Deducible', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true },
            { field: 'timbrado', title: 'Timbrado', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_emision_str', title: 'Fecha Emision', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Razon Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'condicion_str', title: 'Condicion', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_vencimiento_str', title: 'Fecha Vencimiento', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'documento', title: 'Nro. Documento', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
            { field: 'pagado', title: 'Pagado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
            { field: 'pendiente', title: 'Pendiente', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
            { field: 'gravada_5', title: 'IVA 5%', align: 'right', valign: 'middle', sortable: true, visible: false, formatter: separadorMiles},
            { field: 'gravada_10', title: 'IVA 10%', align: 'right', valign: 'middle', sortable: true, visible: false, formatter: separadorMiles},
            { field: 'exenta', title: 'Exenta', align: 'right', valign: 'middle', sortable: true, visible: false, formatter: separadorMiles},
            { field: 'observacion', title: 'Observaciones', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'imputa_iva', title: 'Imputa IVA', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'imputa_ire', title: 'Imputa IRE', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'imputa_irp', title: 'Imputa IRP', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'tipo_proveedor', title: 'Tipo Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: true, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
})

function iconosFila(value, row, index) {
    let disabled = (!row.id_recepcion_compra) ? '' : 'disabled';
    let no_edit = (row.estado != 1) ? '' : 'disabled';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalles mr-1" title="Ver detalles"><i class="fas fa-list"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm editar" title="Editar datos" ${disabled}${no_edit}><i class="fas fa-pencil-alt"></i></button>`
    ].join('');
}


// detalles
$("#tabla_detalles").bootstrapTable({
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
	height: 480,
    sortName: "numero",
    sortOrder: 'asc',
    columns: [
        [
            { field: 'numero', title: 'Numero de Pago', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_pago', title: 'Fecha Pago', align: 'left', valign: 'middle', sortable: true },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'banco', title: 'Banco', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'forma_pago', title: 'Forma Pago', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'monto', title: 'PAGADO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria  },
            { field: 'observacion', title: 'Observacion', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'usuario', title: 'Usuario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

function iconosFilaVencimientos(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar_ven mr-1" title="Editar"><i class="fas fa-edit"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_ven" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaVencimientos = {
    'click .eliminar_ven': function (e, value, row, index) {
        let nombre = row.vencimiento;
        sweetAlertConfirm({
            title: `Eliminar Vencimiento`,
            text: `¿Eliminar el Vencimiento: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_vencimientos').bootstrapTable('removeByUniqueId', row.vencimiento);
            }
        });
    },
    'click .editar_ven': function (e, value, row, index) {
        $('#id_vencimiento').val(row.vencimiento);
        $('#tabla_vencimientos').bootstrapTable('removeByUniqueId', row.vencimiento);
    }
}


$("#tabla_vencimientos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: false,
    height: 200,
    sortName: "vencimiento",
    sortOrder: 'asc',
    uniqueId: 'vencimiento',
    columns: [
        [
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true, formatter: fechaLatina },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaVencimientos, formatter: iconosFilaVencimientos, width: 150 }
        ]
    ]
});

$('#agregar-vencimiento').click(function() {
    var tableData = $('#tabla_vencimientos').bootstrapTable('getData');
    var vencimiento = $('#id_vencimiento').val();

    if (vencimiento == '' ) {
        alertDismissJS('No puede agregar fechas vacias. Favor verifique.', 'error');
        return false;
    }

    if (tableData.find(value => value.vencimiento == vencimiento)) {
        $('#tabla_vencimientos').bootstrapTable('removeByUniqueId', vencimiento)
    }

    $('#tabla_vencimientos').bootstrapTable('insertRow', {
        index: 1,
        row: {
            vencimiento,
        }
    });
    $('#id_vencimiento').val('');
    $('#id_vencimiento').focus();
});


function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Gasto');
    $('#formulario').attr('action', 'editar');
    $('#modal').modal('show');

    $("#id").val(row.id_gasto);
    $("#id_recepcion").val(row.id_recepcion_compra);
    $("#timbrado").val(row.timbrado);
    $('#emision').val(row.fecha_emision);
    //$('#ruc').val(row.ruc);
    $('#entidad').val(row.razon_social);
    $("#condicion").val(row.condicion).trigger('change');
    //$('#vencimiento').val(row.fecha_vencimiento);
    $('#documento').val(row.documento);
    $('#check_iva').prop('checked', (row.imputa_iva == 'S')).trigger('change');
    $('#check_irp').prop('checked', (row.imputa_irp == 'S')).trigger('change');
    $('#check_ire').prop('checked', (row.imputa_ire == 'S')).trigger('change');
    $("#concepto").val(row.concepto);
    $("#monto").val(separadorMiles(row.monto));
    $("#nro").val(row.nro_gasto);
    $("#exenta").val(separadorMiles(row.exenta));
    $("#iva_10").val(separadorMiles(row.gravada_10));
    $("#iva_5").val(separadorMiles(row.gravada_5));
    $("#observacion").val(row.observacion);
    $("#tipo_proveedor").val(row.tipo_proveedor).trigger('change');

    if (row.id_tipo_gasto > 0) {
        $('#id_tipo_gasto').select2('trigger', 'select', {
            data: { id: row.id_tipo_gasto, text: row.nombre }
        });
    }
    if (row.id_sucursal > 0) {
        $('#id_sucursal').select2('trigger', 'select', {
            data: { id: row.id_sucursal, text: row.sucursal }
        });
    }
    if (row.id_tipo_comprobante > 0) {
        $('#id_tipo_factura').select2('trigger', 'select', {
            data: { id: row.id_tipo_comprobante, text: row.nombre_comprobante }
        });
    }
    if (row.id_proveedor > 0) {
        $('#razon_social').select2('trigger', 'select', {
            data: { id: row.id_proveedor, text: row.razon_social, ruc:row.ruc }
        });
    }
    if (row.id_libro_cuentas > 0) {
        $('#id_plan_cuenta').select2('trigger', 'select', {
            data: { id: row.id_libro_cuentas, text: row.denominacion }
        });
    }

    $('#tabla_vencimientos').bootstrapTable('refresh', { url: `${url}?q=ver_vencimientos&id=${row.id_gasto}&id_recepcion=${row.id_recepcion}` })
    
}

$('#btn_buscar').on('click', function(e) {
    var ruc = $('#ruc').val();
    var tipo_proveedor = $('#tipo_proveedor').val();
    $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: url_listados,
        timeout: 10000,
        type: 'POST',
        data: { q:'buscar_proveedor_gasto', ruc, tipo_proveedor},
        beforeSend: function(){
            NProgress.start();
        },
        success: function (data) {
            NProgress.done();
            datos = data;
        },
        error: function (jqXhr) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
    
    if (datos){
        $('#razon_social').select2('trigger', 'select', {
            data: { id: datos.id_proveedor, text: datos.proveedor,ruc: datos.ruc }
        });
    }else{
        alertDismissJS('El proveedor no se encuentra cargado', "error");
        return false;
    }
});

// $('#iva').change(function(){
    
//     var iva = $('#iva').val();
//     var monto =  quitaSeparadorMiles($('#monto').val());

//     if (iva == 1) {
//         iva_5 = Math.round((monto / 21));
//         $('#iva_5').val(separadorMiles(iva_5));
//         $('#iva_10').val('0');
//         $('#extenta').val('0');
//     } else if (iva == 2) {
//         iva_10 = Math.round((monto / 11));
//         $('#iva_10').val(separadorMiles(iva_10));
//         $('#extenta').val('0');
//         $('#iva_5').val('0');
//     }else{
//         $('#extenta').val(separadorMiles(monto));
//         $('#iva_5').val('0');
//         $('#iva_10').val('0');
//     }
// })

$('#iva_10, #iva_5, #exenta').on('keyup change', function (e) {
    var iva_10 = quitaSeparadorMiles($('#iva_10').val());
    var iva_5 = quitaSeparadorMiles($('#iva_5').val());
    var exenta = quitaSeparadorMiles($('#exenta').val());
    
    var total = iva_10 + iva_5 + exenta;
    $('#monto').val(separadorMiles(total));
})

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Gasto');
    $('#formulario').attr('action', 'cargar');
    $('#condicion').val('1').trigger('change');
    $('#eliminar').hide();
    resetForm('#formulario');
    ultimo_correlativo_gasto()
    $('#emision').val(moment().format('YYYY-MM-DD'));
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    data.push({ name: 'vencimientos', value: JSON.stringify($('#tabla_vencimientos').bootstrapTable('getData')) });
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


function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('input[type="checkbox"]').trigger('change');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

$('#imprimir').click(function() {
    var desde        = $('#desde').val();
    var hasta        = $('#hasta').val();
    var sucursal     = $('#sucursales').val();
    var proveedor    = $('#proveedores').val();
   
    var param = {sucursal: sucursal, hasta: hasta, desde: desde, proveedor: proveedor};
    OpenWindowWithPost("imprimir-gastos.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirGastos", param);
});

$('#filtro_fecha').change();

