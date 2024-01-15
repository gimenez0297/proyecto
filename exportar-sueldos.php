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

if($periodo == 1){
    $where = "";
}else{
    $where = " AND ls.periodo='$periodo'";
}

if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
    $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
}else{
    $where_sucursal .="";
};

$db->setQuery("SELECT * FROM (SELECT
                                s.sucursal,
                                f.id_funcionario,
                                f.funcionario,
                                ls.id_liquidacion,
                                ant.monto AS anticipo,
                                pre.monto AS prestamo,
                                ot.monto AS otros_descuentos,
                                DATE_FORMAT(f.fecha_alta,'%d/%m/%Y') AS fecha_ingreso,
                                IFNULL(f.salario_real,0) AS salario,
                                ROUND(IF(aporte = 1, IFNULL(f.salario_real,0)*0.09, 0)) AS retencion_ips,
                                ROUND(IF(aporte = 2, IFNULL(f.salario_real,0)*0.11, 0)) AS iva,
                                ROUND(IF(aporte = 1, IFNULL(f.salario_real,0)*0.165, 0)) AS aporte_patronal,
                                ROUND(IF(lsi.`concepto`='EXTRA', lsi.importe,0)) AS extra,
                                IFNULL(lsd.total_des,0) AS total_descuento,
                                ROUND(IFNULL((SELECT importe
                                    FROM liquidacion_salarios_ingresos lss
                                    LEFT JOIN liquidacion_salarios li ON lss.id_liquidacion=li.id_liquidacion
                                    WHERE lss.id_liquidacion=ls.id_liquidacion AND lss.concepto='COMISIÓN' AND li.id_funcionario=ls.`id_funcionario` GROUP BY li.periodo,li.id_funcionario),0)) comision,
                                ROUND(((f.salario_real-IFNULL(ips.monto,0))-IFNULL(lsd.total_des,0))+ROUND(IF(lsi.`concepto`='EXTRA', IFNULL(lsi.importe,0),0))+ROUND(IFNULL((SELECT importe
                                    FROM liquidacion_salarios_ingresos lss
                                    LEFT JOIN liquidacion_salarios li ON lss.id_liquidacion=li.id_liquidacion
                                    WHERE lss.id_liquidacion=ls.id_liquidacion AND lss.concepto='COMISIÓN' AND li.id_funcionario=ls.`id_funcionario` GROUP BY li.periodo,li.id_funcionario),0))) AS acreditado

                            FROM  funcionarios f
                            LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                            LEFT JOIN liquidacion_salarios ls ON f.id_funcionario=ls.id_funcionario
                            LEFT JOIN (SELECT l.*, SUM(importe) AS total_des FROM liquidacion_salarios_descuentos l WHERE concepto != 'I.P.S 9%' GROUP BY id_liquidacion) lsd ON ls.id_liquidacion=lsd.id_liquidacion
                            LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto = 'I.P.S 9%' GROUP BY id_liquidacion) ips ON ls.id_liquidacion=ips.id_liquidacion
                            LEFT JOIN liquidacion_salarios_ingresos lsi ON ls.id_liquidacion=lsi.id_liquidacion
                            LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto = 'ANTICIPO' GROUP BY id_liquidacion) ant ON ls.id_liquidacion=ant.id_liquidacion
                            LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto = 'PRESTAMO' GROUP BY id_liquidacion) pre ON ls.id_liquidacion=pre.id_liquidacion
                            LEFT JOIN (SELECT l.*, SUM(importe) AS monto FROM liquidacion_salarios_descuentos l WHERE concepto != 'PRESTAMO' AND concepto != 'ANTICIPO' AND concepto != 'I.P.S 9%' GROUP BY id_liquidacion) ot ON ls.id_liquidacion=ot.id_liquidacion
                        WHERE ls.estado != 2 $where_sucursal $where
                        GROUP BY f.id_funcionario, ls.periodo) a
                        GROUP BY id_funcionario");
$rows = $db->loadObjectList();

$db->setQuery("SELECT * FROM sucursales WHERE id_sucursal='$id_sucursal'");
$row_s = $db->loadObject();

# Encabezado de los productos
$encabezado = ["SUCURSAL", "FUNCIONARIO", "FECHA INGRESO", "DIRECTORES", "PERSONAL SUPERIOR", "SALARIO MENSUAL", "REGENCIA", "I.V.A", "RETENCIÓN I.P.S", "APORTE PATRONAL", "HORAS EXTRAS", "COMISIÓN", "ANTICIPO", "PRÉSTAMO", "OTROS DESCUENTOS", "ACREDITADO EN CUENTA"];

$periodo_titulo = ["PERIODO:"];
$periodo_str = [$periodo];

$sucursal_titulo = ["SUCURSAL:"];
$sucursal_str = [$row_s->sucursal];

# Comenzamos en la fila 2
$coun = 2;

$documento = new Spreadsheet();
$Spreadsheet  = $documento->getActiveSheet();
$Spreadsheet->setTitle("Planillas de sueldos");

$Spreadsheet->fromArray($periodo_titulo, null, 'A1');
$Spreadsheet->fromArray($periodo_str, null, 'B1');

