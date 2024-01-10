<?php

    require_once 'inc/PhpSpreadsheet/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');

    include ("inc/funciones.php");
    if (!verificaLogin()) {
        echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
        exit;
    }

    $db = DataBase::conectar();
    $q                          = $_REQUEST['q'];
    $usuario                    = $auth->getUsername();
    $datosUsuario               = datosUsuario($usuario);
    $id_sucursal                = $datosUsuario->id_sucursal;
    $db->setQuery("SELECT * from sucursales where id_sucursal='$id_sucursal'");
	$datos_sucursal_usuario     = $db->loadObject();
    $sucursal_usuario_nombre    = $datos_sucursal_usuario->sucursal;

    $id_rol                     = $datosUsuario->id_rol;
    $fecha_actual               = date('Y-m-d');

    # Encabezado de los productos
    $encabezado                 = ["#", "CODIGO", "PRODUCTO", "PRESENTACIÓN", "VTO. LOTE", "VTO. CANJE", "CANTIDAD", "FRACCIONADO", "FRACCIONABLE EN"];
    $fecha_current = "FECHA DE IMPRESIÓN";
    $fecha_current_str = fechaLatina($fecha_actual);

    $sucursal       = $db->clearText($_REQUEST['id_sucursal']);
    $estado         = $db->clearText($_REQUEST['estado']);
    $proveedor      = $db->clearText($_REQUEST['proveedor']);
    $tipo           = $db->clearText($_REQUEST['tipo']);
    $rubro          = $db->clearText($_REQUEST['rubro']);
    $procedencia    = $db->clearText($_REQUEST['procedencia']);
    $origen         = $db->clearText($_REQUEST['origen']);
    $clasificacion  = $db->clearText($_REQUEST['clasificacion']);
    $presentacion   = $db->clearText($_REQUEST['presentacion']);
    $unidad_medida  = $db->clearText($_REQUEST['unidad_medida']);
    $marca          = $db->clearText($_REQUEST['marca']);
    $laboratorio    = $db->clearText($_REQUEST['laboratorio']);

    $where = "";
    $where_sucursal = "";

    //titulos
        $sucursal_titulo = "SUCURSAL";
        $proveedor_titulo = "PROVEEDOR";
        $tipo_titulo = "TIPO";
        $rubro_titulo = "RUBRO";
        $procedencia_titulo = "PROCEDENCIA";
        $origen_titulo = "ORIGEN";
        $clasificacion_titulo = "CLASIFICACION";
        $presentacion_titulo = "PRESENTACION";
        $unidad_medida_titulo = "UNIDAD DE MEDIDA";
        $marca_titulo = "MARCA";
        $laboratorio_titulo = "LABORATORIO";
        $estado_titulo = "ESTADO";
    //


    if (!empty($proveedor)) {
        $db->setQuery("SELECT proveedor FROM proveedores WHERE id_proveedor='$proveedor'");
        $proveedor_str     = $db->loadObject()->proveedor;
        
        $where .= " AND pp.id_proveedor = $proveedor";
    }

    if (!empty($tipo)) {
        $db->setQuery("SELECT tipo FROM tipos_productos WHERE id_tipo_producto='$tipo'");
        $tipo_str      = $db->loadObject()->tipo;
        
        $where .= " AND p.id_tipo_producto = $tipo";
    }

    if (!empty($rubro)) {
        $db->setQuery("SELECT rubro FROM rubros WHERE id_rubro='$rubro'");
        $rubro_str      = $db->loadObject()->rubro;
        
        $where .= " AND p.id_rubro = $rubro";
    }

    if (!empty($procedencia)) {
        $db->setQuery("SELECT nombre_es FROM paises WHERE id_pais='$procedencia'");
        $procedencia_str      = $db->loadObject()->nombre_es;
        
        $where .= " AND p.id_pais = $procedencia";
    }

    if (!empty($origen)) {
        $db->setQuery("SELECT origen FROM origenes WHERE id_origen='$origen'");
        $origen_str      = $db->loadObject()->origen;
        
        $where .= " AND p.id_origen = $origen";
    }

    if (!empty($clasificacion)) {
        $db->setQuery("SELECT clasificacion FROM clasificaciones_productos WHERE id_clasificacion_producto='$clasificacion'");
        $clasificacion_str      = $db->loadObject()->clasificacion;
        
        $where .= " AND p.id_clasificacion = $clasificacion";
    }

    if (!empty($presentacion)) {
        $db->setQuery("SELECT presentacion FROM presentaciones WHERE id_presentacion='$presentacion'");
        $presentacion_str      = $db->loadObject()->presentacion;
        
        $where .= " AND p.id_presentacion = $presentacion";
    }

    if (!empty($unidad_medida)) {
        $db->setQuery("SELECT unidad_medida FROM unidades_medidas WHERE id_unidad_medida='$unidad_medida'");
        $unidad_medida_str      = $db->loadObject()->unidad_medida;
        
        $where .= " AND p.id_unidad_medida = $unidad_medida";
    }

    if (!empty($marca)) {
        $db->setQuery("SELECT marca FROM marcas WHERE id_marca='$marca'");
        $marca_str      = $db->loadObject()->marca;

        $where .= " AND p.id_marca = $marca";
    }

    if (!empty($laboratorio)) {
        $db->setQuery("SELECT laboratorio FROM laboratorios WHERE id_laboratorio='$laboratorio'");
        $laboratorio_str      = $db->loadObject()->laboratorio;

        $where .= " AND p.id_laboratorio = $laboratorio";
    }

    if (!empty($estado) && intVal($estado) == 2) {
        //activo
        $estado_str = "ACTIVO";
        $where = "AND (l.vencimiento >= CURRENT_DATE() OR l.vencimiento IS NULL)";
    } else if(!empty($estado) && intVal($estado) == 1) {
        //vencido
        $estado_str = "VENCIDO";
        $where = "AND l.vencimiento <= CURRENT_DATE()";
    }
    
    if (esAdmin($id_rol) === false) {
        $where_sucursal .= " AND s.id_sucursal=$id_sucursal";
        $sucursal_str = $sucursal_usuario_nombre;
    } else if(!empty($sucursal) && intVal($sucursal) != 0) {
        $where_sucursal .= " AND s.id_sucursal=$sucursal";
        $db->setQuery("SELECT sucursal from sucursales where id_sucursal='$sucursal'");
        $name_sucursal     = $db->loadObject()->sucursal;
        $sucursal_str      = $name_sucursal;
    } else if(empty($sucursal) || intVal($sucursal) == 0){
        $where_sucursal .= "";
        $sucursal_str      = "TODAS";
    }

    $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                    s.id_stock,
                    IFNULL(SUM(s.stock), 0) AS stock,
                    IFNULL(SUM(s.fraccionado), 0) AS fraccionado,
                    IFNULL(p.cantidad_fracciones, 0) AS cantidad_fracciones,
                    DATE_FORMAT(l.vencimiento, '%d/%m/%Y') AS vencimiento_lote,
                    DATE_FORMAT(l.vencimiento_canje, '%d/%m/%Y') AS vencimiento_canje,
                    p.id_producto,
                    pre.presentacion,
                    p.codigo,
                    p.producto,
                    p.precio,
                    p.precio_fraccionado
                    FROM productos p
                    LEFT JOIN stock s ON p.id_producto=s.id_producto 
                    LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                    LEFT JOIN lotes l ON s.id_lote = l.id_lote
                    LEFT JOIN productos_proveedores pp ON pp.id_producto = p.id_producto
                    WHERE 1=1 $where_sucursal $where 
                    GROUP BY p.id_producto
                    ORDER BY p.producto ASC");
    $row_s = $db->loadObjectList();

    # Comenzamos en la fila 2
    $coun = 2;

    $documento = new Spreadsheet();
    $Spreadsheet  = $documento->getActiveSheet();
    $Spreadsheet->setTitle("Planilla de Stock");

    $cell = 1;
    if (!empty($fecha_current_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $fecha_current);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $fecha_current_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($sucursal_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $sucursal_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $sucursal_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($proveedor_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $proveedor_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $proveedor_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($tipo_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $tipo_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $tipo_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($rubro_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $rubro_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $rubro_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($procedencia_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $procedencia_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $procedencia_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($origen_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $origen_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $origen_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($clasificacion_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $clasificacion_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $clasificacion_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($presentacion_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $presentacion_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $presentacion_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($unidad_medida_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $unidad_medida_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $unidad_medida_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($marca_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $marca_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $marca_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($laboratorio_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $laboratorio_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $laboratorio_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }
    
    if (!empty($estado_str)) {
        $Spreadsheet->setCellValueByColumnAndRow($cell, 1, $estado_titulo);
        $Spreadsheet->setCellValueByColumnAndRow($cell+1, 1, $estado_str);
        $Spreadsheet->getStyleByColumnAndRow($cell, 1, null, null)->getFont()->setBold( true );
        $Spreadsheet->getColumnDimensionByColumn($cell)->setAutoSize(true);
        $Spreadsheet->getColumnDimensionByColumn($cell-1)->setAutoSize(true);
        $cell++;$cell++;
    }

    $Spreadsheet->fromArray($encabezado, null, 'A2');
    $documento
        ->getProperties()
        ->setCreator("Freelancers Py S.A.")
        ->setLastModifiedBy('BaulPHP')
        ->setTitle('Planilla de Stock')
        ->setSubject('Excel')
        ->setDescription('Planilla de Stock')
        ->setKeywords('PHPSpreadsheet')
        ->setCategory('Categoría Cvs');

    $fileName = "planilla_stock_".date_format(date_create_from_format("Y-m-d", $fecha_actual), 'd_m_Y').".xls";

    $Spreadsheet->getStyle('2')->getFont()->setBold( true );

    $writer = new Xlsx($documento);

    foreach ($row_s as $r) {
        $coun++;
        $nro                    = ($coun?:0) - 2;
        $codigo                 = $r->codigo;
        $descripcion            = $r->producto;
        $presentacion           = $r->presentacion;
        $vencimiento_lote       = $r->vencimiento_lote?:'00/00/0000';
        $vencimiento_canje      = $r->vencimiento_canje?:'00/00/0000';
        $cantidad               = $r->stock?:0;
        $fraccionado            = $r->fraccionado?:0;
        $cantidad_fracciones    = $r->cantidad_fracciones?:0;

        /* A */ $Spreadsheet->setCellValueByColumnAndRow(1, $coun, $nro);
        /* B */ $Spreadsheet->setCellValueByColumnAndRow(2, $coun, $codigo);
        /* C */ $Spreadsheet->setCellValueByColumnAndRow(3, $coun, $descripcion);
        /* D */ $Spreadsheet->setCellValueByColumnAndRow(4, $coun, $presentacion);
        /* E */ $Spreadsheet->setCellValueByColumnAndRow(5, $coun, $vencimiento_lote);
        /* F */ $Spreadsheet->setCellValueByColumnAndRow(6, $coun, $vencimiento_canje);
        /* G */ $Spreadsheet->setCellValueByColumnAndRow(7, $coun, $cantidad);
        /* H */ $Spreadsheet->setCellValueByColumnAndRow(8, $coun, $fraccionado);
        /* I */ $Spreadsheet->setCellValueByColumnAndRow(9, $coun, $cantidad_fracciones);

    }
    $coun++;

    $Spreadsheet->getStyle($coun)->getFont()->setBold( true );

    $Spreadsheet->getColumnDimension('A')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('B')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('C')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('D')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('E')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('F')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('G')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('H')->setAutoSize(true);
    $Spreadsheet->getColumnDimension('I')->setAutoSize(true);

    //Agrega filtro a encabezados
    $Spreadsheet->setAutoFilter('A2:I3');
    //Selecciona el rango de los filtros hasta la ultima fila
    $Spreadsheet->getAutoFilter()->setRangeToMaxRow();

    $Spreadsheet->getStyle('A')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('0');
    $Spreadsheet->getStyle('G')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
    $Spreadsheet->getStyle('H')->getNumberFormat()->setFormatCode('#,##0;-#,##0');

header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
header("Content-Type: application/vnd.ms-excel charset=utf-8");
$writer->save('php://output');
