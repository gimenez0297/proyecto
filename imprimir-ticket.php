<?php
include("inc/funciones.php");
$db = DataBase::conectar();

$usuario = $auth->getUsername();
$id_factura = $db->clearText($_REQUEST["id_factura"]);
$id_usuario = $auth->getUserId();
$fecha_actual = date('Y/m/d H:i:s');

$query = "SELECT nombre_apellido, id_rol FROM users WHERE id=$id_usuario";
$db->setQuery("$query");
$user = $db->loadObject();

$empleado = $user->nombre_apellido;
$id_rol = $user ->id_rol;

if (empty($id_factura)) {
    echo "Factura no encontrada";
    exit;
}

$query = "SELECT impresiones FROM facturas WHERE id_factura = $id_factura";
$db->setQuery($query);
$impresiones = $db->loadObject()->impresiones + 1;
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la factura";
    exit;
}

if (esAdmin($id_rol) === false && intval($id_rol) != 9) {
    $query_factura = "SELECT MAX(id_factura) AS ultima_factura FROM facturas WHERE usuario ='$usuario'";
    $db->setQuery("$query_factura");
    $factura = $db->loadObject();
    if ($db->error()) {
        echo "Error de base de datos al recuperar los datos de la factura";
        exit;
    }

    $id_ultima_factura = $factura->ultima_factura;

    if ($id_factura != $id_ultima_factura || $impresiones > 2) {
        echo "No se puede volver a imprimir esta factura.";
        exit;
    }
}
if ($impresiones > 1) {
    $reimpreso = "
        Reimpreso
        <br>
        Fecha: $fecha_actual
        <br>
        Cajero: $usuario
    ";
}

// DATOS DE LA CABECERA DE LA FACTURA
$query = "SELECT 
            f.id_factura, 
            f.numero, 
            f.id_sucursal, 
            DATE_FORMAT(f.fecha,'%d/%m/%Y %H:%i:%s') AS fecha, 
            CASE f.condicion WHEN 1 THEN 'Contado' WHEN 2 THEN 'Crédito' END AS condicion_venta, 
            f.id_cliente, 
            f.ruc,
            f.id_timbrado,
            f.razon_social,  
            f.descuento, 
            f.exenta, 
            f.gravada_5, 
            f.gravada_10, 
            f.total_venta,
            f.delivery,
            f.id_delivery,
            fn.funcionario,
            fn.celular AS celular_delivery,
            c.telefono,
            cs.metodo_pago
            FROM facturas f 
            LEFT JOIN clientes c ON c.id_cliente=f.id_cliente 
            LEFT JOIN cobros cs ON cs.id_factura = f.id_factura
            LEFT JOIN funcionarios fn ON fn.id_funcionario = f.id_delivery
            WHERE f.id_factura=$id_factura";
$db->setQuery("$query");
$factura = $db->loadObject();

if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la factura";
    exit;
}


$id_sucursal = $factura->id_sucursal;
$id_timbrado = $factura->id_timbrado;


//DATOS DEL DESCUENTO POR METODO DE PAGO 
$query ="SELECT c.id_descuento_metodo_pago, c.monto, dp.descripcion
        FROM cobros c
        LEFT JOIN descuentos_pagos dp ON dp.id_descuento_pago = c.id_descuento_metodo_pago 
        WHERE id_factura =  $id_factura AND c.id_metodo_pago IS NULL";
$db->setQuery("$query");
$descuentos_pagos = $db->loadObjectList();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de los Descuentos";
    exit;
}

//DATOS DE LA SUCURSAL
$query = "SELECT * FROM sucursales WHERE id_sucursal = $id_sucursal";
$db->setQuery("$query");
$sucursal = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la sucursal";
    exit;
}
$numero_factura = $factura->numero;
$fecha = $factura->fecha;
$condicion = $factura->condicion_venta;
$ruc = $factura->ruc;
$razon_social = $factura->razon_social;
$direccion = $factura->direccion;
$telefono = $factura->telefono;
$exenta = ($factura->exenta) ?: 0;
$gravada_5 = ($factura->gravada_5) ?: 0;
$gravada_10 = ($factura->gravada_10) ?: 0;
$total_a_pagar = $factura->total_venta;
$descuento = $ft->descuento;
$descuento_fac = $factura->descuento;
$metodo_pago = $factura->metodo_pago;
$delivery = $factura->delivery;
$nombre_delivery = $factura->funcionario;
$telefono_delivery = $factura->celular_delivery;

