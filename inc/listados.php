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
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

			$db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_departamento, nombre
                            FROM departamentos
                            WHERE nombre LIKE '$term%'
                            ORDER BY id_departamento
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
            $id_departamento = $db->clearText($_GET['id_departamento']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_distrito, nombre
                            FROM distritos
                            WHERE id_departamento = $id_departamento AND nombre LIKE '%$term%'
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
            $id_area = $db->clearText($_GET['id_area']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_funcionario, funcionario, ci
                            FROM funcionarios
                            WHERE estado = 1 AND id_area = $id_area AND funcionario LIKE '$term%'
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

            $db->setQuery("SELECT *, f.funcionario AS razon_social,
                                ROUND(f.salario / d.`factor`) AS impuesto
                            FROM funcionarios f
                            LEFT JOIN descuentos d ON f.vinculo = d.vinculo
                            WHERE ci='$ci' AND f.estado = 1");
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

        case 'area_superior':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_area AS id_area_superior, area AS area_superior 
                            FROM areas 
                            WHERE area LIKE '$term%' 
                            ORDER BY area
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

        case 'areas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_area, area
                            FROM areas 
                            WHERE estado = 1 AND area LIKE '$term%' 
                            ORDER BY area
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

        case 'categoria_superior':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_categoria AS id_categoria_superior, CONCAT(categoria,' - (',cargo,' - ',salario,')') AS categoria_superior 
                            FROM categorias 
                            WHERE categoria LIKE '$term%' 
                            ORDER BY categoria
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

        case 'tipo_organizacion':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_tipo, tipo
                            FROM tipo_organizacion
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

        case 'sectores':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_sector, sector
                            FROM sector
                            WHERE sector LIKE '$term%'
                            ORDER BY sector
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

        case 'jefe_organigrama':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_cargo AS id_jefe, cargo AS jefe
                            FROM organigrama
                            WHERE cargo LIKE '$term%'
                            ORDER BY cargo
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

        case 'grupo_sanguineo':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_grupo, grupo_sanguineo
                            FROM grupo_sanguineo
                            WHERE grupo_sanguineo LIKE '%$term%'
                            ORDER BY grupo_sanguineo
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

        case 'cargo_funcional':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_area = $db->clearText($_GET['id_area']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_cargo, cargo
                            FROM organigrama
                            WHERE id_area = $id_area AND estado = 1 AND cargo LIKE '%$term%'
                            ORDER BY cargo
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

        case 'distritos_contacto':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_distrito, nombre
                            FROM distritos
                            WHERE nombre LIKE '%$term%'
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

        case 'localidades':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_distrito = $db->clearText($_GET['id_distrito']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_localidad, localidad
                            FROM localidades
                            WHERE id_distrito = $id_distrito AND localidad LIKE '%$term%'
                            ORDER BY localidad
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

        case 'barrios':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_localidad = $db->clearText($_GET['id_localidad']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_barrio, barrio
                            FROM barrios
                            WHERE id_localidad = $id_localidad AND barrio LIKE '%$term%'
                            ORDER BY barrio
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

        case 'carreras':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_carrera, carrera
                            FROM carreras
                            WHERE carrera LIKE '%$term%'
                            ORDER BY carrera
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

        case 'universidades':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_organizacion, organizacion
                            FROM organizacion
                            WHERE id_tipo = 1 AND organizacion LIKE '%$term%'
                            ORDER BY organizacion
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

        case 'institutos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_organizacion, organizacion
                            FROM organizacion
                            WHERE id_tipo = 3 AND organizacion LIKE '%$term%'
                            ORDER BY organizacion
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

        case 'idiomas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_idioma, idioma
                            FROM idiomas
                            WHERE idioma LIKE '%$term%'
                            ORDER BY idioma
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

        case 'vinculos_familiares':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_vinculo_familiar, vinculo
                            FROM vinculos_familiares
                            WHERE estado = 1 AND vinculo LIKE '%$term%'
                            ORDER BY vinculo
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

        case 'funcionarios_select':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_funcionario, funcionario, ci, o.cargo
                            FROM funcionarios f 
                            LEFT JOIN organigrama o ON f.id_cargo=o.id_cargo
                            WHERE f.estado = 1 AND funcionario LIKE '$term%'
                            GROUP BY f.id_funcionario
                            ORDER BY f.funcionario
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

        case 'cargos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_servidor = $db->clearText($_GET['id_servidor']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS o.id_cargo, o.cargo
                            FROM organigrama o 
                            LEFT JOIN funcionarios f ON o.id_cargo=f.id_cargo
                            WHERE id_funcionario = $id_servidor AND cargo LIKE '%$term%'
                            GROUP BY o.id_cargo
                            ORDER BY o.cargo
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

        case 'objetos_gastos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_objeto_gasto, objeto_gasto
                            FROM objeto_gasto 
                            WHERE objeto_gasto LIKE '%$term%'
                            ORDER BY objeto_gasto
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

        case 'fuente_financiamiento':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_fuente_financiamiento, fuente
                            FROM fuente_financiamiento 
                            WHERE fuente LIKE '%$term%'
                            ORDER BY fuente
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

        case 'numero_resolucion':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_resolucion, numero
                            FROM resoluciones 
                            WHERE numero LIKE '%$term%'
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

        case 'categorias':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_categoria, categoria
                            FROM categorias 
                            WHERE categoria LIKE '%$term%'
                            ORDER BY categoria
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

        case 'nombramientos_servidor':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_area = $db->clearText($_GET['id_area']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS 
                                    f.id_funcionario, 
                                    f.funcionario, 
                                    f.ci, 
                                    f.fecha_ingreso, 
                                    f.fecha_asuncion, 
                                    o.cargo,
                                    CASE f.vinculo WHEN 1 THEN 'PERMANENTE' WHEN 2 THEN 'CONTRATADO' WHEN 3 THEN 'COMISIONADO' WHEN 4 THEN 'CONTRATADO POR PRODUCTO' END AS vinculo,
                                    TIMESTAMPDIFF(YEAR,fecha_ingreso,CURRENT_DATE()) AS anhos,
                                    MONTH(CURRENT_DATE())-MONTH(fecha_ingreso) AS meses,
                                    DAY(CURRENT_DATE())-DAY(fecha_ingreso) AS dias
                            FROM funcionarios f
                            LEFT JOIN organigrama o ON f.id_cargo = o.id_cargo
                            WHERE f.estado = 1 AND f.id_area = $id_area AND f.funcionario LIKE '$term%'
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

        case 'permisos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_permiso, concepto
                            FROM permisos 
                            WHERE concepto LIKE '%$term%'
                            ORDER BY concepto
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

        case 'organizaciones':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_organizacion, organizacion
                            FROM organizacion
                            WHERE estado = 1 AND organizacion LIKE '%$term%'
                            ORDER BY organizacion
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

        case 'organigramas':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_cargo, cargo
                            FROM organigrama
                            WHERE cargo LIKE '$term%'
                            ORDER BY cargo
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

        case 'funcionarios_cargos':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $id_cargo = $db->clearText($_GET['id_cargo']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_funcionario, funcionario
                            FROM funcionarios f 
                            WHERE id_cargo = $id_cargo AND f.estado = 1 AND funcionario LIKE '$term%'
                            GROUP BY f.id_funcionario
                            ORDER BY f.funcionario
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

        case 'instituciones':
            $db = DataBase::conectar();
            $page = $db->clearText($_GET['page']);
            $term = $db->clearText($_GET['term']);
            $resultCount = 5;
            $end = ($page - 1) * $resultCount; 

            $db->setQuery("SELECT SQL_CALC_FOUND_ROWS id_organizacion, organizacion
                            FROM organizacion
                            WHERE estado = 1 AND id_sector = 2 AND organizacion LIKE '%$term%'
                            ORDER BY organizacion
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
