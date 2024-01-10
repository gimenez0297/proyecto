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
                $having = "HAVING CONCAT_WS(' ', rubro, estado_str) LIKE '%$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_rubro,
                            rubro,
                            icono,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            estado_web,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            foto AS logo,
                            orden
                            FROM rubros
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
		

        case 'cargar':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $rubro = mb_convert_case($db->clearText($_POST['rubro']), MB_CASE_UPPER, "UTF-8");
            $imagen = $_FILES['logo'];
            $icono = $db->clearText($_POST['icono']);
            $orden = $db->clearText($_POST['orden']);
            $web = ($db->clearText($_POST['web'])) ?: 2;

            if (empty($rubro)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para el rubro"]);
                exit;
            }
            
            if ($orden == 0 && $web == 1) { echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un orden mayor a 0."]);exit; }

            if ($orden == 0) { $orden ="NULL"; }

            $db->setQuery("SELECT * FROM rubros WHERE orden=$orden");
            $row = $db->loadObject();

            if(!empty($row)){
                echo json_encode(["status" => "error", "mensaje" => "El número de orden ya existe. Favor verifique."]);
                exit;
            }

            $db->setQuery("INSERT INTO rubros (rubro, orden, icono, estado, estado_web, usuario, fecha)
                            VALUES ('$rubro',$orden,'$icono','1','$web','$usuario',NOW())");
        
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();
                exit;
            } else {

                $ultimo_id = $db->getLastID();
                // Para el orden
                // $db->setQuery("UPDATE rubros SET orden=0 where orden=$orden");
                // if ($db->alter()) {
                //     $db->setQuery("UPDATE rubros SET orden='$orden' where id_rubro=$ultimo_id");
                //     if (!$db->alter()) {
                //         echo "Error. " . $db->getError();
                //         $db->rollback();
                //         exit;
                //     }
                // }
                // Para la imagen
                if ($imagen) {
                    $foo = new \Verot\Upload\Upload($imagen);
                    if ($foo->uploaded) {
                        $targetPath = "../../archivos/multimedia/rubros/";
                        if (!is_dir($targetPath)) {
                            mkdir($targetPath, 0777, true);
                        }
                        $foo->file_new_name_body = md5($ultimo_id);
                        $foo->image_convert      = jpg;
                        $foo->image_ratio_y      = true;
                        $foo->image_resize       = true;
                        $foo->image_x            = 240;
                        $foo->image_y            = 240;
                        $foo->process($targetPath);
                        $foto = str_replace("../", "", $targetPath . $foo->file_dst_name);
                        if ($foo->processed) {
                            $db->setQuery("UPDATE rubros SET foto='$foto' WHERE id_rubro=$ultimo_id");
                            if (!$db->alter()) {
                                echo "Error al guardar la foto. " . $db->getError();
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
            echo json_encode(["status" => "ok", "mensaje" => "Rubro registrado correctamente"]);
            
        break;

        case 'editar':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $id_rubro = $db->clearText($_POST['id_rubro']);
            $rubro = mb_convert_case($db->clearText($_POST['rubro']), MB_CASE_UPPER, "UTF-8");
            $change_logo = $db->clearText($_POST['change_logo']);
            $imagen      = $_FILES['logo'];
            $icono = $db->clearText($_POST['icono']);
            $orden = $db->clearText($_POST['orden']);
            $web = ($db->clearText($_POST['web'])) ?: 2;
            if (empty($rubro)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un nombre para el rubro"]);
                exit;
            }
           
            if ($orden == 0 && $web == 1) { echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un orden mayor a 0."]);exit; }

            if ($orden == 0) { $orden ="NULL"; }

            $db->setQuery("SELECT * FROM rubros WHERE orden=$orden AND id_rubro != $id_rubro");
            $row = $db->loadObject();

            if(!empty($row)){
                echo json_encode(["status" => "error", "mensaje" => "El número de orden ya existe. Favor verifique."]);
                exit;
            }

            $db->setQuery("UPDATE rubros SET rubro='$rubro', orden=$orden, icono='$icono', estado_web='$web'  WHERE id_rubro='$id_rubro'");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                $db->rollback();
                exit;
            } else {
                // Para el orden
                // $db->setQuery("UPDATE rubros SET orden=0 where orden=$orden");
                // if ($db->alter()) {
                //     $db->setQuery("UPDATE rubros SET orden='$orden' where id_rubro=$id_rubro");
                //     if (!$db->alter()) {
                //         echo "Error. " . $db->getError();
                //         $db->rollback();
                //         exit;
                //     }
                // }
                // Para al imagen
                if ($change_logo == '1') {
                    $db->setQuery("SELECT foto FROM rubros WHERE id_rubro = $id_rubro");
                    $row = $db->loadObject();
                    if ($row->foto) {
                        if (!unlink("../../" . $row->foto)) {
                            echo "Error al actualizar la foto. " . $db->getError();
                            $db->rollback();
                            exit;
                        }
                    }
                    $foo = new \Verot\Upload\Upload($imagen);

                    if ($foo->uploaded) {
                        $targetPath = "../../archivos/multimedia/rubros/";
                        if (!is_dir($targetPath)) {
                            mkdir($targetPath, 0777, true);
                        }
                        $foo->file_new_name_body = md5($id);
                        $foo->image_convert      = jpg;
                        $foo->image_ratio_y      = true;
                        $foo->image_resize       = true;
                        $foo->image_x            = 240;
                        $foo->image_y            = 240;
                        $foo->process($targetPath);
                        $foto = str_replace("../", "", $targetPath . $foo->file_dst_name);
                        if ($foo->processed) {

                            $db->setQuery("UPDATE rubros SET foto='$foto' WHERE id_rubro=$id_rubro");
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
                    } else {
                        $db->setQuery("UPDATE rubros SET foto=NULL WHERE id_rubro=$id_rubro");
                        if (!$db->alter()) {
                            echo "Error al guardar la foto. " . $db->getError();
                            $db->rollback(); //Revertimos los cambios
                            exit;
                        } else {
                            //echo "Foto cargada con éxito";
                        }
                    }
                }

            }
            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => "Rubro '$rubro' modificado correctamente"]);
        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_rubro = $db->clearText($_POST['id_rubro']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE rubros SET estado='$estado' WHERE id_rubro='$id_rubro'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $rubro = $db->clearText($_POST['rubro']);

            $db->setQuery("SELECT foto FROM rubros WHERE id_rubro = $id");
            $row = $db->loadObject();
            if ($row->foto) {
                unlink("../../" . $row->foto);
            }
            
            $db->setQuery("DELETE FROM rubros WHERE id_rubro = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este Rubro está asociado a un Producto"]);
                    exit;
                }
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Rubro '$rubro' eliminado correctamente"]);
        break;		

	}

?>
