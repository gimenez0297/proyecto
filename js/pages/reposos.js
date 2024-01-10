var url = 'inc/reposos-data';
var url_listados = 'inc/listados';
var img_default = "dist/images/sin-foto.jpg";

$(window).on('keydown', function (event) { 
    switch(event.which) {
        // F1 - Agregar
        case 112:
            event.preventDefault();
            $('#agregar').click();
            break;
    }
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-primary btn-sm cambiar-estado mr-1" title="Cambiar Estado"><i class="fas fa-sync-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>',
    ].join('');
}

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

// Rango: dia actual
// cb(moment(), moment());

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
        [meses[moment().subtract(5, 'month').format("M")]] : [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')]
    }
}, cb);

$('#filtro_fecha').on('apply.daterangepicker', function(ev, picker) { 
    fechaIni = picker.startDate.format('DD/MM/YYYY');
    fechaFin = picker.endDate.format('DD/MM/YYYY');
    $("#desde").val(picker.startDate.format('YYYY-MM-DD')); 
    $("#hasta").val(picker.endDate.format('YYYY-MM-DD'));

    $('#filtro_fecha').change();
}).on('change', function() {
    $('#tabla').bootstrapTable('refresh', { url: url+'?q=ver&desde='+$('#desde').val()+'&hasta='+$('#hasta').val() });
});

window.accionesFila = {
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
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
                    data: { id_reposo: row.id_reposo, estado: estado },
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
    // url: url + '?q=ver',
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
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    sortName: "id_reposo",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_reposo', title: 'ID reposo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'ci', title: 'C.I.', align: 'right', valign: 'middle', sortable: true },
            { field: 'desde', title: 'Desde', align: 'right', valign: 'middle', sortable: true },
            { field: 'hasta', title: 'Hasta', align: 'right', valign: 'middle', sortable: true },
            { field: 'observacion', title: 'Observación', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
            { field: 'documento', visible: false, swichable: false },
            { field: 'usuario', title: 'Usuario Carga', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'fecha', title: 'Fecha Alta', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFila,  formatter: iconosFila }
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

    $('.accept-extensions').popover({ content: 'Solo se aceptan archivos con extensión PDF, JPG, JPEG y PNG.', trigger: 'hover' });

    $('.eliminar_archivo')
    .off('click')
    .on('click', function () {
        $('#archivo').val(null).trigger('change');
        $('#archivo').val(null).prop('files')[0];
    });
});

$('#agregar').click(function() {
    $('#modalLabel').html('Agregar Reposo');
    $('#formulario').attr('action', 'cargar');
    $('#eliminar').hide();
    $('.eliminar_archivo').hide();
//    $('#eliminararchivo').hide();
    $("#id_funcionario").attr('readonly', false);
    resetForm('#formulario');
    $('#fecha_desde').val(moment().format('YYYY-MM-DD'));
    $('#fecha_hasta').val(moment().format('YYYY-MM-DD'));
});

$('#modal')
.on('shown.bs.modal', function (e) {
    $("#id_funcionario").focus();
})
.on('hide.bs.modal', function (e) {
    URL.revokeObjectURL($('#archivo').closest('.input-group').find('[data-action="view"]').attr('href'));
    $('#archivo').closest('.input-group').find('[data-action="view"]').prop('href', '#').removeAttr('target').addClass('disabled').attr('disabled', true);
    $('#pdf_preview').hide();
    $('#pdf_preview').removeAttr('src');
    $('#img_preview').show();
    $('#img_preview').prop('src', img_default);
});

