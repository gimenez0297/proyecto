<?php
    include ("inc/funciones.php");
    verificaLogin();

	$db = DataBase::conectar();

	$usuario = $auth->getUsername();
	$id_factura = $db->clearText($_REQUEST['id_factura']);

	if (empty($id_factura)) {
		//header("Location: /");
		exit;
	}

	// DATOS DEL TIMBRADO
	// DATOS DE LA CABECERA DE LA FACTURA
    $query = "SELECT 
                f.id_factura, 
                f.numero, 
                f.id_sucursal, 
                f.fecha, 
                CASE f.condicion WHEN 1 THEN 'Contado' WHEN 2 THEN 'Crédito' END AS condicion_venta, 
                f.id_cliente, 
                f.ruc, 
                f.razon_social, 
                c.direccion, 
                f.descuento, 
                f.exenta, 
                f.gravada_5, 
                f.gravada_10, 
                f.total_venta, 
                c.telefono 
            FROM facturas f 
	        LEFT JOIN clientes c ON c.id_cliente=f.id_cliente 
            WHERE f.id_factura=$id_factura";
    $db->setQuery("$query");
    $factura = $db->loadObject();

    if ($db->error()) {
        echo "Error de base de datos al recuperar los datos de la factura";
        exit;
    }

    $id_sucursal = $factura->id_sucursal;

    $datosEmpresa = datosEmpresa($id_sucursal);
    $empresa = $datosEmpresa->razon_social;
    $logo = $datosEmpresa->logo;

    $numero_factura = $factura->numero;
    $fecha = fechaLatina($factura->fecha);
    $condicion = $factura->condicion;
    $ruc = $factura->ruc;
    $razon_social = $factura->razon_social;
    $direccion = $factura->direccion;
    $telefono = $factura->telefono;
    $exenta = ($factura->exenta) ?: 0;
    $gravada_5 = ($factura->gravada_5) ?: 0;
    $gravada_10 = ($factura->gravada_10) ?: 0;
    $total_a_pagar = $factura->total_venta;
    $descuento = $ft->descuento;

    //$timbrado = $ft->timbrado;
    //$cod_est = $ft->cod_establecimiento;
    //$punto_exp = $ft->punto_de_expedicion;
    //$ini_vigencia = fechaLatina($ft->inicio_vigencia);
    //$fin_vigencia = fechaLatina($ft->fin_vigencia);
    //$ruc_empresa = $ft->ruc_empresa;
    //$membrete = $ft->membrete;

    $timbrado = "14276178";
    $cod_est = "001";
    $punto_exp = "002";
    $ini_vigencia = "19/08/2020";
    $fin_vigencia = "31/08/2021";
    $ruc_empresa = "3179791-1";
    $membrete = ";
            <div class='imagen'>
                <img class='logo' src='dist/images/logo.png'>
            </div>
            <span class='titulo'>
                FARMACIA SANTA VICTORIA
            </span>
            <br>
            <p class='rubro'>
                <!-- Electricidad - Sanitarios - Pinturas
                <br>
                Herramientas - Artículos Varios
                <br> -->
            </p>
            <p class='direccion'>
                Dirección: R.I.4 Cucupayty Nº 815 c/ Pancha Garmendia
                <br>
                Asuncion - Paraguay / Tel: (021) 537 150 / Cel: (0985) 576 200 / E-mail: etca@live.com
                <br>
            </p>
    ";

    if ($gravada_10 > 0) {
        $liquidacion_10 = $gravada_10 - $descuento;
    }
    if ($gravada_5 > 0) {
        $liquidacion_5 = $gravada_5 - $descuento;
    }

    $liquidacion_iva_10 = round(($liquidacion_10/11),0);
    $liquidacion_iva_5 = round(($liquidacion_5/21),0);
    $total_liquidacion_iva = $liquidacion_iva_10 + $liquidacion_iva_5;

    if ($condicion == "Contado") {
        $x_contado = " <b>X</b>";
        $x_credito = "";
    }else{
        $x_credito = " <b>X</b>";
        $x_contado = "";
    }

    // MONTO TOTAL A LETRAS
    require("inc/numeros-letras.php");		
    $v = new EnLetras();
    $letras = strtoupper($v->ValorEnLetras($factura->total_venta,"GUARANÍES"));

    // DETALLES DEL CONCEPTO
    $db->setQuery("SELECT 
                        p.codigo, 
                        fd.producto, 
                        SUM(fd.cantidad) AS cantidad, 
                        SUM(fd.precio) AS precio, 
                        SUM(fd.descuento) AS descuento, 
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

    $conceptos = "";
    $i = 0;

	/*$total_descuento=0;
	foreach($rows as $r1){
		$producto = $r1->producto;
		
		if ($producto=="Descuento"){
			$total_venta = abs($r1->total_venta);
			$total_descuento += $total_venta;
		}
		
		
	}*/
	
    $total_iva_10 = 0;
    $total_iva_5 = 0;
    $total_exenta = 0;
    foreach($rows as $r) {
        $i++;
        $cantidad = $r->cantidad;
        $producto = $r->producto;

        if (strlen($producto) <= 58) {
            $producto_td = "<td class='tg-prod noprint_td'>$producto</td>";
        } else {
            $producto_td = "<td class='tg-prod noprint_td' style='line-height:9px !important'>$producto</td>";
        }

        $precio = $r->precio;
        $iva_10 = ""; $iva_5 = ""; $exenta = "";
        $iva = $r->iva;
        $monto_iva = $r->total_venta + $r->descuento;

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

        $conceptos .= " <tr style='height: 18px;'>
                            <td class='tg-cant noprint_td'>$cantidad</td>
                            $producto_td
                            <td class='tg-montos noprint_td'>".separadorMiles($precio)."</td>
                            <td class='tg-montos noprint_td'>".separadorMiles($exenta)."</td>
                            <td class='tg-montos noprint_td'>".separadorMiles($iva_5)."</td>
                            <td class='tg-montos noprint_td'>".separadorMiles($iva_10)."</td>
                          </tr>";
    }

    if ($monto_descuento > 0) { 
        $i++;
        $conceptos .= " <tr style='height: 18px;'>
                            <td class='tg-cant noprint_td'>1</td>
                            <td class='tg-prod noprint_td'>Descuento</td>
                            <td class='tg-montos noprint_td'>-".separadorMiles($monto_descuento)."</td>
                            <td class='tg-montos noprint_td'></td>
                            <td class='tg-montos noprint_td'></td>
                            <td class='tg-montos noprint_td'>-".separadorMiles($monto_descuento)."</td>
                        </tr>";
    }

    $filas = $i + 10;
    if ($i < 28) {
        $k = 28 - $i;
        for ($j=1; $j<=$k; $j++) {
            $filas++;
            $conceptos .= "<tr>
                            <td class='tg-cant noprint_td'>&nbsp;</td> 
                            <td class='tg-prod noprint_td'>&nbsp;<br></td>
                            <td class='tg-montos noprint_td'>&nbsp;<br></td>
                            <td class='tg-montos noprint_td'>&nbsp;<br></td>
                            <td class='tg-montos noprint_td'>&nbsp;<br></td>
                            <td class='tg-montos noprint_td'>&nbsp;<br></td>
                         </tr>";
        }
    }
?>

<style type="text/css">
@font-face {
  font-family: 'Inconsolata';
  font-style: normal;
  font-weight: 400;
  src: local('Inconsolata Regular'), local('Inconsolata-Regular'), url(dist/css/fonts/Inconsolata-Regular.woff2) format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:'Inconsolata';font-size:14px;padding:2px 4px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#777777;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:1px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#777777;}
.tg .tg-cant{font-size:12px;text-align:center;border-bottom:none;border-top:none}
.tg .tg-prod{font-size:12px;border-bottom:none;border-top:none}
.tg .tg-montos{font-size:12px;text-align:right;border-bottom:none;border-top:none}
.tg .tg-3ztj{font-size:11px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:center}
.tg .tg-t9wu{font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:center}
.tg .tg-a8ba{font-size:10px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:center;padding:0 !important}
.tg .tg-0ece{font-size:22px;font-family:Inconsolata, Helvetica, sans-serif !important;;border-color:#777777;text-align:center;padding:0}
.tg .tg-wgsn{font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:left;vertical-align:top;padding:7px}
.tg .tg-lct8{font-family:Arial; font-size:xx-small;border-color:#777777;text-align:right;vertical-align:top}
.tg .tg-nb5i{font-weight:bold;font-size:13px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:center}
.tg .tg-qx1n{font-size:9px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:center}
.tg .tg-wdtj{font-size:11px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:left}
.tg .tg-0vz4{font-family:Arial, Helvetica, sans-serif !important;border-color:#777777;text-align:left}
.tg .tg-zo6p{font-size:12px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:right}
.tg .tg-1xtd{font-size:12px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:center;vertical-align:top}
.tg .tg-i21v{font-size:12px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:left;vertical-align:top}
.tg .tg-bma0{font-size:12px;font-family:Arial, Helvetica, sans-serif !important;;border-color:#777777;text-align:right;vertical-align:top}
.condicion{font-size:15px;text-align:right;position:absolute;padding-left:4px !important;line-height:12px !important}
.condicion_cred{font-size:15px;text-align:right;position:absolute;padding-left:10px !important;line-height:12px !important}
.imagen { float: left;} 
.logo { height: 70px; margin:0px 5px 0px 0px; width: auto; position:absolute}
.logo_lady { height: 90px; margin:0px 5px 0px 20px; width: auto; position:absolute}
.span_totaliva{ margin-left:112px; }
.span_iva10{ margin-left:112px; }
.titulo{font-family:'Times New Roman';font-style:italic;font-size:18px;text-align:center;margin-left:150px;}
.rubro {font-family:'Arial';font-style:italic;font-size:12px;margin:5px 0 0 150px;text-align:center;line-height:14px}
.direccion {font-family:'Arial';font-size:10px;margin-left:180px;text-align:center;line-height:12px}
.nt {border-top:none !important}
.nb {border-bottom:none !important}
.nlr {border-left:none !important; border-right: none !important}
.mono{font-family:Inconsolata}

@media print {
	.tg {transform: rotate(0.5deg);}
	.invi {visibility:hidden;}
	.noprint {color:white !important;border-color:white !important}
	.noprint_td {border:none!important}
}
 
 .tg .tg-numero{font-size:8px;font-family:Arial!important;text-align:right;margin:0!important;padding:0px 0 0 0!important;line-height:8px!important}

</style>


<?php
$i=1;
$pie = "Original: Cliente";
while ($i<=2){
	echo "
	<table class='tg' style='table-layout: fixed; width: 720px;margin-bottom:87px;margin-top:21px;margin-left:30px'>
	<colgroup>
	<col style='width: 61px'>
	<col style='width: 315px'>
	<col style='width: 85px'>
	<col style='width: 85px'>
	<col style='width: 85px'>
	<col style='width: 90px'>
	</colgroup>
	  <tr class='invi'>
        <td class='noprint tg-3ztj nb' colspan='3' rowspan='6'>$membrete</td>
		<td class='noprint tg-t9wu nb' colspan='3'>TIMBRADO Nº.: $timbrado</td>
	  </tr>
	  <tr>
		<td class='noprint tg-nb5i nt nb' colspan='3'>R.U.C.: $ruc_empresa</td>
	  </tr>
	  <tr>
		<td class='noprint tg-3ztj nt nb' colspan='3'>Fecha Inicio Vigencia: $ini_vigencia<br>Fecha Fin Vigencia: $fin_vigencia</td>
	  </tr>
	  <tr>
		<td class='noprint tg-qx1n nt nb' colspan='3'>FACTURA</td>
	  </tr>
	  <tr>
		<td class='tg-numero nt nb nlr' colspan='3'>$nro_ft</td>
	  </tr>
	  <tr>
		<td class='noprint tg-0ece nt' colspan='3'>$cod_est-$punto_exp-$numero_factura</td>
	  </tr>
	  <tr>
		<td class='noprint tg-wgsn nlr' colspan='6'></td>
	  </tr>
	  <tr>
		<td class='noprint_td tg-wdtj' colspan='2'><span class='invi'>FECHA DE EMISIÓN:</span> &nbsp;$fecha</td>
		<td class='noprint_td tg-wdtj' colspan='4'><span class='invi'>CONDICIÓN DE VENTA:  CONTADO</span> <span class='condicion'> &nbsp;$x_contado</span><span style='margin-left:100px'><span class='invi'>CRÉDITO</span></span> <span class='condicion_cred'> &nbsp;$x_credito</span></td>
	  </tr>
	  <tr>
		<td class='noprint_td tg-wdtj' colspan='3'><span class='invi'>RUC / CI:</span> &nbsp;&nbsp;$ruc</td>
		<td class='noprint tg-wdtj' colspan='3'>NOTA DE REMISIÓN N°</td>
	  </tr>
	  <tr>
		<td class='tg-wdtj noprint_td' colspan='6'><span class='invi'>NOMBRE O RAZÓN SOCIAL:</span> &nbsp;&nbsp;$razon_social</td>
	  </tr>
	  <tr>
		<td class='tg-wdtj noprint_td' colspan='3'><span class='invi'>DIRECCIÓN: </span>&nbsp;$direccion</td>
		<td class='tg-wdtj noprint_td' colspan='3'><span class='invi'>TELÉFONO: </span>&nbsp;$telefono</td>
	  </tr>
	  <tr>
		<td class='tg-0vz4 nlr noprint' colspan='6'></td>
	  </tr>
	  <tr>
		<td class='tg-3ztj noprint' rowspan='2'>CANT.</td>
		<td class='tg-3ztj noprint' rowspan='2'>DESCRIPCIÓN DE MERCADERÍAS Y/O SERVICIOS</td>
		<td class='tg-3ztj noprint' rowspan='2'>PRECIO<br>UNITARIO</td>
		<td class='tg-3ztj noprint' colspan='3'>VALOR DE VENTA</td>
	  </tr>
	  <tr>
		<td class='tg-3ztj noprint'>EXENTAS</td>
		<td class='tg-3ztj noprint'>5%</td>
		<td class='tg-3ztj noprint'>10%</td>
	  </tr>
	 $conceptos
	  <tr><td class='nlr nt nb'></td></tr>
	  <tr>
		<td class='tg-wdtj noprint' colspan='3'>SUB-TOTALES:</td>
		<td class='tg-zo6p noprint'></td>
		<td class='tg-zo6p noprint'></td>
		<td class='tg-zo6p noprint_td'><span class='mono'>&nbsp;&nbsp;".separadorMiles($total_a_pagar)."</span><br></td>
	  </tr>
	  <tr>
		<td class='tg-wdtj noprint_td' colspan='5'><span class='invi'>TOTAL A PAGAR: </span><span class='mono'>&nbsp;&nbsp;$letras</span></td>
		<td class='tg-zo6p noprint_td' rowspan='2'><span class='mono'>&nbsp;&nbsp;".separadorMiles($total_a_pagar)."</span></td>
	  </tr>
	  <tr>
		<td class='tg-wdtj noprint_td' colspan='5'><span class='invi'>LIQUIDACIÓN DEL IVA: (5%)</span> <span class='mono'>&nbsp;&nbsp;".separadorMiles($liquidacion_iva_5).".-</span><span class='span_iva10'><span class='invi'>(10%) </span><span class='mono'>&nbsp;&nbsp;".separadorMiles($liquidacion_iva_10).".-</span></span><span class='span_totaliva'><span class='invi'>TOTAL IVA:</span> <span class='mono'>&nbsp;&nbsp;".separadorMiles($total_liquidacion_iva).".-</span></span></td>
	  </tr>
	  <tr>
		<td class='tg-lct8 noprint' colspan='6'>$pie<br></td>
	  </tr>
	</table>";
	$i++;
	$pie = "Duplicado: Arch. Tributario";
}
?>
<script>
    var imprimir = '<?php echo $_REQUEST['imprimir']; ?>';
    var recargar = '<?php echo $_REQUEST['recargar']; ?>';
    if (imprimir == "si") {
        window.print();
    }
    if (recargar == "si") {
        window.onunload = refreshParent;
        function refreshParent() {
            window.opener.location.reload();
        }
    }
</script>
