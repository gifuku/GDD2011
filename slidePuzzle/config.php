<?php

define("kDbName",	"gdd2011");
define("kDbUser",	"gdd");
define("kDbPass",	"gdd");

define("kDSN", "mysql:host=localhost;dbname=".kDbName);

define("kDataDir", dirname(__FILE__)."/data");


function createFieldPhrase($inArray){
	$objDb = new PDO(kDSN, kDbUser, kDbPass);
	$fields = array();
	foreach($inArray as $key => $value){
		$fields[] = '`'.$key.'`='.$objDb->quote($value);
	}
	return implode(",", $fields);
}

function createInsertPhrase($inTable, $inFields){
	return 'INSERT INTO `'.$inTable.'` SET '.createFieldPhrase($inFields);
}

function createUpdatePhrase($inTable, $inFields, $where="1"){
	return 'UPDATE `'.$inTable.'` SET '.createFieldPhrase($inFields).' WHERE '.$where;
}

class CSlidePazzle{
	// 参考：　http://www.ic-net.or.jp/home/takaken/nt/index.html
	
	const kProcessLimit = 300;
	
	protected $mObjDb = NULL;
	protected $mPanelId = 0;
	protected $mStart = NULL;
	protected $mAnswer = NULL;
	protected $mWidth = 0;
	protected $mHeight = 0;
	//protected $mTableCache = array();
	protected $mOffsetLeft = 0;
	protected $mOffsetTop = 0;
	protected $mDepthLimit = 30;
	protected $mStartTimestamp = 0;
	protected $mReversalMove = array(
		"U"	=> "D",
		"D"	=> "U",
		"L"	=> "R",
		"R"	=> "L"
	);
	protected $mPanelLabels = array(1,2,3,4,5,6,7,8,9,"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
	
	public function __construct($inPuzzleId=NULL){
		$this->mObjDb = new PDO(kDSN, kDbUser, kDbPass);
		if(is_numeric($inPuzzleId)){
			$this->setPuzzleId($inPuzzleId);
		}
	}
	
	public function __destruct(){
		unset(
			//$this->mTableCache,
			$this->mObjDb,
			$this->mPanelId,
			$this->mStart,
			$this->mAnswer,
			$this->mWidth,
			$this->mHeight,
			$this->mOffsetLeft,
			$this->mOffsetTop,
			$this->mReversalMove,
			$this->mPanelLabels,
			$this->mStartTimestamp
		);
	}
	
	public function setPuzzleId($inPuzzleId){
		$this->mPanelId = $inPuzzleId;
		$sql = 'SELECT * FROM tpuzzle WHERE pzlId='.$inPuzzleId;
		$rs = $this->mObjDb->query($sql);
		if($row = $rs->fetch()){
			/*
			if($row["pzlProcess"]!=""){
				return false;
			}
			*/
			$this->mStart = $row["pzlCurrent"];
			$this->mAnswer = $row["pzlAnswer"];
			$this->mWidth = $row["pzlWidth"];
			$this->mHeight = $row["pzlHeight"];
			return true;
		}
		return false;
	}
	
	public function setDepthLimit($inLimit){
		$this->mDepthLimit = $inLimit;
	}
	
	public function startAnalysis(){
		print date("Y/m/d H:i:s")."\n";
		print "analyse start at index ".$this->mPanelId."\n";
		
		$this->mStartTimestamp = time();
		
		//$sql = 'DELETE * FROM tpuzzleprocess WHERE pzlId='.$this->mPanelId;
		
		/*
		$sql = 'TRUNCATE TABLE `tpuzzleprocess`';
		$this->mObjDb->exec($sql);
		*/
		
		//$route = $this->doProcess($this->mStart);
		//$route = $this->doProcess2(array(array("table" => $this->mStart, "prevMove"=>"", "moveLog"=>"")));
		/*
		$insertArray = array(
			"pzlId"			=> $this->mPanelId,
			"pzpTable"		=> $this->mStart,
			"pzpPrevMove"	=> "",
			"pzpProcess"	=> "",
			"pzpDepth"		=> 0
		);
		$this->mObjDb->exec(createInsertPhrase("tpuzzleprocess", $insertArray));
		$route = $this->doProcess3(0);
		*/
		
		/*
		if ($dh = opendir(kDataDir)) {
			while (($file = readdir($dh)) !== false) {
				if($file != "." && $file != ".."){
					unlink(kDataDir."/".$file);
				}
			}
			closedir($dh);
		}

		file_put_contents(kDataDir."/0.dat", $this->mStart.",,");
		$route = $this->doProcess4(0);
		*/
		
		if ($dh = opendir(kDataDir)) {
			while (($file = readdir($dh)) !== false) {
				if($file != "." && $file != ".."){
					unlink(kDataDir."/".$file);
				}
			}
			closedir($dh);
		}
		/*
		file_put_contents(kDataDir."/0.dat", $this->mStart.",,");
		file_put_contents(kDataDir."/r0.dat", $this->mAnswer.",,");
		$route = $this->doProcess5(0, 0);
		*/
		
		
		$startMD = $this->getMD($this->mStart);
		file_put_contents(kDataDir."/0.dat", $this->mStart.",,,".$startMD.",".$this->getNumOfFall($this->mStart, false) );
		file_put_contents(kDataDir."/r0.dat", $this->mAnswer.",,,".$startMD.",".$this->getNumOfFall($this->mAnswer, true) );
		$route = $this->doProcess6(0, 0);
		
		
		//var_dump($route);
		//$process = implode("", $route);
		$process = $route;
		if($process != false){
			$updateArray = array(
				"pzlProcess" => $process
			);
			$this->mObjDb->exec(createUpdatePhrase("tpuzzle", $updateArray, "pzlId=".$this->mPanelId));
			print "analyse complete. route is [".$process."]\n\n";
		}else{
			print "analyse failed as pattern lost\n\n";
			//exit();
		}
		//return implode("", $route);
		return $process;
	}
	
	protected function getAllowMove($inCurrent, $inPrevMove){
		$returnArray = array();
		
		$spaceOffset = strpos($inCurrent, "0");
		$spaceIndexH = floor($spaceOffset / $this->mWidth);
		$spaceIndexW = $spaceOffset % $this->mWidth;
		$table = $this->createTable($inCurrent);
		
		// U
		//if($inPrevMove!="D" && $spaceIndexH>0 && $table[($spaceIndexH-1)][$spaceIndexW] != "="){
		if($inPrevMove!="D" && $spaceIndexH>$this->mOffsetTop && $table[($spaceIndexH-1)][$spaceIndexW] != "="){
			$returnArray[] = "U";
		}
		// D
		if($inPrevMove!="U" && $spaceIndexH<($this->mHeight-1) && $table[($spaceIndexH+1)][$spaceIndexW] != "="){
			$returnArray[] = "D";
		}
		// L
		//if($inPrevMove!="R" && $spaceIndexW>0 && $table[$spaceIndexH][($spaceIndexW-1)] != "="){
		if($inPrevMove!="R" && $spaceIndexW>$this->mOffsetLeft && $table[$spaceIndexH][($spaceIndexW-1)] != "="){
			$returnArray[] = "L";
		}
		// R
		if($inPrevMove!="L" && $spaceIndexW<($this->mWidth-1) && $table[$spaceIndexH][($spaceIndexW+1)] != "="){
			$returnArray[] = "R";
		}
		return $returnArray;
	}
	
	protected function createTable($inCurrent){
		/*
		$hash = sha1($inCurrent);
		if($inCurrent != "" && !isset($this->mTableCache[$hash])){
		*/
			$table = array();
			$strs = str_split($inCurrent);
			for($h=0; $h<$this->mHeight; $h++){
				$table[$h] = array();
				for($w=0; $w<$this->mWidth; $w++){
					$index = $h*$this->mWidth+$w;
					$table[$h][$w] = $strs[$index];
				}
			}
			return $table;
		/*
			$this->mTableCache[$hash] = serialize($table);
			unset($table);
		}
		return unserialize($this->mTableCache[$hash]);
		*/
	}
	
	protected function getMovedTable($inCurrent, $inMoveTo){
		$table = $this->createTable($inCurrent);
		$spaceOffset = strpos($inCurrent, "0");
		$spaceIndexH = floor($spaceOffset / $this->mWidth);
		$spaceIndexW = $spaceOffset % $this->mWidth;
		//print "0 = ".$spaceIndexH."/".$spaceIndexW."<br />\n";
		//print_r($table);
		switch($inMoveTo){
			case 'U':
				$target = $table[($spaceIndexH-1)][$spaceIndexW];
				$table[($spaceIndexH-1)][$spaceIndexW] = 0;
				break;
				
			case 'D':
				$target = $table[($spaceIndexH+1)][$spaceIndexW];
				$table[($spaceIndexH+1)][$spaceIndexW] = 0;
				break;
				
			case 'L':
				$target = $table[$spaceIndexH][($spaceIndexW-1)];
				$table[$spaceIndexH][($spaceIndexW-1)] = 0;
				break;
				
			case 'R':
				$target = $table[$spaceIndexH][($spaceIndexW+1)];
				$table[$spaceIndexH][($spaceIndexW+1)] = 0;
				break;
		}
		//print "target = ".$target."<br />\n";
		$table[$spaceIndexH][$spaceIndexW] = $target;
		$return = array();
		foreach($table as $row){
			foreach($row as $col){
				$return[] = $col;
			}
		}
		$reutrnStr = implode("", $return);
		unset($return);
		return $reutrnStr;
	}
	
	protected function getMD($inCurrent, $inTarget=NULL, $isReversal=false, $inCreatedTable=NULL){
		if($inTarget !== NULL){
			if($inCreatedTable === NULL){
				$table = $this->createTable($inCurrent);
			}else{
				$table = $inCreatedTable;
			}
			$targetPanel = ($isReversal)?$this->mStart:$this->mAnswer;
			$lastPos = strpos($targetPanel, $inTarget);
			$lastH = floor($lastPos /  $this->mWidth);
			$lastW = $lastPos % $this->mWidth;
			$currH = 0;
			$currW = 0;
			for($h=0; $h<$this->mHeight; $h++){
				for($w=0; $w<$this->mWidth; $w++){
					if($table[$h][$w] == $inTarget){
						$currH = $h;
						$currW = $w;
						break 2;
					}
				}
			}
			$md = abs($lastH - $currH) + abs($lastW - $currW);
		}else{
			$table = $this->createTable($inCurrent);
			$panelSpaces = $this->mWidth*$this->mHeight-1;
			$md = 0;
			for($i=0; $i<$panelSpaces; $i++){
				$label = $this->mPanelLabels[$i];
				$md += $this->getMD($inCurrent, $label, $isReversal, $table);
			}
		}
		return $md;
	}
	
	protected function getNumOfFall($inTable, $isReversal){
		$inStartIndex = 0;
		$inEndIndex = $this->mWidth*$this->mHeight;
		$subTable = substr($inTable, $inStartIndex, $inEndIndex);
		$targetTable = ($isReversal)?$this->mStart:$this->mAnswer;
		$targetSubTable = substr($targetTable, $inStartIndex, $inEndIndex);
		$tableArray = str_split($subTable);
		$nof = 0;
		foreach($tableArray as $index => $str){
			if($str!="0" || $str!="="){
				$keyIndex = strpos($str, $targetSubTable);
				for($i=$index+1; $i<count($tableArray); $i++){
					$targetIndex = strpos($tableArray[$i], $targetTable);
					if($keyIndex>$targetIndex){
						$nof++;
					}
				}
			}
		}
		return $nof;
	}
	
	protected function calcID($inNumOfFall){
		return $inNumOfFall/($this->mWidth-1) + $inNumOfFall%($this->mWidth-1);
	}
	
	protected function getTargetLabel($inCurrent, $inMoveTo){
		$table = $this->createTable($inCurrent);
		$spaceOffset = strpos($inCurrent, "0");
		$spaceIndexH = floor($spaceOffset / $this->mWidth);
		$spaceIndexW = $spaceOffset % $this->mWidth;
		switch($inMoveTo){
			case 'U':
				$target = $table[($spaceIndexH-1)][$spaceIndexW];
				break;
				
			case 'D':
				$target = $table[($spaceIndexH+1)][$spaceIndexW];
				break;
				
			case 'L':
				$target = $table[$spaceIndexH][($spaceIndexW-1)];
				break;
				
			case 'R':
				$target = $table[$spaceIndexH][($spaceIndexW+1)];
				break;
		}
		return $target;
	}
	
	protected function doProcess($inCurrent, $inPrevMove="", $inMoveLog=array()){
		$move = $this->getAllowMove($inCurrent, $inPrevMove);
		foreach($move as $direction){
			//print "process ".$direction."<br />\n";
			$moveLog = $inMoveLog;
			$moveLog[] = $direction;
			$movedTable = $this->getMovedTable($inCurrent, $direction);
			//print $inCurrent." => ".$movedTable."<br />\n";
			if(isset($this->mTableCache[sha1($movedTable)])){
				return false;
			}
			if($movedTable == $this->mAnswer){
				return $moveLog;
			}else{
				$return = $this->doProcess($movedTable, $direction, $moveLog);
				if($return){
					return $return;
				}
			}
		}
	}
	
	protected function doProcess2($inCurrents){
		$nexts = array();
		foreach($inCurrents as $current){
			$move = $this->getAllowMove($current["table"], $current["prevMove"]);
			foreach($move as $direction){
				$moveLog = $current["moveLog"];
				$moveLog .= $direction;
				//print $current["table"]."<br />\n";
				$movedTable = $this->getMovedTable($current["table"], $direction);
				//print $movedTable."<br />\n";
				/*
				if(!isset($this->mTableCache[sha1($movedTable)])){
					if($movedTable == $this->mAnswer){
						return $moveLog;
					}else{
						$next = array(
							"table"		=> $movedTable,
							"prevMove"	=> $direction,
							"moveLog"	=> $moveLog
						);
						$nexts[] = $next;
					}
					unset($next);
				}
				*/
				
				
				//print $current["table"]." => ".$movedTable."\n";
				/*
				$sql = 'SELECT count(*) as count FROM tpuzzleprocess WHERE pzpTable LIKE '.$this->mObjDb->quote($movedTable).' AND pzlId='.$this->mPanelId;
				$rs = $this->mObjDb->query($sql);
				$tableStr = "";
				$row = $rs->fetch();
				if($row["count"] == 0){
				*/
					if($movedTable == $this->mAnswer){
						return $moveLog;
					}else{
						/*
						$insertArray = array(
							"pzlId"			=> $this->mPanelId,
							"pzpTable"		=> $movedTable,
							"pzpProcess"	=> $moveLog 
						);
						$this->mObjDb->exec(createInsertPhrase("tpuzzleprocess", $insertArray));
						*/
						$next = array(
							"table"		=> $movedTable,
							"prevMove"	=> $direction,
							"moveLog"	=> $moveLog
						);
						$nexts[] = $next;
						//print_r($next);
					}
					/*
					$offset = $this->checkOffset($movedTable);
					$offsetTop = $offset["top"];
					$offsetLeft = $offset["left"];
					$changeOffset = false;
					if($offsetTop > $this->mOffsetTop){
						$changeOffset = true;
						$this->mOffsetTop = $offsetTop;
						print "Row ".$offsetTop." is complete.\n";
					}
					if($offsetLeft > $this->mOffsetLeft){
						$changeOffset = true;
						$this->mOffsetLeft = $offsetLeft;
						print "Col ".$offsetLeft." is complete.\n";
					}
					//print memory_get_usage()."\n";
					if($changeOffset){
						$nexts = array($next);
						break 2;
					}
					*/
				//}
				
			}
		}
		//print_r($nexts);
		//print "process for ".count($nexts)."\n";
		if(count($nexts)>0){
			return $this->doProcess2($nexts);
		}else{
			return false;
		}
	}
	
	protected function doProcess3($inDepth){
		$sql = 'SELECT * FROM `tpuzzleprocess` WHERE pzpDepth='.$inDepth;
		$rs = $this->mObjDb->query($sql);
		while($row = $rs->fetch()){
			$move = $this->getAllowMove($row["pzpTable"], $row["pzpPrevMove"]);
			foreach($move as $direction){
				$moveLog = $row["pzpProcess"];
				$moveLog .= $direction;
				$movedTable = $this->getMovedTable($row["pzpTable"], $direction);
				if($movedTable == $this->mAnswer){
					return $moveLog;
				}else{
					$insertArray = array(
						"pzlId"			=> $this->mPanelId,
						"pzpTable"		=> $movedTable,
						"pzpPrevMove"	=> $direction,
						"pzpProcess"	=> $moveLog,
						"pzpDepth"		=> $inDepth+1
					);
					$this->mObjDb->exec(createInsertPhrase("tpuzzleprocess", $insertArray));
					unset($insertArray);
				}
			}
		}
		return $this->doProcess3($inDepth+1);
	}
	
	protected function doProcess4($inDepth){
		if($inDepth>30){
			return false;
		}
		$inFile = fopen(kDataDir."/".$inDepth.".dat", "r");
		$outFile = fopen(kDataDir."/".($inDepth+1).".dat", "w");
		
		while(($data = fgetcsv($inFile, 1024)) !== false){
			if(count($data)){
				$table = $data[0];
				$prevMove = $data[1];
				$moveProcess = $data[2];
				$move = $this->getAllowMove($table, $prevMove);
				foreach($move as $direction){
					$moveLog = $moveProcess;
					$moveLog .= $direction;
					$movedTable = $this->getMovedTable($table, $direction);
					if($movedTable == $this->mAnswer){
						return $moveLog;
					}else{
						fwrite($outFile, $movedTable.",".$direction.",".$moveLog."\n");
					}
				}
			}
		}
		fclose($inFile);
		fclose($outFile);
		return $this->doProcess4($inDepth+1);
	}
	
	protected function doProcess5($inDepth, $inReversalDepth, $isReversal=false){
		if($inDepth+$inReversalDepth>31){ return false; }
		print "Compare for Depth : ".$inDepth." <=> ReversalDepth : ".$inReversalDepth."\n";
		if($isReversal){
			$compFile = kDataDir."/".$inDepth.".dat";
			$readFile = kDataDir."/r".$inReversalDepth.".dat";
			$writeFile = kDataDir."/r".($inReversalDepth+1).".dat";
		}else{
			$compFile = kDataDir."/r".$inReversalDepth.".dat";
			$readFile = kDataDir."/".$inDepth.".dat";
			$writeFile = kDataDir."/".($inDepth+1).".dat";
		}
		
		$compArray = array();
		$compFp = fopen($compFile, "r");
		while(($data = fgetcsv($compFp, 1024)) !== false){
			$compArray[$data[0]] = $data[2];
		}
		fclose($compFp);
		
		$readFp = fopen($readFile, "r");
		$writeFp = fopen($writeFile, "w");
		while(($data = fgetcsv($readFp, 1024)) !== false){
			if(count($data)){
				$table = $data[0];
				$prevMove = $data[1];
				$moveProcess = $data[2];
				$move = $this->getAllowMove($table, $prevMove);
				foreach($move as $direction){
					$movedTable = $this->getMovedTable($table, $direction);
					$moveLog = $moveProcess;
					if($isReversal){
						$moveLog = $this->mReversalMove[$direction].$moveLog;
					}else{
						$moveLog .= $direction;
					}
					if(isset($compArray[$movedTable])){
						if($isReversal){
							return $compArray[$movedTable].$moveLog;
						}else{
							return $moveLog.$compArray[$movedTable];
						}
					}else{
						fwrite($writeFp, $movedTable.",".$direction.",".$moveLog."\n");
					}
				}
			}
		}
		fclose($readFp);
		fclose($writeFp);
		
		unset($compArray);
		
		if($isReversal){
			$nextDepth = $inDepth;
			$nextReversalDepth = $inReversalDepth+1;
		}else{
			$nextDepth = $inDepth+1;
			$nextReversalDepth = $inReversalDepth;
		}
		return $this->doProcess5($nextDepth, $nextReversalDepth, !$isReversal);
	}
	
	protected function doProcess6($inDepth, $inReversalDepth, $isReversal=false){
		if($inDepth+$inReversalDepth>$this->mDepthLimit){ return false; }
		if(time()-$this->mStartTimestamp > self::kProcessLimit){
			print "time is up ! do next panel.\n";
			return false;
		}
		
		//print "Compare for Depth : ".$inDepth." <=> ReversalDepth : ".$inReversalDepth."\n";
		if($isReversal){
			$compFile = kDataDir."/".$inDepth.".dat";
			$readFile = kDataDir."/r".$inReversalDepth.".dat";
			$writeFile = kDataDir."/r".($inReversalDepth+1).".dat";
		}else{
			$compFile = kDataDir."/r".$inReversalDepth.".dat";
			$readFile = kDataDir."/".$inDepth.".dat";
			$writeFile = kDataDir."/".($inDepth+1).".dat";
		}
		
		$compArray = array();
		$compFp = fopen($compFile, "r");
		while(($data = fgetcsv($compFp, 1024)) !== false){
			$compArray[$data[0]] = $data[2];
		}
		fclose($compFp);
		
		$outLines = 0;
		$readFp = fopen($readFile, "r");
		$writeFp = fopen($writeFile, "w");
		while(($data = fgetcsv($readFp, 1024)) !== false){
			if(count($data)){
				$table = $data[0];
				$prevMove = $data[1];
				$moveProcess = $data[2];
				$sumMD = $data[3];
				//$currNumOfFall = $data[4];
				$move = $this->getAllowMove($table, $prevMove);
				foreach($move as $direction){
					$targetLabel = $this->getTargetLabel($table, $direction);
					$currPos = strpos($table, $targetLabel)+1;
					$currTargetMD = $this->getMD($table, $targetLabel, $isReversal);
					$movedTable = $this->getMovedTable($table, $direction);
					$movedPos = strpos($movedTable, $targetLabel)+1;
					$moveAfterMD = $this->getMD($movedTable, $targetLabel, $isReversal);
					//$beforeNumOfFall = $this->getNumOfFall($table, $isReversal);
					//$afterNumOfFall = $this->getNumOfFall($movedTable, $isReversal);
					
					$moveAfterSumMD = $sumMD - $currTargetMD + $moveAfterMD;
					$movedAfterNumOfFall = $this->getNumOfFall($movedTable, $isReversal);
					$invert = $this->calcID($movedAfterNumOfFall);
					$checkDepth = ($isReversal)?$inReversalDepth:$inDepth;
					$chekeParam = ($moveAfterSumMD>$invert)?$moveAfterSumMD:$invert;
					
					
					if($chekeParam <= $this->mDepthLimit - $checkDepth){
						$moveLog = $moveProcess;
						if($isReversal){
							$moveLog = $this->mReversalMove[$direction].$moveLog;
						}else{
							$moveLog .= $direction;
						}
						if(isset($compArray[$movedTable])){
							if($isReversal){
								return $compArray[$movedTable].$moveLog;
							}else{
								return $moveLog.$compArray[$movedTable];
							}
						}else{
							$outLines++;
							fwrite($writeFp, $movedTable.",".$direction.",".$moveLog.",".$moveAfterSumMD."\n");
						}
					}
				}
			}
		}
		fclose($readFp);
		fclose($writeFp);
		
		unset($compArray);
		
		// 出力行数が0の場合終了
		if(!$outLines){ return false; }
		
		if($isReversal){
			$nextDepth = $inDepth;
			$nextReversalDepth = $inReversalDepth+1;
		}else{
			$nextDepth = $inDepth+1;
			$nextReversalDepth = $inReversalDepth;
		}
		return $this->doProcess6($nextDepth, $nextReversalDepth, !$isReversal);
	}
	
	protected function checkOffset($inCurrent){
		$offsetTop = $this->checkOffsetTop($inCurrent);
		$offsetLeft = $this->checkOffsetLeft($inCurrent);
		// ダウンスケール後に壁があるかどうかチェック
		$table = str_split($inCurrent);
		$checkTable = "";
		for($i=$offsetTop; $i<$this->mHeight; $i++){
			for($j=$offsetLeft; $j<$this->mWidth; $j++){
				$index = $i*$this->mWidth+$j;
				$checkTable .= $table[$index];
			}
		}
		if(strpos($checkTable, "=") === false){
			//print "offset change to\ntop : ".$offsetTop."\nleft : ".$offsetLeft."\ntable : ".$inCurrent."\nnewtable : ".$checkTable."\n";
			return array(
				"top"	=> $offsetTop,
				"left"	=> $offsetLeft
			);
		}else{
			return array(
				"top"	=> 0,
				"left"	=> 0
			);
		}
	}
	
	protected function checkOffsetTop($inCurrent){
		$table = $this->createTable($inCurrent);
		$ansTable = $this->createTable($this->mAnswer);
		$offset = 0;
		for($i=0; $i<$this->mHeight-2; $i++){
			if(implode("", $table[$i]) == implode("", $ansTable[$i])){
				$offset = $i+1;
			}else{
				break;
			}
		}
		return $offset;
	}
	
	protected function checkOffsetLeft($inCurrent){
		$table = str_split($inCurrent);
		$ansTable = str_split($this->mAnswer);
		$offset = 0;
		for($i=0; $i<$this->mWidth-2; $i++){
			$currentLine = "";
			$answerLine = "";
			for($j=0; $j<$this->mHeight; $j++){
				$index = $j*$this->mWidth+$i;
				$currentLine .= $table[$index];
				$answerLine .= $ansTable[$index];
			}
			if($currentLine == $answerLine){
				$offset = $i+1;
			}else{
				break;
			}
		}
		return $offset;
	}
}


?>