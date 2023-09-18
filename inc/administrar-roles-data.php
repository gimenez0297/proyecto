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
			$where = "";
			//Parametros de ordenamiento, busqueda y paginacion
			$limit = $_REQUEST['limit'];
			$offset	= $_REQUEST['offset'];
			$order = $_REQUEST['order'];
			$sort = $_REQUEST['sort'];
			if (!isset($sort)) $sort = 2;
			

			if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
				$search = $_REQUEST['search'];
				$where = "AND CONCAT_WS(' ', id_rol, rol, estado) LIKE '%$search%'";
			}
			
			$db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_rol, rol, estado FROM roles  WHERE 1=1 $where ORDER BY $sort $order LIMIT $offset, $limit");
			$rows = $db->loadObjectList();
			
			$db->setQuery("SELECT FOUND_ROWS() as total");		
			$total_row = $db->loadObject();
			$total = $total_row->total;
			
			if ($rows){
				$salida = array('total' => $total, 'rows' => $rows);
			}else{
				$salida = array('total' => 0, 'rows' => array());
			}
			
			echo json_encode($salida);
		
		break;

		case 'ver_menus':
			$db = DataBase::conectar();
			$id_rol = $db->clearText($_REQUEST['id_rol']);

			$db->setQuery("SELECT m.id_menu, m.id_menu_padre, m.menu, m.icono, m.orden,
                                IFNULL(rm.acceso, 0) AS acceso,
                                IFNULL(rm.insertar, 0) AS insertar,
                                IFNULL(rm.editar, 0) AS editar,
                                IFNULL(rm.eliminar, 0) AS eliminar,
                                IF(rm.acceso = 1 AND rm.insertar = 1 AND rm.editar = 1 AND rm.eliminar = 1, 1, 0) AS todos
                            FROM menus m
                            LEFT JOIN roles_menu rm ON rm.id_menu=m.id_menu AND rm.id_rol=$id_rol
                            WHERE m.estado='Habilitado' AND m.id_menu_padre IS NULL
                            ORDER BY orden ASC");
            $rows = $db->loadObjectList() ?: [];

            $salida = verMenusRecursivos($db, $rows, 0, [], $id_rol);
            echo json_encode($salida);
		break;

		case 'cargar':
			$db = DataBase::conectar();
			$rol = $db->clearText($_POST['rol']);
			$estado = $db->clearText($_POST['estado']);
			
			if (empty($rol)) { echo "Error. Favor ingrese el nombre del rol"; exit; }
			if (empty($estado)) { echo "Error. Favor ingrese el estado del rol"; exit; }
		

			$db->setQuery("INSERT INTO roles(rol, estado) VALUES('$rol','$estado');");
		
			if(!$db->alter()){
				echo "Error. ".$db->getError();
			}else{
				echo "Rol agregado correctamente al sistema";
			}
			
		break;
					
		case 'editar':
		
			$db = DataBase::conectar();
			$id_rol = $db->clearText($_POST['hidden_id_rol']);
			$rol = $db->clearText($_POST['rol']);
			$estado = $db->clearText($_POST['estado']);
			
			if (empty($rol)) { echo "Error. Favor ingrese el nombre del rol"; exit; }
			if (empty($estado) && $id_rol != 1) { echo "Error. Favor ingrese el estado del rol"; exit; }

			////// El rol 1 'Administrador del Sistema' no se puede quedar inactivo //////
			if ($id_rol == 1) {
				$estado = "Activo";
			}

			$db->setQuery("UPDATE roles SET rol='$rol', estado='$estado' WHERE id_rol = '$id_rol'");
	
			if (!$db->alter()) {
				echo "Error. ". $db->getError();
			} else {
				echo "Rol modificado correctamente";
			}

		break;
		
		case 'eliminar':
			$db = DataBase::conectar();
			$id_rol = $db->clearText($_POST['id']);
			$rol = $db->clearText($_POST['rol']);

			if ($id_rol == 1) {
				echo "Error. El Rol '$rol' no puede ser eliminado del sistema";
				exit;
			}
			
			////// El rol 1 'Administrador del Sistema' no se puede eliminar //////
			$db->setQuery("DELETE FROM roles WHERE id_rol != 1 AND id_rol = $id_rol");

			if ($db->alter()) {
				echo "Rol '$rol' eliminado correctamente";
			} else {
				echo "Error al eliminar el rol '$rol'. ". $db->getError();
			}
			
		break;		

		case 'editar_permisos':
			$db = DataBase::conectar();
			$db->autocommit(false);
			$id_rol = $db->clearText($_POST['id_rol']);
			$rol = $db->clearText($_POST['rol']);
			
			if (empty($id_rol)) {
				echo "Error. No se encontro el ID del rol. Favor recargue la página e intente nuevamente";
				exit;
			}
			if (empty($_POST['permisos'])) {
				echo "Error. No se encontraron los permisos. Favor recargue la página e intente nuevamente";
				exit;
			}
			if ($id_rol == 1) {
				echo "Error. No puede editar los permisos del rol '$rol'";
				exit;
			}

			$db->setQuery("DELETE FROM roles_menu WHERE id_rol=$id_rol");
		
			if (!$db->alter()) {
				echo "Error al eliminar los permisos para actualizarlos. ".$db->getError();
				$db->rollback();  //Revertimos los cambios
				exit;
			}

			// Guarda los id de los menus que se vayan insertando para no repetirlos
			$menus_insertados = [];

			foreach ($_POST['permisos'] as $v) {
				$id_menu = $db->clearText($v['id_menu']);
				$id_menu_padre = $db->clearText($v['id_menu_padre']);
				$acceso = $db->clearText($v['acceso']);
				$insertar = $db->clearText($v['insertar']);
				$editar = $db->clearText($v['editar']);
				$eliminar = $db->clearText($v['eliminar']);

				// Solo se inserta si tiene acceso al menu, los demas permisos se ignoran si no tiene acceso
				if ($acceso == 1) {
					$db->setQuery("INSERT INTO roles_menu(id_rol, id_menu, acceso, insertar, editar, eliminar)
									VALUES($id_rol, $id_menu, $acceso, $insertar, $editar, $eliminar)");
			
					if (!$db->alter()) {
						echo "Error al editar los permisos del rol. ".$db->getError();
						$db->rollback();  //Revertimos los cambios
						exit;
					}
				}

			}

			$db->commit(); //Insertamos los datos en la BD
			echo "Permisos editados correctamente";
			
		break;

	}

    // Funcion recursiva que busca y encadena los submenus de un menú padre
    function verMenusRecursivos($db, $menus, $index, $acc, $id_rol) {
        if(count($menus) > $index){
            $id_menu 	= $menus[$index]->id_menu;
            $acc[] 		= $menus[$index];
            $i 			= ++$index;

			$db->setQuery("SELECT m.id_menu, m.id_menu_padre, m.menu, m.icono, m.orden,
                                IFNULL(rm.acceso, 0) AS acceso,
                                IFNULL(rm.insertar, 0) AS insertar,
                                IFNULL(rm.editar, 0) AS editar,
                                IFNULL(rm.eliminar, 0) AS eliminar,
                                IF(rm.acceso = 1 AND rm.insertar = 1 AND rm.editar = 1 AND rm.eliminar = 1, 1, 0) AS todos
                            FROM menus m
                            LEFT JOIN roles_menu rm ON rm.id_menu=m.id_menu AND rm.id_rol=$id_rol
                            WHERE m.estado='Habilitado' AND m.id_menu_padre = $id_menu
                            ORDER BY orden ASC");
            $sub_menus = $db->loadObjectList() ?: [];
            if(!empty($sub_menus)){
                $acc = verMenusRecursivos($db, $sub_menus, 0, $acc, $id_rol);
            }
            $acc = verMenusRecursivos($db, $menus, $i, $acc, $id_rol);
        }
        return $acc;
    }


?>
