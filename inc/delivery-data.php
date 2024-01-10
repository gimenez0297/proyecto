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
                $having = "HAVING CONCAT_WS(' ', nombre, precio, estado_str) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    d.`id_delivery`,
                                    dt.`nombre` AS ciudad,
                                    dt.id_distrito,
                                    d.`precio`,
                                    DATE_FORMAT(d.`fecha_carga`, '%d/%m/%Y %h:%i') AS fecha,
                                    CASE d.`estado` WHEN 1 THEN 'Activo' WHEN 0 THEN 'Inactivo' END AS estado_str,
                                    d.estado,
                                    d.usuario
                                    FROM
                                    delivery d
                                    LEFT JOIN distritos dt ON dt.`id_distrito` = d.`id_distrito`
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
            $id_distrito = $db->clearText($_POST['id_distrito']);
            $precio = quitaSeparadorMiles($db->clearText($_POST['precio']));

            if (empty($id_distrito) || !isset($id_distrito) || intval($id_distrito) == 0) {
                echo json_encode(["status" => "error", "mensaje" => "Favor Seleccione una Ciudad."]);
                exit;
            }
            if (empty($precio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Precio"]);
                exit;
            }
            if ($precio < 1) {
                echo json_encode(["status" => "error", "mensaje" => "El precio debe ser mayor a 0."]);
                exit;
            }

            //Verificamos que no haya otro delivery para esa ciudad activo
            $db->setQuery("SELECT
                                d.*,
                                dt.`nombre`
                            FROM
                                delivery d
                            LEFT JOIN distritos dt ON dt.`id_distrito` = d.`id_distrito`
                            WHERE d.id_distrito = $id_distrito AND d.estado = 1");
            $row = $db->loadObject();

            $ciudad = $row->nombre;
            
            if (!empty($row)) {
                echo json_encode(["status" => "error", "mensaje" => "La ciudad $ciudad ya cuenta con una tarifa cargada."]);
                exit;
            }

            $db->setQuery("INSERT INTO `delivery` (`id_distrito`,`precio`,`fecha_carga`,`usuario`)
              VALUES($id_distrito,$precio,NOW(),'$usuario');");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al cargar la Tarifa"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tarifa registrado correctamente"]);
        break;

        case 'editar':
            $db              = DataBase::conectar();
            $id               = $db->clearText($_POST['id']);
            $id_distrito = $db->clearText($_POST['id_distrito']);
            $precio = quitaSeparadorMiles($db->clearText($_POST['precio']));

            if (empty($id_distrito) || !isset($id_distrito) || intval($id_distrito) == 0) {
                echo json_encode(["status" => "error", "mensaje" => "Favor Seleccione una Ciudad."]);
                exit;
            }
            if (empty($precio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Precio"]);
                exit;
            }
            if ($precio < 1) {
                echo json_encode(["status" => "error", "mensaje" => "El precio debe ser mayor a 0."]);
                exit;
            }

            //Verificamos que no haya otro delivery para esa ciudad activo
            $db->setQuery("SELECT
                                d.*,
                                dt.`nombre`
                            FROM
                                delivery d
                            LEFT JOIN distritos dt ON dt.`id_distrito` = d.`id_distrito`
                            WHERE d.id_distrito = $id_distrito AND d.estado = 1 AND id_delivery != $id");
            $row = $db->loadObject();

            $ciudad = $row->nombre;
            
            if (!empty($row)) {
                echo json_encode(["status" => "error", "mensaje" => "La ciudad $ciudad ya cuenta con una tarifa cargada."]);
                exit;
            }

            $db->setQuery("UPDATE
                                `delivery`
                            SET
                                `id_distrito` = $id_distrito,
                                `precio` = $precio
                            WHERE `id_delivery` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al cargar la Tarifa"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tarifa modificado correctamente"]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
    
            $db->setQuery("DELETE FROM `delivery` WHERE `id_delivery` = $id;");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "La tarifa no puede ser eliminada"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Tarifa eliminada correctamente"]);
        break;		

        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE `delivery` SET `estado` = $estado WHERE `id_delivery` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
    }