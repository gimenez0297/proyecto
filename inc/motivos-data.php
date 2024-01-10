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
                $having = "HAVING CONCAT_WS(' ', descripcion, nombre_corto) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                id_nota_remision_motivo,
                                descripcion,
                                nombre_corto,
                                estado,
                                CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                                usuario,
                                DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha
                                FROM notas_remision_motivos
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
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $nombre_corto = mb_convert_case($db->clearText($_POST['nombre_corto']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo descripcion"]);
                exit;
            }

            if (empty($nombre_corto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo nombre"]);
                exit;
            }

            $db->setQuery("INSERT INTO notas_remision_motivos (descripcion, nombre_corto,estado, usuario, fecha)
                            VALUES ('$descripcion','$nombre_corto', '1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Motivo registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_motivo = $db->clearText($_POST['id_motivo']);
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $nombre_corto = mb_convert_case($db->clearText($_POST['nombre_corto']), MB_CASE_UPPER, "UTF-8");
            
            
            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo descripcion"]);
                exit;
            }
            if (empty($nombre_corto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo nombre"]);
                exit;
            }

            $db->setQuery("UPDATE notas_remision_motivos SET descripcion='$descripcion', nombre_corto='$nombre_corto' WHERE id_nota_remision_motivo=$id_motivo");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Motivo modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_motivo = $db->clearText($_POST['id_motivo']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE notas_remision_motivos SET estado='$estado' WHERE id_nota_remision_motivo='$id_motivo'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $motivo = $db->clearText($_POST['motivo']);

            if ($id == 1) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede eliminar este motivo"]);
                exit;
            }
            
            $db->setQuery("DELETE FROM notas_remision_motivos WHERE id_nota_remision_motivo = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El registro se encuentra asociado a otros módulos del sistema"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }
            echo json_encode(["status" => "ok", "mensaje" => "Motivo '$motivo' eliminado correctamente"]);
        break;		

	}

?>
