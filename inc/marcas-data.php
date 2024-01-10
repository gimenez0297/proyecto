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
            $having = "HAVING CONCAT_WS(' ', marca, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_marca,
                            marca,
                            estado,
                            logo,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM marcas
                            $having
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
        $db     = DataBase::conectar();
        $marca  = mb_convert_case($db->clearText($_POST['marca']), MB_CASE_UPPER, "UTF-8");
        $imagen = $_FILES['logo'];

        if (empty($marca)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para la marca"]);
            exit;
        }

        $db->setQuery("INSERT INTO marcas (marca, estado, usuario, fecha)
                            VALUES ('$marca','1','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        } else {

            $ultimo_id = $db->getLastID();
            if ($imagen) {
                $foo = new \Verot\Upload\Upload($imagen);
                if ($foo->uploaded) {
                    $targetPath = "../../archivos/multimedia/marcas/";
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
                        $db->setQuery("UPDATE marcas SET logo='$foto' WHERE id_marca=$ultimo_id");
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

        echo json_encode(["status" => "ok", "mensaje" => "Marca registrada correctamente"]);

        break;

    case 'editar':
        $db          = DataBase::conectar();
        $id_marca    = $db->clearText($_POST['id_marca']);
        $marca       = mb_convert_case($db->clearText($_POST['marca']), MB_CASE_UPPER, "UTF-8");
        $change_logo = $db->clearText($_POST['change_logo']);
        $imagen      = $_FILES['logo'];

        if (empty($marca)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para la marca"]);
            exit;
        }

        $db->setQuery("UPDATE marcas SET marca='$marca' WHERE id_marca='$id_marca'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        } else {

            if ($change_logo) {
                $db->setQuery("SELECT logo FROM marcas WHERE id_marca = $id_marca");
                $row = $db->loadObject();
                if ($row->logo) {
                    if (!unlink("../../" . $row->logo)) {
                        echo "Error al actualizar el logo. " . $db->getError();
                        $db->rollback();
                    }
                }
                $foo = new \Verot\Upload\Upload($imagen);

                if ($foo->uploaded) {
                    $targetPath = "../../archivos/multimedia/marcas/";
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

                        $db->setQuery("UPDATE marcas SET logo='$foto' WHERE id_marca=$id_marca");
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
                    $db->setQuery("UPDATE marcas SET logo=NULL WHERE id_marca=$id_marca");
                    if (!$db->alter()) {
                        echo "Error al guardar foto de perfil. " . $db->getError();
                        $db->rollback(); //Revertimos los cambios
                        exit;
                    } else {
                        //echo "Foto cargada con éxito";
                    }
                }
            }

        }

        echo json_encode(["status" => "ok", "mensaje" => "Marca '$marca' modificada correctamente"]);
        break;

    case 'cambiar-estado':
        $db       = DataBase::conectar();
        $id_marca = $db->clearText($_POST['id_marca']);
        $estado   = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE marcas SET estado='$estado' WHERE id_marca='$id_marca'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $nombre = $db->clearText($_POST['nombre']);

        $db->setQuery("SELECT logo FROM marcas WHERE id_marca = $id");
        $row = $db->loadObject();
        if ($row->logo) {
            unlink("../../" . $row->logo);
        }

        $db->setQuery("DELETE FROM marcas WHERE id_marca = '$id'");
        if (!$db->alter()) {
            if ($db->getErrorCode() == 1451) {
                echo json_encode(["status" => "error", "mensaje" => "Esta Marca está asociada a un Producto"]);
                exit;
            }
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Marca '$nombre' eliminada correctamente"]);
        break;

}
