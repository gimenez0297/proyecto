<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();

switch ($q) {
    case 'ver':
        $db = DataBase::conectar();
        $desde              = $db->clearText($_REQUEST["desde"]);
        $hasta              = $db->clearText($_REQUEST["hasta"]);
        $id_producto        = $db->clearText($_REQUEST["producto"]);
        $id_proveedor_pral  = $db->clearText($_REQUEST["id_proveedor_pral"]);
        $id_sucursal        = $db->clearText($_REQUEST["sucursal"]);
        $id_laboratorio     = $db->clearText($_REQUEST["laboratorio"]);
        $id_marca           = $db->clearText($_REQUEST["marca"]);
        $id_unidad_medida   = $db->clearText($_REQUEST["unidad_medida"]);
        $id_presentacion    = $db->clearText($_REQUEST["presentacion"]);
        $id_clasificacion   = $db->clearText($_REQUEST["clasificacion"]);
        $id_origen          = $db->clearText($_REQUEST["origen"]);
        $id_procedencia     = $db->clearText($_REQUEST["procedencia"]);
        $id_rubro           = $db->clearText($_REQUEST["rubro"]);
        $id_tipo            = $db->clearText($_REQUEST["tipo"]);
        $omitir_remates     = $db->clearText($_REQUEST["omitir_remates"]);


        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        
        $sort=='producto'           ?   $sort='fp.producto'         : '' ;
        $sort=='codigo'             ?   $sort='p.codigo'            : '' ;
        $sort=='tipo'               ?   $sort='p.id_tipo_producto'  : '' ;
        $sort=='principio_activo'   ?   $sort='pp.id_principio'     : '' ;
        $sort=='pais'               ?   $sort='p.id_pais'           : '' ;

        $order_maestro = "ORDER BY $sort $order, fp.id_producto";

        if(is_numeric($sort)){
            $where_id_sucursal = "AND f.id_sucursal='$sort'";
            $sort = "total";
            $order_maestro = "ORDER BY $sort $order";
        }else{
            $where_id_sucursal = "";
        }

        if(empty($limit)){ 
            $limit_range = "";
        }else{
            $limit_range = "LIMIT $offset, $limit";
        }
        if (isset($search) && !empty($search)) {
            $where = "AND CONCAT_WS(' ',p.codigo,p.producto) LIKE '%$search%'";
        }
        if(empty($desde) || empty($hasta)) {
            $desde        = date('Y-m-d');
            $hasta        = date('Y-m-d');
        }
        if(!empty($id_producto) && intVal($id_producto) != 0) {$and_id_producto .= " AND fp.id_producto= $id_producto";}else{$and_id_producto .="";};
        if(!empty($id_proveedor_pral) && intVal($id_proveedor_pral) != 0) {$and_id_proveedor_pral .= " AND pprov.id_proveedor = $id_proveedor_pral";}else{$and_id_proveedor_pral .="";};
        if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {$and_id_sucursal .= " AND f.id_sucursal= $id_sucursal";}else{$and_id_sucursal .="";};
        if(!empty($id_laboratorio) && intVal($id_laboratorio) != 0) {$and_id_laboratorio .= " AND p.id_laboratorio= $id_laboratorio";}else{$and_id_laboratorio .="";};
        if(!empty($id_marca) && intVal($id_marca) != 0) {$and_id_marca .= " AND p.id_marca= $id_marca";}else{$and_id_marca .="";};
        if(!empty($id_unidad_medida) && intVal($id_unidad_medida) != 0) {$and_id_unidad_medida .= " AND p.id_unidad_medida= $id_unidad_medida";}else{$and_id_unidad_medida.="";};
        if(!empty($id_presentacion) && intVal($id_presentacion) != 0) {$and_id_presentacion .= " AND p.id_presentacion= $id_presentacion";}else{$and_id_presentacion.="";};
        if(!empty($id_clasificacion) && intVal($id_clasificacion) != 0) {$and_id_clasificacion .= " AND p.id_clasificacion= $id_clasificacion";}else{$and_id_clasificacion.="";};
        if(!empty($id_origen) && intVal($id_origen) != 0) {$and_id_origen .= " AND p.id_origen= $id_origen";}else{$and_id_origen.="";};
        if(!empty($id_procedencia) && intVal($id_procedencia) != 0) {$and_id_procedencia .= " AND p.id_pais= $id_procedencia";}else{$and_id_procedencia.="";};
        if(!empty($id_rubro) && intVal($id_rubro) != 0) {$and_id_rubro .= " AND p.id_rubro= $id_rubro";}else{$and_id_rubro.="";};
        if(!empty($id_tipo) && intVal($id_tipo) != 0) {$and_id_tipo .= " AND p.id_tipo_producto= $id_tipo";}else{$and_id_tipo.="";};
        if($omitir_remates == 1) { $and_remate .= " AND fp.remate=0"; } else { $and_remate = ""; };
        
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            fp.id_producto,
                            SUM(fp.cantidad) AS total
                        FROM facturas_productos fp
                        LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                        JOIN productos p ON fp.id_producto = p.id_producto
                        JOIN productos_proveedores pprov ON pprov.id_producto = p.id_producto
                        JOIN sucursales s ON f.id_sucursal = s.id_sucursal
                        LEFT JOIN marcas m ON m.id_marca=p.id_marca
                        LEFT JOIN tipos_productos t ON t.id_tipo_producto = p.id_tipo_producto
                        LEFT JOIN laboratorios l ON l.`id_laboratorio`= p.`id_laboratorio`
                        LEFT JOIN origenes o ON o.`id_origen`=p.`id_origen`
                        LEFT JOIN monedas md ON md.`id_moneda`=p.`id_moneda`
                        LEFT JOIN presentaciones pr ON pr.`id_presentacion`=p.`id_presentacion`
                        LEFT JOIN productos_principios pp ON pp.`id_producto`=p.`id_producto`
                        LEFT JOIN principios_activos pa ON pa.`id_principio`=pp.`id_principio`
                        LEFT JOIN unidades_medidas um ON um.`id_unidad_medida`=p.`id_unidad_medida`
                        LEFT JOIN rubros r ON r.`id_rubro`=p.`id_rubro`
                        LEFT JOIN paises ps ON ps.`id_pais`=p.`id_pais`
                        LEFT JOIN productos_clasificaciones pc ON  p.id_producto = pp.id_producto
                        LEFT JOIN clasificaciones_productos cp ON pc.id_clasificacion=cp.id_clasificacion_producto
                        WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' $where 
                        $and_id_tipo $and_id_producto $and_id_sucursal $and_id_laboratorio $and_id_marca $and_id_unidad_medida $and_id_presentacion $and_id_clasificacion $and_id_origen $and_id_procedencia $and_id_rubro $and_remate $and_id_proveedor_pral $where_id_sucursal
                        GROUP BY fp.id_producto
                        $order_maestro
                        $limit_range
                    ");
                    //pais

        $rows = $db->loadObjectList();
        $id_productos = array_column($rows, 'id_producto');
        $lista_id_productos = implode(',',$id_productos);

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total     = $total_row->total;
    
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            fp.*,
                            SUM(fp.cantidad) AS total,
                            p.codigo,
                            s.id_sucursal,
                            m.marca,
                            l.laboratorio,
                            t.tipo,
                            o.origen,
                            md.moneda,
                            pr.presentacion,
                            pa.nombre AS principio_activo,
                            um.unidad_medida,
                            r.rubro,
                            ps.nombre_es AS pais,
                            CASE p.conservacion WHEN '1' THEN 'NORMAL' WHEN '2' THEN 'REFRIGERADO' END AS conservacion
                        FROM facturas_productos fp
                        LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                        JOIN productos p ON fp.id_producto = p.id_producto
                        JOIN productos_proveedores pprov ON pprov.id_producto = p.id_producto
                        JOIN sucursales s ON f.id_sucursal = s.id_sucursal
                        LEFT JOIN marcas m ON m.id_marca=p.id_marca
                        LEFT JOIN tipos_productos t ON t.id_tipo_producto = p.id_tipo_producto
                        LEFT JOIN laboratorios l ON l.`id_laboratorio`= p.`id_laboratorio`
                        LEFT JOIN origenes o ON o.`id_origen`=p.`id_origen`
                        LEFT JOIN monedas md ON md.`id_moneda`=p.`id_moneda`
                        LEFT JOIN presentaciones pr ON pr.`id_presentacion`=p.`id_presentacion`
                        LEFT JOIN productos_principios pp ON pp.`id_producto`=p.`id_producto`
                        LEFT JOIN principios_activos pa ON pa.`id_principio`=pp.`id_principio`
                        LEFT JOIN unidades_medidas um ON um.`id_unidad_medida`=p.`id_unidad_medida`
                        LEFT JOIN rubros r ON r.`id_rubro`=p.`id_rubro`
                        LEFT JOIN paises ps ON ps.`id_pais`=p.`id_pais`
                        LEFT JOIN productos_clasificaciones pc ON  p.id_producto = pp.id_producto
                        LEFT JOIN clasificaciones_productos cp ON pc.id_clasificacion=cp.id_clasificacion_producto
                        WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' AND fp.id_producto IN($lista_id_productos) $where_id_sucursal
                        GROUP BY s.id_sucursal, fp.id_producto
                        $order_maestro");
        $rows = $db->loadObjectList();

        

        $salida = array();

        foreach ($rows as $key => $r) {
            $row = (array) $r;

            $row[$row['id_sucursal']] = $row['total'];

            if ($row['id_producto'] != $rows[$key - 1]->id_producto) {
                $salida[] = $row;
                $i        = array_key_last($salida);
            } else {
                $salida[$i] += $row;
            }
        }

        if ($salida) {
            $salida = array('total' => $total, 'rows' => $salida);
        } else {
            $salida = array('total' => 0, 'rows' => array());
        }

        echo json_encode($salida);
    break;

    case 'ver_detalle':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_GET['id_producto']);
            $sucursal = $db->clearText($_GET['id_sucursal']);
            $desde    = $db->clearText($_GET['desde']) . " 00:00:00";
            $hasta    = $db->clearText($_GET['hasta']) . " 23:59:59";

            if(!empty($sucursal) && intVal($sucursal) != 0) {
                $and_id_sucursal .= " AND f.id_sucursal= $sucursal";
            }else{
                $and_id_sucursal .="";
            };

            $db->setQuery("SELECT 
                                fp.id_factura,
                                CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                                DATE_FORMAT(fecha_venta, '%d/%m/%Y') AS fecha,
                                f.razon_social,
                                f.ruc,
                                fp.producto,
                                fp.lote,
                                fp.cantidad,
                                fp.`precio`,
                                fp.total_venta AS total
                            FROM facturas_productos fp
                            LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                            LEFT JOIN timbrados t ON f.`id_timbrado`=t.`id_timbrado`
                            WHERE fp.id_producto=$id_producto AND f.estado != 2 AND f.fecha_venta BETWEEN '$desde' AND '$hasta' $and_id_sucursal");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'sucursales':
            $db = DataBase::conectar();
            $db->setQuery("SELECT
                                *
                                FROM sucursales
                                WHERE estado=1
                                ");

            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;
}
