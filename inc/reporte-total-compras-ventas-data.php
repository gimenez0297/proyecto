<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();

switch ($q) {
    case 'ver':
        $db           = DataBase::conectar();

        //Parametros de ordenamiento, busqueda y paginacion
        $limit          = $db->clearText($_REQUEST['limit']);
        $offset         = $db->clearText($_REQUEST['offset']);
        $order          = $db->clearText($_REQUEST['order']);
        $sort           = ($db->clearText($_REQUEST['sort'])) ?: 2;
        $id_sucursal    = $_REQUEST['id_sucursal'];
        $desde 		    = $_REQUEST["desde"];
        $hasta 		    = $_REQUEST["hasta"];
        $reporte 		= $_REQUEST["reporte"];

        if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
            if ($reporte==1) {
                $and_id_sucursal .= " AND g.id_sucursal=$id_sucursal";
            }else{
                $and_id_sucursal .= " AND f.id_sucursal=$id_sucursal";
            }     
        }else{
            $and_id_sucursal .="";
        };


        if ($reporte == 1) {
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                g.ruc,
                                g.razon_social,
                                g.timbrado,
                                g.documento,
                                g.gravada_10,
                                g.gravada_5,
                                g.exenta,
                                g.monto,
                                DATE_FORMAT(g.fecha_emision,'%d/%m/%Y') AS fecha_emision,
                                CASE g.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion   
                                FROM gastos g
                                LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                                WHERE DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta' $and_id_sucursal
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
        }else{
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS   
                                f.total_venta AS monto, 
                                f.razon_social,
                                f.ruc,
                                f.gravada_10,
                                f.gravada_5,
                                f.exenta,
                                t.timbrado,
                                CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',f.numero) AS documento,
                                DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha_emision,
                                CASE f.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion
                                FROM facturas f
                                LEFT JOIN timbrados t ON t.id_timbrado = f.id_timbrado
                                WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' $and_id_sucursal
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
        }
        
        

    }