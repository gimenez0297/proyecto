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
                $having = "HAVING CONCAT_WS(' ', id_motivo_asiento, descripcion, estado_str, usuario) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    id_motivo_asiento, 
                                    descripcion,
                                    usuario,
                                    DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                                    estado,
                                    CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str
                                    FROM motivos_asiento
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
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");

            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo descripcion"]);
                exit;
            }

            $db->setQuery("INSERT INTO motivos_asiento (descripcion, usuario, fecha, estado)
                           VALUES('$descripcion','$usuario', NOW(), '1' );");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Motivo registrado correctamente"]);

        break;

        case 'editar':
            $db              = DataBase::conectar();
            $id               = $db->clearText($_POST['id']);
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");

            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo descripcion"]);
                exit;
            }
            $db->setQuery(" UPDATE 
                                            motivos_asiento
                                        SET
                                            descripcion = '$descripcion'
                                        WHERE id_motivo_asiento = $id;
                    
                    ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Motivo modificado correctamente"]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);

            $db->setQuery("DELETE FROM motivos_asiento WHERE id_motivo_asiento=$id");
            if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    exit;
                }
            
            echo json_encode(["status" => "ok", "mensaje" => "Motivo eliminado correctamente"]);
        break;		
        
        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE motivos_asiento SET estado=$estado WHERE id_motivo_asiento=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
            
    }
