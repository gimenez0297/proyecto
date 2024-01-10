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
            $having = "HAVING CONCAT_WS(' ', p.puesto, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            p.id_puesto,
                            p.puesto,
                            p.estado,
                            p.comision,
                            CASE p.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            p.usuario,
                            DATE_FORMAT(p.fecha,'%d/%m/%Y %H:%i:%s') AS fecha, 
                            tp.id_tipo_puesto, 
                            tp.tipo_puesto 
                            FROM puestos p 
                            LEFT JOIN tipos_puestos tp ON p.id_tipo_puesto=tp.id_tipo_puesto 
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
        $puesto = mb_convert_case($db->clearText($_POST['puesto']), MB_CASE_UPPER, "UTF-8");
        $id_tipo_puesto = $db->clearText($_POST['tipo_puesto']);
        $comision = ($db->clearText($_POST['comision'])) ?: 0;

        if (empty($puesto)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese un nombre para el Puesto"]);
            exit;
        }
        if (empty($id_tipo_puesto) || !isset($id_tipo_puesto) || intval($id_tipo_puesto) == 0) {
            $id_tipo_puesto = "NULL";
        }

        $db->setQuery("INSERT INTO puestos (puesto, id_tipo_puesto, estado, comision, usuario, fecha)
                            VALUES ('$puesto',$id_tipo_puesto,'1','$comision','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Puesto registrado correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_puesto = $db->clearText($_POST['id_puesto']);
        $puesto    = mb_convert_case($db->clearText($_POST['puesto']), MB_CASE_UPPER, "UTF-8");
        $id_tipo_puesto = $db->clearText($_POST['tipo_puesto']);
        $comision = ($db->clearText($_POST['comision'])) ?: 0;

        if (empty($puesto)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese un nombre para el Puesto"]);
            exit;
        }
        if (empty($id_tipo_puesto) || !isset($id_tipo_puesto) || intval($id_tipo_puesto) == 0) {
            $id_tipo_puesto = "NULL";
        }

        $db->setQuery("UPDATE puestos SET puesto='$puesto', id_tipo_puesto=$id_tipo_puesto, comision='$comision' WHERE id_puesto='$id_puesto'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Puesto '$puesto' modificado correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_puesto = $db->clearText($_POST['id_puesto']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE puestos SET estado='$estado' WHERE id_puesto='$id_puesto'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $puesto = $db->clearText($_POST['puesto']);

        $db->setQuery("DELETE FROM puestos WHERE id_puesto = '$id'");
        if (!$db->alter()) {
            if ($db->getErrorCode() == 1451) {
                echo json_encode(["status" => "error", "mensaje" => "Esta puesto está asociado a un Funcionario"]);
                exit;
            }
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Puesto '$puesto' eliminado correctamente"]);
        break;

}
