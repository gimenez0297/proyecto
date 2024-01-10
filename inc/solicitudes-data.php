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
            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', ruc, proveedor, fecha, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            sc.id_solicitud_compra,
                            p.id_proveedor,
                            p.ruc,
                            p.proveedor,
                            sc.observacion,
                            sc.numero,
                            sc.estado,
                            CASE sc.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Aprobado' WHEN 2 THEN 'Rechazado' WHEN 3 THEN 'Procesado Parcial' WHEN 4 THEN 'Procesado Total' ELSE '' END AS estado_str,
                            sc.usuario,
                            DATE_FORMAT(sc.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM solicitudes_compras sc
                            JOIN proveedores p ON sc.id_proveedor=p.id_proveedor
                            WHERE sc.fecha BETWEEN '$desde' AND '$hasta' $having
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
            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            try {
                actualizar_estado_solicitud($db, $id, $estado);
                echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
            }

        break;

        case 'cambiar-estados':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $solicitudes = json_decode($_POST['solicitudes']);
            $estado = $db->clearText($_POST['estado']);

            if (empty($solicitudes)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una o más solicitudes"]);
                exit;
            }

            foreach ($solicitudes as $s) {
                $id = $db->clearText($s->id_solicitud_compra);

                try {
                    actualizar_estado_solicitud($db, $id, $estado);
                } catch (Exception $e) {
                    $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Estados actualizados correctamente"]);
        break;

        case 'ver_productos':
            $db = DataBase::conectar();
            $id_solicitud_compra = $db->clearText($_GET['id_solicitud_compra']);
            $db->setQuery("SELECT scp.id_solicitud_compra_producto, p.id_producto, p.producto, p.codigo, pre.id_presentacion, pre.presentacion, scp.cantidad,
                            (
                                SELECT IFNULL(SUM(ocp.cantidad), 0)
                                FROM ordenes_compras_productos ocp
                                JOIN ordenes_compras oc ON ocp.id_orden_compra=oc.id_orden_compra
                                WHERE oc.estado!=2 AND ocp.id_solicitud_compra=scp.id_solicitud_compra AND ocp.id_producto=scp.id_producto
                            ) AS orden_compra,
                            scp.cantidad - (SELECT orden_compra) AS pendiente
                            FROM solicitudes_compras_productos scp
                            JOIN productos p ON scp.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            WHERE scp.id_solicitud_compra=$id_solicitud_compra");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

	}

    /**
     * Actualiza el estado de una solicitud
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id
     * @param int $estado
     * @throws Exception Se dispara en caso de que la solicitud no pueda ser editada
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function actualizar_estado_solicitud(DataBase $db, $id, $estado) {
        if (sc_verificar_estado($db, $id, [SC_ESTADO_PARCIAL, SC_ESTADO_TOTAL])) {
            throw new Exception("No es posible modificar el estado de una solicitud procesada");
        }
        if (!sc_actualizar_estado($db, $id, $estado)) {
            throw new Exception("Error de base de datos al actualizar el estado de la solicitud. Code: ".$db->getErrorCode());
        }
    }


?>
