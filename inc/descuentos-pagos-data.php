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
                $having = "HAVING dp.descripcion LIKE '$search%' OR estado_str LIKE '$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            dp.id_descuento_pago, 
                            dp.descripcion,
                            dp.tipo,
                            TIME_FORMAT(dp.hora_inicio, '%H:%i') AS hora_inicio,
                            TIME_FORMAT(dp.hora_fin, '%H:%i') AS hora_fin,
                            dp.fecha_inicio,
                            dp.fecha_fin,
                            CASE dp.estado WHEN 1 THEN 'Activo' WHEN 2 THEN 'Inactivo' END AS estado_str,
                            dp.estado,
                            DATE_FORMAT(dp.fecha, '%d/%m/%Y') AS fecha,
                            dp.usuario,
                            CONCAT('[', GROUP_CONCAT(CONCAT('{\"dia\":', dpd.dia, '}')), ']') AS dias,
                            CASE tipo
                                WHEN 1 THEN CONCAT(DATE_FORMAT(dp.fecha_inicio, '%d/%m/%Y'), ' - ', DATE_FORMAT(dp.fecha_fin, '%d/%m/%Y'))
                                WHEN 2 THEN (
                                    SELECT GROUP_CONCAT(CASE dia
                                        WHEN 1 THEN 'Dom'
                                        WHEN 2 THEN 'Lun'
                                        WHEN 3 THEN 'Mar'
                                        WHEN 4 THEN 'Miér'
                                        WHEN 5 THEN 'Jue'
                                        WHEN 6 THEN 'Vier'
                                        WHEN 7 THEN 'Sáb'
                                        END SEPARATOR ', ')
                                    FROM descuentos_pagos_dias WHERE id_descuento_pago=dp.id_descuento_pago
                                )
                            END AS configuracion
                            FROM descuentos_pagos dp
                            LEFT JOIN descuentos_pagos_dias dpd ON dp.id_descuento_pago=dpd.id_descuento_pago
                            GROUP BY dp.id_descuento_pago
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

        case 'ver_productos':
            $db = DataBase::conectar();
            $id_proveedor = $db->clearText($_REQUEST['id_proveedor']);
            $id_origen = $db->clearText($_REQUEST['id_origen']);
            $id_tipo_producto = $db->clearText($_REQUEST['id_tipo']);
            $id_laboratorio = $db->clearText($_REQUEST['id_laboratorio']);
            $id_marca = $db->clearText($_REQUEST['id_marca']);
            $id_rubro = $db->clearText($_REQUEST['id_rubro']);

            $where = "";
            if (isset($id_proveedor) && !empty($id_proveedor) && intval($id_proveedor)  > 0) {
                $where .= "AND pp.id_proveedor=$id_proveedor AND proveedor_principal=1";
            }
            if (isset($id_origen) && !empty($id_origen) && intval($id_origen)  > 0) {
                $where .= "AND p.id_origen=$id_origen";
            }
            if (isset($id_tipo_producto) && !empty($id_tipo_producto) && intval($id_tipo_producto)  > 0) {
                $where .= "AND p.id_tipo_producto=$id_tipo_producto";
            }
            if (isset($id_laboratorio) && !empty($id_laboratorio) && intval($id_laboratorio)  > 0) {
                $where .= "AND p.id_laboratorio=$id_laboratorio";
            }
            if (isset($id_marca) && !empty($id_marca) && intval($id_marca)  > 0) {
                $where .= "AND p.id_marca=$id_marca";
            }
            if (isset($id_rubro) && !empty($id_rubro) && intval($id_rubro)  > 0) {
                $where .= "AND p.id_rubro=$id_rubro";
            }

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', codigo, producto, presentacion, laboratorio, principios_activos) LIKE '%$search%'";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    p.id_producto, 
                                    p.producto, 
                                    p.codigo, 
                                    p.cantidad_fracciones, 
                                    p.observaciones, 
                                    pre.id_presentacion, 
                                    pre.presentacion,
                                    l.id_laboratorio,
                                    l.laboratorio,
                                    pa.id_principio,
                                    p.comision,
                                    p.descuento_fraccionado,
                                    pp.id_proveedor,
                                    -- GROUP_CONCAT(pa.nombre SEPARATOR ', ') AS principios_activos,
                                    pa.nombre AS principios_activos,
                                    IFNULL(p.precio, 0) AS precio, 
                                    IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado,
                                    CASE p.descuento_fraccionado WHEN 1 THEN 'Si' ELSE 'No' END AS descuento_fraccionado
                            FROM productos p
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
                            LEFT JOIN productos_principios ppa ON p.id_producto=ppa.id_producto
                            LEFT JOIN principios_activos pa ON ppa.id_principio=pa.id_principio
                            LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto
                            WHERE p.estado=1 $where
                            GROUP BY p.id_producto
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

        case 'ver_filtros':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);

            $db->setQuery("SELECT  
                                dpf.id_descuento_pago_filtro,
                                dpf.id_metodo_pago,
                                mp.metodo_pago,
                                dpf.id_entidad,
                                e.entidad,
                                dpf.id_origen,
                                o.origen,
                                dpf.id_tipo_producto AS id_tipo,
                                tp.tipo,
                                dpf.id_laboratorio,
                                l.laboratorio,
                                dpf.id_marca,
                                m.marca,
                                dpf.id_rubro,
                                r.rubro,
                                dpf.controlado,
                                dpf.porcentaje
                            FROM descuentos_pagos_filtros dpf
                            LEFT JOIN metodos_pagos mp ON dpf.id_metodo_pago=mp.id_metodo_pago
                            LEFT JOIN entidades e ON dpf.id_entidad=e.id_entidad
                            LEFT JOIN origenes o ON dpf.id_origen=o.id_origen
                            LEFT JOIN tipos_productos tp ON dpf.id_tipo_producto=tp.id_tipo_producto
                            LEFT JOIN laboratorios l ON dpf.id_laboratorio=l.id_laboratorio
                            LEFT JOIN marcas m ON dpf.id_marca=m.id_marca
                            LEFT JOIN rubros r ON dpf.id_rubro=r.id_rubro
                            WHERE dpf.id_descuento_pago=$id");
            $rows = $db->loadObjectList() ?: [];
            echo json_encode($rows);
        break;

        case 'ver_productos_descuento':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);

            $db->setQuery("SELECT  
                                p.id_producto, 
                                p.producto, 
                                p.codigo, 
                                pre.id_presentacion, 
                                pre.presentacion,
                                IFNULL(p.precio, 0) AS precio, 
                                IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado,
                                CASE p.descuento_fraccionado WHEN 1 THEN 'Si' ELSE 'No' END AS descuento_fraccionado,
                                dpp.porcentaje
                            FROM descuentos_pagos_productos dpp
                            JOIN productos p ON dpp.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            WHERE p.estado=1 AND dpp.id_descuento_pago=$id");
            $rows = $db->loadObjectList() ?: [];
            echo json_encode($rows);
        break;

        case 'ver_productos_remate':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);

            $db->setQuery("SELECT  
                                p.id_producto, 
                                p.producto, 
                                p.codigo, 
                                pre.id_presentacion, 
                                pre.presentacion,
                                IFNULL(p.precio, 0) AS precio, 
                                IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado,
                                CASE p.descuento_fraccionado WHEN 1 THEN 'Si' ELSE 'No' END AS descuento_fraccionado,
                                dpr.id_lote, 
                                l.lote, 
                                l.vencimiento, 
                                dpr.porcentaje
                            FROM descuentos_pagos_remates dpr
                            JOIN productos p ON dpr.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            JOIN lotes l ON dpr.id_lote=l.id_lote
                            WHERE p.estado=1 AND dpr.id_descuento_pago=$id");
            $rows = $db->loadObjectList() ?: [];
            echo json_encode($rows);
        break;

        case 'cargar':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $hora_inicio = $db->clearText($_POST['hora_inicio']);
            $hora_fin = $db->clearText($_POST['hora_fin']);
            $tipo = $db->clearText($_POST['tipo_configuracion']);
            $fecha_inicio = $db->clearText($_POST['fecha_inicio']);
            $fecha_fin = $db->clearText($_POST['fecha_fin']);
            $dias = $_POST['dias'];
            $detalles = json_decode($_POST['detalles']);
            $detalles2 = json_decode($_POST['detalles2']);
            $detalles3 = json_decode($_POST['detalles3']);

            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el descuento"]);
                exit;
            }
            if (empty($hora_inicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la hora de inicio"]);
                exit;
            }
            if (empty($hora_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la hora de fin"]);
                exit;
            }
            if (strtotime($hora_inicio) > strtotime($hora_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "La hora de inicio es mayor a la hora de fin"]);
                exit;
            }
            if ($tipo == 1) {
                if (empty($fecha_inicio) || empty($fecha_fin)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el rango de fechas"]);
                    exit;
                }
                if (strtotime($fecha_inicio) > strtotime($fecha_fin)) {
                    echo json_encode(["status" => "error", "mensaje" => "La Fecha de inicio es mayor a la Fecha de fin"]);
                    exit;
                }
                if (strtotime($fecha_fin) > strtotime("2022-12-30")) {
                    echo json_encode(["status" => "error", "mensaje" => "Limite de fecha 30/12/2200"]);
                    exit;
                }
            } else if ($tipo == 2) {
                if (empty($dias)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor seleccione los dias que se debe aplicar el descuento"]);
                    exit;
                }
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el tipo de configuración del descuento"]);
                exit;
            }
            if (empty($detalles) && empty($detalles2) && empty($detalles3)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún porcentaje de descuento agregado. Favor complete la tabla de filtros, la tabla de productos o la tabla de remates"]);
                exit;
            }

            if ($tipo == 1) {
                $fecha_inicio = "'$fecha_inicio'";
                $fecha_fin = "'$fecha_fin'";
                $dias = [];
            } else if ($tipo == 2) {
                $fecha_inicio = "NULL";
                $fecha_fin = "NULL";
            }

            $db->setQuery("INSERT INTO descuentos_pagos (
                            descripcion,
                            tipo,
                            hora_inicio,
                            hora_fin,
                            fecha_inicio,
                            fecha_fin,
                            estado,
                            usuario
                        )
                        VALUES
                            (
                            '$descripcion',
                            $tipo,
                            '$hora_inicio',
                            '$hora_fin',
                            $fecha_inicio,
                            $fecha_fin,
                            2,
                            '$usuario'
                            );
                        
                        ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->geterror()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            $id = $db->getLastID();

            // Filtros
            foreach ($detalles as $key => $value) {
                $id_metodo_pago = $db->clearText($value->id_metodo_pago) ?: "NULL";
                $id_entidad = $db->clearText($value->id_entidad) ?: "NULL";
                $id_origen = $db->clearText($value->id_origen) ?: "NULL";
                $id_tipo_producto = $db->clearText($value->id_tipo) ?: "NULL";
                $id_laboratorio = $db->clearText($value->id_laboratorio) ?: "NULL";
                $id_marca = $db->clearText($value->id_marca) ?: "NULL";
                $id_rubro = $db->clearText($value->id_rubro) ?: "NULL";
                $controlado = $value->controlado;
                $porcentaje = $db->clearText($value->porcentaje);

                if (empty($porcentaje) && intval($porcentaje) == 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentajes a descontar mayores a 0%", "error" => "detalles"]);
                    exit;
                }
                if ($porcentaje > 100) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentajes a descontar menores a 100%", "error" => "detalles"]);
                    exit;
                }

                // Controlado puede tener tres valores: null, 0 y 1
                if (is_null($controlado)) {
                    $controlado = "NULL";
                } else {
                    $controlado = $db->clearText($controlado);
                }

                $db->setQuery("INSERT INTO descuentos_pagos_filtros (id_descuento_pago, id_metodo_pago, id_entidad, id_origen, id_tipo_producto, id_laboratorio, id_marca, id_rubro, controlado, porcentaje)
                                VALUES ($id, $id_metodo_pago, $id_entidad, $id_origen, $id_tipo_producto, $id_laboratorio, $id_marca, $id_rubro, $controlado, $porcentaje)");

                if (!$db->alter()) {
                    // Combinación de filtros unica
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Combinación de filtros duplicada"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los filtros", "error" => "detalles"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            // Productos
            foreach ($detalles2 as $key => $value) {
                $id_producto = $db->clearText($value->id_producto);
                $producto = $db->clearText($value->producto);
                $porcentaje = $db->clearText($value->porcentaje);

                if (empty($porcentaje) && intval($porcentaje) == 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentaje a descontar para el producto '$producto'", "error" => "detalles2"]);
                    exit;
                }
                if ($porcentaje > 100) {
                    echo json_encode(["status" => "error", "mensaje" => "El porcentaje de descuento del producto '$producto' supera el 100%", "error" => "detalles2"]);
                    exit;
                }

                $db->setQuery("INSERT INTO descuentos_pagos_productos (id_descuento_pago, id_producto, porcentaje) VALUES ($id, $id_producto, $porcentaje)");
                if (!$db->alter()) {
                    // id_producto e id_descuento_pago unico
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Producto '$producto' agregado", "error" => "detalles2"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error" => "detalles2"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            // Remates
            foreach ($detalles3 as $key => $value) {
                $id_producto = $db->clearText($value->id_producto);
                $producto = $db->clearText($value->producto);
                $id_lote = $db->clearText($value->id_lote);
                $porcentaje = $db->clearText($value->porcentaje);

                if (empty($porcentaje) && intval($porcentaje) == 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentaje a descontar para el producto '$producto'", "error" => "detalles3"]);
                    exit;
                }
                if ($porcentaje > 100) {
                    echo json_encode(["status" => "error", "mensaje" => "El porcentaje de descuento del producto '$producto' supera el 100%", "error" => "detalles3"]);
                    exit;
                }

                $db->setQuery("INSERT INTO descuentos_pagos_remates (id_descuento_pago, id_producto, id_lote, porcentaje) VALUES ($id, $id_producto, $id_lote, $porcentaje)");
                if (!$db->alter()) {
                    // id_producto e id_descuento_pago unico
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Producto '$producto' agregado", "error" => "detalles3"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error" => "detalles3"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            // Días seleccionados
            foreach ($dias as $key => $value) {
                $dia = $db->clearText($value);

                $db->setQuery("INSERT INTO descuentos_pagos_dias (id_descuento_pago, dia) VALUES ($id, $dia)");
                if (!$db->alter()) {
                    // Combinación id_descuento y dia unico
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Día '$dia' agregado", "error" => "detalles2"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los días", "error" => "detalles2"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            $db->commit(); // Guardamos los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Descuento registrado correctamente"]);
            
        break;

        case 'editar': 
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_POST['hidden_id']);
            $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $hora_inicio = $db->clearText($_POST['hora_inicio']);
            $hora_fin = $db->clearText($_POST['hora_fin']);
            $tipo = $db->clearText($_POST['tipo_configuracion']);
            $fecha_inicio = $db->clearText($_POST['fecha_inicio']);
            $fecha_fin = $db->clearText($_POST['fecha_fin']);
            $dias = $_POST['dias'];
            $detalles = json_decode($_POST['detalles']);
            $detalles2 = json_decode($_POST['detalles2']);
            $detalles3 = json_decode($_POST['detalles3']);

            if (empty($descripcion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el descuento"]);
                exit;
            }
            if (empty($hora_inicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la hora de inicio"]);
                exit;
            }
            if (empty($hora_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la hora de fin"]);
                exit;
            }
            if (strtotime($hora_inicio) > strtotime($hora_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "La hora de inicio es mayor a la hora de fin"]);
                exit;
            }
            if ($tipo == 1) {
                if (empty($fecha_inicio) || empty($fecha_fin)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el rango de fechas"]);
                    exit;
                }
                if (strtotime($fecha_inicio) > strtotime($fecha_fin)) {
                    echo json_encode(["status" => "error", "mensaje" => "La Fecha de inicio es mayor a la Fecha de fin"]);
                    exit;
                }
                if (strtotime($fecha_fin) > strtotime("2022-12-30")) {
                    echo json_encode(["status" => "error", "mensaje" => "Limite de fecha 30/12/2200"]);
                    exit;
                }
            } else if ($tipo == 2) {
                if (empty($dias)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor seleccione los dias que se debe aplicar el descuento"]);
                    exit;
                }
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el tipo de configuración del descuento"]);
                exit;
            }
            if (empty($detalles) && empty($detalles2) && empty($detalles3)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún porcentaje de descuento agregado. Favor complete la tabla de filtros, la tabla de productos o la tabla de remates"]);
                exit;
            }

            $db->setQuery("SELECT estado FROM descuentos_pagos WHERE id_descuento_pago=$id");
            $estado = $db->loadObject()->estado;

            if ($estado == 1) {
                $descuento = new stdClass();
                $descuento->id_descuento_pago = $id;
                $descuento->hora_inicio = $hora_inicio;
                $descuento->hora_fin = $hora_fin;
                $descuento->fecha_inicio = $fecha_inicio;
                $descuento->fecha_fin = $fecha_fin;
                $descuento->dias = $dias;

                $v = verificarDescuento($descuento, $tipo);
                if ($v === false) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al verificar la configuración de la campaña '$descripcion'"]);
                    exit;
                } else if ($v > 0) {
                    echo json_encode(["status" => "error", "mensaje" => separadorMiles($v)." productos coinciden con otros descuentos activos"]);
                    exit;
                }
            }

            if ($tipo == 1) {
                $fecha_inicio = "'$fecha_inicio'";
                $fecha_fin = "'$fecha_fin'";
                $dias = [];
            } else if ($tipo == 2) {
                $fecha_inicio = "NULL";
                $fecha_fin = "NULL";
            }

            $db->setQuery("UPDATE
                                descuentos_pagos
                            SET
                                descripcion       = '$descripcion',
                                tipo              = '$tipo',
                                hora_inicio       = '$hora_inicio',
                                hora_fin          = '$hora_fin',
                                fecha_inicio      = $fecha_inicio,
                                fecha_fin         = $fecha_fin
                            WHERE 
                                id_descuento_pago = $id;
            ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            // Filtros
            $db->setQuery("DELETE FROM descuentos_pagos_filtros WHERE id_descuento_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            // Filtros
            foreach ($detalles as $key => $value) {
                $id_metodo_pago = $db->clearText($value->id_metodo_pago) ?: "NULL";
                $id_entidad = $db->clearText($value->id_entidad) ?: "NULL";
                $id_origen = $db->clearText($value->id_origen) ?: "NULL";
                $id_tipo_producto = $db->clearText($value->id_tipo) ?: "NULL";
                $id_laboratorio = $db->clearText($value->id_laboratorio) ?: "NULL";
                $id_marca = $db->clearText($value->id_marca) ?: "NULL";
                $id_rubro = $db->clearText($value->id_rubro) ?: "NULL";
                $controlado = $value->controlado;
                $porcentaje = $db->clearText($value->porcentaje);

                if (empty($porcentaje) && intval($porcentaje) == 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentajes a descontar mayores a 0%", "error" => "detalles"]);
                    exit;
                }
                if ($porcentaje > 100) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentajes a descontar menores a 100%", "error" => "detalles"]);
                    exit;
                }

                // Controlado puede tener tres valores: null, 0 y 1
                if (is_null($controlado)) {
                    $controlado = "NULL";
                } else {
                    $controlado = $db->clearText($controlado);
                }

                $db->setQuery("INSERT INTO descuentos_pagos_filtros (id_descuento_pago, id_metodo_pago, id_entidad, id_origen, id_tipo_producto, id_laboratorio, id_marca, id_rubro, controlado, porcentaje)
                                VALUES ($id, $id_metodo_pago, $id_entidad, $id_origen, $id_tipo_producto, $id_laboratorio, $id_marca, $id_rubro, $controlado, $porcentaje)");

                if (!$db->alter()) {
                    // Combinación de filtros unica
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Combinación de filtros duplicada"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los filtros", "error" => "detalles"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            // Productos
            $db->setQuery("DELETE FROM descuentos_pagos_productos WHERE id_descuento_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            foreach ($detalles2 as $key => $value) {
                $id_producto = $db->clearText($value->id_producto);
                $producto = $db->clearText($value->producto);
                $porcentaje = $db->clearText($value->porcentaje);

                if (empty($porcentaje)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentaje a descontar para el producto '$producto'", "error" => "detalles2"]);
                    exit;
                }
                if ($porcentaje > 100) {
                    echo json_encode(["status" => "error", "mensaje" => "El porcentaje de descuento del producto '$producto' supera el 100%", "error" => "detalles2"]);
                    exit;
                }

                $db->setQuery("INSERT INTO descuentos_pagos_productos (id_descuento_pago, id_producto, porcentaje) VALUES ($id, $id_producto, $porcentaje)");
                if (!$db->alter()) {
                    // id_producto e id_descuento_pago unico
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Producto '$producto' agregado", "error" => "detalles2"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error" => "detalles2"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            // Remates
            $db->setQuery("DELETE FROM descuentos_pagos_remates WHERE id_descuento_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            foreach ($detalles3 as $key => $value) {
                $id_producto = $db->clearText($value->id_producto);
                $producto = $db->clearText($value->producto);
                $id_lote = $db->clearText($value->id_lote);
                $porcentaje = $db->clearText($value->porcentaje);

                if (empty($porcentaje) && intval($porcentaje) == 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el porcentaje a descontar para el producto '$producto'", "error" => "detalles3"]);
                    exit;
                }
                if ($porcentaje > 100) {
                    echo json_encode(["status" => "error", "mensaje" => "El porcentaje de descuento del producto '$producto' supera el 100%", "error" => "detalles3"]);
                    exit;
                }

                $db->setQuery("INSERT INTO descuentos_pagos_remates (id_descuento_pago, id_producto, id_lote, porcentaje) VALUES ($id, $id_producto, $id_lote, $porcentaje)");
                if (!$db->alter()) {
                    // id_producto e id_descuento_pago unico
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Producto '$producto' agregado", "error" => "detalles3"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error" => "detalles3"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            // Días seleccionados
            $db->setQuery("DELETE FROM descuentos_pagos_dias WHERE id_descuento_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            foreach ($dias as $key => $value) {
                $dia = $db->clearText($value);

                $db->setQuery("INSERT INTO descuentos_pagos_dias (id_descuento_pago, dia) VALUES ($id, $dia)");
                if (!$db->alter()) {
                    // Combinación id_descuento y dia unico
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "Día '$dia' agregado", "error" => "detalles2"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los días", "error" => "detalles2"]);
                    }
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }
            }

            $db->commit(); // Guardamos los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Descuento '$descripcion' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado == 1) {
                $db->setQuery("SELECT 
                                    id_descuento_pago, 
                                    descripcion,
                                    tipo, 
                                    hora_inicio, 
                                    hora_fin, 
                                    fecha_inicio, 
                                    fecha_fin 
                                FROM descuentos_pagos 
                                WHERE id_descuento_pago=$id");
                $row = $db->loadObject();

                $descripcion = $row->descripcion;
                $tipo = $row->tipo;

                $descuento = new stdClass();
                $descuento->id_descuento_pago = $id;
                $descuento->hora_inicio = $row->hora_inicio;
                $descuento->hora_fin = $row->hora_fin;
                $descuento->fecha_inicio = $row->fecha_inicio;
                $descuento->fecha_fin = $row->fecha_fin;

                if ($tipo == 2) {
                    $db->setQuery("SELECT dia FROM descuentos_pagos_dias WHERE id_descuento_pago=$id");
                    $descuento->dias = array_column($db->loadObjectList(), "dia");
                } else {
                    $descuento->dias = [];
                }

                $v = verificarDescuento($descuento, $tipo);
                if ($v === false) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al verificar la configuración de la campaña '$descripcion'"]);
                    exit;
                } else if ($v > 0) {
                    echo json_encode(["status" => "error", "mensaje" => separadorMiles($v)." productos coinciden con otros descuentos activos"]);
                    exit;
                }
            }

            $db->setQuery("UPDATE descuentos_pagos SET estado=$estado WHERE id_descuento_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'ver_campanas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            dp.id_descuento_pago, 
                            dp.descripcion, 
                            dp.fecha_inicio, 
                            dp.fecha_fin
                            FROM descuentos_pagos dp
                            WHERE dp.descripcion LIKE '%$term%'
                            ORDER BY descripcion DESC
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM descuentos_pagos WHERE id_descuento_pago=$id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "La Campaña no puede ser eliminada"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Campaña '$nombre' eliminada correctamente"]);
        break;		

    }

    /**
     * Verifica los productos no coincidan entre descuentos activos en la misma fecha
     * @param object $descuento datos del descuento
     *  * `int id_descuento_pago`
     *  * `string hora_inicio`
     *  * `string hora_fin`
     *  * `string fecha_inicio`
     *  * `string fecha_fin`
     *  * `array dias`
     * @param int $tipo tipo de configuración
     *  * `1: Rango de fechas` 
     *  * `2: Días` 
     * @return mixed cantidad de productos que coinciden con el descuento ingresado o false si hubo un error
     */
    function verificarDescuento($descuento, $tipo) {
        $db = DataBase::conectar();
        $id_descuento_pago = $descuento->id_descuento_pago;
        $hora_inicio = $descuento->hora_inicio;
        $hora_fin = $descuento->hora_fin;
        $fecha_inicio = $descuento->fecha_inicio;
        $fecha_fin = $descuento->fecha_fin;
        $dias = $descuento->dias;

        $where = "";

        // Se filtran los descuentos que se deben tener en cuenta
        if ($tipo == 1) {
            $in_descuentos = "
                -- Se compara con los descuentos configurados por rango de fecha
                SELECT dp.id_descuento_pago
                FROM descuentos_pagos dp
                WHERE estado=1 AND dp.id_descuento_pago != $id_descuento_pago $where
                AND (
                    (('$fecha_inicio' BETWEEN dp.fecha_inicio AND dp.fecha_fin) AND ('$hora_inicio' BETWEEN dp.hora_inicio AND dp.hora_fin)) 
                    OR (('$fecha_fin' BETWEEN dp.fecha_inicio AND dp.fecha_fin) AND ('$hora_fin' BETWEEN dp.hora_inicio AND dp.hora_fin))
                    OR ((dp.fecha_inicio BETWEEN '$fecha_inicio' AND '$fecha_fin') AND (dp.hora_inicio BETWEEN '$hora_inicio' AND '$hora_fin')) 
                    OR ((dp.fecha_fin BETWEEN '$fecha_inicio' AND '$fecha_fin') AND (dp.hora_fin BETWEEN '$hora_inicio' AND '$hora_fin'))
                )
                UNION
                -- Se compara con los descuentos configurados por rango de fecha
                SELECT dp.id_descuento_pago
                FROM descuentos_pagos dp
                JOIN descuentos_pagos_dias dpd ON dp.id_descuento_pago=dpd.id_descuento_pago
                JOIN calendario c ON c.fecha_mysql BETWEEN '$fecha_inicio' AND '$fecha_fin'
                WHERE dp.estado=1 AND DAYOFWEEK(c.fecha_mysql)=dpd.dia AND dp.id_descuento_pago != $id_descuento_pago $where
                AND (
                    ('$hora_inicio' BETWEEN hora_inicio AND hora_fin)
                    OR ('$hora_fin' BETWEEN hora_inicio AND hora_fin)
                    OR (hora_inicio BETWEEN '$hora_inicio' AND '$hora_fin')
                    OR (hora_fin BETWEEN '$hora_inicio' AND '$hora_fin')
                )
            ";
        } else if ($tipo == 2) {
            $in_dias = implode(",", $dias);

            $in_descuentos = "
                -- Se compara con los descuentos configurados por rango de fecha
                SELECT id_descuento_pago
                FROM descuentos_pagos dp
                JOIN calendario c ON c.fecha_mysql BETWEEN dp.fecha_inicio AND dp.fecha_fin
                WHERE dp.estado=1 AND dp.id_descuento_pago != $id_descuento_pago AND DAYOFWEEK(c.fecha_mysql) IN ($in_dias) $where
                AND (
                    ('$hora_inicio' BETWEEN hora_inicio AND hora_fin)
                    OR ('$hora_fin' BETWEEN hora_inicio AND hora_fin)
                    OR (hora_inicio BETWEEN '$hora_inicio' AND '$hora_fin')
                    OR (hora_fin BETWEEN '$hora_inicio' AND '$hora_fin')
                )
                UNION
                -- Se compara con los descuentos configurados por día
                SELECT dp.id_descuento_pago
                FROM descuentos_pagos dp
                JOIN descuentos_pagos_dias dpd ON dp.id_descuento_pago=dpd.id_descuento_pago
                WHERE dp.estado=1 AND dp.id_descuento_pago != $id_descuento_pago AND dpd.dia IN ($in_dias) $where
                AND (
                    ('$hora_inicio' BETWEEN hora_inicio AND hora_fin)
                    OR ('$hora_fin' BETWEEN hora_inicio AND hora_fin)
                    OR (hora_inicio BETWEEN '$hora_inicio' AND '$hora_fin')
                    OR (hora_fin BETWEEN '$hora_inicio' AND '$hora_fin')
                )
            ";
        } else {
            return false;
        }

        // Se verifica si los productos están en otras campañas activas
        $db->setQuery("SELECT * FROM (
                            SELECT p.id_producto
                            FROM productos p, descuentos_pagos_filtros dpf
                            WHERE (dpf.id_origen=p.id_origen OR dpf.id_origen IS NULL)
                            AND (dpf.id_tipo_producto=p.id_tipo_producto OR dpf.id_tipo_producto IS NULL)
                            AND (dpf.id_laboratorio=p.id_laboratorio OR dpf.id_laboratorio IS NULL)
                            AND (dpf.id_marca=p.id_marca OR dpf.id_marca IS NULL)
                            AND (dpf.id_rubro=p.id_rubro OR dpf.id_rubro IS NULL)
                            AND (dpf.controlado=p.controlado OR dpf.controlado IS NULL)
                            AND dpf.id_descuento_pago=$id_descuento_pago
                            UNION
                            SELECT id_producto
                            FROM descuentos_pagos_productos
                            WHERE id_descuento_pago=$id_descuento_pago
                            UNION
                            SELECT id_producto
                            FROM descuentos_pagos_remates
                            WHERE id_descuento_pago=$id_descuento_pago
                        ) AS d
                        WHERE id_producto IN (
                            SELECT p.id_producto
                            FROM productos p, descuentos_pagos_filtros dpf
                            JOIN descuentos_pagos dp ON dpf.id_descuento_pago=dp.id_descuento_pago
                            WHERE (dpf.id_origen=p.id_origen OR dpf.id_origen IS NULL)
                            AND (dpf.id_tipo_producto=p.id_tipo_producto OR dpf.id_tipo_producto IS NULL)
                            AND (dpf.id_laboratorio=p.id_laboratorio OR dpf.id_laboratorio IS NULL)
                            AND (dpf.id_marca=p.id_marca OR dpf.id_marca IS NULL)
                            AND (dpf.id_rubro=p.id_rubro OR dpf.id_rubro IS NULL)
                            AND (dpf.controlado=p.controlado OR dpf.controlado IS NULL)
                            AND dp.id_descuento_pago IN ($in_descuentos)
                        )
                        OR id_producto IN (
                            SELECT dpp.id_producto
                            FROM descuentos_pagos_productos dpp
                            JOIN descuentos_pagos dp ON dp.id_descuento_pago=dpp.id_descuento_pago
                            WHERE dp.id_descuento_pago IN ($in_descuentos)
                        )
                        OR id_producto IN (
                            SELECT dpr.id_producto
                            FROM descuentos_pagos_remates dpr
                            JOIN descuentos_pagos dp ON dp.id_descuento_pago=dpr.id_descuento_pago
                            WHERE dp.id_descuento_pago IN ($in_descuentos)
                        )");
        $rows = $db->loadObjectList();
        if ($db->error()) {
            return false;
        }

        $cantidad = count($rows);
        return $cantidad;
    }

?>
