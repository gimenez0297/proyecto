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
            $having = "HAVING CONCAT_WS(' ', a.area, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            a.id_area,
                            a.area,
                            a.id_area_superior, 
                            ar.area AS area_superior,
                            a.estado,
                            CASE a.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            a.usuario,
                            DATE_FORMAT(a.fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                        FROM areas a
                        LEFT JOIN areas ar ON ar.id_area=a.id_area_superior 
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
        $area = mb_convert_case($db->clearText($_POST['area']), MB_CASE_UPPER, "UTF-8");
        $id_area_superior = $db->clearText($_POST['id_area_superior']);

        if (empty($area)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese un nombre para el Área"]);
            exit;
        }
        if (empty($id_area_superior) || !isset($id_area_superior) || intval($id_area_superior) == 0) {
            $id_area_superior = "NULL";
        }

        $db->setQuery("INSERT INTO areas (area, id_area_superior, estado, usuario, fecha)
                            VALUES ('$area',$id_area_superior,'1','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Área registrada correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_area = $db->clearText($_POST['id_area']);
        $area    = mb_convert_case($db->clearText($_POST['area']), MB_CASE_UPPER, "UTF-8");
        $id_area_superior = $db->clearText($_POST['id_area_superior']);

        if (empty($area)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese un nombre para el Área"]);
            exit;
        }
        if (empty($id_area_superior) || !isset($id_area_superior) || intval($id_area_superior) == 0) {
            $id_area_superior = "NULL";
        }

        $db->setQuery("UPDATE areas SET area='$area', id_area_superior=$id_area_superior WHERE id_area='$id_area'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Área '$area' modificada correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_area = $db->clearText($_POST['id_area']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE areas SET estado='$estado' WHERE id_area='$id_area'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $area = $db->clearText($_POST['area']);

        $db->setQuery("DELETE FROM areas WHERE id_area = '$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Área '$area' eliminada correctamente"]);
        break;

}
