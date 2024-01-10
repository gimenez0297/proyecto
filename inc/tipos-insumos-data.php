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
                $having = "HAVING CONCAT_WS(' ', nombre, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_tipo_insumo,
                            nombre,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM tipos_insumos
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
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");

            
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nombre"]);
                exit;
            }
            
            $db->setQuery("INSERT INTO tipos_insumos (nombre, estado, usuario, fecha)
                            VALUES ('$nombre','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tipo de Insumo registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_tipo_insumo = $db->clearText($_POST['id_tipo_insumo']);
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nombre para el tipo de insumo"]);
                exit;
            }

            $db->setQuery("UPDATE tipos_insumos SET nombre='$nombre' WHERE id_tipo_insumo='$id_tipo_insumo'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tipo de Insumo '$nombre' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_tipo_insumo = $db->clearText($_POST['id_tipo_insumo']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE tipos_insumos SET estado=$estado WHERE id_tipo_insumo=$id_tipo_insumo");
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
            
            $db->setQuery("DELETE FROM tipos_insumos WHERE id_tipo_insumo = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este Tipo De Insumo está asociado a un Producto"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tipo de Insumo '$nombre' eliminado correctamente"]);
        break;		

	}

?>