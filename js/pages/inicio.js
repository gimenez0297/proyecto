
// var socketIO = io("http://591e-186-2-200-48.ngrok.io");
var url = 'inc/inicio-data';
var url_listados = 'inc/listados';


$("#tabla").bootstrapTable({
    //toolbar: '#toolbar',
    showExport: false,
    search: false,
    showRefresh: false,
    showToggle: false,
    showColumns: false,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    showFullscreen: false,
    mobileResponsive: true,
    height: 500,
    pageSize: 18,
    sortName: "l.vencimiento",
    sortOrder: 'ASC',
    trimOnSearch: false,
    rowStyle: pintarRows,
    columns: [
        [
            { field: 'id_lote', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false, switchable:false },
            { field: 'lote', title: 'Lote', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'producto', title: 'Producto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'laboratorio', title: 'Laboratorio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true },
            { field: 'origen', title: 'Origen', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'presentacion', title: 'Presentación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'principio_activo', title: 'Principio', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'clasificacion', title: 'Clasificación', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'unidad_medida', title: 'Medida', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'rubro', title: 'Rubro', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'vencimiento_lote', title: 'Vto. Lote', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'estado_lote', title: 'Estado Lote', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
            { field: 'vencimiento_canje', title: 'Vto. Canje', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna  },
            { field: 'estado_canje', title: 'Estado Canje', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado  },
            { field: 'entero', title: 'Entero', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'fracc', title: 'Fraccionado', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria }
        ]
    ]
});

function pintarRows(row, index) {
    var dia = parseInt(row.vencimiento_dia);
    //CANTIDAD DE PRODUCTOS PARA PONER EN EL TEXT
    var _3_dias     = parseInt(row._3_dias);
    var _4_dias     = parseInt(row._4_dias);
    var _5_dias     = parseInt(row._5_dias);
    var _7_dias     = parseInt(row._7_dias);
    var _30_dias    = parseInt(row._30_dias);
    //CANTIDAD DE PRODUCTOS PARA PONER EN EL TEXT

    if($("#span_circule_red").val() == 1 && dia == 3){
        $("#span_circule_red b").html('VENCE EN 3 DÍAS ('+_3_dias+' prod.)');
        return { classes: 'bg-row-red' };
    }  
    if($("#span_circule_amarillo").val() == 1 && dia == 4){
        $("#span_circule_amarillo b").html('VENCE EN 4 DÍAS ('+_4_dias+' prod.)');
        return { classes: 'bg-row-amarillo' };
    }  
    if($("#span_circule_verde").val() == 1 && dia == 7){
        $("#span_circule_verde b").html('VENCE EN 7 DÍAS ('+_5_dias+' prod.)');
        return { classes: 'bg-row-verde' };
    }  
    if($("#span_circule_lila").val() == 1 && dia == 5){
        $("#span_circule_lila b").html('VENCE EN 5 DÍAS ('+_7_dias+' prod.)');
        return { classes: 'bg-row-lila' };
    } 
    if($("#span_circule_celeste").val() == 1 && dia == 30){
        $("#span_circule_celeste b").html('VENCE EN 30 DÍAS ('+_30_dias+' prod.)');
        return { classes: 'bg-row-celeste' };
    }  

    return {};
}

// var userId = $("#id_user").val();
// socketIO.emit("connected", userId);
// datosUsuario(userId);
// socketIO.on("notificacionDatos", function(data) {
//     // console.log(data);
//     datosUsuario(data)
// });

// CALENDARIO 5 PRODUCTOS MAS VENDIDOS
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
        monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
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

function cb3(start, end) {
    fechaIni = start.format('DD/MM/YYYY');
    fechaFin = end.format('DD/MM/YYYY');
    $('#filtro_fecha_venci span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#desde_venc").val(fechaMYSQL(fechaIni));
    $("#hasta_venc").val(fechaMYSQL(fechaFin));
}
cb3(moment(), moment().subtract(-30, 'day'));
$('#filtro_fecha_venci').daterangepicker({
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
        monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
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
}, cb3);

$('#filtro_fecha_venci').on('apply.daterangepicker', function(ev, picker) {
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#desde_venc").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta_venc").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha_venci').change();
}).on('change', function() {
   let desde = $('#desde_venc').val();
   let hasta = $('#hasta_venc').val(); 
   
   $('#tabla').bootstrapTable("refresh",{url: url + '?q=ver_productos_vencer&desde=' + desde + '&hasta=' + hasta});
});

$('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) {
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#desde").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha').change();
}).on('change', function() {
   let desde = $('#desde').val();
   let hasta = $('#hasta').val();
   productosMasVendidos(desde,hasta);
   
});
//FIN

// CALENDARIO VENTAS POR SUCURSAL
var fechaIniS;
var fechaFinS;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cbs(start, end) {
    fechaIniS = start.format('DD/MM/YYYY');
    fechaFinS = end.format('DD/MM/YYYY');
    $('#filtro_fecha_sucursal span').html(start.format('DD/MM/YYYY') + ' al ' + end.format('DD/MM/YYYY'));

    $("#desde_sucursal").val(fechaMYSQL(fechaIniS));
    $("#hasta_sucursal").val(fechaMYSQL(fechaFinS));
}

// Rango: Últimos 30 Días
cbs(moment().subtract(29, 'days'), moment());

$('#filtro_fecha_sucursal').daterangepicker({
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
        monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
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
}, cbs);

$('#filtro_fecha_sucursal').on('apply.daterangepicker', function(ev, picker) {
    fechaIniS = picker.startDate.format('DD/MM/YYYY');
    fechaFinS = picker.endDate.format('DD/MM/YYYY');
    $("#desde_sucursal").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta_sucursal").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha_sucursal').change();
}).on('change', function() {
   let desde = $('#desde_sucursal').val();
   let hasta = $('#hasta_sucursal').val();
   ventasPorSucursal(desde,hasta)
});
//FIN

// CALENDARIO GASTOS POR MES
var fechaIni;
var fechaFin;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

function cba(start, end) {
    fechaIni = start.format('YYYY');
    fechaFin = end.format('YYYY');
    $('#filtro_fecha_anio span').html(start.format('YYYY'));

    $("#desde_anio").val((fechaIni));
    $("#hasta_anio").val((fechaFin));
}
function cba2(start, end) {
    fechaIni = start.format('YYYY');
    fechaFin = end.format('YYYY');
    $('#filtro_fecha_anho span').html(start.format('YYYY'));

    $("#desde_anho").val((fechaIni));
    $("#hasta_anho").val((fechaFin));
}

// Rango: Año actual
cba(moment().startOf('year'), moment().endOf('year'));
cba2(moment().startOf('year'), moment().endOf('year'));

// Rango: dia actual
// cb(moment(), moment());

/*$('#filtro_fecha_anio').daterangepicker({
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
}, cba);*/

CargarCalendarioAnhos("#filtro_fecha_anho","ocultar_mes_ganancia",'cba2');
CargarCalendarioAnhos("#filtro_fecha_anio","ocultar_gasto_mes",'cba');

$('#filtro_fecha_anio').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni = picker.startDate.format('YYYY');
    fechaFin = picker.endDate.format('YYYY');
    $("#desde_anio").val(picker.startDate.format('YYYY')); 
    $("#hasta_anio").val(picker.endDate.format('YYYY'));

    $('#filtro_fecha_anio').change();
}).on('change', function() {
    let anio = $('#desde_anio').val();
    gastosPorMes(anio);
});

