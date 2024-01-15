<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');

include "inc/funciones.php";
$db       = DataBase::conectar();
$usuario  = $auth->getUsername();
$desde 		            = $_REQUEST["desde"];
$hasta 		            = $_REQUEST["hasta"];
$id_sucursal            = $_REQUEST["sucursal"];
$reporte                = $_REQUEST["reporte"];
$columnas               = json_decode($_REQUEST["columnas"],true);
$encabezado             = json_decode($_REQUEST["titulos"],true);

if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
    if ($reporte==1) {
        $tittle = "GASTOS";
        $and_id_sucursal .= " AND g.id_sucursal=$id_sucursal";
    }else{
        $tittle = "COMPRAS";
        $and_id_sucursal .= " AND f.id_sucursal=$id_sucursal";
    }
    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
    $row      = $db->loadObject();
    $sucursal = $row->sucursal;
}else{
    if ($reporte==1) {
        $tittle = "Gastos";
    }else{
        $tittle = "Compras";
    }
    $and_id_sucursal .= "";
    $sucursal = "TODOS";
};

if ($reporte == 1) {
    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
    g.ruc,
    g.razon_social,
    g.timbrado,
    g.documento,
    g.gravada_10,
    g.gravada_5,
    g.exenta,
    g.monto,
    DATE_FORMAT(g.fecha_emision,'%d/%m/%Y') AS fecha_emision,
    CASE g.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion   
    FROM gastos g
    LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
    WHERE DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta' $and_id_sucursal");
    $rows = $db->loadObjectList();
}else{
    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS   
    f.total_venta AS monto, 
    f.razon_social,
    f.ruc,
    f.gravada_10,
    f.gravada_5,
    f.exenta,
    t.timbrado,
    CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',f.numero) AS documento,
    DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha_emision,
    CASE f.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion
    FROM facturas f
    LEFT JOIN timbrados t ON t.id_timbrado = f.id_timbrado
    WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta' $and_id_sucursal");
    $rows = $db->loadObjectList();
}

// $db->setQuery("SELECT * FROM sucursales WHERE id_sucursal='$id_sucursal'");
// $row_s = $db->loadObject();

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
$Spreadsheet->setTitle("Total Compras - Ventas");

$Spreadsheet->fromArray($fecha_desde, null, 'A1');
$Spreadsheet->fromArray($fecha_desde_str, null, 'B1');

$Spreadsheet->fromArray($fecha_hasta, null, 'D1');
$Spreadsheet->fromArray($fecha_hasta_str, null, 'E1');

$Spreadsheet->fromArray($sucursal_titulo, null, 'A2');
$Spreadsheet->fromArray($sucursal_str, null, 'B2');

$Spreadsheet->fromArray($encabezado, null, 'A3');

$documento
    ->getProperties()
    ->setCreator("ABS Montajes S.A")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Total Compras - Ventas')
    ->setSubject('Excel')
    ->setDescription('Total Compras - Ventas')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$fileName = "TotalComprasVentas.xls";

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
    $fecha_emision  = $r->fecha_emision;
    $ruc            = $r->ruc;
    $razon_social   = $r->razon_social;
    $timbrado       = $r->timbrado;
    $documento      = $r->documento;
    $gravada_10     = $r->gravada_10;
    $gravada_5      = $r->gravada_5;
    $exenta         = $r->exenta;
    $monto          = $r->monto;
    $condicion      = $r->condicion;

    if (in_array('fecha_emision',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('fecha_emision', $columnas)+1, $coun, $fecha_emision);   
    }
    if (in_array('ruc',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('ruc', $columnas)+1, $coun, quitaSeparadorMiles($ruc));
    }
    if (in_array('razon_social',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('razon_social', $columnas)+1, $coun, $razon_social);
    }
    if (in_array('timbrado',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('timbrado', $columnas)+1, $coun, $timbrado);
    }
    if (in_array('documento',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('documento', $columnas)+1, $coun, $documento);
    }
    if (in_array('gravada_10',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('gravada_10', $columnas)+1, $coun, $gravada_10);
    }
    if (in_array('gravada_5',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('gravada_5', $columnas)+1, $coun, $gravada_5);
    }
    if (in_array('exenta',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('exenta', $columnas)+1, $coun, $exenta);
    }
    if (in_array('monto',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('monto', $columnas)+1, $coun, $monto);
    }
    if (in_array('condicion',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('condicion', $columnas)+1, $coun, $condicion);
    }

}
$coun++;

$Spreadsheet->getStyle($coun)->getFont()->setBold( true );

// $Spreadsheet->setCellValueByColumnAndRow(1, $coun, 'TOTAL');
// $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $total);

// $Spreadsheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
// $Spreadsheet->getStyle('A')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
// $Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
// $Spreadsheet->getStyle('C')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
// $Spreadsheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
// $Spreadsheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('I')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
// $Spreadsheet->getStyle('J')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
