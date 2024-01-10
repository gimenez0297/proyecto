<?php
include "inc/funciones.php";
$db      = DataBase::conectar();
$usuario = $auth->getUsername();

$usuario       = $auth->getUsername();
$id_extraccion = $db->clearText($_REQUEST["id_extraccion"]);

if (empty($id_extraccion)) {
    //header('Location: /');
    exit;
}

// DATOS DE LA CABECERA DE LA FACTURA
$query = "SELECT SQL_CALC_FOUND_ROWS
                e.id_extraccion,
                e.usuario,
                e.monto_extraccion,
                DATE_FORMAT(e.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
                f.`funcionario`,
                e.observacion,
                c.id_caja,
                e.total_caja_efectivo,
                e.monto_sin_extraer,
                c.id_sucursal,
                c.numero AS caja
            FROM cajas_extracciones e
            LEFT JOIN users u ON e.usuario=u.username
            LEFT JOIN funcionarios f ON u.id=f.id_usuario
            LEFT JOIN cajas_horarios ch ON e.id_caja_horario=ch.id_caja_horario
            LEFT JOIN cajas c ON ch.id_caja=c.id_caja
            LEFT JOIN sucursales s ON c.id_sucursal=s.id_sucursal
            WHERE e.id_extraccion=$id_extraccion";
$db->setQuery("$query");
$extra = $db->loadObject();

if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la extraccion";
    exit;
}

$id_sucursal = $extra->id_sucursal;

//DATOS DE LA SUCURSAL
$query = "SELECT * FROM sucursales WHERE id_sucursal = $id_sucursal";
$db->setQuery("$query");
$sucursal = $db->loadObject();
if ($db->error()) {
    echo "Error de base de datos al recuperar los datos de la sucursal";
    exit;
}
$numero         = $extra->id_extraccion;
$fecha          = $extra->fecha;
$razon_social   = $extra->funcionario;
$total_a_pagar  = $extra->monto_extraccion;
$caja           = $extra->caja;
$total_efectivo = $extra->total_caja_efectivo;
$sin_extraer    = $extra->monto_sin_extraer;

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
$cod_est               = '001';
$punto_exp             = '002';
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
            </div>
        ";

?>

<style type='text/css'>
   /* @font-face {
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
        <br>
        Comprobante Nro.: $numero
        <br>
        Fecha: $fecha
        <br>
        Caja Nro.: $caja
        <br>
        Cajero: $empleado
        <br>
        R.U.C: $ruc

        
        <table width='100%' >
            <thead>
                <tr>
                    <td class='tc-axmw' style='text-align:left; font-size:15px' width='50%'>EXTRACCIÓN:</td>
                    <td class='tc-axmw' style='text-align: right'  width='30%'>" . separadorMiles($total_a_pagar) . "</td>
                </tr>
            </thead>
        </table>
        <br>
        <table width='100%' style='border-style: dashed; border-top-width: 1px; border-bottom-width:0 ;border-left-width: 0;border-right-width: 0;'>
            <td>&nbsp;</td>
        </table>

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