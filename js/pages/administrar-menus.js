var url = 'inc/administrar-menus-data';
var url_listados = 'inc/listados';

$("#btn_expand_collapse_nodes_items a").click((e) => $('#tabla').treegrid($(e.target).data().treeToogle.toString()));
$("#btn_expand_collapse").click((e) => $('#tabla').treegrid('getAllNodes').each(function(){$(this).treegrid('toggle')}));

$('#menu_padre').select2({
    dropdownParent: $("#modal_principal"),
    placeholder: 'Buscar Menú',
    language: "es",
    theme: "bootstrap4",
    width: 'style',
    allowClear: true,
    selectOnClose: false,
    ajax: {
        url: url,
        dataType: 'json',
        delay: 50,
        cache: false,
        async: true,
        data: function(params) {
            return { q: 'ver_menus', term: params.term, page: params.page || 1 }
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.data, function(obj) { return { id: obj.id_menu, text: obj.menu }; }),
                pagination: { more: (params.page * 5) <= data.total_count }
            };
        },
        cache: false
    }
}).on('change', function() {
    let data = $(this).select2('data')[0];
    if (data) {
        let id_menu = data.id;
        let tableData = $('#tabla').bootstrapTable('getData');
        let row = tableData.find(value => value.id_menu == id_menu);
        $('#orden_preview').html(bstFormatterOrden(row.orden, row)+'.');
    } else {
        $('#orden_preview').html('-');
    }
});

$("#estado").select2({
    theme: "bootstrap4",
    width: 'style',
    selectOnClose: true,
    minimumResultsForSearch: Infinity,
});

function iconosFila(value, row, index) {
    return [
        '<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fa fa-pencil-alt"></i></button>',
        '<button type="button" onclick="javascript:void(0)" class="btn btn-success btn-sm agregar ml-1" title="Agregar Submenú"><i class="fa fa-plus"></i></button>'
    ].join('');
}

