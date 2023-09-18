var url = 'inc/legajos-data';
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
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar mr-1" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm imprimir_legajo mr-1" title="Ver PDF"><i class="fas fa-file-pdf"></i></button>',
    ].join('');
}

window.accionesFila = {
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    },
    'click .imprimir_legajo': function (e, value, row, index) {
        var param = {id_funcionario: row.id_funcionario};
        OpenWindowWithPost("imprimir-legajo", "toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=yes,width=1324,height=728", "ImprimirLiquidaciones", param);
    }
}

$("#archivos_escuela").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_escuela").val(cleanFileName);
});

$("#archivos_universidad").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_universidad").val(cleanFileName);
});

$("#archivos_curso").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_curso").val(cleanFileName);
});

$("#archivos_experiencia").change(function() {
    var fileName = $(this).val();
    var cleanFileName = fileName.replace('C:\\fakepath\\', " ");
    $(this).next('.custom-file-label').html(cleanFileName);
    $("#change_experiencia").val(cleanFileName);
});

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
    sortName: "id_funcionario",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            { field: 'id_funcionario', title: 'ID reposo', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'ci', title: 'C.I.', align: 'left', valign: 'middle', sortable: true },
            { field: 'funcionario', title: 'Funcionario', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'area', title: 'Área', align: 'left', valign: 'middle', sortable: true },
            { field: 'estado_str', title: 'Estado', align: 'center', valign: 'middle', sortable: true, formatter: bstFormatterEstado, width: 200 },
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

    $('#tabla').bootstrapTable('refresh');
});

function resetForm(form) {
    $(form).trigger('reset');
    // $(form).find('select').val(null).trigger('change');
    resetValidateForm(form);
}

