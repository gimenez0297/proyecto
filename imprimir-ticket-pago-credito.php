<?php
include("inc/funciones.php");
$db = DataBase::conectar();

$usuario = $auth->getUsername();
$id_recibo = $db->clearText($_REQUEST["id_recibo"]);
$id_usuario = $auth->getUserId();


// DATOS DEL RECIBO
$query = "SELECT r.*,
            c.razon_social,
            mp.metodo_pago,
            cb.id_factura,
            cb.id_sucursal
            FROM recibos r 
            LEFT JOIN clientes c ON c.id_cliente = r.id_cliente
            LEFT JOIN metodos_pagos mp ON mp.id_metodo_pago = r.id_metodo_pago
            LEFT JOIN cobros cb ON cb.id_recibo = r.id_recibo
            WHERE r.id_recibo = $id_recibo";
$db->setQuery("$query");
$recibo = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos del recibo";
    exit;
}

$id_sucursal = $recibo->id_sucursal;
$id_factura  = $recibo->id_factura;
$id_metodo_pago = $recibo->id_metodo_pago;
$concepto = $recibo->concepto;
$total_pago = $recibo->total_pago;

//DATOS DEL COBRO
$query = "SELECT *, DATE_FORMAT(fecha, '%d-%m-%Y') AS fecha_pago FROM cobros WHERE id_recibo = $id_recibo";
$db->setQuery("$query");
$cobro = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos del cobro";
    exit;
}

$fecha_pago = $cobro->fecha_pago;

//DATOS DEL METODO PAGO
$query = "SELECT * FROM metodos_pagos WHERE id_metodo_pago = $id_metodo_pago";
$db->setQuery("$query");
$pago = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos del metodo de pago";
    exit;
}

$metodo_pago = $pago->metodo_pago;
$cajero = $pago->usuario;

//DATOS DE LA SUCURSAL
$query = "SELECT * FROM sucursales WHERE id_sucursal = $id_sucursal";
$db->setQuery("$query");
$sucursal = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la sucursal";
    exit;
}

$nombre                = $sucursal->sucursal;
$direccion_empresa     = $sucursal->direccion;
$telefono_empresa      = $sucursal->telefono;

// DETALLES DE CASA MATRIZ
$db->setQuery("SELECT * FROM sucursales WHERE sucursal LIKE '%casa matriz%'");
$casa = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la casa matriz";
    exit;
}

$ruc_empresa           = $casa->ruc;
$direccion_casa_matriz = $casa->direccion;
$nombre_casa_matriz    = $casa->nombre_empresa;
$telefono_casa_matriz  = $casa->telefono;

//DATOS DE LA FACTURA
$query = "SELECT *, DATE_FORMAT(fecha_venta, '%d-%m-%Y') AS fecha FROM facturas WHERE id_factura = $id_factura";
$db->setQuery("$query");
$factura = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la factura";
    exit;
}

$condicion = 'CREDITO';
$id_timbrado  = $factura->id_timbrado;
$numero_factura = $factura->numero;
$fecha_venta = $factura->fecha;
$cliente = $factura->razon_social;
$ruc = $factura->ruc;

//DATOS DEL TIMBRADO
$query = "SELECT timbrado, 
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
            CASA MATRIZ: $direccion_casa_matriz
            <br>
            Tel: $telefono_casa_matriz
            <br>
            Sucursal: $nombre
            <br>
            $direccion_empresa
            </div>
        ";



$conceptos = '';
$i = 0;



$conceptos .= " 
                    <table  width='100%'>
                    <thead>
                        <tr>
                            <td class='tc-axmw' width='80%'> " . $concepto . "</td>
                            <td class='tc-axmw' style='text-align: left'  >" . separadorMiles($total_pago) . "</td>
                        </tr>
                    </thead>
                </table>
                <table width='100%'>
                    <thead>
                        <tr>
                            
                        </tr>
                    </thead>
                </table>   
                <br>
                    ";


?>

