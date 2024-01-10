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
                $having = "HAVING CONCAT_WS(' ', unidad_medida, sigla, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_unidad_medida,
                            unidad_medida,
                            sigla,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM unidades_medidas
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
            $unidad_medida = mb_convert_case($db->clearText($_POST['unidad_medida']), MB_CASE_UPPER, "UTF-8");
            $sigla = mb_convert_case($db->clearText($_POST['sigla']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($unidad_medida)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para la unidad de medida"]);
                exit;
            }
            if (empty($sigla)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una sigla para la unidad de medida"]);
                exit;
            }

            $db->setQuery("INSERT INTO unidades_medidas (unidad_medida, sigla, estado, usuario, fecha)
                            VALUES ('$unidad_medida','$sigla','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Unidad De Medida registrada correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_unidad_medida = $db->clearText($_POST['id_unidad_medida']);
            $unidad_medida = mb_convert_case($db->clearText($_POST['unidad_medida']), MB_CASE_UPPER, "UTF-8");
            $sigla = mb_convert_case($db->clearText($_POST['sigla']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($unidad_medida)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para la unidad de medida"]);
                exit;
            }
            if (empty($sigla)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una sigla para la unidad de medida"]);
                exit;
            }

            $db->setQuery("UPDATE unidades_medidas SET unidad_medida='$unidad_medida', sigla='$sigla' WHERE id_unidad_medida='$id_unidad_medida'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Unidad De Medida '$unidad_medida' modificada correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_unidad_medida = $db->clearText($_POST['id_unidad_medida']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE unidades_medidas SET estado='$estado' WHERE id_unidad_medida='$id_unidad_medida'");
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
            
            $db->setQuery("DELETE FROM unidades_medidas WHERE id_unidad_medida = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Esta Unidad De Medida está asociada a un Producto"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Unidad De Medida '$nombre' eliminada correctamente"]);
        break;		

	}

?>
