var url = 'inc/administrar-roles-data';
var url_listados = 'inc/listados';

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fa fa-pencil-alt"></i></button>'+
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm permisos ml-2" title="Editar Permisos"><i class="fa fa-plus"></i>&nbsp; Permisos</button>'
    ].join('');
}

window.accionesFila = {
    'click .editar': function (e, value, row, index) {
        resetValidateForm("#formulario");
        $('#modalLabel').html('Editar Rol');
        $('#formulario').attr('action', 'editar');
        $('#modal_principal').modal('show');
        $("#hidden_id_rol").val(row.id_rol);
        $("#rol").val(row.rol);
        $("#estado").val(row.estado).trigger('change');
        if (row.id_rol == 1) {
            $("#estado").prop('disabled', true);
            $('#eliminar').hide();
        } else {
            $('#eliminar').show();
            $("#estado").prop('disabled', false);
        }
    },
    'click .permisos': function (e, value, row, index) {
        $('#modalLabelPermisos').html('Permisos');
        $('#modal_permisos').modal('show');
        $("#permisos_id_rol").val(row.id_rol);
        $("#permisos_rol").html(row.rol);
        $('#tabla_permisos').bootstrapTable('refresh', { url: url+'?q=ver_menus&id_rol=' + row.id_rol });
    }
}

//TOOLTIP EN COLUMNAS TRUNCADAS
$('#tabla').on('mouseenter', ".verTooltip", function () {
    var $this = $(this);
    $this.attr('title', $this.text());
});

//CSS PARA TRUNCAR COLUMNAS MUY LARGAS
function truncarColumna(value,row,index, field){
  return {
    classes: 'verTooltip',
    css: {"max-width": "150px" , "white-space": "pre", "overflow": "hidden", "text-overflow": "ellipsis"}
  };
}

function color (value, row, index) {
    switch (value) {
        case 'Activo':
            return { value: 'Pagada', css: {"color": "#187e63", "font-weight": "500" } }
        break;
        case 'Inactivo':
            return { css: {"color": "black", "font-weight": "500" } }
        break;
        default:
            return {};
        break;
    }
}

$("#tabla").bootstrapTable({
    mobileResponsive: true,
    height: $(window).height()-90,
    pageSize: Math.floor(($(window).height()-90)/50),
    sortName: "id_rol",
    sortOrder: 'desc',
    trimOnSearch: false,
    columns: [
        [
            {	field: 'id_rol', align: 'left', valign: 'middle', title: 'ID Rol', sortable: true }, 
            {	field: 'rol', align: 'left', valign: 'middle', title: 'Rol', sortable: true	},
            {	field: 'estado', align: 'center', valign: 'middle', title: 'Estado', formatter: bstFormatterEstado, sortable: true	},
            {	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: accionesFila,  formatter: iconosFila, width: 200	}
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
})

function editarDatos(row) {
    resetValidateForm("#formulario");
    $('#modalLabel').html('Editar Rol');
    $('#formulario').attr('action', 'editar');
    $('#modal_principal').modal('show');
    $("#hidden_id_rol").val(row.id_rol);
    $("#rol").val(row.rol);
    $("#estado").val(row.estado).trigger('change');
    if (row.id_rol == 1) {
        $("#estado").prop('disabled', true);
        $('#eliminar').hide();
    } else {
        $('#eliminar').show();
        $("#estado").prop('disabled', false);
    }
}

$('#agregar').click(function(){
    $('#modalLabel').html('Agregar Rol');
    $('#formulario').attr('action', 'cargar');
    $("#estado").prop('disabled', false);
    limpiarModal();
});

$('#modal_principal').on('show.bs.modal', function (e) {
    if ($('#formulario').attr('action')=="cargar"){
        limpiarModal(e);
        $('#eliminar').hide();
    }
});

$('#modal_principal').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
});

$(".modal-dialog").draggable({
    handle: ".modal-header"
});
        
function limpiarModal(){
    $(document).find('form').trigger('reset');
    $('#estado').val('Activo').trigger('change');
    resetValidateForm("#formulario");
}

//GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
$("#formulario").submit(function(e){
    e.preventDefault();
    if ($(this).valid() === false) return false;
    var data = $(this).serializeArray();
    $.ajax({
        url: url+'?q='+$(this).attr("action"),
        dataType: 'html',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: data,
        beforeSend: function(){
            NProgress.start();
        },
        success: function(datos, textStatus, jQxhr){
            var n = datos.toLowerCase().indexOf("error");
            if (n == -1) {
                $('#modal_principal').modal('hide');
                alertDismissJS(datos, "ok");
                $('#tabla').bootstrapTable('refresh');
            } else {
                alertDismissJS(datos, "error");
            }
            NProgress.done();
        },
        error: function(jqXhr, textStatus, errorThrown){
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});
    

//ELIMINAR
$('#eliminar').click(function(){
    var rol = $("#rol").val();

    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Rol: ${rol}?`,
        closeOnConfirm: false,
        confirm: function(){
            $.ajax({
                dataType: 'html',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: {q: 'eliminar', id: $("#hidden_id_rol").val(), rol: rol },
                beforeSend: function(){
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    var n = data.toLowerCase().indexOf("error");
                    if (n == -1) {
                        swal.close();
                        $('#modal_principal').modal('hide');
                        $('#tabla').bootstrapTable('refresh');
                        alertDismissJS(data, "ok");
                    } else {
                        alertDismissJS(data, "error");
                    }
                    NProgress.done();
                },
                error: function (jqXhr) {
                    NProgress.done();
                    alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                }
            });
        }
    });
});

$("#estado").select2({
    theme: "bootstrap4",
    width: 'style',
    selectOnClose: true,
    minimumResultsForSearch: Infinity,
});

////// EDITAR PERMISOS /////
function inputCheckbox (clase, title, value) {
    if (value == "1") var checked = "checked"; else var checked = "";
    return [
        '<input type="checkbox" onclick="javascript:void(0)" class="' + clase + '" title="' + title + '"' + checked +'>'
    ].join('');
}

function checkboxAcceso(value, row, index) {
    return inputCheckbox('acceso', 'Acceso a la página', value);
}
function checkboxInsertar(value, row, index) {
    return inputCheckbox('insertar', 'Insertar datos', value);
}
function checkboxEditar(value, row, index) {
    return inputCheckbox('editar', 'Modificar datos', value);
}
function checkboxEliminar(value, row, index) {
    return inputCheckbox('eliminar', 'Eliminar datos', value);
}
function checkboxTodos(value, row, index) {
    return inputCheckbox('todos', 'Todos los permisos', value);
}

// Actualiza los persmisos del menu
function actualizarPermisos (field, value, row) {
    let id = row.id_menu;
    let new_value = value == '1' ? '0' : '1';
    let new_row = row;
    new_row[field] = new_value;

    if (field == 'todos' || (field == 'acceso' && new_value == '0')) {
        new_row = {
            acceso: new_value,
            insertar: new_value,
            editar: new_value,
            eliminar: new_value,
            todos: new_value,
        };
    }

    if (new_row.acceso == '1' && new_row.insertar == '1' && new_row.editar == '1' && new_row.eliminar == '1') {
        new_row.todos = '1';
    } else {
        new_row.todos = '0';
    }

    $('#tabla_permisos').bootstrapTable('updateByUniqueId', { id, row: new_row });
}

// Verifica si se debe desmarcar los menus que estan en un nivel superior, si es que tiene
function actualizarPermisosSuperiores (field, value, row) {
    if (row.id_menu_padre) {
        var menus = $('#tabla_permisos').bootstrapTable('getData');
        var sigte_padre = true;
        menus.forEach(m => {
            if (m.id_menu_padre == row.id_menu_padre && m.id_menu != row.id_menu && m[field] == '0') {
                sigte_padre = false;
                return false;
            }
        });
        if (sigte_padre) {
            actualizarPermisos (field, value, $('#tabla_permisos').bootstrapTable('getRowByUniqueId', row.id_menu_padre));
            actualizarPermisosSuperiores (field, value, $('#tabla_permisos').bootstrapTable('getRowByUniqueId', row.id_menu_padre));
        }
    }
}

// Verifica si se debe desmarcar los menus que contiene, si es que tiene
function actualizarPermisosInferiores (field, value, row) {
    var menus = $('#tabla_permisos').bootstrapTable('getData');
    menus.forEach(m => {
        if (row.id_menu == m.id_menu_padre) {
            actualizarPermisos (field, value, $('#tabla_permisos').bootstrapTable('getRowByUniqueId', m.id_menu));
            actualizarPermisosInferiores (field, value, $('#tabla_permisos').bootstrapTable('getRowByUniqueId', m.id_menu));
        }
    });
}

window.accionesFilaPermisos = {
    'click .acceso': function (e, value, row, index) {
        actualizarPermisos('acceso', value, row);
        //actualizarPermisosSuperiores('acceso', value, row);
        //actualizarPermisosInferiores('acceso', value, row);
    },
    'click .insertar': function (e, value, row, index) {
        actualizarPermisos('insertar', value, row);
        //actualizarPermisosSuperiores('insertar', value, row);
        //actualizarPermisosInferiores('insertar', value, row);
    },
    'click .editar': function (e, value, row, index) {
        actualizarPermisos('editar', value, row);
        //actualizarPermisosSuperiores('editar', value, row);
        //actualizarPermisosInferiores('editar', value, row);
    },
    'click .eliminar': function (e, value, row, index) {
        actualizarPermisos('eliminar', value, row);
        //actualizarPermisosSuperiores('eliminar', value, row);
        //actualizarPermisosInferiores('eliminar', value, row);
    },
    'click .todos': function (e, value, row, index) {
        actualizarPermisos('todos', value, row);
        //actualizarPermisosSuperiores('todos', value, row);
        //actualizarPermisosInferiores('todos', value, row);
    },
}

$("#tabla_permisos").bootstrapTable({
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    // sortName: "orden",
    sortOrder: 'asc',
    trimOnSearch: false,
    uniqueId: 'id_menu',
    idField: 'id_menu',
    treeShowField: 'menu',
    undefinedText: "",
    parentIdField: 'id_menu_padre',
    columns: [
        [
            {	field: 'id_menu', align: 'left', valign: 'middle', title: 'ID Menú', sortable: true, visible: false }, 
            {	field: 'id_menu_padre', align: 'left', valign: 'middle', title: 'ID Menú Padre', sortable: true, visible: false }, 
            {	field: 'orden', align: 'left', valign: 'middle', title: 'Orden', sortable: true, formatter: bstFormatterOrden, width: 10, widthUnit: '%'	},
            {	field: 'menu', align: 'left', valign: 'middle', title: 'Menu', sortable: true	},
            {	field: 'titulo', align: 'left', valign: 'middle', title: 'Título', sortable: true	},
            {	field: 'icono', align: 'center', valign: 'middle', title: 'Icono', sortable: true, width: 7, widthUnit: '%'	},
            {	field: 'acceso', align: 'center', valign: 'middle', title: 'Acceso', sortable: false, events: accionesFilaPermisos, formatter: checkboxAcceso	},
            {	field: 'insertar', align: 'center', valign: 'middle', title: 'Insertar', sortable: false, events: accionesFilaPermisos, formatter: checkboxInsertar	},
            {	field: 'editar', align: 'center', valign: 'middle', title: 'Modificar', sortable: false, events: accionesFilaPermisos, formatter: checkboxEditar	},
            {	field: 'eliminar', align: 'center', valign: 'middle', title: 'Eliminar', sortable: false, events: accionesFilaPermisos, formatter: checkboxEliminar	},
            {	field: 'todos', align: 'center', valign: 'middle', title: 'Todos', sortable: false, events: accionesFilaPermisos, formatter: checkboxTodos	},
        ]
    ]
}).on('post-body.bs.table', function(e, data){
    var columns = $(this).bootstrapTable('getVisibleColumns');
    if(columns){
        var indexField = columns.findIndex((col) => col.field === "menu");
        $("#tabla_permisos").treegrid({
            treeColumn: indexField,
            saveState: true,
            initialState: 'expanded',
        }).on("change", () => $("#tabla_permisos").bootstrapTable('resetView'));
    }
}).on('click-cell.bs.table', (e, field, value, row, $element) => { 
    if(field == "menu") $($element).parent().treegrid('toggle');
});

function bstFormatterOrden(value, row, index, field) {   
    let orden = value;
    if(row._level != 0){
        orden = `${row._parent.orden}.${orden}`;
        orden = bstFormatterOrden(orden, row._parent, index, field);
    }
    return orden;
}

// GUARDAR MODIFICACION DE PERMISOS
$("#btn-editar-permisos").click(function(e){
    e.preventDefault();
    var data = $(this).serializeArray();
    $.ajax({
        url: url+'?q=editar_permisos',
        dataType: 'html',
        type: 'post',
        contentType: 'application/x-www-form-urlencoded',
        data: { id_rol: $('#permisos_id_rol').val(), rol: $('#permisos_rol').html(), permisos: $('#tabla_permisos').bootstrapTable('getData') },
        beforeSend: function(){
            NProgress.start();
        },
        success: function(datos, textStatus, jQxhr){
            var n = datos.toLowerCase().indexOf("error");
            if (n == -1) {
                $('#modal_permisos').modal('hide');
                alertDismissJS(datos, "ok");
            } else {
                alertDismissJS(datos, "error");
            }
            NProgress.done();
        },
        error: function(jqXhr, textStatus, errorThrown){
            NProgress.done();
            alertDismissJS($(jqXhr.responseText).text().trim(), "error");
        }
    });
});

//Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e)
        {
          if (window.RT) clearTimeout(window.RT);
          window.RT = setTimeout(function()
          {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: $(window).height()-90,
                pageSize: Math.floor(($(window).height()-90)/50),
            });
          }, 100);
        });
});
