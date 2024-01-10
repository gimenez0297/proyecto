var url = 'inc/iva-credito-debito-data';


// CALENDARIO
var fechaIni;
var fechaFin;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cb(start, end) {
    fechaIni = start.format('YYYY');
    fechaFin = end.format('YYYY');
    $('#filtro_fecha span').html(start.format('YYYY'));

    $("#desde").val((fechaIni));
    $("#hasta").val((fechaFin));
}

// Rango: Año actual
cb(moment().startOf('year'), moment().endOf('year'));

// Rango: dia actual
// cb(moment(), moment());

$('#filtro_fecha').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    opens: "right",
    format: 'YYYY',
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
        [moment().subtract(0, 'year').format("Y")]: [moment(), moment()],
        [moment().subtract(1, 'year').format("Y")]: [moment().subtract(1, 'year'), moment().subtract(1, 'year')],
        [moment().subtract(2, 'year').format("Y")] : [moment().subtract(2, 'year').startOf('year'), moment().subtract(2, 'year').endOf('year')],
        // [meses[moment().subtract(2, 'year').format("M")]] : [moment().subtract(2, 'year').startOf('year'), moment().subtract(2, 'year').endOf('year')],
        // [meses[moment().subtract(3, 'year').format("M")]] : [moment().subtract(3, 'year').startOf('year'), moment().subtract(3, 'year').endOf('year')],
        // [meses[moment().subtract(4, 'year').format("M")]] : [moment().subtract(4, 'year').startOf('year'), moment().subtract(4, 'year').endOf('year')],
        // [meses[moment().subtract(5, 'year').format("M")]] : [moment().subtract(5, 'year').startOf('year'), moment().subtract(5, 'year').endOf('year')]
    }
}, cb);

$('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni = picker.startDate.format('YYYY');
    fechaFin = picker.endDate.format('YYYY');
    $("#desde").val(picker.startDate.format('YYYY')); 
    $("#hasta").val(picker.endDate.format('YYYY'));

    $('#filtro_fecha').change();
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val() });
});

$("#tabla").bootstrapTable({
    //url: url + '?q=ver',
    toolbar: '#toolbar',
    showExport: true,
    search: false,
    showRefresh: true,
    showToggle: true,
    showColumns: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'false',
    classes: 'table table-hover table-condensed',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height() - 130,
    pageSize: Math.floor(($(window).height() - 130) / 50),
    sortName: "iva",
    sortOrder: 'asc',
    trimOnSearch: false,
    rowStyle: rowStyleTable,
    columns: [
        [
            { field: 'iva', title: 'IVA', align: 'left', valign: 'middle', sortable: true },
            { field: 'enero', title: 'Enero', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles },
            { field: 'febrero', title: 'Febrero', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles },
            { field: 'marzo', title: 'Marzo', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'abril', title: 'Abril', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'mayo', title: 'Mayo', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'junio', title: 'Junio', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'julio', title: 'Julio', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'agosto', title: 'Agosto', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'septiembre', title: 'Septiembre', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'octubre', title: 'Octubre', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'noviembre', title: 'Noviembre', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles},
            { field: 'diciembre', title: 'Diciembre', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles },
            { field: 'total_debito', title: 'Total', align: 'left', valign: 'middle', sortable: true, cellStyle: cellStyleTotal, formatter: separadorMiles },
        ]
    ]
});

function cellStyleTotal(value, row, index, field) {
    if (value < 0) {
        return {
            css: {
                color: 'red'
              }
        }
    }
    return {};
}

function rowStyleTable(row, index) {
    let iva = row.iva;

    if (iva == 'TOTAL') {
        return {
            classes: 'bg-total bg-iva'
        }
    }
    return {};
}

$('#imprimir').click(function() {
    var desde        = $('#desde').val();

   
    var param = {desde: desde};
    OpenWindowWithPost("imprimir-iva", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirIVA", param);
});


$('#filtro_fecha').change();