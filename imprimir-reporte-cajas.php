<?php
include("inc/funciones.php");
$db = DataBase::conectar();

$id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
$id_cajero = $db->clearText($_REQUEST['cajero']);
$desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
$hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
$usuario = $auth->getUsername();

if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
    $where_sucursal .= " AND c.id_sucursal=$id_sucursal";
    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal='$id_sucursal'");
    $row       = $db->loadObject();
    $sucursal = $row->sucursal;
}else{
    $where_sucursal .="";
    $sucursal ="TODAS";
};

if(intVal($id_cajero) != 0 ) {
            $where_id_cajero .= " AND f.id_funcionario=$id_cajero";
            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario =$id_cajero");
            $row = $db->loadObject();
            $id_cajero = $row->id_funcionario;
        }else{
            $where_id_cajero .="";
        };

// Logos
$logo_farmacia = "dist/images/logo.png";

$db->setQuery("SELECT 
                            ch.id_caja_horario,
                            s.sucursal,
                            c.numero,
                            f.`funcionario` AS cajero,
                            DATE_FORMAT(ch.fecha_apertura,'%d/%m/%Y %H:%i:%s') apertura,
                            DATE_FORMAT(ch.fecha_cierre,'%d/%m/%Y %H:%i:%s') cierre,
                            ch.monto_apertura,
                            IFNULL(ce.monto_ext,0) AS sobre,
                            (SELECT IFNULL(SUM(c.monto), 0) AS monto 
                                FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura 
                                WHERE c.estado=1 AND c.id_metodo_pago=1 
                                AND f.id_caja_horario=ch.id_caja_horario) AS total_efectivo,
                            (SELECT (IFNULL(SUM(c.monto), 0) -  (SELECT 
                                                            IFNULL(SUM(nc.total), 0) 
                                                        FROM notas_credito nc 
                                                        WHERE nc.id_caja_horario = ch.id_caja_horario
                                                        AND nc.devolucion_importe = 1) ) AS monto 
                                FROM cobros c JOIN facturas f ON c.id_factura=f.id_factura 
                                WHERE c.estado=1 AND f.id_caja_horario=ch.id_caja_horario) AS venta_sistema,
                                (SELECT
                                IF(cd.monto_ca IS NULL,0,((SUM(c.monto) - IFNULL(ch.devolucion_importe,0))-(IFNULL((SELECT IFNULL(SUM(total), 0) + 
                                                    (
                                                        SELECT IFNULL(SUM(c.monto), 0) AS monto
                                                        FROM cobros c
                                                        JOIN facturas f ON c.id_factura=f.id_factura
                                                        WHERE c.estado=1 AND c.id_metodo_pago != 1 AND f.id_caja_horario=cd.id_caja_horario
                                                    ) AS monto
                                                    FROM cajas_detalles cd
                                                    WHERE tipo=0 AND id_caja_horario=ch.id_caja_horario), 0) + IFNULL(ex.extra,0))))
                            FROM cobros c
                            JOIN facturas f ON c.id_factura=f.id_factura
                            JOIN (SELECT IFNULL(SUM(total), 0) AS monto_ca, id_caja_horario FROM cajas_detalles WHERE tipo=0 GROUP BY id_caja_horario ) cd
                            ON f.`id_caja_horario`=cd.id_caja_horario
                            LEFT JOIN (SELECT IFNULL(SUM(monto_extraccion), 0) AS extra, id_caja_horario FROM cajas_extracciones GROUP BY id_caja_horario) ex
                            ON f.`id_caja_horario`=ex.id_caja_horario
                            WHERE c.estado=1 AND f.id_caja_horario=ch.id_caja_horario
                            GROUP BY f.id_caja_horario) AS diferencia
                        FROM cajas_horarios ch
                        LEFT JOIN cajas c ON ch.id_caja=c.id_caja
                        LEFT JOIN sucursales s ON c.id_sucursal=s.id_sucursal
                        LEFT JOIN users cu ON ch.usuario = cu.`username`
                        LEFT JOIN funcionarios f ON f.id_usuario = cu.id
                        LEFT JOIN (SELECT *, SUM(monto_extraccion) AS monto_ext FROM cajas_extracciones GROUP BY id_caja_horario) ce ON ch.id_caja_horario=ce.id_caja_horario
                        WHERE ch.fecha_apertura BETWEEN '$desde' AND '$hasta' $where_sucursal $where_id_cajero");
$detalle = $db->loadObjectList();

$db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario=$id_cajero");
$func = $db->loadObject();

if (empty($func)) {
    $funcionario = 'TODOS';
}else{
    $funcionario = $func->funcionario;
}

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

$mpdf->SetTitle("Reporte Cajas");
$mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70"></td>
                    <td class="tc-axmw">REPORTES DE CAJAS</td>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Sucursal:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Fecha Desde:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Fecha Hasta:</td>
                    <td width="2%">&nbsp;</td>
                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$sucursal.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    
                </tr>
            </thead>
        </table>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Cajero:</td>

                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$funcionario.'</td>
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

if (empty($func)) {
    $mpdf->writehtml('
        <table class="tg" style="width:100%">
            <thead>
                <tr style="background-color: #b5b5b526;">
                    <th class="tg-zqap">Caja</th>
                    <th class="tg-zqap">Cajero</th>
                    <th class="tg-zqap">Inicio</th>
                    <th class="tg-zqap">Fin</th>
                    <th class="tg-zqap">Apertura</th>
                    <th class="tg-zqap">Efectivo</th>
                    <th class="tg-zqap">Sobre</th>
                    <th class="tg-zqap">Vta. Sist.</th>
                    <th class="tg-zqap">Diferencia</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($detalle as $r) {
        $coun++;
        $caja = $r->numero;
        $cajero = $r->cajero;
        $apertura = $r->apertura;
        $cierre = $r->cierre;
        $monto_apertura = $r->monto_apertura;
        $efectivo = $r->total_efectivo;
        $sobre = $r->sobre;
        $venta_sistema = $r->venta_sistema;
        $diferencia = $r->diferencia;
        $subtotal = $r->total_venta;

        $total_sumado += $venta_sistema;
        $total_dif += $diferencia;

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:left">' . $caja . '</td>
                <td class="tg-1x3m" style="text-align:left">' . $cajero . '</td>
                <td class="tg-1x3m">' . $apertura . '</td>
                <td class="tg-1x3m">' . $cierre . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($monto_apertura) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($efectivo) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($sobre) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($venta_sistema) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($diferencia) . '</td>
            </tr>
        ');
    }

    $mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px" colspan="7">' . separadorMiles($total_sumado) . '</td>
                    <td class="total" style="text-align:right; font-size:12px">' . separadorMiles($total_dif) . '</td>
                </tr>
            </tbody>
        </table>
    ');

}else{
    $mpdf->writehtml('
        <table class="tg" style="width:100%">
            <thead>
                <tr style="background-color: #b5b5b526;">
                    <th class="tg-zqap">Caja</th>
                    <th class="tg-zqap">Inicio</th>
                    <th class="tg-zqap">Fin</th>
                    <th class="tg-zqap">Apertura</th>
                    <th class="tg-zqap">Efectivo</th>
                    <th class="tg-zqap">Sobre</th>
                    <th class="tg-zqap">Vta. Sist.</th>
                    <th class="tg-zqap">Diferencia</th>
                </tr>
            </thead>
        <tbody>

    ');

$coun = 0;
foreach ($detalle as $r) {
    $coun++;
    $caja = $r->numero;
    $cajero = $r->cajero;
    $apertura = $r->apertura;
    $cierre = $r->cierre;
    $monto_apertura = $r->monto_apertura;
    $efectivo = $r->total_efectivo;
    $sobre = $r->sobre;
    $venta_sistema = $r->venta_sistema;
    $diferencia = $r->diferencia;
    $subtotal = $r->total_venta;

    $total_sumado += $venta_sistema;
    $total_dif += $diferencia;

    $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:left">' . $caja . '</td>
                <td class="tg-1x3m">' . $apertura . '</td>
                <td class="tg-1x3m">' . $cierre . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($monto_apertura) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($efectivo) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($sobre) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($venta_sistema) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($diferencia) . '</td>
            </tr>
        ');
}

$mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px" colspan="6">' . separadorMiles($total_sumado) . '</td>
                    <td class="total" style="text-align:right; font-size:12px">' . separadorMiles($total_dif) . '</td>
                </tr>
            </tbody>
        </table>
    ');
}

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

$mpdf->Output("Reporte Cajas.pdf", 'I');
