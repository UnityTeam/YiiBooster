#!/usr/bin/php
<?php
if ($argc >= 2){
	$base = $argv[1];
}
else {
$base = '/var/www/rspace/www.clublaxmag.com/web/content/protected';
}
define('APP_BASEPATH', $base);
require_once(__DIR__ ."/TbTheme.php");

function run(){
	$t = new TbTheme();
	$t->run();
	return $t;
}

echo date("Y-m-d H:i:s") . "Starting to Build Themes \n";
run();
echo date("Y-m-d H:i:s") . " Done Building Themes....\n";


