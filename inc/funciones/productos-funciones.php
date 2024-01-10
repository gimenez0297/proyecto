<?php
    define("ADD", "ADD");
    define("SUB", "SUB");
    define("REC", "REC"); // Recepción de compras
    define("INV", "INV"); // Inventario
    define("FAC", "FAC"); // Factura
    define("AJU", "AJU"); // Ajuste de stock
    define("EAF", "EAF"); // Entero a fraccionado
    define("FAE", "FAE"); // Fraccionado a entero
    define("REM", "REM"); // Nota de remisión
    define("CRED", "CRED"); // Nota de crédito

    /**
     * Actualiza el costo de un producto
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $id_proveedor
     * @param int $costo
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_actualizar_costo(DataBase $db, $id_producto, $id_proveedor, $costo, $costo_ultimo)
    {
        $db->setQuery("SELECT id_producto_proveedor FROM productos_proveedores WHERE id_producto=$id_producto AND id_proveedor=$id_proveedor");
        $row = $db->loadObject();
        if (isset($row)) {
            $db->setQuery("UPDATE productos_proveedores SET costo=$costo, costo_ultimo=$costo_ultimo WHERE id_producto_proveedor=$row->id_producto_proveedor");
        } else {
            $db->setQuery("INSERT productos_proveedores (id_producto, id_proveedor, costo, costo_ultimo) VALUES($id_producto, $id_proveedor, $costo, $costo_ultimo)");
        }

        if (!$db->alter()) {
            throw new Exception("Error de base de datos al actualizar el costo del producto. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Actualiza el stock de un producto por sucursal y lote
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $id_sucursal
     * @param int $id_lote
     * @param int $stock
     * @return mixed
     */
    function producto_actualizar_stock(DataBase $db, $id_producto, $id_sucursal, $id_lote, $stock, $fraccionado) {
        $db->setQuery("INSERT INTO `stock` (
                            id_producto,
                            id_sucursal,
                            id_lote,
                            stock,
                            fraccionado
                        ) VALUES (
                            $id_producto,
                            $id_sucursal,
                            $id_lote,
                            $stock,
                            $fraccionado
                        ) ON DUPLICATE KEY UPDATE 
                            stock=$stock,
                            fraccionado=$fraccionado");
        return $db->alter();
    }

    /**
     * Retorna el stock de un producto por sucursal y lote
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $id_sucursal
     * @param int $id_lote
     * @return mixed
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_stock(DataBase $db, $id_producto, $id_sucursal, $id_lote) {
        $db->setQuery("SELECT stock, fraccionado FROM stock WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal AND id_lote=$id_lote");
        $row = $db->loadObject();

        if ($db->error()) throw new Exception("Error de base de datos al recuperar el stock");

        return $row;
    }

    /**
     * Retorna el stock de un producto por sucursal
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $id_sucursal
     * @param boolean $incluir_vencidos
     * @return mixed
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_stock_sucursal(DataBase $db, $id_producto, $id_sucursal, $incluir_vencidos = false) {
        if ($incluir_vencidos == false) {
            $where_vencimiento = "AND l.vencimiento >= CURRENT_DATE()";
        }else{
            $where_vencimiento = "";
        }

        $db->setQuery("SELECT IFNULL(SUM(s.stock), 0) AS stock, IFNULL(SUM(s.fraccionado), 0) AS fraccionado 
                        FROM stock s
                        JOIN lotes l ON s.id_lote=l.id_lote $where_vencimiento
                        WHERE id_producto=$id_producto AND id_sucursal=$id_sucursal");
        $row = $db->loadObject();

        if ($db->error()) throw new Exception("Error de base de datos al recuperar el stock");

        return $row;
    }

    /**
     * Suma al stock de un producto por sucursal y lote
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $id_sucursal
     * @param int $id_lote
     * @param int $sumar
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_sumar_stock(DataBase $db, $id_producto, $id_sucursal, $id_lote, $stock, $fraccionado) {
        $stock_actual = producto_stock($db, $id_producto, $id_sucursal, $id_lote);
        if (!producto_actualizar_stock($db, $id_producto, $id_sucursal, $id_lote, intval($stock_actual->stock) + abs($stock), ($stock_actual->fraccionado) + abs($fraccionado))) {
            throw new Exception("Error de base de datos al actualizar el stock. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Resta al stock de un producto por sucursal y lote
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $id_sucursal
     * @param int $id_lote
     * @param int $restar
     * @throws Exception En caso de que ocurra un error de base de datos 
     */
    function producto_restar_stock(DataBase $db, $id_producto, $id_sucursal, $id_lote, $stock, $fraccionado) {
        $stock_actual = producto_stock($db, $id_producto, $id_sucursal, $id_lote);
        if (!producto_actualizar_stock($db, $id_producto, $id_sucursal, $id_lote, intval($stock_actual->stock) - abs($stock), ($stock_actual->fraccionado) - abs($fraccionado))) {
            throw new Exception("Error de base de datos al actualizar el stock. Code: ".$db->getErrorCode());
        }
    }

    /**
     * Registra el historial de stock
     * @param DataBase $db Instancia de la Base de Datos.
     * @param object $stock Producto, sucursal, lote
     *  * `int id_producto`
     *  * `int id_sucursal`
     *  * `int id_lote`
     * @param object $historial Datos a insertar en el historial
     *  * `int cantidad` 
     *  * `int fraccionado` 
     *  * `string operacion`
     *  * `int id_origen`
     *  * `string origen`
     *  * `string detalles`
     *  * `string usuario`
     * @return mixed
     */
    function stock_historial(DataBase $db, object $stock, object $historial) {
        // Stock
        $id_producto = $stock->id_producto;
        $id_sucursal = $stock->id_sucursal;
        $id_lote = $stock->id_lote;

        // Historial
        $cantidad = $historial->cantidad;
        $fraccionado = $historial->fraccionado;
        $operacion = $db->clearText($historial->operacion);
        $id_origen = $historial->id_origen;
        $origen = $db->clearText($historial->origen);
        $detalles = $db->clearText($historial->detalles);
        $usuario = $db->clearText($historial->usuario);

        $db->setQuery("SELECT producto FROM productos WHERE id_producto=$id_producto");
        $producto = $db->clearText($db->loadObject()->producto);
        $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
        $sucursal = $db->clearText($db->loadObject()->sucursal);
        $db->setQuery("SELECT lote FROM lotes WHERE id_lote=$id_lote");
        $lote = $db->clearText($db->loadObject()->lote);

        $db->setQuery("INSERT INTO stock_historial(id_producto, producto, id_sucursal, sucursal, id_lote, lote, cantidad, fraccionado, operacion, origen, id_origen, detalles, usuario, fecha)
                        VALUES ($id_producto, '$producto', $id_sucursal, '$sucursal', $id_lote, '$lote', $cantidad, $fraccionado, '$operacion', '$origen', $id_origen, '$detalles', '$usuario', NOW())");
        return $db->alter();
    }

    /**
     * Verifica si un producto ha alcanzado alguno de los niveles configurados, en caso de que si se registra la notificación
     * @param DataBase $db Instancia de la Base de Datos.
     * @param int $id_producto
     * @param int $id_sucursal
     */
    function producto_verificar_niveles_stock(DataBase $db, $id_producto, $id_sucursal) {
        $db->setQuery("SELECT id_sucursal, sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
        $sucursal = $db->loadObject()->sucursal;

        $db->setQuery("SELECT 
                        p.id_producto,
                        p.codigo,
                        p.producto,
                        (
                            SELECT 
                            IFNULL(SUM(s.stock), 0)
                            FROM stock s 
                            JOIN lotes l ON s.id_lote=l.id_lote
                            WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
                        ) AS stock,
                        IFNULL(sn.minimo, 0) AS minimo,
                        IFNULL(sn.maximo, 0) AS maximo
                    FROM productos p
                    LEFT JOIN stock_niveles sn ON p.id_producto=sn.id_producto AND sn.id_sucursal=$id_sucursal
                    WHERE p.id_producto=$id_producto");
        $row = $db->loadObject();

        $producto = $row->producto;
        $codigo = $row->codigo;
        $stock = $row->stock;
        $minimo = $row->minimo;
        $maximo = $row->maximo;

        if ($stock <= $minimo) {
            $titulo = $db->clearText("Stock mínimo");
            $descripcion = $db->clearText("El producto '$producto' ($codigo) ha alcanzado el stock mínimo (".separadorMiles($minimo).") en la sucursal '$sucursal'");
            $db->setQuery("INSERT INTO notificaciones (titulo, descripcion, fecha) VALUES('$titulo', '$descripcion', NOW())");
            return $db->alter();
        }
        if ($stock >= $maximo && $maximo > 0) {
            $titulo = $db->clearText("Stock máximo");
            $descripcion = $db->clearText("El producto '$producto' ($codigo) ha alcanzado el stock máximo (".separadorMiles($minimo).") en la sucursal '$sucursal'");
            $db->setQuery("INSERT INTO notificaciones (titulo, descripcion, fecha) VALUES('$titulo', '$descripcion', NOW())");
            return $db->alter();
        }

        return false;

    }

