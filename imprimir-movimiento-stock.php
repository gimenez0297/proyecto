<?php
    include ("inc/funciones.php");
    $db = DataBase::conectar();
    $usuario = $auth->getUsername();
    $where = '';
    $where_fecha = '';

    $id_producto = $db->clearText($_REQUEST['id_producto']);
    // $producto = $db->clearText($_REQUEST['producto']);
    $desde = $db->clearText($_REQUEST['desde']);
    $hasta = $db->clearText($_REQUEST['hasta']);
    
    $id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
    
    $tipo        = $db->clearText($_REQUEST['tipo']);
    $rubro       = $db->clearText($_REQUEST['rubro']);
    $procedencia = $db->clearText($_REQUEST['procedencia']);
    $origen      = $db->clearText($_REQUEST['origen']);
    $clasificacion = $db->clearText($_REQUEST['clasificacion']);
    $presentacion  = $db->clearText($_REQUEST['presentacion']);
    $unidad_medida = $db->clearText($_REQUEST['unidad_medida']);
    $marca         = $db->clearText($_REQUEST['marca']);
    $laboratorio   = $db->clearText($_REQUEST['laboratorio']);

    if(!empty($desde) && !empty($hasta)) {
        $where_fecha = " AND DATE(sh.fecha) BETWEEN '$desde' AND '$hasta'";
    }
    if(!empty($id_sucursal)) {
        $where .= " AND sh.id_sucursal = $id_sucursal";
    }
    if(!empty($id_producto)) {
        $where .= " AND sh.id_producto = $id_producto";
    }
    if(!empty($tipo)) {
        $where .= " AND p.id_tipo_producto = $tipo";
    }
    if(!empty($rubro)) {
        $where .= " AND p.id_rubro = $rubro";
    }
    if(!empty($procedencia)) {
        $where .= " AND p.id_pais = $procedencia";
    }
    if(!empty($origen)) {
        $where .= " AND p.id_origen = $origen";
    }
    if(!empty($clasificacion)) {
        $where .= " AND p.id_clasificacion = $clasificacion";
    }
    if(!empty($presentacion)) {
        $where .= " AND p.id_presentacion = $presentacion";
    }
    if(!empty($unidad_medida)) {
        $where .= " AND p.id_unidad_medida = $unidad_medida";
    }
    if(!empty($marca)) {
        $where .= " AND p.id_marca = $marca";
    }
    if(!empty($laboratorio)) {
        $where .= " AND p.id_laboratorio = $laboratorio";
    }

    $db->setQuery("SELECT 
                        sh.id_stock_historial,
                        sh.id_producto,
                        sh.producto,
                        p.producto AS producto_str,
                        sh.id_sucursal,
                        sh.sucursal,
                        sh.id_lote,
                        sh.cantidad,
                        sh.fraccionado,
                        sh.operacion,
                        DATE_FORMAT(sh.fecha,'%d/%m/%Y') AS fecha_mov,
                        DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                        CASE 
                            WHEN operacion = 'SUB' THEN CONCAT('-','',sh.cantidad)
                            WHEN operacion = 'ADD' THEN sh.cantidad
                        END AS cantidad_str,
                        CASE 
                            WHEN operacion = 'SUB' THEN CONCAT('-','',sh.fraccionado)
                            WHEN operacion = 'ADD' THEN sh.fraccionado
                        END AS fraccionado_str,
                        CASE 
                            WHEN operacion = 'SUB' THEN 'SALIDA'
                            WHEN operacion = 'ADD' THEN 'ENTRADA'
                        END AS tipo_str,
                        IF(sh.operacion = 'ADD', sh.cantidad, 0) AS entrada,
                        IF(sh.operacion = 'SUB', sh.cantidad, 0) AS salida,
                        IF(sh.operacion = 'ADD', sh.fraccionado, 0) AS entrada_frac,
                        IF(sh.operacion = 'SUB', sh.fraccionado, 0) AS salida_frac,
                        sh.usuario,
                        sh.detalles
                    FROM stock_historial sh
                    LEFT JOIN productos p ON sh.id_producto=p.id_producto
                    WHERE 1=1 $where_fecha $where
                        ");
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
        'margin_top' => 60,
        'margin_bottom' => 15,
    ]);

    foreach ($rows as $r) {
        $fecha = $r->fecha_actual;
        $producto = $r->producto_str;
    }

    $mpdf->SetTitle("Stock");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">EXTRACTO DE PRODUCTO</td>
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
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Producto:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$producto.'</td>
                </tr>
            </thead>
        </table>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Fecha:</td>
                    <td width="60%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle; " >Usuario:</td>
                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:center;">'.$fecha.'</td>
                    <td width="60%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px; text-align:center;">'.$usuario.'</td>
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
                <tr style="background-color: #b5b5b526;">
                    <th class="tg-zqap">Fecha</th>
                    <th class="tg-zqap">Documento</th>
                    <th class="tg-zqap">Tipo</th>
                    <th class="tg-zqap">Depósito</th>
                    <th class="tg-zqap" width="11%">Cant. / Ent.</th>
                    <th class="tg-zqap" width="11%">Cant. / Sal.</th>
                    <th class="tg-zqap" width="11%">Frac. / Ent.</th>
                    <th class="tg-zqap" width="11%">Frac. / Sal.</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $fecha = $r->fecha_mov;
        $detalle = $r->detalles;
        $tipo = $r->tipo_str;
        $deposito = $r->sucursal;
        $entrada_cant = $r->entrada;
        $salida_cant = $r->salida;
        $entrada_frac = $r->entrada_frac;
        $salida_frac = $r->salida_frac;

        $total_cant_ent += $entrada_cant;
        $total_cant_sal += $salida_cant;
        $total_frac_ent += $entrada_frac;
        $total_frac_sal += $salida_frac;

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m">'.$fecha.'</td>
                <td class="tg-1x3m">'.$detalle.'</td>
                <td class="tg-1x3m">'.$tipo.'</td>
                <td class="tg-1x3m">'.$deposito.'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($entrada_cant).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($salida_cant).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($entrada_frac).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($salida_frac).'</td>
            </tr>
        ');
    }

    $mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px; text-align:left"  colspan="4">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_cant_ent).'</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_cant_sal).'</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_frac_ent).'</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_frac_sal).'</td>
                </tr>
                <tr>
                    <td class="total" style="font-size:12px; text-align:left"  colspan="4">SALDO</td>
                    <td class="total" style="text-align:right; font-size:12px" colspan="2">'.separadorMiles($total_cant_ent-$total_cant_sal).'</td>
                    <td class="total" style="text-align:right; font-size:12px" colspan="2">'.separadorMiles($total_frac_ent-$total_frac_sal).'</td>
                </tr>
            </tbody>
        </table>
    ');


    $mpdf->Output("Stock.pdf", 'I');

