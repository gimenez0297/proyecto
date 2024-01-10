<?php
    include ("funciones.php");
    include ("../inc/funciones/premios-funciones.php");
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
                            cp.id_cargas_premios,
                            cp.numero,
                            cp.cantidad,
                            cp.monto,
                            cp.observacion,
                            cp.estado,
                            cp.usuario,
                            g.nro_gasto AS numero_gasto,
                            CASE cp.estado WHEN 0 THEN 'Anulado' WHEN 1 THEN 'Procesado' END AS estado_str,
                            DATE_FORMAT(cp.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM cargas_premios cp
                            LEFT JOIN gastos g ON g.id_cargas_premios = cp.id_cargas_premios
                            WHERE cp.fecha BETWEEN '$desde' AND '$hasta' $having
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
            
            $db->setQuery("SELECT * FROM gastos WHERE id_cargas_premios = $id");
            $row = $db->loadObject();
            $estado_g = $row->estado;
            $id_gasto = $row->id_gasto;
            $condicion = $row->condicion;

    
            if ($estado_g == 1||$estado_g == 2 || $estaestado_gdo == 3) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede anular una carga de premio ya pagada de forma parcial o total."]);
                exit;
            }else{
                if ($condicion == 2) {
                    $db->setQuery("DELETE FROM `gastos_vencimientos` WHERE `id_gasto` = $id_gasto");
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
            
            $db->setQuery("UPDATE cargas_premios SET estado=$estado WHERE id_cargas_premios=$id");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }          
            
            $db->setQuery(" SELECT cpp.id_cargas_premios, p.premio, p.id_premio, cpp.cantidad, cp.numero
                                        FROM cargas_premios_productos cpp
                                        LEFT JOIN cargas_premios cp ON cp.id_cargas_premios=cpp.id_cargas_premios
                                        LEFT JOIN premios p ON cpp.id_premio = p.id_premio
                                        WHERE cpp.id_cargas_premios = $id ");
            $rows = $db->loadObjectList();

            if (empty($rows)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }
                                        
                foreach ($rows as $p) {
                    $id_premio = $p->id_premio;
                    $premio = $p->premio;
                    $cantidad = $p->cantidad;
                    $numero = $p->numero;

                    // Se resta el stock

    
                    premio_restar_stock($db, $id_premio, $cantidad);
                
                    // Se insertan datos en stock insumo historial
                    
                    $historial = new stdClass();
                    $historial->id_premio = $id_premio;
                    $historial->premio = $premio;
                    $historial->cantidad = $cantidad;
                    $historial->operacion = SUB;
                    $historial->id_origen = $id;
                    $historial->origen = ANU;
                    $historial->detalles = "Carga N° " .zerofill($numero)." Anulada";
                    $historial->usuario = $usuario;
    
                    if (!stock_historial_premio($db, $historial)) {
                        throw new Exception("Error al guardar el historial de stock. Code: ".$db->getErrorCode());
                    } 
                }
        
            
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
            
        case 'ver_productos':

            $db = DataBase::conectar();

            $id_cargas_premios = $db->clearText($_GET['id_cargas_premios']);

            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', cantidad ) LIKE '%$search%'";
            }

              $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            p.codigo,
                            p.premio,
                            cp.cantidad,
                            p.costo,
                            cp.monto,
                            cp.monto * cp.cantidad as total,
                            cp.id_cargas_premios_productos,
                            cp.id_cargas_premios,
                            cp.id_premio
                            FROM cargas_premios_productos cp
                            LEFT JOIN premios p ON  p.id_premio =  cp.id_premio     
                            WHERE cp.id_cargas_premios = $id_cargas_premios");

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

