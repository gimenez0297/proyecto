<?php
    include ("inc/funciones.php");
    $db = DataBase::conectar();
    $usuario = $auth->getUsername();
    $where = '';
    $where_fecha = '';

    $id_premio =$db->clearText($_REQUEST["id_premio"]);
    $desde = $db->clearText($_REQUEST['desde']);
    $hasta = $db->clearText($_REQUEST['hasta']);

    if(!empty($desde) && !empty($hasta)) {
        $where_fecha = " AND DATE(sph.fecha) BETWEEN '$desde' AND '$hasta'";
    }

    if(!empty($id_premio) && intVal($id_premio) != 0) {
        $where .= " AND sph.id_premio =$id_premio";
    }

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                            sph.id_stock_premios_historial, 
                            sph.id_premio,
                            sph.premio,
                            sph.operacion,
                            sph.origen,
                            sph.detalles,
                            p.codigo,
                            DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                            CASE 
                                WHEN sph.operacion = 'SUB' THEN 'SALIDA'
                                WHEN sph.operacion = 'ADD' THEN 'ENTRADA'
                            END AS operacion_str,
                            IF(sph.operacion = 'ADD', sph.cantidad, 0) AS entrada,
                            IF(sph.operacion = 'SUB', sph.cantidad, 0) AS salida,
                            DATE_FORMAT(sph.fecha,'%d/%m/%Y') AS fecha 
                            FROM stock_premios_historial sph  
                            JOIN premios p ON  sph.id_premio = p.id_premio
                            WHERE 1=1 $where_fecha $where
                            ORDER BY sph.fecha DESC");
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
        'margin_top' => 60,
        'margin_bottom' => 15,
    ]);

    foreach ($rows as $r) {
        $fecha = $r->fecha_actual;
        $premio = $r->premio;
    }

    $mpdf->SetTitle("Stock Premio"); 
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">REPORTE MOVIMIENTO STOCK</td>
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
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Premio:</td>     
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$premio.'</td>
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
        .tf td{border-color:black`;font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:0px;word-break:normal;}
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
                    <th class="tg-zqap">Fecha</th>
                    <th class="tg-zqap">Documento</th>
                    <th class="tg-zqap">Tipo</th>
                    <th class="tg-zqap">Entrada</th>
                    <th class="tg-zqap">Salida</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $fecha = $r->fecha;
        $detalles = $r->detalles;
        $operacion_str = $r->operacion_str;
        $entrada_cant = $r->entrada;
        $salida_cant = $r->salida;

        
        $total_cant_ent += $entrada_cant;
        $total_cant_sal += $salida_cant;


        $mpdf->WriteHTML('
                <tr>
                    <td class="tg-1x3m">'.$fecha.'</td>
                    <td class="tg-1x3m">'.$detalles.'</td>
                    <td class="tg-1x3m">'.$operacion_str.'</td>
                    <td class="tg-1x3m" style="text-align:right">'.separadorMiles($entrada_cant).'</td>
                    <td class="tg-1x3m" style="text-align:right">'.separadorMiles($salida_cant).'</td>
                </tr>
            ');
    }

    $mpdf->WriteHTML('
                    <tr>
                        <td class="total" style="font-size:12px; text-align:left"  colspan="3">TOTAL</td>
                        <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_cant_ent).'</td>
                        <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_cant_sal).'</td>
                    </tr>
                    <tr>
                        <td class="total" style="font-size:12px; text-align:left"  colspan="3">SALDO</td>
                        <td class="total" style="text-align:right; font-size:12px" colspan="2">'.separadorMiles($total_cant_ent-$total_cant_sal).'</td>
                    </tr>   
            </tbody>
        </table>
    ');


    $mpdf->Output("StockPremio.pdf", 'I');

