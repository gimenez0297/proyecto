<?php
    include ("inc/funciones.php");
    $db                = DataBase::conectar();
    $usuario           = $auth->getUsername();

    $desde 		       = $db->clearText($_REQUEST["desde"]);
    $hasta 		       = $db->clearText($_REQUEST["hasta"]);
    $id_sucursal       = $db->clearText($_REQUEST["sucursal"]);
    $id_marca          = $db->clearText($_REQUEST["marca"]);
    $id_tipo           = $db->clearText($_REQUEST["tipo"]);
    $id_clasificacion  = $db->clearText($_REQUEST["clasificacion"]);
    $id_rubro          = $db->clearText($_REQUEST["rubro"]);
    $id_procedencia    = $db->clearText($_REQUEST["procedencia"]);
    $id_origen         = $db->clearText($_REQUEST["origen"]);
    $id_presentacion   = $db->clearText($_REQUEST["presentacion"]);
    $id_medida         = $db->clearText($_REQUEST["unidad_medida"]);
    $id_laboratorio    = $db->clearText($_REQUEST["laboratorio"]);
    $id_producto       = $db->clearText($_REQUEST["id_producto"]);
    $id_proveedor_pral = $db->clearText($_REQUEST["id_proveedor_pral"]);
    $omitir_remates    = $db->clearText($_REQUEST["omitir_remates"]);
    $columnas          = json_decode($_REQUEST["columnas"],true);
 
    $fecha_actual = date('d/m/Y H:i');

    if(empty($desde) || empty($hasta)) {
        $desde        = date('Y-m-d');
        $hasta        = date('Y-m-d');
    }
    if (!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        $and_id_sucursal .= " AND f.id_sucursal= $id_sucursal";
        $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
        $row     = $db->loadObject();
        $sucursal = $row->sucursal;
    }else{
        $and_id_sucursal .= "";
        $sucursal = "TODOS";
    }
    if (!empty($id_marca) && intVal($id_marca) != 0) {
        $and_id_marca .= " AND p.id_marca= $id_marca";
        $db->setQuery("SELECT marca FROM marcas WHERE id_marca=$id_marca");
        $row     = $db->loadObject();
        $marca = $row->marca;
    }else{
        $and_id_marca .= "";
        $marca = "TODAS";
    }
    if (!empty($id_tipo) && intVal($id_tipo) != 0) {
        $and_id_tipo .= " AND p.id_tipo_producto= $id_tipo";
        $db->setQuery("SELECT tipo FROM tipos_productos WHERE id_tipo_producto=$id_tipo");
        $row     = $db->loadObject();
        $tipo = $row->tipo;
    }else{
        $and_id_tipo .= "";
        $tipo = "TODOS";
    }
    if (!empty($id_clasificacion) && intVal($id_clasificacion) != 0) {
        $and_id_clasificacion .= " AND p.id_clasificacion= $id_clasificacion";
        $db->setQuery("SELECT clasificacion FROM clasificaciones_productos WHERE id_clasificacion_producto=$id_clasificacion");
        $row     = $db->loadObject();
        $clasificacion = $row->clasificacion;
    }else{
        $and_id_clasificacion .= "";
        $clasificacion = "TODOS";
    }
    if (!empty($id_rubro) && intVal($id_rubro) != 0) {
        $and_id_rubro .= " AND p.id_rubro= $id_rubro";
        $db->setQuery("SELECT rubro FROM rubros WHERE id_rubro=$id_rubro");
        $row     = $db->loadObject();
        $rubro = $row->rubro;
    }else{
        $and_id_rubro .= "";
        $rubro = "TODOS";
    }
    if (!empty($id_procedencia) && intVal($id_procedencia) != 0) {
        $and_id_procedencia .= " AND p.id_pais= $id_procedencia";
        $db->setQuery("SELECT nombre_es FROM paises WHERE id_pais=$id_procedencia");
        $row     = $db->loadObject();
        $procedencia = $row->nombre_es;
    }else{
        $and_id_procedencia .= "";
        $procedencia = "TODOS";
    }
    if (!empty($id_origen) && intVal($id_origen) != 0) {
        $and_id_origen .= " AND p.id_origen= $id_origen";
        $db->setQuery("SELECT origen FROM origenes WHERE id_origen=$id_origen");
        $row     = $db->loadObject();
        $origen = $row->origen;
    }else{
        $and_id_origen .= "";
        $origen = "TODOS";
    }
    if (!empty($id_presentacion) && intVal($id_presentacion) != 0) {
        $and_id_presentacion .= " AND p.id_presentacion= $id_presentacion";
        $db->setQuery("SELECT presentacion FROM presentaciones WHERE id_presentacion=$id_presentacion");
        $row     = $db->loadObject();
        $presentacion = $row->presentacion;
    }else{
        $and_id_presentacion .= "";
        $presentacion = "TODOS";
    }
    if (!empty($id_medida) && intVal($id_medida) != 0) {
        $and_id_medida .= " AND p.id_unidad_medida= $id_medida";
        $db->setQuery("SELECT unidad_medida FROM unidades_medidas WHERE id_unidad_medida=$id_medida");
        $row     = $db->loadObject();
        $unidad_medida = $row->unidad_medida;
    }else{
        $and_id_medida .= "";
        $unidad_medida = "TODOS";
    }
    if (!empty($id_laboratorio) && intVal($id_laboratorio) != 0) {
        $and_id_laboratorio .= " AND p.id_laboratorio= $id_laboratorio";
        $db->setQuery("SELECT laboratorio FROM laboratorios WHERE id_laboratorio=$id_laboratorio");
        $row     = $db->loadObject();
        $laboratorio = $row->laboratorio;
    }else{
        $and_id_laboratorio .= "";
        $laboratorio = "TODOS";
    }
    if (!empty($id_producto) && intVal($id_producto) != 0) {
        $and_id_producto .= " AND p.id_producto= $id_producto";
        $db->setQuery("SELECT producto FROM productos WHERE id_producto=$id_producto");
        $row     = $db->loadObject();
        $producto = $row->producto;
    }else{
        $and_id_producto .= "";
        $producto = "TODOS";
    }
    if ($omitir_remates == 1) {
        $and_remate = " AND fp.remate=0";
    } else {
        $and_remate = "";
    }
    if (!empty($id_proveedor_pral) && intVal($id_proveedor_pral != 0)) {
        $and_proveedor_pral = " AND pprov.id_proveedor = $id_proveedor_pral";
    } else {
        $and_proveedor_pral = "";
    }

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
                        WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta'
                        $and_id_tipo $and_id_producto $and_id_sucursal $and_id_laboratorio $and_id_marca $and_id_unidad_medida $and_id_presentacion $and_id_clasificacion $and_id_origen $and_id_procedencia $and_id_rubro $and_remate $and_proveedor_pral
                        GROUP BY s.id_sucursal, fp.id_producto
                        ORDER BY fp.producto ASC");
    $rows = $db->loadObjectList();

    $productos = array();
    foreach ($rows as $key => $r) {
        $row = (array) $r;

        $row[$row['id_sucursal']] = $row['total'];

        if ($row['id_producto'] != $rows[$key - 1]->id_producto) {
            $productos[] = $row;
            $i        = array_key_last($productos);
        } else {
            $productos[$i] += $row;
        }
    }

    $db->setQuery("SELECT * FROM sucursales WHERE estado=1");
    $sucursales = ($db->loadObjectList()) ?: [];

    // Logos
    $logo_farmacia = "dist/images/logo.png";

     // MPDF
     require_once __DIR__ . '/mpdf/vendor/autoload.php';

     $mpdf = new \Mpdf\Mpdf([
         'mode' => 'utf-8',
         'format' => 'A4',
         'orientation' => 'L',
         'margin_left' => 5,
         'margin_right' => 5,
         'margin_top' => 50,
         'margin_bottom' => 15,
     ]);

    $mpdf->SetTitle("Productos Más Vendidos Sucursal");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Productos Vendidos Por Sucursal</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="13%">Fecha desde:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="13%">Fecha Hasta:</td>   
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Sucursal:</td>   
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;vertical-align:middle" width="20%">Fecha:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;vertical-align:middle; " >Usuario:</td>
                </tr>
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$sucursal.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$fecha_actual.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$usuario.'</td>
                </tr>
            </thead>
        </table>
    ');

    $mpdf->SetHTMLFooter('
         <div style="text-align:right">Pag.: {PAGENO}/{nbpg}</div>
    ');

    // Body
    $mpdf->WriteHTML('
    <style type="text/css">
        body{font-family:Arial, sans-serif;font-size:14px;}

        /* Tabla cabecera */
        .tc  {border-collapse:collapse;border-spacing:0;}
        .tc td{border-color:black;font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:0px;word-break:normal;}
        .tc th{border-color:black;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;overflow:hidden;padding:0px;word-break:normal;}
        .tc .tc-axmw{font-size:22px;font-weight:bold;text-align:center;vertical-align:middle}
        .tc .tc-lqfj{font-weight:bold;font-size:12px;text-align:center;vertical-align:middle}
        .tc .tc-wa1i{font-size:14px;text-align:left;vertical-align:middle;padding:3px 2px;}
        .tc .tc-nrix{font-size:10px;text-align:center;vertical-align:middle}
        .tc .tc-9q9o{font-size:14px;text-align:center;vertical-align:top}
        .total{font-size:16px;text-align:left;vertical-align:middle;padding:3px 2px;}

        /* Tabla footer */
        .tf  {border-collapse:collapse;border-spacing:0;}
        .tf td{border-color:black;font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:0px;word-break:normal;}
        .tf th{border-color:black;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;overflow:hidden;padding:0px;word-break:normal;}
        .tf .tf-crjr{font-weight:bold;text-align:left;vertical-align:middle;}
        .tf .tf-rt78{font-weight:bold;text-align:right;vertical-align:middle;}

        /* Tabal contenido */
        .tg  {border-collapse:collapse;border-spacing:0;}
        .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:10px;overflow:hidden;padding:5px 5px;word-break:normal;}
        .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:12px;font-weight:normal;overflow:hidden;padding:5px 5px;word-break:normal;}
        .tg .tg-zqap{font-size:12px;font-weight:bold;text-align:center;vertical-align:middle}
        .tg .tg-1x3m{text-align:left;vertical-align:middle; font-size:12px}
        .tg .tg-wjt0{text-align:center;vertical-align:middle}
        .firma{text-align:center; font-weight: bold; margin-top: 100px}
        .total{text-align:center; font-weight: bold; text-align:center;background-color: #b5b5b526;"}
        .aprobado{text-align:center;margin-top: 60px}

        .promedio{text-align:center;font-weight:bold;margin-top: 50px}
        .tabla{vertical-align: top}

    </style>
    ');

    $mpdf->writehtml('
        <table class="tg" style="width:100%">
            <thead>
                <tr style="background-color: #b5b5b526;">'
    );

    if (in_array('codigo',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Codigo</th>');
    }
    if (in_array('producto',$columnas)) {
    $mpdf->writehtml('<th class="tg-zqap">Producto</th>');
    }
    if (in_array('marca',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Marca</th>');
    }
    if (in_array('laboratorio',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Laboratorio</th>');
    }
    if (in_array('tipo',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Tipo</th>');
    }
    if (in_array('origen',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Origen</th>');
    }
    if (in_array('moneda',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Moneda</th>');
    }
    if (in_array('presentacion',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Presentación</th>');
    }
    if (in_array('principio_activo',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Princ. Activo</th>');
    }
    if (in_array('clasificacion',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Clasificacion</th>');
    }
    if (in_array('unidad_medida',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Unidad Medida</th>');
    }
    if (in_array('rubro',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Rubro</th>');
    }
    if (in_array('pais',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Pais</th>');
    }
    if (in_array('conservacion',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Conservación</th>');
    }
    if (in_array('cantidad_vendida',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Cantidad vendida</th>');
    }

    foreach ($sucursales as $v) {
        if (in_array($v->id_sucursal, $columnas)) {
            $mpdf->writehtml('<th class="tg-zqap">'.$v->sucursal.'</th>');
        }
    }

    $mpdf->writehtml('
                </tr>
            </thead>
        <tbody>'
    );

    $totales = [];
    $coun = 0;
    foreach ($productos as $key => $r) {
        $coun++;
        $codigo             = $r["codigo"];
        $producto           = $r["producto"];
        $marca              = $r["marca"];
        $laboratorio        = $r["laboratorio"];
        $tipo               = $r["tipo"];
        $origen             = $r["origen"];
        $moneda             = $r["moneda"];
        $presentacion       = $r["presentacion"];
        $principio_activo   = $r["principio_activo"];
        $clasificacion      = $r["clasificacion"];
        $unidad_medida      = $r["unidad_medida"];
        $rubro              = $r["rubro"];
        $pais               = $r["pais"];
        $conservacion       = $r["conservacion"];
        $total              += $cantidad;


        $mpdf->WriteHTML('<tr>');

        if (in_array('codigo',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$codigo.'</td>');
        }
        if (in_array('producto',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$producto.'</td>');
        }
        if (in_array('marca',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$marca.'</td>');
        }
        if (in_array('laboratorio',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$laboratorio.'</td>');
        }
        if (in_array('tipo',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$tipo.'</td>');
        }
        if (in_array('origen',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$origen.'</td>');
        }
        if (in_array('moneda',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$moneda.'</td>');
        }
        if (in_array('presentacion',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$presentacion.'</td>');
        }
        if (in_array('principio_activo',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$principio_activo.'</td>');
        }
        if (in_array('clasificacion',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$clasificacion.'</td>');
        }
        if (in_array('unidad_medida',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$unidad_medida.'</td>');
        }
        if (in_array('rubro',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$rubro.'</td>');
        }
        if (in_array('pais',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$pais.'</td>');
        }
        if (in_array('conservacion',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m">'.$conservacion.'</td>');
        }

        foreach ($sucursales as $v) {
            if (empty($r[$v->id_sucursal])) {
                $cantidad = 0;
            } else {
                $cantidad = $r[$v->id_sucursal];
            }


            if (in_array($v->id_sucursal, $columnas)) {
                $mpdf->writehtml('<td class="tg-1x3m" style="text-align:right;">'.$cantidad.'</td>');
                $totales[$v->id_sucursal] += $cantidad;
            }
        }

    }

    $colspan = count($columnas) - count($totales);
    $mpdf->WriteHTML('
        <tr>
            <td class="total" style="font-size:12px" colspan="' . $colspan . '">TOTAL</td>
    ');
    foreach ($sucursales as $v) {
        $total = $totales[$v->id_sucursal];

        if (in_array($v->id_sucursal, $columnas)) {
            $mpdf->WriteHTML('
                <td class="total" style="text-align:right; font-size:12px">' . separadorMiles($total) . '</td>
            ');
        }
    }

    $mpdf->WriteHTML('
                </tr>
            </tbody>
        </table>
    ');

    $mpdf->Output("ProductosVendidosPorSucursal.pdf", 'I');


    
