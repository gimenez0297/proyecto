<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q           = $_REQUEST["q"];
    $usuario     = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;


    

    switch ($q) {
        
        case "ver":
        $db = DataBase::conectar();
        $limit        = $_REQUEST['limit'];
        $offset       = $_REQUEST['offset'];
        $order        = $_REQUEST['order'];
        $sort         = $_REQUEST['sort'];
        $desde 		  = $_REQUEST["desde"];
        $hasta 		  = $_REQUEST["hasta"];
        $proveedor    = $_REQUEST['proveedor'];


        if(empty($desde)) {
            $where_fecha .= "WHERE g.fecha_emision BETWEEN '2022-01-01' AND NOW()";
            $desde        = '2021-01-01';
            $hasta        = date('Y-m-d');
        }else{
            $where_fecha .=" WHERE DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta'";
        };
    
        if(!empty($proveedor) && intVal($proveedor) != 0) {
            $where_id_proveedor .= " AND g.ruc='$proveedor'";
        }else{
            $where_id_proveedor .="";
            $proveedor_pdf           = "Todos";
        };
    
  
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                '2' AS tipo_registro,
				                CASE COALESCE((g.ruc LIKE '%-%'),0)
                                WHEN 0 THEN '12'
                                ELSE '11' END AS 'tipo_identificacion',
                                g.ruc,
                                g.razon_social,
                                tc.codigo,
                                g.fecha_emision,
                                DATE_FORMAT(g.fecha_emision,'%d/%m/%Y') AS fecha_emision_str,
				                g.timbrado,
                                g.documento,
                                g.gravada_10,
                                g.gravada_5,
                                g.exenta,
                                g.monto,
                                'N' AS moneda_extranjera,
                                CASE g.imputa_iva WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS imputa_iva,
                                CASE g.imputa_ire WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS imputa_ire,
                                CASE g.imputa_irp WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS imputa_irp,
                                CASE g.no_imputa WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS no_imputa,
                                g.condicion,
                                g.nro_comprobante_venta_asoc,
                                g.timb_compro_venta_asoc
                                FROM gastos g
                                LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                                $where_fecha AND g.ruc != 'S/R' $where_id_proveedor
                                ORDER BY $sort $order
                                LIMIT $offset, $limit");
        $rows = $db->loadObjectList();

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total     = $total_row->total;

        if ($rows) {
            $salida = array('total' => $total, 'rows' => $rows);
        } else {
            $salida = array('total' => 0, 'rows' => array());
        }

        echo json_encode($salida);
    break;
    
    }