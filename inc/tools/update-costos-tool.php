<?php
    include ("../funciones.php");

    if (!verificaLogin()) {
        echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
        exit;
    }
    
    $usuario = $auth->getUsername();
    $datosUsuario = datosUsuario($usuario);
    $id_sucursal = $datosUsuario->id_sucursal;
    $id_rol = $datosUsuario->id_rol;
    
    if (!esAdmin($id_rol)) {
        echo json_encode(["status" => "error", "mensaje" => "Debes ser administrador."]);
        exit;
    }
    
    $db = DataBase::conectar();
    $db->autoCommit(false);
    
    $db->setQuery("SELECT lotes.*, 
        IFNULL((
            SELECT costo_ultimo 
            FROM productos_proveedores 
            WHERE id_producto = lotes.id_producto 
            AND costo_ultimo IS NOT NULL
            AND costo_ultimo != ''
            ORDER BY proveedor_principal DESC LIMIT 1
        ), 0) AS ultimo
        FROM(
            SELECT l.id_lote, l.lote, l.costo, p.id_producto, p.producto
            FROM lotes l 
            INNER JOIN stock s ON l.id_lote = s.id_lote
            INNER JOIN productos p ON p.id_producto = s.id_producto
            WHERE l.costo = p.precio
            GROUP BY l.id_lote
        )lotes
    ");

    $rows = $db->loadObjectList();

    if (!empty($rows)) {
        foreach ($rows as $key => $r) {
            $id_lote = $r->id_lote;
            $costo_nuevo = $r->ultimo;

            $db->setQuery("UPDATE lotes SET costo=$costo_nuevo WHERE id_lote=$id_lote");
            if (!$db->alter()) {
                $db->rollback();
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el costo del lote"]);
                exit;
            }
        }
    } else {
        echo json_encode(["status" => "warning", "mensaje" => "No existen registros a actualizar."]);
        exit;
    }

    $db->commit();
    echo json_encode(["status" => "success", "mensaje" => "Costos actualizados."]);
?>