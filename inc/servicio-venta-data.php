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
                $having = "HAVING CONCAT_WS(' ', producto, precio, estado_str,usuario) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    id_producto,
                                    codigo,
                                    producto,
                                    precio,
                                    usuario,
                                    DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                                    CASE iva WHEN 1 THEN 'EXENTAS' WHEN 2 THEN '5%'  WHEN 3 THEN '10%'  END AS iva_str,
                                    CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                                    estado,
                                    iva
                                    FROM
                                    productos
                                    WHERE tipo = 2
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
            $db       = DataBase::conectar();
            $codigo   = mb_convert_case($db->clearText($_POST['codigo']), MB_CASE_UPPER, "UTF-8");
            $servicio = mb_convert_case($db->clearText($_POST['servicio']), MB_CASE_UPPER, "UTF-8");
            $precio   = quitaSeparadorMiles($db->clearText($_POST['precio']));
            $iva      = $db->clearText($_POST['iva']);

            if (empty($codigo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Codigo."]);
                exit;
            }
            if (empty($servicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Servicio."]);
                exit;
            }
            if (empty($precio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Precio."]);
                exit;
            }
            if ($precio < 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Precio no puede ser menor a 0."]);
                exit;
            }
            if (empty($iva)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de iva."]);
                exit;
            }

            //Validamos que no exista un producto o servicio con el mismo codigo
            $db->setQuery("SELECT * FROM productos WHERE codigo = '$codigo'");
            $row  = $db->loadObject();
            $nombre = $row->producto;

            if(!empty($row)){
                echo json_encode(["status" => "error", "mensaje" => "El codigo coincide con el codigo del producto/servicio $nombre."]);
                exit;
            }

            $db->setQuery("INSERT INTO `productos` (`codigo`,`producto`,`precio`,`estado`,`usuario`,`fecha`,`iva`,`tipo`)
            VALUES('$codigo','$servicio',$precio,1,'$usuario',NOW(),$iva,2);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Servicio registrado correctamente"]);
        break;

        case 'editar':
            $db       = DataBase::conectar();
            $id       = $db->clearText($_POST['id']);
            $codigo   = mb_convert_case($db->clearText($_POST['codigo']), MB_CASE_UPPER, "UTF-8");
            $servicio = mb_convert_case($db->clearText($_POST['servicio']), MB_CASE_UPPER, "UTF-8");
            $precio   = quitaSeparadorMiles($db->clearText($_POST['precio']));
            $iva      = $db->clearText($_POST['iva']);

            if (empty($codigo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Codigo."]);
                exit;
            }
            if (empty($servicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Servicio."]);
                exit;
            }
            if (empty($precio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Precio."]);
                exit;
            }
            if ($precio < 0) {
                echo json_encode(["status" => "error", "mensaje" => "El Precio no puede ser menor a 0."]);
                exit;
            }
            if (empty($iva)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de iva."]);
                exit;
            }

            //Validamos que no exista un producto o servicio con el mismo codigo
            $db->setQuery("SELECT * FROM productos WHERE codigo = '$codigo' AND id_producto != $id");
            $row  = $db->loadObject();
            $nombre = $row->producto;

            if(!empty($row)){
                echo json_encode(["status" => "error", "mensaje" => "El codigo coincide con el codigo del producto/servicio $nombre."]);
                exit;
            }

            $db->setQuery("UPDATE`productos` SET
                                `codigo` = '$codigo',
                                `producto` = '$servicio',
                                `precio` = $precio,
                                `iva` = $iva
                            WHERE `id_producto` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Servicio modificado correctamente."]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);

            $db->setQuery("DELETE FROM `productos` WHERE `id_producto` = $id;");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El servicio no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Servicio eliminado correctamente"]);
        break;	

        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE productos SET estado=$estado WHERE id_producto=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
    }