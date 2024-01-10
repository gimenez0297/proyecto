var url = 'inc/banner-data';
var url_listados = 'inc/listados';

// Acciones por teclado
$(window).on('keydown', function (event) { 
    switch(event.which){
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
        // F2 Agregar Proveedor
        // case 113:
        //     event.preventDefault();
        //     $('#agregar-proveedor').click();
        //     break;
    }
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>',
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
                    data: { id_banner: row.id_banner, estado },
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
    },
}

function fotos(value, row, index) {
    bhash = Math.floor((Math.random() * 50000) + 1);;
    if (row.foto) {
        return [
            "<div class='zoom_image mb-1' style='width:50px;'><img class='zoom_img' src='../" + row.foto + "?" + bhash + "' alt='-' style='object-fit: cover;'/></div>"

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
    sortName: "id_banner",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_banner', title: 'ID Banner', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'foto', align: 'center', valign: 'middle', title: 'Logo', sortable: false, formatter: fotos },
            { field: 'link', title: 'Link', align: 'left', valign: 'middle', sortable: true, visible: true },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false, switchable: false },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila, width: 120 }
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

    $.fn.modal.Constructor.prototype._enforceFocus = function() {};
    // Configuración dropzone
    Dropzone.autoDiscover = false;
    Dropzone.prototype.defaultOptions.dictFileTooBig = "Archivo muy pesado ({{filesize}}MB). Tamaño Maximo: {{maxFilesize}}MB.";
    Dropzone.prototype.defaultOptions.dictRemoveFile = "Remover Archivo";
    Dropzone.prototype.defaultOptions.dictRemoveFileConfirmation = "¿Remover Archivo?";   
    Dropzone.prototype.defaultOptions.dictCancelUpload = "Cancelar Proceso";
    Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "No puedes subir más archivos.";
    Dropzone.prototype.defaultOptions.dictResponseError = "Servidor no responde.";
    Dropzone.confirm = function(question, accepted, rejected) {
        sweetAlertConfirm({ title: `Eliminar`, text: question, confirm: accepted });
    };

    // GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
    $("#formulario").validate({ ignore: '' });
    $("#formulario").submit(function(e) {
        e.preventDefault();
        if ($(this).valid() === false) return false;

        if (myDropzone.getQueuedFiles().length === 0) {
            var blob = new Blob();
            blob.upload = { 'chunked': myDropzone.defaultOptions.chunking };
            myDropzone.uploadFile(blob);
        } else {
            myDropzone.processQueue();
        }
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
    $('#modalLabel').html('Agregar Banner');
    $("#dropurl").val('cargar');
    $('#eliminar').hide();
    resetForm('#formulario');
});

function resetForm(form) {
    $(form).trigger('reset');
    resetValidateForm(form);
    $("#contenDrop").load("./dropzone-banner.html?v="+getRandomInt(1, 9999));

    // setTimeout(function() {
    //         $("#codigo").focus();
    //     }, 200);
}

// ELIMINAR
$('#eliminar').click(function() {
    var id = $("#id_banner").val();
    var nombre = 'Banner';

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar: ${nombre}?`,
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

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Banner');
    $('#eliminar').show();
    $("#dropurl").val('editar');
    $("#id_banner").val(row.id_banner);
    $("#link").val(row.link);
    // Buscar fotos
    $.ajax({
        url: url,
        type: 'POST',
        data: { q: 'leer_fotos', id_banner: row.id_banner },
        dataType: 'json',
        success: function(response) {
            $.each(response, function(key, value) {
                if (value.name != "") {
                    var mockFile = { name: value.name, size: value.size };
                    myDropzone.emit('addedfile', mockFile);
                    myDropzone.emit('thumbnail', mockFile, value.path);
                    myDropzone.emit('complete', mockFile);
                    myDropzone.files.push(mockFile);
                }
            });
        }
    });

    $('#modal').modal('show');
}
