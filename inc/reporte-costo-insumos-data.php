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
            $producto = $db->clearText($_GET['id_producto']);
            $tipo_producto = $db->clearText($_GET['id_tipo_producto']);

            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', sucursal) LIKE '%$search%'";
            }
            
            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS
                                id_sucursal,
                                sucursal,
                                DATE_FORMAT(nr.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                                IFNULL(SUM(cantidad),0) AS cantidad,
                                IFNULL(SUM(cantidad),0)*IFNULL((p.costo),0) AS costo,
                                SUM(IFNULL((nri.cantidad),0)*IFNULL((nri.costo),0)) AS costo_total
                            FROM sucursales s
                            LEFT JOIN `notas_remision` nr ON s.id_sucursal=nr.`id_sucursal_destino`
                            LEFT JOIN `notas_remision_insumo` nri ON nr.`id_nota_remision`=nri.`id_nota_remision`
                            LEFT JOIN `productos_insumo` p ON nri.`id_producto_insumo`=p.`id_producto_insumo`
                            GROUP BY s.id_sucursal
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
            $desde    = $db->clearText($_GET['desde']);
            $hasta    = $db->clearText($_GET['hasta']);

            $where_fecha = "";

            if ((isset($desde) && !empty($desde)) || (isset($hasta) && !empty($hasta))) {
                $where_fecha = "AND DATE(nr.fecha) BETWEEN '$desde' AND '$hasta'";
            }

            //Parametros de ordenamiento, busqueda y paginacion
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                DATE_FORMAT(nr.fecha, '%d/%m/%Y') AS fecha,
                                nri.`id_producto_insumo`,
                                nri.`producto`,
                                nri.`cantidad`,
                                nri.`codigo`,
                                nri.`costo`,
                                nri.`cantidad_recibida`,
                                IFNULL(nri.`cantidad`,0)*IFNULL(nri.`costo`,0) AS total
                                
                            FROM `notas_remision_insumo` nri
                            LEFT JOIN `notas_remision` nr ON nri.`id_nota_remision`=nr.`id_nota_remision`
                            WHERE nr.`estado` != 2 AND nr.`id_sucursal_destino` = $id_sucursal $where_fecha
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