window.accionesFila = {
    'click .editar': function (e, value, row, index) {
        editarDatos(row);
    },
    'click .agregar': function (e, value, row, index) {
        resetForm('#formulario');
        $('#modalLabel').html('Agregar Menú');
        $('#formulario').attr('action', 'cargar');
        $('#modal_principal').modal('show');

        let menu = bstMenuPadre(row.menu, row);
        $('#menu_padre').select2('trigger', 'select', {
            data: { id: row.id_menu, text: menu }
        });
        $('#estado').val('Habilitado').trigger('change');
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

$("#tabla").bootstrapTable({
    mobileResponsive: true,
    height: $(window).height()-120,
    pageSize: Math.floor(($(window).height()-120)/50),
    // sortName: "orden",
    sortOrder: 'asc',
    trimOnSearch: false,
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
            // {	field: 'submenu', align: 'left', valign: 'middle', title: 'Submenu', sortable: true	},
            {	field: 'titulo', align: 'left', valign: 'middle', title: 'Título', sortable: true	},
            {	field: 'url', align: 'left', valign: 'middle', title: 'Url', sortable: true	},
            {	field: 'icono', align: 'center', valign: 'middle', title: 'Icono', sortable: true, width: 7, widthUnit: '%'	},
            {	field: 'estado', align: 'center', valign: 'middle', title: 'Estado', formatter: bstFormatterEstado, sortable: true	},
            {	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: accionesFila,  formatter: iconosFila	}
        ]
    ]
}).on('dbl-click-row.bs.table', function(e, row, $element) {
    editarDatos(row);
}).on('post-body.bs.table', function(e, data){
    var columns = $(this).bootstrapTable('getVisibleColumns');
    if(columns){
        var indexField = columns.findIndex((col) => col.field === "menu");
        $("#tabla").treegrid({
            treeColumn: indexField,
            saveState: true,
            initialState: 'expanded',
        }).on("change", () => $("#tabla").bootstrapTable('resetView'));
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

function bstMenuPadre(value, row) {   
    let menu = value;
    if(row._level != 0){
        menu = `${row._parent.menu}->${menu}`;
        menu = bstMenuPadre(menu, row._parent);
    }
    return menu;
}

function editarDatos(row) {
    resetForm('#formulario');
    $('#modalLabel').html('Editar Menú');
    $('#formulario').attr('action', 'editar');
    $('#eliminar').show();
    $('#modal_principal').modal('show');
    $("#hidden_id_menu").val(row.id_menu);
    $("#titulo").val(row.titulo);
    $("#url").val(row.url);
    $("#icono").val(row.icono).trigger('change');
    $("#orden").val(row.orden);
    $("#estado").val(row.estado).trigger('change');
    $("#menu").val(row.menu);

    if (row.id_menu_padre) {
        let menu_padre = bstMenuPadre(row._parent.menu, row._parent);
        $('#menu_padre').select2('trigger', 'select', {
            data: { id: row.id_menu_padre, text: menu_padre }
        });
    }
}

$('#agregar').click(function(){
    $('#modalLabel').html('Agregar Menú');
    $('#formulario').attr('action', 'cargar');
    resetForm('#formulario');
    $('#estado').val('Habilitado').trigger('change');
});

$('#modal_principal').on('shown.bs.modal', function (e) {
    $("form input[type!='hidden']:first").focus();
});

$(".modal-dialog").draggable({
    handle: ".modal-header"
});

// GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
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
    var menu = $("#menu").val();
    sweetAlertConfirm({
        title: `Eliminar`,
        text: `¿Eliminar Menu "${menu}?`,
        closeOnConfirm: false,
        confirm: function() {
            $.ajax({
                dataType: 'html',
                async: false,
                type: 'POST',
                url: url,
                cache: false,
                data: {q: 'eliminar', id_menu: $("#hidden_id_menu").val(), menu: menu },
                beforeSend: function(){
                    NProgress.start();
                },
                success: function (data, status, xhr) {
                    var n = data.toLowerCase().indexOf("error");
                    if (n == -1) {
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

//Altura de tabla automatica
$(document).ready(function () {
    $(window).bind('resize', function(e)
        {
          if (window.RT) clearTimeout(window.RT);
          window.RT = setTimeout(function()
          {
            $("#tabla").bootstrapTable('refreshOptions', { 
                height: $(window).height()-120,
                pageSize: Math.floor(($(window).height()-120)/50),
            });
          }, 100);
        });
});

// ICONOS
$("#tabla_iconos").bootstrapTable({
    url: 'dist/icons/font-awesome/fontawesomecheatsheet.json',
    pageSize: 18,
    pagination: true,
    sidePagination: 'client',
    trimOnSearch: false,
    showCustomView: true,
    customView: customViewFormatter,
    columns: [
        [
            {	field: 'Name', title: 'Name', align: 'left', valign: 'middle', sortable: true }, 
            {	field: 'Code', title: 'Code', align: 'left', valign: 'middle', sortable: true	},
            {	field: 'Class', title: 'Class', align: 'left', valign: 'middle', sortable: true	},
        ]
    ]
});


function customViewFormatter(data) {
    var template = $('#menuTemplate').html();
    var view = '';
    $.each(data, function (i, row) {
        view += template.replace('%CODE%', row.Code)
            .replace('%NAME%', row.Name)
            .replace('%CLASS%', row.Class)
            .replace('%ICON%', encodeURI(row.Code));
    });

    return `<div class="row mx-0">${view}</div>`;
}

$('#tabla_iconos').on('custom-view-post-body.bs.table', function() {
    $('.card-icono').on('click', function() {
        var data = $(this).data();
        var code = decodeURI(data.icon);
        $('#icono').val(code).trigger('change');
        $('#modal_iconos').modal('hide');
    });
});
$('#icono').on('change', function() {
    $('#icono_preview').html($(this).val());
});

function resetForm(form) {
    $(form).trigger('reset');
    $(form).find('select').val(null).trigger('change');

    resetValidateForm(form);
}
