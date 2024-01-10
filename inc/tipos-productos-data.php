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
                $having = "HAVING CONCAT_WS(' ', tipo, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_tipo_producto,
                            tipo,
                            principios_activos,
                            CASE principios_activos WHEN 0 THEN 'No' WHEN 1 THEN 'Si' END AS principios_activos_str,
                            lote_automatico,
                            CASE lote_automatico WHEN 0 THEN 'No' WHEN 1 THEN 'Si' END AS lote_automatico_str,
                            lote_prefijo,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM tipos_productos
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
            $tipo = mb_convert_case($db->clearText($_POST['tipo']), MB_CASE_UPPER, "UTF-8");
            $principios_activos = ($db->clearText($_POST['principios_activos'])) ?: 0;
            $lote_automatico = ($db->clearText($_POST['lote_automatico'])) ?: 0;
            $lote_prefijo = mb_convert_case($db->clearText($_POST['lote_prefijo']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($tipo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el tipo de producto"]);
                exit;
            }
            if ($lote_automatico == 1 && empty($lote_prefijo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el prefijo con el que debe iniciar el lote"]);
                exit;
            }

            $db->setQuery("INSERT INTO tipos_productos (tipo, principios_activos, lote_automatico, lote_prefijo, estado, usuario, fecha)
                            VALUES ('$tipo','$principios_activos',$lote_automatico,'$lote_prefijo','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tipo de producto registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_tipo_producto = $db->clearText($_POST['id_tipo_producto']);
            $tipo = mb_convert_case($db->clearText($_POST['tipo']), MB_CASE_UPPER, "UTF-8");
            $principios_activos = ($db->clearText($_POST['principios_activos'])) ?: 0;
            $lote_automatico = ($db->clearText($_POST['lote_automatico'])) ?: 0;
            $lote_prefijo = mb_convert_case($db->clearText($_POST['lote_prefijo']), MB_CASE_UPPER, "UTF-8");
            
            if (empty($tipo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el tipo de producto"]);
                exit;
            }
            if ($lote_automatico == 1 && empty($lote_prefijo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el prefijo con el que debe iniciar el lote"]);
                exit;
            }

            $db->setQuery("UPDATE tipos_productos SET tipo='$tipo', principios_activos='$principios_activos', lote_automatico=$lote_automatico, lote_prefijo='$lote_prefijo' WHERE id_tipo_producto='$id_tipo_producto'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            // Se eliminan todo los principios activos del los productos de este tipo
            if ($principios_activos == 0) {
                $db->setQuery("DELETE FROM productos_principios WHERE id_producto IN (SELECT id_producto FROM productos WHERE id_tipo_producto='$id_tipo_producto')");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tipo de producto '$tipo' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_tipo_producto = $db->clearText($_POST['id_tipo_producto']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE tipos_productos SET estado='$estado' WHERE id_tipo_producto='$id_tipo_producto'");
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
            
            $db->setQuery("DELETE FROM tipos_productos WHERE id_tipo_producto = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este Tipo De Producto está asociado a un Producto"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tipo de producto '$nombre' eliminado correctamente"]);
        break;		

	}

?>
