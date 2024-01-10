<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q            = $_REQUEST['q'];
$usuario      = $auth->getUsername();
$datosUsuario = datosUsuario($usuario);
$id_sucursal  = $datosUsuario->id_sucursal;
$id_rol       = $datosUsuario->rol;

switch ($q) {

    case 'ver':
        $db       = DataBase::conectar();
        $sucursal = $db->clearText($_GET['id_sucursal']);
        $desde    = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta    = $db->clearText($_REQUEST['hasta']) . " 23:59:59";

        // Si es admin puede filtrar por sucursal
        if ($id_rol != 1) {
            $id_sucursal = $db->clearText($_GET['id_sucursal']);
        }

        if (!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
        } else {
            $where_sucursal .= "";
        }
        ;

        $where = "";

        // Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', descripcion, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            i.id_ajuste,
                            i.numero,
                            i.descripcion,
                            s.id_sucursal,
                            s.sucursal,
                            i.fecha,
                            DATE_FORMAT(i.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            i.estado,
                            CASE i.estado WHEN 1 THEN 'Procesado' WHEN 2 THEN 'Anulado' WHEN 0 THEN 'Pendiente' END AS estado_str,
                            i.usuario,
                            DATE_FORMAT(i.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM ajuste_stock i
                            JOIN sucursales s ON i.id_sucursal=s.id_sucursal
                            WHERE i.fecha BETWEEN '$desde' AND '$hasta' $where_sucursal
                            $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
        $rows = $db->loadObjectList();

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total     = $total_row->total;

        if ($rows) {
            $salida = array('total' => $total, 'rows' => $rows);
        } else {
            $salida = array('total' => 0, 'rows' => array());
        }

        echo json_encode($salida);
        break;

    case 'cargar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id_sucursal = $db->clearText($_POST['sucursal']);
        $descripcion =  mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
        $detalles    = json_decode($_POST['detalles'], true);

        if (empty($id_sucursal)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una sucursal"]);
            exit;
        }
        if (empty($descripcion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el ajuste"]);
            exit;
        }

        $db->setQuery("SELECT MAX(numero) AS numero FROM ajuste_stock");
        $row = $db->loadObject();

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
            exit;
        }

        $sgte_numero = intval($row->numero) + 1;
        $numero      = zerofill($sgte_numero);

        $db->setQuery("INSERT INTO ajuste_stock (numero, id_sucursal, descripcion, estado, usuario, fecha)
                            VALUES ('$numero','$id_sucursal','$descripcion',0,'$usuario',NOW())");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            $db->rollback(); // Revertimos los cambios
            exit;
        }

        $id = $db->getLastID();

        foreach ($detalles as $key => $d) {
            $id_producto   = $db->clearText($d['id_producto']);
            $producto      = $db->clearText($d['producto']);
            $id_lote       = $db->clearText($d['id_lote']);
            $lote          = $db->clearText($d['lote']);
            $id_movimiento = $db->clearText($d['id_movimiento']);
            $cantidad      = $db->clearText(quitaSeparadorMiles($d['cantidad']));
            $fraccionado   = $db->clearText(quitaSeparadorMiles($d['fraccionado']));
            $motivo =  mb_convert_case($db->clearText($d['motivo']), MB_CASE_UPPER, "UTF-8");

            $operacion = '';

            $db->setQuery("SELECT producto FROM productos WHERE id_producto=$id_producto");
            $row_pro = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            $stock_lote = producto_stock($db, $id_producto, $id_sucursal, $id_lote);

            $entero_ant      = $stock_lote->stock;
            $fraccionado_ant = $stock_lote->fraccionado;

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            // if ($cantidad == 0 && $fraccionado == 0) {
            //     echo json_encode(["status" => "error", "mensaje" => "Debe de ingresar al menos una cantidad para el producto \"$row_pro->producto\""]);
            //     exit;
            // }

            $db->setQuery("INSERT INTO ajuste_stock_productos (id_ajuste, id_producto, producto, id_lote, lote, tipo_ajuste, motivo, cantidad, fraccionado)
                                VALUES ('$id', $id_producto,'$producto', '$id_lote', '$lote',$id_movimiento,'$motivo','$cantidad','$fraccionado')");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Ajuste de stock registrado correctamente"]);
        break;

    case 'editar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id          = $db->clearText($_POST['hidden_id']);
        $descripcion = $db->clearText($_POST['descripcion']);
        $detalles    = json_decode($_POST['detalles'], true);

        $db->setQuery("SELECT estado FROM ajuste_stock WHERE id_ajuste=$id");
        $row = $db->loadObject();

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }

        if ($row->estado == 2) {
            echo json_encode(["status" => "error", "mensaje" => "No es posible editar un ajuste de stock con estado \"Anulado\""]);
            exit;
        }

        if (empty($descripcion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el ajuste"]);
            exit;
        }

        $db->setQuery("UPDATE ajuste_stock SET descripcion='$descripcion' WHERE id_ajuste=$id");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            $db->rollback(); // Revertimos los cambios
            exit;
        }

        $db->setQuery("DELETE FROM ajuste_stock_productos WHERE id_ajuste=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            $db->rollback(); // Revertimos los cambios
            exit;
        }

        $db->setQuery("DELETE FROM stock_historial WHERE id_origen=$id AND origen = 'AJU'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            $db->rollback(); // Revertimos los cambios
            exit;
        }

        foreach ($detalles as $key => $d) {
            $id_producto   = $db->clearText($d['id_producto']);
            $producto      = $db->clearText($d['producto']);
            $id_lote       = $db->clearText($d['id_lote']);
            $lote          = $db->clearText($d['lote']);
            $id_movimiento = $db->clearText($d['id_movimiento']);
            $cantidad      = $db->clearText(quitaSeparadorMiles($d['cantidad']));
            $fraccionado   = $db->clearText(quitaSeparadorMiles($d['fraccionado']));
            $motivo        = $db->clearText($d['motivo']);

            $db->setQuery("SELECT id_producto FROM stock WHERE id_lote=$id_lote");
            $row = $db->loadObject();

            if (isset($row) && $row->id_producto != $id_producto) {
                echo json_encode(["status" => "error", "mensaje" => "Error. El lote \"$lote\" esta asociado a otro producto"]);
                exit;
            }

            $db->setQuery("INSERT INTO ajuste_stock_productos (id_ajuste, id_producto, producto, id_lote, lote, tipo_ajuste,motivo, cantidad, fraccionado) VALUES ('$id', $id_producto,'$producto', '$id_lote', '$lote',$id_movimiento,'$motivo','$cantidad','$fraccionado')");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

            $id_ajuste_producto = $db->getLastID();

            if ($id_movimiento == 1) {
                $operacion = 'ADD';
                producto_sumar_stock($db, $id_producto, $id_sucursal, $id_lote, $cantidad, $fraccionado);
            } else {
                $operacion = 'SUB';
                producto_restar_stock($db, $id_producto, $id_sucursal, $id_lote, $cantidad, $fraccionado);
            }

            $stock              = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal;
            $stock->id_lote     = $id_lote;

            $historial              = new stdClass();
            $historial->cantidad    = $cantidad;
            $historial->fraccionado = $fraccionado;
            $historial->operacion   = $operacion;
            $historial->id_origen   = $id_ajuste_producto;
            $historial->origen      = AJU;
            $historial->detalles    = "Ajuste N° $numero";
            $historial->usuario     = $usuario;

            if (!stock_historial($db, $stock, $historial)) {
                throw new Exception("Error al guardar el historial de stock. Code: " . $db->getErrorCode());
            }
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Ajuste modificado correctamente"]);
        break;

    case 'aprobar':
        $db = DataBase::conectar();
        $db->autocommit(false);

        $id     = $db->clearText($_POST['id']);
        $estado = $db->clearText($_POST['estado']);

        $db->setQuery("SELECT id_sucursal, id_ajuste, numero FROM ajuste_stock WHERE id_ajuste=$id");
        $row         = $db->loadObject();
        $id_sucursal = $row->id_sucursal;
        $numero      = $row->numero;

        $db->setQuery("SELECT id_ajuste_producto, id_ajuste, id_producto, id_lote, cantidad, fraccionado, tipo_ajuste FROM ajuste_stock_productos WHERE id_ajuste=$id");
        $rows = $db->loadObjectList();

        // Se actualiza el estado de los productos
        foreach ($rows as $key => $p) {
            $id_ajuste_producto = $p->id_ajuste_producto;
            $id_producto        = $p->id_producto;
            $id_ajuste          = $p->id_ajuste;
            $id_lote            = $p->id_lote;
            $cantidad           = $p->cantidad;
            $fraccionado        = $p->fraccionado;
            $tipo_ajuste        = $p->tipo_ajuste;

            $stock_lote = producto_stock($db, $id_producto, $id_sucursal, $id_lote);

            $entero_ant      = $stock_lote->stock ?: 0;
            $fraccionado_ant = $stock_lote->fraccionado ?: 0;

            $dif_entero      = abs($entero_ant - $cantidad);
            $dif_fraccionado = abs($fraccionado_ant - $fraccionado);

            $operacion = '';

            $stock              = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal;
            $stock->id_lote     = $id_lote;

            $historial              = new stdClass();
            $historial->cantidad    = $cantidad;
            $historial->fraccionado = $fraccionado;
            $historial->operacion   = '';
            $historial->id_origen   = $id_ajuste_producto;
            $historial->origen      = AJU;
            $historial->detalles    = "Ajuste N° $numero";
            $historial->usuario     = $usuario;

            if ($tipo_ajuste == 2) {

                $historial->operacion = 'SUB';
                producto_restar_stock($db, $id_producto, $id_sucursal, $id_lote, $cantidad, $fraccionado);
                if (!stock_historial($db, $stock, $historial)) {
                    throw new Exception("Error al guardar el historial de stock. Code: " . $db->getErrorCode());
                }

            } else if ($tipo_ajuste == 1) {
                $historial->operacion = 'ADD';
                producto_sumar_stock($db, $id_producto, $id_sucursal, $id_lote, $cantidad, $fraccionado);
                if (!stock_historial($db, $stock, $historial)) {
                    throw new Exception("Error al guardar el historial de stock. Code: " . $db->getErrorCode());
                }

            } else if ($tipo_ajuste == 3) {
                producto_actualizar_stock($db, $id_producto, $id_sucursal, $id_lote, $cantidad, $fraccionado);

                $operacion_entero      = '';
                $operacion_fraccionado = '';

                if ($entero_ant < $cantidad) {
                    $operacion_entero = 'SUB';
                } else {
                    $operacion_entero = 'ADD';
                }

                if ($fraccionado_ant < $fraccionado) {
                    $operacion_fraccionado = 'SUB';
                } else {
                    $operacion_fraccionado = 'ADD';
                }

                if ($operacion_entero == $operacion_fraccionado) {
                    $historial->operacion   = $operacion_entero;
                    $historial->cantidad    = $dif_entero;
                    $historial->fraccionado = $dif_fraccionado;

                    if (!stock_historial($db, $stock, $historial)) {
                        throw new Exception("Error al guardar el historial de stock. Code: " . $db->getErrorCode());
                    }
                } else {

                    //Operacion para insert de entero
                    $historial->operacion   = $operacion_entero;
                    $historial->cantidad    = $dif_entero;
                    $historial->fraccionado = 0;

                    if (!stock_historial($db, $stock, $historial)) {
                        throw new Exception("Error al guardar el historial de stock. Code: " . $db->getErrorCode());
                    }
                    //Operacion para insert de fraccionado
                    $historial->operacion   = $operacion_fraccionado;
                    $historial->cantidad    = 0;
                    $historial->fraccionado = $dif_fraccionado;

                    if (!stock_historial($db, $stock, $historial)) {
                        throw new Exception("Error al guardar el historial de stock. Code: " . $db->getErrorCode());
                    }
                }

            }
            // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
            if ($id_producto != $rows[$key + 1]->id_producto) {
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);
            }

            $db->setQuery("UPDATE ajuste_stock_productos SET cantidad_anterior=$entero_ant, fraccionado_anterior=$fraccionado_ant WHERE id_ajuste_producto=$id_ajuste_producto");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }
        }

        $db->setQuery("UPDATE ajuste_stock SET estado='$estado' WHERE id_ajuste='$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'anular':
        $db = DataBase::conectar();
        $db->autocommit(false);

        $id     = $db->clearText($_POST['id']);
        $estado = $db->clearText($_POST['estado']);

        $db->setQuery("SELECT id_sucursal, id_ajuste, numero FROM ajuste_stock WHERE id_ajuste=$id");
        $row         = $db->loadObject();
        $id_sucursal = $row->id_sucursal;
        $numero      = $row->numero;

        $db->setQuery("SELECT id_ajuste_producto, id_ajuste, id_producto, id_lote, cantidad, fraccionado, tipo_ajuste FROM ajuste_stock_productos WHERE id_ajuste=$id");
        $rows = $db->loadObjectList();

        // Se actualiza el estado de los productos
        foreach ($rows as $key => $p) {
            $id_ajuste_producto = $p->id_ajuste_producto;
            $id_producto        = $p->id_producto;
            $id_ajuste          = $p->id_ajuste;
            $id_lote            = $p->id_lote;
            $cantidad           = $p->cantidad;
            $fraccionado        = $p->fraccionado;
            $tipo_ajuste        = $p->tipo_ajuste;

            $stock              = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal;
            $stock->id_lote     = $id_lote;

            $historial              = new stdClass();
            $historial->cantidad    = $cantidad;
            $historial->fraccionado = $fraccionado;
            $historial->operacion   = $operacion;
            $historial->id_origen   = $id_ajuste_producto;
            $historial->origen      = AJU;
            $historial->detalles    = "Ajuste N° $numero anulado";
            $historial->usuario     = $usuario;

            if (!stock_historial($db, $stock, $historial)) {
                throw new Exception("Error al guardar el historial de stock. Code: " . $db->getErrorCode());
            }

            // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
            if ($id_producto != $rows[$key + 1]->id_producto) {
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);
            }
        }

        $db->setQuery("UPDATE ajuste_stock SET estado='$estado' WHERE id_ajuste='$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'ver_detalles':
        $db = DataBase::conectar();
        $id = $db->clearText($_GET['id']);

        $db->setQuery("SELECT
                                ip.id_ajuste_producto AS id,
                                ip.id_ajuste,
                                ip.id_producto,
                                ip.id_lote,
                                ip.lote,
                                p.codigo,
                                ip.producto,
                                ip.tipo_ajuste AS id_movimiento,
                                CASE ip.tipo_ajuste WHEN 1 THEN 'POSITIVO' WHEN 2 THEN  'NEGATIVO' WHEN 3 THEN 'REEMPLAZO' END AS movimiento,
                                ip.cantidad,
                                ip.fraccionado,
                                ip.cantidad_anterior,
                                ip.fraccionado_anterior,
                                (SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote) AS cantidad_actual,
                                (SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote) AS fraccionado_actual,

                                -- final_entero
                                IF(aj.estado = 0, 
                                    IF(ip.tipo_ajuste = 1, 
                                        ip.cantidad + IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                        0
                                                    )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )
                                            -
                                            ip.cantidad
                                        , 
                                            ip.cantidad
                                        )
                                    )
                                , -- SINO
                                    IF(ip.tipo_ajuste = 1, 
                                        IFNULL(ip.cantidad + ip.cantidad_anterior,
                                            0
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL(ip.cantidad_anterior - cantidad,
                                                0
                                            )
                                        ,
                                            ip.cantidad
                                        )
                                    )
                                ) AS final_entero,
                                
                                -- final_fraccionado
                                IF(aj.estado = 0, 
                                    IF(ip.tipo_ajuste = 1, 
                                        ip.fraccionado + IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                            0
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )
                                            -
                                            ip.fraccionado
                                        ,
                                            ip.fraccionado
                                        )
                                    )
                                , -- SINO
                                    IF(ip.tipo_ajuste = 1, 
                                        IFNULL(ip.fraccionado + ip.fraccionado_anterior,
                                            0
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL(ip.fraccionado_anterior - fraccionado,
                                                0
                                            )
                                        ,
                                            ip.fraccionado
                                        )
                                    )
                                ) AS final_fraccionado,
                                
                                -- diferencia_actual
                                IF(aj.estado = 0, 
                                    IF(ip.tipo_ajuste = 1, 
                                        ABS(
                                            (ip.cantidad + IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )-ip.cantidad)
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        , 
                                            ip.cantidad
                                            -
                                            IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )
                                        )
                                    )
                                , -- SINO
                                    IF(ip.tipo_ajuste = 1,
                                        IFNULL(
                                            ABS((ip.cantidad + ip.cantidad_anterior) - ip.cantidad_anterior)
                                        ,
                                            0
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL(ip.cantidad_anterior - ip.cantidad,
                                                0
                                            )
                                            -
                                            ip.cantidad_anterior
                                        , 
                                            ip.cantidad
                                            -
                                            ip.cantidad_anterior
                                        )
                                    )
                                ) AS diferencia_actual,
                                
                                -- diferencia_anterior
                                IF(aj.estado = 0, 
                                    IF(ip.tipo_ajuste = 1, 
                                        ABS(
                                            (ip.cantidad + IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )-ip.cantidad)
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        , 
                                            ip.cantidad
                                            -
                                            IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )
                                        )
                                    )
                                , -- SINO
                                    IF(ip.tipo_ajuste = 1,
                                        IFNULL(
                                            ABS((ip.cantidad + ip.cantidad_anterior) - ip.cantidad_anterior)
                                        ,
                                            0
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL(ip.cantidad_anterior - ip.cantidad,
                                                0
                                            )
                                            -
                                            ip.cantidad_anterior
                                        , 
                                            ip.cantidad
                                            -
                                            ip.cantidad_anterior
                                        )
                                    )
                                )AS diferencia_anterior,
                                
                                -- diferencia_actual_fraccionado
                                IF(aj.estado = 0, 
                                    IF(ip.tipo_ajuste = 1, 
                                        ABS(
                                            (ip.fraccionado + IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )-ip.fraccionado)
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        , 
                                            ip.fraccionado
                                            -
                                            IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )
                                        )
                                    )
                                , -- SINO
                                    IF(ip.tipo_ajuste = 1,
                                        IFNULL(
                                            ABS((ip.fraccionado + ip.fraccionado_anterior) - ip.fraccionado_anterior)
                                        ,
                                            0
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL(ip.fraccionado_anterior - ip.fraccionado,
                                                0
                                            )
                                            -
                                            ip.fraccionado_anterior
                                        , 
                                            ip.fraccionado
                                            -
                                            ip.fraccionado_anterior
                                        )
                                    )
                                )AS diferencia_actual_fraccionado ,
                                
                                -- diferencia_anterior_fraccionado
                                IF(aj.estado = 0, 
                                    IF(ip.tipo_ajuste = 1, 
                                        ABS(
                                            (ip.fraccionado + IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )-ip.fraccionado)
                                            -
                                            (IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            ))
                                        , 
                                            ip.fraccionado
                                            -
                                            IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),
                                                0
                                            )
                                        )
                                    )
                                , -- SINO
                                    IF(ip.tipo_ajuste = 1,
                                        IFNULL(
                                            ABS((ip.fraccionado + ip.fraccionado_anterior) - ip.fraccionado_anterior)
                                        ,
                                            0
                                        )
                                    ,
                                        IF(ip.tipo_ajuste = 2, 
                                            IFNULL(ip.fraccionado_anterior - ip.fraccionado,
                                                0
                                            )
                                            -
                                            ip.fraccionado_anterior
                                        , 
                                            ip.fraccionado
                                            -
                                            ip.fraccionado_anterior
                                        )
                                    )
                                )AS diferencia_anterior_fraccionado

                            FROM ajuste_stock_productos ip
                            LEFT JOIN ajuste_stock aj ON ip.id_ajuste=aj.id_ajuste
                            JOIN productos p ON ip.id_producto=p.id_producto
                            WHERE ip.id_ajuste=$id");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
        break;

    case 'recuperar-numero':
        $db = DataBase::conectar();

        $db->setQuery("SELECT MAX(numero) AS numero FROM ajuste_stock");
        $row = $db->loadObject();

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
            exit;
        }

        $sgte_numero = intval($row->numero) + 1;
        $numero      = zerofill($sgte_numero);

        echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

}
