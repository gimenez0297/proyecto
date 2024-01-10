var url = 'inc/reporte-ingreso-egreso-ganancias-data';
var url_listados = 'inc/listados';

$(document).ready(function () {
    // Mostrar datos en la tabla
    $('#filtro_fecha').trigger('change');
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


window.accionesFila = {
    'click .ver-detalle': function(e, value, row, index) {
        var desde = $('#desde').val();
        var hasta = $('#hasta').val();
        $('#modal_detalle').modal('show');
        $('#toolbar_ingresos_text').html(`INGRESOS`);
        $('#toolbar_egresos_text').html(`EGRESOS`);
        $('#tabla_ingresos').bootstrapTable('refresh', { url: url+'?q=ver_ingresos&id_sucursal='+row.id_sucursal+'&desde='+desde+'&hasta='+hasta});
        $('#tabla_egresos').bootstrapTable('refresh', { url: url+'?q=ver_egresos&id_sucursal='+row.id_sucursal+'&desde='+desde+'&hasta='+hasta});
    },
    'click .imprimir': function (e, value, row, index) {
        var desde=$('#desde').val();
        var hasta=$('#hasta').val();
        var param = { id_sucursal: row.id_sucursal, desde: desde, hasta: hasta};
        OpenWindowWithPost("imprimir-reporte-ingresos-egresos-ganancias", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirIngresosEgresosGanancias", param);  
    },
    'click .exportar': function (e, value, row, index) {
        var desde=$('#desde').val();
        var hasta=$('#hasta').val();
        var param = { id_sucursal: row.id_sucursal, desde: desde, hasta: hasta};
        window.location.replace(`exportar-reporte-ingresos-egresos-ganancias.php?${$.param(param)}`);    
    }
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Detalles"><i class="fas fa-list"></i></button>',
        `<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir mr-1" title="Imprimir"><i class="fas fa-print"></i></button>`,
        `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm exportar mr-1" title="Exportar"><i class="fas fa-file-export"></i></button>`,
    ].join('');
}

$("#tabla").bootstrapTable({
    // url: url + '?q=ver',
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
    showFooter: true,
    footerStyle: bstFooterStyle,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height() - 110,
    pageSize: Math.floor(($(window).height() - 110) / 50),
    sortName: "id_sucursal",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_sucursal', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false, switchable:false },
            { field: 'sucursal', title: 'Sucursal', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            // { field: 'total_venta', title: 'Total Venta', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'total_costo', title: 'Total Costo', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'utilidad', title: 'Utilidad', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'porcentaje_utilidad', title: '% Utilidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstTotalUtilidad },
            { field: 'total_gastos', title: 'Total Gastos', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'ganancias', title: 'Ganancias', align: 'right', valign: 'middle', sortable: true ,formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila, switchable: false }
        ]
    ]
});

function bstTotalUtilidad(data) {
    let total_venta = data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current.total_venta)), 0);
    let total_utilidad = data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current.utilidad)), 0);
    let total = (total_utilidad / total_venta) * 100;
	return `<span class="f16">${total.toFixed(2)}%</span>`;
}

// Ingresos y Egresos
$("#tabla_ingresos").bootstrapTable({
    toolbar: '#toolbar_ingresos_text',
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
    sortName: "concepto",
    sortOrder: 'asc',
    uniqueId: 'id_liquidacion_ingreso',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_factura', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_emision', title: 'Fecha Venta', align: 'left', valign: 'middle', sortable: true},
            { field: 'nro_documento', title: 'N° Documento', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'razon_social', title: 'Razon Social', align: 'left', valign: 'middle', sortable: true ,  cellStyle: bstTruncarColumna },
            // { field: 'total_venta', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            // { field: 'total_costo', title: 'Costo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'utilidad', title: 'Utilidad', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'porcentaje_utilidad', title: '% Utilidad', align: 'right', valign: 'middle', sortable: true, footerFormatter: bstTotalUtilidadIngresos },
        ]
    ]
});

function bstTotalUtilidadIngresos(data) {
    let total_venta = data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current.total_venta)), 0);
    let total_utilidad = data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current.utilidad)), 0);
    let total = (total_utilidad / total_venta) * 100;
	return `<span class="f16">${total.toFixed(2)}</span>`;
}

$("#tabla_egresos").bootstrapTable({
    toolbar: '#toolbar_egresos_text',
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
    sortName: "concepto",
    sortOrder: 'asc',
    uniqueId: 'id_gasto',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_gasto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_emision', title: 'Fecha Compra', align: 'left', valign: 'middle', sortable: true},
            { field: 'nro_documento', title: 'N° Documento', align: 'center', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'razon_social', title: 'Razon Social', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'total_venta', title: 'Importe', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            //{ field: 'observacion', title: 'OBSERVACIÓN', align: 'left', valign: 'middle', sortable: true },
        ]
    ]
});

$("#imprimir").on("click", function (e) {
    var param = {
        desde         : $('#desde').val(),
        hasta         : $('#hasta').val(),
    };
      
    OpenWindowWithPost("imprimir-ingresos-egresos-sucursales", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirIngresosEgresosPorSucursales", param);
    //resetForm();
});

$("#exportar").on("click", function (e) {
    var param = {
        desde         : $('#desde').val(),
        hasta         : $('#hasta').val(),
    };
    window.location.replace(`exportar-ingresos-egresos-sucursales.php?${$.param(param)}`);
    //resetForm();
});
