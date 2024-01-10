<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$id_sucursal = datosUsuario($usuario)->id_sucursal;

switch ($q) {

    case 'ver':
        $db    = DataBase::conectar();
        $where = "";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $where = "HAVING f.funcionario LIKE '$search%' OR f.ci LIKE '$search%' OR f.direccion LIKE '$search%' OR p.puesto LIKE '$search%' OR f.telefono LIKE '$search%' OR f.celular LIKE '$search%' OR estado_str LIKE '$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_funcionario,
                            funcionario as razon_social,
                            f.ci,
                            f.direccion,
                            f.telefono,
                            f.celular,
                            f.usuario,
                            d.id_distrito,
                            d.nombre,
                            p.id_puesto,
                            p.puesto,
                            ec.id_estado,
                            ec.descripcion,
                            b.id_banco,
                            b.banco,
                            f.nro_cuenta,
                            curriculum,
                            antecedente,
                            salario_real,
                            f.cantidad_hijos,
                            p.comision,
                            f.comision as comision_func,
                            fecha_baja,
                            fecha_alta,
                            DATE_FORMAT(f.fecha_alta,'%d/%m/%Y') AS fecha_alta_format,
                            DATE_FORMAT(f.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            f.estado,
                            CASE f.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            f.id_sucursal,
                            s.sucursal,
                            f.referencia,
                            f.foto_perfil,
                            f.id_usuario,
                            f.aporte,
                            u.username
                            FROM funcionarios f
                            LEFT JOIN distritos d ON f.id_ciudad=d.id_distrito
                            LEFT JOIN puestos p ON f.id_puesto=p.id_puesto
                            LEFT JOIN estado_civil ec ON f.id_estado=ec.id_estado
                            LEFT JOIN bancos b ON f.id_banco=b.id_banco
                            LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                            LEFT JOIN users u ON f.id_usuario=u.id
                            WHERE 1=1 $where
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
        $rows = $db->loadObjectList();

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total     = $total_row->total;

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

        //$db           = DataBase::conectar();
        $ci           = $db->clearText($_POST['ci']);
        $razon_social = mb_convert_case($db->clearText($_POST['razon_social']), MB_CASE_UPPER, "UTF-8");
        $telefono     = $db->clearText($_POST['telefono']);
        $celular      = $db->clearText($_POST['celular']);
        $direccion    = mb_convert_case($db->clearText($_POST['direccion']), MB_CASE_UPPER, "UTF-8");
        $fecha_alta   = $db->clearText($_POST['fecha_alta']);
        $fecha_baja   = $db->clearText($_POST['fecha_baja']);
        $ciudad       = $db->clearText($_POST['id_ciudad']);
        $puesto       = $db->clearText($_POST['id_puesto']);
        $estado_civil = $db->clearText($_POST['id_estado']);
        $salario      = $db->clearText(quitaSeparadorMiles($_POST['salario']));
        $comision      = $db->clearText(quitaSeparadorMiles($_POST['comision']));
        $cv           = $_FILES['archivo_cv'];
        $antecedente  = $_FILES['logo_ant'];
        $imagen       = $_FILES['foto'];
        $change_logo  = $db->clearText($_POST['change_logo_ant']);
        $change_cv    = $db->clearText($_POST['change_cv']);
        $banco        = $db->clearText($_POST['id_banco']);
        $cuenta       = $db->clearText($_POST['nro_cuenta']);
        $hijos        = $db->clearText($_POST['hijos']);
        $sucursal     = $db->clearText($_POST['id_sucursal']);
        $referencia   = $db->clearText($_POST['referencia']);
        $id_usuario   = $db->clearText($_POST['id_usuario']);
        $aporte       = $db->clearText($_POST['aporte']);

        $documentos = json_decode($_POST['tabla_documento']);
        $count = 1;
        
        $alta = strtotime($fecha_alta);
        $baja = strtotime($fecha_baja);

        if (empty($ci)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el campo Nro. C.I."]);exit;}
        if (empty($razon_social)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese nombre y apellido del funcionario"]);exit;
        }
        if (empty($ciudad)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la ciudad"]);exit;}
        if (empty($puesto)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el puesto"]);exit;}
        if (empty($estado_civil)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un estado civil"]);exit;}
        if (empty($celular)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el número del celular"]);exit;}

        if ($alta > $baja && $baja != '') {
            echo json_encode(["status" => "error", "mensaje" => "La fecha de alta no puede ser mayor a la fecha de baja"]);exit;
        }

        if (empty($id_usuario)) {
            $id_usuario = "NULL";
        }

        if ($id_usuario == 1) {
            echo json_encode(["status" => "error", "mensaje" => "No se puede asociar a este usuario"]);
            exit;
        }

        $db->setQuery("INSERT INTO funcionarios (id_usuario,funcionario, ci, salario_real, comision, direccion, telefono, celular, id_ciudad, id_puesto, id_estado, id_banco, nro_cuenta, fecha_alta, fecha_baja, usuario, fecha, cantidad_hijos, id_sucursal, referencia, aporte)
                            VALUES ($id_usuario,'$razon_social',$ci,'$salario','$comision','$direccion','$telefono','$celular','$ciudad','$puesto','$estado_civil','$banco','$cuenta','$fecha_alta','$fecha_baja','$usuario',NOW(), $hijos, $sucursal,'$referencia','$aporte')");

        if (!$db->alter()) {
            if ($db->getErrorCode() == 1062) {
                echo json_encode(["status" => "error", "mensaje" => "El número de cédula ya existe"]);
                exit;
            }
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        } else {

            $ultimo_id = $db->getLastID();

            $targetPath = "../archivos/funcionarios/";
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }

            foreach ($documentos as $documentos) {
                if($documentos->estado == 1){ //es nuevo y se debe cargar
                    $base64_pdf = $documentos->file;
                    $nombre_doc = url_amigable($documentos->descripcion.'_'.$documentos->fecha).'.pdf';

                    $data = substr($base64_pdf, strpos($base64_pdf, ',') + 1);
                    // Se decodifica
                    $data = base64_decode($data);
                    if (!file_put_contents('../archivos/funcionarios/'.$nombre_doc, $data)) {
                        echo json_encode(["status" => "error", "mensaje" => "No se pudo guardar"]);
                        $db->rollback();
                        exit;
                    }
                    $db->setQuery("INSERT INTO documentos_funcionarios (descripcion, id_funcionario, fecha, documento) VALUES ('$documentos->descripcion','$ultimo_id', NOW(), '$nombre_doc')");

                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Debe ingresar un documento" . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }
            }

            if ($imagen) {
                $foo = new \Verot\Upload\Upload($imagen);
                if ($foo->uploaded) {
                    
                    $foo->file_new_name_body = md5($ultimo_id);
                    $foo->image_convert      = jpg;
                    $foo->image_ratio_y      = true;
                    $foo->image_resize       = true;
                    $foo->image_x            = 640;
                    $foo->image_y            = 640;
                    $foo->process($targetPath);
                    $foto = str_replace("../", "", $targetPath . $foo->file_dst_name);
                    if ($foo->processed) {
                        $db->setQuery("UPDATE funcionarios SET foto_perfil='$foto' WHERE id_funcionario=$ultimo_id");
                        if (!$db->alter()) {
                            echo "Error al guardar foto de perfil. " . $db->getError();
                            $db->rollback(); //Revertimos los cambios
                            exit;
                        } else {
                            //echo "Foto cargada con éxito";
                        }

                        $foo->clean();
                    } else {
                        echo 'Error. ' . $foo->error;
                    }
                }
            }
        }
        $db->commit();
        echo json_encode(["status" => "ok", "mensaje" => "Funcionario registrado correctamente"]);

        break;

    case 'editar':
        $db = DataBase::conectar();
        $db->autocommit(false);

        $hidden_id    = $db->clearText($_POST['hidden_id']);
        $ci           = $db->clearText($_POST['ci']);
        $razon_social = $db->clearText($_POST['razon_social']);
        $telefono     = $db->clearText($_POST['telefono']);
        $celular      = $db->clearText($_POST['celular']);
        $direccion    = $db->clearText($_POST['direccion']);
        $fecha_alta   = $db->clearText($_POST['fecha_alta']);
        $fecha_baja   = $db->clearText($_POST['fecha_baja']);
        $ciudad       = $db->clearText($_POST['id_ciudad']);
        $puesto       = $db->clearText($_POST['id_puesto']);
        $estado_civil = $db->clearText($_POST['id_estado']);
        $salario      = $db->clearText(quitaSeparadorMiles($_POST['salario']));
        $comision      = $db->clearText(quitaSeparadorMiles($_POST['comision']));
        $cv           = $_FILES['archivo_cv'];
        $antecedente  = $_FILES['logo_ant'];
        $imagen       = $_FILES['foto'];
        $change_logo  = $db->clearText($_POST['change_foto']);
        $change_cv    = $db->clearText($_POST['change_cv']);
        $banco       = $db->clearText($_POST['id_banco']);
        $cuenta       = $db->clearText($_POST['nro_cuenta']);
        $hijos       = $db->clearText($_POST['hijos']);
        $sucursal     = $db->clearText($_POST['id_sucursal']);
        $referencia   = $db->clearText($_POST['referencia']);
        $id_usuario   = $db->clearText($_POST['id_usuario']);
        $aporte   = $db->clearText($_POST['aporte']);

        $documentos = json_decode($_POST['tabla_documento']);
        $count = 1;

        $alta = strtotime($fecha_alta);
        $baja = strtotime($fecha_baja);

        if (empty($razon_social)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese nombre y apellido del funcionario"]);exit;
        }
        if (empty($ciudad)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la ciudad"]);exit;}
        if (empty($puesto)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el puesto"]);exit;}
        if (empty($estado_civil)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un estado civil"]);exit;}
        if (empty($celular)) {echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el número de celular"]);exit;}
        if ($alta > $baja && $baja != '') {
            echo json_encode(["status" => "error", "mensaje" => "La fecha de alta no puede ser mayor a la fecha de baja"]);exit;
        }

        $new_id_usuario = $id_usuario;

        if ($new_id_usuario == 1) {
            echo json_encode(["status" => "error", "mensaje" => "No se puede asociar a este usuario"]);
            exit;
        }

        $db->setQuery("SELECT id_usuario FROM funcionarios WHERE id_funcionario = $hidden_id");
        $fila = $db->loadObject();
        if(!empty($fila->id_usuario)){
            $new_id_usuario = $fila->id_usuario;
        }

        if (empty($fila->id_usuario) && empty($id_usuario)) {
            $new_id_usuario = "NULL";
        }

        $db->setQuery("UPDATE funcionarios SET id_usuario=$new_id_usuario, ci='$ci', salario_real='$salario', comision='$comision', funcionario='$razon_social', telefono='$telefono', celular='$celular', direccion='$direccion',id_ciudad='$ciudad',id_puesto='$puesto',id_estado='$estado_civil',id_banco='$banco',nro_cuenta='$cuenta', fecha_baja='$fecha_baja', fecha_alta='$fecha_alta', cantidad_hijos='$hijos', id_sucursal='$sucursal', referencia='$referencia', aporte='$aporte' WHERE id_funcionario = '$hidden_id'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        } else {

            $targetPath = "../archivos/funcionarios/";
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }

            foreach ($documentos as $documentos) {
                if($documentos->estado == 2){ //se elimina de la base de datos por que cuenta con un estado
                    $db->setQuery("SELECT CONCAT('archivos/funcionarios/',documento) as archivo FROM documentos_funcionarios WHERE descripcion='$documentos->descripcion'");
                    $row = $db->loadObject();
                    if (!empty($row)) {
                        $db->setQuery("DELETE FROM documentos_funcionarios WHERE descripcion='$documentos->descripcion'");

                        if (!unlink("../" .$row->archivo)) {
                            echo json_encode(["status" => "error", "mensaje" => "Error al actualizar el documento. " . $db->getError()]);
                            $db->rollback();
                            exit;
                        }

                        if (!$db->alter()) {
                            echo json_encode(["status" => "error", "mensaje" => "Eliminar el documento" . $db->getError()]);
                            $db->rollback();
                            exit;
                        }
                    }
                }
                if($documentos->estado == 1){ //es nuevo y se debe cargar
                    $base64_pdf = $documentos->file;
                    $nombre_doc = url_amigable($documentos->descripcion.'_'.$documentos->fecha).'.pdf';

                    $data = substr($base64_pdf, strpos($base64_pdf, ',') + 1);
                    // Se decodifica
                    $data = base64_decode($data);
                    file_put_contents('../archivos/funcionarios/'.$nombre_doc, $data);                    
                    $db->setQuery("INSERT INTO documentos_funcionarios (descripcion, id_funcionario, fecha, documento) VALUES ('$documentos->descripcion','$hidden_id', NOW(), '$nombre_doc')");

                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Debe ingresar un documento" . $db->getError()]);
                        $db->rollback();
                        exit;
                    }
                }
            }

            if ($change_logo) {
                $db->setQuery("SELECT foto_perfil FROM funcionarios WHERE id_funcionario = $hidden_id");
                $row = $db->loadObject();
                if ($row->foto_perfil) {
                    if (!unlink("../" . $row->foto_perfil)) {
                        echo "Error al actualizar la foto de perfil. " . $db->getError();
                        $db->rollback();
                    }
                }
                $foo = new \Verot\Upload\Upload($imagen);

                if ($foo->uploaded) {
                    
                    $foo->file_new_name_body = md5($id);
                    $foo->image_convert      = jpg;
                    $foo->image_ratio_y      = true;
                    $foo->image_resize       = true;
                    $foo->image_x            = 640;
                    $foo->image_y            = 640;
                    $foo->process($targetPath);
                    $foto = str_replace("../", "", $targetPath . $foo->file_dst_name);
                    if ($foo->processed) {

                        $db->setQuery("UPDATE funcionarios SET foto_perfil='$foto' WHERE id_funcionario=$hidden_id");
                        if (!$db->alter()) {
                            echo "Error al guardar la foto de perfil. " . $db->getError();
                            $db->rollback(); //Revertimos los cambios
                            exit;
                        }

                        $foo->clean();
                    } else {
                        echo 'Error. ' . $foo->error;
                    }
                } else {
                    $db->setQuery("UPDATE funcionarios SET foto_perfil=NULL WHERE id_funcionario=$hidden_id");
                    if (!$db->alter()) {
                        echo "Error al guardar foto de perfil. " . $db->getError();
                        $db->rollback(); //Revertimos los cambios
                        exit;
                    }
                }
            }

        }
        $db->commit();
        echo json_encode(["status" => "ok", "mensaje" => "Funcionario '$razon_social' modificado correctamente"]);
        break;

    case 'eliminar':
        $db = DataBase::conectar();
        $db->autocommit(false);

        $id     = $db->clearText($_POST['id']);
        $nombre = $db->clearText($_POST['razon_social']);

        $db->setQuery("SELECT id_documento, CONCAT('archivos/funcionarios/',documento) as archivo FROM documentos_funcionarios WHERE id_funcionario='$id'");
        $rows = $db->loadObjectList();

        foreach ($rows as $row) {
            $id_documento = $row->id_documento;

            if ($id_documento) {
                $db->setQuery("DELETE FROM documentos_funcionarios WHERE id_documento='$id_documento'");

                if (!unlink("../" .$row->archivo)) {
                    echo json_encode(["status" => "error", "mensaje" => "Error al actualizar el documento. " . $db->getError()]);
                    $db->rollback();
                    exit;
                }

                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error al eliminar el documento " . $db->getError()]);
                    $db->rollback();
                        exit;
                }
            }
        }

        $db->setQuery("SELECT foto_perfil FROM funcionarios WHERE id_funcionario = $id");
        $row = $db->loadObject();
        if ($row->foto_perfil) {
            unlink("../" . $row->foto_perfil);
        }

        $db->setQuery("DELETE FROM funcionarios WHERE id_funcionario = '$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        $db->commit();
        echo json_encode(["status" => "ok", "mensaje" => "Funcionario eliminado correctamente"]);
        break;

    case 'cambiar-estado':
        $db       = DataBase::conectar();
        $id_funcionario = $db->clearText($_POST['id_funcionario']);
        $estado   = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE funcionarios SET estado='$estado' WHERE id_funcionario='$id_funcionario'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
    break;

    case 'verificar_comision':
        $db       = DataBase::conectar();
        $id_puesto = $db->clearText($_POST['id_puesto']);

        $db->setQuery("SELECT * FROM puestos WHERE id_puesto='$id_puesto' AND comision = 1");
        if (!empty($db->loadObject())) {
            echo json_encode(["status" => "ok"]);
        } else{
           echo json_encode(["status" => "error"]);
        } 
    break;

    case 'ver_documentos':
            $db = DataBase::conectar();
            $id_funcionario = $db->clearText($_REQUEST['id_funcionario']);

            $db->setQuery("SELECT 
                            descripcion,
                            date(fecha) as fecha,
                            documento,
                            0 as estado,
                            CONCAT('archivos/funcionarios/',documento) as ver
                            FROM documentos_funcionarios p
                            WHERE id_funcionario='$id_funcionario'
                            ORDER BY descripcion");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;

}
