<?php
    /**
     * NR: Nota de remision
     * SD: Solicitud a deposito
     */

    /**
     * @var int Estado de solicitud 'Pendiente'
     */
    define("SD_ESTADO_PENDIENTE", 0);
    /**
     * @var int Estado de solicitud 'Aprobado'
     */
    define("SD_ESTADO_APROBADO", 1);
    /**
     * @var int Estado de solicitud 'Rechazado'
     */
    define("SD_ESTADO_RECHAZADO", 2);
    /**
     * @var int Estado de solicitud 'Parcial' (Procesado Parcial)
     */
    define("SD_ESTADO_PARCIAL", 3);
    /**
     * @var int Estado de solicitud 'Total' (Procesado Total)
     */
    define("SD_ESTADO_TOTAL", 4);

    /**
     * @var int Estado de producto en solicitud 'Pendiente'
     */
    define("SDP_ESTADO_PENDIENTE", 0);
    /**
     * @var int Estado de producto en solicitud 'Parcial'
     */
    define("SDP_ESTADO_PARCIAL", 1);
    /**
     * @var int Estado de producto en solicitud 'Total'
     */
    define("SDP_ESTADO_TOTAL", 2);

    /**
     * Retorna la cantidad de un producto que fue cargada en la solicitud de deposito
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_deposito
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sdp_cantidad(DataBase $db, $id_solicitud_deposito, $id_producto) {
        $db->setQuery("SELECT cantidad FROM solicitudes_depositos_productos WHERE id_solicitud_deposito=$id_solicitud_deposito AND id_producto=$id_producto");
        $row = $db->loadObject();

        if (empty($row)) throw new Exception("Producto $id_producto no encontrado en la solicitud $id_solicitud_deposito");
        return $row->cantidad;
    }

    /**
     * Retorna la cantidad de un producto que ya ha sido asociada a una nota de remision
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_deposito
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sdp_cantidad_en_nr(DataBase $db, $id_solicitud_deposito, $id_producto) {
        $db->setQuery("SELECT IFNULL(SUM(nrp.cantidad), 0) as cantidad
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado !=2  AND nrp.id_solicitud_deposito=$id_solicitud_deposito AND nrp.id_producto=$id_producto");
        $row = $db->loadObject();
        return $row->cantidad;
    }

    /**
     * Retorna la cantidad de un producto que aún no ha sido asociada a una nota de remision
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_deposito
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sdp_cantidad_pendiente(DataBase $db, $id_solicitud_deposito, $id_producto) {
        // Cantidad registrada en la solicitud de deposito
        $cantidad = sdp_cantidad($db, $id_solicitud_deposito, $id_producto);
        // Cantidad cargada en notas de remision
        $cantidad_nr = sdp_cantidad_en_nr($db, $id_solicitud_deposito, $id_producto);

        return $cantidad - $cantidad_nr;
    }

    /**
     * Actualiza el estado de un producto dentro de una solicitud
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $estado
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sdp_actualizar_estado(DataBase $db, $id_solicitud_deposito, $id_producto, $estado) {
        $db->setQuery("UPDATE solicitudes_depositos_productos SET estado=$estado WHERE id_solicitud_deposito=$id_solicitud_deposito AND id_producto=$id_producto");
        return $db->alter();
    }

    /**
     * Actualiza el estado de un producto dentro de una solicitud calculándolo de forma automática de acuerdo a las cantidades cargadas en la solicitud de deposito
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_deposito
     * @param int $id_producto
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sdp_actualizar_estado_auto(DataBase $db, $id_solicitud_deposito, $id_producto) {
        $cantidad = sdp_cantidad($db, $id_solicitud_deposito, $id_producto);
        $cantidad_nr = sdp_cantidad_en_nr($db, $id_solicitud_deposito, $id_producto);

        if ($cantidad == $cantidad_nr) {
            $estado = SDP_ESTADO_TOTAL;
        } else if ($cantidad_nr > 0) {
            $estado = SDP_ESTADO_PARCIAL;
        } else {
            $estado = SDP_ESTADO_PENDIENTE;
        }

        if (!sdp_actualizar_estado($db, $id_solicitud_deposito, $id_producto, $estado)) {
            throw new Exception("Error de base de datos al actualizar el estado del producto en la solicitud de deposito. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Actualiza el estado de un producto a Procesado Total dentro de una solicitud, verifica si es que no fue finalizado cargando los productos en solicitudes de deposito
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_solicitud_deposito
     * @param int $id_producto
     * @param string $usuario
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sdp_finalizar(DataBase $db, $id_solicitud_deposito, $id_producto, $usuario) {
        $db->setQuery("SELECT estado FROM solicitudes_depositos_productos WHERE id_solicitud_compra=$id_solicitud_deposito AND id_producto=$id_producto");
        $row = $db->loadObject();
        $estado = $row->estado;

        if ($estado == SDP_ESTADO_TOTAL) {
            throw new Exception("Todos los productos de la solicitud fueron agregados en notas de remision");
        }

        $db->setQuery("UPDATE solicitudes_depositos_productos SET estado=".SDP_ESTADO_TOTAL.", usuario_fin='$usuario', fecha_fin=NOW() WHERE id_solicitud_deposito=$id_solicitud_deposito AND id_producto=$id_producto");
        if (!$db->alter()) {
            throw new Exception("Error de base de datos al actualizar el estado del producto a finalizado en la solicitud de deposito. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Actualiza el estado de una solicitud calculándolo de forma automática de acuerdo a los estados de los productos que contiene
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id ID de la solicitud de deposito
     * @throws Exception Se dispara en caso de que ocurra un error de base de datos 
     */
    function sd_actualizar_estado_auto(DataBase $db, $id) {
        // Cantidad registrada en la solicitud de compra
        $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad FROM solicitudes_depositos_productos WHERE id_solicitud_deposito=$id");
        $row = $db->loadObject();
        $cantidad = $row->cantidad;

        // Productos finalizados
        $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad FROM solicitudes_depositos_productos WHERE estado=".SDP_ESTADO_TOTAL." AND id_solicitud_deposito=$id");
        $row = $db->loadObject();
        $cantidad_finalizado = $row->cantidad;

        // Productos parciales
        $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad FROM solicitudes_depositos_productos WHERE estado=".SDP_ESTADO_PARCIAL." AND id_solicitud_deposito=$id");
        $row = $db->loadObject();
        $cantidad_parcial = $row->cantidad;

        // Se actualiza el estado del producto en la solicitud de deposito
        if ($cantidad == $cantidad_finalizado) {
            $estado = SD_ESTADO_TOTAL;
        } else if ($cantidad_parcial > 0) {
            $estado = SD_ESTADO_PARCIAL;
        } else {
            $estado = SD_ESTADO_APROBADO;
        }

        if (!sd_actualizar_estado($db, $id, $estado)) {
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
    function sd_verificar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("SELECT estado FROM solicitudes_depositos WHERE id_solicitud_deposito=$id");
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
    function sd_actualizar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("UPDATE solicitudes_depositos SET estado=$estado WHERE id_solicitud_deposito=$id");
        return $db->alter();
    }