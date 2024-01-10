<?php
    include ("inc/funciones.php");
    $db = DataBase::conectar();
    $usuario = $auth->getUsername();

    $id_sucursal = $_REQUEST["id_sucursal"];
    $desde = $_REQUEST["desde"];
    $hasta = $_REQUEST["hasta"];
    $estado='1';

    $and_sucursal = "";
    $where_fecha = "";
    $and_estado= "";

    if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        $and_sucursal .= " AND aj.id_sucursal=$id_sucursal";
        $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal='$id_sucursal'");
        $row       = $db->loadObject();
        $sucursal = $row->sucursal;
    }else{
        $sucursal ="TODAS";

    if(!empty($and_estado) && intVal($and_estado) != 0){
        $and_estado= " AND aj.estado=$estado";
    }else{
        $and_estado="";
    }
        
    };
    if ((isset($desde) && !empty($desde)) || (isset($hasta) && !empty($hasta))) {
        $where_fecha = "AND DATE(aj.fecha) BETWEEN '$desde' AND '$hasta'";
    }

    $db->setQuery("SELECT
                    ip.id_ajuste_producto AS id,
                    ip.id_ajuste,
                    ip.id_producto,
                    ip.id_lote,
                    aj.numero,
                    ip.lote,
                    p.codigo,
                    ip.producto,
                    ip.tipo_ajuste AS id_movimiento,
                    CASE ip.tipo_ajuste WHEN 1 THEN 'POSITIVO' WHEN 2 THEN  'NEGATIVO' WHEN 3 THEN 'REEMPLAZO' END AS movimiento,
                    ip.cantidad,
                    ip.fraccionado,
                    ip.cantidad_anterior,
                    ip.fraccionado_anterior,
                    (SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote) AS cantidad_actual,
                    (SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote) AS fraccionado_actual,
                    IF(aj.estado = 0, IF(ip.tipo_ajuste = 1, ip.cantidad+IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0)-ip.cantidad, ip.cantidad)),
                        IF(ip.tipo_ajuste = 1, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0), ip.cantidad))) AS final_entero,
                    
                    IF(aj.estado = 0, IF(ip.tipo_ajuste = 1, ip.fraccionado+IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0)-ip.fraccionado, ip.fraccionado)),
                        IF(ip.tipo_ajuste = 1, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0), ip.fraccionado))) AS final_fraccionado,
                    
                    IF(aj.estado = 0, IF(ip.tipo_ajuste = 1, ip.cantidad+IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0)-ip.cantidad, ip.cantidad)),
                        IF(ip.tipo_ajuste = 1, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0), ip.cantidad)))
                    -  (SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote) AS diferencia_actual,
                    
                    IF(aj.estado = 0, IF(ip.tipo_ajuste = 1, ip.cantidad+IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0)-ip.cantidad, ip.cantidad)),
                        IF(ip.tipo_ajuste = 1, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT stock FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0), ip.cantidad)))
                    - ip.cantidad_anterior  AS diferencia_anterior,
                    
                    IF(aj.estado = 0, IF(ip.tipo_ajuste = 1, ip.fraccionado+IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0)-ip.fraccionado, ip.fraccionado)),
                        IF(ip.tipo_ajuste = 1, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0), ip.fraccionado)))
                    - (SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote) AS diferencia_actual_fraccionado ,
                    
                    IF(aj.estado = 0, IF(ip.tipo_ajuste = 1, ip.fraccionado+IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0)-ip.fraccionado, ip.fraccionado)),
                        IF(ip.tipo_ajuste = 1, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0),
                        IF(ip.tipo_ajuste = 2, IFNULL((SELECT fraccionado FROM stock WHERE id_producto=ip.id_producto AND id_sucursal=aj.id_sucursal AND id_lote=ip.id_lote),0), ip.fraccionado)))
                    - ip.fraccionado_anterior AS diferencia_anterior_fraccionado

                FROM ajuste_stock_productos ip
                LEFT JOIN ajuste_stock aj ON ip.id_ajuste=aj.id_ajuste
                JOIN productos p ON ip.id_producto=p.id_producto
                WHERE 1+1 $and_id_ajuste $and_sucursal $where_fecha $and_estado
                ORDER BY aj.numero ASC");
