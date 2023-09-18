<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}

$q           = $_REQUEST['q'];
$usuario     = $auth->getUsername();
$id_sucursal = datosUsuario($usuario)->id_sucursal;

switch ($q) {

    case 'ver':
        $db    = DataBase::conectar();
        $where = "";

        //Parametros de ordenamiento, busqueda y paginacion
        $search = $db->clearText($_REQUEST['search']);
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = ($db->clearText($_REQUEST['sort'])) ?: 2;
        if (isset($search) && !empty($search)) {
            $having = "HAVING CONCAT_WS(' ', c.categoria, c.cargo, estado_str) LIKE '%$search%'";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            c.id_categoria,
                            c.categoria,
                            c.cargo,
                            c.salario,
                            c.estado,
                            CASE c.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            c.usuario,
                            DATE_FORMAT(c.fecha,'%d/%m/%Y %H:%i:%s') AS fecha, 
                            c.id_categoria_superior, 
                            CONCAT(c1.categoria,' - (',c1.cargo,' - ',c1.salario,')') AS categoria_superior 
                        FROM categorias c 
                        LEFT JOIN categorias c1 ON c1.id_categoria=c.id_categoria_superior
                        $having
                        ORDER BY $sort $order
                        LIMIT $offset, $limit");
        $rows = $db->loadObjectList();

        $db->setQuery("SELECT FOUND_ROWS() as total");
        $total_row = $db->loadObject();
        $total     = $total_row->total;

        if ($rows) {
            $salida = array('total' => $total, 'rows' => $rows);
        } else {
            $salida = array('total' => 0, 'rows' => array());
        }

        echo json_encode($salida);
        break;

    case 'cargar':
        $db     = DataBase::conectar();
        $categoria = mb_convert_case($db->clearText($_POST['categoria']), MB_CASE_UPPER, "UTF-8");
        $cargo = mb_convert_case($db->clearText($_POST['cargo']), MB_CASE_UPPER, "UTF-8");
        $salario = $db->clearText(quitaSeparadorMiles($_POST['salario']));
        $id_categoria_superior = $db->clearText($_POST['id_categoria_superior']);

        if (empty($categoria)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripcion en  categoria"]);
            exit;
        }
        if (empty($id_categoria_superior) || !isset($id_categoria_superior) || intval($id_categoria_superior) == 0) {
            $id_categoria_superior = "NULL";
        }

        $db->setQuery("INSERT INTO categorias (categoria, cargo, salario, id_categoria_superior, estado, usuario, fecha)
                            VALUES ('$categoria','$cargo','$salario',$id_categoria_superior,'1','$usuario',NOW())");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Categoría registrada correctamente"]);

        break;

    case 'editar':
        $db        = DataBase::conectar();
        $id_categoria = $db->clearText($_POST['id_categoria']);
        $categoria = mb_convert_case($db->clearText($_POST['categoria']), MB_CASE_UPPER, "UTF-8");
        $cargo = mb_convert_case($db->clearText($_POST['cargo']), MB_CASE_UPPER, "UTF-8");
        $salario = $db->clearText(quitaSeparadorMiles($_POST['salario']));
        $id_categoria_superior = $db->clearText($_POST['id_categoria_superior']);

        if (empty($categoria)) {
            echo json_encode(["status" => "error", "mensaje" => "Favor ingrese una descripcion en categoria"]);
            exit;
        }
        if (empty($id_categoria_superior) || !isset($id_categoria_superior) || intval($id_categoria_superior) == 0) {
            $id_categoria_superior = "NULL";
        }

        $db->setQuery("UPDATE categorias SET categoria='$categoria', cargo='$cargo', salario='$salario', id_categoria_superior=$id_categoria_superior WHERE id_categoria='$id_categoria'");

        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Categoría '$categoria' modificado correctamente"]);
        break;

    case 'cambiar-estado':
        $db        = DataBase::conectar();
        $id_categoria = $db->clearText($_POST['id_categoria']);
        $estado    = $db->clearText($_POST['estado']);

        $db->setQuery("UPDATE categorias SET estado='$estado' WHERE id_categoria='$id_categoria'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Estado actualizado correctamente"]);
        break;

    case 'eliminar':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $categoria = $db->clearText($_POST['categoria']);

        $db->setQuery("DELETE FROM categorias WHERE id_categoria = '$id'");
        if (!$db->alter()) {
            echo json_encode(["status" => "error", "mensaje" => "Error. " . $db->getError()]);
            exit;
        }

        echo json_encode(["status" => "ok", "mensaje" => "Categoría '$categoria' eliminado correctamente"]);
        break;

}
