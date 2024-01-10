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
                $having = "HAVING banco LIKE '$search%' OR ruc LIKE '$search%' OR estado_str LIKE '$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_banco,
                            banco,
                            ruc,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM bancos
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
            $banco     = mb_convert_case($db->clearText($_POST['banco']), MB_CASE_UPPER, "UTF-8");
            $ruc       = $db->clearText($_POST['ruc']);
            $cuentas   = json_decode($_POST['tabla_cuentas'], true);

            if (empty($banco)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para el Banco"]);
                exit;
            }

            $db->setQuery("INSERT INTO bancos (ruc, banco, estado, usuario, fecha)
                            VALUES ('$ruc','$banco','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            $id_banco = $db->getLastID();

            foreach ($cuentas as $p) {
                $cuenta    = $db->clearText($p["cuenta"]);
                $estado = $db->clearText($p["estado"]);

                $db->setQuery("SELECT * FROM bancos_cuentas cu WHERE cu.cuenta= $cuenta");
                $rows = $db->loadObject();
                if (!empty($rows)) {
                    echo json_encode(["status" => "error", "mensaje" => "El número de cuenta '$cuenta' ya esta registrado a un banco."]);
                    exit;
                }

                $db->setQuery("INSERT INTO bancos_cuentas (id_banco, cuenta, estado)
                                    VALUES ($id_banco, '$cuenta', $estado)");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    exit;
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Banco registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_banco = $db->clearText($_POST['id_banco']);
            $ruc = $db->clearText($_POST['ruc']);
            $banco = mb_convert_case($db->clearText($_POST['banco']), MB_CASE_UPPER, "UTF-8");
            $cuentas   = json_decode($_POST['tabla_cuentas'], true);
            
            if (empty($banco)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para el Banco"]);
                exit;
            }

            $db->setQuery("UPDATE bancos SET ruc='$ruc', banco='$banco' WHERE id_banco='$id_banco'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            $db->setQuery("DELETE FROM bancos_cuentas WHERE id_banco='$id_banco'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar las cuentas. " . $db->getError(), "error" => "proveedores"]);
                $db->rollback();
                exit;
            }

            foreach ($cuentas as $p) {
                $cuenta    = $db->clearText($p["cuenta"]);
                $estado = $db->clearText($p["estado"]);

                $db->setQuery("SELECT * FROM bancos_cuentas cu WHERE cu.cuenta= $cuenta");
                $rows = $db->loadObject();
                if (!empty($rows)) {
                    echo json_encode(["status" => "error", "mensaje" => "El número de cuenta '$cuenta' ya esta registrado a un banco."]);
                    exit;
                }

                $db->setQuery("INSERT INTO bancos_cuentas (id_banco, cuenta, estado)
                                    VALUES ($id_banco, '$cuenta', $estado)");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    exit;
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Banco '$banco' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_banco = $db->clearText($_POST['id_banco']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE bancos SET estado='$estado' WHERE id_banco='$id_banco'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $banco = $db->clearText($_POST['banco']);
            
            $db->setQuery("DELETE FROM bancos WHERE id_banco = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este Banco está asociado a un Funcionario"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Banco '$banco' eliminado correctamente"]);
        break;		

        case 'ver_cuentas':
            $db      = DataBase::conectar();
            $id_banco = $db->clearText($_REQUEST['id_banco']);

            $db->setQuery("SELECT 
                                    id_banco,
                                    id_cuenta,
                                    cuenta,
                                    estado,
                                    CASE estado WHEN 0 THEN 'Activo' WHEN 1 THEN 'Inactivo' END AS estado_str
                                FROM bancos_cuentas
                                WHERE id_banco='$id_banco'
                                ORDER BY cuenta");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;


	}

?>
