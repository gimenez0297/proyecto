var url = 'inc/liquidacion-salarios-data';
var url_funcionarios = 'inc/funcionarios-data';
var url_listados = 'inc/listados';

resetWindow();

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla_ingresos").bootstrapTable('refreshOptions', { 
                height: (($(window).height()-430) > 400) ? $(window).height()-430 : 300,
            });
            $("#tabla_descuento").bootstrapTable('refreshOptions', { 
                height: (($(window).height()-430) > 400) ? $(window).height()-430 : 300,
            });
        }, 100);
    });
});

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 - Buscar proveedor
        case 112:
            event.preventDefault();
            $('#btn_buscar').click();
            break;
        // F2 - Focus campo código
        case 113:
            event.preventDefault();
            $('#codigo').focus();
            break;
        // F3 - Focus select productos
        case 114:
            event.preventDefault();
            $('#producto').focus();
            break;
        // F4 - Generar solicitud
        case 115:
            event.preventDefault();
            $('#btn_guardar').click();
            break;
    }
});

function iconosFilaIngreso(value, row, index) {
    let disabled = (row.concepto == "NORMAL") ? 'style="visibility: hidden"' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar" ${disabled}><i class="fas fa-trash"></i></button>`,
    ].join('');
}

function iconosFilaDescuento(value, row, index) {
    let disabled = (row.id_descuento > 0) ? 'style="visibility: hidden"' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_desc" title="Eliminar" ${disabled}><i class="fas fa-trash"></i></button>`,
    ].join('&nbsp');
}

window.accionesFilaIngresos = {
    'click .eliminar': function (e, value, row, index) {
        var nombre = row.concepto;
        sweetAlertConfirm({
            title: `Eliminar Concepto`,
            text: `¿Eliminar Concepto: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_ingresos').bootstrapTable('removeByUniqueId', row.id_);
                neto_final();
            }
        });

    }
}

window.accionesFilaDescuentos = {
    'click .eliminar_desc': function (e, value, row, index) {
        var nombre = row.concepto;
        sweetAlertConfirm({
            title: `Eliminar Concepto`,
            text: `¿Eliminar Concepto: ${nombre}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_descuento').bootstrapTable('removeByUniqueId', row.id_);
                neto_final();
            }
        });
        
    }
}

$("#tabla_ingresos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
	height: $(window).height()-430,
    sortName: "codigo",
    sortOrder: '',
    uniqueId: 'id_',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_', title: 'ID', visible: false },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal  },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna},
            { field: 'importe', title: 'Importe', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaIngresos, formatter: iconosFilaIngreso, width: 100 },
            { field: 'tipo', visible: false}
        ]
    ]
});

$("#tabla_descuento").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
    height: $(window).height()-430,
    sortName: "codigo",
    sortOrder: '',
    uniqueId: 'id_',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'id_', title: 'ID', visible: false },
            { field: 'concepto', title: 'Concepto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna, footerFormatter: bstFooterTextTotal },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'importe', title: 'Importe', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaDescuentos, formatter: iconosFilaDescuento, width: 100 },
            { field: 'tipo', visible: false},
            { field: 'id_descuento', visible: false}
        ]
    ]
});

$('#agregar_ingreso').on('click', function() {
    resetValidateForm("#form_ingreso");
    $("#concepto").val('');
    $("#importe").val('');
    $("#observacion").val('');
});

$('#agregar_descuento').on('click', function() {
    resetValidateForm("#form_descuento");
    $("#concepto_des").val('');
    $("#importe_des").val('');
    $("#observacion_des").val('');
});

// Buscar funcionario
$('#ci').blur(function() {
    var ci = $(this).val();
    var data = {};
    if (ci) {
        var buscar_funcionario = buscarFuncionario(ci);
        if (buscar_funcionario.ci) {
            data = buscar_funcionario;
        } else {
            data = {
                ci,
                id_funcionario: '',
                funcionario: ''
            }
            alertDismissJS('Funcionario no encontrado', 'error');
        }
        cargarDatosFuncionario(data);
    }
});

