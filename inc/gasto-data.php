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
            $db    = DataBase::conectar();
            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit  = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order  = $db->clearText($_REQUEST['order']);
            $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', nombre, tipo_gasto) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    tg.id_tipo_gasto, 
                                    tg.id_sub_tipo_gasto,
                                    tg.nombre, 
                                    sg.nombre AS tipo_gasto, 
                                    tg.estado,
                                    CASE tg.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str
                                    FROM tipos_gastos tg
                                    LEFT JOIN sub_tipos_gastos sg ON sg.id_sub_tipo_gasto = tg.id_sub_tipo_gasto
                                    $having
                                    ORDER BY $sort $order
                                    LIMIT $offset, $limit");
             $rows = $db->loadObjectList();

             $db->setQuery("SELECT FOUND_ROWS() as total");		
             $total_row = $db->loadObject();
             $total     = $total_row->total;
 
             if ($rows) {
                 $salida = array('total' => $total, 'rows' => $rows);
             } else {
                 $salida = array('total' => 0, 'rows' => array());
             }
 
             echo json_encode($salida);
        break;


        case 'cargar':
            $db              = DataBase::conectar();
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            $id_tipo_gasto = $db->clearText($_POST['id_tipo_gasto']) ? : 'NULL';
            
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre"]);
                exit;
            }
            


            $db->setQuery("SELECT * FROM tipos_gastos WHERE id_sub_tipo_gasto = $id_tipo_gasto");
            $rows = $db->loadObject();
            if ($rows) {
                echo json_encode(["status" => "error", "mensaje" => "No puede existir 2 gastos tipo RECEPCION"]);
                exit;
            }


            $db->setQuery("INSERT INTO tipos_gastos (id_sub_tipo_gasto,nombre)
                            VALUES($id_tipo_gasto,'$nombre');");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Gasto registrado correctamente"]);

        break;


        case 'editar':
            $db              = DataBase::conectar();
            $id              = $db->clearText($_POST['id']);
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            $id_tipo_gasto = $db->clearText($_POST['id_tipo_gasto'])? : 'NULL';

            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre"]);
                exit;
            }
            
            $db->setQuery("SELECT * FROM tipos_gastos WHERE id_sub_tipo_gasto = $id_tipo_gasto AND id_tipo_gasto <> $id");
            $rows = $db->loadObject();
            if ($rows) {
                echo json_encode(["status" => "error", "mensaje" => "No puede existir 2 gastos tipo RECEPCION"]);
                exit;
            }

            $db->setQuery(" UPDATE
                                tipos_gastos
                            SET
                                id_sub_tipo_gasto = $id_tipo_gasto,
                                nombre = '$nombre'
                            WHERE id_tipo_gasto = $id;
                    ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Gasto modificado correctamente"]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM tipos_gastos WHERE id_tipo_gasto=$id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El Gasto '$nombre' no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Gasto '$nombre' eliminado correctamente"]);
        break;		
        
        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE tipos_gastos SET estado=$estado WHERE id_tipo_gasto=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
            
    }
