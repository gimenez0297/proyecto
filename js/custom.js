$(function() {
    "use strict";
    $(function() {
        $(".preloader").fadeOut();
    });
    jQuery(document).on('click', '.mega-dropdown', function(e) {
        e.stopPropagation()
    });
    // ============================================================== 
    // This is for the top header part and sidebar part
    // ==============================================================  
    var set = function() {
        var width = (window.innerWidth > 0) ? window.innerWidth : this.screen.width;
        var topOffset = 55;
        if (width < 1170) {
            $("body").addClass("mini-sidebar");
            $('.navbar-brand span').hide();
        } else {
            $("body").removeClass("mini-sidebar");
            $('.navbar-brand span').show();
        }
        var height = ((window.innerHeight > 0) ? window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $(".page-wrapper").css("min-height", (height) + "px");
        }
    };
    $(window).ready(set);
    $(window).on("resize", set);
    // ============================================================== 
    // Theme options
    // ==============================================================     
    $(".sidebartoggler").on('click', function() {
        $("body").toggleClass("mini-sidebar");

    });

    // this is for close icon when navigation open in mobile view
    $(".nav-toggler").on('click', function() {
        $("body").toggleClass("lock-nav");
        $("body, .page-wrapper").trigger("resize");
        $(".nav-toggler i").toggleClass("ti-menu");
        $("body").toggleClass("show-sidebar");
        $("footer").toggleClass("margencito-footer");
        $("#nav_header").toggleClass("margencito");
        $(".page-wrapper").toggleClass("margen-wrapper");
        $("#open").toggleClass("d-none");
        $("#close").toggleClass("d-none");
        $("#open_img").toggleClass("activo");
        $("#close_img").toggleClass("activo");
        $(".left-sidebar").toggleClass("left")
        $(".nav-text-box").toggleClass("push-end")
    });

    $(".left-sidebar").on('mouseover', function(){
        if  ($(".left-sidebar").hasClass("left")){
            $("#open_img").addClass("activo");
            $("#close_img").removeClass("activo");
            $(".nav-text-box").removeClass("push-end")
        }
    })

    $(".left-sidebar").on('mouseout', function(){
        if  ($(".left-sidebar").hasClass("left")){
            $("#open_img").removeClass("activo");
            $("#close_img").addClass("activo");
            $(".nav-text-box").addClass("push-end")
        }
    })
	
	 $(".page-wrapper").on('click', function() {
	  $("body").removeClass("show-sidebar");
    });
	
    $(".nav-lock").on('click', function() {
        $("body").toggleClass("lock-nav");
        $(".nav-lock i").toggleClass("mdi-toggle-switch-off");
        $("body, .page-wrapper").trigger("resize");
    });
    $(".search-box a, .search-box .app-search .srh-btn").on('click', function() {
        $(".app-search").toggle(200);
        $(".app-search input").focus();
    });

    // $(".left-sidebar").removeClass("left")

    // ============================================================== 
    // Right sidebar options
    // ============================================================== 
    $(".right-side-toggle").click(function() {
        $(".right-sidebar").slideDown(50);
        $(".right-sidebar").toggleClass("shw-rside");
    });
    // ============================================================== 
    // This is for the floating labels
    // ============================================================== 
    $('.floating-labels .form-control').on('focus blur', function(e) {
        $(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
    }).trigger('blur');

    // ============================================================== 
    //tooltip
    // ============================================================== 
    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    })
    // ============================================================== 
    //Popover
    // ============================================================== 
    $(function() {
        $('[data-toggle="popover"]').popover()
    })

    // ============================================================== 
    // Perfact scrollbar
    // ============================================================== 
    $('.scroll-sidebar, .right-side-panel, .message-center, .right-sidebar').perfectScrollbar();
    // ============================================================== 
    // Resize all elements
    // ============================================================== 
    $("body, .page-wrapper").trigger("resize");
    // ============================================================== 
    // To do list
    // ============================================================== 
    $(".list-task li label").click(function() {
        $(this).toggleClass("task-done");
    });
    // ============================================================== 
    // Collapsable cards
    // ==============================================================
    $('a[data-action="collapse"]').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.card').find('[data-action="collapse"] i').toggleClass('ti-minus ti-plus');
        $(this).closest('.card').children('.card-body').collapse('toggle');
    });
    // Toggle fullscreen
    $('a[data-action="expand"]').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.card').find('[data-action="expand"] i').toggleClass('mdi-arrow-expand mdi-arrow-compress');
        $(this).closest('.card').toggleClass('card-fullscreen');
    });
    // Close Card
    $('a[data-action="close"]').on('click', function() {
        $(this).closest('.card').removeClass().slideUp('fast');
    });
    // ============================================================== 
    // Color variation
    // ==============================================================

    var mySkins = [
        "skin-default",
        "skin-green",
        "skin-red",
        "skin-blue",
        "skin-purple",
        "skin-megna",
        "skin-default-dark",
        "skin-green-dark",
        "skin-red-dark",
        "skin-blue-dark",
        "skin-purple-dark",
        "skin-megna-dark"
    ]
    /**
     * Get a prestored setting
     *
     * @param String name Name of of the setting
     * @returns String The value of the setting | null
     */
    function get(name) {
        if (typeof(Storage) !== 'undefined') {
            //return localStorage.getItem(name)
        } else {
            window.alert('Please use a modern browser to properly view this template!')
        }
    }
    /**
     * Store a new settings in the browser
     *
     * @param String name Name of the setting
     * @param String val Value of the setting
     * @returns void
     */
    function store(name, val) {
        if (typeof(Storage) !== 'undefined') {
            localStorage.setItem(name, val)
        } else {
            window.alert('Please use a modern browser to properly view this template!')
        }
    }

    /**
     * Replaces the old skin with the new skin
     * @param String cls the new skin class
     * @returns Boolean false to prevent link's default action
     */
    // function changeSkin(cls) {
        // $.each(mySkins, function(i) {
            // $('body').removeClass(mySkins[i])
        // })
        // $('body').addClass(cls)
        // store('skin', cls)
        // return false
    // }

    // function setup() {
        // var tmp = get('skin')
        // if (tmp && $.inArray(tmp, mySkins)) changeSkin(tmp)
        // Add the change skin listener
        // $('[data-skin]').on('click', function(e) {
            // if ($(this).hasClass('knob')) return
            // e.preventDefault()
            // changeSkin($(this).data('skin'))
        // })
    // }
    // setup()
    // $("#themecolors").on("click", "a", function() {
        // $("#themecolors li a").removeClass("working"),
            // $(this).addClass("working")
    // })
	
    // Modal
    $(".modal-dialog").draggable({
        handle: ".modal-header"
    });
	
});

	window.icons = { paginationSwitchDown: 'fa-toggle-down', paginationSwitchUp: 'fa-toggle-up', refresh: 'fa-sync-alt', toggleOff: 'fa-list-alt', toggleOn: 'fa-toggle-on', toggle: 'fa-list-alt', columns: 'fa-th-list', detailOpen: 'fa-plus', detailClose: 'fa-minus', fullscreen: 'fa-arrows-alt',  export: 'fa-download'};

    /**
     * @param {object} config Ver configuración en https://sweetalert2.github.io/#configuration
     */
    function loading(config) {
        let dafault = {
            title: 'Cargando',
            text: 'Espere por favor...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            timerProgressBar: true,
            returnFocus: false,
            didOpen: () => {
                Swal.showLoading();
            },
        }
        Swal.fire($.extend(dafault, config));
    }
		
    function alertDismissJS(text, icon, confirm = () => {}) {
        var title = '';

        switch (icon) {
            case 'error': title = 'Error'; break;
            case 'warning': title = 'Atención'; break;
            case 'info': title = 'Información'; break;
            case 'question': title = ''; break;
            case 'ok': title = 'Operación exitosa'; icon = 'success'; break;
            default: title = text; text = '';
        }

        Swal.fire({
            title,
            text,
            icon,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: 'var(--primary)',
            allowOutsideClick: false,
            returnFocus: false,
        }).then((result) => {
            if (result.isConfirmed) {
                confirm();
            }
        });
    }

    function alertDismissJsSmall(text, icon, duracion = 3000, close = () => {}) {
        var title = '';
        if (!duracion) var duracion = 3000;
        switch (icon) {
            case 'error': title = 'Error'; break;
            case 'warning': title = 'Atención'; break;
            case 'info': title = 'Información'; break;
            case 'question': title = ''; break;
            case 'ok': title = 'Operación exitosa'; icon = 'success'; break;
            default: title = text; text = '';
        }

        const Toast = Swal.mixin({
            toast: false,
            position: 'top-end',
            showConfirmButton: false,
            timer: duracion,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer)
              toast.addEventListener('mouseleave', Swal.resumeTimer)
            },
            willClose: () => {
                close();
              }
          })

          Toast.fire({
            position: 'center',
            icon: icon,
            title: title,
            text : text,
            showConfirmButton: false,
            timer: duracion,
            returnFocus: false
          })
    }
    
    /**
     * @param {object} configUser 
     * * title: `string` 'Confirmar'
     * * text: `string` ''
     * * confirm: `callback` () => {}
     * * closeOnConfirm: `boolean` true
     * * confirmButtonText: `string` 'Aceptar'
     * * cancelButtonText: `string` 'Cancelar'
     * * confirmButtonColor: `string` 'var(--primary)'
     * * cancelButtonColor: `string` ''
     */
    function sweetAlertConfirm(configUser) {
        var configDefault = {
            title: 'Confirmar',
            text: '',
            confirm: () => {},
            cancel: () => {},
            closeOnConfirm: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: 'var(--primary)',
            cancelButtonColor: '',
            returnFocus: false,
            //loadingOnConfirm : false,
        }
        var config = $.extend(configDefault, configUser);

        Swal.fire({
            title: config.title,
            text: config.text,
            icon: 'warning',
            confirmButtonText: config.confirmButtonText,
            confirmButtonColor: config.confirmButtonColor,
            showCancelButton: true,
            cancelButtonText: config.cancelButtonText,
            cancelButtonColor: config.cancelButtonColor,
            reverseButtons: true,
            allowOutsideClick: false,
            preConfirm: () => {
                config.confirm();
                return config.closeOnConfirm;
            }
        }).then((result) => {
            if (result.isConfirmed) {
            } else {
                config.cancel();
            }
        });
    }

    //function alertDismissJS(msj, tipo, duracion) {
        //var salida;
        //if (!duracion) var duracion = 3000;
        //switch (tipo){
            //case 'error':
                //salida = swal("Error", msj, "error"); 
            //break;
            
            //case 'error_span':
                //salida = "<span id='alerta' class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>"+
                //"<span class='glyphicon glyphicon-exclamation-sign'>&nbsp;</span>"+msj+"</span>";
            //break;
            
            //case 'warning':
                //salida = $.toast({ heading: 'Atención', text: msj, position: 'top-center', loaderBg:'#ff8000', icon: 'warning', hideAfter: duracion });
            //break;
            
            //case 'default':
                //salida = $.toast({ heading: 'Información', text: msj, position: 'top-center', loaderBg:'#008c69', icon: 'warning', hideAfter: duracion });
            //break;
            
            //case 'ok':
                //salida = $.toast({ heading: 'Operación exitosa', text: msj, position: 'top-center', loaderBg:'#008c69', icon: 'success', hideAfter: duracion });
            //break;
        //}
        //return salida; 
    //}
    
	
    function alertToast(msj, tipo, duracion) {
        var salida;
        if (!duracion) var duracion = 10000;
        if (msj=="reset"){
            salida = $.toast().reset('all');
        }else{
            switch (tipo){
                case 'error':
                    salida = $.toast({ heading: 'Error', text: msj, position: 'top-center', loaderBg:'#d8d8d8', icon: 'error', hideAfter: duracion  });
                break;
                case 'ok':
                    salida = $.toast({ heading: 'Operación exitosa', text: msj, position: 'top-center', loaderBg:'#008c69', icon: 'success', hideAfter: duracion });
                break;
                case 'warning':
                    salida = $.toast({ heading: 'Atención', text: msj, position: 'top-center', loaderBg:'#d6c900', icon: 'warning', hideAfter: duracion });
                break;
            }
        }
        return salida; 
    }

	function fechaMYSQL(fecha){
		var fechaArr = fecha.split("/");
		var salida = fechaArr[2]+"-"+fechaArr[1]+"-"+fechaArr[0];
		return salida;
	}

	function fechaLatina(fecha){
		var fechaArr = fecha.split("-");
		var salida = fechaArr[2]+"/"+fechaArr[1]+"/"+fechaArr[0];
		return salida;
	}

	//Permitir números y puntos (decimales)
	// USO: onkeypress="numeroDecimales(event, this.value)"
	function numeroDecimales(event,data){
	if((event.charCode>= 48 && event.charCode <= 57) || event.charCode== 46 ||event.charCode == 0){
		if(data.indexOf('.') > -1){
 			if(event.charCode== 46)
  				event.preventDefault();
		}
	}else
		event.preventDefault();
	};

	/*USO: onkeypress="return soloNumeros(event)" */
	function soloNumeros(evt){
		var charCode = (evt.which) ? evt.which : evt.keyCode
		if (charCode > 31 && (charCode < 48 || charCode > 57))
			return false;
		return true;
	}

	//Separador de miles al momento de escribir
	//onkeyup="separadorMilesOnKey(event,this)"
	function separadorMilesOnKey(e,input){
		 -1 !== $.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) || /65|67|86|88/.test(e.keyCode) && (!0 === e.ctrlKey || !0 === e.metaKey) || 35 <= e.keyCode && 40 >= e.keyCode || (e.shiftKey || 48 > e.keyCode || 57 < e.keyCode) && (96 > e.keyCode || 105 < e.keyCode) && e.preventDefault()
		  var $this = $(input);
		  var num = $this.val().replace(/[^\d]/g,'').split("").reverse().join("");
		  var num2 = RemoveRougeChar(num.replace(/(.{3})/g,"$1.").split("").reverse().join(""), ".");
		  return $this.val(num2);
	}

	//Separacion de miles para guaranies y decimales para dolares
	function separadorMilesDecimales(convertString, separa){
		if(convertString.substring(0,1) == separa){
			return convertString.substring(1, convertString.length)    
			}
		return convertString;
	}

	function separadorMiles(x) {
		if(x){
			return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
		}else{
			return 0;
		}
	}

	function quitaSeparadorMiles(valor){
		if(valor){
			return parseInt(valor.toString().replace(/\./g, ""));
		}else{
			return 0;
		}
	}
	
	function quitaSeparadorMilesFloat(valor){
		//sacamos los puntos
		var x = valor.replace(/\./g, "");
		//reemplazamos el punto por coma
		var x = x.replace(/\,/g, ".");
		
		if (Number.isNaN(Number.parseFloat(x))) {
			return 0;
		}else{
			return parseFloat(x);
		}
	}


	//Enter desde el input hace click al button seleccionado
	function enterClick(input, button){
		$("#"+input).keydown(function(e){
			if (e.which === 13) {
				$("#"+button).click();
			}
		});
	}
	//Quita todos los tags HTML
	function htmlToText(x){
		return x.replace(/<[^>]*>/gi, ' - ');
	}


	function getDateTime() {
		var now     = new Date(); 
		var year    = now.getFullYear();
		var month   = now.getMonth()+1; 
		var day     = now.getDate();
		var hour    = now.getHours();
		var minute  = now.getMinutes();
		var second  = now.getSeconds(); 
		if(month.toString().length == 1) {
			var month = '0'+month;
		}
		if(day.toString().length == 1) {
			var day = '0'+day;
		}   
		if(hour.toString().length == 1) {
			var hour = '0'+hour;
		}
		if(minute.toString().length == 1) {
			var minute = '0'+minute;
		}
		if(second.toString().length == 1) {
			var second = '0'+second;
		}   
		//var dateTime = day+'/'+month+'/'+year+' '+hour+':'+minute+':'+second;   
		var dateTime = day+'/'+month+'/'+year+' '+hour+':'+minute+' hs';   
		return dateTime;
	}

	//Separacion de miles para guaranies y decimales para dolares
	function RemoveRougeChar(convertString, separa){
		if(convertString.substring(0,1) == separa){
			return convertString.substring(1, convertString.length)            
			}
		return convertString;
	}

	function readImage(input, output, divFoto) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
				$('#'+divFoto).css('display', 'inline');
				$('#'+output)
					.attr('src', e.target.result)
					.height(120);
				if (input.id == "foto1"){
					$('#borrarFot1').css('display', 'inline');
					$('#borrar_foto1').val('');
				}
				if (input.id == "foto2"){
					$('#borrarFot2').css('display', 'inline');
					$('#borrar_foto2').val('');
				}
				
				
				
			};

			reader.readAsDataURL(input.files[0]);
		}
	}

	function noSubmitForm(obj){
		$(obj).on('keyup keypress', function(e) {
		  var code = e.keyCode || e.which;
		  if (code == 13) { 
			e.preventDefault();
			return false;
		  }
		});
	}
	
	function nextFocus(focused, _FORM_, salto) {
		if (typeof salto == "undefined") {
			salto = 1;
		}
		if (typeof _FORM_ == "undefined") {
			_FORM_ = $("form");
		}
		if (typeof focused == "undefined" || focused == null) {
			var focused = $(':focus');
			limitTag = false;
		}
		if (focused.length > 0 && (focused.prop("tagName") == 'INPUT' || focused.prop("tagName") == 'SELECT')) {
			var all_ = _FORM_.find("input[type=date]:not(.select2-focusser):visible,input[type=text]:not(.select2-focusser):visible,select.select2-hidden-accessible,textarea,select,button").not(':disabled, [readonly]');
			var nextIdx = all_.index(focused) + salto;
			next_ = all_.eq(nextIdx);
			if (next_.prop("tagName")=="BUTTON"){
				nextIdx = all_.index(focused) + salto + 1;
				next_ = all_.eq(nextIdx);
			}
			next_.focus();
			return false;
		}
		return true;
	}



	//Funcion que sirve para enviar variables por POST a un form en un popup. Esto evita que se vean las variables en la barra de dirección del navegador
	function OpenWindowWithPost(url, windowoption, name, params)
	{
		var form = document.createElement("form");
		form.setAttribute("method", "post");
		form.setAttribute("action", url);
		form.setAttribute("target", name);
		for (var i in params) {
			if (params.hasOwnProperty(i)) {
				var input = document.createElement('input');
				input.type = 'hidden';
				input.name = i;
				input.value = params[i];
				form.appendChild(input);
			}
		}
		document.body.appendChild(form);
		window.open("", name, windowoption).focus();
		form.submit();
		document.body.removeChild(form);
	}
	
	//Leemos cotización del día
	function cotizacion(){
		dolar_venta=0;
		$.ajax({
			dataType: 'json', async: false, cache: false, url: 'inc/cotizacion-data.php', type: 'POST', timeout: 10000,  data: {q: 'ver_cotizacion'},
			beforeSend: function(){
				NProgress.start();
			},
			success: function (json){
				NProgress.done();
				dolar_venta = parseInt(json.dolar_venta);
			},
			error: function (xhr) {
				NProgress.done();
				alertDismissJS("No se pudo consultar cotización del día. " + xhr.status + " " + xhr.statusText, 'error');
			}
		});
		return dolar_venta;
	}

	/**
	 * Calcula altura entre la altura de la ventana y la diferencia especificada por parametro. 
	 * @param {number} difencia 
	 * @param {number} minimo 
	 * @returns {number} Retorna el calculo si no el minimo especificado o por defecto.
	 */
	function calcAlturaPropiedad(difencia, minimo){
		// var dif = (typeof difencia === 'undefined' ? 100 : difencia);
		// var min = (typeof minimo === 'undefined' ? 500 : minimo);
		var calc = $(window).height() - difencia;
		return	(calc < minimo ? minimo : calc);
	}

	/**
	 * Realiza una busqueda del ruc ingresado
	 * @param {string} ruc 
     * @return {object} { ruc, dv, razon_social, telefono, direccion }
	 */
    function buscarRUC(ruc) {
        return $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/listados.php',
            timeout: 10000,
            type: 'POST',
            data: { q:'buscar_ruc', ruc },
            beforeSend: function(){
                NProgress.start();
                loading({text: 'Buscando RUC...'});
            },
            success: function (data) {
                NProgress.done();
                Swal.close();
            },
            error: function (jqXhr) {
                NProgress.done();
                Swal.close();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000);
            }
        });
    }

	/**
	 * Realiza una busqueda del ci ingresado
	 * @param {string} ruc 
     * @return {object} { cedula, nombre, nacimiento, sexo, nombre_solo, apellido, telefono, direccion }
	 */
    function buscarCI(ci) {
        return $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/listados.php',
            timeout: 10000,
            type: 'POST',
            data: { q:'buscar_ci', ci },
            beforeSend: function(){
                NProgress.start();
                loading({text: 'Buscando CI...'});
            },
            success: function (data) {
                NProgress.done();
                Swal.close();
            },
            error: function (jqXhr) {
                NProgress.done();
                Swal.close();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000);
            }
        });
	}

	/**
	 * Realiza una busqueda del ruc ingresado
	 * @param {string} ruc 
     * @return {object} 
	 */
    function buscarProveedor(ruc, tipo_proveedor) {
        return $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/listados.php',
            timeout: 10000,
            type: 'POST',
            data: { q:'buscar_proveedor', ruc, tipo_proveedor},
            beforeSend: function(){
                NProgress.start();
                loading({text: 'Buscando Proveedor...'});
            },
            success: function (data) {
                NProgress.done();
                Swal.close();
            },
            error: function (jqXhr) {
                NProgress.done();
                Swal.close();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000);
            }
        });
	}

	/**
	 * Realiza una busqueda de los clientes con el ruc ingresado
	 * @param {string} ruc 
     * @return {object} 
	 */
    function buscarCliente(ruc) {
        return $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/listados.php',
            timeout: 10000,
            type: 'POST',
            data: { q:'buscar_cliente', ruc },
            beforeSend: function(){
                NProgress.start();
                loading({text: 'Buscando Cliente...'});
            },
            success: function (data) {
                NProgress.done();
                Swal.close();
            },
            error: function (jqXhr) {
                NProgress.done();
                Swal.close();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000);
            }
        });
	}

