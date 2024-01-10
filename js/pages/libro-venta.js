var url_listados = 'inc/listados';
var url          = 'inc/libro-venta-data.php';


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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}` });
});

$('#estado').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
});


$('#clientes').select2({
    placeholder: 'TODOS',
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
            return { q: 'clientes-venta', term: params.term, page: params.page || 1}
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_cliente, text: obj.razon_social }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});


$("#tabla").bootstrapTable({
    //url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: false,
    showRefresh: true,
    showToggle: true,
    showColumns: true,
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height() - 210,
    pageSize: Math.floor(($(window).height() - 210) / 50),
    sortName: "numero",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'tipo_reg', title: 'Tipo Reg.', align: 'center', valign: 'middle', width: 8 },
            { field: 'tipo_identificacion', title: 'Tipo Iden.', align: 'center', valign: 'middle', width: 8 },
            { field: 'ruc', title: 'RUC', align: 'center', valign: 'middle', sortable: true,  cellStyle: bstTruncarColumna },
            { field: 'razon_social', title: 'Razon Social/Nombre', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'tipo_comprobante', title: 'Tipo Comprobante', align: 'center', valign: 'middle', width: 8 },
            { field: 'fecha', title: 'Fecha de Emisión', align: 'center', valign: 'middle', sortable: true },
            { field: 'timbrado', title: 'Timbrado', align: 'center', valign: 'middle' },
            { field: 'nro_factura', title: 'Nro. Comprobante', align: 'center', valign: 'middle', sortable: true },
            { field: 'gravada_10', title: 'Gravada 10%(iva incl.)', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles},
            { field: 'gravada_5', title: 'Gravada 5%(iva incl.)', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles},
            { field: 'exenta', title: 'Exenta', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles},
            { field: 'total_venta', title: 'Imp. Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
            { field: 'condicion', title: 'Condicion', align: 'center', valign: 'middle', sortable: true, visible: true },
            { field: 'moneda_extranjera', title: 'Moneda Extran.', align: 'center', valign: 'middle',  visible: true },
            { field: 'imputa_iva', title: 'Imp. IVA', align: 'center', valign: 'middle',  visible: true },
            { field: 'imputa_ire', title: 'Imp. IRE', align: 'center', valign: 'middle',  visible: true },
            { field: 'imputa_irp', title: 'Imp. IRP', align: 'center', valign: 'middle',  visible: true },
            { field: 'nro_comp_asociada', title: 'Nro. comp. asociada', align: 'right', valign: 'middle',  visible: true },
            { field: 'nro_timb_asociada', title: 'Nro. timb. asociada', align: 'right', valign: 'middle',  visible: true },
        ]
    ]
});

$('#btn_buscar').on('click', function (e) {
    var desde       = $('#desde').val();
    var hasta       = $('#hasta').val();
    var id_cliente  = $('#clientes').val();
    var estado      = $('#estado').val();

    $("#tabla").bootstrapTable("refresh", { url: url + '?q=ver&desde=' + desde + '&hasta=' + hasta + '&id_cliente=' + id_cliente + '&estado=' + estado});

});

$("#imprimir").on("click", function (e) {
    var param = {
        desde       : $('#desde').val(),
        hasta       : $('#hasta').val(),
        id_cliente  : $('#clientes').val(),
        estado      : $('#estado').val()
    };
      
    OpenWindowWithPost("imprimir-libro-venta", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirLibroDeVenta", param);
    resetForm();
  });

$("#exportar").on("click", function (e) {
    var param = {
        desde       : $('#desde').val(),
        hasta       : $('#hasta').val(),
        id_cliente  : $('#clientes').val(),
        estado      : $('#estado').val()
    };
      
    //$(this).prop('href',`exportar-libro-venta-rg90.php?${$.param(param)}`);
    //OpenWindowWithPost("exportar-libro-venta-rg90", "toolbar=0,scrollbars=,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "exportarLibroVentaRg90", param);
    window.location.replace(`exportar-libro-venta-rg90.php?${$.param(param)}`);
    resetForm();
});

function resetForm() {
    $('#desde').val("")
    $('#hasta').val("")
    $('#estado').find('select').val(null).trigger('change');
    $('#clientes').find('select').val(null).trigger('change');
}

$('#filtro_fecha').change();