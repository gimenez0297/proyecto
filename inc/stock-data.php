<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $datosUsuario = datosUsuario($usuario);
    $id_sucursal = $datosUsuario->id_sucursal;
    $id_rol = $datosUsuario->id_rol;

    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();

            $sucursal       = $db->clearText($_REQUEST['id_sucursal']);
            $estado         = $db->clearText($_REQUEST['estado']);
            $proveedor      = $db->clearText($_REQUEST['proveedor']);
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
            $where_sucursal = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;

            if (isset($search) && !empty($search)) {
                $having = "HAVING codigo LIKE '$search%' OR producto LIKE '$search%' OR pre.presentacion LIKE '$search%'";
            }

            if (!empty($proveedor)) {
                $where .= " AND pp.id_proveedor = $proveedor";
            }
            if (!empty($tipo)) {
                $where .= " AND p.id_tipo_producto = $tipo";
            }
            if (!empty($rubro)) {
                $where .= " AND p.id_rubro = $rubro";
            }
            if (!empty($procedencia)) {
                $where .= " AND p.id_pais = $procedencia";
            }
            if (!empty($origen)) {
                $where .= " AND p.id_origen = $origen";
            }
            if (!empty($clasificacion)) {
                $where .= " AND p.id_clasificacion = $clasificacion";
            }
            if (!empty($presentacion)) {
                $where .= " AND p.id_presentacion = $presentacion";
            }
            if (!empty($unidad_medida)) {
                $where .= " AND p.id_unidad_medida = $unidad_medida";
            }
            if (!empty($marca)) {
                $where .= " AND p.id_marca = $marca";
            }
            if (!empty($laboratorio)) {
                $where .= " AND p.id_laboratorio = $laboratorio";
            }
            if (!empty($estado) && intVal($estado) == 2) {
                $where = "AND (l.vencimiento >= CURRENT_DATE() OR l.vencimiento IS NULL)";
            } else if(!empty($estado) && intVal($estado) == 1) {
                $where = "AND l.vencimiento <= CURRENT_DATE()";
            }

            if (esAdmin($id_rol) === false) {
                $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
            } else if(!empty($sucursal) && intVal($sucursal) != 0) {
                $where_sucursal .= " AND s.id_sucursal=$sucursal";
            } else if(empty($sucursal)){
                $where_sucursal .= "";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            s.id_stock,
                            IFNULL(SUM(s.stock), 0) AS stock,
                            IFNULL(SUM(s.fraccionado), 0) AS fraccionado,
                            IFNULL(p.cantidad_fracciones, 0) AS cantidad_fracciones,
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote,
                            DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y') AS vencimiento_canje,
                            p.id_producto,
                            pre.presentacion,
                            p.codigo,
                            p.producto,
                            p.precio,
                            p.precio_fraccionado
                            FROM productos p
                            LEFT JOIN stock s ON p.id_producto=s.id_producto 
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN lotes l ON s.id_lote = l.id_lote
                            LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto
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
		
        case 'ver_lotes':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_GET['id_producto']);
            $sucursal = $db->clearText($_GET['id_sucursal']);
            $estado = $db->clearText($_GET['estado']);


            // Si es admin puede filtrar por sucursal
            if ($id_rol != 1) {
                $id_sucursal = $db->clearText($_GET['id_sucursal']);
            }

            if(!empty($sucursal) && intVal($sucursal) != 0) {
                $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
            }else{
                $where_sucursal .="";
            };

            if(!empty($estado) && intVal($estado) == 2) {
                $and_vencimiento = "AND l.vencimiento >= CURRENT_DATE() OR l.vencimiento IS NULL";
            }else if(!empty($estado) && intVal($estado) == 1){
                $and_vencimiento = "AND l.vencimiento <= CURRENT_DATE()";
            }

            //Parametros de ordenamiento, busqueda y paginacion
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            s.id_stock,
                            l.id_lote, 
                            l.lote, 
                            DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y')  AS vencimiento_canje, 
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote , 
                            CASE 
                                WHEN l.vencimiento >= CURRENT_DATE() THEN 'Activo' 
                                ELSE 'Vencido' 
                            END AS estado_lote,
                            IF(l.canje=1,'Si','No') AS canje_str,
                            IF(l.canje=1, IF(l.vencimiento_canje >= CURRENT_DATE(),'Activo','Vencido'),'Sin Canje') AS estado_canje, 
                            IFNULL(SUM(s.stock), 0) AS stock,
                            s.fraccionado
                            FROM stock s
                            JOIN lotes l ON s.id_lote = l.id_lote
                            WHERE s.id_producto=$id_producto $where_sucursal $and_vencimiento 
                            GROUP BY l.id_lote
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

?>
