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
            $db    = DataBase::conectar();
            $where = "";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit  = $db->clearText($_REQUEST['limit']);
            $offset	= $db->clearText($_REQUEST['offset']);
            $order  = $db->clearText($_REQUEST['order']);
            $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', nombre_apellido, registro_nro, estado_str,especialidad) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    d.id_doctor, 
                                    d.nombre_apellido, 
                                    d.registro_nro, 
                                    d.estado,
                                    d.ruc,
                                    e.id_especialidad,
                                    CASE d.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                                    e.nombre AS especialidad
                                    FROM doctores d
                                    LEFT JOIN `especialidades_doctores` e ON e.id_especialidad = d.id_especialidad
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
            $db              = DataBase::conectar();
            $ruc = $db->clearText($_POST['ruc']);
            $registro        = mb_convert_case($db->clearText($_POST['registro']), MB_CASE_UPPER, "UTF-8");
            $nombre_apellido = mb_convert_case($db->clearText($_POST['nombre_apellido']), MB_CASE_UPPER, "UTF-8");
            $id_especialidad = $db->clearText($_POST['id_especialidad']);

            if (empty($ruc)) {
                $ruc = 'SIN RUC';
            }
            if (empty($registro)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nro. Registro"]);
                exit;
            }
            if (empty($nombre_apellido)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre y Apellido"]);
                exit;
            }
            if (empty($id_especialidad) || !isset($id_especialidad) || intval($id_especialidad) == 0) {
                $id_especialidad = "NULL";
            }

            $db->setQuery("INSERT INTO doctores (id_especialidad,nombre_apellido,registro_nro, ruc)
                           VALUES($id_especialidad,'$nombre_apellido','$registro', '$ruc');");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            $id_doctor = $db->getLastID();

            echo json_encode(["status" => "ok", "mensaje" => "Doctor registrado correctamente", "id_doctor" => $id_doctor]);

        break;


        case 'editar':
            $db              = DataBase::conectar();
            $id               = $db->clearText($_POST['id']);
            $ruc             = $db->clearText($_POST['ruc']);
            $registro        = mb_convert_case($db->clearText($_POST['registro']), MB_CASE_UPPER, "UTF-8");
            $nombre_apellido = mb_convert_case($db->clearText($_POST['nombre_apellido']), MB_CASE_UPPER, "UTF-8");
            $id_especialidad = $db->clearText($_POST['id_especialidad']);

            if (empty($ruc)) {
                $ruc = 'SIN RUC';
            }
            if (empty($registro)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nro. Registro"]);
                exit;
            }
            if (empty($nombre_apellido)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Nombre y Apellido"]);
                exit;
            }
            if (empty($id_especialidad) || !isset($id_especialidad) || intval($id_especialidad) == 0) {
                $id_especialidad = "NULL";
            }

            $db->setQuery(" UPDATE doctores
                            SET
                                id_especialidad = $id_especialidad,
                                nombre_apellido = '$nombre_apellido',
                                registro_nro = '$registro',
                                ruc = '$ruc' 
                            WHERE id_doctor = $id;
                    
                    ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Doctor modificado correctamente"]);
        break;

        case 'eliminar':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $nombre = $db->clearText($_POST['nombre']);
            
            $db->setQuery("DELETE FROM doctores WHERE id_doctor=$id");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1451) {
                    echo json_encode(["status" => "error", "mensaje" => "El doctor '$nombre' no puede ser eliminado"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Doctor '$nombre' eliminado correctamente"]);
        break;		
        
        case 'cambiar-estado':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            if ($estado != 0 && $estado != 1) {
                echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
                exit;
            }

            $db->setQuery("UPDATE doctores SET estado=$estado WHERE id_doctor=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;
            
    }