if ($delivery == 1 && !empty($nombre_delivery)) {
    $delivery_if .=
    "
    <div style=''> 
    Delivery: $nombre_delivery
    <br>
    Nro.: $telefono_delivery
    </div>
    <br>
    <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
    <td>&nbsp;</td>
    </table>
    ";
}

if ($descuento_fac > 0) {
    $descuento_if .=
    "
    <div style=''> 
    Usted ahorró en esta compra: ".separadorMiles($descuento_fac)."
    </div>
    <br>
    <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
    <td>&nbsp;</td>
    </table>
    ";
}

// DETALLES DEL CONCEPTO
$db->setQuery("SELECT 
                        p.codigo, 
                        fd.producto,
                        fd.id_producto, 
                        SUM(fd.cantidad) AS cantidad, 
                        fd.precio, 
                        SUM(fd.descuento) AS descuento, 
                        fd.descuento_porc,
                        SUM(fd.total_venta) AS total_venta, 
                        fd.iva 
                    FROM facturas_productos fd
                    JOIN productos p ON fd.id_producto=p.id_producto
                    WHERE fd.id_factura=$id_factura
                    GROUP BY fd.id_producto");

$rows = $db->loadObjectList();
if ($db->error()) {
    echo "Error de base de datos al recuperar los productos de la factura";
    exit;
}

// DETALLES DE CASA MATRIZ
$db->setQuery("SELECT * FROM sucursales WHERE sucursal LIKE '%casa matriz%'");

$casa = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la Casa Matriz";
    exit;
}

$query= "SELECT timbrado, 
                cod_establecimiento, 
                punto_de_expedicion, 
                DATE_FORMAT(inicio_vigencia, '%d/%m/%Y') AS inicio_vigencia, 
                DATE_FORMAT(fin_vigencia, '%d/%m/%Y') AS fin_vigencia
                FROM timbrados
                WHERE id_timbrado = $id_timbrado";
$db->setQuery("$query");
$tim = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos del timbrado";
    exit;
}

$nombre                = strtoupper($sucursal->sucursal);
$ruc_empresa           = $casa->ruc;
$direccion_empresa     = $sucursal->direccion;
$telefono_empresa      = $sucursal->telefono;
$direccion_casa_matriz = $casa->direccion;
$nombre_casa_matriz    = $casa->nombre_empresa;
$telefono_casa_matriz  = $casa->telefono;

$timbrado     = $tim->timbrado;
$cod_est      = $tim->cod_establecimiento;
$punto_exp    = $tim->punto_de_expedicion;
$ini_vigencia = $tim->inicio_vigencia;
$fin_vigencia = $tim->fin_vigencia;


$membrete = "<div style='text-align:center'>
            $nombre_casa_matriz
            <br>
            R.U.C:$ruc_empresa
            <br>
            Tel: $telefono_casa_matriz
            <br>
            Suc.: $nombre
            <br>
            Dir.: $direccion_empresa
            </div>
        ";



$conceptos = '';
$descuentos = '';
$i = 0;

