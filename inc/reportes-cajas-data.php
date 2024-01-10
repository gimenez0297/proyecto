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
        $id_cajero = $db->clearText($_REQUEST['id_cajero']);
        $desde    = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta    = $db->clearText($_REQUEST['hasta']) . " 23:59:59";

        $where = "";

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
            $having = "HAVING CONCAT_WS(' ', f.funcionario) LIKE '%$search%'";
        }

        if(intVal($id_cajero) != 0 ) {
            $where_id_cajero .= " AND f.id_funcionario=$id_cajero";
            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario =$id_cajero");
            $row = $db->loadObject();
            $id_cajero = $row->id_funcionario;
        }else{
            $where_id_cajero .="";
        };

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND c.id_sucursal=$sucursal";
        }else{
            $where_sucursal .="";
        };

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            ch.id_caja_horario,
                            c.numero,
                            f.`funcionario` AS cajero,
                            s.sucursal,
                            DATE_FORMAT(ch.fecha_apertura,'%d/%m/%Y %H:%i:%s') apertura,
                            DATE_FORMAT(ch.fecha_cierre,'%d/%m/%Y %H:%i:%s') cierre,
                            ch.monto_apertura,
                            ch.monto_servicios,
                            IFNULL(ce.monto_ext,0) AS sobre,
                            (SELECT IFNULL(SUM(c.monto), 0) AS monto 
                                FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura 
                                WHERE c.estado=1 AND c.id_metodo_pago=1 
                                AND f.id_caja_horario=ch.id_caja_horario) AS total_efectivo,
                            (SELECT (IFNULL(SUM(c.monto), 0) -  (SELECT 
                                                            IFNULL(SUM(nc.total), 0) 
                                                        FROM notas_credito nc 
                                                        WHERE nc.id_caja_horario = ch.id_caja_horario
                                                        AND nc.devolucion_importe = 1) ) AS monto 
                                FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura 
                                WHERE c.estado=1 AND f.id_caja_horario=ch.id_caja_horario) AS venta_sistema,
                                (SELECT
                                    IF(cd.monto_ca IS NULL,0,((SUM(c.monto) - IFNULL(ch.devolucion_importe,0))-(IFNULL((SELECT IFNULL(SUM(total), 0) + 
                                                    (
                                                        SELECT IFNULL(SUM(c.monto), 0) AS monto
                                                        FROM cobros c
                                                        JOIN facturas f ON c.id_factura=f.id_factura
                                                        WHERE c.estado=1 AND c.id_metodo_pago != 1 AND f.id_caja_horario=cd.id_caja_horario
                                                    ) AS monto
                                                    FROM cajas_detalles cd
                                                    WHERE tipo=0 AND id_caja_horario=ch.id_caja_horario), 0) + IFNULL(ex.extra,0))))
                            FROM cobros c
                            JOIN facturas f ON c.id_factura=f.id_factura
                            JOIN (SELECT IFNULL(SUM(total), 0) AS monto_ca, id_caja_horario FROM cajas_detalles WHERE tipo=0 GROUP BY id_caja_horario ) cd
                            ON f.`id_caja_horario`=cd.id_caja_horario
                            LEFT JOIN (SELECT IFNULL(SUM(monto_extraccion), 0) AS extra, id_caja_horario FROM cajas_extracciones GROUP BY id_caja_horario) ex
                            ON f.`id_caja_horario`=ex.id_caja_horario
                            WHERE c.estado=1 AND f.id_caja_horario=ch.id_caja_horario
                            GROUP BY f.id_caja_horario) AS diferencia
                        FROM cajas_horarios ch
                        LEFT JOIN cajas c ON ch.id_caja=c.id_caja
                        LEFT JOIN users cu ON ch.usuario = cu.`username`
                        LEFT JOIN funcionarios f ON f.id_usuario = cu.id
                        LEFT JOIN sucursales s ON s.id_sucursal=ch.id_sucursal
                        LEFT JOIN (SELECT *, SUM(monto_extraccion) AS monto_ext FROM cajas_extracciones GROUP BY id_caja_horario) ce ON ch.id_caja_horario=ce.id_caja_horario
                        WHERE ch.fecha_apertura BETWEEN '$desde' AND '$hasta' $where_sucursal $where_id_cajero $having
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

    case 'ver_productos':
        $db          = DataBase::conectar();
        $id_producto = $db->clearText($_GET['id_producto']);
        $sucursal = $db->clearText($_GET['id_sucursal']);
        $desde    = $db->clearText($_GET['desde']) . " 00:00:00";
        $hasta    = $db->clearText($_GET['hasta']) . " 23:59:59";

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                f.id_factura,
                                DATE_FORMAT(f.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                                CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                                fp.producto,
                                fp.fraccionado,
                                fp.cantidad,
                                fp.precio,
                                fp.total_venta
                            FROM facturas_productos fp
                            LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                            LEFT JOIN timbrados t ON f.id_timbrado = t.id_timbrado
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                            WHERE p.id_producto=$id_producto AND p.controlado=1 AND f.id_sucursal=$sucursal AND f.fecha BETWEEN '$desde' AND '$hasta'");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;
}
