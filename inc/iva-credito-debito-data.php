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
            $anio = intval($db->clearText($_GET['desde']));

            $db->setQuery("SELECT 
            'DEBITO' AS iva,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 1 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS enero,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 2 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS febrero,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 3 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS marzo,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 4 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS abril,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 5 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS mayo,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 6 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS junio,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 7 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS julio,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 8 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS agosto,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 9 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS septiembre,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 10 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS octubre,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 11 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS noviembre,
            (
                SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE MONTH(fecha_venta) = 12 AND YEAR(fecha_venta) = $anio AND estado = 1
            ) AS diciembre,
            (
                (SELECT
                IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                FROM facturas 
                WHERE YEAR(fecha_venta) = $anio)
            ) AS total_debito  -- iva debito 
            
            UNION ALL 
            
            SELECT 
            'CREDITO' AS iva,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 1 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 1 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS enero, 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 2 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 2 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS febrero, 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 3 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 3 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS marzo, 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 4 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 4 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS abril,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 5 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 5 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS mayo,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 6 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 6 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS junio,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 7 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 7 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS julio,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 8 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 8 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS agosto,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 9 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 9 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS septiembre,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 10 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 10 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS octubre,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 11 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 11 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS noviembre,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 12 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 12 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)
            ) AS diciembre,
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)) -- iva credito
            
            UNION ALL 
            
            SELECT 
            'TOTAL' AS iva,
            ( 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 1 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 1 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 1 AND YEAR(fecha_venta) = $anio))
            ) AS enero,
            
            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 2 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 2 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 2 AND YEAR(fecha_venta) = $anio))
            ) AS febrero,
            
            ( 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 3 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 3 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 3 AND YEAR(fecha_venta) = $anio))
            ) AS marzo,
            
            ( 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 4 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 4 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 4 AND YEAR(fecha_venta) = $anio) )
            ) AS abril,
            
            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 5 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 5 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 5 AND YEAR(fecha_venta) = $anio))
            ) AS mayo,
            
            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 6 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 6 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)-
                 
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 6 AND YEAR(fecha_venta) = $anio))
            ) AS junio,
            
            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 7 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 7 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 7 AND YEAR(fecha_venta) = $anio))
            ) AS julio,
            
            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 8 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT 
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 8 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 8 AND YEAR(fecha_venta) = $anio))
            ) AS agosto,
            
            ( 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 9 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 9 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)-
                 
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 9 AND YEAR(fecha_venta) = $anio))
            ) AS septiembre,
            
            ( 
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 10 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 10 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)-
                 
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 10 AND YEAR(fecha_venta) = $anio))
            ) AS octubre,
            
            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 11 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 11 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 11 AND YEAR(fecha_venta) = $anio))
            ) AS noviembre,
            
            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE MONTH(fecha_emision) = 12 AND YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE MONTH(op.fecha) = 12 AND YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)-
                 
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE MONTH(fecha_venta) = 12 AND YEAR(fecha_venta) = $anio))
            ) AS diciembre,

            (
            (
                (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_credito
                    FROM
                    gastos 
                    WHERE YEAR(fecha_emision) = $anio AND estado IN (2,3)) + 
                (SELECT
                    ROUND(IFNULL(SUM(op.monto)/11,0)) AS monto_sueldo
                    FROM orden_pagos op
                    LEFT JOIN funcionarios f ON f.`id_funcionario` = op.`id_funcionario`
                    WHERE YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3) -
                 (SELECT
                    IFNULL(ROUND(SUM(gravada_5)/21) + ROUND(SUM(gravada_10)/11),0) AS iva_debito
                    FROM facturas 
                    WHERE YEAR(fecha_venta) = $anio))
            ) total_debito -- total iva");
        $rows = $db->loadObjectList() ?: [];
        echo json_encode($rows);
        break;
    }