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
                $having = "HAVING proveedor LIKE '$search%' OR nombre_fantasia LIKE '$search%' OR ruc LIKE '$search%' OR contacto LIKE '$search%' OR telefono LIKE '$search%' OR email LIKE '$search%' OR obs LIKE '%$search%'";
            }
            $tipo_proveedor = $db->clearText($_REQUEST['tipo_proveedor']);

            if (!empty($_REQUEST['tipo_proveedor']) && intVal($tipo_proveedor) != 0) {
                $where_tipo .= " AND pt.tipo_proveedor=$tipo_proveedor";
             }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            p.id_proveedor, 
                            proveedor, 
                            nombre_fantasia, 
                            ruc, 
                            contacto, 
                            direccion, 
                            telefono, 
                            email, 
                            obs, 
                            usuario, 
                            GROUP_CONCAT(CASE pt.tipo_proveedor WHEN 1 THEN 'PRODUCTOS' WHEN 2 THEN 'GASTOS' END separator ', ') AS tipo,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM proveedores p
                            left join proveedores_tipos pt on p.id_proveedor=pt.id_proveedor
                            WHERE 1=1 $where_tipo
                            GROUP BY p.id_proveedor
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
            $proveedor =  mb_convert_case($db->clearText($_POST['proveedor']), MB_CASE_UPPER, "UTF-8");
            $nombre_fantasia = $db->clearText($_POST['nombre_fantasia']);
            $telefono = $db->clearText($_POST['telefono']);
            $direccion = $db->clearText($_POST['direccion']);
            $email = $db->clearText($_POST['email']);
            $contacto = $db->clearText($_POST['contacto']);
            $obs =  mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");
            $tipo_proveedor = $_POST['tipo_proveedor'];

            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese RUC / CI del Proveedor"]);
                exit;
            }

            if (empty($proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese nombre del Proveedor"]);
                exit;
            }
            if (empty($tipo_proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de Proveedor"]);
                exit;
            }

            $db->setQuery("SELECT id_proveedor FROM proveedores WHERE ruc='$ruc'");
            $row = $db->loadObject();
            if (isset($row)) {
                echo json_encode(["status" => "error", "mensaje" => "El RUC / CI ingresado ya esta registrado como Proveedor"]);
                exit;
            }

            $db->setQuery("INSERT INTO proveedores (proveedor, ruc, nombre_fantasia, contacto, direccion, telefono, email, obs, usuario, fecha) VALUES ('$proveedor','$ruc', '$nombre_fantasia', '$contacto','$direccion','$telefono','$email','$obs', '$usuario', NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            $ultimo_id = $db->getLastID();

            foreach ($tipo_proveedor as $tipo) {
                $db->setQuery("INSERT INTO proveedores_tipos (tipo_proveedor, id_proveedor) VALUES ($tipo, $ultimo_id)");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }
            }
                
            echo json_encode(["status" => "ok", "mensaje" => "Proveedor registrado correctamente"]);
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_proveedor = $db->clearText($_POST['id_proveedor']);
            $ruc = $db->clearText($_POST['ruc']);
            $proveedor =  mb_convert_case($db->clearText($_POST['proveedor']), MB_CASE_UPPER, "UTF-8");
            $nombre_fantasia = $db->clearText($_POST['nombre_fantasia']);
            $telefono = $db->clearText($_POST['telefono']);
            $direccion = $db->clearText($_POST['direccion']);
            $email = $db->clearText($_POST['email']);
            $contacto = $db->clearText($_POST['contacto']);
            $obs =  mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");
            $tipo_proveedor = $_POST['tipo_proveedor'];


            $db->setQuery("SELECT id_proveedor FROM proveedores WHERE ruc='$ruc' AND id_proveedor!=$id_proveedor");
            $row = $db->loadObject();
            if (isset($row)) {
                echo json_encode(["status" => "error", "mensaje" => "El RUC / CI ingresado ya esta registrado como Proveedor"]);
                exit;
            }

            $db->setQuery("UPDATE proveedores SET ruc='$ruc', proveedor='$proveedor', nombre_fantasia='$nombre_fantasia', tipo_proveedor='$tipo_proveedor', telefono='$telefono', direccion='$direccion',email='$email', contacto='$contacto' WHERE id_proveedor='$id_proveedor'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            $db->setQuery("DELETE FROM proveedores_tipos WHERE id_proveedor='$id_proveedor'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar los proveedores. " . $db->getError(), "error" => "proveedores"]);
                $db->rollback();
                exit;
            }

            foreach ($tipo_proveedor as $tipo) {
                $db->setQuery("INSERT INTO proveedores_tipos (tipo_proveedor, id_proveedor) VALUES ($tipo, $id_proveedor)");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Proveedor '$proveedor' modificado correctamente"]);
		break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);

            $db->setQuery("DELETE FROM proveedores_tipos WHERE id_proveedor='$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar los proveedores. " . $db->getError(), "error" => "proveedores"]);
                $db->rollback();
                exit;
            }
            
			$db->setQuery("DELETE FROM proveedores WHERE id_proveedor = $id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El registro se encuentra asociado a otros módulos del sistema"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Proveedor '$nombre' eliminado correctamente"]);
            
        break;	

        case 'ver_tipos_proveedor':
            $db = DataBase::conectar();
            $id_proveedor= $db->clearText($_POST['id_proveedor']);

            $db->setQuery("SELECT tipo_proveedor FROM proveedores_tipos WHERE id_proveedor='$id_proveedor'");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;	

	}

?>
