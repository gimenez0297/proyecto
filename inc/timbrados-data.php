<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario 		= $auth->getUsername();
	$datosUsuario 	= datosUsuario($usuario);
	$id_usuario_str = $datosUsuario->id;
	$usuario_str 	= $datosUsuario->username;

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
                $where = "AND CONCAT_WS(' ', CASE tipo
                WHEN 0 THEN 'FACTURA'
                ELSE 'OTROS' END, ruc, timbrado, cod_establecimiento, punto_de_expedicion, sucursal, desde, hasta, estado, CONCAT(cod_establecimiento,'-',punto_de_expedicion,'-',desde),
                CONCAT(cod_establecimiento,'-',punto_de_expedicion,'-',hasta)) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT 
                            SQL_CALC_FOUND_ROWS 
                            t.id_timbrado,
                            t.id_caja,
                            c.numero,
                            t.ruc, 
                            t.timbrado,
                            t.cod_establecimiento,
                            t.punto_de_expedicion,
                            t.fin_vigencia,
                            t.inicio_vigencia,
                            t.desde,
                            t.hasta,
                            t.tipo,
                            t.estado,
                            s.sucursal,
                            CASE t.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' WHEN 2 THEN 'Caducado' END AS estado_str,
                            CASE t.tipo
                            WHEN 0 THEN 'FACTURA'
                            WHEN 1 THEN 'NOTA DE CREDITO'
                            WHEN 2 THEN 'NOTA DE REMISION'
                            ELSE 'OTROS' END AS 'tipo_str',
                            t.id_sucursal,
                            DATE_FORMAT(t.inicio_vigencia, '%d/%m/%Y') AS inicio_vigencia_str,
                            DATE_FORMAT(t.fin_vigencia, '%d/%m/%Y') AS fin_vigencia_str,
                            membrete,
                            CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',t.desde) AS nro_factura_desde,
                            CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',t.hasta) AS nro_factura_hasta
                            FROM timbrados t
                            LEFT JOIN sucursales s ON s.id_sucursal=t.id_sucursal
                            LEFT JOIN cajas c ON c.id_caja = t.id_caja
                            WHERE 1=1 $where
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

        case 'cargar':
            $db = DataBase::conectar();
            $tipo_documento= $db->clearText($_POST['tipo_documento']);
            $id_sucursal = $db->clearText($_POST['id_sucursal']);
            $id_caja = $db->clearText($_POST['id_caja']);
            $ruc = $db->clearText($_POST['ruc']);
            $timbrado = $db->clearText($_POST['timbrado']);
            $establecimiento = $db->clearText($_POST['establecimiento']);
            $expedicion = $db->clearText($_POST['expedicion']);
            $fecha_inicio = $db->clearText($_POST['fecha_inicio']);
            $fecha_fin = $db->clearText($_POST['fecha_fin']);
            $desde = $db->clearText($_POST['desde']);
            $hasta = $db->clearText($_POST['hasta']);
            $membrete = $db->clearText($_POST['membrete']);

            $hoy = date("Y-m-d");

            if ($fecha_fin < $hoy) {
                $estado = '2';
            }else {
                $estado = '1';
            }
            
            if ($fecha_inicio > $fecha_fin) {
                echo json_encode(["status" => "error", "mensaje" => "La Fecha de Inicio no puede ser mayor a la Fecha Fin"]);
                exit;
            }
            
            if (empty($id_sucursal)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una sucursal"]);
                exit;
            }
            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un R.U.C."]);
                exit;
            }
            if (empty($timbrado)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Timbrado"]);
                exit;
            }
            if (empty($establecimiento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Cod. Establecimiento"]);
                exit;
            }
            if (empty($expedicion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Punto de expedicion"]);
                exit;
            }
            if (empty($fecha_inicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una Fecha de Vigencia"]);
                exit;
            }
            if (empty($fecha_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una Fecha de Fin de Vigencia"]);
                exit;
            }
            if (empty($desde)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nro. Desde"]);
                exit;
            }
            if (empty($hasta)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nro. Hasta"]);
                exit;
            }

            if ($desde > $hasta) {
                echo json_encode(["status" => "error", "mensaje" => "Desde no puede ser mayor que el Hasta"]);
                exit;
            }

            $db->setQuery("SELECT * FROM sucursales WHERE id_sucursal = $id_sucursal");
            $deposito = $db->loadObject()->deposito;

            if ($tipo_documento == 0 && empty($id_caja) && $deposito == 0) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una Caja"]);
                exit;
            }

            if (empty($id_caja)) $id_caja = "NULL";

            $db->setQuery("INSERT INTO timbrados (
                ruc,
                timbrado,
                id_sucursal,
                id_caja,
                cod_establecimiento,
                punto_de_expedicion,
                inicio_vigencia,
                fin_vigencia,
                desde,
                hasta,
                estado,
                tipo,
                membrete
              )
              VALUES
                ( '$ruc',
                  '$timbrado',
                  $id_sucursal,
                  $id_caja,
                  '$establecimiento',
                  '$expedicion',
                  '$fecha_inicio',
                  '$fecha_fin',
                  '$desde',
                  '$hasta',
                  '$estado',
                  '$tipo_documento',
                  '$membrete'
                );");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Timbrado registrado correctamente"]);
            

        break;

        case 'editar':
            $db              = DataBase::conectar();
            $id_timbrado     = $db->clearText($_POST['hidden_id']);
            $id_caja         = $db->clearText($_POST['id_caja']);
            $tipo_documento  = $db->clearText($_POST['tipo_documento']);
            $id_sucursal     = $db->clearText($_POST['id_sucursal']);
            $ruc             = $db->clearText($_POST['ruc']);
            $timbrado        = $db->clearText($_POST['timbrado']);
            $establecimiento = $db->clearText($_POST['establecimiento']);
            $expedicion      = $db->clearText($_POST['expedicion']);
            $fecha_inicio    = $db->clearText($_POST['fecha_inicio']);
            $fecha_fin       = $db->clearText($_POST['fecha_fin']);
            $desde           = $db->clearText($_POST['desde']);
            $hasta           = $db->clearText($_POST['hasta']);
            $membrete        = $db->clearText($_POST['membrete']);


            $db->setQuery("SELECT * FROM timbrados WHERE id_timbrado=$id_timbrado");
            $rows = $db->loadObject();
            $estado = $rows->estado;

            if ($estado == "2") {
                echo json_encode(["status" => "error", "mensaje" => "No se puede modificar un timbrado caducado"]);
                exit;
            }
            

            $hoy = date("Y-m-d");

            if ($fecha_fin < $hoy) {
                $estado = '2';
            }else {
                $estado = '1';
            }
            
            if (empty($id_sucursal)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una sucursal"]);
                exit;
            }

            if (empty($ruc)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un R.U.C."]);
                exit;
            }

            if (empty($timbrado)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Timbrado"]);
                exit;
            }
            if (empty($establecimiento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Cod. Establecimiento"]);
                exit;
            }

            if (empty($expedicion)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Punto de expedicion"]);
                exit;
            }

            if (empty($fecha_inicio)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una Fecha de Vigencia"]);
                exit;
            }

            if (empty($fecha_fin)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una Fecha de Fin de Vigencia"]);
                exit;
            }
            
            if (empty($desde)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nro. Desde"]);
                exit;
            }
            
            if (empty($hasta)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un Nro. Hasta"]);
                exit;
            }

            if ($desde > $hasta) {
                echo json_encode(["status" => "error", "mensaje" => "Desde no puede ser mayor que el Hasta"]);
                exit;
            }

            $db->setQuery("SELECT * FROM sucursales WHERE id_sucursal = $id_sucursal");
            $deposito = $db->loadObject()->deposito;

            if ($tipo_documento == 0 && empty($id_caja) && $deposito == 0) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una Caja"]);
                exit;
            }

            if (empty($id_caja)) $id_caja = "NULL";

            $db->setQuery("UPDATE
                            timbrados
                        SET
                            ruc                 = '$ruc',
                            timbrado            = '$timbrado',
                            id_sucursal         = $id_sucursal,
                            id_caja             = $id_caja,
                            cod_establecimiento = '$establecimiento',
                            punto_de_expedicion = '$expedicion',
                            inicio_vigencia     = '$fecha_inicio',
                            fin_vigencia        = '$fecha_fin',
                            desde               = '$desde',
                            hasta               = '$hasta',
                            tipo                = '$tipo_documento',
                            estado              = '$estado',
                            membrete            = '$membrete'
                        WHERE id_timbrado       = $id_timbrado;
          ");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Timbrado editado correctamente"]);
            

        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_timbrado = $db->clearText($_POST['id_timbrado']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE timbrados SET estado=$estado WHERE id_timbrado=$id_timbrado");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;


        case 'verificar-timbrado':

            $db = DataBase::conectar();
            $db->setQuery("UPDATE timbrados SET estado = 2 WHERE fin_vigencia < NOW()");
            $db->alter();
            
        break;
	}

?>
