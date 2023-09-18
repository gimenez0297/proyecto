<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;

    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();
            $where = "";

            // Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', nombre_empresa, sucursal, tipo, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            s.id_sucursal,
                            s.nombre_empresa,
                            s.ruc,
                            s.razon_social,
                            s.sucursal,
                            s.direccion,
                            d.id_distrito,
                            d.nombre AS distrito,
                            s.telefono,
                            s.email,
                            s.deposito,
                            CASE s.deposito WHEN 0 THEN 'No' WHEN 1 THEN 'Si' END AS deposito_str,
                            s.estado,
                            CASE s.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            s.usuario,
                            DATE_FORMAT(s.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM sucursales s
                            LEFT JOIN distritos d ON s.id_distrito=d.id_distrito
                            $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
            $rows = $db->loadObjectList();

            $db->setQuery("SELECT FOUND_ROWS() as total");		
            $total_row = $db->loadObject();
            $total = $total_row->total;

            if ($rows) {
                $salida = array('total' => $total, 'rows' => $rows);
            } else {
                $salida = array('total' => 0, 'rows' => array());
            }

            echo json_encode($salida);
        break;
		
        case 'cargar':
            $db = DataBase::conectar();
            $ruc = $db->clearText($_POST['ruc']);
            $nombre_empresa = mb_convert_case($db->clearText($_POST['nombre_empresa']), MB_CASE_UPPER, "UTF-8");
            $razon_social = mb_convert_case($db->clearText($_POST['razon_social']), MB_CASE_UPPER, "UTF-8");
            $sucursal = mb_convert_case($db->clearText($_POST['sucursal']), MB_CASE_UPPER, "UTF-8");
            $direccion = $db->clearText($_POST['direccion']);
            $id_distrito = ($db->clearText($_POST['distrito'])) ?: "NULL";
            $telefono = $db->clearText($_POST['telefono']);
            $email = $db->clearText($_POST['email']);
            $deposito = ($db->clearText($_POST['deposito'])) ?: 0;
            
            if (empty($sucursal)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el nombre de la sucursal"]);
                exit;
            }

            $db->setQuery("INSERT INTO sucursales (ruc, nombre_empresa, razon_social, sucursal, direccion, id_distrito, telefono, email, deposito, estado, usuario, fecha)
                            VALUES ('$ruc','$nombre_empresa','$razon_social','$sucursal','$direccion',$id_distrito,'$telefono','$email',$deposito,'1','$usuario',NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Sucursal registrada correctamente"]);
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['hidden_id']);
            $ruc = $db->clearText($_POST['ruc']);
            $nombre_empresa = mb_convert_case($db->clearText($_POST['nombre_empresa']), MB_CASE_UPPER, "UTF-8");
            $razon_social = mb_convert_case($db->clearText($_POST['razon_social']), MB_CASE_UPPER, "UTF-8");
            $sucursal = mb_convert_case($db->clearText($_POST['sucursal']), MB_CASE_UPPER, "UTF-8");
            $direccion = $db->clearText($_POST['direccion']);
            $id_distrito = ($db->clearText($_POST['distrito'])) ?: "NULL";
            $telefono = $db->clearText($_POST['telefono']);
            $email = $db->clearText($_POST['email']);
            $deposito = ($db->clearText($_POST['deposito'])) ?: 0;
            
            if (empty($sucursal)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el nombre de la sucursal"]);
                exit;
            }

            $db->setQuery("UPDATE sucursales SET 
                            ruc='$ruc', 
                            nombre_empresa='$nombre_empresa', 
                            razon_social='$razon_social', 
                            sucursal='$sucursal', 
                            direccion='$direccion', 
                            id_distrito=$id_distrito, 
                            telefono='$telefono', 
                            email='$email', 
                            deposito=$deposito 
                            WHERE id_sucursal=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Sucursal '$sucursal' modificada correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE sucursales SET estado = '$estado' WHERE id_sucursal = '$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);

            if ($id == 1) {
                echo json_encode(["status" => "error", "mensaje" => "La sucursal \"$nombre\" no puede eliminada"]);
                exit;
            }
            
            $db->setQuery("DELETE FROM sucursales WHERE id_sucursal = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Esta sucursal no puede ser eliminada"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Sucursal '$nombre' eliminada correctamente"]);
        break;		

	}

?>
