<?php
    /**
     * OC: Orden de compra
     * OCP: Orden de compra producto
     */

    /**
     * @var int Estado de orden de compra 'Pendiente'
     */
    define("OC_ESTADO_PENDIENTE", 0);
    /**
     * @var int Estado de orden de compra 'Aprobado'
     */
    define("OC_ESTADO_APROBADO", 1);
    /**
     * @var int Estado de orden de compra 'Rechazado'
     */
    define("OC_ESTADO_RECHAZADO", 2);
    /**
     * @var int Estado de orden de compra 'Parcial' (Procesado Parcial)
     */
    define("OC_ESTADO_PARCIAL", 3);
    /**
     * @var int Estado de orden de compra 'Total' (Procesado Total)
     */
    define("OC_ESTADO_TOTAL", 4);

    /**
     * @var int Estado de producto en orden de compra 'Pendiente'
     */
    define("OCP_ESTADO_PENDIENTE", 0);
    /**
     * @var int Estado de producto en orden de compra 'Parcial'
     */
    define("OCP_ESTADO_PARCIAL", 1);
    /**
     * @var int Estado de producto en orden de compra 'Total'
     */
    define("OCP_ESTADO_TOTAL", 2);

    /**
     * Retorna la cantidad de un producto que fue cargada en la orden de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_orden_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function ocp_cantidad(DataBase $db, $id_orden_compra, $id_producto) {
        $db->setQuery("SELECT cantidad FROM ordenes_compras_productos WHERE id_orden_compra=$id_orden_compra AND id_producto=$id_producto");
        $row = $db->loadObject();

        if (empty($row)) throw new Exception("Producto $id_producto no encontrado en la orden de compra $id_orden_compra");
        return $row->cantidad;
    }

    /**
     * Retorna la cantidad de un producto que ya ha sido asociada a una recepcion de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_orden_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function ocp_cantidad_en_rc(DataBase $db, $id_orden_compra, $id_producto) {
        $db->setQuery("SELECT IFNULL(SUM(rcp.cantidad), 0) AS cantidad
                        FROM recepciones_compras_productos rcp
                        JOIN recepciones_compras rc ON rcp.id_recepcion_compra=rc.id_recepcion_compra
                        WHERE rc.estado!=2 AND rcp.id_orden_compra=$id_orden_compra AND rcp.id_producto=$id_producto");
        $row = $db->loadObject();
        return $row->cantidad;
    }

    /**
     * Retorna la cantidad de un producto que aún no ha sido asociada a una recepcion de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_orden_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function ocp_cantidad_pendiente(DataBase $db, $id_orden_compra, $id_producto) {
        // Cantidad registrada en la orden de compra
        $cantidad = ocp_cantidad($db, $id_orden_compra, $id_producto);
        // Cantidad cargada en recepciones de compra
        $cantidad_rc = ocp_cantidad_en_rc($db, $id_orden_compra, $id_producto);

        return $cantidad - $cantidad_rc;
    }

    /**
     * Actualiza el estado de un producto dentro de una solicitud
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $estado
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function ocp_actualizar_estado(DataBase $db, $id_orden_compra, $id_producto, $estado) {
        $db->setQuery("UPDATE ordenes_compras_productos SET estado=$estado WHERE id_orden_compra=$id_orden_compra AND id_producto=$id_producto");
        return $db->alter();
    }

    /**
     * Actualiza el estado de un producto dentro de una orden de compra calculándolo de forma automática de acuerdo a las cantidades cargadas en la recepción de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_orden_compra
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function ocp_actualizar_estado_auto(DataBase $db, $id_orden_compra, $id_producto) {
        $cantidad = ocp_cantidad($db, $id_orden_compra, $id_producto);
        $cantidad_rc = ocp_cantidad_en_rc($db, $id_orden_compra, $id_producto);

        if ($cantidad == $cantidad_rc) {
            $estado = OCP_ESTADO_TOTAL;
        } else if ($cantidad_rc > 0) {
            $estado = OCP_ESTADO_PARCIAL;
        } else {
            $estado = OCP_ESTADO_PENDIENTE;
        }

        if (!ocp_actualizar_estado($db, $id_orden_compra, $id_producto, $estado)) {
            throw new Exception("Error de base de datos al actualizar el estado del producto en la orden de compra. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Actualiza el estado de una orden de compra calculándolo de forma automática de acuerdo a los estados de los productos que contiene
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id ID de la orden de compra
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function oc_actualizar_estado_auto(DataBase $db, $id) {
        // Cantidad registrada en la solicitud de compra
        $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad FROM ordenes_compras_productos WHERE id_orden_compra=$id");
        $row = $db->loadObject();
        $cantidad = $row->cantidad;

        // Cantidad cargada en ordenes de compra
        $db->setQuery("SELECT IFNULL(SUM(rcp.cantidad), 0) AS cantidad
                        FROM recepciones_compras_productos rcp
                        JOIN recepciones_compras rc ON rcp.id_recepcion_compra=rc.id_recepcion_compra
                        WHERE rc.estado!=2 AND rcp.id_orden_compra=$id");
        $row = $db->loadObject();
        $cantidad_oc = $row->cantidad;

        // Se actualiza el estado del producto en la solicitud de compra
        if ($cantidad == $cantidad_oc) {
            $estado = OC_ESTADO_TOTAL;
        } else if ($cantidad_oc > 0) {
            $estado = OC_ESTADO_PARCIAL;
        } else {
            $estado = OC_ESTADO_APROBADO;
        }

        if (!oc_actualizar_estado($db, $id, $estado)) {
            throw new Exception("Error de base de datos al actualizar el estado de la orden de compra. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Verifica si una orden de compra posee alguno de los estados indicados
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id
     * @param array|int $estado
     * @throws Exception Se dispara en caso de que la orden de compra no sea encontrada
     */
    function oc_verificar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("SELECT estado FROM ordenes_compras WHERE id_orden_compra=$id");
        $row = $db->loadObject();

        if (empty($row)) {
            throw new Exception("Orden de compra $id no encontrada");
        }

        return ($row->estado == $estado || in_array($row->estado, $estado));
    }

    /**
     * Actualiza el estado de una orden de compra
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id
     * @param array | int $estado
     * @return mixed
     */
    function oc_actualizar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("UPDATE ordenes_compras SET estado=$estado WHERE id_orden_compra=$id");
        return $db->alter();
    }

