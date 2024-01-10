<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;

    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();
            $where = "";

            $id_producto        = $_REQUEST["producto"];
            $id_laboratorio     = $_REQUEST["laboratorio"];
            $id_marca           = $_REQUEST["marca"];
            $id_unidad_medida   = $_REQUEST["unidad_medida"];
            $id_presentacion    = $_REQUEST["presentacion"];
            $id_clasificacion   = $_REQUEST["clasificacion"];
            $id_origen          = $_REQUEST["origen"];
            $id_procedencia     = $_REQUEST["procedencia"];
            $id_rubro           = $_REQUEST["rubro"];
            $id_tipo            = $_REQUEST["tipo"];
            $comision           = $_REQUEST["con_sin"];

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING codigo LIKE '$search%' OR producto LIKE '$search%' OR  presentacion LIKE '$search%' OR laboratorio LIKE '$search%' OR principios_activos LIKE '$search%'";
            }

            if(!empty($id_producto) && intVal($id_producto) != 0) {$and_id_producto .= " AND p.id_producto= $id_producto";}else{$and_id_producto .="";};
            if(!empty($id_laboratorio) && intVal($id_laboratorio) != 0) {$and_id_laboratorio .= " AND p.id_laboratorio= $id_laboratorio";}else{$and_id_laboratorio .="";};
            if(!empty($id_marca) && intVal($id_marca) != 0) {$and_id_marca .= " AND p.id_marca= $id_marca";}else{$and_id_marca .="";};
            if(!empty($id_unidad_medida) && intVal($id_unidad_medida) != 0) {$and_id_unidad_medida .= " AND p.id_unidad_medida= $id_unidad_medida";}else{$and_id_unidad_medida.="";};
            if(!empty($id_presentacion) && intVal($id_presentacion) != 0) {$and_id_presentacion .= " AND p.id_presentacion= $id_presentacion";}else{$and_id_presentacion.="";};
            if(!empty($id_clasificacion) && intVal($id_clasificacion) != 0) {$and_id_clasificacion .= " AND pp.id_clasificacion= $id_clasificacion";}else{$and_id_clasificacion.="";};
            if(!empty($id_origen) && intVal($id_origen) != 0) {$and_id_origen .= " AND p.id_origen= $id_origen";}else{$and_id_origen.="";};
            if(!empty($id_procedencia) && intVal($id_procedencia) != 0) {$and_id_procedencia .= " AND p.id_pais= $id_procedencia";}else{$and_id_procedencia.="";};
            if(!empty($id_rubro) && intVal($id_rubro) != 0) {$and_id_rubro .= " AND p.id_rubro= $id_rubro";}else{$and_id_rubro.="";};
            if(!empty($id_tipo) && intVal($id_tipo) != 0) {$and_id_tipo .= " AND p.id_tipo_producto= $id_tipo";}else{$and_id_tipo.="";};
            if($comision == 1){$and_comision .= "AND p.comision > 0";}elseif ($comision == 2) {$and_comision .= "AND p.comision = 0";}else{$and_comision .= "";}

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS
                                p.id_producto,
                                p.producto,
                                p.id_marca,
                                m.marca,
                                p.id_laboratorio,
                                l.laboratorio,
                                p.id_tipo_producto,
                                tp.tipo,
                                tp.principios_activos,
                                p.id_origen,
                                o.origen,
                                p.comision,
                                p.id_moneda,
                                mo.moneda,
                                p.id_presentacion,
                                pr.presentacion,
                                cp.id_clasificacion_producto,
                                cp.clasificacion,
                                p.id_unidad_medida,
                                um.unidad_medida,
                                um.sigla,
                                p.id_rubro,
                                ru.rubro,
                                p.id_pais,
                                ps.nombre_es AS pais,
                                p.precio,
                                p.precio_fraccionado,
                                p.cantidad_fracciones,
                                p.descuento_fraccionado,
                                p.fuera_de_plaza,
                                p.codigo,
                                p.observaciones,
                                p.controlado,
                                p.descripcion,
                                p.copete,
                                p.estado,
                                p.conservacion,
                                p.web,
                                p.iva,
                                p.comision_concepto AS concepto,
                                CASE p.iva WHEN 1 THEN 'EXENTAS' WHEN 2 THEN '5%'  WHEN 3 THEN '10%'  END AS iva_str,
                                CASE p.conservacion WHEN 1 THEN 'NORMAL' WHEN 2 THEN 'REFRIGERADO' END AS conservacion_str,
                                p.indicaciones,
                                CASE p.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                                p.usuario,
                                DATE_FORMAT(p.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM productos p
                            LEFT JOIN marcas m ON p.id_marca=m.id_marca
                            LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
                            LEFT JOIN tipos_productos tp ON p.id_tipo_producto=tp.id_tipo_producto
                            LEFT JOIN origenes o ON p.id_origen=o.id_origen
                            LEFT JOIN monedas mo ON p.id_moneda=mo.id_moneda
                            LEFT JOIN presentaciones pr ON p.id_presentacion=pr.id_presentacion                         
                            LEFT JOIN productos_clasificaciones pp ON  p.id_producto = pp.id_producto
                            LEFT JOIN clasificaciones_productos cp ON pp.id_clasificacion=cp.id_clasificacion_producto
                            LEFT JOIN unidades_medidas um ON p.id_unidad_medida=um.id_unidad_medida
                            LEFT JOIN rubros ru ON p.id_rubro=ru.id_rubro
                            LEFT JOIN paises ps ON p.id_pais=ps.id_pais
                            WHERE 1=1 $and_id_tipo $and_id_producto $and_id_laboratorio $and_id_marca $and_id_unidad_medida $and_id_presentacion $and_id_clasificacion $and_id_origen $and_id_procedencia $and_id_rubro $and_comision
                            GROUP by 
                            p.id_producto
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

        case 'editar-comision':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_POST['id_producto']);
            $comision    = $db->clearText($_POST['comision']);
            $concepto    = $db->clearText($_POST['concepto']);
            
            if ($comision > 100) {
                echo json_encode(["status" => "error", "mensaje" => "La comision no puede ser mayor a 100"]);
                exit;
            }

            $db->setQuery("UPDATE productos SET comision = '$comision', comision_concepto = '$concepto' WHERE id_producto = $id_producto;");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Comision actualizado correctamente"]);
        break;

        case 'actualizar-comision':
            $db             = DataBase::conectar();
            // $comision       = $db->clearText($_POST['porc_comision']);
            $comision       = $db->clearText($_POST['porc_comision']);
            $concepto       = $db->clearText($_POST['concepto']);
            $tipo           = $db->clearText($_POST['tipo']);
            $rubro          = $db->clearText($_POST['rubro']);
            $procedencia    = $db->clearText($_POST['procedencia']);
            $origen         = $db->clearText($_POST['origen']);
            $clasificacion  = $db->clearText($_POST['clasificacion']);
            $presentacion   = $db->clearText($_POST['presentacion']);
            $unidad_medida  = $db->clearText($_POST['unidad_medida']);
            $marca          = $db->clearText($_POST['marca']);
            $laboratorio    = $db->clearText($_POST['laboratorio']);
            $producto       = $db->clearText($_POST['producto']);
            $con_sin        = $db->clearText($_POST['con_sin']);

            if ($comision > 100) {
                echo json_encode(["status" => "error", "mensaje" => "La comision no puede ser mayor a 100"]);
                exit;
            }


            if(!empty($tipo) && intVal($tipo) != 0) {$and_id_tipo .= " AND id_tipo_producto= $tipo";}else{$and_id_tipo.="";};
            if(!empty($rubro) && intVal($rubro) != 0) {$and_id_rubro .= " AND id_rubro= $rubro";}else{$and_id_rubro.="";};
            if(!empty($procedencia) && intVal($procedencia) != 0) {$and_id_procedencia .= " AND id_pais= $procedencia";}else{$and_id_procedencia.="";};
            if(!empty($origen) && intVal($origen) != 0) {$and_id_origen .= " AND id_origen= $origen";}else{$and_id_origen.="";};
            if(!empty($clasificacion) && intVal($clasificacion) != 0) {$and_id_clasificacion .= " AND id_clasificacion= $clasificacion";}else{$and_id_clasificacion.="";};
            if(!empty($presentacion) && intVal($presentacion) != 0) {$and_id_presentacion .= " AND id_presentacion= $presentacion";}else{$and_id_presentacion.="";};
            if(!empty($unidad_medida) && intVal($unidad_medida) != 0) {$and_id_unidad_medida .= " AND id_unidad_medida= $unidad_medida";}else{$and_id_unidad_medida.="";};
            if(!empty($marca) && intVal($marca) != 0) {$and_id_marca .= " AND id_marca= $marca";}else{$and_id_marca .="";};
            if(!empty($laboratorio) && intVal($laboratorio) != 0) {$and_id_laboratorio .= " AND id_laboratorio= $laboratorio";}else{$and_id_laboratorio .="";};
            if(!empty($producto) && intVal($producto) != 0) {$and_id_producto .= " AND id_producto= $producto";}else{$and_id_producto .="";};
            if($con_sin == 1){$and_comision .= "AND comision > 0";}elseif ($con_sin == 2) {$and_comision .= "AND comision = 0";}else{$and_comision .= "";}

            $db->setQuery("UPDATE productos SET comision = $comision, comision_concepto = '$concepto'
                           WHERE 1=1 $and_id_tipo $and_id_rubro $and_id_procedencia $and_id_origen $and_id_clasificacion $and_id_presentacion $and_id_unidad_medida $and_id_marca $and_id_laboratorio $and_id_producto $and_comision");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Comision actualizado correctamente"]);
        break;
    }
