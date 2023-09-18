<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $datosUsuario = datosUsuario($usuario);
    $id_usuario = $datosUsuario->id;
    $notificaciones = $datosUsuario->notificaciones;

    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();

            if ($notificaciones == 1) {
                $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                n.id_notificacion,
                                n.titulo,
                                n.descripcion,
                                DATE_FORMAT(n.fecha, '%d/%m/%Y %H:%i') AS fecha,
                                IFNULL(nu.estado, 0) AS estado
                                FROM notificaciones n
                                LEFT JOIN notificaciones_usuarios nu ON n.id_notificacion=nu.id_notificacion AND nu.id_usuario=$id_usuario
                                ORDER BY fecha DESC
                                LIMIT 6");
                $rows = $db->loadObjectList() ?: [];
            } else {
                $rows = [];
            }

            echo json_encode($rows);
        break;

        case 'marcar_leido':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);

            $db->setQuery("INSERT INTO notificaciones_usuarios (id_notificacion, id_usuario, estado) VALUES ($id, $id_usuario, 1) ON DUPLICATE KEY UPDATE estado=1");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado de la notificación"]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Notificación actualizada correctamente"]);
        break;

	}

?>
