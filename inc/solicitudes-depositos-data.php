<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;
    $id_rol = datosUsuario($usuario)->id_rol;


    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();
            $where = "";
            $where_sucursal .= "";

            if (esAdmin($id_rol) === false) {
                $where_sucursal .= " AND (sd.id_sucursal=$id_sucursal OR sd.id_deposito = $id_sucursal )"; 
            }

            //Parametros de ordenamiento, busqueda y paginacion
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', sucursal, fecha, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            sd.id_solicitud_deposito,
                            sd.id_deposito,
                            sl.sucursal AS deposito,
                            s.id_sucursal,
                            s.sucursal AS sucursal,
                            sd.observacion,
                            sd.numero,
                            sd.estado,
                            CASE sd.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Aprobado' WHEN 2 THEN 'Rechazado' WHEN 3 THEN 'Procesado Parcial' WHEN 4 THEN 'Procesado Total' ELSE '' END AS estado_str,
                            sd.usuario,
                            sd.id_proveedor,
                            p.proveedor,
                            DATE_FORMAT(sd.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM solicitudes_depositos sd
                            JOIN sucursales s ON sd.id_sucursal=s.id_sucursal
                            JOIN sucursales sl ON sd.id_deposito = sl.id_sucursal
                            JOIN proveedores p ON p.id_proveedor = sd.id_proveedor
                            WHERE sd.fecha BETWEEN '$desde' AND '$hasta' $having $where_sucursal
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
                actualizar_estado($db, $id, $estado);
                echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
            }

        break;

        case 'cambiar-estados':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $rows = json_decode($_POST['rows']);
            $estado = $db->clearText($_POST['estado']);

            if (empty($rows)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una o más solicitudes"]);
                exit;
            }

            foreach ($rows as $r) {
                $id = $db->clearText($r->id_solicitud_deposito);

                try {
                    actualizar_estado($db, $id, $estado);
                } catch (Exception $e) {
                    $db->rollback(); // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Estados actualizados correctamente"]);
        break;

        case 'ver_detalle':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);

            $db->setQuery("SELECT 
                            sdp.id_solicitud_deposito_producto, 
                            p.id_producto, 
                            p.producto, 
                            p.codigo, 
                            pre.id_presentacion, 
                            pre.presentacion, 
                            sdp.cantidad,
                            sdp.cantidad - (SELECT IFNULL(SUM(cantidad), 0)
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr  ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado !=2  AND nrp.id_solicitud_deposito=sdp.id_solicitud_deposito AND nrp.id_producto=sdp.id_producto
                                ) AS pendiente,
                                (SELECT IFNULL(SUM(cantidad_recibida), 0)
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr  ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado !=2  AND nrp.id_solicitud_deposito=sdp.id_solicitud_deposito AND nrp.id_producto=sdp.id_producto
                                ) AS recibido,
                                (SELECT IFNULL(SUM(cantidad), 0)
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr  ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado =1  AND nrp.id_solicitud_deposito=sdp.id_solicitud_deposito AND nrp.id_producto=sdp.id_producto
                                ) AS transito
                            FROM solicitudes_depositos_productos sdp
                            JOIN productos p ON sdp.id_producto=p.id_producto
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            WHERE sdp.id_solicitud_deposito=$id");
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
    function actualizar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("SELECT estado FROM solicitudes_depositos WHERE id_solicitud_deposito=$id");
        $row = $db->loadObject();

        if (empty($row)) {
            throw new Exception("Solicitud $id no encontrada");
        }

        if ($row->estado == 3 || $row->estado == 4) {
            throw new Exception("No es posible modificar el estado de una solicitud procesada");
        }

        $db->setQuery("UPDATE solicitudes_depositos SET estado=$estado WHERE id_solicitud_deposito=$id");
        if (!$db->alter()) {
            throw new Exception("Error de base de datos al actualizar el estado de la solicitud. Code: ".$db->getErrorCode());
        }
    }

?>
