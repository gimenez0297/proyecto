<?php

function primerMenuAccesible($db, $id_usuario) {
    $db->setQuery("SELECT m.`id_menu`, m.menu, SUBSTRING_INDEX(m.url, '/', -1) AS url, m.orden
                        FROM menus m
                        JOIN roles_menu rm ON m.id_menu=rm.id_menu
                        JOIN users u ON u.id_rol=rm.id_rol
                        WHERE m.id_menu_padre IS NULL AND m.estado='Habilitado' AND u.id=$id_usuario
                        ORDER BY m.orden ASC");
    $menus = $db->loadObjectList() ?: [];
    if (!empty($menus)) {
        $menu = menusAccesibles($db, $id_usuario, $menus, 0);
        return $menu->url;
    }
    return null;
}

function menusAccesibles($db, $id_usuario, $menus, $index) {
    $menu = null;
    if (count($menus) > $index) {
        $id_menu = (int) $menus[$index]->id_menu;
        $url = $menus[$index]->url;

        if (!in_array($url, ["", "#"])) {
            $menu = $menus[$index];
        } else {
            $db->setQuery("SELECT m.id_menu, m.menu, SUBSTRING_INDEX(m.url, '/', -1) AS url, m.orden
                            FROM menus m
                            JOIN roles_menu rm ON m.id_menu=rm.id_menu
                            JOIN users u ON u.id_rol=rm.id_rol
                            WHERE m.id_menu_padre = $id_menu AND m.estado='Habilitado' AND u.id=$id_usuario
                            ORDER BY m.orden ASC;");
            $sub_menus = $db->loadObjectList() ?: [];
            if (!empty($sub_menus)) {
                $menu = menusAccesibles($db, $id_usuario, $sub_menus, 0);
            } else {
                $i = ++$index;
                $menu = menusAccesibles($db, $id_usuario, $menus, $i);
            }
        }
    }
    return $menu;
}

