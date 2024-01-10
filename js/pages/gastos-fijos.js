var url = 'inc/gastos-fijos-data';
var url_listados = 'inc/listados';

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


/*function cb(start, end) {
        var fecha = new Date(); //Fecha actual
        var mes = fecha.getMonth()+1; //obteniendo mes
        var dia = fecha.getDate(); //obteniendo dia
        var ano = fecha.getFullYear(); //obteniendo año
        if(dia<10)
          dia='0'+dia; //agrega cero si el menor de 10
        if(mes<10)
          mes='0'+mes //agrega cero si el menor de 10

        return ano+"-"+mes+"-"+dia;
    }*/


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
    $('#condicion').on('change', function() {
        if($(this).val() == 1){
             $('#vencimiento').val('').attr('readonly', 'reandonly');
             $('label[for=vencimiento]').removeClass('label-required');
             $('#vencimiento').prop('required', false);
        }else{
             $('#vencimiento').attr('readonly', false);
             $('#vencimiento').prop('required', true);
             $('label[for=vencimiento]').addClass('label-required');
        }
    });
});


// Buscar Gastos
$('#id_tipo_gasto').blur(async function () {
    var ruc = $(this).val();
    var data = {};
    var tipo_proveedor= 1;
    if (ruc) {
        var buscar_proveedor = await buscarClienteRuc(ruc, tipo_proveedor);
        if (buscar_proveedor.ruc) {
            data = buscar_proveedor;
        } else {
            data = {
                ruc,
                id_proveedor: '',
                proveedor: '',
                nombre_fantasia: ''
            }
            alertDismissJsSmall('Proveedor no encontrado', 'error', 2000);
        }
        cargarDatosProveedor(data);
    }
});


