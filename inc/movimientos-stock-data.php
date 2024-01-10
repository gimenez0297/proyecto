<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $datosUsuario = datosUsuario($usuario);
    $id_sucursal_ = $datosUsuario->id_sucursal;
    $id_rol = $datosUsuario->id_rol;

    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();
            //Parametros de ordenamiento, busqueda y paginacion
            $search         = $db->clearText($_REQUEST['search']);
            $limit          = $db->clearText($_REQUEST['limit']);
            $offset	        = $db->clearText($_REQUEST['offset']);
            $order          = $db->clearText($_REQUEST['order']);
            $sort           = ($db->clearText($_REQUEST['sort'])) ?: 2;
            $sucursal       = $db->clearText($_GET['id_sucursal']);
            $id_producto    = $db->clearText($_REQUEST['id_producto']);
            $tipo           = $db->clearText($_REQUEST['tipo']);
            $rubro          = $db->clearText($_REQUEST['rubro']);
            $procedencia    = $db->clearText($_REQUEST['procedencia']);
            $origen         = $db->clearText($_REQUEST['origen']);
            $clasificacion  = $db->clearText($_REQUEST['clasificacion']);
            $presentacion   = $db->clearText($_REQUEST['presentacion']);
            $unidad_medida  = $db->clearText($_REQUEST['unidad_medida']);
            $marca          = $db->clearText($_REQUEST['marca']);
            $laboratorio    = $db->clearText($_REQUEST['laboratorio']);
            $where = "";
            if (isset($search) && !empty($search)) {
                $having = "HAVING codigo LIKE '$search%' OR producto LIKE '$search%' OR pre.presentacion LIKE '$search%'";
            }

            if (esAdmin($id_rol) === false) {
                $where_sucursal .= " AND s.id_sucursal=$id_sucursal_";
            } else if(!empty($sucursal) && intVal($sucursal) != 0) {
                $where_sucursal .= " AND s.id_sucursal=$sucursal";
            } else if($sucursal === 'null'){
                $where_sucursal .= "";
            }
            
            if(!empty($id_producto)) {
                $where .= " AND p.id_producto = $id_producto";
            }
            if(!empty($tipo)) {
                $where .= " AND p.id_tipo_producto = $tipo";
            }
            if(!empty($rubro)) {
                $where .= " AND p.id_rubro = $rubro";
            }
            if(!empty($procedencia)) {
                $where .= " AND p.id_pais = $procedencia";
            }
            if(!empty($origen)) {
                $where .= " AND p.id_origen = $origen";
            }
            if(!empty($clasificacion)) {
                $where .= " AND pp.id_clasificacion = $clasificacion";
            }
            if(!empty($presentacion)) {
                $where .= " AND p.id_presentacion = $presentacion";
            }
            if(!empty($unidad_medida)) {
                $where .= " AND p.id_unidad_medida = $unidad_medida";
            }
            if(!empty($marca)) {
                $where .= " AND p.id_marca = $marca";
            }
            if(!empty($laboratorio)) {
                $where .= " AND p.id_laboratorio = $laboratorio";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            s.id_stock,
                            IFNULL(SUM(s.stock), 0) AS stock,
                            IFNULL(SUM(s.fraccionado), 0) AS fraccionado,
                            p.id_producto,
                            cp.id_clasificacion_producto,
                            cp.clasificacion,
                            pre.presentacion,
                            p.codigo,
                            p.producto,
                            p.precio,
                            p.precio_fraccionado
                            FROM productos p
                            LEFT JOIN stock s ON p.id_producto=s.id_producto 
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN productos_clasificaciones pp ON  p.id_producto = pp.id_producto
                            LEFT JOIN clasificaciones_productos cp ON pp.id_clasificacion=cp.id_clasificacion_producto
                            WHERE 1=1 $where_sucursal $where 
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
		
        case 'ver_detalle':
            $db = DataBase::conectar();
            $id_sucursal = $db->clearText($_GET['id_sucursal']);
            $id_producto = $db->clearText($_GET['id_producto']);
            $desde = $db->clearText($_REQUEST['fecha_desde']);
            $hasta = $db->clearText($_REQUEST['fecha_hasta']);
            $where = '';
            $where_fecha = '';

            // Si es admin puede filtrar por sucursal
            // if ($id_rol != 1) {
                // $id_sucursal = $db->clearText($_GET['id_sucursal']);
            // }
            if(!empty($desde) && !empty($hasta)) {
                $where_fecha = " AND DATE(sh.fecha) BETWEEN '$desde' AND '$hasta'";
            }
            if(!empty($id_sucursal)) {
                $where .= "AND sh.id_sucursal = $id_sucursal";
            }
            if(!empty($id_producto)) {
                $where .= " AND sh.id_producto = $id_producto";
            }
            //Parametros de ordenamiento, busqueda y paginacion
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                sh.id_stock_historial,
                                sh.id_producto,
                                sh.producto,
                                sh.id_sucursal,
                                sh.sucursal,
                                sh.id_lote,
                                sh.cantidad,
                                sh.fraccionado,
                                sh.operacion,
                                DATE_FORMAT(sh.fecha,'%d/%m/%Y %H:%i:%s') AS fecha_mov,
                                DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                                CASE 
                                    WHEN operacion = 'SUB' THEN CONCAT('-','',sh.cantidad)
                                    WHEN operacion = 'ADD' THEN sh.cantidad
                                END AS cantidad_str,
                                CASE 
                                    WHEN operacion = 'SUB' THEN CONCAT('-','',sh.fraccionado)
                                    WHEN operacion = 'ADD' THEN sh.fraccionado
                                END AS fraccionado_str,
                                CASE 
                                    WHEN operacion = 'SUB' THEN 'SALIDA'
                                    WHEN operacion = 'ADD' THEN 'ENTRADA'
                                END AS tipo_str,
                                IF(sh.operacion = 'ADD', sh.cantidad, 0) AS entrada,
                                IF(sh.operacion = 'SUB', sh.cantidad, 0) AS salida,
                                IF(sh.operacion = 'ADD', sh.fraccionado, 0) AS entrada_frac,
                                IF(sh.operacion = 'SUB', sh.fraccionado, 0) AS salida_frac,
                                sh.usuario,
                                sh.detalles AS detalles_old,
                                CASE 
                                    WHEN sh.origen = 'REM' AND sh.operacion='SUB'
                                    THEN 
                                    (
                                        SELECT CONCAT('Nota De Remisión N° ',LPAD(nr.numero, 7, '0')) 
                                        FROM notas_remision_productos nrp 
                                        LEFT JOIN notas_remision nr ON nr.id_nota_remision = nrp.id_nota_remision 
                                        WHERE id_nota_remision_producto = sh.id_origen
                                    )
                                    ELSE sh.detalles
                                END AS detalles
                            FROM stock_historial sh
                            LEFT JOIN productos p ON sh.id_producto=p.id_producto
                            WHERE 1=1 $where_fecha $where
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

	}
