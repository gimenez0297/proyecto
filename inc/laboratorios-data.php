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
                $having = "HAVING CONCAT_WS(' ', laboratorio, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_laboratorio,
                            laboratorio,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM laboratorios
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
            $laboratorio = mb_convert_case($db->clearText($_POST['laboratorio']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($laboratorio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para el Laboratorio"]);
                exit;
            }

            $db->setQuery("INSERT INTO laboratorios (laboratorio, estado, usuario, fecha)
                            VALUES ('$laboratorio','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Laboratorio registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_laboratorio = $db->clearText($_POST['id_laboratorio']);
            $laboratorio = mb_convert_case($db->clearText($_POST['laboratorio']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($laboratorio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para el Laboratorio"]);
                exit;
            }

            $db->setQuery("UPDATE laboratorios SET laboratorio='$laboratorio' WHERE id_laboratorio='$id_laboratorio'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Laboratorio '$laboratorio' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_laboratorio = $db->clearText($_POST['id_laboratorio']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE laboratorios SET estado='$estado' WHERE id_laboratorio='$id_laboratorio'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $laboratorio = $db->clearText($_POST['laboratorio']);
            
            $db->setQuery("DELETE FROM laboratorios WHERE id_laboratorio = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este Laboratorio está asociado a un Producto"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Laboratorio '$laboratorio' eliminado correctamente"]);
        break;		

	}

?>
