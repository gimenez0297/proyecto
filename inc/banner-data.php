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
                $having = "HAVING estado_str LIKE '$search%' OR fecha LIKE '$search%'";
            }
			
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_banner,
                            foto,
                            estado,
                            link,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            DATE_FORMAT(creacion,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM banner
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

        case 'cargar_editar':
            $db = DataBase::conectar();
            $db->autocommit(false);

            $dropurl = $db->clearText($_POST['dropurl']);
            $files = $_FILES['file'];
            $files_name = $_FILES['file']['name'][0];
            $id_banner = $db->clearText($_POST['id_banner']);
            $link = $db->clearText($_POST['link']);

            if (empty($dropurl)) { echo json_encode(["status" => "error", "mensaje" => "El tipo de formulario esta vacio"]); exit; }

            if ($dropurl == "cargar") {
                $db->setQuery("INSERT INTO banner (link, estado, creacion) VALUES ('$link', 1, NOW())");
                if (!$db->alter()) {
                    echo "Error. " . $db->getError();
                    exit;
                }
                $ultimo_id = $db->getLastID();

                if ($files_name !== "blob") {
                    foreach ($files as $k => $l) {
                        foreach ($l as $i => $v) {
                            if (!array_key_exists($i, $files)) {
                                $files[$i] = array();
                            }
                            $files[$i][$k] = $v;
                        }
                    }
                    foreach ($files as $key => $file) {
                        $foo = new \Verot\Upload\Upload($file);
                        if ($foo->uploaded) {
                            $targetPath = "../../archivos/multimedia/banner/";
                            if (!is_dir($targetPath)) {
                                mkdir($targetPath, 0777, true);
                            }
    
                            $foo->file_new_name_body = md5($ultimo_id);
                            $foo->image_convert      = jpg;
                            $foo->image_resize       = true;
                            $foo->image_ratio        = true;
                            $foo->image_ratio_crop   = true;
                            $foo->image_y            = 740;
                            $foo->image_x            = 1200;
                            $foo->process($targetPath);
                            $foto = str_replace("../", "", $targetPath . $foo->file_dst_name);
                            if ($foo->processed) {
                                $db->setQuery("UPDATE banner SET foto='$foto' WHERE id_banner = '$ultimo_id'");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error al guardar la Imagen. " . $db->getError()]);
                                    $db->rollback();
                                    exit;
                                }
                                $foo->clean();
                            } else {
                                echo json_encode(["status" => "error", "mensaje" => "Error : " . $foo->error]);
                                $db->rollback();
                                exit;
                            }
                        }
                    }
                }
                
                $mensaje = "Banner registrado correctamente";
            }

            if ($dropurl == "editar") {
                $db->setQuery("UPDATE banner SET link='$link' WHERE id_banner = '$id_banner'");
                if (!$db->alter()) {
                    echo "Error. " . $db->getError();
                    exit;
                }

                if ($files_name !== "blob") {
                    foreach ($files as $k => $l) {
                        foreach ($l as $i => $v) {
                            if (!array_key_exists($i, $files)) {
                                $files[$i] = array();
                            }
                            $files[$i][$k] = $v;
                        }
                    }
                    foreach ($files as $key => $file) {
                        $foo = new \Verot\Upload\Upload($file);
                        if ($foo->uploaded) {
                            $targetPath = "../../archivos/multimedia/banner/";
                            if (!is_dir($targetPath)) {
                                mkdir($targetPath, 0777, true);
                            }
    
                            $foo->file_new_name_body = md5($id_banner);
                            $foo->image_convert      = jpg;
                            $foo->image_resize       = true;
                            $foo->image_ratio        = true;
                            $foo->image_ratio_crop   = true;
                            $foo->image_y            = 740;
                            $foo->image_x            = 1200;
                            $foo->process($targetPath);
                            $foto = str_replace("../", "", $targetPath . $foo->file_dst_name);
                            if ($foo->processed) {
                                $db->setQuery("UPDATE banner SET foto='$foto' WHERE id_banner = '$id_banner'");
                                if (!$db->alter()) {
                                    echo json_encode(["status" => "error", "mensaje" => "Error al guardar la Imagen. " . $db->getError()]);
                                    $db->rollback();
                                    exit;
                                }
                                $foo->clean();
                            } else {
                                echo json_encode(["status" => "error", "mensaje" => "Error : " . $foo->error]);
                                $db->rollback();
                                exit;
                            }
                        }
                    }
                }

                $mensaje = "Banner modificado correctamente";

            }

            $db->commit();
            echo json_encode(["status" => "ok", "mensaje" => $mensaje]);

        break;

        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id_banner = $db->clearText($_POST['id_banner']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE banner SET estado='$estado' WHERE id_banner='$id_banner'");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'eliminar':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);

            // Eliminar fotos
            $db->setQuery("SELECT foto FROM banner WHERE id_banner='$id'");
            $row = $db->loadObject();
            $foto = $row->foto;

            if ($foto) {
                if (!unlink("../../" . $foto)) {
                    echo json_encode(["status" => "error", "mensaje" => "Error al eliminar la foto '$foto'. "]);
                    exit;
                }
            }
            
            $db->setQuery("DELETE FROM banner WHERE id_banner = '$id'");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "Este Banner no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "'$nombre' eliminado correctamente"]);
        break;		

        case 'leer_fotos':
            $db = DataBase::conectar();
            $id_banner = $db->clearText($_POST['id_banner']);
            $db->setQuery("SELECT foto FROM banner WHERE id_banner='$id_banner'");
            $rows = $db->loadObjectList();
            if ($rows) {
                foreach ($rows as $r) {
                    $size       = filesize("../../" . $r->foto);
                    $nombre_tmp = explode("/", $r->foto);
                    $nombre     = end($nombre_tmp);
                    $path       = "../" .$r->foto;
                    $salida[]   = ['name' => $nombre, 'size' => $size, 'path' => $path];
                }
            }
            echo json_encode($salida);
        break;

        case 'borrar_fotos':
            $db = DataBase::conectar();
            $foto = $db->clearText($_POST['foto']);
            $id_tmp2 = explode("_", $foto);
            $id_tmp = explode(".", $id_tmp2[0]);
            $id_md5 = $id_tmp[0];

            $query = "SELECT id_banner, foto FROM banner WHERE MD5(id_banner)='$id_md5' AND foto LIKE '%$foto%'";
            $db->setQuery($query);
            $rows = $db->loadObject();
            $id_banner = $rows->id_banner;
            $foto = $rows->foto;

            if ($id_banner) {
                $db->setQuery("UPDATE banner SET foto=NULL WHERE id_banner = '$id_banner'");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error al eliminar la foto '$foto'. " . $db->getError()]);
                    exit;
                }
                if (!unlink("../../" . $foto)) {
                    echo json_encode(["status" => "error", "mensaje" => "Error al eliminar la foto '$foto'. "]);
                    exit;
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Foto eliminada correctamente"]);
        break;

	}

?>
