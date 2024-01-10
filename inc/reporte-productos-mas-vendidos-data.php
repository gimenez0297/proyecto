<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$id_sucursal_ = datosUsuario($usuario)->id_sucursal;
$id_rol = datosUsuario($usuario)->id_rol;

switch ($q) {
    case 'ver':
        $db                 = DataBase::conectar();
        $desde 		        = $db->clearText($_REQUEST["desde"]);
        $hasta 		        = $db->clearText($_REQUEST["hasta"]);
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
        $filtro_fraccionado = $db->clearText($_REQUEST["filtro_fraccionado"]);

        if (esAdmin($id_rol) === false) {
            $where_sucursal .= " AND f.id_sucursal=$id_sucursal_";
        } else if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
            $where_sucursal .= " AND f.id_sucursal=$id_sucursal";
        } else if($id_sucursal === 'null'){
            $where_sucursal .= "";
        }

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (empty($limit)) {
            $limit_range = "";
          } else {
            $limit_range = "LIMIT $offset, $limit";
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
        if(!empty($id_clasificacion) && intVal($id_clasificacion) != 0) {$and_id_clasificacion .= " AND pc.id_clasificacion= $id_clasificacion";}else{$and_id_clasificacion.="";};
        if(!empty($id_origen) && intVal($id_origen) != 0) {$and_id_origen .= " AND p.id_origen= $id_origen";}else{$and_id_origen.="";};
        if(!empty($id_procedencia) && intVal($id_procedencia) != 0) {$and_id_procedencia .= " AND p.id_pais= $id_procedencia";}else{$and_id_procedencia.="";};
        if(!empty($id_rubro) && intVal($id_rubro) != 0) {$and_id_rubro .= " AND p.id_rubro= $id_rubro";}else{$and_id_rubro.="";};
        if(!empty($id_tipo) && intVal($id_tipo) != 0) {$and_id_tipo .= " AND p.id_tipo_producto= $id_tipo";}else{$and_id_tipo.="";};
        if($omitir_remates == 1) { $and_remate = " AND fp.remate=0"; } else { $and_remate = ""; };
        $and_filtro_fraccionado = intVal($filtro_fraccionado) == 1 ? " AND fp.fraccionado = 1": " AND fp.fraccionado != 1";

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            fp.id_producto,
                            p.codigo,
                            fp.producto,
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
                            SUM(CASE fp.fraccionado WHEN '0' THEN fp.cantidad ELSE 0 END) AS cantidad_entero,
                            SUM(CASE fp.fraccionado WHEN '1' THEN fp.cantidad ELSE 0 END) AS cantidad_fraccionado
                           
                        FROM facturas_productos fp
                        LEFT JOIN (SELECT * FROM facturas WHERE estado != 2) f ON fp.id_factura=f.id_factura
                        LEFT JOIN productos p ON p.id_producto=fp.id_producto
                        JOIN productos_proveedores pprov ON pprov.id_producto = p.id_producto
                        LEFT JOIN marcas m ON m.id_marca=p.id_marca
                        LEFT JOIN tipos_productos t ON t.id_tipo_producto = p.id_tipo_producto
                        LEFT JOIN laboratorios l ON l.`id_laboratorio`= p.`id_laboratorio`
                        LEFT JOIN origenes o ON o.`id_origen`=p.`id_origen`
                        LEFT JOIN monedas md ON md.`id_moneda`=p.`id_moneda`
                        LEFT JOIN presentaciones pr ON pr.`id_presentacion`=p.`id_presentacion`
                        LEFT JOIN productos_principios pp ON pp.`id_producto`= p.`id_producto`
                        LEFT JOIN productos_clasificaciones pc ON  p.id_producto = pp.id_producto
                        LEFT JOIN clasificaciones_productos cp ON pc.id_clasificacion=cp.id_clasificacion_producto
                        LEFT JOIN principios_activos pa ON pa.`id_principio`=pp.`id_principio`
                        LEFT JOIN unidades_medidas um ON um.`id_unidad_medida`=p.`id_unidad_medida`
                        LEFT JOIN rubros r ON r.`id_rubro`=p.`id_rubro`
                        LEFT JOIN paises ps ON ps.`id_pais`=p.`id_pais`
                        WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' 
                        $where_sucursal $and_id_marca $and_id_tipo $and_id_clasificacion $and_id_rubro $and_id_procedencia $and_id_origen $and_id_presentacion 
                        $and_id_medida $and_id_laboratorio $and_id_producto $and_remate $and_id_proveedor_pral
                        $having
                        GROUP BY fp.id_producto 
                        ORDER BY $sort $order             
                        $limit_range");
                        
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
                                DATE_FORMAT(fecha_venta,'%d/%m/%Y %H:%i:%s') AS fecha,
                                f.razon_social,
                                f.ruc,
                                fp.producto,
                                fp.lote,
                                (CASE fp.fraccionado WHEN '0' THEN fp.cantidad ELSE 0 END) AS cantidad,
                                (CASE fp.fraccionado WHEN '1' THEN fp.cantidad ELSE 0 END) AS cantidad_fraccionado,
                                fp.`precio`,
                                fp.total_venta AS total
                            FROM facturas_productos fp
                            LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                            LEFT JOIN timbrados t ON f.`id_timbrado`=t.`id_timbrado`
                            WHERE fp.id_producto=$id_producto AND f.estado != 2 AND f.fecha_venta BETWEEN '$desde' AND '$hasta' $and_id_sucursal");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;
}
