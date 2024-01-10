<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST["q"];
$usuario     = $auth->getUsername();
$id_sucursal = datosUsuario($usuario)->id_sucursal;

switch ($q) {

    case "ver":
        $db              = DataBase::conectar();
        $id_caja_horario = intval($db->clearText($_GET['id_caja']));

        $db->setQuery("SELECT
                                f.id_factura,
                                CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                                DATE_FORMAT(f.fecha_venta,'%d/%m/%Y %H:%i:%s') AS fecha_venta,
                                CASE f.condicion WHEN 1 THEN 'Contado' WHEN 2 THEN 'Crédito' END AS condicion,
                                f.ruc,
                                f.razon_social,
                                f.cantidad,
                                f.descuento,
                                f.total_venta,
                                IFNULL((SELECT SUM(monto) FROM cobros WHERE id_factura=f.id_factura AND f.estado IN(0, 1)), 0) AS total_pagado,
                                CASE f.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Anulado' END AS estado,
                                f.fecha,
                                f.usuario
                            FROM facturas f
                            LEFT JOIN timbrados t ON f.id_timbrado=t.id_timbrado
                            WHERE f.id_caja_horario=$id_caja_horario
                            ORDER BY f.fecha DESC");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
        break;

    case "ver_resumen":
        $db              = DataBase::conectar();
        $id_caja_horario = intval($db->clearText($_GET['id_caja']));

        $db->setQuery("SELECT
                                mp.id_metodo_pago,
                                mp.metodo_pago,
                                (
                                    
                                    SELECT IFNULL(SUM(c.monto), 0)
                                    FROM cobros c
                                    JOIN facturas f ON c.id_factura=f.id_factura
                                    WHERE c.id_metodo_pago=mp.id_metodo_pago AND c.estado=1 AND f.id_caja_horario=$id_caja_horario
                                ) * IF(mp.id_metodo_pago = 7 , -1, 1) AS monto
                                FROM metodos_pagos mp
                                ORDER BY mp.metodo_pago");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
        break;

    case "ver_arqueo_cierre":
        $db              = DataBase::conectar();
        $id_caja_horario = intval($db->clearText($_GET['id_caja']));

        $db->setQuery("SELECT
                                cantidad,
                                valor,
                                total
                            FROM cajas_detalles
                            WHERE tipo=0 AND id_caja_horario=$id_caja_horario ORDER BY valor DESC");
        $rows = $db->loadObjectList();

        // Para mostrar las monedas en la tabla si es que no cerro la caja
        if (!$rows) {
            $rows = [
                ["valor" => "100.000"],
                ["valor" => "50.000"],
                ["valor" => "20.000"],
                ["valor" => "10.000"],
                ["valor" => "5.000"],
                ["valor" => "2.000"],
                ["valor" => "1.000"],
                ["valor" => "500"],
                ["valor" => "100"],
                ["valor" => "50"],
            ];
        }
        echo json_encode($rows);
        break;

    case "ver_resumen_servicios":
        $db              = DataBase::conectar();
        $id_caja_horario = intval($db->clearText($_GET['id_caja']));

        $db->setQuery("SELECT s.id_servicio, s.nombre AS servicio, chs.monto
                        FROM cajas_horarios_servicios chs
                        JOIN servicios s ON chs.id_servicio=s.id_servicio
                        WHERE chs.id_caja_horario=$id_caja_horario");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
    break;

    case 'ver_cajeros':
        $db = DataBase::conectar();
        $id_caja = $db->clearText($_REQUEST['id_caja']);

        $page = $db->clearText($_GET['page']);
        $term = $db->clearText($_GET['term']);
        $resultCount = 5;
        $end = ($page - 1) * $resultCount; 

        $db->setQuery("SELECT 
                                id_usuario,
                                usuario,
                                estado,
                                CASE estado WHEN 0 THEN 'Habilitado' WHEN 1 THEN 'Deshabilitado' END AS estado_str
                            FROM cajas_usuarios
                            WHERE id_caja='$id_caja' AND usuario LIKE '%$term%'
                            ORDER BY usuario
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

    case "ver_vouchers":
        $db              = DataBase::conectar();
        $id_caja_horario = intval($db->clearText($_GET['id_caja']));
        $metodo_pago = intval($db->clearText($_GET['metodo_pago']));

        if (!empty($metodo_pago) || intVal($metodo_pago) != 0) {
            $where_metodo_pago .= "AND c.id_metodo_pago = $metodo_pago";
        }else{
            $where_metodo_pago .= "AND c.id_metodo_pago IN (2,3)";
        }

        $db->setQuery("SELECT
                            c.id_cobro,
                            c.`id_metodo_pago`,
                            c.`id_entidad`,
                            c.`metodo_pago`,
                            c.monto,
                            c.`detalles`,
                            DATE_FORMAT(c.`fecha`, '%d/%m/%Y %h:%I') AS fecha,
                            CONCAT(e.`tipo`,' - ', e.`entidad`) AS entidad,
                            c.usuario,
                            CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero
                        FROM
                            cobros c
                        LEFT JOIN entidades e ON e.`id_entidad` = c.`id_entidad`
                        LEFT JOIN facturas f ON f.`id_factura` = c.`id_factura`
                        LEFT JOIN timbrados t ON t.`id_timbrado` = f.`id_timbrado`
                        WHERE f.`id_caja_horario` = $id_caja_horario $where_metodo_pago;");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
    break;

    case 'ver_cajeros':
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

    case "cargar":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $id_caja = $db->clearText($_POST['id_caja']);
        $id_sucursal = $db->clearText($_POST['id_sucursal']);
        $id_usuario = $db->clearText($_POST['cajero']);
        $valores_monedas  = $_POST['valor'];
        $cantidad_monedas = $_POST['cantidad'];
        $total_monedas    = $_POST['total'];
        $monto_apertura   = $db->clearText(quitaSeparadorMiles($_POST['total_caja']));

        if (empty($id_usuario)) {
            echo json_encode(["status" => "error","mensaje" => "Favor seleccione un usuario"]);
            exit;
        }

        // Verificamos si la caja se encuentra asociada a una maquina
        $db->setQuery("SELECT token FROM cajas WHERE id_caja=$id_caja");
        $token_caja = $db->loadObject()->token;

        if ($db->error()) {
            echo json_encode(["status" => "error","mensaje" => "Error de base de datos al recuperar la información de la caja"]);
            exit;
        }

        $caja_ = caja_identificacion($db, $token_caja);
        if (empty($caja_)) {
            echo json_encode(["status" => "error","mensaje" => "La caja no se encuentra asociada a ninguna máquina. Consulte con administración."]);
            exit;
        }

        $db->setQuery("SELECT c.id_caja, cu.usuario FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.estado=1 AND cu.estado=0 AND c.id_sucursal= $id_sucursal AND cu.id_usuario=$id_usuario");
        $row = $db->loadObject();
        $id_caja = $row->id_caja;
        $usuario_ = $row->usuario;

        if($id_caja != $caja_->id_caja){
            echo json_encode(["status" => "error","mensaje" => "El usuario seleccionado no se encuentra asignado a esta caja"]);
            exit;
        }

        $caja_turno = caja_turno($db, $id_caja);
        if ($caja_turno) {
            echo json_encode(["status" => "error", "mensaje" => "El turno #$caja_turno->id_caja_horario se encuentra abierto"]);
            exit;
        }

        $caja            = caja_abierta($db, $id_sucursal, $usuario_);
        $id_caja_horario = $caja->id_caja_horario;
        if ($caja) {
            echo json_encode(["status" => "error", "mensaje" => "Se ha encontrado una caja abierta"]);
            exit;
        }
        $db->setQuery("SELECT estado FROM cajas_usuarios WHERE id_caja = $id_caja AND id_usuario=$id_usuario");
        $estado = $db->loadObject()->estado;

        if ($estado == 1) {
            $db->setQuery("UPDATE cajas_usuarios SET estado=0 WHERE id_caja=$id_caja AND id_usuario=$id_usuario");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar el estado del usuario"]);
                exit;
            }
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
            echo json_encode(["status" => "error", "mensaje" => "Ya no tiene números disponible para facturar"]);
            exit;
        };

        $db->setQuery("INSERT INTO cajas_horarios (id_caja, fecha_apertura, monto_apertura, usuario, id_sucursal, estado, usuario_apertura) VALUES($id_caja, NOW(),'$monto_apertura','$usuario_','$id_sucursal',1, '$usuario')");
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

        $db->setQuery("SELECT coalesce(c.efectivo_inicial,0) as inicial FROM cajas c LEFT JOIN cajas_usuarios cu ON c.id_caja=cu.id_caja WHERE c.id_sucursal=$id_sucursal AND cu.id_usuario=$id_usuario");
        $inicial = $db->loadObject()->inicial;

        if ($inicial != $total_sum) {
            echo json_encode(["status" => "error", "mensaje" => "Monto de apertura incorrecto. Favor revisar."]);
            exit;
        };

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "La apertura de caja se realizo correctamente."]);

        break;

    case "cerrar":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $id_caja_horario = $db->clearText($_POST['id']);
        $valores_monedas  = $_POST['valor'];
        $cantidad_monedas = $_POST['cantidad'];
        $total_monedas    = $_POST['total'];
        $total            = $db->clearText(quitaSeparadorMiles($_POST['total_caja']));
        $servicios    = $_POST['servicios'];
        $montos_servicios    = $_POST['montos_servicios'];
        $total_servicios    = $db->clearText(quitaSeparadorMiles($_POST['total_servicios']));
        $observacion      = $db->clearText($_POST['observacion']);

        $valores_monedas_sen  = $_POST['valor_sen'];
        $cantidad_monedas_sen = $_POST['cantidad_sen'];
        $total_monedas_sen    = $_POST['total_sen'];
        $total_sen            = $db->clearText(quitaSeparadorMiles($_POST['total_caja_sen']));

        // Se verifica el estado de la caja
        $db->setQuery("SELECT estado
                        FROM cajas_horarios
                        WHERE id_caja_horario=$id_caja_horario");
        $estado = $db->loadObject()->estado;
        if ($estado == 0) {
            echo json_encode(["status" => "error", "mensaje" => "La caja se encuentra cerrada"]);
            exit;
        }
        if (empty($estado)) {
            echo json_encode(["status" => "error", "mensaje" => "Caja no encontrada"]);
            exit;
        }

        $db->setQuery("SELECT
                            IFNULL(SUM(c.monto), 0) AS monto
                        FROM cobros c
                        JOIN facturas f ON c.id_factura=f.id_factura
                        WHERE c.estado=1 AND c.id_metodo_pago = 1 AND f.id_caja_horario=$id_caja_horario");
        //!todas las ventas de la caja                
        $total_venta = $db->loadObject()->monto;

        $db->setQuery("SELECT
                            IFNULL(SUM(monto_extraccion), 0) AS monto
                        FROM cajas_extracciones
                        WHERE id_caja_horario=$id_caja_horario");
        //!extracion de caja                
        $extraccion = $db->loadObject()->monto;

        $db->setQuery("SELECT IFNULL(SUM(nc.total),0) AS total
                        FROM cajas_horarios ch 
                        LEFT JOIN notas_credito nc ON nc.id_caja_horario=ch.id_caja_horario AND nc.devolucion_importe = 1 
                        WHERE ch.id_caja_horario=$id_caja_horario 
                        GROUP BY nc.id_caja_horario;");
        //!nota de credito    
        $total_devolucion = $db->loadObject()->total;

        
        //!calculo para obtener el el valor neto
        // $diferencia = (($total_venta - $extraccion) - $total) - $total_devolucion;
        $diferencia = (($total_venta - $total_devolucion) - $extraccion) - $total;

        $db->setQuery("SELECT u.id, ch.id_caja FROM users u JOIN cajas_horarios ch ON u.username=ch.usuario WHERE id_caja_horario=$id_caja_horario");
        $row = $db->loadObject();
        $id_usuario = $row->id;
        $id_caja = $row->id_caja;

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

        // Se registran los servicios
        foreach ($servicios as $key => $value) {
            $id_servicio = quitaSeparadorMiles($db->clearText($value));
            $monto = quitaSeparadorMiles($db->clearText($montos_servicios[$key])) ?: 0;

            $query = "INSERT INTO cajas_horarios_servicios(id_caja_horario, id_servicio, monto) VALUES ($id_caja_horario, $id_servicio, $monto)";
            $db->setQuery($query);
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar los servicios"]);
                $db->rollback(); // Revertimos los cambios
                exit;
            }
        }

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

        $query = "UPDATE cajas_horarios SET fecha_cierre=NOW(), total_venta='$total_venta', monto_cierre='$total', diferencia='$diferencia_pos', monto_servicios=$total_servicios, monto_sencillo_cierre=$total_sencillo, diferencia_sencillo=$diferencia_sencillo, devolucion_importe=$total_devolucion, observacion='$observacion', estado=0, usuario_cierre='$usuario' WHERE id_caja_horario=$id_caja_horario";
        $db->setQuery($query);
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el cierre de caja"]);
            $db->rollback(); //Revertimos los cambios
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
        echo json_encode(["status" => "ok", "mensaje" => "Cierre de caja realizado correctamente"]);

        break;

    case "cajas":
        $db          = DataBase::conectar();
        $page        = $db->clearText($_GET['page']);
        $term        = $db->clearText($_GET['term']);
        $resultCount = 5;
        $end         = ($page - 1) * $resultCount;

        $desde       = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta       = $db->clearText($_REQUEST['hasta']) . " 23:59:59";
        $usuario     = $db->clearText($_REQUEST['usuario']);
        $id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $id_caja = $db->clearText($_REQUEST['id_caja']);
        $fecha       = "";

        // Si las fechas son distintas se agrega la fecha de apertura para poder diferenciar las cajas por dia
        if (strtotime($_REQUEST['desde']) != strtotime($_REQUEST['hasta'])) {
            $fecha = "DATE_FORMAT(fecha_apertura, '%d/%m/%Y'), ' ',";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                id_caja_horario,
                                id_caja,
                                CONCAT($fecha DATE_FORMAT(fecha_apertura, '%H:%i'), ' - ', IF(fecha_cierre IS NULL, 'Abierta', DATE_FORMAT(fecha_cierre, '%H:%i'))) AS caja,
                                estado
                            FROM cajas_horarios
                            WHERE id_caja=$id_caja AND id_sucursal=$id_sucursal AND fecha_apertura BETWEEN '$desde' AND '$hasta' AND CONCAT_WS(' ', id_caja_horario, fecha_apertura, fecha_cierre) LIKE '%$term%'
                            ORDER BY id_caja_horario DESC
                            LIMIT $end, $resultCount");
        $rows = ($db->loadObjectList()) ?: [];

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row   = $db->loadObject();
        $total_count = $total_row->total;

        if (empty($rows)) {
            $salida = ['data' => [], 'total_count' => 0];
        } else {
            $salida = ['data' => $rows, 'total_count' => $total_count];
        }

        echo json_encode($salida);
        break;

    case "cambiar-estado":
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST["id"]);
        $estado = $db->clearText($_POST["estado"]);

        if ($estado != 0 && $estado != 1) {
            echo json_encode(["status" => "error", "mensaje" => "Estado no válido"]);
            exit;
        }

        $db->setQuery("UPDATE metodos_pagos SET estado=$estado WHERE id_metodo_pago=$id");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case "ver_diferencia":
        $db              = DataBase::conectar();
        $id_caja_horario = $db->clearText($_GET['id_caja']);

        $db->setQuery("
                        SELECT
                            'Sencillo Apertura' AS titulo,
                            IFNULL(SUM(total), 0) AS monto
                        FROM cajas_detalles
                        WHERE tipo=1 AND id_caja_horario=$id_caja_horario

                        UNION ALL

                        SELECT
                            'Sencillo Cierre' AS titulo,
                            IFNULL(SUM(total), 0) AS monto
                        FROM cajas_detalles
                        WHERE tipo=2 AND id_caja_horario=$id_caja_horario

                        UNION ALL

                        SELECT
                            'Total venta' AS titulo,
                            (IFNULL(SUM(c.monto),0) - (SELECT 
                                                            IFNULL(SUM(nc.total), 0) 
                                                        FROM notas_credito nc 
                                                        WHERE nc.id_caja_horario = $id_caja_horario
                                                        AND nc.devolucion_importe = 1)) AS monto
                        FROM cobros c
                        JOIN facturas f ON c.id_factura = f.id_factura
                        WHERE c.estado = 1 AND f.id_caja_horario = $id_caja_horario

                        UNION ALL

                        SELECT
                            'Extracción' AS titulo,
                            IFNULL(SUM(monto_extraccion), 0) AS monto
                        FROM cajas_extracciones
                        WHERE id_caja_horario=$id_caja_horario

                        UNION ALL

                        -- Se realiza la suma porque el cajero solo debe cargar el efectivo, pero el sistema debe tener en cuenta los demás métodos de pago para la diferencia, este número es solo visual para el usuario.
                        SELECT
                            'Arqueo Caja' AS titulo,
                            IFNULL(SUM(total), 0) + 
                            (
                                SELECT IFNULL(SUM(c.monto), 0) AS monto
                                FROM cobros c
                                JOIN facturas f ON c.id_factura=f.id_factura
                                WHERE c.estado=1 AND c.id_metodo_pago != 1 AND f.id_caja_horario=cd.id_caja_horario
                            )
                            AS monto
                        FROM cajas_detalles cd
                        WHERE tipo=0 AND id_caja_horario=$id_caja_horario

                        UNION ALL

                        SELECT
                            'Servicios' AS titulo,
                            IFNULL(SUM(monto), 0) AS monto
                        FROM cajas_horarios_servicios
                        WHERE id_caja_horario=$id_caja_horario

                        UNION ALL

                        SELECT
                            'Devolución Importe' AS titulo,
                            IFNULL(SUM(-total), 0) AS monto
                        FROM notas_credito
                        WHERE id_caja_horario=$id_caja_horario AND devolucion_importe = 1

                        UNION ALL


                        SELECT
                        'Diferencia' AS titulo,
                        -- IF(cd.monto_ca IS NULL,0,((cd.monto_ca - (IFNULL(SUM(c.monto), 0)-IFNULL(ex.extra,0)))-ch.total_dev)) AS monto -- Cálculo anterior
                        IF(cd.monto_ca IS NULL,0,((SUM(c.monto)- ch.total_dev)-(IFNULL((SELECT IFNULL(SUM(total), 0) + 
                            (
                                SELECT IFNULL(SUM(c.monto), 0) AS monto
                                FROM cobros c
                                JOIN facturas f ON c.id_factura=f.id_factura
                                WHERE c.estado=1 AND c.id_metodo_pago != 1 AND f.id_caja_horario=cd.id_caja_horario
                            ) AS monto
                            FROM cajas_detalles cd
                            WHERE tipo=0 AND id_caja_horario=$id_caja_horario), 0) + IFNULL(ex.extra,0)))) AS monto
                        FROM cobros c
                        JOIN facturas f ON c.id_factura=f.id_factura
                        JOIN (SELECT IFNULL(SUM(total), 0) AS monto_ca, id_caja_horario FROM cajas_detalles WHERE tipo=0 GROUP BY id_caja_horario ) cd
                        ON f.`id_caja_horario`=cd.id_caja_horario
                        LEFT JOIN (SELECT IFNULL(SUM(monto_extraccion), 0) AS extra, id_caja_horario FROM cajas_extracciones GROUP BY id_caja_horario) ex
                        ON f.`id_caja_horario`=ex.id_caja_horario
                        JOIN (SELECT IFNULL(devolucion_importe,0) AS total_dev, id_caja_horario, total_venta FROM cajas_horarios GROUP BY id_caja_horario ) ch
                        ON f.`id_caja_horario`=ch.id_caja_horario
                        WHERE c.estado=1 AND f.id_caja_horario=$id_caja_horario
                        GROUP BY f.id_caja_horario
        ");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
        break;

    case "extraer":
        $db = DataBase::conectar();
        $db->autocommit(false);

        $observacion      = $_POST['observacion'];
        $monto_extraccion = $db->clearText(quitaSeparadorMiles($_POST['monto_extraccion']));
        $id_caja_horario  = $_POST['id_caja'];

        $db->setQuery("SELECT IFNULL(SUM(c.monto), 0) AS monto FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura WHERE c.estado=1 AND f.id_caja_horario=$id_caja_horario");
        $total_venta = $db->loadObject()->monto;

        $db->setQuery("SELECT IFNULL(SUM(total), 0) AS monto FROM cajas_detalles WHERE tipo=1 AND id_caja_horario=$id_caja_horario");
        $total_apertura = $db->loadObject()->monto;

        $total_caja = $total_venta + $total_apertura;

        $db->setQuery("SELECT IFNULL(SUM(c.monto), 0) AS monto FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura WHERE c.estado=1 AND c.id_metodo_pago=1 AND f.id_caja_horario=$id_caja_horario");
        $total_venta_efectivo = $db->loadObject()->monto;

        if ($total_venta_efectivo < $monto_extraccion) {
            echo json_encode(["status" => "error", "mensaje" => "Monto a extraer es mayor al monto de la caja."]);
            exit;
        }

        $db->setQuery("SELECT sum(monto_extraccion) as total FROM cajas_extracciones WHERE id_caja_horario=$id_caja_horario");
        $total_extra = $db->loadObject()->total;

        $totales_extra = $total_extra + $monto_extraccion;

        if ($totales_extra >= $total_venta_efectivo) {
            echo json_encode(["status" => "error", "mensaje" => "Los montos extraídos ya superan al total de efectivo en caja."]);
            exit;
        }

        $db->setQuery("SELECT estado FROM cajas_horarios WHERE id_caja_horario=$id_caja_horario");
        $estado = $db->loadObject()->estado;

        if ($estado == 0) {
            echo json_encode(["status" => "error", "mensaje" => "La caja ya se encuentra cerrada. No es posible realizar la extracción."]);
            exit;
        }

        $total_caja_efectivo = $total_venta_efectivo + $total_apertura;

        $monto_sin_extraer = $total_caja_efectivo - $monto_extraccion;

        $db->setQuery("INSERT INTO cajas_extracciones (id_caja_horario, usuario, monto_extraccion, total_venta, total_caja, total_venta_efectivo, total_caja_efectivo, monto_sin_extraer, fecha, observacion) VALUES($id_caja_horario, '$usuario','$monto_extraccion','$total_venta','$total_caja','$total_venta_efectivo','$total_caja_efectivo','$monto_sin_extraer',NOW(),'$observacion')");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Ha ocurrido un error al cargar la extracción."]);
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $id_extraccion = $db->getLastID();

        $db->commit(); // Guardamos los cambios
        echo json_encode(["status" => "ok", "mensaje" => "La extracción se ha realizado correctamente.", "id_extraccion" => $id_extraccion]);

        break;

    case "ver_servicios":
        $db = DataBase::conectar();
        $db->setQuery("SELECT id_servicio, nombre AS servicio
                        FROM servicios
                        WHERE estado=1");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
    break;

}
