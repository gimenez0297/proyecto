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
            $pendientes = $db->clearText($_GET['pendientes']);
            $where = "";

            if ($pendientes == 1) {
                $where = "AND estado IN(1,3)";
            }

            // Parametros de ordenamiento, busqueda y paginacion
            $desde = $db->clearText($_REQUEST['desde']);
            $hasta = $db->clearText($_REQUEST['hasta']);
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING  ruc LIKE '$search%' OR proveedor LIKE '$search%' OR nombre_fantasia LIKE '$search%' OR fecha LIKE '$search%' OR estado_str LIKE '$search%' OR condicion_str LIKE '$search%'";
            }

            if ((isset($desde) && !empty($desde)) || (isset($hasta) && !empty($hasta))) {
                $where_fecha = " AND DATE(oc.fecha) BETWEEN '$desde' AND '$hasta'";
            } else {
                $where_fecha = "";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                    oc.id_orden_compra,
                                    p.id_proveedor,
                                    p.ruc,
                                    p.proveedor,
                                    p.nombre_fantasia,
                                    oc.total_costo,
                                    oc.estado,
                                    oc.condicion,
                                    oc.numero,
                                    oc.observacion,
                                    CASE oc.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion_str,
                                    CASE oc.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Aprobado' WHEN 2 THEN 'Rechazado' WHEN 3 THEN 'Procesado Parcial' WHEN 4 THEN 'Procesado Total' END AS estado_str,
                                    oc.usuario,
                                    DATE_FORMAT(oc.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM ordenes_compras oc
                            LEFT JOIN proveedores p
                            ON oc.id_proveedor=p.id_proveedor
                            WHERE 1=1 $where_fecha $where
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

            try {
                cambiar_estado($db, $id, $estado);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                exit;
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'cambiar-estados':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $ordenes = json_decode($_POST['ordenes']);
            $estado = $db->clearText($_POST['estado']);

            if (empty($ordenes)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una o más ordenes"]);
                exit;
            }

            foreach ($ordenes as $s) {
                $id = $db->clearText($s->id_orden_compra);

                try {
                    cambiar_estado($db, $id, $estado);
                } catch (Exception $e) {
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'ver_productos':
            $db = DataBase::conectar();
            $id_orden_compra = $db->clearText($_GET['id_orden_compra']);
            $db->setQuery("SELECT 
                            ocp.id_orden_compra_producto, 
                            ocp.id_producto, 
                            ocp.producto, 
                            ocp.codigo, 
                            ocp.costo, 
                            ocp.cantidad,
                            pre.id_presentacion,
                            pre.presentacion,
                            ocp.id_solicitud_compra,
                            sc.numero,
                            ocp.total_costo AS total,
                            IFNULL(p.precio,0) AS precio_venta,
                            CONCAT(ROUND(IFNULL(((IFNULL(p.precio,0) - pp.costo)/p.precio),0)*100,1), ' %') AS porc_desc,
                            @recepcionado:=(
                                SELECT IFNULL(SUM(rcp.cantidad), 0)
                                FROM recepciones_compras_productos rcp
                                JOIN recepciones_compras rc ON rcp.id_recepcion_compra=rc.id_recepcion_compra
                                WHERE rc.estado!=2 AND rcp.id_orden_compra=ocp.id_orden_compra AND rcp.id_producto=ocp.id_producto
                            ) AS recepcionado,
                            @pendientes:=(ocp.cantidad - (SELECT recepcionado)) AS pendiente,
                            CASE WHEN @pendientes > 0 THEN
                                (@recepcionado*ocp.costo)
                            ELSE
                                ocp.total_costo
                            END AS total_recepcionado
                        FROM ordenes_compras_productos ocp
                        JOIN solicitudes_compras sc ON ocp.id_solicitud_compra=sc.id_solicitud_compra
                        LEFT JOIN productos p ON ocp.id_producto=p.id_producto
                        LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                        LEFT JOIN productos_proveedores pp ON p.id_producto=pp.id_producto AND sc.id_proveedor=pp.id_proveedor
                        LEFT JOIN (SELECT MAX(id_factura_producto), precio, id_producto, fraccionado FROM facturas_productos WHERE fraccionado != 1 GROUP BY id_producto) fv ON ocp.id_producto=fv.id_producto
                        WHERE ocp.cantidad>0 AND ocp.id_orden_compra=$id_orden_compra");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

	}

    /**
     * Actualiza el estado de una orden de comrpa, si es el estado es rechazado modifica el estado de las solicitudes asociadas
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id ID de la orden de compra
     * @param array|int $estado
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos
     */
    function cambiar_estado($db, $id, $estado) {
        // Rechazado o procesada (parcial o total)
        if (oc_verificar_estado($db, $id, [OC_ESTADO_RECHAZADO, OC_ESTADO_PARCIAL, OC_ESTADO_TOTAL])) {
            throw new Exception("No es posible modificar el estado de una orden de compra rechazada o procesada");
        }

        if (!oc_actualizar_estado($db, $id, $estado)) {
            throw new Exception("Error al actualizar el estado de la orden de compra. Code: ".$db->getErrorCode());
        }

        if ($estado == OC_ESTADO_RECHAZADO) {
            $db->setQuery("SELECT id_solicitud_compra, id_producto FROM ordenes_compras_productos WHERE id_orden_compra=$id");
            $rows = $db->loadObjectList();

            $solicitudes = [];
            // Se actualiza el estado de los productos
            foreach ($rows as $p) {
                $id_producto = $p->id_producto;
                $id_solicitud_compra = $p->id_solicitud_compra;

                if (array_search($id_solicitud_compra, $solicitudes) === false) {
                    $solicitudes[] = $id_solicitud_compra;
                }

                scp_actualizar_estado_auto($db, $id_solicitud_compra, $id_producto);
            }

            // Se actualiza el estado de las solicitudes
            foreach ($solicitudes as $id_solicitud_compra) {
                sc_actualizar_estado_auto($db, $id_solicitud_compra);
            }
        }

    }

?>
