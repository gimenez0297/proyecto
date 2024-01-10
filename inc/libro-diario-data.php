<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();

    switch ($q) {

        case 'ver':
            $db    = DataBase::conectar();
            $where = "";
            $id = $db->clearText($_REQUEST['id_libro']);

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit  = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order  = $db->clearText($_REQUEST['order']);
            $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', nombre, estado_str) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                        ld.`id_libro_diario`,
                                        DATE_FORMAT(ld.`fecha`, '%d/%m/%Y') AS fecha,
                                        ld.`nro_asiento`,
                                        ld.`importe`,
                                        ld.`descripcion`,
                                        ld.`usuario`,
                                        DATE_FORMAT(ld.`fecha_creacion`, '%d/%m/%Y') AS fecha_creacion,
                                        CASE ld.estado WHEN 0 THEN 'Anulado' WHEN 1 THEN 'Activo' END AS estado_str,
                                        ld.estado,
                                        ld.contraasiento
                                    FROM
                                        libro_diario ld
                                        WHERE ld.`id_libro_diario_periodo` = $id
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

        case 'cargar_asiento':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id_libro = $db->clearText($_POST['id_libro_diario']);
            $fecha = $db->clearText($_POST['fecha']);
            $tipo_comprobante = $db->clearText($_POST['comprobante']);
            $nro_asiento = $db->clearText($_POST['nro']);
            $descripcion =  mb_convert_case($db->clearText($_POST['descripcion']), MB_CASE_UPPER, "UTF-8");
            $importe = quitaSeparadorMiles($db->clearText($_POST['importe']));
            $asientos = json_decode($_POST['asientos'], true);

            if (empty($id_libro)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un libro diario."]);
                exit;
            }
            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Fecha."]);
                exit;
            }
            if (empty($tipo_comprobante)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor selecciona un comprobante."]);
                exit;
            }
            if (empty($importe)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Importe."]);
                exit;
            }
            if ($importe < 0) {
                echo json_encode(["status" => "error", "mensaje" => "El importe tiene que ser mayor a 0."]);
                exit;
            }
            if (empty($asientos)) {
                echo json_encode(["status" => "error", "mensaje" => "No hay ni un asiesto cargado."]);
                exit;
            }

            $db->setQuery("INSERT INTO `libro_diario` (
                            `id_libro_diario_periodo`,
                            `id_comprobante`,
                            `fecha`,
                            `nro_asiento`,
                            `importe`,
                            `descripcion`,
                            `usuario`,
                            `fecha_creacion`
                            )
                        VALUES
                            (
                            $id_libro,
                            $tipo_comprobante,
                            '$fecha',
                            '$nro_asiento',
                            $importe,
                            '$descripcion',
                            '$usuario',
                            NOW()
                            );");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el asiento."]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            $id_asiento = $db->getLastID();

            foreach ($asientos as $a) {
                $id_libro_cuenta = $db->clearText($a["id_libro_cuenta"]);
                $concepto =  mb_convert_case($db->clearText($a["concepto"]), MB_CASE_UPPER, "UTF-8");
                $debe = quitaSeparadorMiles($db->clearText($a["debe"]));
                $haber = quitaSeparadorMiles($db->clearText($a["haber"]));

                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                  VALUES($id_asiento, $id_libro_cuenta,'$concepto', $debe, $haber);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. Al guardar el detalle del Asiento."]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }  
            }

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Asiento Contable registrado correctamente"]);
        break;

        case 'ver_detalles':
            $db = DataBase::conectar();
            $id_libro_diario = $db->clearText($_REQUEST['id']);

            $db->setQuery("SELECT
                            ld.`id_libro_detalle`,
                            CONCAT(lc.`cuenta`,' - ',lc.`denominacion`) AS denominacion,
                            ld.`concepto`,
                            ld.`debe`,
                            ld.`haber`
                        FROM
                            libro_diario_detalles ld
                        LEFT JOIN libro_cuentas lc ON lc.`id_libro_cuenta` = ld.`id_libro_cuenta`
                        WHERE ld.`id_libro_diario` = $id_libro_diario");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'contra-asiento':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id_libro_diario = $db->clearText($_REQUEST['id']);

            //Preguntamos si ya esta anulado el asiento
            $db->setQuery("SELECT * FROM libro_diario WHERE id_libro_diario = $id_libro_diario");
            $row_e = $db->loadObject();

            $estado_asiento = $row_e->estado;
            $contraasiento = $row_e->contraasiento;

            if ($estado_asiento == 0) {
                echo json_encode(["status" => "error", "mensaje" => "El asiento ya fue anulado."]);
                exit;
            }

            if (!empty($contraasiento)) {
                echo json_encode(["status" => "error", "mensaje" => "No se puede anular un contra asiento."]);
                exit;
            }

            //cambiamos el estado del asiento
            $db->setQuery("UPDATE `libro_diario` SET `estado` = 0 WHERE `id_libro_diario` = $id_libro_diario;");
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
                            WHERE ld.id_libro_diario = $id_libro_diario");
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
              VALUES($id_libro_periodo,NOW(),'$nro_asiento',$importe,'Contra asiento del asiento $nro_asiento_c','$nro_asiento_c','$usuario',NOW());");
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

            $db->commit(); // Guardamos los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Contra asiento registrado correctamente"]);    
        break;

        case 'obtener_numero_asiento':
            $db = DataBase::conectar();
    
            $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
            $nro_asiento = $db->loadObject()->nro_asiento;
    
            if (empty($nro_asiento)) {
                $nro = zerofillAsiento(1);
            } else {
                $nro = zerofillAsiento(intval($nro_asiento) + 1);
            }
    
            echo json_encode(["numero" => $nro]);
            
        break;
    }