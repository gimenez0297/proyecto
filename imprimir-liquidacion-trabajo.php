<?php
	include ("inc/funciones.php");
	
	$db = DataBase::conectar();

    $id_liquidacion = $db->clearText($_REQUEST['id_liquidacion']);
    $id_funcionario = $db->clearText($_REQUEST['id_funcionario']);
	$usuario = $db->clearText($_POST["usuario"]);

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        ls.id_liquidacion,
                        id_funcionario,
                        DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha,
                        funcionario,
                        ci,
                        neto_cobrar,
                        periodo,
                        estado,
                        usuario,
                        nro_cheque,
                        nro_cuenta,
                        forma_pago,
                        periodo,
                        IFNULL((SELECT importe FROM liquidacion_salarios_ingresos WHERE id_liquidacion=ls.id_liquidacion and concepto='COMISIÓN'),0) as comision,
                        CASE forma_pago WHEN 1 THEN 'EFECTIVO' WHEN 2 THEN 'CHEQUE' WHEN 3 THEN 'TRANSFERENCIA' END AS pago,
                        CASE estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Aprobado' WHEN 2 THEN 'Anulado' END AS estado_str
                    FROM liquidacion_salarios ls
                    WHERE id_liquidacion='$id_liquidacion'");
    $rows = $db->loadObject();
    // Logos
    $logo_farmacia = "dist/images/mtess.png";

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        id_liquidacion,
                        concepto,
                        coalesce(sum(importe),0) as importe,
                        observacion
                    FROM liquidacion_salarios_ingresos
                    WHERE id_liquidacion='$id_liquidacion' AND concepto != 'NORMAL'");
    $ingresos = $db->loadObject();

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        id_liquidacion,
                        concepto,
                        coalesce(sum(importe),0) as importe,
                        observacion
                    FROM liquidacion_salarios_descuentos
                    WHERE id_liquidacion='$id_liquidacion' AND concepto != 'I.P.S 9%'");
    $descuento = $db->loadObject();

    $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario='$id_funcionario'");
    $fu = $db->loadObject();

    $db->setQuery("SELECT * FROM salario_minimo");
    $salario = $db->loadObject();

    $db->setQuery("SELECT * FROM configuracion WHERE estado = 1");
    $nro_patronal = $db->loadObject();

    // MPDF
    require_once __DIR__ . '/mpdf/vendor/autoload.php';

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 5,
        'margin_right' => 5,
        'margin_top' => 55,
        'margin_bottom' => 5,
    ]);

    //foreach ($rows as $r) {
        $func = $rows->funcionario;
        $ci = $rows->ci;
        $fecha = $rows->fecha;
        $pago = $rows->pago;
        $periodo = $rows->periodo;
        $ips = $fu->salario_real*0.09;
        if ($fu->aporte != 1){
            $ips = 0;
        }
        $cant_hijo = $fu->cantidad_hijos;
        $bonificacion = ($salario->monto *0.05*$cant_hijo);
   //}

    if($rows->forma_pago == 2){
        $detalle_pago = "<b>Nro. Cheque:</b> $rows->nro_cheque";
    }else if($rows->forma_pago == 3){
        $detalle_pago = "<b>Nro. Cuenta:</b> $rows->nro_cuenta";
    }

    $mpdf->SetTitle("Liquidación de Salario");
    $mpdf->SetHTMLHeader('
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="80"></td>
                <td class="tc-axmw">LIQUIDACIÓN DE SALARIOS<span class="confor"><br><b>(Art. 236 del C. de Trabajo)</b></span></td>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="80" style="visibility:hidden"></td>
            </tr>
        </thead>
    </table>
    <br>
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td class="tc-wa1i" width="65%"><b>Empleador:</b> FARMACIA SANTA VICTORIA</td>
                <td class="tc-wa1i"><b>Nro. Patronal:</b> '.$nro_patronal->numero_patronal.'</td>
            </tr>
            <tr>
                <td class="tc-wa1i"><b>Nombre del Empleado:</b> '.$func.'</td>
            </tr>
            <tr>
                <td class="tc-wa1i"><b>Periodo de Pago:</b> 01 al 30 de '.$periodo.'</td>
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
        .confor{font-size:11px;text-align:center;vertical-align:middle;padding:3px 2px;border-spacing:0;}

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
        .tg .tg-1x3m{text-align:right;vertical-align:middle}
        .tg .tg-wjt0{text-align:center;vertical-align:middle}
        .firma{text-align:center; font-weight: bold; margin-top: 60px}
        .tg .tg-bon{font-size:12px;font-weight:bold;text-align:right;vertical-align:middle}

        .promedio{text-align:center;font-weight:bold;margin-top: 50px}
        .tabla{vertical-align: top}

    </style>
    ');

    $mpdf->writehtml('

                <table class="tg" style="width:100%">
                    <thead>
                         <tr>
                            <th class="tg-zqap" colspan="6" style="text-align: center">REMUNERACIÓN</th>
                            <th class="tg-zqap" colspan="3" style="text-align: center">DEDUCCIONES</th>
                         </tr>
                        <tr>
                            <th class="tg-zqap">Dias Trab.</th>
                            <th class="tg-zqap">Salario Básico</th>
                            <th class="tg-zqap">Hs. Extras</th>
                            <th class="tg-zqap">Comisiones</th>
                            <th class="tg-zqap">Otros ingresos</th>
                            <th class="tg-zqap">TOTAL (1)</th>
                            <th class="tg-zqap">I.P.S. 9%</th>
                            <th class="tg-zqap">Otros Descuentos</th>
                            <th class="tg-zqap">TOTAL (2)</th>
                        </tr>
                    </thead>
                <tbody>

    ');


    $mpdf->WriteHTML('
            <tr>
                <td class="tg-wjt0">27</td>
                <td class="tg-1x3m">'.separadorMiles($fu->salario_real).'</td>
                <td class="tg-1x3m">0</td>
                <td class="tg-1x3m">'.separadorMiles($rows->comision).'</td>
                <td class="tg-1x3m">'.separadorMiles($ingresos->importe).'</td>
                <td class="tg-1x3m">'.separadorMiles($ingresos->importe+$fu->salario_real).'</td>
                <td class="tg-1x3m">'.separadorMiles($ips).'</td>
                <td class="tg-1x3m">'.separadorMiles($descuento->importe).'</td>
                <td class="tg-1x3m">'.separadorMiles($descuento->importe + $ips).'</td>
            </tr>
            <tr>
                <td class="tg-bon" colspan="8" rowspan="2">Bonificación Familiar (3)<br><br><b>NETO A COBRAR 1-2+3</b></td>
                <td class="tg-1x3m">'.separadorMiles($bonificacion).'</td>
            </tr>
            <tr>
                <td class="tg-1x3m">'.separadorMiles($rows->neto_cobrar + $bonificacion).'</td>
            </tr>
        </tbody>
    </table>
    ');

    $mpdf->WriteHTML('
         <table class="tc" style="width:100%; margin-top: 10px">
            <tbody>
                <tr>
                    <td class="tc-wa1i"><b>Fecha:</b> '.$fecha.'</td>
                </tr>
            </tbody>
        </table>
    ');

    $mpdf->WriteHTML('
         <table class="firma" style="width:100%">
            <tbody>
                <tr>
                    <td>_______________________________
                    <br>Firma del Empleador</td>
                    <td>_______________________________
                    <br>Firma del Empleado</td>
                </tr>
            </tbody>
        </table>
    ');

    $mpdf->Output("Liquidacion de Salarios.pdf", 'I');

