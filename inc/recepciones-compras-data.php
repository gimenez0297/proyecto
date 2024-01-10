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
        
        case 'ver':
            $db = DataBase::conectar();
            $estado = $db->clearText($_GET['estado']);
            $sucursal = $db->clearText($_GET['sucursal']);
            $where = "";

            if (intval($estado) > 0 || $estado == '0') {
                $where = "AND estado=$estado";
            }

            if(!empty($sucursal) && intVal($sucursal) != 0) {
                $where_sucursal .= " AND rc.id_sucursal='$sucursal'";
            }else{
                $where_sucursal .="";
            };

            // Parametros de ordenamiento, busqueda y paginacion
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
            $sucursal = $db->clearText($_REQUEST['sucursal']);
            $search = $db->clearText($_REQUEST['search']);
            $limit = ($db->clearText($_REQUEST['limit'])) ?: 9;
            $offset	= ($db->clearText($_REQUEST['offset'])) ?: 0;
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', ruc, proveedor, nombre_fantasia, fecha, estado_str, condicion_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                    rc.id_recepcion_compra,
                                    p.id_proveedor,
                                    p.ruc,
                                    p.proveedor,
                                    p.nombre_fantasia,
                                    rc.total_costo,
                                    rc.estado,
                                    rc.numero,
                                    rc.observacion,
                                    rc.condicion,
                                    s.sucursal,
                                    g.nro_gasto AS numero_gasto,
                                    CASE rc.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion_str,
                                    CASE rc.estado WHEN 1 THEN 'Recibido' WHEN 2 THEN 'Anulado' END AS estado_str,
                                    rc.usuario,
                                    DATE_FORMAT(rc.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM recepciones_compras rc
                            LEFT JOIN proveedores p ON rc.id_proveedor=p.id_proveedor
                            LEFT JOIN sucursales s ON s.id_sucursal = rc.id_sucursal
                            LEFT JOIN gastos g ON rc.id_recepcion_compra = g.id_recepcion_compra
                            WHERE 1=1 AND rc.fecha BETWEEN '$desde' AND '$hasta' $where_sucursal $where
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
		
        case 'cambiar-estado':
            $db = DataBase::conectar();
            $db->autocommit(false);

            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("SELECT id_sucursal, numero FROM recepciones_compras WHERE id_recepcion_compra=$id");
            $row = $db->loadObject();
            $id_sucursal = $row->id_sucursal;
            $numero = $row->numero;

            $db->setQuery("SELECT * FROM gastos WHERE id_recepcion_compra = $id");
            $row = $db->loadObject();
            $estado_g = $row->estado;
            $id_gasto = $row->id_gasto;
            $condicion = $row->condicion;
            $id_asiento = $row->id_asiento;
            if ($estado_g == 1||$estado_g == 2||$estado_g == 3) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede anular una recepcion ya pagada de forma parcial o total."]);
                exit;
            }else{
                $db->setQuery("DELETE FROM `gastos` WHERE `id_gasto` = $id_gasto");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. Al borrar el gasto de la recepcion"]);
                   $db->rollback();  // Revertimos los cambios
                   exit;
                }
            }
            if ($condicion == 2) {
                $db->setQuery("DELETE FROM `recepciones_compras_vencimientos` WHERE `id_recepcion_compra` = $id;");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. Al borrar los vencimientos de la recepcion"]);
                   $db->rollback();  // Revertimos los cambios
                   exit;
                }

            }

            try {
                // Rechazado o procesada (parcial o total)
                if (rc_verificar_estado($db, $id, [RC_ESTADO_RECHAZADO])) {
                    throw new Exception("No es posible modificar el estado de una recepción rechazada o procesada");
                }

                if (!rc_actualizar_estado($db, $id, $estado)) {
                    throw new Exception("Error al actualizar el estado de la recepción. Code: ".$db->getErrorCode());
                }

                if ($estado == RC_ESTADO_RECHAZADO) {
                    $db->setQuery("SELECT id_recepcion_compra_producto, id_orden_compra, id_producto, id_lote, cantidad FROM recepciones_compras_productos WHERE id_recepcion_compra=$id");
                    $rows = $db->loadObjectList();

                    $ordenes_compras = [];
                    // Se actualiza el estado de los productos
                    foreach ($rows as $key => $p) {
                        $id_recepcion_compra_producto = $p->id_recepcion_compra_producto;
                        $id_producto = $p->id_producto;
                        $id_orden_compra = $p->id_orden_compra;
                        $id_lote = $p->id_lote;
                        $cantidad = $p->cantidad;

                        if (array_search($id_orden_compra, $ordenes_compras) === false) {
                            $ordenes_compras[] = $id_orden_compra;
                        }

                        ocp_actualizar_estado_auto($db, $id_orden_compra, $id_producto);
                        producto_restar_stock($db, $id_producto, $id_sucursal, $id_lote, $cantidad, 0);

                        $stock = new stdClass();
                        $stock->id_producto = $id_producto;
                        $stock->id_sucursal = $id_sucursal;
                        $stock->id_lote = $id_lote;

                        $historial = new stdClass();
                        $historial->cantidad = $cantidad;
                        $historial->fraccionado = 0;
                        $historial->operacion = SUB;
                        $historial->id_origen = $id_recepcion_compra_producto;
                        $historial->origen = REC;
                        $historial->detalles = "Recepción N° $numero anulada";
                        $historial->usuario = $usuario;

                        if (!stock_historial($db, $stock, $historial)) {
                            throw new Exception("Error al guardar el historial de stock. Code: ".$db->getErrorCode());
                        }

                        // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
                        if ($id_producto != $rows[$key + 1]->id_producto) {
                            producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);
                        }
                    }

                    // Se actualiza el estado de las ordenes de comrpa
                    foreach ($ordenes_compras as $key => $id_orden_compra) {
                        oc_actualizar_estado_auto($db, $id_orden_compra);
                    }
                }
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
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
                VALUES($id_asiento,$id_libro_cuenta,'CONTRA ASIENTO DEL ASIENTO $nro_asiento_c',$haber,$debe);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'ver_productos':
            $db = DataBase::conectar();
            $id_recepcion_compra = $db->clearText($_GET['id_recepcion_compra']);
            $db->setQuery("SELECT 
                            rcp.id_recepcion_compra_producto, 
                            oc.numero, 
                            rcp.id_producto, 
                            rcp.producto, 
                            rcp.codigo, 
                            rcp.costo, 
                            rcp.cantidad,
                            rcp.id_orden_compra,
                            CASE WHEN 
                                (SELECT cc.cantidad FROM ordenes_compras_productos cc WHERE rcp.id_orden_compra = cc.id_orden_compra AND rcp.id_producto = cc.id_producto)
                                =
                                rcp.cantidad
                            THEN
                                (SELECT c.total_costo FROM ordenes_compras_productos c WHERE rcp.id_orden_compra = c.id_orden_compra AND rcp.id_producto = c.id_producto)
                            ELSE
                                (rcp.cantidad*rcp.costo) 
                            END AS total,    
                            pre.id_presentacion,
                            pre.presentacion,
                            rcp.lote
                        FROM recepciones_compras_productos rcp
                        JOIN ordenes_compras oc ON rcp.id_orden_compra=oc.id_orden_compra
                        LEFT JOIN productos p ON rcp.id_producto=p.id_producto
                        LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                        WHERE rcp.id_recepcion_compra=$id_recepcion_compra");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'ver_archivos':
            $db = DataBase::conectar();
            $id_recepcion_compra = $db->clearText($_GET['id_recepcion_compra']);
            $db->setQuery("SELECT 
                                id_recepcion_compra_archivo AS id,
                                archivo
                            FROM recepciones_compras_archivos
                            WHERE id_recepcion_compra=$id_recepcion_compra");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'cargar_archivo':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_POST['id']);
            $archivo = $_POST['archivo'];

            try {
                $foo = new \Verot\Upload\Upload('data:'.$archivo);

                if ($foo->uploaded) {
                    $targetPath = "../archivos/recepciones_compras/";
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0777, true);
                    }

                    $foo->file_new_name_body = md5($id);
                    $foo->image_convert      = jpg;
                    $foo->image_resize       = true;
                    $foo->image_ratio        = true;
                    $foo->image_ratio_crop   = true;
                    $foo->image_ratio_y      = true;
                    $foo->image_y            = 640;
                    $foo->image_x            = 640;
                    $foo->process($targetPath);
                    $archivo = str_replace("../", "", $targetPath . $foo->file_dst_name);
                    if ($foo->processed) {
                        $db->setQuery("INSERT INTO recepciones_compras_archivos (id_recepcion_compra, archivo) VALUES ($id,'$archivo')");
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
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                exit;
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Archivo guardado correctamente"]);
        break;

        case 'eliminar_archivo':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id = $db->clearText($_POST['id']);

            $db->setQuery("SELECT archivo FROM recepciones_compras_archivos WHERE id_recepcion_compra_archivo=$id");
            $row = $db->loadObject();
            $archivo = $row->archivo;

            if (empty($row)) {
                echo json_encode(["status" => "error", "mensaje" => "Archivo no encontrado"]);
                exit;
            }

            $db->setQuery("DELETE FROM recepciones_compras_archivos WHERE id_recepcion_compra_archivo=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al eliminar el archivo. Code: ".$db->getErrorCode()]);
                exit;
            }

            if (!unlink("../".$archivo)) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error al eliminar el archivo"]);
                exit;
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Archivo eliminado correctamente"]);
        break;

        case 'ver_lotes':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_REQUEST['id_producto']);

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                            l.id_lote, 
                            l.lote, 
                            l.vencimiento, 
                            s.stock, 
                            s.fraccionado, 
                            l.costo, 
                            p.cantidad_fracciones as cant
                        FROM lotes l
                        JOIN stock s ON l.id_lote=s.id_lote
                        LEFT JOIN productos p ON p.id_producto = s.id_producto
                        WHERE s.id_producto=$id_producto
                        AND l.lote LIKE '%$term%'
                        GROUP BY l.lote
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

	}

?>
