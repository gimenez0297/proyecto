var url = 'inc/balance-data';
var url_listados = 'inc/listados';


$('#id_libro').select2({
    placeholder: 'Seleccionar',
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
            return { q: 'libros-diarios', term: params.term, page: params.page || 1 }
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function (obj) { return { id: obj.id_libro_diario_periodo, text: obj.libro }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: `${url}?q=ver&id_libro=${$(this).val()}` })
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm agregar_submenu mr-1" title="Agregar Subcuenta"><i class="fa fa-plus"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .agregar_submenu': function (e, value, row, index) {
        resetForm('#formulario_cuenta');
        $('#modalLabelCuenta').html('Agregar Cuenta');
        $('#formulario_cuenta').attr('action', 'agregar_cuenta');
        $('#modal_cuenta').modal('show');
        $('#id_padre').val(row.id_libro_cuenta)
        $('#cuenta_padre').val(row.denominacion);
        $('#eliminar').hide();
    },
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    }
}

$("#tabla").bootstrapTable({
    //url: url + '?q=ver',
    toolbar: '#toolbar',
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    // sortName: "orden",
    sortOrder: 'asc',
    trimOnSearch: false,
    idField: 'id_libro_cuenta',
    treeShowField: 'cuenta',
    undefinedText: "",
    parentIdField: 'id_padre',
    columns: [
        [
            {	field: 'id_libro_cuenta', align: 'left', valign: 'middle', title: 'ID Libro Ceuntas', sortable: true, visible: false }, 
            {	field: 'id_padre', align: 'left', valign: 'middle', title: 'ID Padre', visible: false }, 
            {	field: 'cuenta', align: 'right', valign: 'middle', title: 'Cuenta', width: 10, widthUnit: '%'	},
            {	field: 'denominacion', align: 'left', valign: 'middle', title: 'Denominacion'},
            {	field: 'saldo', align: 'left', valign: 'middle', title: 'Saldo', formatter: foormaterSaldo},
            {	field: 'acciones', align: 'center', valign: 'middle', title: 'Acciones', sortable: false, events: accionesFila,  formatter: iconosFila,width: 10, widthUnit: '%'}
        ]
    ]
}).on('post-body.bs.table', function(e, data){
    var columns = $(this).bootstrapTable('getVisibleColumns');
    if(columns){
        var indexField = columns.findIndex((col) => col.field === "denominacion");
        $("#tabla").treegrid({
            treeColumn: indexField,
            saveState: true,
            initialState: 'expanded',
        }).on("change", () => $("#tabla").bootstrapTable('resetView'));
    }
}).on('click-cell.bs.table', (e, field, value, row, $element) => { 
    if(field == "denominacion") $($element).parent().treegrid('toggle');
});

function foormaterSaldo(value, row){
    if (row.tipo_cuenta == 1) {
        return separadorMiles(row.debe - row.haber);
    }
    if (row.tipo_cuenta == 2){
        return separadorMiles(row.haber - row.debe);
    }    
    if (row.tipo_cuenta == 3) {
        return separadorMiles(row.haber - row.debe);
    }
    if (row.tipo_cuenta == 4) {
        return separadorMiles(row.debe - row.haber);
    }
    if (row.tipo_cuenta == 5) {
        return separadorMiles(row.debe - row.haber);
    }
}

$('#agregar').click(function(){
    $('#modalLabel').html('Agregar Cuenta Padre');
    $('#formulario').attr('action', 'agregar_padres');
    resetForm('#formulario');
});

$("#formulario_cuenta").submit(function(e) {
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
                $('#modal_cuenta').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

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
    var id = $("#id_padre").val();
    var nombre = $("#denominacion").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Cuenta: ${nombre}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar_cuenta', id },	
                beforeSend: function() {
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal_cuenta').modal('hide');
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

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

function editarDatos(row) {
    resetForm('#formulario_cuenta');
    $('#modalLabelCuenta').html('Editar Cuenta');
    $('#formulario_cuenta').attr('action', 'editar_cuenta');
    $('#modal_cuenta').modal('show');
    $('#eliminar').show();

    $('#id_padre').val(row.id_libro_cuenta)
    $('#cuenta_padre').val(row.denominacion);
    $("#cuenta").val(row.cuenta);
    $("#denominacion").val(row.denominacion);
}