var url = 'inc/orden-pago-data';
var url_proveedores = 'inc/proveedores-data';
var url_funcionarios = 'inc/funcionarios-data';
var url_listados = 'inc/listados';

//noSubmitForm('#formulario');

resetWindow();
obtenerNumero();
verificarTotalEgreso();

function alturaTablaproveeedor() {
    return bstCalcularAlturaTabla(550, 400);
}

function alturaTabla() {
    return bstCalcularAlturaTabla(450, 300);
}

var MousetrapProveedor = new Mousetrap();
var MousetrapGastos = new Mousetrap();
var MousetrapFuncionario = new Mousetrap();

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla_detalles").bootstrapTable('refreshOptions', { 
                height: alturaTabla(),
            });
        }, 100);
    });

    MousetrapTableNavigationCell('#tabla_detalles', MousetrapProveedor);
    MousetrapTableNavigationCell('#tabla_detalles_gastos', MousetrapGastos);
    MousetrapTableNavigationCell('#tabla_liquidaciones', MousetrapFuncionario);

    MousetrapProveedor.bindGlobal('f1', (e) => { e.preventDefault(); $('#btn_buscar_proveedor').click() });
    MousetrapFuncionario.bindGlobal('f1', (e) => { e.preventDefault(); $('#btn_buscar').click() });
    MousetrapGastos.bindGlobal('f1', (e) => { e.preventDefault(); $('#btn_buscar_gastos').click() });
    Mousetrap.bindGlobal('f4', (e) => { e.preventDefault(); $('#btn_guardar').click() });

    $('.modal').on('shown.bs.modal', (e) => Mousetrap.pause());
    $('.modal').on('hidden.bs.modal', (e) => Mousetrap.unpause());

    $('#destino_pago').trigger('select2:select');
    $('#forma_pago').trigger('select2:select');
});

