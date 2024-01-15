<?php
    include ("funciones.php");

    require_once 'PhpSpreadsheet/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    \PhpOffice\PhpSpreadsheet\Shared\StringHelper::setDecimalSeparator('.');

    /*quita limite de memoria*/
        /* ini_set("memory_limit","-1");
        set_time_limit(500); */

        if (!verificaLogin()) {
            echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
            exit;
        }

    $id_sucursal = $_REQUEST['sucursal'];
    $usuario = $auth->getUsername();
    $SIN_PROVEEDORES = [];

    $db = DataBase::conectar();
    $db->autocommit(false);
    
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
            $db->setQuery("SELECT id_proveedor, proveedor_principal FROM productos_proveedores WHERE id_producto = '$id';");

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
    
    if (!empty($id_sucursal)) {

        $archivo = "INVENTARIO_FARMACIA_SANTA_VICTORIA_CENTRO_LOGISTICO_05_2023_1.csv";

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

        $fecha_actual = new DateTime(); // Crea un objeto DateTime con la fecha y hora actuales
        $fecha_actual->modify('last day of this month'); // Establece la fecha al último día de este mes
        $fecha_actual->add(new DateInterval('P1Y')); // Agrega un año a la fecha actual
        $fecha_vencimiento = $fecha_actual->format('Y-m-d');

        while ($i < $cantidad) {
        
            $linea = $array[$i];
            $linear = explode(';', $linea);

            $set_lote = null;
            $lote_existia = true;
            $where_codigo = null;
            $id_proveedor = null;

            $cod_interno = trim(utf8_encode($db->clearText($linear[0])));
            $cod_barra = trim(utf8_encode($db->clearText($linear[3])));
            $producto = trim(utf8_encode($db->clearText($linear[4])));
            $lote = $const_lote = trim(utf8_encode($db->clearText($linear[1])));
            $fecha_v = trim(utf8_encode($db->clearText($linear[2])));
            $cod_fisico = trim(utf8_encode($db->clearText($linear[7])));
            $cant = trim(utf8_encode($db->clearText($linear[5])));
                        
            /* SI NO EXISTE FECHA */
            if (empty($fecha_v)) {
                /* SE CREA LA FECHA */
                $fecha_v = $fecha_vencimiento;
                $set_lote .= " vencimiento = '$fecha_v' ";
            }else {
                $set_lote .= " vencimiento = '$fecha_v' ";
            }
            /* FIN SI */

            /* SI EXISTE "COD BARRA FISICO" */
            if (!empty($cod_fisico)) {
                /* SE REEMPLAZA "COD DE BARRA" */
                $where_codigo .= " codigo = '$cod_fisico' ";
            }
            /* FIN SI */

            /* SI EXISTE EL LOTE */
            if (!empty($lote)) {
                /* SE ACTUALIZA SU VENCIMIENTO SI ES NECESARIO*/

                if (!empty(consultarLote($db, $lote))) {
                    if (!empty($set_lote)) {
                        $id_p = $cod_interno;
                        if (!empty($id_p)) {
                            $db->setQuery("UPDATE lotes SET $set_lote WHERE lote = '$lote';");
                            if (!$db->alter()) {
                                $db->rollback();
                                echo json_encode(["mensaje"=>"No se pudo actualizar el vencimiento de lote $lote. Error: ".$db->getError(), "status"=>"error"]);
                                exit;
                            }
                        }else{
                            $db->rollback();
                            echo json_encode(["mensaje"=>"No se encontró el id del producto $producto.", "status"=>"error"]);
                            exit;
                        }
                    }
                }else{
                    $lote_existia = false;
                    $id_proveedor = consultarProveedor($db, $cod_barra, $cod_interno);
                    if (!empty($id_proveedor) && ($id_proveedor != false)) {
                        $query = "INSERT INTO lotes 
                            (id_proveedor, lote, vencimiento, canje, vencimiento_canje, costo, usuario, fecha)
                            VALUES
                            ($id_proveedor, '$lote', '$fecha_v', 0, '0000-00-00', 0, '$usuario', NOW() )
                        ;";
                        $db->setQuery($query);
                        if (!$db->alter()) {
                            $db->rollback();
                            echo json_encode(["mensaje"=>"No se pudo insertar el lote $lote para el producto $producto. Error: ".$db->getError(), "status"=>"error"]);
                            exit;
                        }
                        $id_lote = $db->getLastID();
                    }else {
                        $product_id = $cod_interno;
                        $add_prod = (object)[
                            "ID" => "$product_id",
                            "codigo" => "$cod_barra",
                            "producto" => "$producto"
                        ];
                        
                        array_push($SIN_PROVEEDORES, $add_prod);
                        $where_codigo = null;                    ;
                    }
                }
                
                $db->setQuery("SELECT id_lote FROM lotes WHERE lote = '$lote'");
                $id_lote = $db->loadObject()->id_lote;
            /* SINO */
            }else {
                $lote_existia = false;
                /* SE GENERA Y SE INSERTAN LOS DATOS */
                $lote = generarLote($db);

                $id_proveedor = consultarProveedor($db, $cod_barra, $cod_interno);
                if (!empty($id_proveedor) && ($id_proveedor != false)) {
                    $query = "INSERT INTO lotes 
                        (id_proveedor, lote, vencimiento, canje, vencimiento_canje, costo, usuario, fecha)
                        VALUES
                        ($id_proveedor, '$lote', '$fecha_v', 0, '0000-00-00', 0, '$usuario', NOW() )
                    ;";
                    $db->setQuery($query);
                    if (!$db->alter()) {
                        $db->rollback();
                        echo json_encode(["mensaje"=>"No se pudo insertar nuevo lote $lote. ".$db->getError(), "status"=>"error"]);
                        exit;
                    }
                    $id_lote = $db->getLastID();
                }else {
                    $product_id = $cod_interno;
                    $add_prod = (object)[
                        "ID" => "$product_id",
                        "codigo" => "$cod_barra",
                        "producto" => "$producto"
                    ];
                    
                    array_push($SIN_PROVEEDORES, $add_prod);
                    $where_codigo = null;                    ;
                }
            }
            /* FINSI */
            
            if (!empty($where_codigo)) {
                $id_p = $cod_interno;
                $db->setQuery("UPDATE productos SET $where_codigo WHERE codigo = '$cod_barra'");
                if (!$db->alter()) {
                    $db->rollback();
                    echo json_encode(["mensaje"=>"No se pudo actualizar el codigo de barras de $cod_barra a $cod_fisico del producto $producto. Error: ".$db->getError(), "status"=>"error"]);
                    exit;
                }
                $cod_barra = $cod_fisico;
            }

            //Se verifica la integridad del lote antes de insertar en stock
            if (empty($const_lote)) {
                if (!empty($id_proveedor) && ($id_proveedor != false)) {
                    $p_id = $cod_interno;
                    if (!empty($p_id)) {
                        $db->setQuery("INSERT INTO stock 
                            (id_producto, id_sucursal, id_lote, stock)
                            VALUES
                            ($p_id, $id_sucursal, $id_lote, $cant)
                        ");
                        if (!$db->alter()) {
                            $db->rollback();
                            echo json_encode(["mensaje"=>"No se pudo insertar stock con el nuevo lote del producto $producto", "status"=>"error"]);
                            exit;
                        }
                    }else{
                        $db->rollback();
                        echo json_encode(["mensaje"=>"No se pudo obtener el id del producto al momento de cargar el stock del lote", "status"=>"error"]);
                        exit;
                    }
                }
            }else {
                if ($lote_existia) {
                    $p_id = $cod_interno;
                    $db->setQuery("UPDATE stock SET stock = '$cant'
                        WHERE id_lote = '$id_lote' AND id_producto = '$p_id' AND id_sucursal = $id_sucursal
                    ;");
                    if (!$db->alter()) {
                        $db->rollback();
                        echo json_encode(["mensaje"=>"No se pudo actualizar stock del producto $producto con lote $lote. Error: ".$db->getError(), "status"=>"error"]);
                        exit;
                    }
                }else {
                    if (!empty($id_proveedor) && ($id_proveedor != false)) {
                        $p_id = $cod_interno;
                        if (!empty($p_id)) {
                            $db->setQuery("INSERT INTO stock 
                                (id_producto, id_sucursal, id_lote, stock)
                                VALUES
                                ($p_id, $id_sucursal, $id_lote, $cant)
                            ");
                            if (!$db->alter()) {
                                $db->rollback();
                                echo json_encode(["mensaje"=>"No se pudo insertar stock del producto $producto con lote $lote. Error: ".$db->getError(), "status"=>"error"]);
                                exit;
                            }
                        }else{
                            $db->rollback();
                            echo json_encode(["mensaje"=>"No se pudo obtener el id del producto al momento de cargar el stock del lote", "status"=>"error"]);
                            exit;
                        }
                    }
                }
            }

            $i++;
        }

        
        $db->commit();
        if ($SIN_PROVEEDORES != []) {
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

            $fileName = "sin_proveedor_principal_".$name_date_now."_".$time."_SUC_$id_sucursal".".xls";
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

            $Spreadsheet->getStyle('A')->getNumberFormat()->setFormatCode('#,##0;-#,##0');
            $Spreadsheet->getStyle('B')->getNumberFormat()->setFormatCode('0');

            //Agrega filtro a encabezados
            $Spreadsheet->setAutoFilter('A2:B3');
            //Selecciona el rango de los filtros hasta la ultima fila
            $Spreadsheet->getAutoFilter()->setRangeToMaxRow();

            header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
            header("Content-Type: application/vnd.ms-excel charset=utf-8");
            $writer->save('php://output');

        }else{
            echo json_encode(["mensaje"=>"DATOS ACTUALIZADOS CORRECTAMENTE", "status"=>"success"]);
        }

    }else {
        echo json_encode(["mensaje"=>"Se necesita el id de la sucursal", "status"=>"error"]);
    }
    

?>