<?php
include("inc/funciones.php");
$db = DataBase::conectar();

$id_solicitud_deposito = $db->clearText($_REQUEST['id_solicitud_deposito']);
$tipo = $db->clearText($_REQUEST['tipo']);
$orden_i = $db->clearText($_REQUEST['orden']);
$usuario = $auth->getUsername();

// PRODUCTOS CON STOCK
if(!empty($tipo) && intVal($tipo) == 1){

    $where_stock = " AND ( SELECT IFNULL(SUM(stock), 0)
                                           FROM stock
                                          WHERE id_producto=p.id_producto AND id_sucursal=sd.id_deposito                    
                                        ) > 0 ";

//OMITIR PRODUCTOS SIN STOCK
}elseif(!empty($tipo) && intVal($tipo) == 2){

    $where_stock = "";

//MODULO DE CARGAR SOLICITUD DE DEPOSITO                                       
}else{

    $where_stock = "";

}

//ORDEN

if (!empty($orden_i) && intval($orden_i) == 1) {
    $order_by = "ORDER BY IFNULL(l.laboratorio,'SIN LABORATORIO')";
    $orden_p = 1;

}elseif (!empty($orden_i) && intval($orden_i) == 2) {
    $order_by = "ORDER BY IFNULL(pv.proveedor,'SIN PROVEEDOR')";
    $orden_p = 2;

}elseif (!empty($orden_i) && intval($orden_i) == 3) {
    $order_by = "ORDER BY IFNULL(m.marca,'SIN MARCA')";
    $orden_p = 3;

}elseif (!empty($orden_i) && intval($orden_i) == 4) {
    $order_by = "ORDER BY IFNULL(r.rubro,'SIN RUBRO')";
    $orden_p = 4;

}else{
    $order_by = "";
    $orden_p = 0;
}

