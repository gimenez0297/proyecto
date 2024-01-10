<?php
    /**
     * Retorna los datos del turno abierto en una caja
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_caja
     * @return mixed
     */
    function caja_turno(DataBase $db, $id_caja) {
        $db->setQuery("SELECT * FROM cajas_horarios WHERE estado=1 AND id_caja=$id_caja");
        return $db->loadObject();
    }

    /**
     * Retorna los datos del turno de la caja que tiene abierta cada usuario por sucursal
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_sucursal
     * @param int $usuario
     * @return mixed
     */
    function caja_abierta (DataBase $db, $id_sucursal, $usuario) {
        $db->setQuery("SELECT * FROM cajas_horarios WHERE estado=1 AND id_sucursal=$id_sucursal AND usuario='$usuario'");
        return $db->loadObject();
    }
   
    /**
     * Retorna los datos de la caja que tiene abierta cada usuario por sucursal
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_sucursal
     * @param int $usuario
     * @param int $token
     * @return mixed
     */
    function caja_identificacion (DataBase $db, $token) {

        $db->setQuery("SELECT limite_caja FROM configuracion ");
        $limite_caja = $db->loadObject()->limite_caja;
        
        $db->setQuery("SELECT * FROM cajas WHERE estado = 1 AND token='$token' AND '$limite_caja' >= timestampdiff(DAY, ultima_conexion, NOw())");
        return $db->loadObject();
    }
?>