/**
     * Realiza una busqueda del numero ingresado
     * @param {string} numero 
     * @return {object} 
     */
    function buscarFactura(numero) {
        return $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/listados.php',
            timeout: 10000,
            type: 'POST',
            data: { q:'buscar_facturas', numero },
            beforeSend: function(){
                NProgress.start();
                loading({text: 'Buscando Factura...'});
            },
            success: function (data) {
                NProgress.done();
                Swal.close();
            },
            error: function (jqXhr) {
                NProgress.done();
                Swal.close();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000);
            }
        });
    }

function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}

// Configuración select2
$.fn.select2.defaults.set('theme', 'bootstrap4');
$.fn.select2.defaults.set('language', 'es');

// Configuración BootstrapTable
$.fn.bootstrapTable.defaults.showAutoRefresh = false;

function buscarFuncionario(ci) {
    return $.ajax({
        dataType: 'json',
        async: false,
        cache: false,
        url: 'inc/listados.php',
        timeout: 10000,
        type: 'POST',
        data: { q:'buscar_funcionario', ci },
        beforeSend: function(){
            NProgress.start();
            loading();
        },
        success: function (data) {
            NProgress.done();
            Swal.close();
        },
        error: function (jqXhr) {
            NProgress.done();
            Swal.close();
            alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000);
        }
    });
}

