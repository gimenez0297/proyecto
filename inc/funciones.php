<?php
require __DIR__.'/auth/autoload.php';
$auth = new \Delight\Auth\Auth($db_auth);
require_once "mysql.php";
include 'class.upload.php';

require_once ("funciones/permisos-funciones.php");
require_once ("funciones/solicitudes-compra-funciones.php");
require_once ("funciones/ordenes-compras-funciones.php");
require_once ("funciones/recepcion-compras-funciones.php");
require_once ("funciones/productos-funciones.php");
require_once ("funciones/caja-funciones.php");
require_once ("funciones/solicitudes-deposito-funciones.php");
require_once ("funciones/insumos-funciones.php");

/**
 * @var string Representa el tipo de MIME permitido para almacenar archivos PDF.
 */
define('ALLOWED_PDF_MIME_TYPE', "application/pdf");

/* ***************************************************************************** */
/*                                   ROLES                                       */
/* ***************************************************************************** */
/**
 * @var array Representa los tipos de imagen permitidos para almacenar.
 */
define('ALLOWED_IMG_MIME_TYPE', ["image/jpg", "image/jpeg", "image/png"]);

/**
 * @var array Representa el rol de vendedor en la BD del sistema.
 */
define('ROL_VENDEDOR', 4);

/**
 * @var array Representa el rol de ADMIN en la BD del sistema.
 */
define('ROL_ADMIN', 1);

/* ***************************************************************************** */
/*                             TIPOS PUESTOS                                     */
/* ***************************************************************************** */

/**
 * @var array Representa el TIPO PUESTO CAJERO en la BD del sistema.
 */
define('TIPO_PUESTO_CAJERO', 1);

function url() {
	$host = $_SERVER['HTTP_HOST'];
	return "http://$host/proyecto/";
}

/* function url_pagina() {
	$host = $_SERVER['HTTP_HOST'];
	return "http://$host/santa-victoria/";
} */

function verificaLogin($pag = "") {
	require __DIR__.'/auth/autoload.php';
	$auth = new \Delight\Auth\Auth($db_auth);
	if (!$auth->isLoggedIn()){
		return false;
	}else if ($auth->isLoggedIn() && !$auth->isNormal()){
		return false;
	} else if ($pag) {
		// VERIFICAMOS SI TIENE PERMISO SOBRE LA PÁGINA
		$pagina = str_replace('.php', '', $pag);
		$id_usuario = $auth->getUserId();

		$db = DataBase::conectar();
        $db->setQuery("SELECT u.id FROM users u JOIN roles_menu rm ON rm.id_rol=u.id_rol JOIN menus m ON rm.id_menu=m.id_menu WHERE m.estado='Habilitado' AND u.id=$id_usuario AND m.url LIKE '%/$pagina'");
		$row = $db->loadObject();
		if (!$row) {
			return false;
		}
	}
	return true;
}