$("#tabla_funcionarios").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_funcionarios',
    search: true,
    showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    buttonsAlign: 'right',
    toolbarAlign: 'left',
    pagination: 'true',
    sidePagination: 'server',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 480,
    pageSize: 15,
    sortName: "razon_social",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_funcionario', title: 'ID Funcionario', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'puesto', title: 'Puesto', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
           // { field: 'obs', title: 'OBSERVACIONES', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#modal_funcionarios').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    let index_funcionarios = -1;

    $('#tabla_funcionarios').bootstrapTable("refresh", {url: url_funcionarios+`?q=ver`}).bootstrapTable('resetSearch', '');

    // NAVEGACION POR TECLADO ENTRE REGISTROS EN TABLA DE PRESUPUESTOS
    $(this).off('keydown').on('keydown', function(e) {
        switch(e.which) {
            case 13: // <ENTER>
                let seleccionado = $('#tabla_funcionarios').bootstrapTable('getSelections');
                if (seleccionado.length > 0) {
                    cargarDatosFuncionario(seleccionado[0]);
                }
            break;
            case 40: // <ARROW UP>
                if ((index_funcionarios + 1) < $('#tabla_funcionarios').bootstrapTable('getData').length) index_funcionarios++;
            break;
            case 38: // <ARROW DOWN>
                if (index_funcionarios > 0) index_funcionarios--;
            break;
            case 27: // <ESC>
                $(this).modal('hide');
            break;
        }
        if (e.target.type != 'search') {
            $('#tabla_funcionarios').bootstrapTable('check', index_funcionarios);
        }
    });
}).on('hidden.bs.modal', function(){
    setTimeout(function(){
        $("#periodo").focus();
    }, 1);
    
});

$('#tabla_funcionarios').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosFuncionario(row);
});

function cargarDatosFuncionario(data) {
    if (data.id_funcionario > 0) {
        $('#funcionario').val(data.razon_social);
        $('#ci').val(data.ci);
    }

    var aporte = 0;

    if(data.aporte == 1){
        var aporte = 0.09;

    }
    //$('#funcionario').val(data.razon_social);
    $('#neto').val(separadorMiles(data.salario_real - (data.salario_real*aporte)));
    $('#id_funcionario').val(data.id_funcionario);
    $("#modal_funcionarios").modal("hide");
    $('#nro_cuenta').val(data.nro_cuenta);

    $('#tabla_ingresos').bootstrapTable('removeAll');
    $('#tabla_descuento').bootstrapTable('removeAll');

    $('#tabla_ingresos').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA,
            concepto: 'NORMAL',
            importe: data.salario_real,
            observacion: '',
            tipo:'ins', 
        }
    });

    if(data.cantidad_hijos > 0){
        $('#tabla_ingresos').bootstrapTable('insertRow', {
        index: 1,
        row: {
            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA,
            concepto: 'BONIFICACIÓN FAMILIAR',
            importe: parseInt(data.salario_real*0.05*data.cantidad_hijos),
            observacion: '',
            tipo:'ins', 
        }
    });
    }

    if(data.aporte == 1){
        $('#tabla_descuento').bootstrapTable('insertRow', {
            index: 1,
            row: {
                id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA,
                concepto: 'I.P.S 9%',
                importe: parseInt(data.salario_real* 0.09),
                observacion: '',
                tipo:'des',
                id_descuento: 1,
            }
        });
    }

    if ($('#periodo').val() != null && $('#periodo').val() != '') {    
        $('#periodo').val(null).trigger('change');
    }

    setTimeout(() => {
        if (data.id) {
            $('#periodo').focus().select();
        } 
    }, 1);
    
}
// Fin buscar proveedor

