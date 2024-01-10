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

    $where_fecha = "";
    $and_id_sucursal ="";
    $and_id_sucursal_fun ="";
    $where_fecha_fun = "";

    if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
        $and_id_sucursal = "AND g.id_sucursal=$id_sucursal";
        $and_id_sucursal_fun = " AND f.id_sucursal=$id_sucursal";       
    }

    if ((isset($desde) && !empty($desde)) || (isset($hasta) && !empty($hasta))) {
        $where_fecha = " AND DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta'";
        $where_fecha_fun = " AND DATE(ls.fecha) BETWEEN '$desde' AND '$hasta'";
    }

    $db->setQuery("SELECT 
                        'GASTOS' AS descripcion,
                        g.ruc,
                        g.razon_social,
                        g.monto AS total,
                        DATE_FORMAT(g.fecha_emision,'%d/%m/%Y') AS fecha_emision,
                        CASE g.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CREDITO' END AS condicion
                    FROM gastos g
                    LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                    WHERE g.estado in(0,1) $and_id_sucursal $where_fecha

                    UNION ALL

                    SELECT 
                        'SALARIOS' AS descripcion,
                        ls.ci AS ruc,
                        ls.funcionario AS razon_social,
                        neto_cobrar AS total,
                        DATE_FORMAT(ls.fecha,'%d/%m/%Y') AS fecha_emision,
                        'CONTADO' AS condicion
                    FROM liquidacion_salarios ls
                    LEFT JOIN funcionarios f ON ls.ci=f.ci
                    WHERE ls.estado = 1 $and_id_sucursal_fun $where_fecha_fun");
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
$sucursal_str = [$row_s->sucursal];

# Comenzamos en la fila 2
$coun = 3;

$documento = new Spreadsheet();
$Spreadsheet  = $documento->getActiveSheet();
$Spreadsheet->setTitle("Egresos A Vencer");

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
    ->setTitle('Egresos A Vencer')
    ->setSubject('Excel')
    ->setDescription('Egresos A Vencer')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$fileName = "EgresosaVencer.xls";

$Spreadsheet->getColumnDimension('A')->setAutoSize(true);
$Spreadsheet->getColumnDimension('B')->setAutoSize(true);
$Spreadsheet->getColumnDimension('C')->setAutoSize(true);
$Spreadsheet->getColumnDimension('D')->setAutoSize(true);
$Spreadsheet->getColumnDimension('E')->setAutoSize(true);
$Spreadsheet->getColumnDimension('F')->setAutoSize(true);


$Spreadsheet->getStyle('1')->getFont()->setBold( true );
$Spreadsheet->getStyle('2')->getFont()->setBold( true );
$Spreadsheet->getStyle('3')->getFont()->setBold( true );

$writer = new Xlsx($documento);

foreach ($rows as $r) {
    $coun++;
    $descripcion    = $r->descripcion;
    $fecha_emision  = $r->fecha_emision;
    $ruc            = $r->ruc;
    $razon_social   = $r->razon_social;
    $monto          = $r->total;
    $condicion      = $r->condicion;

    $total += $monto;

    if (in_array('descripcion',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('descripcion', $columnas)+1, $coun, $descripcion);   
    }
    if (in_array('fecha_emision',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('fecha_emision', $columnas)+1, $coun, $fecha_emision);
    }
    if (in_array('ruc',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('ruc', $columnas)+1, $coun, quitaSeparadorMiles($ruc));   
    }
    if (in_array('razon_social',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('razon_social', $columnas)+1, $coun, $razon_social);
    }
    if (in_array('condicion',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('condicion', $columnas)+1, $coun, $condicion);
    }
    if (in_array('total',$columnas)) {
        $Spreadsheet->setCellValueByColumnAndRow(array_search('total', $columnas)+1, $coun, $monto);
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
