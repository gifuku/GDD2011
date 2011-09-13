<?php
include_once(dirname(__FILE__)."/config.php");

set_time_limit(0);

function createAnswer($inStr){
	$answerStrs = array("1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	$answer = array();
	$data = str_split($inStr);
	$strIndex = 0;
	foreach($data as $str){
		if($str == "="){
			$answer[] = "=";
			$strIndex++;
		}else{
			$answer[] = $answerStrs[$strIndex];
			$strIndex++;
		}
	}
	$answer[(count($answer)-1)] = "0";
	return implode("", $answer);
}


$objDb = new PDO(kDSN, kDbUser, kDbPass);


$fp = fopen(dirname(__FILE__)."/problems.txt", "r");
$limit = explode(" ", fgets($fp));
$problems = fgets($fp);
$lineNum = 1;
while(($data = fgetcsv($fp, 1024, ",")) !== false){
	$puzzleStr = $data[2];
	
	$insertArray = array(
		"pzlId"			=> $lineNum,
		"pzlRootId"		=> $lineNum,
		"pzlParentId"	=> 0,
		"pzlWidth"		=> $data[0],
		"pzlHeight"		=> $data[1],
		"pzlWalls"		=> substr_count($puzzleStr, "="),
		"pzlCurrent"	=> $puzzleStr,
		"pzlAnswer"		=> createAnswer($puzzleStr)
	);
	$objDb->exec(createInsertPhrase("tpuzzle", $insertArray));
	print createInsertPhrase("tpuzzle", $insertArray)."<br />\n";
	$lineNum++;
}

?>