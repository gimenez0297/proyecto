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
            $id_deposito = $db->clearText($_POST['deposito']);
            $id_proveedor = $db->clearText($_POST['id_proveedor']);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $numero = $db->clearText($_POST['numero']);
            $productos = json_decode($_POST['productos'], true);
            
            if (empty($id_deposito)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un deposito"]);
                exit;
            }
            if (empty($id_proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);
                exit;
            }
            if (empty($productos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún producto agregado. Favor verifique."]);
                exit;
            }

            $db->setQuery("SELECT MAX(numero) AS numero FROM solicitudes_depositos");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero_sgte = zerofill($sgte_numero);

            // Cabeceera
            $db->setQuery("INSERT INTO solicitudes_depositos (numero, id_sucursal, id_proveedor, id_deposito, observacion, estado, usuario, fecha)
                            VALUES ('$numero_sgte','$id_sucursal','$id_proveedor','$id_deposito','$observacion','0','$usuario',NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            $id_solicitud_deposito = $db->getLastID();

            foreach ($productos as $p) {
                $id_producto = $db->clearText($p["id_producto"]);
                $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
                if ($cantidad == 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Existe producto con cantidad 0. La cantidad debe ser mayor a 0"]);
                    exit;
                }

                $db->setQuery("INSERT INTO solicitudes_depositos_productos (id_solicitud_deposito, id_producto, cantidad)
                                VALUES ('$id_solicitud_deposito','$id_producto','$cantidad')");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Solicitud a depósito registrada correctamente","id_solicitud_deposito" =>$id_solicitud_deposito]);
        break;

        case 'recuperar-numero':
            $db = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM solicitudes_depositos");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

        case 'faltantes':
            $db = DataBase::conectar();
            $desde = $db->clearText($_REQUEST['desde']);
            $hasta = $db->clearText($_REQUEST['hasta']);

            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $search = $db->clearText($_REQUEST['search']);
            if (isset($search) && !empty($search)) {
                $search_ = "AND codigo LIKE '$search%' OR producto LIKE '$search%' OR  presentacion LIKE '$search%'";
            }
            
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
                                SELECT IFNULL(SUM(fp.cantidad), 0)
                                FROM facturas_productos fp
                                JOIN facturas f ON fp.id_factura=f.id_factura
                                WHERE fp.id_producto=p.id_producto AND f.id_sucursal=$id_sucursal AND DATE(f.fecha) BETWEEN '$desde' AND '$hasta'
                            ) AS cantidad_ventas,
                            (
                                SELECT 
                                IFNULL(SUM(s.stock), 0)
                                FROM stock s 
                                JOIN lotes l ON s.id_lote=l.id_lote
                                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
                            ) AS stock,
                            IF(sn.maximo > sn.minimo, 
                                IFNULL(sn.maximo - (
                                        SELECT IFNULL(SUM(s.stock), 0)
                                        FROM stock s 
                                        JOIN lotes l ON s.id_lote=l.id_lote
                                        WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
                                    ), 0
                                ), 0
                            ) AS recomendado,
                            IFNULL(sn.minimo, 0) AS minimo,
                            IFNULL(sn.maximo, 0) AS maximo,
                            pv.`proveedor`,
			                pv.`id_proveedor`
                        FROM productos p
                        LEFT JOIN presentaciones pr ON pr.`id_presentacion` = p.`id_presentacion`
                        LEFT JOIN stock_niveles sn ON p.id_producto=sn.id_producto AND sn.id_sucursal=$id_sucursal 
                        LEFT JOIN productos_proveedores pp ON pp.`id_producto` = p.`id_producto`
                        LEFT JOIN proveedores pv ON pv.`id_proveedor` = pp.`id_proveedor`
                        GROUP BY p.id_producto
                        HAVING stock <= minimo $search_
                        ORDER BY recomendado DESC
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

	}

?>
