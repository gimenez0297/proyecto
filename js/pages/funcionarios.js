var url = 'inc/funcionarios-data';
var url_listados = 'inc/listados';
var img_default = "dist/images/sin-foto.jpg";

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
    ].join('&nbsp');

}

window.accionesFila = {
    'click .editar': function(e, value, row, index) {
        editarDatos(row);
    },
    'click .ver_cv': function(e, value, row, index) {
        window.open(row.curriculum);
    },
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
                    data: { id_funcionario: row.id_funcionario, estado: estado },
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
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    showFullscreen: true,
    mobileResponsive: true,
    height: $(window).height() - 120,
    pageSize: Math.floor(($(window).height() - 120) / 50),
    sortName: "razon_social",
    sortOrder: 'asc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_funcionario', title: 'ID Funcionario', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ci', title: 'CI', align: 'left', valign: 'middle', sortable: true },
            { field: 'razon_social', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'telefono', title: 'Teléfono', align: 'left', valign: 'middle', sortable: true },
            { field: 'celular', title: 'Celular', align: 'left', valign: 'middle', sortable: true },
            { field: 'puesto', title: 'Puesto', align: 'left', valign: 'middle', sortable: true },
            { field: 'direccion', title: 'Dirección', align: 'left', valign: 'middle', sortable: true, visible: false, cellStyle: bstTruncarColumna },
            // { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha_alta_format', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: false, visible: true },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila, formatter: iconosFila },
            { field: 'fecha_baja', visible: false },
            { field: 'curriculum', visible: false },
            { field: 'antecedente', visible: false },
            { field: 'id_usuario', visible: false },
            { field: 'username', visible: false }
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
});

// Altura de tabla automatica
$(document).ready(function() {
    $(window).bind('resize', function(e) {
        if (window.RT) clearTimeout(window.RT);
        window.RT = setTimeout(function() {
            $("#tabla").bootstrapTable('refreshOptions', {
                height: $(window).height() - 120,
                pageSize: Math.floor(($(window).height() - 120) / 50),
            });
        }, 100);
    });
});

// Documentos
function iconosFilaDocumento(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm ver_doc" title="Ver"><i class="fas fa-file-pdf text-white"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_doc" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('&nbsp');
}

window.accionesFilaDocumento = {
    'click .eliminar_doc': function (e, value, row, index) {
        sweetAlertConfirm({
            title: `Eliminar`,
            text: `¿Esta seguro de eliminar el documento seleccionado?`,
            closeOnConfirm: true,
            confirm: function() {
                if(row.estado == 0){
                    $('#tabla_documentos').bootstrapTable('hideRow', {index:index});
                    $("#tabla_documentos").bootstrapTable('updateRow', {
                        index: index,
                        row: {
                            estado: 2 
                        }
                    });
                }else{
                    $('#tabla_documentos').bootstrapTable('removeByUniqueId', row.id_);
                }
            }
        });
    },
    'click .ver_doc': function (e, value, row, index) {
        window.open(row.ver);
    }
}

$("#documento").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_documento").val(cleanFileName);
});

$("#tabla_documentos").bootstrapTable({
    classes: 'table table-hover table-condensed-sm',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 150,
    sortName: "documento",
    sortOrder: 'asc',
    uniqueId: 'id_',
    columns: [
        [
            { field: 'id_', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'descripcion', title: 'Descripción', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'file', title: 'File', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ver', title: 'Ver', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaDocumento,  formatter: iconosFilaDocumento, width: 100 }
        ]
    ]
});

function cargararchivos() {

    var tableData = $('#tabla_documentos').bootstrapTable('getData');
    var descripcion = $('#descripcion').val();
    var date = new Date();
    var output = String(date.getDate()).padStart(2, '0') + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + date.getFullYear();
    var file = document.querySelector('#documento').files[0];
    getBase64(file);

    setTimeout(function()
        {
            $('#tabla_documentos').bootstrapTable('insertRow', {
            index: 1,
                row: {
                    id_: new Date().getTime(),
                    descripcion: descripcion,
                    fecha: output,
                    file: $("#url_documento").val(),
                    estado: 1,
                    ver: $("#change_documento").val(),
                }
            });
        },500);
    
    $('#descripcion').val('');
    $('#documento').next('.custom-file-label').html('Seleccionar Archivo');
};

$('#agregar-documento').click(function() {
    cargararchivos();
});

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Funcionario');
    $('#formulario').attr('action', 'cargar');
    $('#ver_cv').hide();
    $('#ver_documento').hide();
    $(".comi").addClass("d-none");
    $('#eliminar').hide();
    $('#eliminarfoto').hide();
    $("#id_usuario").prop('disabled', false);
    $('#eliminar_documento').hide();
    $('#img_preview').attr('src', img_default);
    $("#archivo_cv").val(null);
    resetForm('#formulario');
});


