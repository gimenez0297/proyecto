<?php
	include ("inc/funciones.php");
	
	$db = DataBase::conectar();

    $id_liquidacion = $db->clearText($_REQUEST['id_liquidacion']);
    $id_funcionario = $db->clearText($_REQUEST['id_funcionario']);
	$usuario = $auth->getUsername();

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
                        CASE forma_pago WHEN 1 THEN 'EFECTIVO' WHEN 2 THEN 'CHEQUE' WHEN 3 THEN 'TRANSFERENCIA' END AS pago,
                        CASE estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Aprobado' WHEN 2 THEN 'Anulado' END AS estado_str
                    FROM liquidacion_salarios ls
                    WHERE id_liquidacion='$id_liquidacion'");
    $rows = $db->loadObject();
    // Logos
    $logo_farmacia = "dist/images/logo.png";

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        id_liquidacion,
                        concepto,
                        importe,
                        observacion
                    FROM liquidacion_salarios_ingresos
                    WHERE id_liquidacion='$id_liquidacion'");
    $ingresos = $db->loadObjectList();

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        id_liquidacion,
                        concepto,
                        importe,
                        observacion
                    FROM liquidacion_salarios_descuentos
                    WHERE id_liquidacion='$id_liquidacion'");
    $descuento = $db->loadObjectList();

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

    //foreach ($rows as $r) {
        $func = $rows->funcionario;
        $ci = $rows->ci;
        $fecha = $rows->fecha;
        $pago = $rows->pago;
   //}

    if($rows->forma_pago == 2){
        $detalle_pago = "<b>Nro. Cheque:</b> $rows->nro_cheque";
    }else if($rows->forma_pago == 3){
        $detalle_pago = "<b>Nro. Cuenta:</b> $rows->nro_cuenta";
    }else if($rows->forma_pago == 1){
        $detalle_pago = "<b>Sin detalles</b>";
    }

    $mpdf->SetTitle("Liquidación de Salario");
    $mpdf->SetHTMLHeader('
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                <td class="tc-axmw">LIQUIDACIÓN DE SALARIOS</td>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
            </tr>
        </thead>
    </table>
    <hr>
    <br>
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="12%">C.I Nro:</td>
                <td width="2%">&nbsp;</td>
                <td style="font-weight:bold;text-align:left;vertical-align:middle">Funcionario:</td>   
            </tr>
            <tr>
                <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$ci.'</td>
                <td width="2%">&nbsp;</td>
                <td  style="border: 1px solid;font-size:14px;">'.$func.'</td>
            </tr>
        </thead>
    </table>
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td  style="font-weight:bold;vertical-align:center;">Forma de Pago:</td>
                <td width="3%">&nbsp;</td>
                <td  style="font-weight:bold;vertical-align:center;" >Detalle:</td>
                <td width="2%">&nbsp;</td>
                <td  style="font-weight:bold;vertical-align:center;" >Fecha:</td>
                <td width="2%">&nbsp;</td>
                <td  style="font-weight:bold;vertical-align:center;" >Usuario:</td>
            </tr>
            <tr>
                <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$pago.'</td>
                <td width="2%">&nbsp;</td>
                <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$detalle_pago.'</td>
                <td width="2%">&nbsp;</td>
                <td  style=" border: 1px solid;padding:2px;font-size:14px;" width="11%">'.$fecha.'</td>
                <td width="2%">&nbsp;</td>
                <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:center ">'.$usuario.'</td>
                
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
        .tg .tg-tot{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle;background-color:#EAE6E5}

        .promedio{text-align:center;font-weight:bold;margin-top: 50px}
        .tabla{vertical-align: top}

    </style>
    ');

    $mpdf->writehtml('
    <br><br>
    <table style="width:100%">
        <tr>
            <td class="tabla" style="width:50%">
                <table class="tg" style="width:100%">
                    <thead>
                         <tr>
                            <th class="tg-zqap" colspan="2" style="text-align: center;background-color: #b5b5b526;">INGRESOS</th>
                         </tr>
                        <tr>
                            <th class="tg-zqap">Concepto</th>
                            <th class="tg-zqap">Importe</th>
                            
                        </tr>
                    </thead>
                <tbody>
    ');

    foreach ($ingresos as $r) {
        $concepto = $r->concepto;
        $importe = separadorMiles($r->importe);
        $observacion = $r->observacion;

        $total_sumado += quitaSeparadorMiles($importe);

        $mpdf->WriteHTML('
        <tr>
            <td class="tg-1x3m">'.$concepto.'</td>
            <td class="tg-1x3m">'.$importe.'</td>
           
        </tr>
        ');
    }

    $mpdf->WriteHTML('
        <tr>
            <td class="tg-tot" colspan="1" style="background-color: #b5b5b526;">Totales</td>
            <td class="tg-tot" style="background-color: #b5b5b526;">'.separadorMiles($total_sumado).'</td>
           
        </tr>
    ');

    $mpdf->WriteHTML('
            </tbody>
                </table>
                    </td>
                        <td class="tabla" style="width:50%">
                            <table class="tg" style="width:100%">
                                <thead>
                                <tr>
                                    <th class="tg-zqap" colspan="2" style="text-align: center;background-color: #b5b5b526;">DESCUENTOS</th>
                                 </tr>
                                <tr>
                                    <th class="tg-zqap">Concepto</th>
                                    <th class="tg-zqap">Importe</th>
                                    
                                </tr>
                            </thead>
                        <tbody>');

foreach ($descuento as $r) {
        $concepto = $r->concepto;
        $importe = separadorMiles($r->importe);
        $observacion = $r->observacion;

        $total_resta += quitaSeparadorMiles($importe);

        $mpdf->WriteHTML('
        <tr>
            <td class="tg-1x3m">'.$concepto.'</td>
            <td class="tg-1x3m">'.$importe.'</td>
            
        </tr>
        ');
    }

    $mpdf->WriteHTML('
        <tr>
            <td class="tg-tot" colspan="1" style="background-color: #b5b5b526;">Totales</td>
            <td class="tg-tot" style="background-color: #b5b5b526;">'.separadorMiles($total_resta).'</td>
          
        </tr>
    ');

    $mpdf->WriteHTML('
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        ');

    $mpdf->WriteHTML('
        <table class="tabla" style="width:100%" >
            <tbody>
                <tr>
                    <tr>
                        <td class="total" style="border: 1px solid; text-align:center; background-color: #b5b5b526;"><b>Total a cobrar:</b> '.separadorMiles($total_sumado-$total_resta).'</td>
                    </tr>
                </tr>
            </tbody>
        </table>'
    );

    $mpdf->WriteHTML('
         <table class="firma" style="width:100%">
            <tbody>
                <tr>
                    <td>_______________________________
                    <br>Firma del funcionario</td>
                </tr>
            </tbody>
        </table>
    ');

    $mpdf->Output("Liquidacion de Salarios.pdf", 'I');

