<?php
    include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $desde 		  = $_REQUEST["desde"];
    $hasta 		  = $_REQUEST["hasta"];

    $fecha_actual = date('Y/m/d h:m');

    $db->setQuery("SELECT 
                    s.id_sucursal, 
                    s.sucursal, 
                    IFNULL(f.total_venta, 0) AS total_venta, 
                    IFNULL(f.total_costo, 0) AS total_costo, 
                    IFNULL(f.utilidad, 0) AS utilidad,
                    ROUND((IFNULL(f.utilidad, 0) / IFNULL(f.total_venta, 0)) * 100, 2) AS porcentaje_utilidad,
                    IFNULL(g.total_g, 0) AS total_gastos,
                    IFNULL(f.utilidad, 0) - IFNULL(g.total_g, 0) AS ganancias
                FROM sucursales s
                LEFT JOIN (
                    SELECT 
                        id_sucursal, 
                        SUM(total_venta) AS total_venta, 
                        SUM(total_costo) AS total_costo, 
                        SUM(total_venta) - SUM(total_costo) AS utilidad 
                    FROM facturas 
                    WHERE DATE(fecha_venta) BETWEEN '$desde' AND '$hasta'
                    GROUP BY id_sucursal
                ) f ON s.id_sucursal = f.id_sucursal
                LEFT JOIN (
                    SELECT 
                        id_sucursal, 
                        SUM(monto) AS total_g 
                    FROM gastos 
                    WHERE DATE(fecha_emision) BETWEEN '$desde' AND '$hasta' AND estado IN(1,2) 
                    GROUP BY id_sucursal 
                ) g ON s.id_sucursal = g.id_sucursal");
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

    $mpdf->SetTitle("Libro de Compra");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Ingresos, Egresos y Ganancias Por Sucursales</td>
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
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
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
               <th class="tg-zqap">Sucursal</th>
               <th class="tg-zqap">Utilidad</th>
               <th class="tg-zqap">% Utilidad</th>
               <th class="tg-zqap">Total Gastos</th>
               <th class="tg-zqap">Ganancias</th>
           </tr>
       </thead>
   <tbody>

');

$total_ventas = 0;
$total_utilidad = 0;
$total_gastos = 0;
$total_porcentaje_utilidad = 0;
$coun = 0;

foreach ($rows as $r) {
    $coun++;
    $sucursal = $r->sucursal;
    $total_venta = $r->total_venta;
    $utilidad = $r->utilidad;
    $porcentaje_utilidad = $r->porcentaje_utilidad ?: 0;
    $total_gastos = $r->total_gastos;
    $ganancias = $r->ganancias;

    $total_ventas += $total_venta;
    $total_utilidad += $utilidad;
    $total_gastos_total += $total_gastos;
    $total_ganancias_total += $ganancias;

    $mpdf->WriteHTML('
        <tr>
            <td class="tg-1x3m" style="text-align:left;">'.$sucursal.'</td>
            <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($utilidad).'</td>
            <td class="tg-1x3m" style="text-align:right;">'.$porcentaje_utilidad.'%</td>
            <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($total_gastos).'</td>
            <td class="tg-1x3m" style="text-align:right;">'.separadorMiles($ganancias).'</td>
        </tr>
    ');
}

if ($total_utilidad != 0 && $total_ventas != 0) {
    $total_porcentaje_utilidad = ROUND((($total_utilidad / $total_ventas) * 100), 2);
}

$mpdf->WriteHTML('
            <tr>
                <td class="total" style="font-size:12px; text-align:left"  colspan="1">TOTAL</td>
                <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_utilidad).'</td>
                <td class="total" style="text-align:right; font-size:12px">'.$total_porcentaje_utilidad.'%</td>
                <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_gastos_total).'</td>
                <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_ganancias_total).'</td>
            </tr>
        </tbody>
    </table>
');

$mpdf->Output("EgresosIngresosGananciasSucursales.pdf", 'I');
