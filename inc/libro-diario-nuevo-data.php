<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();

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
                $having = "HAVING CONCAT_WS(' ', nombre, estado_str) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                        id_libro_diario_periodo,
                                        nombre,
                                        DATE_FORMAT(desde, '%d/%m/%Y') AS desde,
                                        DATE_FORMAT(hasta, '%d/%m/%Y') AS hasta,
                                        usuario,
                                        DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha,
                                        estado,
                                        CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str 
                                    FROM
                                        libro_diario_periodo
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
            $desde = $db->clearText($_POST['desde']);
            $hasta = $db->clearText($_POST['hasta']);

            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre"]);
                exit;
            }
            if (empty($desde)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Desde"]);
                exit;
            }
            if (empty($hasta)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Hasta"]);
                exit;
            }

            //Cambiamos el estado de libro activo para el que nuevo sea el activo actualmente. 
            $db->setQuery("UPDATE `libro_diario_periodo` SET `estado` = 0 WHERE estado = 1");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datosa al cambiar el estado del libro."]);
                exit;
            }


            $db->setQuery("SELECT
                                DATE_FORMAT(desde, '%d/%m/%Y') AS desde,
                                DATE_FORMAT(hasta, '%d/%m/%Y') AS hasta,
                                nombre
                            FROM
                                libro_diario_periodo
                            WHERE desde BETWEEN '$desde' AND '$hasta'
                            OR  hasta BETWEEN '$desde' AND '$hasta'
                            OR '$desde' BETWEEN desde AND hasta 
                            OR '$hasta' BETWEEN desde AND hasta");
            $row_f = $db->loadObject();

            $desde_c = $row_f->desde;
            $hasta_c = $row_f->hasta;
            $libro   = $row_f->nombre; 

            if (!empty($desde_c) || !empty($hasta_c)) {
                echo json_encode(["status" => "error", "mensaje" => "El intervalo de Fechas cargadas se repiten con el libro $libro"]);
                exit;
            }

            $db->setQuery("INSERT INTO `libro_diario_periodo` (
                                    `nombre`,
                                    `desde`,
                                    `hasta`,
                                    `usuario`,
                                    `fecha`)
                                VALUES
                                    (
                                    '$nombre',
                                    '$desde',
                                    '$hasta',
                                    '$usuario',
                                    NOW());");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Libro diario registrado correctamente"]);
        break;

        case 'editar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            $desde  = $db->clearText($_POST['desde']);
            $hasta  = $db->clearText($_POST['hasta']);

            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre"]);
                exit;
            }
            if (empty($desde)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Desde"]);
                exit;
            }
            if (empty($hasta)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Hasta"]);
                exit;
            }

            $db->setQuery("SELECT
                            DATE_FORMAT(desde, '%d/%m/%Y') AS desde,
                            DATE_FORMAT(hasta, '%d/%m/%Y') AS hasta,
                            nombre
                        FROM
                            libro_diario_periodo
                        WHERE id_libro_diario_periodo != $id AND
                        (desde BETWEEN '$desde' AND '$hasta'
                        OR  hasta BETWEEN '$desde' AND '$hasta'
                        OR '$desde' BETWEEN desde AND hasta 
                        OR '$hasta' BETWEEN desde AND hasta);");
            $row_f = $db->loadObject();

            $desde_c = $row_f->desde;
            $hasta_c = $row_f->hasta;
            $libro   = $row_f->nombre; 

            if (!empty($desde_c) || !empty($hasta_c)) {
                echo json_encode(["status" => "error", "mensaje" => "El intervalo de Fechas cargadas se repiten con el libro $libro"]);
                exit;
            }

            $db->setQuery("UPDATE
                                `libro_diario_periodo`
                            SET
                                `nombre` = '$nombre',
                                `desde` = '$desde',
                                `hasta` = '$hasta'
                            WHERE `id_libro_diario_periodo` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Libro Diario modificado correctamente"]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            
            $db->setQuery("DELETE FROM `libro_diario_periodo` WHERE `id_libro_diario_periodo` = $id;");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El libro no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Libro eliminado correctamente"]);
        break;		

        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            //Cambiamos el estado de libro activo. 
            $db->setQuery("UPDATE `libro_diario_periodo` SET `estado` = 0 WHERE estado = 1");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al cambiar el estado del libro."]);
                exit;
            }

            $db->setQuery("UPDATE `libro_diario_periodo` SET `estado` = $estado WHERE `id_libro_diario_periodo` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
    }