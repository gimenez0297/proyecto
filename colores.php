<?php
	$primary = "#002653";
	$primary_hover = "#002653";
	
	/*LADY IN RED*/
	/*$primary = "#931a33";
	$primary_hover = "#cc2446";*/
	
	//Con transparencia a RGB para el focus
	list($r, $g, $b) = sscanf($primary, "#%02x%02x%02x");
	$focus = "0 0 0 2px rgba($r,$g,$b,0.3)";
	
	echo ":root {
	  --blue: #009efb;
	  --indigo: #6610f2;
	  --purple: #7460ee;
	  --pink: #e83e8c;
	  --red: #f62d51;
	  --orange: #fb9678;
	  --yellow: #ffbc34;
	  --green: #36bea6;
	  --teal: #20c997;
	  --cyan: #01c0c8;
	  --white: #fff;
	  --gray: #6c757d;
	  --gray-dark: #343a40;
	  --blue: #009efb;
	  --indigo: #6610f2;
	  --purple: #7460ee;
	  --pink: #e83e8c;
	  --red: #f62d51;
	  --orange: #fb9678;
	  --yellow: #ffbc34;
	  --green: #36bea6;
	  --teal: #20c997;
	  --cyan: #01c0c8;
	  --white: #fff;
	  --gray: #6c757d;
	  --secondary: #f8f9fa;
	  --success: #006666;
	  --success_hover: #004040;
	  --info: #2460a2;
	  --warning: #d16901;
	  --warning_hover: #a35201;
	  --danger: #c72344;
	  --light: #f8f9fa;
	  --dark: #343a40;
	  --cyan: #01c0c8;
	  --purple: #7460ee;
	  --primary: $primary;
	  --primary_hover: $primary_hover;
	  --focus: $focus;
	  --breakpoint-xs: 0;
	  --breakpoint-sm: 576px;
	  --breakpoint-md: 768px;
	  --breakpoint-lg: 992px;
	  --breakpoint-xl: 1600px;
	  --font-family-sans-serif: 'Rubik', sans-serif;
	  --font-family-monospace: SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
	}";

?>