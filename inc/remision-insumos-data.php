<?php
include("funciones.php");
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
        $id_sucursal_origen = $db->clearText($_POST['sucursal_origen']);
        $id_sucursal_destino = $db->clearText($_POST['sucursal_destino']);
        $ruc_destino = $db->clearText($_POST['ruc_destino']);
        $razon_social_destino =  mb_convert_case($db->clearText($_POST['razon_social_destino']), MB_CASE_UPPER, "UTF-8");
        $domicilio_destino =  mb_convert_case($db->clearText($_POST['razon_social_destino']), MB_CASE_UPPER, "UTF-8");
        $id_nota_remision_motivo = $db->clearText($_POST['motivo']);
        $comprobante_venta = $db->clearText($_POST['comprobante_venta']);
        $comprobante_nro = $db->clearText($_POST['comprobante_nro']);
        $comprobante_timbrado = $db->clearText($_POST['comprobante_timbrado']);
        $fecha_expedicion = $db->clearText($_POST['fecha_expedicion']);
        $fecha_inicio = $db->clearText($_POST['fecha_inicio']);
        $fecha_fin = $db->clearText($_POST['fecha_fin']);
        $ruc_chofer = $db->clearText($_POST['ruc_chofer']);
        $razon_social_chofer =  mb_convert_case($db->clearText($_POST['razon_social_chofer']), MB_CASE_UPPER, "UTF-8");
        $domicilio_chofer =  mb_convert_case($db->clearText($_POST['domicilio_chofer']), MB_CASE_UPPER, "UTF-8");
        $marca_vehiculo =  mb_convert_case($db->clearText($_POST['marca_vehiculo']), MB_CASE_UPPER, "UTF-8");
        $rua =  mb_convert_case($db->clearText($_POST['rua']), MB_CASE_UPPER, "UTF-8");
        $km = $db->clearText($_POST['km']);
        $productos = json_decode($_POST['productos'], true);

        if (empty($id_sucursal_origen)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione la sucursal de origen"]);
            exit;
        }
        if (empty($id_nota_remision_motivo)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un motivo"]);
            exit;
        }
        if ($id_nota_remision_motivo == 1) {
            if (empty($id_sucursal_destino)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione la sucursal de destino"]);
                exit;
            } else if ($id_sucursal_origen == $id_sucursal_destino) {
                echo json_encode(["status" => "error", "mensaje" => "El destino de envío no puede ser igual al de origen"]);
                exit;
            }
            // OBTEMOS DATOS DE LA SUCURSAL DE DESTINO
            $datos_destino = sucursal($db, $id_sucursal_destino);
            if ($db->error()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al obtener la información del destinatario"]);
                exit;
            }
            $ruc_destino = $datos_destino->ruc;
            $razon_social_destino = $datos_destino->razon_social;
            $domicilio_destino = $datos_destino->direccion;
            $estado = 1;
        } else {
            $estado = 3;
        }
        if (empty($id_sucursal_destino)) {
            $id_sucursal_destino = "NULL";
            if (empty($razon_social_destino)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor el ingrese nombre o razón social de la persona o empresa que recibirá las mercaderías"]);
                exit;
            }
            if (empty($ruc_destino)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese el RUC o CI de la persona o empresa que recibirá las mercaderías"]);
                exit;
            }
        }
        if (empty($comprobante_nro)) {
            $fecha_expedicion = "NULL";
        } else {
            $fecha_expedicion = "'$fecha_expedicion'";
        }
        if (empty($fecha_inicio)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese fecha de salida o inicio del traslado."]);
            exit;
        }
        if (empty($fecha_fin)) {
            echo json_encode(["status" => "error", "mensaje" => "Error. Favor ingrese fecha de llegada o fin del traslado."]);
            exit;
        }
        if (empty($productos)) {
            echo json_encode(["status" => "error", "mensaje" => "Ningún producto agregado. Favor verifique."]);
            exit;
        }

        // OBTEMOS DATOS DE LA SUCURSAL DE ORIGEN
        $datos_rtte = sucursal($db, $id_sucursal_origen);
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al obtener la información del remitente"]);
            exit;
        }
        $ruc_rtte = $datos_rtte->ruc;
        $razon_social_rtte = $datos_rtte->razon_social;
        $domicilio_rtte = $datos_rtte->direccion;

        // OBTEMOS LA DESCRIPCIÓN DEL MOTIVO
        $db->setQuery("SELECT descripcion FROM notas_remision_motivos WHERE id_nota_remision_motivo=$id_nota_remision_motivo");
        $motivo = $db->loadObject()->descripcion;
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al obtener el motivo"]);
            exit;
        }

        // DATOS DEL TIMBRADO
        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal_origen AND estado=1 AND tipo=2");
        $tim = $db->loadObject();
        $id_timbrado = $tim->id_timbrado;

        if (empty($id_timbrado)) {
            echo json_encode(["status" => "error", "mensaje" => "No existen timbrados activos para la Nota De Remisión. Favor consulte con su superior."]);
            exit;
        }

        // OBTENEMOS EL MAYOR NUMERO DE NOTA DE REMISIÓN
        $db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM notas_remision WHERE id_timbrado=$id_timbrado");
        $r_max = $db->loadObject();
        $numero = $r_max->ultimo_nro + 1;

        $db->setQuery("SELECT IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_timbrado=$id_timbrado");
        $hasta = $db->loadObject();
        $numero_hasta = $hasta->hasta;

        if ($numero > $numero_hasta) {
            echo json_encode(["status" => "error", "mensaje" => "No tiene números disponibles para generar Notas De Remisión"]);
            exit;
        }

        // Cabeceera
        $db->setQuery("INSERT INTO notas_remision (
                            id_timbrado,
                            id_sucursal_origen,
                            id_sucursal_destino,
                            numero,
                            fecha_emision,
                            ruc_rtte,
                            razon_social_rtte,
                            domicilio_rtte,
                            ruc_destino,
                            razon_social_destino,
                            domicilio_destino,
                            id_nota_remision_motivo,
                            motivo,
                            comprobante_venta,
                            comprobante_nro,
                            comprobante_timbrado,
                            fecha_expedicion,
                            fecha_inicio,
                            fecha_fin,
                            km,
                            marca_vehiculo,
                            rua,
                            rua_remolque,
                            ruc_chofer,
                            razon_social_chofer,
                            domicilio_chofer,
                            estado,
                            fecha,
                            usuario,
                            tipo_remision
                        ) VALUES (
                            $id_timbrado,
                            $id_sucursal_origen,
                            $id_sucursal_destino,
                            $numero,
                            NOW(),
                            '$ruc_rtte',
                            '$razon_social_rtte',
                            '$domicilio_rtte',
                            '$ruc_destino',
                            '$razon_social_destino',
                            '$domicilio_destino',
                            $id_nota_remision_motivo,
                            '$motivo',
                            '$comprobante_venta',
                            '$comprobante_nro',
                            '$comprobante_timbrado',
                            '$fecha_expedicion',
                            '$fecha_inicio',
                            '$fecha_fin',
                            '$km',
                            '$marca_vehiculo',
                            '$rua',
                            '$rua_remolque',
                            '$ruc_chofer',
                            '$razon_social_chofer',
                            '$domicilio_chofer',
                            $estado,
                            NOW(),
                            '$usuario',
                            1
                        )");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la Nota De Remisión"]);
            $db->rollback(); // Revertimos los cambios
            exit;
        }

        $id_nota_remision = $db->getLastID();

        foreach ($productos as $p) {
            $id_producto = $db->clearText($p["id_producto"]);
            $codigo = $db->clearText(quitaSeparadorMiles($p["codigo"]));
            $producto = $db->clearText($p["producto"]);
            $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
            $stock = $db->clearText($p["stock"]);
            $vencimiento = $db->clearText(fechaMYSQL($p["vencimiento"]));
            $id_stock_insumo = $db->clearText($p["id_stock_insumo"]);

            $vencimiento_null = "$vencimiento";

            if ($vencimiento == '--SIN VENCIM') {
                $vencimiento_null = ''; 
            }

            try {
                $stock_sucursal = producto_insumo_stock($db, $id_producto, $vencimiento_null);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                exit;
            }

            if ($stock_sucursal->stock < $cantidad) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "El stock del producto \"$producto\" es insuficiente"]);
                exit;
            }

            if (empty($cantidad)) {
                echo json_encode(["status" => "error", "mensaje" => "El producto \"$producto\" tiene cantidad 0. Favor verifique."]);
                exit;
            }

            $db->setQuery("SELECT IFNULL(costo,0) as costo FROM productos_insumo WHERE id_producto_insumo=$id_producto");
            $costo = $db->loadObject()->costo;

            $db->setQuery("INSERT INTO `notas_remision_insumo` (
                                id_nota_remision,
                                id_producto_insumo,
                                codigo,
                                producto,
                                cantidad,
                                costo
                            ) VALUES (
                                $id_nota_remision,
                                $id_producto,
                                $codigo,
                                '$producto',
                                $cantidad,
                                $costo
                            )");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los insumos"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_nota_remision_producto = $db->getLastID();

            try {
                producto_insumo_restar_stock($db, $id_producto, $vencimiento_null, $cantidad);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                exit;
            }

            $historial = new stdClass();
            $historial->id_producto_insumo = $id_producto;
            $historial->producto = $producto;
            $historial->vencimiento = $vencimiento_null;
            $historial->operacion = SUB;
            $historial->id_origen = $id_nota_remision;
            $historial->origen = REM;
            $historial->detalles = "Remisión N° " .zerofill($numero);
            $historial->usuario = $usuario;

            if (!stock_historial_insumo($db, $historial)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Nota De Remisión registrada correctamente", "id_nota_remision" => $id_nota_remision]);
    break;

    case 'recuperar-numero':
        $db = DataBase::conectar();

        $db->setQuery("SELECT MAX(numero) AS numero FROM notas_remision");
        $row = $db->loadObject();

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número"]);
            exit;
        }

        $sgte_numero = intval($row->numero) + 1;
        $numero = zerofill($sgte_numero);

        echo json_encode(["status" => "ok", "mensaje" => "Numero", "numero" => $numero]);
        break;

    case 'datos-sucursal':
        $db = DataBase::conectar();
        $id = $db->clearText($_POST['id']);
        echo json_encode(sucursal($db, $id));
        break;

    case 'ver_vencimiento':
            $db = DataBase::conectar();
            $id_producto_insumo = $db->clearText($_GET['id_producto_insumo']);

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                id_stock_insumo,
                                id_producto_insumo,
                                stock,
                                IFNULL(DATE_FORMAT(vencimiento, '%d/%m/%Y'),'SIN VENCIMIENTO') AS vencimiento

                            FROM `stock_insumos`
                            WHERE id_producto_insumo = $id_producto_insumo AND (vencimiento >= CURRENT_DATE() OR vencimiento IS NULL) 
                            HAVING vencimiento LIKE '%$term%'
                            ORDER BY vencimiento ASC
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

}

function sucursal($db, $id_sucursal)
{
    $db->setQuery("SELECT s.ruc, s.razon_social, CONCAT_WS(' - ', s.direccion, d.nombre, de.nombre) AS direccion
                        FROM sucursales s
                        LEFT JOIN distritos d ON s.id_distrito=d.id_distrito
                        LEFT JOIN departamentos de ON d.id_departamento=de.id_departamento
                        WHERE id_sucursal=$id_sucursal");
    return $db->loadObject();
}
