<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWQD.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserAppcbHyd.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserAlternateFiles.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserStackLsi.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserBoomiM2MCSV.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserWADAPPCB.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserDuplicateAQMS.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserBhoomiFiles.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserRaveAQMS.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserScaledM2MCSV.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserEnvirotechAQMSMini.php");
require_once($ConstantsArray['dbServerUrl'] ."/Parsers/ParserEnvidas.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/FileSystemUtils.php");

require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
Logger::configure ( $ConstantsArray ['dbServerUrl'] . "log4php/log4php.xml" );
$folderSeq = 317;
$FDS = FolderDataStore::getInstance();
$folders = $FDS->FindAll(false);
$folder = null;
foreach($folders as $folderObj){
    if($folderObj->getSeq() == $folderSeq){
        $folder = $folderObj;
    }
}
$fileUtils = FileSystemUtils::getInstance();

$filesPath =  "/Applications/MAMP/htdocs/elive/Repository/" . $folder->getRepositoryFolder() ."/". $folder->getLocationFolder() . "/" . $folder->getActualName() . "/";
$files= $fileUtils->Read_Directory($filesPath);
$parser = $folder->getParserType();
if(empty($parser)){
    echo("No Parser Found");
    die;
}
$parserInstance = new $parser();
foreach($files as $value){
    $WQDDataArray =  $parserInstance::parse($value,$folder->getSeq());
    echo("\n<br>FolderSeq:".$folder->getSeq() ." ParserType:". $parser);
    if($WQDDataArray != null){
        echo("No Data Found");
    }
}




$content = file($filePath);
// if(empty($content)){
//     return null;
// }
// var_dump($content);
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
		echo "<br>" . $dated . " found for folder seq - " . $folderSeq . " on line " . $lineRow . " no and filePath - ".$filePath;
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