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

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', ruc, entidad, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_entidad,
                            ruc,
                            entidad,
                            tipo,   
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM entidades
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
            $entidad = mb_convert_case($db->clearText($_POST['entidad']), MB_CASE_UPPER, "UTF-8");
            $tipo = mb_convert_case($db->clearText($_POST['tipo']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo R.U.C."]);
                exit;
            }
            if (empty($entidad)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Entidad"]);
                exit;
            }
            if (empty($tipo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo"]);
                exit;
            }

            $db->setQuery("INSERT INTO entidades (ruc, entidad, tipo, estado, usuario, fecha)
                            VALUES ('$ruc','$entidad','$tipo',1,'$usuario',NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Entidad registrada correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $ruc = $db->clearText($_POST['ruc']);
            $entidad = mb_convert_case($db->clearText($_POST['entidad']), MB_CASE_UPPER, "UTF-8");
            $tipo = mb_convert_case($db->clearText($_POST['tipo']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo R.U.C."]);
                exit;
            }
            if (empty($entidad)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Entidad"]);
                exit;
            }
            if (empty($tipo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo"]);
                exit;
            }

            $db->setQuery("UPDATE entidades SET ruc='$ruc', entidad='$entidad', tipo='$tipo' WHERE id_entidad=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Entidad modificada correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE entidades SET estado=$estado WHERE id_entidad=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM entidades WHERE id_entidad=$id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "La entidad '$nombre' no puede ser eliminada"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Entidad '$nombre' eliminada correctamente"]);
        break;		

	}

?>