$(document).ready(function () {
    // Init AutoNumeric
    $('input.autonumeric').initNumber();

    // Se asigna validación por default
    $('form.default-validation').each( function(index, form) {
        $(form).validate();
    });

});

// Config jQuery validate
$.validator.setDefaults({
    errorClass: 'is-invalid',
    // validClass: "is-valid",
    validClass: '',
    errorElement: 'div',
    onfocusout: false,
    onkeyup: false,
    ignoreTitle: true,
    // ignore: '',
    highlight: function(element, errorClass, validClass) {
        let $element = $(element);
        // Si el elemneto es select2
        if ($element.hasClass('select2-hidden-accessible')) {
            $element = $(`[aria-labelledby="select2-${element.id}-container"]`);
        }
        $element.addClass(errorClass).removeClass(validClass);
    },
    unhighlight: function(element, errorClass, validClass) {
        let $element = $(element);
        // Si el elemneto es select2
        if ($element.hasClass('select2-hidden-accessible')) {
            $element = $(`[aria-labelledby="select2-${element.id}-container"]`);
        }
        $element.removeClass(errorClass).addClass(validClass);
    },
    errorPlacement: function ($error, $element) {
        $error.addClass('invalid-feedback').removeClass('is-valid');

        let formGroupElement = $element.closest('.form-group');
        let appendToElement = !formGroupElement.length ? $element.parent() : formGroupElement;
        
        $error.appendTo(appendToElement);
    },
    invalidHandler: function(event, validator) {
        let $invalid = $(validator.errorList[0].element);
        // Si el elemneto esta debtro de un tab
        let tab_id = $invalid.closest('.tab-pane').attr('id');
        if (tab_id) {
            $(`.nav-item .nav-link[href='#${tab_id}']`).tab('show');
            setTimeout(() => $invalid.focus());
        }
    },
});

