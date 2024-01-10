<?php
    include ("inc/funciones.php");
    $db = DataBase::conectar();
    $usuario = $auth->getUsername();

    $premio = $_REQUEST["premio"];

    $db->setQuery("SELECT 
                                        sp.id_stock_premio, 
                                        sp.id_premio,
                                        p.premio,
                                        IFNULL(SUM(sp.stock), 0) AS stock,
                                        p.costo,
                                        p.codigo
                                        FROM premios p
                                        LEFT JOIN  stock_premios sp ON p.id_premio = sp.id_premio 
                                        GROUP BY sp.id_premio ");
    $rows = $db->loadObjectList();

    $fecha_actual = date('d/m/Y h:m');

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

    $mpdf->SetTitle("StockPremio"); 
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">INFORME DE STOCK DE PREMIOS</td>
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
                    <td  style="border: 1px solid;font-size:14px;text-align:center;" width="20%">'.$fecha_actual.'</td>
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
                    <th class="tg-zqap">Premio</th>
                    <th class="tg-zqap">Cantidad</th>
                    <th class="tg-zqap">Costo</th>
                    <th class="tg-zqap">Costo Total</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $codigo = $r->codigo;
        $premio = $r->premio;
        $cantidad = $r->stock;
        $total_cantidad += $cantidad;
        $costo = $r->costo;
        $total_costo_total= $costo*$cantidad;
        $total  +=  $total_costo_total;
        
        

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m">'.$codigo.'</td>
                <td class="tg-1x3m">'.$premio.'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($cantidad).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($costo).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($total_costo_total).'</td>
            </tr>
        ');
    }

    $mpdf->WriteHTML('
                <tr>
                   <td class="total" style="font-size:12px; text-align:left"  colspan="2">TOTAL</td>
                   <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_cantidad).'</td>
                   <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_costo_unitario).'</td>
                   <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total).'</td>
               </tr>
            </tbody>
        </table>
    ');

    
    $mpdf->Output("Stock.pdf", 'I');

