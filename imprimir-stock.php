<?php
    include ("inc/funciones.php");
    $db = DataBase::conectar();
    $usuario = $auth->getUsername();
    $datosUsuario = datosUsuario($usuario);
    $id_sucursal_ = $datosUsuario->id_sucursal;
    $id_rol = $datosUsuario->id_rol;
    $fecha = date("d/m/Y H:i:s");

    $sucursal       = $db->clearText($_REQUEST['id_sucursal']);
    $estado         = $db->clearText($_REQUEST['estado']);
    $proveedor      = $db->clearText($_REQUEST['proveedor']);
    $tipo           = $db->clearText($_REQUEST['tipo']);
    $rubro          = $db->clearText($_REQUEST['rubro']);
    $procedencia    = $db->clearText($_REQUEST['procedencia']);
    $origen         = $db->clearText($_REQUEST['origen']);
    $clasificacion  = $db->clearText($_REQUEST['clasificacion']);
    $presentacion   = $db->clearText($_REQUEST['presentacion']);
    $unidad_medida  = $db->clearText($_REQUEST['unidad_medida']);
    $marca          = $db->clearText($_REQUEST['marca']);
    $laboratorio    = $db->clearText($_REQUEST['laboratorio']);

    $where = "";
    $where_sucursal = "";

    if (!empty($proveedor)) {
        $where .= " AND pp.id_proveedor = $proveedor";
    }
    if (!empty($tipo)) {
        $where .= " AND p.id_tipo_producto = $tipo";
    }
    if (!empty($rubro)) {
        $where .= " AND p.id_rubro = $rubro";
    }
    if (!empty($procedencia)) {
        $where .= " AND p.id_pais = $procedencia";
    }
    if (!empty($origen)) {
        $where .= " AND p.id_origen = $origen";
    }
    if (!empty($clasificacion)) {
        $where .= " AND p.id_clasificacion = $clasificacion";
    }
    if (!empty($presentacion)) {
        $where .= " AND p.id_presentacion = $presentacion";
    }
    if (!empty($unidad_medida)) {
        $where .= " AND p.id_unidad_medida = $unidad_medida";
    }
    if (!empty($marca)) {
        $where .= " AND p.id_marca = $marca";
    }
    if (!empty($laboratorio)) {
        $where .= " AND p.id_laboratorio = $laboratorio";
    }
    if (!empty($estado) && intVal($estado) == 2) {
        $where = "AND (l.vencimiento >= CURRENT_DATE() OR l.vencimiento IS NULL)";
    } else if(!empty($estado) && intVal($estado) == 1) {
        $where = "AND l.vencimiento <= CURRENT_DATE()";
    }

    if (esAdmin($id_rol) === false) {
        $where_sucursal .= " AND s.id_sucursal=$id_sucursal_";
    } else if(!empty($sucursal) && intVal($sucursal) != 0) {
        $where_sucursal .= " AND s.id_sucursal=$sucursal";
    } else if(empty($sucursal)){
        $where_sucursal .= "";
    }

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                        IFNULL(SUM(s.stock), 0) AS stock,
                        IFNULL(SUM(s.fraccionado), 0) AS fraccionado,
                        pre.presentacion,
                        p.id_producto,
                        p.codigo,
                        DATE_FORMAT(NOW(),'%d/%m/%Y %H:%i:%s') AS fecha_actual,
                        p.producto,
                        p.precio,
                        p.precio_fraccionado,
                        l.vencimiento,
                        IF(l.vencimiento >= CURRENT_DATE(),'Activo','Vencido') AS estado
                    FROM productos p
                    LEFT JOIN stock s ON p.id_producto=s.id_producto 
                    LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                    LEFT JOIN lotes l ON s.id_lote = l.id_lote
                    LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto
                    WHERE 1=1 $where_sucursal $where 
                    GROUP BY p.id_producto");
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

    $mpdf->SetTitle("Stock"); 
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">INFORME DE STOCK</td>
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
                    <th class="tg-zqap">Codigo</th>
                    <th class="tg-zqap">Producto</th>
                    <th class="tg-zqap">Presentación</th>
                    <th class="tg-zqap">Cantidad</th>
                    <th class="tg-zqap">Fraccionado</th>
                    <th class="tg-zqap">Estado</th>
                </tr>
            </thead>
        <tbody>

    ');

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $codigo = $r->codigo;
        $producto = $r->producto;
        $presentacion = $r->presentacion;
        $cantidad = $r->stock;
        $fraccionado = $r->fraccionado;
        $estado = $r->estado;

        $mpdf->WriteHTML('
            <tr>
                <td class="tg-1x3m">'.$codigo.'</td>
                <td class="tg-1x3m">'.$producto.'</td>
                <td class="tg-1x3m">'.$presentacion.'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($cantidad).'</td>
                <td class="tg-1x3m" style="text-align:right">'.separadorMiles($fraccionado).'</td>
                <td class="tg-1x3m">'.$estado.'</td>
            </tr>
        ');
    }

    $mpdf->WriteHTML('
            </tbody>
        </table>
    ');

    
    $mpdf->Output("Stock.pdf", 'I');