$total_iva_10 = 0;
$total_iva_5 = 0;
$total_exenta = 0;
foreach ($rows as $r) {
    $i++;
    $cantidad = $r->cantidad;
    $producto = $r->producto;
    $codigo   = $r->codigo;
    $descuento_producto = $r->descuento_porc;

    $precio = $r->precio;
    $iva_10 = '';
    $iva_5 = '';
    $exenta = '';
    $iva = $r->iva;
    $monto_iva = $r->total_venta + $r->descuento;

    $sub_total = $r->total_venta;

    switch ($iva) {
        case 0:
            $exenta = $monto_iva;
            $total_exenta += $monto_iva;
            break;
        case 5:
            $iva_5 = $monto_iva;
            $total_iva_5 += $monto_iva;
            break;
        case 10:
            $iva_10 = $monto_iva;
            $total_iva_10 += $monto_iva;
            break;
    }

    if ($gravada_10 > 0) {
        $liquidacion_10 = $gravada_10;
    }
    if ($gravada_5 > 0) {
        $liquidacion_5 = $gravada_5;
    }

    $liquidacion_iva_10 = round(($liquidacion_10 / 11), 0);
    $liquidacion_iva_5 = round(($liquidacion_5 / 21), 0);
    $total_liquidacion_iva = $liquidacion_iva_10 + $liquidacion_iva_5;

    $conceptos .= " 
                    <table  width='100%'>
                    <thead>
                        <tr>
                            <td class='tc-axmw' >".zerofill($codigo)." ".cortar_titulo($producto,35)."</td>
                        </tr>
                    </thead>
                </table>
                <table width='100%'>
                    <thead>
                        <tr>
                            <td class='tc-axmw' style='text-align: left'  width='10%'>$cantidad</td>
                            <td class='tc-axmw' style='text-align: left'  width='20%'>".separadorMiles($precio)."</td>
                            <td class='tc-axmw' style='text-align: right'  width='20%'>".$descuento_producto."%</td>
                            <td class='tc-axmw' style='text-align: right'  width='20%'>".$iva."%</td>
                            <td class='tc-axmw' style='text-align: right'  width='20%'>".separadorMiles($sub_total)."</td>
                        </tr>
                    </thead>
                </table>   
                <br>
                    ";
}

$i = 0;
foreach ($descuentos_pagos as $d){
    $i++;
    $descripcion = $d->descripcion;
    $monto = $d->monto;
    $porcentaje = $d->porcentaje;
    $id_descuento = $d->id_descuento_metodo_pago;

    $descuento .= " 
                    <table  width='100%'>
                    <thead>
                        <tr>
                            <td class='tc-axmw' >DESCUENTO</td>
                        </tr>
                    </thead>
                </table>
                <table width='100%'>
                    <thead>
                        <tr>
                            <td class='tc-axmw' style='text-align: left'  width='30%'></td>
                            <td class='tc-axmw' style='text-align: right'  width='30%'>".$porcentaje."%</td>
                            <td class='tc-axmw' style='text-align: right'  width='30%'>".separadorMiles($monto)."</td>
                        </tr>
                    </thead>
                </table>   
                <br>
                    ";
}

?>

<style type='text/css'>
/*    @font-face {
        font-family: 'Consolas';
        font-style: normal;
        font-weight: 400;
        src: local('Consolas Regular'), local('Consolas-Regular'), url(dist/css/fonts/Consolas-Regular.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }*/

    body {
        font-family: Consolas;
        font-size: 12.5px!important;
        margin-left:15px;
        padding-left:15px;
        margin-right:0px;
    }

    td, tr {
        font-size: 12px!important;

    }

    /* Tabla cabecera */
    .tc {border-collapse: collapse;border-spacing: 0;}
    .tc td {border-color: black;font-family: Consolas, sans-serif;overflow: hidden;padding: 0px;word-break: normal;}
    .tc th {border-color: black;font-family: Consolas, sans-serif;font-weight: normal;overflow: hidden;padding: 0px;word-break: normal;}
    .tc .tc-axmw {text-align: center;vertical-align: middle}
    .tc .tc-lqfj {font-weight: bold;text-align: center;vertical-align: middle}
    .tc .tc-wa1i {text-align: left;vertical-align: middle;padding: 3px 2px;}
    .tc .tc-nrix {text-align: center;vertical-align: middle}
    .tc .tc-9q9o {text-align: center;vertical-align: top}
    .total {text-align: left;vertical-align: middle;padding: 3px 2px;}
    .tc-axmw-cantidad{ padding-right: 1px;}

    /* Tabla footer */
    .tf {border-collapse: collapse;border-spacing: 0;}
    .tf td {border-color: black;font-family: Consolas, sans-serif;overflow: hidden;padding: 0px;word-break: normal;}
    .tf th {border-color: black;font-family: Consolas, sans-serif;font-weight: normal;overflow: hidden;padding: 0px;word-break: normal;}
    .tf .tf-crjr {font-weight: bold;text-align: left;vertical-align: middle;}
    .tf .tf-rt78 {font-weight: bold;text-align: right;vertical-align: middle;}

    /* Tabal contenido */
    .tg {border-collapse: collapse;border-spacing: 0;margin-top: 50px}
    .tg td {border-color: black;font-family: Consolas, sans-serif;overflow: hidden;padding: 5px 5px;word-break: normal;}
    .tg th {border-color: black;font-family: Consolas, sans-serif;font-weight: normal;overflow: hidden;padding: 5px 5px;word-break: normal;}
    .tg .tg-zqap {font-weight: bold;text-align: left;vertical-align: middle}
    .tg .tg-1x3m {text-align: left;vertical-align: middle}
    .tg .tg-wjt0 {text-align: center;vertical-align: middle;}

    @media print {
	/*.tg {transform: rotate(0.5deg);}*/
	.invi {visibility:hidden;}
	.noprint {color:white !important;border-color:white !important}
	.noprint_td {border:none!important}
}
</style>


