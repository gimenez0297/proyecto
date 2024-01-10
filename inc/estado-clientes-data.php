<?php
include("funciones.php");
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

        $sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $desde = $db->clearText($_REQUEST['desde']);
        $hasta = $db->clearText($_REQUEST['hasta']);
        $estado = $db->clearText($_REQUEST['estado']);
        $cliente = $db->clearText($_REQUEST['cliente']);


        //Parametros de ordenamiento, busqueda y paginacion
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset    = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        if ($estado == '1') {
            $having .= ' HAVING estado_str = "Pagado"';
        } elseif ($estado == '2') {
            $having .= ' HAVING estado_str = "Pendiente"';
        } elseif ($estado == '3') {
            $having .= '';
        }

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND f.id_sucursal=$sucursal";
        }else{
            $where_sucursal .="";
        };

        if (!empty($cliente) && intVal($cliente) != 0) {
            $where_cliente .= " AND f.id_cliente = $cliente";
        } else {
            $where_cliente .= "";
        }



        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                f.id_factura, 
                                f.id_timbrado,
                                f.id_cliente, 
                                f.numero, 
                                DATE_FORMAT(f.fecha_venta, '%d-%m-%Y %H:%i') AS fecha_venta,
                                f.vencimiento,
                                f.ruc,
                                f.razon_social,
                                f.total_venta AS monto,
                                f.usuario,
                                s.sucursal,
                                IFNULL((SELECT SUM(monto) FROM cobros WHERE id_factura = f.id_factura AND estado = 1),0) AS pagado,
                                f.saldo AS saldo,
                                CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',f.numero) AS nro_documento,
                                CASE f.estado WHEN 1 THEN 'Pagado' WHEN 0 THEN 'Pendiente' END AS estado_str
                                FROM facturas f
                                LEFT JOIN timbrados t ON t.id_timbrado = f.id_timbrado
                                LEFT JOIN sucursales s ON s.id_sucursal = f.id_sucursal
                                WHERE f.condicion = 2 AND DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' $where_sucursal $where_cliente
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
        $db           = DataBase::conectar();
        $id_cliente = $db->clearText($_POST['id']);
        $id_metodo_pago = $db->clearText($_POST['metodo']);
        $monto_pagar = quitaSeparadorMiles($db->clearText($_POST['monto_pagar']));
        $detalles = $db->clearText($_POST['detalles']);
        $fecha_pago = $db->clearText($_POST['fecha_pago']);
        $creditos_pagar = json_decode($_POST['creditos_pagados']);

        if (empty($id_metodo_pago)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un metodo de pago"]);
            exit;
        }

        if (empty($monto_pagar)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione ingrese un monto a pagar"]);
            exit;
        }

        if (empty($fecha_pago)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor seleccione ingrese una fecha de pago"]);
            exit;
        }

        //Para saber el metodo de pago 
        $db->setQuery("SELECT * FROM metodos_pagos WHERE id_metodo_pago = $id_metodo_pago");
        $metodo = $db->loadObject();
        $metodo_pago = $metodo->metodo_pago;

        //Para generar el numero de recibo
        $nro_recibo =  1;
        $db->setQuery("SELECT*FROM recibos ORDER BY id_recibo DESC LIMIT 1");
        $nro = $db->loadObjectList();
        if(!empty($nro)){
            foreach($nro as $nro){
                $nro_recibo =  intval($nro->numero);
            }	
            $nro_recibo = zerofill(($nro_recibo*1)+1);
        }

        //Generamos el recibo 
        $db->setQuery("INSERT INTO recibos (id_cliente,id_metodo_pago,numero,detalle_pago,total_pago,concepto,fecha_pago,usuario)
                        VALUES($id_cliente,$id_metodo_pago,$nro_recibo,'$detalles',$monto_pagar,'Pago de Factura/s','$fecha_pago','$usuario')");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error al generar recibo del pago." . $db->geterror()]);
            $db->rollback();
            exit;
        }

        $id_recibo = $db->getLastID();
        
        $facturas = [];

        foreach ($creditos_pagar as $c) {
            if ($monto_pagar > 0) {
                $id_factura = $db->clearText($c->id_factura);
                $db->setQuery("SELECT * FROM facturas WHERE id_factura = $id_factura");
                $factura = $db->loadObject();
                $saldo = $factura->saldo;
                $nro_factura = $factura->numero;
                
                if ($monto_pagar >= $saldo) {
                    //Si el monto ingresado es mayor al saldo de la factura se inserta el saldo como monto 
                    $monto = $saldo;
                    $db->setQuery("UPDATE facturas SET saldo = 0, estado = 1 WHERE id_factura = $id_factura");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error." . $db->geterror()]);
                        $db->rollback();
                        exit;
                    }
                }else{
                    //Si es menor al saldo se inserta el total del monto ingresado
                    $monto = $monto_pagar;
                    $saldo_factura = $saldo - $monto_pagar;
                    $db->setQuery("UPDATE facturas SET saldo = $saldo_factura WHERE id_factura = $id_factura");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error." . $db->geterror()]);
                        $db->rollback();
                        exit;
                    }
                }

                if ($factura) {
                    $db->setQuery("INSERT INTO cobros (id_factura,id_sucursal,id_metodo_pago,id_recibo,metodo_pago,monto,detalles,fecha,usuario)
                            VALUES($id_factura,$id_sucursal,$id_metodo_pago,$id_recibo,'$metodo_pago',$monto,'$detalles','$fecha_pago','$usuario');");
                    if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->geterror()]);
                        $db->rollback();
                        exit;
                    }
                }
                $monto_pagar =  $monto_pagar - $monto;
                array_push($facturas,$nro_factura);
            } 
        }

        $nro_facturas = implode(",",$facturas);
        $db->setQuery("UPDATE recibos SET concepto = 'Pago de Factura/s $nro_facturas' WHERE id_recibo = $id_recibo");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error." . $db->geterror()]);
            $db->rollback();
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Pago realizado correctamente", "id_recibo" => $id_recibo]);

    break;

    case 'ver_recibos':
        $db = DataBase::conectar();
        $id_factura = $db->clearText($_GET["id_factura"]);
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', numero_documento, condicion_str) LIKE '%$search%'";
        }

            $db->setQuery("SELECT c.*,
                            s.sucursal,
                            DATE_FORMAT(c.fecha, '%d-%m-%Y') as fecha
                            FROM cobros c
                            LEFT JOIN sucursales s ON s.id_sucursal = c.id_sucursal
                            WHERE c.id_factura = $id_factura
                        ");
            $row = ($db->loadObjectList()) ?: [];
            echo json_encode($row);
        break;

}
