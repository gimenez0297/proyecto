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
        
        case 'cargar':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id_proveedor = $db->clearText($_POST['id_proveedor']);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $numero = $db->clearText($_POST['numero']);
            $productos = json_decode($_POST['productos'], true);
            
            if (empty($id_proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);
                exit;
            }
            if (empty($productos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún producto agregado. Favor verifique."]);
                exit;
            }

            $db->setQuery("SELECT MAX(numero) AS numero FROM solicitudes_compras");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero_sgte = zerofill($sgte_numero);

            // Cabeceera
            $db->setQuery("INSERT INTO solicitudes_compras (numero, id_proveedor, observacion, estado, usuario, fecha)
                            VALUES ('$numero_sgte','$id_proveedor','$observacion','0','$usuario',NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            $id_solicitud_compra = $db->getLastID();

            foreach ($productos as $p) {
                $id_producto = $db->clearText($p["id_producto"]);
                $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
                
                if ($cantidad <= 0) {
                    echo json_encode(["status" => "error", "mensaje" => "No puede cargar producto/s con cantidad 0. Favor verifique."]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
                $db->setQuery("INSERT INTO solicitudes_compras_productos (id_solicitud_compra, id_producto, cantidad)
                                VALUES ('$id_solicitud_compra','$id_producto','$cantidad')");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Solicitud de compra registrada correctamente"]);
        break;

        case 'recuperar-numero':
            $db       = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM solicitudes_compras");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

        case 'stock-minimo':
            $db = DataBase::conectar();
            $limit = $db->clearText($_REQUEST['limit']);
            $offset    = $db->clearText($_REQUEST['offset']);
            $search = $db->clearText($_REQUEST['search']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            $id_proveedor = $db->clearText($_REQUEST['id_proveedor']);
            if (isset($search) && !empty($search)) {
                $search_ = "AND p.codigo LIKE '$search%' OR p.producto LIKE '$search%' OR  pr.presentacion LIKE '$search%'";
            }

            if(!empty($id_proveedor) && intVal($id_proveedor) != 0) {
                $where_proveedor .= " WHERE pp.id_proveedor = $id_proveedor";
            }else{
                $where_proveedor .="";
            };

            $sql_limit ="";
            if (isset($_REQUEST['limit']) && isset($_REQUEST['offset'])) {
                $sql_limit .= "LIMIT $offset, $limit";
            }
           
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            p.id_producto,
                            p.codigo,
                            p.producto,  
                            pr.presentacion, 
                            (
                                SELECT 
                                IFNULL(SUM(s.stock), 0)
                                FROM stock s 
                                JOIN lotes l ON s.id_lote=l.id_lote
                                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
                            ) AS stock,
                            IF(IFNULL(sn.maximo - (SELECT 
                                    IFNULL(SUM(s.stock), 0)
                                    FROM stock s 
                                    JOIN lotes l ON s.id_lote=l.id_lote
                                    WHERE s.id_producto=p.id_producto AND s.id_sucursal=1 AND vencimiento>=CURRENT_DATE()),0
                            ) < 0, 0,IFNULL(sn.maximo - (SELECT 
                                    IFNULL(SUM(s.stock), 0)
                                    FROM stock s 
                                    JOIN lotes l ON s.id_lote=l.id_lote
                                    WHERE s.id_producto=p.id_producto AND s.id_sucursal=1 AND vencimiento>=CURRENT_DATE()),0
                            )) AS recomendado,
                            IFNULL(sn.minimo, 0) AS minimo,
                            IFNULL(sn.maximo, 0) AS maximo
                        FROM productos p
                        LEFT JOIN presentaciones pr ON pr.`id_presentacion` = p.`id_presentacion`
                        LEFT JOIN stock_niveles sn ON p.id_producto=sn.id_producto AND sn.id_sucursal=$id_sucursal 
                        LEFT JOIN productos_proveedores pp ON p.id_producto = pp.id_producto $where_proveedor
                        GROUP BY p.id_producto
                        HAVING stock <= minimo $search_
                        ORDER BY $sort $order
                        $sql_limit");
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

        case 'faltantes-solicitudes':
            $db = DataBase::conectar();
            $limit = $db->clearText($_REQUEST['limit']);
            $offset    = $db->clearText($_REQUEST['offset']);
            $search = $db->clearText($_REQUEST['search']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            $id_proveedor = $db->clearText($_REQUEST['id_proveedor']);
            if (isset($search) && !empty($search)) {
                $search_ = "AND p.codigo LIKE '$search%' OR p.producto LIKE '$search%' OR  pr.presentacion LIKE '$search%'";
            }

            if(!empty($id_proveedor) && intVal($id_proveedor) != 0) {
                $and_proveedor .= " AND pp.id_proveedor = $id_proveedor ";
            }else{
                $and_proveedor .="";
            };

            $sql_limit ="";
            if (isset($_REQUEST['limit']) && isset($_REQUEST['offset'])) {
                $sql_limit .= "LIMIT $offset, $limit";
            }
        
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            sdp.id_solicitud_deposito_producto,
                            sdp.id_solicitud_deposito, 
                            sdp.cantidad - (
                                    SELECT IFNULL(SUM(cantidad), 0)
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr  ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado !=2  AND nrp.id_solicitud_deposito=sd.id_solicitud_deposito AND nrp.id_producto=sdp.id_producto
                                ) AS pendiente, 
                                (
                                SELECT 
                                IFNULL(SUM(s.stock), 0)
                                FROM stock s 
                                JOIN lotes l ON s.id_lote=l.id_lote
                                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
                            ) AS stock, 
                            (sdp.cantidad - (
                                    SELECT IFNULL(SUM(cantidad), 0)
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr  ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado !=2  AND nrp.id_solicitud_deposito=sd.id_solicitud_deposito AND nrp.id_producto=sdp.id_producto
                                ) - (
                                SELECT 
                                IFNULL(SUM(s.stock), 0)
                                FROM stock s 
                                JOIN lotes l ON s.id_lote=l.id_lote
                                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
                            )) AS recomendado,
                            sd.id_sucursal,
                            sd.numero, 
                            sd.usuario, 
                            sdp.id_producto,
                            p.producto,
                            p.codigo,
                            p.id_presentacion,
                            pr.presentacion,
                            s.sucursal,
                            IFNULL(pv.proveedor,'-') AS proveedor
                        FROM
                            solicitudes_depositos_productos sdp
                        LEFT JOIN solicitudes_depositos sd ON sd.id_solicitud_deposito= sdp.id_solicitud_deposito
                        LEFT JOIN productos p ON p.id_producto = sdp.id_producto
                        LEFT JOIN sucursales s ON s.id_sucursal = sd.id_sucursal
                        LEFT JOIN presentaciones pr ON p.id_presentacion = pr.id_presentacion
                        LEFT JOIN `productos_proveedores` pp ON pp.id_producto = p.id_producto  
                        LEFT JOIN proveedores pv ON pv.id_proveedor = pp.id_proveedor 
                        WHERE  sd.estado IN(1,3) AND sd.id_deposito = $id_sucursal $and_proveedor $search_
                        GROUP BY p.producto
                        HAVING stock < pendiente
                        ORDER BY $sort $order
                        $sql_limit");
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

        case 'productos-solicitud':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT
                                SQL_CALC_FOUND_ROWS 
                                p.id_producto,
                                p.producto,
                                p.codigo,
                                p.precio,
                                pre.id_presentacion,
                                pre.presentacion,
                                (
                                SELECT 
                                IFNULL(SUM(s.stock), 0)
                                FROM stock s 
                                JOIN lotes l ON s.id_lote=l.id_lote
                                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
                                ) AS stock,
                                IFNULL(sn.maximo - (SELECT 
                                    IFNULL(SUM(s.stock), 0)
                                    FROM stock s 
                                    JOIN lotes l ON s.id_lote=l.id_lote
                                    WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()),0
                                ) AS recomendado,
                                IFNULL(sn.minimo, 0) AS minimo,
                                IFNULL(sn.maximo, 0) AS maximo
                            FROM
                                productos p
                            LEFT JOIN presentaciones pre ON p.id_presentacion = pre.id_presentacion
                            LEFT JOIN stock_niveles sn ON p.id_producto=sn.id_producto AND sn.id_sucursal=$id_sucursal 
                            WHERE p.estado=1 AND p.producto LIKE '$term%'
                            ORDER BY p.producto
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;


	}

?>
