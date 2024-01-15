<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator(',');

include "inc/funciones.php";
$db       = DataBase::conectar();
$usuario  = $auth->getUsername();
$sucursal = $db->clearText($_REQUEST["sucursal"]);
$periodo  = $db->clearText($_REQUEST["periodo"]);

$db->setQuery("SELECT
                        fu.funcionario AS vendedor,
                        f.usuario,
                        (COALESCE(fc3.total,0)+COALESCE(fc5.total,0)+COALESCE(comi0.total,0)) AS total_venta,
                        f.fecha_venta,
                        s.sucursal,
                        f.id_factura,
                        IFNULL(fc3.total,0) AS tot_3,
                        IFNULL(fc5.total,0) AS tot_5,
                        ROUND(COALESCE(fc3.total,0) * 0.03) AS com3,
                        ROUND(COALESCE(fc5.total,0) * 0.05) AS com5,
                        COALESCE(comi0.total,0) AS sin_comi,
                        (ROUND(COALESCE(fc3.total,0) * 0.03) + ROUND(COALESCE(fc5.total,0) * 0.05)) AS total_comision,
                        (SUM(f.total_venta)-(COALESCE(fc3.total,0)+COALESCE(fc5.total,0)+COALESCE(comi0.total,0))) AS bruto,
                        CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta)) AS periodo

                    FROM facturas f
                    LEFT JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                    LEFT JOIN (SELECT * FROM facturas_productos fp GROUP BY fp.id_factura) fp ON fp.id_factura=f.id_factura
                    LEFT JOIN users u ON f.usuario=u.username
                    LEFT JOIN funcionarios fu ON u.id=fu.id_usuario
                    LEFT JOIN (SELECT SUM(fp.total_venta) AS total,MONTH(DATE(f.fecha_venta)) AS dat_fac, f.`usuario`
                            FROM facturas_productos fp
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN facturas f ON fp.`id_factura`=f.`id_factura`
                            WHERE fp.comision=3 AND f.`estado`!=2
                            GROUP BY MONTH(DATE(f.fecha_venta)),YEAR(DATE(f.fecha_venta)), f.`usuario`) fc3 ON fc3.dat_fac = MONTH(DATE(f.fecha_venta)) AND fc3.usuario=f.`usuario`
                    LEFT JOIN (SELECT SUM(fp.total_venta) AS total,MONTH(DATE(f.fecha_venta)) AS dat_fac, f.`usuario`
                            FROM facturas_productos fp
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN facturas f ON fp.`id_factura`=f.`id_factura`
                            WHERE fp.comision=5 AND f.`estado`!=2
                            GROUP BY MONTH(DATE(f.fecha_venta)),YEAR(DATE(f.fecha_venta)), f.`usuario`) fc5 ON fc5.dat_fac = MONTH(DATE(f.fecha_venta)) AND fc5.usuario=f.`usuario`
                    LEFT JOIN (SELECT SUM(fp.total_venta) AS total,MONTH(DATE(f.fecha_venta)) AS dat_fac, f.`usuario`
                            FROM facturas_productos fp
                            LEFT JOIN productos p ON fp.id_producto=p.id_producto
                            LEFT JOIN facturas f ON fp.`id_factura`=f.`id_factura`
                            WHERE fp.comision=0 AND f.`estado`!=2
                            GROUP BY MONTH(DATE(f.fecha_venta)),YEAR(DATE(f.fecha_venta)), f.`usuario`) comi0 ON comi0.dat_fac = MONTH(DATE(f.fecha_venta)) AND comi0.usuario=f.`usuario`

                    WHERE f.estado != 2 AND f.id_sucursal=$sucursal AND CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta))='$periodo'
                    GROUP BY MONTH(DATE(f.fecha_venta)), f.usuario");
$rows = $db->loadObjectList();

$db->setQuery("SELECT * FROM sucursales WHERE id_sucursal='$sucursal'");
$row_s = $db->loadObject();

# Encabezado de los productos
$encabezado = ["VENDEDOR", "TOTAL VENTA", "PRODUCTO 3%", "PRODUCTOS 5%", "PRODUCTOS S/C"/*, "TOTAL BRUTO"*/, "3 %", "5 %", "TOTAL COMISIÓN"];

