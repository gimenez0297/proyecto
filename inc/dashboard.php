<?php
	include ("funciones.php");
	if (!verificaLogin(null)) {
		echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
		exit;
	}
	$q = $_REQUEST['q'];
	$usuario = $auth->getUsername();
	$datos_usuario = datosUsuario($usuario);
	$id_sucursal = $datos_usuario->id_sucursal;
	$id_rol = $datos_usuario->id_rol;
	
	switch ($q) {

		case 'ingresos':
			$db = DataBase::conectar();
			if ($id_rol > 1) {
				$sucursal = "AND id_sucursal=$id_sucursal";
			}
			
			$db->setQuery("SELECT IFNULL(SUM(total_a_pagar), 0) AS total, cantidad AS cantidad_ventas
							FROM facturas
							WHERE EXTRACT(YEAR_MONTH FROM fecha) = EXTRACT(YEAR_MONTH FROM CURDATE()) AND estado NOT LIKE 'Anulad%' $sucursal");
			$ventas = $db->loadObject();

			$db->setQuery("SELECT IFNULL(SUM(v.total_a_pagar), 0) AS total, cantidad AS cantidad_ventas, DATE_FORMAT(c.fecha_mysql, '%d/%m/%Y') AS fecha
							FROM calendario c
							LEFT JOIN (SELECT id_factura, total_a_pagar, fecha
							FROM facturas WHERE estado NOT LIKE 'Anulad%' $sucursal) v ON DATE(v.fecha)=c.fecha_mysql
							WHERE EXTRACT(YEAR_MONTH FROM c.fecha_mysql) = EXTRACT(YEAR_MONTH FROM CURDATE())
							GROUP BY c.fecha_mysql");
			$ventas_por_dia = $db->loadObjectList();

			$salida = [
				"ventas" => $ventas,
				"ventas_por_dia" => $ventas_por_dia
			];

			echo json_encode($salida);

		break;

		case 'egresos':
			$db = DataBase::conectar();
			if ($id_rol > 1) {
				$sucursal = "AND c.id_sucursal=$id_sucursal";
			}
			
			$db->setQuery("SELECT IFNULL(SUM(mc.monto), 0) AS monto
							FROM movimientos_caja mc
							JOIN caja c ON c.id_caja=mc.id_caja
							WHERE EXTRACT(YEAR_MONTH FROM mc.fecha) = EXTRACT(YEAR_MONTH FROM CURDATE()) $sucursal");
			$egresos = $db->loadObject();

			$db->setQuery("SELECT IFNULL(SUM(g.monto), 0) AS monto, DATE_FORMAT(cal.fecha_mysql, '%d/%m/%Y') AS fecha
							FROM calendario cal
							LEFT JOIN (SELECT mc.monto, mc.fecha FROM movimientos_caja mc JOIN caja c ON c.id_caja=mc.id_caja WHERE 1=1 $sucursal) g ON DATE(g.fecha)=cal.fecha_mysql
							WHERE EXTRACT(YEAR_MONTH FROM cal.fecha_mysql) = EXTRACT(YEAR_MONTH FROM CURDATE())
							GROUP BY cal.fecha_mysql");
			$egresos_por_dia = $db->loadObjectList();

			$salida = [
				"egresos" => $egresos,
				"egresos_por_dia" => $egresos_por_dia
			];

			echo json_encode($salida);

		break;

		case 'utilidades':
			$db = DataBase::conectar();
			if ($id_rol > 1) {
				$sucursal = "AND v.id_sucursal=$id_sucursal";
			}
			
			$db->setQuery("SELECT IFNULL(SUM(total_a_pagar - IFNULL(total_costo,(SELECT SUM(p.costo) FROM productos p INNER JOIN ventas_detalles vd ON vd.id_producto=p.id_producto WHERE vd.id_factura=v.id_factura GROUP BY vd.id_factura))), 0) AS utilidades
							FROM ventas v WHERE EXTRACT(YEAR_MONTH FROM fecha) = EXTRACT(YEAR_MONTH FROM CURDATE()) AND estado!='Anulada' $sucursal");
			$utilidades = $db->loadObject();

			$db->setQuery("SELECT IFNULL(SUM(u.utilidades), 0) AS utilidades, DATE_FORMAT(c.fecha_mysql, '%d/%m/%Y') AS fecha
							FROM calendario c
							LEFT JOIN (SELECT total_a_pagar - IFNULL(total_costo,(SELECT SUM(p.costo) FROM productos p INNER JOIN ventas_detalles vd ON vd.id_producto=p.id_producto WHERE vd.id_factura=v.id_factura GROUP BY vd.id_factura)) AS utilidades, v.fecha
							FROM ventas v WHERE estado!='Anulada' $sucursal) u ON DATE(u.fecha)=c.fecha_mysql
							WHERE EXTRACT(YEAR_MONTH FROM c.fecha_mysql) = EXTRACT(YEAR_MONTH FROM CURDATE())
							GROUP BY c.fecha_mysql");
			$utilidades_por_dia = $db->loadObjectList();

			$salida = [
				"utilidades" => $utilidades,
				"utilidades_por_dia" => $utilidades_por_dia
			];

			echo json_encode($salida);

		break;

	}

?>