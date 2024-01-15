<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');

include "inc/funciones.php";
$db       = DataBase::conectar();
$usuario  = $auth->getUsername();
$id_sucursal = $db->clearText($_REQUEST["sucursal"]);
$periodo  = $db->clearText($_REQUEST["periodo"]);

$where = "";
$where_sucursal ="";

if (!empty($periodo && $periodo != 'null')) {
    $where .= "AND ls.periodo='$periodo'";
}

if (!empty($id_sucursal) && intVal($id_sucursal) != 0) {
    $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
}

$db->setQuery("SELECT 
                    f.id_funcionario,
                    f.ci,
                    f.funcionario,
                    DATE_FORMAT(f.fecha_baja,'%d/%m/%Y') AS fecha_baja,
                    DATE_FORMAT(f.fecha_alta,'%d/%m/%Y') AS fecha_alta,
                    MAX(ls.id_liquidacion) as id_liquidacion,
                    ls.periodo,
                    ls.neto_cobrar
                FROM funcionarios f
                LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                LEFT JOIN (select * from liquidacion_salarios where estado = 4) ls ON f.id_funcionario=ls.id_funcionario
                WHERE f.fecha_baja != '0000-00-00' $where $where_sucursal
                GROUP BY f.id_funcionario");
$rows = $db->loadObjectList();

$db->setQuery("SELECT * FROM sucursales WHERE id_sucursal='$id_sucursal'");
$row_s = $db->loadObject();

# Encabezado de los productos
$encabezado = ["CEDULA", "FUNCIONARIO", "FECHA INGRESO", "FECHA SALIDA", "PERIODO", "NETO A COBRAR"];

$periodo_titulo = ["PERIODO:"];
$periodo_str = [$periodo];

$sucursal_titulo = ["SUCURSAL:"];
$sucursal_str = [$row_s->sucursal];

# Comenzamos en la fila 2
$coun = 2;

$documento = new Spreadsheet();
$Spreadsheet  = $documento->getActiveSheet();
$Spreadsheet->setTitle("Planillas de Desvinculaciones");

$Spreadsheet->fromArray($periodo_titulo, null, 'A1');
$Spreadsheet->fromArray($periodo_str, null, 'B1');

$Spreadsheet->fromArray($sucursal_titulo, null, 'D1');
$Spreadsheet->fromArray($sucursal_str, null, 'E1');

$Spreadsheet->fromArray($encabezado, null, 'A2');
$documento
    ->getProperties()
    ->setCreator("ABS Montajes S.A")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Planilla de Desvinculaciones')
    ->setSubject('Excel')
    ->setDescription('Planilla de Desvinculaciones')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$fileName = "Planilla_despidos_".$periodo.".xls";

$Spreadsheet->getColumnDimension('A')->setAutoSize(true);
$Spreadsheet->getColumnDimension('B')->setAutoSize(true);
$Spreadsheet->getColumnDimension('C')->setAutoSize(true);
$Spreadsheet->getColumnDimension('D')->setAutoSize(true);
$Spreadsheet->getColumnDimension('E')->setAutoSize(true);
$Spreadsheet->getColumnDimension('F')->setAutoSize(true);

$Spreadsheet->getStyle('1')->getFont()->setBold( true );
$Spreadsheet->getStyle('2')->getFont()->setBold( true );

$writer = new Xlsx($documento);

foreach ($rows as $r) {
    $coun++;
    $ci             = $r->ci;
    $funcionario    = $r->funcionario;
    $fecha_alta     = $r->fecha_alta;
    $fecha_baja     = $r->fecha_baja;
    $periodo        = $r->periodo;
    $neto_cobrar    = $r->neto_cobrar;

    $totales += $neto_cobrar;

    $Spreadsheet->setCellValueByColumnAndRow(1, $coun, $ci);
    $Spreadsheet->setCellValueByColumnAndRow(2, $coun, $funcionario);
    $Spreadsheet->setCellValueByColumnAndRow(3, $coun, $fecha_alta);
    $Spreadsheet->setCellValueByColumnAndRow(4, $coun, $fecha_baja);
    $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $periodo);
    $Spreadsheet->setCellValueByColumnAndRow(6, $coun, $neto_cobrar);

}
$coun++;

$Spreadsheet->getStyle($coun)->getFont()->setBold( true );

$Spreadsheet->setCellValueByColumnAndRow(1, $coun, 'TOTAL');
$Spreadsheet->setCellValueByColumnAndRow(6, $coun, $totales);

$Spreadsheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
