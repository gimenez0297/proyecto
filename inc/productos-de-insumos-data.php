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

                            FROM productos_insumo
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
                $having = "HAVING CONCAT_WS(' ', producto , codigo ) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_producto_insumo,
                            ti.id_tipo_insumo,
                            producto,
                            costo,
                            codigo,
                            ti.nombre,
                            p.estado,
                            CASE p.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            p.usuario,
                            DATE_FORMAT(p.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM productos_insumo p
                            LEFT JOIN tipos_insumos ti ON p.id_tipo_insumo=ti.id_tipo_insumo
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
            $producto = mb_convert_case($db->clearText($_POST['producto']), MB_CASE_UPPER, "UTF-8");
            $costo = $db->clearText(quitaSeparadorMiles($_POST['costo']));
            $codigo = $db->clearText($_POST['codigo']);
            $id_tipo_insumo = $db->clearText($_POST['tipo']);
            
            if (empty($producto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nombre para el Producto de Insumo"]);
                exit;
            }

            if (empty($costo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un costo para el Producto de Insumo"]);
                exit;
            }

            if (empty($id_tipo_insumo)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo"]); 
                exit;
            }

            $db->setQuery("INSERT INTO productos_insumo (producto, costo, codigo, id_tipo_insumo, estado, usuario, fecha)
                            VALUES ('$producto','$costo','$codigo','$id_tipo_insumo','1','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Producto de Insumo Registrado Correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_producto_insumo = $db->clearText($_POST['id_producto_insumo']);
            $producto = mb_convert_case($db->clearText($_POST['producto']), MB_CASE_UPPER, "UTF-8");
            $costo = $db->clearText(quitaSeparadorMiles($_POST['costo']));
            $codigo = $db->clearText($_POST['codigo']);
            $id_tipo_insumo = $db->clearText($_POST['tipo']);
    
       
            if (empty($producto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nombre para el Producto de Insumo"]);
                exit;
            }

            if (empty($costo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un costo para el Producto de Insumo"]);
                exit;
            }

            if (empty($codigo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Codigo para el Producto de Insumo"]);
                exit;
            }

             if (empty($id_tipo_insumo)) { 
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el Tipo de Insumo"]);
                  exit;
             }

            $db->setQuery("UPDATE productos_insumo SET producto='$producto', costo='$costo', codigo='$codigo', id_tipo_insumo=$id_tipo_insumo WHERE id_producto_insumo='$id_producto_insumo'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Insumo de ' $producto'  modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_producto_insumo = $db->clearText($_POST['id_producto_insumo']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE productos_insumo SET estado='$estado' WHERE id_producto_insumo='$id_producto_insumo'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id_producto_insumo = $db->clearText($_POST['id_producto_insumo']);
            $producto = $db->clearText($_POST['producto']);
            
            $db->setQuery("DELETE FROM productos_insumo WHERE id_producto_insumo = '$id_producto_insumo'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El registro se encuentra asociado a otros módulos del sistema"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Este Producto de Insumo  '$producto' eliminado correctamente"]);
        break;		

	}

?>
