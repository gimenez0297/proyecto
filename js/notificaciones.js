$(document).ready(function () {
    $('#btn-notificaciones').on('click', function (event) {
        $(this).parent().toggleClass('show');
        $(this).siblings('.dropdown-menu').toggleClass('show');
        actualizarNotificaciones();
    });

    $('body').on('click', function (e) {
        if (!$('#btn-notificaciones').is(e.target) && $('#btn-notificaciones').has(e.target).length === 0 && $('.show').has(e.target).length === 0) {
            $('#btn-notificaciones').parent().removeClass('show');
            $('#btn-notificaciones').siblings('.dropdown-menu').removeClass('show');
        }
    });

    $('#tabla_notificaciones').bootstrapTable({
        url: 'inc/notificaciones-data?q=ver',
        classes: 'table table-hover table-condensed-xs',
        striped: true,
        icons: 'icons',
        pageSize: 6,
        sortName: "id_notificacion",
        sortOrder: 'desc',
        autoRefresh: true,
        showAutoRefresh: false,
        // autoRefreshInterval: 5,
        showCustomView: true,
        customView: customViewFormatterNotificacion,
        columns: [
            [
                { field: 'id_notificacion', title: 'ID Notificación', align: 'left', valign: 'middle', sortable: true, visible: false },
                { field: 'titulo', title: 'Título', align: 'left', valign: 'middle', sortable: true },
                { field: 'descripcion', title: 'Descripción', align: 'left', valign: 'middle', sortable: true },
                { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            ]
        ]
    }).on('load-success.bs.table', function(e, data) {
        let no_leidos = data.filter(value => value.estado == 0);
        if (no_leidos.length > 0) {
            $('#btn-notificaciones').find('span').html(no_leidos.length).removeClass('d-none');
            $('#btn-notificaciones').find('i').addClass('text-primary');
        } else {
            $('#btn-notificaciones').find('i').removeClass('text-primary');
            $('#btn-notificaciones').find('span').addClass('d-none');
        }
    }).on('custom-view-post-body.bs.table', function() {
        $('#notificaciones').find('.card-button-notificacion').on('click', notificacion_marcar_leido);
    });

    function notificacion_marcar_leido(e) {
        let id = $(this).val();
        $.ajax({
            url: 'inc/notificaciones-data?q=marcar_leido',
            dataType: 'json',
            type: 'post',
            contentType: 'application/x-www-form-urlencoded',
            data: { id },
            beforeSend: function() {
                NProgress.start();
            },
            success: function(data, textStatus, jQxhr) {
                NProgress.done();
                if (data.status == 'ok') {
                    actualizarNotificaciones();
                } else {
                    alertDismissJS(data.mensaje, data.status);
                }
            },
            error: function(jqXhr, textStatus, errorThrown) {
                NProgress.done();
                alertDismissJS($(jqXhr.responseText).text().trim(), "error");
            }
        });
    }

    function customViewFormatterNotificacion(data) {
        let template = $('#templateNotificacion').html();
        let view = '';

        if (data.length > 0) {
            $.each(data, function (i, row) {
                let is_new = row.estado == 1 ? 'd-none' : '';
                view += template.replace('%ID%', row.id_notificacion)
                                .replace('%TITULO%', row.titulo)
                                .replace('%TITLE_TITULO%', row.titulo)
                                .replace(/%IS_NEW%/gmi, is_new)
                                .replace('%FECHA%', row.fecha)
                                .replace('%DESCRIPCION%', row.descripcion)
                                .replace('%TITLE_DESCRIPCION%', row.descripcion);
            });
        } else {
            view = '<a class="dropdown-item text-center" href="#">No hay notificaciones</a>';
        }
        return `<div class="mx-0">${view}</div>`;
    }
});

function actualizarNotificaciones() {
    $('#tabla_notificaciones').bootstrapTable('refresh');
}

