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
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $id_proveedor_gasto= $db->clearText($_POST['proveedor_gasto']);
            $id_banco = $db->clearText($_POST['id_banco']);
            $id_sucursal_caja = $db->clearText($_POST['id_sucursal']);
            $destino_pago = $db->clearText($_POST['destino_pago']);
            $forma_pago = $db->clearText($_POST['forma_pago']);
            $cheque = $db->clearText($_POST['cheque']);
            $cuenta = $db->clearText($_POST['cuenta']);
            $monto = $db->clearText(quitaSeparadorMiles($_POST['monto']));
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
            $nro_movimiento = $db->clearText($_POST['nro_movimiento']);
            $monto_cc = $db->clearText(quitaSeparadorMiles($_POST['monto_cc']));
            $detalles = json_decode($_POST['detalles'], true);
            $detalles_gastos = json_decode($_POST['detalles_gastos'], true);
            $detalles_liquidaciones = json_decode($_POST['detalles_liquidaciones'], true);
            $cuenta_destino = $db->clearText($_POST['cuenta_destino']);
            $fecha_pago = $db->clearText($_POST['fecha']);

            if($destino_pago == 1){
                if (empty($id_proveedor)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);
                    exit;
                }
                $id_funcionario = 'null';
                $id_proveedor_gasto = 'null';
                $id_caja_chica_sucursal = 'null';
            }else if ($destino_pago == 2){
                if (empty($id_funcionario)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un funcionario"]);
                    exit;
                }
                $id_proveedor = 'null';
                $id_proveedor_gasto = 'null';
                $id_caja_chica_sucursal = 'null';
            }else if ($destino_pago == 3){
                if (empty($id_proveedor_gasto)) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);
                    exit;
                }
                $id_proveedor = 'null';
                $id_funcionario = 'null';
                $id_caja_chica_sucursal = 'null';
            }else if($destino_pago == 4){
                $id_proveedor = 'null';
                $id_funcionario = 'null';
                $id_caja_chica_sucursal = 'null';
                $id_proveedor_gasto = 'null';
                if (empty($monto_cc)) {
                    echo json_encode(["status" => "error", "mensaje" => "No hay una configuracion de caja chica para la sucursal."]);
                    exit;
                }
            }
            
            if (empty($id_banco)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un Banco"]);
                exit;
            }
            if (empty($detalles) && $destino_pago == 1) {
                echo json_encode(["status" => "error", "mensaje" => "Debe seleccionar una factura. Favor verifique."]);
                exit;
            }
            if(empty($detalles_gastos) && $destino_pago == 3){
                echo json_encode(["status" => "error", "mensaje" => "Debe seleccionar un gasto. Favor verifique."]);
                exit;
            }
            if(empty($detalles_liquidaciones) && $destino_pago == 2){
                echo json_encode(["status" => "error", "mensaje" => "Debe seleccionar una liquidación. Favor verifique."]);
                exit;
            }
            

            // Se calcula el total costo y se extrae el ID de cada solicitud
            $total_costo = 0;
            $solicitudes = [];

            if ($destino_pago == 1 ) {
                foreach ($detalles as $p) {

                    // Cálculo del costo
                    $costo = $db->clearText(quitaSeparadorMiles($p["monto"]));
                    $total_costo += $costo;
    
                }
            }else if ($destino_pago == 3 ) {
                foreach ($detalles_gastos as $g) {

                    // Cálculo del costo
                    $costo = $db->clearText(quitaSeparadorMiles($g["monto"]));
                    $total_costo += $costo;
    
                }
            }else if ($destino_pago == 2 ) {
                foreach ($detalles_liquidaciones as $g) {

                    // Cálculo del costo
                    $costo = $db->clearText(quitaSeparadorMiles($g["monto"]));
                    $total_costo += $costo;
    
                }
            }else if ($destino_pago == 4){
                $total_costo = $monto_cc;
            }
            

            $db->setQuery("SELECT MAX(numero) AS numero FROM orden_pagos");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            // Cabeceera
            $db->setQuery("INSERT INTO orden_pagos (numero, fecha, fecha_pago, concepto, id_banco, id_proveedor, id_funcionario, id_proveedor_gasto,id_caja_chica_sucursal, destino_pago, forma_pago, nro_cuenta, nro_cheque, cuenta_destino, monto, observacion, usuario)
                            VALUES ('$numero', NOW(), '$fecha_pago', '$concepto', $id_banco, $id_proveedor, $id_funcionario, $id_proveedor_gasto,$id_caja_chica_sucursal, $destino_pago, $forma_pago, '$cuenta', '$cheque', '$cuenta_destino', $total_costo, '$observacion','$usuario')");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago. Code: ".$db->getErrorCode()]);
                exit;
            }

            $id_pago = $db->getLastID();

            if ($destino_pago == 1) {

                foreach ($detalles as $p) {

                    $id_factura = $db->clearText($p["id_recepcion_compra"]);
                    $id_factura_vencimiento = $db->clearText($p["id_recepcion_compra_vencimiento"]);
                    if(empty($id_factura_vencimiento)){
                        $id_factura_vencimiento ='NULL';
                    }
                    $numero_documento = $db->clearText($p["numero_documento"]);
                    $monto = $db->clearText(quitaSeparadorMiles($p["monto"]));
                    $pendiente = $db->clearText(quitaSeparadorMiles($p["pendiente"]));
    
                    try {
                        if (empty($monto)) {
                            throw new Exception("La factura \"$numero_documento\" tiene monto 0. Favor verifique.");
                        }
    
                        if ($monto > $pendiente) {
                            throw new Exception("El monto a pagar de la factura \"$numero_documento\" es mayor al monto pendiente");
                        }
    
                        $db->setQuery("INSERT INTO orden_pagos_proveedores (id_pago, id_factura, id_recepcion_compra_vencimiento, monto)
                                        VALUES ($id_pago, $id_factura, $id_factura_vencimiento, $monto)");
                        if (!$db->alter()) {
                            throw new Exception("Error de base de datos al registrar la factura en la orden de pago. Code: ".$db->getError());
                        }
    
                    } catch (Exception $e) {
                        $db->rollback();  // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                        exit;
                    }
    
                }
            }else if ($destino_pago == 3){

                foreach ($detalles_gastos as $g){
                    $id_gasto = $db->clearText($g["id_gasto"]);
                    $id_gasto_vencimiento = $db->clearText($g["id_gasto_vencimiento"]);
                    if(empty($id_gasto_vencimiento)){
                        $id_gasto_vencimiento ='NULL';
                    }
                    $monto = $db->clearText(quitaSeparadorMiles($g["monto"]));
                    $nro_gasto = $db->clearText(quitaSeparadorMiles($g["nro_gasto"]));
                    $pendiente = $db->clearText(quitaSeparadorMiles($g["pendiente"]));

                    try {
                        if (empty($monto)) {
                            throw new Exception("El gasto \"$nro_gasto\" tiene monto 0. Favor verifique.");
                        }
    
                        if ($monto > $pendiente) {
                            throw new Exception("El monto a pagar del gasto \"$nro_gasto\" es mayor al monto pendiente");
                        }
    
                        $db->setQuery("INSERT INTO orden_pago_gastos (id_pago, id_gasto, monto, id_gasto_vencimiento)
                                        VALUES ($id_pago, $id_gasto, $monto, $id_gasto_vencimiento)");
                        if (!$db->alter()) {
                            throw new Exception("Error de base de datos al registrar el gasto en la orden de pago. Code: ".$db->getError());
                        }
    
                    } catch (Exception $e) {
                        $db->rollback();  // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                        exit;
                    }
                }
            }else if ($destino_pago == 2){

                foreach ($detalles_liquidaciones as $l){
                    $id_liquidacion = $db->clearText($l["id_liquidacion"]);
                    $monto = $db->clearText(quitaSeparadorMiles($l["monto"]));
                    $periodo = $db->clearText(quitaSeparadorMiles($l["periodo"]));
                    $pendiente = $db->clearText(quitaSeparadorMiles($l["pendiente"]));

                    try {
                        if (empty($monto)) {
                            throw new Exception("La liquidación del período \"$periodo\" tiene monto 0. Favor verifique.");
                        }
    
                        if ($monto > $pendiente) {
                            throw new Exception("El monto a pagar del período \"$periodo\" es mayor al monto pendiente");
                        }
    
                        $db->setQuery("INSERT INTO orden_pagos_funcionarios (id_pago, id_liquidacion, monto)
                                        VALUES ($id_pago, $id_liquidacion, $monto)");
                        if (!$db->alter()) {
                            throw new Exception("Error de base de datos al registrar el gasto en la orden de pago. Code: ".$db->getError());
                        }
    
                    } catch (Exception $e) {
                        $db->rollback();  // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                        exit;
                    }
                }
            }else if($destino_pago == 4){

                //Se busca la configuracion de la caja chica.
                $db->setQuery("SELECT * FROM caja_chica WHERE id_sucursal = $id_sucursal_caja");
                $row = $db->loadObject();
                $monto_caja = $row->monto;
                $id_caja_chica = $row->id_caja_chica;


                $db->setQuery("INSERT INTO caja_chica_sucursal (id_caja_chica,cod_movimiento,estado) VALUES ($id_caja_chica,'$nro_movimiento', 0);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago para la caja chica. Code: ".$db->getErrorCode()]);
                    exit;
                }

                $id_caja_chica_sucursal = $db->getLastID();

                $db->setQuery("UPDATE `orden_pagos` SET `id_caja_chica_sucursal` = $id_caja_chica_sucursal WHERE `id_pago` = $id_pago;");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de pago para la caja chica. Code: ".$db->getErrorCode()]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Orden de pago registrada correctamente", "id_pago" => $id_pago]);
            
        break;

        case 'ver_facturas':
            $db = DataBase::conectar();
            $id_proveedor = $db->clearText($_GET["id_proveedor"]);

            $db->setQuery("SELECT * 
                            FROM(SELECT 
                                rc.id_recepcion_compra,
                                rcv.id_recepcion_compra_vencimiento,
                                rc.id_proveedor,
                                rc.numero_documento,
                                p.proveedor,
                                DATE_FORMAT(rcv.vencimiento,'%d/%m/%Y') AS vencimiento,
                                rcv.monto - (
                                        SELECT IFNULL(SUM(opp.monto), 0)
                                        FROM orden_pagos_proveedores opp
                                        LEFT JOIN orden_pagos op
                                        ON opp.id_pago = op.id_pago
                                        WHERE op.estado IN(1,3) AND opp.id_factura=rc.id_recepcion_compra AND opp.id_recepcion_compra_vencimiento=rcv.id_recepcion_compra_vencimiento
                                        ) AS pendiente,
                                
                                'CREDITO' AS condicion,
                                rcv.monto AS total_costo,
                                rcv.monto - (
                                        SELECT IFNULL(SUM(opp.monto), 0)
                                        FROM orden_pagos_proveedores opp
                                        LEFT JOIN orden_pagos op
                                        ON opp.id_pago = op.id_pago
                                        WHERE op.estado IN(1,3) AND opp.id_factura=rc.id_recepcion_compra AND opp.id_recepcion_compra_vencimiento=rcv.id_recepcion_compra_vencimiento
                                        ) AS monto
                            FROM recepciones_compras_vencimientos rcv
                            JOIN recepciones_compras rc
                            ON rcv.id_recepcion_compra=rc.id_recepcion_compra
                            LEFT JOIN proveedores p ON rc.id_proveedor=p.id_proveedor
                            WHERE rc.estado = 1 AND rc.id_proveedor=$id_proveedor AND rc.condicion=2

                            UNION ALL

                            SELECT 
                                rc.id_recepcion_compra,
                                NULL AS id_recepcion_compra_vencimiento,
                                rc.id_proveedor,
                                rc.numero_documento,
                                p.proveedor,
                                NULL AS fecha,
                                total_costo - (
                                        SELECT IFNULL(SUM(opp.monto), 0)
                                        FROM orden_pagos_proveedores opp
                                        LEFT JOIN orden_pagos op
                                        ON opp.id_pago = op.id_pago
                                        WHERE op.estado IN(1,3) AND opp.id_factura=rc.id_recepcion_compra
                                        ) AS pendiente,
                                'CONTADO' AS condicion,
                                total_costo,
                                total_costo - (
                                        SELECT IFNULL(SUM(opp.monto), 0)
                                        FROM orden_pagos_proveedores opp
                                        LEFT JOIN orden_pagos op
                                        ON opp.id_pago = op.id_pago
                                        WHERE op.estado IN(1,3) AND opp.id_factura=rc.id_recepcion_compra
                                        ) AS monto
                            FROM recepciones_compras rc
                            LEFT JOIN proveedores p ON rc.id_proveedor=p.id_proveedor
                            WHERE estado = 1 AND rc.id_proveedor=$id_proveedor AND condicion= 1)a
                            WHERE pendiente > 0");
            $row = ($db->loadObjectList()) ?: [];
            echo json_encode($row);
        break;

        case 'ver_gastos':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET["id_proveedor"]);

            $db->setQuery("SELECT * 
                            FROM (SELECT 
                                    g.id_gasto,
                                    NULL AS id_gasto_vencimiento,
                                    g.nro_gasto,
                                    g.id_proveedor,
                                    p.proveedor,
                                    DATE_FORMAT(g.fecha_vencimiento,'%d/%m/%Y') AS vencimiento,
                                    g.monto AS total,
                                    
                                    g.monto - (SELECT IFNULL(SUM(opg.monto),0)
                                            FROM orden_pago_gastos opg
                                            JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                            WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago) AS pendiente,
                                    'CONTADO' AS condicion,
                                    g.monto - (SELECT IFNULL(SUM(opg.monto),0)
                                            FROM orden_pago_gastos opg
                                            JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                            WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago) AS monto
                                FROM gastos g 
                                LEFT JOIN proveedores p ON g.id_proveedor=p.id_proveedor
                                WHERE g.id_proveedor = $id AND g.condicion=1
                                
                                
                                UNION ALL

                                SELECT 
                                g.id_gasto,
                                gv.id_gasto_vencimiento,
                                g.nro_gasto,
                                g.id_proveedor,
                                p.proveedor,
                                DATE_FORMAT(gv.vencimiento,'%d/%m/%Y') AS vencimiento,
                                gv.monto AS total,
                                gv.monto - (
                                            SELECT IFNULL(SUM(opg.monto), 0)
                                            FROM `orden_pago_gastos` opg
                                            LEFT JOIN orden_pagos op
                                            ON opg.id_pago = op.id_pago
                                        WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_gasto_vencimiento=gv.id_gasto_vencimiento
                                            ) AS pendiente,
                                
                                'CREDITO' AS condicion,
                                gv.monto - (
                                        SELECT IFNULL(SUM(opg.monto), 0)
                                        FROM `orden_pago_gastos` opg
                                        LEFT JOIN orden_pagos op
                                        ON opg.id_pago = op.id_pago
                                        WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_gasto_vencimiento=gv.id_gasto_vencimiento
                                        ) AS monto
                            FROM gastos_vencimientos gv
                            JOIN gastos g
                            ON gv.id_gasto=g.id_gasto
                            LEFT JOIN proveedores p ON g.id_proveedor=p.id_proveedor
                            WHERE g.id_proveedor= $id AND g.condicion=2)a
                            WHERE pendiente > 0");

            $row = ($db->loadObjectList()) ?: [];
            echo json_encode($row);
        break;

        case 'ver_liquidaciones':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET["id_funcionario"]);

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS
                                id_liquidacion,
                                id_funcionario,
                                DATE(fecha) AS fecha,
                                funcionario,
                                CASE
                                    WHEN forma_pago = 1 THEN 'EFECTIVO'
                                    WHEN forma_pago = 2 THEN 'CHEQUE'
                                    WHEN forma_pago = 3 THEN 'TRANSFERENCIA'
                                END AS forma_pago,
                                ci,
                                neto_cobrar AS total,
                                neto_cobrar - (SELECT 
                                        IFNULL(SUM(opg.monto),0)
                                        FROM orden_pagos_funcionarios opg
                                        JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                        WHERE op.estado=3 AND opg.id_liquidacion=l.id_liquidacion AND opg.id_pago=op.id_pago) AS pendiente,
                                neto_cobrar - (SELECT 
                                        IFNULL(SUM(opg.monto),0)
                                        FROM orden_pagos_funcionarios opg
                                        JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                        WHERE op.estado=3 AND opg.id_liquidacion=l.id_liquidacion AND opg.id_pago=op.id_pago) AS monto,
                                periodo,
                                usuario,
                                estado
                            FROM liquidacion_salarios l
                            WHERE id_funcionario = $id AND estado IN (1,3)");

            $row = ($db->loadObjectList()) ?: [];
            echo json_encode($row);
        break;

        case 'recuperar-numero':
            $db       = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM orden_pagos");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

        case 'siguiente-cod-movimiento':
            $db       = DataBase::conectar();
            $id       = $db->clearText($_REQUEST["id"]);

            $db->setQuery("SELECT * FROM caja_chica WHERE id_sucursal = $id");
            $cc_configuracion = $db->loadObject();
            $monto = $cc_configuracion->monto;
            $id_cc = $cc_configuracion->id_caja_chica;

            if (empty($cc_configuracion)) {
                echo json_encode(["status" => "error", "mensaje" => "La sucursal no cuenta con una caja chica configurada."]);
                exit;
            }
             

            $db->setQuery("SELECT
                                MAX(cod_movimiento) AS codigo
                            FROM
                                caja_chica_sucursal
                            WHERE id_caja_chica = $id_cc");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el Nro. movimiento."]);
                exit;
            }

            $sgte_codigo = intval($row->codigo) + 1;
            $codigo = $sgte_codigo;

            echo json_encode(["status" => "ok", "mensaje" => "Codigo", "codigo" => str_pad($codigo, 4, '0',  STR_PAD_LEFT),"Monto", "monto" => $monto]);
        break;

        case 'verificar-total-egreso':

            $db = DataBase::conectar();
            $db->setQuery("SELECT SUM(monto) as monto FROM orden_pagos WHERE estado = 3 AND DATE(fecha) = CURRENT_DATE()");
            $total_pago = $db->loadObject()->monto;

            $db->setQuery("SELECT IFNULL(limite_egreso,0) AS limite FROM configuracion");
            $limite = $db->loadObject()->limite;

            if ($total_pago >= $limite) {
                echo json_encode(["status" => "warning","mensaje" => "Ya ha alcanzado el Límite de Egresos por día."]);
                exit;
            }
            
        break;

	}

?>
