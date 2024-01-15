<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator(',');
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator('.');

include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $desde 		  = $db->clearText($_REQUEST["desde"]);
    $hasta 		  = $db->clearText($_REQUEST["hasta"]);
	$proveedor    = $db->clearText($_REQUEST['proveedor']);

    $ruc = "80051752-0";
    $cod_establecimiento = "001";

    $fecha_actual = date('d/m/Y h:m');


    if(empty($desde)) {
        $where_fecha .= "WHERE g.fecha_emision BETWEEN '2022-01-01' AND NOW()";
        $desde        = '2021-01-01';
        $hasta        = date('Y-m-d');
    }else{
        $where_fecha .=" WHERE DATE(g.fecha_emision) BETWEEN '$desde' AND '$hasta'";
    };

    if($proveedor) {
        $where_id_proveedor .= " AND g.ruc='$proveedor'";
    }else{
        $where_id_proveedor .="";
    };


    $db->setQuery("SELECT 
                        '2' AS tipo_registro,
				        CASE COALESCE((g.ruc LIKE '%-%'),0)
                        WHEN 0 THEN '12'
                        ELSE '11' END AS 'tipo_identificacion',
                        g.ruc,
                        g.razon_social,
                        tc.codigo,
                        g.fecha_emision,
                        DATE_FORMAT(g.fecha_emision,'%d/%m/%Y') AS fecha_emision_str,
				        g.timbrado,
                        g.documento,
                        g.gravada_10,
                        g.gravada_5,
                        g.exenta,
                        g.monto,
                        'N' AS moneda_extranjera,
                        CASE g.imputa_iva WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS imputa_iva,
                        CASE g.imputa_ire WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS imputa_ire,
                        CASE g.imputa_irp WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS imputa_irp,
                        CASE g.no_imputa WHEN 1 THEN 'S' WHEN 0 THEN 'N' END AS no_imputa,
                        DATE_FORMAT(g.fecha_emision, '%Y') AS anio_informe,
                        DATE_FORMAT(g.fecha_emision, '%m') AS mes_informe,
                        g.condicion,
                        g.nro_comprobante_venta_asoc,
                        g.timb_compro_venta_asoc
                        FROM gastos g
                        LEFT JOIN tipos_comprobantes tc ON tc.id_tipo_comprobante = g.id_tipo_comprobante
                        $where_fecha AND g.ruc != 'S/R'$where_id_proveedor");
    $rows = $db->loadObjectList();

foreach ($rows as $r){
    $anio = $r->anio_informe;
    $mes = $r->mes_informe;
}
    

# Encabezado de los productos
$encabezado = ["Tipo Reg.", "Tipo Iden.", "RUC", "Razon Social", "Tipo Comprobante","Fecha Emision", "Timbrado" , "Nro.Comprobante" , "Gravada 10%(iva incl.)" , "Gravada 5%(iva incl.)" , "Exenta" , "Imp. Total" , 
"Condicion" , "Moneda Extran." , "Imp. IVA" , "Imp. IRE" , "Imp. IRP" ,"No Imp.", "Nro. comp. asociada" , "Nro. timb. asociada"];

# Comenzamos en la fila 2
$coun = 1;

$documento = new Spreadsheet();
$hojaLibroCompraRg90 = $documento->getActiveSheet();
$hojaLibroCompraRg90->setTitle("Libro Compra RG90");
$hojaLibroCompraRg90->fromArray($encabezado, null, 'A1');
$documento
->getProperties()
->setCreator("ABS Montajes S.A")
->setLastModifiedBy('BaulPHP')
->setTitle('Libro Compra')
->setSubject('Excel')
->setDescription('Libro de compras en formato RG90')
->setKeywords('PHPSpreadsheet')
->setCategory('Categoría Cvs');

$fileName="$ruc"."_REG_". $mes.$anio."_C".$cod_establecimiento.".csv";

$writer = new Csv($documento);

$writer->setDelimiter(',');
$writer->setUseBOM(true);
$writer->setEnclosureRequired(false);



foreach ($rows as $r) {
    $coun++;
    $tipo_reg = $r->tipo_registro;
    $tipo_iden = $r->tipo_identificacion;
    $ruc = $r->ruc;
    $razon_social = preg_replace('/"/', '',$r->razon_social);
    $tipo_comp = $r->codigo;
    $fecha_emision = $r->fecha_emision_str;
    $timbrado = $r->timbrado;
    $nro_comp = $r->documento;
    $gravada_10 = $r->gravada_10;
    $gravada_5 = $r->gravada_5;
    $exenta = $r->exenta;
    $impo_total = $r->monto;
    $condicion = $r->condicion;
    $moneda_extra = $r->moneda_extranjera;
    $imp_iva  = $r->imputa_iva;
    $imp_ire = $r->imputa_ire;
    $imp_irp = $r->imputa_irp;
    $no_imputa = $r->no_imputa;
    if ($nro_comp_asociada = $r->nro_comprobante_venta_asoc == '') {
        $nro_comp_asociada = '0';
    }else {
        $nro_comp_asociada = $r->nro_comprobante_venta_asoc;
    }
    if ( $nro_timb_asociada = $r->timb_compro_venta_asoc == '') {
        $nro_timb_asociada = '0';
    }else {
        $nro_timb_asociada = $r->timb_compro_venta_asoc;  
    }
  

    $hojaLibroCompraRg90->setCellValueByColumnAndRow(1, $coun, $tipo_reg);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(2, $coun, $tipo_iden);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(3, $coun, $ruc);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(4, $coun, $razon_social);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(5, $coun, $tipo_comp);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(6, $coun, $fecha_emision);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(7, $coun, $timbrado);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(8, $coun, $nro_comp);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(9, $coun, $gravada_10);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(10, $coun, $gravada_5);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(11, $coun, $exenta);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(12, $coun, $impo_total);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(13, $coun, $condicion);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(14, $coun, $moneda_extra);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(15, $coun, $imp_iva);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(16, $coun, $imp_ire);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(17, $coun, $imp_irp);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(17, $coun, $no_imputa);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(18, $coun, $nro_comp_asociada);
    $hojaLibroCompraRg90->setCellValueByColumnAndRow(19, $coun, $nro_timb_asociada);
}



header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
?>