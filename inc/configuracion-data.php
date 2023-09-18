<?php
	include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;

	switch ($q){

		case 'editar':

			$db = DataBase::conectar();

			$nombre_sistema 	   	  = $db->clearText($_POST['nombre_sistema']);
			//$subtitulo_sistema 		 = $db->clearText($_POST['subtitulo_sistema']);
			$numero_patronal 		  = $db->clearText($_POST['numero_patronal']);
			$periodo_devolucion 	  = $db->clearText($_POST['periodo_devolucion']);
			$utilidad 	              = $db->clearText(quitaSeparadorMiles($_POST['utilidad']));
			$limite 	              = $db->clearText(quitaSeparadorMiles($_POST['limite']));
			$limite_caja 	          = $db->clearText(quitaSeparadorMiles($_POST['limite_caja']));
			$limite_egresos 	      = $db->clearText(quitaSeparadorMiles($_POST['limite_egreso']));
			$alerta_nro_timbrado 	  = $db->clearText(quitaSeparadorMiles($_POST['alerta_nro_timbrado']));

			if (empty($nombre_sistema)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre de sistema"]);
                exit;
            }

			if (empty($numero_patronal)) {
				echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un número patronal"]);
                exit;
			}

			if (empty($alerta_nro_timbrado) && $alerta_nro_timbrado != 0) {
				echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Mínimo Timbrado"]);
                exit;
			}

			$db->setQuery("UPDATE configuracion SET 
										nombre_sistema='$nombre_sistema', 
										numero_patronal='$numero_patronal', 
										periodo_devolucion='$periodo_devolucion',   
										utilidad='$utilidad',
										limite_producto='$limite',
										limite_caja='$limite_caja',
										limite_egreso='$limite_egresos',
										alerta_nro_timbrado='$alerta_nro_timbrado'
										WHERE id_configuracion=1");


			if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Configuración modificada correctamente"]);	

		break;

	}
?>
