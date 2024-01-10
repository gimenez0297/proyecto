<?php
    
    require_once __DIR__ . '/PhpSpreadsheet/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
    use PhpOffice\PhpSpreadsheet\Reader\Xls;

    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;


    switch ($q) {
        
        case 'ver':
            $db = DataBase::conectar();
            $where = "";
            $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";

            //Parametros de ordenamiento, busqueda y paginacion
            $search = $db->clearText($_REQUEST['search']);
            $limit = $db->clearText($_REQUEST['limit']);
            $offset = $db->clearText($_REQUEST['offset']);
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING ci LIKE '$search%' OR funcionario LIKE '$search%' OR fecha LIKE '$search%' OR dia LIKE '$search%'";
            }
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                id_asistencia,
                                f.id_funcionario,
                                /*IF(a.total_trabajo > 540,SUM(a.total_trabajo)-540,0)AS h,*/
                                DATE(a.fecha) AS fecha,
                                DATE_FORMAT(a.fecha,'%d/%m/%Y') AS fecha_asist,
                                f.funcionario,
                                f.ci,
                                dia,
                                llegada,
                                FORMAT(a.normal/60, 2) AS normal,
                                FORMAT(SUM(a.total_trabajo/60), 0,'de_DE') AS trabajo,
                                /*FORMAT(a.extra/60, 2) AS extra,*/
                                IF(FORMAT(SUM(a.total_trabajo/60), 0,'de_DE') - FORMAT(a.normal/60, 2)< 0 ,0,FORMAT(SUM(a.total_trabajo/60), 0,'de_DE') - FORMAT(a.normal/60, 2))  AS extra,
                                Max(salida) as salida,
                                a.usuario,
                                a.observacion
                            FROM asistencias a 
                            LEFT JOIN funcionarios f ON a.ci = f.ci
                            WHERE a.fecha BETWEEN '$desde' AND '$hasta'
                            GROUP BY fecha_asist, f.ci
                            $having
                            ORDER BY $sort $order
                            LIMIT $offset, $limit");
            $rows = $db->loadObjectList();

            $db->setQuery("SELECT FOUND_ROWS() as total");      
            $total_row = $db->loadObject();
            $total = $total_row->total;

            if ($rows) {
                $salida = array('total' => $total, 'rows' => $rows);
            } else {
                $salida = array('total' => 0, 'rows' => array());
            }

            echo json_encode($salida);
        break;
        
        case 'cambiar-estado':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $estado = $db->clearText($_POST['estado']);

            $db->setQuery("UPDATE asistencias SET estado=$estado WHERE id_asistencia=$id");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

        case 'procesar':
            $db = DataBase::conectar();

            $documento       = $_FILES['documento'];

            $testAgainstFormats = [
                \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS,
                \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
            ];

            try {
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($_FILES['documento']['tmp_name'], $testAgainstFormats);

            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                echo json_encode(["status" => "error", "mensaje" => "El tipo de archivo no es válido o esta dañado"]);
                exit;
            }

            if($inputFileType == 'Xls') {
                $reader = new Xls();
            } else {
                $reader = new Xlsx();
            }

            $spreadsheet = $reader->load($_FILES['documento']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            if (!empty($sheetData)) {

                foreach ($sheetData as $key => $value) {
                    $dia = $value[7];
                    $fecha = $value[6];
                    $id_funcionario = $value[1];
                    $ci = $value[1];
                    $nombre = $value[2];
                    $llegada = $value[9];
                    $salida = $value[10];
                    $normal = $value[11];
                    $extra = $value[12];
                    $total = $value[13];

                    if($dia == 'Lun.'){
                        $dia_str = 'Lunes';
                    }elseif ($dia == 'Mar.') {
                        $dia_str = 'Martes';
                    }elseif ($dia == 'Mie.') {
                        $dia_str = 'Miercoles';
                    }elseif ($dia == 'Jue.') {
                        $dia_str = 'Jueves';
                    }elseif ($dia == 'Fri.') {
                        $dia_str = 'Viernes';
                    }elseif ($dia == 'Sab.') {
                        $dia_str = 'Sabado';
                    }elseif ($dia == 'Domingo') {
                        $dia_str = 'Domingo';
                    }

                    list($year, $month, $day) = explode("-", $fecha);

                    if(!checkdate($month, $day, $year) || empty($id_funcionario)){
                        continue;
                    }

                     $db->query("INSERT INTO asistencias (fecha_exportacion, fecha, id_funcionario, ci, funcionario, dia, llegada, salida,total_trabajo, normal, extra, usuario) VALUES(NOW(), '$fecha', '$id_funcionario', '$ci', '$nombre', '$dia_str', '$llegada', '$salida', '$total', '$normal', '$extra', '$usuario')");
                     if (!$db->alter()) {
                        echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
                        exit;
                    }
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Asistencia registrado correctamente"]);
            break;

        case 'cargar':
            $db = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $fecha = $db->clearText($_POST['fecha']);
            $llegada = $db->clearText($_POST['llegada']);
            $salida = $db->clearText($_POST['salida']);
            $normal = $db->clearText($_POST['normal']);
            $extra = $db->clearText($_POST['extra']);
            $observacion =  mb_convert_case($db->clearText($_POST['observacion']), MB_CASE_UPPER, "UTF-8");

            if (empty($fecha)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una fecha"]);
                exit;
            }
            if (empty($llegada)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una Hora de Llegada"]);
                exit;
            }
            if (empty($salida)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una Hora de Salida"]);
                exit;
            }

            $dia = diaEspanol($fecha);

            $horas_trabajadas = explode(":", $normal);
            $hora_a_minutos =$horas_trabajadas[0] * 60;
            $normal_fin= $hora_a_minutos + $horas_trabajadas[1];

            $horas_extras = explode(":", $extra);
            $hora_extras_a_minutos =$horas_extras[0] * 60;
            $extra_fin= $hora_extras_a_minutos + $horas_extras[1];

            $db->setQuery("SELECT * FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $row = $db->loadObject();

            $funcionario = $row->funcionario;
            $ci = $row->ci;

            $db->setQuery("INSERT INTO asistencias (
                fecha,
                id_funcionario,
                ci,
                funcionario,
                dia,
                llegada,
                salida,
                total_trabajo,
                normal,
                extra,
                usuario,
                observacion
              )
            VALUES
              (
                '$fecha',
                $id_funcionario,
                '$ci',
                '$funcionario',
                '$dia',
                '$llegada',
                '$salida',
                '$normal_fin',
                540,
                '$extra_fin',
                '$usuario',
                '$observacion'
                )");

            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            echo json_encode(["status" => "ok", "mensaje" => "Asistencia Cargado correctamente"]);
        break;

        case 'horarios':
            $db          = DataBase::conectar();
            $ci = $db->clearText($_GET['ci']);
            $fecha = $db->clearText($_GET['fecha']);

            $db->setQuery("SELECT * 
                            FROM asistencias 
                            WHERE ci='$ci' AND fecha='$fecha'");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;
        }
        

?>
