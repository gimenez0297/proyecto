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
            $id_sucursal = $db->clearText($_POST['deposito']);
            $ruc = $db->clearText($_POST['ruc']);
            $entidad = $db->clearText($_POST['razon_social']);
            $fecha_emision = $db->clearText($_POST['emision']);
            $timbrado = $db->clearText($_POST['timbrado']);
            $numero_documento = $db->clearText($_POST['documento']);
            $condicion = $db->clearText($_POST['condicion']);
            $id_tipo_comprobante = $db->clearText($_POST['id_tipo_factura']);
            $check_iva = $db->clearText($_POST['check_iva']) ?: 0;
            $check_ire = $db->clearText($_POST['check_ire'])?: 0;
            $check_irp = $db->clearText($_POST['check_irp'])?: 0;
            $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");        
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $gravada_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']))?: 0;
            $gravada_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']))?: 0;
            $exenta = quitaSeparadorMiles($db->clearText($_POST['extenta']))?: 0;
            $observacion = $db->clearText($_POST['observacion_factura']);
            $vencimientos = json_decode($_POST['vencimientos'], true);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $productos = json_decode($_POST['productos'], true);
            $archivos = json_decode($_POST['archivos'], true);

            if (empty($id_proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);
                exit;
            }
            if (empty($id_sucursal)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un depósito"]);
                exit;
            }
            if (empty($numero_documento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo número de documento"]);
                exit;
            }
            if (empty($condicion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una condición de compra"]);
                exit;
            }
            if ($condicion == 2 && empty($vencimientos)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese a menos un vencimiento. Verifique"]);
                exit;
            }
            if (empty($productos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún producto agregado. Favor verifique."]);
                exit;
            }
            if (count($archivos) > 6) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "La cantidad máxima de archivos que puede cargar es de 6"]);
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

            $db->setQuery("SELECT MAX(numero) AS numero FROM recepciones_compras");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            // Se calcula el total costo y se extrae el ID de cada orden de compra
            $ordenes_compras = [];
            $total_costo = 0;

            foreach ($productos as $key => $p) {
                $id_orden_compra = $db->clearText($p["id_orden_compra"]);
                $recepcionado = $db->clearText(quitaSeparadorMiles($p["recepcionado"]));
                if (array_search($id_orden_compra, $ordenes_compras) === false) {
                    $ordenes_compras[] = $id_orden_compra;
                }

                // Cálculo del costo
                $costo = $db->clearText(quitaSeparadorMiles($p["costo"]));
                $recepcionado = $db->clearText(quitaSeparadorMiles($p["recepcionado"]));
                $total_costo += $costo * $recepcionado;
            }

            // Cabeceera
            $db->setQuery("INSERT INTO recepciones_compras (numero, id_proveedor, numero_documento, condicion, total_costo, observacion, id_sucursal, estado, usuario, fecha)
                            VALUES ('$numero',$id_proveedor,'$numero_documento', $condicion,$total_costo,'$observacion',$id_sucursal,'1','$usuario',NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_recepcion_compra = $db->getLastID();

            //Tipo de Gasto
            $db->setQuery("SELECT * FROM tipos_gastos WHERE id_sub_tipo_gasto = 1");
            $gasto = $db->loadObject();

            if (!$gasto) {
                echo json_encode(["status" => "error", "mensaje" => "No hay un gasto cargado con tipo Recepcion"]);
                exit;
            }
            $id_tipo_gasto     = $gasto->id_tipo_gasto;
            $id_sub_tipo_gasto = $gasto->id_sub_tipo_gasto;


            if ($check_iva == 0 && $check_ire==0 && $check_irp == 0) {
                $no_imputa = 1;
            }else {
                $no_imputa = 0;
            }

            $db->setQuery("SELECT * FROM gastos ORDER BY id_gasto DESC LIMIT 1");
            $nro = $db->loadObject();
            $nro =  intval($nro->nro_gasto);
            $nro_gasto = zerofill(($nro*1)+1);

            if ($id_tipo_comprobante == '12' || $id_tipo_comprobante == '13') {
                $nro_compro = $numero_documento;
                $timbr_copro = $timbrado;
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
            $retencion_iva = $gravada_suma * 0.3;

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
            VALUES($id_asiento,35,'$concepto',$total_gasto_sin_iva,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            //Se realiza el asiento de la retencion IVA. Que es el 30% del iva. 
            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,29,'RETENCIÓN IVA',$retencion_iva,0);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            //Datos de la Factura
            $db->setQuery("INSERT INTO gastos (id_tipo_gasto,id_sucursal,id_tipo_comprobante,id_sub_tipo_gasto,id_proveedor,id_recepcion_compra,id_asiento,nro_gasto,timbrado,fecha_emision,ruc,razon_social,condicion,
                           fecha_vencimiento,documento,concepto,monto,gravada_10,gravada_5,exenta,imputa_iva,imputa_ire,imputa_irp,no_imputa,
                           nro_comprobante_venta_asoc,timb_compro_venta_asoc,observacion,usuario)
                           VALUES($id_tipo_gasto,$id_sucursal,$id_tipo_comprobante,$id_sub_tipo_gasto,$id_proveedor,$id_recepcion_compra,$id_asiento,'$nro_gasto','$timbrado','$fecha_emision','$ruc','$entidad',$condicion,
                           '$fecha_vencimiento','$numero_documento','$concepto',$monto,$gravada_10,$gravada_5,$exenta,'$check_iva','$check_ire','$check_irp',
                           '$no_imputa','$nro_compro','$timbr_copro','$observacion','$usuario');");
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
            $monto_vecimiento = ceil($total_costo / count($vencimientos));
            $pendiente_vecimiento = $total_costo;
            foreach ($vencimientos as $key => $value) {
                $vencimiento = $db->clearText($value["vencimiento"]);

                // Se valida que la suma de los montos de los vencimientos no supere el total a pagar
                if (($pendiente_vecimiento - $monto_vecimiento) > 0) {
                    $monto = $monto_vecimiento;
                    $pendiente_vecimiento -= $monto_vecimiento;
                } else {
                    $monto = $pendiente_vecimiento;
                    $pendiente_vecimiento = 0;
                }

                $db->setQuery("INSERT INTO recepciones_compras_vencimientos(id_recepcion_compra, vencimiento, monto)
                                VALUES ('$id_recepcion_compra','$vencimiento',$monto)");
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

            foreach ($productos as $key => $p) {
                $id_producto = $db->clearText($p["id_producto"]);
                $id_orden_compra = $db->clearText($p["id_orden_compra"]);
                $id_orden_compra_producto = $db->clearText($p["id_orden_compra_producto"]);
                $numero_oc = $db->clearText($p["numero"]);
                $codigo = $db->clearText($p["codigo"]);
                $producto = $db->clearText($p["producto"]);
                $costo = quitaSeparadorMiles($db->clearText($p["costo"]));
                $costo_ultimo = quitaSeparadorMiles($db->clearText($p["costo_ultimo"]));
                $lotes = json_decode($p['lotes'], true);
                $recepcionado = 0;

                try {

                    foreach ($lotes as $key => $l) {
                        $lote = $db->clearText($l['lote']);
                        $vencimiento = $db->clearText($l['vencimiento']);
                        $cantidad = $db->clearText(quitaSeparadorMiles($l['cantidad']));
                        $canje = ($db->clearText($l['canje'])) ? 1 : 0;
                        $vencimiento_canje = $db->clearText($l['vencimiento_canje']);
                        $recepcionado += $cantidad;

                        $db->setQuery("SELECT l.id_lote
                                        FROM lotes l
                                        JOIN stock s ON l.id_lote=s.id_lote
                                        WHERE s.id_producto=$id_producto AND l.lote='$lote'");
                        $row = $db->loadObject();
                        $id_lote = $row->id_lote;

                        if (empty($row)) {
                            $db->setQuery("INSERT INTO lotes (id_proveedor,lote, vencimiento, canje, vencimiento_canje, costo, usuario, fecha) VALUES('$id_proveedor','$lote','$vencimiento','$canje','$vencimiento_canje', $costo,'$usuario',NOW())");
                            if (!$db->alter()) {
                                if ($db->getErrorCode() == 1062) {
                                    $db->setQuery("SELECT l.id_lote, p.producto, p.codigo
                                                    FROM lotes l
                                                    JOIN stock s ON l.id_lote=s.id_lote
                                                    JOIN productos p ON p.id_producto = s.id_producto
                                                    WHERE l.lote='$lote'");
                                    $row = $db->loadObject();
                                    $producto_lote = $row->producto;
                                    $codigo_lote = $row->codigo;

                                    throw new Exception("El lote \"$lote\" ya existe para el producto \"$producto_lote\" ($codigo_lote)");
                                } else {
                                    throw new Exception("Error de base de datos al registrar el lote \"$lote\". Code: ".$db->getErrorCode());
                                }
                            }

                            $id_lote = $db->getLastID();
                        }

                        $db->setQuery("INSERT INTO recepciones_compras_productos (id_recepcion_compra, id_producto, id_orden_compra, codigo, producto, costo, id_lote, lote, vencimiento, cantidad, canje, vencimiento_canje)
                                        VALUES ($id_recepcion_compra,$id_producto,$id_orden_compra,$codigo,'$producto',$costo,$id_lote,'$lote','$vencimiento',$cantidad,$canje,'$vencimiento_canje')");
                        if (!$db->alter()) {
                            throw new Exception("Error de base de datos al registrar el producto en la recepción. Code: ".$db->getErrorCode());
                        }

                        $id_recepcion_compra_producto = $db->getLastID();

                        producto_sumar_stock($db, $id_producto, $id_sucursal, $id_lote, $cantidad, 0);

                        $stock = new stdClass();
                        $stock->id_producto = $id_producto;
                        $stock->id_sucursal = $id_sucursal;
                        $stock->id_lote = $id_lote;

                        $historial = new stdClass();
                        $historial->cantidad = $cantidad;
                        $historial->fraccionado = 0;
                        $historial->operacion = ADD;
                        $historial->id_origen = $id_recepcion_compra_producto;
                        $historial->origen = REC;
                        $historial->detalles = "Recepción N° $numero";
                        $historial->usuario = $usuario;

                        if (!stock_historial($db, $stock, $historial)) {
                            throw new Exception("Error al guardar el historial de stock. Code: ".$db->getErrorCode());
                        }

                    }

                    // Se verifica para realizar la notificación
                    producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);

                    // Si no se cargaron productos se pasa al siguiente
                    if ($recepcionado == 0) {
                        continue;
                    }

                    $pendiente = ocp_cantidad_pendiente($db, $id_orden_compra, $id_producto);
                    if ($pendiente < 0) {
                        throw new Exception("La cantidad a recepcionar del producto \"$producto\" es mayor a la cantidad pendiente en la orden de compra N° $numero_oc");
                    }
                        
                    producto_actualizar_costo($db, $id_producto, $id_proveedor, $costo, $costo_ultimo);
                    ocp_actualizar_estado_auto($db, $id_orden_compra, $id_producto);
                } catch (Exception $e) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }

            }

            // Se actualiza el estado de las ordenes de compra
            foreach ($ordenes_compras as $key => $id) {
                try {
                    oc_actualizar_estado_auto($db, $id);
                } catch (Exception $e) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }
            }

            // Carga de archivos
            foreach ($archivos as $key => $file) {
                try {
                    $foo = new \Verot\Upload\Upload('data:'.$file["archivo"]);

                    if ($foo->uploaded) {
                        $targetPath = "../archivos/recepciones_compras/";
                        if (!is_dir($targetPath)) {
                            mkdir($targetPath, 0777, true);
                        }

                        $foo->file_new_name_body = md5($id_recepcion_compra);
                        $foo->image_convert      = jpg;
                        $foo->image_resize       = true;
                        $foo->image_ratio        = true;
                        $foo->image_ratio_crop   = true;
                        $foo->image_ratio_y      = true;
                        $foo->image_y            = 1200;
                        $foo->image_x            = 1200;
                        $foo->process($targetPath);
                        $archivo = str_replace("../", "", $targetPath . $foo->file_dst_name);
                        if ($foo->processed) {
                            $db->setQuery("INSERT INTO recepciones_compras_archivos (id_recepcion_compra, archivo) VALUES ($id_recepcion_compra,'$archivo')");
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
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Recepción registrada correctamente"]);
            
        break;

        case 'ver_productos':
            $db = DataBase::conectar();
            $id_proveedor = $db->clearText($_GET["id_proveedor"]);
            $id_orden_compra = $db->clearText($_GET["id_orden_compra"]);
            $where = "";

            if (isset($id_orden_compra) && !empty($id_orden_compra)) {
                $where .= "AND oc.id_orden_compra=$id_orden_compra";
            }

            $db->setQuery("SELECT
                                oc.id_orden_compra, 
                                oc.numero, 
                                ocp.id_orden_compra_producto, 
                                ocp.id_producto, 
                                ocp.producto, 
                                ocp.codigo, 
                                ocp.costo,
                                pp.costo AS costo_ultimo,  
                                ocp.cantidad,
                                ocp.total_costo as costo_tt,
                                pre.id_presentacion,
                                pre.presentacion,
                                CASE p.iva WHEN 1 THEN 'EXENTAS' WHEN 2 THEN '5%'  WHEN 3 THEN '10%'  END AS iva_str,
                                ocp.cantidad - (
                                    SELECT IFNULL(SUM(rcp.cantidad), 0)
                                    FROM recepciones_compras_productos rcp
                                    JOIN recepciones_compras rc ON rcp.id_recepcion_compra=rc.id_recepcion_compra
                                    WHERE rc.estado!=2 AND rcp.id_orden_compra=oc.id_orden_compra AND id_producto=ocp.id_producto
                                ) AS pendiente
                            FROM ordenes_compras_productos ocp
                            JOIN ordenes_compras oc ON ocp.id_orden_compra=oc.id_orden_compra
                            LEFT JOIN productos p ON ocp.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN productos_proveedores pp ON p.id_producto=pp.id_producto AND oc.id_proveedor=pp.id_proveedor
                            WHERE ocp.cantidad>0 AND oc.id_proveedor=$id_proveedor AND oc.estado IN(1,3) AND ocp.estado IN(0,1) $where");
            $row = ($db->loadObjectList()) ?: [];
            echo json_encode($row);
        break;

        case 'recuperar-numero':
            $db       = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM recepciones_compras");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

	}

?>
