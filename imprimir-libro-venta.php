<?php
    include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $desde 		  = $db->clearText($_POST["desde"]);
    $hasta 		  = $db->clearText($_POST["hasta"]);
	$estado       = $db->clearText($_POST['estado']);
	$id_cliente   = $db->clearText($_POST['id_cliente']);

    $fecha_actual = date('d/m/Y h:m');

    if($estado == 3 ) {
        $where .="";
    }else{
        $where .= "AND f.estado = $estado";
    }


    if(empty($desde)) {
        $where_fecha .= "WHERE f.fecha_venta BETWEEN '2022-01-01' AND NOW()";
        $desde        = '2021-01-01';
        $hasta        = date('Y-m-d');
    }else{
        $where_fecha .=" WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta'";
    };

    if(!empty($id_cliente)) {
        $where_id_cliente .= " AND f.id_cliente=$id_cliente";
        $db->setQuery("SELECT * FROM clientes WHERE id_cliente =$id_cliente");
        $row     = $db->loadObject();
        $cliente = $row->razon_social;
    }else{
        $where_id_cliente .="";
        $cliente           = "Todos";
    };

    

    if($estado == 0) {$estado = "Pendiente";}
    if($estado == 1) {$estado = "Pagado";}
    if($estado == 2) {$estado = "Anulado";}
    if($estado == 3) {$estado = "Todos";}

    $db->setQuery("SELECT f.total_venta, 
                                f.razon_social,
                                f.ruc,
                                f.gravada_10,
                                f.gravada_5,
                                f.exenta,
                                t.timbrado,
                                '1' AS tipo_reg,
                                f.condicion,
                                CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',f.numero) AS nro_factura,
                                CASE COALESCE((f.ruc LIKE '%-%'),0)
                                WHEN 0 THEN '12'
                                ELSE '11' END AS 'tipo_identificacion',
                                '109' AS tipo_comprobante,
                                'N' AS moneda_extranjera, 
                                'S' AS imputa_iva,
                                'N' AS imputa_ire,
                                'N' AS imputa_irp,
                                '0' AS nro_comp_asociada,
                                '0' AS nro_timb_asociada,
                                DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha,
                                CASE f.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Anulado' END AS estado_str,                        
                                CASE f.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion_str
                                FROM facturas f
                                LEFT JOIN timbrados t ON t.id_timbrado = f.id_timbrado
                                $where_fecha  $where_id_cliente $where");
    $rows = $db->loadObjectList();

    // Logos
    $logo_farmacia = "dist/images/logo.png";

     // MPDF
     require_once __DIR__ . '/mpdf/vendor/autoload.php';

     $mpdf = new \Mpdf\Mpdf([
         'mode' => 'utf-8',
         'format' => 'Legal',
         'orientation' => 'L',
         'margin_left' => 5,
         'margin_right' => 5,
         'margin_top' => 60,
         'margin_bottom' => 15,
     ]);

    $mpdf->SetTitle("Libro de Venta");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Libro de Ventas</td>
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
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Cliente:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$cliente.'</td>
                </tr>
            </thead>
        </table>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td style="font-weight:bold;vertical-align:middle" width="20%">Fecha:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;vertical-align:middle" width="20%">Estado:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;vertical-align:middle; " >Usuario:</td>
                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$fecha_actual.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$estado.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px; text-align:left;">'.$usuario.'</td>
                    <td width="30%">&nbsp;</td>
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
                    <th class="tg-zqap">Tipo Reg.</th>
                    <th class="tg-zqap">Tipo iden.</th>
                    <th class="tg-zqap">RUC</th>
                    <th class="tg-zqap">Razon Social/Nombre</th>
                    <th class="tg-zqap">Tipo Comp.</th>
                    <th class="tg-zqap">Fecha de Emision</th>
                    <th class="tg-zqap">Timbrado</th>
                    <th class="tg-zqap">Nro. Comprob.</th>
                    <th class="tg-zqap">Gravada 10%(iva incl.)</th>
                    <th class="tg-zqap">Gravada 5%(iva incl.)</th>
                    <th class="tg-zqap">Exenta</th>
                    <th class="tg-zqap">Imp. Total</th>
                    <th class="tg-zqap">Cond.</th>
                    <th class="tg-zqap">Moneda Extran.</th>
                    <th class="tg-zqap">Imp. IVA</th>
                    <th class="tg-zqap">Imp. IRE</th>
                    <th class="tg-zqap">Imp. IRP</th>
                    <th class="tg-zqap">Nro. comp. asoc.</th>
                    <th class="tg-zqap">Nro. timb. asoc.</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $tipo_reg           = $r->tipo_reg;
        $tipo_iden          = $r->tipo_identificacion;
        $ruc                = $r->ruc;
        $razon_social       = $r->razon_social;
        $tipo_compr         = $r->tipo_comprobante;
        $fecha              = $r->fecha;
        $timbrado           = $r->timbrado;
        $documento          = $r->nro_factura;
        $grava10            = $r->gravada_10;
        $grava5             = $r->gravada_5;
        $exenta             = $r->exenta;
        $total              = $r->total_venta;
        $condicion          = $r->condicion;
        $moneda_extran      = $r->moneda_extranjera;
        $imp_iva            = $r->imputa_iva;
        $imp_ire            = $r->imputa_ire;
        $imp_irp            = $r->imputa_irp;
        $nro_comp_asoc      = $r->nro_comp_asociada;
        $nro_timb_asoc      = $r->nro_timb_asociada;
        $total_grava10     += $grava10;
        $total_grava5      += $grava5;
        $total_iva_exenta  += $exenta;
        $total_total       += $total;
        

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:center;">'.$tipo_reg.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$tipo_iden.'</td>
                <td class="tg-1x3m">'.$ruc.'</td>
                <td class="tg-1x3m">'.$razon_social.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$tipo_compr.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$fecha.'</td>
                <td class="tg-1x3m">'.$timbrado.'</td>
                <td class="tg-1x3m">'.$documento.'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($grava10).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($grava5).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($exenta).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($total).'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$condicion.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$moneda_extran.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$imp_iva.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$imp_ire.'</td>
                <td class="tg-1x3m" style="text-align:center;">'.$imp_irp.'</td>
                <td class="tg-1x3m">'.$nro_comp_asoc.'</td>
                <td class="tg-1x3m">'.$nro_timb_asoc.'</td>              
            </tr>
        ');
    }

    $mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px; text-align:left"  colspan="8">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_grava10).'</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_grava5).'</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_iva_exenta).'</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_total).'</td>
                    <td class="total" style="font-size:12px; text-align:left"  colspan="7"></td>
                </tr>
            </tbody>
        </table>
    ');


    
    $mpdf->Output("LibroDeVenta.pdf", 'I');
