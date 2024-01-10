<?php
    include ("inc/funciones.php");
     $db       = DataBase::conectar();
     $usuario  = $auth->getUsername();
     $periodo  = $db->clearText($_REQUEST['periodo']);
     $id       = $db->clearText($_REQUEST['id_funcionario']);

     
      $fecha_actual = date('d/m/Y h:m'); 
       
        $db->setQuery("SELECT 
                            UPPER(ca.mes) AS mes,
                            ca.ano,
                            (
                            SELECT ROUND(IFNULL(SUM(lsi.importe), 0),0) AS total
                            FROM liquidacion_salarios ls
                            JOIN liquidacion_salarios_ingresos lsi ON lsi.id_liquidacion = ls.id_liquidacion
                            WHERE CONCAT(UPPER(ca.mes), ' - ', ca.ano) = ls.periodo AND ls.id_funcionario = $id
                            ) AS total
                             FROM calendario ca
                             WHERE ano = $periodo
                             GROUP BY mes_nro");
    $rows = $db->loadObjectList();

    $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id");

    $func = $db->loadObject();
    $funcionario = $func->funcionario;

   
  

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

    $mpdf->SetTitle("Reporte Aguinaldos ". $tittle);
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Detalle de Pago</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="13%">Periodo:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Funcionario:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.($periodo).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$funcionario.'</td>
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
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$fecha_actual.'</td>
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
                <tr style="background-color: #b5b5b526;">
                   <th class="tg-zqap">Mes</th>
                   <th class="tg-zqap">Año</th>
                   <th class="tg-zqap">Total</th>
                  


                </tr>
            </thead>
        <tbody>
    ');

   

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $mes    = $r->mes;
        $ano    = $r->ano;
        $total  = $r->total;
      

        // $total_cant     += $cantidad;
        // $total          += $total_venta;

        $mpdf->WriteHTML('
            <tr>
               <td class="tg-1x3m" style="text-align:left;">'.$mes.'</td>
               <td class="tg-1x3m" style="text-align:right;">'. ($ano) .'</td>
               <td class="tg-1x3m" style="text-align:right;">'. separadorMiles($total) .'</td>
           

            </tr>
        ');

        
    }

    $mpdf->WriteHTML('
            </tbody>
        </table>
    ');

    $mpdf->Output("Detalle Sueldos.pdf", 'I');

