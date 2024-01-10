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
                $having = "HAVING CONCAT_WS(' ', descripcion, producto, marca, clasificacion, DATE_FORMAT(dp.fecha_inicio,'%d/%m/%Y'), DATE_FORMAT(dp.fecha_fin,'%d/%m/%Y'), estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            dp.id_descuento_producto,
                            dp.descripcion,
                            p.id_producto,
                            p.codigo,
                            p.producto,
                            m.id_marca,
                            m.marca,
                            cp.id_clasificacion_producto,
                            cp.clasificacion,
                            dp.porcentaje,
                            dp.fecha_inicio,
                            dp.fecha_fin,
                            dp.estado,
                            CASE dp.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            dp.usuario,
                            DATE_FORMAT(dp.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM descuentos_productos dp
                            LEFT JOIN productos p ON dp.id_producto=p.id_producto
                            LEFT JOIN marcas m ON dp.id_marca=m.id_marca
                            LEFT JOIN clasificaciones_productos cp ON dp.id_clasificacion=cp.id_clasificacion_producto
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
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $id_producto = ($db->clearText($_POST['producto'])) ?: "NULL"; // Campo opcional
            $id_marca = ($db->clearText($_POST['marca'])) ?: "NULL"; // Campo opcional
            $id_clasificacion = ($db->clearText($_POST['clasificacion'])) ?: "NULL"; // Campo opcional
            $fecha_inicio = $db->clearText($_POST['fecha_inicio']);
            $fecha_fin = $db->clearText($_POST['fecha_fin']);
            $porcentaje = $db->clearText($_POST['porcentaje']);
            
            // Al menos uno debe estar definido
            if ($id_producto == "NULL" && $id_marca == "NULL" && $id_clasificacion == "NULL") {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un Producto, una Marca o una Descripcón"]);
                exit;
            }
            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el descuento"]);
                exit;
            }
            if (empty($porcentaje) && intval($porcentaje) == 0) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentaje a descontar"]);
                exit;
            }
            if (empty($fecha_inicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha de inicio"]);
                exit;
            }
            if (empty($fecha_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha de fin"]);
                exit;
            }
            if (strtotime($fecha_inicio) > strtotime($fecha_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "La Fecha de inicio es mayor a la Fecha de fin"]);
                exit;
            }

            // Se verifica la disponibilidad de las fechas
            if (!empty($id_producto)) {
                $v = verificarFechasProducto($id_producto, $fecha_inicio, $fecha_fin);
                if ($v) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" del producto seleccionado"]);
                    exit;
                }
            } else {
                $id_producto = "NULL";
            }
            if (!empty($id_marca)) {
                $v = verificarFechasMarca($id_marca, $fecha_inicio, $fecha_fin);
                if ($v) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" de la marca seleccionada"]);
                    exit;
                }
            } else {
                $id_marca = "NULL";
            }
            if (!empty($id_clasificacion)) {
                $v = verificarFechasClasificacion($id_clasificacion, $fecha_inicio, $fecha_fin);
                if ($v) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" de la clasificación de productos seleccionada"]);
                    exit;
                }
            } else {
                $id_clasificacion = "NULL";
            }

            $db->setQuery("INSERT INTO descuentos_productos (descripcion, id_producto, id_marca, id_clasificacion, porcentaje, fecha_inicio, fecha_fin, estado, usuario, fecha)
                            VALUES ('$descripcion',$id_producto,$id_marca,$id_clasificacion,$porcentaje,'$fecha_inicio','$fecha_fin','1','$usuario',NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->geterror()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Descuento registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['hidden_id']);
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $id_producto = $db->clearText($_POST['producto']);
            $id_marca = $db->clearText($_POST['marca']);
            $id_clasificacion = $db->clearText($_POST['clasificacion']);
            $fecha_inicio = $db->clearText($_POST['fecha_inicio']);
            $fecha_fin = $db->clearText($_POST['fecha_fin']);
            $porcentaje = $db->clearText($_POST['porcentaje']);
            
            // Al menos uno debe estar definido
            if (empty($id_producto) && empty($id_marca) && empty($id_clasificacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un Producto, una Marca o una Descripcón $id_descuento_producto"]);
                exit;
            }
            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el descuento"]);
                exit;
            }
            if (empty($porcentaje)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentaje a descontar"]);
                exit;
            }
            if (empty($fecha_inicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha de inicio"]);
                exit;
            }
            if (empty($fecha_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha de fin"]);
                exit;
            }
            if (strtotime($fecha_inicio) > strtotime($fecha_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "La Fecha de inicio es mayor a la Fecha de fin"]);
                exit;
            }

            // Se verifica la disponibilidad de las fechas
            if (!empty($id_producto)) {
                $v = verificarFechasProducto($id_producto, $fecha_inicio, $fecha_fin);
                if ($v && $v->id_descuento_producto != $id) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" del producto seleccionado"]);
                    exit;
                }
            } else {
                $id_producto = "NULL";
            }
            if (!empty($id_marca)) {
                $v = verificarFechasMarca($id_marca, $fecha_inicio, $fecha_fin);
                if ($v && $v->id_descuento_producto != $id) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" de la marca seleccionada"]);
                    exit;
                }
            } else {
                $id_marca = "NULL";
            }
            if (!empty($id_clasificacion)) {
                $v = verificarFechasClasificacion($id_clasificacion, $fecha_inicio, $fecha_fin);
                if ($v && $v->id_descuento_producto != $id) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" de la clasificación de productos seleccionada"]);
                    exit;
                }
            } else {
                $id_clasificacion = "NULL";
            }

            $db->setQuery("UPDATE descuentos_productos SET descripcion='$descripcion', id_producto=$id_producto, id_marca=$id_marca, id_clasificacion=$id_clasificacion, porcentaje=$porcentaje, fecha_inicio='$fecha_inicio', fecha_fin='$fecha_fin' WHERE id_descuento_producto=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Descuento '$descripcion' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("SELECT id_producto, id_marca, id_clasificacion, fecha_inicio, fecha_fin FROM descuentos_productos WHERE id_descuento_producto=$id");
            $row = $db->loadObject();

            // Se verifica la disponibilidad de las fechas
            if (!empty($row->id_producto)) {
                $v = verificarFechasProducto($row->id_producto, $row->fecha_inicio, $row->fecha_fin);
                if ($v && $v->id_descuento_producto != $id) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" del producto seleccionado"]);
                    exit;
                }
            }
            if (!empty($row->id_marca)) {
                $v = verificarFechasMarca($row->id_marca, $row->fecha_inicio, $row->fecha_fin);
                if ($v && $v->id_descuento_producto != $id) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" de la marca seleccionada"]);
                    exit;
                }
            }
            if (!empty($row->id_clasificacion)) {
                $v = verificarFechasClasificacion($row->id_clasificacion, $row->fecha_inicio, $row->fecha_fin);
                if ($v && $v->id_descuento_producto != $id) {
                    echo json_encode(["status" => "error", "mensaje" => "Las fechas ingresadas coinciden con el descuento \"$v->descripcion\" de la clasificación de productos seleccionada"]);
                    exit;
                }
            }

            $db->setQuery("UPDATE descuentos_productos SET estado='$estado' WHERE id_descuento_producto=$id");
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
            
            $db->setQuery("DELETE FROM descuentos_productos WHERE id_descuento_producto=$id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este descuento no puede ser eliminado"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Descuento '$nombre' eliminado correctamente"]);
        break;		

	}

    function verificarFechasProducto($id, $inicio, $fin) {
        return verificarFechas('id_producto', $id, $inicio, $fin);
    }

    function verificarFechasMarca($id, $inicio, $fin) {
        return verificarFechas('id_marca', $id, $inicio, $fin);
    }

    function verificarFechasClasificacion($id, $inicio, $fin) {
        return verificarFechas('id_clasificacion', $id, $inicio, $fin);
    }

    function verificarFechas($campo, $id, $inicio, $fin) {
        $db = DataBase::conectar();
        $where = "";

        $db->setQuery("SELECT id_descuento_producto, descripcion
                        FROM descuentos_productos
                        WHERE estado=1 AND $campo=$id
                        AND (('$inicio' BETWEEN fecha_inicio AND fecha_fin) OR ('$fin' BETWEEN fecha_inicio AND fecha_fin)
                        OR (fecha_inicio BETWEEN '$inicio' AND '$fin') OR (fecha_fin BETWEEN '$inicio' AND '$fin'))");
        $row = $db->loadObject();
        return $row;
    }

?>
