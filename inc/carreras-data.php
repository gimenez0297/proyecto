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
            $having = "HAVING CONCAT_WS(' ', carrera, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_carrera,
                            carrera,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                            FROM carreras
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
        $carrera = mb_convert_case($db->clearText($_POST['carrera']), MB_CASE_UPPER, "UTF-8");

        if (empty($carrera)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese un nombre para la carrera"]);
            exit;
        }

        $db->setQuery("INSERT INTO carreras (carrera, estado, usuario, fecha)
                            VALUES ('$carrera','1','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Carrera registrado correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_carrera = $db->clearText($_POST['id_carrera']);
        $carrera    = mb_convert_case($db->clearText($_POST['carrera']), MB_CASE_UPPER, "UTF-8");

        if (empty($carrera)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese un nombre para la carrera"]);
            exit;
        }

        $db->setQuery("UPDATE carreras SET carrera='$carrera' WHERE id_carrera='$id_carrera'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Carrera '$carrera' modificado correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_carrera = $db->clearText($_POST['id_carrera']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE carreras SET estado='$estado' WHERE id_carrera='$id_carrera'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $carrera = $db->clearText($_POST['carrera']);

        $db->setQuery("DELETE FROM carreras WHERE id_carrera = '$id'");
        if (!$db->alter()) {
            if ($db->getErrorCode() == 1451) {
                echo json_encode(["status" => "error", "mensaje" => "Esta carrera está asociado a un Funcionario"]);
                exit;
            }
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Carrera '$carrera' eliminado correctamente"]);
        break;

}
