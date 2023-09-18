var url = 'inc/configuracion-data.php';

$(document).ready(function(){
    $('[data-toggle="popover"]').popover(); 
});

//GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e) {
    e.preventDefault();

    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url + '?q=editar',
        dataType: 'json',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data, 
        beforeSend: function() {
            NProgress.start();
        },
        success: function(data, textStatus, jQxhr) {
            NProgress.done();

            if (data.status == 'ok') {
                alertDismissJS(data.mensaje, data.status, function(){ 
                    // location.reload(); 
                });
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
})
