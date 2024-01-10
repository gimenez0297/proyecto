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
        
        case 'cargar':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $observacion = $db->clearText($_POST['observacion']);
            $premio = json_decode($_POST['premio'], true);

            /*DATOS DE LA FACTURA*/
            $ruc = $db->clearText($_POST['ruc']);
            $id_proveedor = $db->clearText($_POST['id_proveedor']);
            $fecha_emision = $db->clearText($_POST['emision']);
            $timbrado = $db->clearText($_POST['timbrado']);
            $numero_documento = $db->clearText($_POST['documento']);
            $condicion = $db->clearText($_POST['condicion']);
            // $fecha_vencimiento = $db->clearText($_POST['vencimiento_factura']);
            $id_tipo_comprobante = $db->clearText($_POST['id_tipo_factura']);
            $check_iva = $db->clearText($_POST['check_iva']) ?: 0;
            $check_ire = $db->clearText($_POST['check_ire'])?: 0;
            $check_irp = $db->clearText($_POST['check_irp'])?: 0;
            $concepto =  mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");        
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $gravada_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']))?: 0;
            $gravada_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']))?: 0;
            $exenta = quitaSeparadorMiles($db->clearText($_POST['extenta']))?: 0;
            $obs = $db->clearText($_POST['observacion_factura']);
            $vencimientos = json_decode($_POST['vencimientos'], true);


            if (empty($premio)) {echo json_encode(["status" => "error", "mensaje" => "Ningún premio agregado. Favor verifique."]);exit;}
            if (empty($ruc)) {echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo RUC"]);exit;}
            if (empty($id_proveedor)) {echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);exit;}
            if (empty($fecha_emision)) {echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Fecha de Emision"]);exit;}
            if (empty($timbrado)) {echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Timbrado"]);exit;}
            if (empty($numero_documento)) {echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo número de documento"]);exit;}
            if (empty($condicion)) {echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una condición de compra"]);exit;}
            if ($condicion == 2 && empty($vencimientos)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese a menos un vencimiento. Verifique"]);exit;}           
             if (empty($id_tipo_comprobante)) {echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo de Factura"]);exit;}
            if (empty($concepto)) {echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Concepto"]);exit;}
            if (empty($monto)) {echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto"]);exit;}
            if ($monto == 0) {echo json_encode(["status" => "error", "mensaje" => "No puede cargar Facturas con Monto 0"]);exit;}

            /*DATOS DEL PROVEEDOR*/
            $db->setQuery("SELECT * FROM proveedores WHERE id_proveedor = $id_proveedor");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
               $db->rollback();  // Revertimos los cambios
               exit;
            }
            $proveedor = $db->loadObject(); 

            $razon_social= $db->clearText($proveedor->proveedor);
            
            $db->setQuery("SELECT MAX(numero) AS numero FROM cargas_premios");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero_sgte = zerofill($sgte_numero);
        
             // Se calcula el total monto y la cantidad total de premios
            $total_cantidad=0;
            $total_costo=0;
            foreach ($premio as $p) {
                 // Cálculo del costo
                 $costo = $db->clearText(quitaSeparadorMiles($p["costo"]));
                 $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
                //  $puntos = $db->clearText(quitaSeparadorMiles($p["puntos"]));
                 $total_costo += $costo * $cantidad;
                 $total_cantidad += $cantidad;
                //  $total_puntos += $puntos;
            }


            // Cabecera
            $db->setQuery("INSERT INTO cargas_premios (numero, cantidad, monto, observacion, estado, usuario, fecha) 
                                        VALUES ('$numero_sgte', $total_cantidad, $total_costo, '$observacion','1','$usuario', NOW());");
            if (!$db->alter()) {
                 echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            $id_cargas_premios = $db->getLastID();

            //Tipo de Gasto Premio = 3
            $db->setQuery("SELECT * FROM tipos_gastos WHERE id_sub_tipo_gasto = 3");
            $gasto = $db->loadObject();
            if (!$gasto) {
                echo json_encode(["status" => "error", "mensaje" => "No hay un gasto cargado con tipo Premio"]);
                exit;
            }
            $id_tipo_gasto     = $gasto->id_tipo_gasto;
            $id_sub_tipo_gasto = $gasto->id_sub_tipo_gasto;

            if ($check_iva == 0 && $check_ire==0 && $check_irp == 0) {
                $no_imputa = 1;
            }else {
                $no_imputa = 0;
            }

            if ($id_tipo_comprobante == '12' || $id_tipo_comprobante == '13') {
                $nro_compro = $numero_documento;
                $timbr_copro = $timbrado;
            }

            $db->setQuery("SELECT * FROM gastos ORDER BY id_gasto DESC LIMIT 1");
            $nro = $db->loadObject();
            $nro =  intval($nro->nro_gasto);
            $nro_gasto = zerofill(($nro*1)+1);

            //Datos de la Factura
            $db->setQuery("INSERT INTO gastos (id_tipo_gasto,id_sucursal,id_tipo_comprobante,id_sub_tipo_gasto,id_proveedor,id_cargas_premios,tipo_proveedor,nro_gasto,timbrado,fecha_emision,ruc,razon_social,condicion,
                           fecha_vencimiento,documento,concepto,monto,gravada_10,gravada_5,exenta,imputa_iva,imputa_ire,imputa_irp,no_imputa,
                           nro_comprobante_venta_asoc,timb_compro_venta_asoc,observacion,usuario)
                           VALUES($id_tipo_gasto,$id_sucursal,$id_tipo_comprobante,$id_sub_tipo_gasto,$id_proveedor,$id_cargas_premios,2,'$nro_gasto','$timbrado','$fecha_emision','$ruc','$razon_social',$condicion,
                           '$fecha_vencimiento','$numero_documento','$concepto',$monto,$gravada_10,$gravada_5,$exenta,'$check_iva','$check_ire','$check_irp',
                           '$no_imputa','$nro_compro','$timbr_copro','$obs','$usuario');");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
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
                    exit;
                }
            }
           

             foreach ($premio as $p) {
                // $id_carga_insumo = $db->clearText($p['id_carga_insumo']);
                $id_premio = $db->clearText($p['id_premio']);
                $monto = $db->clearText(quitaSeparadorMiles($p['costo']));
                $cantidad = $db->clearText(quitaSeparadorMiles($p['cantidad']));
                // $puntos = $db->clearText(quitaSeparadorMiles($p['puntos']));
                $vencimiento = $db->clearText($p['vencimiento']);
                
                $vencimiento_null = "'$vencimiento'";

                if (empty($vencimiento)) {
                    $vencimiento_null = 'NULL'; 
                }

                if (($cantidad) <= 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Cantidad debe ser mayor a 0"]);
                    exit;
                }

                // if (($puntos) <= 0) {
                //     echo json_encode(["status" => "error", "mensaje" => "Punto debe ser mayor a 0"]);
                //     exit;
                // }

                if (($monto) <= 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Monto debe ser mayor a 0"]);
                    exit;
                }


                $db->setQuery("UPDATE premios  SET costo=$monto  WHERE id_premio=$id_premio");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
         
             $db->setQuery("INSERT INTO cargas_premios_productos (id_cargas_premios, id_premio, cantidad, monto) 
                                        VALUES ('$id_cargas_premios', '$id_premio', '$cantidad', $monto)");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }

                premio_sumar_stock($db, $id_premio, $cantidad);
                
                $historial = new stdClass();
                $historial->id_premio = $id_premio;
                $historial->premio = $premio;
            $historial->cantidad = $cantidad;
                $historial->operacion = ADD;
                $historial->id_origen = $id_cargas_premios;
                $historial->origen = CAR;
                $historial->detalles = "Carga N° " .$numero_sgte;
                $historial->usuario = $usuario;

                if (!stock_historial_premio($db, $historial)) {
                    throw new Exception("Error al guardar el historial de stock. Code: ".$db->getErrorCode());
                } 
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Carga de Premio registrada correctamente"]);
        break;

        case 'recuperar-numero':
            $db   = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM cargas_premios");
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
