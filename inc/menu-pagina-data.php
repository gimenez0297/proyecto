<?php
include "funciones.php";
if (!verificaLogin()) {
    echo json_encode(["status" => "error", "mensaje" => "La sesion ha expirado, refresque la página y vuelva a iniciar sesión."]);
    exit;
}
$q       = $_REQUEST['q'];
$usuario = $auth->getUsername();

switch ($q) {

    case 'ver':
        $db         = DataBase::conectar();
        $where      = "";
        $where_tipo = "";
        //Parametros de ordenamiento, busqueda y paginacion
        $limit  = $db->clearText($_REQUEST['limit']);
        $offset = $db->clearText($_REQUEST['offset']);
        $order  = $db->clearText($_REQUEST['order']);
        $sort   = $db->clearText($_REQUEST['sort']);

        if (!isset($sort)) {
            $sort = 'm.orden+1';
        }

        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            $search = $db->clearText($_REQUEST['search']);
            $where  = "AND CONCAT_WS(' ', m.id_menu_pagina, mp.menu, m.menu, m.titulo, m.url, m.orden, m.estado, m.tipo) LIKE '%$search%'";
        }

        if (!empty($_REQUEST['tipo'])) {
            $tipo_filtro = $db->clearText($_REQUEST['tipo']);
            $where_tipo  = "AND m.tipo=$tipo_filtro";
        }

        $db->setQuery("SELECT SQL_CALC_FOUND_ROWS m.id_menu_pagina,m.tipo, m.id_menu_padre, IF(m.id_menu_padre IS NULL, m.menu, mp.menu) AS menu, IF(m.id_menu_padre IS NOT NULL, m.menu, '-') AS submenu, m.titulo, m.url, m.url_tipo, m.url_target, m.id_pagina, p.titulo AS nombre_pagina, m.orden, CASE m.estado WHEN '1' THEN 'Activo' WHEN '0' THEN 'Inactivo' END AS nombre_estado, CASE m.tipo WHEN '1' THEN 'CABECERA' WHEN '2' THEN 'FOOTER' END AS nombre_tipo, DATE_FORMAT(m.creacion,'%d/%m/%Y %H:%i:%s') AS fecha , m.estado
            FROM menus_pagina m
            LEFT JOIN menus_pagina mp ON m.id_menu_padre=mp.id_menu_pagina
            LEFT JOIN paginas p ON m.id_pagina=p.id_pagina
            WHERE 1=1 $where $where_tipo ORDER BY $sort , m.tipo $order LIMIT $offset, $limit");
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

    case 'ver_menus':
        $db = DataBase::conectar();

        $db->setQuery("SELECT m.id_menu_pagina, IF(m.id_menu_padre IS NULL, m.menu, CONCAT(mp.menu,'->',m.menu)) AS menu
            FROM menus_pagina m
            LEFT JOIN menus_pagina mp ON m.id_menu_padre=mp.id_menu_pagina
            ORDER BY m.orden+1, mp.orden+1");
        $rows = $db->loadObjectList();

        echo json_encode($rows);

        break;

    case 'ver_paginas':
        $db = DataBase::conectar();

        $db->setQuery("SELECT id_pagina, titulo FROM paginas WHERE estado=1");
        $rows = $db->loadObjectList();

        echo json_encode($rows);

        break;

    case 'cambiar-estado':
        $db     = DataBase::conectar();
        $id     = $db->clearText($_POST['id']);
        $status = $db->clearText($_POST['estado']);

        $db->setQuery("SELECT id_menu_pagina FROM menus_pagina mp WHERE mp.id_menu_padre=$id");
        $rows = $db->loadObjectList();
        if ($rows) {
            foreach ($rows as $row) {
                $db->setQuery("UPDATE menus_pagina SET estado=$status WHERE id_menu_pagina = $row->id_menu_pagina");
                if (!$db->alter()) {
                    echo "Error. " . $db->getError();
                }
            }
        }

        $db->setQuery("UPDATE menus_pagina SET estado=$status WHERE id_menu_pagina=$id");

        if ($db->alter()) {
            echo "Estado actualizado correctamente";
        } else {
            echo "Error al cambiar estado";
        }

        break;

    case 'cargar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id_menu_padre = $db->clearText($_POST['menu_padre']);
        $menu          = $db->clearText($_POST['menu']);
        $titulo        = $db->clearText($_POST['titulo']);

        $url       = $db->clearText(htmlspecialchars($_POST['url']));
        $url_tipo  = $db->clearText($_POST['url_tipo']);
        $id_pagina = $db->clearText($_POST['pagina']);
        $target    = $db->clearText($_POST['target']);

        $orden = $db->clearText($_POST['orden']);
        $tipo  = $db->clearText($_POST['tipo']);

        if (empty($id_menu_padre)) {$id_menu_padre = "NULL";}
        if (empty($menu)) {echo "Error. Favor ingrese el nombre del menú";exit;}
        if (empty($orden)) {echo "Error. Favor ingrese el orden del menú";exit;}
        if (empty($url_tipo)) {echo "Error. Favor ingrese un tipo de Url";exit;}
        if (empty($target)) {echo "Error. Favor ingrese un tipo de Target";exit;}

        if ($url_tipo == 1) {
            if (empty($id_pagina)) {echo "Error. Favor ingrese una página";exit;}

            $db->setQuery("SELECT url, id_pagina FROM paginas WHERE id_pagina=$id_pagina");
            $row = $db->loadObject();
            if ($row) {
                $url       = $row->url;
                $id_pagina = $row->id_pagina;
            }
        }

        if ($url_tipo == 2) {
            if (empty($url)) {echo "Error. Favor ingrese un enlance";exit;}
        }

        $db->setQuery("INSERT INTO menus_pagina(id_menu_padre, menu, titulo, url, url_tipo,url_target,id_pagina, orden, tipo,creacion) VALUES($id_menu_padre,'$menu','$titulo','$url','$url_tipo','$target','$id_pagina','$orden','$tipo',NOW());");

        if (!$db->alter()) {
            echo "Error. " . $db->getError();
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $db->commit(); //Insertamos los datos en la BD
        echo "Menú agregado correctamente";
        break;

    case 'editar':

        $db            = DataBase::conectar();
        $id_menu       = $db->clearText($_POST['hidden_id_menu']);
        $id_menu_padre = $db->clearText($_POST['menu_padre']);
        $menu          = $db->clearText($_POST['menu']);
        $titulo        = $db->clearText($_POST['titulo']);

        $url       = $db->clearText($_POST['url']);
        $url_tipo  = $db->clearText($_POST['url_tipo']);
        $id_pagina = $db->clearText($_POST['pagina']);
        $target    = $db->clearText($_POST['target']);

        $orden = $db->clearText($_POST['orden']);
        $tipo  = $db->clearText($_POST['tipo']);

        if (empty($id_menu_padre)) {$id_menu_padre = "NULL";}
        if (empty($menu)) {echo "Error. Favor ingrese el nombre del menú";exit;}
        if (empty($orden)) {echo "Error. Favor ingrese el orden del menú";exit;}
        if (empty($url_tipo)) {echo "Error. Favor ingrese un tipo de Url";exit;}
        if (empty($target)) {echo "Error. Favor ingrese un tipo de Target";exit;}

        if ($url_tipo == 1) {
            if (empty($id_pagina)) {echo "Error. Favor ingrese una página";exit;}

            $db->setQuery("SELECT url, id_pagina FROM paginas WHERE id_pagina=$id_pagina");
            $row = $db->loadObject();
            if ($row) {
                $url       = $row->url;
                $id_pagina = $row->id_pagina;
            }
        }

        if ($url_tipo == 2) {
            if (empty($url)) {echo "Error. Favor ingrese un enlance";exit;}
        }

        $db->setQuery("UPDATE menus_pagina
            SET id_menu_padre=$id_menu_padre, menu='$menu', titulo='$titulo', url='$url', url_tipo='$url_tipo', url_target='$target', id_pagina='$id_pagina', orden='$orden', tipo='$tipo'
            WHERE id_menu_pagina = '$id_menu'");

        if (!$db->alter()) {
            echo "Error. " . $db->getError();
        } else {
            echo "Menú modificado correctamente";
        }

        break;

    case 'eliminar':
        $db = DataBase::conectar();
        $db->autocommit(false);
        $id_menu_pagina = $db->clearText($_POST['id_menu_pagina']);
        $menu           = $db->clearText($_POST['menu']);

        $db->setQuery("SELECT id_menu_pagina FROM menus_pagina mp WHERE mp.id_menu_padre=$id_menu_pagina");
        $rows = $db->loadObjectList();
        if ($rows) {
            echo "Error al tratar de eliminar el menu, hay otro menu vinculado";
            exit;
        }

        $db->setQuery("DELETE FROM menus_pagina WHERE id_menu_pagina = $id_menu_pagina");

        if (!$db->alter()) {
            echo "Error al eliminar el menú '$menu'. " . $db->getError();
            $db->rollback(); //Revertimos los cambios
            exit;
        }

        $db->commit(); //Insertamos los datos en la BD
        echo "Menú '$menu' eliminado correctamente";
        break;

}
