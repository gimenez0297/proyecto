<?php
include("funciones.php");
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q = $_REQUEST['q'];
$usuario = $auth->getUsername();
$datosUsuario = datosUsuario($usuario);
$id_sucursal = $datosUsuario->id_sucursal;
$id_rol = $datosUsuario->id_rol;

switch ($q) {

    case 'ver':
        $db = DataBase::conectar();
        $desde = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta = $db->clearText($_REQUEST['hasta']) . " 23:59:59";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit = $db->clearText($_REQUEST['limit']);
        $offset    = $db->clearText($_REQUEST['offset']);
        $order = $db->clearText($_REQUEST['order']);
        $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING numero LIKE '$search%' OR ci LIKE '$search%' OR  nombre_apellido LIKE '$search%' OR usuario LIKE '$search%' OR condicion LIKE '$search%' OR ruc LIKE '$search%' OR razon_social LIKE '$search%' OR fecha LIKE '$search%' OR estado_str LIKE '$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        f.id_factura,
                        f.id_sucursal,
                        f.id_orden,
                        CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                        DATE_FORMAT(f.fecha_venta,'%d/%m/%Y %H:%i:%s') AS fecha_venta,
                        CASE f.condicion WHEN 1 THEN 'Contado' WHEN 2 THEN 'Crédito' END AS condicion,
                        f.vencimiento,
                        f.id_cliente, 
                        f.ruc,
                        f.razon_social,
                        f.cantidad,
                        f.impresiones,
                        f.editar_cobros,
                        f.usuario,
                        f.estado,
                        s.sucursal,
                        u.nombre_apellido,
                        u.ci,
                        CASE f.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Anulado' END AS estado_str,
                        DATE_FORMAT(f.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                        IF (
                            (
                                SELECT COUNT(c.id_factura)
                                FROM cobros c
                                WHERE c.id_metodo_pago IN (2,3)  AND c.id_factura = f.id_factura 
                            ) > 0, 
                            IF (
                                (
                                    SELECT COUNT(c.id_factura)
                                    FROM cobros c
                                    WHERE c.id_metodo_pago IN (2,3)  AND c.detalles = '' AND c.id_factura = f.id_factura
                                ) > 0, 
                                'Pendiente',
                                'Cargado'
                            ), 
                            'No Requerido'
                        ) AS voucher
                        FROM facturas f
                        LEFT JOIN timbrados t ON f.id_timbrado = t.id_timbrado
                        LEFT JOIN sucursales s ON s.id_sucursal = f.id_sucursal               
                        JOIN users u ON u.username = f.usuario
                        WHERE f.id_orden IS NOT NULL
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

    case 'ver_productos':
        $db = DataBase::conectar();
        $id_factura = $db->clearText($_GET['id_factura']);

        $db->setQuery("SELECT 
                        fp.id_factura_producto, 
                        fp.id_producto, 
                        p.codigo, 
                        p.producto, 
                        p.controlado, 
                        pre.id_presentacion, 
                        pre.presentacion, 
                        SUM(fp.cantidad) AS cantidad, 
                        (fp.precio * SUM(fp.cantidad)) AS subtotal,
                        SUM(fp.total_venta) AS total_venta,
                        fp.precio, 
                        fp.fraccionado, 
                        fp.id_lote,
                        fp.lote
                    FROM facturas_productos fp
                    JOIN productos p ON fp.id_producto=p.id_producto
                    LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                    LEFT JOIN facturas f ON f.id_factura = fp.id_factura
                    WHERE fp.id_factura=$id_factura
                    GROUP BY fp.id_producto");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;

    case "ver_cobros":
        $db = DataBase::conectar();
        $id = $db->clearText($_REQUEST["id"]);
    
        $db->setQuery("SELECT 
                            c.id_cobro, 
                            c.id_metodo_pago, 
                            c.id_descuento_metodo_pago, 
                            c.id_recibo, 
                            IF(c.id_metodo_pago IS NOT NULL, c.metodo_pago, dp.descripcion) AS metodo_pago,
                            c.detalles, 
                            r.numero AS numero_recibo, 
                            c.monto
                        FROM cobros c
                        LEFT JOIN descuentos_pagos dp ON c.id_descuento_metodo_pago=dp.id_descuento_pago
                        LEFT JOIN recibos r ON c.id_recibo=r.id_recibo
                        WHERE c.estado=1 AND c.id_factura=$id");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
    break;

    case "editar_cobros":
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id_factura = $db->clearText($_POST["id_factura"]);
        $data = json_decode($_POST["data"]);

        if (empty($data)) {
            echo json_encode(["status" => "error", "mensaje" => "No se encontraron los cobros de la factura"]);
            exit;
        }

        $db->setQuery("SELECT editar_cobros FROM facturas WHERE id_factura=$id_factura");
        $row = $db->loadObject();
        if ($db->error()) {
            $db->rollback(); // Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos de los cobros"]);
            exit;
        }

        if ($row->editar_cobros == 0) {
            echo json_encode(["status" => "error", "mensaje" => "No puede editar los cobros de esta factura"]);
            exit;
        }

        foreach ($data as $key => $value) {
            $id = $db->clearText($value->id_cobro);
            $new_value = $db->clearText($value->detalles);

            $db->setQuery("SELECT id_factura FROM cobros WHERE id_cobro=$id");
            $row = $db->loadObject();
            if ($db->error()) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos de los cobros"]);
                exit;
            }

            if ($row->id_factura != $id_factura) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "El cobro no corresponde a la factura seleccionada"]);
                exit;
            }

            $db->setQuery("UPDATE cobros SET detalles='$new_value' WHERE id_cobro=$id");
            if (!$db->alter()) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al editar el cobro"]);
                exit;
            }
        }

        $db->setQuery("UPDATE facturas SET editar_cobros=0 WHERE id_factura=$id_factura");
        if (!$db->alter()) {
            $db->rollback(); // Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la factura"]);
            exit;
        }

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Cobro modificado correctamente"]);

    break;

    case 'anular':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id = $db->clearText($_POST['id']);

        $db->setQuery("SELECT id_cliente, id_orden, estado FROM facturas WHERE id_factura=$id");
        $row = $db->loadObject();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar la factura"]);
            exit;
        }

        $id_cliente = $row->id_cliente;
        $id_orden = $row->id_orden;

        if ($row->estado == 2) {
            echo json_encode(["status" => "error", "mensaje" => "La factura ya fue anulada"]);
            exit;
        }

        // Se anula la factura
        $db->setQuery("UPDATE facturas SET estado=2 WHERE id_factura=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        // Se anulan los cobros
        $db->setQuery("UPDATE cobros SET estado=0 WHERE id_factura=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular los pagos de la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        //Se anula la orden. 
        $db->setQuery("UPDATE `ordenes` SET `estado` = 2, `observacion` = 'Factura Anulada' WHERE `id_orden` = $id_orden;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular la orden."]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        // Se suma el stock
        $db->setQuery("SELECT fp.id_factura_producto, fp.fraccionado, fp.id_producto, fp.id_lote, f.id_sucursal, f.numero, fp.cantidad
                            FROM facturas_productos fp
                            JOIN facturas f ON fp.id_factura=f.id_factura
                            WHERE fp.id_factura=$id");
        $rows = $db->loadObjectList();

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los productos de la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        foreach ($rows as $key => $p) {
            $id_factura_producto = $p->id_factura_producto;
            $id_producto = $p->id_producto;
            $id_lote = $p->id_lote;
            $id_sucursal = $p->id_sucursal;
            $numero = $p->numero;
            $cantidad = $p->cantidad;
            $fraccionado = $p->fraccionado;

            if ($fraccionado == 0) {
                $sumar_stock = $cantidad;
                $sumar_fraccionado = 0;
            } else {
                $sumar_stock = 0;
                $sumar_fraccionado = $cantidad;
            }

            try {
                producto_sumar_stock($db, $id_producto, $id_sucursal, $id_lote, $sumar_stock, $sumar_fraccionado);
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
            $historial->cantidad = $sumar_stock;
            $historial->fraccionado = $sumar_fraccionado;
            $historial->operacion = ADD;
            $historial->id_origen = $id_factura_producto;
            $historial->origen = FAC;
            $historial->detalles = "Factura N° " . zerofill($numero) . " anulada";
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
        echo json_encode(["status" => "ok", "mensaje" => "Factura anulada correctamente"]);
    break;
}