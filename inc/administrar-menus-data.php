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

            $db->setQuery("SELECT id_menu, id_menu_padre, menu, titulo, url, icono, orden, estado
                                FROM menus
                                WHERE id_menu_padre IS NULL
                                ORDER BY orden ASC");
            $rows = $db->loadObjectList() ?: [];

            $salida = verMenusRecursivos($db, $rows, 0, []);
            echo json_encode($salida);
        
        break;

		case 'ver_menus':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_menu, id_menu_padre, menu
                            FROM menus
                            WHERE menu LIKE '%$term%'
                            ORDER BY orden
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            $menus = [];
            foreach ($rows as $row) {
                $id_menu = $row->id_menu;
                $menu =  menu_padre($row); // Contentena con su menú padre, si es que tiene
                $menus[] = ["id_menu" => $id_menu, "menu" => $menu];
            }
            
            if (empty($menus)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $menus, 'total_count' => $total_count];
            }

            echo json_encode($salida);
		break;

		case 'cargar':
			$db = DataBase::conectar();
			$id_menu_padre = $db->clearText($_POST['menu_padre']);
			$menu = $db->clearText($_POST['menu']);
			$titulo = $db->clearText($_POST['titulo']);
			$url = $db->clearText($_POST['url']);
			$icono = $db->clearText($_POST['icono']);
			$orden = $db->clearText($_POST['orden']);
			$estado = $db->clearText($_POST['estado']);
			
			if (empty($id_menu_padre)) { $id_menu_padre = "NULL"; }
			if (empty($menu)) { echo "Error. Favor ingrese el nombre del menú"; exit; }
			if (empty($orden)) { echo "Error. Favor ingrese el orden del menú"; exit; }
			if (empty($estado)) { echo "Error. Favor ingrese el estado del menú"; exit; }
		

			$db->setQuery("INSERT INTO menus(id_menu_padre, menu, titulo, url, icono, orden, estado) VALUES($id_menu_padre,'$menu','$titulo','$url','$icono','$orden','$estado');");
			if (!$db->alter()) {
				echo "Error. ".$db->getError();
				exit;
			}

			echo "Menú agregado correctamente";
		break;
					
		case 'editar':
		
			$db = DataBase::conectar();
			$id_menu = $db->clearText($_POST['hidden_id_menu']);
			$id_menu_padre = $db->clearText($_POST['menu_padre']);
			$menu = $db->clearText($_POST['menu']);
			$titulo = $db->clearText($_POST['titulo']);
			$url = $db->clearText($_POST['url']);
			$icono = $db->clearText($_POST['icono']);
			$orden = $db->clearText($_POST['orden']);
			$estado = $db->clearText($_POST['estado']);
			
			if (empty($id_menu_padre)) { $id_menu_padre = "NULL"; }
			if (empty($menu)) { echo "Error. Favor ingrese el nombre del menú"; exit; }
			if (empty($orden)) { echo "Error. Favor ingrese el orden del menú"; exit; }
			if (empty($estado)) { echo "Error. Favor ingrese el estado del rol"; exit; }

			$db->setQuery("UPDATE menus SET id_menu_padre=$id_menu_padre, menu='$menu', titulo='$titulo', url='$url', icono='$icono', orden='$orden', estado='$estado' WHERE id_menu = '$id_menu'");
	
			if (!$db->alter()) {
				echo "Error. ". $db->getError();
			} else {
				echo "Menú modificado correctamente";
			}

		break;
		
		case 'eliminar':
			$db = DataBase::conectar();
			$db->autocommit(FALSE);
			$id_menu = $db->clearText($_POST['id_menu']);
			$menu = $db->clearText($_POST['menu']);

			$db->setQuery("DELETE FROM menus WHERE id_menu = $id_menu");

			if (!$db->alter()) {
				echo "Error al eliminar el menú '$menu'. ". $db->getError();
				$db->rollback();  //Revertimos los cambios
				exit;
			}

			$db->setQuery("DELETE FROM roles_menu WHERE id_menu = $id_menu");

			if (!$db->alter()) {
				echo "Error la eliminar la relacion entre los roles y el el menú '$menu'. ". $db->getError();
				$db->rollback();  //Revertimos los cambios
				exit;
			}

			$db->commit(); //Insertamos los datos en la BD
			echo "Menú '$menu' eliminado correctamente";
		break;		

	}

    // Funcion recursiva que concatena un menú con su menú padre
    function menu_padre($m) {
        $db = DataBase::conectar();
        $id_menu_padre = $m->id_menu_padre;
        $menu = $m->menu;

        if (isset($id_menu_padre)) {
            $db->setQuery("SELECT id_menu, id_menu_padre, menu
                            FROM menus
                            WHERE id_menu='$id_menu_padre'");
            $row = $db->loadObject();
            return menu_padre($row) . "->" . $menu;
        }

        return $menu;
    }

    // Funcion recursiva que busca y encadena los submenus de un menú padre
    function verMenusRecursivos($db, $menus, $index, $acc){
        if(count($menus) > $index){
            $id_menu 	= $menus[$index]->id_menu;
            $acc[] 		= $menus[$index];
            $i 			= ++$index;

            $db->setQuery("SELECT id_menu, id_menu_padre, menu, titulo, url, icono, orden, estado
                                FROM menus
                                WHERE id_menu_padre = $id_menu
                                ORDER BY orden ASC");
            $sub_menus = $db->loadObjectList() ?: [];
            if(!empty($sub_menus)){
                $acc = verMenusRecursivos($db, $sub_menus, 0, $acc);
            }
            $acc = verMenusRecursivos($db, $menus, $i, $acc);
        }
        return $acc;
    }


?>
