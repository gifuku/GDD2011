<?php
include_once(dirname(__FILE__)."/config.php");

ini_set("memory_limit", "2048M");

$objPuzzle = new CSlidePazzle(135);

if($objPuzzle){
	print $objPuzzle->startAnalysis();
}

?>