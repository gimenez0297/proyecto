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
                $where = "AND CONCAT_WS(' ', funcionario, ci, observacion) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_reposo,
                            funcionario,
                            id_funcionario,
                            ci,
                            fecha_desde,
                            fecha_hasta,
                            DATE_FORMAT(fecha_desde,'%d/%m/%Y') AS desde,
                            DATE_FORMAT(fecha_hasta,'%d/%m/%Y') As hasta,
                            observacion,
                            documento,
                            usuario,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM reposos
                            WHERE 1=1 AND fecha BETWEEN '$desde' AND '$hasta' $where
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
            $db->autocommit(false);

            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $fecha_desde    = $db->clearText($_POST['fecha_desde']);
            $fecha_hasta    = $db->clearText($_POST['fecha_hasta']);
            $observacion    =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $archivo        = $_FILES['archivo'];

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();
            
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un funcionario para continuar."]);
                exit;
            }
        
            if($fecha_desde==''){
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha Desde."]);
                exit;
            }

            if($fecha_hasta==''){
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha Hasta."]);
                exit;
            }

            $db->setQuery("INSERT INTO reposos (fecha, id_funcionario, ci, funcionario, fecha_desde, fecha_hasta, observacion, usuario)
                            VALUES (NOW(), '$id_funcionario','$row->ci','$row->funcionario','$fecha_desde','$fecha_hasta','$observacion','$usuario')");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();
                exit;
            }else{
                $ultimo_id = $db->getLastID();

                $targetPath = "../archivos/reposos/";
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }

                if (!empty($archivo['type']) && ($archivo['error'] == 0)) {
                    $original_file_temp = $archivo['tmp_name'];
                    $original_file_name = $archivo['name'];
                    $original_file_type = $archivo['type'];
                    $upload             = new \Verot\Upload\Upload($original_file_temp, 'es_ES');
                    $extension          = pathinfo($original_file_name, PATHINFO_EXTENSION);
                    $nombre_archivo = md5($ultimo_id);
                    
                    if($original_file_type === ALLOWED_PDF_MIME_TYPE) 
                    {
                        $target_file_path = uploadPDF($upload, $nombre_archivo, $targetPath);
                    } else {
                        $target_file_path = uploadImg($upload, $nombre_archivo, $targetPath);
                    }

                    if(!$target_file_path){
                        echo json_encode(["status" => "error", "mensaje" => "Error. Al guardar el archivo."]);
                        $db->rollback();
                        exit;
                    }

                    $ruta_almacenar = str_replace('../', '', $targetPath.$target_file_path);

                    $db->setQuery("UPDATE reposos SET documento='$ruta_almacenar' WHERE id_reposo=$ultimo_id");
                    if (!$db->alter()) {
                        echo "Error al guardar el documento. " . $db->getError();
                        $db->rollback(); //Revertimos los cambios
                        if (file_exists($targetPath . $target_file_path)) {
                            unlink($targetPath . $target_file_path);
                        }
                        exit;
                    }

                }
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Reposo registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $db->autocommit(false);

            $id_reposo = $db->clearText($_POST['id_reposo']);
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $fecha_desde   = $db->clearText($_POST['fecha_desde']);
            $fecha_hasta   = $db->clearText($_POST['fecha_hasta']);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
            $archivo       = $_FILES['archivo'];
            $change_archivo  = $db->clearText($_POST['change_archivo']);

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un funcionario para continuar."]);
                exit;
            }
            if($fecha_desde==''){
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha Desde."]);
                exit;
            }

            if($fecha_hasta==''){
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha Hasta."]);
                exit;
            }

            $db->setQuery("UPDATE reposos SET id_funcionario='$id_funcionario', ci='$row->ci', funcionario='$row->funcionario', fecha_desde='$fecha_desde', fecha_hasta='$fecha_hasta', observacion='$observacion' WHERE id_reposo = '$id_reposo'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();
                exit;
            }else{
                $targetPath = "../archivos/reposos/";
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }
                if ($change_archivo == "1") {
                    $db->setQuery("SELECT documento FROM reposos WHERE id_reposo = $id_reposo");
                    $row = $db->loadObject();

                    if ($row->documento) {
                        if (file_exists("../" . $row->documento)) {
                            if (!unlink("../" . $row->documento)) {
                                echo "Error al actualizar el documento. " . $db->getError();
                                $db->rollback();
                                exit;
                            }
                        }
                    }

                    $ruta_almacenar = '';
                    if (!empty($archivo['type']) && ($archivo['error'] == 0)) {
                        $original_file_temp = $archivo['tmp_name'];
                        $original_file_name = $archivo['name'];
                        $original_file_type = $archivo['type'];
                        $upload             = new \Verot\Upload\Upload($original_file_temp, 'es_ES');
                        $extension          = pathinfo($original_file_name, PATHINFO_EXTENSION);
                        $nombre_archivo = md5($id_reposo);
                        
                        if($original_file_type === ALLOWED_PDF_MIME_TYPE) 
                        {
                            $target_file_path = uploadPDF($upload, $nombre_archivo, $targetPath);
                        } else {
                            $target_file_path = uploadImg($upload, $nombre_archivo, $targetPath);
                        }
    
                        if(!$target_file_path){
                            echo json_encode(["status" => "error", "mensaje" => "Error. Al guardar el archivo."]);
                            $db->rollback();
                            exit;
                        }
    
                        $ruta_almacenar = str_replace('../', '', $targetPath.$target_file_path);
                    }

                    $db->setQuery("UPDATE reposos SET documento='$ruta_almacenar' WHERE id_reposo=$id_reposo");
                    if (!$db->alter()) {
                        echo "Error al guardar el documento. " . $db->getError();
                        $db->rollback(); //Revertimos los cambios
                        if (file_exists($targetPath . $target_file_path)) {
                            unlink($targetPath . $target_file_path);
                        }
                        exit;
                    }
                }
            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Reposo modificado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id_reposo']);
           // $nombre = $db->clearText($_POST['nombre']);

            $db->setQuery("SELECT documento FROM reposos WHERE id_reposo = $id");
            $row = $db->loadObject();
            if ($row->documento) {
                unlink("../" . $row->documento);
            }
            
            $db->setQuery("DELETE FROM reposos WHERE id_reposo = '$id'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Reposo eliminado correctamente"]);
        break;

        case 'cambiar-estado':
        $db       = DataBase::conectar();
        $id_reposo = $db->clearText($_POST['id_reposo']);
        $estado   = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE reposos SET estado='$estado' WHERE id_reposo='$id_reposo'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;		

	}

?>
