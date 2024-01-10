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

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            $premio = $db->clearText($_GET['id_premio']);
      

            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', codigo, premio ) LIKE '%$search%'";
            }


            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            sp.id_stock_premio, 
                            sp.id_premio,
                            p.premio,
                            IFNULL(SUM(sp.stock), 0) AS stock,
                            p.costo AS costo_unitario,
                            p.costo * IFNULL(sp.stock, 0) AS costo_total,
                            p.codigo
                            FROM premios p
                            LEFT JOIN  stock_premios sp ON p.id_premio = sp.id_premio
                            WHERE 1=1  
                            GROUP BY sp.id_premio
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
            $id_premio = $db->clearText($_GET['id_premio']);


            //Parametros de ordenamiento, busqueda y paginacion
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        sp.id_stock_premio, 
                        sp.id_premio AS id_premio,
                        IFNULL(sp.stock, 0) AS stock,
                        p.costo AS costo_unitario, 
                        p.costo * IFNULL(sp.stock, 0) AS costo_total
                        FROM stock_premios sp
                        JOIN premios p ON p.id_premio = sp.id_premio
                        WHERE  sp.id_premio = $id_premio
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
