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
            $having = "HAVING CONCAT_WS(' ', concepto, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_bonificacion,
                            concepto,
                            porcentaje,
                            observacion,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM bonificaciones
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
        $concepto = mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $porcentaje = $db->clearText($_POST['porcentaje']);
        $observacion = mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");

        if (empty($concepto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un concepto para la bonificación"]);
            exit;
        }

        $db->setQuery("INSERT INTO bonificaciones (concepto, porcentaje, observacion, estado, usuario, fecha)
                            VALUES ('$concepto','$porcentaje','$observacion','1','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Bonificación registrado correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_bonificacion = $db->clearText($_POST['id_bonificacion']);
        $concepto    = mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $porcentaje = $db->clearText($_POST['porcentaje']);
        $observacion = mb_convert_case($db->clearText($_POST['obs']), MB_CASE_UPPER, "UTF-8");

        if (empty($concepto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un concepto para la bonificación"]);
            exit;
        }

        $db->setQuery("UPDATE bonificaciones SET concepto='$concepto', porcentaje='$porcentaje', observacion='$observacion' WHERE id_bonificacion='$id_bonificacion'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Bonificación '$concepto' modificado correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_bonificacion = $db->clearText($_POST['id_bonificacion']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE bonificaciones SET estado='$estado' WHERE id_bonificacion='$id_bonificacion'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $concepto = $db->clearText($_POST['concepto']);

        $db->setQuery("DELETE FROM bonificaciones WHERE id_bonificacion= '$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Bonificación '$concepto' eliminado correctamente"]);
        break;

}
