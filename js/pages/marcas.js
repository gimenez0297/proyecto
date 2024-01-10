var url = 'inc/marcas-data';

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .cambiar-estado': function(e, value, row, index) {
        let sigteEstado = (row.estado_str == "Activo" ? "Inactivo" : "Activo");
        let estado = (row.estado == 1 ? 0 : 1);
        sweetAlertConfirm({
            title: `Cambiar Estado`,
            text: `¿Actualizar estado a '${sigteEstado}'?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=cambiar-estado',
                    type: 'post',
                    data: { id_marca: row.id_marca, estado },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function(jqXhr, textStatus, errorThrown) {
                        NProgress.done();
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    },
    'click .editar': function (e, value, row, index) {

        editarDatos(row);
    }
}

function fotos(value, row, index) {
    bhash = Math.floor((Math.random() * 50000) + 1);;
    if (row.logo) {
        return [
            "<div class='zoom_image mb-1' style='width:50px;'><img class='zoom_img' src='" + row.logo + "?" + bhash + "' alt='-' style='object-fit: cover;'/></div>"

        ].join('');
    } else {
        return [
            "<div class='zoom_image mb-1' style='width:50px;'><img class='zoom_img' src='dist/images/sin-foto.jpg' alt='-' style='object-fit: cover;'/></div>"
        ].join('');
    }
}

$("#tabla").bootstrapTable({
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
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "marca",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_marca', title: 'ID Marca', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'marca', title: 'Marca', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'logos', align: 'center', valign: 'middle', title: 'Logo', sortable: false, formatter: fotos },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200 }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

// Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: $(window).height()-120,
                pageSize: Math.floor(($(window).height()-120)/50),
            });
        }, 100);
    });
});

$('#tabla').on('load-success.bs.table', function(e) {

        var currentMousePos = { x: -1, y: -1 };
        $(document).mousemove(function(event) {
            currentMousePos.x = event.pageX;
            currentMousePos.y = event.pageY;
            if ($('#zoom_modal').css('display') != 'none') {
                $('#zoom_modal').css({
                    top: currentMousePos.y - 100,
                    left: currentMousePos.x - 600,
                });
            }
        });
        $('.zoom_image').on('mouseover', function() {
            var image = $(this).find('img');
            //  image = image[0].currentSrc;
            $('#zoom_modal').html(image.clone());
            $('#zoom_modal').css({
                top: currentMousePos.y - 170,
                left: currentMousePos.x - 600
            });
            $('#zoom_modal').show();

        });

        $('.zoom_image').on('mouseleave', function() {
            $('#zoom_modal').hide();
        });

        $("#tabla").bootstrapTable('resetView');
    });

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Marca');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    $('#img_preview').attr('src', img_default);
    $('#eliminarfoto').hide();
    $('#logo').next('.custom-file-label').html('Elegir Imagen');
    resetForm('#formulario');
});

var img_default = "dist/images/sin-foto.jpg";
$('#eliminarfoto').click(function() {
    $('#logo').val('');
    $('#change_logo').val('1');
    $('#img_preview').attr('src', img_default);
    $('#eliminarfoto').hide();
    $('#logo').next('.custom-file-label').html('Elegir Imagen');
});

$('#modal').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
});

function resetForm(form) {
    $(form).trigger('reset');
    resetValidateForm(form);
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#img_preview').attr('src', e.target.result);
            $('#change_logo').val('1');
            $('#eliminarfoto').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#logo").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    readURL(this);
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
    //var data = $(this).serializeArray();

    $.ajax({
        url: url + '?q='+$(this).attr("action"),
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        processData: false,
        contentType: false,
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
    var id = $("#id_marca").val();
    var nombre = $("#marca").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Marca: ${nombre}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id, nombre },	
                beforeSend: function() {
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal').modal('hide');
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

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
    }
});

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Marca');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_marca").val(row.id_marca);
    $("#marca").val(row.marca);
    $('#logo').val('');
    $('#change_logo').val('0');
    if (row.logo) {
        // Se usa un número aleatorio para evitar cache
        $('#img_preview').attr('src', `${row.logo}?v=${getRandomInt(1, 9999)}`);
        $('#eliminarfoto').show();
        
    } else {
        $('#img_preview').attr('src', img_default);
        $('#eliminarfoto').hide();
        
    }
}

