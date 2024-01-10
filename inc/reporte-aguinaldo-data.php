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
        $periodo  = $db->clearText($_REQUEST['periodo']);

       

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING funcionario LIKE '$search%' OR sucursal LIKE '$search%'";
        }

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND f.id_sucursal=$sucursal";
        }else{
            $where_sucursal .="";
        };

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                   f.id_funcionario,
                                   f.funcionario,
                                   f.ci,
                                   f.salario_real,
                                   DATE_FORMAT(f.fecha_alta, '%d/%m/%Y') AS fecha_alta,
                                   (SELECT ROUND(IFNULL(SUM(lsi.importe)/12,0),0) AS total
                                    FROM liquidacion_salarios ls
                                    JOIN liquidacion_salarios_ingresos lsi ON lsi.id_liquidacion = ls.id_liquidacion
                                    WHERE SUBSTR(ls.periodo,-4)='$periodo' AND ls.id_funcionario = f.id_funcionario) AS aguinaldo,                          
                                     CASE f.estado
                                      WHEN 0 THEN 'Inactivo'
                                      WHEN 1 THEN 'Activo'
                                 END AS estado                                                 
                              FROM funcionarios f
                              where 1=1 $where_sucursal
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


      case 'ver_detalle':
        $db          = DataBase::conectar();
        $id = $db->clearText($_GET['id_funcionario']);
        $periodo = $db->clearText($_GET['periodo']);

        $db->setQuery("SELECT 
                            UPPER(ca.mes) AS mes,
                            ca.ano,
                            (
                            SELECT ROUND(IFNULL(SUM(lsi.importe), 0),0) AS total
                            FROM liquidacion_salarios ls
                            JOIN liquidacion_salarios_ingresos lsi ON lsi.id_liquidacion = ls.id_liquidacion
                            WHERE CONCAT(UPPER(ca.mes), ' - ', ca.ano) = ls.periodo AND ls.id_funcionario = $id
                            ) AS total
                         FROM calendario ca
                         WHERE ano = $periodo
                         GROUP BY mes_nro");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;



        case 'recuperar_calendario':
        $db = DataBase::conectar();
        $db->setQuery("SELECT MAX(ano) AS maximo_anho, MIN(ano) AS minimo_anho, COUNT(*) AS fechas FROM calendario WHERE YEAR(NOW()) >= ano ");
        $row_calendario = $db->loadObject();
        
        echo json_encode(["status" => "ok", "calendario" => $row_calendario]);
    break;

    
}
