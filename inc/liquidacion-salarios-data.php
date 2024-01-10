<?php
    include ("funciones.php");
    if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

    $q = $_REQUEST['q'];
    $usuario = $auth->getUsername();
    $id_sucursal = datosUsuario($usuario)->id_sucursal;

    switch ($q) {

        case 'cargar':
            $db = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $funcionario = mb_convert_case($db->clearText($_POST['funcionario']), MB_CASE_UPPER, "UTF-8");
            $ci = $db->clearText($_POST['ci']);
            $periodo = $db->clearText($_POST['periodo']);
            $neto = quitaSeparadorMiles($db->clearText($_POST['neto']));
            $ingresos = json_decode($_POST['ingresos'], true);
            $descuentos = json_decode($_POST['descuentos'],true);
            $forma = $db->clearText($_POST['forma']);
            $cheque = $db->clearText($_POST['cheque']);
            $cuenta = $db->clearText($_POST['cuenta']);
            
            if (empty($id_funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un funcionario"]);
                exit;
            }
            if (empty($funcionario)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor complete el nombre del funcionario"]);
                exit;
            }
            if (empty($ingresos)) {
                echo json_encode(["status" => "error", "mensaje" => "Ningún ingreso agregado. Favor verifique."]);
                exit;
            }

            $query = "SELECT * 
                        FROM liquidacion_salarios 
                        WHERE id_funcionario='$id_funcionario' 
                            and periodo= '$periodo' 
                            and estado IN (0,1)";
                                    
            $db->setQuery($query);
            $rows = $db->loadObjectList();  
            
            if (!empty($rows)){
                echo json_encode(["status" => "error", "mensaje" => "El funcionario ya se encuentra en el periodo '$periodo'"]);
                exit;
            }

            // Cabeceera
            $db->setQuery("INSERT INTO liquidacion_salarios (
                                fecha, 
                                funcionario, 
                                id_funcionario, 
                                ci, 
                                neto_cobrar, 
                                usuario, 
                                periodo, 
                                forma_pago, 
                                nro_cheque, 
                                nro_cuenta)
                            VALUES (
                                NOW(), 
                                '$funcionario', 
                                '$id_funcionario', 
                                '$ci',
                                '$neto',
                                '$usuario',
                                '$periodo',
                                '$forma',
                                '$cheque',
                                '$cuenta')");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                exit;
            }

            $id_liquidacion = $db->getLastID();

            foreach ($ingresos as $p) {
                $concepto = $db->clearText($p["concepto"]);
                $importe_ing = $db->clearText(quitaSeparadorMiles($p["importe"]));
                $obs_ing = $db->clearText($p["observacion"]);

                $db->setQuery("INSERT INTO liquidacion_salarios_ingresos (id_liquidacion, concepto, importe, observacion)
                                VALUES ('$id_liquidacion','$concepto','$importe_ing','$obs_ing')");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }
            }

            foreach ($descuentos as $d) {
                $concepto_des = $db->clearText($d["concepto"]);
                $importe_des = $db->clearText(quitaSeparadorMiles($d["importe"]));
                $obs_des = $db->clearText($d["observacion"]);

                $db->setQuery("INSERT INTO liquidacion_salarios_descuentos (id_liquidacion, fecha, concepto, importe, observacion)
                                VALUES ('$id_liquidacion', NOW(),'$concepto_des','$importe_des','$obs_des')");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error. ".$db->getError()]);
                    exit;
                }
            }

            echo json_encode(["status" => "ok", "mensaje" => "Liquidación de salario registrada correctamente", "id_liquidacion" => $id_liquidacion]);
            
        break;

        case 'verificar_periodo_funcionario':
            $db       = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $periodo   = $db->clearText($_POST['periodo']);
            $mes   = $db->clearText($_POST['mes_']);
            $anho   = $db->clearText($_POST['anho']);

            $fecha_periodo_desde = $anho.'-'.$mes.'-01';
            $fecha_periodo_hasta = $anho.'-'.$mes.'-28';

            if($id_funcionario == ''){
                echo json_encode(["status" => "error", "mensaje" => "Debe seleccionar el funcionario."]);
                exit;
            }else{
                $db->setQuery("SELECT
                                *
                            FROM
                                liquidacion_salarios
                            WHERE id_funcionario='$id_funcionario' and periodo= '$periodo' and estado IN (0,1)");
                if (!empty($db->loadObject())) {
                    echo json_encode(["status" => "error", "mensaje" => "El funcionario ya cuenta con una liquidación del periodo '$periodo'"]);
                    exit;
                }

                $db->setQuery("SELECT * FROM (
                                                SELECT 
                                                    f.id_funcionario AS id_,  
                                                    'I.P.S 9%' AS concepto,
                                                    CASE 
                                                    
                                                        WHEN fecha_alta != '0000-00-00' AND fecha_baja = '0000-00-00' AND MONTH(fecha_alta) = $mes AND YEAR(fecha_alta) = $anho THEN ROUND(((f.salario_real / (SELECT COUNT(fecha_mysql) AS cantidad 
                                                                        FROM calendario WHERE MONTH(fecha_mysql) = MONTH(f.fecha_alta) 
                                                                        AND YEAR(fecha_mysql) = YEAR(f.fecha_alta) 
                                                                        )) * (SELECT COUNT(fecha_mysql) AS cantidad 
                                                                        FROM calendario WHERE fecha_mysql >= f.fecha_alta 
                                                                        AND MONTH(fecha_mysql) = MONTH(f.fecha_alta) 
                                                                        AND YEAR(fecha_mysql) = YEAR(f.fecha_alta) 
                                                                        )) * 0.09)
                                                        WHEN fecha_baja != '0000-00-00' AND MONTH(fecha_baja) = $mes AND YEAR(fecha_baja) = $anho THEN ROUND(((f.salario_real / (SELECT COUNT(fecha_mysql) AS cantidad 
                                                                        FROM calendario WHERE MONTH(fecha_mysql) = MONTH(f.fecha_baja) 
                                                                        AND YEAR(fecha_mysql) = YEAR(f.fecha_baja) 
                                                                        )) * (SELECT COUNT(fecha_mysql) AS cantidad 
                                                                        FROM calendario WHERE fecha_mysql <= f.fecha_baja 
                                                                        AND MONTH(fecha_mysql) = MONTH(f.fecha_baja) 
                                                                        AND YEAR(fecha_mysql) = YEAR(f.fecha_baja) 
                                                                        )) * 0.09 )
                                                        ELSE ROUND(((f.salario_real / 30) * 30) *0.09)
                    
                                                    END AS anticipo_mes,
                                                    fecha_alta,
                                                    '' AS observacion,
                                                     DATE_FORMAT(fecha_alta, '%d/%m/%Y') AS fecha_formato   
                                                FROM funcionarios f
                                                WHERE f.id_funcionario = $id_funcionario AND f.aporte = 1

                                                UNION ALL

                                                SELECT
                                                     id_anticipo AS id_,
                                                     'ANTICIPO' AS concepto,
                                                     a.monto AS anticipo_mes, 
                                                     a.fecha,
                                                     a.observacion,
                                                     DATE_FORMAT(a.fecha, '%d/%m/%Y') AS fecha_formato
                                                FROM anticipos a 
                                                WHERE fecha BETWEEN '$fecha_periodo_desde' AND '$fecha_periodo_hasta' AND id_funcionario = '$id_funcionario' AND estado = 1
                                                UNION ALL
                                                SELECT
                                                     id_prestamo AS id_,
                                                     'PRESTAMO' AS concepto,
                                                     ROUND(a.monto/cantidad_cuota) AS anticipo_mes, 
                                                     a.fecha,
                                                     a.observacion,
                                                     DATE_FORMAT(a.fecha, '%d/%m/%Y') AS fecha_formato
                                                FROM prestamos a 
                                                WHERE fecha BETWEEN '$fecha_periodo_desde' AND '$fecha_periodo_hasta' AND id_funcionario = '$id_funcionario' AND estado = 1
                                                UNION ALL
                                                SELECT
                                                     id_descuento AS id_,
                                                     descuento AS concepto,
                                                     a.monto AS anticipo_mes, 
                                                     a.fecha,
                                                     a.observacion,
                                                     DATE_FORMAT(a.fecha, '%d/%m/%Y') AS fecha_formato 
                                                FROM descuentos_funcionarios a 
                                                WHERE fecha BETWEEN '$fecha_periodo_desde' AND '$fecha_periodo_hasta' AND id_funcionario = '$id_funcionario' AND estado = 1

                                                UNION ALL

                                                SELECT 
                                                    CONCAT(f.id_funcionario, rep.id_reposo) AS id_,  
                                                    'REPOSO DE I.P.S' AS concepto,
                                                    -- (salario real / dias del mes) * dias NO trabajados
                                                    ROUND((f.salario_real / DAY(LAST_DAY(rep.fecha_desde))) * DATEDIFF(rep.fecha_hasta, rep.fecha_desde)) AS  anticipo_mes, -- importe
                                                    DATE_FORMAT(rep.fecha_desde, '%d/%m/%Y') AS fecha_desde,
                                                    CASE WHEN (rep.observacion = '' OR rep.observacion IS NULL) THEN
                                                        CONCAT('DE ', IFNULL(DATE_FORMAT(rep.fecha_desde, '%d/%m/%Y'), 'NO DEFINIDO'), ' A ', IFNULL(DATE_FORMAT(rep.fecha_hasta, '%d/%m/%Y'), 'NO DEFINIDO'))
                                                    ELSE
                                                        CONCAT(rep.observacion, ' (', DATEDIFF(rep.fecha_hasta, rep.fecha_desde), ' Dias)')
                                                    END AS observacion, -- observacion  
                                                    DATE_FORMAT(rep.fecha_hasta, '%d/%m/%Y') AS fecha_hasta
                                                FROM reposos rep 
                                                LEFT JOIN funcionarios f ON f.id_funcionario = rep.id_funcionario
                                                WHERE (rep.fecha_desde BETWEEN '$fecha_periodo_desde' AND '$fecha_periodo_hasta') AND f.id_funcionario = $id_funcionario AND f.aporte = 1 AND rep.estado = 1



                                                ) AS descuentos");

                echo json_encode(["status" => "ok", "mensaje" => "", "data" => $db->loadObjectList()]);
            }

        break;  

        case 'consultar_descuentos':
            $db       = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $periodo   = $db->clearText($_POST['periodo']);

            if($id_funcionario == ''){
                echo json_encode(["status" => "error", "mensaje" => "Debe seleccionar el funcionario."]);
                exit;
            }else{
                $db->setQuery("SELECT
                                *
                            FROM
                                liquidacion_salarios
                            WHERE id_funcionario='$id_funcionario' and periodo= '$periodo' and estado IN (0,1)");
                if (!empty($db->loadObject())) {
                    echo json_encode(["status" => "error", "mensaje" => "El funcionario ya cuenta con una liquidación del periodo '$periodo'"]);
                } else{
                   echo json_encode(["status" => "ok"]);
                } 
            }

        break;  

        case 'verificar_comision_funcionario':
            $db       = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $periodo   = $db->clearText($_POST['periodo']);
            $mes   = $db->clearText($_POST['mes_']);

            $db->setQuery("SELECT IFNULL(utilidad,0) AS utilidad FROM configuracion");
            $utilidad = $db->loadObject()->utilidad;

            $db->setQuery("SELECT 
                                fu.id_funcionario,
                                'COMISIÓN' AS concepto,
                                SUM(ROUND((fp.total_venta*IFNULL(fp.comision,0))/100)) AS total,
                                fu.`fecha_alta` AS fecha,
                                '' AS observacion
                            FROM facturas_productos fp
                            JOIN facturas f ON fp.id_factura=f.id_factura
                            JOIN users u ON f.usuario=u.username
                            JOIN funcionarios fu ON u.id=fu.id_usuario 
                            WHERE f.`estado`!=2 AND fu.id_funcionario=$id_funcionario AND CONCAT(UCASE(mes(f.fecha_venta,'es_ES')),' - ',YEAR(f.fecha_venta)) ='$periodo' AND ROUND((IFNULL((
                                    SELECT SUM(total_venta) - SUM(total_costo) 
                                    FROM facturas 
                                    WHERE id_factura = f.id_factura
                                ), 0) / f.total_venta) * 100, 2) >= '$utilidad'
                            ");

                echo json_encode(["status" => "ok", "mensaje" => "", "data" => $db->loadObjectList()]);
        break; 	

        case 'verificar_normal_funcionario':
            $db       = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $periodo   = $db->clearText($_POST['periodo']);
            $mes   = $db->clearText($_POST['mes_']);
            $anho   = $db->clearText($_POST['anho']);

            $db->setQuery("SELECT fecha_baja FROM funcionarios WHERE id_funcionario = $id_funcionario");
            $fecha = $db->loadObject()->fecha_baja;

            $e_fecha=strtotime($fecha);
            $e_fecha_str=strtotime('01-'.$mes.'-'.$anho);

            if ($e_fecha_str > $e_fecha && $e_fecha > 0) {
                echo json_encode(["status" => "ok", "mensaje" => "", "data" => []]);
            }

            $db->setQuery("SELECT 
                                f.id_funcionario,  
                                'NORMAL' AS concepto,
                                CASE 
                              
                                WHEN fecha_alta != '0000-00-00' AND fecha_baja = '0000-00-00' AND MONTH(fecha_alta) = $mes AND YEAR(fecha_alta) = $anho THEN ROUND(((f.salario_real / (SELECT COUNT(fecha_mysql) AS cantidad 
                                                FROM calendario WHERE MONTH(fecha_mysql) = MONTH(f.fecha_alta) 
                                                AND YEAR(fecha_mysql) = YEAR(f.fecha_alta) 
                                                )) * (SELECT COUNT(fecha_mysql) AS cantidad 
                                                FROM calendario WHERE fecha_mysql >= f.fecha_alta 
                                                AND MONTH(fecha_mysql) = MONTH(f.fecha_alta) 
                                                AND YEAR(fecha_mysql) = YEAR(f.fecha_alta) 
                                                )))
                                WHEN fecha_baja != '0000-00-00' AND MONTH(fecha_baja) = $mes AND YEAR(fecha_baja) = $anho THEN ROUND(((f.salario_real / (SELECT COUNT(fecha_mysql) AS cantidad 
                                                FROM calendario WHERE MONTH(fecha_mysql) = MONTH(f.fecha_baja) 
                                                AND YEAR(fecha_mysql) = YEAR(f.fecha_baja) 
                                                )) * (SELECT COUNT(fecha_mysql) AS cantidad 
                                                FROM calendario WHERE fecha_mysql <= f.fecha_baja 
                                                AND MONTH(fecha_mysql) = MONTH(f.fecha_baja) 
                                                AND YEAR(fecha_mysql) = YEAR(f.fecha_baja) 
                                                )))
                                ELSE f.salario_real
                                END AS total,
                                fecha_alta,
                                '' AS observacion,
                                 DATE_FORMAT(fecha_alta, '%d/%m/%Y') AS fecha_formato   
                            FROM funcionarios f
                            WHERE f.id_funcionario = $id_funcionario
                            ");

                echo json_encode(["status" => "ok", "mensaje" => "", "data" => $db->loadObjectList()]);
        break; 

        case 'verificar_extra_funcionario':
            $db       = DataBase::conectar();
            $id_funcionario = $db->clearText($_POST['id_funcionario']);
            $periodo   = $db->clearText($_POST['periodo']);

            $db->setQuery("SELECT *, 
                                SUM(extra) AS extra_total,
                                CONCAT(SUM(extra),' ','hs') AS observacion,
                                ROUND(((salario_real/30)/8)*SUM(extra)) AS monto_extra
                            FROM (SELECT
                                    DATE_FORMAT(a.fecha,'%d/%m/%Y') AS fecha_asist,
                                    a.funcionario,
                                    f.salario_real,
                                    FORMAT(a.normal/60, 2) AS normal,
                                    FORMAT(SUM(a.total_trabajo/60), 0,'de_DE') AS trabajo,
                                    /*FORMAT(a.extra/60, 2) AS extra,*/
                                    IF(FORMAT(SUM(a.total_trabajo/60), 0,'de_DE') - FORMAT(a.normal/60, 2)< 0 ,0,FORMAT(SUM(a.total_trabajo/60), 0,'de_DE') - FORMAT(a.normal/60, 2))  AS extra,
                                    salida
                                FROM asistencias a 
                                LEFT JOIN funcionarios f ON a.id_funcionario = f.id_funcionario
                                WHERE CONCAT(UCASE(mes(a.fecha,'es_ES')),' - ',YEAR(a.fecha)) ='$periodo' AND a.id_funcionario=$id_funcionario
                                GROUP BY fecha_asist, a.ci)a"
                                            );

                echo json_encode(["status" => "ok", "mensaje" => "", "data" => $db->loadObject()]);

        break; 

	}

?>
