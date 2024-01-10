<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$id_sucursal = datosUsuario($usuario)->id_sucursal;

switch ($q) {

    case 'ver':
        $db = DataBase::conectar();

        $proveedor = $db->clearText($_REQUEST['proveedor']);

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', nombre, estado_str) LIKE '%$search%'";
        }

        if (!empty($proveedor && intVal($proveedor) != 0)) {
            $where_proveedor .= " AND p.ruc='$proveedor'";
        } else {
            $where_proveedor .= "";
        }

        $db->setQuery("SELECT
                            r.id_proveedor,
                            p.proveedor,
                            p.ruc,
                            IFNULL(SUM(op.pagado_hasta_ahora),0) AS pagado,
                            (SELECT SUM(total_costo) FROM `recepciones_compras` WHERE id_proveedor=p.id_proveedor
                            GROUP BY id_proveedor) AS total,
                            IFNULL((SELECT SUM(total_costo) FROM `recepciones_compras` WHERE id_proveedor=p.id_proveedor
                            GROUP BY id_proveedor)-IFNULL(SUM(op.pagado_hasta_ahora),0),0) AS saldo
                            FROM proveedores p
                            JOIN recepciones_compras r ON r.id_proveedor = p.id_proveedor
                            LEFT JOIN ( SELECT ops.*,SUM(ops.monto) AS pagado_hasta_ahora FROM `orden_pagos_proveedores` ops JOIN orden_pagos op ON ops.id_pago=op.id_pago
                            GROUP BY ops.id_factura, id_recepcion_compra_vencimiento) op ON op.id_factura = r.id_recepcion_compra
                            LEFT JOIN orden_pagos o ON o.id_pago = op.id_pago
                            WHERE 1=1 $where_proveedor
                            GROUP BY r.id_proveedor");
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

    case 'ver_detalles':
        $db           = DataBase::conectar();
        $id_proveedor = $db->clearText($_GET["id_proveedor"]);
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', numero_documento, condicion_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT a.*,
                            CASE 
                                WHEN pagado IS NULL THEN 'Pendiente'
                                WHEN total_costo = pagado THEN 'Pagado'
                                WHEN total_costo > pagado THEN 'Pagado Parcial'
                            END AS estado_str

                         FROM (SELECT 
                                    rc.id_recepcion_compra,
                                    rc.id_proveedor,
                                    rc.numero_documento,
                                    CASE rc.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion_str,
                                    p.proveedor,
                                    rc.total_costo,
                                    pagado_hasta_ahora AS pagado,
                                    ifnull(rc.total_costo,0) - ifnull(pagado_hasta_ahora,0) AS saldo,
                                    DATE_FORMAT(rcv.vencimiento, '%d/%m/%Y') AS vencimiento
                                FROM recepciones_compras rc
                                LEFT JOIN proveedores p ON p.id_proveedor = rc.id_proveedor
                                LEFT JOIN recepciones_compras_vencimientos rcv ON rcv.id_recepcion_compra= rc.id_recepcion_compra
                                LEFT JOIN (SELECT ops.*,SUM(ops.monto) AS pagado_hasta_ahora FROM `orden_pagos_proveedores` ops JOIN orden_pagos op ON ops.id_pago=op.id_pago GROUP BY id_factura) opp ON opp.id_factura = rc.id_recepcion_compra
                                WHERE rc.id_proveedor=$id_proveedor
                                GROUP BY rc.id_recepcion_compra
                            )a
                        ");
        $row = ($db->loadObjectList()) ?: [];
        echo json_encode($row);
        break;

}
