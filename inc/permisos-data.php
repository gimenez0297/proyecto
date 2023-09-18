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
            $having = "HAVING CONCAT_WS(' ', concepto, relcionado_a_str, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            p.id_permiso,
                            concepto,
                            cantidad,
                            unidad,
                            IF(unidad = 0,'DIAS','HORAS') AS unidad_str,
                            periodo,
                            relacionado_a,
                            CASE relacionado_a WHEN 1 THEN 'Entrada' WHEN 2 THEN 'Salida' WHEN 3 THEN 'Intermedia' WHEN 4 THEN 'Sin Marcación' WHEN 5 THEN 'Vacaciones' END AS relcionado_a_str,
                            goce_sueldo,
                            IF(goce_sueldo = 0,'SI','NO') AS goce_sueldo_str,
                            autenticada,
                            IF(autenticada = 0,'SI','NO') AS autenticada_str,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            GROUP_CONCAT(CASE pd.id_documento WHEN 1 THEN 'Certificado Médico' WHEN 2 THEN 'Constancia de Estudio' WHEN 3 THEN 'Constancia Médica' END separator ', ') AS documentos,
                            validez,
                            observacion,
                            usuario,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                        FROM permisos p
                        LEFT JOIN  permisos_documentos pd ON p.id_permiso = pd.id_permiso
                        GROUP BY p.id_permiso
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
        $cantidad = $db->clearText($_POST['cantidad']);
        $unidad = $db->clearText($_POST['unidad']);
        $periodo = $db->clearText($_POST['periodo']);
        $relacionado_a = $db->clearText($_POST['relacionado_a']);
        $goce_sueldo = $db->clearText($_POST['goce_sueldo']);
        $autenticada = $db->clearText($_POST['autenticada']);
        $validez = $db->clearText($_POST['validez']);
        $obs = $db->clearText($_POST['obs']);
        $documentos = $_POST['documentos'];

        if (empty($concepto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un concepto para el permiso"]);
            exit;
        }

        $db->setQuery("INSERT INTO `permisos` (
                                          `concepto`,
                                          `cantidad`,
                                          `unidad`,
                                          `periodo`,
                                          `relacionado_a`,
                                          `goce_sueldo`,
                                          `autenticada`,
                                          `validez`,
                                          `observacion`,
                                          `estado`,
                                          `usuario`,
                                          `fecha`
                                        )
                                        VALUES
                                          (
                                            '$concepto',
                                            '$cantidad',
                                            '$unidad',
                                            '$periodo',
                                            '$relacionado_a',
                                            '$goce_sueldo',
                                            '$autenticada',
                                            '$validez',
                                            '$obs',
                                            1,
                                            '$usuario',
                                            NOW()
                                          );");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        $ultimo_id = $db->getLastID();

        foreach ($documentos as $doc) {
            $db->setQuery("INSERT INTO permisos_documentos (id_permiso, id_documento) VALUES ($ultimo_id , $doc)");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }
        }

        echo json_encode(["status" => "ok", "mensaje" => "Permiso registrado correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_permiso = $db->clearText($_POST['id_permiso']);
        $concepto = mb_convert_case($db->clearText($_POST['concepto']), MB_CASE_UPPER, "UTF-8");
        $cantidad = $db->clearText($_POST['cantidad']);
        $unidad = $db->clearText($_POST['unidad']);
        $periodo = $db->clearText($_POST['periodo']);
        $relacionado_a = $db->clearText($_POST['relacionado_a']);
        $goce_sueldo = $db->clearText($_POST['goce_sueldo']);
        $autenticada = $db->clearText($_POST['autenticada']);
        $validez = $db->clearText($_POST['validez']);
        $obs = $db->clearText($_POST['obs']);
        $documentos = $_POST['documentos'];

        if (empty($concepto)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un concepto para el permiso"]);
            exit;
        }

        $db->setQuery("UPDATE
                          `permisos`
                        SET
                          `concepto` = '$concepto',
                          `cantidad` = '$cantidad',
                          `unidad` = '$unidad',
                          `periodo` = '$periodo',
                          `relacionado_a` = '$relacionado_a',
                          `goce_sueldo` = '$goce_sueldo',
                          `autenticada` = '$autenticada',
                          `validez` = '$validez',
                          `observacion` = '$obs'
                        WHERE `id_permiso` = $id_permiso;");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        $db->setQuery("DELETE FROM permisos_documentos WHERE id_permiso='$id_permiso'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
            $db->rollback();
            exit;
        }

        foreach ($documentos as $doc) {
            $db->setQuery("INSERT INTO permisos_documentos (id_permiso, id_documento) VALUES ($id_permiso , $doc)");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }
        }

        echo json_encode(["status" => "ok", "mensaje" => "Permiso '$concepto' modificado correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_permiso = $db->clearText($_POST['id_permiso']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE `permisos` SET `estado` = '$estado' WHERE `id_permiso` = $id_permiso;");
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

        $db->setQuery("DELETE FROM permisos WHERE id_permiso = '$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Permiso '$concepto' eliminado correctamente"]);
        break;

    case 'ver_permisos_documentos':
            $db = DataBase::conectar();
            $id_permiso= $db->clearText($_POST['id_permiso']);

            $db->setQuery("SELECT id_documento FROM permisos_documentos WHERE id_permiso='$id_permiso'");
            $rows = ($db->loadObjectList()) ?: [];

            echo json_encode($rows);
        break;  

}
