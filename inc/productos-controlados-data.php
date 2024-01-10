<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$datosUsuario = datosUsuario($usuario);
$id_sucursal = $datosUsuario->id_sucursal;
$id_rol = $datosUsuario->id_rol;

switch ($q) {

    case 'ver':
        $db       = DataBase::conectar();
        $sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $desde    = $db->clearText($_REQUEST['desde']) . " 00:00:00";
        $hasta    = $db->clearText($_REQUEST['hasta']) . " 23:59:59";

        $where = "";
        $where_sucursal = "";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', producto) LIKE '%$search%'";
        }

        // if(!empty($sucursal) && intVal($sucursal) != 0) {
        //     $where_sucursal .= " AND f.id_sucursal=$sucursal";
        // }else{
        //     $where_sucursal .="";
        // };

        if (esAdmin($id_rol) === false) {
            $where_sucursal .= " AND f.id_sucursal=$id_sucursal";
        } else if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND f.id_sucursal=$sucursal";
        } else if($sucursal === 'null'){
            $where_sucursal .= "";
        }


        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                f.id_factura,
                                fp.producto,
                                p.id_producto,
                                fp.fraccionado,
                                sum(fp.cantidad) as cantidad,
                                pr.presentacion,
                                fp.precio,
                                p.codigo,
                                sum(fp.total_venta) AS total_venta,
                                DATE_FORMAT(f.fecha, '%d/%m/%Y %H:%i:%s') AS fecha
                            FROM facturas_productos fp
                            LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                            LEFT JOIN presentaciones pr ON p.id_presentacion=pr.id_presentacion
                            WHERE p.controlado=1 AND f.fecha BETWEEN '$desde' AND '$hasta' $where_sucursal
                            $having
                            GROUP BY fp.id_producto
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

    case 'ver_productos':
        $db          = DataBase::conectar();
        $id_producto = $db->clearText($_GET['id_producto']);
        $sucursal = $db->clearText($_GET['id_sucursal']);
        $desde    = $db->clearText($_GET['desde']) . " 00:00:00";
        $hasta    = $db->clearText($_GET['hasta']) . " 23:59:59";

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND f.id_sucursal=$sucursal";
        }else{
            $where_sucursal .="";
        };

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                f.id_factura,
                                DATE_FORMAT(f.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                                CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                                fp.producto,
                                fp.fraccionado,
                                fp.cantidad,
                                fp.precio,
                                fp.total_venta
                            FROM facturas_productos fp
                            LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                            LEFT JOIN timbrados t ON f.id_timbrado = t.id_timbrado
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                            WHERE p.id_producto=$id_producto AND p.controlado=1  AND f.fecha BETWEEN '$desde' AND '$hasta' $where_sucursal");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;
}
