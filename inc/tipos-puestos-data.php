<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST["q"];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;

    switch ($q) {
        
        case "ver":
            $db = DataBase::conectar();
            $where = "";

            // Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', tipo_puesto) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_tipo_puesto,
                            tipo_puesto
                            FROM tipos_puestos
                            $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
            $rows = $db->loadObjectList();

            $db->setQuery("SELECT FOUND_ROWS() as total");		
            $total_row = $db->loadObject();
            $total = $total_row->total;

            if ($rows) {
                $salida = array("total" => $total, "rows" => $rows);
            } else {
                $salida = array("total" => 0, "rows" => array());
            }

            echo json_encode($salida);
        break;

        case "editar":
            $db = DataBase::conectar();
            $id = $db->clearText($_POST["id"]);
            $tipo_puesto =  mb_convert_case($db->clearText($_POST['tipo_puesto']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($tipo_puesto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo"]);
                exit;
            }

            $db->setQuery("UPDATE tipos_puestos SET tipo_puesto='$tipo_puesto' WHERE id_tipo_puesto=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tipo De Puesto modificado correctamente"]);
        break;

	}

?>
