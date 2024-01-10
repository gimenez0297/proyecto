<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$id_sucursal = datosUsuario($usuario)->id_sucursal;

switch ($q) {

    case 'ver':
        $db       = DataBase::conectar();
        $sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $periodo  = $db->clearText($_REQUEST['periodo']);
        $desde    = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta    = $db->clearText($_REQUEST['hasta']) . " 23:59:59";

        if($periodo == 'null'){
            $where = " AND CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta))=CONCAT(UCASE(mes(CURRENT_DATE,'es_ES')),' - ',YEAR(CURRENT_DATE))";
        }else{
            $where = " AND CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta))='$periodo'";
        }

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "AND CONCAT_WS(' ', fu.funcionario, mes(f.fecha_venta,'es_ES')) LIKE '%$search%'";
        }

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= "  AND f.id_sucursal=$sucursal";
        }else{
            $where_sucursal .="";
        };

        $db->setQuery("SELECT IFNULL(utilidad,0) AS utilidad FROM configuracion");
        $utilidad = $db->loadObject()->utilidad;

        $db->setQuery("SELECT
                            IFNULL(fu.funcionario, u.nombre_apellido) AS vendedor,
                            f.usuario,
                            sum(f.total_venta) as total_venta,
                            f.fecha_venta,
                            f.id_factura,
                            SUM(fp.monto_comision) AS monto_comision,
                            CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta)) AS periodo

                        FROM facturas f
                        LEFT JOIN (SELECT id_factura, SUM(ROUND((total_venta*IFNULL(comision,0))/100)) AS monto_comision FROM facturas_productos GROUP BY id_factura) fp ON fp.id_factura=f.id_factura
                        LEFT JOIN users u ON f.usuario=u.username
                        LEFT JOIN funcionarios fu ON u.id=fu.id_usuario
                        WHERE f.estado != 2 AND ROUND((IFNULL((
                                SELECT SUM(total_venta) - SUM(total_costo) 
                                FROM facturas 
                                WHERE id_factura = f.id_factura
                            ), 0) / f.total_venta) * 100, 2) >= '$utilidad' $where_sucursal $where $having
                        GROUP BY MONTH(DATE(f.fecha_venta)), f.usuario
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

    case 'ver_detalle':
        $db      = DataBase::conectar();
        $id_funcionario = $db->clearText($_REQUEST['funcionario']);
        $periodo = $db->clearText($_REQUEST['periodo']);

        $db->setQuery("SELECT IFNULL(utilidad,0) AS utilidad FROM configuracion");
        $utilidad = $db->loadObject()->utilidad;

        $db->setQuery("SELECT 
                            fu.id_funcionario,
                            CONCAT(IFNULL(fp.comision,0),'','%') AS comision,
                            fu.funcionario,
                            CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta)) AS periodo,
                            SUM(fp.total_venta) AS monto_comision,
                            ROUND((SUM(fp.total_venta)*IFNULL(fp.comision,0))/100) AS porcentaje_com,
                            GROUP_CONCAT(DISTINCT fp.`comision_concepto` SEPARATOR ' | ') AS comision_concepto
                        FROM facturas_productos fp
                        LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                        LEFT JOIN users u ON f.usuario=u.username
                        LEFT JOIN funcionarios fu ON u.id=fu.id_usuario 
                        WHERE f.`estado`!=2 AND (fu.funcionario = '$id_funcionario' OR u.nombre_apellido = '$id_funcionario') AND CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta)) ='$periodo' AND ROUND((IFNULL((
                                SELECT SUM(total_venta) - SUM(total_costo) 
                                FROM facturas 
                                WHERE id_factura = f.id_factura
                            ), 0) / f.total_venta) * 100, 2) >= '$utilidad'
                        GROUP BY MONTH(DATE(f.fecha_venta)),YEAR(DATE(f.fecha_venta)), f.`usuario`,fp.comision");
        $rows = ($db->loadObjectList()) ?: [];

        echo json_encode($rows);
        break;
}
