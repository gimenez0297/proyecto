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
            $id_cliente = $db->clearText($_POST['id_cliente']);
            $observacion = $db->clearText($_POST['observacion']);
            $razon_social = $db->clearText($_POST['razon_social']);
            $ruc = $db->clearText($_POST['ruc']);
            $detalle_puntos = $db->clearText(quitaSeparadorMiles($_POST['detalle_puntos']));
            $numero = $db->clearText(quitaSeparadorMiles($_POST['numero']));
            $productos = json_decode($_POST['productos'], true);
            
            if (empty($productos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún premio agregado. Favor verifique."]);
                exit;
            }

            if ($ruc == '44444401-7') {
                echo json_encode(["status" => "error", "mensaje" => "Los cliente Sin Nombre no pueden canjear puntos"]);
                exit;
            }

            //Comprobamos que el cliente tiene los puntos suficientes
            $db->setQuery("SELECT puntos AS punto_c
                                        FROM clientes
                                        WHERE id_cliente =$id_cliente");
            $punto_c = $db->loadObject()->punto_c;

            if ($detalle_puntos < $punto_c  || $detalle_puntos <= 0 ) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "El cliente no tiene puntos suficientes"]);
                exit;
            }

             //Buscamos el numero mayor
             $db->setQuery("SELECT MAX(numero) AS numero FROM canjes_puntos");
             $db->rollback(); // Revertimos los cambios
             $row = $db->loadObject();
 
             if ($db->error()) {
                 echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                 exit;
             }
             
             $sgte_numero = intval($row->numero) + 1;
             $numero_sgte = zerofill($sgte_numero);

            //Obtenemos la cantidad de puntos totales
            foreach ($productos as $p) {
                    $id_premio = $db->clearText($p["id_premio"]);
                    $premio = $db->clearText($p["premio"]);
                    $puntos = $db->clearText(quitaSeparadorMiles($p["puntos"]));
                    $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
                    $costo = $db->clearText(quitaSeparadorMiles($p["costo"]));

                    $total_costo += $db->clearText(quitaSeparadorMiles($p["total_costo"]));
                    $total_puntos  += $db->clearText(quitaSeparadorMiles($p["monto"]));
                    $total_cantidad += $cantidad;

                //Validamos si el canje trae cantidades validos
                if ($cantidad <= 0) {
                    echo json_encode(["status" => "error", "mensaje" => "No puede cargar premios con cantidad 0. Favor verifique."]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
                //Validamos si el canje trae puntos validos
                if ($puntos <= 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Punto debe ser mayor a 0"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
                //Validamos si el cliente posee los puntos necesarios
                if($punto_c < $total_puntos ){
                    echo json_encode(["status" => "error", "mensaje" => "El cliente no posee los puntos suficientes"]);
                    exit;
                }
                //Validamos si tenemos la cantidad en stock
                $db->setQuery("SELECT IFNULL(SUM(stock),0) AS stock FROM stock_premios where id_premio=$id_premio");
                $stock = $db->loadObject()->stock;
                if ($db->error()) {
                    $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al buscar el stock"]);
                    exit;
                }
                if($stock<$total_cantidad ){
                    $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "No hay stock suficiente para completar el canje"]);
                    exit;
                }  
            }
            //Recuperamos el razon_social
            $db->setQuery("SELECT  razon_social
                                        FROM clientes
                                        WHERE id_cliente =$id_cliente");
                $razon_social_ = $db->loadObject()->razon_social;
                if (!$razon_social_) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de Base de datos"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }

                // Cabecera
            $db->setQuery("INSERT INTO canjes_puntos ( numero, cantidad, puntos, id_cliente, ruc, razon_social, observacion, estado, fecha, usuario)
                                       VALUES ('$numero_sgte', '$total_cantidad', '$total_puntos', '$id_cliente', '$ruc', '$razon_social_', '$observacion' , '1', NOW(), '$usuario');");

                if (!$db->alter()) {
                    $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    
                    exit;
                }
           
                $id_canje_punto = $db->getLastID(); 
             
            foreach ($productos as $p) {
                $id_premio = $db->clearText($p["id_premio"]);
                $premio = $db->clearText($p["premio"]);
                $puntos = $db->clearText(quitaSeparadorMiles($p["puntos"]));
                $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
                $costo = $db->clearText(quitaSeparadorMiles($p["costo"]));

                $total_total = $cantidad * $puntos;
                //Detalle
                $db->setQuery("INSERT INTO canjes_puntos_premios ( id_canje_punto, id_premio, costo, cantidad, puntos, total)
                                VALUES ('$id_canje_punto', '$id_premio', '$costo', '$cantidad', '$puntos', '$total_total' );");

                if (!$db->alter()) {
                    $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }  

                //Se resta el stock
                premio_restar_stock($db, $id_premio, $cantidad);
        
                 // Se insertan datos en stock insumo historial
        
                $historial = new stdClass();
                $historial->id_premio = $id_premio;
                $historial->premio = $premio;
                $historial->cantidad = $cantidad;
                $historial->operacion = SUB;
                $historial->id_origen = $id_canje_punto;
                $historial->origen = CANJ;
                $historial->detalles = "Canje N° " .zerofill($numero);
                $historial->usuario = $usuario;
        
                if (!stock_historial_premio($db, $historial)) {
                    $db->rollback(); // Revertimos los cambios
                    throw new Exception("Error al guardar el historial de stock. Code: ".$db->getErrorCode());
                } 
            }

            //Se insertar los puntos utilizados en la tabla clientes_puntos, tener en cuenta solo los puntos con estado cero
            $db->setQuery("SELECT puntos, id_cliente_punto, fecha, utilizados
                                FROM clientes_puntos 
                                WHERE id_cliente=$id_cliente AND  estado=0
                                ORDER BY fecha ASC");
            $rows = $db->loadObjectList();

            if (empty($rows)) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "No se ha encontrado ningún punto del cliente"]);
                exit;
            }

            $puntos_carga=$total_puntos;
            foreach ($rows as $v) {
                $id_cliente_punto = $v->id_cliente_punto;
                $puntos = intval($v->puntos) - intval($v->utilizados);
             
                if($puntos_carga >= $puntos){
                    $utilizados = $puntos;
                    $puntos_carga -= $puntos;
                    $estado = 1;
                }else{
                    $utilizados = $puntos_carga ;
                    $puntos_carga= 0;
                    $estado = 0;
                }

                    $db->setQuery("UPDATE clientes_puntos SET utilizados = utilizados + $utilizados, estado = $estado
                                    WHERE id_cliente_punto = $id_cliente_punto AND  estado = 0 ");

                    if (!$db->alter()) {
                        $db->rollback(); // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los puntos acumulados"]);
                        exit;
                    }

                    $db->setQuery("INSERT INTO canjes_puntos_utilizados ( id_canje_punto, id_cliente_punto, utilizados)
                    VALUES ('$id_canje_punto', '$id_cliente_punto', '$utilizados');");

                    if (!$db->alter()) {
                        $db->rollback(); // Revertidisos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                        exit;
                    }        

                    if (!actualiza_puntos_clientes($id_cliente)) {
                        $db->rollback(); // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar los puntos del cliente"]);
                        exit;
                    }

                    if($puntos_carga == 0) break;
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Canje registrado correctamente"]);
        break;

        case 'recuperar-numero':
            $db       = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM canjes_puntos");
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
