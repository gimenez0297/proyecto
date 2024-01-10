<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$sucursal_usuario = datosUsuario($usuario)->id_sucursal;
$tipo_puesto_cajero = TIPO_PUESTO_CAJERO;
$id_rol = datosUsuario($usuario)->id_rol;

switch ($q) {
    case 'ver':
        $db = DataBase::conectar();

        $fecha_actual = date('Y-m-d');
        $primer_dia = date('Y-m-01 00:00:00', strtotime($fecha_actual));
        $ultimo_dia = date('Y-m-t 23:59:59', strtotime($fecha_actual));

        /* FILTRO VENDEDORES */
            $desde                      = $db->clearText($_REQUEST['desde']) ? $db->clearText($_REQUEST['desde']).' 00:00:00' : "$primer_dia";
            $hasta                      = $db->clearText($_REQUEST['hasta']) ? $db->clearText($_REQUEST['hasta']).' 23:59:59' : "$ultimo_dia";
            
            $id_sucursal                = $db->clearText($_REQUEST["sucursal"]);
            $proveedor_principal        = $db->clearText($_REQUEST['proveedor']);
            $id_vendedor                = $db->clearText($_REQUEST["vendedor"]);
        /**/

        /* Parametros de ordenamiento, busqueda y paginacion */
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        if (empty($limit)) {
            $limit_range = "";
        } else {
            $limit_range = "LIMIT $offset, $limit";
        }

        // SUCURSAL
        if(!empty($id_sucursal) && intVal($id_sucursal) != 0) {
            $and_id_sucursal_f = " AND f.id_sucursal = $id_sucursal ";
            $and_id_sucursal_fff = " AND fff.id_sucursal = $id_sucursal ";
            $and_id_sucursal_factura = " AND facturas.id_sucursal = $id_sucursal ";
            $and_id_sucursal = " AND id_sucursal = $id_sucursal ";
        }else{
            if (esAdmin($id_rol)) {
                $and_id_sucursal_fff = "";
                $and_id_sucursal_factura = "";
                $and_id_sucursal_f = "";
                $and_id_sucursal = "";
            } else {
                $and_id_sucursal_f = " AND f.id_sucursal = $sucursal_usuario ";
                $and_id_sucursal_fff = " AND fff.id_sucursal = $sucursal_usuario ";
                $and_id_sucursal_factura = " AND facturas.id_sucursal = $sucursal_usuario ";
                $and_id_sucursal = " AND id_sucursal = $sucursal_usuario ";
            }
            
        };

        // PROVEEDOR PRINCIPAL
        if (!empty($proveedor_principal) && intVal($proveedor_principal) != 0) {
            $where_proveedor_principal =
            " WHERE id_producto IN (
                SELECT id_producto FROM productos_proveedores
                WHERE proveedor_principal = 1
                AND id_proveedor = $proveedor_principal
                GROUP BY id_producto
            ) ";

            $where_proveedor_principal_fp =
            " AND facturas_productos.id_producto IN (
                SELECT id_producto FROM productos_proveedores
                WHERE proveedor_principal = 1
                AND id_proveedor = $proveedor_principal
                GROUP BY id_producto
            ) ";
            
            $where_proveedor_principal_fpp =
            " AND fpp.id_producto IN (
                SELECT id_producto FROM productos_proveedores
                WHERE proveedor_principal = 1
                AND id_proveedor = $proveedor_principal
                GROUP BY id_producto
            ) ";


            $db->setQuery("SELECT proveedor FROM proveedores WHERE id_proveedor = $proveedor_principal;");
            $name_proveedor = $db->loadObject()->proveedor;
            $as_proveedor = " '$name_proveedor' AS proveedor_p_nombre, ";
        } else {
            $where_proveedor_principal_fp = "";
            $where_proveedor_principal_fpp = "";
            $where_proveedor_principal = "";
            $as_proveedor = "";
        }
        

        // VENDEDOR
        $and_id_vendedor = (!empty($id_vendedor) && intVal($id_vendedor) != 0) ? " AND f.id_funcionario = $id_vendedor " : "";

        // SEARCH
        $having = (!empty($search)) ? " HAVING v.funcionario LIKE '$search%' " : "";

        $query ="SELECT SQL_CALC_FOUND_ROWS *, 
            (venta - costo) AS utilidad, 
            IFNULL((((venta - costo)/venta)*100), 0) AS margen, $as_proveedor
            -- Participación
            IFNULL((
                (
                    (
                        SELECT COUNT(id_factura)
                        FROM facturas
                        -- where
                        WHERE estado = 1
                        $and_id_sucursal
                        AND usuario = tab.username
                        AND (fecha_venta BETWEEN '$desde' AND '$hasta')
                        AND facturas.id_factura IN (
                            SELECT id_factura FROM facturas_productos
                            $where_proveedor_principal
                        )
                        
                    )
                    /
                    (
                    SELECT SUM(cant_ventas) FROM(
                        SELECT
                        (
                            SELECT COUNT(id_factura)
                                FROM facturas
                                -- where
                                WHERE estado = 1
                                $and_id_sucursal
                                AND usuario = u.username
                                AND (fecha_venta BETWEEN '$desde' AND '$hasta')
                                AND facturas.id_factura IN (
                                    SELECT id_factura FROM facturas_productos
                                    $where_proveedor_principal
                                )    
                                
                            ) AS cant_ventas
            
                            FROM funcionarios f
                            INNER JOIN users u ON u.id = f.id_usuario
                            INNER JOIN sucursales s ON s.id_sucursal = f.id_sucursal
            
                            WHERE 
                            f.id_puesto IN(SELECT id_puesto FROM puestos WHERE id_tipo_puesto = $tipo_puesto_cajero)
                            $and_id_sucursal_f
                            -- AND u.status = 0
                            AND ((f.fecha_baja = '0000-00-00') OR (f.fecha_baja BETWEEN '$desde' AND '$hasta'))
                        ) vtas
                    )
                ) * 100
            ), 0) AS participacion
            
            FROM(
                SELECT f.id_funcionario AS id_vendedor, f.funcionario AS nombre_vendedor, u.username, u.status, f.estado, f.id_sucursal, s.sucursal,
                    IFNULL((
                        SELECT COUNT(id_factura)
                        FROM facturas
                        -- where
                        WHERE estado = 1
                        $and_id_sucursal_f
                        AND usuario = u.username
                        AND (fecha_venta BETWEEN '$desde' AND '$hasta')
                        AND facturas.id_factura IN (
                            SELECT id_factura FROM facturas_productos
                            $where_proveedor_principal
                        )    
                    ),0) AS cantidad_venta,
                
                    IFNULL((
                        SELECT SUM(facturas_productos.total_venta)
                        FROM facturas_productos
                        INNER JOIN facturas ON facturas.id_factura = facturas_productos.id_factura
                        -- where
                        WHERE facturas.estado = 1
                        $and_id_sucursal_factura
                        AND facturas.usuario = u.username
                        AND (facturas.fecha_venta BETWEEN '$desde' AND '$hasta')
                        AND facturas.id_factura IN (
                            SELECT id_factura FROM facturas_productos
                            $where_proveedor_principal
                        )
                        $where_proveedor_principal_fp
                        
                    ),0) AS venta,

                    u.username  as usuario,
                
                    IFNULL(total_costo.total_costo_sum, 0) AS costo
                
                FROM funcionarios f
                INNER JOIN users u ON u.id = f.id_usuario
                INNER JOIN sucursales s ON s.id_sucursal = f.id_sucursal
                LEFT JOIN (
                    SELECT
                        fff.usuario,
                        SUM(
                            CASE
                                WHEN fpp.fraccionado = 1 THEN
                                    ROUND(
                                        (SELECT IFNULL(costo, 0) FROM lotes WHERE id_lote = fpp.id_lote) /
                                        (SELECT cantidad_fracciones FROM productos WHERE id_producto = fpp.id_producto) *
                                        fpp.cantidad
                                    )
                                ELSE
                                    ROUND((SELECT IFNULL(costo, 0) FROM lotes WHERE id_lote = fpp.id_lote) * fpp.cantidad)
                            END
                        ) AS total_costo_sum
                    FROM facturas_productos fpp
                    INNER JOIN facturas fff ON fff.id_factura = fpp.id_factura
                    WHERE fff.estado = 1
                    $and_id_sucursal_fff
                    AND (fff.fecha_venta BETWEEN '$desde' AND '$hasta')
                    AND fff.id_factura IN (
                        SELECT id_factura FROM facturas_productos
                        $where_proveedor_principal
                    )
                    $where_proveedor_principal_fpp
                    GROUP BY fff.usuario
                ) AS total_costo ON total_costo.usuario = u.username
                WHERE 
                f.id_puesto IN(SELECT id_puesto FROM puestos WHERE id_tipo_puesto = $tipo_puesto_cajero)
                -- AND u.status = 0
                $and_id_sucursal_f
                $and_id_vendedor
                AND ((f.fecha_baja = '0000-00-00') OR (f.fecha_baja BETWEEN '$desde' AND '$hasta'))
            )tab
            $having
            ORDER BY $sort $order
            $limit_range
        ;";

        $db->setQuery($query);

        $rows = $db->loadObjectList();

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total     = $total_row->total;

        if ($rows) {
            $salida = array('total' => $total, 'rows' => $rows);
        } else {
            $salida = array('total' => 0, 'rows' => []);
        }

        echo json_encode($salida);
    break;

    case 'ver_detalle':
        $db = DataBase::conectar();
        $fecha_actual = $ultimo_dia = new DateTime();
        $primer_dia = $fecha_actual->format('Y-m') . '-01 00:00:00';

        $ultimo_dia->modify('last day of this month');
        $ultimo_dia->setTime(23, 59, 59);
        $ultimo_dia = $ultimo_dia->format('Y-m-d H:i:s');

        /* FILTRO VENDEDORES */
            $desde                      = $db->clearText($_REQUEST['desde']) ? $db->clearText($_REQUEST['desde']).' 00:00:00' : "$primer_dia";
            $hasta                      = $db->clearText($_REQUEST['hasta']) ? $db->clearText($_REQUEST['hasta']).' 23:59:59' : "$ultimo_dia";            
            $proveedor_principal        = $db->clearText($_REQUEST['proveedor']);
            $id_vendedor                = $db->clearText($_REQUEST["vendedor"]);
        /**/

        /* Parametros de ordenamiento, busqueda y paginacion */
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;

        if (empty($limit)) {
            $limit_range = "";
        } else {
            $limit_range = "LIMIT $offset, $limit";
        }
        

        // PROVEEDOR PRINCIPAL
        if (!empty($proveedor_principal) && intVal($proveedor_principal) != 0) {
            $where_proveedor_principal =
            " AND fp.id_producto IN (
                SELECT id_producto FROM productos_proveedores
                WHERE proveedor_principal = 1
                AND id_proveedor = $proveedor_principal
                GROUP BY id_producto
            ) ";


            $db->setQuery("SELECT proveedor FROM proveedores WHERE id_proveedor = $proveedor_principal;");
            $name_proveedor = $db->loadObject()->proveedor;
            $as_proveedor = " '$name_proveedor' AS proveedor_p_nombre, ";
        } else {
            $where_proveedor_principal = "";
            $as_proveedor = "";
        }
        

        // VENDEDOR
        $and_id_vendedor = (!empty($id_vendedor) && intVal($id_vendedor) != 0) ? " AND v.id_funcionario = $id_vendedor " : "";

        // SEARCH
        $having = (!empty($search)) ? " HAVING producto LIKE '$search%' OR numero LIKE '$search%' OR codigo LIKE '$search%' OR lote LIKE '$search%' " : "";

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                v.id_funcionario AS id_vendedor, 
                fp.total_venta AS total_venta,
                v.funcionario AS nombre_vendedor, 
                f.usuario,
                $as_proveedor
                CONCAT_WS('-',t.cod_establecimiento,t.punto_de_expedicion,f.numero) AS numero,
                DATE_FORMAT(f.fecha_venta, '%d/%m/%Y') AS fecha_venta,
                (SELECT codigo FROM productos WHERE id_producto = fp.id_producto) AS codigo,
                s.id_sucursal, s.sucursal,
            
                fp.id_producto,
                fp.id_lote,
                fp.lote,
                fp.precio,
                fp.producto,
                fp.cantidad,
                fp.descuento_porc,
                fp.id_factura_producto,
                fp.id_factura,
                CASE WHEN fp.fraccionado = 1 THEN
                    'Fraccionado'
                ELSE
                    'Entero'
                END AS fraccionado,
                
                CASE WHEN fp.fraccionado = 1 THEN
                    ROUND(        
                        ( -- Costo fraccionado
                            (SELECT IFNULL(costo,0) AS costo FROM lotes WHERE id_lote = fp.id_lote)
                            /
                            (SELECT cantidad_fracciones FROM productos WHERE id_producto= fp.id_producto)
                        )
                        *
                        fp.cantidad
                    )
                ELSE
                    ROUND((SELECT IFNULL(costo,0) AS costo FROM lotes WHERE id_lote = fp.id_lote) * fp.cantidad)
                END AS total_costo,
                
                (
                    fp.total_venta 
                    - 
                    (
                        CASE WHEN fp.fraccionado = 1 THEN
                            ROUND(        
                                ( -- Costo fraccionado
                                    (SELECT IFNULL(costo,0) AS costo FROM lotes WHERE id_lote = fp.id_lote)
                                    /
                                    (SELECT cantidad_fracciones FROM productos WHERE id_producto= fp.id_producto)
                                )
                                *
                                fp.cantidad
                            )
                        ELSE
                            ROUND((SELECT IFNULL(costo,0) AS costo FROM lotes WHERE id_lote = fp.id_lote) * fp.cantidad)
                        END
                    )
                ) AS utilidad,
                
                (
                    (
                        (
                            fp.total_venta 
                            - 
                            (
                                CASE WHEN fp.fraccionado = 1 THEN
                                    ROUND(        
                                        ( -- Costo fraccionado
                                            (SELECT IFNULL(costo,0) AS costo FROM lotes WHERE id_lote = fp.id_lote)
                                            /
                                            (SELECT cantidad_fracciones FROM productos WHERE id_producto= fp.id_producto)
                                        )
                                        *
                                        fp.cantidad
                                    )
                                ELSE
                                    ROUND((SELECT IFNULL(costo,0) AS costo FROM lotes WHERE id_lote = fp.id_lote) * fp.cantidad)
                                END
                            )
                        )
                        /
                        fp.total_venta
                    ) * 100
                ) AS margen,
                
                -- hidden
                f.razon_social,
                f.ruc,
                fp.lote
                -- hidden
            FROM (SELECT funcionarios.*, users.id_rol, users.status AS estado_u FROM funcionarios INNER JOIN users ON users.id = funcionarios.id_usuario) v
            LEFT JOIN sucursales s ON v.id_sucursal = s.id_sucursal
            LEFT JOIN facturas f ON f.id_sucursal = s.id_sucursal
            LEFT JOIN facturas_productos fp ON fp.id_factura = f.id_factura
            
            INNER JOIN timbrados t ON f.id_timbrado=t.id_timbrado
            
            LEFT JOIN funcionarios ff ON ff.id_funcionario = v.id_funcionario
            LEFT JOIN users usr ON usr.id = ff.id_usuario
            
            WHERE ff.id_puesto IN(SELECT id_puesto FROM puestos WHERE id_tipo_puesto = $tipo_puesto_cajero) -- TIPO PUESTO CAJERO
                -- AND v.estado_u = 0
                AND f.estado = 1
                AND ((ff.fecha_baja = '0000-00-00') OR (ff.fecha_baja BETWEEN '$desde' AND '$hasta'))
                AND f.usuario = usr.username
                AND (f.fecha_venta BETWEEN '$desde' AND '$hasta') -- fechas
                $and_id_vendedor -- vendedor
                $where_proveedor_principal
            $having
            ORDER BY $sort $order
            $limit_range
        ");

        $rows = $db->loadObjectList();
        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total     = $total_row->total;
        
        if ($rows) {
            $salida = array('total' => $total, 'rows' => $rows);
        } else {
            $salida = array('total' => 0, 'rows' => []);
        }
        echo json_encode($salida);
    break;

    case 'sucursales':
        $db = DataBase::conectar();
        $search = $db->clearText($_REQUEST['term']);
        $having = $search ? "HAVING sucursal LIKE '%$search%'" : "";
        if (esAdmin($id_rol)) {
            $where = "";
        } else {
            $where = " AND id_sucursal = $sucursal_usuario";                
        }

        $db->setQuery("SELECT * FROM sucursales WHERE estado = 1 $where $having");

        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;

    case 'vendedores':
        $db = DataBase::conectar();

        $search = $db->clearText($_REQUEST['term']);
        $having = $search ? "HAVING f.ci LIKE '%$search%' OR  f.funcionario LIKE '%$search%'" : "";
        $id_sucur = $db->clearText($_REQUEST['id_sucursal']);

        if (!empty($id_sucur) && $id_sucur != 0) {
            $where = " AND f.id_sucursal = $id_sucur ";
        } else {
            if (esAdmin($id_rol)) {
                $where = "";
            } else {
                $where = " AND f.id_sucursal = $sucursal_usuario";
            }
            
        }

        $db->setQuery("SELECT f.id_funcionario, f.funcionario AS nombre_apellido, f.ci 
            FROM users u 
            INNER JOIN funcionarios f on f.id_usuario = u.id
            WHERE f.id_puesto IN(SELECT id_puesto FROM puestos WHERE id_tipo_puesto = $tipo_puesto_cajero)
            AND u.username !='admin' 
            -- AND u.status = 0 
            AND ((f.fecha_baja = '0000-00-00') OR (f.fecha_baja BETWEEN '$desde' AND '$hasta')) 
            $where
            $having
        ;");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;
        
    case 'proveedores':
        $db = DataBase::conectar();
        $search = $db->clearText($_REQUEST['term']);
        $having = $search ? "HAVING proveedor LIKE '%$search%'" : "";
        $db->setQuery("SELECT * FROM proveedores $having");
        $rows = ($db->loadObjectList()) ?: [];
        echo json_encode($rows);
    break;
}
