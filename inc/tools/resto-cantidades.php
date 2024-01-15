<?php
// actualiza cantidades no actualizadas

    require_once '../PhpSpreadsheet/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');

function csvToArray($csvFile)
{
    $lines = file($csvFile, FILE_IGNORE_NEW_LINES);
    $arrayData = [];
    foreach ($lines as $line) {
        $arrayData[] = str_getcsv($line, ";");
    }
    return $arrayData;
}

// Función para buscar el valor en el array y devolver el subarray completo
function searchInArray($dataArray, $searchValue)
{
    foreach ($dataArray as $array) {
        $data = $array[0];
        if ($data === $searchValue) {
            return $array;
        }
    }
    return null;
}

function generarLote($db){
    $db->setQuery("SELECT MAX(CAST(REPLACE(lote, 'SV', '') AS SIGNED)) AS ultimo_lote FROM lotes WHERE lote LIKE 'SV%'");
    $ultimo_lote = $db->loadObject()->ultimo_lote;
    $lote_nuevo = 'SV'.($ultimo_lote+1);
    return $lote_nuevo;
}

function obtenerIdProducto($db, $codigo){
    $db->setQuery("SELECT id_producto FROM productos WHERE codigo = '$codigo';");
    $id = $db->loadObject()->id_producto;
    return $id;
}

function consultarLote($db, $lot){
    $db->setQuery("SELECT lote FROM lotes WHERE lote = '$lot';");
    $res = $db->loadObject()->lote;
    $res = !empty($res) ? $res : false;
    return $res;
}

function consultarProveedor($db, $cod, $idp){
    if (empty($idp)) {
        $id = obtenerIdProducto($db, $cod);
    }else {
        $id = $idp;
    }

    if (!empty($id) && ($id != false)) { // Si encuentra el codigo de barras
        $query = "SELECT id_proveedor, proveedor_principal FROM productos_proveedores WHERE id_producto = CAST('$id' AS UNSIGNED);";
        $db->setQuery($query);

        $lista_p = $db->loadObjectList();
        
        $principal = 0;
        foreach ($lista_p as $key => $value) {
            if ($value->proveedor_principal == 1) {
                $principal = $value->id_proveedor;
            }
        }
        if (empty($principal) || $principal == 0) {
            return false;
        } else {
            return $principal;
        }
    }
    return false;
}

include ("../funciones.php");
verificaLogin();
$id_sucursal = $_REQUEST['sucursal'];
$usuario = $auth->getUsername();
$SIN_PROVEEDORES = [];

$db = DataBase::conectar();
$db->autocommit(false);

