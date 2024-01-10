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
    $id_rol = $datosUsuario->rol;

    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();

            // Si es admin puede filtrar por sucursal
            if ($id_rol != 1) {
                $id_sucursal = $db->clearText($_GET['id_sucursal']);
            }

            $where = "";
            $and_vencimiento ="";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;

            $id_sucursal = $db->clearText($_GET['id_sucursal']);
            $estado = $db->clearText($_GET['estado']);
            $tipo        = $db->clearText($_REQUEST['tipo']);
            $rubro       = $db->clearText($_REQUEST['rubro']);
            $procedencia = $db->clearText($_REQUEST['procedencia']);
            $origen      = $db->clearText($_REQUEST['origen']);
            $clasificacion = $db->clearText($_REQUEST['clasificacion']);
            $presentacion  = $db->clearText($_REQUEST['presentacion']);
            $unidad_medida = $db->clearText($_REQUEST['unidad_medida']);
            $marca         = $db->clearText($_REQUEST['marca']);
            $laboratorio   = $db->clearText($_REQUEST['laboratorio']);


            if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
                $where_sucursal .= " AND s.id_sucursal= $id_sucursal";
            }else{$where_sucursal .="";};
            
            if(!empty($estado) && intVal($estado) == 2) {
                $and_vencimiento = "AND l.vencimiento >= CURRENT_DATE()";
            }else if(!empty($estado) && intVal($estado) == 1){
                $and_vencimiento = "AND l.vencimiento <= CURRENT_DATE()";
            }

            if(!empty($tipo) && intVal($tipo) != 0) {
                $and_id_tipo .= " AND p.id_tipo_producto= $tipo";
            }else{$and_id_tipo.="";};

            if(!empty($rubro) && intVal($rubro) != 0) {
                $and_id_rubro .= " AND p.id_rubro= $rubro";
            }else{$and_id_rubro.="";};

            if(!empty($procedencia) && intVal($procedencia) != 0) {
                $and_id_procedencia .= " AND p.id_pais= $procedencia";
            }else{$and_id_procedencia.="";};

            if(!empty($origen) && intVal($origen) != 0) {
                $and_id_origen .= " AND p.id_origen= $origen";
            }else{$and_id_origen.="";};

            if(!empty($clasificacion) && intVal($clasificacion) != 0) {
                $and_id_clasificacion .= " AND p.id_clasificacion= $clasificacion";
            }else{$and_id_clasificacion.="";};

            if(!empty($presentacion) && intVal($presentacion) != 0) {
                $and_id_presentacion .= " AND p.id_presentacion= $presentacion";
            }else{$and_id_presentacion.="";};

            if(!empty($unidad_medida) && intVal($unidad_medida) != 0) {
                $and_id_unidad_medida .= " AND p.id_unidad_medida= $unidad_medida";
            }else{$and_id_unidad_medida.="";};

            if(!empty($marca) && intVal($marca) != 0) {
                $and_id_marca .= " AND p.id_marca= $marca";
            }else{$and_id_marca .="";};

            if(!empty($laboratorio) && intVal($laboratorio) != 0) {
                $and_id_laboratorio .= " AND p.id_laboratorio= $laboratorio";
            }else{$and_id_laboratorio .="";};
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            s.id_stock,
                            IFNULL(SUM(s.stock_lote), 0) AS stock,
                            IFNULL(SUM(s.fraccionado), 0) AS fraccionado,
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote,
                            DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y') AS vencimiento_canje,
                            p.id_producto,
                            pre.presentacion,
                            cp.id_clasificacion_producto,
                            cp.clasificacion,
                            p.codigo,
                            p.producto,
                            p.precio,
                            SUM( s.stock_lote * l.costo) AS total_entero,
                            SUM(s.fraccionado * p.precio_fraccionado) AS total_fracc,
                            SUM(s.stock_lote * l.costo) AS total_general,
                            p.precio_fraccionado
                            FROM productos p
                            LEFT JOIN (SELECT *, SUM(stock) AS stock_lote, SUM(fraccionado) AS stock_frac FROM stock GROUP BY id_lote) s ON p.id_producto=s.id_producto $where_sucursal
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN productos_clasificaciones pp ON  p.id_producto = pp.id_producto
                            LEFT JOIN clasificaciones_productos cp ON pp.id_clasificacion=cp.id_clasificacion_producto
                            LEFT JOIN lotes l ON s.id_lote = l.id_lote
                            WHERE 1=1 $where_sucursal $and_vencimiento $and_id_tipo $and_id_rubro $and_id_procedencia $and_id_origen $and_id_marca  $and_id_clasificacion_producto            $and_id_presentacion $and_id_medida $and_id_laboratorio 
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
                            IFNULL(SUM(s.stock), 0),
                            l.id_lote, 
                            l.lote, 
                            DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y')  AS vencimiento_canje, 
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote ,
                            l.costo, 
                            CASE 
                                WHEN l.vencimiento >= CURRENT_DATE() THEN 'Activo' 
                                ELSE 'Vencido' 
                            END AS estado_lote,
                            IF(l.canje=1,'Si','No') AS canje_str,
                            IF(l.canje=1, IF(l.vencimiento_canje >= CURRENT_DATE(),'Activo','Vencido'),'Sin Canje') AS estado_canje, 
                            IFNULL(SUM(s.stock), 0) AS stock,
                            IFNULL(SUM(s.stock), 0) * IFNULL(l.costo,0) AS total_general,
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