$('#filtro_fecha_anho').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni = picker.startDate.format('YYYY');
    fechaFin = picker.endDate.format('YYYY');
    $("#desde_anho").val(picker.startDate.format('YYYY')); 
    $("#hasta_anho").val(picker.endDate.format('YYYY'));

    $('#filtro_fecha_anho').change();
}).on('change', function() {
    let anho = $('#desde_anho').val();
    gananciasPorMes(anho);
});


function datosUsuario(id) {
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'usuario', id: id },
        beforeSend: function() {
            // NProgress.start();
        },
        success: function(data, status, xhr) {
            // NProgress.done();
            // alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
            	var usuario= data.usuario;
            	//$("#telefono_usuario").html(usuario.telefono);
            //     $('#modal').modal('hide');
            //     $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr) {
            // alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}

function productosMasVendidos(desde,hasta){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'productos_mas_vendidos', desde:desde, hasta:hasta},
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, status, xhr) {
            NProgress.done();

            if (data.status == "error") {
                alertDismissJS(data.mensaje, data.status);
                return;
            }
           
            graficoProductosMasVendidos(data, desde, hasta)
        },
        error: function(jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}

function ventasPorSucursal(desde,hasta){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'ventas_sucursal', desde:desde, hasta:hasta},
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, status, xhr) {
            NProgress.done();
            graficoVentasSucursal(data, desde, hasta)
        },
        error: function(jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}

