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
        $db       = DataBase::conectar();
        $sucursal = $db->clearText($_REQUEST['id_sucursal']);
        $periodo  = $db->clearText($_GET['periodo']);

        $where = "";
        $where_sucursal = "";

        if (!empty($periodo && $periodo != 'null')) {
            $where .= "AND ls.periodo='$periodo'";
        }

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING f.funcionario LIKE '$search%' OR f.ci LIKE '$search%'";
        }

        if (!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND s.id_sucursal=$sucursal";
        }

        $db->setQuery("SELECT 
                            f.id_funcionario,
                            f.ci,
                            f.funcionario,
                            DATE_FORMAT(f.fecha_baja,'%d/%m/%Y') AS fecha_baja,
                            DATE_FORMAT(f.fecha_alta,'%d/%m/%Y') AS fecha_alta,
                            MAX(ls.id_liquidacion) as id_liquidacion,
                            ls.periodo,
                            ls.neto_cobrar
                        FROM funcionarios f
                        LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                        LEFT JOIN (select * from liquidacion_salarios where estado = 4) ls ON f.id_funcionario=ls.id_funcionario
                        WHERE f.fecha_baja != '0000-00-00' $where $where_sucursal
                        GROUP BY f.id_funcionario
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

    case 'ver_liquidacion_ingreso':
        $db             = DataBase::conectar();
        $id_liquidacion = $db->clearText($_GET['id_liquidacion']);

        $db->setQuery("SELECT
                                id_liquidacion_ingreso,
                                id_liquidacion,
                                concepto,
                                importe,
                                observacion
                            FROM liquidacion_salarios_ingresos
                            WHERE id_liquidacion=$id_liquidacion");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;

    case 'ver_liquidacion_descuento':
        $db             = DataBase::conectar();
        $id_liquidacion = $db->clearText($_GET['id_liquidacion']);

        $db->setQuery("SELECT
                                id_liquidacion_descuento,
                                id_liquidacion,
                                concepto,
                                importe,
                                observacion
                            FROM liquidacion_salarios_descuentos
                            WHERE id_liquidacion=$id_liquidacion");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;
}
