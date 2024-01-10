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
        $db    = DataBase::conectar();
        $where = "";

        $sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $desde = $db->clearText($_REQUEST['desde']);
        $hasta = $db->clearText($_REQUEST['hasta']);
        $proveedor = $db->clearText($_REQUEST['proveedor']);

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $where = "AND CONCAT_WS(' ', f.funcionario, f.ci, f.direccion ,p.puesto, f.telefono, f.celular) LIKE '%$search%'";
        }
        
        if (!empty($_REQUEST['id_sucursal']) && intVal($sucursal) != 0) {
           $where .= " AND g.id_sucursal=$sucursal";
        }else{
            $where .= "";
        }
        if (!empty($desde)) {
            $where_fecha .= " AND g.fecha_emision BETWEEN '$desde' AND '$hasta'";
        }else{
            $where_fecha .= "";
        }
        if (!empty($proveedor && intVal($proveedor) != 0)) {
            $where_proveedor .= " AND g.ruc='$proveedor'";
        }else{
            $where_proveedor .= "";
        }
        

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                        g.id_gasto,
                        g.id_sub_tipo_gasto,
                        g.id_tipo_gasto,
                        g.id_recepcion_compra,
                        tg.nombre,
                        g.id_sucursal,
                        s.sucursal,
                        g.id_tipo_comprobante,
                        tc.nombre_comprobante,
                        g.nro_gasto,
                        g.timbrado,
                        g.fecha_emision,
                        DATE_FORMAT(g.fecha_emision, '%d/%m/%Y') AS fecha_emision_str,
                        g.ruc,
                        g.razon_social,
                        g.condicion,
                        g.fecha_vencimiento,
                        DATE_FORMAT(g.fecha_vencimiento, '%d/%m/%Y') AS fecha_vencimiento_str,
                        CASE g.condicion
                        WHEN 1 THEN 'CONTADO'
                        WHEN 2 THEN 'CREDITO' END AS 'condicion_str',
                        CASE g.imputa_iva
                        WHEN 1 THEN 'S'
                        WHEN 0 THEN 'N' END AS 'imputa_iva',
                        CASE g.imputa_ire
                        WHEN 1 THEN 'S'
                        WHEN 0 THEN 'N' END AS 'imputa_ire',
                        CASE g.imputa_irp
                        WHEN 1 THEN 'S'
                        WHEN 0 THEN 'N' END AS 'imputa_irp',
                        g.documento,
                        g.concepto,
                        g.monto,
                        g.exenta,
                        g.gravada_10,
                        g.gravada_5,
                        g.observacion,
                        CASE g.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Pagado Parcial' WHEN 2 THEN 'Pagado' WHEN 3 THEN 'Anulado' END AS estado_str,
                        g.tipo_proveedor,
                        g.id_proveedor,
                        g.estado,
                        g.usuario,
                        g.`id_libro_cuentas`,
                        CONCAT(lc.cuenta, ' - ',lc.denominacion) AS denominacion,
                        CASE g.deducible WHEN 1 THEN 'DEDUCIBLE' WHEN 2 THEN 'NO DEDUCIBLE' END AS deducible_str,
                        IF( g.id_caja_chica IS NULL, 
                        IF(g.id_recepcion_compra IS NULL,
                            g.monto - (SELECT IFNULL(SUM(opg.monto),0)
                                    FROM orden_pago_gastos opg
                                    JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                    WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago),
                            
                            g.monto - (SELECT IFNULL(SUM(opp.monto),0)
                                    FROM orden_pagos_proveedores opp
                                    JOIN orden_pagos op ON opp.id_pago=op.id_pago
                                    WHERE op.estado=3 AND opp.id_factura=g.id_recepcion_compra)), 0) 
                        AS pendiente,

                        IF(g.id_caja_chica IS NULL,            
                        IF(g.id_recepcion_compra IS NULL,
                            (SELECT IFNULL(SUM(opg.monto),0)
                                    FROM orden_pago_gastos opg
                                    JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                    WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago),
                            
                            (SELECT IFNULL(SUM(opp.monto),0)
                            FROM orden_pagos_proveedores opp
                            JOIN orden_pagos op ON opp.id_pago=op.id_pago
                            WHERE op.estado=3 AND opp.id_factura=g.id_recepcion_compra)), g.monto) 
                        AS pagado
                        
                        FROM gastos g
                        LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                        LEFT JOIN tipos_gastos tg ON tg.id_tipo_gasto = g.id_tipo_gasto
                        LEFT JOIN sucursales s ON s.id_sucursal = g.id_sucursal
                        LEFT JOIN libro_cuentas lc ON lc.id_libro_cuenta = g.`id_libro_cuentas`
                        WHERE 1=1 $where $where_fecha $where_proveedor
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

    case 'cargar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id_tipo_gasto = $db->clearText($_POST['id_tipo_gasto']);
        $id_sucursal = $db->clearText($_POST['id_sucursal']);
        $id_plan_cuenta = $db->clearText($_POST['id_plan_cuenta']);
        $timbrado = $db->clearText($_POST['timbrado']);
        $fecha_emision = $db->clearText($_POST['emision']);
        $nro_gasto =  zerofill($db->clearText($_POST['nro']));
        $ruc = $db->clearText($_POST['ruc']);
        $entidad = $db->clearText($_POST['razon_social']);
        $condicion = $db->clearText($_POST['condicion']);
        $id_tipo_comprobante = $db->clearText($_POST['id_tipo_factura']);
        $nro_documento = $db->clearText($_POST['documento']);
        $check_iva = $db->clearText($_POST['check_iva']) ?: 0;
        $check_ire = $db->clearText($_POST['check_ire'])?: 0;
        $check_irp = $db->clearText($_POST['check_irp'])?: 0;
        $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
        $gravada_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']));
        $gravada_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']));
        $exenta = quitaSeparadorMiles($db->clearText($_POST['exenta']));
        $observacion = $db->clearText($_POST['observacion']);
        $tipo_proveedor = $db->clearText($_POST['tipo_proveedor']);
        $vencimientos = json_decode($_POST['vencimientos'], true);


        if (empty($id_tipo_gasto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo de gasto."]);
            exit;
        }
        if (empty($id_sucursal)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Sucursal."]);
            exit;
        }
        if (empty($id_plan_cuenta)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Plan Cuenta."]);
            exit;
        }
        if (empty($timbrado)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Timbrado."]);
            exit;
        }
        if (empty($fecha_emision)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Fecha de Emision."]);
            exit;
        }
        if (empty($ruc)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo RUC."]);
            exit;
        }
        if (empty($condicion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Condicion."]);
            exit;
        }
        if ($condicion == 2 && empty($vencimientos)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese a menos un vencimiento."]);
            exit;
        }
        if (empty($id_tipo_comprobante)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo de Factura."]);
            exit;
        }
        if (empty($concepto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Concepto."]);
            exit;
        }
        if (empty($monto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto."]);
            exit;
        }
        if ($monto == 0) {
            echo json_encode(["status" => "error", "mensaje" => "No puede cargar Facturas con Monto 0."]);
            exit;
        }
        if (empty($tipo_proveedor)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de proveedor."]);
            exit;
        }
        
        if ($check_iva == 0 && $check_ire == 0 && $check_irp == 0) {
            $no_imputa = 1;
        }else{
            $no_imputa = 0;
        }
        if ($id_tipo_comprobante == '12' || $id_tipo_comprobante == '13') {
            $nro_compro = $nro_documento;
            $timbr_copro = $timbrado;
        }

        $db->setQuery("SELECT proveedor FROM proveedores WHERE id_proveedor = $entidad");
        $id_proveedor = $entidad;
        $entidad = $db->clearText($db->loadObject()->proveedor);

        //Buscamos el libro diario activo actualmente
        $db->setQuery("SELECT id_libro_diario_periodo, estado FROM libro_diario_periodo WHERE estado = 1");
        $id_libro_diario_periodo = $db->loadObject()->id_libro_diario_periodo;

        //Sacar el IVA para el asiento
        $iva_5_asiento = $gravada_5 * 0.05;
        $iva_10_asiento = $gravada_10 * 0.1;
        $gravada_5_sin_iva  = round($gravada_5 - ($gravada_5 * 0.05));
        $gravada_10_sin_iva = round($gravada_10 - ($gravada_10 * 0.1));
        $gravada_suma = $iva_5_asiento + $iva_10_asiento;
        $total_gasto_sin_iva = $monto - $gravada_suma;

        //Obtenemos el nro de asiento
        $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
        $nro_asiento = $db->loadObject()->nro_asiento;

        if (empty($nro_asiento)) {
            $nro = zerofillAsiento(1);
        } else {
            $nro = zerofillAsiento(intval($nro_asiento) + 1);
        }

        //Se carga la cabecera del asiento
        $db->setQuery("INSERT INTO `libro_diario` (`id_libro_diario_periodo`,`id_comprobante`,`fecha`,`nro_asiento`,`importe`,`descripcion`,`usuario`,`fecha_creacion`)
        VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$monto,'$concepto','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la carga del gasto.  
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $id_asiento = $db->getLastID();

        //Se cargan los detalles del asiento. Se cargan los IVA correspondiente. 
        //Solo se realiza el asiento si el gasto contiene productos con iva 5%.
        if($iva_5_asiento > 0){
            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,32,'IVA 5%',$iva_5_asiento,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }

        //Solo se realiza el asiento si el gasto contiene productos con iva 10%.
        if($iva_10_asiento > 0){
            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,31,'IVA 10%',$iva_10_asiento,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }

        //Se realiza el asiento del monto a la cuenta de deudas con proveedores
        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
        VALUES($id_asiento,83,'$concepto',0,$monto);");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }
        //Se realiza el asiento del monto a la cuenta de que se asigno
        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
        VALUES($id_asiento,$id_plan_cuenta,'$concepto',$total_gasto_sin_iva,0);");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $db->setQuery("INSERT INTO gastos (
                            id_tipo_gasto,
                            id_sucursal,
                            id_tipo_comprobante,
                            id_proveedor,
                            id_asiento,
                            id_libro_cuentas,
                            tipo_proveedor,
                            nro_gasto,
                            timbrado,
                            fecha_emision,
                            ruc,
                            razon_social,
                            condicion,
                            fecha_vencimiento,
                            documento,
                            concepto,
                            monto,
                            gravada_10,
                            gravada_5,
                            exenta,
                            imputa_iva,
                            imputa_ire,
                            imputa_irp,
                            no_imputa,
                            nro_comprobante_venta_asoc,
                            timb_compro_venta_asoc,
                            observacion,
                            usuario
                        )
                        VALUES
                            (
                            $id_tipo_gasto,
                            $id_sucursal,
                            $id_tipo_comprobante,
                            $id_proveedor,
                            $id_asiento,
                            $id_plan_cuenta,
                            $tipo_proveedor,
                            '$nro_gasto',
                            '$timbrado',
                            '$fecha_emision',
                            '$ruc',
                            '$entidad',
                            $condicion,
                            '$fecha_vencimiento',
                            '$nro_documento',
                            '$concepto',
                            $monto,
                            $gravada_10,
                            $gravada_5,
                            $exenta,
                            '$check_iva',
                            '$check_ire',
                            '$check_irp',
                            '$no_imputa',
                            '$nro_compro',
                            '$timbr_copro',
                            '$observacion',
                            '$usuario'
                            );");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $id_gasto = $db->getLastID();

        // Vencimientos
        // Se toman solo las fechas válidas
        $vencimientos = array_filter($vencimientos, function($value) {
            list($year, $month, $day) = explode("-", $value["vencimiento"]);
            return checkdate($month, $day, $year);
        });

        // Se calcula el monto a pagar en cada vencimiento
        $monto_vecimiento = ceil($monto / count($vencimientos));
        $pendiente_vecimiento = $monto;
        foreach ($vencimientos as $key => $value) {
            $vencimiento = $db->clearText($value["vencimiento"]);

            // Se valida que la suma de los montos de los vencimientos no supere el total a pagar
            if (($pendiente_vecimiento - $monto_vecimiento) > 0) {
                $monto_f = $monto_vecimiento;
                $pendiente_vecimiento -= $monto_vecimiento;
            } else {
                $monto_f = $pendiente_vecimiento;
                $pendiente_vecimiento = 0;
            }

            $db->setQuery("INSERT INTO `gastos_vencimientos` (`id_gasto`,`vencimiento`,`monto`)
                            VALUES($id_gasto,'$vencimiento',$monto_f);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }
        
        $ultimo_v = $vencimientos[count($vencimientos)-1]["vencimiento"];
        $db->setQuery("UPDATE `gastos` SET fecha_vencimiento = '$ultimo_v' WHERE id_gasto =  $id_gasto");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Gasto registrado correctamente"]);
    
    break;

    case 'editar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id = $db->clearText($_POST['id']);
        $id_recepcion = $db->clearText($_POST['id_recepcion']);
        $id_tipo_gasto = $db->clearText($_POST['id_tipo_gasto']);
        $id_sucursal = $db->clearText($_POST['id_sucursal']);
        $id_plan_cuenta = $db->clearText($_POST['id_plan_cuenta']);
        $timbrado = $db->clearText($_POST['timbrado']);
        $fecha_emision = $db->clearText($_POST['emision']);
        $nro_gasto = zerofill($db->clearText($_POST['nro']));
        $ruc = $db->clearText($_POST['ruc']);
        $entidad = $db->clearText($_POST['razon_social']);
        $condicion = $db->clearText($_POST['condicion']);
        $fecha_vencimiento = $db->clearText($_POST['vencimiento']);
        $id_tipo_comprobante = $db->clearText($_POST['id_tipo_factura']);
        $nro_documento = $db->clearText($_POST['documento']);
        $check_iva = $db->clearText($_POST['check_iva']) ?: 0;
        $check_ire = $db->clearText($_POST['check_ire'])?: 0;
        $check_irp = $db->clearText($_POST['check_irp'])?: 0;
        $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
        $gravada_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10'])) ?: 0;
        $gravada_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']))?: 0;
        $exenta = quitaSeparadorMiles($db->clearText($_POST['exenta']))?: 0;
        $observacion = $db->clearText($_POST['observacion']);
        $tipo_proveedor = $db->clearText($_POST['tipo_proveedor']);
        $vencimientos = json_decode($_POST['vencimientos'], true);

        if (empty($id_tipo_gasto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo de gasto"]);
            exit;
        }
        if (empty($id_sucursal)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Sucursal"]);
            exit;
        }
        if (empty($timbrado)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Timbrado"]);
            exit;
        }
        if (empty($fecha_emision)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Fecha de Emision"]);
            exit;
        }
        if (empty($ruc)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo RUC"]);
            exit;
        }
        if (empty($condicion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Condicion"]);
            exit;
        }
        if (empty($condicion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Condicion"]);
            exit;
        }
        if ($condicion == 2 && empty($vencimientos)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese a menos un vencimiento. Verifique"]);
            exit;
        }
        if (empty($id_tipo_comprobante)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo de Factura"]);
            exit;
        }
        if (empty($concepto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Concepto"]);
            exit;
        }
        if (empty($monto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto"]);
            exit;
        }
        if ($monto == 0) {
            echo json_encode(["status" => "error", "mensaje" => "No puede cargar Facturas con Monto 0"]);
            exit;
        }
        if (empty($tipo_proveedor)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de proveedor"]);
            exit;
        }
    
        if ($check_iva == 0 && $check_ire == 0 && $check_irp == 0) {
            $no_imputa = 1;
        }else {
            $no_imputa = 0;
        }
        if ($id_tipo_comprobante == '12' || $id_tipo_comprobante == '13') {
            $nro_compro = $nro_documento;
            $timbr_copro = $timbrado;
        }
        if (isset($id_recepcion) && !empty($id_recepcion)) {
            echo json_encode(["status" => "error", "mensaje" => "No se puede editar una Gasto Tipo Recepcion"]);
            exit;
        }

        $db->setQuery("SELECT proveedor FROM proveedores WHERE id_proveedor = $entidad");
        $id_proveedor = $entidad;
        $entidad = $db->clearText($db->loadObject()->proveedor);

        $db->setQuery("SELECT * FROM gastos WHERE id_gasto=$id");
        $estado = $db->loadObject()->estado;
        if ($estado == 2||$estado == 3) {
            echo json_encode(["status" => "error", "mensaje" => "No se puede editar un gasto ya pagado de forma parcial o total."]);
            exit;
        }

        //Si se puede editar, se saca el IVA para el asiento nuevamente
        $iva_5_asiento = $gravada_5 * 0.05;
        $iva_10_asiento = $gravada_10 * 0.1;
        $gravada_5_sin_iva  = round($gravada_5 - ($gravada_5 * 0.05));
        $gravada_10_sin_iva = round($gravada_10 - ($gravada_10 * 0.1));
        $gravada_suma = $iva_5_asiento + $iva_10_asiento;
        $total_gasto_sin_iva = $monto - $gravada_suma;

        //Buscamos el id_asiento para poder modificar su cabecera. 
        $db->setQuery("SELECT id_asiento FROM gastos WHERE id_gasto = $id");
        $id_asiento =  $db->loadObject()->id_asiento;

        //Si es un gasto que no cuenta con asiento se crea el asiento y sus detalles.
        if (empty($id_asiento)) {
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
            VALUES($id_libro_diario_periodo,1,NOW(),'$nro',$monto,'$concepto','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la carga del gasto.  
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_asiento = $db->getLastID();
        }else{
            //Si es un gasto que cuenta con un asiento contable, se modifica el mismo. 
            $db->setQuery("UPDATE `libro_diario` SET `importe` = $monto, `descripcion` = '$concepto' WHERE `id_libro_diario` = $id_asiento;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            //Si tiene un asiento, eliminamos su detalle.
            $db->setQuery("DELETE FROM `libro_diario_detalles` WHERE `id_libro_diario` = $id_asiento;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al eliminar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }

        //Se cargan los detalles del asiento. Se cargan los IVA correspondiente. 
        //Solo se realiza el asiento si el gasto contiene productos con iva 5%.
        if($iva_5_asiento > 0){
            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,32,'IVA 5%',$iva_5_asiento,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                exit;
            }
        }

        //Solo se realiza el asiento si el gasto contiene productos con iva 10%.
        if($iva_10_asiento > 0){
            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,31,'IVA 10%',$iva_10_asiento,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }

        //Se realiza el asiento del monto a la cuenta de deudas con proveedores
        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
        VALUES($id_asiento,83,'$concepto',0,$monto);");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }
        //Se realiza el asiento del monto a la cuenta de que se asigno
        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
        VALUES($id_asiento,$id_plan_cuenta,'$concepto',$total_gasto_sin_iva,0);");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $db->setQuery("UPDATE
                        gastos
                        SET
                        id_tipo_gasto = $id_tipo_gasto,
                        id_sucursal = $id_sucursal,
                        id_tipo_comprobante = $id_tipo_comprobante,
                        id_proveedor = $id_proveedor,
                        id_asiento = $id_asiento,
                        tipo_proveedor = $tipo_proveedor,
                        nro_gasto = '$nro_gasto',
                        timbrado = '$timbrado',
                        fecha_emision = '$fecha_emision',
                        ruc = '$ruc',
                        razon_social = '$entidad',
                        condicion = $condicion,
                        fecha_vencimiento = '$fecha_vencimiento',
                        documento = '$nro_documento',
                        concepto = '$concepto',
                        monto = $monto,
                        gravada_10 = $gravada_10,
                        gravada_5=$gravada_5,
                        exenta = $exenta,
                        imputa_iva = '$check_iva',
                        imputa_ire = '$check_ire',
                        imputa_irp = '$check_irp',
                        no_imputa = $no_imputa,
                        nro_comprobante_venta_asoc = '$nro_compro',
                        timb_compro_venta_asoc = '$timbr_copro',
                        observacion = '$observacion',
                        usuario = '$usuario'
                        WHERE id_gasto = $id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $db->setQuery("SELECT estado FROM gastos WHERE id_gasto=$id");
        $estado = $db->loadObject();
        $gasto_estado = $estado->estado;
        if ($gasto_estado == 2||$gasto_estado == 3||$gasto_estado == 4) {
            echo json_encode(["status" => "error", "mensaje" => "No se puede editar las fechas unas vez pagado o anulado."]);
            exit;
        }

        $db->setQuery("DELETE FROM `gastos_vencimientos` WHERE `id_gasto` = $id;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al editar las fechas"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }
        // Vencimientos
            // Se toman solo las fechas válidas
            $vencimientos = array_filter($vencimientos, function($value) {
                list($year, $month, $day) = explode("-", $value["vencimiento"]);
                return checkdate($month, $day, $year);
            });

            // Se calcula el monto a pagar en cada vencimiento
            $monto_vecimiento = ceil($monto / count($vencimientos));
            $pendiente_vecimiento = $monto;
            foreach ($vencimientos as $key => $value) {
                $vencimiento = $db->clearText($value["vencimiento"]);

                // Se valida que la suma de los montos de los vencimientos no supere el total a pagar
                if (($pendiente_vecimiento - $monto_vecimiento) > 0) {
                    $monto_f = $monto_vecimiento;
                    $pendiente_vecimiento -= $monto_vecimiento;
                } else {
                    $monto_f = $pendiente_vecimiento;
                    $pendiente_vecimiento = 0;
                }

                $db->setQuery("INSERT INTO `gastos_vencimientos` (`id_gasto`,`vencimiento`,`monto`)
                                VALUES($id,'$vencimiento',$monto_f);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Gasto modificado correctamente"]);
    break;


    case 'cambiar-estado':
        $db = DataBase::conectar();
        $id = $db->clearText($_POST['id']);
        $estado = $db->clearText($_POST['estado']);

        if ($estado != 0 && $estado != 1) {
            echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
            exit;
        }

        $db->setQuery("UPDATE gastos SET estado=$estado WHERE id_gasto=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
    break;

    case 'ultimo_correlativo_presupuesto':
        $db = DataBase::conectar();
        $nro_gasto =  1;
        $db->setQuery("SELECT*FROM gastos ORDER BY id_gasto DESC LIMIT 1");
        $nro = $db->loadObjectList();
        if(!empty($nro)){
            foreach($nro as $nro){
                $nro_gasto =  intval($nro->nro_gasto);
            }	

            $nro_gasto = zerofill(($nro_gasto*1)+1);
        }

        echo json_encode(["nro_gasto" => str_pad($nro_gasto, 7, '0',  STR_PAD_LEFT)]);
        
    break;

    case 'ver_detalle_recepcion':
        $db = DataBase::conectar();
        $id = $db->clearText($_GET['id']);

        $db->setQuery("SELECT op.numero,
                        DATE_FORMAT(op.fecha, '%d/%m/%y %H:%i') AS fecha_pago,
                        op.concepto,
                        b.banco,
                        CASE op.forma_pago WHEN 1 THEN 'TRANSFERENCIA' WHEN 2 THEN 'CHEQUE' WHEN 3 THEN 'EFECTIVO' END AS forma_pago,
                        op.observacion,
                        op.usuario,
                        op.monto
                        FROM orden_pagos_proveedores opp
                        LEFT JOIN orden_pagos op ON opp.id_pago=op.id_pago
                        LEFT JOIN bancos b ON op.`id_banco` = b.`id_banco`
                        WHERE opp.id_factura=$id AND op.estado=3");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;

    case 'ver_detalles_gasto':
        $db = DataBase::conectar();
        $id = $db->clearText($_GET['id']);

        $db->setQuery("SELECT op.numero,
                        DATE_FORMAT(op.fecha, '%d/%m/%y %H:%i') AS fecha_pago,
                        op.concepto,
                        b.banco,
                        CASE op.forma_pago WHEN 1 THEN 'TRANSFERENCIA' WHEN 2 THEN 'CHEQUE' WHEN 3 THEN 'EFECTIVO' END AS forma_pago,
                        op.observacion,
                        op.usuario,
                        op.monto
                        FROM orden_pago_gastos opg 
                        LEFT JOIN orden_pagos op ON opg.id_pago=op.id_pago
                        LEFT JOIN bancos b ON op.id_banco = b.id_banco
                        WHERE opg.id_gasto=$id AND op.estado=3");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;


    case 'ver_vencimientos':
        $db = DataBase::conectar();
        $id = $db->clearText($_GET['id']);
        $id_2 = $db->clearText($_GET['id_recepcion']);

        if (!empty($id_2) && intVal($id_2) != 0) {
            $db->setQuery("SELECT vencimiento FROM recepciones_compras_vencimientos WHERE id_recepcion_compra = $id_2");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        }else if(!empty($id) && intVal($id) != 0){
            $db->setQuery("SELECT vencimiento FROM gastos_vencimientos WHERE id_gasto = $id");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        }
    break;

    

}
