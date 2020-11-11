<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
Logger::configure ( $ConstantsArray ['dbServerUrl'] . "log4php/log4php.xml" );
$folderSeq = 79;
$filePath = "/home/envirote/public_html/public_html/app/Backup/bhoomi/manglam_cements_ltd_-_aligarh/aligarh/Jan2019/20190111 06_15.txt";
echo "File Path is " .  $filePath;
$content = file($filePath);
$numLines = count($content);
$lineRow = 0;
for($i=0;$i<$numLines;$i++){
	if(trim($content[$i]) == ""){

	}else{
		$lineRow = $i;
		$i = $numLines;;
	}
}
$arrayCount = 0;
$WQDDataArray = new ArrayObject();
$numLines = count($content);
echo "<br>Total Line Count - " . $numLines;
$logger = Logger::getLogger ( "eliveLogger" );
for ($i = $lineRow; $i < $numLines; $i++) {
	$line = trim($content[$i]);
	$lineVals = explode(',', $line);
	$totalChannels = (count($lineVals) -2);
	$dateStr = $lineVals[0];
	$timeStr = $lineVals[1];
	$dated = DateConvert($dateStr . $timeStr);
	if (strpos($dated, '1999') !== false) {
		echo $dated . "<br> found for folder seq - " . $folderSeq . " on line " . $lineRow . " no and filePath - ".$filePath;
		continue;
	}
}

function DateConvert($dateStr){
	$YY = substr($dateStr,0,4);
	$MM = substr($dateStr,4,2);
	$DD = substr($dateStr,6,2);
	$HH = substr($dateStr,8,2);
	$MN = substr($dateStr,10,2);
	$SS = substr($dateStr,12,2);
	//2015 08 29 14 37
	return date('Y-m-d H:i:s',mktime($HH, $MN, $SS, $MM, $DD, $YY));
}