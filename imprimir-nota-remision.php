<?php
	include ("inc/funciones.php");
	verificaLogin();
	$usuario = $auth->getUsername();

	$db = DataBase::conectar();

	$id_nota_remision = $db->clearText($_REQUEST['id_nota_remision']);
	if (empty($id_nota_remision)) {
		echo "Nota de Remisión no encontrada.";
		exit;
	}

	//$id_sucursal = datosUsuario($usuario)->id_sucursal;
	
	//$empresa = datosEmpresa($usuario)->razon_social;
	//$logo = datosEmpresa($usuario)->logo;
	
	$db->setQuery("SELECT
                    id_nota_remision,
                    id_timbrado,
                    id_sucursal_origen,
                    id_sucursal_destino,
                    numero,
                    fecha_emision,
                    ruc_rtte,
                    razon_social_rtte,
                    domicilio_rtte,
                    ruc_destino,
                    razon_social_destino,
                    domicilio_destino,
                    id_nota_remision_motivo,
                    motivo,
                    comprobante_venta,
                    comprobante_nro,
                    comprobante_timbrado,
                    fecha_expedicion,
                    fecha_inicio,
                    fecha_fin,
                    km,
                    marca_vehiculo,
                    rua,
                    rua_remolque,
                    ruc_chofer,
                    razon_social_chofer,
                    domicilio_chofer,
                    observacion,
                    tipo_remision
                FROM notas_remision
                WHERE id_nota_remision=$id_nota_remision");
	$row = $db->loadObject();
	
	$id_timbrado = $row->id_timbrado;
	
	$numero = $row->numero;
	$fecha_emision = fechaLatina($row->fecha_emision);
	
	$ruc_rtte = $row->ruc_rtte;
	$razon_social_rtte = $row->razon_social_rtte;
	$domicilio_rtte = $row->domicilio_rtte;
	
	$ruc_destino = $row->ruc_destino;
	$razon_social_destino = $row->razon_social_destino;
	$domicilio_destino = $row->domicilio_destino;
	$motivo = $row->motivo;
	$comprobante_venta = $row->comprobante_venta ?: "<br>";
	$comprobante_nro = zerofill($row->comprobante_nro) ?: "<br>";
	$comprobante_timbrado = $row->comprobante_timbrado ?: "<br>";
	$row->fecha_expedicion=="0000-00-00" ? $fecha_expedicion = "<br>" : $fecha_expedicion = fechaLatina($row->fecha_expedicion);
	$fecha_inicio = fechaLatina($row->fecha_inicio);
	$fecha_fin = fechaLatina($row->fecha_fin);
	$km = $row->km ?: "<br>";
	$marca_vehiculo = $row->marca_vehiculo;
	$rua = $row->rua;
	$rua_remolque = $row->rua_remolque ?: "<br>";
	$ruc_chofer = $row->ruc_chofer ?: "<br>";
	$razon_social_chofer = $row->razon_social_chofer ?: "<br>";
	$domicilio_chofer = $row->domicilio_chofer ?: "<br>";
    $tipo_remision = $row->tipo_remision;
	
	// DATOS DEL TIMBRADO
	$db->setQuery("SELECT * FROM timbrados WHERE id_timbrado=$id_timbrado");
	$tim = $db->loadObject();
	$timbrado = $tim->timbrado;
	$cod_est = $tim->cod_establecimiento;
	$punto_exp = $tim->punto_de_expedicion;
	$ini_vigencia = fechaLatina($tim->inicio_vigencia);
	$fin_vigencia = fechaLatina($tim->fin_vigencia);
	$ruc_empresa = $tim->ruc;
	$membrete = $tim->membrete;

	//MONTO TOTAL A LETRAS
	// require("inc/numeros-letras.php");		
	// $v = new EnLetras();
	// $letras = strtoupper($v->ValorEnLetras($total,"GUARANÍES"));


	// DETALLES DEL CONCEPTO

    $conceptos = "";
    $i = 0;
    $total_cantidad = 0;

    if($tipo_remision == 0){
        $db->setQuery("SELECT
                        @row_coun := @row_coun + 1 AS orden,
                        nrp.id_nota_remision_producto,
                        nrp.id_nota_remision,
                        nrp.id_producto,
                        nrp.codigo,
                        nrp.producto,
                        p.presentacion,
                        SUM(nrp.cantidad) AS cantidad,
                        nrp.lote
                    FROM (SELECT @row_coun := 0) row_coun, notas_remision_productos nrp
                    JOIN productos pro ON pro.id_producto = nrp.id_producto
                    JOIN presentaciones p ON p.id_presentacion = pro.id_presentacion
                    WHERE nrp.id_nota_remision=$id_nota_remision
                    GROUP BY nrp.id_lote
                    order by nrp.id_nota_remision_producto asc
                    ");
        $rows = $db->loadObjectList();

        foreach($rows as $r) {
            $i++;
            $id_producto = $r->id_producto;
            $codigo = $r->codigo;
            $cantidad = $r->cantidad;
            $producto = $r->producto;
            $presentacion = $r->presentacion;
            $lote = $r->lote;
            $total_cantidad += $cantidad;
            $conceptos .= "
                <tr style='height: 15px'>
                    <td class='noprint_td tg-azew'>$lote</td>
                    <td class='noprint_td tg-azew'>$codigo</td>
                    <td class='noprint_td tg-azew'>$cantidad</td>
                    <td class='noprint_td tg-t41y' colspan='4'>$producto ($presentacion)</td>
                </tr>
            ";
        }

        if ($i < 45) {
            $k = 45 - $i;
            for ($j = 0; $j < $k; $j++) {
                $conceptos .= "
                    <tr style='height: 15px'>
                        <td class='noprint_td tg-azew'>&nbsp;<br></td>
                        <td class='noprint_td tg-azew'>&nbsp;<br></td>
                        <td class='noprint_td tg-azew'>&nbsp;<br></td>
                        <td class='noprint_td tg-t41y' colspan='4'>&nbsp;<br></td>
                    </tr>
                ";
            }
        }
    }else{
        $db->setQuery("SELECT
                        id_nota_remision_insumo,
                        id_nota_remision,
                        id_producto_insumo,
                        codigo,
                        producto,
                        cantidad
                    FROM notas_remision_insumo
                    WHERE id_nota_remision=$id_nota_remision");
        $rows = $db->loadObjectList();

        foreach($rows as $r) {
            $i++;
            $id_producto = $r->id_producto_insumo;
            $codigo = $r->codigo;
            $cantidad = $r->cantidad;
            $producto = $r->producto;
            $total_cantidad += $cantidad;
            $conceptos .= "
                <tr style='height: 15px'>
                    <td class='noprint_td tg-azew'>-</td>
                    <td class='noprint_td tg-azew'>$codigo</td>
                    <td class='noprint_td tg-azew'>$cantidad</td>
                    <td class='noprint_td tg-t41y' colspan='4'>$producto</td>
                </tr>
            ";
        }

        if ($i < 45) {
            $k = 45 - $i;
            for ($j = 0; $j < $k; $j++) {
                $conceptos .= "
                    <tr style='height: 15px'>
                        <td class='noprint_td tg-azew'>&nbsp;<br></td>
                        <td class='noprint_td tg-azew'>&nbsp;<br></td>
                        <td class='noprint_td tg-azew'>&nbsp;<br></td>
                        <td class='noprint_td tg-t41y' colspan='4'>&nbsp;<br></td>
                    </tr>
                ";
            }
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
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:1px 2px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:0px 2px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
.tg .tg-doeh{font-weight:bold;font-size:10px;border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-arzr{font-weight:bold;font-size:10px;border-color:inherit;text-align:center;vertical-align:middle}
.tg .tg-pie{padding:0 2px 0 !important;border:none!important;font-weight:normal;font-size:8px;border-color:inherit;text-align:right;vertical-align:middle}
.tg .tg-2h92{font-weight:bold;font-size:11px;border-color:inherit;text-align:center;vertical-align:middle}
.tg .tg-c3ow{font-weight:bold;font-size:11px;border-color:inherit;text-align:left;vertical-align:top;padding:1px}
.tg .tg-azew{font-size:10px;border-color:inherit;text-align:center;vertical-align:middle}
.tg .tg-4jjf{font-style:italic;font-size:12px;border-color:inherit;text-align:center;vertical-align:middle}
.tg .tg-t41y{font-size:10px;border-color:inherit;text-align:left;vertical-align:middle}
.nota{font-weight:bold;font-size:16px;font-family:Arial, sans-serif !important;text-align:center}
.nro{font-weight:normal;font-size:18px;font-family:Inconsolata, Helvetica, sans-serif !important;text-align:center}
.titulo{font-family:'Times New Roman';font-style:italic;font-size:13px;text-align:center;margin-left:90px;}
.rubro {font-family:'Arial';font-style:italic;font-size:11px;margin:5px 0 0 90px;text-align:center;line-height:8px}
.direccion {font-family:'Arial';font-size:9px;margin-left:90px;text-align:center;line-height:12px}
.nt {border-top:none !important}
.nb {border-bottom:none !important}
.nl {border-left:none !important;}
.nr {border-right:none !important;}
.nlr {border-left:none !important; border-right: none !important}
.imagen { float: left; } 
/* .logo { height: 60px; margin:0px 5px 0px 5px; width: auto; position:absolute} */
.logo { height: 60px; margin:0px 5px 0px -25px; width: auto }
.mono{font-family:Inconsolata}

@media print {
	.saltopage{break-after: page;}
}

</style>

<?php 
$i = 1;
$tr_invi = '';
$margin = "margin-top:10px;";
$margin_left = "margin-left:30px;";
while ($i <= 2) {
    echo "
    <table class='tg saltopage' style='undefined;table-layout: fixed; width: 710px; $margin_left $margin'>
        <colgroup>
            <col style='width: 105px'>
            <col style='width: 80px'>
            <col style='width: 138px'>
            <col style='width: 110px'>
            <col style='width: 133px'>
            <col style='width: 144px'>
        </colgroup>
        $tr_invi
        <tr class='invi'>
            <th class='noprint tg-4jjf' colspan='4'>$membrete</th>
            <th class='noprint tg-doeh' colspan='2'>
                TIMBRADO Nº.: $timbrado<br>
                Válido desde el $ini_vigencia hasta el $fin_vigencia<br>
                RUC: $ruc_rtte<br>
                <span class='nota'>NOTA DE REMISIÓN</span><br>
                <span class='nro'>$cod_est-$punto_exp-$numero</span>
            </th>
        </tr>
        <tr>
            <td class='noprint_td tg-c3ow nlr' colspan='6'></td>
        </tr>
        <tr>
            <td class='noprint_td tg-t41y'><span class='invi'>Fecha de emisión:</span><br><b>$fecha_emision</b></td>
            <td class='noprint_td tg-t41y' colspan='2'><span class='invi'>Nombre o Razón Social Remitente:</span> <br><b>$razon_social_rtte</b></td>
            <td class='noprint_td tg-t41y'><span class='invi'>RUC ó CI Ret:</span> <b>$ruc_rtte</b></td>
            <td class='noprint_td tg-t41y' colspan='2'><span class='invi' style='margin-right: 15px;'>Domicilio Ret.:</span><b>$domicilio_rtte</b></td>
        </tr>
        <tr>
            <td class='noprint_td tg-t41y'><span class='invi'>Motivo:</span> <br><b>$motivo</b></td>
            <td class='noprint_td tg-t41y' colspan='2 style='line-height: 7px;'><span class='invi'>Nom. o Razón Social Dest.:</span> <b>$razon_social_destino</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>RUC ó CI Dest.:</span> <br><b>$ruc_destino</b></td>
            <td class='noprint_td tg-t41y' colspan='2'><span class='invi' style='margin-right: 15px;'>Domicilio Dest.:</span> <b>$domicilio_destino</b><br></td>
        </tr>

          <tr>
            <td class='noprint_td tg-t41y'><span class='invi'>Comp. de Venta:</span> <b>$comprobante_venta</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Comp. Vta N°:</span> <b>$comprobante_nro</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Número de timbrado:</span> <b>$comprobante_timbrado</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Fecha Expedición:</span> <b>$fecha_expedicion</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Fecha de inicio del traslado:</span><br><b>$fecha_inicio</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Fecha estimada fin del traslado:</span><br><b>$fecha_fin</b><br></td>
          </tr>
          <tr>
            <td class='noprint_td tg-t41y' colspan='3'><span class='invi'>Direc. del punto de partida:</span> <b>$domicilio_rtte</b></td>
            <td class='noprint_td tg-t41y' colspan='2'><span class='invi'>Direc. del punto de llegada:</span> <b>$domicilio_destino</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Kilómetros de recorrido:</span><br> <b>$km</b></td>
          </tr>
          <tr>
            <td class='noprint_td tg-t41y' colspan='4'><span class='invi'>Cambio de fecha de término de traslado y/o punto de llegada:</span><br></td>
            <td class='noprint_td tg-t41y' colspan='2'><span class='invi'>Motivo:</span></td>
          </tr>

          <tr>
            <td class='noprint_td tg-t41y'><span class='invi'>Marca del Vehículo:</span><br><b>$marca_vehiculo</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Nº RUA:</span><br><b>$rua</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>Nº RUA Remolque/otros:</span> <br><b>$rua_remolque</b></td>
            <td class='noprint_td tg-t41y' colspan='2'><span class='invi'>Nombre o Razón Social del Conductor:</span> <b>$razon_social_chofer</b><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>RUC ó CI del Conductor:</span><br><b>$ruc_chofer</b></td>
          </tr>
          <tr>
            <td class='noprint_td tg-t41y' colspan='3'><span class='invi'>Agente Intermediario de transporte (Cuando intervenga)<br>Nombre o Razón Social:</span><br></td>
            <td class='noprint_td tg-t41y'><span class='invi'>RUC:</span><br><br></td>
            <td class='noprint_td tg-t41y' colspan='2'><span class='invi'>Domicilio:</span><br><br></td>
          </tr>
          <tr>
            <td class='noprint_td tg-c3ow nlr' colspan='6'></td>
          </tr>
         
          <tr>
            <td class='noprint_td tg-2h92'><span class='invi'>Lote<br></span></td>
            <td class='noprint_td tg-2h92'><span class='invi'>Artículo / Código<br></span></td>
            <td class='noprint_td tg-2h92'><span class='invi'>Cantidad</span></td>
            <td class='noprint_td tg-2h92' colspan='4'><span class='invi'>Descripción detallada de la Mercadería<br></span></td>
          </tr>
          $conceptos
          <tr>
            <td class='noprint_td tg-azew'><b><span class='invi'>TOTALES</span></b></td>
            <td class='noprint_td tg-t41y' colspan='1'>&nbsp;<br></td>
            <td class='noprint_td tg-azew'><b>$total_cantidad</b></td>
            <td class='noprint_td tg-t41y' colspan='4'>&nbsp;<br></td>
          </tr>
          <tr>
            <td class='noprint_td tg-arzr' colspan='6'><span class='invi'>RECEPCIÓN DE LAS MERCADERÍAS<br></span></td>
          </tr>
          <tr>
            <td class='noprint_td tg-c3ow nr' colspan='2'><span class='invi'>Firma:</span> </td>
            <td class='noprint_td tg-c3ow nl nr' colspan='2'><span class='invi'>Aclaración:</span> </td>
            <td class='noprint_td tg-c3ow nl' colspan='4'><span class='invi'>RUC/Cédula de Identidad:</span> </td>
          </tr>
          <tr>
            <td class='noprint_td tg-pie' colspan='6'><span class='invi'>Original: Destinatario de las mercaderías | Duplicado: Remitente de las mercaderías | Triplicado: Administración Tributaria | Cuadruplicado: Transportista</span></td>
          </tr>
    </table>
        ";
    $i++;
    $margin = "margin-top:23px;";
    $tr_invi = "<tr>
                  <td class='noprint_td tg-t41y' style='padding: 10px' colspan='6'></td>
              </tr>";
}

?>
<script>
	var imprimir='<?php echo $_REQUEST['imprimir']; ?>';
	var recargar='<?php echo $_REQUEST['recargar']; ?>';
	if (imprimir=="si"){
		window.print();
	}
	if (recargar=="si"){
		window.onunload = refreshParent;
		function refreshParent() {
			window.opener.location.reload();
		}
	}
</script>
