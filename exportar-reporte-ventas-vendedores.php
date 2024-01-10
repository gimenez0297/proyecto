<?php

    require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');
    \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator(',');

    include "inc/funciones.php";
    if (!verificaLogin()) {
        echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
        exit;
    }

    $db                         = DataBase::conectar();
    $q                          = $_REQUEST['q'];
    $usuario                    = $auth->getUsername();
    $sucursal_usuario           = datosUsuario($usuario)->id_sucursal;
    
    $db->setQuery("SELECT * from sucursales where id_sucursal='$sucursal_usuario'");
	$datos_sucursal_usuario     = $db->loadObject();
    $sucursal_usuario_nombre    = $datos_sucursal_usuario->sucursal;
    $tipo_puesto_cajero               = TIPO_PUESTO_CAJERO;
    $id_rol                     = datosUsuario($usuario)->id_rol;

    $fecha_actual               = date('Y-m-d');
    $primer_dia                 = date('Y-m-01 00:00:00', strtotime($fecha_actual));
    $ultimo_dia                 = date('Y-m-t 23:59:59', strtotime($fecha_actual));

    /* FILTRO VENDEDORES */
    $desde                      = $db->clearText($_REQUEST['desde']) ? $db->clearText($_REQUEST['desde']).' 00:00:00' : "$primer_dia";
    $hasta                      = $db->clearText($_REQUEST['hasta']) ? $db->clearText($_REQUEST['hasta']).' 23:59:59' : "$ultimo_dia";
    
    $id_sucursal                = $db->clearText($_REQUEST["sucursal"]);
    $proveedor_principal        = $db->clearText($_REQUEST['proveedor']);
    $id_vendedor                = $db->clearText($_REQUEST["vendedor"]);
    /**/

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
        $titulo_proveedor = ["Proveedor"];
        $desc_proveedor = ["$name_proveedor"];
    }
    
    
    // VENDEDOR
    if (!empty($id_vendedor) && intVal($id_vendedor) != 0) {

        $db->setQuery("SELECT funcionario FROM funcionarios WHERE id_funcionario = $id_vendedor;");
        $name_vendedor = $db->loadObject()->funcionario;
        $and_id_vendedor = " AND f.id_funcionario = $id_vendedor ";
        $titulo_vendedor = ["Vendedor"];
        $desc_vendedor = ["$name_vendedor"];
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
    /*quita limite de memoria*/
        //ini_set("memory_limit","-1");

    $fecha_desde = ["DESDE"];
    $fecha_desde_str = [fechaLatina($desde)];

    $fecha_hasta = ["HASTA"];
    $fecha_hasta_str = [fechaLatina($hasta)];

    $fecha_current = ["IMPRESIÓN"];
    $fecha_current_str = [fechaLatina($fecha_actual)];

    $encabezado = ["N°", "VENDEDOR", "SUCURSAL", "CANT. VENTAS", "VENTAS ₲S", "COSTO", "UTILIDAD", "MARGEN", "PARTICIPACIÓN"];

    # Comenzamos en la fila 3
    $coun = 2;

    $documento = new Spreadsheet();
    $Spreadsheet  = $documento->getActiveSheet();
    $Spreadsheet->setTitle("Ventas por Vendedor");

    $Spreadsheet->fromArray($fecha_desde, null, 'A1');
    $Spreadsheet->fromArray($fecha_desde_str, null, 'B1');

    $Spreadsheet->fromArray($fecha_hasta, null, 'C1');
    $Spreadsheet->fromArray($fecha_hasta_str, null, 'D1');

    $Spreadsheet->fromArray($fecha_current, null, 'E1');
    $Spreadsheet->fromArray($fecha_current_str, null, 'F1');

    /* SI EXISTE FILTRO DE VENDEDOR Y DE PROVEEDOR SE AGREGAN AMBOS TITULOS UNO JUTO AL OTRO,
    SI SOLO EXISTE UNO DE ELLOS, SE AGREGARÁ EL CORRESPODIENTE JUSTO A LA FECHA ACTUAL 
    */
    if (!empty($titulo_vendedor) && !empty($titulo_proveedor)) {
        $Spreadsheet->fromArray($titulo_vendedor, null, 'G1');
        $Spreadsheet->fromArray($desc_vendedor, null, 'H1');
        $Spreadsheet->fromArray($titulo_proveedor, null, 'I1');
        $Spreadsheet->fromArray($desc_proveedor, null, 'J1');
    }else if (!empty($titulo_vendedor)) {
        $Spreadsheet->fromArray($titulo_vendedor, null, 'J1');
        $Spreadsheet->fromArray($desc_vendedor, null, 'H1');
    }else if (!empty($titulo_proveedor)) {
        $Spreadsheet->fromArray($titulo_proveedor, null, 'G1');
        $Spreadsheet->fromArray($desc_proveedor, null, 'H1');
    }
    
    $Spreadsheet->fromArray($encabezado, null, 'A2');

    $documento
        ->getProperties()
        ->setCreator("Freelancers Py S.A.")
        ->setLastModifiedBy('BaulPHP')
        ->setTitle('Ingresos,Egresos y Ganancias')
        ->setSubject('Excel')
        ->setDescription('Ventas por Vendedores')
        ->setKeywords('PHPSpreadsheet')
        ->setCategory('Categoría Cvs')
    ;

    $Spreadsheet->getColumnDimension('A')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('B')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('C')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('D')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('E')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('F')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('G')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('H')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('I')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('J')->setAutoSize(true);

    $Spreadsheet->getStyle('1')->getFont()->setBold( true );
    $Spreadsheet->getStyle('2')->getFont()->setBold( true );

    $writer = new Xlsx($documento);

    $coun = 2;
    $r_numero = $r_cant_ventas = $r_ventas = $r_costos = $r_utilidad = $r_margen = $r_participacion = 
    $total_cant_ventas = $total_utilidad = $total_margen = $total_ventas = $total_costos = $total_participacion = 0;
    $r_vendedor = $r_sucursal = "";

    foreach ($rows as $r) {
        $coun++;
        $r_numero           = $coun-2;
        $r_vendedor         = $r->nombre_vendedor;
        $r_sucursal         = $r->sucursal;
        $r_cant_ventas      = $r->cantidad_venta?:0;
        $r_ventas           = $r->venta?:0;
        $r_costos           = $r->costo?:0;
        $r_utilidad         = $r->utilidad?:0;
        $r_margen           = $r->margen?:0;
        $r_participacion    = $r->participacion?:0;

        $total_cant_ventas   += $r_cant_ventas?:0;
        $total_ventas        += $r_ventas?:0;
        $total_costos        += $r_costos?:0;
        $total_utilidad      += $r_utilidad?:0;
        $total_participacion += $r_participacion?:0;

        $Spreadsheet->setCellValueByColumnAndRow(1, $coun, $r_numero);              // A
        $Spreadsheet->setCellValueByColumnAndRow(2, $coun, $r_vendedor);            // B
        $Spreadsheet->setCellValueByColumnAndRow(3, $coun, $r_sucursal);            // C
        $Spreadsheet->setCellValueByColumnAndRow(4, $coun, $r_cant_ventas);         // D
        $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $r_ventas);              // E
        $Spreadsheet->setCellValueByColumnAndRow(6, $coun, $r_costos);              // F
        $Spreadsheet->setCellValueByColumnAndRow(7, $coun, $r_utilidad);            // G
        $Spreadsheet->setCellValueByColumnAndRow(8, $coun, $r_margen);              // H
        $Spreadsheet->setCellValueByColumnAndRow(9, $coun, $r_participacion);       // I
    }

    $coun++;

    if ($total_ventas != 0) {
        $total_margen = round(($total_utilidad / $total_ventas) * 100, 2);
    }

    $Spreadsheet->getStyle($coun)->getFont()->setBold( true );

    $Spreadsheet->setCellValueByColumnAndRow(1, $coun, 'TOTALES');
    $Spreadsheet->setCellValueByColumnAndRow(4, $coun, $total_cant_ventas);
    $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $total_ventas);
    $Spreadsheet->setCellValueByColumnAndRow(6, $coun, $total_costos);
    $Spreadsheet->setCellValueByColumnAndRow(7, $coun, $total_utilidad);
    $Spreadsheet->setCellValueByColumnAndRow(8, $coun, $total_margen);
    $Spreadsheet->setCellValueByColumnAndRow(9, $coun, $total_participacion);

    $Spreadsheet->getColumnDimension('A')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('B')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('C')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('D')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('E')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('F')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('G')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('H')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('I')->setAutoSize(true);

    $Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('0');
    $Spreadsheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $Spreadsheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $Spreadsheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $Spreadsheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $Spreadsheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

    header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
    header("Content-Type: application/vnd.ms-excel charset=utf-8");
    $writer->save('php://output');
?>