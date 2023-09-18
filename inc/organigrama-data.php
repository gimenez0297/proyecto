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
        $id_area = $db->clearText($_REQUEST['id_area']);
        $where_area ="";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', o.cargo, jefe, area, estado_str) LIKE '%$search%'";
        }
        if(!empty($id_area) && intVal($id_area) != 0) {
            $where_area = "  AND o.id_area=$id_area";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            o.id_cargo,
                            o.cargo ,
                            o.id_jefe, 
                            og.cargo AS jefe,
                            a.id_area,
                            a.area,
                            o.estado,
                            CASE o.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            o.usuario,
                            DATE_FORMAT(o.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                            o.confianza,
                            o.multiple_personas
                        FROM organigrama o
                        LEFT JOIN `organigrama` og ON og.id_cargo=o.id_jefe 
                        LEFT JOIN areas a ON o.id_area = a.id_area
                        WHERE 1=1 $where_area
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
        $cargo = mb_convert_case($db->clearText($_POST['cargo']), MB_CASE_UPPER, "UTF-8");
        $id_area = $db->clearText($_POST['id_area']);
        $id_jefe = $db->clearText($_POST['id_jefe']);
        $confianza = ($db->clearText($_POST['confianza'])) ?: 0;
        $multiple = ($db->clearText($_POST['multiple'])) ?: 0;

        if (empty($cargo)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el cargo"]);
            exit;
        }
        if (empty($id_area) || !isset($id_area) || intval($id_area) == 0) {
            $id_area = "NULL";
        }
        if (empty($id_jefe) || !isset($id_jefe) || intval($id_jefe) == 0) {
            $id_jefe = "NULL";
        }

        $db->setQuery("INSERT INTO organigrama (cargo, id_area, id_jefe, confianza, multiple_personas, estado, usuario, fecha)
                            VALUES ('$cargo',$id_area,$id_jefe,$confianza,$multiple,'1','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Organigrama registrada correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_cargo = $db->clearText($_POST['id_cargo']);
        $cargo = mb_convert_case($db->clearText($_POST['cargo']), MB_CASE_UPPER, "UTF-8");
        $id_area = $db->clearText($_POST['id_area']);
        $id_jefe = $db->clearText($_POST['id_jefe']);
        $confianza = ($db->clearText($_POST['confianza'])) ?: 0;
        $multiple = ($db->clearText($_POST['multiple'])) ?: 0;

        if (empty($cargo)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el cargo"]);
            exit;
        }
        if (empty($id_area) || !isset($id_area) || intval($id_area) == 0) {
            $id_area = "NULL";
        }
        if (empty($id_jefe) || !isset($id_jefe) || intval($id_jefe) == 0) {
            $id_jefe = "NULL";
        }

        $db->setQuery("UPDATE organigrama SET cargo='$cargo', id_area=$id_area, id_jefe=$id_jefe, confianza=$confianza, multiple_personas=$multiple WHERE id_cargo='$id_cargo'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Organigrama '$cargo' modificada correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_cargo = $db->clearText($_POST['id_cargo']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE organigrama SET estado='$estado' WHERE id_cargo='$id_cargo'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $cargo = $db->clearText($_POST['cargo']);

        $db->setQuery("DELETE FROM organigrama WHERE id_cargo = '$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Área '$cargo' eliminada correctamente"]);
        break;

}
