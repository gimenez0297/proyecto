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
        $sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $where_fecha = "";
        $where .= "";
        $desde = $db->clearText($_REQUEST['desde']);
        $hasta = $db->clearText($_REQUEST['hasta']);

         if (!empty($desde)) {
            $where_fecha .= " AND g.fecha_emision BETWEEN '$desde' AND '$hasta'";
        }

        if (!empty($_REQUEST['id_sucursal']) && intVal($sucursal) != 0) {
           $where .= " AND g.id_sucursal=$sucursal";
        }

       //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', sucursal, timbrado,ruc,proveedor,documentos,nombre) LIKE '%$search%'";
            }
            
        

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                        g.id_gasto,
                        fg.id_gastos_fijos,
                        pro.id_proveedor,
                        tc.nombre_comprobante,                 
                        fg.nombre AS descripcion,
                        pro.proveedor,
                        g.id_sub_tipo_gasto,
                        g.id_tipo_gasto,
                        g.id_recepcion_compra,
                        g.id_libro_cuentas,
                        tg.nombre,
                        g.id_sucursal,
                        s.sucursal,
                        g.id_tipo_comprobante,
                        g.nro_gasto,
                        lc.denominacion,
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
                        CASE g.estado 
                        WHEN 0 THEN 'Pendiente'
                        WHEN 1 THEN 'Pagado Parcial' 
                        WHEN 2 THEN 'Pagado' 
                        WHEN 3 THEN 'Anulado'
                        END AS estado_str,

                        g.tipo_proveedor,
                        g.id_proveedor,
                        g.estado,
                        g.usuario,
                        CASE g.deducible WHEN 1 THEN 'DEDUCIBLE' WHEN 2 THEN 'NO DEDUCIBLE' END AS deducible_str,
                        
                            g.monto - (SELECT IFNULL(SUM(opg.monto),0)
                                    FROM orden_pago_gastos opg
                                    JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                    WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago)
                            
                            
                        AS pendiente,

                            (SELECT IFNULL(SUM(opg.monto),0)
                                    FROM orden_pago_gastos opg
                                    JOIN orden_pagos op ON opg.id_pago=op.id_pago
                                WHERE op.estado=3 AND opg.id_gasto=g.id_gasto AND opg.id_pago=op.id_pago)
                      
                        AS pagado
                        
                        FROM gastos g
                        LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                        LEFT JOIN tipos_gastos tg ON tg.id_tipo_gasto = g.id_tipo_gasto
                        LEFT JOIN sucursales s ON s.id_sucursal = g.id_sucursal
                        LEFT JOIN gastos_fijos fg ON fg.id_tipo_gastos = g.id_tipo_gasto
                        LEFT JOIN proveedores pro ON pro.id_proveedor = g.id_proveedor
                        LEFT JOIN libro_cuentas lc ON lc.id_libro_cuenta = g.id_libro_cuentas
                        WHERE 1=1  $where_fecha $where AND gasto_fijo= 1
                        GROUP BY id_gasto
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
        $id_proveedor = $db->clearText($_POST['id_proveedor']);
        $id_gasto_fijo = $db->clearText($_POST['gasto_fijo']);
        $id_tipo_factura = $db->clearText($_POST['id_tipo_factura']);
        $id_plan_cuenta = $db->clearText($_POST['id_plan_cuenta']);
        $gasto_fijo = $db->clearText($_POST['gasto_fijo']);
        $timbrado = $db->clearText($_POST['timbrado']);
        $eminsion = $db->clearText($_POST['emision']);
        $sucursal = $db->clearText($_POST['id_sucursal']);
        $razon_social = $db->clearText($_POST['razon_social']);
        $ruc = $db->clearText($_POST['ruc']);
        $documento = $db->clearText($_POST['documento']);
        $monto = quitaSeparadorMiles($_POST['monto']);
        $check_iva = $db->clearText($_POST['check_iva']) ?: 0;
        $check_ire = $db->clearText($_POST['check_ire'])?: 0;
        $check_irp = $db->clearText($_POST['check_irp'])?: 0;
        $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $iva_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']));
        $iva_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']));
        $exenta = quitaSeparadorMiles($db->clearText($_POST['exenta']));
        $observacion = $db->clearText($_POST['observacion']);

        $db->setQuery("SELECT * FROM `proveedores` WHERE `id_proveedor` = $razon_social ");
        $pp = $db->loadObject();
        $proveedor_p       = $db->clearText($pp->proveedor);

        //Obtenemos el nro de asiento
        $db->setQuery("SELECT nro_gasto FROM gastos ORDER BY nro_gasto DESC LIMIT 1");
        $nro_gasto = $db->loadObject()->nro_gasto;

        if (empty($nro_gasto)) {
            $nro_gasto = zerofill(1);
        } else {
            $nro_gasto = zerofill(intval($nro_gasto) + 1);
        }

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

        //Se carga la cabezera del asiento
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

        $db->setQuery("INSERT INTO gastos (gasto_fijo,id_tipo_gasto,id_gasto_fijo,id_sucursal,id_tipo_comprobante,id_proveedor, id_asiento, id_libro_cuentas,timbrado,fecha_emision,nro_gasto,razon_social,ruc,documento,imputa_iva,imputa_ire,imputa_irp,concepto,monto,gravada_10,gravada_5,exenta,observacion,condicion,usuario)
                       VALUES(1,$id_tipo_gasto,$id_gasto_fijo,$id_sucursal,$id_tipo_factura, $id_proveedor,$id_asiento,$id_plan_cuenta,'$timbrado','$eminsion','$nro_gasto','$proveedor_p','$ruc','$documento',$check_iva,$check_ire,$check_irp,'$concepto',$monto,$iva_10,$iva_5,
                              $exenta,'$observacion',1,'$usuario');");
        if (!$db->alter()) {
            $db->rollback();  //Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
            exit;
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Gasto Fijo registrado correctamente"]);
        
    break;

    case 'editar':
        $db = DataBase::conectar();
        $id = $db->clearText($_POST['id']);
        $id_tipo_gasto = $db->clearText($_POST['id_tipo_gasto']);
        $id_proveedor = $db->clearText($_POST['id_proveedor']);
        $id_gasto_fijo = $db->clearText($_POST['gasto_fijo']);
        $id_tipo_factura = $db->clearText($_POST['id_tipo_factura']);
        $id_plan_cuenta = $db->clearText($_POST['id_plan_cuenta']);
        $gasto_fijo = $db->clearText($_POST['gasto_fijo']);
        $timbrado = $db->clearText($_POST['timbrado']);
        $emision = $db->clearText($_POST['emision']);
        $nro =  zerofill($db->clearText($_POST['nro']));
        $sucursal = $db->clearText($_POST['id_sucursal']);
        $razon_social = $db->clearText($_POST['razon_social']);
        $ruc = $db->clearText($_POST['ruc']);
        $documento = $db->clearText($_POST['documento']);
        $monto = quitaSeparadorMiles($_POST['monto']);
        $check_iva = $db->clearText($_POST['check_iva']) ?: 0;
        $check_ire = $db->clearText($_POST['check_ire'])?: 0;
        $check_irp = $db->clearText($_POST['check_irp'])?: 0;
        $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $iva_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']));
        $iva_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']));
        $exenta = quitaSeparadorMiles($db->clearText($_POST['exenta']));
        $observacion = $db->clearText($_POST['observacion']);
     
        $db->setQuery("SELECT * FROM `proveedores` WHERE `id_proveedor` = $razon_social ");
        $pp = $db->loadObject();
        $proveedor_p       = $db->clearText($pp->proveedor);

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
    
        $db->setQuery("UPDATE gastos SET
                                        id_tipo_gasto = $id_tipo_gasto,
                                        id_sucursal = $sucursal,
                                        id_tipo_comprobante = $id_tipo_factura,
                                        id_gasto_fijo = $id_gasto_fijo,
                                        id_proveedor='$id_proveedor',
                                        id_asiento='$id_asiento',
                                        id_libro_cuentas='$id_plan_cuenta',
                                        nro_gasto = '$nro',
                                        timbrado = '$timbrado',
                                        fecha_emision = '$emision',
                                        ruc = '$ruc',
                                        razon_social = '$proveedor_p',
                                        documento = '$documento',
                                        concepto = '$concepto',
                                        monto = $monto,
                                        gravada_10 = $iva_10,
                                        gravada_5=$iva_5,
                                        exenta = $exenta,
                                        imputa_iva = '$check_iva',
                                        imputa_ire = '$check_ire',
                                        imputa_irp = '$check_irp',
                                        observacion = '$observacion',
                                        usuario = '$usuario'

                                    WHERE id_gasto = $id");
    
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Gasto Fijo editado correctamente"]);
    break;

    // case 'eliminar':
    //     $db     = DataBase::conectar();
    //     $id     = $db->clearText($_POST['id']);
    //     $nro = $db->clearText($_POST['nro']);
        
    //     $db->setQuery("DELETE FROM gastos WHERE id_gasto=$id");
    //     if (!$db->alter()) {
    //         if ($db->getErrorCode() == 1451) {
    //             echo json_encode(["status" => "error", "mensaje" => "El Gasto Fijo Nro°'$nro' no puede ser eliminado"]);
    //         } else {
    //             echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
    //         }
    //         exit;
    //     }

    //     echo json_encode(["status" => "ok", "mensaje" => "Gasto Fijo Nro°'$nro' eliminado correctamente"]);
    // break;	

    // case 'cambiar-estado':
    //     $db = DataBase::conectar();
    //     $id = $db->clearText($_POST['id']);
    //     $estado = $db->clearText($_POST['estado']);

    //     if ($estado != 0 && $estado != 1) {
    //         echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
    //         exit;
    //     }

    //     $db->setQuery("UPDATE gastos_fijos SET estado=$estado WHERE id_gasto_fijo=$id");
    //     if (!$db->alter()) {
    //         echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
    //         exit;
    //     }

    //     echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
    // break;

    case 'ultimo_correlativo_gasto_fijo':
        $db = DataBase::conectar();
        $nro_gasto = 1;
        $db->setQuery("SELECT*FROM gastos ORDER BY id_gasto DESC LIMIT 1");
        $nro = $db->loadObjectList();
        if(!empty($nro)){
            foreach($nro as $nro){
                $nro_gasto=  intval($nro->nro_gasto);
            }	

            $nro_gasto = zerofill(($nro_gasto*1)+1);
        }
  
        echo json_encode(["nro_gasto" => str_pad($nro_gasto, 7, '0',  STR_PAD_LEFT)]);
        
    break;



    case 'buscar_razon_social':
            $db = DataBase::conectar();
            $id_ = $db->clearText($_POST["id_"]);
            
            $db->setQuery("SELECT pt.id_proveedor, p.id_gasto_fijo_tipo, pt.ruc, pt.proveedor
                            FROM gastos_fijos p
                            LEFT JOIN proveedores pt ON p.id_proveedor = pt.id_proveedor
                            WHERE id_gasto_fijo_tipo ='$id_' 
                            GROUP BY p.id_tipo_gasto");
            $row = ($db->loadObject()) ?: ["id_" => null];
            echo json_encode($row);
    break;     


     case 'rescupera_tipo_gastos':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST["id"]);
            
            $db->setQuery("SELECT
                                p.id_proveedor, 
                                pt.nombre_comprobante,     
                                pt.id_tipo_comprobante,                   
                                j.ruc,
                                j.proveedor,
                                p.iva_10,
                                p.iva_5,
                                p.exenta,
                                p.monto,
                                p.concepto
                            FROM gastos_fijos p
                            LEFT JOIN tipos_comprobantes pt ON pt.id_tipo_comprobante = p.id_tipo_factura
                            LEFT JOIN proveedores j ON j.id_proveedor = p.id_proveedor    
                            WHERE id_gastos_fijos=$id");
        
            $row = $db->loadObject();
            echo json_encode($row);
    break;     


}