<style type='text/css'>
    @font-face {
        font-family: 'Inconsolata';
        font-style: normal;
        font-weight: 400;
        src: local('Inconsolata Regular'), local('Inconsolata-Regular'), url(dist/css/fonts/Inconsolata-Regular.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }

    body {
        font-family: Inconsolata;
        font-size: 14px;
    }

    /* Tabla cabecera */
    .tc {
        border-collapse: collapse;
        border-spacing: 0;
    }

    .tc td {
        border-color: black;
        font-family: Inconsolata, sans-serif;
        font-size: 14px;
        overflow: hidden;
        padding: 0px;
        word-break: normal;
    }

    .tc th {
        border-color: black;
        font-family: Inconsolata, sans-serif;
        font-size: 14px;
        font-weight: normal;
        overflow: hidden;
        padding: 0px;
        word-break: normal;
    }

    .tc .tc-axmw {
        font-size: 12px;
        text-align: center;
        vertical-align: middle
    }

    .tc .tc-lqfj {
        font-weight: bold;
        font-size: 12px;
        text-align: center;
        vertical-align: middle
    }

    .tc .tc-wa1i {
        font-size: 14px;
        text-align: left;
        vertical-align: middle;
        padding: 3px 2px;
    }

    .tc .tc-nrix {
        font-size: 10px;
        text-align: center;
        vertical-align: middle
    }

    .tc .tc-9q9o {
        font-size: 14px;
        text-align: center;
        vertical-align: top
    }

    .total {
        font-size: 16px;
        text-align: left;
        vertical-align: middle;
        padding: 3px 2px;
    }

    .tc-axmw-cantidad {
        font-size: 9px;
        padding-right: 1px;
    }

    /* Tabla footer */
    .tf {
        border-collapse: collapse;
        border-spacing: 0;
    }

    .tf td {
        border-color: black;
        font-family: Inconsolata, sans-serif;
        font-size: 14px;
        overflow: hidden;
        padding: 0px;
        word-break: normal;
    }

    .tf th {
        border-color: black;
        font-family: Inconsolata, sans-serif;
        font-size: 14px;
        font-weight: normal;
        overflow: hidden;
        padding: 0px;
        word-break: normal;
    }

    .tf .tf-crjr {
        font-weight: bold;
        text-align: left;
        vertical-align: middle;
    }

    .tf .tf-rt78 {
        font-weight: bold;
        text-align: right;
        vertical-align: middle;
    }

    /* Tabal contenido */
    .tg {
        border-collapse: collapse;
        border-spacing: 0;
        margin-top: 50px
    }

    .tg td {
        border-color: black;
        font-family: Inconsolata, sans-serif;
        font-size: 10px;
        overflow: hidden;
        padding: 5px 5px;
        word-break: normal;
    }

    .tg th {
        border-color: black;
        font-family: Inconsolata, sans-serif;
        font-size: 12px;
        font-weight: normal;
        overflow: hidden;
        padding: 5px 5px;
        word-break: normal;
    }

    .tg .tg-zqap {
        font-size: 12px;
        font-weight: bold;
        text-align: left;
        vertical-align: middle
    }

    .tg .tg-1x3m {
        text-align: left;
        vertical-align: middle
    }

    .tg .tg-wjt0 {
        text-align: center;
        vertical-align: middle;
    }

    @media print {
        .tg {
            transform: rotate(0.5deg);
        }

        .invi {
            visibility: hidden;
        }

        .noprint {
            color: white !important;
            border-color: white !important
        }

        .noprint_td {
            border: none !important
        }
    }
</style>


<?php

echo "
    <section width='100%'>

        $membrete
        FACTURA: $condicion&nbsp&nbsp; Pago:$metodo_pago
        <br>
        Timbrado: $timbrado
        <br>
        Valido desde: $ini_vigencia
        <br>
        Valido Hasta: $fin_vigencia
        <br>
        Factura Nro.: $cod_est-$punto_exp-$numero_factura
        <br>
        Fecha  de la Venta: $fecha_venta
        <br>
        Fecha  del Pago: $fecha_pago
        <br>
        Cajero: $cajero
        <br>
        Cliente: $cliente
        <br>
        R.U.C: $ruc

        <table width='100%'>
            <thead>
                <tr>
                    <td class='tc-axmw' >Concepto</td>
                    <td class='tc-axmw' width='50%' ></td>
                    <td class='tc-axmw' >Total</td>
                </tr>
            </thead>
        </table>

        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>
        $conceptos
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left; font-size:15px' width='50%'>TOTAL A PAGAR:</td>
                    <td class='tc-axmw' style='text-align: right'  width='30%'>" . separadorMiles($total_pago) . "</td>
                </tr>
            </thead>
        </table>
        <br>
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>

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