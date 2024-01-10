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

        $id_caja_sucursal = $db->clearText($_REQUEST['id_caja']);


        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $where = "AND CONCAT_WS(' ', g.`nro_gasto`,g.ruc,  g.razon_social  g.concepto, g.monto, g.`deducible`) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                    ccf.`id_caja_chica_facturas`,
                    g.id_gasto,
                    g.`nro_gasto`,
                    DATE_FORMAT(g.`fecha_emision`, '%d/%m/%Y') AS fecha_emision,
                    g.ruc,
                    g.razon_social,
                    g.concepto,
                    g.monto,
                    CASE
                    g.`deducible`
                    WHEN 1
                    THEN 'DEDUCIBLE'
                    WHEN 2
                    THEN 'NO DEDUCIBLE'
                    END AS deducible
                FROM
                    caja_chica_facturas ccf
                    LEFT JOIN gastos g
                    ON ccf.`id_gasto` = g.`id_gasto`
                    WHERE ccf.id_caja_chica_sucursal = $id_caja_sucursal
                    $where 
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
        $deducible = $db->clearText($_POST['tipo_gasto']);
        $tipo_gasto = $db->clearText($_POST['tipo_g']);
        $tipo_proveedor = $db->clearText($_POST['tipo_proveedor']);
        $ruc = $db->clearText($_POST['ruc']);
        $entidad = $db->clearText($_POST['razon_social']);
        $fecha_emision = $db->clearText($_POST['emision']);
        $timbrado = $db->clearText($_POST['timbrado']);
        $nro_documento = $db->clearText($_POST['documento']);
        $id_tipo_comprobante = $db->clearText($_POST['id_tipo_factura']);
        $id_sucursal_gasto = $db->clearText($_POST['id_sucursal_gasto']);
        $id_plan_cuenta = $db->clearText($_POST['id_plan_cuenta']);
        $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
        $gravada_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']));
        $gravada_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']));
        $exenta = quitaSeparadorMiles($db->clearText($_POST['exenta']));
        $observacion = $db->clearText($_POST['observacion_factura']);
        $id_sucursal_caja = $db->clearText($_POST['id_tipo_factura']);
        $id_caja_chica = $db->clearText($_POST['caja_chica']);
        $check_iva = $db->clearText($_POST['check_iva']) ?: 0;
        $check_ire = $db->clearText($_POST['check_ire']) ?: 0;
        $check_irp = $db->clearText($_POST['check_irp']) ?: 0;

        //Si el gasto es deducible
        if ($deducible == 1) {

            if (empty($tipo_proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de proveedor."]);
                exit;
            }
            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo RUC."]);
                exit;
            }
            if (empty($fecha_emision)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Fecha de Emision."]);
                exit;
            }
            if (empty($timbrado)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Timbrado."]);
                exit;
            }
            if (empty($nro_documento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Documento."]);
                exit;
            }
            if (empty($id_tipo_comprobante)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo de Factura"]);
                exit;
            }
            if (empty($id_sucursal_gasto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Sucursal"]);
                exit;
            }
            if (empty($id_plan_cuenta)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Plan de Cuentas"]);
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

            //Se obtiene la informacion del proveedor
            $db->setQuery("SELECT proveedor FROM proveedores WHERE id_proveedor = $entidad");
            $id_proveedor = $entidad;
            $entidad = $db->clearText($db->loadObject()->proveedor);
        } else if ($deducible == 2) {
            $ruc = 'S/R';
            $entidad = 'S/N';
            $timbrado = 'S/T';
            $nro_documento = 'S/D';
            $id_tipo_comprobante = 'NULL';
            $id_proveedor = 'NULL';
            $tipo_proveedor = 'NULL';
            $tipo_gasto = 'NULL';
            $id_plan_cuenta = 'NULL';
        }

        //Si los checks son 0 entonces no imputa
        if ($check_iva == 0 && $check_ire == 0 && $check_irp == 0) {
            $no_imputa = 1;
        } else {
            $no_imputa = 0;
        }

        //Se obtiene la informacion de la caja chica que selecciono 
        $db->setQuery("SELECT * FROM caja_chica_sucursal WHERE id_caja_chica_sucursal = $id_caja_chica");
        $cc_sucursal = $db->loadObject();
        $estado_cc = $cc_sucursal->estado;
        $id_cc_configuracion = $cc_sucursal->id_caja_chica;
        $sobrante_ccs = $cc_sucursal->sobrante;

        //Si el estado es 2 es porque ya esta rendida la caja
        if ($estado_cc == 2) {
            echo json_encode(["status" => "error", "mensaje" => "Movimiento ya Cerrado y Rendido.Favor Verificar"]);
            exit;
        }

        //Se obtiene la configuracion de la caja 
        $db->setQuery("SELECT * FROM caja_chica WHERE id_caja_chica = $id_cc_configuracion");
        $cc_configuracion = $db->loadObject();
        $cc_monto_factura = $cc_configuracion->maximo_factura;

        //Se verifica que el monto no supere el monto maximo de factura 
        if ($monto > $cc_monto_factura) {
            echo json_encode(["status" => "error", "mensaje" => "El monto supera al monto maximo permitido.Favor Verificar"]);
            exit;
        }

        //Se verifica que el saldo cubra el monto de la factura
        if ($monto > $sobrante_ccs) {
            echo json_encode(["status" => "error", "mensaje" => "El monto supera el saldo Disponible.Favor Verificar"]);
            exit;
        }

        //Si no supera el saldo minimo entonces se descuenta del sobrante.
        $sobrante_fin_cc = $sobrante_ccs - $monto;
        //Se guarda el nuevo sobrante
        $db->setQuery("UPDATE caja_chica_sucursal SET `sobrante` = $sobrante_fin_cc
                            WHERE `id_caja_chica_sucursal` = $id_caja_chica;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el saldo."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }
        //Se general el numero de gasto
        $db->setQuery("SELECT * FROM gastos ORDER BY id_gasto DESC LIMIT 1");
        $nro = $db->loadObject();
        $nro =  intval($nro->nro_gasto);
        $nro_gasto = zerofill(($nro * 1) + 1);

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

        if ($deducible == 1) {
            //Se realiza el asiento del monto a la cuenta de que se asigno
            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,$id_plan_cuenta,'$concepto',$total_gasto_sin_iva,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }

        if ($deducible == 2) {
            //Se realiza el asiento del monto a la cuenta de que se asigno
            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,232,'$concepto',$monto,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }

        //Se descuenta de la cuenta de caja chica.
        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
        VALUES($id_asiento,289,'$concepto',0,$monto);");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        //Se inserta el Gasto 
        $db->setQuery("INSERT INTO gastos (
                                id_tipo_gasto,
                                id_sucursal,
                                id_tipo_comprobante,
                                id_proveedor,
                                id_caja_chica,
                                id_asiento,
                                id_libro_cuentas,
                                tipo_proveedor,
                                nro_gasto,
                                timbrado,
                                fecha_emision,
                                ruc,
                                razon_social,
                                condicion,
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
                                observacion,
                                deducible,
                                estado,
                                usuario
                            )
                            VALUES
                                (
                                $tipo_gasto,
                                $id_sucursal_gasto,
                                $id_tipo_comprobante,
                                $id_proveedor,
                                $id_caja_chica,
                                $id_asiento,
                                $id_plan_cuenta,
                                $tipo_proveedor,
                                '$nro_gasto',
                                '$timbrado',
                                '$fecha_emision',
                                '$ruc',
                                '$entidad',
                                1,
                                '$nro_documento',
                                '$concepto',
                                $monto,
                                $gravada_10,
                                $gravada_5,
                                $exenta,
                                '$imputa_iva',
                                '$imputa_ire',
                                '$imputa_irp',
                                '$no_imputa',
                                '$observacion',
                                $deducible,
                                2,
                                '$usuario'
                                );");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registar el Gasto"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $id_gasto = $db->getLastID();

        $db->setQuery("INSERT INTO `caja_chica_facturas` (`id_caja_chica_sucursal`,`id_gasto`)
                            VALUES ($id_caja_chica,$id_gasto);");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al agregar el gasto en caja chica."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }
        
        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Gasto registrado correctamente", "saldo" => $sobrante_fin_cc]);
        break;

        case 'eliminar-gasto' : 
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_REQUEST['id']);
            $id_caja = $db->clearText($_REQUEST['id_caja']);

            //Se obtiene la informacion de la caja
            $db->setQuery("SELECT * FROM caja_chica_sucursal WHERE id_caja_chica_sucursal = $id_caja");
            $caja_c = $db->loadObject();
            $estado_c = $caja_c->estado;
            $sobrante_c = $caja_c->sobrante;

            //Si la caja esta rendida no le da borras el gasto
            if ($estado_c == 2) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede borrar un gasto de un movimiento ya rendido."]);
                exit;
            }

            //Se optiene la informacion del gasto
            $db->setQuery("SELECT * FROM gastos WHERE id_gasto=$id");
            $gasto = $db->loadObject();
            $monto_g = $gasto->monto;
            $id_asiento =$gasto->id_asiento;

            $monto_f = $sobrante_c + $monto_g;

            //Borramos el registro de caja chica facturas
            $db->setQuery("DELETE FROM `caja_chica_facturas` WHERE `id_gasto` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al eliminar el gasto en caja chica."]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            //Borramos el gasto
            $db->setQuery("DELETE FROM `gastos` WHERE `id_gasto` = $id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al eliminar el gasto."]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            //Actualizamos el sobrante 
            $db->setQuery("UPDATE `caja_chica_sucursal` SET `sobrante` = $monto_f
                            WHERE `id_caja_chica_sucursal` = $id_caja;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el saldo."]);
                $db->rollback();  // Revertimos los cambios
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
                VALUES($id_asiento,$id_libro_cuenta,'Contra asiento del asiento $nro_asiento_c',$haber,$debe);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Gasto registrado correctamente", "saldo" => $monto_f]);
        break;

        case 'rendir-caja':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_REQUEST['id']);

            //Se obtiene la informacion de la caja
            $db->setQuery("SELECT * FROM caja_chica_sucursal WHERE id_caja_chica_sucursal = $id");
            $caja_c = $db->loadObject();
            $estado_c = $caja_c->estado;
            $sobrante = $caja_c->sobrante;
            
            //Si la caja esta rendida no le da borras el gasto
            if ($estado_c == 2) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede volver a rendir.Movimiento ya rendido."]);
                exit;
            }

            //Se realiza un asiento contable la cual, si la caja tiene sobrante, descuenta y queda en 0 la cuenta de caja chica.
            if ($sobrante > 0) {
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
                VALUES($id_libro_diario_periodo,2,NOW(),'$nro',$sobrante,'RENDICIÓN DE CAJA CHICA','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la rendicion de la caja chica.  
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }       

                $id_asiento = $db->getLastID();

                //Se realiza el asiento que descuenta el sobrante de la caja chica. 
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,289,'RENDICIÓN DE CAJA CHICA',0,$sobrante);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }


            $db->setQuery("UPDATE
                                `caja_chica_sucursal`
                            SET
                                `fecha_rendicion` = NOW(),
                                `usuario_rendicion` = '$usuario',
                                `estado` = 2
                            WHERE `id_caja_chica_sucursal` =$id;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la rendicion."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Rendicion registrado correctamente"]);
        break; 

}