function iconosFilaIngreso(value, row, index) {
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>`,
    ].join('');
}


window.accionesFilaIngresos = {
    'click .eliminar': function (e, value, row, index) {
        var nro = row.numero_documento;
        sweetAlertConfirm({
            title: `Eliminar Factura`,
            text: `¿Eliminar Factura Nro: ${nro}?`,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $('#tabla_detalle').bootstrapTable('removeByUniqueId', row.id_recepcion_compra);
            }
        });

    }
}

// Buscar proveedor
$('#ruc').blur(async function() {
    var ruc = $(this).val();
    var data = {};
    if (ruc) {
        var buscar_proveedor = await buscarProveedor(ruc);
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

$('#modal_proveedores').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_proveedores').bootstrapTable("refresh", {url: url_proveedores+`?q=ver`}).bootstrapTable('resetSearch', '');
    keyboard_navigation_for_table_in_modal('#tabla_proveedores', this, cargarDatosProveedor);
}).on('hidden.bs.modal', function(){
    setTimeout(function(){
        $("#id_banco").focus();
    }, 1);
});

$('#tabla_proveedores').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosProveedor(row);
});

function cargarDatosProveedor(data) {
    if (data.id_proveedor > 0) {
        $('#proveedor').select2('trigger', 'select', {
            data: { id: data.id_proveedor, text: data.proveedor }
        });
        $('#ruc').val(data.ruc);
    }
    $('#id_proveedor').val(data.id_proveedor);
    $("#modal_proveedores").modal("hide");
    $('#tabla_detalles').bootstrapTable('refresh', { url: `${url}?q=ver_facturas&id_proveedor=${data.id_proveedor}` })
}
// Fin buscar proveedor

$('#modal_gastos').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_proveedor_gastos').bootstrapTable("refresh", {url: url_proveedores+`?q=ver&tipo_proveedor=2`}).bootstrapTable('resetSearch', '');
    keyboard_navigation_for_table_in_modal('#tabla_proveedor_gastos', this, cargarDatosGastos);
}).on('hidden.bs.modal', function(){
    setTimeout(function(){
        $("#id_banco").focus();
    }, 1);
});

$('#tabla_proveedor_gastos').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosGastos(row);
});

function cargarDatosGastos(data) {
    if (data.id_proveedor > 0) {
        $('#proveedor_gasto').select2('trigger', 'select', {
            data: { id: data.id_proveedor, text: data.proveedor }
        });
        $('#ruc').val(data.ruc);
    }
    $('#id_proveedor').val(data.id_proveedor);
    $("#modal_gastos").modal("hide");
    $('#tabla_detalles_gastos').bootstrapTable('refresh', { url: `${url}?q=ver_gastos&id_proveedor=${data.id_proveedor}` });
}

// Detalles
$("#tabla_detalles").bootstrapTable({
    toolbar: '#toolbar_facturas',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    icons: 'icons',
    checkboxHeader: false,
    mobileResponsive: true,
    showFooter: true,
	height: alturaTabla(),
    sortName: 'id_recepcion_compra_vencimiento',
    sortOrder: 'asc',
    uniqueId: 'id_recepcion_compra_vencimiento',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_recepcion_compra', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'id_recepcion_compra_vencimiento', title: 'ID Producto', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'proveedor', title: 'Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'numero_documento', title: 'Nro. Factura', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'condicion', title: 'Condición', align: 'left', valign: 'middle', sortable: true},
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true},
            { field: 'total_costo', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria},
            { field: 'pendiente', title: 'Pendiente', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width: 150, editable: { type: 'text' } },
        ]
    ]
}).on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    let total = quitaSeparadorMiles(row.total_costo);
    let pendiente = quitaSeparadorMiles(row.pendiente);
    let monto = quitaSeparadorMiles(row.monto);

    if (monto > pendiente || monto <= 0 ) {
        monto = pendiente;
    }
    // Se actualizan los valores
    $('#tabla_detalles').bootstrapTable('updateByUniqueId', { id: row.id_recepcion_compra_vencimiento, row: { monto: separadorMiles(monto) } });
});

$.extend($('#tabla_detalles').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_detalles').on('post-body.bs.table', function(e, data) {
    // Solo se puede editar los productos que aun no se hayan devuelto en su totalidad
    $.each(data, (index, value) => {
        if (value.condicion == 'CONTADO') {
            setTimeout(() => $('#tabla_detalles').find(`tr[data-index=${index}]`).find('.editable').editable('disable'));
        }
    });
});

// Detalles Funcionario
$("#tabla_liquidaciones").bootstrapTable({
    toolbar: '#toolbar_liquidaciones',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    icons: 'icons',
    checkboxHeader: false,
    mobileResponsive: true,
    showFooter: true,
    height: alturaTabla(),
    sortName: 'id_liquidacion',
    sortOrder: 'asc',
    uniqueId: 'id_liquidacion',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_liquidacion', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'periodo', title: 'Periodo', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'forma_pago', title: 'Forma Pago', align: 'left', valign: 'middle', sortable: true},
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria},
            { field: 'pendiente', title: 'Pendiente', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width: 150 },
        ]
    ]
}).on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    let total = quitaSeparadorMiles(row.total_costo);
    let pendiente = quitaSeparadorMiles(row.pendiente);
    let monto = quitaSeparadorMiles(row.monto);

    if (monto > pendiente || monto <= 0 ) {
        monto = pendiente;
    }
    // Se actualizan los valores
    $('#tabla_liquidaciones').bootstrapTable('updateByUniqueId', { id: row.id_liquidacion, row: { monto: separadorMiles(monto) } });
});

$.extend($('#tabla_liquidaciones').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

// $('#tabla_liquidaciones').on('post-body.bs.table', function(e, data) {
//     // Solo se puede editar los productos que aun no se hayan devuelto en su totalidad
//     $.each(data, (index, value) => {
//         // if (value.condicion == 'CONTADO') {
//             setTimeout(() => $('#tabla_liquidaciones').find(`tr[data-index=${index}]`).find('.editable').editable('disable'));
//         // }
//     });
// });

// Detalles Gastos
$("#tabla_detalles_gastos").bootstrapTable({
    toolbar: '#toolbar_gastos',
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    search: true,
    showRefresh: true,
    showToggle: true,
    searchAlign: 'right',
    icons: 'icons',
    mobileResponsive: true,
    showFooter: true,
	height: alturaTabla(),
    sortName: 'id_gasto',
    sortOrder: 'asc',
    uniqueId: 'id_gasto',
    footerStyle: bstFooterStyle,
    columns: [
        [
            { field: 'state', align: 'center', valign: 'middle', checkbox: true},
            { field: 'id_gasto', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'proveedor', title: 'Proveedor', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'nro_gasto', title: 'Nro. Gasto', align: 'left', valign: 'middle', sortable: true, footerFormatter: bstFooterTextTotal },
            { field: 'condicion', title: 'Condición', align: 'left', valign: 'middle', sortable: true},
            { field: 'vencimiento', title: 'Vencimiento', align: 'left', valign: 'middle', sortable: true},
            { field: 'total', title: 'Total', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria},
            { field: 'pendiente', title: 'Pendiente', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria },
            { field: 'monto', title: 'Monto', align: 'right', valign: 'middle', sortable: true, formatter: separadorMiles, footerFormatter: bstFooterSumatoria, width: 150, editable: { type: 'text' } },
        ]
    ]
}).on('editable-save.bs.table', function(e, field, row, rowIndex, oldValue, $el) {
    let total = quitaSeparadorMiles(row.total);
    let pendiente = quitaSeparadorMiles(row.pendiente);
    let monto = quitaSeparadorMiles(row.monto);

    if (monto > pendiente || monto <= 0 ) {
        monto = pendiente;
    }

    // Se actualizan los valores
    $('#tabla_detalles_gastos').bootstrapTable('updateByUniqueId', { id: row.id_gasto, row: { monto: separadorMiles(monto) } });
});

$.extend($('#tabla_detalles_gastos').editable.defaults, {
    mode: 'inline',
    showbuttons: false,
    emptytext: '',
    toggle: 'manual',
    onblur: 'submit',
    tpl: '<input type="text" style="width:140px" onkeyup="separadorMilesOnKey(event, this)">'
});

$('#tabla_detalles_gastos').on('post-body.bs.table', function(e, data) {
    // Solo se puede editar los productos que aun no se hayan devuelto en su totalidad
    $.each(data, (index, value) => {
        if (value.condicion_str == 'CONTADO') {
            setTimeout(() => $('#tabla_detalles_gastos').find(`tr[data-index=${index}]`).find('.editable').editable('disable'));
        }
    });
});

$("#tabla_proveedores").bootstrapTable({
    url: url + '?q=ver',
    toolbar: '#toolbar_proveedores',
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
    sortName: "proveedor",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_proveedor', title: 'ID PROVEEDOR', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'proveedor', title: 'PROVEEDOR / RAZÓN SOCIAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'nombre_fantasia', title: 'NOMBRE DE FANTASIA', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'obs', title: 'OBSERVACIONES', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$("#tabla_proveedor_gastos").bootstrapTable({
    //url: url + '?q=ver',
    toolbar: '#toolbar_proveedor_gastos',
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
    sortName: "proveedor",
    sortOrder: 'asc',
    trimOnSearch: false,
    singleSelect: true,
    clickToSelect: true,
    columns: [
        [
            { field: 'check', align: 'center', valign: 'middle', checkbox: true },
            { field: 'id_proveedor', title: 'ID PROVEEDOR', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'proveedor', title: 'PROVEEDOR / RAZÓN SOCIAL', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'nombre_fantasia', title: 'NOMBRE DE FANTASIA', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ruc', title: 'R.U.C', align: 'left', valign: 'middle', sortable: true },
            { field: 'obs', title: 'OBSERVACIONES', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
        ]
    ]
});

$('#btn_guardar').on('click', () => $('#formulario').submit());
$('#formulario').submit(function(e) {
    e.preventDefault();

    if ($('#id_proveedor').val() == '') {
        //alertDismissJS('Ningún producto agregado. Favor verifique.', "error");
        $('html, body').animate({ scrollTop: 0 }, "slow");
    } else if ($('#tabla_detalles').bootstrapTable('getSelections').length == 0 && $('#destino_pago').val() == 1) {
        alertDismissJS('Ninguna factura seleccionada. Favor verifique.', "error");
        return false;
    }

    if ($(this).valid() === false) return false;

    sweetAlertConfirm({
        title: `¿Guardar Orden de Pago?`,
        confirmButtonText: 'Guardar',
        closeOnConfirm: false,
        confirm: () => {
            var data = $(this).serializeArray();
            data.push({ name: 'detalles', value: JSON.stringify($('#tabla_detalles').bootstrapTable('getSelections')) });
            data.push({ name: 'detalles_gastos', value: JSON.stringify($('#tabla_detalles_gastos').bootstrapTable('getSelections')) });
            data.push({ name: 'detalles_liquidaciones', value: JSON.stringify($('#tabla_liquidaciones').bootstrapTable('getSelections')) });

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
                        alertDismissJS(data.mensaje, data.status, function(){
                            var param = { id_pago: data.id_pago};
                            OpenWindowWithPost("imprimir-pagos.php", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirPagos", param);
                            resetWindow();
                        });
                    } else {
                        alertDismissJS(data.mensaje, data.status);
                    }
                },
                error: function (xhr) {
                    NProgress.done();
                    alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
                }
            });
        }
    });
});

$('#btn_agregar').click(function() {
    var tabla = $('#tabla_detalles').bootstrapTable('getSelections');
    let carga_cero = tabla.find(value => parseInt(quitaSeparadorMiles(value.monto)) === 0);

    if(carga_cero){
        alertDismissJS(`La factura N°: "${carga_cero.numero_documento}" tiene monto 0. Favor verifique.`, "error");
    }else{
        $('#tabla_detalle').bootstrapTable('append', tabla);
       
        $("#modal_proveedores").modal("hide");
    }
    
});

function resetWindow() {
    $('input, textarea').val('');
    $('#formulario').find('select').val(null).trigger('change');
    $('#forma_pago').val('1').trigger('change');
    $('#destino_pago').val('1').trigger('change');
    $('#tabla_detalles').bootstrapTable('removeAll');
    $('#destino_pago').trigger('select2:select');
    $('#forma_pago').trigger('select2:select');

    setTimeout(function(){
        $("#destino_pago").focus();
        $('#fecha').val(moment().format('YYYY-MM-DD'));
    }, 1);
    obtenerNumero();
    verificarTotalEgreso();
}

// Configuración select2
$('#forma_pago').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('select2:select', function(){
    campos($("#destino_pago").val(), this.value);
});

$('#destino_pago').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    minimumResultsForSearch: Infinity,
}).on('select2:select', function(){
    obtenerNumero();
    campos(this.value, $("#forma_pago").val());
});

function campos(destino, forma){
    var $movimiento = $('.sucursal');
    var $cuentas = $('.cuentas');
    var $cuentas_destino = $('.destino');
    var $concepto = $('.concepto');
    var $proveedor = $('.proveedor');
    var $funcionario = $('.funcionario');
    var $gastos = $('.gastos');
    var $cheques = $('.cheques');
    var $monto_cc = $('.monto_cc');
    var $tabla_proveedor = $('.tabla_proveedor');
    var $tabla_funcionario = $('.tabla_funcionario');
    var $tabla_gastos = $('.tabla_gastos');

    $movimiento.removeClass().addClass('form-group sucursal d-none');
    $cuentas.removeClass().addClass('form-group cuentas d-none');
    $cuentas_destino.removeClass().addClass('form-group cuentas destino d-none');
    $concepto.removeClass().addClass('form-group concepto d-none');
    $proveedor.removeClass().addClass('form-group proveedor d-none');
    $funcionario.removeClass().addClass('form-group funcionario d-none');
    $gastos.removeClass().addClass('form-group gastos d-none');
    $cheques.removeClass().addClass('form-group cheques d-none');
    $monto_cc.removeClass().addClass('form-group monto_cc d-none');
    $tabla_proveedor.addClass('d-none');
    $tabla_funcionario.addClass('d-none');
    $tabla_gastos.addClass('d-none');

    if(destino == 1){
        $proveedor.addClass('col-md-6 col-sm-12').removeClass('d-none');
        $tabla_proveedor.removeClass('d-none');
        MousetrapProveedor.unpause();
        MousetrapGastos.pause();
        MousetrapFuncionario.pause();
    }else if(destino == 2){
        $funcionario.addClass('col-md-6 col-sm-12').removeClass('d-none');
        $tabla_funcionario.removeClass('d-none');
        MousetrapProveedor.pause();
        MousetrapGastos.pause();
        MousetrapFuncionario.unpause();
       
    }else if(destino == 3){
        $gastos.addClass('col-md-6 col-sm-12').removeClass('d-none');
        $tabla_gastos.removeClass('d-none');
        MousetrapProveedor.pause();
        MousetrapGastos.unpause();
        MousetrapFuncionario.pause();
    }

    if(destino == 4){
        $movimiento.addClass('col-md-3 col-sm-12').removeClass('d-none');
        MousetrapProveedor.pause();
        MousetrapGastos.pause();
        MousetrapFuncionario.pause();
        if(forma == 1){
            $cuentas.addClass('col-md-2 col-sm-12').removeClass('d-none');
            $concepto.addClass('col-md-6 col-sm-12').removeClass('d-none');
            $monto_cc.addClass('col-md-2 col-sm-12').removeClass('d-none');
            
        }else if(forma == 2){
            $cheques.addClass('col-md-3 col-sm-12').removeClass('d-none');
            $concepto.addClass('col-md-6 col-sm-12').removeClass('d-none');
            $monto_cc.addClass('col-md-3 col-sm-12').removeClass('d-none');
            
        }else if(forma == 3){
            $monto_cc.addClass('col-md-3 col-sm-12').removeClass('d-none');
            $concepto.addClass('col-md-9 col-sm-12').removeClass('d-none');
            
        }
    }else {
        if(forma == 1){
            $cuentas.addClass('col-md-3 col-sm-12').removeClass('d-none');
            $concepto.addClass('col-md-6 col-sm-12').removeClass('d-none');
            
        }else if(forma == 2){
            $cuentas.addClass('col-md-3 col-sm-12').removeClass('d-none');
            $cuentas_destino.addClass('d-none').removeClass('d-nonecol-md-3 col-sm-12');
            $cheques.addClass('col-md-3 col-sm-12').removeClass('d-none');
            $concepto.addClass('col-md-6 col-sm-12').removeClass('d-none');
            
        }else if(forma == 3){
            $concepto.addClass('col-md-12 col-sm-12').removeClass('d-none');
            
        }
    }

}

$('#id_sucursal').select2({
    //dropdownParent: $("#modal"),
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
}).on('select2:select', function(){
    obtenerCodigoMovimiento();
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
        ]
    ]
});

$('#modal_funcionarios').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_proveedores').bootstrapTable("refresh", {url: url_proveedores+`?q=ver`}).bootstrapTable('resetSearch', '');
    keyboard_navigation_for_table_in_modal('#tabla_proveedores', this, cargarDatosProveedor);
}).on('hidden.bs.modal', function(){
    setTimeout(function(){
        $("#id_banco").focus();
    }, 1);
});

$('#modal_funcionarios').on('shown.bs.modal', function (e) {
    $("input[type='search']").focus();
    $('#tabla_funcionarios').bootstrapTable("refresh", {url: url_funcionarios+`?q=ver`}).bootstrapTable('resetSearch', '');
    keyboard_navigation_for_table_in_modal('#tabla_funcionarios', this, cargarDatosFuncionario);
}).on('hidden.bs.modal', function(){
    setTimeout(function(){
        $("#id_banco").focus();
    }, 1);
});

$('#tabla_funcionarios').on('dbl-click-row.bs.table', function (e, row, $element, field) {
    cargarDatosFuncionario(row);
});

function cargarDatosFuncionario(data) {
    if (data.id_funcionario > 0) {
        $('#funcionario').select2('trigger', 'select', {
            data: { id: data.id_funcionario, text: data.razon_social }
        });
        $('#ci').val(data.ci);
    }
    //$('#funcionario').val(data.razon_social);
    $('#id_funcionario').val(data.id_funcionario);
    $('#cuenta_destino').val(data.nro_cuenta);
    $("#modal_funcionarios").modal("hide");
    $('#tabla_liquidaciones').bootstrapTable('refresh', { url: `${url}?q=ver_liquidaciones&id_funcionario=${data.id_funcionario}` });
}

$('#id_banco').select2({
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'bancos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_banco, text: obj.banco }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    setTimeout(() => {
        $('#cuenta').val(null).trigger('change');
    }, 1);
});

$('#cuenta').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    // allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        async: true,
        data: function (params) {
            return { q: 'cuentas', id_banco: $("#id_banco").val(), term: params.term, page: params.page || 1}
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { 
                    return { id: obj.id_cuenta, text: obj.cuenta}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#proveedor').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    // allowClear: false,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        async: true,
        data: function (params) {
            return { q: 'proveedores', term: params.term, page: params.page || 1 , tipo_proveedor: 1}
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
}).on('change', function(){
    var data = $('#proveedor').select2('data')[0] || {};
    $('#ruc').val(data.ruc || '');
    $('#id_proveedor').val(data.id || '');
    setTimeout(() => {
        $('#tabla_detalles').bootstrapTable('refresh', { url: `${url}?q=ver_facturas&id_proveedor=${$(this).val()}` });
        if (data.id) {
            $('#id_banco').focus().select();
        } 
    }, 1);
});

$('#funcionario').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    // allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        async: true,
        data: function (params) {
            return { q: 'funcionarios', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { 
                    return { id: obj.id_funcionario, text: obj.funcionario, ci: obj.ci, nro_cuenta: obj.nro_cuenta}; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function(){
    var data = $('#funcionario').select2('data')[0] || {};
    $('#ci').val(data.ci || '');
    $('#id_funcionario').val(data.id || '');
    $('#cuenta_destino').val(data.nro_cuenta || '');
    setTimeout(() => {
        $('#tabla_liquidaciones').bootstrapTable('refresh', { url: `${url}?q=ver_liquidaciones&id_funcionario=${data.id}` });
        if (data.id) {
            $('#id_banco').focus().select();
        } 
    }, 1);
});


$('#proveedor_gasto').select2({
    placeholder: 'Seleccionar',
    width: 'style',
    // allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url_listados,
        dataType: 'json',
        delay: 50,
        async: true,
        data: function (params) {
            return { q: 'proveedores_gastos_orden_pago', term: params.term, page: params.page || 1, tipo_proveedor: 2 }
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
}).on('change', function(){
    var data = $('#proveedor_gasto').select2('data')[0] || {};
    setTimeout(() => {
        $('#tabla_detalles_gastos').bootstrapTable('refresh', { url: `${url}?q=ver_gastos&id_proveedor=${data.id}` });
        if (data.id) {
            $('#id_banco').focus().select();
        } 
    }, 1);
});

function obtenerNumero() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'recuperar-numero' },
        beforeSend: function() { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();

            $('#numero').val(data.numero);
        },
        error: function (xhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
        }
    });
}

function obtenerCodigoMovimiento() {
    id_sucursal = $('#id_sucursal').val();
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'siguiente-cod-movimiento', id: id_sucursal},
        beforeSend: function() { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
            if (data.status == 'ok') {
                $('#nro_movimiento').val(data.codigo);
                $('#monto_cc').val(separadorMiles(data.monto));
            }else{
                alertDismissJsSmall(data.mensaje, data.status, 3000, () => $('#id_sucursal').focus())
            }
        },
        error: function (xhr) {
            NProgress.done();
            alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
        }
    });
}

function verificarTotalEgreso() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar-total-egreso' },
        beforeSend: function () { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
            alertDismissJsSmall(data.mensaje, data.status, 3000, () => $('#destino_pago').focus())
        },
        error: function (xhr) {
            NProgress.done();
        }
    });
}
