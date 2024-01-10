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
                $having = "HAVING CONCAT_WS(' ', presentacion, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_presentacion,
                            presentacion,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM presentaciones
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
            $presentacion = mb_convert_case($db->clearText($_POST['presentacion']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($presentacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para la presentación"]);
                exit;
            }

            preg_match_all('/^[a-zA-Z0-9]+/', $presentacion, $matches, PREG_SET_ORDER, 0);

            if (strlen($presentacion) != strlen($matches[0][0])) {
                echo json_encode(["status" => "error", "mensaje" => "La presentación solo puede contener letras y números"]);
                exit;
            }

            $db->setQuery("INSERT INTO presentaciones (presentacion, estado, usuario, fecha)
                            VALUES ('$presentacion','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Presentacion registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_presentacion = $db->clearText($_POST['id_presentacion']);
            $presentacion = mb_convert_case($db->clearText($_POST['presentacion']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($presentacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para la presentación"]);
                exit;
            }

            preg_match_all('/^[a-zA-Z0-9]+/', $presentacion, $matches, PREG_SET_ORDER, 0);

            if (strlen($presentacion) != strlen($matches[0][0])) {
                echo json_encode(["status" => "error", "mensaje" => "La presentación solo puede contener letras y números"]);
                exit;
            }

            $db->setQuery("UPDATE presentaciones SET presentacion='$presentacion' WHERE id_presentacion='$id_presentacion'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Presentacion '$presentacion' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_presentacion = $db->clearText($_POST['id_presentacion']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE presentaciones SET estado='$estado' WHERE id_presentacion='$id_presentacion'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $presentacion = $db->clearText($_POST['presentacion']);
            
            $db->setQuery("DELETE FROM presentaciones WHERE id_presentacion = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Esta Presentación está asociado a un Producto"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Presentacion '$presentacion' eliminado correctamente"]);
        break;		

	}

?>