// GUARDAR ESCUELA
$('#agregar_escuela').on('click', () => $('#formulario_escuela').submit());
$("#formulario_escuela").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
    data.append('id_funcionario', $('#id_funcionario').val());

    $.ajax({
        url: url + '?q=cargar_escuela',
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
            if (data.status == 'ok') {
                $('#tabla_escuela').bootstrapTable('refresh');
                $('#archivos_escuela').next('.custom-file-label').html('Seleccionar Archivo');
                alertDismissJS(data.mensaje, data.status);
                resetForm('#formulario_escuela');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// GUARDAR UNIVERSIDAD
$('#agregar_universidad').on('click', () => $('#formulario_universidad').submit());
$("#formulario_universidad").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
    data.append('id_funcionario', $('#id_funcionario').val());

    $.ajax({
        url: url + '?q=cargar_universidad',
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
            if (data.status == 'ok') {
                $('#tabla_universidad').bootstrapTable('refresh');
                $('#archivos_universidad').next('.custom-file-label').html('Seleccionar Archivo');
                alertDismissJS(data.mensaje, data.status);
                resetForm('#formulario_universidad');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// GUARDAR CURSO
$('#agregar_curso').on('click', () => $('#formulario_curso').submit());
$("#formulario_curso").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
    data.append('id_funcionario', $('#id_funcionario').val());

    $.ajax({
        url: url + '?q=cargar_curso',
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
            if (data.status == 'ok') {
                $('#tabla_cursos').bootstrapTable('refresh');
                $('#archivos_curso').next('.custom-file-label').html('Seleccionar Archivo');
                alertDismissJS(data.mensaje, data.status);
                resetForm('#formulario_curso');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// GUARDAR EXPERIENCIA
$('#agregar_experiencia').on('click', () => $('#formulario_experiencia').submit());
$("#formulario_experiencia").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
    data.append('id_funcionario', $('#id_funcionario').val());

    $.ajax({
        url: url + '?q=cargar_experiencia',
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
            if (data.status == 'ok') {
                $('#tabla_experiencias').bootstrapTable('refresh');
                $('#archivos_experiencia').next('.custom-file-label').html('Seleccionar Archivo');
                alertDismissJS(data.mensaje, data.status);
                resetForm('#formulario_experiencia');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// GUARDAR IDIOMA
$('#agregar_idioma').on('click', () => $('#formulario_idioma').submit());
$("#formulario_idioma").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
    data.append('id_funcionario', $('#id_funcionario').val());

    $.ajax({
        url: url + '?q=cargar_idioma',
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
            if (data.status == 'ok') {
                $('#tabla_idiomas').bootstrapTable('refresh');
                alertDismissJS(data.mensaje, data.status);
                resetForm('#formulario_idioma');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

// GUARDAR FAMILIAR
$('#agregar_familia').on('click', () => $('#formulario_familia').submit());
$("#formulario_familia").submit(function(e) {
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = new FormData(this);
    data.append('id_funcionario', $('#id_funcionario').val());

    $.ajax({
        url: url + '?q=cargar_familiar',
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
            if (data.status == 'ok') {
                $('#tabla_familias').bootstrapTable('refresh');
                alertDismissJS(data.mensaje, data.status);
                resetForm('#formulario_familia');
            } else {
                alertDismissJS(data.mensaje, data.status);
            }
        },
        error: function(jqXhr, textStatus, errorThrown) {
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

function editarDatos(row) {
    resetForm('#formulario_escuela');
    $('#modalLabel').html('Editar Legajo');
    $('#formulario').attr('action', 'editar');
    $('#tab a[href="#datos-basicos"]').tab('show');
    $('#eliminar').show();
    $('#modal').modal('show');

    $("#id_funcionario").val(row.id_funcionario);
    $('#tabla_escuela').bootstrapTable('refresh', { url: url+'?q=ver_escuelas&id_funcionario='+row.id_funcionario });
    $('#tabla_idiomas').bootstrapTable('refresh', { url: url+'?q=ver_idiomas&id_funcionario='+row.id_funcionario });
    $('#tabla_universidad').bootstrapTable('refresh', { url: url+'?q=ver_universidad&id_funcionario='+row.id_funcionario });
    $('#tabla_cursos').bootstrapTable('refresh', { url: url+'?q=ver_curso&id_funcionario='+row.id_funcionario });
    $('#tabla_experiencias').bootstrapTable('refresh', { url: url+'?q=ver_experiencia&id_funcionario='+row.id_funcionario });
    $('#tabla_familias').bootstrapTable('refresh', { url: url+'?q=ver_familiar&id_funcionario='+row.id_funcionario });

}

function iconosFilaEscuela(value, row, index) {
    let disabled = (row.documento == null) ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver_doc mr-1" title="Ver" ${disabled}><i class="fas fa-file-pdf text-white"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_escuela" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaEscuela = {
    'click .eliminar_escuela': function(e, value, row, index) {
        let id = row.id;

        sweetAlertConfirm({
            title: `¿Eliminar el registro seleccionado?`,
            text: 'Esta acción no se puede revertir.',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_escuela',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_escuela').bootstrapTable('refresh');
                            alertDismissJS(data.mensaje, data.status);
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
        });
    },
    'click .ver_doc': function (e, value, row, index) {
        window.open(row.documento);
    }
}

$("#tabla_escuela").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 180,
    sortName: "documento",
    sortOrder: 'asc',
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'escuela', title: 'Escuela/Colegio', align: 'left', valign: 'middle', sortable: true, cellStyle: bstTruncarColumna },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true },
            { field: 'nivel', title: 'Nivel', align: 'left', valign: 'middle', sortable: true },
            { field: 'anho_ingreso', title: 'Año Ingreso', align: 'right', valign: 'middle', sortable: true },
            { field: 'anho_egreso', title: 'Año Egreso', align: 'right', valign: 'middle', sortable: true },
            { field: 'documento', title: 'Blob', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaEscuela,  formatter: iconosFilaEscuela, width: 100 }
        ]
    ]
});

function iconosFilaUniversidad(value, row, index) {
    let disabled = (row.documento == null) ? 'disabled' : '';

    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver_doc mr-1" title="Ver" ${disabled}><i class="fas fa-file-pdf text-white"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_universidad" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaUniversidad = {
    'click .eliminar_universidad': function(e, value, row, index) {
        let id = row.id;

        sweetAlertConfirm({
            title: `¿Eliminar el registro seleccionado?`,
            text: 'Esta acción no se puede revertir.',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_universidad',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_universidad').bootstrapTable('refresh');
                            alertDismissJS(data.mensaje, data.status);
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
        });
    },
    'click .ver_doc': function (e, value, row, index) {
        window.open(row.documento);
    }
}

$("#tabla_universidad").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 180,
    sortName: "documento",
    sortOrder: 'asc',
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'universidad', title: 'Universidad', align: 'left', valign: 'middle', sortable: true },
            { field: 'carrera', title: 'Carrera', align: 'left', valign: 'middle', sortable: true },
            { field: 'titulo', title: 'Titulo', align: 'left', valign: 'middle', sortable: true },
            { field: 'estado', title: 'Estado', align: 'left', valign: 'middle', sortable: true },
            { field: 'nivel', title: 'Nivel', align: 'left', valign: 'middle', sortable: true },
            { field: 'ingreso_egreso', title: 'Año I/E', align: 'left', valign: 'middle', sortable: true },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaUniversidad, formatter: iconosFilaUniversidad, width: 100 }
        ]
    ]
});

function iconosFilaCurso(value, row, index) {
    let disabled = (row.documento == null) ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver_doc mr-1" title="Ver" ${disabled}><i class="fas fa-file-pdf text-white"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_curso" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaCurso = {
    'click .eliminar_curso': function(e, value, row, index) {
        let id = row.id;

        sweetAlertConfirm({
            title: `¿Eliminar el registro seleccionado?`,
            text: 'Esta acción no se puede revertir.',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_curso',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_cursos').bootstrapTable('refresh');
                            alertDismissJS(data.mensaje, data.status);
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
        });
    },
    'click .ver_doc': function (e, value, row, index) {
        window.open(row.documento);
    }
}

$("#tabla_cursos").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 180,
    sortName: "documento",
    sortOrder: 'asc',
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'instituto', title: 'Universidad', align: 'left', valign: 'middle', sortable: true },
            { field: 'curso', title: 'Curso', align: 'left', valign: 'middle', sortable: true },
            { field: 'tipo', title: 'Tipo', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha', title: 'Fecha', align: 'left', valign: 'middle', sortable: true },
            { field: 'duracion', title: 'Duración Hs.', align: 'right', valign: 'middle', sortable: true },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaCurso, formatter: iconosFilaCurso, width: 100 }
        ]
    ]
});

function iconosFilaExperiencia(value, row, index) {
    let disabled = (row.documento == null) ? 'disabled' : '';
    return [
        `<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm ver_doc mr-1" title="Ver" ${disabled}><i class="fas fa-file-pdf text-white"></i></button>`,
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_exp" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFilaExperiencia = {
    'click .eliminar_exp': function(e, value, row, index) {
        let id = row.id;

        sweetAlertConfirm({
            title: `¿Eliminar el registro seleccionado?`,
            text: 'Esta acción no se puede revertir.',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_experiencia',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_experiencias').bootstrapTable('refresh');
                            alertDismissJS(data.mensaje, data.status);
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
        });
    },
    'click .ver_doc': function (e, value, row, index) {
        window.open(row.documento);
    }
}

$("#tabla_experiencias").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 180,
    sortName: "documento",
    sortOrder: 'asc',
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'instituto', title: 'Instituto', align: 'left', valign: 'middle', sortable: true},
            { field: 'cargo', title: 'Cargo', align: 'left', valign: 'middle', sortable: true },
            { field: 'vinculo', title: 'Vinculo', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_desde', title: 'Desde', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_hasta', title: 'Hasta', align: 'left', valign: 'middle', sortable: true },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFilaExperiencia, formatter: iconosFilaExperiencia, width: 100 }
        ]
    ]
});

