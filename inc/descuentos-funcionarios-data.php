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
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING funcionario LIKE '$search%' OR ci LIKE '$search%' OR monto LIKE '$search%' OR observacion LIKE '$search%' OR estado_str LIKE '$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_anticipo,
                            id_funcionario,
                            funcionario,
                            ci,
                            monto,
                            fecha as fecha_tabla,
                            DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha,
                            observacion,
                            usuario,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' WHEN 2 THEN 'Procesado' END AS estado_str,
                            DATE_FORMAT(fecha_creacion,'%d/%m/%Y') AS fecha_creacion
                            FROM anticipos
                            WHERE fecha BETWEEN '$desde' AND '$hasta'
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

        case 'ver_prestamos':
            $db = DataBase::conectar();
            $where = "";
            $desde2 = $db->clearText($_REQUEST['desde2'])." 00:00:00";
            $hasta2 = $db->clearText($_REQUEST['hasta2'])." 23:59:59";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING funcionario LIKE '$search%' OR ci LIKE '$search%' OR monto LIKE '$search%' OR observacion LIKE '$search%' OR estado_str LIKE '$search%'";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_prestamo,
                            id_funcionario,
                            funcionario,
                            ci,
                            monto,
                            fecha as fecha_tabla,
                            DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha,
                            cantidad_cuota as cuota,
                            observacion,
                            usuario,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' WHEN 2 THEN 'Procesado' END AS estado_str,
                            DATE_FORMAT(fecha_creacion,'%d/%m/%Y') AS fecha_creacion
                            FROM prestamos
                            WHERE fecha BETWEEN '$desde2' AND '$hasta2'
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
		
        case 'ver_otros':
            $db = DataBase::conectar();
            $where = "";
            $desde3 = $db->clearText($_REQUEST['desde3'])." 00:00:00";
            $hasta3 = $db->clearText($_REQUEST['hasta3'])." 23:59:59";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING descuento LIKE '$search%' OR estado_str LIKE '$search%' OR funcionario LIKE '$search%' OR observacion LIKE '$search%' OR ci LIKE '$search%'";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_descuento,
                            descuento,
                            id_funcionario,
                            funcionario,
                            ci,
                            monto,
                            fecha as fecha_tabla,
                            DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha,
                            observacion,
                            usuario,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            DATE_FORMAT(fecha_creacion,'%d/%m/%Y') AS fecha_creacion
                            FROM descuentos_funcionarios
                            WHERE fecha BETWEEN '$desde3' AND '$hasta3'
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
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $obs =  mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");
            $fecha = $db->clearText($_POST['fecha']);

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un monto para continuar"]);
                exit;
            }
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un funcionario"]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un fecha"]);
                exit;
            }

            $db->setQuery("INSERT INTO anticipos (fecha, fecha_creacion, id_funcionario, ci, funcionario, monto, usuario, observacion)
                            VALUES ('$fecha', NOW(), '$id_funcionario','$row->ci','$row->funcionario','$monto','$usuario','$obs')");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Anticipo registrado correctamente"]);
            
        break;

        case 'cargar_prestamo':
            $db = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario_pre']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto_pre']));
            $cuota = $db->clearText($_POST['cuota']);
            $obs =  mb_convert_case($db->clearText($_POST['obs_pre']), MB_CASE_UPPER, "UTF-8");
            $fecha = $db->clearText($_POST['fecha_prestamo']);
            
            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un monto para continuar"]);
                exit;
            }
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un funcionario"]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un fecha"]);
                exit;
            }

            $db->setQuery("INSERT INTO prestamos (fecha, fecha_creacion, id_funcionario, ci, funcionario, monto , cantidad_cuota, usuario, observacion)
                            VALUES ('$fecha', NOW(), '$id_funcionario','$row->ci','$row->funcionario','$monto', '$cuota','$usuario','$obs')");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Préstamo registrado correctamente"]);
            
        break;

        case 'cargar_otro':
            $db = DataBase::conectar();
            $descuento = mb_convert_case($db->clearText($_POST['descuento']), MB_CASE_UPPER, "UTF-8");
            $id_funcionario = $db->clearText($_POST['id_funcionario_otro']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto_otro']));
            $obs =  mb_convert_case($db->clearText($_POST['obs_otro']), MB_CASE_UPPER, "UTF-8");
            $fecha = $db->clearText($_POST['fecha_otro']);
            
            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un monto para continuar"]);
                exit;
            }
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un funcionario"]);
                exit;
            }
            if (empty($descuento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre al descuento"]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un fecha"]);
                exit;
            }

            $db->setQuery("INSERT INTO descuentos_funcionarios (fecha, descuento, id_funcionario, ci, funcionario, monto, estado, usuario, fecha_creacion, observacion)
                            VALUES ('$fecha', '$descuento','$id_funcionario','$row->ci','$row->funcionario','$monto','1','$usuario',NOW(),'$obs' )");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Descuento registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();

            $id_anticipo = $db->clearText($_POST['id_anticipo']);
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $obs =  mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");
            $fecha = $db->clearText($_POST['fecha']);

            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un monto para continuar"]);
                exit;
            }

            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un funcionario"]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un fecha"]);
                exit;
            }

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            $db->setQuery("UPDATE anticipos SET fecha='$fecha', id_funcionario='$id_funcionario', ci='$row->ci', funcionario='$row->funcionario', monto='$monto', observacion='$obs' WHERE id_anticipo = '$id_anticipo'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Anticipo modificado correctamente"]);
        break;

        case 'editar-prestamo':
            $db = DataBase::conectar();

            $id_prestamo = $db->clearText($_POST['id_prestamo']);
            $id_funcionario = $db->clearText($_POST['id_funcionario_pre']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto_pre']));
            $cuota = $db->clearText($_POST['cuota']);
            $obs =  mb_convert_case($db->clearText($_POST['obs_pre']), MB_CASE_UPPER, "UTF-8");
            $fecha = $db->clearText($_POST['fecha_prestamo']);

            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un monto para continuar"]);
                exit;
            }
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un funcionario"]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un fecha"]);
                exit;
            }

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            $db->setQuery("UPDATE prestamos SET fecha='$fecha', id_funcionario='$id_funcionario', ci='$row->ci', funcionario='$row->funcionario', monto='$monto', cantidad_cuota='$cuota', observacion='$obs' WHERE id_prestamo = '$id_prestamo'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Anticipo modificado correctamente"]);
        break;

        case 'editar-otros':
            $db = DataBase::conectar();
            $id_descuento = $db->clearText($_POST['id_descuento']);
            $descuento = mb_convert_case($db->clearText($_POST['descuento']), MB_CASE_UPPER, "UTF-8");
            $id_funcionario = $db->clearText($_POST['id_funcionario_otro']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto_otro']));
            $obs =  mb_convert_case($db->clearText($_POST['obs_otro']), MB_CASE_UPPER, "UTF-8");
            $fecha = $db->clearText($_POST['fecha_otro']);

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();
            
            if (empty($descuento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para el descuento"]);
                exit;
            }
            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un monto para continuar"]);
                exit;
            }
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un funcionario"]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un fecha"]);
                exit;
            }

            $db->setQuery("UPDATE descuentos_funcionarios SET fecha='$fecha', descuento='$descuento', id_funcionario='$id_funcionario', ci='$row->ci', funcionario='$row->funcionario', monto='$monto', observacion='$obs' WHERE id_descuento='$id_descuento'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Descuento '$descuento' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db       = DataBase::conectar();
            $id_anticipo = $db->clearText($_POST['id_anticipo']);
            $estado   = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE anticipos SET estado='$estado' WHERE id_anticipo='$id_anticipo'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;  

        case 'cambiar-estado-prestamo':
            $db       = DataBase::conectar();
            $id_prestamo = $db->clearText($_POST['id_prestamo']);
            $estado   = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE prestamos SET estado='$estado' WHERE id_prestamo='$id_prestamo'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break; 

        case 'cambiar-estado-otro':
            $db       = DataBase::conectar();
            $id_descuento = $db->clearText($_POST['id_descuento']);
            $estado   = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE descuentos_funcionarios SET estado='$estado' WHERE id_descuento='$id_descuento'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break; 

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM anticipos WHERE id_anticipo = '$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Anticipo eliminado correctamente"]);
        break;	

        case 'eliminar_prestamo':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM prestamos WHERE id_prestamo = '$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Préstamo eliminado correctamente"]);
        break;

        case 'eliminar_otros':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM descuentos_funcionarios WHERE id_descuento = '$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Descuento '$nombre' eliminado correctamente"]);
        break;   	

	}

?>
