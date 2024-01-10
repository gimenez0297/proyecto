<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$id_sucursal = datosUsuario($usuario)->id_sucursal;

switch ($q) {

    case 'ver':
        $db    = DataBase::conectar();
        $where = "";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        if (isset($search) && !empty($search)) {
            $having = "HAVING proveedor LIKE '$search%' OR nombre_fantasia LIKE '$search%' OR ruc LIKE '$search%' OR contacto LIKE '$search%' OR telefono LIKE '$search%' OR email LIKE '$search%' OR obs LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            p.id_proveedor,
                            p.proveedor,
                            p.nombre_fantasia,
                            p.ruc,
                            p.contacto,
                            p.direccion,
                            p.telefono,
                            p.email,
                            p.obs,
                            p.usuario,
                            pt.tipo_proveedor,
                            DATE_FORMAT(p.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt on p.id_proveedor=pt.id_proveedor
                            WHERE pt.tipo_proveedor = 1
                            GROUP BY p.id_proveedor
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

    case 'ver-proveedor':
        $db = DataBase::conectar();
        //Parametros de ordenamiento, busqueda y paginacion
        $proveedor = $db->clearText($_REQUEST['proveedor']);
        $sucursal  = $db->clearText($_REQUEST['sucursal']);
        $search    = $db->clearText($_REQUEST['search']);
        if (isset($search) && !empty($search)) {
            $having = "HAVING pro.producto LIKE '$search%' OR p.proveedor LIKE '$search%' OR pp.codigo LIKE '$search%'";
        }

        $db->setQuery("SELECT pp.id_producto_proveedor,
                            pp.id_producto,
                            pro.producto,
                            pp.id_proveedor,
                            pp.codigo,
                            pro.precio,
                            pp.costo,
                            IFNULL(dpp.porcentaje, 0) AS descuento,
                            ROUND(IF(IFNULL(dpp.porcentaje, 0) > 0 AND IFNULL(pro.precio, 0) > 0, pro.precio - ((pro.precio*dpp.porcentaje)/100), 0)) AS precio_descuento,
                            dpp.id_metodo_pago,
                            ps.id_presentacion,
                            ps.presentacion
                            FROM productos_proveedores pp
                            JOIN productos pro ON pp.id_producto = pro.id_producto
                            LEFT JOIN presentaciones ps ON pro.id_presentacion = ps.id_presentacion
                            JOIN descuentos_proveedores_productos dpp ON pp.id_producto = dpp.id_producto AND dpp.id_sucursal = '$sucursal'
                            WHERE 1=1 AND pp.id_proveedor = '$proveedor' AND pp.proveedor_principal = 1 
                            $having
                            ORDER BY pp.id_producto");
        $rows   = ($db->loadObjectList()) ?: [];
        $salida = array();

        foreach ($rows as $key => $r) {
            $row = (array) $r;

            $row["m_".$row['id_metodo_pago']] = $row['descuento'];
            $row["p_desc_m_".$row['id_metodo_pago']] = $row['precio_descuento'];

            if ($row['id_producto'] != $rows[$key - 1]->id_producto) {
                $salida[] = $row;
                $i        = array_key_last($salida);
            } else {
                $salida[$i] += $row;
            }
        }
        echo json_encode($salida);
        break;

    case 'sucursales_select':
        $db          = DataBase::conectar();
        $proveedor   = $db->clearText($_REQUEST['id_proveedor']);
        $id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $db->setQuery("SELECT @row_coun := @row_coun + 1 AS id_,
                            s.id_sucursal,
                            s.sucursal,
                            dp.id_metodo_pago,
                            mp.metodo_pago,
                            dp.id_origen,
                            o.origen,
                            dp.id_tipo_producto as id_tipo,
                            tp.tipo,
                            dp.id_laboratorio,
                            l.laboratorio,
                            dp.id_marca,
                            m.marca,
                            dp.id_rubro,
                            r.rubro,
                            p.proveedor,
                            dp.controlado,
                            dp.porcentaje
                            FROM (SELECT @row_coun := 0) row_coun, sucursales s
                            LEFT JOIN descuentos_proveedores dp ON s.id_sucursal = dp.id_sucursal
                            LEFT JOIN proveedores p ON dp.id_proveedor = p.id_proveedor
                            LEFT JOIN metodos_pagos mp ON dp.id_metodo_pago = mp.id_metodo_pago
                            LEFT JOIN origenes o ON dp.id_origen = o.id_origen
                            LEFT JOIN tipos_productos tp ON dp.id_tipo_producto = tp.id_tipo_producto
                            LEFT JOIN laboratorios l ON dp.id_laboratorio = l.id_laboratorio
                            LEFT JOIN marcas m ON dp.id_marca = m.id_marca
                            LEFT JOIN rubros r ON dp.id_rubro = r.id_rubro
                            WHERE s.estado=1 AND dp.id_proveedor = '$proveedor' AND dp.id_sucursal = '$id_sucursal'
                            ORDER BY s.sucursal");

        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;

    case 'productos_proveedor':
        $db = DataBase::conectar();
        $id     = intval($db->clearText($_GET['id']));

        $page     = $db->clearText($_GET['page']);
        $term     = $db->clearText($_GET['term']);
        $resultCount = 5;
        $end = ($page - 1) * $resultCount; 

        $db->setQuery("SELECT  SQL_CALC_FOUND_ROWS 
                            pp.id_producto_proveedor,
                            p.id_producto,
                            p.producto,
                            pp.codigo,
                            p.precio,
                            pp.costo,
                            pre.id_presentacion,
                            pre.presentacion
                        FROM productos_proveedores pp
                        JOIN productos p ON pp.id_producto = p.id_producto
                        LEFT JOIN presentaciones pre ON p.id_presentacion = pre.id_presentacion
                        WHERE pp.id_proveedor=$id AND pp.proveedor_principal=1 AND producto LIKE '%$term%'
                        ORDER BY p.producto
                        LIMIT $end, $resultCount");
        $rows = ($db->loadObjectList()) ?: [];

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total_count = $total_row->total;
        
        if (empty($rows)) {
            $salida = ['data' => [], 'total_count' => 0];
        } else {
            $salida = ['data' => $rows, 'total_count' => $total_count];
        }

        echo json_encode($salida);
    break;

    case 'editar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id           = $db->clearText($_POST['hidden_id']);
        $id_proveedor = $db->clearText($_POST['hidden_id_proveedor']);
        $productos    = json_decode($_POST['proveedor_principal']);
        $detalles     = json_decode($_POST['detalles']);
        $sucursales   = json_decode($_POST['sucursales']);

        foreach ($sucursales as $s) {
            $sucursal = ($db->clearText($s->id_sucursal));

            $db->setQuery("DELETE FROM descuentos_proveedores WHERE id_proveedor='$id_proveedor' AND id_sucursal='$sucursal'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar los proveedores. " . $db->getError(), "error" => "proveedores"]);
                $db->rollback();
                exit;
            }

            foreach ($detalles as $d) {
                $id_metodo_pago = ($db->clearText($d->id_metodo_pago)) ?: "NULL";
                $id_origen      = ($db->clearText($d->id_origen)) ?: "NULL";
                $id_tipo        = ($db->clearText($d->id_tipo)) ?: "NULL";
                $id_laboratorio = ($db->clearText($d->id_laboratorio)) ?: "NULL";
                $id_marca       = ($db->clearText($d->id_marca)) ?: "NULL";
                $id_rubro       = ($db->clearText($d->id_rubro)) ?: "NULL";
                $controlado     = $d->controlado;
                $porcentaje     = $db->clearText($d->porcentaje);


                if (empty($porcentaje) && $porcentaje != 0) {
                    echo json_encode(["status" => "error", "mensaje" => "Favor especificar un porcentaje"]);
                    exit;
                }

                if ($porcentaje < 0) {
                    echo json_encode(["status" => "error", "mensaje" => "No puede ingresar un descuento menor a 0%"]);
                    exit;
                }
                if ($porcentaje > 100) {
                    echo json_encode(["status" => "error", "mensaje" => "No puede ingresar un descuento mayor a 100%"]);
                    exit;
                }

                // Controlado puede tener tres valores: null, 0 y 1
                if (is_null($controlado)) {
                    $controlado = "NULL";
                } else {
                    $controlado = $db->clearText($controlado);
                }

                $db->setQuery("INSERT INTO descuentos_proveedores (id_sucursal, id_proveedor, id_metodo_pago, id_origen, id_tipo_producto, id_laboratorio, id_marca, id_rubro, controlado, porcentaje, estado, usuario, fecha) VALUES ('$sucursal', $id_proveedor,$id_metodo_pago,$id_origen,$id_tipo,$id_laboratorio,$id_marca,$id_rubro,$controlado,$porcentaje,1,'$usuario',NOW())");

                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error." . $db->geterror()]);
                    $db->rollback();
                    exit;
                }
            }

            $db->setQuery("DELETE FROM descuentos_proveedores_productos WHERE id_proveedor='$id_proveedor' AND id_sucursal='$sucursal'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar los proveedores. " . $db->getError(), "error" => "proveedores"]);
                $db->rollback();
                exit;
            }

            if ($productos) {
                $db->setQuery("SELECT * FROM metodos_pagos");
                $met = ($db->loadObjectList()) ?: [];

                foreach ($productos as $p) {
                    $id_proveedor = $db->clearText($p->id_proveedor);
                    $id_producto  = $db->clearText($p->id_producto);
                    foreach ($met as $metodo) {
                        $id_metodo = $db->clearText($metodo->id_metodo_pago);
                        $descuento = $db->clearText($p->{"m_".$id_metodo}) ?: 0;

                        $db->setQuery("INSERT INTO descuentos_proveedores_productos (id_sucursal, id_proveedor, id_producto, porcentaje, id_metodo_pago, fecha)
                                        VALUES ($sucursal, $id_proveedor, $id_producto, $descuento, $id_metodo,NOW())");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->geterror()]);
                            $db->rollback();
                            exit;
                        }
                    }
                }
            }
        }

        $db->commit();
        echo json_encode(["status" => "ok", "mensaje" => "Descuento Agregado correctamente"]);
        break;

    case 'cambiar-estado':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $estado = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE descuentos_proveedores SET estado='$estado' WHERE id_descuento_proveedor=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'metodos_pagos':
        $db = DataBase::conectar();
        $db->setQuery("SELECT
                            *
                            FROM metodos_pagos
                            WHERE estado=1
                            ");

        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;
}
