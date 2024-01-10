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
            $condicion = $db->clearText($_POST['condicion']);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $productos = json_decode($_POST['productos'], true);
            
            if (empty($id_proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);
                exit;
            }
            if (empty($condicion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una condición de compra"]);
                exit;
            }
            if (empty($productos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún producto seleccionado. Favor verifique."]);
                exit;
            }

            $db->setQuery("SELECT MAX(numero) AS numero FROM ordenes_compras");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            // Se calcula el total costo y se extrae el ID de cada solicitud
            $total_costo = 0;
            $solicitudes = [];
            foreach ($productos as $p) {
                // Carga de solicitudes
                $id_solicitud_compra = $db->clearText($p["id_solicitud_compra"]);
                if (array_search($id_solicitud_compra, $solicitudes) === false) {
                    $solicitudes[] = $id_solicitud_compra;
                }

                // Cálculo del costo
                $tt_costo = $db->clearText(quitaSeparadorMiles($p["total_costo"]));
                $total_costo += $tt_costo;
            }

            // Cabeceera
            $db->setQuery("INSERT INTO ordenes_compras (numero, id_proveedor, condicion, observacion, total_costo, estado, usuario, fecha)
                            VALUES ('$numero', $id_proveedor, $condicion,'$observacion', $total_costo,'0','$usuario',NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la orden de compra. Code: ".$db->getErrorCode()]);
                exit;
            }

            $id_orden_compra = $db->getLastID();

            foreach ($productos as $p) {
                $id_producto = $db->clearText($p["id_producto"]);
                $id_solicitud_compra = $db->clearText($p["id_solicitud_compra"]);
                $id_solicitud_compra_producto = $db->clearText($p["id_solicitud_compra_producto"]);
                $codigo = $db->clearText($p["codigo"]);
                $producto = $db->clearText($p["producto"]);
                $costo = $db->clearText(quitaSeparadorMiles($p["costo"]));
                $costo_ultimo = $db->clearText(quitaSeparadorMiles($p["costo_ultimo"]));
                $costo_tt = $db->clearText(quitaSeparadorMiles($p["total_costo"]));
                $solicitado = $db->clearText(quitaSeparadorMiles($p["solicitado"]));
                $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
                $finalizar = $db->clearText(quitaSeparadorMiles($p["finalizar"]));

                try {
                    if (empty($cantidad) && $finalizar != 1) {
                        throw new Exception("El producto \"$producto\" tiene cantidad 0. Favor verifique.");
                    }

                    $pendiente = scp_cantidad_pendiente($db, $id_solicitud_compra, $id_producto);
                    if ($pendiente - $cantidad  < 0) {
                        throw new Exception("La cantidad a recepcionar del producto \"$producto\" es mayor a la cantidad pendiente en la solicitud de compra N° $id_solicitud_compra");
                    }

                    $db->setQuery("INSERT INTO ordenes_compras_productos (id_orden_compra, id_producto, id_solicitud_compra, codigo, producto, costo, cantidad, estado, total_costo)
                                    VALUES ($id_orden_compra,$id_producto,$id_solicitud_compra,$codigo,'$producto',$costo,$cantidad,0, '$costo_tt')");
                    if (!$db->alter()) {
                        throw new Exception("Error de base de datos al registrar el producto en la orden de compra. Code: ".$db->getError());
                    }

                    producto_actualizar_costo($db, $id_producto, $id_proveedor, $costo, $costo_ultimo);
                    scp_actualizar_estado_auto($db, $id_solicitud_compra, $id_producto);

                    // Si finaliza la carga del producto antes de asociar todos los productos a ordenes de compra
                    if ($finalizar == 1) scp_finalizar($db, $id_solicitud_compra, $id_producto, $usuario);
                } catch (Exception $e) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }

            }

            // Se actualiza el estado de las solicitudes
            foreach ($solicitudes as $id) {
                try {
                    sc_actualizar_estado_auto($db, $id);
                } catch (Exception $e) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Orden de compra registrada correctamente", "id_orden_compra" => $id_orden_compra]);
            
        break;

        case 'ver_productos':
            $db = DataBase::conectar();
            $id_solicitud_compra = intval($db->clearText($_POST["id_solicitud_compra"]));

            $db->setQuery("SELECT
                                sc.id_solicitud_compra, 
                                sc.numero, 
                                scp.id_solicitud_compra_producto, 
                                p.id_producto, 
                                p.producto, 
                                p.codigo, 
                                pp.costo, 
                                IFNULL(fv.precio,0) AS precio_venta,
                                ROUND(IFNULL(((IFNULL(fv.precio,0) - pp.costo)/fv.precio),0)*100,1) AS porc_desc,
                                scp.cantidad - (
                                    SELECT IFNULL(SUM(cantidad), 0)
                                    FROM ordenes_compras_productos ocp
                                    JOIN ordenes_compras oc ON ocp.id_orden_compra=oc.id_orden_compra
                                    WHERE oc.estado!=2 AND ocp.id_solicitud_compra=sc.id_solicitud_compra AND ocp.id_producto=scp.id_producto
                                ) AS cantidad,
                                (scp.cantidad - (
                                    SELECT IFNULL(SUM(cantidad), 0)
                                    FROM ordenes_compras_productos ocp
                                    JOIN ordenes_compras oc ON ocp.id_orden_compra=oc.id_orden_compra
                                    WHERE oc.estado!=2 AND ocp.id_solicitud_compra=sc.id_solicitud_compra AND ocp.id_producto=scp.id_producto
                                )) * pp.costo AS total_costo,
                                pre.id_presentacion,
                                pre.presentacion,
                                scp.cantidad AS solicitado, 
                                pp.costo AS costo_ultimo,
                                0 AS porcentaje,
                                scp.cantidad - (
                                    SELECT IFNULL(SUM(cantidad), 0)
                                    FROM ordenes_compras_productos ocp
                                    JOIN ordenes_compras oc ON ocp.id_orden_compra=oc.id_orden_compra
                                    WHERE oc.estado!=2 AND ocp.id_solicitud_compra=sc.id_solicitud_compra AND ocp.id_producto=scp.id_producto
                                ) AS pendiente,
                                (SELECT IFNULL(SUM(st.stock), 0) FROM stock st JOIN productos pr ON st.id_producto=pr.id_producto WHERE st.id_producto = p.id_producto ) AS stock
                            FROM solicitudes_compras_productos scp
                            JOIN solicitudes_compras sc ON scp.id_solicitud_compra=sc.id_solicitud_compra
                            JOIN productos p ON scp.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN productos_proveedores pp ON p.id_producto=pp.id_producto AND sc.id_proveedor=pp.id_proveedor
                            LEFT JOIN (SELECT MAX(id_factura_producto), precio, id_producto, fraccionado FROM facturas_productos WHERE fraccionado != 1 GROUP BY id_producto) fv ON p.id_producto=fv.id_producto
                            WHERE sc.id_solicitud_compra=$id_solicitud_compra AND sc.estado IN(1,3) AND scp.estado IN(0,1)");
            $row = ($db->loadObjectList()) ?: [];
            echo json_encode($row);
        break;

        case 'recuperar-numero':
            $db       = DataBase::conectar();

            $db->setQuery("SELECT MAX(numero) AS numero FROM ordenes_compras");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
                exit;
            }

            $sgte_numero = intval($row->numero) + 1;
            $numero = zerofill($sgte_numero);

            echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

        case 'ver':
            $db = DataBase::conectar();

            //Parametros de ordenamiento, busqueda y paginacion
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', ruc, proveedor, fecha ) LIKE '%$search%'";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            sc.id_solicitud_compra,
                            p.id_proveedor,
                            p.ruc,
                            p.proveedor,
                            sc.observacion,
                            sc.numero,
                            sc.usuario,
                            sc.estado,
                            DATE_FORMAT(sc.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM solicitudes_compras sc
                            JOIN proveedores p ON sc.id_proveedor=p.id_proveedor
                            WHERE  sc.estado IN(1,3) $having
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
	}

?>
