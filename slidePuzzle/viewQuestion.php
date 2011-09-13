<?php
include_once(dirname(__FILE__)."/config.php");

$st = (isset($_POST["st"]))?$_POST["st"]:1;
$ed = (isset($_POST["ed"]))?$_POST["ed"]:20;


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>無題ドキュメント</title>
<style type="text/css" media="screen">
<!--

*{
	font-size:1em;
}

table.puzzle{
	border-collapse: collapse;
	border-spacing: 0;
	margin-bottom:3em;
}

table.puzzle td{
	width: 14px;
	height:14px;
}

	.grid{
		background-color: #EEF;
		border: 2px solid #CCC;
		font-weight: bold;
		padding: 5px 10px;
		text-align: justify;
	}
	
	.void{
		background-color: white;
	}
	
	.wall{
		background-color:#666;
		color: #fff;
	}

-->
</style>
</head>

<body>
<form action="" method="post">
	クイズ番号 開始：<input type="text" name="st" value="<?=$st?>" />～終了：<input type="text" name="ed" value="<?=$ed?>" />
    <input type="submit" value="設定" />
</form>
<?php
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$objDb = new PDO(kDSN, kDbUser, kDbPass);
	$sql = 'SELECT pzlId, pzlWidth, pzlHeight, pzlCurrent, pzlAnswer FROM tpuzzle WHERE pzlId>='.$st.' AND pzlId<='.$ed;
	$rs = $objDb->query($sql);
	while($row = $rs->fetch()){
		$width = $row["pzlWidth"];
		$height = $row["pzlHeight"];
		
		print "Quiz Index : ".$row["pzlId"]."<br />";
		print "RawData : ".$row["pzlCurrent"]."<br />";
		?>
	<table>
		<tr>
			<td>
				<table class="puzzle">
        	<?php
			$dataStr = str_split($row["pzlCurrent"], 1);
			for($h=0; $h<$height; $h++){
				?>
					<tr>
<?php
				for($w=0; $w<$width; $w++){
					$index = $h*$width+$w;
					$class = array();
					$class[] = "grid";
					$str = $dataStr[$index];
					if($str == "="){
						$class[] = "wall";
					}elseif($str == "0"){
						$class[] = "void";
					}
					?>
						<td class="<?=implode(" ", $class)?>"><?=$str?></td>
<?php
				}
				?>
					</tr>
<?php
			}
			?>
				</table>
			</td>
			<td style="padding:20px;">=></td>
			<td>
				<table class="puzzle">
        	<?php
			$dataStr = str_split($row["pzlAnswer"], 1);
			for($h=0; $h<$height; $h++){
				?>
					<tr>
<?php
				for($w=0; $w<$width; $w++){
					$index = $h*$width+$w;
					$class = array();
					$class[] = "grid";
					$str = $dataStr[$index];
					if($str == "="){
						$class[] = "wall";
					}elseif($str == "0"){
						$class[] = "void";
					}
					?>
						<td class="<?=implode(" ", $class)?>"><?=$str?></td>
<?php
				}
				?>
					</tr>
<?php
			}
			?>
				</table>
			</td>
		</tr>
	</table>
<?php
	}
}
?>
</body>
</html>