/**
 * Elimina las clases agregadas a los campos de un formulario validado por jQuery validate
 * @param {mixed} selector Selector del form
 */
function resetValidateForm(selector) {
    let validator = $(selector).data('validator');
    let errorElement = validator.settings.errorElement;
    let errorClass = validator.settings.errorClass;

    validator.resetForm();
    $(selector).find(errorClass.replace(/^|\s/g, '.')).not(errorElement).removeClass(errorClass);
}


/**
 * Agrega eventos de teclado para la navegación por una tabla bootstrapTable dentro de un modal
 * @param {mixed} tabla Selector para la tabla
 * @param {mixed} modal Selector para el modal
 * @param {callback} callback Se ejecuta al presionar la tecla enter, pasa como parámetro la fila seleccionada
 */
function keyboard_navigation_for_table_in_modal(tabla, modal, callback) {
    let index = -1;
    let $tabla = $(tabla);
    let $modal = $(modal);

    $tabla.on('post-body.bs.table', function(e, data) { index = -1; });
    $tabla.on('check.bs.table', function(e, row, $element) {
        index = $element.parents('tr').data().index;
    });

    // NAVEGACION POR TECLADO ENTRE REGISTROS EN TABLA DE PRESUPUESTOS
    $modal.off('keydown').on('keydown', function(e) {
        switch(e.which) {
            case 13: // <ENTER>
                let seleccionado = $tabla.bootstrapTable('getSelections');
                if (seleccionado.length > 0) {
                    callback(seleccionado[0]);
                }
            break;
            case 40: // <ARROW DOWN>
                $tabla.bootstrapTable('uncheckAll');
                if ((index + 1) < $tabla.bootstrapTable('getData').length) index++;
                $tabla.bootstrapTable('check', index);
            break;
            case 38: // <ARROW UP>
                $tabla.bootstrapTable('uncheckAll');
                if (index > 0) index--;
                if (index < 0) index++;
                $tabla.bootstrapTable('check', index);
            break;
            case 27: // <ESC>
                $modal.modal('hide');
            break;
        }
    });
}

