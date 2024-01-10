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

            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            $premio = $db->clearText($_GET['id_premio']);

            if(isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', codigo, descripcion, premio) LIKE '%$search%'";
            }

            if(!empty($premio) && intVal($premio) != 0) {
                $and_premio .= " AND sp.id_premio=$premio";
            }else{
                $and_premio .="";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                sp.id_stock_premio, 
                                sp.id_premio,
                                p.codigo,
                                p.premio,
                                p.descripcion,
                                p.costo,
                                p.puntos,
                                IFNULL(SUM(sp.stock), 0) AS stock,
                                CASE 
                                    estado WHEN 0 THEN 'Inactivo' 
                                    WHEN 1 THEN 'Activo' 
                                    END AS estado
                                FROM stock_premios sp
                                LEFT JOIN  premios p ON p.id_premio = sp.id_premio
                                WHERE 1=1 $and_premio  
                                GROUP BY sp.id_stock_premio
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
            $search = $db->clearText($_REQUEST['search']);
            $id_premio = $db->clearText($_REQUEST['id_premio']);
            $desde = $db->clearText($_REQUEST['fecha_desde']);
            $hasta = $db->clearText($_REQUEST['fecha_hasta']);
            $where = '';
            $where_fecha = '';

            if (isset($search) && !empty($search)) {
                $having = "HAVING fecha LIKE '$search%' OR detalles LIKE '$search%' OR operacion_str LIKE '$search%'";
            }

            if(!empty($desde) && !empty($hasta)) {
                $where_fecha = " AND DATE(sph.fecha) BETWEEN '$desde' AND '$hasta'";
            }

            if(!empty($id_premio)) {
                $where .= " AND sph.id_premio = $id_premio";
            }

            //Parametros de ordenamiento, busqueda y paginacion
            $limit      = $db->clearText($_REQUEST['limit']);
            $offset	   = $db->clearText($_REQUEST['offset']);
            $order     = $db->clearText($_REQUEST['order']);
            $sort       = ($db->clearText($_REQUEST['sort'])) ?: 2;
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                sph.id_stock_premios_historial, 
                                sph.id_premio,
                                sph.operacion,
                                sph.origen,
                                sph.detalles,
                                p.codigo,
                                CASE 
                                    WHEN sph.operacion = 'SUB' THEN 'SALIDA'
                                    WHEN sph.operacion = 'ADD' THEN 'ENTRADA'
                                END AS operacion_str,
                                IF(sph.operacion = 'ADD', sph.cantidad, 0) AS entrada,
                                IF(sph.operacion = 'SUB', sph.cantidad, 0) AS salida,
                                IF(sph.operacion = 'SUB', sph.cantidad, 0) AS salida,
                                DATE_FORMAT(sph.fecha,'%d/%m/%Y') AS fecha 
                                FROM stock_premios_historial sph  
                                JOIN premios p ON  sph.id_premio = p.id_premio
                                WHERE 1=1 $where_fecha $where
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

	}

?>