$('#archivo')
.on('change', function () {
    let thisInput = $(this);
    let divInputGroup = thisInput.closest('.input-group');
    let file = thisInput.prop('files')[0];
    let filePath = this.value;

    function setViewAction(divInputGroup, file) {
        let btnFileView = divInputGroup.find('[data-action="view"]');
        let fileShowConfig = {href: '#', target: '_self'};
        
        if (file) {
            let gen_url = URL.createObjectURL(file);
            btnFileView.removeAttr('disabled').removeClass('disabled');
            fileShowConfig = {href: gen_url, target: '_blank'};

            if (file.type.indexOf('pdf') !== -1) {
                $('#pdf_preview').show();
                $('#img_preview').hide();
                $('#pdf_preview').attr('src', gen_url);
            } else {
                $('#pdf_preview').hide();
                $('#img_preview').show();
                $('#img_preview').attr('src', gen_url);
            }
            $('.eliminar_archivo').show();
            
        }else{
            btnFileView.prop('disabled', true).addClass('disabled');
            $('.eliminar_archivo').hide();
            URL.revokeObjectURL(btnFileView.attr('href'));
            $('#pdf_preview').hide();
            $('#img_preview').show();
            $('#img_preview').prop('src', img_default);
        }
        $('#change_archivo').val('1');

        btnFileView.prop(fileShowConfig);
    }
    function setLabel(filePath, inputTag) {
        let fileLabel = 'Seleccionar Archivo...';
        if (filePath) {
            fileLabel = filePath.replace(/^.*[\\\/]/, '');
        }
        inputTag.next('label').text(fileLabel);
    }

    divInputGroup.find('[data-action]').prop('disabled', !file);

    setLabel(filePath, thisInput);
    setViewAction(divInputGroup, file);
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');
    $('#tab a[href="#datos-basicos"]').tab('show');
    resetValidateForm(form);
}

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").validate({ ignore: '' });
$("#formulario").submit(function(e){
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
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
        success: function(data, textStatus, jQxhr){
            NProgress.done();
            alertDismissJS(data.mensaje, data.status);
            if (data.status == 'ok') {
                $('#modal').modal('hide');
                $('#tabla').bootstrapTable('refresh');
            }
        },
        error: function(jqXhr, textStatus, errorThrown){
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// ELIMINAR
$('#eliminar').click(function() {
    var id_reposo = $("#id_reposo").val();
    var id = $("#id_funcionario").val();
    //var nombre = ('select[id="id_funcionario"] option:selected').text()

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Reposo?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'json',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: { q: 'eliminar', id_reposo },	
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
    $('#modalLabel').html('Editar Reposo');
    $('#formulario').attr('action', 'editar');
    $('#tab a[href="#datos-basicos"]').tab('show');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_reposo").val(row.id_reposo);
    $("#fecha_desde").val(row.fecha_desde);
    $("#fecha_hasta").val(row.fecha_hasta);
    $("#observacion").val(row.observacion);
    $("#id_funcionario").attr('readonly', true);

    $('#archivo').val('');
    $('#change_archivo').val('0');
    if (row.documento) {
        let urlCompleta = window.location.href;
        let directorioActual = urlCompleta.split('/').slice(0, -1).join('/');
        let doc = directorioActual + '/' + row.documento;

        function obtenerTipoArchivo(url) {
            let tipoArchivo = null;
            
            $.ajax({
                url: url,
                type: 'HEAD',
                async: false,
                success: function(data, status, xhr) {
                    let contentType = xhr.getResponseHeader('Content-Type');
                    tipoArchivo = contentType.split('/')[1];
                },
                error: function() {
                    tipoArchivo = false;
                }
            });
            
            return tipoArchivo;
        }
        
        let tipo_archivo = obtenerTipoArchivo(doc);

        if (tipo_archivo) {
            if (tipo_archivo.indexOf('pdf') !== -1) {
                console.log('pdf');
                $('#pdf_preview').attr('src', `${doc}`);
                $('#pdf_preview').show();
                $('#img_preview').hide();
            } else {
                $('#img_preview').show();
                $('#pdf_preview').hide();
                $('#img_preview').attr('src', `${doc}`);
            }
        }
        

        $('#archivo').closest('.input-group').find('[data-action="view"]').prop({href: doc, target: '_blank'});
        $('#archivo').closest('.input-group').find('[data-action="view"]').removeAttr('disabled').removeClass('disabled');
        $('.eliminar_archivo').show();
    } else {
        $('#img_preview').attr('src', img_default);
        $('.eliminar_archivo').hide();
    }

    if (row.id_funcionario) {
        $("#id_funcionario").append(new Option(row.funcionario, row.id_funcionario, true, true)).trigger('change');
    }
}

$('#id_funcionario').select2({
    dropdownParent: $("#modal"),
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
            return { q: 'funcionarios', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_funcionario, text: obj.funcionario }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#filtro_fecha').change();