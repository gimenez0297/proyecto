<?php
include("funciones.php");
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q = $_REQUEST['q'];
$usuario = $auth->getUsername();
$id_sucursal = datosUsuario($usuario)->id_sucursal;

switch ($q) {

    case 'ver':
        $db = DataBase::conectar();
        $where = "";

        //Parametros de ordenamiento, busqueda y paginacion
        $desde = $db->clearText($_REQUEST['desde']);
        $hasta = $db->clearText($_REQUEST['hasta']);
        $search = $db->clearText($_REQUEST['search']);
        $limit = $db->clearText($_REQUEST['limit']);
        $offset    = $db->clearText($_REQUEST['offset']);
        $order = $db->clearText($_REQUEST['order']);
        $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', sucursal_origen, sucursal_destino, numero, fecha, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            nr.id_nota_remision,
                            nr.id_sucursal_origen,
                            so.sucursal AS sucursal_origen,
                            nr.id_sucursal_destino,
                            sd.sucursal AS sucursal_destino,
                            nr.razon_social_destino,
                            nr.observacion,
                            nr.numero,
                            nr.estado,
                            nr.id_nota_remision_motivo,
                            nrm.descripcion AS motivo,
                            nr.tipo_remision,
                            CASE nr.tipo_remision WHEN 0 THEN 'Productos' WHEN 1 THEN 'Insumos' END AS tipo,
                            CASE nr.estado WHEN 1 THEN 'En Tránsito' WHEN 2 THEN 'Anulado' WHEN 3 THEN 'Finalizado' WHEN 4 THEN 'F. Incompleto' END AS estado_str,
                            nr.usuario,
                            DATE_FORMAT(nr.fecha_emision,'%d/%m/%Y %H:%i:%s') AS fecha_emision,
                            nr.usuario_recepcion,
                            DATE_FORMAT(nr.fecha_actualizacion,'%d/%m/%Y %H:%i:%s') AS fecha_recepcion
                            FROM notas_remision nr
                            JOIN notas_remision_motivos nrm ON nr.id_nota_remision_motivo=nrm.id_nota_remision_motivo
                            JOIN sucursales so ON nr.id_sucursal_origen=so.id_sucursal
                            LEFT JOIN sucursales sd ON nr.id_sucursal_destino=sd.id_sucursal
                            WHERE DATE(nr.fecha_emision) BETWEEN '$desde' AND '$hasta' $having
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

    case 'anular':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id = $db->clearText($_POST['id']);

        $db->setQuery("SELECT 
                                id_nota_remision,
                                id_nota_remision_motivo,
                                id_sucursal_origen,
                                id_sucursal_destino,
                                numero,
                                estado 
                            FROM notas_remision 
                            WHERE id_nota_remision=$id");
        $row = $db->loadObject();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar la nota de remisión"]);
            exit;
        }

        $id_nota_remision = $row->id_nota_remision;
        $id_sucursal_origen = $row->id_sucursal_origen;
        $id_sucursal_destino = $row->id_sucursal_destino;
        $id_nota_remision_motivo = $row->id_nota_remision_motivo;
        $numero = $row->numero;
        $estado = $row->estado;

        if ($estado == 2) {
            echo json_encode(["status" => "error", "mensaje" => "La nota de remisión ya fue anulada"]);
            exit;
        }
        if ($estado == 3 && $id_nota_remision_motivo == 1 || $estado == 4 && $id_nota_remision_motivo == 1 ) {
            echo json_encode(["status" => "error", "mensaje" => "La nota de remisión no se puede anular"]);
            exit;
        }
        if (empty($id_sucursal_origen)) {
            echo json_encode(["status" => "error", "mensaje" => "Sucursal de Origen no encontrada"]);
            exit;
        }
        if ($id_nota_remision_motivo == 1 && empty($id_sucursal_destino)) {
            echo json_encode(["status" => "error", "mensaje" => "Sucursal de Destino no encontrada"]);
            exit;
        }

        $db->setQuery("UPDATE notas_remision SET estado=2 WHERE id_nota_remision=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular la nota de remisión"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $db->setQuery("SELECT
                                id_nota_remision_producto,
                                id_nota_remision,
                                id_producto,
                                id_solicitud_deposito,
                                codigo,
                                producto,
                                id_lote,
                                lote,
                                cantidad,
                                cantidad_recibida
                            FROM notas_remision_productos 
                            WHERE id_nota_remision=$id");
        $rows = $db->loadObjectList();

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los productos de la notas de remisión"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $solicitudes = [];
        foreach ($rows as $key => $p) {
            $id_nota_remision_producto = $p->id_nota_remision_producto;
            $id_producto = $p->id_producto;
            $id_lote = $p->id_lote;
            $cantidad = $p->cantidad;
            $id_solicitud_deposito = $p->id_solicitud_deposito;

            // Se devuelve el stock a la sucursal de origen
            try {
                producto_sumar_stock($db, $id_producto, $id_sucursal_origen, $id_lote, $cantidad, 0);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                exit;
            }

            $stock = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal_origen;
            $stock->id_lote = $id_lote;

            $historial = new stdClass();
            $historial->cantidad = $cantidad;
            $historial->fraccionado = 0;
            $historial->operacion = ADD;
            $historial->id_origen = $id_nota_remision_producto;
            $historial->origen = REM;
            $historial->detalles = "Nota De Remisión N° " . zerofill($numero) . " anulada";
            $historial->usuario = $usuario;

            if (!stock_historial($db, $stock, $historial)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
            if ($id_producto != $rows[$key + 1]->id_producto) {
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal_origen);
            }

            // Se resta el stock a la sucursal de destino
            if (isset($id_sucursal_destino) && !empty($id_sucursal_destino) && $estado == 3) {
                try {
                    producto_restar_stock($db, $id_producto, $id_sucursal_destino, $id_lote, $cantidad_recibida, 0);
                } catch (Exception $e) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                    exit;
                }

                $stock->id_sucursal = $id_sucursal_destino;
                $historial->cantidad = $cantidad_recibida;
                $historial->operacion = SUB;

                if (!stock_historial($db, $stock, $historial)) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }

                // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
                if ($id_producto != $rows[$key + 1]->id_producto) {
                    producto_verificar_niveles_stock($db, $id_producto, $id_sucursal_destino);
                }
            }

            if (isset($id_solicitud_deposito)) {
                if (array_search($id_solicitud_deposito, $solicitudes) === false) {
                    $solicitudes[] = $id_solicitud_deposito;
                }

                sdp_actualizar_estado_auto($db, $id_solicitud_deposito, $id_producto);
            }
        }

        if (count($solicitudes) > 0) {
            // Se actualiza el estado de las solicitudes
            foreach ($solicitudes as $id_solicitud_deposito) {
                sd_actualizar_estado_auto($db, $id_solicitud_deposito);
            }
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Nota de Remisión anulada correctamente"]);

        break;

    case 'recepcionar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id = $db->clearText($_POST['id']);
        $observacion = $db->clearText($_POST['observacion']);
        $detalle = json_decode($_POST['detalle'], true);

        $db->setQuery("SELECT 
                                id_nota_remision,
                                id_sucursal_origen,
                                id_sucursal_destino,
                                id_nota_remision_motivo,
                                numero,
                                estado 
                            FROM notas_remision 
                            WHERE id_nota_remision=$id");
        $row = $db->loadObject();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar la nota de remisión"]);
            exit;
        }

        $id_nota_remision = $row->id_nota_remision;
        $id_sucursal_origen = $row->id_sucursal_origen;
        $id_sucursal_destino = $row->id_sucursal_destino;
        $id_nota_remision_motivo = $row->id_nota_remision_motivo;
        $numero = $row->numero;
        $estado = $row->estado;
        if ($estado != 1) {
            echo json_encode(["status" => "error", "mensaje" => "Solo puede recepcionar notas de remisión con estado \"En Tránsito\""]);
            exit;
        }
        if ($id_nota_remision_motivo == 1 && empty($id_sucursal_destino)) {
            echo json_encode(["status" => "error", "mensaje" => "Sucursal de Destino no encontrada"]);
            exit;
        }
        if (empty($detalle)) {
            echo json_encode(["status" => "error", "mensaje" => "Productos no encontrados"]);
            exit;
        }

        $estado_real = '';
        foreach ($detalle as $key => $v) {
            $cantidad += $db->clearText(quitaSeparadorMiles($v["cantidad"]));
            $cantidad_recibida += $db->clearText(quitaSeparadorMiles($v["cantidad_recibida"]));
        }

        if($cantidad > $cantidad_recibida){
            $estado_real = 4;
        }else{
            $estado_real = 3;
        }


        $db->setQuery("UPDATE notas_remision SET observacion='$observacion', usuario_recepcion='$usuario', fecha_actualizacion= NOW(), estado='$estado_real' WHERE id_nota_remision=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de la nota de remisión"]);
            $db->rollback();  // Revertimos los cambios
            exit;
        }

        foreach ($detalle as $key => $v) {
            $id_nota_remision_producto = $db->clearText($v["id_nota_remision_producto"]);
            $producto = $db->clearText($v["producto"]);
            $cantidad_recibida = $db->clearText(quitaSeparadorMiles($v["cantidad_recibida"]));
            $observacion = $db->clearText($v["observacion"]);

            $db->setQuery("SELECT
                                    id_nota_remision_producto,
                                    id_nota_remision,
                                    id_producto,
                                    codigo,
                                    producto,
                                    id_lote,
                                    lote,
                                    cantidad,
                                    cantidad_recibida
                                FROM notas_remision_productos 
                                WHERE id_nota_remision_producto=$id_nota_remision_producto");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos del producto \"$producto\""]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_producto = $row->id_producto;
            $codigo = $row->codigo;
            $producto = $row->producto;
            $id_lote = $row->id_lote;
            $lote = $row->lote;
            $cantidad = $row->cantidad;

            if ($cantidad_recibida > $cantidad) {
                echo json_encode(["status" => "error", "mensaje" => "La cantidad recepcionada del producto \"$producto\" supera la cantidad registrada en la nota de remisión"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $db->setQuery("UPDATE notas_remision_productos SET cantidad_recibida=$cantidad_recibida, observacion='$observacion' WHERE id_nota_remision_producto=$id_nota_remision_producto");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la cantidad recepcionada del producto \"$producto\""]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            try {
                producto_sumar_stock($db, $id_producto, $id_sucursal_destino, $id_lote, $cantidad_recibida, 0);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                exit;
            }

            $stock = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal_destino;
            $stock->id_lote = $id_lote;

            $historial = new stdClass();
            $historial->cantidad = $cantidad_recibida;
            $historial->fraccionado = 0;
            $historial->operacion = ADD;
            $historial->id_origen = $id_nota_remision_producto;
            $historial->origen = REM;
            $historial->detalles = "Nota De Remisión N° " . zerofill($numero);
            $historial->usuario = $usuario;

            if (!stock_historial($db, $stock, $historial)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
            if ($id_producto != $detalle[$key + 1]->id_producto) {
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal_destino);
            }
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Recepción de productos registrada correctamente"]);
        break;

    case 'ver_detalle':
        $db = DataBase::conectar();
        $id = $db->clearText($_GET['id']);

        $db->setQuery("SELECT 
                            nrp.id_nota_remision_producto, 
                            p.id_producto, 
                            p.producto, 
                            p.codigo, 
                            pre.id_presentacion, 
                            pre.presentacion, 
                            nrp.cantidad, 
                            nrp.cantidad_recibida, 
                            nrp.observacion,
                            sd.numero,
                            nrp.lote
                            FROM notas_remision_productos nrp
                            JOIN productos p ON nrp.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            LEFT JOIN solicitudes_depositos sd ON sd.id_solicitud_deposito = nrp.id_solicitud_deposito
                            WHERE nrp.id_nota_remision=$id");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;

    case 'ver_detalle_insumo':
        $db = DataBase::conectar();
        $id = $db->clearText($_GET['id']);

        $db->setQuery("SELECT 
                            nrp.id_nota_remision_insumo, 
                            p.id_producto_insumo, 
                            p.producto, 
                            p.codigo, 
                            nrp.cantidad, 
                            nrp.cantidad_recibida, 
                            nrp.observacion
                            FROM notas_remision_insumo nrp
                            JOIN productos_insumo p ON nrp.id_producto_insumo=p.id_producto_insumo
                            WHERE nrp.id_nota_remision=$id");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;

    case 'recepcionar_insumo':
        $db = DataBase::conectar();
        $db->autocommit(false);

        $id = $db->clearText($_POST['id_remision_insumo']);
        $observacion = $db->clearText($_POST['observacion_insumo']);
        $detalle = json_decode($_POST['detalle'], true);
        

        

        $db->setQuery("SELECT 
                                id_nota_remision,
                                id_sucursal_origen,
                                id_sucursal_destino,
                                id_nota_remision_motivo,
                                numero,
                                estado 
                            FROM notas_remision 
                            WHERE id_nota_remision=$id");
        $row = $db->loadObject();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar la nota de remisiónnnn"]);
            exit;
        }

        $id_nota_remision = $row->id_nota_remision;
        $id_sucursal_origen = $row->id_sucursal_origen;
        $id_sucursal_destino = $row->id_sucursal_destino;
        $id_nota_remision_motivo = $row->id_nota_remision_motivo;
        $numero = $row->numero;
        $estado = $row->estado;

        if ($estado != 1) {
            echo json_encode(["status" => "error", "mensaje" => "Solo puede recepcionar notas de remisión con estado \"En Tránsito\""]);
            exit;
        }
        if ($id_nota_remision_motivo == 1 && empty($id_sucursal_destino)) {
            echo json_encode(["status" => "error", "mensaje" => "Sucursal de Destino no encontrada"]);
            exit;
        }
        if (empty($detalle)) {
            echo json_encode(["status" => "error", "mensaje" => "Insumos no encontrados"]);
            exit;
        }

        $estado_real = '';
        foreach ($detalle as $key => $v) {
            $cantidad += $db->clearText(quitaSeparadorMiles($v["cantidad"]));
            $cantidad_recibida += $db->clearText(quitaSeparadorMiles($v["cantidad_recibida"]));
        }

        if($cantidad > $cantidad_recibida){
            $estado_real = 4;
        }else{
            $estado_real = 3;
        }


        $db->setQuery("UPDATE notas_remision SET observacion='$observacion', usuario_recepcion='$usuario', fecha_actualizacion= NOW(), estado='$estado_real' WHERE id_nota_remision=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de la nota de remisión"]);
            $db->rollback();  // Revertimos los cambios
            exit;
        }

        foreach ($detalle as $key => $v) {
            $id_nota_remision_insumo = $db->clearText($v["id_nota_remision_insumo"]);
            $producto = $db->clearText($v["producto"]);
            $cantidad_recibida = $db->clearText(quitaSeparadorMiles($v["cantidad_recibida"]));
            $observacion = $db->clearText($v["observacion"]);

            $db->setQuery("SELECT
                                    id_nota_remision_insumo,
                                    id_nota_remision,
                                    id_producto_insumo,
                                    codigo,
                                    producto,
                                    cantidad,
                                    cantidad_recibida
                                FROM notas_remision_insumo 
                                WHERE id_nota_remision_insumo=$id_nota_remision_insumo");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos del producto \"$producto\""]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_producto = $row->id_producto_insumo;
            $codigo = $row->codigo;
            $producto = $row->producto;
            $cantidad = $row->cantidad;

            if ($cantidad_recibida > $cantidad) {
                echo json_encode(["status" => "error", "mensaje" => "La cantidad recepcionada del producto \"$producto\" supera la cantidad registrada en la nota de remisión"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $db->setQuery("UPDATE notas_remision_insumo SET cantidad_recibida=$cantidad_recibida, observacion='$observacion' WHERE id_nota_remision_insumo=$id_nota_remision_insumo");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la cantidad recepcionada del producto \"$producto\""]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Recepción de productos registrada correctamente"]);
        break;
}
