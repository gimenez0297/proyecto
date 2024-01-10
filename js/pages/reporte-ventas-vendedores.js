var url = 'inc/reporte-ventas-vendedores-data.php';
var url_listados = 'inc/listados';

Mousetrap.bindGlobal('f1', (e) => { e.preventDefault(); $('#filtros').click() });

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

$(document).ready(function () {
    /* TABLAS */
        $("#tabla").bootstrapTable({
            url: url + `?q=ver&desde=${$('#desde').val()}&hasta=${$('#hasta').val()}`,
            toolbar: '#toolbar',
            showExport: true,
            search: true,
            showRefresh: true,
            showToggle: false,
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
            sortName: "nombre_vendedor",
            uniqueId: 'id_vendedor',
            sortOrder: 'asc',
            trimOnSearch: false,
            pageList: [1, 5, 30, 50, 100, 'All'],
            pageSize: 30,
            columns: [
                [
                    { field: 'id_vendedor', visible: false },
                    { field: 'id_sucursal', visible: false, switchable:false },
                    { field: 'nombre_vendedor', title: 'VENDEDOR', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
                    { field: 'proveedor_p_nombre', title: 'Proveedor P.', align: 'left', valign: 'middle', sortable: false, visible: false, switchable: false },
                    { field: 'usuario', title: 'USUARIO', align: 'left', valign: 'middle', sortable: true, visible: false },
                    { field: 'sucursal', title: 'SUCURSAL', align: 'left', valign: 'middle', sortable: true, visible: true},
                    { field: 'cantidad_venta', title: 'CANT. VENTAS', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
                    { field: 'venta', title: 'VENTAS', align: 'right', valign: 'middle', sortable: true, formatter:separadorMiles, footerFormatter: bstFooterSumatoria },
                    { field: 'costo', title: 'COSTO', align: 'right', valign: 'middle', sortable: true, formatter:separadorMiles, footerFormatter: bstFooterSumatoria },
                    { field: 'utilidad', title: 'UTILIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
                    { field: 'margen', title: 'MARGEN', align: 'right', valign: 'middle', sortable: true, formatter: bstDosDecimalesPorcentaje, footerFormatter: calcMargen },
                    { field: 'participacion', title: 'PARTICIPACIÓN', align: 'right', valign: 'middle', sortable: true, formatter: bstDosDecimalesPorcentaje, footerFormatter: bstFooterSumatoriaRoundPorcent },
                    { field: 'acciones', title: 'DETALLES', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 100, switchable:false}
                ]
            ]
        }).on('load-success.bs.table', function (e, rows, stat, head) {
            if (rows.total > 0) {
                if(rows.rows[0].proveedor_p_nombre != undefined){
                    $('#tabla').bootstrapTable('showColumn', 'proveedor_p_nombre');
                }else{
                    $('#tabla').bootstrapTable('hideColumn', 'proveedor_p_nombre');
                }
            }else{
                $('#tabla').bootstrapTable('hideColumn', 'proveedor_p_nombre');
            }
        });

        $("#tabla_detalle").bootstrapTable({
            toolbar: '#toolbar_detalle',
            showExport: true,
            search: true,
            showRefresh: true,
            showFullscreen: true,
            showToggle: false,
            showColumns: true,
            pagination: 'true',
            sidePagination: 'server',
            classes: 'table table-hover table-condensed-sm',
            striped: true,
            icons: 'icons',
            mobileResponsive: false,
            showFooter: true,
            height: 500,
            sortName: "v.id_funcionario",
            sortOrder: 'asc',
            trimOnSearch: false,
            footerStyle: bstFooterStyle,
            pageList: [5, 25, 50, 100, 200, 'All'],
            pageSize: 100,
            columns: [
                [
                    { field: 'id_vendedor', visible: false },
                    { field: 'fecha_venta', title: 'FECHA', align: 'left', valign: 'middle', sortable: true },
                    { field: 'nombre_vendedor', title: 'VENDEDOR', align: 'left', valign: 'middle', visible: false, sortable: false, footerFormatter: bstFooterTextTotal },
                    { field: 'numero', title: 'N° FACTURA', align: 'left', valign: 'middle', sortable: true, visible: false },
                    { field: 'codigo', title: 'CÓDIGO', align: 'left', valign: 'middle', sortable: true },
                    { field: 'producto', title: 'PRODUCTO', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
                    { field: 'id_lote', visible: false, swichable: false},
                    { field: 'lote', title: 'LOTE', align: 'left', valign: 'middle', sortable: true },
                    { field: 'precio', title: 'PRECIO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles },
                    { field: 'cantidad', title: 'CANTIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles,  footerFormatter: bstFooterSumatoria },
                    { field: 'descuento_porc', title: 'DESCUENTO %', align: 'right', valign: 'middle', sortable: true, formatter: bstFormatterPorcentaje },
                    { field: 'total_venta', title: 'SUBTOTAL', align: 'right', valign: 'middle', sortable: true, formatter:separadorMiles, footerFormatter: bstFooterSumatoria },
                    { field: 'total_costo', title: 'COSTO', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
                    { field: 'utilidad', title: 'UTILIDAD', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
                    { field: 'margen', title: 'MARGEN', align: 'right', valign: 'middle', sortable: true, formatter: bstDosDecimalesPorcentaje },
                    { field: 'fraccionado', title: 'FRACCIÓN', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },

                ]
            ]
        });
    /**/

    if (window.matchMedia("(max-width: 1123px)").matches) {
        setTimeout(() => {
            $('.contenedor_dinamico').css('padding-top', $('.topbar').height());
        }, 1000);
    }
    $(window).resize(function () {
        //iguala el padding top del contenedor al tamaño del header
        $('.contenedor_dinamico').css('padding-top', $('.topbar').height());
    });
});

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
        [meses[moment().subtract(5, 'month').format("M")]] : [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')],
        'Todos': [moment('0001-01-01'), moment()],
    }
}, cb);

$('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) {
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#desde").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha').change();
});

$('#filtro_sucursal').select2({
    placeholder: 'Sucursales',
    dropdownParent: '#modal',
    width: '100%',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url,
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
                results: $.map(data, function (obj) { return { id: obj.id_sucursal, text: obj.sucursal }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function (e) {
    if (!empty($(this).val()) && !empty($('#filtro_vendedor').val())) {
        $('#filtro_vendedor').val(null).trigger('change');
    }
});

$('#filtro_vendedor').select2({
    placeholder: 'Vendedores',
    dropdownParent: '#modal',
    width: '100%',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'vendedores', id_sucursal: ($('#filtro_sucursal').val() ? $('#filtro_sucursal').val() : 0), term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data, function (obj) { return { id: obj.id_funcionario, text: obj.nombre_apellido }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#filtro_proveedor').select2({
    placeholder: 'proveedores',
    dropdownParent: '#modal',
    width: '100%',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function (params) {
            return { q: 'proveedores', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data, function (obj) { return { id: obj.id_proveedor, text: obj.proveedor }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#filtros').click(function() {
    $('#modalLabel').html('Filtros');
    setTimeout(function(){
        $("#fecha_desde").focus();
        $('#tipo_imp').val(1);
    }, 200);
});


/**/
    // BUSQUEDA DE PRODUCTOS
    function formatResult(node) {
        let $result = node.text;
        if (node.loading !== true && node.id_presentacion) {
            $result = $(`<span>${node.text} <small>(${node.presentacion})</small></span>`);
        }
        return $result;
    };

    $('#id_producto').select2({
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
            data: function(params) {
                return { q: 'productos', term: params.term, page: params.page || 1 }
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: $.map(data.data, function(obj) {
                        return {
                            id: obj.id_producto,
                            text: obj.producto,
                            codigo: obj.codigo,
                            id_presentacion: obj.id_presentacion,
                            presentacion: obj.presentacion
                        };
                    }),
                    pagination: { more: (params.page * 5) <= data.total_count }
                };
            },
            cache: false
        },
        templateResult: formatResult,
        templateSelection: formatResult
    });
/**/

window.accionesFila = {
    'click .ver-detalles': function(e, value, row, index) {

        let proveedor = $("#filtro_proveedor").val()?$("#filtro_proveedor").val():0;
        let desde = $("#desde").val()?$("#desde").val():0;
        let hasta = $("#hasta").val()?$("#hasta").val():0;
        let row_vendedor = row.id_vendedor ? row.id_vendedor : 0;

        $('#modal_detalle').modal('show');
        setTimeout(() => {
            $('#toolbar_titulo').html(`Vendedor: ${row.nombre_vendedor}`);
            $('#tabla_detalle').bootstrapTable('refresh', { url: url+'?q=ver_detalle&vendedor='+row_vendedor+'&desde='+ desde+'&hasta='+ hasta + '&proveedor=' + proveedor});
            
        }, 200);
    }
}

function iconosFila(value, row, index) {

    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver-detalles mr-1" title="Detalles"><i class="fas fa-list"></i></button>',
    ].join('');
}

/**
 * Suma numeros enteros y previene la utilizacion de separadores de miles
 * @param {*} data
 */
function bstFooterSumatoria (data) {
    let field = this.field;
    var total = separadorMiles(data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current[field])), 0));
	return `<span class="f16">${total}</span>`;
}

/**
 * Sumatoria decimal redondeada
 * @param {*} data 
 */
function bstFooterSumatoriaRound(data) {
    let field = this.field;
    var total =
      data.reduce((acc, current) => {
        acc += parseFloat(bstDosDecimales(current[field]));
        return acc;
      }, 0);
    return `<span class="f16">${Math.floor(total)}</span>`;
}

/**
 * Sumatoria decimal redondeada con simbolo de porcentaje al final
 * @param {*} data 
 */
function bstFooterSumatoriaRoundPorcent(data) {
    let field = this.field;
    var total =
      data.reduce((acc, current) => {
        acc += parseFloat(bstDosDecimales(current[field]));
        return acc;
      }, 0)
    ;
    return `<span class="f16">${Math.floor(bstDosDecimales(total))}%</span>`;
}
  
/**
 * Toma un numero y fija sus decimales a 2
 * @param {*} numero
 */
function bstDosDecimales (numero) {
    let value = parseFloat(numero);
    return `${value.toFixed(2)}`;
}

/**
 * Toma un numero, fija sus decimales a 2 y le agrega un simbolo de porcentaje
 * @param {*} numero
 */
function bstDosDecimalesPorcentaje (numero) {
    let value = parseFloat(numero);
	return `${value.toFixed(2)}%`;
}

function calcMargen(data) {
    let total_ventas = parseFloat(data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current['venta'])), 0));
    let total_utilidad = parseFloat(data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current['utilidad'])), 0));
    let margen_t = 0;
    
    if (total_ventas != 0) {
        margen_t = bstDosDecimales(((total_utilidad/total_ventas)*100));
    }

	return `<span class="f16">${margen_t}%</span>`;    
}

