var url = 'inc/reporte-aguinaldo-data';
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

    // Mostrar datos en la tabla
    $('#filtro_sucursal').trigger('change');
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
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&periodo=${$('#periodo').val()}` })
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 30);
}



window.accionesFila = {
     'click .ver-detalle': function(e, value, row, index) {

        var sucursal = $("#filtro_sucursal").val();
        var desde = $("#desde").val();
        var hasta = $("#hasta").val();

        $('#modal_detalle').modal('show');
        $('#toolbar_titulo').html(`Funcionario: ${row.funcionario}`);
        $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&id_funcionario='+row.id_funcionario +'&periodo='+ $('#periodo').val()});
        //$('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&id_cliente='+row.id_cliente+'&id_sucursal='+ sucursal+'&desde='+ desde+'&hasta='+ hasta });
    },
    'click .imprimir': function (e, value, row, index) {
        var param = {
            id_funcionario : row.id_funcionario,
            periodo : $('#periodo').val()
        }
        OpenWindowWithPost("imprimir-reporte-aguinaldo-detalle", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirReporteDetalle", param);
    },
}

function iconosFila(value, row, index) {
    return [
        
         //'<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
         '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalle mr-1" title="Detalles"><i class="fas fa-list"></i></button>',
         `<button type="button" onclick="javascript:void(0)" class="btn btn-warning btn-sm imprimir mr-1" title="Imprimir Rendicion"><i class="fas fa-file-invoice"></i></button>`,

    ].join('');
}





/*window.accionesFila = {
    'click .ver-liquidaciones': function(e, value, row, index) {
        $('#modal_liquidaciones').modal('show');
        $('#toolbar_liquidaciones_text').html(`INGRESOS`);
        $('#toolbar_liquidaciones_text_des').html(`DESCUENTOS`);
        $('#tabla_liquidaciones').bootstrapTable('refresh', { url: url+'?q=ver_liquidacion_ingreso&id_liquidacion='+row.id_liquidacion });
        $('#tabla_liquidaciones_des').bootstrapTable('refresh', { url: url+'?q=ver_liquidacion_descuento&id_liquidacion='+row.id_liquidacion });
    }
}*/

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
    sortName: "id_funcionario",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_funcionario', title: 'ID FUNCIONARIO', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ci', title: 'C.I', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, formatter: separadorMiles},
            { field: 'salario_real', title: 'Salario Actual', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles},
            { field: 'fecha_alta', title: 'Fecha Ingreso', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            //{ field: 'fecha_baja', title: 'Salida', align: 'right', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'aguinaldo', title: 'Monto Aguinaldo', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, width: 70},
            //{ field: 'estado', title: 'Estado', align: 'right', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila}
        ]
    ]
})

$("#imprimir").on("click", function (e) {
    var param = {
        periodo       : $('#periodo').val(),
        sucursal      : $('#filtro_sucursal').val(),
    };
      
    OpenWindowWithPost("imprimir-reporte-aguinaldo", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=yes,resizable=yes,width=850,height=650", "imprimirAguinaldos", param);
});

// Detalles

$("#tabla_detalle").bootstrapTable({
   toolbar: '#toolbar_detalle',
    showExport: false,
    search: true,
    showRefresh: false,
    showToggle: false,
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: 480,
    sortName: "sucursal",
    sortOrder: 'asc',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'mes', title: 'Mes', align: 'left', valign: 'middle', sortable: true, visible: true, footerFormatter: bstFooterTextTotal },
            { field: 'ano', title: 'Año', align: 'right', valign: 'middle', sortable: true},
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
        ]
    ]
});




// CALENDARIO GASTOS POR MES
var fechaIni;
var fechaFin;
var meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];


function cba2(start, end) {
    fechaIni = start.format('YYYY');
    fechaFin = end.format('YYYY');
    $('#filtro_fecha_anho span').html(start.format('YYYY'));
    $("#periodo").val(start.format('YYYY'));

    $("#desde_anho").val((fechaIni));
    $("#hasta_anho").val((fechaFin));
}

// Rango: Año actual
cba2(moment().startOf('year'), moment().endOf('year'));



CargarCalendarioAnhos("#filtro_fecha_anho","ocultar_mes_ganancia",'cba2');
//CargarCalendarioAnhos("#filtro_fecha_anio","ocultar_gasto_mes",'cba');


$('#filtro_fecha_anho').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni = picker.startDate.format('YYYY');
    fechaFin = picker.endDate.format('YYYY');
    $("#desde_anho").val(picker.startDate.format('YYYY')); 
    $("#hasta_anho").val(picker.endDate.format('YYYY'));
    $("#periodo").val(picker.endDate.format('YYYY'));


    $('#filtro_fecha_anho').change();
}).on('change', function() {

    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_sucursal=${$(this).val()}&periodo=${$('#periodo').val()}` })
});


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

            $('li[data-range-key='+ocultar+']').parents('.ranges').children('ul').children('li[data-range-key='+anho_actual+']').click()
                setTimeout(() => {
                $("li[data-range-key="+ocultar+"]").remove();
                
            }, 500);
            
        },
        error: function(jqXhr) {
            // alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
}


$("#span_circule_red,#span_circule_amarillo,#span_circule_lila,#span_circule_verde,#span_circule_celeste").val(1);
$('#filtro_fecha').change();
$('#filtro_fecha_sucursal').change();
$('#filtro_fecha_anio').change();
$('#filtro_fecha_anho').change();
$('#filtro_fecha_venci').change();






