$('#gasto_fijo').select2({
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
            return { q: 'gastos_fijos', tipo_gasto: $('#id_tipo_gasto').val(), term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_gastos_fijos, text: obj.nombre }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function (results) {    
   poblarInputGasatos($('#gasto_fijo').val())
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

$('#filtro_sucursales').select2({
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` })
});

$('#sucursal').select2({
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
}).on('select2:select', function(event) {    
    $('#gasto_fijo').val(null).trigger('change');
});


/*SUMA LOS IVA PARA MOSTRAR EN EL INPUT MONTO*/
$('#iva_10, #iva_5, #exenta').on('keyup change', function(e){
    var iva_10 = quitaSeparadorMiles($('#iva_10').val());
    var iva_5 = quitaSeparadorMiles($('#iva_5').val());
    var exenta = quitaSeparadorMiles($('#exenta').val());
    var total = iva_10 + iva_5 + exenta;
    $('#monto').val(separadorMiles(total));

})

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
            return { q: 'proveedores_gastos', term: params.term, page: params.page || 1 }
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
        $('#id_proveedor').val(data.id)
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

$('#id_sub_tipo_gasto').select2({
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
            return { q: 'sub_tipos_gastos_fijos_gastos_fijos', term: params.term, page: params.page || 1,id: $('#id_tipo_gasto').val() }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_gasto_fijo_sub_tipo, text: obj.nombre }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 1 ? 0 : 1);
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id: row.id_gasto_fijo, estado },
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
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
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
    classes: 'table table-hover table-condensed',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height() - 130,
    pageSize: Math.floor(($(window).height() - 130) / 50),
    sortName: "nro_gasto",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
             { field: 'id_gasto', title: 'ID Gasto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_tipo_gasto', title: 'ID Gastos', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_tipo_comprobante', title: 'ID Tipo Comprobante', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_sucursal', title: 'ID Sucursal', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_sub_tipo_gasto', title: 'ID Sub Tipo Gasto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_proveedor', title: 'ID Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_recepcion_compra', title: 'ID Recepcion Compra', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'nro_gasto', title: 'Nro Gasto', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal  },
            { field: 'nombre', title: 'Tipo de Gasto', align: 'left', valign: 'middle', sortable: true },
            { field: 'deducible_str', title: 'Deducible / No Deducible', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, visible:false },
            { field: 'timbrado', title: 'Timbrado', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_emision_str', title: 'Fecha Emision', align: 'left', valign: 'middle', sortable: true, visible: true},
            { field: 'ruc', title: 'RUC', align: 'left', valign: 'middle', sortable: true,cellStyle: bstTruncarColumna},
            { field: 'razon_social', title: 'Razon Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'condicion_str', title: 'Condicion', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_vencimiento_str', title: 'Fecha Vencimiento', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'documento', title: 'Nro. Documento', align: 'left', valign: 'middle', sortable: true, visible: false},
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, visible:false},
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria, visible:false},
            { field: 'pagado', title: 'Pagado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria, visible:false},
            { field: 'pendiente', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,footerFormatter: bstFooterSumatoria},
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
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
    if ('dbl-click-row.bs.table') {
        editarDatos(row);
    }

});

function iconosFila(value, row, index) {
    let no_edit = (row.estado ==0 ) ? '' : 'disabled';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos" ${no_edit}><i class="fas fa-pencil-alt"></i></button>`
    ].join('');
}

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Gasto Fijo');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
    $('#emision').val(moment().format('YYYY-MM-DD'));
    ultimo_correlativo_gasto_fijo()
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
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

// ELIMINAR
$('#eliminar').click(function() {
    var id = $("#id").val();
    var nro = $("#nro").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar el Gasto Fijo Nro°${nro}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, nro },	
                beforeSend: function() {
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

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Gasto Fijo');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id").val(row.id_gasto);
    //$("#id_recepcion").val(row.id_recepcion_compra);
    $("#timbrado").val(row.timbrado);
    $('#emision').val(row.fecha_emision);
    $('#ruc').val(row.ruc);
    $('#razon_social').val(row.razon_social);
    $("#condicion").val(row.condicion).trigger('change');
    //$('#vencimiento').val(row.fecha_vencimiento);
    $('#documento').val(row.documento);
    $('#check_iva').prop('checked', (row.imputa_iva == 'S')).trigger('change');
    $('#check_irp').prop('checked', (row.imputa_irp == 'S')).trigger('change');
    $('#check_ire').prop('checked', (row.imputa_ire == 'S')).trigger('change');
    $("#concepto").val(row.concepto);
    $("#nro").val(row.nro_gasto);
    $("#observacion").val(row.observacion);
    //$("#tipo_proveedor").val(row.tipo_proveedor).trigger('change');
 

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
    if (row.id_gastos_fijos > 0) {
        $('#gasto_fijo').select2('trigger', 'select', {
            data: { id: row.id_gastos_fijos, text: row.descripcion }
        });
    }
    if (row.id_proveedor > 0) {
        $('#razon_social').select2('trigger', 'select', {
            data: { id: row.id_proveedor, text: row.proveedor,ruc: row.ruc }
        });
    }
    if (row.id_libro_cuentas > 0) {
        $('#id_plan_cuenta').select2('trigger', 'select', {
            data: { id: row.id_libro_cuentas, text: row.denominacion }
        });
    }

    $("#monto").val(separadorMiles(row.monto));
    $("#exenta").val(separadorMiles(row.exenta));
    $("#iva_10").val(separadorMiles(row.gravada_10));
    $("#iva_5").val(separadorMiles(row.gravada_5));
   
}

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function ultimo_correlativo_gasto_fijo() {
    $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: url,
        type: 'POST',
        data: { q: 'ultimo_correlativo_gasto_fijo' },
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

$('#filtro_fecha').change();


//Recupera tipos de Gastos
function poblarInputGasatos(gasto) {
    $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: url,
        type: 'POST',
        data: { q: 'rescupera_tipo_gastos', id: gasto},
        beforeSend: function () {
            //NProgress.start();
        },
        success: function (json) {
            $('#iva_10').val(separadorMiles(json.iva_10));
            $('#iva_5').val(separadorMiles(json.iva_5));
            $('#exenta').val(separadorMiles(json.exenta));
            $('#monto').val(separadorMiles(json.monto)); 
            $('#concepto').val(separadorMiles(json.concepto)); 
            $('#razon_social').select2('trigger', 'select', {
                data: { id: json.id_proveedor, text: json.proveedor }
            }); 
            $('#id_tipo_factura').select2('trigger', 'select', {
                data: { id: json.id_tipo_comprobante, text: json.nombre_comprobante }
            }); 
            setTimeout(function(){
             $('#ruc').val(json.ruc);
            },200);
        },
        error: function (xhr) {
            //NProgress.done();
        }
    });
}


