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
        $sucursal = $db->clearText($_GET['sucursal']);
        $where = "";
        $where_sucursal='';

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        if (isset($search) && !empty($search)) {
            $having = "HAVING numero LIKE '$search%' OR estado_str LIKE '$search%' OR s.sucursal LIKE '$search%'";
        }

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND c.id_sucursal='$sucursal'";
        }else{
            $where_sucursal .="";
        };

        //obtenemos el valor de configuracion limite de tiempo de conexion 
        $db->setQuery("SELECT limite_caja FROM configuracion ");
        $limite_caja = $db->loadObject()->limite_caja;

        
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                id_caja,
                                numero,
                                c.id_sucursal,
                                s.sucursal,
                                c.estado,
                                c.efectivo_inicial,
                                c.tope_efectivo,
                                c.observacion,
                                c.descripcion,
                                c.ultima_conexion,
                                CASE c.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                                c.usuario,
                                DATE_FORMAT(c.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                                IF ( c.ultima_conexion IS NOT NULL, IF(timestampdiff(DAY, c.ultima_conexion, NOW()) <= $limite_caja, 'Activo' , 'Inactivo'), 'Inactivo')  AS estado_con
                            FROM cajas c
                            LEFT JOIN sucursales s ON c.id_sucursal=s.id_sucursal
                            WHERE 1=1  $where_sucursal 
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
        $db          = DataBase::conectar();
        $numero      = $db->clearText($_POST['numero']);
        $id_sucursal = $db->clearText($_POST['id_sucursal']);
        $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
        $efectivo    = quitaSeparadorMiles($db->clearText($_POST['efectivo']));
        $tope        = quitaSeparadorMiles($db->clearText($_POST['tope']));
        $usuarios    = json_decode($_POST['tabla_usuarios'], true);

        $db->setQuery("SELECT * FROM cajas WHERE numero = '$numero' AND id_sucursal=$id_sucursal");
        $rows = $db->loadObject();
        if (!empty($rows)) {
            echo json_encode(["status" => "error", "mensaje" => "El número de caja ya esta registrada en la sucursal seleccionada."]);
            exit;
        }

        $db->setQuery("INSERT INTO cajas (numero, id_sucursal, estado, observacion, usuario, fecha, efectivo_inicial, tope_efectivo)
                            VALUES ('$numero', $id_sucursal,'1', '$observacion','$usuario',NOW(), '$efectivo', '$tope')");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        $id_caja = $db->getLastID();

        $db->setQuery("SELECT estado FROM cajas WHERE id_caja=$id_caja");
        $estado =$db->loadObject()->estado;
        if ($estado == 1) {
            // Se verifica que los usuarios solo estén en una caja activa por sucursal
            $db->setQuery("SELECT cu.id_usuario, cu.usuario
                            FROM cajas_usuarios cu
                            JOIN cajas c ON cu.id_caja=c.id_caja
                            WHERE c.id_caja != $id_caja AND c.id_sucursal=$id_sucursal AND c.estado = 1 AND cu.id_usuario IN (
	                            SELECT id_usuario
	                            FROM cajas_usuarios
	                            WHERE id_caja = $id_caja
                            )");
            $row = $db->loadObject();
            if (!empty($row)) {
                echo json_encode(["status" => "error", "mensaje" => "El usuario '$row->usuario' ya esta asignado a una caja en la misma sucursal."]);
                exit;
            }
        }

        foreach ($usuarios as $p) {
            $id_usuario = $db->clearText($p["id_usuario"]);
            $usuario    = quitaSeparadorMiles($p["usuario"]);
            $estado = $db->clearText($p["estado"]);

            $db->setQuery("INSERT INTO cajas_usuarios (id_caja, id_usuario, usuario, estado, fecha)
                                VALUES ($id_caja, $id_usuario, '$usuario', $estado, NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }
        }

        echo json_encode(["status" => "ok", "mensaje" => "Caja registrada correctamente"]);

        break;

    case 'editar':
        $db          = DataBase::conectar();
        $id_caja     = $db->clearText($_POST['id_caja']);
        $numero      = $db->clearText($_POST['numero']);
        $id_sucursal = $db->clearText($_POST['id_sucursal']);
        $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");
        $efectivo    = quitaSeparadorMiles($db->clearText($_POST['efectivo']));
        $tope    = quitaSeparadorMiles($db->clearText($_POST['tope']));

        $usuarios = json_decode($_POST['tabla_usuarios'], true);

        if (empty($numero)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese un número para la Caja"]);
            exit;
        }

        $db->setQuery("UPDATE cajas SET numero='$numero', id_sucursal=$id_sucursal, observacion='$observacion',efectivo_inicial='$efectivo',tope_efectivo='$tope' WHERE id_caja='$id_caja'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        $db->setQuery("DELETE FROM cajas_usuarios WHERE id_caja='$id_caja'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error al guardar los usuarios. " . $db->getError(), "error" => "proveedores"]);
            $db->rollback();
            exit;
        }

        $db->setQuery("SELECT estado FROM cajas WHERE id_caja=$id_caja");
        $estado =$db->loadObject()->estado;
        if ($estado == 1) {
            // Se verifica que los usuarios solo estén en una caja activa por sucursal
            $db->setQuery("SELECT cu.id_usuario, cu.usuario
                            FROM cajas_usuarios cu
                            JOIN cajas c ON cu.id_caja=c.id_caja
                            WHERE c.id_caja != $id_caja AND c.id_sucursal=$id_sucursal AND c.estado = 1 AND cu.id_usuario IN (
	                            SELECT id_usuario
	                            FROM cajas_usuarios
	                            WHERE id_caja = $id_caja
                            )");
            $row = $db->loadObject();
            if (!empty($row)) {
                echo json_encode(["status" => "error", "mensaje" => "El usuario '$row->usuario' ya esta asignado a una caja en la misma sucursal."]);
                exit;
            }
        }

        foreach ($usuarios as $p) {
            $id_usuario = $db->clearText($p["id_usuario"]);
            $usuario    = quitaSeparadorMiles($p["usuario"]);
            $estado = $db->clearText($p["estado"]);

            $db->setQuery("INSERT INTO cajas_usuarios (id_caja, id_usuario, usuario, estado, fecha)
                                VALUES ($id_caja, $id_usuario, '$usuario', $estado, NOW())");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }
        }

        echo json_encode(["status" => "ok", "mensaje" => "Caja '$numero' modificado correctamente"]);
        break;

    case 'cambiar-estado':
        $db      = DataBase::conectar();
        $id_caja = $db->clearText($_POST['id_caja']);
        $estado  = $db->clearText($_POST['estado']);

        if ($estado == 1) {
            $db->setQuery("SELECT id_sucursal FROM cajas WHERE id_caja=$id_caja");
            $id_sucursal =$db->loadObject()->id_sucursal;

            // Se verifica que los usuarios solo estén en una caja activa por sucursal
            $db->setQuery("SELECT cu.id_usuario, cu.usuario
                            FROM cajas_usuarios cu
                            JOIN cajas c ON cu.id_caja=c.id_caja
                            WHERE c.id_caja != $id_caja AND c.id_sucursal=$id_sucursal AND c.estado = 1 AND cu.id_usuario IN (
	                            SELECT id_usuario
	                            FROM cajas_usuarios
	                            WHERE id_caja = $id_caja
                            )");
            $row = $db->loadObject();
            if (!empty($row)) {
                echo json_encode(["status" => "error", "mensaje" => "El usuario '$row->usuario' ya esta asignado a una caja en la misma sucursal."]);
                exit;
            }

        }
        
        $db->setQuery("UPDATE cajas SET estado='$estado' WHERE id_caja='$id_caja'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $numero = $db->clearText($_POST['numero']);

        $db->setQuery("DELETE FROM cajas WHERE id_caja = '$id'");
        if (!$db->alter()) {
            if ($db->getErrorCode() == 1451) {
                echo json_encode(["status" => "error", "mensaje" => "Esta caja está asociada a un Timbrado"]);
                exit;
            }
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Caja '$numero' eliminado correctamente"]);
        break;

    case 'ver_usuarios':
        $db      = DataBase::conectar();
        $id_caja = $db->clearText($_REQUEST['id_caja']);

        $db->setQuery("SELECT 
                                id_usuario,
                                usuario,
                                estado,
                                CASE estado WHEN 0 THEN 'Habilitado' WHEN 1 THEN 'Deshabilitado' END AS estado_str
                            FROM cajas_usuarios
                            WHERE id_caja='$id_caja'
                            ORDER BY usuario");
        $rows = ($db->loadObjectList()) ?: [];

        echo json_encode($rows);
        break;

    case 'verificar_usuario':
        $db          = DataBase::conectar();
        $id_usuario  = $db->clearText($_POST['id_usuario']);
        $numero      = $db->clearText($_POST['numero']);
        $id_sucursal = $db->clearText($_POST['id_sucursal']);

        if ($id_sucursal == '') {
            echo json_encode(["status" => "error", "mensaje" => "Debe seleccionar una sucursal."]);
            exit;
        } else {
            $db->setQuery("SELECT *
                                FROM cajas_usuarios cu
                                LEFT JOIN cajas c ON cu.`id_caja`=c.`id_caja`
                                WHERE cu.`id_usuario`= $id_usuario AND c.`id_sucursal` = $id_sucursal AND c.`estado` = 1");
            if (!empty($db->loadObject())) {
                echo json_encode(["status" => "error", "mensaje" => "El usuario ya esta asignado a una caja en la misma sucursal."]);
            } else {
                echo json_encode(["status" => "ok"]);
            }
        }

        break;

    case 'asignar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $descripcion = $db->clearText($_POST['descripcion']);
        $fecha_hora = date('Ymd_His');
        $token     = Password::hash($id . $fecha_hora);

        $db->setQuery("UPDATE cajas SET token ='$token' ,descripcion='$descripcion' , ultima_conexion=NOW()  WHERE id_caja='$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        setcookie("token", $token, time() + 3600 * 24 * 365, "/");

        echo json_encode(["status" => "ok", "mensaje" => "La máquina ha sido asignado a la caja correctamente"]);
        break;

    case 'eliminar_caja':
            $db     = DataBase::conectar();
            $id     = $db->clearText($_POST['id']);
         
        //Actualizacion la conexion de la caja
        $db->setQuery("UPDATE cajas SET ultima_conexion=NULL , token = NULL WHERE id_caja = $id ");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la conexion caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => " La conexion ha finalizado "]);
        
    break;

}
