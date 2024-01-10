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
                $having = "HAVING razon_social LIKE '$search%' OR ruc LIKE '$search%' OR email LIKE '$search%' OR obs LIKE '$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_cliente,
                            razon_social,
                            ruc,
                            telefono,
                            celular,
                            email,
                            id_tipo,
                            tipo,
                            obs,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            puntos
                            FROM clientes
                            WHERE 1=1 
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
            $ruc = $db->clearText($_POST['ruc']);
            $razon_social =  mb_convert_case($db->clearText($_POST['razon_social']), MB_CASE_UPPER, "UTF-8");
            $telefono = $db->clearText($_POST['telefono']);
            $celular = $db->clearText($_POST['celular']);
            $email = $db->clearText($_POST['email']);
            $id_tipo = $db->clearText($_POST['id_tipo']);
            $obs =  mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");
            $tipo = $db->clearText($_POST['id_tipo']);
            $direcciones = json_decode($_POST['tabla_direcciones']);
            
            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo RUC / CI"]);
                exit;
            }
            if (empty($razon_social)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese nombre y apellido del cliente o Razón Social"]);
                exit;
            }

            $db->setQuery("SELECT id_cliente
                            FROM clientes
                            WHERE SUBSTRING_INDEX(ruc, '-', 1) = SUBSTRING_INDEX('$ruc', '-', 1)");
            $row = $db->loadObject();
            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al verificar el R.U.C. / C.I. del cliente"]);
                exit;
            }
            if (isset($row)) {
                echo json_encode(["status" => "error", "mensaje" => "El R.U.C. \"$ruc\" ya existe"]);
                exit;
            }

            $db->setQuery("SELECT * FROM clientes_tipos WHERE id_cliente_tipo = $id_tipo");
            $row = $db->loadObject();

            $db->setQuery("INSERT INTO clientes (razon_social, ruc, telefono, celular, email, id_tipo, tipo, obs, usuario, fecha)
                            VALUES ('$razon_social','$ruc','$telefono','$celular','$email',$id_tipo,'$row->tipo','$obs','$usuario',NOW())");
        
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1062) {
                    echo json_encode(["status" => "error", "mensaje" => "El R.U.C. \"$ruc\" ya existe"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                }
                exit;
            }else {

                $ultimo_id = $db->getLastID();

                foreach ($direcciones as $d) {
                    $latitud = $db->clearText($d->latitud);
                    $longitud = $db->clearText($d->longitud);
                    $direccion = $db->clearText($d->direccion);
                    $referencia = $db->clearText($d->referencia);
                    $latitud = $db->clearText($d->latitud);

                    $db->setQuery("INSERT INTO clientes_direcciones (id_cliente, direccion, longitud, latitud, referencia, fecha) VALUES($ultimo_id, '$direccion', '$longitud', '$latitud', '$referencia', NOW())");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al guardar la dirreccion. " . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Cliente registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_cliente = $db->clearText($_POST['id_cliente']);
            $ruc = $db->clearText($_POST['ruc']);
            $razon_social =  mb_convert_case($db->clearText($_POST['razon_social']), MB_CASE_UPPER, "UTF-8");
            $telefono = $db->clearText($_POST['telefono']);
            $celular = $db->clearText($_POST['celular']);
            $email = $db->clearText($_POST['email']);
            $id_tipo = $db->clearText($_POST['id_tipo']);
            $obs =  mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");
            $direcciones = json_decode($_POST['tabla_direcciones']);


            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo RUC / CI"]);
                exit;
            }
            if (empty($razon_social)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese nombre y apellido del cliente o Razón Social"]);
                exit;
            }

            $db->setQuery("SELECT id_cliente
                            FROM clientes
                            WHERE SUBSTRING_INDEX(ruc, '-', 1) = SUBSTRING_INDEX('$ruc', '-', 1) AND id_cliente!=$id_cliente");
            $row = $db->loadObject();
             if (!$db->alter()) {
                if ($db->getErrorCode() == 1062) {
                    echo json_encode(["status" => "error", "mensaje" => "El R.U.C. \"$ruc\" ya existe"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                }
                exit;
            }else {
                $db->setQuery("DELETE FROM `clientes_direcciones` WHERE `id_cliente` = $id_cliente;");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    exit;
                }

                foreach ($direcciones as $d) {
                    $latitud = $db->clearText($d->latitud);
                    $longitud = $db->clearText($d->longitud);
                    $direccion = $db->clearText($d->direccion);
                    $referencia = $db->clearText($d->referencia);
                    $latitud = $db->clearText($d->latitud);

                    $db->setQuery("INSERT INTO clientes_direcciones (id_cliente, direccion, longitud, latitud, referencia, fecha) VALUES($id_cliente, '$direccion', '$longitud', '$latitud', '$referencia', NOW())");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al guardar la dirreccion. " . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }
            }

            $db->setQuery("SELECT * FROM clientes_tipos WHERE id_cliente_tipo = $id_tipo");
            $row = $db->loadObject();

            $db->setQuery("UPDATE clientes SET ruc='$ruc', razon_social='$razon_social', telefono='$telefono', celular='$celular',email='$email',id_tipo='$id_tipo',tipo='$row->tipo', obs='$obs' WHERE id_cliente = '$id_cliente'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Cliente '$razon_social' modificado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM `clientes_direcciones` WHERE `id_cliente` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }
            
            $db->setQuery("DELETE FROM clientes WHERE id_cliente = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El cliente no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos."]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Cliente '$nombre' eliminado correctamente"]);
        break;	

    case 'obtener-direcciones':
        $db = DataBase::conectar();
        $id_cliente = $db->clearText($_REQUEST['id_cliente']);

        $db->setQuery("SELECT
                            id_cliente_direccion,
                            id_cliente,
                            direccion,
                            longitud,
                            latitud,
                            CONCAT(latitud,',',longitud) as coordenadas,
                            referencia
                        FROM clientes_direcciones
                        WHERE id_cliente = $id_cliente
                        ORDER BY id_cliente_direccion DESC
                       ");

        $rows = ($db->loadObjectList()) ?: [];

        echo json_encode($rows);
    break;	

	}

?>
