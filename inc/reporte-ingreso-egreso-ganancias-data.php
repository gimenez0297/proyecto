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
        $desde 		  = $_REQUEST["desde"];
        $hasta 		  = $_REQUEST["hasta"];
        // $id_sucursal  = $_REQUEST["sucursal"];


        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            s.id_sucursal, 
                            s.sucursal, 
                            IFNULL(f.total_venta, 0) AS total_venta, 
                            IFNULL(f.total_costo, 0) AS total_costo, 
                            IFNULL(f.utilidad, 0) AS utilidad,
                            ROUND((IFNULL(f.utilidad, 0) / IFNULL(f.total_venta, 0)) * 100, 2) AS porcentaje_utilidad,
                            IFNULL(g.total_g, 0) AS total_gastos,
                            IFNULL(f.utilidad, 0) - IFNULL(g.total_g, 0) AS ganancias
                        FROM sucursales s
                        LEFT JOIN (
                            SELECT 
                                id_sucursal, 
                                SUM(total_venta) AS total_venta, 
                                SUM(total_costo) AS total_costo, 
                                SUM(total_venta) - SUM(total_costo) AS utilidad 
                            FROM facturas 
                            WHERE DATE(fecha_venta) BETWEEN '$desde' AND '$hasta'
                            GROUP BY id_sucursal
                        ) f ON s.id_sucursal = f.id_sucursal
                        LEFT JOIN (
                            SELECT 
                                id_sucursal, 
                                SUM(monto) AS total_g 
                            FROM gastos 
                            WHERE DATE(fecha_emision) BETWEEN '$desde' AND '$hasta' AND estado IN(1,2) 
                            GROUP BY id_sucursal 
                        ) g ON s.id_sucursal = g.id_sucursal");
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

    case 'ver_ingresos':
        $db = DataBase::conectar();
        $id_sucursal = $db->clearText($_GET['id_sucursal']);
        $desde = $db->clearText($_GET['desde']);
        $hasta = $db->clearText($_GET['hasta']);
        
        $db->setQuery("SELECT 
                            DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha_emision,
                            CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, f.numero) AS nro_documento,
                            f.id_factura,
                            f.razon_social,
                            f.total_venta,
                            f.total_costo,
                            (
                                SELECT SUM(total_venta) - SUM(total_costo) 
                                FROM facturas 
                                WHERE id_factura = f.id_factura
                            ) AS utilidad,
                            ROUND((IFNULL((
                                SELECT SUM(total_venta) - SUM(total_costo) 
                                FROM facturas 
                                WHERE id_factura = f.id_factura
                            ), 0) / f.total_venta) * 100, 2) AS porcentaje_utilidad
                        FROM facturas f
                        LEFT JOIN timbrados t ON t.id_timbrado=f.id_timbrado
                        WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' 
                            AND f.id_sucursal = $id_sucursal");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;

    case 'ver_egresos':
        $db = DataBase::conectar();
        $id_sucursal = $db->clearText($_GET['id_sucursal']);
        $desde = $db->clearText($_GET['desde']);
        $hasta = $db->clearText($_GET['hasta']);

        $db->setQuery("SELECT 
                        DATE_FORMAT(fecha_emision, '%d/%m/%Y') AS fecha_emision,
                        id_gasto,
                        documento AS nro_documento,
                        razon_social,
                        monto AS total_venta
                    FROM gastos 
                    WHERE fecha_emision BETWEEN '$desde' AND '$hasta'
                        AND id_sucursal = $id_sucursal 
                        AND estado IN (1, 2)");          
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;
        
}
