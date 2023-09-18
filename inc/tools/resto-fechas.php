<?php
// ACTUALIZA TODAS LAS FECHAS DE VENCIMIENTOS PARA TODOS LOS PRODUCTOS CON FECHAS 
include ("../funciones.php");
verificaLogin();
$usuario = $auth->getUsername();

$db = DataBase::conectar();
$db->autocommit(false);

    $archivo = "sin_perfectos.csv";
    if (!file_exists($archivo)) {
        echo json_encode(["mensaje"=>"No se encontró el archivo", "status"=>"error"]);
        exit;
    }
    $conenido = file_get_contents($archivo);
    $array = explode("\r", $conenido);
    $cantidad = count($array);
    $i = 0;
    $duplicados = 0;
    $omitidos = 0;

    while ($i < $cantidad) {
        $linea = $array[$i];
        $linear = explode(';', $linea);

        $set_lote = null;

        $cod_interno = trim(utf8_encode($db->clearText($linear[0])));
        $cod_barra = trim(utf8_encode($db->clearText($linear[3])));
        $producto = trim(utf8_encode($db->clearText($linear[4])));
        $lote = $const_lote = trim(utf8_encode($db->clearText($linear[1])));
        $fecha_v = trim(utf8_encode($db->clearText($linear[2])));

        /* SI NO EXISTE FECHA */
        if (!empty($fecha_v)) {
            $set_lote .= " vencimiento = '$fecha_v' ";
        }
        /* FIN SI */

        /* SE ACTUALIZA SU VENCIMIENTO SI ES NECESARIO*/
        if (!empty($set_lote)) {
            $id_p = $cod_interno;
            if (!empty($id_p)) {
                $db->setQuery("UPDATE lotes SET $set_lote WHERE lote = '$lote';");
                if (!$db->alter()) {
                    $db->rollback();
                    echo json_encode(["mensaje"=>"No se pudo actualizar el vencimiento de lote $lote. Error: ".$db->getError(), "status"=>"error"]);
                    exit;
                }
            }else{
                $db->rollback();
                echo json_encode(["mensaje"=>"No se encontró el id del producto $producto.", "status"=>"error"]);
                exit;
            }
        }

        $i++;

    }
    $db->commit();
    echo json_encode(["mensaje"=>"DATOS ACTUALIZADOS CORRECTAMENTE", "status"=>"success"]);

?>