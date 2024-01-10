<?php

ini_set("memory_limit",'512M');
set_time_limit ( 600 );

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
	$estado       = $db->clearText($_REQUEST['estado']);
	$id_cliente   = $db->clearText($_REQUEST['id_cliente']);

    $ruc = "80051752-0";
    $cod_establecimiento = "001";

    $fecha_actual = date('d/m/Y h:m');

    if($estado == 3 ) {
        $where .="";
    }else{
        $where .= "AND f.estado = $estado";
    }

    


    if(empty($desde)) {
        $where_fecha .= "WHERE f.fecha_venta BETWEEN '2022-01-01' AND NOW()";
        $desde        = '2021-01-01';
        $hasta        = date('Y-m-d');
    }else{
        $where_fecha .=" WHERE DATE(f.fecha_venta) BETWEEN '$desde' AND '$hasta'";
    };

    if(!empty($id_cliente)) {
        $where_id_cliente .= " AND f.id_cliente=$id_cliente";
        $db->setQuery("SELECT * FROM clientes WHERE id_cliente =$id_cliente");
        $row     = $db->loadObject();
        $cliente = $row->razon_social;
    }else{
        $where_id_cliente .="";
        $cliente           = "Todos";
    };




    $db->setQuery("SELECT 
                            CASE f.estado WHEN 2 THEN 0 ELSE f.total_venta END AS total_venta,
                            CASE f.estado WHEN 2 THEN 'ANULADO' ELSE f.razon_social END AS razon_social,
                            CASE f.estado WHEN 2 THEN 'AA' ELSE f.ruc END AS ruc,
                            CASE f.estado WHEN 2 THEN 0 ELSE f.gravada_10 END AS gravada_10,
                            CASE f.estado WHEN 2 THEN 0 ELSE f.gravada_5 END AS gravada_5,
                            CASE f.estado WHEN 2 THEN 0 ELSE f.exenta END AS exenta,
                            t.timbrado,
                            '1' AS tipo_reg,
                            f.condicion,
                            CONCAT(t.cod_establecimiento,'-',t.punto_de_expedicion,'-',f.numero) AS nro_factura,
                            CASE COALESCE((f.ruc LIKE '%-%'),0)
                                WHEN 0 THEN '12'
                                ELSE '11' 
                            END AS 'tipo_identificacion',
                            '109' AS tipo_comprobante,
                            'N' AS moneda_extranjera, 
                            'S' AS imputa_iva,
                            'N' AS imputa_ire,
                            'N' AS imputa_irp,
                            '0' AS nro_comp_asociada,
                            '0' AS nro_timb_asociada,
                            DATE_FORMAT(f.fecha_venta,'%d/%m/%Y') AS fecha,
                            DATE_FORMAT(f.fecha_venta, '%Y') AS anio_informe,
                            DATE_FORMAT(f.fecha_venta, '%m') AS mes_informe,
                            CASE f.estado WHEN 0 THEN 'Pendiente' WHEN 1 THEN 'Pagado' WHEN 2 THEN 'Anulado' END AS estado_str,
                            CASE f.condicion WHEN 1 THEN 'CONTADO' WHEN 2 THEN 'CRÉDITO' END AS condicion_str
                        FROM facturas f
                        LEFT JOIN timbrados t ON t.id_timbrado = f.id_timbrado
                        $where_fecha  $where_id_cliente $where");
    $rows = $db->loadObjectList();

foreach ($rows as $r){
    $anio = $r->anio_informe;
    $mes = $r->mes_informe;
}
    

# Encabezado de los productos
$encabezado = ["Tipo Reg.", "Tipo Iden.", "RUC", "Razon Social", "Tipo Comprobante","Fecha Emision", "Timbrado" , "Nro.Comprobante" , "Gravada 10%(iva incl.)" , "Gravada 5%(iva incl.)" , "Exenta" , "Imp. Total" , 
"Condicion" , "Moneda Extran." , "Imp. IVA" , "Imp. IRE" , "Imp. IRP" , "Nro. comp. asociada" , "Nro. timb. asociada"];

# Comenzamos en la fila 2
$coun = 1;

$documento = new Spreadsheet();
$hojaLibroVentaRg90 = $documento->getActiveSheet();
$hojaLibroVentaRg90->setTitle("Libro Venta RG90");
$hojaLibroVentaRg90->fromArray($encabezado, null, 'A1');
$documento
->getProperties()
->setCreator("Freelancers Py S.A.")
->setLastModifiedBy('BaulPHP')
->setTitle('Libro Ventas')
->setSubject('Excel')
->setDescription('Libro de ventas en formato RG90')
->setKeywords('PHPSpreadsheet')
->setCategory('Categoría Cvs');

$fileName="$ruc"."_REG_". $mes.$anio."_V".$cod_establecimiento.".csv";

$writer = new Csv($documento);

$writer->setDelimiter(',');
$writer->setUseBOM(true);
$writer->setEnclosureRequired(false);



foreach ($rows as $r) {
    $coun++;
    $tipo_reg = $r->tipo_reg;
    $tipo_iden = $r->tipo_identificacion;
    $ruc = $r->ruc;
    $razon_social = preg_replace('/"/', '',$r->razon_social);
    $tipo_comp = $r->tipo_comprobante;
    $fecha_emision = $r->fecha;
    $timbrado = $r->timbrado;
    $nro_comp = $r->nro_factura;
    $gravada_10 = $r->gravada_10;
    $gravada_5 = $r->gravada_5;
    $exenta = $r->exenta;
    $impo_total = $r->total_venta;
    $condicion = $r->condicion;
    $moneda_extra = $r->moneda_extranjera;
    $imp_iva  = $r->imputa_iva;
    $imp_ire = $r->imputa_ire;
    $imp_irp = $r->imputa_irp;
    $nro_comp_asociada = $r->nro_comp_asociada;
    $nro_timb_asociada = $r->nro_timb_asociada;    
    

    $hojaLibroVentaRg90->setCellValueByColumnAndRow(1, $coun, $tipo_reg);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(2, $coun, $tipo_iden);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(3, $coun, $ruc);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(4, $coun, $razon_social);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(5, $coun, $tipo_comp);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(6, $coun, $fecha_emision);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(7, $coun, $timbrado);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(8, $coun, $nro_comp);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(9, $coun, $gravada_10);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(10, $coun, $gravada_5);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(11, $coun, $exenta);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(12, $coun, $impo_total);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(13, $coun, $condicion);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(14, $coun, $moneda_extra);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(15, $coun, $imp_iva);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(16, $coun, $imp_ire);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(17, $coun, $imp_irp);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(18, $coun, $nro_comp_asociada);
    $hojaLibroVentaRg90->setCellValueByColumnAndRow(19, $coun, $nro_timb_asociada);
}



header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
?>