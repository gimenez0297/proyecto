<?php
include "inc/funciones.php";
$db      = DataBase::conectar();
$usuario = $auth->getUsername();

$usuario       = $auth->getUsername();
$id_nota_credito = $db->clearText($_REQUEST["id"]);

if (empty($id_nota_credito)) {
    //header('Location: /');
    exit;
}

// DATOS DE LA CABECERA DE LA FACTURA
$query = "SELECT
                    n.numero, 
                    f.numero AS nro_factura, 
                    f.fecha AS fecha_factura, 
                    n.id_cliente, 
                    n.ruc, 
                    n.razon_social, 
                    CONCAT_WS(' / ',c.telefono, c.celular) AS telefono, 
                    CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, n.numero) AS numero_cred,
                    CONCAT_WS('-', t.cod_establecimiento, t.punto_de_expedicion, f.numero) AS nro_factura_origen,
                    n.total, 
                    f.exenta, 
                    f.gravada_5, 
                    f.gravada_10, 
                    n.id_sucursal,
                    n.fecha,
                    DATE_FORMAT(n.fecha,'%d/%m/%Y') AS fecha_format,
                    n.id_timbrado
                FROM notas_credito n 
                JOIN facturas f ON n.id_factura_origen=f.id_factura 
                LEFT JOIN clientes c ON c.id_cliente=n.id_cliente 
                LEFT JOIN timbrados t ON n.id_timbrado=t.id_timbrado 
                WHERE n.id_nota_credito=$id_nota_credito";
$db->setQuery("$query");
$rows = $db->loadObject();

if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la nota de crédito";
    exit;
}

$id_sucursal = $rows->id_sucursal;
$id_timbrado = $rows->id_timbrado;

//DATOS DE LA SUCURSAL
$query = "SELECT * FROM sucursales WHERE id_sucursal = $id_sucursal";
$db->setQuery("$query");
$sucursal = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la sucursal";
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
 
// DETALLES
$db->setQuery("SELECT 
                    ncp.id_producto,
                    ncp.producto,
                    p.codigo,
                    ncp.cantidad AS cantidad,
                    ncp.lote,
                    ROUND(ncp.total_venta/ncp.cantidad) AS precio,
                    ncp.total_venta
                FROM notas_credito_productos ncp
                LEFT JOIN productos p ON ncp.id_producto=p.id_producto
                WHERE ncp.id_nota_credito=$id_nota_credito
                ");

$rows_prod = $db->loadObjectList();

if ($db->error()) {
    echo "Error de base de datos al recuperar los productos de la factura";
    exit;
}

$fecha          = $rows->fecha_format;
$timbrado       = $tim->timbrado;
$val_desde      = $tim->inicio_vigencia;
$val_hasta      = $tim->fin_vigencia;
$nro_cred       = $rows->numero_cred;
$nro_factura    = $rows->nro_factura_origen;
$razon_social   = $rows->razon_social;
$ruc            = $rows->ruc;
$total_a_pagar  = $rows->total;

$exenta = ($rows->exenta) ?: 0;
$gravada_5 = ($rows->gravada_5) ?: 0;
$gravada_10 = ($rows->gravada_10) ?: 0;

// DETALLES DE CASA MATRIZ
$db->setQuery("SELECT * FROM sucursales WHERE sucursal LIKE '%casa matriz%'");

$casa = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los productos de la sucursal";
    exit;
}

$nombre                = $sucursal->sucursal;
$ruc_empresa           = $casa->ruc;
$direccion_empresa     = $sucursal->direccion;
$telefono_empresa      = $sucursal->telefono;
$direccion_casa_matriz = $casa->direccion;
$nombre_casa_matriz    = $casa->nombre_empresa;
$telefono_casa_matriz  = $casa->telefono;
$cod_est               = $tim->cod_establecimiento;;
$punto_exp             = $tim->punto_de_expedicion;
$empleado              = $razon_social;
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
            <br>
            <b>NOTA DE CRÉDITO</b>
            </div>
        ";

