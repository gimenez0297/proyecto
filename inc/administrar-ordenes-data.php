<?php
include("funciones.php");
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q = $_REQUEST['q'];
$usuario = $auth->getUsername();
$datosUsuario = datosUsuario($usuario);
$id_sucursal = datosUsuario($usuario)->id_sucursal;
$id_usuario = datosUsuario($usuario)->id;

switch ($q) {
    case 'ver':
        $db = DataBase::conectar();
        $desde = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta = $db->clearText($_REQUEST['hasta']) . " 23:59:59";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit = $db->clearText($_REQUEST['limit']);
        $offset    = $db->clearText($_REQUEST['offset']);
        $order = $db->clearText($_REQUEST['order']);
        $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING ruc LIKE '$search%' OR razon_social LIKE '$search%' OR  direccion LIKE '$search%' OR referencia LIKE '$search%' OR metodo_pago LIKE '$search%' OR ruc LIKE '$search%' OR razon_social LIKE '$search%' OR fecha LIKE '$search%' OR estado_str LIKE '$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            o.`id_orden`,
                            o.`id_cliente`,
                            o.`ruc`,
                            o.`razon_social`,
                            o.`direccion`,
                            o.`referencia`,
                            o.`telefono`,
                            o.`email`,
                            o.`cantidad`,
                            o.`contraentrega_monto`,
                            o.`total` - o.`total_delivery` AS subtotal,
                            o.`contraentrega_monto` - o.`total` AS vuelto,
                            o.`total`,
                            CASE o.`metodo_pago` WHEN 1 THEN 'TARJETA DE CREDITO' WHEN 2 THEN 'CONTRAENTREGA' END AS metodo_pago,
                            DATE_FORMAT(o.fecha, '%d/%m/%Y %H:%i') AS fecha,
                            o.total_delivery,
                            ds.nombre AS ciudad,
                            CASE o.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Procesado' WHEN 2 THEN 'Anulado' END AS estado_str,
                            o.estado
                        FROM
                            ordenes o
                        LEFT JOIN delivery d ON d.id_delivery = o.id_delivery
                        LEFT JOIN distritos ds ON ds.id_distrito = d.id_distrito
                        WHERE o.`fecha` BETWEEN '$desde' AND '$hasta'
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

    case 'facturar':
        $db = DataBase::conectar();
        $id_orden = $db->clearText($_REQUEST['id']);

        if (empty($id_orden)) {
            echo json_encode(["status" => "error", "mensaje" => "Orden no encontrada."]);
            exit;
        }
        
        $editar_cobros = 0;

        $token_caja = $_COOKIE['token'];
        $caja_abierta = false;
        
        //VERIFICAMOS SI LA MAQUINA SE ENCUENTRA ASIGANADA A LA CAJA WEB.
        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración.", "caja_abierta" => $caja_abierta]);
            exit;
        }

        //VERIFICAMOS SI EL USUARIO ESTA ASIGNADO A ESTA CAJA.
        $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.estado=1 AND cu.estado=0 AND c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $id_caja = $db->loadObject()->id_caja;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración."]);
            exit;
        }

        //VERIFICAMOS SI HAY CAJA ABIERTA 
        $caja = caja_abierta($db, $id_sucursal, $usuario);
        $id_caja_horario = $caja->id_caja_horario;
        $id_caja = $caja->id_caja;
        if (!$caja) {
            echo json_encode(["status" => "error", "mensaje" => "No se ha encontrado ninguna caja abierta"]);
            exit;
        }

        //OBTENEMOS LA INFORMACION DE LA CABEZERA DE LA ORDEN.
        $db->setQuery("SELECT * FROM ordenes WHERE id_orden = $id_orden");
        $row_orden = $db->loadObject();

        //Obtenemos el estado de la orden para saber si ya fue facturada o anulada.
        $estado_orden = $row_orden->estado;
        //Obtenemos el metodo de pago.
        $metodo_pago = $row_orden->metodo_pago;
        //Obtenemos el id_cliente, ruc y razon social de la orden.
        $id_cliente = $row_orden->id_cliente;
        $ruc = $row_orden->ruc; 
        $razon_social = $row_orden->razon_social;

        if ($estado_orden == 1) {
            echo json_encode(["status" => "error", "mensaje" => "Orden ya facturado."]);
            exit;
        }else if ($estado_orden == 2) {
            echo json_encode(["status" => "error", "mensaje" => "Orden anulada."]);
            exit;
        }

        if ($metodo_pago == 1) {
            $editar_cobros = 1; 
        }

        //OBTENEMOS LOS PRODUCTOS DE LA ORDEN 
        $db->setQuery("SELECT * FROM ordenes_detalles WHERE id_orden = $id_orden");
        $row_orden_p = $db->loadObjectList();


        //DATOS DEL TIMBRADO
        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND id_caja =$id_caja AND estado=1 AND tipo='0'");
        $tim = $db->loadObject();
        $id_timbrado = $tim->id_timbrado;
        
        if (empty($id_timbrado)){
            echo json_encode(["status" => "error","mensaje" => "Error. No existen timbrados activos para la Factura. Consulte con administración."]);
            //$db->rollback();
            exit;
        }

        //OBTENEMOS EL MAYOR NUMERO DE FACTURA
        $db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM facturas WHERE id_timbrado= $id_timbrado");
        $r_max = $db->loadObject();    
        $numero_fact = $r_max->ultimo_nro+1;

        $db->setQuery("SELECT IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_timbrado = $id_timbrado");
        $hasta = $db->loadObject();    
        $numero_hasta = $hasta->hasta;

        if ($numero_fact > $numero_hasta) {
            echo json_encode(["status" => "error", "mensaje" => "Ya no tiene numeros disponible para facturar"]);
            exit;
        };

        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al generar el número de la factura"]);
            exit;
        }

        $gravada_10 = 0; $gravada_5 = 0; $exenta = 0; $cantidad = 0; $total_venta = 0; 
        foreach ($row_orden_p as $key => $p) {
            $id_producto = $db->clearText($p->id_producto);
            $cantidad += $db->clearText(quitaSeparadorMiles($p->cantidad));
            $precio = $db->clearText(quitaSeparadorMiles($p->cantidad));
            $total_venta += $db->clearText(quitaSeparadorMiles($p->total_orden));
            $total_o = $db->clearText(quitaSeparadorMiles($p->total_orden));

            //Buscamos que tipo de iva tiene el producto.
            $db->setQuery("SELECT * FROM productos WHERE id_producto = $id_producto");
            $iva = $db->loadObject()->iva;

            if ($iva == 1) {
                $exenta += $total_o;
            }else if($iva == 2){
                $gravada_5 +=$total_o;
            }else if ($iva == 3) {
                $gravada_10 += $total_o;
            }
        }

        $db->setQuery("INSERT INTO `facturas` (
                        `id_sucursal`,
                        `id_caja_horario`,
                        `id_timbrado`,
                        `id_nota_credito`,
                        `id_orden`,
                        `numero`,
                        `fecha_venta`,
                        `condicion`,
                        `vencimiento`,
                        `id_cliente`,
                        `ruc`,
                        `razon_social`,
                        `cantidad`,
                        `descuento`,
                        `total_costo`,
                        `total_venta`,
                        `exenta`,
                        `gravada_5`,
                        `gravada_10`,
                        `saldo`,
                        `receta`,
                        `id_doctor`,
                        `delivery`,
                        `id_delivery`,
                        `courier`,
                        `editar_cobros`,
                        `usuario`,
                        `estado`,
                        `fecha`)
                    VALUES
                        (
                        $id_sucursal,
                        $id_caja_horario,
                        $id_timbrado,
                        NULL,
                        $id_orden,
                        $numero_fact,
                        NOW(),
                        1,
                        NULL,
                        $id_cliente,
                        '$ruc',
                        '$razon_social',
                        $cantidad,
                        0,
                        0,
                        $total_venta,
                        $exenta,
                        $gravada_5,
                        $gravada_10,
                        0,
                        0,
                        NULL,
                        1,
                        NULL,
                        0,
                        $editar_cobros,
                        '$usuario',
                        1,
                        NOW());");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar la factura"]);
            $db->rollback();  //Revertimos los cambios
            exit;
        }

        $id_factura = $db->getLastID();

        //Detalle de la factura
        foreach ($row_orden_p as $key => $p) {
            $id_producto = $db->clearText($p->id_producto);
            $producto = $db->clearText($p->producto);
            $cantidad = $db->clearText(quitaSeparadorMiles($p->cantidad));
            $precio = $db->clearText(quitaSeparadorMiles($p->precio));

            $total_producto = $cantidad * $precio;

            //Buscamos que tipo de iva tiene el producto.
            $db->setQuery("SELECT * FROM productos WHERE id_producto = $id_producto");
            $iva = $db->loadObject()->iva;

            if ($iva == 1) {
                $iva_nro = 0;
            }else if($iva == 2){
                $iva_nro +=5;
            }else if ($iva == 3) {
                $iva_nro += 10;
            }
            
            //Recupera el stock de la sucursal por producto.
            try {
                $stock_sucursal = producto_stock_sucursal($db, $id_producto, $id_sucursal);
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

            // La resta del stock se realiza por lote
            $db->setQuery("SELECT l.id_lote, l.lote, l.vencimiento, s.stock, s.fraccionado
            FROM lotes l
            JOIN stock s ON l.id_lote=s.id_lote
            WHERE s.id_producto=$id_producto AND id_sucursal=$id_sucursal AND vencimiento>=CURRENT_DATE()
            ORDER BY vencimiento ASC");
            $rows = $db->loadObjectList();

            if (empty($rows)) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "No se ha encontrado ningún lote para el producto \"$producto\""]);
                exit;
            }

            foreach ($rows as $v) {
                    $id_lote = $v->id_lote;
                    $lote = $v->lote;
                    $stock = $v->stock;
                    $stock_fraccionado = $v->fraccionado;

                    // Se omite el lote si es cero
                    if ($stock == 0) continue;

                    // Se calcula cuanto restar de cada lote
                    if ($cantidad_restar <= $stock) {
                        $restar_stock = $cantidad_restar;
                        $cantidad_restar = 0;
                    } else {
                        $restar_stock = $stock;
                        $cantidad_restar -= $stock;
                    }

                    $restar_fraccionado = 0;
                    $cantidad_venta = $restar_stock;
            }

            //Insertamos los productos en el detalle
            $db->setQuery("INSERT INTO `facturas_productos` (
                        `id_factura`,
                        `id_producto`,
                        `producto`,
                        `id_lote`,
                        `lote`,
                        `fraccionado`,
                        `cantidad`,
                        `precio`,
                        `remate`,
                        `tipo_descuento`,
                        `descuento`,
                        `descuento_porc`,
                        `total_venta`,
                        `iva`,
                        `comision`,
                        `comision_concepto`
                        )
                    VALUES
                        (
                        $id_factura,
                        $id_producto,
                        '$producto',
                        $id_lote,
                        '$lote',
                        0,
                        $cantidad,
                        $precio,
                        0,
                        NULL,
                        0,
                        0,
                        $total_producto,
                        $iva_nro,
                        0,
                        NULL
                        );");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar la factura"]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_factura_producto = $db->getLastID();

            $restar_stock = $cantidad;

            try {
                producto_restar_stock($db, $id_producto, $id_sucursal, $id_lote, $restar_stock, 0);
            } catch (Exception $e) {
                $db->rollback();  // Revertimos los cambios
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el stock del producto \"$producto\""]);
                exit;
            }

            $stock = new stdClass();
            $stock->id_producto = $id_producto;
            $stock->id_sucursal = $id_sucursal;
            $stock->id_lote = $id_lote;

            $historial = new stdClass();
            $historial->cantidad = $restar_stock;
            $historial->fraccionado = 0;
            $historial->operacion = SUB;
            $historial->id_origen = $id_factura_producto;
            $historial->origen = FAC;
            $historial->detalles = "Factura N° ".zerofill($numero_fact);
            $historial->usuario = $usuario;

            if (!stock_historial($db, $stock, $historial)) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar el historial de stock"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }

            // Se verifica para realizar la notificación
            producto_verificar_niveles_stock($db, $id_producto, $id_sucursal);
        }

        if ($metodo_pago == 1) {
            $metodo_p = 2;
            $metodo_str = "Tarjeta de Crédito";
        }elseif ($metodo_pago == 2) {
            $metodo_p = 1;
            $metodo_str = "Efectivo";
        }

        //Insertamos el Cobro 
        $db->setQuery("INSERT INTO `cobros` (
                    `id_factura`,
                    `id_sucursal`,
                    `id_metodo_pago`,
                    `id_descuento_metodo_pago`,
                    `id_entidad`,
                    `id_recibo`,
                    `metodo_pago`,
                    `monto`,
                    `detalles`,
                    `fecha`,
                    `usuario`,
                    `estado`
                )
                VALUES
                    (
                    $id_factura,
                    $id_sucursal,
                    $metodo_p,
                    NULL,
                    NULL,
                    NULL,
                    '$metodo_str',
                    $total_venta,
                    '',
                    NOW(),
                    '$usuario',
                    1
                    );");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar los cobros"]);
            $db->rollback();  // Revertimos los cambios
            exit;
        }

        $db->setQuery("UPDATE `ordenes` SET `estado` = 1 WHERE `id_orden` = $id_orden;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la orden"]);
            $db->rollback();  // Revertimos los cambios
            exit;
        }

        $db->commit(); // Se guardan los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Factura guardada con éxito.","id_factura" => $id_factura]);
    break;

    case 'ver_detalle':
        $db = DataBase::conectar();
        $id = $db->clearText($_REQUEST['id']);

        $db->setQuery("SELECT od.id_producto, p.codigo, od.producto, pre.presentacion, od.cantidad, od.precio, od.total_orden AS total
                        FROM ordenes_detalles od
                        JOIN productos p ON p.id_producto=od.id_producto
                        JOIN presentaciones pre ON pre.id_presentacion=p.id_presentacion
                        WHERE od.id_orden=$id");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
    break;

    case 'anular_orden':
        $db = DataBase::conectar();
        $id = $db->clearText($_REQUEST['id']);
        $motivo = mb_convert_case($db->clearText($_POST['motivo']), MB_CASE_UPPER, "UTF-8");

        if (empty($motivo)) {
            echo json_encode(["status" => "error", "mensaje" => "Completar el campo de Motivo."]);
            exit;
        }

        $db->setQuery("SELECT * FROM ordenes WHERE id_orden = $id;");
        $row = $db->loadObject();

        $estado = $row->estado;

        if ($estado == 1 || $estado == 2) {
            echo json_encode(["status" => "error", "mensaje" => "No se puede anular la orden."]);
            exit;
        }

        $db->setQuery("UPDATE `ordenes` SET `estado` = 2, `observacion` = '$motivo' WHERE `id_orden` = $id;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar los cobros"]);
            $db->rollback();  // Revertimos los cambios
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Factura anulada con éxito."]);
    break;

    case 'verificar_caja':
        $db = DataBase::conectar();
        $token_caja = $_COOKIE['token'];
        $caja_abierta = false;

        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración.", "caja_abierta" => $caja_abierta]);
            exit;
        }

        $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.estado=1 AND cu.estado=0 AND c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $id_caja = $db->loadObject()->id_caja;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración.","caja_abierta" => $caja_abierta]);
            exit;
        }

        $caja = caja_abierta($db, $id_sucursal, $usuario);
        $caja_abierta = (isset($caja)) ? true : false;
        $id_caja = $caja->id_caja;
        if (empty($caja)) {
            echo json_encode(["status" => "error", "mensaje" => "La caja se encuentra cerrada", "caja_abierta" => $caja_abierta]);
            exit;
        }

        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND id_caja=$id_caja AND estado=1 AND tipo='0'");
        $tim = $db->loadObject();
        $id_timbrado = $tim->id_timbrado;
            
        if (empty($id_timbrado)) {
            echo json_encode(["status" => "error","mensaje" => "No existen timbrados activos para la Factura. Consulte con administración.", "caja_abierta" => $caja_abierta]);
            exit;
        }

        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND estado=1 AND tipo='0'");
        $tim = $db->loadObject();
        $id_timbrado = $tim->id_timbrado;

        $db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM facturas WHERE id_timbrado= $id_timbrado");
        $r_max = $db->loadObject();    
        $numero_fact = $r_max->ultimo_nro+1;

        $db->setQuery("SELECT IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_timbrado = $id_timbrado");
        $hasta = $db->loadObject();    
        $numero_hasta = $hasta->hasta;

        if ($numero_fact > $numero_hasta) {
            echo json_encode(["status" => "error", "mensaje" => "Ya no tiene numeros disponible para facturar", "caja_abierta" => $caja_abierta]);
            exit;
        }

        // Alerta de números disponibles
        $db->setQuery("SELECT alerta_nro_timbrado FROM configuracion");
        $tim = $db->loadObject();
        $alerta_nro_timbrado = $tim->alerta_nro_timbrado;

        $disponibles = $numero_hasta - ($numero_fact - 1);
        if ($disponibles <= $alerta_nro_timbrado) {
            echo json_encode(["status" => "error", "mensaje" => "Atención, quedan $disponibles facturas disponibles para impresión. Favor comunicar a Administración", "caja_abierta" => $caja_abierta]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Caja sin errores", "caja_abierta" => $caja_abierta]);
    break;

    case "abrir_caja":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $valores_monedas  = $_POST['valor'];
        $cantidad_monedas = $_POST['cantidad'];
        $total_monedas    = $_POST['total'];
        $monto_apertura   = $db->clearText(quitaSeparadorMiles($_POST['total_caja']));

        $token_caja = $_COOKIE['token'];

        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración."]);
            exit;
        }

        $db->setQuery("SELECT id FROM users WHERE username= '$usuario'");
        $id_usuario = $db->loadObject()->id;

        $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.estado=1 AND cu.estado=0 AND c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $id_caja = $db->loadObject()->id_caja;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración."]);
            exit;
        }

        $caja_turno = caja_turno($db, $id_caja);
        if ($caja_turno) {
            echo json_encode(["status" => "error", "mensaje" => "El turno #$caja_turno->id_caja_horario se encuentra abierto. Consulte con Administración"]);
            exit;
        }

        $caja            = caja_abierta($db, $id_sucursal, $usuario);
        $id_caja_horario = $caja->id_caja_horario;
        if ($caja) {
            echo json_encode(["status" => "error", "mensaje" => "Se ha encontrado una caja abierta"]);
            exit;
        }

        $db->setQuery("SELECT estado FROM cajas_usuarios WHERE id_caja = $id_caja AND id_usuario=$id_usuario");
        $estado = $db->loadObject()->estado;

        if ($estado == 1) {
            echo json_encode(["status" => "error", "mensaje" => "No puede abrir una caja porque los montos no coinciden con el total de ventas"]);
            exit;
        }

        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND id_caja=$id_caja AND estado=1 AND tipo='0'");
        $tim = $db->loadObject();
        
        $id_timbrado = $tim->id_timbrado;

        if (empty($id_timbrado)) {
            echo json_encode(["status" => "error", "mensaje" => "No existen timbrados activos para la Factura. Consulte con administración."]);
            //$db->rollback();
            exit;
        }

        $db->setQuery("SELECT id_timbrado FROM timbrados WHERE id_sucursal=$id_sucursal AND estado=1 AND tipo='0'");
        $tim         = $db->loadObject();
        $id_timbrado = $tim->id_timbrado;

        $db->setQuery("SELECT IFNULL(MAX(numero),0) AS ultimo_nro FROM facturas WHERE id_timbrado= $id_timbrado");
        $r_max       = $db->loadObject();
        $numero_fact = $r_max->ultimo_nro + 1;

        $db->setQuery("SELECT IFNULL(hasta,0) AS hasta FROM timbrados WHERE id_timbrado = $id_timbrado");
        $hasta        = $db->loadObject();
        $numero_hasta = $hasta->hasta;

        if ($numero_fact > $numero_hasta) {
            echo json_encode(["status" => "error", "mensaje" => "Ya no tiene numeros disponible para facturar"]);
            exit;
        };

        $db->setQuery("INSERT INTO cajas_horarios (id_caja, fecha_apertura, monto_apertura, usuario, id_sucursal, estado, usuario_apertura) VALUES($id_caja, NOW(),'$monto_apertura','$usuario','$id_sucursal',1, '$usuario')");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar la apertura caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $id_caja_horario = $db->getLastID();
        foreach ($valores_monedas as $key => $value) {
            $valor    = quitaSeparadorMiles($db->clearText($value)) ?: 0;
            $cantidad = quitaSeparadorMiles($db->clearText($cantidad_monedas[$key])) ?: 0;
            $total    = quitaSeparadorMiles($db->clearText($total_monedas[$key])) ?: 0;

            $total_sum += $total;

            $query = "INSERT INTO cajas_detalles(id_caja_horario, cantidad, valor, total, tipo) VALUES($id_caja_horario,$cantidad,$valor,$total,1)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el detalle de la caja"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }
        }

        $db->setQuery("SELECT c.efectivo_inicial FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal=$id_sucursal AND cu.id_usuario=$id_usuario");
        $inicial = $db->loadObject()->efectivo_inicial;

        if ($inicial != $total_sum) {
            echo json_encode(["status" => "error", "mensaje" => "Monto de apertura incorrecto. Favor verificar."]);
            exit;
        }

        //Actualizacion la conexion de la caja
        $db->setQuery("UPDATE cajas SET ultima_conexion=NOW() WHERE id_caja = $id_caja ");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la conexion caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "La apertura de caja se realizo correctamente."]);

    break;

    case "cerrar_caja":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $valores_monedas  = $_POST['valor'];
        $cantidad_monedas = $_POST['cantidad'];
        $total_monedas    = $_POST['total'];
        $total            = $db->clearText(quitaSeparadorMiles($_POST['total_caja']));
        $observacion      = $db->clearText($_POST['observacion']);

        $valores_monedas_sen  = $_POST['valor_sen'];
        $cantidad_monedas_sen = $_POST['cantidad_sen'];
        $total_monedas_sen    = $_POST['total_sen'];
        $total_sen            = $db->clearText(quitaSeparadorMiles($_POST['total_caja_sen']));

        $token_caja = $_COOKIE['token'];

        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La máquina no se encuentra asignada a ninguna caja. Consulte con administración."]);
            exit;
        }

        $caja            = caja_abierta($db, $id_sucursal, $usuario);
        $id_caja_horario = $caja->id_caja_horario;
        if (!$caja) {
            echo json_encode(["status" => "error", "mensaje" => "No se ha encontrado ninguna caja abierta"]);
            exit;
        }

        // Se verifica que las ventas de tipo tarjeta tengan detalles cargados (número de voucher)
        $db->setQuery("SELECT c.id_cobro, c.detalles, c.id_metodo_pago 
                        FROM facturas f
                        JOIN cobros c ON f.id_factura=c.id_factura
                        WHERE f.estado=1 AND c.id_metodo_pago IN (2, 3) AND f.id_caja_horario=$id_caja_horario AND (c.detalles IS NULL OR c.detalles='')");
        $rows = $db->loadObjectList();
        if ($db->error()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al verificar los cobros realizados"]);
            exit;
        }
        
        if (count($rows) > 0) {
            echo json_encode(["status" => "error", "mensaje" => "Número de voucher pendiente de carga en ".count($rows)." venta/s"]);
            exit;
        }

        $db->setQuery("SELECT
                            IFNULL(SUM(c.monto), 0) AS monto
                        FROM cobros c
                        JOIN facturas f ON c.id_factura=f.id_factura
                        WHERE c.estado=1 AND c.id_metodo_pago = 1 AND f.id_caja_horario=$id_caja_horario");
        $total_venta = $db->loadObject()->monto;

        // $db->setQuery("SELECT
        //                     IFNULL(SUM(monto_extraccion), 0) AS monto
        //                 FROM cajas_extracciones
        //                 WHERE id_caja_horario=$id_caja_horario");
        // $extraccion = $db->loadObject()->monto;

        // $db->setQuery("SELECT IFNULL(SUM(nc.total),0) AS total
        //                 FROM cajas_horarios ch 
        //                 LEFT JOIN notas_credito nc ON nc.id_caja_horario=ch.id_caja_horario AND nc.devolucion_importe = 1 
        //                 WHERE ch.id_caja_horario=$id_caja_horario 
        //                 GROUP BY nc.id_caja_horario;");
        // $total_devolucion = $db->loadObject()->total;

        $diferencia = (($total_venta) - $total);

        $db->setQuery("SELECT id FROM users WHERE username= '$usuario'");
        $id_usuario = $db->loadObject()->id;

        $db->setQuery("SELECT c.id_caja FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $id_caja = $db->loadObject()->id_caja;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "No se encuentra asignado a esta caja. Consulte con administración."]);
            exit;
        }

        if ($diferencia < 0) {
            $diferencia_pos = $diferencia * -1;
        } else {
            $diferencia_pos = $diferencia;
        }

        if ($diferencia_pos >= 10000) {
            $db->setQuery("UPDATE cajas_usuarios SET estado=1 WHERE id_caja=$id_caja AND id_usuario=$id_usuario");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }
        }

        foreach ($valores_monedas as $key => $value) {
            $valor    = quitaSeparadorMiles($db->clearText($value)) ?: 0;
            $cantidad = quitaSeparadorMiles($db->clearText($cantidad_monedas[$key])) ?: 0;
            $total    = quitaSeparadorMiles($db->clearText($total_monedas[$key])) ?: 0;

            $query = "INSERT INTO cajas_detalles(id_caja_horario, cantidad, valor, total, tipo) VALUES($id_caja_horario,$cantidad,$valor,$total,0)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el detalle de la caja"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

        }

        // // Se registran los servicios
        // foreach ($servicios as $key => $value) {
        //     $id_servicio = quitaSeparadorMiles($db->clearText($value));
        //     $monto = quitaSeparadorMiles($db->clearText($montos_servicios[$key])) ?: 0;

        //     $query = "INSERT INTO cajas_horarios_servicios(id_caja_horario, id_servicio, monto) VALUES ($id_caja_horario, $id_servicio, $monto)";
        //     $db->setQuery($query);
        //     if (!$db->alter()) {
        //         echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los servicios"]);
        //         $db->rollback(); // Revertimos los cambios
        //         exit;
        //     }
        // }

        // Se registran los montos del sencillo
        foreach ($valores_monedas_sen as $key => $value) {
            $valor    = quitaSeparadorMiles($db->clearText($value)) ?: 0;
            $cantidad = quitaSeparadorMiles($db->clearText($cantidad_monedas_sen[$key])) ?: 0;
            $total    = quitaSeparadorMiles($db->clearText($total_monedas_sen[$key])) ?: 0;

            $total_sencillo += $total;

            $query = "INSERT INTO cajas_detalles(id_caja_horario, cantidad, valor, total, tipo) VALUES($id_caja_horario,$cantidad,$valor,$total,2)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el detalle de la caja"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }

        }

        $db->setQuery("SELECT c.efectivo_inicial FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $efectivo_inicial = $db->loadObject()->efectivo_inicial;

        $diferencia_sencillo = $efectivo_inicial - $total_sencillo;

        if($efectivo_inicial != $total_sencillo){
            $db->setQuery("UPDATE cajas_usuarios SET estado=1 WHERE id_caja=$id_caja AND id_usuario=$id_usuario");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                exit;
            }
        }

        $query = "UPDATE cajas_horarios SET fecha_cierre=NOW(), total_venta='$total_venta', monto_cierre='$total', diferencia='$diferencia_pos', monto_sencillo_cierre=$total_sencillo, diferencia_sencillo=$diferencia_sencillo, observacion='$observacion', estado=0, usuario_cierre='$usuario' WHERE id_caja_horario=$id_caja_horario";
        $db->setQuery($query);
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el cierre de caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        // Actualizacion la conexion de la caja
        $db->setQuery("UPDATE cajas SET ultima_conexion=NOW() WHERE id_caja = $id_caja ");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la conexion caja"]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "Cierre de caja realizado correctamente"]);

    break;
}
