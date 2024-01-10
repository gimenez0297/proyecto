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
                $having = " AND CONCAT_WS(' ', razon_social, ruc) LIKE '%$search%'";
            }

            if(!empty($sucursal) && intVal($sucursal) != 0) {
                $and_id_sucursal .= " AND id_sucursal= $sucursal";
            }else{
                $and_id_sucursal .="";
            };

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                fecha_venta,
                                DATE_FORMAT(fecha_venta, '%d/%m/%Y') AS fecha,
                                ruc,
                                id_cliente,
                                razon_social,
                                SUM(cantidad) AS cantidad,
                                SUM(total_venta) AS total

                            FROM facturas 
                            WHERE estado != 2 AND fecha_venta BETWEEN '$desde' AND '$hasta' $and_id_sucursal
                            $having
                            GROUP BY id_cliente
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
            $id_cliente = $db->clearText($_GET['id_cliente']);
            $sucursal = $db->clearText($_GET['id_sucursal']);
            $desde    = $db->clearText($_GET['desde']) . " 00:00:00";
            $hasta    = $db->clearText($_GET['hasta']) . " 23:59:59";

            if(!empty($sucursal) && intVal($sucursal) != 0) {
                $and_id_sucursal .= " AND f.id_sucursal= $sucursal";
            }else{
                $and_id_sucursal .="";
            };

            $db->setQuery("SELECT 
                                fp.id_factura,
                                CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                                DATE_FORMAT(fecha_venta,'%d/%m/%Y %H:%i:%s') AS fecha,
                                fp.producto,
                                fp.lote,
                                fp.cantidad,
                                fp.`precio`,
                                fp.total_venta AS total
                            FROM facturas_productos fp
                            LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                            LEFT JOIN timbrados t ON f.`id_timbrado`=t.`id_timbrado`
                            WHERE f.id_cliente=$id_cliente AND f.estado != 2 AND f.fecha_venta BETWEEN '$desde' AND '$hasta' $and_id_sucursal");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;
	}

?>
