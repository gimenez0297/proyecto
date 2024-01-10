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

        case 'cargar':
            $db = DataBase::conectar();
            $db->autocommit(false);
            $lote = mb_convert_case($db->clearText($_POST['lote']), MB_CASE_UPPER, "UTF-8");
            $vencimiento = $db->clearText($_POST['vencimiento']);
            $canje = $db->clearText($_POST['canje']) ?: 0;
            $vencimiento_canje = $db->clearText($_POST['vencimiento_canje']);
            $id_producto = $db->clearText($_POST['id_producto']);
            $id_proveedor = $db->clearText($_POST['proveedor']);
            $costo = $db->clearText(quitaSeparadorMiles($_POST['costo']));

            if (empty($lote)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripción para el lote"]);
                exit;
            }
            if (empty($vencimiento)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha de vencimiento del lote"]);
                exit;
            }
            if ($canje == 1 && empty($vencimiento_canje)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese la fecha de vencimiento del canje"]);
                exit;
            }
            if (empty($id_proveedor)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un proveedor"]);
                exit;
            }

            $db->setQuery("INSERT INTO lotes (id_proveedor,lote, vencimiento, canje, vencimiento_canje, costo, usuario, fecha)
                            VALUES ('$id_proveedor','$lote','$vencimiento',$canje,'$vencimiento_canje', $costo,'$usuario',NOW())");
            if (!$db->alter()) {
                if ($db->getErrorCode() == 1062) {
                    echo json_encode(["status" => "error", "mensaje" => "El lote cargado ya existe"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                }
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            $id = $db->getLastID();


            $db->setQuery("INSERT INTO stock(id_producto, id_sucursal, id_lote, stock)
                        SELECT $id_producto, id_sucursal, $id, 0 FROM sucursales");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            $data = ["id" => $id, "lote" => $lote, "vencimiento" => $vencimiento, "canje" => $canje, "vencimiento_canje" => $vencimiento_canje ];

            $db->commit(); // Se guardan los cambios
            echo json_encode(["status" => "ok", "mensaje" => "Lote registrado correctamente", "data" => $data]);
            
        break;

        case 'obtener-lote':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_POST["id_producto"]);
            $public_cantidad = $db->clearText(quitaSeparadorMiles($_POST["public_cantidad"]));

            $db->setQuery("SELECT lote_automatico, lote_prefijo
                            FROM tipos_productos tp
                            JOIN productos p ON tp.id_tipo_producto=p.id_tipo_producto
                            WHERE id_producto=$id_producto");
            $row = $db->loadObject();

            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el lote"]);
                exit;
            }

            $lote_automatico = $row->lote_automatico;
            $prefijo = $row->lote_prefijo;

            if ($lote_automatico == 1) {
                $db->setQuery("SELECT id_lote, MAX(lote) AS lote FROM lotes WHERE lote LIKE '$prefijo%'");
                $row = $db->loadObject();

                if ($db->error()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el lote"]);
                    exit;
                }

                if (isset($row)) {
                    $sgte_lote = intval(preg_replace("/^$prefijo/i", "", $row->lote)) + $public_cantidad;
                    $lote = $prefijo.zerofill($sgte_lote);
                }else{
                    $lote = $prefijo.zerofill(1);
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Lote", "lote" => $lote]);

        break;

	}

?>
