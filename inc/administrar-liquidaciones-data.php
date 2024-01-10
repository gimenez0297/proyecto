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
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', ci, funcionario, fecha, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                id_liquidacion,
                                id_funcionario,
                                DATE(fecha) AS fecha,
                                funcionario,
                                ci,
                                neto_cobrar,
                                periodo,
                                estado,
                                usuario,
                                CASE estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Aprobado' WHEN 2 THEN 'Anulado' WHEN 3 THEN 'Pagado Parcial' WHEN 4 THEN 'Pagado Total' END AS estado_str
                            FROM liquidacion_salarios
                            WHERE fecha BETWEEN '$desde' AND '$hasta'
                            $having
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

            //Se obtiene los datos de la liquidacion para sumar 
            $db->setQuery("SELECT * FROM liquidacion_salarios WHERE id_liquidacion = $id");
            $row = $db->loadObject();
            $sueldo = $row->neto_cobrar;

            if ($estado == 1) { 
                //Buscamos el libro diario activo actualmente
                $db->setQuery("SELECT id_libro_diario_periodo, estado FROM libro_diario_periodo WHERE estado = 1");
                $id_libro_diario_periodo = $db->loadObject()->id_libro_diario_periodo;

                //Obtenemos el nro de asiento
                $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
                $nro_asiento = $db->loadObject()->nro_asiento;

                if (empty($nro_asiento)) {
                    $nro = zerofillAsiento(1);
                } else {
                    $nro = zerofillAsiento(intval($nro_asiento) + 1);
                }

                //Se carga la cabezera del asiento
                $db->setQuery("INSERT INTO `libro_diario` (`id_libro_diario_periodo`,`id_comprobante`,`fecha`,`nro_asiento`,`importe`,`descripcion`,`usuario`,`fecha_creacion`)
                VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$sueldo,'LIQUIDACIÓN DE SUELDO','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la liquidacion de sueldo.  
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }

                $id_asiento = $db->getLastID();

                //Se realiza el asiento del monto a la cuenta de sueldos  a pagar
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,121,'SUELDO A PAGAR',0,$sueldo);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }

                $db->setQuery("UPDATE liquidacion_salarios SET id_asiento=$id_asiento WHERE id_liquidacion=$id");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }
            }elseif ($estado == 2) {
                //Buscamos si la liquidicacion cuanta con un asiento contable. 
                $db->setQuery("SELECT * FROM liquidacion_salarios WHERE id_liquidacion = $id");
                $id_asiento = $db->loadObject()->id_asiento;

                if (!empty($id_asiento)) {
                    //Preguntamos si ya esta anulado el asiento
                    $db->setQuery("SELECT * FROM libro_diario WHERE id_libro_diario = $id_asiento");
                    $row_e = $db->loadObject();

                    $estado_asiento = $row_e->estado;

                    if ($estado_asiento == 0) {
                        echo json_encode(["status" => "error", "mensaje" => "El asiento ya fue anulado."]);
                        exit;
                    }

                    //Cambiamos el estado del asiento
                    $db->setQuery("UPDATE `libro_diario` SET `estado` = 0 WHERE `id_libro_diario` = $id_asiento;");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el asiento."]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }
                     //Se obtiene el detalle del asiento
                    $db->setQuery("SELECT
                                        ld.*,
                                        lc.`tipo_cuenta`,
                                        lbd.*
                                    FROM
                                        libro_diario_detalles ld
                                    LEFT JOIN libro_cuentas lc ON lc.`id_libro_cuenta` = ld.`id_libro_cuenta`
                                    LEFT JOIN libro_diario lbd ON lbd.`id_libro_diario` = ld.`id_libro_diario`
                                    WHERE ld.id_libro_diario = $id_asiento");
                    $rows = $db->loadObjectList();

                    foreach ($rows as $r) {
                        $id_libro_periodo = $db->clearText($r->id_libro_diario_periodo);
                        $importe = $db->clearText($r->importe);
                        $nro_asiento_c = $db->clearText($r->nro_asiento);
                    }

                    //Número de asiento
                    $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
                    $nro_asiento = $db->loadObject()->nro_asiento;

                    if (empty($nro_asiento)) {
                        $nro_asiento = zerofillAsiento(1);
                    } else {
                        $nro_asiento = zerofillAsiento(intval($nro_asiento) + 1);
                    }

                    $db->setQuery("INSERT INTO `libro_diario` (`id_libro_diario_periodo`,`fecha`,`nro_asiento`,`importe`,`descripcion`,`contraasiento`,`usuario`,`fecha_creacion`)
                        VALUES($id_libro_periodo,NOW(),'$nro_asiento',$importe,'CONTRA ASIENTO DEL ASIENTO $nro_asiento_c','$nro_asiento_c','$usuario',NOW());");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }

                    $id_asiento = $db->getLastID();

                    foreach ($rows as $r) {
                        $id_libro_detalle = $db->clearText($r->id_libro_detalle);
                        $id_libro_cuenta  = $db->clearText($r->id_libro_cuenta);
                        $debe  = $db->clearText($r->debe);
                        $haber  = $db->clearText($r->haber);
                        $tipo_cuenta = $db->clearText($r->tipo_cuenta);
                        $nro_asiento_c = $db->clearText($r->nro_asiento);

                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,$id_libro_cuenta,'CONTRA ASIENTO DEL ASIENTO $nro_asiento_c',$haber,$debe);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                            $db->rollback();  // Revertimos los cambios
                            exit;
                        }
                    }
                }
            }

            $db->setQuery("UPDATE liquidacion_salarios SET estado=$estado WHERE id_liquidacion=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'cambiar-estados':
            $db = DataBase::conectar();
            $solicitudes = json_decode($_POST['liquidaciones']);
            $estado = $db->clearText($_POST['estado']);

            if (empty($solicitudes)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una o más liquidaciones"]);
                exit;
            }

            foreach ($solicitudes as $s) {
                $id = $db->clearText($s->id_liquidacion);

                if ($estado == 1) { 
                    //Buscamos el libro diario activo actualmente
                    $db->setQuery("SELECT id_libro_diario_periodo, estado FROM libro_diario_periodo WHERE estado = 1");
                    $id_libro_diario_periodo = $db->loadObject()->id_libro_diario_periodo;
    
                    //Obtenemos el nro de asiento
                    $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
                    $nro_asiento = $db->loadObject()->nro_asiento;

                    $db->setQuery("SELECT * FROM liquidacion_salarios WHERE id_liquidacion = $id");
                    $row = $db->loadObject();
                    $sueldo = $row->neto_cobrar;
    
                    if (empty($nro_asiento)) {
                        $nro = zerofillAsiento(1);
                    } else {
                        $nro = zerofillAsiento(intval($nro_asiento) + 1);
                    }
    
                    //Se carga la cabezera del asiento
                    $db->setQuery("INSERT INTO `libro_diario` (`id_libro_diario_periodo`,`id_comprobante`,`fecha`,`nro_asiento`,`importe`,`descripcion`,`usuario`,`fecha_creacion`)
                    VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$sueldo,'LIQUIDACIÓN DE SUELDO','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la liquidacion de sueldo.  
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
    
                    $id_asiento = $db->getLastID();
    
                    //Se realiza el asiento del monto a la cuenta de deudas con proveedores
                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                    VALUES($id_asiento,121,'SUELDO A PAGAR',0,$sueldo);");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
    
                    $db->setQuery("UPDATE liquidacion_salarios SET id_asiento=$id_asiento, estado=$estado WHERE id_liquidacion=$id");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                        exit;
                    }
                }elseif ($estado == 2) {
                    //Buscamos si la liquidicacion cuanta con un asiento contable. 
                    $db->setQuery("SELECT * FROM liquidacion_salarios WHERE id_liquidacion = $id");
                    $id_asiento = $db->loadObject()->id_asiento;
    
                    if (!empty($id_asiento)) {
                        //Preguntamos si ya esta anulado el asiento
                        $db->setQuery("SELECT * FROM libro_diario WHERE id_libro_diario = $id_asiento");
                        $row_e = $db->loadObject();
    
                        $estado_asiento = $row_e->estado;
    
                        if ($estado_asiento == 0) {
                            echo json_encode(["status" => "error", "mensaje" => "El asiento ya fue anulado."]);
                            exit;
                        }
    
                        //Cambiamos el estado del asiento
                        $db->setQuery("UPDATE `libro_diario` SET `estado` = 0 WHERE `id_libro_diario` = $id_asiento;");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el asiento."]);
                            $db->rollback();  // Revertimos los cambios
                            exit;
                        }
                         //Se obtiene el detalle del asiento
                        $db->setQuery("SELECT
                                            ld.*,
                                            lc.`tipo_cuenta`,
                                            lbd.*
                                        FROM
                                            libro_diario_detalles ld
                                        LEFT JOIN libro_cuentas lc ON lc.`id_libro_cuenta` = ld.`id_libro_cuenta`
                                        LEFT JOIN libro_diario lbd ON lbd.`id_libro_diario` = ld.`id_libro_diario`
                                        WHERE ld.id_libro_diario = $id_asiento");
                        $rows = $db->loadObjectList();
    
                        foreach ($rows as $r) {
                            $id_libro_periodo = $db->clearText($r->id_libro_diario_periodo);
                            $importe = $db->clearText($r->importe);
                            $nro_asiento_c = $db->clearText($r->nro_asiento);
                        }
    
                        //Número de asiento
                        $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
                        $nro_asiento = $db->loadObject()->nro_asiento;
    
                        if (empty($nro_asiento)) {
                            $nro_asiento = zerofillAsiento(1);
                        } else {
                            $nro_asiento = zerofillAsiento(intval($nro_asiento) + 1);
                        }
    
                        $db->setQuery("INSERT INTO `libro_diario` (`id_libro_diario_periodo`,`fecha`,`nro_asiento`,`importe`,`descripcion`,`contraasiento`,`usuario`,`fecha_creacion`)
                            VALUES($id_libro_periodo,NOW(),'$nro_asiento',$importe,'CONTRA ASIENTO DEL ASIENTO $nro_asiento_c','$nro_asiento_c','$usuario',NOW());");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                            $db->rollback();  // Revertimos los cambios
                            exit;
                        }
    
                        $id_asiento = $db->getLastID();
    
                        foreach ($rows as $r) {
                            $id_libro_detalle = $db->clearText($r->id_libro_detalle);
                            $id_libro_cuenta  = $db->clearText($r->id_libro_cuenta);
                            $debe  = $db->clearText($r->debe);
                            $haber  = $db->clearText($r->haber);
                            $tipo_cuenta = $db->clearText($r->tipo_cuenta);
                            $nro_asiento_c = $db->clearText($r->nro_asiento);
    
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,$id_libro_cuenta,'CONTRA ASIENTO DEL ASIENTO $nro_asiento_c',$haber,$debe);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                                $db->rollback();  // Revertimos los cambios
                                exit;
                            }
                        }
                    }

                    $db->setQuery("UPDATE liquidacion_salarios SET estado=$estado WHERE id_liquidacion=$id");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                        exit;
                    }
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
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

?>