$Spreadsheet->fromArray($sucursal_titulo, null, 'D1');
$Spreadsheet->fromArray($sucursal_str, null, 'E1');

$Spreadsheet->fromArray($encabezado, null, 'A2');
$documento
    ->getProperties()
    ->setCreator("ABS Montajes S.A")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Planilla de sueldos')
    ->setSubject('Excel')
    ->setDescription('Planilla de sueldos')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$fileName = "Planilla_sueldos_".$periodo.".xls";

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
$Spreadsheet->getColumnDimension('N')->setAutoSize(true);
$Spreadsheet->getColumnDimension('O')->setAutoSize(true);
$Spreadsheet->getColumnDimension('P')->setAutoSize(true);

$Spreadsheet->getStyle('1')->getFont()->setBold( true );
$Spreadsheet->getStyle('2')->getFont()->setBold( true );

$writer = new Xlsx($documento);

foreach ($rows as $r) {
    $coun++;
    $sucursal       = $r->sucursal;
    $funcionario    = $r->funcionario;
    $fecha          = $r->fecha_ingreso;
    $director       = 0;
    $superior       = 0;
    $salario        = $r->salario;
    $regencia       = 0;
    $iva            = $r->iva;
    $ips            = $r->retencion_ips;
    $aporte         = $r->aporte_patronal;
    $extra          = $r->extra;
    $comision       = $r->comision;
    $anticipo       = $r->anticipo;
    $prestamo       = $r->prestamo;
    $otros_des      = $r->otros_descuentos;
    $total_descuento  = $r->total_descuento;
    $acreditado     = $r->acreditado;

    $directores += $director;
    $superiores += $superior;
    $salarios_sum += $salario;
    $regencias += $regencia;
    $ivas += $iva;
    $ips_monto += $ips ;
    $aportes_pat += $aporte ;
    $hs_extra += $extra ;
    $comisiones_tot +=  $comision ;
    $anticipo_tot +=  $anticipo ;
    $prestamo_tot +=  $prestamo ;
    $otros_des_tot +=  $otros_des ;
    $descuento_tot +=  $total_descuento ;
    $acreditados_tot +=  $acreditado ;

    $Spreadsheet->setCellValueByColumnAndRow(1, $coun, $sucursal);
    $Spreadsheet->setCellValueByColumnAndRow(2, $coun, $funcionario);
    $Spreadsheet->setCellValueByColumnAndRow(3, $coun, $fecha);
    $Spreadsheet->setCellValueByColumnAndRow(4, $coun, $director);
    $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $superior);
    $Spreadsheet->setCellValueByColumnAndRow(6, $coun, $salario);
    $Spreadsheet->setCellValueByColumnAndRow(7, $coun, $regencia);
    $Spreadsheet->setCellValueByColumnAndRow(8, $coun, $iva);
    $Spreadsheet->setCellValueByColumnAndRow(9, $coun, $ips);
    $Spreadsheet->setCellValueByColumnAndRow(10, $coun, $aporte);
    $Spreadsheet->setCellValueByColumnAndRow(11, $coun, $extra);
    $Spreadsheet->setCellValueByColumnAndRow(12, $coun, $comision);
    $Spreadsheet->setCellValueByColumnAndRow(13, $coun, $anticipo);
    $Spreadsheet->setCellValueByColumnAndRow(14, $coun, $prestamo);
    $Spreadsheet->setCellValueByColumnAndRow(15, $coun, $otros_des);
    $Spreadsheet->setCellValueByColumnAndRow(16, $coun, $acreditado);

}
$coun++;

$Spreadsheet->getStyle($coun)->getFont()->setBold( true );

$Spreadsheet->setCellValueByColumnAndRow(1, $coun, 'TOTAL');
$Spreadsheet->setCellValueByColumnAndRow(4, $coun, $directores);
$Spreadsheet->setCellValueByColumnAndRow(5, $coun, $superiores);
$Spreadsheet->setCellValueByColumnAndRow(6, $coun, $salarios_sum);
$Spreadsheet->setCellValueByColumnAndRow(7, $coun, $regencias);
$Spreadsheet->setCellValueByColumnAndRow(8, $coun, $ivas);
$Spreadsheet->setCellValueByColumnAndRow(9, $coun, $ips_monto);
$Spreadsheet->setCellValueByColumnAndRow(10, $coun, $aportes_pat);
$Spreadsheet->setCellValueByColumnAndRow(11, $coun, $hs_extra);
$Spreadsheet->setCellValueByColumnAndRow(12, $coun, $comisiones_tot);
$Spreadsheet->setCellValueByColumnAndRow(13, $coun, $anticipo_tot);
$Spreadsheet->setCellValueByColumnAndRow(14, $coun, $prestamo_tot);
$Spreadsheet->setCellValueByColumnAndRow(15, $coun, $otros_des_tot);
$Spreadsheet->setCellValueByColumnAndRow(16, $coun, $acreditados_tot);

$Spreadsheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('E')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('I')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('J')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('K')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('L')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('M')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('N')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('O')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('P')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