$db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                    sd.id_solicitud_deposito,
                    sd.id_sucursal,
                    sd.numero,
                    sd.id_proveedor,
                    sd.observacion,
                    s.sucursal,
                    DATE_FORMAT(sd.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                    DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i') AS fecha_actual,
                    p.proveedor
                FROM solicitudes_depositos sd
                JOIN sucursales s ON  sd.id_sucursal = s.id_sucursal 
                LEFT JOIN proveedores p ON sd.id_proveedor=p.id_proveedor
                WHERE sd.id_solicitud_deposito = $id_solicitud_deposito ");
$rows = $db->loadObject();
// Logos
$logo_farmacia = "dist/images/logo.png";

$db->setQuery("SELECT 
                        @row_coun := @row_coun + 1 AS orden, 
                        sdp.id_solicitud_deposito,
                        sdp.id_producto,
                        pv.proveedor,
                        p.producto,
                        m.marca,
                        r.rubro,
                        sd.id_deposito,
                        p.codigo,
                        p.precio AS unitario,
                        sdp.cantidad,
                        pre.presentacion,
                        pre.id_presentacion,
                        IFNULL(l.id_laboratorio,0) AS id_laboratorio,
                        IFNULL(l.laboratorio,'SIN LABORATORIO') AS laboratorio,
                        IFNULL(pp.id_proveedor,0) AS id_proveedor,
                        IFNULL(pv.proveedor,'SIN PROVEEDOR') AS proveedor,
                        IFNULL(m.id_marca,0) AS id_marca,
                        IFNULL(m.marca,'SIN MARCA') AS marca,
                        IFNULL(r.id_rubro,0) AS id_rubro,
                        IFNULL(r.rubro,'SIN RUBRO') AS rubro
                    FROM (SELECT @row_coun := 0) row_coun, solicitudes_depositos_productos sdp
                    JOIN productos p ON sdp.id_producto=p.id_producto
                    JOIN solicitudes_depositos sd ON sdp.id_solicitud_deposito = sd.id_solicitud_deposito
                    LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                    LEFT JOIN laboratorios l ON l.id_laboratorio = p.id_laboratorio
                    LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto AND pp.proveedor_principal = 1
                    LEFT JOIN proveedores pv ON pv.id_proveedor = pp.id_proveedor
                    LEFT JOIN marcas m ON p.id_marca = m.id_marca
                    LEFT JOIN rubros r ON p.id_rubro = r.id_rubro
                    WHERE  sd.id_solicitud_deposito = $id_solicitud_deposito $where_stock
                    $order_by
                    

                   ");



                    
$detalle = $db->loadObjectList();

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

$mpdf->SetTitle("Solicitud de deposito");
$mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70"></td>
                    <td class="tc-axmw">SOLICITUD DE DEPÓSITO</td>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="8.5%">Nro:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Sucursal:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Proveedor:</td>   
                    </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;">'.$rows->numero.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$rows->sucursal.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$rows->proveedor.'</td>
                </tr>
            </thead>
        </table>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Fecha solicitado:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle" width="20%">Fecha impresión:</td>
                    <td width="60%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle; " >Usuario:</td>
                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:center;">'.$rows->fecha.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px; margin-left: 2px; font-size:14px;text-align:center;">'.$rows->fecha_actual.'</td>
                    <td width="40%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px; text-align:center;">'.$usuario.'</td>
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

        /* Tabla contenido */
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
                    <th class="tg-zqap">Item</th>
                    <th class="tg-zqap">Código</th>
                    <th class="tg-zqap">Descripción</th>
                    <th class="tg-zqap">Presentación</th>
                    <th class="tg-zqap">Cantidad</th>
                </tr>
            </thead>
        <tbody>

    ');

if ($orden_p == 1) {
    $id_laboratorio_ = 0;

}elseif ($orden_p == 2) {
    $id_proveedor_ = 0;

}elseif ($orden_p == 3) {
    $id_marca_ = 0;

}elseif ($orden_p == 4) {
    $id_rubro_ = 0;    
}

$coun = 0;
foreach ($detalle as $r) {
    if ($orden_p == 1) {
        if ($r->id_laboratorio != $id_laboratorio_) {
            $id_laboratorio_ = $r->id_laboratorio;
            $mpdf->WriteHTML('
                <tr>
                    <td class="tg-baqh total" colspan="5" style="background-color: #b5b5b526;font-size:12px" style="text-align: left"><span style="font-weight:bold">'.$r->laboratorio.'</span></td>
                </tr>
            ');
        }
    }elseif ($orden_p == 2) {
        if ($r->id_proveedor != $id_proveedor_) {
            $id_proveedor_ = $r->id_proveedor;
            $mpdf->WriteHTML('
              <tr>
                <td class="tg-baqh total" colspan="5" style="background-color: #b5b5b526;font-size:12px" style="text-align: left"><span style="font-weight:bold">'.$r->proveedor.'</span></td>
              </tr>
            ');
        }
    }elseif ($orden_p == 3) {
        if ($r->id_marca != $id_marca_) {
            $id_marca_ = $r->id_marca;
            $mpdf->WriteHTML('
                <tr>
                    <td class="tg-baqh total" colspan="5" style="background-color: #b5b5b526;font-size:12px" style="text-align: left"><span style="font-weight:bold">'.$r->marca.'</span></td>
                </tr>
            ');
        }    
    }elseif ($orden_p == 4) {
        if ($r->id_rubro != $id_rubro_) {
            $id_rubro_ = $r->id_rubro;
            $mpdf->WriteHTML('
                <tr>
                    <td class="tg-baqh total" colspan="5" style="background-color: #b5b5b526;font-size:12px" style="text-align: left"><span style="font-weight:bold">'.$r->rubro.'</span></td>
                </tr>
            ');
        }    
    }




    $coun++;
    $orden = $r->orden;
    $codigo = $r->codigo;
    $producto = $r->producto;
    $presentacion = $r->presentacion;
    $cantidad = $r->cantidad;
    $unitario = $r->unitario;

    $total_cantidad += $cantidad;

    $subtotal = $r->unitario * $r->cantidad;

    $total_total += $subtotal;

    $mpdf->WriteHTML('
            <tr>
                <td class="tg-wjt0">' . $orden . '</td>
                <td class="tg-1x3m" style="text-align:right">' . $codigo . '</td>
                <td class="tg-1x3m">' . $producto . '</td>
                <td class="tg-1x3m">' . $presentacion . '</td>
                <td class="tg-1x3m" style="text-align:right">' . $cantidad . '</td>
            </tr>
        ');
}

$mpdf->WriteHTML('
                <tr>
                    <td class="total" style="text-align:left; font-size:12px" colspan="4">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px" >' . separadorMiles($total_cantidad) . '</td>
                </tr>
            </tbody>
        </table>
    ');

$mpdf->Output("Solicitud de deposito.pdf", 'I');
