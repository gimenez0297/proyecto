<?php
	include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;

	switch ($q){

		case 'editar':
            $db = DataBase::conectar();
			$cantidad_acumulacion = quitaSeparadorMiles($db->clearText($_POST['cantidad_acumulacion']));
			$cantidad_canjeo = quitaSeparadorMiles($db->clearText($_POST['cantidad_canjeo']));
			$puntos_acumulacion	= quitaSeparadorMiles($db->clearText($_POST['puntos_acumulacion'])) ? : 0;
			$puntos_canjeo	= quitaSeparadorMiles($db->clearText($_POST['puntos_canjeo'])) ? : 0;
			$configuracion_acumulacion = $db->clearText($_POST['configuracion_acumulacion']);
			$configuracion_canjeo = $db->clearText($_POST['configuracion_canjeo']);
			$acumular_credito= $db->clearText($_POST['acumular_credito']) ? :  0 ;
			$canjeo_credito= $db->clearText($_POST['canjeo_credito']) ? :  0 ;
			$periodo_canje= $db->clearText(quitaSeparadorMiles($_POST['periodo_vencimiento']));
			$productos_acumulacion = json_decode($_POST['productos_acumulacion']);
            $productos_canjeo = json_decode($_POST['productos_canjeo']);

            
            if (empty($cantidad_acumulacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un cantidad para acumulación"]);
                exit;
            }

			if (empty($cantidad_canjeo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un cantidad para canjeo"]);
                exit;
            }

			if (empty($puntos_acumulacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una cantidad para acumulación "]);
                exit;
            }

			if (empty($puntos_canjeo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una cantidad para canjeo "]);
                exit;
            }

			if (empty($configuracion_acumulacion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Metodo de Acumulación "]);
                exit;
            }

			if (empty($periodo_canje)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el Periodo de Vencimiento."]);
                exit;
            }

			if ($configuracion_acumulacion == 2 || $configuracion_acumulacion == 3||$configuracion_acumulacion == 6) {
				if (empty($productos_acumulacion)) {
					echo json_encode(["status" => "error", "mensaje" => "Favor agregar producto/s en la tabla de acumulación."]);
					exit;
				}
			}

			if (empty($configuracion_canjeo)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Metodo de Canjeo "]);
                exit;
            }

			if ($configuracion_canjeo == 2 || $configuracion_canjeo == 3||$configuracion_canjeo == 6) {
				if (empty($productos_canjeo)) {
					echo json_encode(["status" => "error", "mensaje" => "Favor agregar producto/s en la tabla de canejo."]);
					exit;
				}
			}

          	$db->setQuery("UPDATE plan_puntos SET
							ventas_credito = '$acumular_credito',
							configuracion = '$configuracion_acumulacion',
							cantidad = $cantidad_acumulacion,
							puntos = $puntos_acumulacion,
							periodo_canje=$periodo_canje,
							fecha = NOW(),
							usuario = '$usuario'
							WHERE tipo = 1");
			  
			if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
      		}

			$db->setQuery("SELECT * FROM plan_puntos WHERE tipo=1");
			$row = $db->loadObject();

			$id_productos_acumulacion = $row->id_plan_puntos;

			$db->setQuery("DELETE FROM plan_puntos_productos WHERE id_plan_punto = 1");
				if (!$db->alter()) {
					echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error"]);
					$db->rollback(); // Revertimos los cambios
					exit;
				}

			if (isset($productos_acumulacion)) {
				foreach ($productos_acumulacion as $key => $value) {
					
					$id_producto = $db->clearText($value->id_producto);
					$puntos = $db->clearText($value->puntos) ?: 0;

					if (empty($id_producto)) {
						echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Producto."]);
						exit;
					}

					$db->setQuery("INSERT INTO plan_puntos_productos (id_plan_punto,id_producto,puntos,fecha,usuario)
								VALUES($id_productos_acumulacion,$id_producto,$puntos,NOW(),'$usuario');");
					if (!$db->alter()) {
						echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error"]);
						$db->rollback(); // Revertimos los cambios
						exit;
					}
				}
			}

			$db->setQuery("UPDATE plan_puntos SET
							ventas_credito = '$canjeo_credito',
							configuracion = '$configuracion_canjeo',
							cantidad = $cantidad_canjeo,
							puntos = $puntos_canjeo,
							periodo_canje=$periodo_canje,
							fecha = NOW(),
							usuario = '$usuario'
							WHERE tipo = 2");
			if (!$db->alter()) {
				echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
				exit;
			}

			$db->setQuery("SELECT * FROM plan_puntos WHERE tipo=2");
			$row = $db->loadObject();

			$id_productos_canjeo = $row->id_plan_puntos;

			$db->setQuery("DELETE FROM plan_puntos_productos WHERE id_plan_punto = 2");
				if (!$db->alter()) {
					echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error"]);
					$db->rollback(); // Revertimos los cambios
					exit;
				}

			if (isset($productos_canjeo)) {
				foreach ($productos_canjeo as $key => $value) {
					
					$id_producto = $db->clearText($value->id_producto);
					$puntos = $db->clearText($value->puntos) ?: 0;

					if (empty($id_producto)) {
						echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Producto."]);
						exit;
					}


					$db->setQuery("INSERT INTO plan_puntos_productos (id_plan_punto,id_producto,puntos,fecha,usuario)
								VALUES($id_productos_canjeo,$id_producto,$puntos,NOW(),'$usuario');");
					if (!$db->alter()) {
						echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los productos", "error"]);
						$db->rollback(); // Revertimos los cambios
						exit;
					}
				}
			}

            // Se actualizan los puntos que vencen con la nueva configuración
			$db->setQuery("UPDATE clientes_puntos SET estado = 2, fecha_actualizacion = NOW() WHERE estado = 0 AND DATEDIFF(DATE(NOW()) , DATE(fecha)) > $periodo_canje");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de los puntos de los clientes"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            // Se actualizan los puntos que están disponibles con la nueva configuración
			$db->setQuery("UPDATE clientes_puntos SET estado = 0, fecha_actualizacion = NOW() WHERE estado = 2 AND DATEDIFF(DATE(NOW()) , DATE(fecha)) < $periodo_canje");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de los puntos de los clientes"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            if (!actualiza_puntos_clientes()) {
			    $db->rollback(); // Revertimos los cambios
			    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar los puntos de los clientes"]);
			    exit;
			}

            echo json_encode(["status" => "ok", "mensaje" => "Puntos de cliente se registro correctamente"]);

			break;

			case 'ver':
				$db = DataBase::conectar();
				$db->setQuery("SELECT 
									ventas_credito,
									configuracion,
									puntos,
									tipo,
									cantidad,
									periodo_canje
									FROM plan_puntos");
				$rows = $db->loadObjectList();
				echo json_encode($rows);
			break;
			
			case 'ver_productos':
				$db = DataBase::conectar();
				$id_proveedor = $db->clearText($_REQUEST['id_proveedor']);
				$id_origen = $db->clearText($_REQUEST['id_origen']);
				$id_tipo_producto = $db->clearText($_REQUEST['id_tipo']);
				$id_laboratorio = $db->clearText($_REQUEST['id_laboratorio']);
				$id_marca = $db->clearText($_REQUEST['id_marca']);
				$id_rubro = $db->clearText($_REQUEST['id_rubro']);
	
				$where = "";
				if (isset($id_proveedor) && !empty($id_proveedor) && intval($id_proveedor)  > 0) {
					$where .= "AND pp.id_proveedor=$id_proveedor AND proveedor_principal=1";
				}
				if (isset($id_origen) && !empty($id_origen) && intval($id_origen)  > 0) {
					$where .= "AND p.id_origen=$id_origen";
				}
				if (isset($id_tipo_producto) && !empty($id_tipo_producto) && intval($id_tipo_producto)  > 0) {
					$where .= "AND p.id_tipo_producto=$id_tipo_producto";
				}
				if (isset($id_laboratorio) && !empty($id_laboratorio) && intval($id_laboratorio)  > 0) {
					$where .= "AND p.id_laboratorio=$id_laboratorio";
				}
				if (isset($id_marca) && !empty($id_marca) && intval($id_marca)  > 0) {
					$where .= "AND p.id_marca=$id_marca";
				}
				if (isset($id_rubro) && !empty($id_rubro) && intval($id_rubro)  > 0) {
					$where .= "AND p.id_rubro=$id_rubro";
				}
	
				//Parametros de ordenamiento, busqueda y paginacion
				$search = $db->clearText($_REQUEST['search']);
				$limit = $db->clearText($_REQUEST['limit']);
				$offset = $db->clearText($_REQUEST['offset']);
				$order = $db->clearText($_REQUEST['order']);
				$sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
				if (isset($search) && !empty($search)) {
					$having = "HAVING CONCAT_WS(' ', codigo, producto, presentacion, laboratorio, principios_activos) LIKE '%$search%'";
				}
				
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
										p.id_producto, 
										p.producto, 
										p.codigo, 
										p.cantidad_fracciones, 
										p.observaciones, 
										pre.id_presentacion, 
										pre.presentacion,
										l.id_laboratorio,
										l.laboratorio,
										pa.id_principio,
										p.comision,
										p.descuento_fraccionado,
										pp.id_proveedor,
										-- GROUP_CONCAT(pa.nombre SEPARATOR ', ') AS principios_activos,
										pa.nombre AS principios_activos,
										IFNULL(p.precio, 0) AS precio, 
										IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado,
										CASE p.descuento_fraccionado WHEN 1 THEN 'Si' ELSE 'No' END AS descuento_fraccionado
								FROM productos p
								LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
								LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
								LEFT JOIN productos_principios ppa ON p.id_producto=ppa.id_producto
								LEFT JOIN principios_activos pa ON ppa.id_principio=pa.id_principio
								LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto
								WHERE p.estado=1 $where
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

			case 'ver_productos_cargados':
				$db = DataBase::conectar();
				$id= $db->clearText($_GET['id']);
				$db->setQuery("SELECT ppp.id_plan_punto,
								p.producto,
								p.codigo,
								pr.presentacion,
								ppp.puntos,
								p.id_producto
								FROM plan_puntos_productos ppp
								LEFT JOIN plan_puntos pp ON pp.id_plan_puntos=ppp.id_plan_punto
								LEFT JOIN productos p ON p.id_producto=ppp.id_producto
								LEFT JOIN presentaciones pr ON pr.id_presentacion=p.id_presentacion
								WHERE ppp.id_plan_punto=$id");
				$rows = $db->loadObjectList();
				echo json_encode($rows);
			break;
            
            case 'recuperar_busqueda':
				$db = DataBase::conectar();
				$id_proveedor = $db->clearText($_REQUEST['id_proveedor']);
				$id_origen = $db->clearText($_REQUEST['id_origen']);
				$id_tipo_producto = $db->clearText($_REQUEST['id_tipo']);
				$id_laboratorio = $db->clearText($_REQUEST['id_laboratorio']);
				$id_marca = $db->clearText($_REQUEST['id_marca']);
				$id_rubro = $db->clearText($_REQUEST['id_rubro']);
	
				$where = "";
				if (isset($id_proveedor) && !empty($id_proveedor) && intval($id_proveedor)  > 0) {
					$where .= "AND pp.id_proveedor=$id_proveedor AND proveedor_principal=1";
				}
				if (isset($id_origen) && !empty($id_origen) && intval($id_origen)  > 0) {
					$where .= "AND p.id_origen=$id_origen";
				}
				if (isset($id_tipo_producto) && !empty($id_tipo_producto) && intval($id_tipo_producto)  > 0) {
					$where .= "AND p.id_tipo_producto=$id_tipo_producto";
				}
				if (isset($id_laboratorio) && !empty($id_laboratorio) && intval($id_laboratorio)  > 0) {
					$where .= "AND p.id_laboratorio=$id_laboratorio";
				}
				if (isset($id_marca) && !empty($id_marca) && intval($id_marca)  > 0) {
					$where .= "AND p.id_marca=$id_marca";
				}
				if (isset($id_rubro) && !empty($id_rubro) && intval($id_rubro)  > 0) {
					$where .= "AND p.id_rubro=$id_rubro";
				}
				
				$db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
										p.id_producto, 
										p.producto, 
										p.codigo, 
										p.cantidad_fracciones, 
										p.observaciones, 
										pre.id_presentacion, 
										pre.presentacion,
										l.id_laboratorio,
										l.laboratorio,
										pa.id_principio,
										p.comision,
										p.descuento_fraccionado,
										pp.id_proveedor,
										-- GROUP_CONCAT(pa.nombre SEPARATOR ', ') AS principios_activos,
										pa.nombre AS principios_activos,
										IFNULL(p.precio, 0) AS precio, 
										IFNULL(p.precio_fraccionado, 0) AS precio_fraccionado,
										CASE p.descuento_fraccionado WHEN 1 THEN 'Si' ELSE 'No' END AS descuento_fraccionado
								FROM productos p
								LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
								LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
								LEFT JOIN productos_principios ppa ON p.id_producto=ppa.id_producto
								LEFT JOIN principios_activos pa ON ppa.id_principio=pa.id_principio
								LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto
								WHERE p.estado=1 $where
								GROUP BY p.id_producto");
				$rows = $db->loadObjectList();

				echo json_encode(["datos" => $rows]);
			break;
    
	}
?>
