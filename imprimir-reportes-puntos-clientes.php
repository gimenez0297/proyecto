<?php
    include ("inc/funciones.php");
    $db = DataBase::conectar();
    $usuario = $auth->getUsername();

    $estado   = $db->clearText($_REQUEST['estado']);
    
    if(!empty($estado) && intVal($estado) == 2) {
        $where_puntos = "AND puntos > 0";
    }else if(!empty($estado) && intVal($estado) == 1){
        $where_puntos = "AND puntos = 0";
    }

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                id_cliente,
                                razon_social,
                                ruc,
                                direccion,
                                telefono,
                                celular,
                                email,
                                id_tipo,
                                tipo,
                                obs,
                                usuario,
                                DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                                referencia,
                                longitud,
                                latitud, 
                                puntos 
                                FROM clientes  
                                WHERE 1=1 $where_puntos
                                GROUP BY razon_social
                                ORDER BY puntos DESC");
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
        'margin_top' => 50,
        'margin_bottom' => 15,
    ]);

    foreach ($rows as $r) {
        $fecha = $r->fecha_actual;
    }

    $mpdf->SetTitle("Puntos de Clientes"); 
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">REPORTE DE PUNTOS DE CLIENTES</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;">Fecha:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Usuario:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:center;" width="20%">'.$fecha.'</td>
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

    $mpdf->writehtml('
        <table class="tg" style="width:100%">
            <thead>
                <tr style="background-color: #b5b5b526;">
                    <th class="tg-zqap">Razón Social</th>
                    <th class="tg-zqap">Ruc</th>
                    <th class="tg-zqap">Teléfono </th>
                    <th class="tg-zqap">Celular</th>
                    <th class="tg-zqap">Dirección</th>
                    <th class="tg-zqap">Puntos</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $razon_social = $r->razon_social;
        $ruc = $r->ruc;
        $telefono = $r->telefono;
        $celular = $r->celular;
        $direccion = $r->direccion;
        $puntos = $r->puntos;

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m">'.$razon_social.'</td>
                <td class="tg-1x3m">'.$ruc.'</td>
                <td class="tg-1x3m">'.$telefono.'</td>
                <td class="tg-1x3m">'.$celular.'</td>
                <td class="tg-1x3m">'.$direccion.'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($puntos).'</td>
            </tr>
        ');
    }

    $mpdf->WriteHTML('
            </tbody>
        </table>
    ');

    
    $mpdf->Output("ReportePuntosClientes.pdf", 'I');


