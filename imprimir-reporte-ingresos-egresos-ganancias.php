<?php
    include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $desde 		  = $_REQUEST["desde"];
    $hasta 		  = $_REQUEST["hasta"];
    $id_sucursal  = $_REQUEST['id_sucursal'];

    $fecha_actual = date('Y/m/d h:m');

    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal ");
    $row     = $db->loadObject();
    $sucursal = $row->sucursal;

    // INGRESOS
    $db->setQuery("SELECT 
                        DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha_emision,
                        CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, f.numero) AS nro_documento,
                        f.id_factura,
                        f.razon_social,
                        f.total_venta,
                        f.total_costo,
                        (
                            SELECT SUM(total_venta) - SUM(total_costo) 
                            FROM facturas 
                            WHERE id_factura = f.id_factura
                        ) AS utilidad,
                        ROUND((IFNULL((
                            SELECT SUM(total_venta) - SUM(total_costo) 
                            FROM facturas 
                            WHERE id_factura = f.id_factura
                        ), 0) / f.total_venta) * 100, 2) AS porcentaje_utilidad
                    FROM facturas f
                    LEFT JOIN timbrados t ON t.id_timbrado=f.id_timbrado
                    WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' 
                        AND f.id_sucursal = $id_sucursal");
    $ingresos = $db->loadObjectList();

    // EGRESOS
    $db->setQuery("SELECT 
                        DATE_FORMAT(fecha_emision, '%d/%m/%Y') AS fecha_emision,
                        id_gasto,
                        documento AS nro_documento,
                        razon_social,
                        monto AS total_venta
                    FROM gastos 
                    WHERE fecha_emision BETWEEN '$desde' AND '$hasta'
                        AND id_sucursal = $id_sucursal 
                        AND estado IN (1, 2)");
    $egresos = $db->loadObjectList();

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

    $mpdf->SetTitle("Libro de Compra");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Ingresos, Egresos y Ganancias</td>
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
                    <td style="font-weight:bold;vertical-align:middle" width="20%">Fecha:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;vertical-align:middle; " >Usuario:</td>
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
   .tg  {border-collapse:collapse;border-spacing:0;margin-bottom:1rem;}
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
                <th colspan="5" class="tg-zqap">INGRESOS</th>
            </tr>
           <tr style="background-color: #b5b5b526;">
               <th class="tg-zqap" style="width:10%">Fecha Venta</th>
               <th class="tg-zqap" style="width:10%">N° Documento</th>
               <th class="tg-zqap" style="width:60%">Razon Social/Nombre</th>
               <th class="tg-zqap" style="width:10%">Utilidad</th>
               <th class="tg-zqap" style="width:10%">% Utilidad</th>
           </tr>
       </thead>
   <tbody>

');

$total_ventas = 0;
$total_utilidad = 0;
$total_porcentaje_utilidad = 0;
$coun = 0;

foreach ($ingresos as $in) {
    $coun++;
    $fecha_venta = $in->fecha_emision;
    $nro_documento = $in->nro_documento;
    $razon_social = $in->razon_social;
    $total_venta = $in->total_venta;
    $utilidad = $in->utilidad;
    $porcentaje_utilidad = $in->porcentaje_utilidad;

    $total_ventas += $total_venta;
    $total_utilidad += $utilidad;

    $mpdf->WriteHTML('
        <tr>
            <td class="tg-1x3m" style="text-align:center;">'.$fecha_venta.'</td>
            <td class="tg-1x3m">'.$nro_documento.'</td>
            <td class="tg-1x3m">'.$razon_social.'</td>
            <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($utilidad).'</td>
            <td class="tg-1x3m" style="text-align:right;">'.$porcentaje_utilidad.'</td>
        </tr>
    ');
}

if ($total_utilidad != 0 && $total_ventas != 0) {
    $total_porcentaje_utilidad = ROUND((($total_utilidad / $total_ventas) * 100), 2);
}

$mpdf->WriteHTML('
            <tr>
                <td class="total" style="font-size:12px; text-align:left"  colspan="3">TOTAL</td>
                <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_utilidad).'</td>
                <td class="total" style="text-align:right; font-size:12px">'.$total_porcentaje_utilidad.'</td>
            </tr>
        </tbody>
    </table>
');

$mpdf->writehtml('
    <table class="tg" style="width:100%">
       <thead>
            <tr style="background-color: #b5b5b526;">
                <th colspan="4" class="tg-zqap">EGRESOS</th>
            </tr>
           <tr style="background-color: #b5b5b526;">
               <th class="tg-zqap" style="width:13%">Fecha Compra</th>
               <th class="tg-zqap" style="width:15%">N° Documento</th>
               <th class="tg-zqap" style="width:60%">Razon Social/Nombre</th>
               <th class="tg-zqap">Importe</th>
           </tr>
       </thead>
    <tbody>
');
$total_total_e = 0;
$coun = 0;
    foreach ($egresos as $eg) {
        $coun++;
        $fecha_venta_e        = $eg->fecha_emision;
        $nro_documento_e      = $eg->nro_documento;
        $razon_social_e       = $eg->razon_social;
        $importe_e            = $eg->total_venta;
        $total_total_e       += $importe_e;
        

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:center;">'.$fecha_venta_e.'</td>
                <td class="tg-1x3m">'.$nro_documento_e.'</td>
                <td class="tg-1x3m">'.$razon_social_e.'</td>
                <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($importe_e).'</td>
            </tr>
        ');
    }

    $mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px; text-align:left"  colspan="3">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_total_e).'</td>
                </tr>
            </tbody>
        </table>
    ');

    $ganancia = $total_utilidad - $total_total_e;

    $mpdf->writehtml('
   <table class="tg" style="width:100%">
       <thead>
            <tr style="background-color: #b5b5b526;">
                <th colspan="4" class="tg-zqap">GANANCIAS</th>
            </tr>
           <tr style="background-color: #b5b5b526;">
               <th class="tg-zqap" style="width:33%">Total Utilidad</th>
               <th class="tg-zqap" style="width:33%">Total Egreso</th>
               <th class="tg-zqap" style="width:34%">Importe</th>
           </tr>
       </thead>
       <tbody>
    ');


        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($total_utilidad).'</td>
                <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($total_total_e).'</td>
                <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($ganancia).'</td>
            </tr>
            </tbody>
            </table>
        ');
    


    
    $mpdf->Output("EgresosIngresosGanancias.pdf", 'I');

