/* BootstrapTable Utils */

/**
 * TRUNCAR COLUMNAS MUY LARGAS
 * cellStyle: bstTruncarColumna
 */
function bstTruncarColumna(value, row, index, field) {
    // TOOLTIP EN COLUMNAS TRUNCADAS
    $('.table').on('mouseenter', ".verTooltip", function () {
        var $this = $(this);
        $this.attr('title', $this.text());
    });
    return {
        classes: 'verTooltip',
        css: { 'max-width': '150px' , 'white-space': 'nowrap', 'overflow': 'hidden', 'text-overflow': 'ellipsis' }
    };
}

/**
 * SUMA LOS VALORES DE UNA COLUMNA
 * footerFormatter: bstFooterSumatoria
 */
function bstFooterSumatoria (data) {
    let field = this.field;
    var total = separadorMiles(data.reduce((acc, current) => acc += Number(quitaSeparadorMiles(current[field])), 0));
	return `<span class="f16">${total}</span>`;
}

function bstFooterTextTotal (data) {
	return '<span class="f16">TOTALES</span>';
}

/**
 * Estilo para las columnas de estado
 * formatter: bstColorEstado
 */
function bstFormatterEstado(data) {
    switch (data) {
        case 'Pendiente':
        case 'Inactivo':
        case 'Vencido':
        case 'Deshabilitado':
        case 'Rendido':
            return '<span style="text-transform: uppercase;" class="badge badge-pill badge-danger">' + data + '</span></b>';
        case 'Caducado':
        return '<span style="text-transform: uppercase;" class="badge badge-pill badge-danger">' + data + '</span></b>';
        case 'Cargado':
        case 'Aprobado':
        case 'Activo':
        case 'Pagado':
        case 'Recibido':
        case 'Procesado':
        case 'Reemplazado':
        case 'Habilitado':
        case 'En Tránsito':
        case 'Utilizado':
        case 'Abierto':
        case 'Entero':
            return '<span style="text-transform: uppercase;" class="badge badge-pill badge-success">' + data + '</span></b>';
        case 'Anulado':
        case 'Rechazado':
        case 'Bloqueado':
            return '<span style="text-transform: uppercase;" class="badge badge-pill badge-dark">' + data + '</span></b>';
        case 'F. Incompleto':
        case 'Procesado Parcial':
        case 'Pagado Parcial':
        case 'Fraccionado':
        case 'Contraseña Expirada':
            return '<span style="text-transform: uppercase;" class="badge badge-pill badge-warning">' + data + '</span></b>';
        case 'Finalizado':
        case 'Procesado Total':
        case 'No Requerido':
        case 'Sin Canje':
        case 'Pagado Total':
            return '<span style="text-transform: uppercase;" class="badge badge-pill badge-info">' + data + '</span></b>';
        default:
            return '<span style="text-transform: uppercase;" class="badge badge-pill badge-default">' + data + '</span></b>';
    }
}

/**
 * Estilo para las columnas condicionales(si o no)
 * formatter: bstCondicional
 */
function bstCondicional(data) {
    switch (parseInt(data)) {
        case 0: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-dark">NO</span></b>';
        case 1: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-success">SI</span></b>';
        case 2: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-dark">NO</span></b>';
        default: return '<span style="text-transform: uppercase;" class="badge badge-pill badge-default">-</span></b>';
    }
}

/**
 * Añade el simbolo del '%'
 * formatter: bstColorEstado
 */
function bstFormatterPorcentaje(data) {
    let value = parseInt(data);
    return value + '%';
}

function bstFormatterPorcentajeDecimal(data) {
    let value = parseFloat(data);
    return value + '%';
}

function bstFooterStyle(column) {
    return {
        classes: 'bstFooterStyle'
    }
}

/**
 * Calcula la altura de la tabla a partir de la altura de la ventana
 * @param {int} restar Valor a restar de la altura de la ventana
 * @param {int} min Altura mínima de la tabla
 * @return {int}
 */
function bstCalcularAlturaTabla(restar, min) {
    let altura_tabla = window.innerHeight - Math.abs(restar) || 0;
    return (altura_tabla < Math.abs(min) || 0) ? min : altura_tabla;
}


/**
 * Formatea un valor de fecha y hora acorde al formato `DD/MM/YYYY HH:mm` 
 * @param {string} fecha_hora 
 * @return {string}
 */
 function bstFormatterFechaHora(fecha_hora){
    return intlDateTimeFormat(fecha_hora, {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
    });
}

/**
 * Formatea un valor de fecha acorde al formato `DD/MM/YYYY` 
 * @param {string} fecha 
 * @return {string}
 */
function bstFormatterFecha(fecha){
    if(!fecha) return null;
    return intlDateTimeFormat(`${fecha}T00:00`, {
        year: 'numeric', month: '2-digit', day: '2-digit'
    });
}