function iconosIdiomas(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_idioma" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesIdiomas = {
    'click .eliminar_idioma': function(e, value, row, index) {
        let id = row.id;

        sweetAlertConfirm({
            title: `¿Eliminar el registro seleccionado?`,
            text: 'Esta acción no se puede revertir.',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_idioma',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_idiomas').bootstrapTable('refresh');
                            alertDismissJS(data.mensaje, data.status);
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
        });
    }
}

$("#tabla_idiomas").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 180,
    sortName: "idioma",
    sortOrder: 'asc',
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'idioma', title: 'Idioma', align: 'left', valign: 'middle', sortable: true },
            { field: 'lee', title: 'Lee', align: 'left', valign: 'middle', sortable: true },
            { field: 'habla', title: 'Habla', align: 'left', valign: 'middle', sortable: true },
            { field: 'escribe', title: 'Escribe', align: 'left', valign: 'middle', sortable: true },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesIdiomas,  formatter: iconosIdiomas, width: 100 }
        ]
    ]
});

function iconosFamiliar(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-danger btn-sm eliminar_familiar" title="Eliminar"><i class="fas fa-trash"></i></button>',
    ].join('');
}

window.accionesFamiliar = {
    'click .eliminar_familiar': function(e, value, row, index) {
        let id = row.id;

        sweetAlertConfirm({
            title: `¿Eliminar el registro seleccionado?`,
            text: 'Esta acción no se puede revertir.',
            confirmButtonText: 'Eliminar',
            confirmButtonColor: 'var(--danger)',
            confirm: function() {
                $.ajax({
                    dataType: 'json',
                    url: url + '?q=eliminar_familiar',
                    type: 'post',
                    data: { id },
                    beforeSend: function() {
                        NProgress.start();
                    },
                    success: function(data, textStatus, jQxhr) {
                        NProgress.done();
                        if (data.status == 'ok') {
                            $('#tabla_familias').bootstrapTable('refresh');
                            alertDismissJS(data.mensaje, data.status);
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
        });
    }
}

$("#tabla_familias").bootstrapTable({
    toolbar: '#toolbar_archivos',
    toolbarAlign: 'right',
    sidePagination: 'client',
    classes: 'table table-hover table-condensed-xs',
    striped: true,
    icons: 'icons',
    mobileResponsive: true,
    height: 180,
    sortName: "nombre",
    sortOrder: 'asc',
    uniqueId: 'id',
    columns: [
        [
            { field: 'id', title: 'ID', align: 'left', valign: 'middle', sortable: true, visible: false },
            { field: 'vinculo', title: 'Vinculo', align: 'left', valign: 'middle', sortable: true },
            { field: 'ci', title: 'C.I', align: 'left', valign: 'middle', sortable: true },
            { field: 'nombre', title: 'Nombre', align: 'left', valign: 'middle', sortable: true },
            { field: 'apellido', title: 'Apellido', align: 'left', valign: 'middle', sortable: true },
            { field: 'fecha_nacimiento', title: 'Fecha Nac.', align: 'left', valign: 'middle', sortable: true },
            { field: 'sexo', title: 'Sexo', align: 'left', valign: 'middle', sortable: true },
            { field: 'acciones', title: 'Acciones', align: 'center', valign: 'middle', sortable: false, events: accionesFamiliar,  formatter: iconosFamiliar, width: 150 }
        ]
    ]
});

$('#universidad').select2({
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
            return { q: 'universidades', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_organizacion, text: obj.organizacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#instituto_curso').select2({
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
            return { q: 'institutos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_organizacion, text: obj.organizacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});


$('#instituto').select2({
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
            return { q: 'institutos', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_organizacion, text: obj.organizacion }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#carrera').select2({
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
            return { q: 'carreras', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_carrera, text: obj.carrera }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#idioma').select2({
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
            return { q: 'idiomas', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_idioma, text: obj.idioma }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});

$('#vinculo_familiar').select2({
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
            return { q: 'vinculos_familiares', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_vinculo_familiar, text: obj.vinculo }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
});



$('#estado_escuela').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#nivel_escuela').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#estado_universidad').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#nivel_universidad').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#tipo_curso').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#vinculo').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#lee').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#habla').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#escribe').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});

$('#sexo').select2({
    dropdownParent: $("#modal"),
    placeholder: 'Seleccione',
    width: 'style',
    allowClear: false,
    selectOnClose: false,
});