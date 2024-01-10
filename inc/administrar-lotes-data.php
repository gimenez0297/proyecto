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
        $desde 		        = $_REQUEST["desde"];
        $hasta 		        = $_REQUEST["hasta"];
        $id_producto        = $_REQUEST["producto"];
        $id_laboratorio     = $_REQUEST["laboratorio"];
        $id_marca           = $_REQUEST["marca"];
        $id_unidad_medida   = $_REQUEST["unidad_medida"];
        $id_presentacion    = $_REQUEST["presentacion"];
        $id_clasificacion   = $_REQUEST["clasificacion"];
        $id_origen          = $_REQUEST["origen"];
        $id_procedencia     = $_REQUEST["procedencia"];
        $id_rubro           = $_REQUEST["rubro"];
        $id_tipo            = $_REQUEST["tipo"];

        $where = "";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING l.lote LIKE '$search%' OR p.producto LIKE '$search%' OR p.codigo LIKE '$search%'";
        }

        // if (!isset($_REQUEST['id_sucursal']) && empty($_REQUEST['id_sucursal'])) {
        //     $sucursal = $id_sucursal;
        // }

        if ((isset($desde) && !empty($desde)) || (isset($hasta) && !empty($hasta))) {
            $where_fecha = "AND DATE(l.vencimiento) BETWEEN '$desde' AND '$hasta'";
        } else {
            $where_fecha = "";
        }

        if(!empty($id_producto) && intVal($id_producto) != 0) {$and_id_producto .= " AND p.id_producto= $id_producto";}else{$and_id_producto .="";};
        if(!empty($id_laboratorio) && intVal($id_laboratorio) != 0) {$and_id_laboratorio .= " AND p.id_laboratorio= $id_laboratorio";}else{$and_id_laboratorio .="";};
        if(!empty($id_marca) && intVal($id_marca) != 0) {$and_id_marca .= " AND p.id_marca= $id_marca";}else{$and_id_marca .="";};
        if(!empty($id_unidad_medida) && intVal($id_unidad_medida) != 0) {$and_id_unidad_medida .= " AND p.id_unidad_medida= $id_unidad_medida";}else{$and_id_unidad_medida.="";};
        if(!empty($id_presentacion) && intVal($id_presentacion) != 0) {$and_id_presentacion .= " AND p.id_presentacion= $id_presentacion";}else{$and_id_presentacion.="";};
        if(!empty($id_clasificacion) && intVal($id_clasificacion) != 0) {$and_id_clasificacion .= " AND pc.id_clasificacion= $id_clasificacion";}else{$and_id_clasificacion.="";};
        if(!empty($id_origen) && intVal($id_origen) != 0) {$and_id_origen .= " AND p.id_origen= $id_origen";}else{$and_id_origen.="";};
        if(!empty($id_procedencia) && intVal($id_procedencia) != 0) {$and_id_procedencia .= " AND p.id_pais= $id_procedencia";}else{$and_id_procedencia.="";};
        if(!empty($id_rubro) && intVal($id_rubro) != 0) {$and_id_rubro .= " AND p.id_rubro= $id_rubro";}else{$and_id_rubro.="";};
        if(!empty($id_tipo) && intVal($id_tipo) != 0) {$and_id_tipo .= " AND p.id_tipo_producto= $id_tipo";}else{$and_id_tipo.="";};
         
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            l.id_lote,
                            l.lote,
                            l.canje,
                            l.usuario,
                            p.producto,
                            p.codigo,
                            IF(l.canje=1,'Si','No') AS canje_str,
                            DATE_FORMAT(l.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote,
                            DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y') AS vencimiento_canje,
                            CASE 
                                WHEN l.vencimiento >= CURRENT_DATE() THEN 'Activo' 
                                ELSE 'Vencido' 
                            END AS estado_lote,
                            IF(l.canje=1, IF(l.vencimiento_canje >= CURRENT_DATE(),'Activo','Vencido'),'Sin Canje') AS estado_canje,
                            IFNULL(s.entero,0) AS entero,
                            IFNULL(s.fraccionado_st,0) AS fracc,
                            pr.presentacion,
                            pa.nombre AS principio_activo,
                            cp.id_clasificacion_producto,
                            cp.clasificacion,
                            um.unidad_medida,
                            r.rubro,
                            m.marca,
                            la.laboratorio,
                            t.tipo,
                            o.origen,
                            p.precio AS costo
                        FROM lotes l
                        LEFT JOIN (SELECT *, SUM(stock) AS entero, SUM(fraccionado) AS fraccionado_st FROM stock GROUP BY id_lote) s ON l.id_lote=s.id_lote
                        LEFT JOIN productos p ON s.id_producto=p.id_producto
                        LEFT JOIN marcas m ON m.id_marca=p.id_marca
                        LEFT JOIN tipos_productos t ON t.id_tipo_producto = p.id_tipo_producto
                        LEFT JOIN productos_clasificaciones pc ON  p.id_producto = pc.id_producto
                        LEFT JOIN clasificaciones_productos cp ON pc.id_clasificacion=cp.id_clasificacion_producto
                        LEFT JOIN laboratorios la ON la.id_laboratorio= p.id_laboratorio
                        LEFT JOIN origenes o ON o.id_origen=p.id_origen
                        LEFT JOIN presentaciones pr ON pr.id_presentacion=p.id_presentacion
                        LEFT JOIN productos_principios pp ON pp.id_producto=p.id_producto
                        LEFT JOIN principios_activos pa ON pa.id_principio=pp.id_principio
                        LEFT JOIN unidades_medidas um ON um.id_unidad_medida=p.id_unidad_medida
                        LEFT JOIN rubros r ON r.id_rubro=p.id_rubro
                        WHERE 1=1 $where_fecha $and_id_tipo $and_id_producto $and_id_laboratorio $and_id_marca $and_id_unidad_medida $and_id_presentacion $and_id_clasificacion $and_id_origen $and_id_procedencia $and_id_rubro
                        GROUP BY l.id_lote
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

    case 'ver_productos':
        $db          = DataBase::conectar();
        $id_lote = $db->clearText($_GET['id_lote']);

        $db->setQuery("SELECT 
                            s.id_lote,
                            s.id_producto,
                            p.producto,
                            l.lote,
                            su.sucursal,
                            s.id_sucursal,
                            SUM(stock) AS entero,
                            SUM(fraccionado) AS fraccionado
                            
                        FROM stock s
                        LEFT JOIN sucursales su ON s.id_sucursal=su.id_sucursal
                        LEFT JOIN productos p ON s.id_producto=p.id_producto
                        LEFT JOIN lotes l ON s.id_lote=l.id_lote
                        WHERE s.id_lote=$id_lote
                        GROUP BY s.id_sucursal, s.id_lote");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
        break;
}
