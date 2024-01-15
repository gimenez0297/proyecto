<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');

include "inc/funciones.php";
verificaLogin('productos.php');
$db       = DataBase::conectar();
$usuario  = $auth->getUsername();

$db->setQuery("SELECT 
                    SQL_CALC_FOUND_ROWS
                    p.id_producto,
                    p.producto,
                    p.id_marca,
                    CASE WHEN m.marca IS NULL THEN
                        'SIN DEFINIR'
                    WHEN TRIM(m.marca) = '' THEN
                        'SIN DEFINIR'
                    WHEN TRIM(m.marca) != '' THEN
                        m.marca
                    END AS marca,
                    p.id_laboratorio,
                    CASE WHEN l.laboratorio IS NULL THEN
                        'SIN DEFINIR'
                    WHEN TRIM(l.laboratorio) = '' THEN
                        'SIN DEFINIR'
                    WHEN TRIM(l.laboratorio) != '' THEN
                        l.laboratorio
                    END AS laboratorio,
                    p.id_tipo_producto,
                    tp.tipo,
                    tp.principios_activos,
                    p.id_origen,
                    o.origen,
                    p.comision,
                    p.id_moneda,
                    mo.moneda,
                    p.id_presentacion,
                    pr.presentacion,
                    cp.id_clasificacion_producto,
                    CASE WHEN cp.clasificacion IS NULL THEN
                        'SIN DEFINIR'
                    WHEN TRIM(cp.clasificacion) = '' THEN
                        'SIN DEFINIR'
                    WHEN TRIM(cp.clasificacion) != '' THEN
                        cp.clasificacion
                    END AS clasificacion,
                    p.id_unidad_medida,
                    CASE WHEN um.unidad_medida IS NULL THEN
                        'SIN DEFINIR'
                    WHEN TRIM(um.unidad_medida) = '' THEN
                        'SIN DEFINIR'
                    WHEN TRIM(um.unidad_medida) != '' THEN
                        um.unidad_medida
                    END AS unidad_medida,
                    um.sigla,
                    p.id_rubro,
                    CASE WHEN ru.rubro IS NULL THEN
                        'SIN DEFINIR'
                    WHEN TRIM(ru.rubro) = '' THEN
                        'SIN DEFINIR'
                    WHEN TRIM(ru.rubro) != '' THEN
                        ru.rubro
                    END AS rubro,
                    p.id_pais,
                    ps.nombre_es AS pais,
                    p.precio,
                    p.precio_fraccionado,
                    p.cantidad_fracciones,
                    p.descuento_fraccionado,
                    p.fuera_de_plaza,
                    p.fraccion,
                    p.codigo,
                    CASE WHEN p.observaciones IS NULL THEN
                        'SIN DEFINIR'
                    WHEN TRIM(p.observaciones) = '' THEN
                        'SIN DEFINIR'
                    WHEN TRIM(p.observaciones) != '' THEN
                        p.observaciones
                    END AS observaciones,
                    p.controlado,
                    CASE WHEN p.descripcion IS NULL THEN
                        'SIN DEFINIR'
                    WHEN TRIM(p.descripcion) = '' THEN
                        'SIN DEFINIR'
                    WHEN TRIM(p.descripcion) != '' THEN
                        p.descripcion
                    END AS descripcion,
                    p.copete,
                    p.estado,
                    p.conservacion,
                    p.web,
                    CASE WHEN web = 1 THEN
                        'SI'
                    WHEN web != 1 THEN
                        'NO'
                    END AS web_str,
                    p.iva,
                    CASE p.iva WHEN 1 THEN 'EXENTAS' WHEN 2 THEN '5%'  WHEN 3 THEN '10%'  END AS iva_str,
                    CASE p.conservacion WHEN 1 THEN 'NORMAL' WHEN 2 THEN 'REFRIGERADO' END AS conservacion_str,
                    p.indicaciones,
                    CASE p.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                    p.usuario,
                    DATE_FORMAT(p.fecha,'%d/%m/%Y %H:%i:%s') AS fecha,
                    p.destacar,
                    p.comision_concepto
                FROM productos p
                LEFT JOIN marcas m ON p.id_marca=m.id_marca
                LEFT JOIN laboratorios l ON p.id_laboratorio=l.id_laboratorio
                LEFT JOIN tipos_productos tp ON p.id_tipo_producto=tp.id_tipo_producto
                LEFT JOIN origenes o ON p.id_origen=o.id_origen
                LEFT JOIN monedas mo ON p.id_moneda=mo.id_moneda
                LEFT JOIN presentaciones pr ON p.id_presentacion=pr.id_presentacion
                /*LEFT JOIN clasificaciones_productos cp ON p.id_clasificacion=cp.id_clasificacion_producto*/
                LEFT JOIN productos_clasificaciones pp ON  p.id_producto = pp.id_producto
                LEFT JOIN clasificaciones_productos cp ON pp.id_clasificacion=cp.id_clasificacion_producto
                LEFT JOIN unidades_medidas um ON p.id_unidad_medida=um.id_unidad_medida
                LEFT JOIN rubros ru ON p.id_rubro=ru.id_rubro
                LEFT JOIN paises ps ON p.id_pais=ps.id_pais
                GROUP BY p.id_producto
                ORDER BY p.producto ASC;");
$rows = $db->loadObjectList();

$db->setQuery("SELECT FOUND_ROWS() as total");
$total_row = $db->loadObject();
$total_count = $total_row->total;

$date_now = date_create();
$time = date_timestamp_get($date_now);
$name_date_now = date_format($date_now, "d_m_Y"); 

# Encabezado de los productos
$encabezado = [
    "ID PRODUCTO", 
    "CÓDIGO", 
    "PRODUCTO", 
    "DESCRIPCION", 
    "MARCA", 
    "LABORATORIO", 
    "TIPO", 
    "PRINCIPIOS ACTIVOS", 
    "ORIGEN",
    "COMISION", 
    "PRESENTACIÓN", 
    "CLASIFICACION", 
    "UNIDAD DE MEDIDA", 
    "RUBRO", 
    "PRECIO", 
    "PRECIO FRACCIONADO", 
    "OBSERVACIONES", 
    "ESTADO",
    "CONSERVACIÓN",
    "WEB",
    "IVA",
    "FECHA DE ALTA",
    "CANTIDAD FRACCIONES",
];
/* */
    $productos_total = ["PRODUCTOS EN TOTAL:"];
    $productos_total_str = [$total_count];

    # Comenzamos en la fila 2
    $coun = 2;

    $documento = new Spreadsheet();
    $Spreadsheet  = $documento->getActiveSheet();
    $Spreadsheet->setTitle("PLANILLA DE PRODUCTOS");

    $Spreadsheet->fromArray($productos_total, null, 'A1');
    $Spreadsheet->fromArray($productos_total_str, null, 'B1');

    $Spreadsheet->fromArray($encabezado, null, 'A2');
    $documento
        ->getProperties()
        ->setCreator("ABS Montajes S.A")
        ->setLastModifiedBy('BaulPHP')
        ->setTitle('PLANILLA DE PRODUCTOS')
        ->setSubject('Excel')
        ->setDescription('PLANILLA DE PRODUCTOS')
        ->setKeywords('PHPSpreadsheet')
        ->setCategory('Categoría Cvs');

    $fileName = "planilla_productos_".$name_date_now."_".$time.".xls";

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
    $Spreadsheet->getColumnDimension('P')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('Q')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('R')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('S')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('T')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('U')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('V')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('W')->setAutoSize(true);

    $Spreadsheet->getStyle('1')->getFont()->setBold( true );
    $Spreadsheet->getStyle('2')->getFont()->setBold( true );

    $writer = new Xlsx($documento);
/* */
foreach ($rows as $r) {
    $coun++;

    $id_producto                = $r->id_producto;
    $codigo                     = $r->codigo;
    $producto                   = $r->producto;
    $descripcion                = Strip_tags($r->descripcion) ?: 'SIN DEFINIR';
    $marca                      = $r->marca;
    $laboratorio                = $r->laboratorio;
    $tipo                       = $r->tipo;
    $principios_activos         = $r->principios_activos;
    $origen                     = $r->origen;
    $comision                   = $r->comision;
    $presentacion               = $r->presentacion;
    $clasificacion              = $r->clasificacion;
    $unidad_medida              = $r->unidad_medida;
    $rubro                      = $r->rubro;
    $precio                     = $r->precio;
    $precio_fraccionado         = $r->precio_fraccionado;
    $observaciones              = Strip_tags($r->observaciones) ?: 'SIN DEFINIR';
    $estado_str                 = $r->estado_str;
    $conservacion_str           = $r->conservacion_str;
    $web_str                    = $r->web_str;
    $iva_str                    = $r->iva_str;
    $fecha                      = $r->fecha;
    $cantidad_fracciones        = $r->cantidad_fracciones;

    $Spreadsheet->setCellValueByColumnAndRow(1, $coun, $id_producto);
    $Spreadsheet->setCellValueByColumnAndRow(2, $coun, $codigo);
    $Spreadsheet->setCellValueByColumnAndRow(3, $coun, $producto);
    $Spreadsheet->setCellValueByColumnAndRow(4, $coun, $descripcion);
    $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $marca);
    $Spreadsheet->setCellValueByColumnAndRow(6, $coun, $laboratorio);
    $Spreadsheet->setCellValueByColumnAndRow(7, $coun, $tipo);
    $Spreadsheet->setCellValueByColumnAndRow(8, $coun, $principios_activos);
    $Spreadsheet->setCellValueByColumnAndRow(9, $coun, $origen);
    $Spreadsheet->setCellValueByColumnAndRow(10, $coun, $comision);
    $Spreadsheet->setCellValueByColumnAndRow(11, $coun, $presentacion);
    $Spreadsheet->setCellValueByColumnAndRow(12, $coun, $clasificacion);
    $Spreadsheet->setCellValueByColumnAndRow(13, $coun, $unidad_medida);
    $Spreadsheet->setCellValueByColumnAndRow(14, $coun, $rubro);
    $Spreadsheet->setCellValueByColumnAndRow(15, $coun, $precio);
    $Spreadsheet->setCellValueByColumnAndRow(16, $coun, $precio_fraccionado);
    $Spreadsheet->setCellValueByColumnAndRow(17, $coun, $observaciones);
    $Spreadsheet->setCellValueByColumnAndRow(18, $coun, $estado_str);
    $Spreadsheet->setCellValueByColumnAndRow(19, $coun, $conservacion_str);
    $Spreadsheet->setCellValueByColumnAndRow(20, $coun, $web_str);
    $Spreadsheet->setCellValueByColumnAndRow(21, $coun, $iva_str);
    $Spreadsheet->setCellValueByColumnAndRow(22, $coun, $fecha);
    $Spreadsheet->setCellValueByColumnAndRow(23, $coun, $cantidad_fracciones);

}
$coun++;

$Spreadsheet->getStyle($coun)->getFont()->setBold( true );

$Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('0');
$Spreadsheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('J')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('O')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('P')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
$Spreadsheet->getStyle('U')->getNumberFormat()->setFormatCode('0%');
$Spreadsheet->getStyle('V')->getNumberFormat()->setFormatCode('dd/mm/aaaa;@');
$Spreadsheet->getStyle('W')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

//Agrega filtro a encabezados
$Spreadsheet->setAutoFilter('A2:W3');
//Selecciona el rango de los filtros hasta la ultima fila
$Spreadsheet->getAutoFilter()->setRangeToMaxRow();

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
