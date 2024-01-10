<?php
    include ("inc/funciones.php");
    $db                = DataBase::conectar();
    $usuario           = $auth->getUsername();
    $desde 		       = $_REQUEST["desde"];
    $hasta 		       = $_REQUEST["hasta"];
    $id_sucursal       = $_REQUEST["sucursal"];
    $columnas          = json_decode($_REQUEST["columnas"],true);

    $fecha_actual = date('d/m/Y h:m');
   
    $fecha_actual = date('d/m/Y h:m');

    if(empty($desde)) {
        $desde        = '2021-01-01';
        $hasta        = date('Y-m-d');
    }
   
    if (!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        $and_id_sucursal .= " AND f.id_sucursal= $id_sucursal";
        $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
        $row     = $db->loadObject();
        $sucursal = $row->sucursal;
    }else{
        $and_id_sucursal .= "";
        $sucursal = "TODOS";
    }

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
            fecha_venta,
            DATE_FORMAT(fecha_venta, '%d/%m/%Y') AS fecha,
            ruc,
            id_cliente,
            razon_social,
            SUM(cantidad) AS cantidad,
            SUM(total_venta) AS total

            FROM facturas 
            WHERE estado != 2 AND DATE(fecha_venta) BETWEEN '$desde' AND '$hasta' $and_id_sucursal
            GROUP BY id_cliente");
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
         'margin_top' => 60,
         'margin_bottom' => 15,
     ]);

    $mpdf->SetTitle("Ranking de Clientes");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Ranking de Clientes</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="13%">Fecha Desde:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="13%">Fecha Hasta:</td>   
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Sucursal:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$sucursal.'</td>
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
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$fecha_actual.'</td>
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
                <tr style="background-color: #b5b5b526;">'
    );

    if (in_array('ruc',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">RUC</th>');
    }
    if (in_array('razon_social',$columnas)) {
    $mpdf->writehtml('<th class="tg-zqap">Razón Social</th>');
    }
    if (in_array('fecha',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Fecha</th>');
    }
    if (in_array('cantidad',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Cantidad</th>');
    }
    if (in_array('total',$columnas)) {
        $mpdf->writehtml('<th class="tg-zqap">Total</th>');
    }
    $mpdf->writehtml('
                </tr>
            </thead>
        <tbody>'
    );

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $ruc            = $r->ruc;
        $razon_social   = $r->razon_social;
        $fecha          = $r->fecha;
        $cantidad       = $r->cantidad;
        $total_venta    = $r->total;
        $total_cant     += $cantidad;
        $total          += $total_venta;

        $mpdf->WriteHTML('<tr>');

        if (in_array('ruc',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$ruc.'</td>');
        }
        if (in_array('razon_social',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$razon_social.'</td>');
        }
        if (in_array('fecha',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$fecha.'</td>');
        }
        if (in_array('cantidad',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.$cantidad.'</td>');
        }
        if (in_array('total',$columnas)) {
            $mpdf->writehtml('<td class="tg-1x3m" style="text-align:center;">'.separadorMiles($total).'</td>');
        }

        $mpdf->WriteHTML('</tr>');
    }

    $mpdf->WriteHTML('
            </tbody>
        </table>
    ');

    $mpdf->Output("RankingClientes.pdf", 'I');

