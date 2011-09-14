<?php

ini_set('memory_limit', "2048M");


function process($inQuestion, $inDepth){
	$temp = array();
	foreach($inQuestion as $index => $questions){
		$divide = array();
		$loseFiveMulti = array();
		$min = 10;
		foreach($questions as $num){
			if($num%5!=0){
				$loseFiveMulti[] = $num;
			}
			$divide[] = floor($num/2);
		}
		if(count($loseFiveMulti) == 0){
			return $inDepth;
		}
		if(count($divide)!=count($loseFiveMulti)){
			$temp[] = $loseFiveMulti;
		}
		$temp[] = $divide;
	}
	unset($loseFiveMulti, $divide);
	return process($temp, $inDepth+1);
}



$fp = fopen("./data.txt", "r");

$question = array();
$answer = array();

$numofQ = fgets($fp);
for($i=0; $i<$numofQ*2; $i+=2){
	$numCount = fgets($fp);
	$q = fgets($fp);
	$question[] = explode(" ", $q);
}

fclose($fp);

unlink("answer.txt");
foreach($question as $index => $q){
	//$answer[] = process(array($q), 1);
	file_put_contents("answer.txt", process(array($q), 1)."\n", FILE_APPEND);
}

//file_put_contents("answer.txt", implode("\n", $answer)."\n");


?>
