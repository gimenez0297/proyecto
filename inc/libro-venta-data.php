<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q          = $_REQUEST["q"];
    $usuario    = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;


    switch ($q) {
        
        case "ver":
        $db = DataBase::conectar();
        $limit      = $_REQUEST['limit'];
        $offset     = $_REQUEST['offset'];
        $order      = $_REQUEST['order'];
        $sort       = $_REQUEST['sort'];
        $desde 		= $_REQUEST["desde"];
        $hasta 		= $_REQUEST["hasta"];
        $estado     = $_REQUEST['estado'];
        $id_cliente = $_REQUEST['id_cliente'];

        if($estado == 3 || empty($estado)) {
            $where .="";
        }else{
            $where .= "AND f.estado = $estado";
        }
    
        if(empty($desde)) {
            $where_fecha .= "WHERE f.fecha_venta BETWEEN '2022-01-01' AND NOW()";
            $desde = '2021-01-01';
            $hasta = date('Y-m-d');
        }else{
            $where_fecha .=" WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta'";
        };
    
        if(intVal($id_cliente) != 0 ) {
            $where_id_cliente .= " AND f.id_cliente=$id_cliente";
            $db->setQuery("SELECT * FROM clientes WHERE id_cliente =$id_cliente");
            $row = $db->loadObject();
            $id_cliente = $row->id_cliente;
        }else{
            $where_id_cliente .="";
            $cliente = "Todos";
        };
    
        if($estado == 0) {$estado = "Pendiente";}
        if($estado == 1) {$estado = "Pagado";}
        if($estado == 2) {$estado = "Anulado";}
        if($estado == 3) {$estado = "Todos";}
    
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS   
                                f.total_venta, 
                                f.razon_social,
                                f.ruc,
                                f.gravada_10,
                                f.gravada_5,
                                f.exenta,
                                t.timbrado,
                                '1' AS tipo_reg,
                                f.condicion,
                                CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',f.numero) AS nro_factura,
                                CASE COALESCE((f.ruc LIKE '%-%'),0)
                                WHEN 0 THEN '12'
                                ELSE '11' END AS 'tipo_identificacion',
                                '109' AS tipo_comprobante,
                                'N' AS moneda_extranjera, 
                                'S' AS imputa_iva,
                                'N' AS imputa_ire,
                                'N' AS imputa_irp,
                                '0' AS nro_comp_asociada,
                                '0' AS nro_timb_asociada,
                                DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha,
                                CASE f.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Anulado' END AS estado_str,                        
                                CASE f.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion_str
                                FROM facturas f
                                LEFT JOIN timbrados t ON t.id_timbrado = f.id_timbrado
                                $where_fecha  $where_id_cliente $where
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