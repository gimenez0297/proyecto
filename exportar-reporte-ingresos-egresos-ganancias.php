<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator(',');

include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $desde 		  = $db->clearText($_REQUEST["desde"]);
    $hasta 		  = $db->clearText($_REQUEST["hasta"]);
	$id_sucursal  = $db->clearText($_REQUEST['id_sucursal']);

    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
    $row     = $db->loadObject();
    $sucursal = $row->sucursal;

    //INGRESOS
    $db->setQuery("SELECT 
                        DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha_emision,
                        CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, f.numero) AS nro_documento,
                        f.id_factura,
                        f.razon_social,
                        f.total_venta,
                        f.total_costo,
                        (
                            SELECT SUM(total_venta) - SUM(total_costo) 
                            FROM facturas 
                            WHERE id_factura = f.id_factura
                        ) AS utilidad,
                        ROUND((IFNULL((
                            SELECT SUM(total_venta) - SUM(total_costo) 
                            FROM facturas 
                            WHERE id_factura = f.id_factura
                        ), 0) / f.total_venta) * 100, 2) AS porcentaje_utilidad
                    FROM facturas f
                    LEFT JOIN timbrados t ON t.id_timbrado=f.id_timbrado
                    WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' 
                        AND f.id_sucursal = $id_sucursal");
    $ingresos = $db->loadObjectList();

    //EGRESOS
    $db->setQuery("SELECT 
                        DATE_FORMAT(fecha_emision, '%d/%m/%Y') AS fecha_emision,
                        id_gasto,
                        documento AS nro_documento,
                        razon_social,
                        monto AS total_venta
                    FROM gastos 
                    WHERE fecha_emision BETWEEN '$desde' AND '$hasta'
                        AND id_sucursal = $id_sucursal 
                        AND estado IN (1, 2)");
    $egresos = $db->loadObjectList();

    $documento = new Spreadsheet();
    $documento
    ->getProperties()
    ->setCreator("ABS Montajes S.A")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Ingresos y Egresos')
    ->setSubject('Excel')
    ->setDescription('Ingresos y Egresos')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');
    
    $hojaDeIngresos = $documento->getActiveSheet();
    $hojaDeIngresos->setTitle("Ingresos");
    
    # Encabezado de los ingresos
    $encabezado = ["FECHA VENTA", "N° DOCUMENTO", "RAZON SOCIAL/NOMBRE", "UTILIDAD", "% UTILIDAD"];

    $desde_titulo = ["DESDE:"];
    $desde_str = [fechaLatina($desde)];

    $hasta_titulo = ["HASTA:"];
    $hasta_str = [fechaLatina($hasta)];

    $sucursal_titulo = ["SUCURSAL:"];
    $sucursal_str = [$sucursal];

    $hojaDeIngresos->fromArray($desde_titulo, null, 'A1');
    $hojaDeIngresos->fromArray($desde_str, null, 'B1');

    $hojaDeIngresos->fromArray($hasta_titulo, null, 'A2');
    $hojaDeIngresos->fromArray($hasta_str, null, 'B2');

    $hojaDeIngresos->fromArray($sucursal_titulo, null, 'A3');
    $hojaDeIngresos->fromArray($sucursal_str, null, 'B3');

    $hojaDeIngresos->fromArray($encabezado, null, 'A5');

    $fileName = "ingresosEgresosGanancias.xls";

    $total_ventas = 0;
    $total_utilidad = 0;
    $total_porcentaje_utilidad = 0;
    $coun = 6;

    foreach ($ingresos as $in)  {
        $fecha_venta        = $in->fecha_emision;
        $nro_documento      = $in->nro_documento;
        $razon_social       = $in->razon_social;
        $total_venta        = $in->total_venta;
        $utilidad           = $in->utilidad;
        $porcentaje_utilidad= $in->porcentaje_utilidad;

        $total_ventas       += $total_venta;
        $total_utilidad     += $utilidad;

        # Escribir registros en el documento
        $hojaDeIngresos->setCellValueByColumnAndRow(1, $coun, $fecha_venta);
        $hojaDeIngresos->setCellValueByColumnAndRow(2, $coun, $nro_documento);
        $hojaDeIngresos->setCellValueByColumnAndRow(3, $coun, $razon_social);
        $hojaDeIngresos->setCellValueByColumnAndRow(4, $coun, $utilidad);
        $hojaDeIngresos->setCellValueByColumnAndRow(5, $coun, $porcentaje_utilidad);
        $coun++;
    }

    $coun++;

    if ($total_utilidad != 0 && $total_ventas != 0) {
        $total_porcentaje_utilidad = ROUND((($total_utilidad / $total_ventas) * 100), 2);
    }

    $hojaDeIngresos->setCellValueByColumnAndRow(3, $coun, 'TOTAL');
    $hojaDeIngresos->setCellValueByColumnAndRow(4, $coun, $total_ventas);
    $hojaDeIngresos->setCellValueByColumnAndRow(5, $coun, $total_porcentaje_utilidad);
    
    # Ahora creamos la hoja "egresos"
    $hojaEgresos = $documento->createSheet();
    $hojaEgresos->setTitle("Egresos");
    
    # Declaramos el encabezado
    $encabezado = ["FECHA VENTA", "N° DOCUMENTO", "RAZON SOCIAL/NOMBRE", "IMPORTE"];
    $hojaEgresos->fromArray($desde_titulo, null, 'A1');
    $hojaEgresos->fromArray($desde_str, null, 'B1');

    $hojaEgresos->fromArray($hasta_titulo, null, 'A2');
    $hojaEgresos->fromArray($hasta_str, null, 'B2');

    $hojaEgresos->fromArray($sucursal_titulo, null, 'A3');
    $hojaEgresos->fromArray($sucursal_str, null, 'B3');
    $hojaEgresos->fromArray($encabezado, null, 'A5');
    
    $total_total_e=0;
    $counp = 6;
    foreach ($egresos as $eg) {
        $fecha_venta_e        = $eg->fecha_emision;
        $nro_documento_e      = $eg->nro_documento;
        $razon_social_e       = $eg->razon_social;
        $importe_e            = $eg->total_venta;
        $total_total_e       += $importe_e;
        
        # Escribir en el documento
        $hojaEgresos->setCellValueByColumnAndRow(1, $counp, $fecha_venta_e);
        $hojaEgresos->setCellValueByColumnAndRow(2, $counp, $nro_documento_e);
        $hojaEgresos->setCellValueByColumnAndRow(3, $counp, $razon_social_e);
        $hojaEgresos->setCellValueByColumnAndRow(4, $counp, $importe_e);
        $counp++;
    }
    $counp++;

    $hojaEgresos->setCellValueByColumnAndRow(3, $counp, 'TOTAL');
    $hojaEgresos->setCellValueByColumnAndRow(4, $counp, $total_total_e);
    # Crear un "escritor"
    $writer = new Xlsx($documento);

    $hojaDeIngresos->getColumnDimension('A')->setAutoSize(true);
    $hojaDeIngresos->getColumnDimension('B')->setAutoSize(true);
    $hojaDeIngresos->getColumnDimension('C')->setAutoSize(true);
    $hojaDeIngresos->getColumnDimension('D')->setAutoSize(true);

    $hojaEgresos->getColumnDimension('A')->setAutoSize(true);
    $hojaEgresos->getColumnDimension('B')->setAutoSize(true);
    $hojaEgresos->getColumnDimension('C')->setAutoSize(true);
    $hojaEgresos->getColumnDimension('D')->setAutoSize(true);

    $hojaDeIngresos->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

    $hojaEgresos->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

    $hojaDeIngresos->getStyle('1')->getFont()->setBold( true );
    $hojaDeIngresos->getStyle('2')->getFont()->setBold( true );
    $hojaDeIngresos->getStyle('3')->getFont()->setBold( true );
    $hojaDeIngresos->getStyle('5')->getFont()->setBold( true );
    $hojaEgresos->getStyle('1')->getFont()->setBold( true );
    $hojaEgresos->getStyle('2')->getFont()->setBold( true );
    $hojaEgresos->getStyle('3')->getFont()->setBold( true );
    $hojaEgresos->getStyle('5')->getFont()->setBold( true );

    # Ahora creamos la hoja "Ganancias"
    $hojaGanancia = $documento->createSheet();
    $hojaGanancia->setTitle("Ganancias");
    $ganancia = $total_ventas - $total_total_e;

    # Declaramos el encabezado
    $encabezado = ["TOTAL VENTAS", "TOTAL GASTOS", "GANANCIAS"];
    $hojaGanancia->fromArray($desde_titulo, null, 'A1');
    $hojaGanancia->fromArray($desde_str, null, 'B1');

    $hojaGanancia->fromArray($hasta_titulo, null, 'A2');
    $hojaGanancia->fromArray($hasta_str, null, 'B2');

    $hojaGanancia->fromArray($sucursal_titulo, null, 'A3');
    $hojaGanancia->fromArray($sucursal_str, null, 'B3');
    $hojaGanancia->fromArray($encabezado, null, 'A5');

    $counx=6;
    # Escribir en el documento
    $hojaGanancia->setCellValueByColumnAndRow(1, $counx, $total_ventas);
    $hojaGanancia->setCellValueByColumnAndRow(2, $counx, $total_total_e);
    $hojaGanancia->setCellValueByColumnAndRow(3, $counx, $ganancia);
   
    # Crear un "escritor"
    $writer = new Xlsx($documento);

    $hojaDeIngresos->getColumnDimension('A')->setAutoSize(true);
    $hojaDeIngresos->getColumnDimension('B')->setAutoSize(true);
    $hojaDeIngresos->getColumnDimension('C')->setAutoSize(true);
    $hojaDeIngresos->getColumnDimension('D')->setAutoSize(true);
    $hojaDeIngresos->getColumnDimension('E')->setAutoSize(true);

    $hojaEgresos->getColumnDimension('A')->setAutoSize(true);
    $hojaEgresos->getColumnDimension('B')->setAutoSize(true);
    $hojaEgresos->getColumnDimension('C')->setAutoSize(true);
    $hojaEgresos->getColumnDimension('D')->setAutoSize(true);

    $hojaGanancia->getColumnDimension('A')->setAutoSize(true);
    $hojaGanancia->getColumnDimension('B')->setAutoSize(true);
    $hojaGanancia->getColumnDimension('C')->setAutoSize(true);
    $hojaGanancia->getColumnDimension('D')->setAutoSize(true);

    $hojaDeIngresos->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $hojaDeIngresos->getStyle('E')->getNumberFormat()->setFormatCode('#,##0.00_ ;-#,##0.00');

    $hojaEgresos->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

    $hojaGanancia->getStyle('A')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $hojaGanancia->getStyle('B')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $hojaGanancia->getStyle('C')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

    $hojaDeIngresos->getStyle('1')->getFont()->setBold( true );
    $hojaDeIngresos->getStyle('2')->getFont()->setBold( true );
    $hojaDeIngresos->getStyle('3')->getFont()->setBold( true );
    $hojaDeIngresos->getStyle('5')->getFont()->setBold( true );
    $hojaDeIngresos->getStyle($coun)->getFont()->setBold( true );

    $hojaEgresos->getStyle('1')->getFont()->setBold( true );
    $hojaEgresos->getStyle('2')->getFont()->setBold( true );
    $hojaEgresos->getStyle('3')->getFont()->setBold( true );
    $hojaEgresos->getStyle('5')->getFont()->setBold( true );
    $hojaEgresos->getStyle($counp)->getFont()->setBold( true );

    $hojaGanancia->getStyle('1')->getFont()->setBold( true );
    $hojaGanancia->getStyle('2')->getFont()->setBold( true );
    $hojaGanancia->getStyle('3')->getFont()->setBold( true );
    $hojaGanancia->getStyle('5')->getFont()->setBold( true );

    header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
    header("Content-Type: application/vnd.ms-excel charset=utf-8");
    $writer->save('php://output');

