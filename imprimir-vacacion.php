<?php
	include ("inc/funciones.php");
	
	$db = DataBase::conectar();

    $id_vacacion = $db->clearText($_REQUEST['id_vacacion']);
    $id_funcionario = $db->clearText($_REQUEST['id_funcionario']);
	$usuario = $auth->getUsername();

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            id_vacacion,
                            funcionario,
                            id_funcionario,
                            ci,
                            antiguedad,
                            total_vacacion,
                            utilizado,
                            importe,
                            DATE_FORMAT(fecha_desde, '%d/%m/%Y') fecha_desde,
                            DATE_FORMAT(fecha_hasta, '%d/%m/%Y') fecha_hasta,
                            DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                            observacion,
                            anho,
                            usuario,
                            estado,
                            CASE estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' WHEN 2 THEN 'Procesado' WHEN 3 THEN 'Cerrado' END AS estado_str,
                            DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s') AS fecha
                    FROM vacaciones
                    WHERE id_vacacion='$id_vacacion'");
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
        'margin_bottom' => 5,
    ]);

    foreach ($rows as $r) {
        $func = $r->funcionario;
        $ci = $r->ci;
        $fecha = $r->fecha_actual;
    }

    $mpdf->SetTitle("Vacaciones");
    $mpdf->SetHTMLHeader('
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                <td class="tc-axmw">VACACIONES</td>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
            </tr>
        </thead>
    </table>
    <br>
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td class="tc-wa1i"><b>Fecha:</b> '.$fecha.'</td>
                <td class="tc-wa1i"><b>Usuario:</b> '.$usuario.'</td>
            </tr>
            <tr>
                <td class="tc-wa1i"><b>Funcionario:</b> '.$func.'</td>
                <td class="tc-wa1i"><b>C.I. Nro:</b> '.$ci.'</td>
            </tr>
        </thead>
    </table>

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
        .aprobado{text-align:center;margin-top: 60px}

        .promedio{text-align:center;font-weight:bold;margin-top: 50px}
        .tabla{vertical-align: top}

    </style>
    ');

    $mpdf->writehtml('
    <table class="tg" style="width:100%">
        <thead>
        <tr>
            <th class="tg-zqap">Desde</th>
            <th class="tg-zqap">Hasta</th>
            <th class="tg-zqap">Total Dias</th>
            <th class="tg-zqap">Utilizado</th>
            <th class="tg-zqap">Pendiente</th>
            <th class="tg-zqap">Importe</th>
        </tr>
    </thead>
    <tbody>
    ');

    foreach ($rows as $r) {
        $desde = $r->fecha_desde;
        $hasta = $r->fecha_hasta;
        $total = $r->total_vacacion;
        $utilizado = $r->utilizado;
        $saldo = $total-$utilizado;
        $importe = $r->importe;

        $total_v += quitaSeparadorMiles($importe);

        $mpdf->WriteHTML('
        <tr>
            <td class="tg-1x3m">'.$desde.'</td>
            <td class="tg-1x3m">'.$hasta.'</td>
            <td class="tg-1x3m" style="text-align: right">'.$total.'</td>
            <td class="tg-1x3m" style="text-align: right">'.$utilizado.'</td>
            <td class="tg-1x3m" style="text-align: right">'.$saldo.'</td>
            <td class="tg-1x3m" style="text-align: right">'.separadorMiles($importe).'</td>
        </tr>
        ');
    }

    $mpdf->WriteHTML('
        <tr>
            <td class="tg-zqap" colspan="5">Totales</td>
            <td class="tg-1x3m" style="text-align: right">'.separadorMiles($total_v).'</td>
          
        </tr>
    ');

    $mpdf->WriteHTML('
            </tbody>
        </table>
        ');

     $mpdf->WriteHTML('
         <table class="aprobado" style="width:100%">
            <tbody>
               <tr>
                    <td>_______________________________
                    <br>Autorizado por</td>
                    <td>_______________________________
                    <br>Aprobado por</td>
                </tr>
            </tbody>
        </table>
    ');

    /*$mpdf->WriteHTML('
         <table class="firma" style="width:100%">
            <tbody>
                <tr>
                    <td>_______________________________
                    <br>Firma del funcionario</td>
                </tr>
            </tbody>
        </table>
    ');*/

    $mpdf->Output("Vacaciones.pdf", 'I');

