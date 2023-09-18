<?php
// Cera todos los productos por sucursal

include ("../funciones.php");
verificaLogin();
$id_sucursal = $_REQUEST['sucursal'];
$usuario = $auth->getUsername();

$db = DataBase::conectar();
$db->autocommit(false);

if (!empty($id_sucursal)) {

    $db->setQuery("UPDATE stock SET stock = 0, fraccionado = 0 WHERE id_sucursal = $id_sucursal;");
    if (!$db->alter()) {
        $db->rollback();
        echo json_encode(["mensaje"=>"No se pudo actualizar el stock.", "status"=>"error"]);
        exit;
    }
    $db->commit();
    echo json_encode(["mensaje"=>"Datos actualizados con éxito", "status"=>"ok"]);
}else {
    echo json_encode(["mensaje"=>"Se necesita el id de la sucursal", "status"=>"error"]);
}


?>