/**
 * Agrega eventos de teclado para la navegación por una tabla bootstrapTable
 * @param {mixed} tabla Selector para la tabla
 * @param {Mousetrap} mousetrap instancia de la clase Mousetrap
 * @param {callback} callback Se ejecuta al presionar la tecla enter, pasa como parámetro la fila seleccionada
 */
function MousetrapTableNavigation(tabla, mousetrap, callback) {
    let index_shift = 0;
    let index = -1;
    let $tabla = $(tabla);

    $tabla.on('post-body.bs.table', function(e, data) { index = -1; });
    $tabla.on('check.bs.table', function(e, row, $element) {
        if (index != $element.parents('tr').data().index) {
            index = $element.parents('tr').data().index;
            $tabla.bootstrapTable('uncheckAll');
            $tabla.bootstrapTable('check', index);
        }
    });

    // NAVEGACION POR TECLADO ENTRE REGISTROS
    if (typeof callback === 'function') {
        mousetrap.bind('enter', function(e) {
            let singleSelect = $tabla.bootstrapTable('getOptions').singleSelect;
            let selections = $tabla.bootstrapTable('getSelections');
            if (selections.length > 0) {
                if (singleSelect !== true) {
                    callback(selections);
                } else {
                    callback(selections[0]);
                }
            }
        });
    }
    mousetrap.bind('up', function(e) {
        e.preventDefault(); 
        $tabla.bootstrapTable('uncheckAll');
        if (index > 0) index--;
        if (index < 0) index++;
        $tabla.bootstrapTable('check', index);
        index_shift = 0;
        setTimeout(() => focus(), 10);
    });
    mousetrap.bind('down', function(e) {
        e.preventDefault(); 
        $tabla.bootstrapTable('uncheckAll');
        if ((index + 1) < $tabla.bootstrapTable('getData').length) index++;
        $tabla.bootstrapTable('check', index);
        index_shift = 0;
        setTimeout(() => focus(), 10);
    });

    mousetrap.bind('shift+up', function(e) {
        e.preventDefault(); 
        let singleSelect = $tabla.bootstrapTable('getOptions').singleSelect;
        if (singleSelect == true) return false;

        if (index > 0) {
            index_shift++;
            index--;
        }
        if (index < 0) index++;
        if (index_shift > 0) {
            $tabla.bootstrapTable('check', index);
        } else {
            $tabla.bootstrapTable('uncheck', index+1);
        }
    });
    mousetrap.bind('shift+down', function(e) {
        e.preventDefault(); 
        let singleSelect = $tabla.bootstrapTable('getOptions').singleSelect;
        if (singleSelect == true) return false;

        if ((index + 1) < $tabla.bootstrapTable('getData').length) {
            index_shift--;
            index++;
        }
        if (index_shift >= 0) {
            $tabla.bootstrapTable('uncheck', index-1);
        } else {
            $tabla.bootstrapTable('check', index);
        }
    });

    function focus() {
        $tabla.find('tbody tr[tabindex]').removeAttr('tabindex');
        $tabla.find(`tr[data-index=${index}]`).attr('tabindex', '-1').focus();
    }
}

