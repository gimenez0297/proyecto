<?php
	include ("inc/funciones.php");
    require("inc/numeros-letras.php");
	
	$db = DataBase::conectar();

    $id_pago = $db->clearText($_REQUEST['id_pago']);
    $id_funcionario = $db->clearText($_REQUEST['id_funcionario']);
	$usuario = $db->clearText($_POST["usuario"]);

    $db->setQuery("SELECT 
                        id_pago,
                        DATE_FORMAT(op.fecha, '%d/%m/%Y %H:%i:%s') AS fecha,
                        concepto,
                        op.id_banco,
                        op.id_proveedor,
                        op.id_funcionario,
                        bc.cuenta AS nro_cuenta,
                        op.nro_cheque,
                        op.id_moneda,
                        op.numero,
                        op.monto,
                        op.usuario,
                        op.destino_pago,
                        op.cuenta_destino,
                        op.observacion
                    FROM orden_pagos op
                    LEFT JOIN monedas m ON op.id_moneda=m.id_moneda
                    LEFT JOIN proveedores p ON op.id_proveedor=p.id_proveedor
                    LEFT JOIN funcionarios f ON op.id_funcionario=f.id_funcionario
                    LEFT JOIN bancos_cuentas bc ON op.nro_cuenta=bc.id_cuenta
                    WHERE id_pago='$id_pago'");
    $rows = $db->loadObject();

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

    //foreach ($rows as $r) {
        $func = $rows->funcionario;
        $ci = $rows->ci;
        $fecha = $rows->fecha;
        $pago = $rows->pago;
        $destino = $rows->destino_pago;
   //}

    if($rows->forma_pago == 2){
        $detalle_pago = "<b>Nro. Cheque:</b> $rows->nro_cheque";
    }else if($rows->forma_pago == 3){
        $detalle_pago = "<b>Nro. Cuenta:</b> $rows->nro_cuenta";
    }

    $mpdf->SetTitle("Orden de Pago");

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
                    <th class="tg-zqop" colspan="5" style="text-align: center">ORDEN DE PAGO</th>
                 </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="tg-zqap" colspan="3"><b>Nro Reg: </b>'.$rows->numero.'</td>
                    <td class="tg-zqll" colspan="2"><b>Moneda Orig.: </b> Guaranies</td>
                </tr>
                <tr>
                    <td class="tg-zqzz" colspan="3"><b>Fecha y Hora: </b> '.$rows->fecha.'</td>
                    <td class="tg-zqmm" colspan="2"><b>Moneda Dest.: </b> Guaranies</td>
                </tr>
                <tr>
                    <td class="tg-zqcc" colspan="5"><b>Usuario: </b>'.$rows->usuario.'</td>

                </tr>
                <tr>
                    <td class="tg-zqcc" colspan="5"><b>Concepto: </b>'.$rows->concepto.'</td>
                    
                </tr>
                <tr>
                    <td class="tg-zqcc" colspan="5" style="padding-bottom: 50px"><b>Observación: </b>'.$rows->observacion.'</td>
                    
                </tr>
                <tr>
                    <td class="tg-zcue" >Cuenta Origen</td>
                    <td class="tg-zdes" >Cuenta Destino</td>
                    <td class="tg-zdes" >Nro. Cheque</td>
                    <td class="tg-zdes" >Cotizac.</td>
                    <td class="tg-impo" >Importe</td>
                </tr>
            
    ');

    $mpdf->WriteHTML('
    <tr>
        <td class="tg-zcu1">'.$rows->nro_cuenta.'</td>
        <td class="tg-zde1">'.$rows->cuenta_destino.'</td>
        <td class="tg-zde1">'.$rows->nro_cheque.'</td>
        <td class="tg-zde1">1</td>
        <td class="tg-imp1">'.separadorMiles($rows->monto).'</td>
    </tr>
    ');

    $v = new EnLetras();
    $letras = $v->ValorEnLetras($rows->monto, "");

    $mpdf->WriteHTML('
    <tr>
        <td class="tg-sgua" colspan="5"><b>Son guaranies: </b>'.$letras.'</td>
    </tr>
    ');

    $mpdf->WriteHTML('
    <tr>
        <td class="tg-entr" colspan="2" >Entregado por .............................................</td>
        <td class="tg-reci" colspan="3" >Recibido por ...............................................</td>
    </tr>
    <tr>
        <td class="tg-aut" colspan="2" >Autorizado por ............................................</td>
        <td class="tg-aut1" colspan="3" >V° B° Gerencia ............................................</td>
    </tr>
    ');

    $mpdf->WriteHTML('
                </tbody>
            </table>

        ');



    $mpdf->Output("Orden de pago.pdf", 'I');

