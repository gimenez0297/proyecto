<?php
    include ("inc/funciones.php");
    $db           = DataBase::conectar();
    $usuario      = $auth->getUsername();
    $anio 		  = $db->clearText($_POST["desde"]);

    $fecha_actual = date('d/m/Y h:i');

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
                ) AS total_debito  -- iva debito ");
    $rows_credito = $db->loadObjectList();

    $db->setQuery(" SELECT 
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
                            WHERE YEAR(op.fecha) = $anio AND f.`aporte` = 2 AND op.`estado` = 3)) AS total_credito");
    $rows_debito = $db->loadObjectList();

    $db->setQuery("SELECT 
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
                    ) AS total");
    $rows_total = $db->loadObjectList();

// Logos
$logo_farmacia = "dist/images/logo.png";

// MPDF
require_once __DIR__ . '/mpdf/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'Legal',
    'orientation' => 'L',
    'margin_left' => 5,
    'margin_right' => 5,
    'margin_top' => 50,
    'margin_bottom' => 15,
]);

$mpdf->SetTitle("Iva Crédito y Débito");
$mpdf->SetHTMLHeader('
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70"></td>
                <td class="tc-axmw">IVA Crédito y Débito</td>
                <td class="tc-nrix"><img src="'.$logo_farmacia.'" alt="Image" height="70" style="visibility:hidden"></td>
            </tr>
        </thead>
    </table>
    <hr>
    <br>
    <table class="tc" width="100%">
        <thead>
            <tr>
                <td  style="font-weight:bold;text-align:left;vertical-align:center;" width="10%">Periodo:</td>
                <td width="2%">&nbsp;</td>
            </tr>
            <tr>
                <td  style="border: 1px solid;font-size:14px;text-align:center;">'.$anio.'</td>
                <td width="90%">&nbsp;</td>
            </tr>
        </thead>
    </table>
');

$mpdf->SetHTMLFooter('
     <div style="text-align:right">Pag.: {PAGENO}/{nbpg}</div>
');

// Body
$mpdf->WriteHTML('
<style type="text/css">
    body{font-family:Arial, sans-serif;font-size:14px;}

    /* Tabla cabecera */
    .tc  {border-collapse:collapse;border-spacing:0;}
    .tc td{border-color:black;font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:0px;word-break:normal;}
    .tc th{border-color:black;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;overflow:hidden;padding:0px;word-break:normal;}
    .tc .tc-axmw{font-size:22px;font-weight:bold;text-align:center;vertical-align:middle}
    .tc .tc-lqfj{font-weight:bold;font-size:12px;text-align:center;vertical-align:middle}
    .tc .tc-wa1i{font-size:14px;text-align:left;vertical-align:middle;padding:3px 2px;}
    .tc .tc-nrix{font-size:10px;text-align:center;vertical-align:middle}
    .tc .tc-9q9o{font-size:14px;text-align:center;vertical-align:top}
    .total{font-size:16px;text-align:left;vertical-align:middle;padding:3px 2px;}

    /* Tabla footer */
    .tf  {border-collapse:collapse;border-spacing:0;}
    .tf td{border-color:black;font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:0px;word-break:normal;}
    .tf th{border-color:black;font-family:Arial, sans-serif;font-size:14px;font-weight:normal;overflow:hidden;padding:0px;word-break:normal;}
    .tf .tf-crjr{font-weight:bold;text-align:left;vertical-align:middle;}
    .tf .tf-rt78{font-weight:bold;text-align:right;vertical-align:middle;}

    /* Tabal contenido */
    .tg  {border-collapse:collapse;border-spacing:0;}
    .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:10px;overflow:hidden;padding:5px 5px;word-break:normal;}
    .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:12px;font-weight:normal;overflow:hidden;padding:5px 5px;word-break:normal;}
    .tg .tg-zqap{font-size:12px;font-weight:bold;text-align:center;vertical-align:middle}
    .tg .tg-1x3m{text-align:left;vertical-align:middle; font-size:12px}
    .tg .tg-wjt0{text-align:center;vertical-align:middle}
    .firma{text-align:center; font-weight: bold; margin-top: 100px}
    .total{text-align:center; font-weight: bold; text-align:center;background-color: #b5b5b526;"}
    .aprobado{text-align:center;margin-top: 60px}

    .promedio{text-align:center;font-weight:bold;margin-top: 50px}
    .tabla{vertical-align: top}
    .color{font-weight: bold;background-color: #b5b5b526;"}
    .red{color:red;}
    .negrita{font-weight:bold;}

</style>
');

$mpdf->writehtml('
    <table class="tg" style="width:100%">
        <thead>
            <tr style="background-color: #b5b5b526;">
                <th class="tg-zqap">IVA</th>
                <th class="tg-zqap">Enero</th>
                <th class="tg-zqap">Febrero</th>
                <th class="tg-zqap">Marzo</th>
                <th class="tg-zqap">Abril</th>
                <th class="tg-zqap">Mayo</th>
                <th class="tg-zqap">Junio</th>
                <th class="tg-zqap">Julio</th>
                <th class="tg-zqap">Agosto</th>
                <th class="tg-zqap">Septiembre</th>
                <th class="tg-zqap">Octubre</th>
                <th class="tg-zqap">Noviembre</th>
                <th class="tg-zqap">Diciembre</th>
                <th class="tg-zqap">Total</th>
            </tr>
        </thead>
    <tbody>

');

$coun = 0;
foreach ($rows_credito as $rc) {
    $coun++;

    $mpdf->WriteHTML('
        <tr>
            <td class="tg-1x3m negrita">'.$rc->iva.'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->enero).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->febrero).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->marzo).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->abril).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->mayo).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->junio).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->julio).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->agosto).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->septiembre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->octubre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->noviembre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->diciembre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rc->total_debito).'</td>
        </tr>
    ');
}

