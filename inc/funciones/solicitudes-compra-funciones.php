<?php
    /**
     * SC: Solicitud de compra
     * SCP: Solicitud de compra producto
     */

    /**
     * @var int Estado de solicitud 'Pendiente'
     */
    define("SC_ESTADO_PENDIENTE", 0);
    /**
     * @var int Estado de solicitud 'Aprobado'
     */
    define("SC_ESTADO_APROBADO", 1);
    /**
     * @var int Estado de solicitud 'Rechazado'
     */
    define("SC_ESTADO_RECHAZADO", 2);
    /**
     * @var int Estado de solicitud 'Parcial' (Procesado Parcial)
     */
    define("SC_ESTADO_PARCIAL", 3);
    /**
     * @var int Estado de solicitud 'Total' (Procesado Total)
     */
    define("SC_ESTADO_TOTAL", 4);

    /**
     * @var int Estado de producto en solicitud 'Pendiente'
     */
    define("SCP_ESTADO_PENDIENTE", 0);
    /**
     * @var int Estado de producto en solicitud 'Parcial'
     */
    define("SCP_ESTADO_PARCIAL", 1);
    /**
     * @var int Estado de producto en solicitud 'Total'
     */
    define("SCP_ESTADO_TOTAL", 2);

    /**
     * Retorna la cantidad de un producto que fue cargada en la solicitud de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function scp_cantidad(DataBase $db, $id_solicitud_compra, $id_producto) {
        $db->setQuery("SELECT cantidad FROM solicitudes_compras_productos WHERE id_solicitud_compra=$id_solicitud_compra AND id_producto=$id_producto");
        $row = $db->loadObject();

        if (empty($row)) throw new Exception("Producto $id_producto no encontrado en la solicitud $id_solicitud_compra");
        return $row->cantidad;
    }

    /**
     * Retorna la cantidad de un producto que ya ha sido asociada a una orden de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function scp_cantidad_en_oc(DataBase $db, $id_solicitud_compra, $id_producto) {
        $db->setQuery("SELECT IFNULL(SUM(ocp.cantidad), 0) AS cantidad
                        FROM ordenes_compras_productos ocp
                        JOIN ordenes_compras oc ON ocp.id_orden_compra=oc.id_orden_compra
                        WHERE oc.estado!=2 AND ocp.id_solicitud_compra=$id_solicitud_compra AND ocp.id_producto=$id_producto");
        $row = $db->loadObject();
        return $row->cantidad;
    }

    /**
     * Retorna la cantidad de un producto que aún no ha sido asociada a una orden de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function scp_cantidad_pendiente(DataBase $db, $id_solicitud_compra, $id_producto) {
        // Cantidad registrada en la solicitud de compra
        $cantidad = scp_cantidad($db, $id_solicitud_compra, $id_producto);
        // Cantidad cargada en ordenes de compra
        $cantidad_oc = scp_cantidad_en_oc($db, $id_solicitud_compra, $id_producto);

        return $cantidad - $cantidad_oc;
    }

    /**
     * Actualiza el estado de un producto dentro de una solicitud
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $estado
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function scp_actualizar_estado(DataBase $db, $id_solicitud_compra, $id_producto, $estado) {
        $db->setQuery("UPDATE solicitudes_compras_productos SET estado=$estado WHERE id_solicitud_compra=$id_solicitud_compra AND id_producto=$id_producto");
        return $db->alter();
    }

    /**
     * Actualiza el estado de un producto dentro de una solicitud calculándolo de forma automática de acuerdo a las cantidades cargadas en la orden de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function scp_actualizar_estado_auto(DataBase $db, $id_solicitud_compra, $id_producto) {
        $cantidad = scp_cantidad($db, $id_solicitud_compra, $id_producto);
        $cantidad_oc = scp_cantidad_en_oc($db, $id_solicitud_compra, $id_producto);

        if ($cantidad == $cantidad_oc) {
            $estado = SCP_ESTADO_TOTAL;
        } else if ($cantidad_oc > 0) {
            $estado = SCP_ESTADO_PARCIAL;
        } else {
            $estado = SCP_ESTADO_PENDIENTE;
        }

        if (!scp_actualizar_estado($db, $id_solicitud_compra, $id_producto, $estado)) {
            throw new Exception("Error de base de datos al actualizar el estado del producto en la solicitud de compra. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Actualiza el estado de un producto a Procesado Total dentro de una solicitud, verifica si es que no fue finalizado cargando los productos en órdenes de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_compra
     * @param int $id_producto
     * @param string $usuario
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function scp_finalizar(DataBase $db, $id_solicitud_compra, $id_producto, $usuario) {
        $db->setQuery("SELECT estado FROM solicitudes_compras_productos WHERE id_solicitud_compra=$id_solicitud_compra AND id_producto=$id_producto");
        $row = $db->loadObject();
        $estado = $row->estado;

        if ($estado == SCP_ESTADO_TOTAL) {
            throw new Exception("Todos los productos de la solicitud fueron agregados en órdenes de compra");
        }

        $db->setQuery("UPDATE solicitudes_compras_productos SET estado=".SCP_ESTADO_TOTAL.", usuario_fin='$usuario', fecha_fin=NOW() WHERE id_solicitud_compra=$id_solicitud_compra AND id_producto=$id_producto");
        if (!$db->alter()) {
            throw new Exception("Error de base de datos al actualizar el estado del producto a finalizado en la solicitud de compra. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Actualiza el estado de una solicitud calculándolo de forma automática de acuerdo a los estados de los productos que contiene
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id ID de la solicitud de compra
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sc_actualizar_estado_auto(DataBase $db, $id) {
        // Cantidad registrada en la solicitud de compra
        $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad FROM solicitudes_compras_productos WHERE id_solicitud_compra=$id");
        $row = $db->loadObject();
        $cantidad = $row->cantidad;

        // Productos finalizados
        $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad FROM solicitudes_compras_productos WHERE estado=".SCP_ESTADO_TOTAL." AND id_solicitud_compra=$id");
        $row = $db->loadObject();
        $cantidad_finalizado = $row->cantidad;

        // Productos parciales
        $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad FROM solicitudes_compras_productos WHERE estado=".SCP_ESTADO_PARCIAL." AND id_solicitud_compra=$id");
        $row = $db->loadObject();
        $cantidad_parcial = $row->cantidad;

        // Se actualiza el estado del producto en la solicitud de compra
        if ($cantidad == $cantidad_finalizado) {
            $estado = SC_ESTADO_TOTAL;
        } else if ($cantidad_parcial > 0) {
            $estado = SC_ESTADO_PARCIAL;
        } else {
            $estado = SC_ESTADO_APROBADO;
        }

        if (!sc_actualizar_estado($db, $id, $estado)) {
            throw new Exception("Error de base de datos al actualizar el estado de la solicitud. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Verifica si una solicitud posee alguno de los estados indicados
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id
     * @param array|int $estado
     * @throws Exception Se dispara en caso de que la solicitud no sea encontrada
     */
    function sc_verificar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("SELECT estado FROM solicitudes_compras WHERE id_solicitud_compra=$id");
        $row = $db->loadObject();

        if (empty($row)) {
            throw new Exception("Solicitud $id no encontrada");
        }

        return ($row->estado == $estado || in_array($row->estado, $estado));
    }

    /**
     * Actualiza el estado de una solicitud en base de datos
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id
     * @param array | int $estado
     * @return mixed
     */
    function sc_actualizar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("UPDATE solicitudes_compras SET estado=$estado WHERE id_solicitud_compra=$id");
        return $db->alter();
    }