<?php

echo "
    <section width='100%'>

        $membrete
        FACTURA: $condicion&nbsp&nbsp; Pago: $metodo_pago
        <br>
        Timbrado: $timbrado
        <br>
        Valido desde: $ini_vigencia
        <br>
        Valido Hasta: $fin_vigencia
        <br>
        Factura Nro.: $cod_est-$punto_exp-$numero_factura
        <br>
        Fecha: $fecha
        <br>
        Cajero: $empleado
        <br>
        Cliente: $razon_social
        <br>
        R.U.C: $ruc

        <table width='100%'>
            <thead>
                <tr>
                    <td class='tc-axmw' >Cant.</td>
                    <td class='tc-axmw' >Producto</td>
                    <td class='tc-axmw' >Descuento</td>
                    <td class='tc-axmw' >Imp.</td>
                    <td class='tc-axmw' >Total</td>
                </tr>
            </thead>
        </table>

        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>
        $conceptos
        $descuento
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left; font-size: 11px' width='50%'>TOTAL A PAGAR:</td>
                    <td class='tc-axmw' style='text-align: right'  width='30%'>".separadorMiles($total_a_pagar)."</td>
                </tr>
            </thead>
        </table>
        <br>
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left' >IVA 10%:&nbsp;</td>
                    <td class='tc-axmw' style='text-align:right; padding-right:20px ' >".separadorMiles($liquidacion_iva_10)."</td>
                </tr>
            </thead>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left' >IVA 5%:</td>
                    <td class='tc-axmw' style='text-align: right; padding-right:20px'  width='30%'>".separadorMiles($liquidacion_iva_5)."</td>
                </tr>
            </thead>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left'>Total IVA:</td>
                    <td class='tc-axmw' style='text-align:right; padding-right:20px'>".separadorMiles($total_liquidacion_iva)."</td>
                </tr>
            </thead>
        </table>
        <br>
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>
        $descuento_if
        $delivery_if
        $reimpreso
        <div style='text-align:center;'>Gracias por su visita.<br>Vuelva Pronto.</div>
    </section>

";

// Se actualiza el número de impresiones
$db->setQuery("UPDATE facturas SET impresiones=$impresiones, usuario_impresion='$usuario', fecha_impresion=NOW() WHERE id_factura=$id_factura");
if (!$db->alter()) {
    echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al actualizar la factura"]);
    exit;
}

?>

<script>
    var imprimir = '<?php echo $_REQUEST['imprimir']; ?>';
    var recargar = '<?php echo $_REQUEST['recargar']; ?>';
    if (imprimir == "si") {
        window.print();
        //window.close();
    }
    if (recargar == "si") {
        window.onunload = refreshParent;
        function refreshParent() {
            window.opener.location.reload();
        }
    }
</script>