$('#formulario_liq').submit(function(e) {
    e.preventDefault();
    var funcionario = $("#id_funcionario").val();

    if ($('#id_funcionario').val() == '') {
        //alertDismissJS('Ningún producto agregado. Favor verifique.', "error");
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if ($('#tabla_ingresos').bootstrapTable('getData').length == 0) {
        alertDismissJS('Ningún ingreso ha sido cargado. Favor verifique.', "error");
        return false;
    }

    if ($(this).valid() === false) return false;

    sweetAlertConfirm({
        title: `¿Guardar Liquidación de Salarios?`,
        confirmButtonText: 'Guardar',
        closeOnConfirm: false,
        confirm: () => {
            var data = $(this).serializeArray();
            data.push({ name: 'ingresos', value: JSON.stringify($('#tabla_ingresos').bootstrapTable('getData')) });
            data.push({ name: 'descuentos', value: JSON.stringify($('#tabla_descuento').bootstrapTable('getData')) });
            $.ajax({
                url: url + '?q=cargar',
                dataType: 'json',
                type: 'post',
                contentType: 'application/x-www-form-urlencoded',
                data: data,
                beforeSend: function(){
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    if (data.status == 'ok') {
                        $('#tabla_ingresos').bootstrapTable('scrollTo', 'top');
                        resetWindow();
                    }else{
                        alertDismissJS(data.mensaje, data.status);
                    }

                    alertDismissJS(data.mensaje, data.status, function(){
                        var param = { id_liquidacion: data.id_liquidacion, id_funcionario: funcionario};
                        OpenWindowWithPost("imprimir-liquidaciones.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirLiquidaciones", param);
                        resetWindow();
                      $('#ruc').focus();
                    });
                },
                error: function (xhr) {
                    NProgress.done();
                    alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
                }
            });
        }
    });
});

function resetWindow() {
    $("#id_funcionario, #ci, #funcionario").val('');
    $("#neto").val(0);
    $("#forma").val('1').trigger('change');
    $("#cuenta").val('');
    $("#cheque").val('');
    $(".cheques").addClass('d-none');
    $(".cuentas").addClass('d-none');
    $("#periodo").val('');
    $("#periodo").select2({
        placeholder: "Seleccionar",
        ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'periodo', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.periodo, text: obj.periodo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
    });
    $('#tabla_ingresos').bootstrapTable('removeAll');
    $('#tabla_descuento').bootstrapTable('removeAll');
    $("#ci").focus();
}

function bstFooterSumatoria (data) {
    var tipo = '';
    if(data[0] != undefined){
        tipo = data[0].tipo;
    }
    
    let field = this.field;
    var total = separadorMiles(data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current[field])), 0));
    return `<span class="f16 ${tipo}">${total}</span>`;
}

$("#form_ingreso").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    let concepto = $("#concepto").val();
    let importe = $("#importe").val();
    let obs = $("#observacion").val();

    $("#tabla_ingresos").bootstrapTable('insertRow', {
        index: 0,
        row: {
            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
            concepto: concepto.toUpperCase(),
            importe: importe,
            observacion: obs,
            tipo:'ins', 
        }
    });
    $("#modal").modal('hide');
    neto_final();
});

$("#form_descuento").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    let concepto = $("#concepto_des").val();
    let importe = $("#importe_des").val();
    let obs = $("#observacion_des").val();

    $("#tabla_descuento").bootstrapTable('insertRow', {
        index: 0,
        row: {
            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
            concepto: concepto.toUpperCase(),
            importe: importe,
            observacion: obs,
            tipo:'des',
            id_descuento: 0, 
        }
    });
    $("#modal_descuento").modal('hide');
    neto_final();
});

function neto_final() {
    $("#neto").val(separadorMiles(quitaSeparadorMiles($("span.ins").text())-quitaSeparadorMiles($("span.des").text())));
}