function gastosPorMes(anio){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'gastos_meses', anio:anio},
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, status, xhr) {
            NProgress.done();
            graficoGastosPorMes(data, anio);
        },
        error: function(jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}

function graficoProductosMasVendidos(data, desde, hasta){
    
    var nombres = data.map(function(productos){return productos.producto});
    var cantidades = data.map(function(productos){return Number(productos.cantidad)});

    var fraccionado = data.map(function(productos){return Number(productos.fraccionado)});
    var entero      = data.map(function(productos){return Number(productos.entero)});

    Highcharts.chart('container', {
        chart: {
            type: 'column'
        },
        title: {
            text: '5 Productos mas vendidos desde '+fechaLatina(desde)+' hasta '+fechaLatina(hasta)
        },
        xAxis: {
            categories: nombres
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Cantidad'
            },
            stackLabels: {
                enabled: true,
                style: {
                    fontWeight: 'bold',
                    color: '#00a7b0' || 'gray',
                    textOutline: 'none'
                }
            }
        },
        legend: {
            align: 'left',
            x: 200,
            verticalAlign: 'top',
            y: 18,
            floating: true,
            backgroundColor:
                Highcharts.defaultOptions.legend.backgroundColor || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: [{
            name: 'Entero',
            data: entero
        },{
            name: 'Fraccionado',
            data: fraccionado,
            color: '#90ed7d'
        }]
    });
              
}
/*
function graficoProductosMasVendidos(data, desde, hasta){
    
    var nombres = data.map(function(productos){return productos.producto});
    var cantidades = data.map(function(productos){return Number(productos.cantidad)});

    //var fraccionado = data.map(function(productos){return Number(productos.fraccionado)});
    var entero      = data.map(function(productos){return Number(productos.entero)});

    Highcharts.chart('container', {
        chart: {
            type: 'column'
        },
        title: {
            text: '5 Productos mas vendidos desde '+fechaLatina(desde)+' hasta '+fechaLatina(hasta)
        },
        credits: {
            enabled: false
        },
        yAxis: {
            title: {
                text: 'Cantidad'
            }
        },
        xAxis: {
            categories: nombres
        },
        legend:"null",
        tooltip:"null",
        series: [{
            data: cantidades,
            colorByPoint: true
        }],
        tooltip: {
            pointFormat: 'Cantidad: <b>{point.categories}</b><br>'
        },
        
    });
}*/

function gananciasPorMes(anho){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'ganancias_meses', anho:anho},
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, status, xhr) {
            NProgress.done();
            graficoGananciasPorMes(data, anho);
        },
        error: function(jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}

function graficoVentasSucursal(data, desde, hasta){

    var sucursales = data.map(function(sucursal){return sucursal.sucursal});
    var ventas = data.map(function(sucursal){return Number(sucursal.venta)});

    Highcharts.chart('container1', {
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Ventas Por Sucursal desde '+fechaLatina(desde)+' hasta '+fechaLatina(hasta),
        },
        xAxis: {
            categories: sucursales,
        },
        yAxis: {
            title: {
                text: 'Millones'
            }
        },
        tooltip: {
            valueSuffix: 'millones'
        },
        legend:"null",
        credits: {
            enabled: false
        },
        series: [{
            data:ventas,
            colorByPoint: true,
        }],
        tooltip: {
            pointFormat: '<b>{point.y} millones</b><br>'
        },
    });
}


