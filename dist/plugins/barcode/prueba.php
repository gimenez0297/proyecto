<?php
include 'barcode.php';

$generator = new barcode_generator();

/* Output directly to standard output. */
//$format = "png";
$symbology = "ean13nopad";
$data = "7811111171571";
$options = "";

/*$generator->output_image($format, $symbology, $data, $options);

$image = $generator->render_image($symbology, $data, $options);
imagepng($image);
imagedestroy($image);*/

/* Generate SVG markup. */
echo "<style>svg { width: 200px; height: 90px;} span{font-family:Arial;font-size:12px;margin-left:40px}</style>";
$svg = $generator->render_svg($symbology, $data, $options);
echo $svg . "<br><span> BLUSA JF333 NEGRA</span>";



?>

