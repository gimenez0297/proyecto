<?php
include("inc/funciones.php");
$db = DataBase::conectar();

$id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
$desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
$hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
$usuario = $auth->getUsername();
$datosUsuario = datosUsuario($usuario);
$id_sucursal_ = $datosUsuario->id_sucursal;
$id_rol = $datosUsuario->id_rol;

// if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
//     $where_sucursal .= " AND f.id_sucursal=$id_sucursal";
//     $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal='$id_sucursal'");
//     $row       = $db->loadObject();
//     $sucursal = $row->sucursal;
// }else{
//     $where_sucursal .="";
//     $sucursal ="TODAS";
// };

if (esAdmin($id_rol) === false) {

    $and_id_sucursal .= " AND f.id_sucursal=$id_sucursal_";
    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal_");
    $row     = $db->loadObject();
    $sucursal = $row->sucursal;
    $where_sucursal .= " AND f.id_sucursal=$id_sucursal_";

} else if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {

    $and_id_sucursal .= " AND f.id_sucursal=$id_sucursal";
    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
    $row     = $db->loadObject();
    $sucursal = $row->sucursal;
    $where_sucursal .= " AND f.id_sucursal=$id_sucursal";

} else if(empty($id_sucursal)){

    $where_sucursal .= "";
    $sucursal = "TODOS"; 
    
}

// Logos
$logo_farmacia = "dist/images/logo.png";

$db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                    f.id_factura,
                    fp.producto,
                    fp.fraccionado,
                    sum(fp.cantidad) AS cantidad,
                    fp.precio,
                    p.codigo,
                    pr.presentacion,
                    sum(fp.total_venta) AS total_venta,
                    DATE_FORMAT(f.fecha, '%d/%m/%Y %H:%i:%s') AS fecha
                FROM facturas_productos fp
                LEFT JOIN facturas f ON fp.id_factura=f.id_factura
                LEFT JOIN productos p ON fp.id_producto=p.id_producto
                LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                LEFT JOIN presentaciones pr ON p.id_presentacion=pr.id_presentacion
                WHERE p.controlado=1 AND f.fecha BETWEEN '$desde' AND '$hasta' $where_sucursal
                GROUP BY p.id_producto");
$detalle = $db->loadObjectList();

$db->setQuery("SELECT * FROM sucursales WHERE id_sucursal=$id_sucursal");
$suc = $db->loadObject();

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

$mpdf->SetTitle("Productos Controlados Vendidos");
$mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="' . $logo_farmacia . '" alt="Image" height="70"></td>
                    <td class="tc-axmw">PRODUCTOS CONTROLADOS VENDIDOS</td>
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
                    <th class="tg-zqap">Código</th>
                    <th class="tg-zqap">Descripción</th>
                    <th class="tg-zqap">Presentación</th>
                    <th class="tg-zqap">Cantidad</th>
                    <th class="tg-zqap">SubTotal</th>
                </tr>
            </thead>
        <tbody>

    ');

$coun = 0;
foreach ($detalle as $r) {
    $coun++;
    $orden = $r->orden;
    $producto = $r->producto;
    $codigo = $r->codigo;
    $cantidad = $r->cantidad;
    $fraccionado = $r->fraccionado;
    $presentacion = $r->presentacion;
    $unitario = $r->precio;
    $subtotal = $r->total_venta;

    $total_sumado += $subtotal;

    $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m" style="text-align:left">' . $codigo . '</td>
                <td class="tg-1x3m">' . $producto . '</td>
                <td class="tg-1x3m">' . $presentacion . '</td>
                <td class="tg-1x3m" style="text-align:right">' . $cantidad . '</td>
                <td class="tg-1x3m" style="text-align:right">' . separadorMiles($subtotal) . '</td>
            </tr>
        ');
}

$mpdf->WriteHTML('
                <tr>
                    <td class="total" style="font-size:12px">TOTAL</td>
                    <td class="total" style="text-align:right; font-size:12px" colspan="4">' . separadorMiles($total_sumado) . '</td>
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
