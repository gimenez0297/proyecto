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
                $having = "HAVING CONCAT_WS(' ', banco, tipo_movimiento,nro_comprobante,concepto,observacion,usuario,estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            mb.`id_movimiento_bancario`,
                            b.banco,
                            DATE_FORMAT(mb.`fecha_comprobante`, '%d/%m/%Y') AS fecha_comprobante,
                            CASE mb.tipo_movimiento WHEN 1 THEN 'CRÉDITO' WHEN 2 THEN 'DÉBITO' END AS tipo_movimiento_str,
                            mb.tipo_movimiento,
                            b.id_banco,
                            mb.nro_comprobante,
                            mb.importe,
                            mb.concepto,
                            mb.observacion,
                            DATE_FORMAT(mb.fecha_creacion, '%d/%m/%Y') AS fecha_creacion,
                            mb.usuario,
                            CASE mb.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            mb.estado
                            FROM
                            movimientos_bancarios mb
                            LEFT JOIN bancos b ON b.id_banco = mb.id_banco
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
            $db              = DataBase::conectar();
            $nro_comprobante = mb_convert_case($db->clearText($_POST['nro_comprobante']), MB_CASE_UPPER, "UTF-8");
            $concepto        = mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
            $observacion     = mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $importe         = quitaSeparadorMiles($db->clearText($_POST['importe']));
            $tipo_movimiento = $db->clearText($_POST['tipo_movimiento']);
            $fecha           = $db->clearText($_POST['fecha']);
            $id_banco        = $db->clearText($_POST['id_banco']);

            if (empty($nro_comprobante)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nro. Comprobante."]);
                exit;
            }
            if (empty($concepto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Concepto."]);
                exit;
            }
            if (empty($importe)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Importe."]);
                exit;
            }
            if ($importe < 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Importe no puede ser menor a 0."]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor coloque una Fecha."]);
                exit;
            }
            if (empty($id_banco)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un Banco."]);
                exit;
            }

            $db->setQuery("INSERT INTO `movimientos_bancarios` (`id_banco`,`fecha_comprobante`,`tipo_movimiento`,`nro_comprobante`,`importe`,`concepto`,`observacion`,`fecha_creacion`,`usuario`)
            VALUES($id_banco,'$fecha',$tipo_movimiento,'$nro_comprobante',$importe,'$concepto','$observacion',NOW(),'$usuario');");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Movimiento Bancario registrado correctamente"]);
        break;

        case 'editar':
            $db              = DataBase::conectar();
            $id              = $db->clearText($_POST['id']);
            $nro_comprobante = mb_convert_case($db->clearText($_POST['nro_comprobante']), MB_CASE_UPPER, "UTF-8");
            $concepto        = mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
            $observacion     = mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $importe         = quitaSeparadorMiles($db->clearText($_POST['importe']));
            $tipo_movimiento = $db->clearText($_POST['tipo_movimiento']);
            $fecha           = $db->clearText($_POST['fecha']);
            $id_banco        = $db->clearText($_POST['id_banco']);

            if (empty($nro_comprobante)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nro. Comprobante."]);
                exit;
            }
            if (empty($concepto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Concepto."]);
                exit;
            }
            if (empty($importe)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Importe."]);
                exit;
            }
            if ($importe < 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Importe no puede ser menor a 0."]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor coloque una Fecha."]);
                exit;
            }
            if (empty($id_banco)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un Banco."]);
                exit;
            }

            $db->setQuery("UPDATE`movimientos_bancarios`
                            SET
                                `id_banco` = $id_banco,
                                `fecha_comprobante` = '$fecha',
                                `tipo_movimiento` = $tipo_movimiento,
                                `nro_comprobante` = '$nro_comprobante',
                                `importe` = $importe,
                                `concepto` = '$concepto',
                                `observacion` = '$observacion'
                            WHERE `id_movimiento_bancario` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Movimiento Bancario modificado correctamente."]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);

            $db->setQuery("DELETE FROM `movimientos_bancarios` WHERE `id_movimiento_bancario` = $id;");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El Movimiento Bancario no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Movimiento Bancario eliminado correctamente"]);
        break;	
	}