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
                $having = "HAVING CONCAT_WS(' ', sucursal, monto, monto_minimo,maximo_factura,estado_str) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            cc.`id_caja_chica`,
                            cc.id_sucursal,
                            cc.id_funcionario,
                            cc.`monto`,
                            cc.`monto_minimo`,
                            cc.`maximo_factura`,
                            DATE_FORMAT(cc.`fecha`, '%d/%m/%Y') AS fecha_registro,
                            cc.`usuario`,
                            CASE cc.`estado` WHEN 1 THEN 'Activo' WHEN 0 THEN 'Inactivo' END AS estado_str,
                            cc.estado,
                            s.sucursal,
                            f.funcionario
                            FROM caja_chica cc
                            LEFT JOIN sucursales s ON cc.id_sucursal = s.id_sucursal
                            LEFT JOIN funcionarios f on f.id_funcionario = cc.id_funcionario
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
            $id_sucursal     = $db->clearText($_POST['id_sucursal']);
            $id_funcionario  = $db->clearText($_POST['id_responsable']);
            $monto           = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $minimo          = quitaSeparadorMiles($db->clearText($_POST['minimo']));
            $max_factura     = quitaSeparadorMiles($db->clearText($_POST['max_factura']));

            if (empty($id_sucursal)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una sucursal para la caja chica."]);
                exit;
            }
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un responsable para la caja chica."]);
                exit;
            }
            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto."]);
                exit;
            }
            if ($monto <= 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Monto no puede ser menor o igual a 0."]);
                exit;
            }
            if (empty($minimo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto Mínimo."]);
                exit;
            }
            if ($minimo <= 0) {
                echo json_encode(["status" => "error", "mensaje" => "El MontoMínimo no puede ser menor o igual a 0."]);
                exit;
            }
            if (empty($max_factura)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto Máximo De Factura."]);
                exit;
            }
            if ($max_factura <= 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Monto Máximo De Factura no puede ser menor o igual a 0."]);
                exit;
            }

            $db->setQuery("SELECT * FROM caja_chica WHERE id_sucursal = $id_sucursal");
            $row = $db->loadObject();
            if ($row) {
                echo json_encode(["status" => "error", "mensaje" => "Ya hay una caja chica configurada para esta sucursal."]);
                exit;
            }

            $db->setQuery("INSERT INTO `caja_chica` (`id_sucursal`,`id_funcionario`,`monto`,`monto_minimo`,`maximo_factura`,`fecha`,`usuario`)
                        VALUES($id_sucursal,$id_funcionario,$monto,$minimo,$max_factura,NOW(),'$usuario');");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al cargar la caja chica."]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Caja Chica registrado correctamente"]); 

        break;

        case 'editar':
            $db              = DataBase::conectar();
            $id              = $db->clearText($_POST['id']);
            $id_sucursal     = $db->clearText($_POST['id_sucursal']);
            $id_funcionario  = $db->clearText($_POST['id_responsable']);
            $monto           = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $minimo          = quitaSeparadorMiles($db->clearText($_POST['minimo']));
            $max_factura     = quitaSeparadorMiles($db->clearText($_POST['max_factura']));

            if (empty($id_sucursal)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una sucursal para la caja chica."]);
                exit;
            }
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un responsable para la caja chica."]);
                exit;
            }
            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto."]);
                exit;
            }
            if ($monto <= 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Monto no puede ser menor o igual a 0."]);
                exit;
            }
            if (empty($minimo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto Mínimo."]);
                exit;
            }
            if ($minimo <= 0) {
                echo json_encode(["status" => "error", "mensaje" => "El MontoMínimo no puede ser menor o igual a 0."]);
                exit;
            }
            if (empty($max_factura)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto Máximo De Factura."]);
                exit;
            }
            if ($max_factura <= 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Monto Máximo De Factura no puede ser menor o igual a 0."]);
                exit;
            }

            $db->setQuery("SELECT * FROM caja_chica WHERE id_sucursal = $id_sucursal AND id_caja_chica != $id");
            $row = $db->loadObject();
            if ($row) {
                echo json_encode(["status" => "error", "mensaje" => "Ya hay una caja chica configurada para esta sucursal."]);
                exit;
            }

            $db->setQuery("UPDATE
                                `caja_chica`
                            SET
                                `id_sucursal` = $id_sucursal,
                                `id_funcionario` = $id_funcionario,
                                `monto` = $monto,
                                `monto_minimo` = $minimo,
                                `maximo_factura` = $max_factura
                            WHERE `id_caja_chica` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al editar la caja chica."]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Caja Chica modificado correctamente"]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $sucursal = $db->clearText($_POST['sucursal']);

            $db->setQuery("DELETE FROM `caja_chica` WHERE `id_caja_chica` = $id;");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "La caja chica de '$sucursal' no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al eliminar la caja chica"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Caja chica de '$sucursal' eliminado correctamente"]);
        break;	

        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE `caja_chica` SET `estado` = $estado WHERE `id_caja_chica` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de la caja chica."]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    }