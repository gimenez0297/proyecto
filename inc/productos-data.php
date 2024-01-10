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

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING codigo LIKE '$search%' OR producto LIKE '$search%' OR  presentacion LIKE '$search%' OR laboratorio LIKE '$search%' OR principios_activos LIKE '$search%'";
            }
			
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
                                p.fraccion,
                                p.codigo,
                                p.observaciones,
                                p.controlado,
                                p.descripcion,
                                p.copete,
                                p.estado,
                                p.conservacion,
                                p.web,
                                p.iva,
                                CASE p.iva WHEN 1 THEN 'EXENTAS' WHEN 2 THEN '5%'  WHEN 3 THEN '10%'  END AS iva_str,
                                CASE p.conservacion WHEN 1 THEN 'NORMAL' WHEN 2 THEN 'REFRIGERADO' END AS conservacion_str,
                                p.indicaciones,
                                CASE p.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                                p.usuario,
                                p.usuario_modifica,
                                DATE_FORMAT(p.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                                p.destacar,
                                p.comision_concepto
                            FROM productos p
                            LEFT JOIN marcas m ON p.id_marca=m.id_marca
                            LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
                            LEFT JOIN tipos_productos tp ON p.id_tipo_producto=tp.id_tipo_producto
                            LEFT JOIN origenes o ON p.id_origen=o.id_origen
                            LEFT JOIN monedas mo ON p.id_moneda=mo.id_moneda
                            LEFT JOIN presentaciones pr ON p.id_presentacion=pr.id_presentacion
                            /*LEFT JOIN clasificaciones_productos cp ON p.id_clasificacion=cp.id_clasificacion_producto*/
                            LEFT JOIN productos_clasificaciones pp ON  p.id_producto = pp.id_producto
                            LEFT JOIN clasificaciones_productos cp ON pp.id_clasificacion=cp.id_clasificacion_producto
                            LEFT JOIN unidades_medidas um ON p.id_unidad_medida=um.id_unidad_medida
                            LEFT JOIN rubros ru ON p.id_rubro=ru.id_rubro
                            LEFT JOIN paises ps ON p.id_pais=ps.id_pais
                            GROUP BY p.id_producto
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

        case 'cargar_editar':
            $db = DataBase::conectar();
            $db->autocommit(false);

            $dropurl = $db->clearText($_POST['dropurl']);
            $files = $_FILES['file'];
            $files_name = $_FILES['file']['name'][0];
            $id_producto = $db->clearText($_POST['id_producto']);
            $codigo = $db->clearText($_POST['codigo']);
            $producto = mb_convert_case($db->clearText($_POST['producto']), MB_CASE_UPPER, "UTF-8");
            $id_tipo_producto = $db->clearText($_POST['tipo']);
            $id_rubro = $db->clearText($_POST['rubro']);
            $id_pais = $db->clearText($_POST['procedencia']);
            $id_origen = $db->clearText($_POST['origen']);
            //$id_clasificacion = $db->clearText($_POST['clasificacion']);
            $id_presentacion = $db->clearText($_POST['presentacion']);
            $id_unidad_medida = $db->clearText($_POST['unidad_medida']);
            $conservacion = $db->clearText($_POST['conservacion']);
            $id_marca = $db->clearText($_POST['marca']);
            $id_laboratorio = $db->clearText($_POST['laboratorio']);
            $precio = $db->clearText(quitaSeparadorMiles($_POST['precio']));
            $precio_fraccionado = $db->clearText(quitaSeparadorMiles($_POST['precio_fraccionado'])) ?: 0;
            $cantidad_fracciones = ($db->clearText(quitaSeparadorMiles($_POST['cantidad_fracciones']))) ?: 0;
            $descuento_fraccionado = ($db->clearText($_POST['descuento_fraccionado'])) ?: 0;
            $indicaciones =  mb_convert_case($db->clearText($_POST['indicaciones']), MB_CASE_UPPER, "UTF-8");
            $fuera_de_plaza = ($db->clearText($_POST['fuera_de_plaza'])) ?: 0;
            $fraccionado = ($db->clearText($_POST['fraccionado'])) ?: 0;
            $observaciones =  mb_convert_case($db->clearText($_POST['observaciones']), MB_CASE_UPPER, "UTF-8");
            $controlado = ($db->clearText($_POST['controlado'])) ?: 0;
            $descripcion = $db->clearText($_POST['descripcion']);
            $copete = $db->clearText($_POST['copete']);
            $web = ($db->clearText($_POST['web'])) ?: 0;
            $iva = $db->clearText($_POST['iva']);
            $etiquetas = $_POST['etiquetas'];
            $proveedores = json_decode($_POST['proveedores']);
            $principios = json_decode($_POST['principios']);
            $stock_niveles = json_decode($_POST['stock_niveles']);
            $clasificacion = json_decode($_POST['clasificacion']);
            $comision = $db->clearText($_POST['comision']);
            $comision_concepto = $db->clearText($_POST['comision_concepto']);
            $destacar = ($db->clearText($_POST['destacar'])) ?: 2;

            if (empty($dropurl)) { echo json_encode(["status" => "error", "mensaje" => "El tipo de formulario esta vacio"]); exit; }
            if (empty($producto)) { echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el nombre del producto"]); exit; }
            if (empty($id_tipo_producto)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo"]); exit; }
            //if (empty($id_rubro)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un rubro"]); exit; }
            if (empty($id_pais)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione la procedencia"]); exit; }
            if (empty($id_origen)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un origen"]); exit; }
            //if (empty($id_clasificacion)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una clasificacion"]); exit; }
            if (empty($id_presentacion)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una presentacion"]); exit; }
            if (empty($id_unidad_medida)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una unidad de medida"]); exit; }
            if (empty($conservacion)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el tipo de conservación"]); exit; }
            if (empty($precio)) { echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el precio del producto"]); exit; }
            if (empty($iva)) { echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el tipo de IVA"]); exit; }

            // Campos opcionales

            if (empty($id_marca)) { $id_marca = "NULL"; }
            if (empty($id_laboratorio)) { $id_laboratorio = "NULL"; }
            // Se pusieron opcionales a pedido del client
            if (empty($id_clasificacion)) { $id_clasificacion = "NULL"; }
            if (empty($id_rubro)) { $id_rubro = "NULL"; }

            if ($dropurl == "cargar") {

                $db->setQuery("INSERT INTO productos ( 
                    codigo, 
                    id_marca, 
                    id_laboratorio, 
                    id_tipo_producto, 
                    id_origen, 
                    id_presentacion, 
                    /*id_clasificacion,*/
                    id_unidad_medida, 
                    id_rubro, 
	                id_pais, 
                    producto, 
                    precio, 
                    precio_fraccionado, 
                    cantidad_fracciones, 
                    descuento_fraccionado, 
                    comision,
                    comision_concepto,
                    copete, 
                    web, 
                    iva,
                    descripcion, 
                    conservacion, 
                    controlado, 
                    indicaciones, 
                    fuera_de_plaza, 
                    fraccion,
                    observaciones,
                    estado, 
                    usuario, 
                    fecha,
                    destacar
	            ) VALUES ( 
                    '$codigo', 
                    $id_marca, 
                    $id_laboratorio, 
                    '$id_tipo_producto', 
                    '$id_origen', 
                    '$id_presentacion', 
                    /*$id_clasificacion,*/
                    '$id_unidad_medida', 
                    $id_rubro, 
                    '$id_pais', 
                    '$producto', 
                    '$precio', 
                    '$precio_fraccionado', 
                    '$cantidad_fracciones', 
                    '$descuento_fraccionado', 
                    '$comision',
                    '$comision_concepto',
                    '$copete', 
                    '$web', 
                    '$iva',
                    '$descripcion', 
                    '$conservacion', 
                    '$controlado', 
                    '$indicaciones', 
                    $fuera_de_plaza,
                    $fraccionado, 
                    '$observaciones',
                    '1', 
                    '$usuario', 
                    NOW(),
                    '$destacar'
                )");

                if (!$db->alter()) {
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "El código de producto ya existe"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    }
                    $db->rollback();
                    exit;
                }
                $id_producto = $db->getLastID();
                // Si no se carga el código se genera automáticamente
                if (empty($codigo)) {
                    

                    // Ejemplo
                    //$codigo = ean13_check_digit(78400000000+$ultimo_id); // Ya estaba comentado en el ejemplo
                    
                    // Usar desde aca y adaptar
                    //$cod_categoria = strval(str_pad($id_categoria, 3, '0', STR_PAD_LEFT));
                    //$cod_id_producto = strval(str_pad($ultimo_id, 6, '0', STR_PAD_LEFT));
                    //$codigo = ean13_check_digit('784'.$cod_categoria.$cod_id_producto);
                    
                    ////ACTUALIZAMOS EN PRODUCTO
                    //$db->setQuery("UPDATE productos SET codigo=$codigo WHERE id_producto=$ultimo_id");
                    //if (!$db->alter()) {
                        //echo json_encode(["status" => "error", "mensaje" => "Error al crear el código de barras. " . $db->getError()]);
                        //$db->rollback();
                        //exit;
                    //}
                    // Fin ejemplo

                    $cod_id_producto = strval(str_pad($id_producto, 9, '0', STR_PAD_LEFT));
                    $codigo = ean13_check_digit('784'.$cod_id_producto);
                    
                    //ACTUALIZAMOS EN PRODUCTO
                    $db->setQuery("UPDATE productos SET codigo='$codigo' WHERE id_producto='$id_producto'");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al crear el código de barras. " . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }

                $mensaje = "Producto registrado correctamente";
            }

            if ($dropurl == "editar") {

                if (empty($codigo)) {
                    $cod_id_producto = strval(str_pad($id_producto, 9, '0', STR_PAD_LEFT));
                    $codigo = ean13_check_digit('784'.$cod_id_producto);
                }

                $db->setQuery("UPDATE productos SET  
                    id_marca=$id_marca, 
                    id_laboratorio=$id_laboratorio, 
                    id_tipo_producto='$id_tipo_producto', 
                    id_origen='$id_origen', 
                    id_presentacion='$id_presentacion', 
                    /*id_clasificacion=$id_clasificacion,*/
                    id_unidad_medida='$id_unidad_medida', 
                    id_rubro=$id_rubro, 
	                id_pais='$id_pais', 
                    codigo='$codigo', 
                    producto='$producto', 
                    precio='$precio', 
                    precio_fraccionado='$precio_fraccionado', 
                    cantidad_fracciones='$cantidad_fracciones', 
                    descuento_fraccionado='$descuento_fraccionado', 
                    comision='$comision',
                    comision_concepto= '$comision_concepto',
                    copete='$copete',
                    web='$web', 
                    iva='$iva', 
                    descripcion='$descripcion', 
                    conservacion='$conservacion', 
                    controlado='$controlado', 
                    indicaciones='$indicaciones', 
                    fuera_de_plaza=$fuera_de_plaza, 
                    fraccion=$fraccionado, 
                    observaciones='$observaciones', 
                    usuario_modifica='$usuario', 
                    destacar='$destacar'
                WHERE id_producto='$id_producto'");

                if (!$db->alter()) {
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "El código de producto ya existe"]);
                    } else {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                    }
                    $db->rollback();
                    exit;
                }

                $mensaje = "Producto '$producto' modificado correctamente";

            }

            if ($files_name !== "blob") {
                foreach ($files as $k => $l) {
                    foreach ($l as $i => $v) {
                        if (!array_key_exists($i, $files)) {
                            $files[$i] = array();
                        }
                        $files[$i][$k] = $v;
                    }
                }
                foreach ($files as $key => $file) {
                    $foo = new \Verot\Upload\Upload($file);
                    if ($foo->uploaded) {
                        $targetPath = "../../archivos/multimedia/productos/";
                        if (!is_dir($targetPath)) {
                            mkdir($targetPath, 0777, true);
                        }

                        $foo->file_new_name_body = md5($id_producto);
                        $foo->image_convert      = jpg;
                        $foo->image_resize       = true;
                        $foo->image_ratio        = true;
                        $foo->image_ratio_crop   = true;
                        $foo->image_y            = 740;
                        $foo->image_x            = 740;
                        $foo->process($targetPath);
                        $foto = str_replace("../", "", $targetPath . $foo->file_dst_name);
                        if ($foo->processed) {
                            $db->setQuery("INSERT INTO productos_fotos (id_producto, foto, orden) VALUES ('$id_producto','$foto', '$key')");
                            if (!$db->alter()) {
                                echo json_encode(["status" => "error", "mensaje" => "Error al guardar la Imagen. " . $db->getError()]);
                                $db->rollback();
                                exit;
                            }
                            $foo->clean();
                        } else {
                            echo json_encode(["status" => "error", "mensaje" => "Error : " . $foo->error]);
                            $db->rollback();
                            exit;
                        }
                    }
                }
            }

            // Etiquetas
            $db->setQuery("DELETE FROM productos_etiquetas WHERE id_producto='$id_producto'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar las etiquetas. " . $db->getError()]);
                $db->rollback();
                exit;
            }
            if ($etiquetas) {
                foreach ($etiquetas as $value) {
                    $etiqueta = $db->clearText($value);
                    $db->setQuery("INSERT INTO productos_etiquetas (id_producto, etiqueta) VALUES('$id_producto', '$etiqueta')");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al guardar las etiquetas. " . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }
            }
            // Fin etiquetas

            // Proveedores

            $db->setQuery("SELECT * FROM `productos_proveedores` WHERE `proveedor_principal` = 1 AND `id_producto`= $id_producto");
            $pp = $db->loadObject();
            $proveedor_p       = $pp->id_proveedor;

            $db->setQuery("DELETE FROM productos_proveedores WHERE id_producto='$id_producto'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar los proveedores. " . $db->getError(), "error" => "proveedores"]);
                $db->rollback();
                exit;
            }

            if ($proveedores) {
                $verificacion = array_filter($proveedores, function($value){ return $value->proveedor_principal == 1; });
                
                if (count($verificacion) > 1) {
                    echo json_encode(["status" => "error", "mensaje" => "Solo puede haber un proveedor principal", "error" => "proveedores"]);
                    $db->rollback();
                    exit;
                }

                if (count($verificacion) < 1) {
                    echo json_encode(["status" => "error", "mensaje" => "Debe haber un proveedor principal", "error" => "proveedores"]);
                    $db->rollback();
                    exit;
                }

                foreach ($proveedores as $p) {
                    $id_proveedor = $db->clearText($p->id_proveedor);
                    $proveedor = $db->clearText($p->proveedor);
                    $codigo =  mb_convert_case($db->clearText($p->codigo), MB_CASE_UPPER, "UTF-8"); 
                    $costo = $db->clearText(quitaSeparadorMiles($p->costo));
                    $proveedor_principal = $db->clearText($p->proveedor_principal);

                    $db->setQuery("INSERT INTO productos_proveedores (id_producto, id_proveedor, codigo, costo,proveedor_principal) VALUES($id_producto, $id_proveedor, '$codigo', $costo,'$proveedor_principal')");
                    if (!$db->alter()) {
                        // id_producto e id_proveedor unicos, si se repite retorna este error
                        if ($db->getErrorCode() == 1062) {
                            echo json_encode(["status" => "error", "mensaje" => "El proveedor $proveedor ya fue agregado", "error" => "proveedores"]);
                            $db->rollback();
                            exit;
                        }
                        echo json_encode(["status" => "error", "mensaje" => "Error al guardar los proveedores. " . $db->getError(), "error" => "proveedores"]);
                        $db->rollback();
                        exit;
                    }  
                }

                if ($proveedor_p) {
                        $db->setQuery("SELECT * FROM `productos_proveedores` WHERE `proveedor_principal` = 1 AND `id_producto`= $id_producto");
                        $pp2 = $db->loadObject();
                        $proveedor_p2       = $pp2->id_proveedor;

                    if ($proveedor_p <> $proveedor_p2) {
                        $db->setQuery("DELETE FROM `descuentos_proveedores_productos` WHERE id_proveedor = $proveedor_p AND id_producto = $id_producto");
                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Error al guardar proveedor principal. " . $db->getError(), "error" => "proveedores"]);
                            $db->rollback();
                            exit;
                        }
                    }
                }
                
            }
            // Fin proveedores

            // Principios activos
            $db->setQuery("DELETE FROM productos_principios WHERE id_producto='$id_producto'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar los principios activos. " . $db->getError(), "error" => "principios"]);
                $db->rollback();
                exit;
            }

            $db->setQuery("SELECT principios_activos FROM tipos_productos WHERE id_tipo_producto='$id_tipo_producto'");
            $row = $db->loadObject();

            // if ($row->principios_activos == 1 && empty($principios)) {
            //     echo json_encode(["status" => "error", "mensaje" => "Favor complete la tabla de principios activos", "error" => "principios"]);
            //     $db->rollback();
            //     exit;
            // }

            foreach ($principios as $p) {
                $id_principio = $db->clearText($p->id_principio);
                $principio = $db->clearText($p->principio);

                $db->setQuery("INSERT INTO productos_principios (id_producto, id_principio) VALUES('$id_producto', '$id_principio')");
                if (!$db->alter()) {
                    // id_producto e id_principio unicos, si se repite retorna este error
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "El principio activo $principio ya fue agregado", "error" => "principios"]);
                        $db->rollback();
                        exit;
                    }
                    echo json_encode(["status" => "error", "mensaje" => "Error al guardar los principios activos. " . $db->getError(), "error" => "principios"]);
                    $db->rollback();
                    exit;
                }
            }
            // Fin principios activos



             // Clasificaiones
            $db->setQuery("DELETE FROM productos_clasificaciones WHERE id_producto='$id_producto'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar las clasificaciones. " . $db->getError(), "error" => "clasificaciones"]);
                $db->rollback();
                exit;
            }


            foreach ($clasificacion as $p) {
                $id_clasificacion = $db->clearText($p->id_clasificacion);
                $clasificacion = $db->clearText($p->clasificacion);
                
                $db->setQuery("INSERT INTO productos_clasificaciones (id_producto, id_clasificacion) VALUES('$id_producto', '$id_clasificacion')");
                if (!$db->alter()) {
                    // id_producto e id_principio unicos, si se repite retorna este error
                    if ($db->getErrorCode() == 1062) {
                        echo json_encode(["status" => "error", "mensaje" => "La clasificacion $clasificacion ya fue agregado", "error" => "clasificaciones"]);
                        $db->rollback();
                        exit;
                    }
                    echo json_encode(["status" => "error", "mensaje" => "Error al guardar las clasificaciones. " . $db->getError(), "error" => "clasificaciones"]);
                    $db->rollback();
                    exit;
                }
            }

             // Fin Clasificaciones


            // Stock niveles
            $db->setQuery("DELETE FROM stock_niveles WHERE id_producto=$id_producto");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar los niveles de stock", "error" => "stock_niveles"]);
                $db->rollback();
                exit;
            }

            foreach ($stock_niveles as $v) {
                $id_sucursal = $db->clearText($v->id_sucursal);
                $minimo = $db->clearText(quitaSeparadorMiles($v->minimo));
                $maximo = $db->clearText(quitaSeparadorMiles($v->maximo));

                $db->setQuery("INSERT INTO stock_niveles (id_sucursal, id_producto, minimo, maximo) VALUES($id_sucursal, $id_producto, $minimo, $maximo)");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar los niveles de stock", "error" => "stock_niveles"]);
                    $db->rollback();
                    exit;
                }
            }
            // Fin stock niveles

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => $mensaje]);

        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_POST['id_producto']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE productos SET estado='$estado' WHERE id_producto='$id_producto'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM productos WHERE id_producto = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este producto no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                }
                exit;
            }

            // Eliminar fotos
            $query = "SELECT id_producto_foto, foto FROM productos_fotos WHERE id_producto='$id'";
            $db->setQuery($query);
            $rows = ($db->loadObjectList()) ?: [];

            foreach ($rows as $row) {
                $id_producto_foto = $row->id_producto_foto;
                $foto = $row->foto;

                if ($id_producto_foto) {
                    $db->setQuery("DELETE FROM productos_fotos WHERE id_producto_foto='$id_producto_foto'");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al eliminar la foto '$foto'. " . $db->getError()]);
                        exit;
                    }
                    if (!unlink("../../" . $foto)) {
                        echo json_encode(["status" => "error", "mensaje" => "Error al eliminar la foto '$foto'. "]);
                        exit;
                    }
                }
            }

            // Eliminar proveedores
            $db->setQuery("DELETE FROM productos_proveedores WHERE id_producto='$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al eliminar los proveedores. " . $db->getError()]);
                exit;
            }

            // Eliminar etiquetas
            $db->setQuery("DELETE FROM productos_etiquetas WHERE id_producto='$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al eliminar las etiquetas. " . $db->getError()]);
                exit;
            }

            // Eliminar principios activos
            $db->setQuery("DELETE FROM productos_principios WHERE id_producto='$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error al eliminar los principios activos. " . $db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Producto '$nombre' eliminado correctamente"]);
        break;		

        case 'leer_fotos':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_POST['id_producto']);
            $db->setQuery("SELECT foto FROM productos_fotos WHERE id_producto='$id_producto'");
            $rows = $db->loadObjectList();
            if ($rows) {
                foreach ($rows as $r) {
                    $size       = filesize("../" . $r->foto);
                    $nombre_tmp = explode("/", $r->foto);
                    $nombre     = end($nombre_tmp);
                    $path       = '../'.$r->foto;
                    $salida[]   = ['name' => $nombre, 'size' => $size, 'path' => $path];
                }
            }
            echo json_encode($salida);
        break;

        case 'borrar_fotos':
            $db = DataBase::conectar();
            $foto = $db->clearText($_POST['foto']);
            $id_tmp2 = explode("_", $foto);
            $id_tmp = explode(".", $id_tmp2[0]);
            $id_md5 = $id_tmp[0];

            $query = "SELECT id_producto_foto, foto FROM productos_fotos WHERE MD5(id_producto)='$id_md5' AND foto LIKE '%$foto%'";
            $db->setQuery($query);
            $rows = $db->loadObject();
            $id_producto_foto = $rows->id_producto_foto;
            $foto = $rows->foto;

            if ($id_producto_foto) {
                $db->setQuery("DELETE FROM productos_fotos WHERE id_producto_foto = '$id_producto_foto'");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error al eliminar la foto '$foto'. " . $db->getError()]);
                    exit;
                }
                if (!unlink("../../" . $foto)) {
                    echo json_encode(["status" => "error", "mensaje" => "Error al eliminar la foto '$foto'. "]);
                    exit;
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Foto eliminada correctamente"]);
        break;

        case 'ver_etiquetas':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_POST['id_producto']);

            $db->setQuery("SELECT id_producto_etiqueta, etiqueta FROM productos_etiquetas WHERE id_producto='$id_producto'");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;

        case 'ver_principios':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_REQUEST['id_producto']);

            $db->setQuery("SELECT 
                            pp.id_producto_principio,
                            pa.id_principio,
                            pa.nombre AS principio
                            FROM principios_activos pa
                            JOIN productos_principios pp ON pa.id_principio=pp.id_principio
                            WHERE pp.id_producto='$id_producto'
                            ORDER BY nombre");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;

        case 'ver_proveedores':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_REQUEST['id_producto']);

            $db->setQuery("SELECT 
                            pp.id_producto_proveedor,
                            p.id_proveedor,
                            p.ruc,
                            p.proveedor,
                            p.nombre_fantasia,
                            p.contacto,
                            p.telefono,
                            pp.codigo,
                            pp.costo,
                            pp.proveedor_principal
                            FROM proveedores p
                            JOIN productos_proveedores pp ON p.id_proveedor=pp.id_proveedor
                            WHERE pp.id_producto='$id_producto'
                            ORDER BY proveedor");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;

        case 'ver_niveles_stock':
            $db = DataBase::conectar();
            $id = $db->clearText($_REQUEST['id']);

            if (isset($id) && !empty($id)) {
                $where = " AND id_producto=$id";
            } else {
                $where = " AND id_producto IS NULL";
            }

            $db->setQuery("SELECT 
                            s.id_sucursal,
                            s.sucursal,
                            IFNULL(sn.minimo, 0) AS minimo,
                            IFNULL(sn.maximo, 0) AS maximo
                            FROM sucursales s
                            LEFT JOIN stock_niveles sn ON s.id_sucursal=sn.id_sucursal $where
                            ORDER BY sucursal");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;

    
//CLASIFICACION

         case 'ver_clasificacion':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_REQUEST['id_producto']);

            $db->setQuery("SELECT 
                            pp.id_producto_clasificacion,
                            pp.id_clasificacion,
                            pa.clasificacion 
                            FROM clasificaciones_productos pa
                            JOIN productos_clasificaciones pp ON pa.id_clasificacion_producto = pp.id_producto_clasificacion
                            WHERE pp.id_producto='$id_producto'
                            ORDER BY pa.clasificacion");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;

// LOTES
        case 'ver_lotes':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_REQUEST['id_producto']);

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                            l.id_lote, 
                            l.lote, 
                            l.vencimiento, 
                            s.stock, 
                            s.fraccionado, 
                            l.costo, 
                            p.cantidad_fracciones as cant
                        FROM lotes l
                        JOIN stock s ON l.id_lote=s.id_lote
                        LEFT JOIN productos p ON p.id_producto = s.id_producto
                        WHERE s.id_producto=$id_producto
                        AND l.lote LIKE '%$term%'
                        GROUP BY l.lote
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

	}

?>
