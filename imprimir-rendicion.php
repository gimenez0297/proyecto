<?php
    include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $id_caja 	  = $_REQUEST["id_caja"];

    $fecha_actual = date('d/m/Y h:i');

    $db->setQuery("SELECT * FROM caja_chica_sucursal WHERE id_caja_chica_sucursal = $id_caja");
    $caja_c = $db->loadObject();
    $cod_movimiento = $caja_c->cod_movimiento;
    $id_cc_configuracion = $caja_c->id_caja_chica;

    $db->setQuery("SELECT * FROM caja_chica WHERE id_caja_chica = $id_cc_configuracion");
    $cc_configuracion = $db->loadObject();
    $id_sucursal = $cc_configuracion->id_sucursal;

    $db->setQuery("SELECT * FROM sucursales WHERE id_sucursal = $id_sucursal");
    $sucursal = $db->loadObject();
    $nombre_sucursal = $sucursal->sucursal;


    $db->setQuery("SELECT  
                    ccf.`id_caja_chica_facturas`,
                    g.id_gasto,
                    g.`nro_gasto`,
                    DATE_FORMAT(g.`fecha_emision`, '%d/%m/%Y') AS fecha_emision,
                    CASE
                    WHEN g.deducible = 1
                    THEN g.ruc
                    WHEN g.deducible = 2
                    THEN 'S/R'
                    END AS ruc,
                    CASE
                    WHEN g.deducible = 1
                    THEN g.razon_social
                    WHEN g.deducible = 2
                    THEN 'S/N'
                    END AS razon_social,
                    g.concepto,
                    g.monto,
                    CASE
                    g.`deducible`
                    WHEN 1
                    THEN 'DEDUCIBLE'
                    WHEN 2
                    THEN 'NO DEDUCIBLE'
                    END AS deducible
                FROM
                    caja_chica_facturas ccf
                    LEFT JOIN gastos g
                    ON ccf.`id_gasto` = g.`id_gasto`
                    WHERE ccf.id_caja_chica_sucursal = $id_caja");
    $gastos = $db->loadObjectList();

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

    $mpdf->SetTitle("Rendicion de Caja Chica");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Rendicion de Caja</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="18%">Nro.° Movimiento </td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Sucursal</td>   
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Fecha:</td>   
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Usuario:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;">'.$cod_movimiento.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$nombre_sucursal.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$fecha_actual.'</td>
                    <td width="2%">&nbsp;</td>
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
                    <th class="tg-zqap">Nro.° Gasto</th>
                    <th class="tg-zqap">Fecha</th>
                    <th class="tg-zqap">Tipo Gasto</th>
                    <th class="tg-zqap">RUC</th>
                    <th class="tg-zqap">Razon Social/Nombre</th>
                    <th class="tg-zqap">Concepto</th>
                    <th class="tg-zqap">Monto</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($gastos as $g) {
        $coun++;
        $nro_gasto          = $g->nro_gasto;
        $fecha              = $g->fecha_emision;
        $ruc                = $g->ruc;
        $razon_social       = $g->razon_social;
        $concepto           = $g->concepto;
        $total              = $g->monto;
        $deducible          = $g->deducible;
        $total_total       += $total;
        

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:center;">'.$nro_gasto.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$fecha.'</td>
                <td class="tg-1x3m">'.$deducible.'</td>
                <td class="tg-1x3m" >'.$ruc.'</td>
                <td class="tg-1x3m">'.$razon_social.'</td>
                <td class="tg-1x3m">'.$concepto.'</td>
                <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($total).'</td>
                
            </tr>
        ');
    }


    $mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px; text-align:left"  colspan="6">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_total).'</td>
                </tr>
            </tbody>
        </table>
    ');


$mpdf->Output("RankingClientes.pdf", 'I');

