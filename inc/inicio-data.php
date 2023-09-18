<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q = $_REQUEST['q'];

switch ($q) {
    case 'usuario':

        $usuario     = $auth->getUsername();
        $data = datosUsuario($usuario);
        echo json_encode(["status" => "ok", "usuario" => $data]);

    break;

    case 'recuperar_calendario':
        $db = DataBase::conectar();
        $db->setQuery("SELECT MAX(ano) AS maximo_anho, MIN(ano) AS minimo_anho, COUNT(*) AS fechas FROM calendario WHERE YEAR(NOW()) >= ano ");
        $row_calendario = $db->loadObject();
        
        echo json_encode(["status" => "ok", "calendario" => $row_calendario]);
    break;

    case 'productos_mas_vendidos':
        $db = DataBase::conectar();
        $desde = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta = $db->clearText($_REQUEST['hasta']) . " 23:59:59"; 

        $db->setQuery("SELECT 
                            a.*, 
                            CONCAT('Total:',cantidad,'<br>Enteros:',entero,'Fraccionado:',fraccionado) AS text_mensaje 
                        FROM (SELECT
                                fp.id_producto,
                                p.producto,
                                SUM(fp.cantidad) AS cantidad,
                                SUM(IF(fp.fraccionado = 1, fp.cantidad,0)) AS fraccionado,
                                SUM(IF(fp.fraccionado != 1, fp.cantidad,0)) AS entero 
                            FROM
                            facturas_productos fp
                            LEFT JOIN productos p ON p.id_producto = fp.id_producto
                            LEFT JOIN facturas f ON f.id_factura = fp.id_factura
                            WHERE f.fecha_venta BETWEEN '$desde' AND '$hasta' AND f.estado = 1
                            GROUP BY fp.id_producto
                            ORDER BY cantidad DESC 
                            LIMIT 5)a");
        $rows = $db->loadObjectList();

        echo json_encode($rows);
    break;

    case 'ventas_sucursal':
        $db = DataBase::conectar();
        $desde = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta = $db->clearText($_REQUEST['hasta']) . " 23:59:59"; 

        $db->setQuery("SELECT*FROM ((SELECT
                            f.id_sucursal,
                            'TOTAL VENTA' AS sucursal,
                            SUM(f.total_venta) AS venta
                        FROM
                            facturas f
                        LEFT JOIN sucursales s ON s.id_sucursal = f.id_sucursal
                        WHERE f.fecha_venta BETWEEN '$desde' AND '$hasta' AND f.estado = 1)
                        UNION ALL
                        (SELECT
                            f.id_sucursal,
                            s.sucursal,
                            SUM(f.total_venta) AS venta
                        FROM
                            facturas f
                        LEFT JOIN sucursales s ON s.id_sucursal = f.id_sucursal
                        WHERE f.fecha_venta BETWEEN '$desde' AND '$hasta' AND f.estado = 1
                        GROUP BY f.id_sucursal)
                        )a ORDER BY venta DESC ");
        $rows = $db->loadObjectList();

        echo json_encode($rows);
    break;

    case 'gastos_meses':
        $db = DataBase::conectar();
        $anio = $db->clearText($_REQUEST['anio']);

        $meses = [1,2,3,4,5,6,7,8,9,10,11,12];

        $db->setQuery("SELECT * FROM tipos_gastos WHERE estado = 1");
        $tipos_gastos = $db->loadObjectList();

        $resultado = [];
        foreach ($tipos_gastos as $tg) {
            $id_tipo_gasto = $tg->id_tipo_gasto;
            $nombre = $tg->nombre;

            $montos = array_reduce($meses, function($acc, $mes) use ($db, $id_tipo_gasto, $anio){

                $db->setQuery("SELECT
                                    SUM(g.monto) AS monto
                                FROM
                                    gastos g
                                WHERE g.id_tipo_gasto =$id_tipo_gasto  AND MONTH(g.fecha_emision) = $mes  AND YEAR(g.fecha_emision) = $anio");
                $monto_g = (int) $db->loadObject()->monto;

                if ($monto_g == 0) {
                    $monto_g = null;
                }
                $acc[] = $monto_g;
                return $acc;
            }, []);

            $resultado[] = (object) ['name' => $nombre, 'data' => $montos];
        }
       
        echo json_encode($resultado);

    break;

    case 'ganancias_meses':
        $db = DataBase::conectar();
        $anho = $db->clearText($_REQUEST['anho']);

        $meses = [1,2,3,4,5,6,7,8,9,10,11,12];

        $db->setQuery("SELECT * FROM sucursales");
        $sucursales_row = $db->loadObjectList();

        $resultado = [];
        foreach ($sucursales_row as $rc) {
            $id_sucursal = $rc->id_sucursal;
            $nombre = $rc->sucursal;

            $montos = array_reduce($meses, function($acc, $mes) use ($db, $id_sucursal, $anho){

                $db->setQuery("SELECT
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
                                    WHERE MONTH(fecha_venta) = '$mes' AND YEAR(fecha_venta) = '$anho' AND estado = 1
                                    GROUP BY id_sucursal
                                ) f ON s.id_sucursal = f.id_sucursal
                                LEFT JOIN (
                                    SELECT 
                                        id_sucursal, 
                                        SUM(monto) AS total_g 
                                    FROM gastos 
                                    WHERE MONTH(fecha_emision) = '$mes' AND YEAR(fecha_emision) = '$anho'  AND estado IN(1,2) 
                                    GROUP BY id_sucursal 
                                ) g ON s.id_sucursal = g.id_sucursal HAVING id_sucursal = '$id_sucursal' ");
                $monto_g = (int) $db->loadObject()->ganancias;
                // $monto_g = (int) $db->loadObject()->monto;

                if ($monto_g == 0) {
                    $monto_g = null;
                }
                $acc[] = $monto_g;
                return $acc;
            }, []);

            $resultado[] = (object) ['name' => $nombre, 'data' => $montos];
        }
       
        echo json_encode($resultado);

    break;

    case 'ver_productos_vencer':
        $db       = DataBase::conectar();
        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        $desde 		        = $_REQUEST["desde"];
        $hasta 		        = $_REQUEST["hasta"];

        $where = "";
        if (isset($search) && !empty($search)) {
            $having = "";
        }

        //PARA MOSTRAR EL DETALLE O CANTIDAD EXACTA DE PRODUCTOS EN CADA DIA
        $db->setQuery("SELECT 
                            SUM(IF(TIMESTAMPDIFF(DAY, DATE(NOW()), l.vencimiento) = 3,1,0)) AS _3_dias,
                            SUM(IF(TIMESTAMPDIFF(DAY, DATE(NOW()), l.vencimiento) = 4,1,0)) AS _4_dias,
                            SUM(IF(TIMESTAMPDIFF(DAY, DATE(NOW()), l.vencimiento) = 5,1,0)) AS _5_dias,
                            SUM(IF(TIMESTAMPDIFF(DAY, DATE(NOW()), l.vencimiento) = 7,1,0)) AS _7_dias,
                            SUM(IF(TIMESTAMPDIFF(DAY, DATE(NOW()), l.vencimiento) = 30,1,0)) AS _30_dias
                        FROM lotes l
                        WHERE 1=1 AND DATE(l.vencimiento) BETWEEN '$desde' AND '$hasta'");
        $row_cant = $db->loadObject();
        $_3_dias    = $row_cant->_3_dias ?: 0;        
        $_4_dias    = $row_cant->_4_dias ?: 0;        
        $_5_dias    = $row_cant->_5_dias ?: 0;        
        $_7_dias    = $row_cant->_7_dias ?: 0;        
        $_30_dias   = $row_cant->_30_dias ?: 0;            

        $db->setQuery("SELECT 
                            SQL_CALC_FOUND_ROWS
                            $_3_dias AS _3_dias,
                            $_4_dias AS _4_dias,
                            $_5_dias AS _5_dias,
                            $_7_dias AS _7_dias,
                            $_30_dias AS _30_dias,
                            l.id_lote,
                            l.lote,
                            l.canje,
                            l.usuario,
                            p.producto,
                            IF(l.canje=1,'Si','No') AS canje_str,
                            DATE_FORMAT(l.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote,
                            DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y') AS vencimiento_canje,
                            CASE 
                                WHEN l.vencimiento >= CURRENT_DATE() THEN 'Activo' 
                                ELSE 'Vencido' 
                            END AS estado_lote,
                            IF(l.canje=1, IF(l.vencimiento_canje >= CURRENT_DATE(),'Activo','Vencido'),'Sin Canje') AS estado_canje,
                            IFNULL(s.entero,0) AS entero,
                            IFNULL(s.fraccionado_st,0) AS fracc,
                            pr.presentacion,
                            pa.nombre AS principio_activo,
                            cp.id_clasificacion_producto,
                            cp.clasificacion,
                            um.unidad_medida,
                            r.rubro,
                            m.marca,
                            la.laboratorio,
                            t.tipo,
                            o.origen,
                            p.precio AS costo,
                            TIMESTAMPDIFF(DAY, DATE(NOW()), l.vencimiento) AS vencimiento_dia
                        FROM lotes l
                        LEFT JOIN (SELECT *, SUM(stock) AS entero, SUM(fraccionado) AS fraccionado_st FROM stock GROUP BY id_lote) s ON l.id_lote=s.id_lote
                        LEFT JOIN productos p ON s.id_producto=p.id_producto
                        LEFT JOIN marcas m ON m.id_marca=p.id_marca
                        LEFT JOIN tipos_productos t ON t.id_tipo_producto = p.id_tipo_producto
                        LEFT JOIN productos_clasificaciones pc ON  p.id_producto = pc.id_producto
                        LEFT JOIN clasificaciones_productos cp ON pc.id_clasificacion=cp.id_clasificacion_producto
                        LEFT JOIN laboratorios la ON la.id_laboratorio= p.id_laboratorio
                        LEFT JOIN origenes o ON o.id_origen=p.id_origen
                        LEFT JOIN presentaciones pr ON pr.id_presentacion=p.id_presentacion
                        LEFT JOIN productos_principios pp ON pp.id_producto=p.id_producto
                        LEFT JOIN principios_activos pa ON pa.id_principio=pp.id_principio
                        LEFT JOIN unidades_medidas um ON um.id_unidad_medida=p.id_unidad_medida
                        LEFT JOIN rubros r ON r.id_rubro=p.id_rubro
                        WHERE 1=1 AND DATE(l.vencimiento) BETWEEN '$desde' AND '$hasta'
                        -- GROUP BY l.id_lote
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