$periodo_titulo = ["PERIODO:"];
$periodo_str = [$periodo];

$sucursal_titulo = ["SUCURSAL:"];
$sucursal_str = [$row_s->sucursal];

# Comenzamos en la fila 2
$coun = 2;

$documento = new Spreadsheet();
$comision  = $documento->getActiveSheet();
$comision->setTitle("Comisiones");

$comision->fromArray($periodo_titulo, null, 'A1');
$comision->fromArray($periodo_str, null, 'B1');

$comision->fromArray($sucursal_titulo, null, 'D1');
$comision->fromArray($sucursal_str, null, 'E1');

$comision->fromArray($encabezado, null, 'A2');
$documento
    ->getProperties()
    ->setCreator("ABS Montajes S.A")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Comision')
    ->setSubject('Excel')
    ->setDescription('Comisiones')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$fileName = "comision.xls";

$writer = new Xlsx($documento);

$comision->getColumnDimension('A')->setAutoSize(true);
$comision->getColumnDimension('B')->setAutoSize(true);
$comision->getColumnDimension('C')->setAutoSize(true);
$comision->getColumnDimension('D')->setAutoSize(true);
$comision->getColumnDimension('E')->setAutoSize(true);
//$comision->getColumnDimension('F')->setAutoSize(true);
$comision->getColumnDimension('F')->setAutoSize(true);
$comision->getColumnDimension('G')->setAutoSize(true);
$comision->getColumnDimension('H')->setAutoSize(true);

$comision->getStyle('1')->getFont()->setBold( true );
$comision->getStyle('2')->getFont()->setBold( true );

foreach ($rows as $r) {
    $coun++;
    $vendedor       = $r->vendedor;
    $total_venta    = $r->total_venta;
    $prod3          = $r->tot_3;
    $prod5          = $r->tot_5;
    $prod_sin_com   = $r->sin_comi;
    $total_bruto    = $r->bruto;
    $com3           = $r->com3;
    $com5           = $r->com5;
    $total_comision = $r->total_comision;

    $total_sumado += $total_venta;
    $prod3_ += $prod3;
    $prod5_ += $prod5;
    $prod_sin += $prod_sin_com;
    $total_b += $total_bruto;
    $com3_ += $com3;
    $com5_ += $com5;
    $total += $total_comision;

    $comision->setCellValueByColumnAndRow(1, $coun, $vendedor);
    $comision->setCellValueByColumnAndRow(2, $coun, $total_venta);
    $comision->setCellValueByColumnAndRow(3, $coun, $prod3);
    $comision->setCellValueByColumnAndRow(4, $coun, $prod5);
    $comision->setCellValueByColumnAndRow(5, $coun, $prod_sin_com);
    //$comision->setCellValueByColumnAndRow(6, $coun, $total_bruto);
    $comision->setCellValueByColumnAndRow(6, $coun, $com3);
    $comision->setCellValueByColumnAndRow(7, $coun, $com5);
    $comision->setCellValueByColumnAndRow(8, $coun, $total_comision);

}

$coun++;

$comision->getStyle($coun)->getFont()->setBold( true );

$comision->setCellValueByColumnAndRow(1, $coun, 'TOTAL');
$comision->setCellValueByColumnAndRow(2, $coun, $total_sumado);
$comision->setCellValueByColumnAndRow(3, $coun, $prod3_);
$comision->setCellValueByColumnAndRow(4, $coun, $prod5_);
$comision->setCellValueByColumnAndRow(5, $coun, $prod_sin);
//$comision->setCellValueByColumnAndRow(6, $coun, $total_b);
$comision->setCellValueByColumnAndRow(6, $coun, $com3_);
$comision->setCellValueByColumnAndRow(7, $coun, $com5_);
$comision->setCellValueByColumnAndRow(8, $coun, $total);

$comision->getStyle('B')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$comision->getStyle('C')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$comision->getStyle('D')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$comision->getStyle('E')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
//$comision->getStyle('F')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$comision->getStyle('F')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$comision->getStyle('G')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$comision->getStyle('H')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