/*function googleMaps(id, lat_input, lon_input, lat, lon) {
    var marker;
    position = {
        coords: {
            latitude: lat,
            longitude: lon
        }
    }
    success(position);

    function success(position) {
        var coords = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

        var myOptions = {
            zoom: 12,
            center: coords,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        var map = new google.maps.Map(document.querySelector(id), myOptions);

        addMarker(coords, 'Ubicación', map);

        google.maps.event.addListener(map, 'click', function(event) {
            addMarker(event.latLng, 'Click Generated Marker', map);
        });

        function addMarker(latlng, title, map) {
            if (!marker) {
                marker = new google.maps.Marker({
                    position: latlng,
                    map: map,
                    title: title,
                    draggable: true
                });
            } else {
                marker.setPosition(latlng);
                $(lat_input).val(latlng.lat());
                $(lon_input).val(latlng.lng());
            }

            google.maps.event.addListener(marker, 'drag', function(event) {
                $(lat_input).val(event.latLng.lat());
                $(lon_input).val(event.latLng.lng());
            });

            google.maps.event.addListener(marker, 'dragend', function(event) {
                $(lat_input).val(event.latLng.lat());
                $(lon_input).val(event.latLng.lng());
            });

            //Borrar marcador
            // google.maps.event.addListener(marker, "dblclick", function() {
                // marker.setMap(null);
            // });
        }

        $(lat_input).val(position.coords.latitude);
        $(lon_input).val(position.coords.longitude);
    }
}*/

/**
 * Formatea una fecha y hora sensible al idioma usando la API de Internacionalización.
 * @param {string} fecha_hora 
 * @param {object} format_options 
 * @param {string} locate 
 * @return {string} 
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/DateTimeFormat
 */
