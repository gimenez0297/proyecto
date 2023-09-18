<?php
require "inc/funciones.php";
require __DIR__.'/inc/auth/autoload.php';
$auth = new \Delight\Auth\Auth($db_auth);
$auth->logOut();
header ("Location: " . url());
