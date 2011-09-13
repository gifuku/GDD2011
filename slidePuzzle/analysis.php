<?php
include_once(dirname(__FILE__)."/config.php");
//ini_set("memory_limit", "2048M");
ini_set("memory_limit", "3072M");
//ini_set("memory_limit", -1);


$objDb = new PDO(kDSN, kDbUser, kDbPass);
//$sql = "SELECT pzlId FROM tpuzzle WHERE pzlHeight=3 AND pzlWidth<=4 AND pzlWalls=0 AND pzlProcess='' ORDER BY pzlWidth ASC";
//$sql = "SELECT pzlId FROM tpuzzle WHERE pzlWidth<=4 AND pzlHeight<=4 AND pzlProcess='' ORDER BY pzlWidth ASC, pzlHeight ASC";
$sql = "SELECT pzlId, pzlWidth+pzlHeight as dim FROM tpuzzle WHERE pzlProcess='' AND pzlWidth+pzlHeight<=8 ORDER BY dim ASC";
//$sql = "SELECT pzlId, pzlWidth+pzlHeight as dim FROM tpuzzle WHERE pzlProcess='' ORDER BY dim ASC, pzlWalls DESC";
//$sql = "SELECT pzlId, pzlWidth+pzlHeight as dim FROM tpuzzle WHERE pzlProcess='' ORDER BY dim ASC";
//$sql = "SELECT pzlId FROM tpuzzle WHERE pzlId=174";

/*
$rs = $objDb->query($sql);
while($row = $rs->fetch()){
	$objPuzzle = new CSlidePazzle();
	if($objPuzzle->setPuzzleId($row["pzlId"])){
		$objPuzzle->startAnalysis();
	}
	unset($objPuzzle);
}
*/

$depthLimit = array(70, 80, 100);
foreach($depthLimit as $limit){
	print "###### Depth limit set to ".$limit." ######\n\n\n";
	$rs = $objDb->query($sql);
	while($row = $rs->fetch()){
		$objPuzzle = new CSlidePazzle();
		$objPuzzle->setDepthLimit($limit);
		if($objPuzzle->setPuzzleId($row["pzlId"])){
			$sql = 'UPDATE tpuzzle SET pzlProcessDate="'.date("YmdHis").'" WHERE pzlId='.$row["pzlId"];
			$objDb->exec($sql);
			$objPuzzle->startAnalysis();
		}
		unset($objPuzzle);
	}
	unset($rs);
}

print "End of process\n";

?>