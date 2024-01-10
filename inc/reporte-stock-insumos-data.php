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

            $desde    = $db->clearText($_REQUEST['desde']);
            $hasta    = $db->clearText($_REQUEST['hasta']);
            // $sucursal = $db->clearText($_GET['id_sucursal']);

            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', codigo, producto, tipo_insumo) LIKE '%$search%'";
            }

            if(!empty($producto) && intVal($producto) != 0) {
                $and_producto .= " AND si.id_producto_insumo=$producto";
            }else{
                $and_producto .="";
            }
            if(!empty($tipo_producto) && intVal($tipo_producto) != 0) {
                $and_tipo_producto .= " AND pin.id_tipo_insumo =$tipo_producto";
            }else{
                $and_tipo_producto .="";
            }

            if ((isset($desde) && !empty($desde)) || (isset($hasta) && !empty($hasta))) {
                $where_fecha = "AND DATE(si.vencimiento) BETWEEN '$desde' AND '$hasta'";
            } else {
                $where_fecha = "";
            }

            
            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS
                                si.id_stock_insumo,
                                si.id_producto_insumo,
                                pin.codigo,
                                pin.producto,
                                ti.nombre AS tipo_insumo,
                                si.stock,
                                DATE_FORMAT(si.vencimiento, '%d/%m/%Y') AS vencimiento,
                                CASE 
                                    WHEN si.vencimiento >= CURRENT_DATE() OR si.vencimiento IS NULL THEN 'Activo' 
                                    ELSE 'Vencido' 
                                END AS estado
                                
                            FROM stock_insumos si
                            LEFT JOIN productos_insumo pin ON pin.id_producto_insumo = si.id_producto_insumo
                            LEFT JOIN tipos_insumos ti ON ti.id_tipo_insumo = pin.id_tipo_insumo
                            WHERE 1=1 $and_producto $and_tipo_producto $where_fecha
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
            $id_producto_insumo = $db->clearText($_GET['id_producto_insumo']);
            // $sucursal = $db->clearText($_GET['id_sucursal']);


            // Si es admin puede filtrar por sucursal
            // if ($id_rol != 1) {
            //     $id_sucursal = $db->clearText($_GET['id_sucursal']);
            // }

            // if(!empty($sucursal) && intVal($sucursal) != 0) {
            //     $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
            // }else{
            //     $where_sucursal .="";
            // };

            //Parametros de ordenamiento, busqueda y paginacion
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                cip.id_carga_insumo_producto, 
                                cip.cantidad, 
                                DATE_FORMAT(cip.vencimiento,'%d/%m/%Y') AS vencimiento,
                                cip.monto 
                            FROM cargas_insumos_productos cip 
                            WHERE cip.id_producto_insumo= $id_producto_insumo
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
