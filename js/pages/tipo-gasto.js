var url = 'inc/tipo-gasto-data';

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
});

function alturaTabla() {
    return bstCalcularAlturaTabla(120, 300);
}
function pageSizeTabla() {
    return Math.floor(alturaTabla() / 50);
}

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    }
}

$('#tabla').bootstrapTable({
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
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: alturaTabla(),
    pageSize: pageSizeTabla(),
    sortName: 'id_sub_tipo_gasto',
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_sub_tipo_gasto', title: 'ID', align: 'left', valign: 'middle', sortable: true, width: 200 },
            { field: 'nombre', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

$('#modal').on('shown.bs.modal', function (e) {
    $('#formulario input[type!="hidden"]:first').focus();
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$('#formulario').submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url + '?q='+$(this).attr('action'),
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
            alertDismissJS($(jqXhr.responseText).text().trim(), 'error');
        }
    });
});

function resetForm(form) {
    $(form).trigger('reset');
    resetValidateForm(form);
}

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Tipo De Gasto');
    $('#formulario').attr('action', 'editar');
    $('#modal').modal('show');

    $('#id').val(row.id_sub_tipo_gasto);
    $('#tipo_gasto').val(row.nombre);
}

