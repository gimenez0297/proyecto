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
        case 'ver':
            $db = DataBase::conectar();
            // $estado = $db->clearText($_GET['estado']);
            $sucursal = $db->clearText($_REQUEST['sucursal']);
            // $where = "";

            // if (intval($estado) > 0 || $estado == '0') {
            //     $where = "AND estado=$estado";
            // }

            if(!empty($sucursal) && intVal($sucursal) != 0) {
                $where_sucursal .= " AND cc.id_sucursal='$sucursal'";
            }else{
                $where_sucursal .="";
            };

            // Parametros de ordenamiento, busqueda y paginacion
            // $desde = $db->clearText($_REQUEST['desde'])." 00:00:00";
            // $hasta = $db->clearText($_REQUEST['hasta'])." 23:59:59";
            // $sucursal = $db->clearText($_REQUEST['sucursal']);
            $search = $db->clearText($_REQUEST['search']);
            $limit = ($db->clearText($_REQUEST['limit'])) ?: 9;
            $offset	= ($db->clearText($_REQUEST['offset'])) ?: 0;
            $order = $db->clearText($_REQUEST['order']);
            $sort = ($db->clearText($_REQUEST['sort'])) ?: 2;
            if (isset($search) && !empty($search)) {
                $having = "HAVING CONCAT_WS(' ', cod_movimiento, saldo, sobrante, fecha_apertura, fecha_rendicion, usuario_apertura,usuario_rendicion,estado_str,sucursal) LIKE '%$search%'";
            }

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                ccs.`id_caja_chica_sucursal`,
                                ccs.`cod_movimiento`,
                                ccs.`saldo`,
                                ccs.`sobrante`,
                                DATE_FORMAT(ccs.`fecha_apertura`,'%d/%m/%Y %H:%i') AS fecha_apertura,
                                IFNULL(DATE_FORMAT(ccs.`fecha_rendicion`, '%d/%m/%Y %H:%i'),'S/F') AS fecha_rendicion, 
                                ccs.`usuario_apertura`,
                                IFNULL(ccs.`usuario_rendicion`,'S/U') AS usuario_rendicion,
                                CASE ccs.`estado` WHEN 1 THEN 'Abierto' WHEN 2 THEN 'Rendido' END AS estado_str,
                                s.sucursal,
                                ccs.estado,
                                ccd.estado AS estado_deposito,
                                ccd.id_caja_chica_deposito
                            FROM
                                caja_chica_sucursal ccs
                            LEFT JOIN caja_chica cc ON ccs.id_caja_chica = cc.id_caja_chica
                            LEFT JOIN sucursales s ON s.id_sucursal = cc.id_sucursal
                            LEFT JOIN caja_chica_depositos ccd ON ccd.id_caja_chica_sucursal = ccs.id_caja_chica_sucursal
                            WHERE ccs.estado = 2 $where_sucursal
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

        case 'ver_detalles':
            $db = DataBase::conectar();
            $id_caja = $db->clearText($_GET['id_caja']);
            $db->setQuery("SELECT
                                ccf.`id_caja_chica_facturas`,
                                g.`nro_gasto`,
                                DATE_FORMAT(g.`fecha_emision`, '%d/%m/%Y') AS fecha_emision,
                                CASE
                                WHEN g.deducible = 1
                                THEN g.ruc
                                WHEN g.deducible = 2
                                THEN 'S/R'
                                END AS ruc,
                                CASE
                                WHEN g.deducible = 1
                                THEN g.razon_social
                                WHEN g.deducible = 2
                                THEN 'S/N'
                                END AS razon_social,
                                CASE
                                WHEN g.deducible = 1
                                THEN g.timbrado
                                WHEN g.deducible = 2
                                THEN 'S/T'
                                END AS timbrado,
                                CASE
                                WHEN g.deducible = 1
                                THEN g.documento
                                WHEN g.deducible = 2
                                THEN 'S/NRO'
                                END AS documento,
                                g.concepto,
                                g.monto,
                                CASE g.deducible WHEN 1 THEN 'DEDUCIBLE' WHEN 2 THEN 'NO DEDUCIBLE' END AS tipo_gasto,
                                s.sucursal
                            FROM
                                caja_chica_facturas ccf
                            LEFT JOIN gastos g ON g.`id_gasto` = ccf.`id_gasto`
                            LEFT JOIN caja_chica_sucursal ccs ON ccs.id_caja_chica_sucursal = ccf.id_caja_chica_sucursal 
                            LEFT JOIN caja_chica cc ON cc.id_caja_chica = ccs.id_caja_chica
                            LEFT JOIN sucursales s ON s.id_sucursal = cc.id_sucursal
                            WHERE ccf.id_caja_chica_sucursal = $id_caja");
            $rows = ($db->loadObjectList()) ?: [];
            echo json_encode($rows);
        break;

        case 'cargar-comprobante':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $id_banco = $db->clearText($_POST['id_banco']);
            $id_cuenta = $db->clearText($_POST['id_cuenta']);
            $fecha_deposito = $db->clearText($_POST['fecha_deposito']);
            $nro_comprobante = $db->clearText($_POST['nro_comprobante']);

            if (empty($id_banco)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione un Banco."]);
                exit;
            }
            if (empty($fecha_deposito)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione una Fecha del Deposito."]);
                exit;
            }
            if (empty($id_cuenta)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor seleccione el Número de Cuenta"]);
                exit;
            }
            if (empty($nro_comprobante)) {
                echo json_encode(["status" => "error", "mensaje" => "Favor completé el Número de Comprobante"]);
                exit;
            }

            //Buscamos los datos de la caja chica.
            $db->setQuery("SELECT * FROM caja_chica_sucursal WHERE id_caja_chica_sucursal = $id");
            $row = $db->loadObject();
            $movimiento = $row->cod_movimiento;
            $sobrante = $row->sobrante;

            //Buscamos el libro diario activo actualmente
            $db->setQuery("SELECT id_libro_diario_periodo, estado FROM libro_diario_periodo WHERE estado = 1");
            $id_libro_diario_periodo = $db->loadObject()->id_libro_diario_periodo;

            //Obtenemos el nro de asiento
            $db->setQuery("SELECT nro_asiento FROM libro_diario ORDER BY nro_asiento DESC LIMIT 1");
            $nro_asiento = $db->loadObject()->nro_asiento;

            if (empty($nro_asiento)) {
                $nro = zerofillAsiento(1);
            } else {
                $nro = zerofillAsiento(intval($nro_asiento) + 1);
            }

            //Se carga la cabezera del asiento
            $db->setQuery("INSERT INTO `libro_diario` (`id_libro_diario_periodo`,`id_comprobante`,`fecha`,`nro_asiento`,`importe`,`descripcion`,`usuario`,`fecha_creacion`)
            VALUES($id_libro_diario_periodo,1,NOW(),'$nro',$sobrante,'DEPOSITO DE SOBRANTE DE CAJA CHICA N° $movimiento','$usuario',NOW());"); //El usuario que se registra como el que realizo el asiento es el usuario que realiza la carga del gasto.  
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del ingreso."]);
                $db->rollback();  //Revertimos los cambios
                exit;
            }

            $id_asiento = $db->getLastID();

            if ($id_banco == 1) {//BANCO ITAU PARAGUAY SA.
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,12,'DEPOSITO DE SOBRANTE DE CAJA CHICA N° $movimiento',$sobrante,0);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }elseif ($id_banco == 3) {//BANCO FAMILIAR.
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,293,'DEPOSITO DE SOBRANTE DE CAJA CHICA N° $movimiento',$sobrante,0);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }elseif ($id_banco == 4) {//BANCO NACIONAL DE FOMENTO.
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,10,'DEPOSITO DE SOBRANTE DE CAJA CHICA N° $movimiento',$sobrante,0);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }elseif ($id_banco == 5) {//BANCO REGIONAL.
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,11,'DEPOSITO DE SOBRANTE DE CAJA CHICA N° $movimiento',$sobrante,0);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }elseif ($id_banco == 6) {//BANCO ITAU CAJA DE AHORRO.
                $db->setQuery("INSERT INTO `libro_diario_detalles` (`id_libro_diario`,`id_libro_cuenta`,`concepto`,`debe`,`haber`)
                VALUES($id_asiento,292,'DEPOSITO DE SOBRANTE DE CAJA CHICA N° $movimiento',$sobrante,0);");
                if (!$db->alter()) {
                    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el asiento contable del gasto."]);
                    $db->rollback();  //Revertimos los cambios
                    exit;
                }
            }

            $db->setQuery("INSERT INTO `caja_chica_depositos` (`id_caja_chica_sucursal`,`id_banco`,`id_cuenta`,`fecha_deposito`,`nro_comprobante`,`fecha_registro`,`usuario`,estado)
              VALUES($id,$id_banco,$id_cuenta,'$fecha_deposito','$nro_comprobante',NOW(),'$usuario',2);");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al registrar el comprobante de depósito. Code: ".$db->getErrorCode()]);
                exit;
            }

            $id_caja_chica_comprobante = $db->getLastID();

            echo json_encode(["status" => "ok", "mensaje" => "Comprobante de Depósito guardado correctamente.", "id_comprobante" => $id_caja_chica_comprobante]);
        break;

       

        
    }