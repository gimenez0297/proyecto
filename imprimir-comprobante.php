<?php
	include ("inc/funciones.php");
    require("inc/numeros-letras.php");
	
	$db = DataBase::conectar();

    $id_comprobante = $db->clearText($_REQUEST['id_comprobante']);


    $db->setQuery("SELECT
                    DATE_FORMAT(ccd.`fecha_deposito`, '%d/%m/%Y') AS fecha_deposito,
                    ccd.`nro_comprobante`,
                    b.`banco`,
                    ccs.`cod_movimiento`,
                    ccd.`usuario`,
                    ccs.`sobrante`,
                    bc.`cuenta` AS nro_cuenta
                FROM
                    caja_chica_depositos ccd
                LEFT JOIN bancos b ON b.`id_banco`=ccd.`id_banco`
                LEFT JOIN caja_chica_sucursal ccs ON ccs.`id_caja_chica_sucursal` = ccd.`id_caja_chica_sucursal`
                LEFT JOIN bancos_cuentas bc ON bc.`id_cuenta` = ccd.`id_cuenta`
                WHERE ccd.id_caja_chica_deposito =  $id_comprobante");
    $rows = $db->loadObject();

    $nro_movimiento = $rows->cod_movimiento;
    $fecha = $rows->fecha_deposito;
    $usuario = $rows->usuario;
    $nro_cuenta = $rows->nro_cuenta;
    $nro_comprobante = $rows->nro_comprobante;
    $sobrante = $rows->sobrante;
    $banco = $rows->banco;

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
        'margin_top' => 10,
        'margin_bottom' => 5,
    ]);

  
    $mpdf->SetTitle("Comprobante de Deposito");

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
        .tg .tg-zqap{font-size:12px;text-align:left;vertical-align:middle; border-right: none; border-bottom: none}
        .tg .tg-zqll{font-size:12px;text-align:left;vertical-align:middle; border-left: none; border-bottom: none}
        .tg .tg-zqop{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle}
        .tg .tg-1x3m{text-align:left;vertical-align:middle}
        .tg .tg-wjt0{text-align:center;vertical-align:middle}
        .firma{text-align:center; font-weight: bold; margin-top: 100px}
        .tg .tg-zqmm{font-size:12px;text-align:left;vertical-align:middle; border-left: none; border-bottom: none; border-top: none}
        .tg .tg-zqzz{font-size:12px;text-align:left;vertical-align:middle; border-right: none; border-bottom: none; border-top: none}
        .tg .tg-zqcc{font-size:12px;text-align:left;vertical-align:middle; border-bottom: none; border-top: none}
        .tg .tg-zobs{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-right: none; border-top: none}
        .tg .tg-zcue{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-right: none; border-top: none; border-bottom: none}
        .tg .tg-zdes{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-right: none; border-left: none; border-top: none; border-bottom: none}
        .tg .tg-impo{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-left: none; border-top: none; border-bottom: none}
        .tg .tg-imp1{font-size:12px;text-align:left;vertical-align:middle; border-left: none; border-top: none; border-bottom: none; padding-bottom: 30px}
        .tg .tg-zde1{font-size:12px;text-align:left;vertical-align:middle; border-right: none; border-left: none; border-top: none; border-bottom: none; padding-bottom: 30px}
        .tg .tg-zcu1{font-size:12px;text-align:left;vertical-align:middle; border-right: none; border-top: none; border-bottom: none; padding-bottom: 30px}

        .tg .tg-sgua{font-size:12px;text-align:left;vertical-align:middle; border-bottom: none; border-top: none; padding-bottom: 40px}

        .tg .tg-entr{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-right: none; border-bottom: none; border-top: none; padding-bottom: 20px}
        .tg .tg-reci{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-left: none; border-bottom: none; border-top: none; padding-bottom: 20px}

        .tg .tg-aut{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-right: none; border-top: none; padding-bottom: 20px}
        .tg .tg-aut1{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle; border-left: none; border-top: none; padding-bottom: 20px}
        

        .promedio{text-align:center;font-weight:bold;margin-top: 50px}
        .tabla{vertical-align: top}

    </style>
    ');

    $mpdf->writehtml('
        <table class="tg" style="width:100%">
            <thead>
                 <tr>
                    <th class="tg-zqop" colspan="5" style="text-align: center">COMPROBANTE DE DEPOSITO</th>
                 </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="tg-zqap" colspan="3"><b>Nro° Movimiento: </b>'.$nro_movimiento.'</td>
                    <td class="tg-zqll" colspan="2"></td>
                </tr>
                <tr>
                    <td class="tg-zqzz" colspan="3"><b>Fecha y Hora del Deposito: </b> '.$fecha.'</td>
                    <td class="tg-zqmm" colspan="2"></td>
                </tr>
                <tr>
                    <td class="tg-zqcc" colspan="5"><b>Usuario: </b>'.$usuario.'</td>
                </tr>
                <tr>
                    <td class="tg-zqcc" colspan="5"><b>Banco: </b>'.$banco.'</td>
                </tr>
                <tr>
                    <td class="tg-zqcc" colspan="5"><b>Concepto: </b>Depósito de Restante de caja chica Nro° '.$nro_movimiento.'</td>
                    
                </tr>
                <tr>
                    <td class="tg-zcue" >Cuenta Origen</td>
                    <td class="tg-zdes" >Cuenta Destino</td>
                    <td class="tg-zdes" >Nro. Transferencia</td>
                    <td class="tg-zdes" >Cotizac.</td>
                    <td class="tg-impo" >Importe</td>
                </tr>
            
    ');

    $mpdf->WriteHTML('
    <tr>
        <td class="tg-zcu1">1111111111</td>
        <td class="tg-zde1">'.$nro_cuenta.'</td>
        <td class="tg-zde1">'.$nro_comprobante.'</td>
        <td class="tg-zde1">1</td>
        <td class="tg-imp1">'.separadorMiles($sobrante).'</td>
    </tr>
    ');

    $v = new EnLetras();
    $letras = $v->ValorEnLetras($sobrante, "");

    $mpdf->WriteHTML('
    <tr>
        <td class="tg-sgua" colspan="5"><b>Son guaranies: </b>'.$letras.'</td>
    </tr>
    ');

    $mpdf->WriteHTML('
    <tr>
        <td class="tg-entr" colspan="2" >Depositado por .............................................</td>
        <td class="tg-reci" colspan="3" >Firma ...............................................</td>
    </tr>
    <tr>
        <td class="tg-aut" colspan="2" >Entregado a ............................................</td>
        <td class="tg-aut1" colspan="3" >Firma  ............................................</td>
    </tr>
    ');

    $mpdf->WriteHTML('
                </tbody>
            </table>

        ');



    $mpdf->Output("Comprobante de Deposito.pdf", 'I');

