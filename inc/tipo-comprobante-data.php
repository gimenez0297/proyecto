<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q           = $_REQUEST['q'];
    $usuario     = $auth->getUsername();
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
                $having = "HAVING CONCAT_WS(' ', nombre_comprobante, estado_str) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    id_tipo_comprobante,
                                    codigo,
                                    nombre_comprobante,
                                    estado,
                                    CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str
                                    FROM tipos_comprobantes
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
            $db     = DataBase::conectar();
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            $codigo = $db->clearText($_REQUEST['codigo']);
            
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre"]);
                exit;
            }

            if (empty($codigo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Codigo"]);
                exit;
            }

            $db->setQuery("INSERT INTO tipos_comprobantes (codigo, nombre_comprobante)
                            VALUES ($codigo,'$nombre');");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Comprobante registrado correctamente"]);

        break;


        case 'editar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            $codigo = $db->clearText($_REQUEST['codigo']);

            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre"]);
                exit;
            }

            if (empty($codigo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Codigo"]);
                exit;
            }

            $db->setQuery(" UPDATE
                                tipos_comprobantes
                                SET
                                codigo = $codigo,
                                nombre_comprobante = '$nombre'
                                WHERE id_tipo_comprobante = $id;
                    ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Comprobante modificada correctamente"]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM tipos_comprobantes WHERE id_tipo_comprobante=$id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El comprobante '$nombre' no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "El comprobante '$nombre' eliminado correctamente"]);
        break;		
        
        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE tipos_comprobantes SET estado=$estado WHERE id_tipo_comprobante=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
            
    }