$('#imprimir').click(function(e) {
    e.preventDefault();
    sweetAlertConfirm({
        title: '¿Desea tener en cuenta los filtros?',
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        confirm: () => {
            let proveedor       = $('#filtro_proveedor').val()?$('#filtro_proveedor').val():0;
            let id_sucursal     = $('#filtro_sucursal').val()?$('#filtro_sucursal').val():0;
            let vendedor        = $('#filtro_vendedor').val()?$('#filtro_vendedor').val():0;
            let desde           = !empty($('#desde').val())?$('#desde').val():0;
            let hasta           = !empty($('#hasta').val())?$('#hasta').val():0;
                
            let param = {sucursal: id_sucursal, desde: desde, hasta: hasta, proveedor:proveedor, vendedor: vendedor };
        
            OpenWindowWithPost("imprimir-reporte-ventas-vendedores", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ReporteVentasPorVendedor", param);
        },
        cancel: () => {
            let param = {sucursal: 0, desde: 0, hasta: 0, proveedor:0, vendedor: 0 };
        
            OpenWindowWithPost("imprimir-reporte-ventas-vendedores", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ReporteVentasPorVendedor", param);
        },
    });
});

async function exportt(params) {
    return new Promise(resolve => {
      const ventanaDescarga = window.open('exportar-reporte-ventas-vendedores.php?'+$.param(params),  '_blank');
      ventanaDescarga.document.title = "Creando archivo...";
      const temporizador = setInterval(() => {
        if (ventanaDescarga.closed) {
          clearInterval(temporizador);
          resolve(true);
        }
      }, 1000); // Comprueba si la ventana se ha cerrado cada segundo
    });
}

$('#exportar').click(function(e) {
    e.preventDefault();
    sweetAlertConfirm({
        title: '¿Desea tener en cuenta los filtros?',
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        confirm: async () => {
            let proveedor       = $('#filtro_proveedor').val()?$('#filtro_proveedor').val():0;
            let id_sucursal     = $('#filtro_sucursal').val()?$('#filtro_sucursal').val():0;
            let vendedor        = $('#filtro_vendedor').val()?$('#filtro_vendedor').val():0;
            let desde           = !empty($('#desde').val())?$('#desde').val():0;
            let hasta           = !empty($('#hasta').val())?$('#hasta').val():0;
                
            let param = {sucursal: id_sucursal, desde: desde, hasta: hasta, proveedor:proveedor, vendedor: vendedor };
            
            NProgress.start();
            loading({text: 'Creando Archivo...'});
            $('#exportar').prop('disabled', true).addClass('disabled');

            const resultado = await exportt(param);
            
            NProgress.done();
            Swal.close();
            if (resultado) {
                alertToast("Archivo Creado", "ok", 2000)
            }
            $(this).removeAttr('disabled').removeClass('disabled');
            
        },
        cancel: async () => {
            let param = {sucursal: 0, desde: 0, hasta: 0, proveedor:0, vendedor: 0 };
            
            NProgress.start();
            loading({text: 'Creando Archivo...'});
            $('#exportar').prop('disabled', true).addClass('disabled');

            const resultado = await exportt(param);
            
            NProgress.done();
            Swal.close();
            if (resultado) {
                alertToast("Archivo Creado", "ok", 2000)
            }
            $(this).removeAttr('disabled').removeClass('disabled');
        },
    });
});

$('#filtrar').click(function() {
    let proveedor       = $('#filtro_proveedor').val()?$('#filtro_proveedor').val():0;
    let id_sucursal     = $('#filtro_sucursal').val()?$('#filtro_sucursal').val():0;
    let vendedor        = $('#filtro_vendedor').val()?$('#filtro_vendedor').val():0;
    let desde           = $('#desde').val()?$('#desde').val():0;
    let hasta           = $('#hasta').val()?$('#hasta').val():0;

    $('#tabla').bootstrapTable("refresh",{url: url + '?q=ver&desde=' + desde + '&hasta=' + hasta + '&sucursal=' + id_sucursal + '&proveedor=' + proveedor + '&vendedor=' + vendedor});

    $('#modal').modal('hide');
});

$('#filtro_fecha').change();
