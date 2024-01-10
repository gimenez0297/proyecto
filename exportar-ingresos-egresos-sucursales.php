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

$db->setQuery("SELECT 
                    s.id_sucursal, 
                    s.sucursal, 
                    IFNULL(f.total_venta, 0) AS total_venta, 
                    IFNULL(f.total_costo, 0) AS total_costo, 
                    IFNULL(f.utilidad, 0) AS utilidad,
                    ROUND((IFNULL(f.utilidad, 0) / IFNULL(f.total_venta, 0)) * 100, 2) AS porcentaje_utilidad,
                    IFNULL(g.total_g, 0) AS total_gastos,
                    IFNULL(f.utilidad, 0) - IFNULL(g.total_g, 0) AS ganancias
                FROM sucursales s
                LEFT JOIN (
                    SELECT 
                        id_sucursal, 
                        SUM(total_venta) AS total_venta, 
                        SUM(total_costo) AS total_costo, 
                        SUM(total_venta) - SUM(total_costo) AS utilidad 
                    FROM facturas 
                    WHERE DATE(fecha_venta) BETWEEN '$desde' AND '$hasta'
                    GROUP BY id_sucursal
                ) f ON s.id_sucursal = f.id_sucursal
                LEFT JOIN (
                    SELECT 
                        id_sucursal, 
                        SUM(monto) AS total_g 
                    FROM gastos 
                    WHERE DATE(fecha_emision) BETWEEN '$desde' AND '$hasta' AND estado IN(1,2) 
                    GROUP BY id_sucursal 
                ) g ON s.id_sucursal = g.id_sucursal");
$rows = $db->loadObjectList();


$fecha_desde = ["DESDE:"];
$fecha_desde_str = [fechaLatina($desde)];

$fecha_hasta = ["HASTA:"];
$fecha_hasta_str = [fechaLatina($hasta)];

$encabezado = ["SUCURSAL", "UTILIDAD", "% UTILIDAD", "TOTAL GASTOS", "GANANCIAS"];

# Comenzamos en la fila 3
$coun = 2;

$documento = new Spreadsheet();
$Spreadsheet  = $documento->getActiveSheet();
$Spreadsheet->setTitle("Ingresos, Egresos y Ganancias");

$Spreadsheet->fromArray($fecha_desde, null, 'A1');
$Spreadsheet->fromArray($fecha_desde_str, null, 'B1');

$Spreadsheet->fromArray($fecha_hasta, null, 'C1');
$Spreadsheet->fromArray($fecha_hasta_str, null, 'D1');

$Spreadsheet->fromArray($encabezado, null, 'A2');

$documento
    ->getProperties()
    ->setCreator("Freelancers Py S.A.")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Ingresos,Egresos y Ganancias')
    ->setSubject('Excel')
    ->setDescription('Ingresos,Egresos y Ganancias')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$Spreadsheet->getColumnDimension('A')->setAutoSize(true);
$Spreadsheet->getColumnDimension('B')->setAutoSize(true);
$Spreadsheet->getColumnDimension('C')->setAutoSize(true);
$Spreadsheet->getColumnDimension('D')->setAutoSize(true);
$Spreadsheet->getColumnDimension('E')->setAutoSize(true);

$Spreadsheet->getStyle('1')->getFont()->setBold( true );
$Spreadsheet->getStyle('2')->getFont()->setBold( true );
// $Spreadsheet->getStyle('3')->getFont()->setBold( true );

$writer = new Xlsx($documento);

$total_ventas = 0;
$total_utilidad = 0;
$total_gastos = 0;
$total_porcentaje_utilidad = 0;

foreach ($rows as $r) {
    $coun++;
    $sucursal = $r->sucursal;
    $total_venta = $r->total_venta;
    $utilidad = $r->utilidad;
    $porcentaje_utilidad = $r->porcentaje_utilidad ?: 0;
    $total_gastos = $r->total_gastos;
    $ganancias = $r->ganancias;

    $total_ventas += $total_venta;
    $total_utilidad += $utilidad;
    $total_gastos_total += $total_gastos;
    $total_ganancias_total += $ganancias;

    $Spreadsheet->setCellValueByColumnAndRow(1, $coun, $sucursal);
    $Spreadsheet->setCellValueByColumnAndRow(2, $coun, $utilidad);
    $Spreadsheet->setCellValueByColumnAndRow(3, $coun, $porcentaje_utilidad);
    $Spreadsheet->setCellValueByColumnAndRow(4, $coun, $total_gastos);
    $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $ganancias);
}

if ($total_utilidad != 0 && $total_ventas != 0) {
    $total_porcentaje_utilidad = ROUND((($total_utilidad / $total_ventas) * 100), 2);
}

$Spreadsheet->getStyle($coun)->getFont()->setBold( true );

$Spreadsheet->setCellValueByColumnAndRow(1, $coun, 'TOTAL');
$Spreadsheet->setCellValueByColumnAndRow(2, $coun, $total_utilidad);
$Spreadsheet->setCellValueByColumnAndRow(3, $coun, $total_porcentaje_utilidad);
$Spreadsheet->setCellValueByColumnAndRow(4, $coun, $total_gastos_total);
$Spreadsheet->setCellValueByColumnAndRow(5, $coun, $total_ganancias_total);

$Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('C')->getNumberFormat()->setFormatCode('#,##0.00_ ;-#,##0.00');
$Spreadsheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
