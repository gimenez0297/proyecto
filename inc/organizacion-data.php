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
            $having = "HAVING CONCAT_WS(' ', organizacion, tipo, sector, pais, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            o.`id_organizacion`,
                            o.`organizacion`,
                            o.estado,
                            CASE o.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            o.usuario,
                            DATE_FORMAT(o.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            t.id_tipo,
                            t.tipo,
                            s.id_sector,
                            s.sector,
                            p.id_pais,
                            p.nombre_es AS pais
                        FROM organizacion o
                        LEFT JOIN `tipo_organizacion` t ON o.id_tipo = t.id_tipo
                        LEFT JOIN sector s ON o.id_sector = s.id_sector
                        LEFT JOIN paises p ON o.id_pais = p.id_pais
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
        $organizacion = mb_convert_case($db->clearText($_POST['organizacion']), MB_CASE_UPPER, "UTF-8");
        $id_tipo = $db->clearText($_POST['id_tipo']);
        $id_sector = $db->clearText($_POST['id_sector']);
        $id_pais = $db->clearText($_POST['id_pais']);

        if (empty($organizacion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor escriba la organización"]);
            exit;
        }
        if (empty($id_tipo) || !isset($id_tipo) || intval($id_tipo) == 0) {
            $id_tipo = "NULL";
        }
        if (empty($id_pais) || !isset($id_pais) || intval($id_pais) == 0) {
            $id_pais = "NULL";
        }
        if (empty($id_sector) || !isset($id_sector) || intval($id_sector) == 0) {
            $id_sector = "NULL";
        }

        $db->setQuery("INSERT INTO organizacion (organizacion, id_tipo, id_sector, id_pais, estado, usuario, fecha)
                            VALUES ('$organizacion',$id_tipo,$id_sector,$id_pais,'1','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Organización registrada correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_organizacion = $db->clearText($_POST['id_organizacion']);
        $organizacion = mb_convert_case($db->clearText($_POST['organizacion']), MB_CASE_UPPER, "UTF-8");
        $id_tipo = $db->clearText($_POST['id_tipo']);
        $id_sector = $db->clearText($_POST['id_sector']);
        $id_pais = $db->clearText($_POST['id_pais']);

        if (empty($organizacion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor escriba la organización"]);
            exit;
        }
        if (empty($id_tipo) || !isset($id_tipo) || intval($id_tipo) == 0) {
            $id_tipo = "NULL";
        }
        if (empty($id_pais) || !isset($id_pais) || intval($id_pais) == 0) {
            $id_pais = "NULL";
        }
        if (empty($id_sector) || !isset($id_sector) || intval($id_sector) == 0) {
            $id_sector = "NULL";
        }

        $db->setQuery("UPDATE organizacion SET organizacion='$organizacion', id_tipo=$id_tipo, id_sector=$id_sector, id_pais=$id_pais WHERE id_organizacion='$id_organizacion'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Organización '$organizacion' modificada correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_organizacion = $db->clearText($_POST['id_organizacion']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE organizacion SET estado='$estado' WHERE id_organizacion='$id_organizacion'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $organizacion = $db->clearText($_POST['organizacion']);

        $db->setQuery("DELETE FROM organizacion WHERE id_organizacion = '$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Organización '$organizacion' eliminado correctamente"]);
        break;

}
