<?php
    include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $desde 		  = $db->clearText($_POST["desde"]);
    $hasta 		  = $db->clearText($_POST["hasta"]);
	$sucursal     = $db->clearText($_POST['sucursal']);
    $proveedor    = $db->clearText($_POST['proveedor']);


    $fecha_actual = date('d/m/Y h:m');

    if(empty($desde)) {
        $where_fecha .= "WHERE g.fecha_emision BETWEEN '2022-01-01' AND NOW()";
        $desde        = '2021-01-01';
        $hasta        = date('Y-m-d');
    }else{
        $where_fecha .=" WHERE DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta'";
    };

    if (!empty($_REQUEST['sucursal']) && intVal($sucursal) != 0) {
        $where .= " AND g.id_sucursal=$sucursal";
        $db->setQuery("SELECT * FROM sucursales WHERE id_sucursal =$sucursal");
        $row     = $db->loadObject();
        $sucursal = $row->sucursal;
        $direccion=$row->direccion;
     }else{
         $where .= "";
         $sucursal = "TODAS";
         $direccion = "-";
     }

     if (!empty($proveedor && intVal($proveedor) != 0)) {
        $where_proveedor .= " AND g.ruc='$proveedor'";
    }else{
        $where_proveedor .= "";
    }


     $db->setQuery("SELECT  g.id_gasto,
                            tg.nombre,
                            s.sucursal,
                            tc.nombre_comprobante,
                            g.nro_gasto,
                            g.timbrado,
                            DATE_FORMAT(g.fecha_emision, '%d/%m/%Y') AS fecha_emision_str,
                            DATE_FORMAT(g.fecha_emision, '%m') AS mes_str,
                            g.ruc,
                            g.razon_social,
                            CASE g.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS 'condicion_str',
                            g.documento,
                            g.concepto,
                            g.monto,
                            g.gravada_10,
                            g.gravada_5,
                            g.exenta,
                            g.observacion
                            FROM gastos g
                            LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                            LEFT JOIN tipos_gastos tg ON tg.id_tipo_gasto = g.id_tipo_gasto
                            LEFT JOIN sucursales s ON s.id_sucursal = g.id_sucursal
                            $where_fecha  $where $where_proveedor");
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

   $mpdf->SetTitle("Gastos");
   $mpdf->SetHTMLHeader('
       <table class="tc" width="100%">
           <thead>
               <tr>
                   <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                   <td class="tc-axmw">Gastos</td>
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
                   <td style="font-weight:bold;text-align:left;vertical-align:middle">Sucursal:</td>   
                   <td width="2%">&nbsp;</td>
                   <td style="font-weight:bold;text-align:left;vertical-align:middle">Dirección:</td> 
               </tr>
               <tr>
                   <td  style="border: 1px solid;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                   <td width="2%">&nbsp;</td>
                   <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                   <td width="2%">&nbsp;</td>
                   <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$sucursal.'</td>
                   <td width="2%">&nbsp;</td>
                   <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$direccion.'</td>
               </tr>
           </thead>
       </table>
       <table class="tc" width="100%">
           <thead>
               <tr>
                   <td style="font-weight:bold;vertical-align:middle" width="20%">Fecha:</td>
                   <td width="2%">&nbsp;</td>
                   <td style="font-weight:bold;vertical-align:middle;">Usuario:</td>
               </tr> 
               <tr>
                   <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$fecha_actual.'</td>
                   <td width="50%">&nbsp;</td>
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
                   <th class="tg-zqap">Nro. Gasto</th>
                   <th class="tg-zqap">Fecha</th>
                   <th class="tg-zqap">Gasto</th>
                   <th class="tg-zqap">Sucursal</th>
                   <th class="tg-zqap">RUC</th>
                   <th class="tg-zqap">Razon Social</th>
                   <th class="tg-zqap">Concept
                   o</th>
                   <th class="tg-zqap">Documento</th>
                   <th class="tg-zqap">Condicion</th>
                   <th class="tg-zqap">Grav.10%</th>
                   <th class="tg-zqap">Grav.5%</th>
                   <th class="tg-zqap">EXENTA</th>
                   <th class="tg-zqap">TOTAL</th>
               </tr>
           </thead>
       <tbody>

   ');

   $coun = 0;
   foreach ($rows as $r) {
       $coun++;
       $nro_gasto = $r->nro_gasto;
       $fecha= $r->fecha_emision_str;
       $gasto = $r->nombre;
       $sucursal = $r->sucursal;
       $concepto = $r->concepto;
       $ruc = $r->ruc;
       $razon_social = $r->razon_social;
       $comprobante = $r->nombre_comprobante;
       $timbrado = $r->timbrado;
       $documento = $r->documento;
       $condicion = $r->condicion_str;
       $iva_10 = $r->gravada_10;
       $iva_5 = $r->gravada_5;
       $exenta = $r->exenta;
       $total = $r->monto;
       $observacion = $r->observacion;
       $total_iva_10 += $iva_10;
       $total_iva_5 += $iva_5;
       $total_iva_exenta += $exenta;
       $total_total += $total;
       $mes =$r->mes_str;
       

       $mpdf->WriteHTML('
           <tr>
               <td class="tg-1x3m">'.$nro_gasto.'</td>
               <td class="tg-1x3m">'.$fecha.'</td>
               <td class="tg-1x3m">'.$gasto.'</td>
               <td class="tg-1x3m" >'.$sucursal.'</td>
               <td class="tg-1x3m">'.$ruc.'</td>
               <td class="tg-1x3m">'.$razon_social.'</td>
               <td class="tg-1x3m">'.$concepto.'</td>
               <td class="tg-1x3m">'.$documento.'</td>
               <td class="tg-1x3m">'.$condicion.'</td>
               <td class="tg-1x3m" style="text-align:right">'.separadorMiles($iva_10).'</td>
               <td class="tg-1x3m" style="text-align:right">'.separadorMiles($iva_5).'</td>
               <td class="tg-1x3m" style="text-align:right">'.separadorMiles($exenta).'</td>
               <td class="tg-1x3m" style="text-align:right">'.separadorMiles($total).'</td>
           </tr>
       ');
   }

   $mpdf->WriteHTML('
               <tr>
                   <td class="total" style="font-size:12px; text-align:left"  colspan="9">TOTAL</td>
                   <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_iva_10).'</td>
                   <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_iva_5).'</td>
                   <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_iva_exenta).'</td>
                   <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_total).'</td>
               </tr>
           </tbody>
       </table>
   ');


   
   $mpdf->Output("Gastos_.'$mes'..pdf", 'I');