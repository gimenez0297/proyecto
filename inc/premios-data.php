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

        //Ver codigo
        case 'ver_codigo':
            $db = DataBase::conectar();
            $db->setQuery("SELECT
                            
                            IFNULL( MAX(codigo),0) +1 as codigo

                            FROM premios
                        ");
            echo json_encode($db->loadObject());
            // echo json_encode($salida);
        break;



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
                $having = "HAVING CONCAT_WS(' ', premio, codigo ) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_premio,
                            premio,
                            codigo,
                            descripcion,
                            costo,
                            puntos,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM premios
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
            $premio             = mb_convert_case($db->clearText($_POST['premio']), MB_CASE_UPPER, "UTF-8");
            $codigo             = $db->clearText($_POST['codigo']);
            $descripcion      = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $costo                = $db->clearText(quitaSeparadorMiles($_POST['costo']));
            $puntos             = $db->clearText(quitaSeparadorMiles($_POST['puntos']));
            
            if (empty($premio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nombre para el Premio"]);
                exit;
            }

            if ($costo < 0) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un costo para el Premio"]);
                exit;
            }

            if (empty($puntos)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un punto para el Premio"]);
                exit;
            
            }

            $db->setQuery("INSERT INTO premios (premio, codigo, descripcion, costo, puntos, fecha, usuario , estado)
                            VALUES ('$premio','$codigo','$descripcion','$costo', '$puntos', NOW(),'$usuario', '1')");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Producto de Insumo Registrado Correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_premio              = $db->clearText($_POST['id_premio']);
            $premio                   = mb_convert_case($db->clearText($_POST['premio']), MB_CASE_UPPER, "UTF-8");
            $descripcion            = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $costo                      = $db->clearText(quitaSeparadorMiles($_POST['costo']));
            $puntos                    = $db->clearText(quitaSeparadorMiles($_POST['puntos']));
            $codigo                    = $db->clearText($_POST['codigo']);
           
            if (empty($premio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nombre para el Premio"]);
                exit;
            }

            if (empty($costo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un costo para el Premio"]);
                exit;
            }

            if (empty($puntos)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un punto para el Premio"]);
                exit;
            
            }

            $db->setQuery("UPDATE premios SET premio='$premio', 
                                                                          costo='$costo', 
                                                                          puntos='$puntos', 
                                                                          codigo='$codigo', 
                                                                          descripcion='$descripcion'  
                                                                          WHERE id_premio=$id_premio ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Premio modificado correctamente '$premio'"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_premio = $db->clearText($_POST['id_premio']);
            $estado = $db->clearText($_POST['estado']);


            $db->setQuery("UPDATE premios SET estado='$estado' WHERE id_premio='$id_premio'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id_premio = $db->clearText($_POST['id_premio']);
            $premio = $db->clearText($_POST['premio']);
            
            $db->setQuery("DELETE FROM premios WHERE id_premio = '$id_premio'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El registro se encuentra asociado a otros módulos del sistema"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Premio eliminado correctamente '$premio'"]);
        break;		

	}

?>
