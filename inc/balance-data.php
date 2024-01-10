<?php

include ("funciones.php");
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}
$q = $_REQUEST['q'];
$usuario = $auth->getUsername();

switch ($q) {
    
    case 'ver':
        $db = DataBase::conectar();
        $id_libro = $db->clearText($_REQUEST['id_libro']);

        $db->setQuery("SELECT *
                        FROM
                            libro_cuentas
                        WHERE id_padre IS NULL 
                        ORDER BY nivel ASC");
        $rows = $db->loadObjectList() ?: [];

        $salida = verCuentasRecursivas($db, $rows, 0, [],$id_libro);
        echo json_encode($salida);
    
    break;

    case 'agregar_cuenta':
        $db = DataBase::conectar();
        $id_padre = $db->clearText($_POST['id_padre']);
        $cuenta = $db->clearText($_POST['cuenta']);
        $denominacion = mb_convert_case($db->clearText($_POST['denominacion']), MB_CASE_UPPER, "UTF-8");

        if (empty($cuenta)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Cuenta"]);
            exit;
        }
        if (empty($denominacion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Denominacion"]);
            exit;
        }

        $db->setQuery("SELECT * FROM libro_cuentas WHERE cuenta = $cuenta");
        $row = $db->loadObject();

        if (!empty($row)) {
            echo json_encode(["status" => "error", "mensaje" => "Ya existe una cuenta con el mismo nro. de cuenta."]);
            exit;
        }

        $db->setQuery("SELECT * FROM libro_cuentas WHERE id_libro_cuenta = $id_padre;");
        $row_c = $db->loadObject();

        $tipo_cuenta = $row_c->tipo_cuenta;

        $db->setQuery("INSERT INTO `libro_cuentas` (
                            `id_padre`,
                            `cuenta`,
                            `tipo_cuenta`,
                            `denominacion`,
                            `usuario`,
                            `fecha`
                        )
                        VALUES
                            (
                            $id_padre,
                            $cuenta,
                            $tipo_cuenta,
                            '$denominacion',
                            '$usuario',
                            NOW());");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Cuenta registrada correctamente"]);
    break;

    case 'agregar_padres':
        $db = DataBase::conectar();
        $cuenta = $db->clearText($_POST['cuenta_pa']);
        $denominacion = mb_convert_case($db->clearText($_POST['denominacion_padre']), MB_CASE_UPPER, "UTF-8");

        if (empty($cuenta)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Cuenta"]);
            exit;
        }
        if (empty($denominacion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Denominacion"]);
            exit;
        }
        
        $db->setQuery("SELECT * FROM libro_cuentas WHERE cuenta = $cuenta");
        $row = $db->loadObject();

        if (!empty($row)) {
            echo json_encode(["status" => "error", "mensaje" => "Ya existe una cuenta con el mismo nro. de cuenta."]);
            exit;
        }


        $db->setQuery("INSERT INTO `libro_cuentas` (
                        `nivel`,
                        `cuenta`,
                        `denominacion`,
                        `usuario`,
                        `fecha`
                    )
                    VALUES
                        (
                        1,
                        $cuenta,
                        '$denominacion',
                        '$usuario',
                        NOW()
                        );");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Cuenta Padre registrada correctamente"]);
    break;

    case 'editar_cuenta':
        $db = DataBase::conectar();
        $id_padre = $db->clearText($_POST['id_padre']);
        $cuenta = $db->clearText($_POST['cuenta']);
        $denominacion = mb_convert_case($db->clearText($_POST['denominacion']), MB_CASE_UPPER, "UTF-8");

        if (empty($cuenta)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Cuenta"]);
            exit;
        }
        if (empty($denominacion)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor complete el campo Denominacion"]);
            exit;
        }

        $db->setQuery("SELECT * FROM libro_cuentas WHERE cuenta = $cuenta AND id_libro_cuenta != $id_padre");
        $row = $db->loadObject();

        if (!empty($row)) {
            echo json_encode(["status" => "error", "mensaje" => "Ya existe una cuenta con el mismo nro. de cuenta."]);
            exit;
        }

        $db->setQuery("UPDATE
                            `libro_cuentas`
                        SET
                            `cuenta` = $cuenta,
                            `denominacion` = '$denominacion'
                        WHERE `id_libro_cuenta` = $id_padre;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Cuenta actualizada correctamente"]);
    break;

    case 'eliminar_cuenta':
        $db = DataBase::conectar();
        $id_padre = $db->clearText($_POST['id']);

        $db->setQuery("SELECT * FROM libro_cuentas WHERE id_libro_cuenta = $id_padre;");
        $row = $db->loadObject();

        $nivel = $row->nivel;

        if ($nivel == 1) {
            echo json_encode(["status" => "error", "mensaje" => "No se pueden borrar cuentas de Nivel 1."]);
            exit;
        }

        $db->setQuery("SELECT * FROM libro_cuentas WHERE id_padre = $id_padre");
        $row_p = $db->loadObject();

        if (!empty($row_p)) {
            echo json_encode(["status" => "error", "mensaje" => "No se pueden borrar cuentas que tengan subcuentas."]);
            exit;
        }

        $db->setQuery("DELETE
                        FROM
                            `libro_cuentas`
                        WHERE `id_libro_cuenta` = $id_padre;");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error de base de datos"]);
            exit;
        }   

        echo json_encode(["status" => "ok", "mensaje" => "Cuenta Eliminada correctamente"]);
    break;
}

// Funcion recursiva que busca y encadena las subcuentas de una cuenta padre
function verCuentasRecursivas($db, $cuentas, $index, $acc,$id_libro){
    if(count($cuentas) > $index){
        $id_cuenta 	= $cuentas[$index]->id_libro_cuenta;
        $acc[] 		= $cuentas[$index];
        $i 			= ++$index;

        $db->setQuery("SELECT
                            lc.`id_libro_cuenta`,
                            lc.`id_padre`,
                            lc.`cuenta`,
                            lc.`denominacion`,
                            lc.tipo_cuenta,
                            (
                                SELECT IFNULL(SUM(l.`haber`),0) FROM libro_diario_detalles l 
                                LEFT JOIN libro_diario ld ON ld.id_libro_diario = l.id_libro_diario
                                WHERE l.`id_libro_cuenta` = lc.`id_libro_cuenta` AND ld.id_libro_diario_periodo = $id_libro
                            ) AS haber,                            
                            (
                            SELECT IFNULL(SUM(ldd.`debe`),0) FROM libro_diario_detalles ldd
                            LEFT JOIN libro_diario lddd ON lddd.id_libro_diario = ldd.id_libro_diario
                            WHERE ldd.`id_libro_cuenta` = lc.`id_libro_cuenta` AND lddd.id_libro_diario_periodo = $id_libro
                            ) AS debe                        
                            FROM
                            libro_cuentas lc
                        WHERE lc.id_padre = $id_cuenta
                        ORDER BY cuenta ASC");
        $sub_cuentas = $db->loadObjectList() ?: [];
        if(!empty($sub_cuentas)){
            $acc = verCuentasRecursivas($db, $sub_cuentas, 0, $acc, $id_libro);
        }
        $acc = verCuentasRecursivas($db, $cuentas, $i, $acc, $id_libro);
    }
    return $acc;
}