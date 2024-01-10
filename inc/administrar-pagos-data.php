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

            // Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', ruc_ci, beneficiario, fecha, estado_str, condicion_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT 
                                op.id_pago,
                                op.id_proveedor,
                                op.id_funcionario,
                                op.id_proveedor_gasto,
                                op.id_caja_chica_sucursal,
                                CASE
                                    WHEN op.destino_pago = 1 THEN p.proveedor
                                    WHEN op.destino_pago = 2 THEN f.funcionario
                                    WHEN op.destino_pago = 3 THEN p.proveedor
                                    WHEN op.destino_pago = 4 THEN fc.funcionario
                                END AS beneficiario,
                                DATE_FORMAT(op.fecha,'%d/%m/%Y') AS fecha_carga,
                                DATE_FORMAT(op.fecha_pago,'%d/%m/%Y') AS fecha_pago,
                                op.monto,
                                CASE
                                    WHEN op.destino_pago = 1 THEN p.ruc
                                    WHEN op.destino_pago = 2 THEN f.ci
                                    WHEN op.destino_pago = 3 THEN p.ruc
                                    WHEN op.destino_pago = 4 THEN fc.ci
                                END AS ruc_ci,
                                CASE
                                    WHEN op.forma_pago = 1 THEN 'TRANSFERENCIA'
                                    WHEN op.forma_pago = 2 THEN 'CHEQUE'
                                END AS forma_,
                                CASE
                                    WHEN op.forma_pago = 1 THEN bc.cuenta
                                    WHEN op.forma_pago = 2 THEN op.nro_cheque
                                END AS nro_forma,
                                op.destino_pago,
                                op.forma_pago,
                                op.observacion,
                                CASE op.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Anulado' WHEN 3 THEN 'Pagado' END AS estado_str,
                                op.estado,
                                op.usuario,
                                op.numero
                            FROM orden_pagos op
                            LEFT JOIN funcionarios f ON op.id_funcionario=f.id_funcionario
                            LEFT JOIN bancos_cuentas bc ON op.nro_cuenta=bc.id_cuenta
                            LEFT JOIN proveedores p ON op.id_proveedor=p.id_proveedor OR op.id_proveedor_gasto=p.id_proveedor
                            LEFT JOIN caja_chica_sucursal ccs ON ccs.id_caja_chica_sucursal = op.id_caja_chica_sucursal
                            LEFT JOIN caja_chica cc ON cc.id_caja_chica = ccs.id_caja_chica
                            LEFT JOIN funcionarios fc ON fc.id_funcionario = cc.id_funcionario
                            WHERE op.fecha BETWEEN '$desde' AND '$hasta'
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
            $db->autocommit(false);

            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);
            $destino_pago = $db->clearText($_POST['destino']);

            /* 
                * INFO VALIDACIONES
                * estado: 1(aprovado), 2(anulado), 3(pagado)
                * destino_pago: 1(proveedores), 2(funcionarios), 3(gastos), 4(caja chica)
                * forma_pago: 1(transferencia), 2(cheques), 3(efectivo)
                * condicion: 1(contado), 2(credito)
            */

            if($estado == 3){

                $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago=$id");
                $orden = $db->loadObject();
                    
                    // ! si la condicion de pago es credito o contado

                    if($orden->forma_pago == 2 || $orden->forma_pago == 1){

                        // ! si es a pago proveedores

                        if($orden->destino_pago == 1){

                            // ! recuperamos la orden que esta asociado con el recepcion
                            
                            $db->setQuery("SELECT 
                                                    op.id_pago_proveedor,
                                                    op.id_pago,
                                                    op.id_factura,
                                                    op.id_recepcion_compra_vencimiento,
                                                    rc.condicion
                                            FROM orden_pagos_proveedores op
                                            JOIN recepciones_compras rc ON rc.id_recepcion_compra = op.id_factura 
                                            WHERE op.id_pago=$id");

                                            if (!$db->loadObjectList()) {
                                                echo json_encode(["status" => "error", "mensaje" => "Error al recuperar la recepcion."]);
                                                exit;
                                            }
                                            
                            // ! recuperamos la condicion

                            $rows = $db->loadObjectList();

                            // ! recorremos el objeto

                            foreach ($rows as $r) {

                                $condicion = $r->condicion;
                            
                                // ! si la condicion es credito y si no tiene algun archivo cargado en la orden de pago retornamos error

                                if($condicion == 2){
            
                                    $db->setQuery("SELECT * FROM orden_pagos_archivos WHERE id_pago=$id");
                                    $row = $db->loadObject();

                                    if (empty($row)) {
                                        echo json_encode(["status" => "error", "mensaje" => "Aún no ha cargado ningún documento. Favor verifique."]);
                                        exit;
                                    }
                                }
                            }
                                            
                            // ! si es pago a gastos

                        }else if($orden->destino_pago == 3){

                            // ! recuperamos la orden que esta asociado con el gasto
                            
                            $db->setQuery("SELECT 
                                                    opg.id_pago_gasto,
                                                    opg.id_pago,
                                                    opg.id_gasto,
                                                    opg.id_gasto_vencimiento,
                                                    g.condicion
                                            FROM orden_pago_gastos opg
                                            JOIN gastos g ON g.id_gasto = opg.id_gasto 
                                            WHERE opg.id_pago=$id");

                                            if (!$db->loadObjectList()) {
                                                echo json_encode(["status" => "error", "mensaje" => "Error al recuperar la recepcion."]);
                                                exit;
                                            }
                                            
                            // ! recuperamos la condicion

                            $rows = $db->loadObjectList();

                            // ! recorremos el objeto

                            foreach ($rows as $r) {

                                $condicion = $r->condicion;
                            
                                // ! si la condicion es credito y si no tiene algun archivo cargado en la orden de pago retornamos error

                                if($condicion == 2){
            
                                    $db->setQuery("SELECT * FROM orden_pagos_archivos WHERE id_pago=$id");
                                    $row = $db->loadObject();

                                    if (empty($row)) {
                                        echo json_encode(["status" => "error", "mensaje" => "Aún no ha cargado ningún documento. Favor verifique."]);
                                        exit;
                                    }
                                }
                            } 
                        }
                    }
                }

            $db->setQuery("UPDATE orden_pagos SET estado='$estado' WHERE id_pago=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

           

            $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago=$id");
            $row = $db->loadObject();
            
            $id_proveedor_gasto = $row->id_proveedor_gasto;
            $id_proveedor = $row->id_proveedor;
            $id_funcionario = $row->id_funcionario;
            $id_caja_chica_sucursal = $row->id_caja_chica_sucursal;

            if (isset($id_proveedor_gasto)) {

                $db->setQuery("SELECT * FROM orden_pago_gastos WHERE id_pago=$id");
                $id_g = $db->loadObject()->id_gasto;
               
                $db->setQuery("SELECT g.id_gasto,
                                g.monto,
                                g.nro_gasto,
                                g.monto-(SELECT IFNULL(SUM(opg.monto),0)
                                FROM orden_pago_gastos opg
                                JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago) AS pendiente
                                FROM gastos g
                                WHERE g.id_gasto = $id_g");
                $row = $db->loadObject();
                $pendiente = $row->pendiente;
                $monto = $row->monto;
                $nro_gasto = $row->nro_gasto;
    
                if ($pendiente == 0) { 
                    $estado_gasto = 2;
                }else if ($pendiente == $monto){
                    $estado_gasto = 0; 
                }
                else if ($pendiente > 0){
                    $estado_gasto = 1;
                }

                if ($estado == 3) {
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
                    VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto,'PAGO DE GASTO N° $nro_gasto','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que aprueba el pago. 
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }

                    $id_asiento = $db->getLastID();

                    //Se realiza el asiento del monto que se paga y se descuenta de la deuda proveedores. 
                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                    VALUES($id_asiento,83,'PAGO DE GASTO N° $nro_gasto',$monto,0);");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }

                    //Se obtiene el banco de la cual pago para poder descontar el monto pagado. 
                    $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                    $orden = $db->loadObject();
                    $forma_pago = $orden->forma_pago;
                    $id_banco = $orden->id_banco;

                    if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                        //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                        if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,12,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,293,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,10,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 5) {//BANCO REGIONAL.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,11,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,292,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }
                    }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,290,'CHEQUE PARA EL PAGO DEL GASTO N° $nro_gasto',$monto,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                    }

                    $db->setQuery("UPDATE orden_pagos SET id_libro_diario = $id_asiento WHERE id_pago=$id");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
                    
                }elseif ($estado == 2) {
                    //Si anula el pago, se busca si ya cuenta con un asiento contable y se realiza el contra asiento. 
                    
                    $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                    $id_asiento = $db->loadObject()->id_libro_diario;

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

                $db->setQuery("UPDATE gastos SET estado = $estado_gasto WHERE id_gasto = $id_g");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
    
            }else if(isset($id_proveedor)){
                $db->setQuery("SELECT * FROM orden_pagos_proveedores WHERE id_pago=$id");
                $id_f = $db->loadObject()->id_factura;

                $db->setQuery("SELECT g.id_gasto,
                                g.monto,
                                g.nro_gasto,
                                g.gravada_10,
                                g.gravada_5,
                                g.monto-(SELECT IFNULL(SUM(opp.monto),0)
                                FROM `orden_pagos_proveedores` opp
                                JOIN orden_pagos op ON opp.id_pago=op.id_pago
                                WHERE op.estado=3 AND opp.id_factura=g.id_recepcion_compra) AS pendiente
                                FROM gastos g
                                WHERE g.id_recepcion_compra =$id_f ");
                $row = $db->loadObject();
                $pendiente = $row->pendiente;
                $monto = $row->monto;
                $id_g2= $row->id_gasto;
                $nro_gasto = $row->nro_gasto;
                $gravada_10 = $row->gravada_10;
                $gravada_5 = $row->gravada_5;

                if ($pendiente == 0) {
                    $estado_gasto = 2;
                }else if ($pendiente == $monto){
                    $estado_gasto = 0; 
                }
                else if ($pendiente > 0){
                    $estado_gasto = 1;
                }

                if ($estado == 3) {
                    $iva_5_asiento = $gravada_5 * 0.05;
                    $iva_10_asiento = $gravada_10 * 0.1;
                    $gravada_suma = $iva_5_asiento + $iva_10_asiento;
                    $retencion_iva = $gravada_suma * 0.3;

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
                    VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto,'PAGO DE GASTO N° $nro_gasto','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que aprueba el pago. 
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }

                    $id_asiento = $db->getLastID();

                    //Se realiza el asiento del monto que se paga y se descuenta de la deuda proveedores. 
                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                    VALUES($id_asiento,83,'PAGO DE GASTO N° $nro_gasto',$monto,0);");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
                    //Se realiza el asiento de la retencion IVA. Que es el 30% del iva. 
                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                    VALUES($id_asiento,29,'DEBITO DE RETENCIÓN IVA',0,$retencion_iva);");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }

                    //Se obtiene el banco de la cual pago para poder descontar el monto pagado. 
                    $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                    $orden = $db->loadObject();
                    $forma_pago = $orden->forma_pago;
                    $id_banco = $orden->id_banco;

                    if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                        //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                        if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,12,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,293,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,10,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 5) {//BANCO REGIONAL.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,11,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }
                        elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,292,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }
                    }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,290,'CHEQUE PARA EL PAGO DEL GASTO N° $nro_gasto',$monto,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                    }

                    $db->setQuery("UPDATE orden_pagos SET id_libro_diario = $id_asiento WHERE id_pago=$id");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }

                }elseif ($estado == 2) {
                    //Si anula el pago, se busca si ya cuenta con un asiento contable y se realiza el contra asiento. 
                    
                    $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                    $id_asiento = $db->loadObject()->id_libro_diario;

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

                $db->setQuery("UPDATE gastos SET estado = $estado_gasto WHERE id_gasto = $id_g2");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }

            if (isset($id_funcionario)) {
                $db->setQuery("SELECT * FROM orden_pagos_funcionarios WHERE id_pago=$id");
                // $id_liquidacion = $db->loadObjectList();
                $liquidaciones = $db->loadObjectList();

                foreach ($liquidaciones as $r) {
                    $id_liquidacion = $r->id_liquidacion;

                    $db->setQuery("SELECT 
                                l.id_liquidacion,
                                l.neto_cobrar AS total,
                                l.neto_cobrar - (SELECT 
                                        IFNULL(SUM(opg.monto),0)
                                        FROM orden_pagos_funcionarios opg
                                        JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                        WHERE op.estado=3 AND opg.id_liquidacion=l.id_liquidacion AND opg.id_pago=op.id_pago) AS pendiente
                            FROM liquidacion_salarios l
                            WHERE l.id_liquidacion = $id_liquidacion");
                    $row = $db->loadObject();

                    $pendiente = $row->pendiente;
                    $monto = $row->total;
                    $id_liq= $row->id_liquidacion;

                    if ($pendiente == 0) {
                        $estado_liq = 4;
                    }else if ($pendiente == $monto){
                        $estado_liq = 1; 
                    }
                    else if ($pendiente > 0){
                        $estado_liq = 3;
                    }

                    if ($estado == 3) {
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
                        VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto,'PAGO DE SUELDO','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la liquidacion de sueldo.  
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }

                        $id_asiento = $db->getLastID();

                        //Se realiza el asiento del monto a la cuenta de sueldo a pagar
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,121,'SUELDO PAGADO',$monto,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                        
                        //Se realiza el asiento del monto a la cuenta de sueldo a pagar
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,191,'SUELDO PAGADO',$monto,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }

                        //Se obtiene el banco de la cual pago para poder descontar el monto pagado. 
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $orden = $db->loadObject();
                        $forma_pago = $orden->forma_pago;
                        $id_banco = $orden->id_banco;

                        if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                            //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                            if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,12,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,293,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,10,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 5) {//BANCO REGIONAL.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,11,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                            elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,292,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                        }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,290,'CHEQUE PARA EL PAGO DE SUELDO',$monto,0);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }

                        $db->setQuery("UPDATE orden_pagos SET id_libro_diario = $id_asiento WHERE id_pago=$id");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                    }elseif ($estado == 2) {
                        //Buscamos el id_asiento 
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $id_asiento = $db->loadObject()->id_libro_diario;

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

                    $db->setQuery("UPDATE liquidacion_salarios SET estado = $estado_liq WHERE id_liquidacion = $id_liq");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
                }
            }

            if (isset($id_caja_chica_sucursal)) {

                //Recuperamos el monto de la orden de pago.
                $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago=$id");
                $pago = $db->loadObject();
                $monto_o = $pago->monto;
                $id_cc_sucursal = $pago->id_caja_chica_sucursal;

                //Se obtiene la ultima caja de esa sucursal para saber si esta rendida o no
                $db->setQuery("SELECT * FROM caja_chica_sucursal  WHERE id_caja_chica_sucursal = $id_cc_sucursal ORDER BY id_caja_chica_sucursal DESC LIMIT 1");
                $ultima_cc = $db->loadObject();
                $id_caja_chica = $ultima_cc->id_caja_chica;


                //Recuperamos la configuracion de la caja chica.
                $db->setQuery("SELECT * FROM caja_chica WHERE id_caja_chica = $id_caja_chica");
                $caja_chica = $db->loadObject();
                $monto_caja = $caja_chica->monto;

                //Validamos si el monto final coincide con el monto de la caja 
                if ($monto_o != $monto_caja) {
                    echo json_encode(["status" => "error", "mensaje" => "No se esta cubriendo el total de la caja chica. Favor verifique."]);
                    exit;
                }

                if ($estado == 3) {
                    $db->setQuery("SELECT * FROM caja_chica_sucursal WHERE id_caja_chica = $id_caja_chica AND estado = 1");
                    $activo = $db->loadObject();
                    //Validamos que no este un movimiento que le falte rendicion.
                    if ($activo) {
                        echo json_encode(["status" => "error", "mensaje" => "Hay Movimiento con Rendición Pendiente. Favor verifique."]);
                        exit;
                    }

                    //Buscamos el detalle de la orden
                    $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                    $orden = $db->loadObject();
                    $forma_pago = $orden->forma_pago;
                    $id_banco = $orden->id_banco;

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
                    VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto_caja,'CAJA CHICA','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la carga del gasto.  
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }

                    $id_asiento = $db->getLastID();

                    //Se realiza el asiento del monto a la cuenta de caja chica. 
                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                    VALUES($id_asiento,289,'ACREDITAR CAJA CHICA',$monto_caja,0);");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }

                    if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                        //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                        if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,12,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,293,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,10,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($id_banco == 5) {//BANCO REGIONAL.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,11,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }
                        elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,292,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }
                    //Si son de los otros bancos, no se registran hasta el momento porque no cuenta con una cuenta en el plan de cuentas.
                    }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,290,'CHEQUE PARA CAJA CHICA',$monto_caja,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                    }
                    
                    $db->setQuery(" UPDATE `caja_chica_sucursal` 
                                    SET
                                        saldo = $monto_o,
                                        sobrante = $monto_o,
                                        `fecha_apertura` = NOW(),
                                        `usuario_apertura` = '$usuario',
                                        `estado` = 1
                                    WHERE `id_caja_chica_sucursal` = $id_caja_chica_sucursal;");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago. Code: ".$db->getErrorCode()]);
                        exit;
                    }

                    $db->setQuery("UPDATE `orden_pagos` SET `id_libro_diario` = $id_asiento WHERE `id_pago` = $id;");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago para la caja chica. Code: ".$db->getErrorCode()]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
                }elseif ($estado == 2) {
                    //Se busca el id_asiento.
                    $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                    $row = $db->loadObject();
                    $id_asiento = $row->id_libro_diario;
                    $id_caja_chica = $row->id_caja_chica_sucursal;

                    //Buscamos si la caja ya tiene gastos.
                    $db->setQuery("SELECT * FROM caja_chica_facturas WHERE id_caja_chica_sucursal = $id_caja_chica");
                    $rows_f = $db->loadObjectList();
                    
                    //Si la caja cuenta con facturas no se puede cancelar el pago. 
                    if (!empty($rows_f)) {
                        echo json_encode(["status" => "error", "mensaje" => "No se puede anular el pago.La caja ya cuenta con gastos cargados."]);
                        exit;
                    }

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

                    $db->setQuery(" UPDATE `caja_chica_sucursal` 
                                SET
                                    `estado` = 0
                                WHERE `id_caja_chica_sucursal` = $id_caja_chica;");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago. Code: ".$db->getErrorCode()]);
                        exit;
                    }

                }

            }
            
            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'cambiar-estados':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $ordenes = json_decode($_POST['ordenes']);
            $estado = $db->clearText($_POST['estado']);

            if (empty($ordenes)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una o más pagos"]);
                exit;
            }

            /* 
                * INFO VALIDACIONES
                * estado: 1(aprovado), 2(anulado), 3(pagado)
                * destino_pago: 1(proveedores), 2(funcionarios), 3(gastos), 4(caja chica)
                * forma_pago: 1(transferencia), 2(cheques), 3(efectivo)
                * condicion: 1(contado), 2(credito)
            */

            foreach ($ordenes as $s) {
                $id = $db->clearText($s->id_pago);

                $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago=$id");
                $row = $db->loadObject();
                
                $id_proveedor_gasto = $row->id_proveedor_gasto;
                $id_proveedor = $row->id_proveedor;
                $id_caja_chica_sucursal = $row->id_caja_chica_sucursal;
                $id_funcionario = $row->id_funcionario;
                $numero = $row->numero;

                if($estado == 3){

                    // ! si la condicion de pago es credito o contado

                    if($row->forma_pago == 2 || $row->forma_pago == 1){

                        // ! si es a pago proveedores

                        if($row->destino_pago == 1){

                            // ! recuperamos la orden que esta asociado con el recepcion
                            
                            $db->setQuery("SELECT 
                                                    op.id_pago_proveedor,
                                                    op.id_pago,
                                                    op.id_factura,
                                                    op.id_recepcion_compra_vencimiento,
                                                    rc.condicion
                                            FROM orden_pagos_proveedores op
                                            JOIN recepciones_compras rc ON rc.id_recepcion_compra = op.id_factura 
                                            WHERE op.id_pago=$id");

                                            if (!$db->loadObjectList()) {
                                                echo json_encode(["status" => "error", "mensaje" => "Error al recuperar la recepcion."]);
                                                exit;
                                            }
                                            
                            // ! recuperamos la condicion

                            $rows = $db->loadObjectList();

                            // ! recorremos el objeto

                            foreach ($rows as $r) {

                                $condicion = $r->condicion;
                            
                                // ! si la condicion es credito y si no tiene algun archivo cargado en la orden de pago retornamos error

                                if($condicion == 2){
            
                                    $db->setQuery("SELECT * FROM orden_pagos_archivos WHERE id_pago=$id");
                                    $row = $db->loadObject();

                                    if (empty($row)) {
                                        echo json_encode(["status" => "error", "mensaje" => "Aún no ha cargado ningún documento. Favor verifique."]);
                                        exit;
                                    }
                                }
                            }
                                            
                            // ! si es pago a gastos

                        }else if($orden->destino_pago == 3){

                            // ! recuperamos la orden que esta asociado con el gasto
                            
                            $db->setQuery("SELECT 
                                                    opg.id_pago_gasto,
                                                    opg.id_pago,
                                                    opg.id_gasto,
                                                    opg.id_gasto_vencimiento,
                                                    g.condicion
                                            FROM orden_pago_gastos opg
                                            JOIN gastos g ON g.id_gasto = opg.id_gasto 
                                            WHERE opg.id_pago=$id");

                                            if (!$db->loadObjectList()) {
                                                echo json_encode(["status" => "error", "mensaje" => "Error al recuperar la recepcion."]);
                                                exit;
                                            }
                                            
                            // ! recuperamos la condicion

                            $rows = $db->loadObjectList();

                            // ! recorremos el objeto

                            foreach ($rows as $r) {

                                $condicion = $r->condicion;
                            
                                // ! si la condicion es credito y si no tiene algun archivo cargado en la orden de pago retornamos error

                                if($condicion == 2){
            
                                    $db->setQuery("SELECT * FROM orden_pagos_archivos WHERE id_pago=$id");
                                    $row = $db->loadObject();

                                    if (empty($row)) {
                                        echo json_encode(["status" => "error", "mensaje" => "Aún no ha cargado ningún documento. Favor verifique."]);
                                        exit;
                                    }
                                }
                            } 
                        }
                    }
                }

                $db->setQuery("UPDATE orden_pagos SET estado=$estado WHERE id_pago=$id");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }

                if (isset($id_proveedor_gasto)) {

                    $db->setQuery("SELECT * FROM orden_pago_gastos WHERE id_pago=$id");
                    $id_g = $db->loadObject()->id_gasto;
                   
                    $db->setQuery("SELECT g.id_gasto,
                                    g.monto,
                                    g.monto-(SELECT IFNULL(SUM(opg.monto),0)
                                    FROM orden_pago_gastos opg
                                    JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                    WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago) AS pendiente
                                    FROM gastos g
                                    WHERE g.id_gasto = $id_g");
                    $row = $db->loadObject();
                    $pendiente = $row->pendiente;
                    $monto = $row->monto;
        
                    if ($pendiente == 0) {
                        $estado_gasto = 2;
                    }else if ($pendiente == $monto){
                        $estado_gasto = 0; 
                    }
                    else if ($pendiente > 0){
                        $estado_gasto = 1;
                    }

                    if ($estado == 3) {
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
                        VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto,'PAGO DE GASTO N° $nro_gasto','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que aprueba el pago. 
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
    
                        $id_asiento = $db->getLastID();
    
                        //Se realiza el asiento del monto que se paga y se descuenta de la deuda proveedores. 
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,83,'PAGO DE GASTO N° $nro_gasto',$monto,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
    
                        //Se obtiene el banco de la cual pago para poder descontar el monto pagado. 
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $orden = $db->loadObject();
                        $forma_pago = $orden->forma_pago;
                        $id_banco = $orden->id_banco;
    
                        if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                            //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                            if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,12,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,293,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,10,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 5) {//BANCO REGIONAL.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,11,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                            elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,292,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                        }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,290,'CHEQUE PARA EL PAGO DEL GASTO N° $nro_gasto',$monto,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }
    
                        $db->setQuery("UPDATE orden_pagos SET id_libro_diario = $id_asiento WHERE id_pago=$id");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                        
                    }elseif ($estado == 2) {
                        //Si anula el pago, se busca si ya cuenta con un asiento contable y se realiza el contra asiento. 
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $id_asiento = $db->loadObject()->id_libro_diario;
    
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
        
                    $db->setQuery("UPDATE gastos SET estado = $estado_gasto WHERE id_gasto = $id_g");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
                }else if(isset($id_proveedor)){
                    $db->setQuery("SELECT * FROM orden_pagos_proveedores WHERE id_pago=$id");
                    $id_f = $db->loadObject()->id_factura;
    
                    $db->setQuery("SELECT g.id_gasto,
                                    g.monto,
                                    g.monto-(SELECT IFNULL(SUM(opp.monto),0)
                                    FROM `orden_pagos_proveedores` opp
                                    JOIN orden_pagos op ON opp.id_pago=op.id_pago
                                    WHERE op.estado=3 AND opp.id_factura=g.id_recepcion_compra) AS pendiente
                                    FROM gastos g
                                    WHERE g.id_recepcion_compra =$id_f ");
                    $row = $db->loadObject();
                    $pendiente = $row->pendiente;
                    $monto = $row->monto;
                    $id_g2= $row->id_gasto;
    
                    if ($pendiente == 0) {
                        $estado_gasto = 2;
                    }else if ($pendiente == $monto){
                        $estado_gasto = 0; 
                    }
                    else if ($pendiente > 0){
                        $estado_gasto = 1;
                    }

                    if ($estado == 3) {
                        $iva_5_asiento = $gravada_5 * 0.05;
                        $iva_10_asiento = $gravada_10 * 0.1;
                        $gravada_suma = $iva_5_asiento + $iva_10_asiento;
                        $retencion_iva = $gravada_suma * 0.3;
    
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
                        VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto,'PAGO DE GASTO N° $nro_gasto','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que aprueba el pago. 
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
    
                        $id_asiento = $db->getLastID();
    
                        //Se realiza el asiento del monto que se paga y se descuenta de la deuda proveedores. 
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,83,'PAGO DE GASTO N° $nro_gasto',$monto,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                        //Se realiza el asiento de la retencion IVA. Que es el 30% del iva. 
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,29,'DEBITO DE RETENCIÓN IVA',0,$retencion_iva);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
    
                        //Se obtiene el banco de la cual pago para poder descontar el monto pagado. 
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $orden = $db->loadObject();
                        $forma_pago = $orden->forma_pago;
                        $id_banco = $orden->id_banco;
    
                        if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                            //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                            if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,12,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,293,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,10,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 5) {//BANCO REGIONAL.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,11,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                            elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,292,'DEBITO POR PAGO DEL GASTO N° $nro_gasto',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                        }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,290,'CHEQUE PARA EL PAGO DEL GASTO N° $nro_gasto',$monto,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }
    
                        $db->setQuery("UPDATE orden_pagos SET id_libro_diario = $id_asiento WHERE id_pago=$id");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
    
                    }elseif ($estado == 2) {
                        //Si anula el pago, se busca si ya cuenta con un asiento contable y se realiza el contra asiento. 
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $id_asiento = $db->loadObject()->id_libro_diario;
    
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
    
                    $db->setQuery("UPDATE gastos SET estado = $estado_gasto WHERE id_gasto = $id_g2");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                        $db->rollback();  //Revertimos los cambios
                        exit;
                    }
                }

                if (isset($id_funcionario)) {
                    $db->setQuery("SELECT * FROM orden_pagos_funcionarios WHERE id_pago=$id");
                    // $id_liquidacion = $db->loadObjectList();
                    $liquidaciones = $db->loadObjectList();
    
                    foreach ($liquidaciones as $r) {
                        $id_liquidacion = $r->id_liquidacion;
    
                        $db->setQuery("SELECT 
                                    l.id_liquidacion,
                                    l.neto_cobrar AS total,
                                    l.neto_cobrar - (SELECT 
                                            IFNULL(SUM(opg.monto),0)
                                            FROM orden_pagos_funcionarios opg
                                            JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                            WHERE op.estado=3 AND opg.id_liquidacion=l.id_liquidacion AND opg.id_pago=op.id_pago) AS pendiente
                                FROM liquidacion_salarios l
                                WHERE l.id_liquidacion = $id_liquidacion");
                        $row = $db->loadObject();
    
                        $pendiente = $row->pendiente;
                        $monto = $row->total;
                        $id_liq= $row->id_liquidacion;
    
                        if ($pendiente == 0) {
                            $estado_liq = 4;
                        }else if ($pendiente == $monto){
                            $estado_liq = 1; 
                        }
                        else if ($pendiente > 0){
                            $estado_liq = 3;
                        }

                        if ($estado == 3) {
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
                            VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto,'PAGO DE SUELDO','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la liquidacion de sueldo.  
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
    
                            $id_asiento = $db->getLastID();
    
                            //Se realiza el asiento del monto a la cuenta de sueldo a pagar
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,121,'SUELDO PAGADO',$monto,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                            
                            //Se realiza el asiento del monto a la cuenta de sueldo a pagar
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,191,'SUELDO PAGADO',$monto,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
    
                            //Se obtiene el banco de la cual pago para poder descontar el monto pagado. 
                            $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                            $orden = $db->loadObject();
                            $forma_pago = $orden->forma_pago;
                            $id_banco = $orden->id_banco;
    
                            if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                                //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                                if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                    VALUES($id_asiento,12,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                    if (!$db->alter()) {
                                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                        $db->rollback();  //Revertimos los cambios
                                        exit;
                                    }
                                }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                    VALUES($id_asiento,293,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                    if (!$db->alter()) {
                                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                        $db->rollback();  //Revertimos los cambios
                                        exit;
                                    }    
                                }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                    VALUES($id_asiento,10,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                    if (!$db->alter()) {
                                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                        $db->rollback();  //Revertimos los cambios
                                        exit;
                                    }
                                }elseif ($id_banco == 5) {//BANCO REGIONAL.
                                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                    VALUES($id_asiento,11,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                    if (!$db->alter()) {
                                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                        $db->rollback();  //Revertimos los cambios
                                        exit;
                                    }
                                }
                                elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                    VALUES($id_asiento,292,'DEBITO POR PAGO DE SUELDO',0,$monto);");
                                    if (!$db->alter()) {
                                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                        $db->rollback();  //Revertimos los cambios
                                        exit;
                                    }
                                }
                            //Si son de los otros bancos, no se registran hasta el momento porque no cuenta con una cuenta en el plan de cuentas.
                            }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                                    $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                    VALUES($id_asiento,290,'CHEQUE PARA EL PAGO DE SUELDO',$monto,0);");
                                    if (!$db->alter()) {
                                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                        $db->rollback();  //Revertimos los cambios
                                        exit;
                                    }
                                }
    
                            $db->setQuery("UPDATE orden_pagos SET id_libro_diario = $id_asiento WHERE id_pago=$id");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }elseif ($estado == 2) {
                            //Buscamos el id_asiento 
                            $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                            $id_asiento = $db->loadObject()->id_libro_diario;
    
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
    
                        $db->setQuery("UPDATE liquidacion_salarios SET estado = $estado_liq WHERE id_liquidacion = $id_liq");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
    
                    }
    
                }

                if (isset($id_caja_chica_sucursal)) {

                    //Recuperamos el monto de la orden de pago.
                    $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago=$id");
                    $pago = $db->loadObject();
                    $monto_o = $pago->monto;
                    $id_cc_sucursal = $pago->id_caja_chica_sucursal;
                    
                    //Se obtiene la ultima caja de esa sucursal para saber si esta rendida o no
                    $db->setQuery("SELECT * FROM caja_chica_sucursal  WHERE id_caja_chica_sucursal = $id_cc_sucursal ORDER BY id_caja_chica_sucursal DESC LIMIT 1");
                    $ultima_cc = $db->loadObject();
                    $id_caja_chica = $ultima_cc->id_caja_chica;
    
                    //Recuperamos la configuracion de la caja chica.
                    $db->setQuery("SELECT * FROM caja_chica WHERE id_caja_chica = $id_caja_chica");
                    $caja_chica = $db->loadObject();
                    $monto_caja = $caja_chica->monto;
    
                    //Validamos si el monto final coincide con el monto de la caja 
                    if ($monto_o != $monto_caja) {
                        echo json_encode(["status" => "error", "mensaje" => "No se esta cubriendo el total de la caja chica. Favor verifique."]);
                        exit;
                    }

                    if ($estado == 3) {

                        $db->setQuery("SELECT * FROM caja_chica_sucursal WHERE id_caja_chica = $id_caja_chica AND estado = 1");
                        $activo = $db->loadObject();
                        //Validamos que no este un movimiento que le falte rendicion.
                        if ($activo) {
                            echo json_encode(["status" => "error", "mensaje" => "Hay Movimiento con Rendición Pendiente. Favor verifique."]);
                            exit;
                        }

                        //Buscamos el detalle de la orden
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $orden = $db->loadObject();
                        $forma_pago = $orden->forma_pago;

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
                        VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto_caja,'CAJA CHICA','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la carga del gasto.  
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }

                        $id_asiento = $db->getLastID();

                        //Se realiza el asiento del monto a la cuenta de caja chica. 
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,289,'ACREDITAR CAJA CHICA',$monto_caja,0);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }

                        if ($forma_pago == 1 ||$forma_pago == 3) {//Si es transferencia o efectivo se registra en las cuentas de los bancos
                            //Se pregunta de que banco es, asi se descuenta el monto de la cuenta correspondiente. 
                            if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,12,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,293,'DEBITO POR CAJA CHICA',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }    
                            }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,10,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }elseif ($id_banco == 5) {//BANCO REGIONAL.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,11,'DEBITO POR CAJA CHICA',0,$monto_caja);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                            elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                                VALUES($id_asiento,292,'DEBITO POR CAJA CHICA',0,$monto);");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                    $db->rollback();  //Revertimos los cambios
                                    exit;
                                }
                            }
                        }elseif ($forma_pago == 2) {//Si es cheque se registra en la cuenta de cheques diferido emitidos
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,290,'CHEQUE PARA CAJA CHICA',$monto_caja,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                                $db->rollback();  //Revertimos los cambios
                                exit;
                            }
                        }

                        $db->setQuery(" UPDATE `caja_chica_sucursal` 
                                        SET
                                            saldo = $monto_o,
                                            sobrante = $monto_o,
                                            `fecha_apertura` = NOW(),
                                            `usuario_apertura` = '$usuario',
                                            `estado` = 1
                                        WHERE `id_caja_chica_sucursal` = $id_caja_chica_sucursal;");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago. Code: ".$db->getErrorCode()]);
                            exit;
                        }

                        $db->setQuery("UPDATE `orden_pagos` SET `id_libro_diario` = $id_asiento WHERE `id_pago` = $id;");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago para la caja chica. Code: ".$db->getErrorCode()]);
                            $db->rollback();  //Revertimos los cambios
                            exit;
                        }
                    }elseif ($estado == 2) {
                        //Se busca el id_asiento.
                        $db->setQuery("SELECT * FROM orden_pagos WHERE id_pago = $id");
                        $row = $db->loadObject();
                        $id_asiento = $row->id_libro_diario;
                        $id_caja_chica = $row->id_caja_chica_sucursal;
    
                        //Buscamos si la caja ya tiene gastos.
                        $db->setQuery("SELECT * FROM caja_chica_facturas WHERE id_caja_chica_sucursal = $id_caja_chica");
                        $rows_f = $db->loadObjectList();
                        
                        //Si la caja cuenta con facturas no se puede cancelar el pago. 
                        if (!empty($rows_f)) {
                            echo json_encode(["status" => "error", "mensaje" => "No se puede anular el pago.La caja ya cuenta con gastos cargados."]);
                            exit;
                        }
    
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
    
                        $db->setQuery(" UPDATE `caja_chica_sucursal` 
                                    SET
                                        `estado` = 0
                                    WHERE `id_caja_chica_sucursal` = $id_caja_chica;");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago. Code: ".$db->getErrorCode()]);
                            exit;
                        }
    
                    }
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'ver_detalles':
            $db = DataBase::conectar();
            $id_pago = $db->clearText($_GET['id_pago']);

            $db->setQuery("SELECT 
                                id_pago_proveedor,
                                opp.id_recepcion_compra_vencimiento,
                                opp.id_pago,
                                id_factura,
                                opp.monto,
                                rc.numero_documento,
                                DATE_FORMAT(rcp.vencimiento,'%d/%m/%Y') AS vencimiento,
                                CASE
                                    WHEN rc.condicion = 1 THEN 'CONTADO'
                                    WHEN rc.condicion = 2 THEN 'CRÉDITO'
                                END AS condicion,
                                p.proveedor,
                                IF(opp.id_recepcion_compra_vencimiento IS NULL, rc.total_costo, rcp.monto) AS total_costo
                            FROM orden_pagos_proveedores opp
                            LEFT JOIN orden_pagos op ON opp.id_pago=op.id_pago
                            LEFT JOIN recepciones_compras_vencimientos rcp ON opp.id_recepcion_compra_vencimiento=rcp.id_recepcion_compra_vencimiento
                            LEFT JOIN recepciones_compras rc ON opp.id_factura=rc.id_recepcion_compra
                            LEFT JOIN proveedores p ON op.id_proveedor=p.id_proveedor
                            WHERE opp.id_pago =$id_pago");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'ver_detalles_caja_chica':
            $db = DataBase::conectar();
            $id_pago = $db->clearText($_GET['id_pago']);

            $db->setQuery("SELECT
                                op.`id_caja_chica_sucursal`,
                                DATE_FORMAT(op.`fecha`, '%d/%m/%Y') AS fecha,
                                op.`concepto`,
                                op.`monto`,
                                f.`funcionario`
                            FROM
                                orden_pagos op
                            LEFT JOIN caja_chica_sucursal ccs ON op.`id_caja_chica_sucursal`=ccs.`id_caja_chica_sucursal`
                            LEFT JOIN caja_chica cc ON cc.`id_caja_chica` = ccs.`id_caja_chica`
                            LEFT JOIN funcionarios f ON f.`id_funcionario` = cc.`id_funcionario`
                            WHERE op.`id_pago` = $id_pago");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'ver_detalles_gasto':
            $db = DataBase::conectar();
            $id_pago = $db->clearText($_GET['id_pago']);

            $db->setQuery("SELECT 
                            id_pago_gasto,
                            opg.id_pago,
                            opg.monto as total_costo,
                            p.proveedor,
                            CASE g.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion,
                            DATE_FORMAT(g.fecha_vencimiento , '%d/%m/%Y') AS fecha_vencimiento,
                            g.nro_gasto as numero_documento
                        FROM orden_pago_gastos opg
                        LEFT JOIN orden_pagos op ON opg.id_pago=op.id_pago
                        LEFT JOIN proveedores p ON op.id_proveedor_gasto=p.id_proveedor
                        LEFT JOIN gastos g ON opg.id_gasto=g.id_gasto
                        WHERE opg.id_pago = $id_pago");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'ver_archivos':
            $db = DataBase::conectar();
            $id_pago = $db->clearText($_GET['id_orden_pago']);

            $db->setQuery("SELECT 
                                id_orden_pago_archivo AS id,
                                archivo,
                                nro_documento,
                                tipo_documento,
                                CASE tipo_documento WHEN 1 THEN 'RECIBO' WHEN 2 THEN 'TRANSFERENCIA' END AS documento
                            FROM orden_pagos_archivos
                            WHERE id_pago=$id_pago");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'cargar_archivo':
            $db = DataBase::conectar();
            $db->autocommit(false);

            $id = $db->clearText($_POST['id_pago']);
            $nro_documento = $db->clearText($_POST['nro_documento']);
            $tipo_documento = $db->clearText($_POST['tipo']);
            $archivo = $_FILES['archivos'];

            try {
                $foo = new \Verot\Upload\Upload($archivo);

                if ($foo->uploaded) {
                    $targetPath = "../archivos/orden_pagos/";
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0777, true);
                    }

                    $foo->file_new_name_body    = md5($id);
                    $foo->image_convert              = jpg;
                    $foo->image_resize                 = true;
                    $foo->image_ratio                   = true;
                    $foo->image_ratio_crop          = true;
                    $foo->image_ratio_y                = true;
                    $foo->image_y                         = 640;
                    $foo->image_x                         = 640;
                    $foo->process($targetPath);
                    $archivo = str_replace("../", "", $targetPath . $foo->file_dst_name);
                    if ($foo->processed) {
                        $db->setQuery("INSERT INTO orden_pagos_archivos (id_pago, archivo, nro_documento, tipo_documento) VALUES ($id,'$archivo','$nro_documento', $tipo_documento)");
                        if (!$db->alter()) {
                            throw new Exception("Error de base de datos al guardar los archivos. Code: " . $db->getErrorCode());
                        }
                        $foo->clean();
                    } else {
                        throw new Exception("Error al guardar los archivos: " . $foo->error);
                    }
                } else {
                    throw new Exception("Archivo no encontrado");
                }
            } catch (Exception $e) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                exit;
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Archivo guardado correctamente"]);
        break;

        case 'eliminar_archivo':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_POST['id']);

            $db->setQuery("SELECT archivo FROM orden_pagos_archivos WHERE id_orden_pago_archivo=$id");
            $row = $db->loadObject();
            $archivo = $row->archivo;

            if (empty($row)) {
                echo json_encode(["status" => "error", "mensaje" => "Archivo no encontrado"]);
                exit;
            }

            $db->setQuery("DELETE FROM orden_pagos_archivos WHERE id_orden_pago_archivo=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al eliminar el archivo. Code: ".$db->getErrorCode()]);
                exit;
            }

            if (!unlink("../".$archivo)) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error al eliminar el archivo"]);
                exit;
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Archivo eliminado correctamente"]);
        break;

        case 'ver_detalles_liquidacion':
            $db = DataBase::conectar();
            $id_pago = $db->clearText($_GET['id_pago']);

            $db->setQuery("SELECT 
                                opf.id_liquidacion,
                                opf.monto,
                                ls.neto_cobrar,
                                ls.periodo,
                                ls.funcionario,
                                ls.ci
                            FROM orden_pagos_funcionarios opf
                            LEFT JOIN liquidacion_salarios ls ON opf.id_liquidacion=ls.id_liquidacion
                            WHERE opf.id_pago = $id_pago");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

	}
