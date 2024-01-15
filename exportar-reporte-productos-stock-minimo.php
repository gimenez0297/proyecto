<?php

require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');
\PhpOffice\PhpSpreadsheet\Shared\StringHelper::setThousandsSeparator(',');

include "inc/funciones.php";
$db       = DataBase::conectar();
$usuario  = $auth->getUsername();
$id_sucursal_sistema = datosUsuario($usuario)->id_sucursal;
// $desde 		       = $_REQUEST["desde"];
// $hasta             = $_REQUEST["hasta"];
$id_sucursal       = $_REQUEST["sucursal"];
$id_marca          = $_REQUEST["marca"];
$id_tipo           = $_REQUEST["tipo"];
$id_clasificacion  = $_REQUEST["clasificacion"];
$id_rubro          = $_REQUEST["rubro"];
$id_procedencia    = $_REQUEST["procedencia"];
$id_origen         = $_REQUEST["origen"];
$id_presentacion   = $_REQUEST["presentacion"];
$id_medida         = $_REQUEST["unidad_medida"];
$id_laboratorio    = $_REQUEST["laboratorio"];
$id_producto       = $_REQUEST["id_producto"];
$columnas          = json_decode($_REQUEST["columnas"],true);
$encabezado        = json_decode($_REQUEST["titulos"],true);



if (!empty($id_sucursal) && intVal($id_sucursal) != 0) {
    $and_id_sucursal .= " AND sn.id_sucursal= $id_sucursal";
    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
    $row     = $db->loadObject();
    $sucursal = $row->sucursal;
}else{
    $id_sucursal = $id_sucursal_sistema;
    $db->setQuery("SELECT sucursal FROM sucursales WHERE id_sucursal=$id_sucursal");
    $row     = $db->loadObject();
    $sucursal = $row->sucursal;
}
if (!empty($id_marca) && intVal($id_marca) != 0) {
    $and_id_marca .= " AND p.id_marca= $id_marca";
    $db->setQuery("SELECT marca FROM marcas WHERE id_marca=$id_marca");
    $row     = $db->loadObject();
    $marca = $row->marca;
}else{
    $and_id_marca .= "";
    $marca = "TODAS";
}
if (!empty($id_tipo) && intVal($id_tipo) != 0) {
    $and_id_tipo .= " AND p.id_tipo_producto= $id_tipo";
    $db->setQuery("SELECT tipo FROM tipos_productos WHERE id_tipo_producto=$id_tipo");
    $row     = $db->loadObject();
    $tipo = $row->tipo;
}else{
    $and_id_tipo .= "";
    $tipo = "TODOS";
}
if (!empty($id_clasificacion) && intVal($id_clasificacion) != 0) {
    $and_id_clasificacion .= " AND p.id_clasificacion= $id_clasificacion";
    $db->setQuery("SELECT clasificacion FROM clasificaciones_productos WHERE id_clasificacion_producto=$id_clasificacion");
    $row     = $db->loadObject();
    $clasificacion = $row->clasificacion;
}else{
    $and_id_clasificacion .= "";
    $clasificacion = "TODOS";
}
if (!empty($id_rubro) && intVal($id_rubro) != 0) {
    $and_id_rubro .= " AND p.id_rubro= $id_rubro";
    $db->setQuery("SELECT rubro FROM rubros WHERE id_rubro=$id_rubro");
    $row     = $db->loadObject();
    $rubro = $row->rubro;
}else{
    $and_id_rubro .= "";
    $rubro = "TODOS";
}
if (!empty($id_procedencia) && intVal($id_procedencia) != 0) {
    $and_id_procedencia .= " AND p.id_pais= $id_procedencia";
    $db->setQuery("SELECT nombre_es FROM paises WHERE id_pais=$id_procedencia");
    $row     = $db->loadObject();
    $procedencia = $row->nombre_es;
}else{
    $and_id_procedencia .= "";
    $procedencia = "TODOS";
}
if (!empty($id_origen) && intVal($id_origen) != 0) {
    $and_id_origen .= " AND p.id_origen= $id_origen";
    $db->setQuery("SELECT origen FROM origenes WHERE id_origen=$id_origen");
    $row     = $db->loadObject();
    $origen = $row->origen;
}else{
    $and_id_origen .= "";
    $origen = "TODOS";
}
if (!empty($id_presentacion) && intVal($id_presentacion) != 0) {
    $and_id_presentacion .= " AND p.id_presentacion= $id_presentacion";
    $db->setQuery("SELECT presentacion FROM presentaciones WHERE id_presentacion=$id_presentacion");
    $row     = $db->loadObject();
    $presentacion = $row->presentacion;
}else{
    $and_id_presentacion .= "";
    $presentacion = "TODOS";
}
if (!empty($id_medida) && intVal($id_medida) != 0) {
    $and_id_medida .= " AND p.id_unidad_medida= $id_medida";
    $db->setQuery("SELECT unidad_medida FROM unidades_medidas WHERE id_unidad_medida=$id_medida");
    $row     = $db->loadObject();
    $unidad_medida = $row->unidad_medida;
}else{
    $and_id_medida .= "";
    $unidad_medida = "TODOS";
}
if (!empty($id_laboratorio) && intVal($id_laboratorio) != 0) {
    $and_id_laboratorio .= " AND p.id_laboratorio= $id_laboratorio";
    $db->setQuery("SELECT laboratorio FROM laboratorios WHERE id_laboratorio=$id_laboratorio");
    $row     = $db->loadObject();
    $laboratorio = $row->laboratorio;
}else{
    $and_id_laboratorio .= "";
    $laboratorio = "TODOS";
}
if (!empty($id_producto) && intVal($id_producto) != 0) {
    $and_id_producto .= " AND p.id_producto= $id_producto";
    $db->setQuery("SELECT producto FROM productos WHERE id_producto=$id_producto");
    $row     = $db->loadObject();
    $producto = $row->producto;
}else{
    $and_id_producto .= "";
    $producto = "TODOS";
}


