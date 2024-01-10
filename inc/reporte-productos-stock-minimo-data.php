<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$id_sucursal_sistema = datosUsuario($usuario)->id_sucursal;

switch ($q) {
    case 'ver':
        $db           = DataBase::conectar();
        $id_producto  = $_REQUEST["producto"];
        $id_sucursal  = $_REQUEST["sucursal"];
        $id_laboratorio  = $_REQUEST["laboratorio"];
        $id_marca     = $_REQUEST["marca"];
        $id_unidad_medida  = $_REQUEST["unidad_medida"];
        $id_presentacion  = $_REQUEST["presentacion"];
        $id_clasificacion  = $_REQUEST["clasificacion"];
        $id_origen  = $_REQUEST["origen"];
        $id_procedencia  = $_REQUEST["procedencia"];
        $id_rubro  = $_REQUEST["rubro"];
        $id_tipo      = $_REQUEST["tipo"];


        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
            $id_sucursal =$id_sucursal;
        }
        else{
            $id_sucursal = $id_sucursal_sistema;
        };



        if(!empty($id_producto) && intVal($id_producto) != 0) {$and_id_producto .= " AND p.id_producto= $id_producto";}else{$and_id_producto .="";};
        if(!empty($id_laboratorio) && intVal($id_laboratorio) != 0) {$and_id_laboratorio .= " AND p.id_laboratorio= $id_laboratorio";}else{$and_id_laboratorio .="";};
        if(!empty($id_marca) && intVal($id_marca) != 0) {$and_id_marca .= " AND p.id_marca= $id_marca";}else{$and_id_marca .="";};
        if(!empty($id_unidad_medida) && intVal($id_unidad_medida) != 0) {$and_id_unidad_medida .= " AND p.id_unidad_medida= $id_unidad_medida";}else{$and_id_unidad_medida.="";};
        if(!empty($id_presentacion) && intVal($id_presentacion) != 0) {$and_id_presentacion .= " AND p.id_presentacion= $id_presentacion";}else{$and_id_presentacion.="";};
        if(!empty($id_clasificacion) && intVal($id_clasificacion) != 0) {$and_id_clasificacion .= " AND pc.id_clasificacion= $id_clasificacion";}else{$and_id_clasificacion.="";};
        if(!empty($id_origen) && intVal($id_origen) != 0) {$and_id_origen .= " AND p.id_origen= $id_origen";}else{$and_id_origen.="";};
        if(!empty($id_procedencia) && intVal($id_procedencia) != 0) {$and_id_procedencia .= " AND p.id_pais= $id_procedencia";}else{$and_id_procedencia.="";};
        if(!empty($id_rubro) && intVal($id_rubro) != 0) {$and_id_rubro .= " AND p.id_rubro= $id_rubro";}else{$and_id_rubro.="";};
        if(!empty($id_tipo) && intVal($id_tipo) != 0) {$and_id_tipo .= " AND p.id_tipo= $id_tipo";}else{$and_id_tipo.="";};

        
        
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                             p.id_producto,
                            p.codigo,
                            p.producto,  
                            m.marca,
                            l.`laboratorio`,
                            t.tipo,
                            o.`origen`,
                            md.`moneda`,
                            pr.`presentacion`,
                            pa.`nombre` AS principio_activo,
                            cp.id_clasificacion_producto,
                            cp.clasificacion,
                            um.`unidad_medida`,
                            r.`rubro`,
                            ps.`nombre_es` AS pais,
                            CASE p.`conservacion` WHEN '1' THEN 'NORMAL' WHEN '2' THEN 'REFRIGERADO' END AS conservacion,  
                            (
                                SELECT 
                                IFNULL(SUM(s.stock), 0)
                                FROM stock s 
                                JOIN lotes l ON s.id_lote=l.id_lote
                                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal  AND vencimiento>=CURRENT_DATE()
                            ) AS stock,
                            IFNULL(sn.minimo, 0) AS minimo,
                            IFNULL(sn.maximo, 0) AS maximo
                        FROM productos p
                        LEFT JOIN presentaciones pr ON pr.`id_presentacion` = p.`id_presentacion`
                        LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto 
                        LEFT JOIN marcas m ON m.id_marca=p.id_marca
                        LEFT JOIN tipos_productos t ON t.id_tipo_producto = p.id_tipo_producto
                        LEFT JOIN productos_clasificaciones pc ON  p.id_producto = pp.id_producto
                        LEFT JOIN clasificaciones_productos cp ON pc.id_clasificacion=cp.id_clasificacion_producto
                        LEFT JOIN laboratorios l ON l.`id_laboratorio`= p.`id_laboratorio`
                        LEFT JOIN origenes o ON o.`id_origen`=p.`id_origen`
                        LEFT JOIN monedas md ON md.`id_moneda`=p.`id_moneda`
                        LEFT JOIN productos_principios ppr ON ppr.`id_producto`=p.`id_producto`
                        LEFT JOIN principios_activos pa ON pa.`id_principio`=ppr.`id_principio`
                        LEFT JOIN unidades_medidas um ON um.`id_unidad_medida`=p.`id_unidad_medida`
                        LEFT JOIN rubros r ON r.`id_rubro`=p.`id_rubro`
                        LEFT JOIN paises ps ON ps.`id_pais`=p.`id_pais`
                        LEFT JOIN stock_niveles sn ON p.id_producto=sn.id_producto AND sn.id_sucursal=$id_sucursal 
                        $and_id_producto $and_id_laboratorio $and_id_marca $and_id_unidad_medida $and_id_presentacion $and_id_clasificacion $and_id_origen $and_id_procedencia $and_id_rubro
                        GROUP BY id_producto 
                        HAVING stock <= minimo AND minimo > 0
                        ORDER BY stock DESC 
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
}