foreach ($rows_prod as $r) {
    $i++;
    $cantidad = $r->cantidad;
    $producto = $r->producto;
    $codigo   = $r->codigo;
    $precio   = $r->precio;
    $sub_total   = $r->total_venta;
    $lote   = $r->lote;

    $iva_10 = '';
    $iva_5 = '';
    $exenta = '';
    $iva = 10;
    $monto_iva = $r->total_venta;

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
        $liquidacion_10 = $sub_total;
    }
    if ($gravada_5 > 0) {
        $liquidacion_5 = $gravada_5;
    }

    $liquidacion_exenta = 0;
    $liquidacion_iva_10 = round(($total_a_pagar / 11), 0);
    $liquidacion_iva_5 = 0;
    $total_liquidacion_iva = $liquidacion_exenta + $liquidacion_iva_10 + $liquidacion_iva_5;

    $conceptos .= " 
                    <table  width='100%'>
                    <thead>
                        <tr>
                            <td class='tc-axmw' >".zerofill($codigo)." ".cortar_titulo($producto,30)."</td>
                        </tr>
                    </thead>
                </table>
                <table width='100%'>
                    <thead>
                        <tr>
                            <td class='tc-axmw' style='text-align: left'  width='10%'>$cantidad</td>
                            <td class='tc-axmw' style='text-align: left'  width='10%'>$lote</td>
                            <td class='tc-axmw' style='text-align: center'  width='400%'>".separadorMiles($precio)."</td>
                            <td class='tc-axmw' style='text-align: right'  width='20%'>".separadorMiles($sub_total)."</td>
                        </tr>
                    </thead>
                </table>   
                    ";
}

?>

<style type='text/css'>
    /*@font-face {
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
    .tg {transform: rotate(0.5deg);}
    .invi {visibility:hidden;}
    .noprint {color:white !important;border-color:white !important}
    .noprint_td {border:none!important}
}
</style>


<?php

echo "
    <section width='100%'>

        $membrete

        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
        <td></td>
        </table>

        FECHA: $fecha
        <br>
        TIMBRADO: $timbrado
        <br>
        VÁLIDO DESDE: $val_desde
        <br>
        VÁLIDO HASTA: $val_hasta
        <br>
        NRO.: $nro_cred
        <br>
        FACTURA NRO.: $nro_factura
        <br>
        NOMBRES: $razon_social
        <br>
        R.U.C/C.I: $ruc

        <table width='100%'>
            <thead>
                <tr>
                    <td class='tc-axmw' >Código</td>
                    <td class='tc-axmw' >Lote</td>
                    <td class='tc-axmw' >Cant.</td>
                    <td class='tc-axmw' >Producto</td>
                    <td class='tc-axmw' >Total</td>
                </tr>
            </thead>
        </table>

        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
        <td></td>
        </table>
        $conceptos
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
        <td></td>
        </table>
        <table width='100%' style='margin-bottom:0px'>
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left; font-size:15px' width='60%'>TOTAL:</td>
                    <td class='tc-axmw' style='text-align: right'  width='10%'>".separadorMiles($total_a_pagar)."</td>
                </tr>
            </thead>
        </table>
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
        <td></td>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left' >EXENTA:&nbsp;</td>
                    <td class='tc-axmw' style='text-align:right; padding-right:20px' width='20%'>".separadorMiles($liquidacion_exenta)."</td>
                </tr>
            </thead>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left' >IVA 10%:&nbsp;</td>
                    <td class='tc-axmw' style='text-align:right; padding-right:15px' width='20%'>".separadorMiles($liquidacion_iva_10)."</td>
                </tr>
            </thead>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left' >IVA 5%:</td>
                    <td class='tc-axmw' style='text-align: right; padding-right:20px'  width='20%'>".separadorMiles($liquidacion_iva_5)."</td>
                </tr>
            </thead>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left'>Total IVA:</td>
                    <td class='tc-axmw' style='text-align:right; padding-right:15px' width='20%'>".separadorMiles($total_liquidacion_iva)."</td>
                </tr>
            </thead>
        </table>
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td></td>
        </table>
        <div style='text-align:center;'>Original Cliente - Duplicado Arc.tribut.</div>
    </section>

"

?>

<script>
    var imprimir = '<?php echo $_REQUEST['imprimir']; ?>';
    var recargar = '<?php echo $_REQUEST['recargar']; ?>';
    if (imprimir == "si") {
        window.print();
        window.close();
    }
    if (recargar == "si") {
        window.onunload = refreshParent;
        function refreshParent() {
            window.opener.location.reload();
        }
    }
</script>