$db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                p.id_producto,
                p.codigo,
                p.producto,  
                m.marca,
                l.`laboratorio`,
                t.tipo,
                o.`origen`,
                md.`moneda`,
                pr.`presentacion`,
                pa.`nombre` AS principio_activo,
                c.clasificacion,
                um.`unidad_medida`,
                r.`rubro`,
                ps.`nombre_es` AS pais,
                CASE p.`conservacion` WHEN '1' THEN 'NORMAL' WHEN '2' THEN 'REFRIGERADO' END AS conservacion,  
                (
                SELECT 
                IFNULL(SUM(s.stock), 0)
                FROM stock s 
                JOIN lotes l ON s.id_lote=l.id_lote
                WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id_sucursal  AND vencimiento>=CURRENT_DATE()
                ) AS stock,
                IFNULL(sn.minimo, 0) AS minimo,
                IFNULL(sn.maximo, 0) AS maximo
                FROM productos p
                LEFT JOIN presentaciones pr ON pr.`id_presentacion` = p.`id_presentacion`
                LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto 
                LEFT JOIN marcas m ON m.id_marca=p.id_marca
                LEFT JOIN tipos_productos t ON t.id_tipo_producto = p.id_tipo_producto
                LEFT JOIN clasificaciones_productos c ON c.id_clasificacion_producto=p.id_clasificacion
                LEFT JOIN laboratorios l ON l.`id_laboratorio`= p.`id_laboratorio`
                LEFT JOIN origenes o ON o.`id_origen`=p.`id_origen`
                LEFT JOIN monedas md ON md.`id_moneda`=p.`id_moneda`
                LEFT JOIN productos_principios ppr ON ppr.`id_producto`=p.`id_producto`
                LEFT JOIN principios_activos pa ON pa.`id_principio`=ppr.`id_principio`
                LEFT JOIN unidades_medidas um ON um.`id_unidad_medida`=p.`id_unidad_medida`
                LEFT JOIN rubros r ON r.`id_rubro`=p.`id_rubro`
                LEFT JOIN paises ps ON ps.`id_pais`=p.`id_pais`
                LEFT JOIN stock_niveles sn ON p.id_producto=sn.id_producto AND sn.id_sucursal=$id_sucursal 
                $and_id_producto $and_id_laboratorio $and_id_marca $and_id_unidad_medida $and_id_presentacion $and_id_clasificacion $and_id_origen $and_id_procedencia $and_id_rubro
                GROUP BY id_producto 
                HAVING stock <= minimo AND minimo > 0
                ORDER BY stock DESC");
$rows = $db->loadObjectList();

# Encabezado de los productos
//$encabezado = ["CODIGO", "PRODUCTO", "CANTIDAD", "MARCA", "TIPO", "CLASIFICACIÓN"];

// $desde_titulo = ["DESDE:"];
// $desde_str = [fechaLatina($desde)];

// $hasta_titulo = ["HASTA:"];
// $hasta_str = [fechaLatina($hasta)];

$sucursal_titulo = ["SUCURSAL:"];
$sucursal_str = [$sucursal];

// $marcas_titulo = ["MARCAS:"];
// $marcas_str = [$marca];

// $tipos_titulo = ["TIPOS:"];
// $tipos_str = [$tipo];

// $clasificaciones_titulo = ["CLASIFICACIONES:"];
// $clasificaciones_str = [$clasificacion];

# Comenzamos en la fila 2
$coun = 4;

$documento = new Spreadsheet();
$productos_vendidos  = $documento->getActiveSheet();
$productos_vendidos->setTitle("Productos Stock Mínimo");

// $productos_vendidos->fromArray($desde_titulo, null, 'A1');
// $productos_vendidos->fromArray($desde_str, null, 'B1');

// $productos_vendidos->fromArray($hasta_titulo, null, 'D1');
// $productos_vendidos->fromArray($hasta_str, null, 'E1');

$productos_vendidos->fromArray($sucursal_titulo, null, 'A2');
$productos_vendidos->fromArray($sucursal_str, null, 'B2');

// $productos_vendidos->fromArray($marcas_titulo, null, 'D2');
// $productos_vendidos->fromArray($marcas_str, null, 'E2');

