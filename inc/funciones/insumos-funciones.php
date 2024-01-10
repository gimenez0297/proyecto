<?php
    define("ADD", "ADD");     // ENTRADA
    define("SUB", "SUB");       //SALIDA
    define("CAR","CAR");        //CARGA INSUMO 
    define("REM","REM");        //REMISION

    /**
     * Retorna el stock de un producto por id_producto insumo y vencimiento
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto_insumo
     * @param date $vencimiento
     * @return mixed
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_insumo_stock(DataBase $db, $id_producto_insumo, $vencimiento) {
                    
        $and_vencimiento = 'AND vencimiento IS NULL';
        if (!empty($vencimiento)) {
            $and_vencimiento = " AND vencimiento='$vencimiento'";
        }

        $db->setQuery("SELECT stock, id_stock_insumo FROM stock_insumos WHERE id_producto_insumo=$id_producto_insumo $and_vencimiento ");
        $row = $db->loadObject();

        if ($db->error()) throw new Exception("Error de base de datos al recuperar el stock");

        return $row;
    }

        /**
     * Actualiza el stock de un producto insumo por id_producto_insumo y vencimiento
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto_insumo
     * @param date $vencimiento
     * @param int $stock
     * @return mixed
     */
    function producto_actualizar_stock_insumo(DataBase $db, $id_producto_insumo, $vencimiento, $stock,$id_stock_insumo) {
        $vencimiento_null = "'$vencimiento'";

        if (empty($vencimiento)) {
            $vencimiento_null = 'NULL'; 
        }

        if ($id_stock_insumo) {
            $db->setQuery("UPDATE `stock_insumos` SET `stock` = '$stock' WHERE `id_stock_insumo` = '$id_stock_insumo';");
        }else{
            $db->setQuery("INSERT INTO `stock_insumos` (`id_producto_insumo`,`stock`,`vencimiento`) VALUES('$id_producto_insumo','$stock',$vencimiento_null);");
        }
        return $db->alter();
    }

    /**
     * Suma al stock de un producto insumo por id_producto_insumo y vencimiento
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto_insumo
     * @param date $vencimiento
     * @param int $stock
     * @param int $sumar
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_insumo_sumar_stock(DataBase $db, $id_producto_insumo, $vencimiento, $cantidad) {
        $stock_actual = producto_insumo_stock($db, $id_producto_insumo , $vencimiento);
        $id_stock_insumo = $stock_actual->id_stock_insumo;
        if ( !producto_actualizar_stock_insumo($db, $id_producto_insumo, $vencimiento, intval($stock_actual->stock) + intval($cantidad),$id_stock_insumo)){
            throw new Exception("Error de base de datos al actualizar el stock. Code: ".$db->getErrorCode());     
        }
    }

   /**
     * Resta al stock de un producto insumo por id_producto_insumo y vencimiento
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto_insumo
     * @param date $vencimiento
     * @param int $stock
     * @param int $restar
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_insumo_restar_stock(DataBase $db, $id_producto_insumo, $vencimiento, $cantidad) {
        $stock_actual = producto_insumo_stock($db, $id_producto_insumo , $vencimiento);
        $id_stock_insumo = $stock_actual->id_stock_insumo;
        if ( !producto_actualizar_stock_insumo($db, $id_producto_insumo, $vencimiento, intval($stock_actual->stock) -  intval($cantidad),$id_stock_insumo)){
            throw new Exception("Error de base de datos al actualizar el stock. Code: ".$db->getErrorCode());     
        }
    }

       /**
     * Registra el historial de stock
     * @param DataBase $db Instancia de la Base de Datos.
     * @param object $historial Datos a insertar en el stock_insumos_historial
     *  * `int id_producto_insumo` 
     *  * `string producto` 
     *  * `string operacion`
     *  * `int id_origen`
     *  * `string origen`
     *  * `string detalles`
     *  * `string usuario`
     *  * `date fecha`
     * @return mixed
     */
    function stock_historial_insumo(DataBase $db, object $historial) {
       
        // Historial
        $id_producto_insumo = $historial->id_producto_insumo;
        $producto = $historial->producto;
        $vencimiento = $historial->vencimiento ?: 'NULL';
        $operacion = $historial->operacion;
        $id_origen = $historial->id_origen;
        $origen = $historial->origen;
        $detalles = $historial->detalles;
        $usuario = $historial->usuario;

        $db->setQuery("SELECT producto FROM productos_insumo WHERE id_producto_insumo=$id_producto_insumo");
        $producto = $db->loadObject()->producto;
        // $db->setQuery("SELECT vencimiento FROM cargas_productos_insumos WHERE id_sucursal=$id_sucursal");
        // $vencimiento = $db->loadObject()->vencimiento;

        $db->setQuery("INSERT INTO stock_insumos_historial(
                            id_producto_insumo, 
                            producto, 
                            vencimiento, 
                            operacion,
                            id_origen, 
                            origen,
                            detalles, 
                            usuario,
                            fecha)
                        VALUES (
                            $id_producto_insumo, 
                            '$producto', 
                            $vencimiento, 
                            '$operacion',
                            $id_origen,
                            '$origen',
                            '$detalles', 
                            '$usuario', 
                            NOW())");
        return $db->alter();
    }
