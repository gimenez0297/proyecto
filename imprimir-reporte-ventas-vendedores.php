<?php
    include "inc/funciones.php";
    if (!verificaLogin()) {
        echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
        exit;
    }

    $db                = DataBase::conectar();
    
    $q           = $_REQUEST['q'];
    $usuario     = $auth->getUsername();
    $sucursal_usuario = datosUsuario($usuario)->id_sucursal;
    
    $db->setQuery("SELECT * from sucursales where id_sucursal='$sucursal_usuario'");
	$datos_sucursal_usuario = $db->loadObject();
    $sucursal_usuario_nombre = $datos_sucursal_usuario->sucursal;

    $tipo_puesto_cajero = TIPO_PUESTO_CAJERO;
    $id_rol = datosUsuario($usuario)->id_rol;

    $fecha_actual = date('Y-m-d');
    $primer_dia = date('Y-m-01 00:00:00', strtotime($fecha_actual));
    $ultimo_dia = date('Y-m-t 23:59:59', strtotime($fecha_actual));



    /* FILTRO VENDEDORES */
        $desde                      = $db->clearText($_REQUEST['desde']) ? $db->clearText($_REQUEST['desde']).' 00:00:00' : "$primer_dia";
        $hasta                      = $db->clearText($_REQUEST['hasta']) ? $db->clearText($_REQUEST['hasta']).' 23:59:59' : "$ultimo_dia";
        
        $id_sucursal                = $db->clearText($_REQUEST["sucursal"]);
        $proveedor_principal        = $db->clearText($_REQUEST['proveedor']);
        $id_vendedor                = $db->clearText($_REQUEST["vendedor"]);
    /**/

    /* Parametros de ordenamiento, busqueda y paginacion */
    $search = $db->clearText($_REQUEST['search']);

    // SUCURSAL
    if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        $and_id_sucursal_f = " AND f.id_sucursal = $id_sucursal ";
        $and_id_sucursal_fff = " AND fff.id_sucursal = $id_sucursal ";
        $and_id_sucursal_factura = " AND facturas.id_sucursal = $id_sucursal ";
        $and_id_sucursal = " AND id_sucursal = $id_sucursal ";
        
        $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal = $id_sucursal");
        $sucursal = $db->loadObject()->sucursal;
    }else{
        if (esAdmin($id_rol)) {
            $and_id_sucursal_fff = "";
            $and_id_sucursal_factura = "";
            $and_id_sucursal_f = "";
            $and_id_sucursal = "";
            $sucursal = "TODAS";
        } else {
            $and_id_sucursal_f = " AND f.id_sucursal = $sucursal_usuario ";
            $and_id_sucursal_fff = " AND fff.id_sucursal = $sucursal_usuario ";
            $and_id_sucursal_factura = " AND facturas.id_sucursal = $sucursal_usuario ";
            $and_id_sucursal = " AND id_sucursal = $sucursal_usuario ";
            $sucursal = "$sucursal_usuario_nombre";
        }
    };

    if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        $and_id_sucursal_f = " AND f.id_sucursal = $id_sucursal ";
        $and_id_sucursal_fff = " AND fff.id_sucursal = $id_sucursal ";
        $and_id_sucursal_factura = " AND facturas.id_sucursal = $id_sucursal ";
        $and_id_sucursal = " AND id_sucursal = $id_sucursal ";
    }else{
        
    };

    // PROVEEDOR PRINCIPAL
    if (!empty($proveedor_principal) && intVal($proveedor_principal) != 0) {
        $where_proveedor_principal =
        " WHERE id_producto IN (
            SELECT id_producto FROM productos_proveedores
            WHERE proveedor_principal = 1
            AND id_proveedor = $proveedor_principal
            GROUP BY id_producto
        ) ";

        $where_proveedor_principal_fp =
        " AND facturas_productos.id_producto IN (
            SELECT id_producto FROM productos_proveedores
            WHERE proveedor_principal = 1
            AND id_proveedor = $proveedor_principal
            GROUP BY id_producto
        ) ";
        
        $where_proveedor_principal_fpp =
        " AND fpp.id_producto IN (
            SELECT id_producto FROM productos_proveedores
            WHERE proveedor_principal = 1
            AND id_proveedor = $proveedor_principal
            GROUP BY id_producto
        ) ";


        $db->setQuery("SELECT proveedor FROM proveedores WHERE id_proveedor = $proveedor_principal;");
        $name_proveedor = $db->loadObject()->proveedor;
        $as_proveedor = " '$name_proveedor' AS proveedor_p_nombre, ";
    } else {
        $where_proveedor_principal_fp = "";
        $where_proveedor_principal_fpp = "";
        $where_proveedor_principal = "";
        $name_proveedor = "";
        $as_proveedor = "";
    }

    if (empty($name_proveedor)) {
        $titulo_proveedor = "";
        $desc_proveedor = "";
    }else {
        $titulo_proveedor = 
        "
        <td width='2%'>&nbsp;</td>
        <td style='font-weight:bold;vertical-align:middle; ' >Proveedor:</td>
        ";
        $desc_proveedor = 
        "
        <td width='2%'>&nbsp;</td>
        <td  style='border: 1px solid;padding:2px;font-size:14px; text-align:left;'>".$name_proveedor."</td>
        ";
    }
    
    
    // VENDEDOR
    if (!empty($id_vendedor) && intVal($id_vendedor) != 0) {

        $db->setQuery("SELECT funcionario FROM funcionarios WHERE id_funcionario = $id_vendedor;");
        $name_vendedor = $db->loadObject()->funcionario;

        $and_id_vendedor = " AND f.id_funcionario = $id_vendedor ";

        $titulo_vendedor = 
        "
        <td width='2%'>&nbsp;</td>
        <td style='font-weight:bold;vertical-align:middle; ' >Vendedor:</td>
        ";

        $desc_vendedor = 
        "
        <td width='2%'>&nbsp;</td>
        <td  style='border: 1px solid;padding:2px;font-size:14px; text-align:left;'>".$name_vendedor."</td>
        ";
    } else {
        $titulo_vendedor = "";
        $desc_vendedor = "";
        $and_id_vendedor = "";
    }

    $query ="SELECT *, 
        (venta - costo) AS utilidad, 
        IFNULL((((venta - costo)/venta)*100), 0) AS margen, $as_proveedor
        -- Participación
        IFNULL((
            (
                (
                    SELECT COUNT(id_factura)
                    FROM facturas
                    -- where
                    WHERE estado = 1
                    $and_id_sucursal
                    AND usuario = tab.username
                    AND (fecha_venta BETWEEN '$desde' AND '$hasta')
                    AND facturas.id_factura IN (
                        SELECT id_factura FROM facturas_productos
                        $where_proveedor_principal
                    )
                    
                )
                /
                (
                SELECT SUM(cant_ventas) FROM(
                    SELECT
                    (
                        SELECT COUNT(id_factura)
                            FROM facturas
                            -- where
                            WHERE estado = 1
                            $and_id_sucursal
                            AND usuario = u.username
                            AND (fecha_venta BETWEEN '$desde' AND '$hasta')
                            AND facturas.id_factura IN (
                                SELECT id_factura FROM facturas_productos
                                $where_proveedor_principal
                            )    
                            
                        ) AS cant_ventas
        
                        FROM funcionarios f
                        INNER JOIN users u ON u.id = f.id_usuario
                        INNER JOIN sucursales s ON s.id_sucursal = f.id_sucursal
        
                        WHERE 
                        f.id_puesto IN(SELECT id_puesto FROM puestos WHERE id_tipo_puesto = $tipo_puesto_cajero)
                        $and_id_sucursal_f
                        -- AND u.status = 0
                        AND ((f.fecha_baja = '0000-00-00') OR (f.fecha_baja BETWEEN '$desde' AND '$hasta'))
                    ) vtas
                )
            ) * 100
        ), 0) AS participacion
        
        FROM(
            SELECT f.id_funcionario AS id_vendedor, f.funcionario AS nombre_vendedor, u.username, u.status, f.estado, f.id_sucursal, s.sucursal,
                IFNULL((
                    SELECT COUNT(id_factura)
                    FROM facturas
                    -- where
                    WHERE estado = 1
                    $and_id_sucursal_f
                    AND usuario = u.username
                    AND (fecha_venta BETWEEN '$desde' AND '$hasta')
                    AND facturas.id_factura IN (
                        SELECT id_factura FROM facturas_productos
                        $where_proveedor_principal
                    )    
                ),0) AS cantidad_venta,
            
                IFNULL((
                    SELECT SUM(facturas_productos.total_venta)
                    FROM facturas_productos
                    INNER JOIN facturas ON facturas.id_factura = facturas_productos.id_factura
                    -- where
                    WHERE facturas.estado = 1
                    $and_id_sucursal_factura
                    AND facturas.usuario = u.username
                    AND (facturas.fecha_venta BETWEEN '$desde' AND '$hasta')
                    AND facturas.id_factura IN (
                        SELECT id_factura FROM facturas_productos
                        $where_proveedor_principal
                    )
                    $where_proveedor_principal_fp
                    
                ),0) AS venta,
            
                IFNULL(total_costo.total_costo_sum, 0) AS costo
            
            FROM funcionarios f
            INNER JOIN users u ON u.id = f.id_usuario
            INNER JOIN sucursales s ON s.id_sucursal = f.id_sucursal
            LEFT JOIN (
                SELECT
                    fff.usuario,
                    SUM(
                        CASE
                            WHEN fpp.fraccionado = 1 THEN
                                ROUND(
                                    (SELECT IFNULL(costo, 0) FROM lotes WHERE id_lote = fpp.id_lote) /
                                    (SELECT cantidad_fracciones FROM productos WHERE id_producto = fpp.id_producto) *
                                    fpp.cantidad
                                )
                            ELSE
                                ROUND((SELECT IFNULL(costo, 0) FROM lotes WHERE id_lote = fpp.id_lote) * fpp.cantidad)
                        END
                    ) AS total_costo_sum
                FROM facturas_productos fpp
                INNER JOIN facturas fff ON fff.id_factura = fpp.id_factura
                WHERE fff.estado = 1
                $and_id_sucursal_fff
                AND (fff.fecha_venta BETWEEN '$desde' AND '$hasta')
                AND fff.id_factura IN (
                    SELECT id_factura FROM facturas_productos
                    $where_proveedor_principal
                )
                $where_proveedor_principal_fpp
                GROUP BY fff.usuario
            ) AS total_costo ON total_costo.usuario = u.username
            WHERE 
            f.id_puesto IN(SELECT id_puesto FROM puestos WHERE id_tipo_puesto = $tipo_puesto_cajero)
            -- AND u.status = 0
            $and_id_sucursal_f
            $and_id_vendedor
            AND ((f.fecha_baja = '0000-00-00') OR (f.fecha_baja BETWEEN '$desde' AND '$hasta'))
        )tab
        ORDER BY sucursal asc, nombre_vendedor asc
    ;";

    $db->setQuery($query);

    $rows = $db->loadObjectList();

    // Logos
    $logo_farmacia = "dist/images/logo.png";

     // MPDF
     require_once __DIR__ . '/mpdf/vendor/autoload.php';
    /*quita limite de memoria*/
        //ini_set("memory_limit","-1");
  
    $mpdf = new \Mpdf\Mpdf([
         'mode' => 'utf-8',
         'format' => 'A4',
         'orientation' => 'L',
         'margin_left' => 5,
         'margin_right' => 5,
         'margin_top' => 50,
         'margin_bottom' => 15,
    ]);

    $mpdf->SetTitle("Ventas por Vendedor");
    $mpdf->SetHTMLHeader('
        <table class="tc" width="100%">
            <thead>
                <tr>
                    <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                    <td class="tc-axmw">Ventas por Vendedores</td>
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
                    <td style="font-weight:bold;vertical-align:middle" width="15%">Fecha de Impresión:</td>
                    <td width="2%">&nbsp;</td>
                    <td style="font-weight:bold;text-align:left;vertical-align:middle">Sucursal:</td>
                    '.$titulo_proveedor.'
                    '.$titulo_vendedor.'
                </tr>
                <tr>
                    <td  style="border: 1px solid;font-size:14px;text-align:left;">'.fechaLatina($desde).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.fechaLatina($hasta).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;text-align:left;">'.date('d/m/Y', strtotime($fecha_actual)).'</td>
                    <td width="2%">&nbsp;</td>
                    <td  style="border: 1px solid;padding:2px;font-size:14px;">'.$sucursal.'</td>
                    '.$desc_proveedor.'
                    '.$desc_vendedor.'
                </tr>
            </thead>
        </table>
        <hr>
        <br>
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

    $mpdf->writehtml(
        "
        <table class='tg' style='width:100%'>
            <thead>
                <tr style='background-color: #b5b5b526;'>
                    <th class='tg-zqap'>N°</th>
                    <th class='tg-zqap'>VENDEDOR</th>
                    <th class='tg-zqap'>SUCURSAL</th>
                    <th class='tg-zqap'>CANT. VENTAS</th>
                    <th class='tg-zqap'>VENTAS ₲S</th>
                    <th class='tg-zqap'>COSTO</th>
                    <th class='tg-zqap'>UTILIDAD</th>
                    <th class='tg-zqap'>MARGEN</th>
                    <th class='tg-zqap'>PARTICIPACIÓN</th>
                </tr>
            </thead>
            
            <tbody>
        "
    );

    $coun = 0;
    foreach ($rows as $r) {
        $coun++;
        $r_numero           = $coun;
        $r_vendedor         = $r->nombre_vendedor;
        $r_sucursal         = $r->sucursal;
        $r_cant_ventas      = $r->cantidad_venta?:0;
        $r_ventas           = $r->venta?:0;
        $r_costos           = $r->costo?:0;
        $r_utilidad         = $r->utilidad?:0;
        $r_margen           = $r->margen?:0;
        $r_participacion    = $r->participacion?:0;



        $total_cant_ventas   += $r_cant_ventas?:0;
        $total_utilidad      += $r_utilidad?:0;
        $total_ventas        += $r_ventas?:0;
        $total_costos        += $r_costos?:0;
        $total_participacion += $r_participacion?:0;

        $mpdf->WriteHTML("
            <tr>
                <td class='tg-1x3m' style='text-align:center;'>$r_numero</td>
                <td class='tg-1x3m' style='text-align:left;'>$r_vendedor</td>
                <td class='tg-1x3m' style='text-align:left;'>$r_sucursal</td>
                <td class='tg-1x3m' style='text-align:right;'>".separadorMiles($r_cant_ventas)."</td>
                <td class='tg-1x3m' style='text-align:right;'>".separadorMiles($r_ventas)."</td>
                <td class='tg-1x3m' style='text-align:right;'>".separadorMiles($r_costos)."</td>
                <td class='tg-1x3m' style='text-align:right;'>".separadorMiles($r_utilidad)."</td>
                <td class='tg-1x3m' style='text-align:right;'>".round($r_margen, 2)."%</td>
                <td class='tg-1x3m' style='text-align:right;'>".round($r_participacion, 2)."%</td>
            </tr>
        ");
        $sucursal_anterior = $r_sucursal;
    }
    if ($total_ventas != 0) {
        $total_margen = round(($total_utilidad / $total_ventas) * 100, 2);
    }

    $mpdf->WriteHTML('
        <tr>
            <td class="total" style="font-size:12px">TOTALES </td>
            <td class="total" style="text-align:right; font-size:12px" ></td>
            <td class="total" style="text-align:right; font-size:12px" ></td>
            <td class="total" style="text-align:right; font-size:12px" >' . separadorMiles($total_cant_ventas) . '</td>
            <td class="total" style="text-align:right; font-size:12px" >₲ ' . separadorMiles($total_ventas) . '</td>
            <td class="total" style="text-align:right; font-size:12px" >₲ ' . separadorMiles($total_costos) . '</td>
            <td class="total" style="text-align:right; font-size:12px" >₲ ' . separadorMiles($total_utilidad) . '</td>
            <td class="total" style="text-align:right; font-size:12px" >'   . round($total_margen, 2) . '%</td>
            <td class="total" style="text-align:right; font-size:12px" >'   . round($total_participacion, 2) . '%</td>
        </tr>
    ');

    $mpdf->WriteHTML('
            </tbody>
        </table>
    ');

    $mpdf->Output("Reporte_Ventas_Vendedores.pdf", 'I');

?>