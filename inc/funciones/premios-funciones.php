<?php
    define("ADD", "ADD");     // ENTRADA
    define("SUB", "SUB");       //SALIDA
    define("CAR","CAR");        //CARGA PREMIO 
    define("ANU","ANU");        //ANULAR CARGA PREMIO
    define("CANJ","CANJ");     //CANJE
    define("ADC","ADC");     //ADMINISTRACION CANJE

    /**
     * Retorna el stock de un premio por id_premio 
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_premio
     * @return mixed
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function premio_stock(DataBase $db, $id_premio) {
        // $and_vencimiento = 'AND vencimiento IS NULL';
        // if (!empty($vencimiento)) {
        //     $and_vencimiento = " AND vencimiento='$vencimiento'";
        // }

        $db->setQuery("SELECT stock, id_stock_premio FROM stock_premios WHERE id_premio=$id_premio");
        $row = $db->loadObject();


        if ($db->error()) throw new Exception("Error de base de datos al recuperar el stock");

        return $row;
    }

        /**
     * Actualiza el stock de un premio insumo por id_premio
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_premio
     * @param int $stock
     * @return mixed
     */
    function premio_actualizar_stock_premio(DataBase $db, $id_premio, $stock ,$id_stock_premio) {
        // $vencimiento_null = "'$vencimiento'";

        // if (empty($vencimiento)) {
        //     $vencimiento_null = 'NULL'; 
        // }

        if ($id_stock_premio > 0) {
            $db->setQuery("UPDATE `stock_premios` SET `stock` = '$stock' WHERE `id_stock_premio` = '$id_stock_premio';");
        }else{
            $db->setQuery("INSERT INTO `stock_premios` (`id_premio`,`stock` ) VALUES(' $id_premio ', ' $stock ' );");
        }
        return $db->alter();
    }

    /**
     * Suma al stock de un premio por id_premio
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_premio
     * @param int $stock
     * @param int $sumar
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function premio_sumar_stock(DataBase $db, $id_premio, $cantidad) {
        $stock_actual = premio_stock($db, $id_premio);
        $id_stock_premio = $stock_actual->id_stock_premio;
        if ( !premio_actualizar_stock_premio($db, $id_premio, intval($stock_actual->stock) + intval($cantidad),$id_stock_premio)){
            throw new Exception("Error de base de datos al actualizar el stock. Code: ".$db->getErrorCode());     
        }
    }

    /**
     * Resta al stock de un premio por id_premio
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_premio
     * @param int $stock
     * @param int $restar
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function premio_restar_stock(DataBase $db, $id_premio, $cantidad) {
        $stock_actual = premio_stock($db, $id_premio);
        $id_stock_premio = $stock_actual->id_stock_premio;
        if ( !premio_actualizar_stock_premio($db, $id_premio, intval($stock_actual->stock) - intval($cantidad),$id_stock_premio)){
            throw new Exception("Error de base de datos al actualizar el stock. Code: ".$db->getErrorCode());     
        }
    }

       /**
     * Registra el historial de stock_premios_historial
     * @param DataBase $db Instancia de la Base de Datos.
     * @param object $historial Datos a insertar en el stock_premios_historial
     *  * `int id_premio` 
     *  * `string premio` 
     *  * `string operacion`
     *  * `int id_origen`
     *  * `string origen`
     *  * `string detalles`
     *  * `string usuario`
     *  * `date fecha`
     * @return mixed
     */
    function stock_historial_premio(DataBase $db, object $historial) {
       
        // Historial
        $id_premio = $historial->id_premio;
        $premio = $historial->premio;
        $cantidad = $historial->cantidad;
        $operacion = $historial->operacion;
        $id_origen = $historial->id_origen;
        $origen = $historial->origen;
        $detalles = $historial->detalles;
        $usuario = $historial->usuario;

        $db->setQuery("SELECT premio FROM premios WHERE id_premio=$id_premio");
        $premio = $db->loadObject()->premio;
        // $db->setQuery("SELECT vencimiento FROM cargas_productos_insumos WHERE id_sucursal=$id_sucursal");
        // $vencimiento = $db->loadObject()->vencimiento;

        $db->setQuery("INSERT INTO stock_premios_historial(
                            id_premio, 
                            premio, 
                            cantidad,
                            operacion,
                            id_origen, 
                            origen,
                            detalles, 
                            usuario,
                            fecha)
                        VALUES (
                            $id_premio, 
                            '$premio',
                            '$cantidad', 
                            '$operacion',
                            $id_origen,
                            '$origen',
                            '$detalles', 
                            '$usuario', 
                            NOW())");
        return $db->alter();
    }