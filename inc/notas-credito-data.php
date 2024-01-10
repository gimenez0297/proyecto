<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST["q"];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;
    $datosUsuario = datosUsuario($usuario);
    $id_rol = $datosUsuario->id_rol;

    switch ($q) {

        case "ver":
            $db = DataBase::conectar();
            $sucursal = $db->clearText($_REQUEST['id_sucursal']);
            $desde = $db->clearText($_REQUEST['desde']) . " 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta']) . " 23:59:59";
            $where = "";

            // Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', sucursal, ruc, razon_social, total, estado_str) LIKE '%$search%'";
            }

            // Si es admin puede filtrar por sucursal
            // if ($id_rol != 1) {
            //     $sucursal = $id_sucursal;
            // }

            if (esAdmin($id_rol) === false) {
                $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
            } else if(!empty($sucursal) && intVal($sucursal) != 0) {
                $where_sucursal .= " AND s.id_sucursal=$sucursal";
            } else if(empty($sucursal)){
                $where_sucursal .= "";
            }

            // if(!empty($sucursal) && intVal($sucursal) != 0) {
            //     $where_sucursal .= " AND s.id_sucursal=$sucursal";
            // }else{
            //     $where_sucursal .="";
            // };
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                nc.id_nota_credito,
                                CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, nc.numero) AS numero,
                                nc.id_factura_origen,
                                CONCAT_WS('-', tfo.cod_establecimiento, tfo.punto_de_expedicion, fo.numero) AS nro_factura_origen,
                                nc.id_factura_destino,
                                CONCAT_WS('-', tfd.cod_establecimiento, tfd.punto_de_expedicion, fd.numero) AS nro_factura_destino,
                                nc.id_cliente,
                                nc.ruc,
                                nc.razon_social,
                                nc.total,
                                s.id_sucursal,
                                s.sucursal,
                                nc.estado,
                                CASE nc.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Utilizado' WHEN 2 THEN 'Anulado' END AS estado_str,
                                nc.usuario,
                                DATE_FORMAT(nc.fecha,'%d/%m/%Y') AS fecha
                            FROM notas_credito nc
                            JOIN timbrados t ON nc.id_timbrado=t.id_timbrado
                            JOIN sucursales s ON nc.id_sucursal=s.id_sucursal
                            JOIN facturas fo ON nc.id_factura_origen=fo.id_factura
                            JOIN timbrados tfo ON fo.id_timbrado=tfo.id_timbrado
                            LEFT JOIN facturas fd ON nc.id_factura_destino=fd.id_factura
                            LEFT JOIN timbrados tfd ON fd.id_timbrado=tfd.id_timbrado
                            WHERE nc.fecha BETWEEN '$desde' AND '$hasta' $where_sucursal
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

        case "cargar":
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_POST["id"]);
            $detalle = json_decode($_POST["detalle"]);

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
                            fecha
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
                            0,
                            NOW()
                        )");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la Nota De Crédito"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            $id_cabecera = $db->getLastID();

            foreach ($detalle as $key => $value) {
                $id_producto = $db->clearText($value->id_producto);
                $cantidad = $db->clearText(quitaSeparadorMiles($value->cantidad));
                $devolucion = $db->clearText(quitaSeparadorMiles($value->devolucion));
                $total_venta = $db->clearText(quitaSeparadorMiles($value->total_venta));

                $db->setQuery("SELECT fp.producto, fp.fraccionado, fp.cantidad, fp.id_lote, fp.lote, l.vencimiento
                                FROM facturas_productos fp
                                JOIN lotes l ON fp.id_lote=l.id_lote
                                WHERE fp.id_producto=$id_producto AND fp.id_factura=$id
                                ORDER BY l.vencimiento");
                $rows = $db->loadObjectList();
                if ($db->error()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos de los productos"]);
                    $db->rollback(); // Revertimos los cambios
                    exit;
                }

                $cantidad_sumar = $devolucion;
                foreach ($rows as $key => $v) {
                    $producto = $v->producto;
                    $fraccionado = $v->fraccionado;
                    $cantidad_lote = $v->cantidad;
                    $id_lote = $v->id_lote;
                    $lote = $v->lote;

                    // Se calcula cuanto sumar a cada lote
                    if ($cantidad_sumar <= $cantidad_lote) {
                        $cantidad_devolucion = $cantidad_sumar;
                        $cantidad_sumar = 0;
                    } else {
                        $cantidad_devolucion = $cantidad_lote;
                        $cantidad_sumar -= $cantidad_lote;
                    }

                    if ($fraccionado == 0) { // Si no es fraccionado
                        $sumar_stock = $cantidad_devolucion;
                        $sumar_fraccionado = 0;
                    } else { // Si es fraccionado
                        $sumar_stock = 0;
                        $sumar_fraccionado = $cantidad_devolucion;
                    }

                    $total_devolucion = round(($total_venta / $cantidad * $cantidad_devolucion), 0);

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
                                        $cantidad_devolucion,
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
                    if ($cantidad_sumar == 0) break;
                }

                // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);

            }

            $db->commit(); // Guardamos los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Nota De Crédito registrada correctamente", "id" => $id_cabecera]);

        break;

        case 'anular':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_POST['id']);

            $db->setQuery("SELECT estado FROM notas_credito WHERE id_nota_credito=$id");
            $row = $db->loadObject();
            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar la factura"]);
                exit;
            }

            if ($row->estado == 2) {
                echo json_encode(["status" => "error", "mensaje" => "La nota de crédito ya fue anulada"]);
                exit;
            }
            if ($row->estado == 1) {
                echo json_encode(["status" => "error", "mensaje" => "La nota de crédito ya fue utilizada"]);
                exit;
            }

            $db->setQuery("UPDATE notas_credito SET estado=2 WHERE id_nota_credito=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular la nota de crédito"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $db->setQuery("SELECT ncp.id_nota_credito_producto, ncp.id_producto, ncp.id_lote, nc.id_sucursal, nc.numero, ncp.fraccionado, ncp.cantidad
                                FROM notas_credito_productos ncp
                                JOIN notas_credito nc ON ncp.id_nota_credito=nc.id_nota_credito
                                WHERE ncp.id_nota_credito=$id
                                ORDER BY ncp.id_producto");
            $rows = $db->loadObjectList();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los productos de la nota de crédito"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            foreach ($rows as $key => $v) {
                $id_nota_credito_producto = $v->id_nota_credito_producto;
                $id_producto = $v->id_producto;
                $id_lote = $v->id_lote;
                $id_sucursal = $v->id_sucursal;
                $numero = $v->numero;
                $cantidad = $v->cantidad;
                $fraccionado = $v->fraccionado;

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
                $historial->id_origen = $id_nota_credito_producto;
                $historial->origen = CRED;
                $historial->detalles = "Nota De Crédito N° ".zerofill($numero)." anulada";
                $historial->usuario = $usuario;

                if (!stock_historial($db, $stock, $historial)) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }

                // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
                if ($id_producto != $rows[$key + 1]->id_producto) {
                    producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Nota de crédito anulada correctamente"]);

        break;

	}

?>
