<?php
    include ("inc/funciones.php");
     $db       = DataBase::conectar();
     $usuario                = $auth->getUsername();
     $sucursal = $db->clearText($_REQUEST['sucursal']);
     $periodo  = $db->clearText($_REQUEST['periodo']);

      $fecha_actual = date('d/m/Y h:m'); 

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING funcionario LIKE '$search%' OR sucursal LIKE '$search%'";
        }

        if(!empty($sucursal) && intVal($sucursal) != 0) {
            $where_sucursal .= " AND f.id_sucursal=$sucursal";
        }else{
            $where_sucursal .="";
        };

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                   f.id_funcionario,
                                   f.funcionario,
                                   f.ci,
                                   f.salario_real,
                                   DATE_FORMAT(f.fecha_alta, '%d/%m/%Y') AS fecha_alta,
                                   (SELECT ROUND(IFNULL(SUM(lsi.importe)/12,0),0) AS total
                                    FROM liquidacion_salarios ls
                                    JOIN liquidacion_salarios_ingresos lsi ON lsi.id_liquidacion = ls.id_liquidacion
                                    WHERE SUBSTR(ls.periodo,-4)='$periodo' AND ls.id_funcionario = f.id_funcionario) AS aguinaldo,                          
                                     CASE f.estado
                                      WHEN 0 THEN 'Inactivo'
                                      WHEN 1 THEN 'Activo'
                                          END AS estado                                                 
                                          FROM funcionarios f
                                          where 1=1 $where_sucursal
                                          GROUP BY f.id_funcionario");
    $rows = $db->loadObjectList();

    $db->setQuery("SELECT * FROM sucursales WHERE id_sucursal= $sucursal");
    $func = $db->loadObject();

    if (empty($func)) {
        $sucursal = 'TODOS';
    }else{
        $sucursal = $func->sucursal;
    }

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

    $mpdf->SetTitle("Reporte Aguinaldos ". $tittle);
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Aguinaldo de Funcionarios</td>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
                </tr>
            </thead>
        </table>
        <hr>
        <br>
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="13%">Periodo:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Sucursal:</td>   
                </tr>
                <tr>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.($periodo).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$sucursal.'</td>
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
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$fecha_actual.'</td>
                    <td width="60%">&nbsp;</td>
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
                   <th class="tg-zqap">Funcionario</th>
                   <th class="tg-zqap">C.I</th>
                   <th class="tg-zqap">Salario Actual</th>
                   <th class="tg-zqap">Fecha Ingreso</th>
                   <th class="tg-zqap">Monto Aguinaldo</th>


                </tr>
            </thead>
        <tbody>
    ');

   

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $funcionario    = $r->funcionario;
        $ci             = $r->ci;
        $salario_real   = $r->salario_real;
        $fecha_alta     = $r->fecha_alta;
        $aguinaldo       = $r->aguinaldo;

        // $total_cant     += $cantidad;
        // $total          += $total_venta;

        $mpdf->WriteHTML('
            <tr>
               <td class="tg-1x3m" style="text-align:left;">'.$funcionario.'</td>
               <td class="tg-1x3m" style="text-align:right;">'. separadorMiles($ci) .'</td>
               <td class="tg-1x3m" style="text-align:right;">'. separadorMiles($salario_real) .'</td>
               <td class="tg-1x3m" style="text-align:center;">'.$fecha_alta.'</td>
               <td class="tg-1x3m" style="text-align:right;">'. separadorMiles($aguinaldo) .'</td>

            </tr>
        ');

        
    }

    $mpdf->WriteHTML('
            </tbody>
        </table>
    ');

    $mpdf->Output("Aguinaldos.pdf", 'I');

