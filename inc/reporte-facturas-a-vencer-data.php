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
        $condicion 		= $_REQUEST["reporte"];

        $where_fecha = "";
        $and_id_sucursal ="";
        $and_id_sucursal_fun ="";
        $where_fecha_fun = "";

        if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
            $and_id_sucursal = "AND g.id_sucursal=$id_sucursal";
            $and_id_sucursal_fun = " AND f.id_sucursal=$id_sucursal";       
        }

        if ((isset($desde) && !empty($desde)) || (isset($hasta) && !empty($hasta))) {
            $where_fecha = " AND DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta'";
            $where_fecha_fun = " AND DATE(ls.fecha) BETWEEN '$desde' AND '$hasta'";
        }

        $db->setQuery("SELECT 
                            'GASTOS' AS descripcion,
                            g.ruc,
                            g.razon_social,
                            g.monto AS total,
                            DATE_FORMAT(g.fecha_emision,'%d/%m/%Y') AS fecha_emision,
                            CASE g.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion
                        FROM gastos g
                        LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                        WHERE g.estado in(0,1) $and_id_sucursal $where_fecha

                        UNION ALL

                        SELECT 
                            'SALARIOS' AS descripcion,
                            ls.ci AS ruc,
                            ls.funcionario AS razon_social,
                            neto_cobrar AS total,
                            DATE_FORMAT(ls.fecha,'%d/%m/%Y') AS fecha_emision,
                            'CONTADO' AS condicion
                        FROM liquidacion_salarios ls
                        LEFT JOIN funcionarios f ON ls.ci=f.ci
                        WHERE ls.estado = 1 $and_id_sucursal_fun $where_fecha_fun
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