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
            // if ($id_rol != 1) {
            //     $id_sucursal = $db->clearText($_GET['id_sucursal']);
            // }

            $where = "";
            $and_tipo_producto ="";
            $and_vencimiento ="";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            $producto = $db->clearText($_GET['id_producto']);
            $estado = $db->clearText($_GET['estado']);
            $tipo_producto = $db->clearText($_GET['id_tipo_producto']);
            // $sucursal = $db->clearText($_GET['id_sucursal']);

            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', codigo, producto, tipo_insumo) LIKE '%$search%'";
            }
            
            if(!empty($estado) && intVal($estado) == 2) {
                $and_vencimiento = "AND si.vencimiento >= CURRENT_DATE() OR si.vencimiento IS NULL";
            }else if(!empty($estado) && intVal($estado) == 1){
                $and_vencimiento = "AND si.vencimiento <= CURRENT_DATE()";
            }
            
            if(!empty($tipo_producto) && intVal($tipo_producto) != 0) {
                $and_tipo_producto = " AND pin.id_tipo_insumo =$tipo_producto";
            }


            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            si.id_stock_insumo, 
                            si.id_producto_insumo,
                            pin.producto,
                            IFNULL(SUM(si.stock), 0) AS stock,
                            ti.nombre AS tipo_insumo,
                            pin.codigo
                            FROM productos_insumo pin
                            LEFT JOIN  stock_insumos si ON pin.id_producto_insumo = si.id_producto_insumo
                            LEFT JOIN tipos_insumos ti ON ti.id_tipo_insumo = pin.id_tipo_insumo
                            WHERE 1=1 $and_vencimiento $and_tipo_producto
                            GROUP BY si.id_producto_insumo
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
            $estado = $db->clearText($_GET['estado']);
            $tipo_producto = $db->clearText($_GET['id_tipo_producto']);
            // $sucursal = $db->clearText($_GET['id_sucursal']);

            $and_vencimiento = "";

            if(!empty($estado) && intVal($estado) == 2) {
                $and_vencimiento = "AND (si.vencimiento >= CURRENT_DATE() OR si.vencimiento IS NULL)";
            }else if(!empty($estado) && intVal($estado) == 1){
                $and_vencimiento = "AND si.vencimiento <= CURRENT_DATE()";
            }


            //Parametros de ordenamiento, busqueda y paginacion
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        si.id_stock_insumo, 
                        si.id_producto_insumo,
                        IFNULL(si.stock, 0) AS stock,
                        pin.costo AS costo_unitario, 
                        pin.costo * IFNULL(si.stock, 0) AS costo_total,
                        DATE_FORMAT(si.vencimiento, '%d/%m/%Y') AS vencimiento,
                        CASE 
                            WHEN si.vencimiento >=CURRENT_DATE() THEN 'Activo' 
                            WHEN si.vencimiento <=CURRENT_DATE() THEN 'Vencido'
                            WHEN si.vencimiento IS NULL THEN 'Activo' 
                        ELSE  'Error de fecha'
                        END AS estado
                        FROM stock_insumos si
                        JOIN productos_insumo pin ON pin.id_producto_insumo = si.id_producto_insumo
                        WHERE  si.id_producto_insumo = $id_producto_insumo  $and_vencimiento
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
