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
            $sucursal = $db->clearText($_REQUEST['id_sucursal']);
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";

            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', ususario, observacion, caja) LIKE '%$search%'";
            }

            if (!isset($_REQUEST['id_sucursal']) && empty($_REQUEST['id_sucursal'])) {
                $sucursal = $id_sucursal;
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                e.id_extraccion,
                                e.usuario,
                                e.monto_extraccion,
                                DATE_FORMAT(e.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                                e.observacion,
                                c.id_caja,
                                c.id_sucursal,
                                CONCAT_WS(' | ',c.numero,s.sucursal) AS caja
                                
                            FROM cajas_extracciones e
                            LEFT JOIN cajas_horarios ch ON e.id_caja_horario=ch.id_caja_horario
                            LEFT JOIN cajas c ON ch.id_caja=c.id_caja
                            LEFT JOIN sucursales s ON c.id_sucursal=s.id_sucursal
                            WHERE c.id_sucursal=$sucursal AND e.fecha BETWEEN '$desde' AND '$hasta'
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
