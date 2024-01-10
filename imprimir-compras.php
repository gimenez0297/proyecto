<?php
include("inc/funciones.php");
$db = DataBase::conectar();

$id_orden_compra = $db->clearText($_REQUEST['id_orden_compra']);
$usuario = $auth->getUsername();

$db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                    id_orden_compra,
                    numero,
                    oc.id_proveedor,
                    condicion,
                    observacion,
                    oc.total_costo,
                    oc.estado,
                    DATE_FORMAT(oc.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                    p.proveedor
                FROM ordenes_compras oc
                LEFT JOIN proveedores p ON oc.id_proveedor=p.id_proveedor
                WHERE oc.id_orden_compra='$id_orden_compra'");
$rows = $db->loadObject();
// Logos
$logo_farmacia = "dist/images/logo.png";

$db->setQuery("SELECT 
                        @row_coun := @row_coun + 1 AS orden, 
                        id_orden_compra,
                        ocp.id_producto,
                        ocp.codigo,
                        ocp.producto,
                        ocp.costo AS unitario,
                        ocp.cantidad,
                        ocp.total_costo,
                        ocp.estado,
                        IFNULL(p.precio,0) AS precio_venta,
                        CONCAT(ROUND(IFNULL(((IFNULL(p.precio,0) - pp.costo)/p.precio),0)*100,1), '%') AS porc_desc,
                        pr.presentacion
                    FROM (SELECT @row_coun := 0) row_coun, ordenes_compras_productos ocp
                    JOIN solicitudes_compras sc ON ocp.id_solicitud_compra=sc.id_solicitud_compra
                    LEFT JOIN productos p ON ocp.id_producto=p.id_producto
                    LEFT JOIN presentaciones pr ON p.id_presentacion=pr.id_presentacion
                    LEFT JOIN productos_proveedores pp ON p.id_producto=pp.id_producto AND sc.id_proveedor=pp.id_proveedor
                    LEFT JOIN (SELECT MAX(id_factura_producto), precio, id_producto, fraccionado FROM facturas_productos WHERE fraccionado != 1 GROUP BY id_producto) fv ON ocp.id_producto=fv.id_producto
                    WHERE ocp.id_orden_compra='$id_orden_compra'");
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

$mpdf->SetTitle("Orden de Compra");
$mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70"></td>
                    <td class="tc-axmw">ORDEN DE COMPRA</td>
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
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Proveedor:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;">'.$rows->numero.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$rows->proveedor.'</td>
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
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:center;">'.$rows->fecha.'</td>
                    <td width="60%">&nbsp;</td>
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
                    <th class="tg-zqap">Item</th>
                    <th class="tg-zqap">Código</th>
                    <th class="tg-zqap">Cantidad</th>
                    <th class="tg-zqap">Descripción</th>
                    <th class="tg-zqap">Presentación</th>
                    <th class="tg-zqap">Desc %.</th>
                    <th class="tg-zqap">Precio U.</th>
                    <th class="tg-zqap">P.Público</th>
                    <th class="tg-zqap">SubTotal</th>
                </tr>
            </thead>
        <tbody>

    ');

$coun = 0;
$sum = '';
foreach ($detalle as $r) {
    $coun++;
    $orden = $r->orden;
    $producto = $r->producto;
    $codigo = $r->codigo;
    $cantidad = $r->cantidad;
    $presentacion = $r->presentacion;
    $unitario = $r->unitario;
    $estado = $r->estado;
    $tt_costo = $r->total_costo;
    $ppublico = $r->precio_venta;
    $porc_desc = $r->porc_desc;

    $subtotal = $tt_costo;
    

    $mpdf->WriteHTML('
            <tr>
                <td class="tg-wjt0">' . $orden . '</td>
                <td class="tg-1x3m" style="text-align:right">' . $codigo . '</td>
                <td class="tg-1x3m" style="text-align:right">' . $cantidad . '</td>
                <td class="tg-1x3m">' . $producto . '</td>
                <td class="tg-1x3m">' . $presentacion . '</td>
                <td class="tg-1x3m">' . $porc_desc . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($unitario) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($ppublico) . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($subtotal) . '</td>
            </tr>
        ');
    $sum += $ppublico;    
}

$mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px" colspan="8">' . separadorMiles($rows->total_costo) . '</td>

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
