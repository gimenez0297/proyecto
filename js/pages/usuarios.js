  var url = "inc/usuarios-data";
    var url_listados = "inc/listados";
    var socketIO = io("http://591e-186-2-200-48.ngrok.io");

	/*$('.modal').on("hidden.bs.modal", function (e) { //fire on closing modal box
		if ($('.modal:visible').length) { // check whether parent modal is opend after child modal close
			$('body').addClass('modal-open'); // if open mean length is 1 then add a bootstrap css class to body of the page
		}
	});*/

	$("#dpto, #sucursal, #estado, #rol").select2({
        theme: "bootstrap4",
        width: 'auto',
        minimumResultsForSearch: 10,
        selectOnClose: true,
        dropdownPosition: 'below',
    });

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
			'<button type="button" onclick="javascript:void(0)" class="btn btn-info btn-sm editar" title="Editar datos"><i class="fas fa-pencil-alt"></i></button>'
		].join('');
	}
	
	window.accionesFila = {
		'click .editar': function (e, value, row, index) {
			editarDatos(row);
		}
	}

	// function color (value, row, index) {
	// 	switch (value) {
	// 		case 'Activo':
	// 			return { value: 'Pagada', css: {"color": "#187e63", "font-weight": "500" } }
	// 		break;
	// 		case 'Pendiente':
	// 			return { css: {"color": "black", "font-weight": "500" } }
	// 		break;
	// 		case 'Bloqueado':
	// 			return { css: {"color": "red", "font-weight": "500" } }
	// 		break;
	// 		case 'Contraseña Expirada':
	// 			return { css: {"color": "gray", "font-weight": "500" } }
	// 		break;
	// 		default:
	// 			return {};
	// 		break;
	// 	}
	// }

	$("#tabla").bootstrapTable({
		mobileResponsive: true,
		height: $(window).height()-90,
		pageSize: Math.floor(($(window).height()-90)/50),
		columns: [
			[
				{	field: 'id', align: 'left', valign: 'middle', title: 'ID', sortable: true, visible: true }, 
				{	field: 'username', align: 'left', valign: 'middle', title: 'Usuario', sortable: true	},
				{	field: 'nombre_apellido', align: 'left', valign: 'middle', title: 'Nombre', sortable: true	},
				{	field: 'departamento', align: 'left', valign: 'middle', title: 'Dpto./Área', sortable: true	},
				{	field: 'cargo', align: 'left', valign: 'middle', title: 'Cargo', sortable: true, visible: false	},
				{	field: 'telefono', align: 'left', valign: 'middle', title: 'Tel.', sortable: true	},
				{	field: 'direccion', align: 'left', valign: 'middle', title: 'Dirección', sortable: true, visible:false },
				{	field: 'ci', align: 'left', valign: 'middle', title: 'C.I.', sortable: true, visible: false	}, 
				{	field: 'email', align: 'left', valign: 'middle', title: 'E-mail', sortable: true, visible: false	},
				{	field: 'foto', align: 'left', valign: 'middle', title: 'Foto', sortable: true, visible:false	},
				//{	field: 'roles', align: 'left', valign: 'middle', title: 'Rol', sortable: true },
				{	field: 'id_rol', align: 'left', valign: 'middle', title: 'ID Rol', sortable: true, visible: false }, 
				{	field: 'rol', align: 'left', valign: 'middle', title: 'Rol', sortable: true }, 
				{	field: 'estado', align: 'center', valign: 'middle', title: 'Estado', formatter: bstFormatterEstado, sortable: true }, 
				{	field: 'id_sucursal', visible:false }, 
				{	field: 'status', visible:false }, 
				{	field: 'sucursal', align: 'left', valign: 'middle', title: 'Sucursal', sortable: true }, 
                {	field: 'notificaciones_str', align: 'center', valign: 'middle', title: 'Notificaciones', sortable: true }, 
				{	field: 'fecha_registro', align: 'left', valign: 'middle', title: 'Fecha alta', sortable: true, visible:false	}, 
				{	field: 'ultimo_acceso', align: 'left', valign: 'middle', title: 'Último acceso', sortable: true	}, 
				{	field: 'usuario_carga', align: 'left', valign: 'middle', title: 'Usuario Carga', sortable: true, visible: false	}, 
				{	field: 'editar', align: 'center', valign: 'middle', title: 'Editar', sortable: false, events: accionesFila,  formatter: iconosFila	}
			]
		]
	}).on('dbl-click-row.bs.table', function(e, row, $element) {
	    editarDatos(row);
	});

	function editarDatos(row) {
		resetValidateForm("#formulario");
    	$('#modalLabel').html('Editar Usuario');
		$('#formulario').attr('action', 'editar');
		$('#restablecer').show();
		$('#estado_cont').show();
		$('#modal_principal').modal('show');
		$("#hidden_id_usuario").val(row.id);
		$("#nombre").val(row.nombre_apellido);
		$("#ci").val(row.ci);
		$("#telefono").val(row.telefono);
		$("#direccion").val(row.direccion);
		$("#email").val(row.email);
		$("#cargo").val(row.cargo);
		$("#dpto").select2('trigger', 'select', {
            data: { id: row.departamento, text: row.departamento }
        });
        $("#estado").select2('trigger', 'select', {
            data: { id: row.status, text: row.estado }
        });
        $('#passConten').hide();
        $('#contenC').hide();
		$("#usuario").val(row.username).attr("disabled", true);

        if (row.id_sucursal) {
            $("#sucursal").append(new Option(row.sucursal, row.id_sucursal, true, true)).trigger('change');
        }

        $("#ver_notificaciones").prop('checked', row.notificaciones == 1 ? true : false);

		//Marcamos los roles del usuario
		/*$.ajax({
			dataType: 'json', async: false, cache: false, url: 'inc/usuarios-data.php', type: 'POST', data: {q: 'ver_roles_usuario', id:row.id },
			beforeSend: function(){
				NProgress.start();
			},
			success: function (json){
				$('#rol').selectpicker('val', json);
				$('#rol').selectpicker('refresh');
				NProgress.done();
			},
			error: function (xhr) {
				alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
			}
		});*/
		$("#rol").val(row.id_rol).trigger('change');
}
	
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
	
	$('#agregar').click(function(){
		$('#modalLabel').html('Registrar Usuario');
		$('#formulario').attr('action', 'cargar');
		resetValidateForm("#formulario");
	});
	
	$('#modal_principal').on('show.bs.modal', function (e) {
		if ($('#formulario').attr('action')=="cargar"){
			limpiarModal(e);
			$("#usuario").attr("disabled", false);
			$('#passConten').show();
			$('#contenC').show();
			//$('#sucursal').prop("disabled", true);
			$('#estado_cont').hide();
			$('#restablecer').hide();
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
		$('#rol, #sucursal, #dpto').val(null).trigger('change');
		resetValidateForm("#formulario");
		//$('#rol').selectpicker('val', '');
		//$('#rol').selectpicker('refresh');
	}
	
	//GUARDAR NUEVO REGISTRO O CAMBIOS EDITADOS
	$("#formulario").submit(function(e){
		e.preventDefault();
		if ($(this).valid() === false) return false;
		var data = $(this).serializeArray();
		//data.push({name: 'detalles', value: JSON.stringify($('#tabla_detalles').bootstrapTable('getData'))});
		$.ajax({
			url: 'inc/usuarios-data?q='+$(this).attr("action"),
			dataType: 'html',
			type: 'post',
			contentType: 'application/x-www-form-urlencoded',
			data: data,
			beforeSend: function(){
				NProgress.start();
			},
			success: function(datos, textStatus, jQxhr){
				NProgress.done();
				var n = datos.toLowerCase().indexOf("error");
				if (n == -1) {
					$('#modal_principal').modal('hide');
					alertDismissJS(datos, "ok");
					$('#tabla').bootstrapTable('refresh', {url: 'inc/usuarios-data.php?q=ver'});
					socketIO.emit("notificacionDatos", $("#hidden_id_usuario").val());
				}else{
					alertDismissJS(datos, "error");
				}
			},
			error: function(jqXhr, textStatus, errorThrown){
				NProgress.done();
				alertDismissJS($(jqXhr.responseText).text().trim(), "error");
			}
		});
	});
		
	
	//ELIMINAR
	$('#restablecer').click(function(){
		var nombre = $("#usuario").val();
        sweetAlertConfirm({
			title: "¿Restablecer Usuario: "+nombre+"?",
			text: "La nueva contraseña sera: "+nombre+"",   
            closeOnConfirm: false,
            confirm: function() {
                $.ajax({
                    dataType: 'html',
                    async: false,
                    type: 'POST',
                    url: 'inc/usuarios-data.php',
                    cache: false,
                    data: {q: 'restablecer_pass', id: $("#hidden_id_usuario").val(), nombre: $("#usuario").val() },	
                    beforeSend: function(){
                        NProgress.start();
                    },
                    success: function (data, status, xhr) {
                        var n = data.toLowerCase().indexOf("error");
                        if (n == -1) {
                            $('#modal_principal').modal('hide');
                            alertDismissJS(data, "ok");
                            NProgress.done();
                            $('#tabla').bootstrapTable('refresh', {url: 'inc/usuarios-data.php?q=ver'});
                        }else{
                            alertDismissJS(data, "error");
                        }
                    },
                    error: function (jqXhr) {
                        alertDismissJS($(jqXhr.responseText).text().trim(), "error");
                    }
                });
            }
        });
	});
	
	$('#usuario').on('keyup change', function (e) {
		$('#password').val($('#usuario').val().trim());
	})
	
	
	//AL PERDER EL FOCO Y SI EL MODAL ESTÁ VISIBLE
	$('#buscar_ci').click(async function() {
		let ci = $('#ci').val();

	    if ($('#modal_principal').is(':visible') && ci) {
			let data = await buscarCI(ci);
			if (data.cedula) {
				$('#ci').val(data.cedula);
				$('#nombre').val(data.apellido + ' ' + data.nombre_solo);
				$('#direccion').val(data.direccion);
			} else {
				alertDismissJsSmall('CI no encontrado', 'error', 2000, () => $('#ci').focus().select());
			}
	    }
	});
	
	//ROLES
	$.ajax({
		dataType: 'json', async: false, cache: false, url: 'inc/usuarios-data.php', type: 'POST', data: {q: 'ver_roles'},
		beforeSend: function(){
			NProgress.start();
		},
		success: function (json){
			$('#rol').empty();
			$.each(json, function(key, value) {
				//$('#rol').append('<option value="'+ value.rol + '">' + value.rol + '</option>');
				$('#rol').append('<option value="'+ value.id_rol + '">' + value.rol + '</option>');
			 });
			NProgress.done();
			$('#rol').selectpicker('refresh');
		},
		error: function (xhr) {
			NProgress.done();
			alertDismissJS("No se pudo completar la operación: " + xhr.status + " " + xhr.statusText, 'error');
		}
	});

    $('#sucursal').select2({
        dropdownParent: $("#modal_principal"),
        placeholder: 'Seleecionar',
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