if (!empty($id_sucursal)) {

    /* Excel de cantidades iniciales */
        $archivo = "sin_perfectos.csv";
        if (!file_exists($archivo)) {
            echo json_encode(["mensaje"=>"No se encontró el archivo", "status"=>"error"]);
            exit;
        }
        $conenido = file_get_contents($archivo);
        $array = explode("\r", $conenido);
        $cantidad = count($array);
        $i = 0;
        $duplicados = 0;
        $omitidos = 0;
    /**/
    /* Backup */
        /* $csvFile = 'backup.csv';  // Reemplaza 'archivo.csv' por la ruta de tu archivo CSV
        $anterior = csvToArray($csvFile); */
    /**/

    $fecha_actual = new DateTime(); // Crea un objeto DateTime con la fecha y hora actuales
    $fecha_actual->modify('last day of this month'); // Establece la fecha al último día de este mes
    $fecha_actual->add(new DateInterval('P1Y')); // Agrega un año a la fecha actual
    $fecha_vencimiento = $fecha_actual->format('Y-m-d');

    $db->setQuery("UPDATE stock SET stock = 0, fraccionado = 0 WHERE id_sucursal = $id_sucursal;");
    if (!$db->alter()) {
        $db->rollback();
        echo json_encode(["mensaje"=>"No se pudo actualizar stock del producto $producto con lote $lote", "status"=>"error"]);
        exit;
    }

    while ($i < $cantidad) {
        $linea = $array[$i];
        $linear = explode(';', $linea);

        $set_lote = null;

        $cod_interno = (int)preg_replace("/[^0-9]/", "", $db->clearText($linear[1]));
        $cod_barra = $db->clearText($linear[0]);
        $producto = $db->clearText($linear[2]);
        $lote = $db->clearText($linear[4]);
        $fecha_v = $db->clearText($linear[5]);
        $cant = (int)preg_replace("/[^0-9]/", "",$db->clearText($linear[3]));

        $id_proveedor = consultarProveedor($db, $cod_barra, $cod_interno);

        if (empty($lote)) {
            $lote = generarLote($db);
            $query = "INSERT INTO lotes 
                        (id_proveedor, lote, vencimiento, canje, vencimiento_canje, costo, usuario, fecha)
                        VALUES
                        ($id_proveedor, '$lote', '$fecha_v', 0, '0000-00-00', 0, '$usuario', NOW() )
                    ;";
            $db->setQuery($query);
            if (!$db->alter()) {
                $db->rollback();
                echo json_encode(["mensaje"=>"No se pudo generar el lote $lote. Error: ".$db->getError(), "status"=>"error"]);
                exit;
            }

            $id_lote = $db->getLastID();

            $db->setQuery("INSERT INTO stock(id_producto, id_sucursal, id_lote, stock)
                        SELECT $cod_interno, id_sucursal, $id_lote, 0 FROM sucursales");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                $db->rollback();  // Revertimos los cambios
                exit;
            }
        }

        $db->setQuery("SELECT s.id_lote FROM stock s LEFT JOIN lotes l 
        ON l.id_lote = s.id_lote WHERE l.lote = '$lote' AND l.id_proveedor = '$id_proveedor' AND s.id_producto = '$cod_interno' GROUP BY s.id_lote");
        $id_lote = $db->loadObject()->id_lote;

        if (empty($id_lote)) {
            if (!empty($id_proveedor) && ($id_proveedor != false)) {
                $ff = $fecha_v ?: $fecha_vencimiento;
                $query = "INSERT INTO lotes 
                    (id_proveedor, lote, vencimiento, canje, vencimiento_canje, costo, usuario, fecha)
                    VALUES
                    ($id_proveedor, '$lote', '$ff', 0, '0000-00-00', 0, '$usuario', NOW() );";
                $db->setQuery($query);
                if (!$db->alter()) {
                    $db->rollback();
                    echo json_encode(["mensaje"=>"No se pudo insertar el lote $lote para el producto $producto. Error: ".$db->getError(), "status"=>"error"]);
                    exit;
                }

                $id_lote = $db->getLastID();

                $db->setQuery("INSERT INTO stock(id_producto, id_sucursal, id_lote, stock)
                        SELECT $cod_interno, id_sucursal, $id_lote, 0 FROM sucursales");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
            }else {
                $add_prod = (object)[
                    "ID" => $cod_interno,
                    "codigo" => $cod_barra,
                    "producto" => $producto
                ];
                
                array_push($SIN_PROVEEDORES, $add_prod);
                $where_codigo = null;                    ;
            }
        }
        

        //SE VERIFICA SI EL LOTE EXISTE REGISTRADO
        $entero_s = $fraccionado_s = $entero_r = $fraccionado_r = $inicial = $real = 0;
        if (!empty($id_lote)) {
            $inicial = empty($cant) ? 0 : $cant;

            $fecha_ac = $fecha_v ?: $fecha_vencimiento;

            $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad, IFNULL(SUM(fraccionado), 0) AS fraccionado FROM stock_historial 
            WHERE (fecha > '2023-05-12') AND (id_sucursal = $id_sucursal) AND (operacion = 'SUB')
            AND id_lote = '$id_lote' AND id_producto = '$cod_interno';");
            $m_restar = $db->loadObject();
            $entero_r = $m_restar->cantidad;
            $fraccionado_r = $m_restar->fraccionado;
            
            $db->setQuery("SELECT IFNULL(SUM(cantidad), 0) AS cantidad, IFNULL(SUM(fraccionado), 0) AS fraccionado FROM stock_historial 
            WHERE (fecha > '2023-05-12') AND (id_sucursal = $id_sucursal) AND (operacion = 'ADD')
            AND id_lote = '$id_lote' AND id_producto = '$cod_interno';");
            $m_sumar = $db->loadObject();
            $entero_s = $m_sumar->cantidad;
            $fraccionado_s = $m_sumar->fraccionado;

            $db->setQuery("SELECT fraccion, cantidad_fracciones FROM productos WHERE id_producto = $cod_interno;");
            $sql = $db->loadObject();
            $fraccion = $sql->fraccion;
            $cant_fraccion = $sql->cantidad_fracciones;

            if ($fraccion == 0) {
                $cant_fraccion = 1;
            }

            $inicial_div = $inicial / $cant_fraccion;
            $inicial_exp = explode(".", $inicial_div);

            $inicial_entero = $inicial_exp[0];
            $inicial_fraccionado = $inicial % $cant_fraccion;

            $real_entero = ($inicial_entero + $entero_s) - $entero_r;
            $real_fracc = ($inicial_fraccionado + $fraccionado_s) - $fraccionado_r;


            $db->setQuery("SELECT id_sucursal FROM stock WHERE id_producto = $cod_interno AND id_lote = $id_lote AND id_sucursal = $id_sucursal;");
            $id_suc_prod = $db->loadObject()->id_sucursal;

            if (empty($id_suc_prod)) {
                $db->setQuery("INSERT INTO stock 
                    (id_producto, id_sucursal, id_lote, stock, fraccionado)
                    VALUES
                    ('$cod_interno', '$id_sucursal', '$id_lote', '$real_entero', '$real_fracc');");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
                    $db->rollback();  // Revertimos los cambios
                    exit;
                }
            }

            $db->setQuery("UPDATE stock SET stock = '$real_entero', fraccionado = '$real_fracc' WHERE id_lote = '$id_lote' AND id_producto = '$cod_interno' AND id_sucursal = $id_sucursal;");
            if (!$db->alter()) {
                $db->rollback();
                echo json_encode(["mensaje"=>"No se pudo actualizar stock del producto $producto con lote $lote", "status"=>"error"]);
                exit;
            }

            $db->setQuery("UPDATE lotes SET vencimiento = '$fecha_ac' WHERE id_lote = '$id_lote';");
            if (!$db->alter()) {
                $db->rollback();
                echo json_encode(["mensaje"=>"No se pudo actualizar stock del producto $producto con lote $lote", "status"=>"error"]);
                exit;
            }
        }
        $i++;

    }

    $db->commit();
    if (!empty($SIN_PROVEEDORES)) {
        $date_now = date_create();
        $time = date_timestamp_get($date_now);
        $name_date_now = date_format($date_now, "d_m_Y");
        
        $encabezado = [
            "ID PRODUCTO", 
            "CÓDIGO", 
            "PRODUCTO", 
        ];
        $coun = 2;
        $documento = new Spreadsheet();
        $Spreadsheet  = $documento->getActiveSheet();
        $Spreadsheet->setTitle("SIN PROVEEDOR PRINCIPAL");

        $Spreadsheet->fromArray($encabezado, null, 'A2');
        $documento
            ->getProperties()
            ->setCreator("ABS Montajes S.A")
            ->setLastModifiedBy('BaulPHP')
            ->setTitle('SIN PROVEEDOR PRINCIPAL')
            ->setSubject('Excel')
            ->setDescription('SIN PROVEEDOR PRINCIPAL')
            ->setKeywords('PHPSpreadsheet')
            ->setCategory('Categoría Cvs');

        $fileName = "sin_proveedor_principal_".$name_date_now."_".$time."_SUC_$id_sucursal".".xlsx";
        $Spreadsheet->getColumnDimension('A')->setAutoSize(true);
        $Spreadsheet->getColumnDimension('B')->setAutoSize(true);
        $Spreadsheet->getColumnDimension('C')->setAutoSize(true);

        $Spreadsheet->getStyle('1')->getFont()->setBold( true );
        $Spreadsheet->getStyle('2')->getFont()->setBold( true );
        $writer = new Xlsx($documento);

        foreach ($SIN_PROVEEDORES as $r) {
            $coun++;

            $id_pp        = $r->ID;
            $cb           = $r->codigo;
            $descripcion  = $r->producto;

            $Spreadsheet->setCellValueByColumnAndRow(1, $coun, $id_pp);
            $Spreadsheet->setCellValueByColumnAndRow(2, $coun, $cb);
            $Spreadsheet->setCellValueByColumnAndRow(3, $coun, $descripcion);

        }
        $coun++;

        $Spreadsheet->getStyle('A')->getNumberFormat()->setFormatCode('0');
        $Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('0');

        //Agrega filtro a encabezados
        $Spreadsheet->setAutoFilter('A2:B3');
        //Selecciona el rango de los filtros hasta la ultima fila
        $Spreadsheet->getAutoFilter()->setRangeToMaxRow();

        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        header("Content-Type: application/vnd.ms-excel charset=utf-8");
        $writer->save('php://output');

    }else{
        echo json_encode(["mensaje"=>"No hay productos sin proveedor principal", "status"=>"success"]);
    }
}else {
    echo json_encode(["mensaje"=>"Se necesita el id de la sucursal", "status"=>"error"]);
}

echo json_encode(["mensaje"=>"Datos actualizados con exito", "status"=>"error"]);
?>