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

        if ($periodo == 1) {
            $where = "";
        } else {
            $where = " AND ls.periodo='$periodo'";
        }

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (empty($limit)) {
            $limit_range = "";
        } else {
            $limit_range = "LIMIT $offset, $limit";
        }       
        
        if (isset($search) && !empty($search)) {
            $having = "HAVING funcionario LIKE '$search%' OR sucursal LIKE '$search%'";
        }

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND s.id_sucursal=$sucursal";
        }else{
            $where_sucursal .="";
        };

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT
                                        s.sucursal,
                                        f.id_funcionario,
                                        f.funcionario,
                                        ls.id_liquidacion,
                                        ant.monto AS anticipo,
                                        pre.monto AS prestamo,
                                        ot.monto AS otros_descuentos,
                                        DATE_FORMAT(f.fecha_alta,'%d/%m/%Y') AS fecha_ingreso,
                                        IFNULL(f.salario_real,0) AS salario,
                                        ROUND(IF(aporte = 1, IFNULL(f.salario_real,0)*0.09, 0)) AS retencion_ips,
                                        ROUND(IF(aporte = 2, IFNULL(f.salario_real,0)*0.11, 0)) AS iva,
                                        ROUND(IF(aporte = 1, IFNULL(f.salario_real,0)*0.165, 0)) AS aporte_patronal,
                                        ROUND(IF(lsi.`concepto`='EXTRA', lsi.importe,0)) AS extra,
                                        IFNULL(lsd.total_des,0) AS total_descuento,
                                        ROUND(IFNULL((SELECT importe
                                            FROM liquidacion_salarios_ingresos lss
                                            LEFT JOIN liquidacion_salarios li ON lss.id_liquidacion=li.id_liquidacion
                                            WHERE lss.id_liquidacion=ls.id_liquidacion AND lss.concepto='COMISIÓN' AND li.id_funcionario=ls.`id_funcionario` GROUP BY li.periodo,li.id_funcionario),0)) comision,
                                        ROUND(((f.salario_real-IFNULL(ips.monto,0))-IFNULL(lsd.total_des,0))+ROUND(IF(lsi.`concepto`='EXTRA', IFNULL(lsi.importe,0),0))+ROUND(IFNULL((SELECT importe
                                            FROM liquidacion_salarios_ingresos lss
                                            LEFT JOIN liquidacion_salarios li ON lss.id_liquidacion=li.id_liquidacion
                                            WHERE lss.id_liquidacion=ls.id_liquidacion AND lss.concepto='COMISIÓN' AND li.id_funcionario=ls.`id_funcionario` GROUP BY li.periodo,li.id_funcionario),0))) AS acreditado

                                    FROM  funcionarios f
                                    LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                                    LEFT JOIN liquidacion_salarios ls ON f.id_funcionario=ls.id_funcionario
                                    LEFT JOIN (SELECT l.*, SUM(importe) AS total_des FROM liquidacion_salarios_descuentos l WHERE concepto != 'I.P.S 9%' GROUP BY id_liquidacion) lsd ON ls.id_liquidacion=lsd.id_liquidacion
                                    LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto = 'I.P.S 9%' GROUP BY id_liquidacion) ips ON ls.id_liquidacion=ips.id_liquidacion
                                    LEFT JOIN liquidacion_salarios_ingresos lsi ON ls.id_liquidacion=lsi.id_liquidacion
                                    LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto = 'ANTICIPO' GROUP BY id_liquidacion) ant ON ls.id_liquidacion=ant.id_liquidacion
                                    LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto = 'PRESTAMO' GROUP BY id_liquidacion) pre ON ls.id_liquidacion=pre.id_liquidacion
                                    LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto != 'PRESTAMO' AND concepto != 'ANTICIPO' AND concepto != 'I.P.S 9%' GROUP BY id_liquidacion) ot ON ls.id_liquidacion=ot.id_liquidacion
                                    WHERE ls.estado != 2 $where_sucursal $where
                                    GROUP BY f.id_funcionario, ls.periodo) a
                        GROUP BY id_funcionario
                        $having
                        ORDER BY $sort $order
                        $limit_range");
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

    case 'ver_liquidacion_ingreso':
        $db = DataBase::conectar();
        $id_liquidacion = $db->clearText($_GET['id_liquidacion']);

        $db->setQuery("SELECT 
                            id_liquidacion_ingreso,
                            id_liquidacion,
                            concepto,
                            importe,
                            observacion
                        FROM liquidacion_salarios_ingresos
                        WHERE id_liquidacion=$id_liquidacion");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;

    case 'ver_liquidacion_descuento':
        $db = DataBase::conectar();
        $id_liquidacion = $db->clearText($_GET['id_liquidacion']);

        $db->setQuery("SELECT 
                            id_liquidacion_descuento,
                            id_liquidacion,
                            concepto,
                            importe,
                            observacion
                        FROM liquidacion_salarios_descuentos
                        WHERE id_liquidacion=$id_liquidacion");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;
}
