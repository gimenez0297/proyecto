<?php
	include ("funciones.php");
	//verificaLogin();
	$q = $_REQUEST['q'];
	
	switch ($q) {

        // BUSCAR RUC EN SET
        case 'buscar_ruc':
            $db = DataBase::conectar();
            $ruc_tmp = $db->clearText($_POST['ruc']);
            
            //Si tiene guion, entonces sacamos
            if (stripos($ruc_tmp, "-") !== false) {
                $ruc_sin_dv_tmp = explode("-",$ruc_tmp);
                $ruc_tmp = $ruc_sin_dv_tmp[0];	
            }
            $url = "https://marangatu.set.gov.py/eset-restful/contribuyentes/consultar?ruc=$ruc_tmp&codigoEstablecimiento=001";

            $context = array(
                  "ssl" => array(
                 'ciphers' => 'DEFAULT:!DH',
              ),
            );
            $json = file_get_contents($url, false, stream_context_create($context));
            $arr = json_decode($json);

            if ($arr->procesamientoCorrecto == "true" && $arr->estado == "ACTIVO") {
                $ruc = $arr->ruc;
                $dv = $arr->dv;
                $nombre_tmp = $arr->nombre;
                $razon_social = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $nombre_tmp)));
                $telefono = $arr->telefono;
                
                $direccion_tmp = $arr->direccion;
                
                //REEMPLAZAMOS PALABRAS INNECESARIAS EN DIRECCIONES
                $search  = array('AVENIDA, ', 'Numero #', ' //DEPARTAMENTO', ' //CASA', ' //OFICINA', 'CALLE, ', ' //INTERIOR', 'CASA #', 'ESQUINA', 'CASI ', 'ENTRE ');
                $replace = array('', 'N° ', '', '', '', '', '', 'N° ', 'ESQ.', 'C/ ', 'E/ ');
                $direccion = str_ireplace($search, $replace, $direccion_tmp);
                
            } else {
                $ruc = ""; $dv = ""; $razon_social = ""; $telefono = ""; $direccion = "";
            }
            
            $salida = ["ruc" => $ruc, "dv" => $dv, "razon_social" => $razon_social, "telefono" => $telefono, "direccion" => $direccion];
            echo json_encode($salida);
        
        break;
		
        // BUSCAR CI EN BASE DE DATOS DE CÉDULAS DE ÑAMANDU
        case 'buscar_ci':
            $db = DataBase::conectar();
            $ci = $db->clearText($_POST['ci']);

            // Si tiene guion, entonces sacamos
            if (stripos($ci, "-") !== false) {
                $ruc_sin_dv_tmp = explode("-",$ci);
                $ci = $ruc_sin_dv_tmp[0];	
            }

            $url = file_get_contents("https://datos.namandu.com/datos.php?x=$ci");
            $arr = (json_decode($url) ?: []);
            foreach ($arr as $v) {
                $datos = $v;
            }
            $datos = $datos ?: ["cedula" => NULL];
            echo json_encode($datos);
        break;

        // BUSCAR PROVEEDOR
        case 'buscar_proveedor':
            $db = DataBase::conectar();
            $ruc = $db->clearText($_POST["ruc"]);
            $tipo_proveedor = $db->clearText($_POST["tipo_proveedor"]);

            $db->setQuery("SELECT p.id_proveedor, proveedor, nombre_fantasia, ruc, contacto, direccion, telefono, email, obs
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt ON p.id_proveedor=pt.id_proveedor
                            WHERE ruc='$ruc' AND pt.tipo_proveedor=$tipo_proveedor
                            GROUP BY p.id_proveedor");
            $row = ($db->loadObject()) ?: ["ruc" => null];
            echo json_encode($row);
        break;		

        // BUSCAR CLIENTE
        case 'buscar_cliente':
            $db = DataBase::conectar();
            $ruc = $db->clearText($_POST["ruc"]);

            $db->setQuery("SELECT id_cliente, razon_social, ruc, telefono, celular, email, obs, id_tipo, tipo, puntos
                            FROM clientes
                            WHERE (ruc='$ruc' OR ruc LIKE '$ruc-%')");
            $row = ($db->loadObject()) ?: ["ruc" => null];
            echo json_encode($row);
        break;		

        case 'paises':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_pais, nombre_es AS pais
                            FROM paises
                            WHERE nombre_es LIKE '$term%'
                            ORDER BY nombre_es
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'tipos_productos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_tipo_producto, tipo, principios_activos
                            FROM tipos_productos
                            WHERE estado=1 AND tipo LIKE '$term%'
                            ORDER BY tipo
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'productos_insumo':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS 
                                id_producto_insumo,
                                producto,
                                costo,
                                codigo,
                                (SELECT 
                                 IFNULL(SUM(s.stock), 0)
                                 FROM stock_insumos s 
                                 WHERE s.id_producto_insumo=p.id_producto_insumo AND (vencimiento>=CURRENT_DATE() OR vencimiento IS NULL)
                                ) AS stock
                            FROM productos_insumo p
                            WHERE estado=1 AND  producto LIKE '$term%'
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'premios':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                            id_premio,
                            premio,
                            codigo,
                            costo,
                            puntos
                            FROM premios
                            WHERE estado=1 AND  premio LIKE '$term%'
                            
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'clasificaciones_productos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_clasificacion_producto, clasificacion
                            FROM clasificaciones_productos
                            WHERE estado=1 AND clasificacion LIKE '$term%'
                            ORDER BY clasificacion
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'principios_activos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_principio, nombre AS principio
                            FROM principios_activos
                            WHERE estado=1 AND nombre LIKE '$term%'
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'laboratorios':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_laboratorio, laboratorio
                            FROM laboratorios
                            WHERE estado=1 AND laboratorio LIKE '$term%'
                            ORDER BY laboratorio
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'presentaciones':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_presentacion, presentacion
                            FROM presentaciones
                            WHERE estado=1 AND presentacion LIKE '$term%'
                            ORDER BY presentacion
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'unidades_medidas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_unidad_medida, unidad_medida, sigla
                            FROM unidades_medidas
                            WHERE estado=1 AND unidad_medida LIKE '$term%'
                            ORDER BY unidad_medida
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'origenes':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_origen, origen
                            FROM origenes
                            WHERE estado=1 AND origen LIKE '$term%'
                            ORDER BY origen
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'marcas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_marca, marca
                            FROM marcas
                            WHERE estado=1 AND marca LIKE '$term%'
                            ORDER BY marca
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'rubros':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_rubro, rubro
                            FROM rubros
                            WHERE estado=1 AND rubro LIKE '$term%'
                            ORDER BY rubro
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'proveedores':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $tipo_proveedor=$db->clearText($_GET['tipo_proveedor']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_proveedor, proveedor, ruc, nombre_fantasia, contacto, telefono
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt ON p.id_proveedor=pt.id_proveedor
                            WHERE pt.tipo_proveedor=$tipo_proveedor AND proveedor LIKE '$term%'
                            GROUP BY p.id_proveedor
                            ORDER BY proveedor
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'proveedores_pral':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                            p.*,
                            pp.*
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt ON p.id_proveedor=pt.id_proveedor
                            LEFT JOIN productos_proveedores pp ON pp.id_proveedor=p.id_proveedor
                            WHERE pp.proveedor_principal = 1 AND pt.tipo_proveedor = 1 AND p.proveedor LIKE '$term%'
                            GROUP BY p.id_proveedor
                            ORDER BY p.proveedor
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'grupos_clasificaciones':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_grupo, grupo
                            FROM grupos
                            WHERE estado=1 AND grupo LIKE '$term%'
                            ORDER BY grupo
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'productos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, p.codigo,p.fraccion, p.precio, pre.id_presentacion, pre.presentacion, p.descuento_fraccionado
                            FROM productos p
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            WHERE p.estado=1 AND p.producto LIKE '$term%'
                            ORDER BY p.producto
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'sucursales':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET[$id]);
            $where = "";

            if (!empty($id)) {
                $where = "AND id_sucursal=$id";
            }

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS ruc, razon_social, id_sucursal, sucursal, direccion, id_sucursal, deposito
                            FROM sucursales
                            WHERE estado=1 AND sucursal LIKE '$term%' $where
                            ORDER BY nombre_empresa
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'depositos':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET[$id]);
            $where = "";

            if (!empty($id)) {
                $where = "AND id_sucursal=$id";
            }

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS ruc, razon_social, id_sucursal, sucursal, direccion 
                            FROM sucursales
                            WHERE estado=1 AND deposito=1 AND sucursal LIKE '$term%' $where
                            ORDER BY nombre_empresa
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;


		case 'clientes':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_cliente, CONCAT(ruc,' - ', razon_social) AS cliente FROM clientes WHERE estado = 1 ORDER BY cliente");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;

		case 'usuarios':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_usuario, nombre_usuario from usuarios order by 2");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'vendedoras_por_suc':
			$where="";
			if (isset($_REQUEST['id'])){
				$where = "AND id_sucursal=".$_REQUEST['id'];
			}
			$db = DataBase::conectar();
			//$db->setQuery("SELECT id, nombre_apellido FROM users WHERE (roles_mask='2' OR roles_mask='1') AND username !='admin' AND username !='vendedor' $where");
			$db->setQuery("SELECT id, nombre_apellido FROM users WHERE id_rol='2' AND username !='admin' AND username !='vendedor' $where");
			$rows = $db->loadObjectList();	
			echo json_encode($rows);
		break;
		
		case 'roles':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_rol, rol from roles where estado = 1 order by 2");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'menus':
			$db = DataBase::conectar();
			$db->setQuery("SELECT id_menu, CONCAT_WS('->',menu,submenu) as menu FROM menus WHERE estado=1 ORDER BY orden");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		break;
		
		case 'departamentos':
			$db = DataBase::conectar();
			$db->setQuery("SELECT departamento from departamentos where estado = 1 order by 1");
			$rows = $db->loadObjectList();
			echo json_encode($rows);
		
		break;
		
		case 'categorias':
			$db = DataBase::conectar();
			$db->setQuery("SELECT * FROM categorias ORDER BY categoria");
			/*$db->setQuery("SELECT a.id_categoria padre_id, a.categoria padre_cat, a.descripcion padre_desc,
							IFNULL(b.id_categoria,a.id_categoria) as hijo_id, IFNULL(b.categoria,a.categoria) hijo_cat, 
							b.descripcion hijo_desc
							FROM categorias a LEFT OUTER JOIN categorias b ON a.id_categoria = b.id_padre
							WHERE a.id_padre = 0 ORDER BY a.categoria, b.categoria");*/
			$rows = $db->loadObjectList();
			if ($rows) echo json_encode($rows);
		break;
		
		case 'familias':
			$db = DataBase::conectar();
			$buscar = trim($db->clearText($_GET['query']));
			$db->setQuery("SELECT * FROM categorias WHERE categoria LIKE '%$buscar%' ORDER BY categoria LIMIT 8");
			$rows = $db->loadObjectList();
			
			$suggestions = array();
			if ($rows){
				foreach ($rows AS $row) {
					$suggestions[] = array(
						"value" => trim($row->categoria),
						"data" => $row->id_categoria
					);
				}
			}
			$result = array(
				"query" => "Unit",
				"suggestions" => $suggestions
			);
			echo json_encode($result);
		break;

        case 'distritos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_distrito, nombre
                            FROM distritos
                            WHERE nombre LIKE '$term%'
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'puestos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_puesto, puesto
                            FROM puestos
                            WHERE puesto LIKE '$term%' AND estado=1
                            ORDER BY puesto
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'estado_civil':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_estado, descripcion
                            FROM estado_civil
                            WHERE descripcion LIKE '$term%'
                            ORDER BY descripcion
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

         case 'funcionarios':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_funcionario, funcionario, ci, nro_cuenta
                            FROM funcionarios
                            WHERE funcionario LIKE '$term%'
                            ORDER BY funcionario
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'buscar_funcionario':
            $db = DataBase::conectar();
            $ci = $db->clearText($_POST["ci"]);

            $db->setQuery("SELECT *, funcionario AS razon_social
                            FROM funcionarios
                            WHERE ci='$ci' AND estado = 1");
            $row = ($db->loadObject()) ?: ["ruc" => null];
            echo json_encode($row);
        break;  

        case 'periodo':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT
                                * FROM (
                                    (SELECT
                                        NULL AS select_periodo,
                                        CONCAT(CASE MONTH(CURRENT_DATE)
                                            WHEN 1 THEN 'ENERO'
                                            WHEN 2 THEN  'FEBRERO'
                                            WHEN 3 THEN 'MARZO' 
                                            WHEN 4 THEN 'ABRIL' 
                                            WHEN 5 THEN 'MAYO'
                                            WHEN 6 THEN 'JUNIO'
                                            WHEN 7 THEN 'JULIO'
                                            WHEN 8 THEN 'AGOSTO'
                                            WHEN 9 THEN 'SEPTIEMBRE'
                                            WHEN 10 THEN 'OCTUBRE'
                                            WHEN 11 THEN 'NOVIEMBRE'
                                            WHEN 12 THEN 'DICIEMBRE'
                                            END, ' - ', YEAR(CURRENT_DATE)) AS periodo
                                    )
                                    UNION ALL
                                    (SELECT 
                                        (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) AS select_periodo,
                                        CONCAT(CASE MONTH((SELECT select_periodo))
                                            WHEN 1 THEN 'ENERO'
                                            WHEN 2 THEN 'FEBRERO'
                                            WHEN 3 THEN 'MARZO' 
                                            WHEN 4 THEN 'ABRIL' 
                                            WHEN 5 THEN 'MAYO'
                                            WHEN 6 THEN 'JUNIO'
                                            WHEN 7 THEN 'JULIO'
                                            WHEN 8 THEN 'AGOSTO'
                                            WHEN 9 THEN 'SEPTIEMBRE'
                                            WHEN 10 THEN 'OCTUBRE'
                                            WHEN 11 THEN 'NOVIEMBRE'
                                            WHEN 12 THEN 'DICIEMBRE'
                                            END, ' - ', 
                                            YEAR((SELECT select_periodo))
                                            ) AS periodo
                                    )
                                ) AS fechas
                                WHERE periodo LIKE '$term%';");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break; 

        case 'periodo_anho':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT
                                * FROM (
                                    (SELECT
                                        NULL AS select_periodo,
                                        CONCAT(CASE MONTH(CURRENT_DATE)
                                            WHEN 1 THEN 'ENERO'
                                            WHEN 2 THEN  'FEBRERO'
                                            WHEN 3 THEN 'MARZO' 
                                            WHEN 4 THEN 'ABRIL' 
                                            WHEN 5 THEN 'MAYO'
                                            WHEN 6 THEN 'JUNIO'
                                            WHEN 7 THEN 'JULIO'
                                            WHEN 8 THEN 'AGOSTO'
                                            WHEN 9 THEN 'SEPTIEMBRE'
                                            WHEN 10 THEN 'OCTUBRE'
                                            WHEN 11 THEN 'NOVIEMBRE'
                                            WHEN 12 THEN 'DICIEMBRE'
                                            END, ' - ', YEAR(CURRENT_DATE)) AS periodo
                                    )
                                    UNION ALL
                                    (SELECT 
                                    (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) AS select_periodo,
                                    CONCAT(CASE MONTH((SELECT select_periodo))
                                        WHEN 1 THEN 'ENERO'
                                        WHEN 2 THEN 'FEBRERO'
                                        WHEN 3 THEN 'MARZO' 
                                        WHEN 4 THEN 'ABRIL' 
                                        WHEN 5 THEN 'MAYO'
                                        WHEN 6 THEN 'JUNIO'
                                        WHEN 7 THEN 'JULIO'
                                        WHEN 8 THEN 'AGOSTO'
                                        WHEN 9 THEN 'SEPTIEMBRE'
                                        WHEN 10 THEN 'OCTUBRE'
                                        WHEN 11 THEN 'NOVIEMBRE'
                                        WHEN 12 THEN 'DICIEMBRE'
                                        END, ' - ', 
                                        YEAR((SELECT select_periodo))
                                        ) AS periodo
                                    )
                                    UNION ALL
                                    (SELECT 
                                    (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH)) AS select_periodo,
                                    CONCAT(CASE MONTH((SELECT select_periodo))
                                        WHEN 1 THEN 'ENERO'
                                        WHEN 2 THEN 'FEBRERO'
                                        WHEN 3 THEN 'MARZO' 
                                        WHEN 4 THEN 'ABRIL' 
                                        WHEN 5 THEN 'MAYO'
                                        WHEN 6 THEN 'JUNIO'
                                        WHEN 7 THEN 'JULIO'
                                        WHEN 8 THEN 'AGOSTO'
                                        WHEN 9 THEN 'SEPTIEMBRE'
                                        WHEN 10 THEN 'OCTUBRE'
                                        WHEN 11 THEN 'NOVIEMBRE'
                                        WHEN 12 THEN 'DICIEMBRE'
                                        END, ' - ', 
                                        YEAR((SELECT select_periodo))
                                        ) AS periodo
                                    )
                                    UNION ALL
                                    (SELECT 
                                    (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)) AS select_periodo,
                                    CONCAT(CASE MONTH((SELECT select_periodo))
                                        WHEN 1 THEN 'ENERO'
                                        WHEN 2 THEN 'FEBRERO'
                                        WHEN 3 THEN 'MARZO' 
                                        WHEN 4 THEN 'ABRIL' 
                                        WHEN 5 THEN 'MAYO'
                                        WHEN 6 THEN 'JUNIO'
                                        WHEN 7 THEN 'JULIO'
                                        WHEN 8 THEN 'AGOSTO'
                                        WHEN 9 THEN 'SEPTIEMBRE'
                                        WHEN 10 THEN 'OCTUBRE'
                                        WHEN 11 THEN 'NOVIEMBRE'
                                        WHEN 12 THEN 'DICIEMBRE'
                                        END, ' - ', 
                                        YEAR((SELECT select_periodo))
                                        ) AS periodo
                                    )
                                    UNION ALL
                                    (SELECT 
                                    (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 4 MONTH)) AS select_periodo,
                                    CONCAT(CASE MONTH((SELECT select_periodo))
                                        WHEN 1 THEN 'ENERO'
                                        WHEN 2 THEN 'FEBRERO'
                                        WHEN 3 THEN 'MARZO' 
                                        WHEN 4 THEN 'ABRIL' 
                                        WHEN 5 THEN 'MAYO'
                                        WHEN 6 THEN 'JUNIO'
                                        WHEN 7 THEN 'JULIO'
                                        WHEN 8 THEN 'AGOSTO'
                                        WHEN 9 THEN 'SEPTIEMBRE'
                                        WHEN 10 THEN 'OCTUBRE'
                                        WHEN 11 THEN 'NOVIEMBRE'
                                        WHEN 12 THEN 'DICIEMBRE'
                                        END, ' - ', 
                                        YEAR((SELECT select_periodo))
                                        ) AS periodo
                                    )
                                    UNION ALL
                                    (SELECT 
                                    (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 5 MONTH)) AS select_periodo,
                                    CONCAT(CASE MONTH((SELECT select_periodo))
                                        WHEN 1 THEN 'ENERO'
                                        WHEN 2 THEN 'FEBRERO'
                                        WHEN 3 THEN 'MARZO' 
                                        WHEN 4 THEN 'ABRIL' 
                                        WHEN 5 THEN 'MAYO'
                                        WHEN 6 THEN 'JUNIO'
                                        WHEN 7 THEN 'JULIO'
                                        WHEN 8 THEN 'AGOSTO'
                                        WHEN 9 THEN 'SEPTIEMBRE'
                                        WHEN 10 THEN 'OCTUBRE'
                                        WHEN 11 THEN 'NOVIEMBRE'
                                        WHEN 12 THEN 'DICIEMBRE'
                                        END, ' - ', 
                                        YEAR((SELECT select_periodo))
                                        ) AS periodo
                                    )
                                    UNION ALL
                                    (SELECT 
                                    (SELECT DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)) AS select_periodo,
                                    CONCAT(CASE MONTH((SELECT select_periodo))
                                        WHEN 1 THEN 'ENERO'
                                        WHEN 2 THEN 'FEBRERO'
                                        WHEN 3 THEN 'MARZO' 
                                        WHEN 4 THEN 'ABRIL' 
                                        WHEN 5 THEN 'MAYO'
                                        WHEN 6 THEN 'JUNIO'
                                        WHEN 7 THEN 'JULIO'
                                        WHEN 8 THEN 'AGOSTO'
                                        WHEN 9 THEN 'SEPTIEMBRE'
                                        WHEN 10 THEN 'OCTUBRE'
                                        WHEN 11 THEN 'NOVIEMBRE'
                                        WHEN 12 THEN 'DICIEMBRE'
                                        END, ' - ', 
                                        YEAR((SELECT select_periodo))
                                        ) AS periodo
                                    )
                                ) AS fechas
                                WHERE periodo LIKE '$term%';");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'bancos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_banco, banco
                            FROM bancos
                            WHERE banco LIKE '$term%' AND estado=1
                            ORDER BY id_banco ASC
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break; 

        case 'cuentas-activas':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_cuenta, cuenta
                            FROM bancos_cuentas
                            WHERE cuenta LIKE '%$term%' AND estado=0 AND id_banco=$id
                            ORDER BY cuenta
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break; 

        case 'metodos_pagos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_metodo_pago, concat(orden,' - ',metodo_pago) as metodo_pago, entidad
                            FROM metodos_pagos
                            WHERE estado=1 AND concat(orden,' - ',metodo_pago) LIKE '$term%'
                            ORDER BY metodo_pago
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'ver_metodos_pagos':
            $db = database::conectar();
            $db->setquery("SELECT * FROM metodos_pagos WHERE estado=1");

            $rows = ($db->loadobjectlist()) ?: [];
            echo json_encode($rows);
        break;

        case 'ver_metodos_tarjetas':
            $db = database::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount;
            $db->setquery("SELECT  SQL_CALC_FOUND_ROWS * FROM metodos_pagos WHERE estado=1 AND id_metodo_pago IN (2,3) LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'buscar_producto_por_codigo':
            $db = DataBase::conectar();
            $codigo = $db->clearText($_POST["codigo"]);


            $db->setQuery("SELECT p.id_producto, p.producto, p.codigo, p.fraccion, p.precio, pre.id_presentacion, pre.presentacion
                            FROM productos p
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            WHERE p.estado=1 AND p.codigo=$codigo");
            $row = $db->loadObject();
            echo json_encode($row);
        break;

        case 'buscar_producto_por_codigo_insumo':
            $db = DataBase::conectar();
            $codigo = $db->clearText($_POST["codigo"]);

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                            id_producto_insumo,
                            producto,
                            costo,
                            codigo
                            FROM productos_insumo
                            WHERE estado=1 AND codigo=$codigo");
            $row = $db->loadObject();
            echo json_encode($row);
        break;

        case 'buscar_premio_por_codigo':
            $db = DataBase::conectar();
            $codigo = $db->clearText($_POST["codigo"]);

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                            id_premio,
                            premio,
                            costo,
                            codigo,
                            puntos
                            FROM premios
                            WHERE estado=1 AND codigo=$codigo");
            $row = $db->loadObject();
            echo json_encode($row);
        break;

        case 'lotes':
            $db = DataBase::conectar();
            $id_producto = $db->clearText($_GET["id_producto"]);

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT l.id_lote, l.lote, l.vencimiento
                            FROM lotes l
                            JOIN stock s ON l.id_lote=s.id_lote
                            WHERE s.id_producto=$id_producto AND l.lote LIKE '$term%'
                            GROUP BY l.id_lote
                            ORDER BY l.vencimiento DESC
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'clientes_tipos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_cliente_tipo, tipo
                            FROM clientes_tipos
                            WHERE tipo LIKE '$term%'
                            ORDER BY tipo
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'proveedores_modal':
            $db = DataBase::conectar();
            $producto = $db->clearText($_GET['producto']);
            $page     = $db->clearText($_GET['page']);
            $term     = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_proveedor, p.proveedor, pp.costo
                            FROM proveedores p
                            JOIN productos_proveedores pp ON p.id_proveedor=pp.id_proveedor
                            WHERE p.proveedor LIKE '$term%' AND pp.id_producto='$producto'
                            ORDER BY p.proveedor
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'usuarios_funcionarios':
            $db = DataBase::conectar();
            $page     = $db->clearText($_GET['page']);
            $term     = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS u.id, u.username
                            FROM users u
                            LEFT JOIN funcionarios f ON u.id=f.id_usuario
                            WHERE u.username LIKE '$term%' AND f.id_usuario IS NULL AND u.id != 1
                            ORDER BY u.username
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'usuarios_cajeros':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT id AS id_usuario, username AS nombre_usuario 
                            FROM users u
                            JOIN funcionarios f ON f.id_usuario = u.id
                            JOIN puestos p ON p.id_puesto = f.id_puesto
                            WHERE p.id_tipo_puesto = 1
                            AND username LIKE '$term%'
                            ORDER BY username
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'cajas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT id_caja, numero
                            FROM cajas
                            WHERE id_sucursal=$id_sucursal AND numero LIKE '$term%'
                            ORDER BY numero
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'entidades':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_entidad, entidad, tipo, CONCAT_WS(' - ', tipo, entidad) AS tipo_entidad
                                  FROM entidades
                                  WHERE estado=1 AND entidad LIKE '$term%'
                                  ORDER BY entidad
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'cajas-select-timbrado':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 
            
            $db->setQuery("SELECT * FROM cajas WHERE id_sucursal = $id
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'clientes-venta':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS * 
                            FROM clientes
                            WHERE razon_social LIKE '$term%'
                            ORDER BY razon_social
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'doctores':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS * 
                            FROM especialidades_doctores
                            WHERE nombre LIKE '$term%' AND estado=1
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'doctores_facturas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                            d.id_doctor, 
                            d.nombre_apellido, 
                            d.registro_nro, 
                            d.estado,
                            e.id_especialidad,
                            CASE d.estado WHEN 0 THEN 'Inactivo' WHEN 1 THEN 'Activo' END AS estado_str,
                            e.nombre AS especialidad
                            FROM doctores d
                            LEFT JOIN `especialidades_doctores` e ON e.id_especialidad = d.id_especialidad
                            WHERE d.estado=1
                            AND d.nombre_apellido LIKE '$term%'
                            ORDER BY d.nombre_apellido
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'tipos_puestos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_tipo_puesto, tipo_puesto 
                            FROM tipos_puestos 
                            WHERE tipo_puesto LIKE '$term%' 
                            ORDER BY tipo_puesto
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'tipos_comprobantes':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_tipo_comprobante, nombre_comprobante 
                            FROM tipos_comprobantes 
                            WHERE nombre_comprobante LIKE '$term%' 
                            ORDER BY nombre_comprobante
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'tipos_gastos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_tipo_gasto, nombre
                            FROM tipos_gastos
                            WHERE nombre LIKE '$term%' 
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'sub_tipos_gastos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_sub_tipo_gasto, nombre
                            FROM sub_tipos_gastos
                            WHERE nombre LIKE '$term%' 
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'proveedores-compras':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS ruc,razon_social AS proveedor 
                            FROM gastos 
                            WHERE razon_social LIKE '$term%' 
                            GROUP BY ruc
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'notas_remision_motivos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_nota_remision_motivo, descripcion, nombre_corto 
                            FROM notas_remision_motivos 
                            WHERE estado = 1 AND nombre_corto LIKE '$term%' 
                            ORDER BY id_nota_remision_motivo
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'proveedores-pagados':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery(" SELECT SQL_CALC_FOUND_ROWS p.ruc,p.proveedor
                            FROM recepciones_compras r
                            LEFT JOIN proveedores p ON p.id_proveedor = r.id_proveedor
                            WHERE proveedor LIKE '$term%' 
                            GROUP BY p.ruc
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'clientes-credito':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery(" SELECT SQL_CALC_FOUND_ROWS ruc,razon_social,id_cliente
                            FROM facturas 
                            WHERE razon_social LIKE '$term%' AND condicion = 2
                            GROUP BY ruc
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'entidades_descuento':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 
            
            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS e.id_entidad, e.entidad, IFNULL(dp.porcentaje,0) AS porcentaje,dp.descripcion, dp.id_descuento_pago
                                  FROM entidades e
                                  LEFT JOIN descuentos_pagos dp ON dp.id_entidad = e.id_entidad AND dp.estado = 1 AND dp.id_metodo_pago = $id
                                  WHERE e.estado=1  AND entidad LIKE '$term%'
                                  ORDER BY entidad
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'cajeros':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                f.id_funcionario,
                                f.funcionario

                            FROM tipos_puestos tp
                            LEFT JOIN puestos p ON tp.id_tipo_puesto=p.id_tipo_puesto
                            LEFT JOIN funcionarios f ON p.id_puesto=f.id_puesto
                            WHERE tp.id_tipo_puesto=1 AND funcionario LIKE '$term%'
                            ORDER BY funcionario
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        //Trae los cajeros de la sucursal en donde fue filtrado
        case 'cajeros_sucursal':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_sucursal = $db->clearText($_REQUEST['id_sucursal']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $where_sucursal = '';
            if(!empty($id_sucursal)  && intval($id_sucursal) != 0 ){
                $where_sucursal = " AND f.id_sucursal =  $id_sucursal " ;
            }

            $db->setQuery(" SELECT 
                                        p.id_puesto,
                                        p.puesto,
                                        tp.id_tipo_puesto,
                                        f.funcionario,
                                        f.id_funcionario,
                                        f.id_sucursal,
                                        f.usuario,
                                        u.username
                                        FROM puestos p
                                        LEFT JOIN tipos_puestos tp ON tp.id_tipo_puesto = p.id_tipo_puesto
                                        LEFT JOIN funcionarios f ON p.id_puesto = f.id_puesto
                                        JOIN users u ON f.id_usuario = u.id
                            WHERE tp.id_tipo_puesto = 1  $where_sucursal   AND f.funcionario LIKE '$term%'
                            ORDER BY funcionario
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'metodos_pagos_facturacion':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS mp.id_metodo_pago, CONCAT(mp.orden,' - ',mp.metodo_pago) AS metodo_pago, IFNULL(dp.porcentaje,0) AS porcentaje, mp.entidad, dp.descripcion, dp.id_descuento_pago
                            FROM metodos_pagos mp
                            LEFT JOIN descuentos_pagos dp ON dp.id_metodo_pago = mp.id_metodo_pago AND dp.estado = 1 AND dp.id_entidad IS NULL
                            WHERE mp.estado=1 AND concat(mp.orden,' - ',mp.metodo_pago) LIKE '$term%'
                            ORDER BY mp.id_metodo_pago ASC
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;
            
            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'productos-nota-remision':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_producto, p.producto, p.codigo, p.precio, pre.id_presentacion, pre.presentacion,
            (SELECT 
                             IFNULL(SUM(s.stock), 0)
                             FROM stock s 
                             JOIN lotes l ON s.id_lote=l.id_lote
                             WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id AND vencimiento>=CURRENT_DATE()
                            ) AS stock
                            FROM productos p
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            WHERE p.estado=1 AND p.producto LIKE '$term%'
                            ORDER BY p.producto
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'buscar_producto_por_codigo_nota_remision':
            $db = DataBase::conectar();
            $id = $db->clearText($_POST['id']);
            $codigo = $db->clearText($_POST["codigo"]);


            $db->setQuery("SELECT  p.id_producto, p.producto, p.codigo, p.precio, pre.id_presentacion, pre.presentacion,
            (SELECT 
                             IFNULL(SUM(s.stock), 0)
                             FROM stock s 
                             JOIN lotes l ON s.id_lote=l.id_lote
                             WHERE s.id_producto=p.id_producto AND s.id_sucursal=$id AND vencimiento>=CURRENT_DATE()
                            ) AS stock
                            FROM productos p
                            LEFT JOIN presentaciones pre ON p.id_presentacion=pre.id_presentacion
                            WHERE p.estado=1 AND p.codigo='$codigo'");
            $row = $db->loadObject();
            echo json_encode($row);
        break;

        case 'sucursales_descuento':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET['id']);
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS ruc, razon_social, id_sucursal, sucursal, direccion, id_sucursal
                            FROM sucursales
                            WHERE estado=1 AND  id_sucursal!=$id AND sucursal LIKE '$term%' $where
                            ORDER BY nombre_empresa
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'buscar_facturas':
            $db = DataBase::conectar();
            $numero = $db->clearText($_POST["numero"]);

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                            f.id_factura,
                            CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, f.numero) AS numero,
                            DATE_FORMAT(f.fecha_venta, '%d/%m/%Y %H:%i:%s') AS fecha_venta,
                            f.fecha_venta as fecha_ac,
                             CASE
                                WHEN f.fecha_venta >= NOW() - INTERVAL (SELECT periodo_devolucion FROM configuracion) DAY THEN 1
                                ELSE 2
                            END AS periodo,
                            f.ruc,
                            m.id_metodo_pago,
                            f.razon_social,
                            f.id_cliente,
                            f.total_venta,
                            s.id_sucursal,
                            s.sucursal
                            FROM facturas f
                            JOIN sucursales s ON f.id_sucursal=s.id_sucursal
                            JOIN timbrados t ON f.id_timbrado=t.id_timbrado
                            JOIN (SELECT 
                                    GROUP_CONCAT(' ',mp.metodo_pago) AS metodos,
                                    c.id_metodo_pago,
                                    c.id_factura
                                FROM cobros c
                                LEFT JOIN metodos_pagos mp ON c.id_metodo_pago=mp.id_metodo_pago
                                GROUP BY id_factura) m ON f.id_factura=m.id_factura
                            WHERE f.estado IN (0,1) AND CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, f.numero) = '$numero'");
            $row = ($db->loadObject()) ?: ["ruc" => null];
            echo json_encode($row);
        break;

        /*case 'proveedores_gastos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $tipo_proveedor=$db->clearText($_GET['tipo_proveedor']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_proveedor, proveedor, ruc, nombre_fantasia, contacto, telefono
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt ON p.id_proveedor=pt.id_proveedor
                            WHERE proveedor LIKE '$term%' AND pt.tipo_proveedor = $tipo_proveedor
                            GROUP BY p.id_proveedor
                            ORDER BY proveedor
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;*/

        case 'proveedores_gastos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_proveedor, proveedor,ruc
                            FROM proveedores 
                            WHERE proveedor LIKE '$term%' 
                            ORDER BY proveedor
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;


        
        case 'proveedores_gastos_orden_pago':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $tipo_proveedor=$db->clearText($_GET['tipo_proveedor']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_proveedor, proveedor, ruc, nombre_fantasia, contacto, telefono
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt ON p.id_proveedor=pt.id_proveedor
                            WHERE proveedor LIKE '$term%' AND pt.tipo_proveedor = $tipo_proveedor
                            GROUP BY p.id_proveedor
                            ORDER BY proveedor
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'buscar_proveedor_gasto':
            $db = DataBase::conectar();
            $ruc=$db->clearText($_POST['ruc']);
            $tipo_proveedor=$db->clearText($_POST['tipo_proveedor']);

            $db->setQuery("SELECT p.id_proveedor,
                            proveedor,
                            ruc
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt on p.id_proveedor=pt.id_proveedor
                            WHERE ruc='$ruc' 
                            AND pt.tipo_proveedor=$tipo_proveedor
                            GROUP BY p.id_proveedor");
            $row = ($db->loadObject()) ;
            echo json_encode($row);
        break;


        case 'tipos_gastos_fijos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_gasto_fijo_tipo, nombre
                            FROM gastos_fijos_tipos
                            WHERE estado= 1 AND nombre LIKE '$term%' 
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'sub_tipos_gastos_fijos_gastos_fijos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 
            $id=$db->clearText($_GET['id']);


            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_gasto_fijo_sub_tipo, nombre
                            FROM gastos_fijos_sub_tipos
                            WHERE estado= 1 AND id_gasto_fijo_tipo = $id AND nombre LIKE '$term%' 
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'tipo_insumo':             
            $db = DataBase::conectar();             
            $page = $db->clearText($_GET['page']);             
            $term = $db->clearText($_GET['term']);             
            $resultCount = 5;             
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS nombre, id_tipo_insumo                             
                            FROM tipos_insumos                             
                            WHERE estado=1 AND nombre LIKE '$term%'                            
                            ORDER BY nombre                             
                            LIMIT $end, $resultCount");             
            $rows = ($db->loadObjectList()) ?: [];             
            $db->setQuery("SELECT FOUND_ROWS() as total");             
            $total_row = $db->loadObject();             
            $total_count = $total_row->total;   

            if (empty($rows)) {                 
                $salida = ['data' => [], 'total_count' => 0];             
            } else {                 
                $salida = ['data' => $rows, 'total_count' => $total_count];             
            }              
            echo json_encode($salida);
        break;

        case 'proveedores_gastos_carga_insumo':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $tipo_proveedor=$db->clearText($_GET['tipo_proveedor']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS p.id_proveedor, proveedor, ruc, nombre_fantasia, contacto, telefono
                            FROM proveedores p
                            LEFT JOIN proveedores_tipos pt ON p.id_proveedor=pt.id_proveedor
                            WHERE proveedor LIKE '$term%' AND pt.tipo_proveedor = 2
                            GROUP BY p.id_proveedor
                            ORDER BY proveedor
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'usuarios_encargados_caja_chica':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 
            $id=$db->clearText($_GET['id']);


            $db->setQuery("SELECT id AS id_usuario, username AS nombre_usuario , f.funcionario, id_funcionario
                            FROM users u
                            JOIN funcionarios f ON f.id_usuario = u.id
                            JOIN puestos p ON p.id_puesto = f.id_puesto
                            WHERE p.id_tipo_puesto = 3 AND f.`id_sucursal` = $id 
                            AND username LIKE '$term%'
                            ORDER BY username
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'sucursales_caja_chica':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET[$id]);
            $where = "";

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                cc.id_sucursal,
                                s.`sucursal`
                            FROM
                                caja_chica cc
                            LEFT JOIN sucursales s ON s.`id_sucursal`=cc.`id_sucursal`
                            WHERE cc.`estado` = 1 AND sucursal LIKE '$term%' $where
                            ORDER BY id_sucursal
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'cajas_chicas_sucursal':
            $db = DataBase::conectar();
            $id = $db->clearText($_GET[$id]);
            $where = "";

            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 
            $id=$db->clearText($_GET['id']);

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS
                                ccs.`id_caja_chica_sucursal`,
                                ccs.`cod_movimiento`,
                                ccs.`saldo`,
                                ccs.`sobrante`,
                                ccs.`estado`,
                                CASE ccs.estado WHEN 1 THEN 'Abierto' WHEN 2 THEN 'Rendido' END AS estado_str
                            FROM
                                caja_chica_sucursal ccs
                            LEFT JOIN caja_chica cc ON cc.`id_caja_chica`= ccs.`id_caja_chica`
                            WHERE cc.`id_sucursal`= $id AND ccs.estado IN(1,2) AND cod_movimiento LIKE '$term%' $where
                            ORDER BY cod_movimiento DESC
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'cuentas':             
            $db = DataBase::conectar();             
            $page = $db->clearText($_GET['page']);             
            $term = $db->clearText($_GET['term']);   
            $id_banco = $db->clearText($_GET['id_banco']);

            $resultCount = 5;             
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS 
                                cuenta, 
                                id_cuenta                             
                            FROM bancos_cuentas                             
                            WHERE estado=0 AND id_banco=$id_banco
                            AND cuenta LIKE '$term%'                            
                            ORDER BY cuenta                             
                            LIMIT $end, $resultCount");             
            $rows = ($db->loadObjectList()) ?: [];             
            $db->setQuery("SELECT FOUND_ROWS() as total");             
            $total_row = $db->loadObject();             
            $total_count = $total_row->total;   

            if (empty($rows)) {                 
                $salida = ['data' => [], 'total_count' => 0];             
            } else {                 
                $salida = ['data' => $rows, 'total_count' => $total_count];             
            }              
            echo json_encode($salida);
        break;

        case 'productos_insumos_codigo':
            $db = DataBase::conectar();
            $codigo = $db->clearText($_POST['codigo']);

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS 
                                id_producto_insumo,
                                producto,
                                costo,
                                codigo,
                                (SELECT 
                                 IFNULL(SUM(s.stock), 0)
                                 FROM stock_insumos s 
                                 WHERE s.id_producto_insumo=p.id_producto_insumo AND (vencimiento>=CURRENT_DATE() OR vencimiento IS NULL)
                                ) AS stock
                            FROM productos_insumo p
                            WHERE estado=1 AND codigo='$codigo'");
            $row = $db->loadObject();
            echo json_encode($row);
        break;


        case 'gastos_fijos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $gasto = $db->clearText($_GET['tipo_gasto']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_gastos_fijos, nombre 
                            FROM gastos_fijos 
                            WHERE id_tipo_gastos =  $gasto and nombre LIKE '$term%' 
                            ORDER BY nombre
                            LIMIT $end, $resultCount");
            $rows = ($db->loadObjectList()) ?: [];

            $db->setQuery("SELECT FOUND_ROWS() as total");
            $total_row = $db->loadObject();
            $total_count = $total_row->total;

            if (empty($rows)) {
                $salida = ['data' => [], 'total_count' => 0];
            } else {
                $salida = ['data' => $rows, 'total_count' => $total_count];
            }

            echo json_encode($salida);
        break;

        case 'libros-diarios':             
            $db = DataBase::conectar();             
            $page = $db->clearText($_GET['page']);             
            $term = $db->clearText($_GET['term']);   

            $resultCount = 5;             
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS 
                                id_libro_diario_periodo, 
                                CONCAT(nombre, ' - (', DATE_FORMAT(desde, '%d/%m/%Y'), ' - ', DATE_FORMAT(hasta, '%d/%m/%Y'),')') AS libro
                            FROM
                                libro_diario_periodo                             
                            HAVING libro LIKE '$term%'                                                        
                            LIMIT $end, $resultCount");             
            $rows = ($db->loadObjectList()) ?: [];             
            $db->setQuery("SELECT FOUND_ROWS() as total");             
            $total_row = $db->loadObject();             
            $total_count = $total_row->total;   

            if (empty($rows)) {                 
                $salida = ['data' => [], 'total_count' => 0];             
            } else {                 
                $salida = ['data' => $rows, 'total_count' => $total_count];             
            }              
            echo json_encode($salida);
        break;

        case 'motivo-asiento':             
            $db = DataBase::conectar();             
            $page = $db->clearText($_GET['page']);             
            $term = $db->clearText($_GET['term']);   

            $resultCount = 5;             
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS 
                                id_motivo_asiento,
                                descripcion
                            FROM motivos_asiento                         
                            HAVING descripcion LIKE '$term%'                                                        
                            LIMIT $end, $resultCount");             
            $rows = ($db->loadObjectList()) ?: [];             
            $db->setQuery("SELECT FOUND_ROWS() as total");             
            $total_row = $db->loadObject();             
            $total_count = $total_row->total;   

            if (empty($rows)) {                 
                $salida = ['data' => [], 'total_count' => 0];             
            } else {                 
                $salida = ['data' => $rows, 'total_count' => $total_count];             
            }              
            echo json_encode($salida);
        break;

        case 'planes':             
            $db = DataBase::conectar();             
            $page = $db->clearText($_GET['page']);             
            $term = $db->clearText($_GET['term']);   

            $resultCount = 5;             
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT 
                                SQL_CALC_FOUND_ROWS 
                                id_libro_cuenta,
                                CONCAT(cuenta, ' - ',denominacion) AS denominacion,
                                tipo_cuenta
                            FROM
                                `libro_cuentas` lc
                            WHERE lc.`nivel` IS NULL
                            HAVING denominacion LIKE '$term%'                                                                           
                            LIMIT $end, $resultCount");             
            $rows = ($db->loadObjectList()) ?: [];             
            $db->setQuery("SELECT FOUND_ROWS() as total");             
            $total_row = $db->loadObject();             
            $total_count = $total_row->total;   

            if (empty($rows)) {                 
                $salida = ['data' => [], 'total_count' => 0];             
            } else {                 
                $salida = ['data' => $rows, 'total_count' => $total_count];             
            }              
            echo json_encode($salida);
        break;
	}

?>
