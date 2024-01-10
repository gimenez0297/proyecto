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
            $sucursal = $db->clearText($_REQUEST['id_sucursal']);
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
            $estado = $db->clearText($_REQUEST['estado']);

            $where = "";
            $where_puntos .="";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', razon_social, ruc, telefono, celular, direccion ) LIKE '%$search%'";
            }

            if (!isset($_REQUEST['id_sucursal']) && empty($_REQUEST['id_sucursal'])) {
                $sucursal = $id_sucursal;
            }

            if(!empty($estado) && intVal($estado) == 2) {
                $where_puntos = "AND puntos > 0";
            }else if(!empty($estado) && intVal($estado) == 1){
                $where_puntos = "AND puntos = 0";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_cliente,
                            razon_social,
                            ruc,
                            direccion,
                            telefono,
                            celular,
                            email,
                            id_tipo,
                            tipo,
                            obs,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            referencia,
                            longitud,
                            latitud, 
                            puntos 
                            FROM clientes  
                            WHERE 1=1 $where_puntos
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

        case 'ver_detalle':
        $db             = DataBase::conectar();
        $id_cliente = $db->clearText($_GET['id_cliente']);
        $estado = $db->clearText($_REQUEST['estado']);

        $where = "";
        $where_puntos ="";

        if(!empty($estado) && intVal($estado) == 2) {
            $where_puntos = "AND (puntos > 0)";
        }else if(!empty($estado) && intVal($estado) == 1){
            $where_puntos = "AND (puntos = 0)";
        }

        $db->setQuery("SELECT 
                        cp.id_cliente_punto,
                        cp.puntos,
                        cp.utilizados,
                        cp.puntos-utilizados AS total,
                        DATE_FORMAT(f.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                        CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero
                    FROM clientes_puntos cp
                    JOIN facturas f ON cp.id_factura = f.id_factura 
                    JOIN timbrados t ON t.id_timbrado = f.id_timbrado 
                    WHERE  cp.estado IN (0,1) AND cp.id_cliente =$id_cliente $where_puntos");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;
	}

?>
