<?php
include("inc/funciones.php");
$db = DataBase::conectar();

$id_proveedor = $db->clearText($_REQUEST['id_proveedor']);
$usuario = $auth->getUsername();

// Logos
$logo_farmacia = "dist/images/logo.png";

$db->setQuery("SELECT a.*,
                    CASE 
                        WHEN pagado IS NULL THEN 'Pendiente'
                        WHEN total_costo = pagado THEN 'Pagado'
                        WHEN total_costo > pagado THEN 'Pagado Parcial'
                    END AS estado_str

                 FROM (SELECT 
                            rc.id_recepcion_compra,
                            rc.id_proveedor,
                            rc.numero_documento,
                            CASE rc.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion_str,
                            p.proveedor,
                            rc.total_costo,
                            DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                            IFNULL(pagado_hasta_ahora,0) AS pagado,
                            (rc.total_costo - IFNULL(pagado_hasta_ahora,0)) AS saldo,
                            IFNULL(DATE_FORMAT(rc.fecha, '%d/%m/%Y'),0) AS fecha_rec,
                            IFNULL(DATE_FORMAT(rcv.vencimiento, '%d/%m/%Y'),0) AS vencimiento
                        FROM recepciones_compras rc
                        LEFT JOIN proveedores p ON p.id_proveedor = rc.id_proveedor
                        LEFT JOIN recepciones_compras_vencimientos rcv ON rcv.id_recepcion_compra= rc.id_recepcion_compra
                        LEFT JOIN (SELECT ops.*,SUM(ops.monto) AS pagado_hasta_ahora FROM `orden_pagos_proveedores` ops JOIN orden_pagos op ON ops.id_pago=op.id_pago
                        GROUP BY id_factura) opp ON opp.id_factura = rc.id_recepcion_compra
                        WHERE p.id_proveedor=$id_proveedor
                        GROUP BY rc.id_recepcion_compra
                    )a");
$detalle = $db->loadObjectList();


$db->setQuery("SELECT * FROM proveedores WHERE id_proveedor=$id_proveedor");
$pro = $db->loadObject();

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

foreach ($detalle as $r) {
    $fecha = $r->fecha_actual;
}

$mpdf->SetTitle("Extracto Proveedor");
$mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70"></td>
                    <td class="tc-axmw">EXTRACTOS PROVEEDORES</td>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Fecha:</td>
                    <td width="70%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Usuario:</td>
                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$fecha.'</td>
                    <td width="70%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$usuario.'</td>
                    
                </tr>
            </thead>
        </table>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Proveedor:</td>
                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$pro->proveedor.'</td>
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
        .tc  {border-collapse:collapse;border-spacing:0; }
        .tc td{border-color:black;font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:0px;word-break:normal;margin: 20px;}
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
        .total{text-align:center; font-weight: bold; text-align:center;background-color: #b5b5b526;}
        .aprobado{margin-top: 60px;}
        .firma{text-align:center;margin-top: 30px; font-weight: none}

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
                    <th class="tg-zqap">Condición</th>
                    <th class="tg-zqap">Vencimiento</th>
                    <th class="tg-zqap">Total</th>
                    <th class="tg-zqap">Pagado</th>
                    <th class="tg-zqap">Saldo</th>
                </tr>
            </thead>
        <tbody>

    ');

$coun = 0;
foreach ($detalle as $r) {
    $coun++;
    $fecha = $r->fecha_rec;
    $documento = $r->numero_documento;
    $condicion = $r->condicion_str;
    $vencimiento = $r->vencimiento;
    $total = $r->total_costo;
    $pagado = $r->pagado;
    $saldo = $r->saldo;

    $total_sumado += $total;
    $total_pagado += $pagado;
    $total_saldo  += $saldo;

    $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:left">' . $fecha . '</td>
                <td class="tg-1x3m">' . $documento . '</td>
                <td class="tg-1x3m">' . $condicion . '</td>
                <td class="tg-1x3m">' . $vencimiento . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($total) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($pagado) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($saldo) . '</td>
            </tr>
        ');
}

$mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px" colspan="4">' . separadorMiles($total_sumado) . '</td>
                    <td class="total" style="text-align:right; font-size:12px">' . separadorMiles($total_pagado) . '</td>
                    <td class="total" style="text-align:right; font-size:12px">' . separadorMiles($total_saldo) . '</td>
                </tr>
            </tbody>
        </table>
    ');

$mpdf->WriteHTML('
         <table class="aprobado" style="width:100%">
            <tbody>
               <tr>
                    <td style="padding-top:5px">Autorizado por:___________________________________________________________</td>
                    <td style="padding-top:5px;">Firma:_______________________________</td>
                </tr>
                <tr>
                    <td style="padding-top:10px; padding-bottom:5px">Aprobado por:____________________________________________________________</td>
                    <td style="padding-top:10px; padding-bottom:5px">Firma:_______________________________</td>
                </tr>
            </tbody>
        </table>
    ');

$mpdf->Output("Ordenes Compras.pdf", 'I');
