<?php
    include ("inc/funciones.php");
    $db = DataBase::conectar();
    $usuario = $auth->getUsername();

    $id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
    $estado   = $db->clearText($_REQUEST['estado']);
    $tipo        = $db->clearText($_REQUEST['tipo']);
    $rubro       = $db->clearText($_REQUEST['rubro']);
    $procedencia = $db->clearText($_REQUEST['procedencia']);
    $origen      = $db->clearText($_REQUEST['origen']);
    $clasificacion = $db->clearText($_REQUEST['clasificacion']);
    $presentacion  = $db->clearText($_REQUEST['presentacion']);
    $unidad_medida = $db->clearText($_REQUEST['unidad_medida']);
    $marca         = $db->clearText($_REQUEST['marca']);
    $laboratorio   = $db->clearText($_REQUEST['laboratorio']);
    
    if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        $where_sucursal .= " AND s.id_sucursal= $id_sucursal";
    }else{$where_sucursal .="";};
    
    if(!empty($estado) && intVal($estado) == 2) {
        $and_vencimiento = "AND l.vencimiento >= CURRENT_DATE() OR l.vencimiento IS NULL";
    }else if(!empty($estado) && intVal($estado) == 1){
        $and_vencimiento = "AND l.vencimiento <= CURRENT_DATE()";
    }

    if(!empty($tipo) && intVal($tipo) != 0) {
        $and_id_tipo .= " AND p.id_tipo_producto= $tipo";
    }else{$and_id_tipo.="";};

    if(!empty($rubro) && intVal($rubro) != 0) {
        $and_id_rubro .= " AND p.id_rubro= $rubro";
    }else{$and_id_rubro.="";};

    if(!empty($procedencia) && intVal($procedencia) != 0) {
        $and_id_procedencia .= " AND p.id_pais= $procedencia";
    }else{$and_id_procedencia.="";};

    if(!empty($origen) && intVal($origen) != 0) {
        $and_id_origen .= " AND p.id_origen= $origen";
    }else{$and_id_origen.="";};

    if(!empty($clasificacion) && intVal($clasificacion) != 0) {
        $and_id_clasificacion .= " AND p.id_clasificacion= $clasificacion";
    }else{$and_id_clasificacion.="";};

    if(!empty($presentacion) && intVal($presentacion) != 0) {
        $and_id_presentacion .= " AND p.id_presentacion= $presentacion";
    }else{$and_id_presentacion.="";};

    if(!empty($unidad_medida) && intVal($unidad_medida) != 0) {
        $and_id_unidad_medida .= " AND p.id_unidad_medida= $unidad_medida";
    }else{$and_id_unidad_medida.="";};

    if(!empty($marca) && intVal($marca) != 0) {
        $and_id_marca .= " AND p.id_marca= $marca";
    }else{$and_id_marca .="";};

    if(!empty($laboratorio) && intVal($laboratorio) != 0) {
        $and_id_laboratorio .= " AND p.id_laboratorio= $laboratorio";
    }else{$and_id_laboratorio .="";};

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            s.id_stock,
                            IFNULL(SUM(s.stock_lote), 0) AS stock,
                            IFNULL(SUM(s.fraccionado), 0) AS fraccionado,
                            DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote,
                            DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y') AS vencimiento_canje,
                            p.id_producto,
                            pr.presentacion,
                            p.codigo,
                            p.producto,
                            p.precio,
                            SUM(l.costo) AS costo_lote,
                            SUM(s.stock_lote  * l.costo) AS total_entero,
                            SUM(s.fraccionado * p.precio_fraccionado) AS total_fracc,
                            SUM(s.stock_lote * l.costo) AS total_general,
                            DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                            p.precio_fraccionado
                        FROM productos p
                        LEFT JOIN (SELECT *, SUM(stock) AS stock_lote, SUM(fraccionado) AS stock_frac FROM stock GROUP BY id_lote) s ON p.id_producto=s.id_producto
                        LEFT JOIN presentaciones pr ON p.id_presentacion=pr.id_presentacion
                        LEFT JOIN lotes l ON s.id_lote = l.id_lote
                        WHERE 1=1 $where_sucursal $and_vencimiento $and_id_tipo $and_id_rubro $and_id_procedencia $and_id_origen $and_id_marca  $and_id_clasificacion   $and_id_presentacion $and_id_medida $and_id_laboratorio 
                        GROUP BY p.id_producto");
    $rows = $db->loadObjectList();

    // Logos
    $logo_farmacia = "dist/images/logo.png";

    // MPDF
    require_once __DIR__ . '/mpdf/vendor/autoload.php';

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 5,
        'margin_right' => 5,
        'margin_top' => 50,
        'margin_bottom' => 15,
    ]);

    foreach ($rows as $r) {
        $fecha = $r->fecha_actual;
    }

    $mpdf->SetTitle("Stock Valorizado"); 
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">STOCK VALORIZADO</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;">Fecha:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Usuario:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;" width="20%">'.$fecha.'</td>
                    <td width="65%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px; text-align:center">'.$usuario.'</td>
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
        .tg .tg-zqap{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle}
        .tg .tg-1x3m{text-align:left;vertical-align:middle}
        .tg .tg-wjt0{text-align:center;vertical-align:middle}
        .firma{text-align:center; font-weight: bold; margin-top: 100px}
        .total{text-align:center; font-weight: bold; text-align:center;background-color:#EAE6E5}
        .aprobado{text-align:center;margin-top: 60px}

        .promedio{text-align:center;font-weight:bold;margin-top: 50px}
        .tabla{vertical-align: top}

    </style>
    ');

    $mpdf->writehtml('
        <table class="tg" style="width:100%">
            <thead>             
                <tr style="background-color: #b5b5b526;">
                    <th class="tg-zqap">Código</th>
                    <th class="tg-zqap">Producto</th>
                    <th class="tg-zqap">Presentación</th>
                    <th class="tg-zqap">Cantidad</th>
                    <th class="tg-zqap">Total</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $codigo = $r->codigo;
        $producto = $r->producto;
        $presentacion = $r->presentacion;
        $cantidad = $r->stock;
        $fraccionado = $r->fraccionado;
        $total_fracc = $r->total_fracc;
        $total_entero =$r->total_entero;
        
        $total_cantidad += $cantidad ;
        $total_total += $total_entero; 

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m">'.$codigo.'</td>
                <td class="tg-1x3m">'.$producto.'</td>
                <td class="tg-1x3m">'.$presentacion.'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($cantidad).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($total_entero).'</td>
               
            </tr>
        ');
    }

    $mpdf->WriteHTML('
            <tr>
                <td class="total" style="font-size:12px; text-align:left; background-color: #b5b5b526;"  colspan="3">TOTAL</td>
                <td class="total" style="text-align:right; font-size:12px; background-color: #b5b5b526;">'.separadorMiles($total_cantidad).'</td>
                <td class="total" style="text-align:right; font-size:12px; background-color: #b5b5b526;">'.separadorMiles($total_total).'</td>
            </tr>
        </tbody>
    </table>
    ');

    
    $mpdf->Output("StockValorizado.pdf", 'I');