function url_amigable($url_tmp){
	//header('Content-Type: text/html; charset=utf-8');
	//Convertimos a minúsculas y UTF8
	$url_utf8 = mb_strtolower($url_tmp, 'UTF-8');
	//Reemplazamos espacios por guion
	$find = array(' ', '&', '\r\n', '\n', '+');
	$url_utf8 = str_replace ($find, '-', $url_utf8);
	
	//Convertimos todos los caracteres especiales a ASCII NO ANDA CON PHP 5.4
	//$url_utf8 = iconv('UTF-8', 'ASCII//TRANSLIT', $url_utf8); 
	
	$url_utf8 = strtr(utf8_decode($url_utf8), 
			utf8_decode('_àáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'),
							'-aaaaaaaceeeeiiiionoooooouuuuyy');
	
	//Ya que usamos TRANSLIT en el comando iconv, tenemos que limpiar los simbolos que quedaron
	$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	$url = preg_replace ($find, $repl, $url_utf8);
	return $url;
}
function resizeImage($sourceImage, $targetImage, $maxWidth, $maxHeight, $quality = 90){
	// Obtain image from given source file.
	if (!$image = @imagecreatefromjpeg($sourceImage))
	{
		return false;
	}
	// Get dimensions of source image.
	list($origWidth, $origHeight) = getimagesize($sourceImage);
	if ($maxWidth == 0)
	{
		$maxWidth  = $origWidth;
	}
	if ($maxHeight == 0)
	{
		$maxHeight = $origHeight;
	}
	// Calculate ratio of desired maximum sizes and original sizes.
	$widthRatio = $maxWidth / $origWidth;
	$heightRatio = $maxHeight / $origHeight;
	// Ratio used for calculating new image dimensions.
	$ratio = min($widthRatio, $heightRatio);
	// Calculate new image dimensions.
	$newWidth  = (int)$origWidth  * $ratio;
	$newHeight = (int)$origHeight * $ratio;
	// Create final image with new dimensions.
	$newImage = imagecreatetruecolor($newWidth, $newHeight);
	imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
	imagejpeg($newImage, $targetImage, $quality);
	// Free up the memory.
	imagedestroy($image);
	imagedestroy($newImage);
	return true;
}
function limpia_archivo($url_tmp) {
	$url_utf8 = mb_strtolower($url_tmp, 'UTF-8');
	$find = array(' ', '&', '\r\n', '\n', '+');
	$url_utf8 = str_replace ($find, '-', $url_utf8);
	$url_utf8 = strtr(utf8_decode($url_utf8), 
			utf8_decode('_àáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'),
							'-aaaaaaaceeeeiiiionoooooouuuuyy');
	//Ya que usamos TRANSLIT en el comando iconv, tenemos que limpiar los simbolos que quedaron
	$find = array('/[^a-z0-9.\-<>]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	$url = preg_replace ($find, $repl, $url_utf8);
	return $url;
}

function mesEspanol($mes) {
	$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	return $meses[$mes-1];
}

function datosUsuario($username) {
	$db = DataBase::conectar();
	$db->setQuery("SELECT * from users where username='$username'");
	$u = $db->loadObject();
	return $u;
}
function permisos($username) {
	$db = DataBase::conectar();
	$nombre_archivo = basename($_SERVER['PHP_SELF']);
	$pagina = str_replace('.php', '', $nombre_archivo);
	$db->setQuery("SELECT r.rol, m.titulo AS titulo_pagina, rm.acceso, rm.insertar, rm.editar, rm.eliminar
					FROM users u
					JOIN roles r ON r.id_rol=u.id_rol
					JOIN roles_menu rm ON rm.id_rol=r.id_rol
					JOIN menus m ON rm.id_menu=m.id_menu
					WHERE username='$username' AND m.url LIKE '%/$pagina'");
	$p = $db->loadObject();
	return $p;
}

function verRol($auth) {
	// ROLES_MASK 1 = ADMIN
	// ROLES_MASK 2 = VENTAS
	// ROLES_MASK 4 = DEPOSITO
	// SUMAR ROLES PARA TENER EL ROLES_MASK
	// $auth->admin()->getRolesForUserById($id_usuario); POR ID DE USUARIO
	return $auth->getRoles(); //DEL USUARIO LOGUEADO
}

function menu($id_usuario) {
	$db = DataBase::conectar();
    $db->setQuery("SELECT m.id_menu, rm.id_rol, IFNULL(m.id_menu_padre, 0) AS id_menu_padre, m.menu, m.url, m.icono, m.orden
					FROM menus m
					JOIN roles_menu rm ON m.id_menu=rm.id_menu
					JOIN users u ON u.id_rol=rm.id_rol
					WHERE m.estado='Habilitado' AND u.id=$id_usuario
					UNION
					SELECT mp.id_menu, rm.id_rol, IFNULL(mp.id_menu_padre, 0) AS id_menu_padre, mp.menu, mp.url, mp.icono, mp.orden
					FROM menus m
					JOIN roles_menu rm ON m.id_menu=rm.id_menu
					JOIN menus mp ON m.id_menu_padre=mp.id_menu
					JOIN users u ON u.id_rol=rm.id_rol
					WHERE mp.estado='Habilitado' AND u.id=$id_usuario
					ORDER BY orden");

    $rows = $db->loadObjectList();

	// Se agrupan los menus y submenus
    $menus = [];
    foreach ($rows as $menu) {
		$menus[$menu->id_menu_padre][] = $menu;
    }

	// Funcion recursiva que crea el html
    function html($id_menu, $menus) {
        $html = "";
        if (isset($menus[$id_menu])) {
            foreach ($menus[$id_menu] as $menu) {
				// Menus
                if(!isset($menus[$menu->id_menu])) {
                    $html .= '<li><a href="' . $menu->url .'">' . $menu->menu . $menu->icono . '</a></li>';
				}
				// Menus padres
                if(isset($menus[$menu->id_menu])) {
					$html .= '<li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">' . $menu->icono .'<span class="hide-menu">' . $menu->menu . '</span></a>
								<ul aria-expanded="false" class="collapse">';
                    $html .= html($menu->id_menu, $menus);
					$html .= '</ul>
							</li>';
                }
            }
        }
        return $html;
    }
    echo html(0, $menus);
}

function datosSucursal($username) {
	$db = DataBase::conectar();
	$db->setQuery("SELECT * FROM sucursales s INNER JOIN users u ON u.id_sucursal=s.id_sucursal AND u.username='$username'");
	$u = $db->loadObject();
	return $u;
}

function configuracionSistema() {
	$db = DataBase::conectar();
	$db->setQuery("SELECT * FROM configuracion WHERE estado=1");
	$config = $db->loadObject();
	return $config;
}

function mostrarPlanPuntos() {
	$db = DataBase::conectar();
	$db->setQuery("SELECT * FROM plan_puntos WHERE tipo=1");
	$config = $db->loadObject();
	return $config;
}

function datosEmpresa($id_sucursal) {
	$db = DataBase::conectar();
    $db->setQuery("SELECT ruc, razon_social, id_sucursal, sucursal, nombre_empresa, concat(nombre_empresa,' - ',sucursal) as sucursal_name, direccion 
                    FROM sucursales 
                    WHERE id_sucursal=$id_sucursal AND estado=1 
                    ORDER BY sucursal");
	$u = $db->loadObject();
	return $u;
}

function fechaLatina($fecha) {
    $fecha = substr($fecha,0,10);
	/*$date = new DateTime($fecha);
	return $date->format('d/m/Y');*/
    list($anio,$mes,$dia)=explode("-",$fecha);
	if (!$anio){
		return "";
	}else{
		return $dia."/".$mes."/".$anio;
	}
}
function fechaLatinaHora($fecha){
	/*$date = new DateTime($fecha);
	return $date->format('d/m/Y H:i');*/
    list($anio,$mes,$dia)=explode("-",$fecha);
	$hora = substr($fecha,11,5);
	if (!$anio){
		return "";
	}else{
		return substr($dia,0,2)."/".$mes."/".$anio." ".$hora;
	}
}
function fechaMYSQL($fecha){
    $fecha = substr($fecha,0,10);
    list($dia,$mes,$anio)=explode("/",$fecha);
    return $anio."-".$mes."-".$dia;
}
function fechaMYSQLHora($fecha){
    $fecha_sola = substr($fecha,0,10);
	$fecha_hora = substr($fecha,11,16);
    list($dia,$mes,$anio)=explode("/",$fecha_sola);
	list($hora,$min) = explode(":",$fecha_hora);
    return $anio."-".$mes."-".$dia." ".$hora.":".$min;
}
function getAutoincrement($table){
	$db = DataBase::conectar();
	$db->setQuery("SELECT LPAD(`AUTO_INCREMENT`,9,'0') as auto FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
	$r = $db->loadObject()->auto;
	return $r;
}
function redondearGs($gs){
	if (strlen($gs) >= 4){
	   $a = (int)$gs / 100;
	   $b = round($a);
	   $c = $b * 100;
	   return $c;
	}else if (strlen($gs) <= 3)	{
		$a = (int)$gs / 100;
		$b = round($a);
	    $c = $b * 100;
		return $c;
	} 
}
function separadorMiles($number){
	if (is_numeric($number)){
		$nro=number_format($number,0, ".", ".");
		return $nro;
	}
}
function separadorMilesDecimales($number){
	if (is_numeric($number)){
		$nro=number_format($number,2, ",", ".");
		return $nro;
	}
}
function quitaSeparadorMiles($x){
    if($x) {
        return str_replace(['.',','],['','.'],$x);
    }else{
        return 0;
    }
}
function fechaEspanol($x){
	$dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
	$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	if ($x == "dia"){
		return $dias[date('w')];
	}else{
		return $dias[date('w')].", ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
	}
}

function diaEspanol($fecha){
	$dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
	return $dias[date("w", strtotime($fecha))];
}

function nombrePagina($pagina){
	$db2 = DataBase::conectar();
	$db2->setQuery("SELECT titulo from menus where url like '%".$pagina."'");
	$pa = $db2->loadObject();
	return $pa->titulo;
}
function alertDismiss($msj, $tipo){
	
	switch ($tipo){
		case 'error':
			$salida = "<div class='alert alert-danger'> <i class='fa fa-exclamation-triangle'></i>&nbsp;&nbsp;$msj&nbsp;&nbsp;&nbsp;<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
					 <span aria-hidden='true'>&times;</span></button></div>";
		break;
		
		case 'error_span':
			$salida = "<span class='alert alert-danger alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-exclamation-sign'>&nbsp;</span>$msj</span>";
		break;
		
		case 'ok':
			$salida = "<div class='alert alert-success'> <i class='fa fa-check-circle'></i>&nbsp;&nbsp;$msj&nbsp;&nbsp;&nbsp;<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
						<span aria-hidden='true'>&times;</span></button></div>";
		break;
		
		case 'ok_span':
			$salida = "<span class='alert alert-success alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-ok'>&nbsp;</span>$msj</span>";
		break;
		
		case 'yellow':
			$salida = "<div class='alert alert-warning alert-dismissable'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
			<span class='glyphicon glyphicon-ok'>&nbsp;</span>$msj</div>";
		break;
		
	}
	return $salida; 
}
function sweetAlert($msj, $tipo){
	return ["msj"=>$msj, "tipo"=>"error"];
}
function exportarExcel($datos, $titulo){
		
	$hoy=date('d-m-Y');
	$nombre='xls/Exportado_'.$titulo.'_'.$hoy.".xls";
	
	$xml = simplexml_load_string($datos);
	$salida = "<table border='1'>";
	foreach ($xml->Worksheet->Table->Row as $row) {
	   $celda = $row->Cell;
	   $salida .= "<tr>".$celda;
	   //echo "\t";
	   foreach ($celda as $cell) {
			$salida .= "<td>".$cell->Data."</td>";
			//echo "\t";
		}
		$salida .= "</tr>";
	}
	$salida .= "</table>";
	//print $salida;
	
	file_put_contents($nombre, utf8_decode($salida));
	
	echo $nombre;
}
function ceiling($number=NULL, $significance=1)
{
	return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
}
class Password {
    const SALT = 'freelancerpy';
    public static function hash($password) {
        return hash('sha512', self::SALT . $password);
    }
    public static function verify($password, $hash) {
        return ($hash == self::hash($password));
    }
}
function convert($size,$unit){
	if($unit == "KB"){
	  	return $fileSize = round($size / 1024,4);	
	}
	if($unit == "MB"){
	  	return $fileSize = round($size / 1024 / 1024,4);	
	}
	if($unit == "GB"){
	  	return $fileSize = round($size / 1024 / 1024 / 1024,4);	
	}
}

// FUNCION PARA CALCULAR CODIGO DE BARRA EAN13
function ean13_check_digit($digits){
    //first change digits to a string so that we can access individual numbers
    $digits =(string)$digits;
    // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
    $even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];
    // 2. Multiply this result by 3.
    $even_sum_three = $even_sum * 3;
    // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
    $odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];
    // 4. Sum the results of steps 2 and 3.
    $total_sum = $even_sum_three + $odd_sum;
    // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
    $next_ten = (ceil($total_sum/10))*10;
    $check_digit = $next_ten - $total_sum;
    return $digits . $check_digit;
}

function zerofill($text) {
    return str_pad($text,  7, "0", STR_PAD_LEFT);
}

function zerofillAsiento($text) {
    return str_pad($text,  10, "0", STR_PAD_LEFT);
}

function cortar_titulo($titulo, $len) {
	//BORRAMOS LOS TAGS HTML Y DE CONTROL (RETORNO DE CARRO, TABS, ETC)
		$intro  = preg_replace('~[[:cntrl:]]~', '', trim(strip_tags($titulo)));
		$maxPos = $len;
		if (strlen($intro) > $maxPos) {
			$lastPos = ($maxPos - 3) - strlen($intro);
			$intro   = substr($intro, 0, $lastPos) . '...';
		}
	return $intro;
}

function actualiza_puntos_clientes($id_cliente = 0) {
    $db = DataBase::conectar();
    if (intval($id_cliente) > 0) {
        $where = "WHERE c.id_cliente=$id_cliente";
    }

    $db->setQuery("UPDATE clientes c SET c.puntos=(
                        SELECT IFNULL(SUM(puntos-utilizados), 0)
                        FROM clientes_puntos WHERE estado=0 AND id_cliente=c.id_cliente
                    ) $where");
    return $db->alter();
}

function esAdmin($id_rol) {
   return $id_rol == ROL_ADMIN; 
}

function esCajero($id_rol) {
   return $id_rol == 4; 
}

function esEncargado($id_rol) {
	return $id_rol == 9; 
 }

function actualiza_estado_puntos_clientes(){
	$db = DataBase::conectar();
	//Recuperamos el Periodo de canje
   $db->setQuery(" SELECT periodo_canje
   FROM plan_puntos  WHERE tipo = 1");
   $periodo_canje = $db->loadObject()->periodo_canje;
   if (!$db->alter()) {
		echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar el periodo de canje"]);
		$db->rollback();  //Revertimos los cambios
		exit;
	}

   //Actualizamos los puntos de los clientes a vencido si su fecha es mayor al periodo de canje establecida en plan puntos
   $db->setQuery("UPDATE clientes_puntos SET estado = 2, fecha_actualizacion = NOW() WHERE estado = 0 AND TIMESTAMPDIFF(DAY, DATE(fecha), DATE(NOW())) > $periodo_canje AND TIMESTAMPDIFF(DAY, DATE(fecha_actualizacion) , DATE(NOW())) > 0 ");
   if (!$db->alter()) {
	   echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de los puntos de los clientes"]);
	   $db->rollback();  //Revertimos los cambios
	   exit;
   }

   //Actualizamos los puntos de los clientes de vencidos a activos (si cumplen con el periodo de canje establecida en plan puntos)
   $db->setQuery("UPDATE clientes_puntos SET estado = 0, fecha_actualizacion = NOW() WHERE estado = 2 AND TIMESTAMPDIFF(DAY, DATE(fecha), DATE(NOW())) < $periodo_canje AND TIMESTAMPDIFF(DAY,  DATE(fecha_actualizacion), DATE(NOW())) > 0 ");
   if (!$db->alter()) {
	   echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de los puntos de los clientes"]);	
	   $db->rollback();  //Revertimos los cambios
	   exit;
   }

   //Actualiza todos los puntos de los clientes restando puntos con utilizados
   if (!actualiza_puntos_clientes()) {
	   $db->rollback(); // Revertimos los cambios
	   echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar los puntos de los clientes"]);
	   exit;
   }
}

/**
 * Carga una archivo PDF en la ruta especificada por `$target_path` usando la libreria `class.upload`.
 * @param \Verot\Upload\Upload $upload Instancia de la clase `Upload` con la imagen a subir.
 * @param string $file_name El nombre que tendrá el archivo luego de la carga.
 * @param string $target_path Ruta relativa donde almacenar el archivo durante la carga.
 * @return false|string Retorna el nombre del archivo cargado, en caso de error devuelve `false`.
 */
function uploadPDF(\Verot\Upload\Upload $upload, $file_name, $target_path)
{
    if(!$upload->uploaded){
        return false;
    } 

    if (!is_dir($target_path)) {
        mkdir($target_path, 0777, true);
    }

    $upload->allowed            = [ALLOWED_PDF_MIME_TYPE];
    $upload->file_src_name_ext  = 'pdf';
    $upload->file_src_mime      = ALLOWED_PDF_MIME_TYPE;
    $upload->file_new_name_body = $file_name;
    $upload->process($target_path);
        
    if(!$upload->processed){
        return false;  
    }

    $upload->clean();
    return $upload->file_dst_name;
}

/**
 * Carga una imagen en la ruta especificada por `$target_path` usando la libreria `class.upload`.
 * @param \Verot\Upload\Upload $upload Instancia de la clase `Upload` con la imagen a subir.
 * @param string $file_name El nombre que tendrá el archivo luego de la carga.
 * @param string $target_path Ruta relativa donde almacenar el archivo durante la carga.
 * @return false|string Retorna el nombre del archivo cargado, en caso de error devuelve `false`.
 */
function uploadImg(\Verot\Upload\Upload $upload, $file_name, $target_path)
{
	if(!$upload->uploaded){
		return false;
	} 

	if (!is_dir($target_path)) {
		mkdir($target_path, 0777, true);
	}

	$upload->allowed            = ALLOWED_IMG_MIME_TYPE;
	$upload->file_new_name_body = $file_name;
	$upload->image_convert 		= 'jpg';
	$upload->image_ratio_y 		= true;
	$upload->image_resize 		= true;
	$upload->image_x  			= 640;
	$upload->image_y 		 	= 640;

	$upload->process($target_path);

	if(!$upload->processed){
		return false;  
	}

	$upload->clean();
	return $upload->file_dst_name;
}



?>
