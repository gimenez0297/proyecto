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
        
        case 'ver':
            $db = DataBase::conectar();
            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', numero, monto, observacion, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            ci.id_carga_insumo,
                            ci.numero,
                            ci.cantidad,
                            ci.monto,
                            ci.observacion,
                            ci.estado,
                            ci.usuario,
                            g.nro_gasto AS numero_gasto,
                            CASE ci.estado WHEN 0 THEN 'Anulado' WHEN 1 THEN 'Procesado' END AS estado_str,
                            DATE_FORMAT(ci.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM cargas_insumos ci
                            LEFT JOIN gastos g ON ci.id_carga_insumo = g.id_carga_insumo
                            WHERE ci.fecha BETWEEN '$desde' AND '$hasta' $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
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
		
        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("SELECT * FROM gastos WHERE id_carga_insumo = $id");
            $row = $db->loadObject();
            $estado_g = $row->estado;
            $id_gasto = $row->id_gasto;
            $condicion = $row->condicion;

            if ($estado_g == 1||$estado_g == 2 || $estaestado_gdo == 3) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede anular una carga de insumos ya pagada de forma parcial o total."]);
                exit;
            }else{
                if ($condicion == 2) {
                    $db->setQuery("DELETE FROM `gastos_vencimientos` WHERE `id_gasto` = $id_gasto;");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. Al borrar los vencimientos de la recepcion"]);
                       $db->rollback();  // Revertimos los cambios
                       exit;
                    }
    
                }
                $db->setQuery("DELETE FROM `gastos` WHERE `id_gasto` = $id_gasto");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. Al borrar el gasto de la recepcion"]);
                   $db->rollback();  // Revertimos los cambios
                   exit;
                }
            }

            
            
            $db->setQuery("UPDATE cargas_insumos SET estado=$estado WHERE id_carga_insumo=$id");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }
            
            $db->setQuery("SELECT cp.id_producto_insumo, p.producto, cp.cantidad, cp.vencimiento, ci.numero 
                                        FROM cargas_insumos_productos cp
                                        LEFT JOIN cargas_insumos ci ON cp.id_carga_insumo=ci.id_carga_insumo
                                        LEFT JOIN productos_insumo p ON cp.id_producto_insumo=p.id_producto_insumo
                                        WHERE cp.id_carga_insumo=$id");
            $rows = $db->loadObjectList();

            if (empty($rows)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

                                        
                foreach ($rows as $p) {
                    $id_producto_insumo = $p->id_producto_insumo;
                    $producto = $p->producto;
                    $cantidad = $p->cantidad;
                    $numero = $p->numero;
                    $vencimiento=$rows-> vencimiento;
                    
                    if (empty($vencimiento)) {
                        $vencimiento_null = 'NULL'; 
                    }else{
                        $vencimiento_null = "'$vencimiento'";
                    }    


                    //Se resta el stock

    
                    producto_insumo_restar_stock($db, $id_producto_insumo, $vencimiento, $cantidad);
                
                    //Se insertan datos en stock insumo historial
                    
                    $historial = new stdClass();
                    $historial->id_producto_insumo = $id_producto_insumo;
                    $historial->producto = $producto;
                    $historial->vencimiento = $vencimiento_null;
                    $historial->operacion = SUB;
                    $historial->id_origen = $id;
                    $historial->origen = REM;
                    $historial->detalles = "Recepción N° " .zerofill($numero);
                    $historial->usuario = $usuario;
    
                    if (!stock_historial_insumo($db, $historial)) {
                        throw new Exception("Error al guardar el historial de stock. Code: ".$db->getErrorCode());
                    } 
                }
        
            
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
            
        case 'ver_productos':

            $db = DataBase::conectar();

            $id_carga_insumo = $db->clearText($_GET['id_carga_insumo']);

            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', cantidad, vencimiento) LIKE '%$search%'";
            }

              $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            cip.id_carga_insumo_producto,
                            cip.id_carga_insumo,
                            cip.id_producto_insumo,
                            cip.cantidad,
                            cip.vencimiento,
                            cip.monto,
                            pi.id_producto_insumo,
                            pi.producto,
                            cip.monto * cip.cantidad as total,
                            pi.codigo,                   
                            DATE_FORMAT(cip.vencimiento,'%d/%m/%Y') AS vencimiento
                            FROM cargas_insumos_productos cip
                            LEFT JOIN productos_insumo pi ON    pi.id_producto_insumo =  cip.id_producto_insumo     
                            WHERE cip.id_carga_insumo = $id_carga_insumo");

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

