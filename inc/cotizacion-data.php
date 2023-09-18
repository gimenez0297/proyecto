<?php

	include('funciones.php');
	if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}
	$db = DataBase::conectar();
	$q = $db->clearText($_REQUEST['q']);
	$usuario = $auth->getUsername();
	
	switch ($q){
		
		case 'ver_cotizacion':
		//Comprobamos si existe ya la cotizacion de hoy
		$db->setQuery("SELECT id_cotizacion, compra, venta FROM cotizaciones WHERE DATE(fecha)=CURDATE() AND id_moneda='1'");
		$row = $db->loadObject();
		if (empty($row)){
			function file_get_contents_curl($url) { 
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE); 
				curl_setopt($ch, CURLOPT_HEADER, 0); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_URL, $url); 
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
				$data = curl_exec($ch); 
				curl_close($ch); 
				return $data; 
			}

			$sitio="http://cotizext.maxicambios.com.py/maxicambios.xml";
			$content=@file_get_contents_curl($sitio);


			//trae todas las compras de las monedas
			preg_match_all('%<compra>(.*?)</compra>%i', $content, $compras);
			$compra_dolar=$compras[1][0];
			$compra_peso_arg=$compras[1][1];
			$compra_real=$compras[1][2];
			$compra_peso_uy=$compras[1][3];
			$compra_euro=$compras[1][4];
			$compra_yen=$compras[1][5];
			$compra_peso_mex=$compras[1][6];
			$compra_soles_peru=$compras[1][7];
			$compra_peso_bol=$compras[1][8];
			$compra_peso_col=$compras[1][9];

			//trae todas las ventas de las monedas
			preg_match_all('%<venta>(.*?)</venta>%i', $content, $ventas);
			$venta_dolar=$ventas[1][0];
			$venta_peso_arg=$ventas[1][1];
			$venta_real=$ventas[1][2];
			$venta_peso_uy=$ventas[1][3];
			$venta_euro=$ventas[1][4];
			$venta_yen=$ventas[1][5];
			$venta_peso_mex=$ventas[1][6];
			$venta_soles_peru=$ventas[1][7];
			$venta_peso_bol=$ventas[1][8];
			$venta_peso_col=$ventas[1][9];

			if (!empty($venta_dolar)){
				$db->setQuery("INSERT INTO cotizaciones(id_moneda,compra,venta,fecha) VALUES ('1','$compra_dolar','$venta_dolar',NOW())");
				$db->alter();
			}
		}

		$db->setQuery("SELECT * FROM cotizaciones WHERE id_moneda='1' ORDER BY 1 DESC LIMIT 1");
		$row = $db->loadObject();
		
		echo json_encode(["dolar_venta"=>$row->venta,"id_cotizacion"=>$row->id_cotizacion]);
		break;
		
		case 'actualizar_cotizacion':
			$dolar_venta = $db->clearText($_POST['dolar_venta_cotiza']);
			if (empty($dolar_venta)) { echo "Error. Favor ingrese monto en guaraníes equivalente a 1 dólar americano en la venta."; exit; }
			
			$db->setQuery("UPDATE cotizaciones SET venta='$dolar_venta', fecha=NOW(), usuario='$usuario' WHERE DATE(fecha)=DATE(NOW())");
			if(!$db->alter()){
				echo "Error. ".$db->getError();
			}else{
				echo "Cotización actualizada exitosamente";
			}
		break;
	}

?>