// $productos_vendidos->fromArray($tipos_titulo, null, 'A3');
// $productos_vendidos->fromArray($tipos_str, null, 'B3');

// $productos_vendidos->fromArray($clasificaciones_titulo, null, 'D3');
// $productos_vendidos->fromArray($clasificaciones_str, null, 'E3');

$productos_vendidos->fromArray($encabezado, null, 'A3');

$documento
    ->getProperties()
    ->setCreator("ABS Montajes S.A")
    ->setLastModifiedBy('BaulPHP')
    ->setTitle('Productos Stock Mínimo')
    ->setSubject('Excel')
    ->setDescription('Productos Stock Mínimo')
    ->setKeywords('PHPSpreadsheet')
    ->setCategory('Categoría Cvs');

$fileName = "productosStockMinimo.xls";

$writer = new Xlsx($documento);

$productos_vendidos->getColumnDimension('A')->setAutoSize(true);
$productos_vendidos->getColumnDimension('B')->setAutoSize(true);
$productos_vendidos->getColumnDimension('C')->setAutoSize(true);
$productos_vendidos->getColumnDimension('D')->setAutoSize(true);
$productos_vendidos->getColumnDimension('E')->setAutoSize(true);
$productos_vendidos->getColumnDimension('F')->setAutoSize(true);
$productos_vendidos->getColumnDimension('G')->setAutoSize(true);
$productos_vendidos->getColumnDimension('H')->setAutoSize(true);
$productos_vendidos->getColumnDimension('I')->setAutoSize(true);
$productos_vendidos->getColumnDimension('J')->setAutoSize(true);
$productos_vendidos->getColumnDimension('K')->setAutoSize(true);
$productos_vendidos->getColumnDimension('L')->setAutoSize(true);
$productos_vendidos->getColumnDimension('M')->setAutoSize(true);
$productos_vendidos->getColumnDimension('N')->setAutoSize(true);
$productos_vendidos->getColumnDimension('O')->setAutoSize(true);
$productos_vendidos->getColumnDimension('P')->setAutoSize(true);


$productos_vendidos->getStyle('1')->getFont()->setBold( true );
$productos_vendidos->getStyle('2')->getFont()->setBold( true );
$productos_vendidos->getStyle('3')->getFont()->setBold( true );


foreach ($rows as $r) {
    $coun++;
    $codigo             = $r->codigo;
    $producto           = $r->producto;
    $marca              = $r->marca;
    $laboratorio        = $r->laboratorio;
    $tipo               = $r->tipo;
    $origen             = $r->origen;
    $moneda             = $r->moneda;
    $presentacion       = $r->presentacion;
    $principio_activo   = $r->principio_activo;
    $clasificacion      = $r->clasificacion;
    $unidad_medida      = $r->unidad_medida;
    $rubro              = $r->rubro;
    $pais               = $r->pais;
    $conservacion       = $r->conservacion;
    $stock              = $r->stock;
    $minimo             = $r->minimo;

    $total += $cantidad;

    if (in_array('codigo',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('codigo', $columnas)+1, $coun, quitaSeparadorMiles($codigo));   
    }
    if (in_array('producto',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('producto', $columnas)+1, $coun, $producto);
    }
    if (in_array('marca',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('marca', $columnas)+1, $coun, $marca);
    }
    if (in_array('laboratorio',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('laboratorio', $columnas)+1, $coun, $laboratorio);
    }
    if (in_array('tipo',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('tipo', $columnas)+1, $coun, $tipo);
    }
    if (in_array('origen',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('origen', $columnas)+1, $coun, $origen);
    }
    if (in_array('moneda',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('moneda', $columnas)+1, $coun, $moneda);
    }
    if (in_array('presentacion',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('presentacion', $columnas)+1, $coun, $presentacion);
    }
    if (in_array('principio_activo',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('principio_activo', $columnas)+1, $coun, $principio_activo);
    }
    if (in_array('clasificacion',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('clasificacion', $columnas)+1, $coun, $clasificacion);
    }
    if (in_array('unidad_medida',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('unidad_medida', $columnas)+1, $coun, $unidad_medida);
    }
    if (in_array('rubro',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('rubro', $columnas)+1, $coun, $rubro);
    }
    if (in_array('pais',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('pais', $columnas)+1, $coun, $pais);
    }
    if (in_array('conservacion',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('conservacion', $columnas)+1, $coun, $conservacion);
    }
    if (in_array('stock',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('stock', $columnas)+1, $coun, $stock);
    }
    if (in_array('minimo',$columnas)) {
        $productos_vendidos->setCellValueByColumnAndRow(array_search('minimo', $columnas)+1, $coun, $minimo);
    }

}

$coun++;

$productos_vendidos->getStyle($coun)->getFont()->setBold( true );

// $productos_vendidos->setCellValueByColumnAndRow(1, $coun, 'TOTAL');
// $productos_vendidos->setCellValueByColumnAndRow(3, $coun, $total);

$productos_vendidos->getStyle('A')->getNumberFormat()->setFormatCode('###');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