$('#eliminarfoto').click(function() {
    $('#foto').val('');
    $('#change_foto').val('1');
    $('#img_preview').attr('src', img_default);
    $('#eliminarfoto').hide();
    $('#foto').next('.custom-file-label').html('Seleccionar Archivo');
});

$('#modal').on('shown.bs.modal', function(e) {
    $("form input[type!='hidden']:first").focus();
});

function resetForm(form) {
    $(form).trigger('reset');
    $('.custom-file-label').html('Seleccionar Archivo');
    $(form).find('select').val(null).trigger('change');
    $('#tab a[href="#datos-basicos"]').tab('show');
    $('#tabla_documentos').bootstrapTable('removeAll');
    resetValidateForm(form);
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#img_preview').attr('src', e.target.result);
            $('#change_foto').val('1');
            $('#eliminarfoto').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#foto").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    readURL(this);
});

$("#archivo_cv").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    console.log(cleanFileName);
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_cv").val(cleanFileName);
});

$("#logo_ant").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_logo_ant").val(cleanFileName);
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").validate({ ignore: '' });
$("#formulario").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;

    var data = new FormData(this);
    data.append('tabla_documento', JSON.stringify($('#tabla_documentos').bootstrapTable('getData')));
    $.ajax({
        url: url + '?q=' + $(this).attr("action"),
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
    var id = $("#hidden_id").val();
    var nombre = $("#razon_social").val();
    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Funcionario: ${nombre}?`,
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
                success: function(data, status, xhr) {
                    NProgress.done();
                    alertDismissJS(data.mensaje, data.status);
                    if (data.status == 'ok') {
                        $('#modal').modal('hide');
                        $('#tabla').bootstrapTable('refresh');
                    }
                },
                error: function(jqXhr) {
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });
});

$('#btn_buscar').on('click', async function(e) {
    var ruc = $('#ci').val();

    if ($('#modal').is(':visible') && ruc) {
        var data = await buscarCI(ruc);
        if (data.cedula) {
            $('#ci').val(data.cedula);
            $('#razon_social').val(data.apellido + ' ' + data.nombre_solo);
            $('#telefono').val(data.telefono);
            $('#direccion').val(data.direccion);
        } else {
            alertDismissJsSmall('CI no encontrado', 'error', 2000, () => $('#ci').focus().select());
        }
    }
});

// Acciones por teclado
$(window).on('keydown', function(event) {
    switch (event.which) {
        // F1 Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
            // F2 Buscar RUC / CI
        case 113:
            event.preventDefault();
            $('#btn_buscar').click();
            break;
    }
});

function editarDatos(row) {
    resetForm('#formulario');
    $('.custom-file-label').html('Seleccionar Archivo');
    $('#tab a[href="#datos-basicos"]').tab('show');
    $('#modalLabel').html('Editar Funcionario');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#hidden_id").val(row.id_funcionario);
    $("#razon_social").val(row.razon_social);
    $("#ci").val(row.ci);
    $("#telefono").val(row.telefono);
    $("#celular").val(row.celular);
    $("#direccion").val(row.direccion);
    $("#fecha_alta").val(row.fecha_alta);
    $("#fecha_baja").val(row.fecha_baja);
    $("#salario").val(separadorMiles(row.salario_real));
    $("#nro_cuenta").val(row.nro_cuenta);
    $("#hijos").val(row.cantidad_hijos);
    $("#referencia").val(row.referencia);
    $("#aporte").val(row.aporte);
    $('#aporte').trigger('change');

    $('#ver_documento').hide();
    $('#eliminar_documento').hide();
    $('#ver_cv').hide();
    $('#eliminar_cv').hide();

    if(row.comision == 1){
        $(".comi").removeClass('d-none');
        $("#comision").val(separadorMiles(row.comision_func));
    }else{
        $(".comi").addClass('d-none');
    }

    if (row.curriculum) {
        $('#ver_cv').show();

        $('#eliminar_cv').show();
        $('#ver_cv').attr("href", row.curriculum);
    }
    if (row.antecedente) {
        $('#ver_documento').show();
        $('#eliminar_documento').show();
        $('#ver_documento').attr("href", row.antecedente);
    }

    $('#foto').val('');
    $('#change_foto').val('0');
    if (row.foto_perfil) {
        // Se usa un número aleatorio para evitar cache
        $('#img_preview').attr('src', `${row.foto_perfil}?v=${getRandomInt(1, 9999)}`);
        $('#eliminarfoto').show();
        
    } else {
        $('#img_preview').attr('src', img_default);
        $('#eliminarfoto').hide();
        
    }

    if (row.id_distrito) {
        $("#id_ciudad").append(new Option(row.nombre, row.id_distrito, true, true)).trigger('change');
    }
    if (row.id_puesto) {
        $("#id_puesto").append(new Option(row.puesto, row.id_puesto, true, true)).trigger('change');
    }
    if (row.id_estado) {
        $("#id_estado").append(new Option(row.descripcion, row.id_estado, true, true)).trigger('change');
    }
    if (row.id_banco) {
        $("#id_banco").append(new Option(row.banco, row.id_banco, true, true)).trigger('change');
    }
    if (row.id_sucursal) {
        $("#id_sucursal").append(new Option(row.sucursal, row.id_sucursal, true, true)).trigger('change');
    }
    if(row.id_usuario){
        $("#id_usuario").select2('trigger', 'select', { data: { id: row.id_usuario, text: row.username }});
        $("#id_usuario").prop('disabled', true);
    }else{
        $("#id_usuario").prop('disabled', false);
    }
    
    $('#tabla_documentos').bootstrapTable('refresh', { url: url+'?q=ver_documentos&id_funcionario='+row.id_funcionario });

    $('#eliminar_cv').click(function() {
        var id = row.id_funcionario;
        var nombre = row.razon_social;
        sweetAlertConfirm({
            title: `Eliminar`,
            text: `¿Eliminar el C.V. de : ${nombre}?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    async: false,
                    type: 'POST',
                    url: url,
                    cache: false,
                    data: { q: 'eliminar_cv', id: id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, status, xhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#modal').modal('hide');
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function(jqXhr) {
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    });

    $('#eliminar_documento').click(function() {
        var id = row.id_funcionario;
        var nombre = row.razon_social;
        sweetAlertConfirm({
            title: `Eliminar`,
            text: `¿Eliminar el Antecedente de : ${nombre}?`,
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    async: false,
                    type: 'POST',
                    url: url,
                    cache: false,
                    data: { q: 'eliminar_an', id: id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, status, xhr) {
                        NProgress.done();
                        alertDismissJS(data.mensaje, data.status);
                        if (data.status == 'ok') {
                            $('#modal').modal('hide');
                            $('#tabla').bootstrapTable('refresh');
                        }
                    },
                    error: function(jqXhr) {
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
    });

}

$('#id_ciudad').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'distritos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_distrito, text: obj.nombre }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_puesto').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'puestos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_puesto, text: obj.puesto }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('select2:select', function(){
    $.ajax({
        dataType: 'json',
        async: false,
        type: 'POST',
        url: url,
        cache: false,
        data: { q: 'verificar_comision', id_puesto: this.value },   
        beforeSend: function() {
            NProgress.start();
        },
        success: function (data, status, xhr) {
            NProgress.done();
            $("#comision").val('');

            if(data.status == 'ok' ){
                $(".comi").removeClass("d-none");
            }else{
                $(".comi").addClass("d-none");
                $("#comision").val('');
            }
        },
        error: function (jqXhr) {
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

$('#id_estado').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'estado_civil', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_estado, text: obj.descripcion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#id_banco').select2({
    dropdownParent: $("#modal"),
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
});

$('#documento').change( function(event) {
    var tmppath = URL.createObjectURL(event.target.files[0]); 
    $("#change_documento").val(tmppath);     
});

function getBase64(file) {
   var reader = new FileReader();
   reader.readAsDataURL(file);
   reader.onload = function () {
    //return reader.result;
    $("#url_documento").val(reader.result);
    console.log(reader.result);
   };
   reader.onerror = function (error) {
     //console.log('Error: ', error);
   };
}

$('#id_sucursal').select2({
    dropdownParent: $("#modal"),
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
});

$('#id_usuario').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'usuarios_funcionarios', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id, text: obj.username }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#aporte').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    tags: true,
    maximumInputLength: 4,
    //minimumResultsForSearch: Infinity,
});
