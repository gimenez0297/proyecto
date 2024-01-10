<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');

include "inc/funciones.php";
$db       = DataBase::conectar();
$usuario  = $auth->getUsername();
$desde             = $_REQUEST["desde"];
$hasta             = $_REQUEST["hasta"];
$id_sucursal       = $_REQUEST["sucursal"];
$columnas          = json_decode($_REQUEST["columnas"],true);
$encabezado        = json_decode($_REQUEST["titulos"],true);

if (!empty($id_sucursal) && intVal($id_sucursal) != 0) {
    $and_id_sucursal .= " AND id_sucursal= $id_sucursal";
    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
    $row     = $db->loadObject();
    $sucursal = $row->sucursal;
}else{
    $and_id_sucursal .= "";
    $sucursal = "TODOS";
}

$db->setQuery("SELECT SQL_CALC_FOUND_ROWS
            fecha_venta,
            DATE_FORMAT(fecha_venta, '%d/%m/%Y') AS fecha,
            ruc,
            id_cliente,
            razon_social,
            SUM(cantidad) AS cantidad,
            SUM(total_venta) AS total

            FROM facturas 
            WHERE estado != 2 AND DATE(fecha_venta) BETWEEN '$desde' AND '$hasta' $and_id_sucursal
            GROUP BY id_cliente");
$rows = $db->loadObjectList();

$db->setQuery("SELECT * FROM sucursales WHERE id_sucursal='$id_sucursal'");
$row_s = $db->loadObject();

# Encabezado de las ventas
// $encabezado = ["RUC", "RAZÓN SOCIAL", "FECHA", "CANTIDAD", "TOTAL VENTA"];

$fecha_desde = ["DESDE:"];
$fecha_desde_str = [fechaLatina($desde)];

$fecha_hasta = ["HASTA:"];
$fecha_hasta_str = [fechaLatina($hasta)];

$sucursal_titulo = ["SUCURSAL:"];
$sucursal_str = [$sucursal];

# Comenzamos en la fila 2
$coun = 3;

$documento = new Spreadsheet();
$Spreadsheet  = $documento->getActiveSheet();
$Spreadsheet->setTitle("Ranking de Clientes");

$Spreadsheet->fromArray($fecha_desde, null, 'A1');
$Spreadsheet->fromArray($fecha_desde_str, null, 'B1');

$Spreadsheet->fromArray($fecha_hasta, null, 'D1');
$Spreadsheet->fromArray($fecha_hasta_str, null, 'E1');

$Spreadsheet->fromArray($sucursal_titulo, null, 'A2');
$Spreadsheet->fromArray($sucursal_str, null, 'B2');

$Spreadsheet->fromArray($encabezado, null, 'A3');

$documento
    ->getProperties()
    ->setCreator("Freelancers Py S.A.")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Ranking de Clientes')
    ->setSubject('Excel')
    ->setDescription('Ranking de Clientes')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$fileName = "RankingClientes.xls";

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
$Spreadsheet->getColumnDimension('K')->setAutoSize(true);
$Spreadsheet->getColumnDimension('L')->setAutoSize(true);
$Spreadsheet->getColumnDimension('M')->setAutoSize(true);
$Spreadsheet->getColumnDimension('O')->setAutoSize(true);
$Spreadsheet->getColumnDimension('P')->setAutoSize(true);

$Spreadsheet->getStyle('1')->getFont()->setBold( true );
$Spreadsheet->getStyle('2')->getFont()->setBold( true );
$Spreadsheet->getStyle('3')->getFont()->setBold( true );

$writer = new Xlsx($documento);

foreach ($rows as $r) {
    $coun++;
    $ruc            = $r->ruc;
    $razon_social   = $r->razon_social;
    $fecha          = $r->fecha;
    $cantidad       = $r->cantidad;
    $total_venta    = $r->total;

    $total += $total_venta;

    if (in_array('ruc',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('ruc', $columnas)+1, $coun, quitaSeparadorMiles($ruc));   
    }
    if (in_array('razon_social',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('razon_social', $columnas)+1, $coun, $razon_social);
    }
    if (in_array('fecha',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('fecha', $columnas)+1, $coun, $fecha);
    }
    if (in_array('cantidad',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('cantidad', $columnas)+1, $coun, $cantidad);
    }
    if (in_array('total',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('total', $columnas)+1, $coun, $total_venta);
    }

}
$coun++;

$Spreadsheet->getStyle($coun)->getFont()->setBold( true );

// $Spreadsheet->setCellValueByColumnAndRow(1, $coun, 'TOTAL');
// $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $total);

// $Spreadsheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('A')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('C')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
