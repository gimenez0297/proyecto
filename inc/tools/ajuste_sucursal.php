<?php
// ACTUALIZACION DE STOCK CON LOTES FICTICIOS
include ("../funciones.php");
verificaLogin();
$usuario = $auth->getUsername();
$id_sucursal = $_REQUEST['sucursal'];

$db = DataBase::conectar();
$db->autocommit(false);

// UPDATE STOCK

$db->setQuery("UPDATE `stock` s JOIN lotes l ON s.id_lote = l.id_lote SET stock = stock - 500 WHERE s.`id_sucursal` = $id_sucursal AND l.lote LIKE '031523-%' AND l.vencimiento = '2024-12-31' AND stock >= '500'");
if (!$db->alter()) {
    $db->rollback();
    echo json_encode(["mensaje"=>"Error de base de datos. Error: ".$db->getError(), "status"=>"error"]);
    exit;
}

$db->commit();
echo json_encode(["mensaje"=>"DATOS ACTUALIZADOS CORRECTAMENTE", "status"=>"success"]);

?>