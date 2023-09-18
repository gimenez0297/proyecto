<?php
	include ("funciones.php");
	if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}
	$q = $_REQUEST['q'];
	$usuario_carga = $auth->getUsername();
	$id_sucursal = datosUsuario($usuario)->id_sucursal;
	switch ($q){
		
		case 'ver':
			$db = DataBase::conectar();
						
			if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])){
				$search = $_REQUEST['search'];
				$where = "AND CONCAT_WS(' ', c.razon_social, c.ruc, c.direccion, c.email, c.obs, s.sucursal) LIKE '%$search%'";
			}
			
			$db->setQuery("SELECT u.id,u.email,u.password,u.username,u.nombre_apellido,u.departamento,u.usuario_carga,u.cargo,u.ci,u.telefono,u.direccion,u.id_sucursal,s.sucursal, u.foto,u.status,u.roles_mask,u.registered,u.last_login,u.force_logout,u.id_rol,r.rol, u.notificaciones, CASE u.notificaciones WHEN 0 THEN 'No' WHEN 1 THEN 'Si' END AS notificaciones_str FROM users u LEFT JOIN sucursales s ON s.id_sucursal=u.id_sucursal LEFT JOIN roles r ON r.id_rol=u.id_rol ORDER BY u.id_sucursal, username");
			$rows = $db->loadObjectList();
			foreach ($rows as $r){
				$id = $r->id;
				//$estado = $r->status;
				switch ($r->status){
					case 0: $estado = "Activo";	break;
					case 1:	$estado = "Archivado"; break;
					case 2:	$estado = "Baneado"; break;
					case 3:	$estado = "Bloqueado"; break;
					case 4: $estado = "Pendiente"; break;
					case 5: $estado = "Suspendido"; break;
				}

				//$roles = implode(" / ",$auth->admin()->getRolesForUserById($id));
				
				$registered = new DateTime();
				$registered->setTimestamp($r->registered);
				$fecha_registro = $registered->format('d/m/Y H:i:s');
				
				$last_login = new DateTime();
				$last_login->setTimestamp($r->last_login);
				$ultimo_acceso = $last_login->format('d/m/Y H:i:s');
				

				$salida[] = [
					'id' => $id,
					'ci' => $r->ci,
					'email' => $r->email,
					'username' => $r->username,
					'nombre_apellido' => $r->nombre_apellido,
					'departamento' => $r->departamento,
					'cargo' => $r->cargo,
					'telefono' => $r->telefono,
					'direccion' => $r->direccion,
					'id_sucursal' => $r->id_sucursal,
					'sucursal' => $r->sucursal,
					'foto' => $r->foto,
					'estado' => $estado,
					'status' => $r->status,
					'rol' => $r->rol,
					'fecha_registro' => $fecha_registro,
					'ultimo_acceso' => $ultimo_acceso,
					'id_rol' => $r->id_rol,
					'notificaciones' => $r->notificaciones,
					'notificaciones_str' => $r->notificaciones_str,
					'usuario_carga'=>$r->usuario_carga,
				];
			}
			
			echo json_encode($salida);
		
		break;
		
		case 'ver_roles':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_rol, rol FROM roles WHERE estado='Activo'");
			$rows = $db->loadObjectList();
			if (!$rows) $rows = [];
			echo json_encode($rows);
		break;
		
		case 'ver_roles_usuario':
			$id = $_REQUEST['id'];
			$roles = $auth->admin()->getRolesForUserById($id);
			foreach($roles as $key => $value){
				//$salida[] = $key;
				$salida[] = $value;
			}
			//echo json_encode($salida, JSON_NUMERIC_CHECK);
			echo json_encode($salida);
		break;
	
		//BUSCAR CI EN SET
		case 'buscar_ci':
			$db = DataBase::conectar();
			$ci = $db->clearText($_POST['ci']);
			
			//Si tiene guion, entonces sacamos
			if (stripos($ci, "-") !== false) {
				$ruc_sin_dv_tmp = explode("-",$ci);
				$ci = $ruc_sin_dv_tmp[0];	
			}
			$url = "https://servicios.set.gov.py/eset-publico/ciudadano/recuperar?cedula=$ci";
			$ch = curl_init();
			// Disable SSL verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// Will return the response, if false it print the response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// Set the url
			curl_setopt($ch, CURLOPT_URL,$url);
			// Execute
			$json=curl_exec($ch);
			// Closing
			curl_close($ch);

			$arr = json_decode($json);

			if ($arr->presente=="true"){
				$cedula = $arr->resultado->cedula;
				$nombreCompleto_tmp = $arr->resultado->nombreCompleto;
				
				$nombre = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $nombreCompleto_tmp)));
			}else{
				$cedula="";
				$nombre="";
			}
			
			$salida = ["ci"=>$cedula, "nombre"=>$nombre];
			echo json_encode($salida);
		break;

		case 'cargar':
			$db = DataBase::conectar();
			$ci = $db->clearText($_POST['ci']);
			$nombre = $db->clearText($_POST['nombre']);
			$telefono = $db->clearText($_POST['telefono']);
			$direccion = $db->clearText($_POST['direccion']);
			$email = $db->clearText($_POST['email']);
			$dpto = $db->clearText($_POST['dpto']);
			$cargo = $db->clearText($_POST['cargo']);
			$sucursal = $db->clearText($_POST['sucursal']);
			$id_rol = $db->clearText($_POST['rol']);
			$usuario = $db->clearText(trim(strtolower($_POST['usuario'])));
			$password = $db->clearText($_POST['password']);
			$expira = $_POST['expira'];
			$notificaciones = $_POST['ver_notificaciones'] ? 1 : 0;
			
			$expira=="on" ? $status = 4 : $status = 0;

			if (empty($nombre)){
				echo "Error. Favor ingrese nombre y apellido del usuario";
				exit;
			}
			if (empty($id_rol)){
				echo "Error. Favor ingrese rol. El rol es el conjunto de permisos que posee el usuario en el sistema.";
				exit;
			}
			if (empty($usuario)){
				echo "Error. Favor ingrese nombre de usuario. Este dato se requiere para iniciar sesión en el sistema.";
				exit;
			}
			if (empty($password)){
				echo "Error. Favor escriba una contraseña temporal. Se requiere para el primer inicio de sesión en el sistema.";
				exit;
			}
			if (empty($email)){
				$email = $usuario."@localhost.com";
			}
			
			try {
				$userId = $auth->admin()->createUserWithUniqueUsername($email, $password, $usuario);

				$db->setQuery("UPDATE users SET nombre_apellido='$nombre', departamento='$dpto', cargo='$cargo', ci='$ci', telefono='$telefono', direccion='$direccion', id_sucursal='$sucursal', foto='dist/images/users/nobody.png', status=$status, id_rol='$id_rol', notificaciones=$notificaciones, usuario_carga='$usuario_carga' WHERE id=$userId");

				if(!$db->alter()){
					echo "Error. ".$db->getError();
				}else{
					echo "Usuario registrado correctamente";
				}
			}
			catch (\Delight\Auth\InvalidEmailException $e) {
				die('Error. Dirección de correo no válido.');
			}
			catch (\Delight\Auth\InvalidPasswordException $e) {
				die('Error. Contraseña no válida.');
			}
			catch (\Delight\Auth\DuplicateUsernameException $e) {
				die("Error. Nombre de usuario ya existe. Favor verifique.");
			}
			
		break;
					
		case 'editar':
		
			$db = DataBase::conectar();
			$id_usuario = $_POST['hidden_id_usuario'];

			$ci = $db->clearText($_POST['ci']);
			$nombre = $db->clearText($_POST['nombre']);
			$telefono = $db->clearText($_POST['telefono']);
			$direccion = $db->clearText($_POST['direccion']);
			$email = $db->clearText($_POST['email']);
			$dpto = $db->clearText($_POST['dpto']);
			$cargo = $db->clearText($_POST['cargo']);
			$sucursal = $db->clearText($_POST['sucursal']);
			$id_rol = $db->clearText($_POST['rol']);
			$estado = $db->clearText($_POST['estado']);
			$notificaciones = $_POST['ver_notificaciones'] ? 1 : 0;

			$db->setQuery("UPDATE users SET ci='$ci', nombre_apellido='$nombre', id_sucursal='$sucursal', telefono='$telefono', direccion='$direccion', email='$email', departamento='$dpto', cargo='$cargo', status='$estado', id_rol=$id_rol, notificaciones=$notificaciones WHERE id = '$id_usuario'");
			
			if(!$db->alter()){
				echo "Error. ". $db->getError();
			}else{
				echo "Usuario '$nombre' modificado correctamente";
			}

		break;
		
		case 'restablecer_pass':
			$success = false;
			$id = $_POST['id'];
			$nombre = $_POST['nombre'];
			
			$db = DataBase::conectar();

			$auth->admin()->changePasswordForUserByUsername($nombre, $nombre);
			$db->setQuery("UPDATE users SET status=4 WHERE id = $id");

			if($db->alter()){
				echo "Usuario '$nombre' restablecido correctamente";
			}else{
				echo "Error al eliminar '$nombre'. ". $db->getError();
			}
			
		break;		

	}


?>