function intlDateTimeFormat(fecha_hora, format_options, locate = 'es-PY'){
    let date = Date.parse(fecha_hora);
    if(!isNaN(date)){
        let dateTimeFormat = new Intl.DateTimeFormat(locate, format_options);
        return dateTimeFormat.format(date);
    } return fecha_hora;
}

/**
 * Contenedor de métodos para la navegación por una matriz
 */
class MatrizNavegation {
    /**
     * @param {int} columns
     * @param {int} rows
     * @param {int} x
     * @param {int} y
     */
    constructor (columns, rows, x, y) {
        this.x = x || 1;
        this.y = y || 1;
        this.columns = columns || 0;
        this.rows = rows || 0;
    }
    /**
     * Actualiza la cantidad de columnas de la matriz
     * @param {int} columns
     */
    setColumns(columns) {
        this.columns = columns;
    }
    /**
     * Actualiza la cantidad de filas de la matriz
     * @param {int} rows
     */
    setRows(rows) {
        this.rows = rows;
    }
    /**
     * Retorna la cantidad de columnas de la matriz
     * @return {int}
     */
    getColumns() {
        return this.columns;
    }
    /**
     * Retorna la cantidad de filas de la matriz
     * @return {int}
     */
    getRows() {
        return this.rows;
    }
    /**
     * Actualiza la posición de la fila dentro de la matriz
     * @param {int} rows
     */
    setX(x) {
        this.x = x;
    }
    /**
     * Actualiza la posición de la columna dentro de la matriz
     * @param {int} rows
     */
    setY(y) {
        this.y = y;
    }
    /**
     * Retorna la posición de la fila dentro de la matriz
     * @return {int}
     */
    getX() {
        return this.x;
    }
    /**
     * Retorna la posición de la columna dentro de la matriz
     * @return {int}
     */
    getY() {
        return this.y;
    }

    /**
     * Avanza la posición a la siguiente celda
     */
    next() {
        if ((this.x + 1) <= this.columns) {
            this.x++;
        } else if ((this.y + 1) <= this.rows) {
            this.x = 1;
            this.y++;
        }
    }
    /**
     * Retrocede la posición a la celda anterior
     */
    prev() {
        if (this.x > 1) {
            this.x--;
        } else if (this.y > 1) {
            this.x = this.columns;
            this.y--;
        }
    }

    /**
     * Cambia la posición a la celda de la izquierda
     */
    left() {
        if (this.x > 1) this.x--;
    }
    /**
     * Cambia la posición a la celda de la derecha
     */
    right() {
        if ((this.x + 1) <= this.columns) this.x++;
    }
    /**
     * Cambia la posición a la celda de arriba
     */
    up() {
        if (this.y > 1) this.y--;
    }
    /**
     * Cambia la posición a la celda de abajo
     */
    down() {
        if ((this.y + 1) <= this.rows) this.y++;
    }
}

/**
 * Asigna eventos a la tabla para la navegación por celdas
 * @param {jQuery} $tabla
 * @param {Mousetrap} Mousetrap
 */
function MousetrapTableNavigationCell(tabla, Mousetrap, check = false) {
    let $tabla = $(tabla);
    let mn = new MatrizNavegation();
    let css_class = 'bst-celda-editable';

    $tabla.on('post-body.bs.table', function(e, data) {
        let table_columns = $tabla.bootstrapTable('getVisibleColumns');
        let columns = table_columns.reduce(function(acc, value, index) {
            if (value.editable) acc++;
            return acc;
        }, 0);
        let rows = data.length;

        mn.setRows(rows);
        mn.setColumns(columns);

        if (mn.getX() > columns && mn.getX() > 1) mn.setX(columns);
        if (mn.getY() > rows && mn.getY() > 1) mn.setY(rows);
        setTimeout(() => resaltar(), 10);
    });
    $tabla.on('editable-shown.bs.table', function() { Mousetrap.pause(); setTimeout(() => quitarResaltado(), 10) });
    $tabla.on('editable-hidden.bs.table', function(e, data) { Mousetrap.unpause(); setTimeout(() => resaltar(), 10) });
    $tabla.on('click-cell.bs.table', function(e, field, value, row, $element) {
        if ($element.find('.editable').length > 0) {
            mn.setY($element.parent().data().index + 1);
            mn.setX($element.parent().find('.editable').index($element.find('.editable')) + 1);

            setTimeout(() => editable(), 10);
        }
    });

    // NAVEGACION POR TECLADO ENTRE CELDAS
    Mousetrap.bind('enter', function(e) {
        setTimeout(() => editable(), 10);
    });
    Mousetrap.bind('shift+tab', function(e) {
        e.preventDefault(); 
        mn.prev();
        setTimeout(() => resaltar(), 10);
    });
    Mousetrap.bind('tab', function(e) {
        e.preventDefault(); 
        mn.next();
        setTimeout(() => resaltar(), 10);
    });
    Mousetrap.bind('up', function(e) {
        e.preventDefault(); 
        mn.up();
        setTimeout(() => resaltar(), 10);
    });
    Mousetrap.bind('down', function(e) {
        e.preventDefault(); 
        mn.down();
        setTimeout(() => resaltar(), 10);
    });
    Mousetrap.bind('left', function(e) {
        e.preventDefault(); 
        mn.left();
        setTimeout(() => resaltar(), 10);
    });
    Mousetrap.bind('right', function(e) {
        e.preventDefault(); 
        mn.right();
        setTimeout(() => resaltar(), 10);
    });

    function resaltar() {
        quitarResaltado();
        td().addClass(css_class).attr('tabindex', '-1').focus();
        if (check && mn.getRows() > 0) $tabla.bootstrapTable('check', mn.getY() - 1);
    }
    function quitarResaltado() {
        $(`.${css_class}`).removeClass(css_class).removeAttr('tabindex');
        if (check) $tabla.bootstrapTable('uncheckAll');
    }
    function td() {
        return $($tabla.find(`tr[data-index=${mn.getY() - 1}]`).find('.editable')[mn.getX() - 1]).parent();
    }
    function editable() {
        return $($tabla.find(`tr[data-index=${mn.getY() - 1}]`).find('.editable')[mn.getX() - 1]).editable('show');
    }
}

