<?php
include "../inc/funciones.php";

$db = DataBase::conectar();

$conenido = file_get_contents("clientes.csv");
$array = explode("\r", $conenido);
$cantidad = count($array);
$i = 0;
$duplicados = 0;
$omitidos = 0;

while ($i <= $cantidad) {
    $linea = $array[$i];
    $linear = explode(',', $linea);

    $id_distrito = 257; // SIN DATOS
    $razon_social = trim(utf8_encode($db->clearText($linear[5])));
    $direccion = utf8_encode($db->clearText($linear[6]));
    $telefono = utf8_encode($db->clearText($linear[7]));
    $celular = utf8_encode($db->clearText($linear[8]));
    $ruc = utf8_encode($db->clearText($linear[11]));
    $email = utf8_encode($db->clearText($linear[16]));
    $fecha = utf8_encode($db->clearText($linear[19]));
    $fecha_nacimiento = utf8_encode($db->clearText($linear[20]));
    $usuario = "admin";

    $buscar = buscar_ruc($ruc);

    if (isset($ruc) && !empty($ruc) && $buscar["ruc"] != "") {
        $ruc = $buscar["ruc"] . "-" . $buscar["dv"];
        $razon_social = $buscar["razon_social"];
        $telefono = $buscar["telefono"];
        $direccion = $buscar["direccion"];
    } else {
        $omitidos++;
        $i++;
        continue;
    }

    $query = "SELECT id_cliente FROM clientes WHERE ruc='$ruc'";
    $db->setQuery($query);
    $row = $db->loadObject();
    if (isset($row)) {
        $omitidos++;
        $i++;
        continue;
    }

    $query = "INSERT INTO clientes (`id_distrito`, `razon_social`, `direccion`, `telefono`, `celular`, `ruc`, `email`, `fecha`, `fecha_nacimiento`, `tipo`, `usuario`)
                VALUES ('$id_distrito','$razon_social','$direccion','$telefono','$celular','$ruc','$email','$fecha','$fecha_nacimiento','$tipo','$usuario')";

    $db->setQuery($query);
    if (!$db->alter()) {
        $error = "RUC: $ruc; Code: " . $code . ". ". $db->getError();
    } else {
        $id_registro = $db->getLastID();
    }

    echo "$i- RUC: $ruc; Razón Social: $razon_social - ID: $id_registro <br>";
    $i++;
}

echo "<br><br><br>Omitidos: $omitidos";

// BUSCAR RUC EN SET
function buscar_ruc($ruc_tmp) {
    //Si tiene guion, entonces sacamos
    if (stripos($ruc_tmp, "-") !== false) {
        $ruc_sin_dv_tmp = explode("-",$ruc_tmp);
        $ruc_tmp = $ruc_sin_dv_tmp[0];	
    }
    $url = "https://marangatu.set.gov.py/eset-restful/contribuyentes/consultar?ruc=$ruc_tmp&codigoEstablecimiento=001";
    $ch = curl_init();
    // Disable SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Will return the response, if false it print the response
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Set the url
    curl_setopt($ch, CURLOPT_URL,$url);
    // Execute
    $json=curl_exec($ch);
    // Closing
    curl_close($ch);

    $arr = json_decode($json);

    if ($arr->procesamientoCorrecto == "true") {
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
    return $salida;
}
		
// BUSCAR CI EN BASE DE DATOS DE CÉDULAS DE ÑAMANDU
function buscar_ci($ci) {
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
    return $datos;
}
