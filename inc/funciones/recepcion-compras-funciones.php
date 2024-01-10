<?php
    /**
     * RC: Recepción de compra
     * RCP: Recepción de compra producto
     */

    /**
     * @var int Estado de recepción 'Recepcionado'
     */
    define("OC_ESTADO_RECEPCIONADO", 1);
    /**
     * @var int Estado de recepción 'Rechazado'
     */
    define("RC_ESTADO_RECHAZADO", 2);

    /**
     * Verifica si una recepción posee alguno de los estados indicados
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id
     * @param array|int $estado
     * @throws Exception Se dispara en caso de que la recepción no sea encontrada
     */
    function rc_verificar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("SELECT estado FROM recepciones_compras WHERE id_recepcion_compra=$id");
        $row = $db->loadObject();

        if (empty($row)) {
            throw new Exception("Recepción de compras $id no encontrada");
        }

        return ($row->estado == $estado || in_array($row->estado, $estado));
    }

    /**
     * Actualiza el estado de una recepción de compras
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id
     * @param array | int $estado
     * @return mixed
     */
    function rc_actualizar_estado(DataBase $db, $id, $estado) {
        $db->setQuery("UPDATE recepciones_compras SET estado=$estado WHERE id_recepcion_compra=$id");
        return $db->alter();
    }