/**
 * Completa con ceros a la izquierda hasta llegar a siete dígitos
 */
function zerofill(number) {
    if (number) {
        return number.toString().padStart(7, "0");
    }
    return number;
}

/* FUNCION QUE DIBUJA EL MAPA SEGUN LOS PARAMETROS QUE LE PASES EN LATIT Y LONGIT */
function dibujarMapa(latit, longit, nombreMapa) {
    
    let campoLatitud = $('#lat');
    let campoLongitud = $('#lng');
    let campoLatitudLongitud = $('#lat_lng');

    /* SELECCIONA EL TIPO DE MAPA A UTILIZAR */
    const TilesProvider = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    /* SELECCIONA LA IMAGEN QUE SERA PUNTERO EN EL MAPA */
    let iconMarker = L.icon({
        iconUrl: 'dist/images/marker.png',
        iconSize: [60, 60],
        iconAnchor: [30, 60]
    });

    /* SE DEBE AGREGAR ZOOMCONTROL FALSE PARA PODER MODIFICAR DE LUGAR LOS CONTROLES */
    /* let myMap = L.map('myMap', { zoomControl: false }).setView([latit, longit], 13); */
    let myMap = L.map(nombreMapa, { zoomControl: false }).setView([latit, longit], 13);

    /* PASA LOS DATOS DE LATITUD Y LONGITUD A LOS CAMPOS OCULTOS */
    campoLatitud.val(latit);
    campoLongitud.val(longit);
    campoLatitudLongitud.val(`${latit},${longit}`);

    /* DESACTIVA EL ZOOM CON DOBLE CLICK */
    myMap.doubleClickZoom.disable();

    /* POSICIONA LOS CONTROLES DEL ZOOM A LA DERECHA */
    L.control.zoom({ position: "topright" }).addTo(myMap);

    /* SELECCIONA EL ZOOM A APLICAR AL MAPA */
    L.tileLayer(TilesProvider, {
        maxZoom: 18,
    }).addTo(myMap);

    /* AGREGA EL MARCADOR CON POP-UP AL MAPA */
    /* const marcador = L.marker([latit, longit], { icon: iconMarker, alt: 'Ubicacion', draggable: true, autoPan: true }).addTo(myMap).bindPopup('IA-Campo te encontro!'); */

    /* AGREGA EL MARCADOR SIMPLE AL MAPA */
    const marcador = L.marker([latit, longit], { icon: iconMarker, alt: 'Ubicacion', draggable: true, autoPan: true }).addTo(myMap);

    /*MODIFICA LA POSICION DEL MARCADOR EN DOBLE CLICK*/
    /* myMap.on('dblclick', e => { */
    myMap.on('click', e => {
        let latlong = myMap.mouseEventToLatLng(e.originalEvent);
        marcador.setLatLng([latlong.lat, latlong.lng])
        console.log('Latitud: ' + latlong.lat + ', Longitud: ' + latlong.lng);
        campoLatitud.val(latlong.lat);
        campoLongitud.val(latlong.lng);
        campoLatitudLongitud.val(`${latlong.lat},${latlong.lng}`);
    });

    /* VERIFICA LA LATITUD Y LONGITUD CUANDO SE MUEVE EL PUNTERO */
    marcador.on("moveend", e => {
        let latlong = marcador.getLatLng();
        //console.log('Latitud: ' + latlong.lat + ', Longitud: ' + latlong.lng);
        campoLatitud.val(latlong.lat);
        campoLongitud.val(latlong.lng);
        campoLatitudLongitud.val(`${latlong.lat},${latlong.lng}`);
    });
    
    return myMap;

}

function ceiling(number, significance){
    return Math.ceil((Number(number) / Number(significance))) * Number(significance);
}

function actualiza_estado_puntos_cliente() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: 'inc/administrar-canjes-premios-data',
        cache: false,
        data: { q: 'actualiza_estado_puntos_cliente' },
        beforeSend: function () { NProgress.start(); },
        success: function (data, status, xhr) {
            NProgress.done();
            alertDismissJsSmall(data.mensaje, data.status, 2000)
        },
        error: function (xhr) {
            NProgress.done();
        }
    });
}

function empty(variable) {
    return (variable == 0 || variable == '' || variable == null || variable == undefined || variable == '0')
}

/*function buscarClienteRuc(ruc, razon_social) {
        return $.ajax({
            dataType: 'json',
            cache: false,
            url: 'inc/gastos-data.php',
            timeout: 10000,
            type: 'POST',
            data: { q:'buscar_razon_social', ruc, razon_social},
            beforeSend: function(){
                NProgress.start();
                loading({text: 'Buscando razon_social...'});
            },
            success: function (data) {
                NProgress.done();
                Swal.close();
            },
            error: function (jqXhr) {
                NProgress.done();
                Swal.close();
                alertDismissJsSmall($(jqXhr.responseText).text().trim(), 'error', 2000);
            }
        });
    }*/
