<?php

use PhpOffice\PhpSpreadsheet\Calculation\Engine\Logger;

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
                $having = "HAVING CONCAT_WS(' ',ruc ,fecha, razon_social, numero, observacion, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_canje_punto,
                            numero,
                            cantidad,
                            puntos,
                            ruc,
                            fecha,
                            razon_social,
                            observacion,
                            usuario,
                            estado,
                            CASE estado WHEN 2 THEN 'Anulado' WHEN 1 THEN 'Procesado' END AS estado_str,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM canjes_puntos
                            WHERE fecha BETWEEN '$desde' AND '$hasta' $having
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
            
            //Recuperamos el Periodo canje
            $db->setQuery(" SELECT periodo_canje
                                        FROM plan_puntos  WHERE tipo = 1");
            $periodo_canje = $db->loadObject()->periodo_canje;

            //Comprobamos si tiene registros vencidos
            $db->setQuery(" SELECT *
                                        FROM clientes_puntos cp
                                        JOIN canjes_puntos_utilizados cpu ON cp.id_cliente_punto=cpu.id_cliente_punto
                                        WHERE id_canje_punto = $id  AND (timestampdiff(DAY, cp.fecha, NOw()) > $periodo_canje) ");
            $rows = $db->loadObjectList();
            
            if (!empty($rows)) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Los puntos del canje se encuentran vencidos"]);
                exit;
            }
     
            //Recuperamos los datos para devolver al stock premio
            $db->setQuery(" SELECT cpp.id_canje_punto_premio, cpp.id_premio, cpp.costo, cpp.cantidad, cp.numero, cp.id_canje_punto, p.premio
                                        FROM canjes_puntos_premios cpp
                                        LEFT JOIN canjes_puntos cp ON cp.id_canje_punto=cpp.id_canje_punto
                                        LEFT JOIN premios p ON cpp.id_premio = p.id_premio
                                        WHERE cp.id_canje_punto = $id ");
            $rows = $db->loadObjectList();
        
            if (empty($rows)) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

                foreach ($rows as $p) {
                    $id_canje_punto_premio = $p->id_canje_punto_premio;
                    $id_premio = $p->id_premio;
                    $cantidad = $p->cantidad;
                    $numero = $p->numero;
                    $premio = $p->premio;
                       
                    // Se suma el stock

                    premio_sumar_stock($db, $id_premio, $cantidad);
                
                    $historial = new stdClass();
                    $historial->id_premio = $id_premio;
                    $historial->premio = $premio;
                    $historial->cantidad = $cantidad;
                    $historial->operacion = ADD;
                    $historial->id_origen = $id;
                    $historial->origen = ADC;
                    $historial->detalles = "Canje N° " .$numero." Anulada";
                    $historial->usuario = $usuario;
    
                    if (!stock_historial_premio($db, $historial)) {
                        $db->rollback(); // Revertimos los cambios
                        throw new Exception("Error al guardar el historial de stock. Code: ".$db->getErrorCode());
                    } 

                }

                //Recuperamos los puntos del cliente
                $db->setQuery(" SELECT cp.id_cliente_punto, cp.id_cliente, cp.fecha, cp.utilizados, cpu.id_canje_punto, cpu.utilizados AS utilizados_cpu
                                            FROM clientes_puntos cp
                                            JOIN canjes_puntos_utilizados cpu ON cp.id_cliente_punto=cpu.id_cliente_punto
                                            WHERE id_canje_punto = $id  AND (timestampdiff(DAY, cp.fecha, NOw()) < $periodo_canje) ORDER BY fecha ASC");
                $rows = $db->loadObjectList();

                if (empty($rows)) {
                $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    exit;
                }

                foreach ($rows as $p) {
                    $id_cliente_punto = $p->id_cliente_punto; 
                    $utilizados_cpu = $p->utilizados_cpu;
                    $utilizados = $p->utilizados;

                    if($utilizados == $utilizados_cpu){
                        $utilizados_carga=0;
                    }else{
                        $utilizados_carga = $utilizados - $utilizados_cpu;
                    }    
        
                    //Retornamos al cliente sus puntos
                    
                    $db->setQuery("UPDATE clientes_puntos SET  utilizados = $utilizados_carga, estado=0
                                                WHERE id_cliente_punto = $id_cliente_punto ");

                    if (!$db->alter()) {
                        $db->rollback(); // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los puntos acumulados"]);
                        exit;
                    }

                }

                //Cambiamos el estado a Anulado
                $db->setQuery("UPDATE canjes_puntos SET estado=$estado WHERE id_canje_punto=$id");
    
                if (!$db->alter()) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }

                if (!actualiza_puntos_clientes($id_cliente)) {
                    $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar los puntos del cliente"]);
                    exit;
                }
            
            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'ver_productos':

            $db = DataBase::conectar();
            $id_canje_punto = $db->clearText($_GET['id_canje_punto']);

            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', cantidad ) LIKE '%$search%'";
            }

              $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            cpp.id_canje_punto_premio,
                            cpp.id_canje_punto,
                            p.codigo,
                            p.premio,
                            cpp.cantidad,
                            cpp.costo, 
                            cpp.total,
                            cpp.puntos,
                            cpp.id_premio
                            FROM canjes_puntos_premios cpp
                            LEFT JOIN premios p ON  p.id_premio =  cpp.id_premio     
                            WHERE cpp.id_canje_punto = $id_canje_punto");

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

        case 'actualiza_estado_puntos_cliente':

            actualiza_estado_puntos_clientes();
             

        break;
	}