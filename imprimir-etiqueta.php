<?php
include "inc/funciones.php";
include "inc/barcode.php";
verificaLogin("");

$db = DataBase::conectar();

$symbology = "ean13";
$cantidad = $db->clearText($_POST['cantidad']) ?: 1;
$id_producto = $db->clearText($_POST['id_producto']);
$id_lote = $db->clearText($_POST['id_lote']);
$options = "";


// $db->setQuery("SELECT codigo, producto, precio FROM productos WHERE id_producto='$id_producto'");
// $row = $db->loadObject();
// if (empty($row)) {
// 	exit;
// }

$db->setQuery("SELECT
					s.id_lote,
					s.id_producto,
					l.lote, 
					DATE_FORMAT(l.vencimiento,' %d/%m/%Y ') AS vencimiento,
					p.producto, 
					p.codigo,
					p.precio
				FROM stock s
				LEFT JOIN lotes l ON l.id_lote = s.id_lote
				LEFT JOIN productos p ON p.id_producto = s.id_producto
				WHERE s.id_producto=$id_producto AND s.id_lote=$id_lote");
$row = $db->loadObject();
if (empty($row)) {
 	exit;
}

$codigo = $row->codigo;
$producto = strtoupper(substr($row->producto, 0, 20));
$lote = strtoupper(substr($row->lote, 0, 20));
$vencimiento = strtoupper(substr($row->vencimiento, 0, 20));
$precio = separadorMiles($row->precio);

/* Generate SVG markup. */
$options['w'] = '90';
$options['h'] = '30';
$options['pl'] = '0';
$options['pr'] = '7';
$options['pt'] = '0';
$options['th'] = '8';
$options['ts'] = '8';

$generator = new barcode_generator();
$svg = $generator->render_svg($symbology, $codigo, $options);
?>



<style type="text/css">
	html {
		margin: 0;
		padding: 0;
	}

	body {
		display: block;
		padding: 0;
		margin: 0;
	}

	.barcode {
		margin: 0;
	}

	.contenido {
		padding: 0;
		margin-left: 0px;
		margin-top: 8px;
		display: inline-block;
	}

	.titulo {
		font-weight: 700;
		text-align: center;
		font-family: Arial;
		font-size: 5pt;
		word-wrap: break-word;
		text-align-last: center;
		width: 100%;
		margin-left: 7px;
	}

	.codigo {
		font-weight: 600;
		text-align: center;
		font-family: Arial;
		font-size: 5pt;
		/* word-wrap: break-word; */
		text-align-last: center;
		/* width: 100%; */
		margin-left: 7px;
	}
	.producto {
		font-weight: 400;
		text-align: center;
		font-family: Arial;
		font-size: 4pt;
		word-wrap: break-word;
		/*word-break: break-all;*/
		text-align-last: center;
		width: 100%;
	}

	svg {
		margin-left: 17px;
		/* margin: auto; */
		text-align: center;
		height: 0.900cm;
		width: 3cm;
	}
</style>

<body>


<?php
for ($i = 1; $i <= $cantidad; $i++) {
    // Verificar si el contador es múltiplo de 3
  
    ?>
    <div class="contenido">
		<div class="titulo">
			<span><?php echo $lote; ?></span>
			<span><?php echo $vencimiento; ?></span>
		</div>
        <span class="barcode">
			<?php echo $svg; ?>
		</span>
		<div class="codigo">
            <span><?php echo $codigo; ?></span>
        </div>
        <div class="producto">
            <span><?php echo $producto; ?></span>
        </div>
    </div>
<?php
  	if ($i % 3 == 0) {
		echo '<p style="page-break-after: always;"></p>';
	}
}
?>

</body>

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

	window.onunload = focusParent;

	function focusParent() {
		window.opener.focus();
	}
</script>