$rows = $db->loadObjectList() ?: [];

    $fecha_actual = date('d/m/Y h:m');

    // Logos
    $logo_farmacia = "dist/images/logo.png";

    // MPDF
    require_once __DIR__ . '/mpdf/vendor/autoload.php';

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'L',
        'margin_left' => 5,
        'margin_right' => 5,
        'margin_top' => 60,
        'margin_bottom' => 15,
    ]);

    $mpdf->SetTitle("AjusteStock"); 
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">INFORME DE AJUSTE STOCK</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
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
                </tr> 
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.$sucursal.'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.fechaLatina($hasta).'</td>
                </tr>
            </thead>
        </table>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;">Fecha:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Usuario:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;" width="20%">'.$fecha_actual.'</td>
                    <td width="65%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px; text-align:center">'.$usuario.'</td>
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
        .tg .tg-zqap{font-size:12px;font-weight:bold;text-align:left;vertical-align:middle}
        .tg .tg-1x3m{text-align:left;vertical-align:middle}
        .tg .tg-wjt0{text-align:center;vertical-align:middle}
        .firma{text-align:center; font-weight: bold; margin-top: 100px}
        .total{text-align:center; font-weight: bold; text-align:center;background-color:#EAE6E5}
        .aprobado{text-align:center;margin-top: 60px}

        .promedio{text-align:center;font-weight:bold;margin-top: 50px}
        .tabla{vertical-align: top}

    </style>
    ');


//Producto
    $mpdf->writehtml('
    <table class="tg" style="width:100%">
    <thead>
         <tr style="background-color: #b5b5b526;">
             <th rowspan="2" class="tg-zqap" style="text-align:center">N º</th>
             <th colspan="4" class="tg-zqap" style="text-align:center">Producto</th>
             <th colspan="4" class="tg-zqap" style="text-align:center">Cantidad Entero</th>
             <th colspan="4" class="tg-zqap" style="text-align:center">Cantidad Fraccionado</th>
         </tr>
        <tr style="background-color: #b5b5b526;">
            <th class="tg-zqap" style="width:8%">Código</th>
            <th class="tg-zqap" style="width:8%">Producto</th>
            <th class="tg-zqap" style="width:8%">Lote</th>
            <th class="tg-zqap" style="width:8%">Movimiento</th>
            <th class="tg-zqap" style="width:8%">Ajuste</th>
            <th class="tg-zqap" style="width:8%">Stock Anterior</th>
            <th class="tg-zqap" style="width:8%">Stock Final</th>
            <th class="tg-zqap" style="width:8%">Diferencia</th>
            <th class="tg-zqap" style="width:8%">Ajuste</th>
            <th class="tg-zqap" style="width:8%">Stock Anterior</th>
            <th class="tg-zqap" style="width:8%">Stock Final</th>
            <th class="tg-zqap" style="width:8%">Diferencia</th>
        </tr>
    </thead>
    <tbody>

 ');

    $coun = 0;

    $total_cantidad=0;
    $total_cantidad_anterior=0;
    $total_final_entero=0;
    $total_diferencia_anterior=0;

    $total_fraccionado=0;
    $total_fraccionado_anterior=0;
    $total_final_fraccionado=0;
    $total_diferencia_anterior_fraccionado=0;
    
    foreach ($rows as $r) {
        $coun++;
        $numero = $r->numero;
        $codigo = $r->codigo;
        $producto = $r->producto;
        $lote = $r->lote;
        $movimiento = $r->movimiento;

        $cantidad = $r->cantidad;
        $cantidad_anterior = $r->cantidad_anterior;
        $final_entero = $r->final_entero;
        $diferencia_anterior = $r->diferencia_anterior;

        $fraccionado = $r->fraccionado;
        $fraccionado_anterior = $r->fraccionado_anterior;
        $final_fraccionado = $r->final_fraccionado;
        $diferencia_anterior_fraccionado = $r->diferencia_anterior_fraccionado;

        $total_cantidad += $cantidad;
        $total_cantidad_anterior += $cantidad_anterior;
        $total_final_entero += $final_entero;
        $total_diferencia_anterior += $diferencia_anterior;

        $total_fraccionado += $fraccionado;
        $total_fraccionado_anterior += $fraccionado_anterior;
        $total_final_fraccionado += $final_fraccionado;
        $total_diferencia_anterior_fraccionado += $diferencia_anterior_fraccionado;

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m">'.$numero.'</td>
                <td class="tg-1x3m">'.$codigo.'</td>
                <td class="tg-1x3m">'.$producto.'</td>
                <td class="tg-1x3m">'.$lote.'</td>
                <td class="tg-1x3m">'.$movimiento.'</td>
            
                <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($cantidad).'</td>
                <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($cantidad_anterior).'</td>
                <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($final_entero).'</td>
                <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($diferencia_anterior).'</td>
           
            <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($fraccionado).'</td>
            <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($fraccionado_anterior).'</td>
            <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($final_fraccionado).'</td>
            <td class="tg-1x3m" style="text-align:right; font-size:12px">'.separadorMiles($diferencia_anterior_fraccionado).'</td>
        </tr>
        ');
    }

    $mpdf->WriteHTML('
        <tr>
            <td class="total" style="font-size:12px; text-align:left"  colspan="5">TOTAL</td>
        
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_cantidad).'</td>
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_cantidad_anterior).'</td> 
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_final_entero).'</td> 
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_diferencia_anterior).'</td> 
       
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_fraccionado).'</td>
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_fraccionado_anterior).'</td> 
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_final_fraccionado).'</td> 
            <td class="total" style="text-align:right; font-size:12px">'.separadorMiles($total_diferencia_anterior_fraccionado).'</td> 
        </tr>
    </tbody>
</table>
       
    ');

    $mpdf->Output("Ajuste Stock.pdf", 'I');
