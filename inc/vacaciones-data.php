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
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $where = "HAVING funcionario LIKE '$search%' OR anho LIKE '$search%' OR ci LIKE '$search%' OR observacion LIKE '$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_vacacion,
                            funcionario,
                            id_funcionario,
                            ci,
                            antiguedad,
                            total_vacacion,
                            utilizado,
                            importe,
                            fecha_desde,
                            fecha_hasta,
                            observacion,
                            anho,
                            usuario,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' WHEN 2 THEN 'Procesado' WHEN 3 THEN 'Cerrado' END AS estado_str,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM vacaciones
                            WHERE 1=1 AND fecha BETWEEN '$desde' AND '$hasta' 
                            $where
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
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $anho = $db->clearText($_POST['anho']);
            $antiguedad = $db->clearText($_POST['antiguedad']);
            $total_vacacion = $db->clearText($_POST['corresponde']);
            $utilizar = $db->clearText($_POST['utilizar']);
            $importe = $db->clearText(quitaSeparadorMiles($_POST['importe']));
            $fecha_desde   = $db->clearText($_POST['fecha_desde']);
            $fecha_hasta   = $db->clearText($_POST['fecha_hasta']);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $pendiente = $db->clearText($_POST['pendiente']);


            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            $db->setQuery("SELECT * FROM vacaciones WHERE id_funcionario = '$id_funcionario' AND anho='$anho' AND estado = 1");
            $vac = $db->loadObject();
            if (!empty($vac)) {
                echo json_encode(["status" => "error", "mensaje" => "El funcionario ya cuenta con una vacación del año '$anho'."]);
                exit;
            }

            
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un funcionario para continuar."]);
                exit;
            }
            if($pendiente < $utilizar){
                echo json_encode(["status" => "error", "mensaje" => "La cantidad a utilizar no puede ser mayor al pendiente"]);
                exit;
            }
            if($utilizar==0){
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la cantidad a utilizar."]);
                exit;
            }
            if($fecha_desde==''){
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha Desde."]);
                exit;
            }

            $db->setQuery("INSERT INTO vacaciones (fecha, id_funcionario, ci, funcionario, antiguedad, total_vacacion, utilizado, importe, anho, fecha_desde, fecha_hasta, usuario, observacion)
                            VALUES (NOW(), '$id_funcionario','$row->ci','$row->funcionario','$antiguedad','$total_vacacion','$utilizar','$importe','$anho','$fecha_desde','$fecha_hasta','$usuario','$observacion')");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Vacación registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $id_vacacion = $db->clearText($_POST['id_vacacion']);
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $anho = $db->clearText($_POST['anho']);
            $antiguedad = $db->clearText($_POST['antiguedad']);
            $total_vacacion = $db->clearText($_POST['corresponde']);
            $utilizar = $db->clearText($_POST['utilizar']);
            $importe = $db->clearText(quitaSeparadorMiles($_POST['importe']));
            $fecha_desde   = $db->clearText($_POST['fecha_desde']);
            $fecha_hasta   = $db->clearText($_POST['fecha_hasta']);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $pendiente = $db->clearText($_POST['pendiente']);

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un funcionario para continuar."]);
                exit;
            }

            if($pendiente < $utilizar){
                echo json_encode(["status" => "error", "mensaje" => "La cantidad a utilizar no puede ser mayor al pendiente"]);
                exit;
            }

            if($utilizar==0){
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la cantidad a utilizar."]);
                exit;
            }

            $db->setQuery("UPDATE vacaciones SET id_funcionario='$id_funcionario', ci='$row->ci', funcionario='$row->funcionario', antiguedad='$antiguedad', total_vacacion='$total_vacacion',utilizado='$utilizar', importe='$importe', anho='$anho', fecha_desde='$fecha_desde', fecha_hasta='$fecha_hasta', observacion='$observacion' WHERE id_vacacion = '$id_vacacion'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Vacación modificado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id_vacacion']);
           // $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM vacaciones WHERE id_vacacion = '$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Vacación eliminado correctamente"]);
        break;

        case 'cambiar-estado':
        $db       = DataBase::conectar();
        $id_vacacion = $db->clearText($_POST['id_vacacion']);
        $estado   = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE vacaciones SET estado='$estado' WHERE id_vacacion='$id_vacacion'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;	

        case 'recuperar_antiguedad':
            $db       = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $estado   = $db->clearText($_POST['estado']);

            $db->setQuery("SELECT
                                f.funcionario,
                                TIMESTAMPDIFF(YEAR, fecha_alta, NOW()) AS antiguedad,
                                (COALESCE(vf.total_vacacion,0) - COALESCE(vf.utilizado,0)) AS saldo
                            FROM
                                funcionarios f
                            LEFT JOIN (SELECT * FROM vacaciones WHERE estado IN (1,2)) vf 
                            ON f.id_funcionario=vf.id_funcionario
                            WHERE f.id_funcionario='$id_funcionario'");

            echo json_encode($db->loadObject());
        break; 

        case 'verificar_vacacion':
            $db       = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $anho   = $db->clearText($_POST['anho']);
            if($id_funcionario == ''){
                echo json_encode(["status" => "error", "mensaje" => "Debe seleccionar el funcionario."]);
                exit;
            }else{
                $db->setQuery("SELECT
                                *
                            FROM
                                vacaciones
                            WHERE id_funcionario='$id_funcionario' and anho= '$anho' and estado IN (1,2)");
                if (!empty($db->loadObject())) {
                    echo json_encode(["status" => "error", "mensaje" => "El funcionario ya cuenta con una vacación del año '$anho'"]);
                } else{
                   echo json_encode(["status" => "ok"]);
                } 
            }

        break;  	

	}

?>