$('#periodo').select2({
    placeholder: 'Seleccione',
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
            return { q: 'periodo', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.periodo, text: obj.periodo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };

        },
        cache: false
    }
}).on('select2:select', function(){
    var mes = $("#periodo").val().split(" -")[0];
    var anho = $("#periodo").val().split("- ")[1];
    var meses = '';

    if(mes == 'ENERO'){
        meses = '01';
    }if(mes == 'FEBRERO'){
        meses = '02';
    }if(mes == 'MARZO'){
        meses = '03';
    }if(mes == 'ABRIL'){
        meses = '04';
    }if(mes == 'MAYO'){
        meses = '05';
    }if(mes == 'JUNIO'){
        meses = '06';
    }if(mes == 'JULIO'){
        meses = '07';
    }if(mes == 'AGOSTO'){
        meses = '08';
    }if(mes == 'SEPTIEMBRE'){
        meses = '09';
    }if(mes == 'OCTUBRE'){
        meses = '10';
    }if(mes == 'NOVIEMBRE'){
        meses = '11';
    }if(mes == 'DICIEMBRE'){
        meses = '12';
    }

    var datos = $('#tabla_descuento').bootstrapTable('getData');
    var descripcion = "I.P.S 9%";

    var datos_ing = $('#tabla_ingresos').bootstrapTable('getData');
    // var descripcion_ing = "COMISIÓN";

    /* $.each(datos, function (i, v) {
      if( v.id_descuento > 0){
            var id = [v.id_];
            var field = 'id_';
            $('#tabla_descuento').bootstrapTable('remove', { field: field, values: id });
      }
    }); */

    $('#tabla_descuento').bootstrapTable('removeAll');

    $.each(datos_ing, function (i, v) {
      if(v.id_ > 0){
            var id = [v.id_];
            var field = 'id_';
            $('#tabla_ingresos').bootstrapTable('remove', { field: field, values: id });
      }
    });

    // var datos_ext = $('#tabla_ingresos').bootstrapTable('getData');
    // var descripcion_ext = "EXTRA";

    // $.each(datos_ext, function (i, v) {
    //   if(v.concepto == descripcion_ext){
    //         var id = [v.id_];
    //         var field = 'id_';
    //         $('#tabla_ingresos').bootstrapTable('remove', { field: field, values: id });
    //   }
    // });

    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar_periodo_funcionario', id_funcionario: $("#id_funcionario").val(), periodo: this.value, mes_: meses, anho: anho},   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();

            if(data.status == 'error' ){
                alertDismissJS(data.mensaje, "error");
            }else{
                for(const descuento of data.data){
                    $("#tabla_descuento").bootstrapTable('insertRow', {
                        index: 0,
                        row: {
                            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
                            concepto: descuento.concepto,
                            importe: descuento.anticipo_mes,
                            observacion: descuento.observacion,
                            tipo:'des',
                            id_descuento: descuento.id_, 
                        }
                    });
                }
            }
            neto_final();
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar_comision_funcionario', id_funcionario: $("#id_funcionario").val(), periodo: this.value, mes_: meses, anho: anho},   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();

            if(data.status == 'error' ){
                alertDismissJS(data.mensaje, "error");
            }else{
               for(const ingreso of data.data){
                    if(ingreso.total > 0){
                        $("#tabla_ingresos").bootstrapTable('insertRow', {
                            index: 0,
                            row: {
                                id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
                                concepto: ingreso.concepto,
                                importe: ingreso.total,
                                observacion: '',
                                tipo:'ins',
                            }
                        }); 
                    }
                    
                }
                      
            }
            neto_final();
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar_normal_funcionario', id_funcionario: $("#id_funcionario").val(), periodo: this.value, mes_: meses, anho: anho},   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();

            if(data.status == 'error' ){
                alertDismissJS(data.mensaje, "error");
            }else{
               for(const ingreso of data.data){
                    $("#tabla_ingresos").bootstrapTable('insertRow', {
                        index: 0,
                        row: {
                            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
                            concepto: ingreso.concepto,
                            importe: ingreso.total,
                            observacion: '',
                            tipo:'ins',
                        }
                    }); 
                }
                      
            }
            neto_final();
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });

    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar_extra_funcionario', id_funcionario: $("#id_funcionario").val(), periodo: this.value, mes_: meses, anho: anho},   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();

            if(data.status == 'error' ){
                alertDismissJS(data.mensaje, "error");
            }else{
                
                if(data.data.monto_extra > 0){
                    $("#tabla_ingresos").bootstrapTable('insertRow', {
                        index: 0,
                        row: {
                            id_: new Date().getTime(), //ID PARA PODER ELIMINAR LA FILA
                            concepto: 'EXTRA',
                            importe: data.data.monto_extra,
                            observacion: data.data.observacion,
                            tipo:'ins',
                        }
                    }); 
                }      
                
            }
            neto_final();
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('#forma').select2({
    //dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    tags: true,
}).on('select2:select', function(){
    if(this.value == 1){
        $(".cuentas").addClass('d-none');
        $(".cheques").addClass('d-none');
        $("#cheque").val('');
        $("#cuenta").val('');
    }else if(this.value == 2){
        $(".cuentas").addClass('d-none');
        $(".cheques").removeClass('d-none');
        $("#cuenta").val('');
    }else{
        $(".cuentas").removeClass('d-none');
        $(".cheques").addClass('d-none');
        $("#cheque").val('');
        $("#cuenta").val($("#nro_cuenta").val());
    }
});
