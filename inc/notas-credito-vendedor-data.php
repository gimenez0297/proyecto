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
        
        case "cargar":
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_POST["id_factura"]);
            $detalle = json_decode($_POST["detalle"]);
            $devolucion_importe = ($db->clearText($_POST['devolucion_importe'])) ?: 0;
            $estado = 0;

            if (empty($id)) {
                echo json_encode(["status" => "error", "mensaje" => "Ninguna factura seleccionada. Favor verifique."]);
                exit;
            }
            if (empty($detalle)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún producto seleccionado. Favor verifique"]);
                exit;
            }

            // DATOS DEL TIMBRADO
            $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND estado=1 AND tipo=1");
            $tim = $db->loadObject();
            $id_timbrado = $tim->id_timbrado;
            
            if (empty($id_timbrado)) {
                echo json_encode(["status" => "error","mensaje" => "Error. No existen timbrados activos para la Nota De Crédito. Favor consulte con su superior."]);
                exit;
            }

            $caja = caja_abierta($db, $id_sucursal, $usuario);
            $caja_abierta = (isset($caja)) ? true : false;
            $id_caja_horario = $caja->id_caja_horario;
            if (empty($caja)) {
                echo json_encode(["status" => "error", "mensaje" => "La caja se encuentra cerrada", "caja_abierta" => $caja_abierta]);
                exit;
            }
    
            // OBTENEMOS EL MAYOR NUMERO DE NOTA DE CRÉDITO
            $db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM notas_credito WHERE id_timbrado=$id_timbrado");
            $r_max = $db->loadObject();    
            $numero = $r_max->ultimo_nro+1;
            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número de la Nota De Crédito"]);
                exit;
            }

            $db->setQuery("SELECT IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_timbrado=$id_timbrado");
            $hasta = $db->loadObject();    
            $numero_hasta = $hasta->hasta;
            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número de la Nota de Crédito"]);
                exit;
            }

            if ($numero > $numero_hasta) {
                echo json_encode(["status" => "error", "mensaje" => "Ya no tiene números disponible para la Nota De Crédito"]);
                exit;
            }

            // Datos de la cabecera
            $db->setQuery("SELECT id_cliente, ruc, razon_social FROM facturas WHERE id_factura=$id");
            $row = $db->loadObject();
            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos de la factura"]);
                exit;
            }
            $id_cliente = $row->id_cliente;
            $ruc = $row->ruc;
            $razon_social = $row->razon_social;

            //Comprobando si el id_cliente corresponde a SIN NOMBRE
            if ($ruc == '44444401-7') {
                echo json_encode(["status" => "error", "mensaje" => "No puede generar una nota de crédito a las facturas SIN NOMBRE"]);
                exit;
            }

            $total = 0;
            $cantidad = 0;
            foreach ($detalle as $key => $value) {
                $cantidad_venta = $db->clearText(quitaSeparadorMiles($value->cantidad));
                $devolucion = $db->clearText(quitaSeparadorMiles($value->devolucion));
                $total_venta = $db->clearText(quitaSeparadorMiles($value->total_venta));
                $total_devolucion = round(($total_venta / $cantidad_venta * $devolucion), 0);
                $cantidad += $devolucion;
                $total += $total_devolucion;
            }

            if ($devolucion_importe == 1){
                $estado = 1;
            }

            $db->setQuery("INSERT INTO notas_credito (
                            id_factura_origen,
                            id_timbrado,
                            id_sucursal,
                            numero,
                            cantidad,
                            total,
                            id_cliente,
                            ruc,
                            razon_social,
                            usuario,
                            estado,
                            fecha,
                            devolucion_importe,
                            id_caja_horario
                        ) VALUES (
                            $id,
                            $id_timbrado,
                            $id_sucursal,
                            $numero,
                            $cantidad,
                            $total,
                            $id_cliente,
                            '$ruc',
                            '$razon_social',
                            '$usuario',
                            $estado,
                            NOW(),
                            $devolucion_importe,
                            $id_caja_horario
                        )");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la Nota De Crédito"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            $id_cabecera = $db->getLastID();

            foreach ($detalle as $key => $value) {
                $id_producto = $db->clearText($value->id_producto);
                $id_lote = $db->clearText($value->id_lote);
                $fraccionado = $db->clearText($value->fraccionado);
                $lote = $db->clearText($value->lote);
                $producto = $db->clearText($value->producto);
                $cantidad = $db->clearText(quitaSeparadorMiles($value->cantidad));
                $devolucion = $db->clearText(quitaSeparadorMiles($value->devolucion));
                $total_venta = $db->clearText(quitaSeparadorMiles($value->total_venta));

                // $db->setQuery("SELECT fp.producto, fp.fraccionado, fp.cantidad, fp.id_lote, fp.lote, l.vencimiento
                //                 FROM facturas_productos fp
                //                 JOIN lotes l ON fp.id_lote=l.id_lote
                //                 WHERE fp.id_producto=$id_producto AND fp.id_factura=$id
                //                 ORDER BY l.vencimiento");
                // $rows = $db->loadObjectList();
                // if ($db->error()) {
                //     echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos de los productos"]);
                //     $db->rollback(); // Revertimos los cambios
                //     exit;
                // }

                // $cantidad_sumar = $devolucion;
                // foreach ($rows as $key => $v) {
                //     $producto = $v->producto;
                //     $fraccionado = $v->fraccionado;
                //     $cantidad_lote = $v->cantidad;
                //     $id_lote = $v->id_lote;
                //     $lote = $v->lote;

                //     // Se calcula cuanto sumar a cada lote
                //     if ($cantidad_sumar <= $cantidad_lote) {
                //         $cantidad_devolucion = $cantidad_sumar;
                //         $cantidad_sumar = 0;
                //     } else {
                //         $cantidad_devolucion = $cantidad_lote;
                //         $cantidad_sumar -= $cantidad_lote;
                //     }

                    if ($fraccionado == 0) { // Si no es fraccionado
                        $sumar_stock = $devolucion;
                        $sumar_fraccionado = 0;
                    } else { // Si es fraccionado
                        $sumar_stock = 0;
                        $sumar_fraccionado = $devolucion;
                    }

                    $total_devolucion = round(($total_venta / $cantidad * $devolucion), 0);

                    $db->setQuery("INSERT INTO notas_credito_productos (
                                        id_nota_credito,
                                        id_producto,
                                        producto,
                                        id_lote,
                                        lote,
                                        fraccionado,
                                        cantidad,
                                        total_venta
                                    ) VALUES (
                                        $id_cabecera,
                                        $id_producto,
                                        '$producto',
                                        $id_lote,
                                        '$lote',
                                        $fraccionado,
                                        $devolucion,
                                        $total_devolucion
                                    )");

                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos"]);
                        $db->rollback(); // Revertimos los cambios
                        exit;
                    }

                    $id_detalle = $db->getLastID();

                    try {
                        producto_sumar_stock($db, $id_producto, $id_sucursal, $id_lote, $sumar_stock, $sumar_fraccionado);
                    } catch (Exception $e) {
                        $db->rollback();  // Revertimos los cambios
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                        $db->rollback(); // Revertimos los cambios
                        exit;
                    }

                    $stock = new stdClass();
                    $stock->id_producto = $id_producto;
                    $stock->id_sucursal = $id_sucursal;
                    $stock->id_lote = $id_lote;

                    $historial = new stdClass();
                    $historial->cantidad = $sumar_stock;
                    $historial->fraccionado = $sumar_fraccionado;
                    $historial->operacion = ADD;
                    $historial->id_origen = $id_detalle;
                    $historial->origen = CRED;
                    $historial->detalles = "Nota De Crédito N° ".zerofill($numero);
                    $historial->usuario = $usuario;

                    if (!stock_historial($db, $stock, $historial)) {
                        echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                        $db->rollback(); // Revertimos los cambios
                        exit;
                    }

                    // Si ya no hay cantidades que registrar se finaliza
                    //if ($cantidad_sumar == 0) break;
                // }

                // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);

            }

            $db->commit(); // Guardamos los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Nota De Crédito registrada correctamente", "id" => $id_cabecera]);

        break;

        case "ver_facturas":
            $db = DataBase::conectar();
            $where = "";

            // Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', numero, ruc, razon_social) LIKE '%$search%'";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            f.id_factura,
                            CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, f.numero) AS numero,
                            DATE_FORMAT(f.fecha_venta, '%d/%m/%Y %H:%i:%s') AS fecha_venta,
                            f.fecha_venta as fecha_ac,
                             CASE
                                WHEN f.fecha_venta >= NOW() - INTERVAL (SELECT periodo_devolucion FROM configuracion) DAY THEN 1
                                ELSE 2
                            END AS periodo,
                            m.metodos,
                            m.id_metodo_pago,
                            f.ruc,
                            f.razon_social,
                            f.id_cliente,
                            f.total_venta,
                            s.id_sucursal,
                            s.sucursal
                            FROM facturas f
                            JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                            JOIN timbrados t ON f.id_timbrado=t.id_timbrado
                            JOIN (SELECT 
                                    GROUP_CONCAT(' ',mp.metodo_pago) AS metodos,
                                    c.id_metodo_pago,
                                    c.id_factura
                                FROM cobros c
                                LEFT JOIN metodos_pagos mp ON c.id_metodo_pago=mp.id_metodo_pago
                                GROUP BY id_factura) m ON f.id_factura=m.id_factura
                            WHERE f.estado IN (0,1) AND f.id_sucursal=$id_sucursal
                            $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
            $rows = $db->loadObjectList();

            $db->setQuery("SELECT FOUND_ROWS() as total");      
            $total_row = $db->loadObject();
            $total = $total_row->total;

            if ($rows) {
                $salida = array("total" => $total, "rows" => $rows);
            } else {
                $salida = array("total" => 0, "rows" => array());
            }

            echo json_encode($salida);
        break;

        case "ver_detalle":
            $db = DataBase::conectar();
            $id = $db->clearText($_REQUEST["id"]);

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            fp.id_factura_producto,
                            p.codigo,
                            fp.id_producto,
                            fp.producto,
                            fp.lote,
                            fp.id_lote,
                            f.ruc,
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote,
                            pre.presentacion,
                            SUM(fp.total_venta - (fp.total_venta * IFNULL((
                                SELECT detalles
                                FROM cobros
                                WHERE id_factura=fp.id_factura AND estado=1 AND id_descuento_metodo_pago IS NOT NULL
                            ), 0) / 100)) AS total_venta,
                            SUM(fp.cantidad) AS cantidad,
                            SUM(fp.cantidad)-IFNULL((SELECT 
                                    SUM(ncp.cantidad)
                                    FROM notas_credito_productos ncp
                                    LEFT JOIN notas_credito nc ON ncp.id_nota_credito=nc.id_nota_credito
                                    WHERE nc.estado!=2 AND  ncp.id_producto = fp.`id_producto` AND nc.id_factura_origen=fp.`id_factura`  AND ncp.`fraccionado`=fp.`fraccionado` AND ncp.id_lote = fp.id_lote
                                    GROUP BY ncp.id_lote, nc.id_factura_origen ),0) AS devolucion,
                            IFNULL((SELECT 
                                    SUM(ncp.cantidad)
                                    FROM notas_credito_productos ncp
                                    LEFT JOIN notas_credito nc ON ncp.id_nota_credito=nc.id_nota_credito
                                    WHERE nc.estado!=2 AND  ncp.id_producto = fp.`id_producto` AND nc.id_factura_origen=fp.`id_factura`  AND ncp.`fraccionado`=fp.`fraccionado` AND ncp.id_lote = fp.id_lote
                                GROUP BY ncp.id_lote, nc.id_factura_origen),0) AS devuelto,
                                fp.fraccionado
                            FROM facturas_productos fp
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN lotes l ON l.id_lote = fp.id_lote
                            LEFT JOIN facturas f ON fp.id_factura = f.id_factura
                            WHERE fp.id_factura=$id
                            GROUP BY fp.id_lote, fp.fraccionado
                            ORDER BY codigo");
            $rows = $db->loadObjectList() ?: [];

            echo json_encode($rows);
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
            $id_caja_horario = $caja->id_caja_horario;
            if (empty($caja)) {
                echo json_encode(["status" => "error", "mensaje" => "La caja se encuentra cerrada", "caja_abierta" => $caja_abierta]);
                exit;
            }

            $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND id_caja=$id_caja AND estado=1 AND tipo='1'");
            $tim = $db->loadObject();
            $id_timbrado = $tim->id_timbrado;
                
            if (empty($id_timbrado)) {
                echo json_encode(["status" => "error","mensaje" => "No existen timbrados activos para la Nota De Crédito. Favor consulte con su superior.", "caja_abierta" => $caja_abierta]);
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

            echo json_encode(["status" => "ok", "mensaje" => "Caja sin errores", "caja_abierta" => $caja_abierta]);
        break;

        
	}

?>
