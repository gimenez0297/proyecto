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
                $having = "HAVING CONCAT_WS(' ', metodo_pago, orden, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_metodo_pago,
                            metodo_pago,
                            orden,
                            estado,
                            entidad,
                            sigla,
                            CASE entidad WHEN 1 THEN 'Si' WHEN 0 THEN 'No' END AS entidad_str,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM metodos_pagos
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
		

        case "cargar":
            $db = DataBase::conectar();
            $metodo_pago = $db->clearText($_POST["metodo_pago"]);
            $orden = $db->clearText($_POST["orden"]);
            $entidad = ($db->clearText($_POST['entidad'])) ?: 0;
            $sigla = mb_convert_case($db->clearText($_POST['sigla']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($metodo_pago)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Método De Pago"]);
                exit;
            }

            $db->setQuery("INSERT INTO metodos_pagos (metodo_pago, orden, entidad, sigla, estado, usuario, fecha)
                            VALUES ('$metodo_pago', $orden,$entidad,'$sigla',1,'$usuario',NOW())");

            if (!$db->alter()) {
                if ($db->getErrorCode() == 1062) {
                    echo json_encode(["status" => "error", "mensaje" => "El orden \"$orden\" ya existe"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Método de pago registrado correctamente"]);
            
        break;

        case "editar":
            $db = DataBase::conectar();
            $id = $db->clearText($_POST["id"]);
            $metodo_pago = $db->clearText($_POST["metodo_pago"]);
            $orden = $db->clearText($_POST["orden"]);
            $entidad = ($db->clearText($_POST['entidad'])) ?: 0;
            $sigla = mb_convert_case($db->clearText($_POST['sigla']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($metodo_pago)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Método De Pago"]);
                exit;
            }

            $db->setQuery("UPDATE metodos_pagos SET metodo_pago='$metodo_pago', orden=$orden, entidad = $entidad, sigla = '$sigla' WHERE id_metodo_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Método de pago modificado correctamente"]);
        break;

        case "cambiar-estado":
            $db = DataBase::conectar();
            $id = $db->clearText($_POST["id"]);
            $estado = $db->clearText($_POST["estado"]);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE metodos_pagos SET estado=$estado WHERE id_metodo_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case "eliminar":
            $db = DataBase::conectar();
            $id = $db->clearText($_POST["id"]);
            $nombre = $db->clearText($_POST["nombre"]);

            if ($id == 1 || $id  == 2 || $id  == 3 || $id  == 7 || $id  == 9) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede eliminar este metodo de pago"]);
                exit;
            }
            
            $db->setQuery("DELETE FROM metodos_pagos WHERE id_metodo_pago=$id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El método de pago no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Método de pago \"$nombre\" eliminado correctamente"]);
        break;		

	}

?>
