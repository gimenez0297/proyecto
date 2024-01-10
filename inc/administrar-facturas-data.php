<?php
include("funciones.php");
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q = $_REQUEST['q'];
$usuario = $auth->getUsername();
$datosUsuario = datosUsuario($usuario);
$id_sucursal = $datosUsuario->id_sucursal;
$id_rol = $datosUsuario->id_rol;

switch ($q) {

    case 'ver':
        $db = DataBase::conectar();
        $sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $funcionario= $db->clearText($_REQUEST['id_funcionario']);
        $desde = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta = $db->clearText($_REQUEST['hasta']) . " 23:59:59";

        $where = "";
        $where_sucursal ="";
        $where_funcionario ="";
        $where_usuario = "";
        $select = "";
        $columnas_admin = "";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit = $db->clearText($_REQUEST['limit']);
        $offset    = $db->clearText($_REQUEST['offset']);
        $order = $db->clearText($_REQUEST['order']);
        $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING numero LIKE '$search%' OR ci LIKE '$search%' OR  nombre_apellido LIKE '$search%' OR usuario LIKE '$search%' OR condicion LIKE '$search%' OR ruc LIKE '$search%' OR razon_social LIKE '$search%' OR fecha LIKE '$search%' OR estado_str LIKE '$search%'";
        }

        if (esAdmin($id_rol) === false) {
            $where_sucursal .= " AND f.id_sucursal=$id_sucursal";
            $select = ", (SELECT MAX(id_factura) FROM facturas WHERE id_sucursal = '$id_sucursal') AS ultima_factura";
        } else if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND f.id_sucursal=$sucursal";
        }

        //Si es cajero
        if (esCajero($id_rol) == true){
            $where_funcionario .= " AND f.usuario='$usuario' ";
            $select = ", (SELECT MAX(id_factura) FROM facturas WHERE usuario = '$usuario' AND id_sucursal=$id_sucursal) AS ultima_factura";
        }else{ 
                if($funcionario != 'null' ){
                    $where_funcionario .= " AND f.usuario='$funcionario' ";
                }
        }

        if (esAdmin($id_rol)) {
            $columnas_admin = "
                f.descuento,
                f.total_costo,
                f.total_venta,
                f.exenta,
                f.gravada_5,
                f.gravada_10,
                f.saldo,
                
            ";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            f.id_factura,
                            f.id_sucursal,
                            CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                            DATE_FORMAT(f.fecha_venta,'%d/%m/%Y %H:%i:%s') AS fecha_venta,
                            CASE f.condicion WHEN 1 THEN 'Contado' WHEN 2 THEN 'Crédito' END AS condicion,
                            f.vencimiento,
                            f.id_cliente, 
                            f.ruc,
                            f.razon_social,
                            f.cantidad,
                            $columnas_admin
                            f.impresiones,
                            f.editar_cobros,
                            f.usuario,
                            f.estado,
                            s.sucursal,
                            u.nombre_apellido,
                            u.ci,
                            CASE f.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Anulado' END AS estado_str,
                            DATE_FORMAT(f.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            IF (
                                (
                                    SELECT COUNT(p.id_producto)
                                    FROM productos p
                                    JOIN facturas_productos fp ON p.id_producto=fp.id_producto
                                    WHERE p.controlado=1 AND fp.id_factura=f.id_factura
                                ) > 0, 
                                IF (
                                    (
                                        SELECT COUNT(id_documento)
                                        FROM documentos_facturas
                                        WHERE tipo=1 AND id_factura=f.id_factura
                                    ) > 0, 
                                    'Cargado', 
                                    'Pendiente'
                                ), 
                                'No Requerido'
                            ) AS receta,
                            courier,
                            IF (
                                (
                                    SELECT COUNT(c.id_factura)
                                    FROM cobros c
                                    WHERE c.id_metodo_pago IN (2,3)  AND c.id_factura = f.id_factura 
                                ) > 0, 
                                IF (
                                    (
                                        SELECT COUNT(c.id_factura)
                                        FROM cobros c
                                        WHERE c.id_metodo_pago IN (2,3)  AND c.detalles = '' AND c.id_factura = f.id_factura
                                    ) > 0, 
                                    'Pendiente',
                                    'Cargado'
                                ), 
                                'No Requerido'
                            ) AS voucher
                            $select
                            FROM facturas f
                            LEFT JOIN timbrados t ON f.id_timbrado = t.id_timbrado
                            LEFT JOIN sucursales s ON s.id_sucursal = f.id_sucursal               
                            JOIN users u ON u.username = f.usuario
                            WHERE f.fecha_venta BETWEEN '$desde' AND '$hasta' $where_sucursal $where_funcionario
                            $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
        $rows = $db->loadObjectList();
        

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total = $total_row->total;

        if ($rows) {
            $salida = array('total' => $total, 'rows' => $rows);
        } else {
            $salida = array('total' => 0, 'rows' => array());
        }

        echo json_encode($salida);
        break;

    case 'anular':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id = $db->clearText($_POST['id']);

        $db->setQuery("SELECT id_cliente, estado, id_asiento FROM facturas WHERE id_factura=$id");
        $row = $db->loadObject();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar la factura"]);
            exit;
        }

        $id_cliente = $row->id_cliente;
        $id_asiento = $row->id_asiento;

        if ($row->estado == 2) {
            echo json_encode(["status" => "error", "mensaje" => "La factura ya fue anulada"]);
            exit;
        }

        // Se anula la factura
        $db->setQuery("UPDATE facturas SET estado=2 WHERE id_factura=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        // Se anulan los cobros
        $db->setQuery("UPDATE cobros SET estado=0 WHERE id_factura=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular los pagos de la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        // Se anulan los puntos acumulados
        $db->setQuery("UPDATE clientes_puntos SET estado=3 WHERE id_factura=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular los puntos de la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        // Se consulta si el metodo de pago es Puntos
        //Recuperamos el Periodo canje
        $db->setQuery("SELECT periodo_canje FROM plan_puntos WHERE tipo=2");
        $periodo_canje = $db->loadObject()->periodo_canje;
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar la configuración del plan de puntos"]);
            exit;
        }

        $db->setQuery("SELECT * FROM clientes_puntos cp
                            JOIN facturas_puntos_utilizados fpu ON fpu.id_cliente_punto=cp.id_cliente_punto
                            WHERE fpu.id_factura=$id  AND (timestampdiff(DAY, cp.fecha, NOw()) > $periodo_canje)");
        $rows = $db->loadObjectList();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los puntos utilizados en el canje"]);
            exit;
        }
        if (!empty($rows)) {
            $db->rollback(); // Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Los puntos del canje se encuentran vencidos"]);
            exit;
        }
        $db->setQuery("SELECT id_cliente_punto, utilizados FROM facturas_puntos_utilizados WHERE id_factura=$id");
        $rows = $db->loadObjectList();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los puntos utilizados en el canje"]);
            exit;
        }

        foreach ($rows as $v) {
            $id_cliente_punto = $v->id_cliente_punto;
            $utilizados = $v->utilizados;

            $db->setQuery("UPDATE clientes_puntos SET utilizados=utilizados-$utilizados, estado=0 WHERE id_cliente_punto=$id_cliente_punto");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al anular los pagos de la factura"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }
        }

        if (!actualiza_puntos_clientes($id_cliente)) {
            $db->rollback(); // Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar los puntos del cliente"]);
            exit;
        }

        // Se suma el stock
        $db->setQuery("SELECT fp.id_factura_producto, fp.fraccionado, fp.id_producto, fp.id_lote, f.id_sucursal, f.numero, fp.cantidad
                            FROM facturas_productos fp
                            JOIN facturas f ON fp.id_factura=f.id_factura
                            WHERE fp.id_factura=$id");
        $rows = $db->loadObjectList();

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los productos de la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        foreach ($rows as $key => $p) {
            $id_factura_producto = $p->id_factura_producto;
            $id_producto = $p->id_producto;
            $id_lote = $p->id_lote;
            $id_sucursal = $p->id_sucursal;
            $numero = $p->numero;
            $cantidad = $p->cantidad;
            $fraccionado = $p->fraccionado;

            if ($fraccionado == 0) {
                $sumar_stock = $cantidad;
                $sumar_fraccionado = 0;
            } else {
                $sumar_stock = 0;
                $sumar_fraccionado = $cantidad;
            }

            try {
                producto_sumar_stock($db, $id_producto, $id_sucursal, $id_lote, $sumar_stock, $sumar_fraccionado);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                exit;
            }

            $stock = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal;
            $stock->id_lote = $id_lote;

            $historial = new stdClass();
            $historial->cantidad = $sumar_stock;
            $historial->fraccionado = $sumar_fraccionado;
            $historial->operacion = ADD;
            $historial->id_origen = $id_factura_producto;
            $historial->origen = FAC;
            $historial->detalles = "Factura N° " . zerofill($numero) . " anulada";
            $historial->usuario = $usuario;

            if (!stock_historial($db, $stock, $historial)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            // Se verifica para realizar la notificación (se realiza en la última iteración de cada producto)
            if ($id_producto != $rows[$key + 1]->id_producto) {
                producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);
            }
        }

        //Preguntamos si ya esta anulado el asiento
        $db->setQuery("SELECT * FROM libro_diario WHERE id_libro_diario = $id_asiento");
        $row_e = $db->loadObject();

        $estado_asiento = $row_e->estado;

        if ($estado_asiento == 0) {
            echo json_encode(["status" => "error", "mensaje" => "El asiento ya fue anulado."]);
            exit;
        }

        //Cambiamos el estado del asiento
        $db->setQuery("UPDATE `libro_diario` SET `estado` = 0 WHERE `id_libro_diario` = $id_asiento;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el asiento."]);
            $db->rollback();  // Revertimos los cambios
            exit;
        }

        //Se obtiene el detalle del asiento
        $db->setQuery("SELECT
                            ld.*,
                            lc.`tipo_cuenta`,
                            lbd.*
                        FROM
                            libro_diario_detalles ld
                        LEFT JOIN libro_cuentas lc ON lc.`id_libro_cuenta` = ld.`id_libro_cuenta`
                        LEFT JOIN libro_diario lbd ON lbd.`id_libro_diario` = ld.`id_libro_diario`
                        WHERE ld.id_libro_diario = $id_asiento");
        $rows = $db->loadObjectList();

        foreach ($rows as $r) {
            $id_libro_periodo = $db->clearText($r->id_libro_diario_periodo);
            $importe = $db->clearText($r->importe);
            $nro_asiento_c = $db->clearText($r->nro_asiento);
        }

        //Número de asiento
        $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
        $nro_asiento = $db->loadObject()->nro_asiento;

        if (empty($nro_asiento)) {
            $nro_asiento = zerofillAsiento(1);
        } else {
            $nro_asiento = zerofillAsiento(intval($nro_asiento) + 1);
        }

        $db->setQuery("INSERT INTO `libro_diario` (`id_libro_diario_periodo`,`fecha`,`nro_asiento`,`importe`,`descripcion`,`contraasiento`,`usuario`,`fecha_creacion`)
            VALUES($id_libro_periodo,NOW(),'$nro_asiento',$importe,'CONTRA ASIENTO DEL ASIENTO $nro_asiento_c','$nro_asiento_c','$usuario',NOW());");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
            $db->rollback();  // Revertimos los cambios
            exit;
        }

        $id_asiento = $db->getLastID();

        foreach ($rows as $r) {
            $id_libro_detalle = $db->clearText($r->id_libro_detalle);
            $id_libro_cuenta  = $db->clearText($r->id_libro_cuenta);
            $debe  = $db->clearText($r->debe);
            $haber  = $db->clearText($r->haber);
            $tipo_cuenta = $db->clearText($r->tipo_cuenta);
            $nro_asiento_c = $db->clearText($r->nro_asiento);

            $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
            VALUES($id_asiento,$id_libro_cuenta,'Contra asiento del asiento $nro_asiento_c',$haber,$debe);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Factura anulada correctamente"]);
    break;

    case 'ver_productos':
        $db = DataBase::conectar();
        $id_factura = $db->clearText($_GET['id_factura']);
        $columnas_admin = "";

        if (esAdmin($id_rol)) {
            $columnas_admin = "
                SUM(fp.cantidad) AS cantidad, 
                SUM(fp.descuento) AS descuento,
                (fp.precio * SUM(fp.cantidad)) AS subtotal,
                SUM(fp.total_venta) AS total_venta,
                fp.precio, 
            ";
        }

        $db->setQuery("SELECT 
                        fp.id_factura_producto, 
                        fp.id_producto, 
                        p.codigo, 
                        p.producto, 
                        p.controlado, 
                        pre.id_presentacion, 
                        pre.presentacion, 
                        $columnas_admin
                        fp.fraccionado, 
                        fp.id_lote,
                        fp.lote
                    FROM facturas_productos fp
                    JOIN productos p ON fp.id_producto=p.id_producto
                    LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                    LEFT JOIN facturas f ON f.id_factura = fp.id_factura
                    WHERE fp.id_factura=$id_factura
                    GROUP BY fp.id_producto");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;

    case 'guardar-documento':
        $db = DataBase::conectar();
        $id = $db->clearText($_POST['hidden_id']);
        $doctor = $db->clearText($_POST['doctor']);
        $descripcion = mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
        $tipo = $db->clearText($_POST['tipo']);
        $documento= $_FILES['documento'];

        //Si el id_doctor tiene algun valor y es de tipo receta se inserta el dato en la variable auxiliar, si es tipo courier el dato es Null
        if(!empty($doctor)  && $tipo == 1){
            $doctor_valor=$doctor;
        }else{
            $doctor_valor = 'NULL';
        }

            try {
                $foo = new \Verot\Upload\Upload($documento);

                if ($foo->uploaded) {
                    $targetPath = "../archivos/recetas/";
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0777, true);
                    }

                    $foo->file_new_name_body    = md5($id);
                    $foo->image_convert              = jpg;
                    $foo->image_resize                 = true;
                    $foo->image_ratio                   = true;
                    $foo->image_ratio_crop          = true;
                    $foo->image_ratio_y                = true;
                    $foo->image_y                         = 640;
                    $foo->image_x                         = 640;
                    $foo->process($targetPath);
                    $documento = str_replace("../", "", $targetPath . $foo->file_dst_name);
                    if ($foo->processed) {
                        $db->setQuery("INSERT INTO documentos_facturas (descripcion, id_factura, usuario, fecha, documento, tipo, id_doctor) VALUES ('$descripcion',  $id, '$usuario', NOW(), '$documento', $tipo, $doctor_valor )");
                        if (!$db->alter()) {
                            throw new Exception("Error de base de datos al guardar los archivos. Code: " . $db->getErrorCode());
                        }
                        $foo->clean();
                    } else {
                        throw new Exception("Error al guardar los archivos: " . $foo->error);
                    }
                } else {
                    throw new Exception("Archivo no encontrado");
                }
            } catch (Exception $e) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                exit;
            }
        
        $db->commit();
        echo json_encode(["status" => "ok", "mensaje" => "Archivo guardado correctamente"]);
    break;

    case 'eliminar_documento':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id_ = $db->clearText($_POST['id_']);

        $db->setQuery("SELECT documento FROM documentos_facturas WHERE id_documento=$id_");
        $row = $db->loadObject();
        $documento = $row->documento;

        if (empty($row)) {
            echo json_encode(["status" => "error", "mensaje" => "Archivo no encontrado"]);
            exit;
        }

        $db->setQuery("DELETE FROM documentos_facturas WHERE id_documento=$id_");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al eliminar el archivo. Code: ".$db->getErrorCode()]);
            exit;
        }

        if (!unlink("../".$documento)) {
            $db->rollback(); // Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Error al eliminar el archivo"]);
            exit;
        }

        $db->commit();
        echo json_encode(["status" => "ok", "mensaje" => "Archivo eliminado correctamente"]);
    break;

    case 'ver_documentos':
        $db = DataBase::conectar();
        $id_factura = $db->clearText($_REQUEST['id_factura']);

        $db->setQuery("SELECT 
                            df.id_documento AS id_,
                            df.descripcion,
                            df.usuario,
                            DATE_FORMAT(df.fecha, '%d/%m/%Y %H:%i') AS fecha,
                            df.documento,
                            0 AS estado,
                            df.documento AS ver,
                            df.tipo,
                            d.nombre_apellido AS doctor_str,
                            CASE df.tipo WHEN 1 THEN 'RECETA' WHEN 2 THEN 'COURIER' END AS tipo_str
                            FROM documentos_facturas df
                            LEFT JOIN doctores d ON d.id_doctor=df.id_doctor
                            WHERE df.id_factura=$id_factura
                            ORDER BY descripcion");
        $rows = ($db->loadObjectList()) ?: [];

        echo json_encode($rows);
    break;

    case "ver_cobros":
        $db = DataBase::conectar();
        $id = $db->clearText($_REQUEST["id"]);
    
        $db->setQuery("SELECT 
                            c.id_cobro, 
                            c.id_metodo_pago, 
                            c.id_descuento_metodo_pago, 
                            c.id_recibo, 
                            IF(c.id_metodo_pago IS NOT NULL, c.metodo_pago, dp.descripcion) AS metodo_pago,
                            c.detalles, 
                            r.numero AS numero_recibo, 
                            c.monto
                        FROM cobros c
                        LEFT JOIN descuentos_pagos dp ON c.id_descuento_metodo_pago=dp.id_descuento_pago
                        LEFT JOIN recibos r ON c.id_recibo=r.id_recibo
                        WHERE c.estado=1 AND c.id_factura=$id");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
    break;

    case "editar_cobros":
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id_factura = $db->clearText($_POST["id_factura"]);
        $data = json_decode($_POST["data"]);

        if (empty($data)) {
            echo json_encode(["status" => "error", "mensaje" => "No se encontraron los cobros de la factura"]);
            exit;
        }

        $db->setQuery("SELECT editar_cobros FROM facturas WHERE id_factura=$id_factura");
        $row = $db->loadObject();
        if ($db->error()) {
            $db->rollback(); // Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos de los cobros"]);
            exit;
        }

        if ($row->editar_cobros == 0 && esAdmin($id_rol) === false) {
            echo json_encode(["status" => "error", "mensaje" => "No puede editar los cobros de esta factura"]);
            exit;
        }

        foreach ($data as $key => $value) {
            $id = $db->clearText($value->id_cobro);
            $new_value = $db->clearText($value->detalles);

            $db->setQuery("SELECT id_factura FROM cobros WHERE id_cobro=$id");
            $row = $db->loadObject();
            if ($db->error()) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al recuperar los datos de los cobros"]);
                exit;
            }

            if ($row->id_factura != $id_factura) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "El cobro no corresponde a la factura seleccionada"]);
                exit;
            }

            $db->setQuery("UPDATE cobros SET detalles='$new_value' WHERE id_cobro=$id");
            if (!$db->alter()) {
                $db->rollback(); // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al editar el cobro"]);
                exit;
            }
        }

        $db->setQuery("UPDATE facturas SET editar_cobros=0 WHERE id_factura=$id_factura");
        if (!$db->alter()) {
            $db->rollback(); // Revertimos los cambios
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la factura"]);
            exit;
        }

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Cobro modificado correctamente"]);

    break;

    case 'cargar_doctor':
        $db              = DataBase::conectar();
        $ruc = $db->clearText($_POST['ruc']);
        $registro        = mb_convert_case($db->clearText($_POST['registro']), MB_CASE_UPPER, "UTF-8");
        $nombre_apellido = mb_convert_case($db->clearText($_POST['nombre_apellido']), MB_CASE_UPPER, "UTF-8");
        $id_especialidad = $db->clearText($_POST['id_especialidad']);

        if (empty($ruc)) {
            $ruc = 'SIN RUC';
        }
        if (empty($registro)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nro. Registro"]);
            exit;
        }
        if (empty($nombre_apellido)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre y Apellido"]);
            exit;
        }
        if (empty($id_especialidad) || !isset($id_especialidad) || intval($id_especialidad) == 0) {
            $id_especialidad = "NULL";
        }

        $db->setQuery("INSERT INTO doctores (id_especialidad,nombre_apellido,registro_nro, ruc)
                       VALUES($id_especialidad,'$nombre_apellido','$registro', '$ruc');");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }

        $id = $db->getLastID();
        $data = [ 'id' =>$id, 'nombre_apellido' => $nombre_apellido ];

        echo json_encode(["status" => "ok", "mensaje" => "Doctor registrado correctamente", "data" => $data ]);


    break;

}