foreach ($rows_debito as $rd) {
    $coun++;

    $mpdf->WriteHTML('
        <tr>
            <td class="tg-1x3m negrita">'.$rd->iva.'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->enero).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->febrero).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->marzo).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->abril).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->mayo).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->junio).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->julio).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->agosto).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->septiembre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->octubre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->noviembre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->diciembre).'</td>
            <td class="tg-1x3m" style="text-align:right">'.separadorMiles($rd->total_credito).'</td>
        </tr>
    ');
}

foreach ($rows_total as $rt) {
    $coun++;

    if ($rt->enero < 0) {$red_e = 'red';}else{$red_e = '';}
    if ($rt->febrero < 0) {$red_f = 'red';}else{$red_f = '';}
    if ($rt->marzo < 0) {$red_m = 'red';}else{$red_m = '';}
    if ($rt->abril < 0) {$red_a = 'red';}else{$red_a = '';}
    if ($rt->mayo < 0) {$red_my = 'red';}else{$red_my = '';}
    if ($rt->junio < 0) {$red_jn = 'red';}else{$red_jn = '';}
    if ($rt->julio < 0) {$red_jl = 'red';}else{$red_jl = '';}
    if ($rt->agosto < 0) {$red_ag = 'red';}else{$red_ag = '';}
    if ($rt->septiembre < 0) {$red_s = 'red';}else{$red_s = '';}
    if ($rt->octubre < 0) {$red_o = 'red';}else{$red_o = '';}
    if ($rt->noviembre < 0) {$red_n = 'red';}else{$red_n = '';}
    if ($rt->diciembre < 0) {$red_d = 'red';}else{$red_d = '';}
    if ($rt->total < 0) {$red_t = 'red';}else{$red_t = '';}


    $mpdf->WriteHTML('
    
        <tr>
            <td class="tg-1x3m color">'.$rt->iva.'</td>
            <td class="tg-1x3m color '.$red_e.'" style="text-align:right">'.separadorMiles($rt->enero).'</td>
            <td class="tg-1x3m color '.$red_f.'" style="text-align:right">'.separadorMiles($rt->febrero).'</td>
            <td class="tg-1x3 color '.$red_m.'" style="text-align:right">'.separadorMiles($rt->marzo).'</td>
            <td class="tg-1x3m color '.$red_a.'" style="text-align:right">'.separadorMiles($rt->abril).'</td>
            <td class="tg-1x3m color '.$red_my.'" style="text-align:right">'.separadorMiles($rt->mayo).'</td>
            <td class="tg-1x3m color '.$red_jn.'" style="text-align:right">'.separadorMiles($rt->junio).'</td>
            <td class="tg-1x3m color '.$red_jl.'" style="text-align:right">'.separadorMiles($rt->julio).'</td>
            <td class="tg-1x3m color '.$red_ag.'" style="text-align:right">'.separadorMiles($rt->agosto).'</td>
            <td class="tg-1x3m color '.$red_s.'" style="text-align:right">'.separadorMiles($rt->septiembre).'</td>
            <td class="tg-1x3m color '.$red_o.'" style="text-align:right">'.separadorMiles($rt->octubre).'</td>
            <td class="tg-1x3m color '.$red_n.'" style="text-align:right">'.separadorMiles($rt->noviembre).'</td>
            <td class="tg-1x3m color '.$red_d.'" style="text-align:right">'.separadorMiles($rt->diciembre).'</td>
            <td class="tg-1x3m color '.$red_t.'" style="text-align:right">'.separadorMiles($rt->total).'</td>
        </tr>
    ');
}

$mpdf->WriteHTML('
        </tbody>
    </table>
');



$mpdf->Output("IVACreditoDebito$anio.pdf", 'I');