<?php
include_once(dirname(__FILE__)."/config.php");


$objDb = new PDO(kDSN, kDbUser, kDbPass);

$answers = array();

$sql = 'SELECT pzlProcess FROM tpuzzle WHERE 1 ORDER BY pzlId ASC';
$rs = $objDb->query($sql);
while($row = $rs->fetch()){
	$answers[] = $row["pzlProcess"];
}

header("Content-type: application/octet-stream");
header('Content-Disposition: attachment; filename=answer'.date("YmdHi").'.txt');

print implode("\n", $answers);

?>