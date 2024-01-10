<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;
    $id_usuario = datosUsuario($usuario)->id;

    switch ($q) {

        case 'ver_productos':
            $db = DataBase::conectar();
            $id_principio = $db->clearText($_GET['id_principio']);
            $codigo = $db->clearText($_GET['codigo']);

            $where = "";
            if (isset($id_principio) && !empty($id_principio) && intval($id_principio)  > 0) {
                $where = "AND pa.id_principio=$id_principio";
            }

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING codigo LIKE '$search%' OR producto LIKE '$search%' OR  presentacion LIKE '$search%' OR laboratorio LIKE '$search%' OR principios_activos LIKE '$search%'";
            }

            // Si se busca por código omitir los demás filtros
            if (isset($codigo) && !empty($codigo) && intval($codigo) > 0) {
                $where = " AND p.codigo = '$codigo'";
                $having = "";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    p.id_producto, 
                                    p.producto, 
                                    p.codigo, 
                                    p.cantidad_fracciones, 
                                    p.observaciones, 
                                    pre.id_presentacion, 
                                    pre.presentacion,
                                    l.id_laboratorio,
                                    l.laboratorio,
                                    pa.id_principio,
                                    p.comision,
                                    p.comision_concepto,
                                    p.controlado,
                                    p.descuento_fraccionado,
                                    p.fraccion,
                                    -- GROUP_CONCAT(pa.nombre SEPARATOR ', ') AS principios_activos,
                                    pa.nombre AS principios_activos,
                                    CASE p.iva WHEN 1 THEN 'EXENTAS' WHEN 2 THEN '5%'  WHEN 3 THEN '10%'  END AS iva_str,
                                    IFNULL(p.precio, 0) AS precio, 
                                    IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado,
                                    (SELECT 
                                        IFNULL(SUM(s.stock), 0)
                                        FROM stock s 
                                        WHERE s.`id_producto`=p.`id_producto` AND s.`id_sucursal`= $id_sucursal
                                        AND s.id_lote IN (SELECT id_lote FROM lotes WHERE s.id_lote=id_lote AND vencimiento>=CURRENT_DATE())) AS stock,
                                    (SELECT 
                                        IFNULL(SUM(s.fraccionado), 0)
                                        FROM stock s 
                                        WHERE s.`id_producto`=p.`id_producto` AND s.`id_sucursal`= $id_sucursal
                                        AND s.id_lote IN (SELECT id_lote FROM lotes WHERE s.id_lote=id_lote AND vencimiento>=CURRENT_DATE())) AS stock_fraccionado,
                                    p.tipo
                            FROM productos p
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
                            LEFT JOIN productos_principios ppa ON p.id_producto=ppa.id_producto
                            LEFT JOIN principios_activos pa ON ppa.id_principio=pa.id_principio
                            WHERE p.estado=1 $where
                            GROUP BY p.id_producto
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

        case 'cargar':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id_cliente = $db->clearText($_POST['id_cliente']);
            $condicion = $db->clearText($_POST['condicion']) ?: 1;
            $vencimiento = $db->clearText($_POST['vencimiento']);
            $observacion = $db->clearText($_POST['observacion']);
            $productos = json_decode($_POST['productos'], true);
            $cobros = json_decode($_POST['cobros'], true);
            $delivery = $db->clearText($_POST['delivery']) ? 1 : 0;
            $id_delivery = $db->clearText($_POST['id_delivery']);
            $receta = $db->clearText($_POST['receta']) ? 1 : 0;

            $courier = $db->clearText($_POST['courier']) ? 1 : 0;

            $id_nota_credito = $db->clearText($_POST['id_nota_credito']);
            $nota_credito = false;

            $editar_cobros = 0;

            $token_caja = $_COOKIE['token'];
            $caja_abierta = false;

            $caja_ = caja_identificacion($db, $token_caja);
            if (empty($caja_)) {
                echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración.", "caja_abierta" => $caja_abierta]);
                exit;
            }

            $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.estado=1 AND cu.estado=0 AND c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
            $id_caja = $db->loadObject()->id_caja;

            if($id_caja != $caja_->id_caja){
                echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración."]);
                exit;
            }

            $caja = caja_abierta($db, $id_sucursal, $usuario);
            $id_caja_horario = $caja->id_caja_horario;
            $id_caja = $caja->id_caja;
            if (!$caja) {
                echo json_encode(["status" => "error", "mensaje" => "No se ha encontrado ninguna caja abierta"]);
                exit;
            }

            if (empty($id_cliente)) {
                echo json_encode(["status" => "error", "mensaje" => "Cliente no registrado"]);
                exit;
            }
            if (empty($productos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún producto agregado. Favor verifique."]);
                exit;
            }
            if ($condicion == 2 && empty($vencimiento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Vencimiento"]);
                exit;
            }
            if ($condicion == 1 && empty($cobros)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún cobro agregado. Favor verifique."]);
                exit;
            }
            if ($delivery == 1 && empty($id_delivery) && intval($id_delivery) == 0) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún delivery seleccionado. Favor verifique."]);
                exit;
            }

            // Solo si es factura contado se registran los cobros
            if ($condicion == 1) {
                foreach ($cobros as $key => $value) {
                    if ($value["id_metodo_pago"] == 7) $nota_credito = true;
                    // Si la venta es con delivery, el método de pago es tarjeta y no se ha cargado el número de voucher
                    if (($value["id_metodo_pago"] == 2 || $value["id_metodo_pago"] == 3) && empty($value["detalles"])) $editar_cobros = 1; 
                }
            }

            if ($nota_credito === true) {
                if (empty($id_nota_credito)) {
                    echo json_encode(["status" => "error", "mensaje" => "Nota De Crédito no seleccionada"]);
                    exit;
                }

                $db->setQuery("SELECT nc.estado, CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, nc.numero) AS numero
                            FROM notas_credito nc
                            JOIN timbrados t ON nc.id_timbrado=t.id_timbrado
                            WHERE id_nota_credito=$id_nota_credito");
                $row = $db->loadObject();

                if ($row->estado == 1) {
                    echo json_encode(["status" => "error", "mensaje" => "Nota De Crédito $row->numero utilizada"]);
                    exit;
                }
                if ($row->estado == 2) {
                    echo json_encode(["status" => "error", "mensaje" => "Nota De Crédito $row->numero anulada"]);
                    exit;
                }
            } else {
                $id_nota_credito = "NULL";
            }

            if ($delivery == 0) $id_delivery = "NULL";

            // Se registran o actualizan los datos del cliente
            $db->setQuery("SELECT id_cliente, ruc, razon_social FROM clientes WHERE id_cliente=$id_cliente");
            $row_cliente = $db->loadObject();
            $id_cliente = $row_cliente->id_cliente;
            $ruc = $row_cliente->ruc;
            $razon_social = $row_cliente->razon_social;

            if (empty($row_cliente)) {
                echo json_encode(["status" => "error", "mensaje" => "Cliente no encontrado"]);
                exit;
            }

            //DATOS DEL TIMBRADO
            $db->setQuery("SELECT id_timbrado, IFNULL(desde,0) AS desde, IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_sucursal=$id_sucursal AND id_caja =$id_caja AND estado=1 AND tipo='0'");
            $tim = $db->loadObject();
            $id_timbrado = $tim->id_timbrado;
            $desde = $tim->desde;
            $hasta = $tim->hasta;
            
            if (empty($id_timbrado)){
                echo json_encode(["status" => "error","mensaje" => "Error. No existen timbrados activos para la Factura. Consulte con administración."]);
                //$db->rollback();
                exit;
            }
    
            //OBTENEMOS EL MAYOR NUMERO DE FACTURA
            // $db->setQuery("SET @@cte_max_recursion_depth = 01000000;");
            // $db->alter();

            //AQUI SE GENERA EL NUMERO DE LA FACTURA

                //establecemos un rango entre el valor $desde del timbrado hasta el numero maximo de factura en ese timbrado    
                $db->setQuery("SELECT MAX(numero) AS max FROM facturas WHERE id_timbrado='$id_timbrado'");
                $row = $db->loadObject();
                $numero_maximo = $row->max;

                //si no existen numeros en la factura, se toma el valor $desde como el primero
                if(!empty($numero_maximo)){
                    $rango_numeros= range($desde,$numero_maximo);
                    
                    //obtenemos todos los numeros existentes en las facturas de ese timbrado y los guardamos en un array
                        $db->setQuery("SELECT TRIM(LEADING '0' FROM numero) AS numero FROM facturas WHERE id_timbrado='$id_timbrado' ORDER BY numero ASC");
                        $rows = $db->loadObjectList();
                        foreach ($rows as $r) {
                            $lista_numeros[] = intval($r->numero);
                        }
                    //

                    //obtenemos una lista de los numeros faltantes teniendo en cuenta el rango anteriormente establecido y utilizamos el primer valor como numero para la factura
                    $numeros_faltantes = array_diff($rango_numeros,$lista_numeros);

                    //se usar reset para obtener el primer valor del arreglo ya que con array_diff el indice 0 no esta garantizado
                    $primer_numero = reset($numeros_faltantes);

                    //si no existe numeros faltantes, el numero de la factura sera el maximo + 1
                    empty($numeros_faltantes) ? $numero_fact = $numero_maximo+1 : $numero_fact = $primer_numero;

                }else{
                    $numero_fact = $desde;
                }
            
            //
            
            if ($numero_fact > $hasta) {
            echo json_encode(["status" => "error", "mensaje" => "Ya no tiene numeros disponible para facturar"]);
            exit;
            };

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número de la factura"]);
                exit;
            }

            $gravada_10 = 0; $gravada_5 = 0; $exenta = 0; $cantidad = 0; $total_venta = 0; $total_costo = 0;
            foreach ($productos as $key => $p) {
                $id_prod = $db->clearText($p['id_producto']);
                $cantidad = $db->clearText(quitaSeparadorMiles($p['cantidad']));
                $cantidad_total += $cantidad;
                $precio = $db->clearText(quitaSeparadorMiles($p['precio_venta']));
                $precio_costo = $db->clearText(quitaSeparadorMiles($p['precio']));
                //$total_venta += $precio * $db->clearText(quitaSeparadorMiles($p['cantidad']));
                $total_venta += $db->clearText(quitaSeparadorMiles($p['total']));
                $iva = $db->clearText(quitaSeparadorMiles($p['iva_str']));
                $total = $db->clearText(quitaSeparadorMiles($p['total']));
                $id_lote = $db->clearText($p['id_lote']);
                $id_lote = $db->clearText($p['id_lote']);
                $fraccion = $db->clearText($p['fraccionado']);
                
                $controlado = $db->clearText($p['controlado']);
                
                if($controlado == 1 && $receta == 0){
                    echo json_encode(["status" => "error","mensaje" => "Debe de seleccionar la receta para el producto controlado."]);
                    exit;
                }
                
                $costo = 0;
                $db->setQuery("SELECT IFNULL(costo,0) AS costo FROM lotes WHERE id_lote = $id_lote");
                $lote_costo = $db->loadObject()->costo;    
                
                if ($fraccion == 1) {
                    $db->setQuery("SELECT cantidad_fracciones FROM productos WHERE id_producto=$id_prod");
                    $cant_fracciones = $db->loadObject()->cantidad_fracciones ?: 0;
                    $costo = (($lote_costo?:0)/$cant_fracciones);
                } else {
                    $costo = $lote_costo?:0;
                }
                

                $total_costo += $costo * $cantidad;

                if ($iva == 'EXENTAS') {
                    $exenta += $total;
                }else if($iva == '5%'){
                    $gravada_5 +=$total;
                }else if ($iva == '10%') {
                    $gravada_10 += $total;
                }
            }

            //Sacar el IVA para el asiento
            $iva_5_asiento = $gravada_5 * 0.05;
            $iva_10_asiento = $gravada_10 * 0.1;
            $gravada_5_sin_iva  = round($gravada_5 - ($gravada_5 * 0.05));
            $gravada_10_sin_iva = round($gravada_10 - ($gravada_10 * 0.1));
            $gravada_suma = $iva_5_asiento + $iva_10_asiento;
            $total_venta_sin_iva = $total_venta - $gravada_suma;

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
            VALUES($id_libro_diario_periodo,1,NOW(),'$nro',$total_venta,'VENTA EN SUCURSAL','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el vendedor que realiza la venta.  
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                exit;
            }

            $id_asiento = $db->getLastID();

            //Se cargan los detalles del asiento. Se cargan los IVA correspondiente. 
            //Solo se realiza el asiento si la venta contiene productos con iva 5%.
            if($iva_5_asiento > 0){
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,32,'IVA 5%',0,$iva_5_asiento);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                    exit;
                }
            }

            //Solo se realiza el asiento si la venta contiene productos con iva 10%.
            if($iva_10_asiento > 0){
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,31,'IVA 10%',0,$iva_10_asiento);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                    exit;
                }
            }

            if ($condicion == 2) {
                $vencimiento = "'$vencimiento'";
                $estado = 0;
                $saldo = $total_venta;
            } else {
                $condicion = 1;
                $vencimiento = "NULL";
                $estado = 1;
                $saldo = 0;
            }

            $db->setQuery("INSERT INTO facturas (
                        id_sucursal,
                        id_caja_horario,
                        id_timbrado,
                        id_nota_credito,
                        numero,
                        fecha_venta,
                        condicion,
                        vencimiento,
                        id_cliente,
                        ruc,
                        razon_social,
                        cantidad,
                        descuento,
                        total_costo,
                        total_venta,
                        exenta,
                        gravada_5,
                        gravada_10,
                        saldo,
                        usuario,
                        estado,
                        fecha,
                        delivery,
                        id_delivery,
                        receta,
                        courier,
                        editar_cobros,
                        id_asiento
                    ) VALUES (
                        $id_sucursal,
                        $id_caja_horario,
                        $id_timbrado,
                        $id_nota_credito,
                        LPAD($numero_fact,7,'0'),
                        NOW(),
                        '$condicion',
                        $vencimiento,
                        '$id_cliente',
                        '$ruc',
                        '$razon_social',
                        '$cantidad_total',
                        0,
                        $total_costo,
                        $total_venta,
                        $exenta,
                        $gravada_5,
                        $gravada_10,
                        $saldo,
                        '$usuario',
                        $estado,
                        NOW(),
                        $delivery,
                        $id_delivery,
                        '$receta',
                        $courier,
                        $editar_cobros,
                        $id_asiento
                    )");
	
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar la factura"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
	
            $id_factura = $db->getLastID();
            $descuento_total = 0;
            // Detalle de la factura
            foreach ($productos as $key => $p) {
                $id_producto = $db->clearText($p['id_producto']);
                $producto = $db->clearText($p['producto']);
                $fraccionado = $db->clearText($p['fraccionado']);
                $cantidad = $db->clearText(quitaSeparadorMiles($p['cantidad']));
                $precio = $db->clearText(quitaSeparadorMiles($p['precio_venta']));
                $comision = $db->clearText($p['comision']);
                $comision_concepto = $db->clearText($p['comision_concepto']);
                $remate = $db->clearText($p['remate']);
                $tipo_descuento = $db->clearText($p['tipo_descuento']);
                $descuento_porc = $db->clearText($p['descuento_porcentaje']);
                $id_descuento = $db->clearText($p['id_descuento']) ?: "NULL";
                $iva_str = $db->clearText(quitaSeparadorMiles($p['iva_str']));
                $id_lote = $db->clearText($p['id_lote']);
                $lote = $db->clearText($p['lote']);
                $tipo = $db->clearText($p['tipo']);

                if ($iva_str == 'EXENTAS') {
                    $iva_porc = 0;
                }else if($iva_str == '5%'){
                    $iva_porc =5;
                }else if ($iva_str == '10%') {
                    $iva_porc= 10;
                }


                $descuento = ($cantidad * $precio) * $descuento_porc / 100;
                $total = ($cantidad * $precio) - $descuento;
                $descuento_total += $descuento;

                if (intval($cantidad) == 0) {
                    echo json_encode(["status" => "error", "mensaje" => "El producto \"$producto\" tiene cantidad 0. Favor verifique"]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }

                if ($tipo == 1) {
                    try {
                        $stock_lote = producto_stock($db, $id_producto, $id_sucursal, $id_lote);
                    } catch (Exception $e) {
                        $db->rollback();  // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                        exit;
                    }
    
                    if ($fraccionado == 1) {
                        $db->setQuery("SELECT cantidad_fracciones FROM productos WHERE id_producto=$id_producto");
                        $row = $db->loadObject();
                        $cantidad_fracciones = $row->cantidad_fracciones;
    
                        if ($db->error()) {
                            $db->rollback();  // Revertimos los cambios
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al consultar el producto \"$producto\" para realizar la fracción"]);
                            exit;
                        }
    
                        if (intval($cantidad_fracciones) == 0) {
                            $db->rollback();  // Revertimos los cambios
                            echo json_encode(["status" => "error", "mensaje" => "El producto \"$producto\" no puede ser fraccionado"]);
                            exit;
                        }
    
                        if (intval($cantidad_fracciones) <= $cantidad) {
                            $db->rollback();  // Revertimos los cambios
                            echo json_encode(["status" => "error", "mensaje" => "La cantidad cargada iguala o supera la cantidad de fracciones ($cantidad_fracciones) que puede tener el producto \"$producto\""]);
                            exit;
                        }
    
                        // Si el stock fraccionado es insuficiente
                        if ($stock_lote->fraccionado < $cantidad) {
                            // Se verifica si hay el stock para fraccionar
                            if ($stock_lote->stock <= 0) {
                                $db->rollback();  // Revertimos los cambios
                                echo json_encode(["status" => "error", "mensaje" => "El stock fraccionado del producto \"$producto\" es insuficiente"]);
                                exit;
                            }
    
                            // Se realiza la conversión de entero a fraccionado
                            try {
                                // Se resta 1 del stock
                                producto_restar_stock($db, $id_producto, $id_sucursal, $id_lote, 1, 0);
                                // Se suma la cantidad de fracciones en que se divide el producto al stock fraccionado
                                producto_sumar_stock($db, $id_producto, $id_sucursal, $id_lote, 0, $cantidad_fracciones);
    
                                $stock = new stdClass();
                                $stock->id_producto = $id_producto;
                                $stock->id_sucursal = $id_sucursal;
                                $stock->id_lote = $id_lote;
    
                                $historial = new stdClass();
                                $historial->cantidad = 1;
                                $historial->fraccionado = 0;
                                $historial->operacion = SUB;
                                $historial->id_origen = "NULL";
                                $historial->origen = EAF;
                                $historial->detalles = "Conversión de entero a fraccionado";
                                $historial->usuario = $usuario;
    
                                // Se registra la resta del stock
                                if (!stock_historial($db, $stock, $historial)) {
                                    throw new Exception("Error de base de datos al guardar el historial de stock");
                                }
    
                                $historial->cantidad = 0;
                                $historial->fraccionado = $cantidad_fracciones;
                                $historial->operacion = ADD;
    
                                // Se registra la suma del stock fraccionado
                                if (!stock_historial($db, $stock, $historial)) {
                                    throw new Exception("Error de base de datos al guardar el historial de stock");
                                }
    
                            } catch (Exception $e) {
                                $db->rollback();  // Revertimos los cambios
                                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                                exit;
                            }
                        }
    
                    } else if ($stock_lote->stock < $cantidad) {
                        $db->rollback();  // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "El stock del producto \"$producto\" es insuficiente"]);
                        exit;
                    }
                }
                

                $db->setQuery("INSERT INTO facturas_productos (
                                id_factura,
                                id_producto, 
                                producto,
                                fraccionado,
                                id_lote,
                                lote,
                                cantidad,
                                precio,
                                remate,
                                tipo_descuento,
                                descuento,
                                descuento_porc,
                                total_venta,
                                iva,
                                comision,
                                comision_concepto
                            ) VALUES (
                                $id_factura,
                                $id_producto,
                                '$producto',
                                $fraccionado,
                                $id_lote,
                                '$lote',
                                $cantidad,
                                $precio,
                                $remate,
                                '$tipo_descuento',
                                $descuento,
                                $descuento_porc,
                                $total,
                                $iva_porc,
                                $comision,
                                '$comision_concepto'
                            )");

                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar la factura"]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }

                $id_factura_producto = $db->getLastID();

                if ($fraccionado == 0) {
                    $restar_stock = $cantidad;
                    $restar_fraccionado = 0;
                } else {
                    $restar_stock = 0;
                    $restar_fraccionado = $cantidad;
                }

                try {
                    producto_restar_stock($db, $id_producto, $id_sucursal, $id_lote, $restar_stock, $restar_fraccionado);
                } catch (Exception $e) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                    exit;
                }

                $stock = new stdClass();
                $stock->id_producto = $id_producto;
                $stock->id_sucursal = $id_sucursal;
                $stock->id_lote = $id_lote;

                $historial = new stdClass();
                $historial->cantidad = $restar_stock;
                $historial->fraccionado = $restar_fraccionado;
                $historial->operacion = SUB;
                $historial->id_origen = $id_factura_producto;
                $historial->origen = FAC;
                $historial->detalles = "Factura N° ".zerofill($numero_fact);
                $historial->usuario = $usuario;

                if (!stock_historial($db, $stock, $historial)) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }

                // Se verifica para realizar la notificación
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);

            }

            // INSERTAMOS LOS PAGOS SI ES FACTURA CONTADO
            if ($condicion == 1) {

                $total_cobros = array_sum(array_column($cobros, 'monto'));
                // Si se pago con nota de crédito se permite que el total cobrado sea mayor
                if ($total_cobros > $total_venta && $nota_credito === false) {
                    echo json_encode(["status" => "error", "mensaje" => "El total cobrado es mayor al total de la venta"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
                if ($total_cobros < $total_venta) {
                    echo json_encode(["status" => "error", "mensaje" => "El total cobrado es menor al total de la venta"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }

                $porcentajes_pagos = [];
                foreach ($cobros as $key => $v){
                    $id_metodo_pago = $db->clearText($v['id_metodo_pago']) ?: 'NULL';
                    $id_descuento = $db->clearText($v['id_descuento_metodo_pago']) ?: 'NULL';
                    $id_entidad = $db->clearText($v['id_entidad']) ?: 'NULL';
                    $metodo_pago = $db->clearText($v['metodo_pago'])?: 'NULL';
                    $metodo_str = explode(' - ', $metodo_pago)?: 'Descuento';
                    $monto = $db->clearText(quitaSeparadorMiles($v['monto']));
                    $detalles = $db->clearText($v['detalles']);

                    if (($id_metodo_pago == 2 || $id_metodo_pago == 3) && $delivery == 0 && empty($detalles)) {
                        echo json_encode(["status" => "error", "mensaje" => "El campo Detalles es obligatorio para los métodos de pago de tipo Tarjeta en ventas sin delivery"]);
                        $db->rollback(); // Revertimos los cambios
                        exit;
                    }

                    $db->setQuery("INSERT INTO `cobros` ( 
                                    id_factura, 
                                    id_sucursal, 
                                    id_metodo_pago,
                                    id_descuento_metodo_pago, 
                                    id_entidad,
                                    metodo_pago, 
                                    monto,
                                    detalles, 
                                    fecha, 
                                    usuario, 
                                    estado
                                ) VALUES ( 
                                    $id_factura, 
                                    $id_sucursal, 
                                    $id_metodo_pago, 
                                    $id_descuento,
                                    $id_entidad,
                                    '$metodo_str[1]', 
                                    $monto,
                                    '$detalles', 
                                    NOW(), 
                                    '$usuario',
                                    1
                                )");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar los cobros"]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }

                    //Sacamos que porcentaje equivale el monto del pago en el total de la venta.
                    $porcentaje = round($monto * 100 / $total_venta);
                    //Guardamos los porcentajes de que equivalen 
                    array_push($porcentajes_pagos, (object) ['porcentaje' => $porcentaje, 'metodo_pago' => $id_metodo_pago]);
                }

                //Recorremos el array de los porcentajes. 
                foreach($porcentajes_pagos as $key => $p){
                    $porcentaje  = $p->porcentaje;
                    $metodo_pago = $p->metodo_pago;

                    $monto_asiento = $total_venta_sin_iva * $porcentaje /  100;

                    //Si lo que se vende es un producto suma las siguientes cuentas. 
                    if ($tipo == 1) {
                        //Si el metodo de pago es efectivo ingresa en la cuenta de caja.
                        if($metodo_pago == 1){
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,8,'INGRESO POR VENTA',$monto_asiento,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                                $db->rollback();  // Revertimos los cambios
                                exit;
                            }
                        }
                        //Si es metodo de pago tarjeta de credito ingresaen la cuenta de tarjeta de credito.
                        if($metodo_pago == 2){
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,14,'INGRESO POR VENTA',$monto_asiento,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                                $db->rollback();  // Revertimos los cambios
                                exit;
                            }
                        }
                        //Si es metodo de pago tarjeta de credito ingresaen la cuenta de tarjeta de debito.
                        if($metodo_pago == 3){
                            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                            VALUES($id_asiento,15,'INGRESO POR VENTA',$monto_asiento,0);");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                                $db->rollback();  // Revertimos los cambios
                                exit;
                            }
                        }
                    }else{
                    //Si es un servicio, suma a la cuenta de Ventas de Bienes
                        $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                        VALUES($id_asiento,144,'INGRESO POR VENTA DE SERVICIO',0,$monto_asiento);");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                            $db->rollback();  // Revertimos los cambios
                            exit;
                        }
                    }
                }

                // Si es un canje de puntos
                $metodo_pago_puntos = array_search(9, array_column($cobros, "id_metodo_pago"));
                if ($metodo_pago_puntos !== false) {

                    // Validaciones
                    // Si es un canje, no se debe mezclar con otro método de pago
                    if (count($cobros) > 1) {
                        echo json_encode(["status" => "error", "mensaje" => "El canje de puntos no puede ser utilizado con otro método de pago"]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }
                    if ($ruc == "44444401-7") {
                        echo json_encode(["status" => "error", "mensaje" => "El cliente SIN NOMBRE no puede realizar un canje de puntos"]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }

                    // Se consulan los puntos del cliente
                    $db->setQuery("SELECT puntos FROM clientes WHERE id_cliente=$id_cliente");
                    $puntos_disponibles = $db->loadObject()->puntos;
                    if ($db->error()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al consultar los puntos del cliente"]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }

                    // Se consulan los puntos necesarios para el canje
                    try {
                        $puntos = total_puntos_venta($db, $productos, $condicion);
                    } catch (Exception $e) {
                        $db->rollback();  // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => $e->getMessage(), "puntos" => 0]);
                        exit;
                    }

                    // Se verifica que el cliente tenga puntos suficientes para realizar el canje
                    if ($puntos_disponibles < $puntos) {
                        echo json_encode(["status" => "error", "mensaje" => "El cliente no posee los puntos suficientes para realizar el canje"]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }

                    // Se registran los puntos utilizados
                    $db->setQuery("SELECT puntos, id_cliente_punto, fecha
                                    FROM clientes_puntos 
                                    WHERE id_cliente = $id_cliente AND estado = 0
                                    ORDER BY fecha ASC");
                    $rows = $db->loadObjectList();
                    if ($db->error()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al consultar los puntos del cliente"]);
                        $db->rollback();  // Revertimos los cambios
                        exit;
                    }

                    $puntos_carga = $puntos;
                    foreach ($rows as $v) {
                        $id_cliente_punto = $v->id_cliente_punto;
                        $puntos = intval($v->puntos) - intval($v->utilizados);

                        if ($puntos_carga >= $puntos) {
                            $utilizados = $puntos;
                            $puntos_carga -= $puntos;
                            $estado = 1;
                        } else {
                            $utilizados = $puntos_carga;
                            $puntos_carga = 0;
                            $estado = 0;
                        }

                        $db->setQuery("UPDATE clientes_puntos SET utilizados = utilizados + $utilizados, estado = $estado
                                        WHERE id_cliente_punto = $id_cliente_punto");
                        if (!$db->alter()) {
                            $db->rollback(); // Revertimos los cambios
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los puntos utilizados"]);
                            exit;
                        }

                        $db->setQuery("INSERT INTO facturas_puntos_utilizados (id_factura, id_cliente_punto, utilizados)
                                        VALUES ('$id_factura', '$id_cliente_punto', '$utilizados');");
                        if (!$db->alter()) {
                            $db->rollback(); // Revertimos los cambios
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los puntos utilizados"]);
                            exit;
                        }

                        // Si ya no hay puntos para restar se finaliza
                        if ($puntos_carga == 0) break;
                        
                    }

                    if (!actualiza_puntos_clientes($id_cliente)) {
                        $db->rollback(); // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar los puntos del cliente"]);
                        exit;
                    }

                }
            }   

            $db->setQuery("UPDATE facturas SET descuento = $descuento_total WHERE id_factura = $id_factura");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar los Descuentos"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            // Actualizamos la nota de crédito si es que hay
            if ($nota_credito === true) {
                $db->setQuery("UPDATE notas_credito SET id_factura_destino=$id_factura, estado=1 WHERE id_nota_credito=$id_nota_credito");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la Nota De Crédito"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
            }

            // Acumulación de puntos
            // Configuración de acumulación de puntos
            $db->setQuery("SELECT ventas_credito, configuracion, cantidad, puntos FROM plan_puntos WHERE tipo=1");
            $config_puntos = $db->loadObject();
            if ($db->error()) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al consultar la configuración de acumulación de puntos"]);
                exit;
            }

            // Configuración de acumulación de puntos por producto
            $db->setQuery("SELECT ppp.id_producto, ppp.puntos
                            FROM plan_puntos_productos ppp
                            JOIN plan_puntos pp ON ppp.id_plan_punto=pp.id_plan_puntos
                            WHERE tipo=1");
            $config_productos = $db->loadObjectList();
            if ($db->error()) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al consultar la configuración de acumulación de puntos por producto"]);
                exit;
            }

            $ventas_credito = $config_puntos->ventas_credito;
            $config = $config_puntos->configuracion;
            $cantidad = $config_puntos->cantidad;
            $puntos = $config_puntos->puntos;
            $puntos_acumulados = 0;

            // Si la venta es al contado o si es crédito y está habilitado se acumulan puntos, si el cliente no es SIN NOMBRE y si el método de pago es distinto a Puntos
            if (($condicion == 1 || ($condicion == 2 && $ventas_credito == 1)) && $ruc != "44444401-7" && $metodo_pago_puntos === false) {
                switch ($config) {
                    // Monto de compra para todos los productos
                    case "1":
                        $total_venta = 0;
                        foreach ($productos as $key => $p) {
                            $total_venta += $db->clearText(quitaSeparadorMiles($p['total']));
                        }

                        $puntos_acumulados = floor($total_venta / $cantidad) * $puntos;
                    break;

                    // Monto de compra para productos seleccionados
                    case "2":
                        $productos_validos = array_column($config_productos, 'id_producto');
                        $total_venta = 0;
                        foreach ($productos as $key => $p) {
                            $id_producto = $p['id_producto'];
                            if (in_array($id_producto, $productos_validos)) {
                                $total_venta += $db->clearText(quitaSeparadorMiles($p['total']));
                            }
                        }

                        $puntos_acumulados = floor($total_venta / $cantidad) * $puntos;
                    break;

                    // Monto de compra para todos los productos excepto seleccionados
                    case "3":
                        $productos_no_validos = array_column($config_productos, 'id_producto');
                        $total_venta = 0;
                        foreach ($productos as $key => $p) {
                            $id_producto = $p['id_producto'];
                            if (in_array($id_producto, $productos_no_validos) === false) {
                                $total_venta += $db->clearText(quitaSeparadorMiles($p['total']));
                            }
                        }

                        $puntos_acumulados = floor($total_venta / $cantidad) * $puntos;
                    break;

                    // Por cantidad de compras
                    case "4":
                        $db->setQuery("SELECT COUNT(id_factura) AS cantidad FROM facturas WHERE id_cliente=$id_cliente AND estado IN(0, 1)");
                        $cantidad_compras = $db->loadObject()->cantidad;

                        if ($db->error()) {
                            $db->rollback();  // Revertimos los cambios
                            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al consultar la cantidad de compras del cliente"]);
                            exit;
                        }

                        if (($cantidad_compras % $cantidad) == 0) {
                            $puntos_acumulados = $puntos;
                        }
                    break;

                    // Por cantidad de productos comprados
                    case "5":
                        $total_cantidad = 0;
                        foreach ($productos as $key => $p) {
                            $total_cantidad += $db->clearText(quitaSeparadorMiles($p['cantidad']));
                        }

                        $puntos_acumulados = floor($total_cantidad / $cantidad) * $puntos;
                    break;

                    // Especificar puntos a productos seleccionados
                    case "6":
                        $puntos_acumulados = 0;
                        foreach ($productos as $key => $p) {
                            $id_producto = $p['id_producto'];
                            $key = array_search($id_producto, array_column($config_productos, 'id_producto'));
                            if ($key !== false) {
                                $puntos_acumulados += $config_productos[$key]->puntos;
                            }
                        }
                    break;
                }

                // Si acumulo puntos se registra en base de datos
                if ($puntos_acumulados > 0) {
                    $db->setQuery("INSERT INTO clientes_puntos (
                                        id_cliente,
                                        id_factura,
                                        fecha,
                                        puntos,
                                        fecha_actualizacion
                                    ) VALUES (
                                        $id_cliente,
                                        $id_factura,
                                        NOW(),
                                        $puntos_acumulados,
                                        NOW()
                                    )");
                    if (!$db->alter()) {
                        $db->rollback(); // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los puntos acumulados"]);
                        exit;
                    }

                    if (!actualiza_puntos_clientes($id_cliente)) {
                        $db->rollback(); // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar los puntos del cliente"]);
                        exit;
                    }
                }
            }
            // Fin acumulación de puntos

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Factura guardada con éxito.", "id_factura" => $id_factura, "id_cliente" => $id_cliente]);
        break;

        case 'recuperar-numero':
            $db       = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM solicitudes_compras");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

        case 'cargar_clientes':
            $db = DataBase::conectar();
            $ruc = $db->clearText($_POST['ruc_str']);
            $razon_social = $db->clearText($_POST['razon_social_str']);
            $telefono = $db->clearText($_POST['telefono']);
            $celular = $db->clearText($_POST['celular']);
            $email = $db->clearText($_POST['email']);
            $id_tipo = $db->clearText($_POST['id_tipo']);
            $obs = $db->clearText($_POST['obs']);
            $tipo = $db->clearText($_POST['id_tipo']);
            $direcciones = json_decode($_POST['tabla_direcciones'], true);


            if (empty($razon_social)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese nombre y apellido del cliente o Razón Social"]);
                exit;
            }

            $db->setQuery("SELECT * FROM clientes_tipos WHERE id_cliente_tipo = $id_tipo");
            $row = $db->loadObject();

            $db->setQuery("SELECT id_cliente FROM clientes WHERE SUBSTRING_INDEX(ruc, '-', 1) = SUBSTRING_INDEX('$ruc', '-', 1)");
            $row_cliente = $db->loadObject();

            if (empty($row_cliente)) {
                $db->setQuery("INSERT INTO clientes (razon_social, ruc, telefono, celular, email, id_tipo, tipo, obs, usuario, fecha)
                            VALUES ('$razon_social','$ruc','$telefono','$celular','$email',$id_tipo,'$row->tipo','$obs','$usuario',NOW())");
        
                if (!$db->alter()) {
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "El R.U.C. \"$ruc\" ya existe"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    }
                    exit;
                }
                $id_cliente = $db->getLastID();

                foreach ($direcciones as $d) {
                    $latitud = $db->clearText($d["latitud"]);
                    $longitud = $db->clearText($d["longitud"]);
                    $direccion = $db->clearText($d["direccion"]);
                    $referencia = $db->clearText($d["referencia"]);

                    $db->setQuery("INSERT INTO clientes_direcciones (id_cliente, direccion, longitud, latitud, referencia, fecha) VALUES($id_cliente, '$direccion', '$longitud', '$latitud', '$referencia', NOW())");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al guardar la dirreccion. " . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }

            }else{
                $id_cliente_d = $row_cliente->id_cliente;
                
                $db->setQuery("DELETE FROM `clientes_direcciones` WHERE `id_cliente` = $id_cliente_d;");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    exit;
                }

                $db->setQuery("UPDATE clientes SET ruc='$ruc', razon_social='$razon_social', telefono='$telefono', celular='$celular',email='$email',id_tipo='$id_tipo',tipo='$row->tipo', obs='$obs' WHERE ruc = '$ruc'");

                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }

                foreach ($direcciones as $d) {
                    $latitud = $db->clearText($d["latitud"]);
                    $longitud = $db->clearText($d["longitud"]);
                    $direccion = $db->clearText($d["direccion"]);
                    $referencia = $db->clearText($d["referencia"]);

                    $db->setQuery("INSERT INTO clientes_direcciones (id_cliente, direccion, longitud, latitud, referencia, fecha) VALUES($id_cliente_d, '$direccion', '$longitud', '$latitud', '$referencia', NOW())");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al guardar la dirreccion. " . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }
                
            }

            echo json_encode(["status" => "ok", "mensaje" => "Cliente registrado correctamente", "id_cliente" => $id_cliente]);
            
        break;

        case 'stock_por_sucursal':
            $db = DataBase::conectar();

            $where = "";
            $id_producto = $db->clearText($_GET['id_producto']);
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                su.id_sucursal, 
                                su.sucursal, 
                                su.direccion,
                                IFNULL(( 
                                    SELECT SUM(s.stock)
                                    FROM stock s 
                                    JOIN lotes l ON s.id_lote=l.id_lote
                                    WHERE s.id_producto=$id_producto AND s.id_sucursal=su.id_sucursal AND l.vencimiento>=CURRENT_DATE()
                                ), 0) AS stock,
                                IFNULL((
                                    SELECT 
                                    SUM(s.fraccionado)
                                    FROM stock s 
                                    JOIN lotes l ON s.id_lote=l.id_lote
                                    WHERE s.id_producto=$id_producto AND s.id_sucursal=su.id_sucursal AND l.vencimiento>=CURRENT_DATE()
                                ), 0) AS stock_fraccionado 
                            FROM sucursales su 
                            WHERE su.deposito != 1");
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

        case 'ver_detalle':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_GET['id_producto']);
            
            $db->setQuery("SELECT 
                            p.producto, 
                            l.laboratorio,
                            pr.proveedor, 
                            o.origen, 
                            tp.tipo, 
                            IFNULL(p.precio, 0) AS precio, 
                            IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado, 
                            p.fuera_de_plaza 
                            FROM productos p
                            LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
                            LEFT JOIN origenes o ON p.id_origen=o.id_origen
                            LEFT JOIN tipos_productos tp ON p.id_tipo_producto=tp.id_tipo_producto
                            LEFT JOIN productos_proveedores pp ON p.id_producto=pp.id_producto AND pp.proveedor_principal=1
                            LEFT JOIN proveedores pr ON pp.id_proveedor=pr.id_proveedor
                            WHERE p.id_producto=$id_producto");
            $row = $db->loadObject();
            echo json_encode($row);
        break;

        case 'verificar_caja':
            $db = DataBase::conectar();
            $token_caja = $_COOKIE['token'];
            $caja_abierta = false;

            $caja_ = caja_identificacion($db, $token_caja);
            if (empty($caja_)) {
                echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración.", "caja_abierta" => $caja_abierta]);
                exit;
            }

            $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.estado=1 AND cu.estado=0 AND c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
            $id_caja = $db->loadObject()->id_caja;

            if($id_caja != $caja_->id_caja){
                echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración.","caja_abierta" => $caja_abierta]);
                exit;
            }

            $caja = caja_abierta($db, $id_sucursal, $usuario);
            $caja_abierta = (isset($caja)) ? true : false;
            $id_caja = $caja->id_caja;
            if (empty($caja)) {
                echo json_encode(["status" => "error", "mensaje" => "La caja se encuentra cerrada", "caja_abierta" => $caja_abierta]);
                exit;
            }

            $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND id_caja=$id_caja AND estado=1 AND tipo='0'");
            $tim = $db->loadObject();
            $id_timbrado = $tim->id_timbrado;
                
            if (empty($id_timbrado)) {
                echo json_encode(["status" => "error","mensaje" => "No existen timbrados activos para la Factura. Consulte con administración.", "caja_abierta" => $caja_abierta]);
                exit;
            }

            $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND estado=1 AND tipo='0'");
            $tim = $db->loadObject();
            $id_timbrado = $tim->id_timbrado;

            $db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM facturas WHERE id_timbrado= $id_timbrado");
            $r_max = $db->loadObject();    
            $numero_fact = $r_max->ultimo_nro+1;
 
            $db->setQuery("SELECT IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_timbrado = $id_timbrado");
            $hasta = $db->loadObject();    
            $numero_hasta = $hasta->hasta;
 
            if ($numero_fact > $numero_hasta) {
                echo json_encode(["status" => "error", "mensaje" => "Ya no tiene numeros disponible para facturar", "caja_abierta" => $caja_abierta]);
                exit;
            }

            // Alerta de números disponibles
            $db->setQuery("SELECT alerta_nro_timbrado FROM configuracion");
            $tim = $db->loadObject();
            $alerta_nro_timbrado = $tim->alerta_nro_timbrado;

            $disponibles = $numero_hasta - ($numero_fact - 1);
            if ($disponibles <= $alerta_nro_timbrado) {
                echo json_encode(["status" => "error", "mensaje" => "Atención, quedan $disponibles facturas disponibles para impresión. Favor comunicar a Administración", "caja_abierta" => $caja_abierta]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Caja sin errores", "caja_abierta" => $caja_abierta]);
        break;

        case 'ver_principios':
            $db = DataBase::conectar();

            $where = "";
            $id_producto = $db->clearText($_GET['id_producto']);
            
            $db->setQuery("SELECT 
                                pp.id_principio,
                                pa.nombre
                            FROM productos_principios pp
                            LEFT JOIN principios_activos pa ON pp.id_principio=pa.id_principio
                            WHERE pp.id_producto=$id_producto
                            ");
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

        case 'ver_deliverys':
            $db    = DataBase::conectar();
            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit  = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order  = $db->clearText($_REQUEST['order']);
            $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $where = "AND CONCAT_WS(' ', f.funcionario, f.ci, f.celular) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                            id_funcionario,
                            funcionario as razon_social,
                            f.ci,
                            f.celular
                            FROM funcionarios f
                            JOIN puestos p ON f.id_puesto=p.id_puesto
                            JOIN tipos_puestos tp ON p.id_tipo_puesto=tp.id_tipo_puesto
                            WHERE tp.id_tipo_puesto=2 AND f.estado=1 $where
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
            $rows = $db->loadObjectList();

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total     = $total_row->total;

            if ($rows) {
                $salida = array('total' => $total, 'rows' => $rows);
            } else {
                $salida = array('total' => 0, 'rows' => array());
            }

            echo json_encode($salida);
        break;

        case "extraer":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $observacion      = $_POST['observacion'];
        $monto_extraccion = $db->clearText(quitaSeparadorMiles($_POST['monto_extraccion']));

        $token_caja = $_COOKIE['token'];

        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración."]);
            exit;
        }

        $caja            = caja_abierta($db, $id_sucursal, $usuario);
        $id_caja_horario = $caja->id_caja_horario;
        if (!$caja) {
            echo json_encode(["status" => "error", "mensaje" => "No se ha encontrado ninguna caja abierta"]);
            exit;
        }

        $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $id_caja = $db->loadObject()->id_caja;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración.","caja_abierta" => $caja_abierta]);
            exit;
        }

        $db->setQuery("SELECT IFNULL(SUM(c.monto), 0) AS monto FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura WHERE c.estado=1 AND f.id_caja_horario=$id_caja_horario");
        $total_venta = $db->loadObject()->monto;

        $db->setQuery("SELECT IFNULL(SUM(total), 0) AS monto FROM cajas_detalles WHERE tipo=1 AND id_caja_horario=$id_caja_horario");
        $total_apertura = $db->loadObject()->monto;

        $total_caja = $total_venta + $total_apertura;

        $db->setQuery("SELECT IFNULL(SUM(c.monto), 0) AS monto FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura WHERE c.estado=1 AND c.id_metodo_pago=1 AND f.id_caja_horario=$id_caja_horario");
        $total_venta_efectivo = $db->loadObject()->monto;

        if ($total_venta_efectivo < $monto_extraccion) {
            echo json_encode(["status" => "error", "mensaje" => "Monto a extraer es mayor al monto de efectivo en caja."]);
            exit;
        }

        $db->setQuery("SELECT sum(monto_extraccion) as total FROM cajas_extracciones WHERE id_caja_horario=$id_caja_horario");
        $total_extra = $db->loadObject()->total;

        $totales_extra = $total_extra + $monto_extraccion;

        if ($totales_extra >= $total_venta_efectivo) {
            echo json_encode(["status" => "error", "mensaje" => "Los montos extraídos ya superan al total de efectivo en caja."]);
            exit;
        }

        $db->setQuery("SELECT estado FROM cajas_horarios WHERE id_caja_horario=$id_caja_horario");
        $estado = $db->loadObject()->estado;

        if ($estado == 0) {
            echo json_encode(["status" => "error", "mensaje" => "La caja ya se encuentra cerrada. No es posible realizar la extracción."]);
            exit;
        }

        $total_caja_efectivo = $total_venta_efectivo + $total_apertura;

        $monto_sin_extraer = $total_caja_efectivo - $monto_extraccion;

        $db->setQuery("INSERT INTO cajas_extracciones (id_caja_horario, usuario, monto_extraccion, total_venta, total_caja, total_venta_efectivo, total_caja_efectivo, monto_sin_extraer, fecha, observacion) VALUES($id_caja_horario, '$usuario','$monto_extraccion','$total_venta','$total_caja','$total_venta_efectivo','$total_caja_efectivo','$monto_sin_extraer',NOW(),'$observacion')");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Ha ocurrido un error al cargar la extracción."]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $id_extraccion = $db->getLastID();

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "La extracción se ha realizado correctamente.", "id_extraccion" => $id_extraccion]);

        break;

    case "abrir_caja":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $valores_monedas  = $_POST['valor'];
        $cantidad_monedas = $_POST['cantidad'];
        $total_monedas    = $_POST['total'];
        $monto_apertura   = $db->clearText(quitaSeparadorMiles($_POST['total_caja']));

        $token_caja = $_COOKIE['token'];

        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración."]);
            exit;
        }

        $db->setQuery("SELECT id FROM users WHERE username= '$usuario'");
        $id_usuario = $db->loadObject()->id;

        $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.estado=1 AND cu.estado=0 AND c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $id_caja = $db->loadObject()->id_caja;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración."]);
            exit;
        }

        $caja_turno = caja_turno($db, $id_caja);
        if ($caja_turno) {
            echo json_encode(["status" => "error", "mensaje" => "El turno #$caja_turno->id_caja_horario se encuentra abierto. Consulte con Administración"]);
            exit;
        }

        $caja            = caja_abierta($db, $id_sucursal, $usuario);
        $id_caja_horario = $caja->id_caja_horario;
        if ($caja) {
            echo json_encode(["status" => "error", "mensaje" => "Se ha encontrado una caja abierta"]);
            exit;
        }

        $db->setQuery("SELECT estado FROM cajas_usuarios WHERE id_caja = $id_caja AND id_usuario=$id_usuario");
        $estado = $db->loadObject()->estado;

        if ($estado == 1) {
            echo json_encode(["status" => "error", "mensaje" => "No puede abrir una caja porque los montos no coinciden con el total de ventas"]);
            exit;
        }

        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND id_caja=$id_caja AND estado=1 AND tipo='0'");
        $tim = $db->loadObject();
        
        $id_timbrado = $tim->id_timbrado;

        if (empty($id_timbrado)) {
            echo json_encode(["status" => "error", "mensaje" => "No existen timbrados activos para la Factura. Consulte con administración."]);
            //$db->rollback();
            exit;
        }

        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND estado=1 AND tipo='0'");
        $tim         = $db->loadObject();
        $id_timbrado = $tim->id_timbrado;

        $db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM facturas WHERE id_timbrado= $id_timbrado");
        $r_max       = $db->loadObject();
        $numero_fact = $r_max->ultimo_nro + 1;

        $db->setQuery("SELECT IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_timbrado = $id_timbrado");
        $hasta        = $db->loadObject();
        $numero_hasta = $hasta->hasta;

        if ($numero_fact > $numero_hasta) {
            echo json_encode(["status" => "error", "mensaje" => "Ya no tiene numeros disponible para facturar"]);
            exit;
        };

        $db->setQuery("INSERT INTO cajas_horarios (id_caja, fecha_apertura, monto_apertura, usuario, id_sucursal, estado, usuario_apertura) VALUES($id_caja, NOW(),'$monto_apertura','$usuario','$id_sucursal',1, '$usuario')");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la apertura caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $id_caja_horario = $db->getLastID();
        foreach ($valores_monedas as $key => $value) {
            $valor    = quitaSeparadorMiles($db->clearText($value)) ?: 0;
            $cantidad = quitaSeparadorMiles($db->clearText($cantidad_monedas[$key])) ?: 0;
            $total    = quitaSeparadorMiles($db->clearText($total_monedas[$key])) ?: 0;

            $total_sum += $total;

            $query = "INSERT INTO cajas_detalles(id_caja_horario, cantidad, valor, total, tipo) VALUES($id_caja_horario,$cantidad,$valor,$total,1)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el detalle de la caja"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }
        }

        $db->setQuery("SELECT c.efectivo_inicial FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal=$id_sucursal AND cu.id_usuario=$id_usuario");
        $inicial = $db->loadObject()->efectivo_inicial;

        if ($inicial != $total_sum) {
            echo json_encode(["status" => "error", "mensaje" => "Monto de apertura incorrecto. Favor verificar."]);
            exit;
        }

        //Actualizacion la conexion de la caja
        $db->setQuery("UPDATE cajas SET ultima_conexion=NOW() WHERE id_caja = $id_caja ");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la conexion caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "La apertura de caja se realizo correctamente."]);

        break;

    case "cerrar_caja":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $valores_monedas  = $_POST['valor'];
        $cantidad_monedas = $_POST['cantidad'];
        $total_monedas    = $_POST['total'];
        $total            = $db->clearText(quitaSeparadorMiles($_POST['total_caja']));
        $servicios    = $_POST['servicios'];
        $montos_servicios    = $_POST['montos_servicios'];
        $total_servicios    = $db->clearText(quitaSeparadorMiles($_POST['total_servicios']));
        $observacion      = $db->clearText($_POST['observacion']);

        $valores_monedas_sen  = $_POST['valor_sen'];
        $cantidad_monedas_sen = $_POST['cantidad_sen'];
        $total_monedas_sen    = $_POST['total_sen'];
        $total_sen            = $db->clearText(quitaSeparadorMiles($_POST['total_caja_sen']));

        $token_caja = $_COOKIE['token'];

        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración."]);
            exit;
        }

        $caja            = caja_abierta($db, $id_sucursal, $usuario);
        $id_caja_horario = $caja->id_caja_horario;
        if (!$caja) {
            echo json_encode(["status" => "error", "mensaje" => "No se ha encontrado ninguna caja abierta"]);
            exit;
        }

        // Se verifica que las ventas de tipo tarjeta tengan detalles cargados (número de voucher)
        $db->setQuery("SELECT c.id_cobro, c.detalles, c.id_metodo_pago 
                        FROM facturas f
                        JOIN cobros c ON f.id_factura=c.id_factura
                        WHERE f.estado=1 AND c.id_metodo_pago IN (2, 3) AND f.id_caja_horario=$id_caja_horario AND (c.detalles IS NULL OR c.detalles='')");
        $rows = $db->loadObjectList();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al verificar los cobros realizados"]);
            exit;
        }
        
        if (count($rows) > 0) {
            echo json_encode(["status" => "error", "mensaje" => "Número de voucher pendiente de carga en ".count($rows)." venta/s"]);
            exit;
        }

        $db->setQuery("SELECT
                            IFNULL(SUM(c.monto), 0) AS monto
                        FROM cobros c
                        JOIN facturas f ON c.id_factura=f.id_factura
                        WHERE c.estado=1 AND c.id_metodo_pago = 1 AND f.id_caja_horario=$id_caja_horario");
        //!todas las ventas de la caja    
        $total_venta = $db->loadObject()->monto;

        $db->setQuery("SELECT
                            IFNULL(SUM(monto_extraccion), 0) AS monto
                        FROM cajas_extracciones
                        WHERE id_caja_horario=$id_caja_horario");
        //!extracion de caja                
        $extraccion = $db->loadObject()->monto;

        $db->setQuery("SELECT IFNULL(SUM(nc.total),0) AS total
                        FROM cajas_horarios ch 
                        LEFT JOIN notas_credito nc ON nc.id_caja_horario=ch.id_caja_horario AND nc.devolucion_importe = 1 
                        WHERE ch.id_caja_horario=$id_caja_horario 
                        GROUP BY nc.id_caja_horario;");
        //!nota de credito    
        $total_devolucion = $db->loadObject()->total;

        // $diferencia = (($total_venta - $extraccion) - $total) - $total_devolucion;
        $diferencia = (($total_venta - $total_devolucion) - $extraccion) - $total;

        $db->setQuery("SELECT id FROM users WHERE username= '$usuario'");
        $id_usuario = $db->loadObject()->id;

        $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $id_caja = $db->loadObject()->id_caja;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración."]);
            exit;
        }

        if ($diferencia < 0) {
            $diferencia_pos = $diferencia * -1;
        } else {
            $diferencia_pos = $diferencia;
        }

        if ($diferencia_pos >= 10000) {
            $db->setQuery("UPDATE cajas_usuarios SET estado=1 WHERE id_caja=$id_caja AND id_usuario=$id_usuario");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }
        }

        foreach ($valores_monedas as $key => $value) {
            $valor    = quitaSeparadorMiles($db->clearText($value)) ?: 0;
            $cantidad = quitaSeparadorMiles($db->clearText($cantidad_monedas[$key])) ?: 0;
            $total    = quitaSeparadorMiles($db->clearText($total_monedas[$key])) ?: 0;

            $query = "INSERT INTO cajas_detalles(id_caja_horario, cantidad, valor, total, tipo) VALUES($id_caja_horario,$cantidad,$valor,$total,0)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el detalle de la caja"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

        }

        // Se registran los servicios
        foreach ($servicios as $key => $value) {
            $id_servicio = quitaSeparadorMiles($db->clearText($value));
            $monto = quitaSeparadorMiles($db->clearText($montos_servicios[$key])) ?: 0;

            $query = "INSERT INTO cajas_horarios_servicios(id_caja_horario, id_servicio, monto) VALUES ($id_caja_horario, $id_servicio, $monto)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los servicios"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }
        }

        // Se registran los montos del sencillo
        foreach ($valores_monedas_sen as $key => $value) {
            $valor    = quitaSeparadorMiles($db->clearText($value)) ?: 0;
            $cantidad = quitaSeparadorMiles($db->clearText($cantidad_monedas_sen[$key])) ?: 0;
            $total    = quitaSeparadorMiles($db->clearText($total_monedas_sen[$key])) ?: 0;

            $total_sencillo += $total;

            $query = "INSERT INTO cajas_detalles(id_caja_horario, cantidad, valor, total, tipo) VALUES($id_caja_horario,$cantidad,$valor,$total,2)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el detalle de la caja"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

        }

        $db->setQuery("SELECT c.efectivo_inicial FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $efectivo_inicial = $db->loadObject()->efectivo_inicial;

        $diferencia_sencillo = $efectivo_inicial - $total_sencillo;

        if($efectivo_inicial != $total_sencillo){
            $db->setQuery("UPDATE cajas_usuarios SET estado=1 WHERE id_caja=$id_caja AND id_usuario=$id_usuario");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }
        }

        $query = "UPDATE cajas_horarios SET fecha_cierre=NOW(), total_venta='$total_venta', monto_cierre='$total', diferencia='$diferencia_pos', monto_servicios=$total_servicios, monto_sencillo_cierre=$total_sencillo, diferencia_sencillo=$diferencia_sencillo, devolucion_importe=$total_devolucion, observacion='$observacion', estado=0, usuario_cierre='$usuario' WHERE id_caja_horario=$id_caja_horario";
        $db->setQuery($query);
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el cierre de caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        // Actualizacion la conexion de la caja
        $db->setQuery("UPDATE cajas SET ultima_conexion=NOW() WHERE id_caja = $id_caja ");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la conexion caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Cierre de caja realizado correctamente"]);

        break;

        case 'ver_lotes':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_GET['id_producto']);

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT l.id_lote, l.lote, l.vencimiento, s.stock, s.fraccionado, l.costo, p.cantidad_fracciones as cant
                            FROM lotes l
                            JOIN stock s ON l.id_lote=s.id_lote
                            LEFT JOIN productos p ON p.id_producto = s.id_producto
                            WHERE s.id_producto=$id_producto AND s.id_sucursal=$id_sucursal AND l.vencimiento>=CURRENT_DATE() AND (s.stock > 0 OR s.fraccionado > 0)
                            AND l.lote LIKE '%$term%'
                            ORDER BY l.vencimiento
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'buscar_nota_credito':
            $db = DataBase::conectar();
            $numero = $db->clearText($_POST['numero']);

            $db->setQuery("SELECT nc.id_nota_credito, nc.ruc, nc.razon_social, nc.total, CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, nc.numero) AS numero
                            FROM notas_credito nc
                            JOIN timbrados t ON nc.id_timbrado=t.id_timbrado
                            WHERE nc.estado=0
                            AND CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, nc.numero)='$numero'
                            ORDER BY nc.id_nota_credito DESC");
            $row = $db->loadObject();
            echo json_encode($row);
        break;

        case 'ver_total_puntos_venta':
            $db = Database::conectar();
            $productos = json_decode($_POST['productos']);

            try {
                $puntos = total_puntos_venta($db, $productos, 1);
                echo json_encode(["status" => "ok", "mensaje" => "", "puntos" => $puntos]);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage(), "puntos" => 0]);
                exit;
            }

        break;

        case 'ver_descuentos':
            $db = Database::conectar();

            $productos = json_decode($_POST['productos']);
            $metodos_pagos = json_decode($_POST['metodos_pagos']);

            if (empty($productos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún producto seleccionado"]);
                exit;
            }
            if (empty($metodos_pagos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún metodo de pago seleccionado"]);
                exit;
            }

            // Se organiza la información de métodos de pago
            $array_where_metodos_pagos = []; // Guarda las condiciones sql
            $array_id_metodos_pagos = []; // Guarda el ID de cada método de pago
            foreach ($metodos_pagos as $key => $value) {
                $id_metodo_pago = $db->clearText($value->id_metodo_pago);
                $id_entidad = $db->clearText($value->id_entidad);

                if (isset($id_entidad) && !empty($id_entidad)) {
                    $array_where_metodos_pagos[] = "(id_metodo_pago=$id_metodo_pago AND id_entidad=$id_entidad)";
                } else {
                    $array_where_metodos_pagos[] = "id_metodo_pago=$id_metodo_pago";
                }
                $array_id_metodos_pagos[] = $id_metodo_pago;
            }

            // Se obtienen los descuentos por campaña activos que coinciden con la fecha actual
            $db->setQuery("SELECT 
                dp.id_descuento_pago
                FROM descuentos_pagos dp
                WHERE dp.estado=1
                AND (TIME(NOW()) BETWEEN dp.hora_inicio AND dp.hora_fin)
                AND (
                    (
                        (CURRENT_DATE() BETWEEN dp.fecha_inicio AND dp.fecha_fin)
                        AND 
                        dp.tipo = 1
                    )
                    OR 
                    DAYOFWEEK(NOW()) IN (
                        SELECT dia FROM descuentos_pagos_dias WHERE id_descuento_pago=dp.id_descuento_pago
                    )
                )"
            );
            $rows = $db->loadObjectList();
            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            // Se concatena las condiciones sql con OR
            $where_metodos_pagos = implode(' OR ', $array_where_metodos_pagos);
            // Se concatena con una coma el ID de los métodos de pago
            $in_id_metodo_pago = implode(',', $array_id_metodos_pagos);
            // Se concatena con una coma el ID de los descuentos por campaña
            $in_id_descuento_pago = implode(',', array_column($rows, 'id_descuento_pago'));

            if (empty($in_id_descuento_pago)) {
                $in_id_descuento_pago = 0;
            }

            $descuentos = [];
            foreach ($productos as $key => $value) {
                /**
                 * Las consultas a la base de datos se realizan mayor a menor prioridad de configuración de descuento
                 *
                 * Prioridad de descuentos
                 * 1 - Configuración por Productos de descuentos por proveedor
                 * 2 - Configuración por Remates de descuentos por campaña
                 * 3 - Configuración por Productos de descuentos por campaña
                 * 4 - Configuración por filtros de descuentos por campaña
                 * 5 - Configuración por filtros de descuentos por proveedor
                 */
                
                $id_producto = $value->id_producto;
                $id_lote = $value->id_lote;
                 
                // 1 - Configuración por Productos de descuentos por proveedor
                $db->setQuery("SELECT 
                                    dpp.id_producto, 
                                    dpp.porcentaje, 
                                    0 AS remate,
                                    'DPP' AS tipo
                                FROM descuentos_proveedores_productos dpp
                                JOIN proveedores p ON dpp.id_proveedor=p.id_proveedor
                                JOIN productos_proveedores pp ON p.id_proveedor=pp.id_proveedor AND pp.proveedor_principal=1
                                WHERE id_metodo_pago IN ($in_id_metodo_pago) AND dpp.id_producto = $id_producto AND dpp.id_sucursal = $id_sucursal
                                GROUP BY dpp.id_producto
                                ORDER BY id_producto");
                $row = $db->loadObject();
                if ($db->error()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    exit;
                }

                if (!empty($row)) {
                    $descuentos[] = [
                        "id_producto" => $id_producto,
                        "id_lote" => $id_lote,
                        "porcentaje" => $row->porcentaje,
                        "remate" => $row->remate,
                        "tipo" => $row->tipo
                    ];
                    continue;
                }

                // 2 - Configuración por Remates de descuentos por campaña
                $db->setQuery("SELECT 
                                    id_producto, 
                                    id_lote, 
                                    porcentaje, 
                                    1 AS remate,
                                    'DCR' AS tipo
                                FROM descuentos_pagos_remates
                                WHERE id_descuento_pago IN ($in_id_descuento_pago) AND id_producto = $id_producto AND id_lote = $id_lote
                                GROUP BY id_producto
                                ORDER BY id_producto");
                $row = $db->loadObject();
                if ($db->error()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    exit;
                }

                if (!empty($row)) {
                    $descuentos[] = [
                        "id_producto" => $id_producto,
                        "id_lote" => $id_lote,
                        "porcentaje" => $row->porcentaje,
                        "remate" => $row->remate,
                        "tipo" => $row->tipo
                    ];
                    continue;
                }

                // 3 - Configuración por Productos de descuentos por campaña
                $db->setQuery("SELECT 
                                id_producto, 
                                porcentaje, 
                                0 AS remate,
                                'DCP' AS tipo
                            FROM descuentos_pagos_productos
                            WHERE id_descuento_pago IN ($in_id_descuento_pago) AND id_producto = $id_producto
                            GROUP BY id_producto
                            ORDER BY id_producto");
                $row = $db->loadObject();
                    if ($db->error()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    exit;
                }

                if (!empty($row)) {
                    $descuentos[] = [
                        "id_producto" => $id_producto,
                        "id_lote" => $id_lote,
                        "porcentaje" => $row->porcentaje,
                        "remate" => $row->remate,
                        "tipo" => $row->tipo
                    ];
                    continue;
                }

                // 4 - Configuración por filtros de descuentos por campaña
                $db->setQuery("SELECT 
                                    p.id_producto, 
                                    MIN(dpf.porcentaje) AS porcentaje, 
                                    0 AS remate,
                                    'DCF' AS tipo
                                FROM productos p, descuentos_pagos_filtros dpf
                                WHERE dpf.id_descuento_pago IN ($in_id_descuento_pago)
                                AND p.id_producto = $id_producto
                                AND (($where_metodos_pagos) OR dpf.id_metodo_pago IS NULL)
                                AND (dpf.id_origen=p.id_origen OR dpf.id_origen IS NULL)
                                AND (dpf.id_tipo_producto=p.id_tipo_producto OR dpf.id_tipo_producto IS NULL)
                                AND (dpf.id_laboratorio=p.id_laboratorio OR dpf.id_laboratorio IS NULL)
                                AND (dpf.id_marca=p.id_marca OR dpf.id_marca IS NULL)
                                AND (dpf.id_rubro=p.id_rubro OR dpf.id_rubro IS NULL)
                                AND (dpf.controlado=p.controlado OR dpf.controlado IS NULL)
                                GROUP BY p.id_producto
                                ORDER BY p.id_producto");
                $row = $db->loadObject();
                    if ($db->error()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    exit;
                }

                if (!empty($row)) {
                    $descuentos[] = [
                        "id_producto" => $id_producto,
                        "id_lote" => $id_lote,
                        "porcentaje" => $row->porcentaje,
                        "remate" => $row->remate,
                        "tipo" => $row->tipo
                    ];
                    continue;
                }

                
                // 5 - Configuración por filtros de descuentos por proveedor
                $db->setQuery("SELECT 
                                    p.id_producto, 
                                    MIN(dp.porcentaje) AS porcentaje, 
                                    0 AS remate,
                                    'DP' AS tipo
                                FROM productos p
                                JOIN productos_proveedores pp ON p.id_producto=pp.id_producto
                                JOIN descuentos_proveedores dp ON pp.id_proveedor=dp.id_proveedor AND pp.proveedor_principal=1
                                WHERE (dp.id_metodo_pago IN ($in_id_metodo_pago) OR dp.id_metodo_pago IS NULL)
                                AND p.id_producto = $id_producto
                                AND dp.id_sucursal = $id_sucursal
                                AND (dp.id_origen=p.id_origen OR dp.id_origen IS NULL)
                                AND (dp.id_tipo_producto=p.id_tipo_producto OR dp.id_tipo_producto IS NULL)
                                AND (dp.id_laboratorio=p.id_laboratorio OR dp.id_laboratorio IS NULL)
                                AND (dp.id_marca=p.id_marca OR dp.id_marca IS NULL)
                                AND (dp.id_rubro=p.id_rubro OR dp.id_rubro IS NULL)
                                AND (dp.controlado=p.controlado OR dp.controlado IS NULL)
                                GROUP BY p.id_producto
                                ORDER BY p.id_producto");
                $row = $db->loadObject();
                if ($db->error()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    exit;
                }

                if (!empty($row)) {
                    $descuentos[] = [
                        "id_producto" => $id_producto,
                        "id_lote" => $id_lote,
                        "porcentaje" => $row->porcentaje,
                        "remate" => $row->remate,
                        "tipo" => $row->tipo
                    ];
                    continue;
                }

                // Si no tiene ningún descuento
                $descuentos[] = [
                    "id_producto" => $id_producto,
                    "id_lote" => $id_lote,
                    "porcentaje" => 0,
                    "remate" => 0,
                    "tipo" => ""
                ];

            }

            echo json_encode(["status" => "ok", "mensaje" => "", "data" => ["descuentos" => $descuentos]]);
        break;

        case 'ver_productos_clientes':
            $db    = DataBase::conectar();

            $id_cliente = $db->clearText($_GET['id_cliente']);
            $desde = $db->clearText($_GET['desde']) . " 00:00:00";
            $hasta = $db->clearText($_GET['hasta']) . " 23:59:59";
            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit  = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order  = $db->clearText($_REQUEST['order']);
            $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', fp.producto, p.codigo) LIKE '%$search%'";
            }

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS 
                                f.id_cliente,
                                f.id_factura,
                                DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha,
                                CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                                p.id_producto, 
                                p.producto, 
                                p.codigo, 
                                p.cantidad_fracciones, 
                                p.observaciones, 
                                pre.id_presentacion, 
                                pre.presentacion,
                                pa.id_principio,
                                p.comision,
                                p.descuento_fraccionado,
                                p.controlado,
                                p.fraccion,
                                pa.nombre AS principios_activos,
                                CASE p.iva WHEN 1 THEN 'EXENTAS' WHEN 2 THEN '5%'  WHEN 3 THEN '10%'  END AS iva_str,
                                IFNULL(p.precio, 0) AS precio, 
                                IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado,
                                SUM(fp.cantidad) AS cantidad,
                                SUM(fp.descuento) AS descuento,
                                fp.fraccionado,
                                SUM(fp.total_venta) AS total_venta,
                                CASE f.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion,
                                fu.funcionario AS vendedor

                            FROM facturas_productos fp 
                            LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                            LEFT JOIN timbrados t ON f.id_timbrado = t.id_timbrado
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN users u ON f.usuario = u.username
                            LEFT JOIN funcionarios fu ON u.id=fu.id_usuario
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN productos_principios ppa ON p.id_producto=ppa.id_producto
                            LEFT JOIN principios_activos pa ON ppa.id_principio=pa.id_principio
                            WHERE f.id_cliente = $id_cliente AND f.fecha_venta BETWEEN '$desde' AND '$hasta'
                            GROUP BY f.id_factura, p.id_producto
                            $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
             $rows = $db->loadObjectList();

             $db->setQuery("SELECT FOUND_ROWS() as total");     
             $total_row = $db->loadObject();
             $total     = $total_row->total;
 
             if ($rows) {
                 $salida = array('total' => $total, 'rows' => $rows);
             } else {
                 $salida = array('total' => 0, 'rows' => array());
             }
 
             echo json_encode($salida);
        break;

        case 'obtener-direcciones':
            $db = DataBase::conectar();
            $id_cliente = $db->clearText($_REQUEST['id_cliente']);
    
            $db->setQuery("SELECT
                                id_cliente_direccion,
                                id_cliente,
                                direccion,
                                longitud,
                                latitud,
                                CONCAT(latitud,',',longitud) as coordenadas,
                                referencia
                            FROM clientes_direcciones
                            WHERE id_cliente = $id_cliente
                            ORDER BY id_cliente_direccion DESC
                           ");
    
            $rows = ($db->loadObjectList()) ?: [];
    
            echo json_encode($rows);
        break;	

	}

    /**
     * Retorna la cantidad de puntos necesarios para canjear por los productos
     * @param DataBase $db Instancia de la Base de Datos.
     * @param array $productos
     * @param int $condicion 1: Contado; 2: Crédito
     * @throws Exception En caso de que ocurra un error de base de datos o algún producto no pueda ser canjeado
     */
    function total_puntos_venta($db, $productos, $condicion) {
        $db->setQuery("SELECT id_plan_puntos, ventas_credito, configuracion, cantidad, puntos FROM plan_puntos WHERE tipo = 2");
        $row = $db->loadObject();
        if ($db->error()) {
            throw new Exception("Error de base de datos");
        }

        $id_plan_punto = $row->id_plan_puntos;
        $ventas_credito = $row->ventas_credito;
        $configuracion = $row->configuracion;
        $cantidad = $row->cantidad;
        $puntos = $row->puntos;

        $db->setQuery("SELECT id_producto, puntos FROM plan_puntos_productos WHERE id_plan_punto=$id_plan_punto");
        $productos_sel = $db->loadObjectList();
        if ($db->error()) {
            throw new Exception("Error de base de datos");
        }

        if ($ventas_credito == 0 && $condicion == 2) {
            throw new Exception("El canje de puntos no esta disponible para las ventas a crédito");
        }

        switch ($configuracion) {
            // Monto de compra para todos los productos
            case 1:
                $total = array_sum(array_column($productos, "total"));
                $puntos = (ceiling($total, $cantidad) / $cantidad) * $puntos;
                return $puntos;
            break;

            // Monto de compra para productos seleccionados
            case 2:
                $array_id_producto = array_column($productos_sel, "id_producto");

                $productos_filtrados = [];
                foreach($productos as $value) {
                    if (in_array($value->id_producto, $array_id_producto)) {
                        $productos_filtrados[] = $value;
                    }
                }

                $cantidad_productos = count($productos);
                $cantidad_productos_filtrados = count($productos_filtrados);

                if ($cantidad_productos_filtrados < $cantidad_productos) {
                    $cantidad = $cantidad_productos - $cantidad_productos_filtrados;
                    throw new Exception("$cantidad producto/s no disponible/s para canje de puntos");
                }

                $total = array_sum(array_column($productos_filtrados, "total"));
                $puntos = (ceiling($total, $cantidad) / $cantidad) * $puntos;
                return $puntos;
            break;

            // Monto de compra para todos los productos excepto seleccionados
            case 3:
                $array_id_producto = array_column($productos_sel, "id_producto");

                $productos_filtrados = [];
                foreach($productos as $value) {
                    if (in_array($value->id_producto, $array_id_producto) === false) {
                        $productos_filtrados[] = $value;
                    }
                }

                $cantidad_productos = count($productos);
                $cantidad_productos_filtrados = count($productos_filtrados);

                if ($cantidad_productos_filtrados < $cantidad_productos) {
                    $cantidad = $cantidad_productos - $cantidad_productos_filtrados;
                    throw new Exception("$cantidad producto/s no disponible/s para canje de puntos");
                }

                $total = array_sum(array_column($productos_filtrados, "total"));
                $puntos = (ceiling($total, $cantidad) / $cantidad) * $puntos;
                return $puntos;
            break;

            // Por cantidad de compras
            case 4:
                throw new Exception("Configuración de canje de puntos no disponible");
            break;

            // Por cantidad de productos comprados
            case 5:
                $total = array_sum(array_column($productos, "cantidad"));
                $cantidad_necesaria = ceiling($total, $cantidad);

                if ($total < $cantidad_necesaria) {
                    $faltantes = $cantidad_necesaria - $total;
                    throw new Exception("$faltantes producto/s (cada $cantidad productos) requeridos para realizar el canje de puntos");
                }

                $puntos = ($total / $cantidad) * $puntos;
                return $puntos;
            break;

            // Especificar puntos a productos seleccionados
            case 6:
                $array_id_producto = array_column($productos_sel, "id_producto");

                $productos_filtrados = [];
                $puntos = 0;
                foreach($productos as $value) {
                    $value = (array) $value;
                    $key = array_search($value["id_producto"], $array_id_producto);
                    if ($key !== false) {
                        $productos_filtrados[] = $value;
                        $puntos += $productos_sel[$key]->puntos * $value["cantidad"];
                    }
                }

                $cantidad_productos = count($productos);
                $cantidad_productos_filtrados = count($productos_filtrados);

                if ($cantidad_productos_filtrados < $cantidad_productos) {
                    $cantidad = $cantidad_productos - $cantidad_productos_filtrados;
                    throw new Exception("$cantidad producto/s no disponible/s para canje de puntos");
                }

                return $puntos;
            break;

            default: throw new Exception("Canje de puntos no configurado");
        }
    }


?>
