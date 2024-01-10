<?php
    include ("inc/funciones.php");
    $db                     = DataBase::conectar();
    $usuario                = $auth->getUsername();
    $desde 		            = $_REQUEST["desde"];
    $hasta 		            = $_REQUEST["hasta"];
    $id_sucursal            = $_REQUEST["sucursal"];
    $reporte                = $_REQUEST["reporte"];
    $columnas               = json_decode($_REQUEST["columnas"],true);

    $fecha_actual = date('d/m/Y h:m');
   
    $fecha_actual = date('d/m/Y h:m');

    if(empty($desde)) {
        $desde        = '2021-01-01';
        $hasta        = date('Y-m-d');
    }
   
    if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        if ($reporte==1) {
            $tittle = "GASTOS";
            $and_id_sucursal .= " AND g.id_sucursal=$id_sucursal";
        }else{
            $tittle = "COMPRAS";
            $and_id_sucursal .= " AND f.id_sucursal=$id_sucursal";
        }
        $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
        $row      = $db->loadObject();
        $sucursal = $row->sucursal;
    }else{
        if ($reporte==1) {
            $tittle = "Gastos";
        }else{
            $tittle = "Compras";
        }
        $and_id_sucursal .= "";
        $sucursal = "TODOS";
    };

    if ($reporte == 1) {
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
        g.ruc,
        g.razon_social,
        g.timbrado,
        g.documento,
        g.gravada_10,
        g.gravada_5,
        g.exenta,
        g.monto,
        DATE_FORMAT(g.fecha_emision,'%d/%m/%Y') AS fecha_emision,
        CASE g.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion   
        FROM gastos g
        LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
        WHERE DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta' $and_id_sucursal");
        $rows = $db->loadObjectList();
    }else{
        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS   
        f.total_venta AS monto, 
        f.razon_social,
        f.ruc,
        f.gravada_10,
        f.gravada_5,
        f.exenta,
        t.timbrado,
        CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',f.numero) AS documento,
        DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha_emision,
        CASE f.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion
        FROM facturas f
        LEFT JOIN timbrados t ON t.id_timbrado = f.id_timbrado
        WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' $and_id_sucursal");
        $rows = $db->loadObjectList();
    }
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

    $mpdf->SetTitle("Total ". $tittle);
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Total '.$tittle.'</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="13%">Fecha Desde:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="13%">Fecha Hasta:</td>   
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Sucursal:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$sucursal.'</td>
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
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$fecha_actual.'</td>
                    <td width="60%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px; text-align:left;">'.$usuario.'</td>
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

    if (in_array('fecha_emision',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Fecha Emision</th>');
    }
    if (in_array('ruc',$columnas)) {
    $mpdf->writehtml('<th class="tg-zqap">RUC</th>');
    }
    if (in_array('razon_social',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Razon Social</th>');
    }
    if (in_array('timbrado',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Timbrado</th>');
    }
    if (in_array('documento',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Documento</th>');
    }
    if (in_array('gravada_10',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Gravada 10</th>');
    }
    if (in_array('gravada_5',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Gravada 5</th>');
    }
    if (in_array('exenta',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Exenta</th>');
    }
    if (in_array('monto',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Monto</th>');
    }
    if (in_array('condicion',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Condicion</th>');
    }
    $mpdf->writehtml('
                </tr>
            </thead>
        <tbody>'
    );

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $fecha_emision  = $r->fecha_emision;
        $ruc            = $r->ruc;
        $razon_social   = $r->razon_social;
        $timbrado       = $r->timbrado;
        $documento      = $r->documento;
        $gravada_10     = $r->gravada_10;
        $gravada_5      = $r->gravada_5;
        $exenta         = $r->exenta;
        $monto          = $r->monto;
        $condicion      = $r->condicion;
        // $total_cant     += $cantidad;
        // $total          += $total_venta;

        $mpdf->WriteHTML('<tr>');

        if (in_array('fecha_emision',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$fecha_emision.'</td>');
        }
        if (in_array('ruc',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$ruc.'</td>');
        }
        if (in_array('razon_social',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$razon_social.'</td>');
        }
        if (in_array('timbrado',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$timbrado.'</td>');
        }
        if (in_array('documento',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$documento.'</td>');
        }
        if (in_array('gravada_10',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.separadorMiles($gravada_10).'</td>');
        }
        if (in_array('gravada_5',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.separadorMiles($gravada_5).'</td>');
        }
        if (in_array('exenta',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.separadorMiles($exenta).'</td>');
        }
        if (in_array('monto',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.separadorMiles($monto).'</td>');
        }
        if (in_array('condicion',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$condicion.'</td>');
        }

        $mpdf->WriteHTML('</tr>');
    }

    $mpdf->WriteHTML('
            </tbody>
        </table>
    ');

    $mpdf->Output("TotalCompraVenta.pdf", 'I');

