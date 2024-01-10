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
                $having = "HAVING CONCAT_WS(' ', clasificacion, grupo, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            c.id_clasificacion_producto,
                            c.clasificacion,
                            g.id_grupo,
                            g.grupo,
                            c.estado,
                            CASE c.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            c.usuario,
                            DATE_FORMAT(c.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM clasificaciones_productos c
                            LEFT JOIN grupos g ON c.id_grupo=g.id_grupo
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
            $clasificacion = mb_convert_case($db->clearText($_POST['clasificacion']), MB_CASE_UPPER, "UTF-8");
            $id_grupo = $db->clearText($_POST['grupo']);
            
            if (empty($clasificacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para la clasificación de productos"]);
                exit;
            }
            if (empty($id_grupo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un grupo"]);
                exit;
            }

            $db->setQuery("INSERT INTO clasificaciones_productos (clasificacion, id_grupo, estado, usuario, fecha)
                            VALUES ('$clasificacion','$id_grupo','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Clasificación de producto registrada correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_clasificacion_producto = $db->clearText($_POST['id_clasificacion_producto']);
            $clasificacion = mb_convert_case($db->clearText($_POST['clasificacion']), MB_CASE_UPPER, "UTF-8");
            $id_grupo = $db->clearText($_POST['grupo']);
            
            if (empty($clasificacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para la clasificación de productos"]);
                exit;
            }
            if (empty($id_grupo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un grupo"]);
                exit;
            }

            $db->setQuery("UPDATE clasificaciones_productos SET clasificacion='$clasificacion', id_grupo='$id_grupo' WHERE id_clasificacion_producto='$id_clasificacion_producto'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Clasificación de producto '$clasificacion' modificada correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_clasificacion_producto = $db->clearText($_POST['id_clasificacion_producto']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE clasificaciones_productos SET estado='$estado' WHERE id_clasificacion_producto='$id_clasificacion_producto'");
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
            
            $db->setQuery("DELETE FROM clasificaciones_productos WHERE id_clasificacion_producto = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Esta Clasificación está asociada a un Producto"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Clasificación de producto '$nombre' eliminada correctamente"]);
        break;		

	}

?>