function graficoGastosPorMes(data, anio){
    Highcharts.chart('container2', {
    
        title: {
            text: 'Gastos Por Meses del Año '+anio,
        },
    
        yAxis: {
            title: {
                text: 'Monto'
            }
        },
        xAxis: {
            categories: [
                'Ene',
                'Feb',
                'Mar',
                'Abr',
                'May',
                'Jun',
                'Jul',
                'Ago',
                'Sep',
                'Oct',
                'Nov',
                'Dic'
            ],
            crosshair: true
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },
    
        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },
            }
        },
        credits: {
            enabled: false
        },
        series: data,
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }
    
    });
}

function graficoGananciasPorMes(data, anho){
    Highcharts.chart('container3', {
    
        title: {
            text: 'Ganancias Por Meses del Año '+anho,
        },
    
        yAxis: {
            title: {
                text: 'Monto'
            }
        },
        xAxis: {
            categories: [
                'Ene',
                'Feb',
                'Mar',
                'Abr',
                'May',
                'Jun',
                'Jul',
                'Ago',
                'Sep',
                'Oct',
                'Nov',
                'Dic'
            ],
            crosshair: true
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },
    
        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },
            }
        },
        credits: {
            enabled: false
        },
        series: data,
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }
    
    });
}

function CargarCalendarioAnhos(id = '', ocultar = 'ocultar_str', func = 'cba2') {
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'recuperar_calendario' },
        beforeSend: function() {
            // NProgress.start();
        },
        success: function(data, status, xhr) {
            var calendario = data.calendario;
            var primer_anho = calendario.minimo_anho;
            var ultimo_anho = calendario.maximo_anho;
            var anho_actual = calendario.maximo_anho;
            var cantidad = calendario.fechas;

            var anhos_obj = {[ultimo_anho]: [moment([ultimo_anho, 3, 1]), moment([ultimo_anho, 3, 1])]};
            for (let i = 1; i <= cantidad; i++) {
                ultimo_anho = ultimo_anho-1;
                if(ultimo_anho < primer_anho){
                    break;
                }
                anhos_obj[ultimo_anho] = [moment([ultimo_anho, 3, 1]), moment([ultimo_anho, 3, 1])];
               
            }
            
            $(id).daterangepicker({
                //singleDatePicker: false,
                //showDropdowns: false,
                opens: "right",
                format: 'YYYY',
                locale: {
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Borrar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: ocultar,
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi','Sa'],
                    monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"],
                    firstDay: 1
                },
                ranges: anhos_obj
            }, func);
            setTimeout(() => {
                $('li[data-range-key='+ocultar+']').parents('.ranges').children('ul').children('li[data-range-key='+anho_actual+']').click()
                $("li[data-range-key="+ocultar+"]").remove();
            }, 1000);
            
        },
        error: function(jqXhr) {
            // alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}

$(".point_select").click(function() {
    if($(this).val() != 1){
        $(this).val(1);
        $(this).addClass('active');
    }else{
        $(this).val(0);
        $(this).removeClass('active');
    }
    $('#filtro_fecha_venci').change();
});

$("#span_circule_red,#span_circule_amarillo,#span_circule_lila,#span_circule_verde,#span_circule_celeste").val(1);
$('#filtro_fecha').change();
$('#filtro_fecha_sucursal').change();
$('#filtro_fecha_anio').change();
$('#filtro_fecha_anho').change();
$('#filtro_fecha_venci').change();

//HOVER VENCIMIENTOS
$('#span_circule_red,#span_circule_amarillo,#span_circule_lila,#span_circule_verde,#span_circule_celeste').hover(function(e){
    $('#tabla tbody tr').css({
      "opacity": "0.5"
    });

    var color = (e.target.id).split('_')[2];
    if((e.target.id).split('_')[2] == undefined) color = (e.currentTarget.id).split('_')[2];

    $('#tabla tbody tr.bg-row-'+color).css({
        "opacity": "2"
    });
},function(e){//CUAL YA NO ESTA ENCIMA
    $('#tabla tbody tr').css({
        "opacity": ""
    });

    var color = (e.target.id).split('_')[2];
    if((e.target.id).split('_')[2] == undefined) color = (e.currentTarget.id).split('_')[2];
    $('#tabla tbody tr.bg-row-'+color).css({
        "opacity": ""
    });
});
//HOVER VENCIMIENTOS
