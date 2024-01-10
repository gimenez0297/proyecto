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



        //Se extrae el ID de cada solicitud
        $solicitudes = [];
        foreach ($productos as $p) {
            // Carga de solicitudes
            $id_solicitud_deposito = $db->clearText($p["id_solicitud_deposito"]);
            if (isset($id_solicitud_deposito) && !empty($id_solicitud_deposito)) {
                if (array_search($id_solicitud_deposito, $solicitudes) === false) {
                    $solicitudes[] = $id_solicitud_deposito;
                }
            }
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
                            0
                        )");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la Nota De Remisión"]);
            $db->rollback(); // Revertimos los cambios
            exit;
        }

        $id_nota_remision = $db->getLastID();

        $db->setQuery("SELECT  * FROM sucursales WHERE id_sucursal = $id_sucursal_destino AND deposito = 1");
        $incluir_vencidos = !empty($db->loadObject());

        foreach ($productos as $p) {
            $id_producto = $db->clearText($p["id_producto"]);
            $id_solicitud_deposito = $db->clearText($p["id_solicitud_deposito"]) ?: 'NULL';
            $codigo = $db->clearText(quitaSeparadorMiles($p["codigo"]));
            $producto = $db->clearText($p["producto"]);
            // $numero = $db->clearText($p["numero"]);
            $cantidad = $db->clearText(quitaSeparadorMiles($p["cantidad"]));
            $id_lote = $db->clearText($p["id_lote"]);
            $lote = $db->clearText($p["lote"]);
            $stock = $db->clearText($p["stock"]);

            try {
                $stock_sucursal = producto_stock_sucursal($db, $id_producto, $id_sucursal_origen, $incluir_vencidos);
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

            if (isset($id_solicitud_deposito) && !empty($id_solicitud_deposito) && $id_solicitud_deposito != 'NULL') {
                $pendiente = sdp_cantidad_pendiente($db, $id_solicitud_deposito, $id_producto);

                if ($pendiente - $cantidad  < 0) {
                    echo json_encode(["status" => "error", "mensaje" => "La cantidad a enviar del producto \"$producto\" es mayor a la cantidad pendiente en la solicitud de deposito N° $numero"]);
                    $db->rollback();
                    exit;
                }
            }

            $db->setQuery("INSERT INTO `notas_remision_productos` (
                                id_nota_remision,
                                id_solicitud_deposito,
                                id_producto,
                                codigo,
                                producto,
                                id_lote,
                                lote,
                                cantidad
                            ) VALUES (
                                $id_nota_remision,
                                $id_solicitud_deposito,
                                $id_producto,
                                $codigo,
                                '$producto',
                                $id_lote,
                                '$lote',
                                $cantidad
                            )");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar las mercaderías"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_nota_remision_producto = $db->getLastID();

            try {
                producto_restar_stock($db, $id_producto, $id_sucursal_origen, $id_lote, $cantidad, 0);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                exit;
            }

            $stock = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal_origen;
            $stock->id_lote = $id_lote;

            $historial = new stdClass();
            $historial->cantidad = $cantidad;
            $historial->fraccionado = 0;
            $historial->operacion = SUB;
            $historial->id_origen = $id_nota_remision_producto;
            $historial->origen = REM;
            $historial->detalles = "Nota De Remisión N° " . zerofill($numero);
            $historial->usuario = $usuario;

            if (!stock_historial($db, $stock, $historial)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }
        }

        // Se verifica para realizar la notificación
        producto_verificar_niveles_stock($db, $id_producto, $id_sucursal_origen);

        if (isset($id_solicitud_deposito) && !empty($id_solicitud_deposito) && $id_solicitud_deposito != 'NULL') {
            try {
                sdp_actualizar_estado_auto($db, $id_solicitud_deposito, $id_producto);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                exit;
            }
        }

        if (count($solicitudes) > 0) {
            // Se actualiza el estado de las solicitudes
            foreach ($solicitudes as $id) {
                try {
                    sd_actualizar_estado_auto($db, $id);
                } catch (Exception $e) {
                    $db->rollback();  // Revertimos los cambios
                    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
                    exit;
                }
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

    case 'ver_solicitudes':
        $db = DataBase::conectar();
        $id = $db->clearText($_REQUEST['id']);
        $id_origen = $db->clearText($_REQUEST['id_origen']);
        $limit = $db->clearText($_REQUEST['limit']) ?: 15;
        $offset    = $db->clearText($_REQUEST['offset']) ?: 0;
        $search = $db->clearText($_REQUEST['search']);
        $order = $db->clearText($_REQUEST['order']);
        $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = " HAVING p.producto LIKE '$search%' OR p.codigo LIKE '$search%' OR pr.presentacion LIKE '$search%' OR sd.numero LIKE '$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            sdp.id_solicitud_deposito_producto,
                            sdp.id_solicitud_deposito, 
                            sdp.cantidad - (
                                    SELECT IFNULL(SUM(cantidad), 0)
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr  ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado !=2  AND nrp.id_solicitud_deposito=sd.id_solicitud_deposito AND nrp.id_producto=sdp.id_producto
                                ) AS pendiente, 
                                (
                                SELECT 
                                IFNULL(SUM(s.stock), 0)
                                FROM stock s 
                                JOIN lotes l ON s.id_lote=l.id_lote
                                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_origen AND vencimiento>=CURRENT_DATE()
                            ) AS stock, 
                            sd.id_sucursal,
                            sd.numero, 
                            sd.usuario, 
                            sdp.id_producto,
                            p.producto,
                            p.codigo,
                            p.id_presentacion,
                            pr.presentacion,
                            s.sucursal
                        FROM
                            solicitudes_depositos_productos sdp
                        LEFT JOIN solicitudes_depositos sd ON sd.id_solicitud_deposito= sdp.id_solicitud_deposito
                        LEFT JOIN productos p ON p.id_producto = sdp.id_producto
                        LEFT JOIN sucursales s ON s.id_sucursal = sd.id_sucursal
                        LEFT JOIN presentaciones pr ON p.id_presentacion = pr.id_presentacion
                        WHERE sd.id_sucursal = $id AND sd.id_deposito = $id_origen AND sd.estado IN(1,3) AND sdp.cantidad - (
                                    SELECT IFNULL(SUM(cantidad), 0)
                                    FROM notas_remision_productos nrp
                                    JOIN notas_remision nr  ON nrp.id_nota_remision=nr.id_nota_remision
                                    WHERE nr.estado !=2  AND nrp.id_solicitud_deposito=sd.id_solicitud_deposito AND nrp.id_producto=sdp.id_producto
                                )!=0
                        $having
                        ORDER BY $sort $order
                        LIMIT $offset, $limit");
        $rows = $db->loadObjectList();
        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total = $total_row->total;

        if ($rows) {
            $salida = array('total' => $total, 'rows' => $rows);
        } else {
            $salida = array('total' => 0, 'rows' => array());
        }

        echo json_encode($salida);
        break;

        case 'ver_lotes':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_GET['id_producto']);
            $id_sucursal = $db->clearText($_GET['id_sucursal']);
            $id_destino  = $db->clearText($_GET['id_destino']);
            $vencimiento = "AND l.vencimiento>=CURRENT_DATE()";

            $db->setQuery("SELECT  * FROM sucursales WHERE id_sucursal = $id_destino AND deposito = 1");
            $row = $db->loadObject();

            if (!empty($row)) {
                $vencimiento = "";
            }

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                l.id_lote, 
                                l.lote, 
                                l.vencimiento, 
                                s.stock, 
                                s.fraccionado, 
                                l.costo, 
                                p.cantidad_fracciones as cant
                            FROM lotes l
                            JOIN stock s ON l.id_lote=s.id_lote
                            LEFT JOIN productos p ON p.id_producto = s.id_producto
                            WHERE s.id_producto=$id_producto AND s.id_sucursal=$id_sucursal AND (s.stock > 0 OR s.fraccionado > 0) $vencimiento
                            AND l.lote LIKE '%$term%'
                            ORDER BY l.vencimiento
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
