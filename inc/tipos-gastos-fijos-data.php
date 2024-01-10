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
                $having = "HAVING CONCAT_WS(' ', nombre, tipo_gasto) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                     st.id_gastos_fijos, 
                                     st.id_tipo_gastos,
                                     p.id_proveedor, 
                                     n.nombre AS descripcion,       
                                     st.id_tipo_factura, 
                                     pc.nombre_comprobante, 
                                     p.proveedor,                       
                                     st.ruc, 
                                     st.monto,
                                     st.iva_10,
                                     st.iva_5,
                                     st.concepto,
                                     st.nombre,
                                     st.exenta
                                    FROM gastos_fijos st
                                    LEFT JOIN proveedores p ON st.id_proveedor = p.id_proveedor
                                    LEFT JOIN tipos_comprobantes pc ON pc.id_tipo_comprobante = st.id_tipo_factura
                                    LEFT JOIN tipos_gastos n ON n.id_tipo_gasto = st.id_tipo_gastos         
                                    ");
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
		

        case 'cargar':
            $db = DataBase::conectar();
            $id_tipo_gasto = $db->clearText($_POST['id_tipo_gasto']);
            $razon_social = $db->clearText($_POST['razon_social']);
            $id_tipo_factura = $db->clearText($_POST['id_tipo_factura']);
            $ruc = $db->clearText($_POST['ruc']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $concepto = mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            $iva_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']));
            $iva_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']));
            $exenta = quitaSeparadorMiles($db->clearText($_POST['exenta']));
            
            if (empty($id_tipo_gasto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo Gastos"]);
                exit;
            }
            if (empty($id_tipo_factura)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de factura"]);
                exit;
            }
            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Ruc"]);
                exit;
            }
            if (empty($razon_social)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una Razon Social"]);
                exit;
            }
            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto"]);
                exit;
            }
            if ($monto == 0) {
                echo json_encode(["status" => "error", "mensaje" => "No puede cargar Facturas con Monto 0"]);
                exit;
            }
            
            $db->setQuery("INSERT INTO gastos_fijos (
                                                     id_tipo_gastos, 
                                                     id_proveedor, 
                                                     id_tipo_factura, 
                                                     ruc, 
                                                     monto,
                                                     concepto,
                                                     nombre,
                                                     iva_10,
                                                     iva_5,
                                                     exenta)
                                        VALUES  
                                                 ($id_tipo_gasto, 
                                                 '$razon_social',
                                                  $id_tipo_factura, 
                                                  '$ruc',
                                                  $monto,
                                                  '$concepto',
                                                  '$nombre',
                                                  $iva_10,
                                                  $iva_5,
                                                  $exenta)"); 
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Gasto Fijo registrado correctamente"]);
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_gastos_fijos = $db->clearText($_POST['id_gastos_fijos']);
            $id_tipo_gasto = $db->clearText($_POST['id_tipo_gasto']);
            $razon_social = $db->clearText($_POST['razon_social']);
            $id_tipo_factura = $db->clearText($_POST['id_tipo_factura']);
            $ruc = $db->clearText($_POST['ruc']);
            $monto = quitaSeparadorMiles($db->clearText($_POST['monto']));
            $concepto = mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
            $nombre = mb_convert_case($db->clearText($_POST['nombre']), MB_CASE_UPPER, "UTF-8");
            $iva_10 = quitaSeparadorMiles($db->clearText($_POST['iva_10']));
            $iva_5 = quitaSeparadorMiles($db->clearText($_POST['iva_5']));
            $exenta = quitaSeparadorMiles($db->clearText($_POST['exenta']));

            if (empty($id_tipo_gasto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Tipo Gastos"]);
                exit;
            }
            if (empty($id_tipo_factura)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un tipo de factura"]);
                exit;
            }
            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Ruc"]);
                exit;
            }

            if (empty($razon_social)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una Razon Social"]);
                exit;
            }
            if (empty($concepto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingresar el concepto"]);
                exit;
            }
            if (empty($nombre)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor especificar el nombre"]);
                exit;
            }
            if (empty($monto)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Monto"]);
                exit;
            }
            if ($monto == 0) {
                echo json_encode(["status" => "error", "mensaje" => "No puede cargar Gastos Fijos con Monto 0"]);
                exit;
            }

             $db->setQuery("UPDATE gastos_fijos SET 
                                                    id_tipo_gastos= '$id_tipo_gasto', 
                                                    id_proveedor='$razon_social',
                                                    id_tipo_factura='$id_tipo_factura', 
                                                    ruc='$ruc',
                                                    monto=$monto,
                                                    concepto='$concepto',
                                                    nombre='$nombre',
                                                    iva_10=$iva_10,
                                                    iva_5=$iva_5,
                                                    exenta=$exenta
                                                 WHERE id_gastos_fijos = $id_gastos_fijos");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Gasto Fijo Modificado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            
            
            $db->setQuery("DELETE FROM gastos_fijos WHERE id_gastos_fijos = '$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }
            echo json_encode(["status" => "ok", "mensaje" => "Gasto Fijo eliminado correctamente"]);
        break;		

	}

?>
