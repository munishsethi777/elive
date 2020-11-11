<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/ChannelConfigurationDataStore.php");
$fromDate = strtotime('2016-09-08 16:42:56');
$toDate = strtotime('2016-09-12 09:51:41');
$folderSeq = 107;

$fromChNumbers = array(0=>7);
$toChNumber = "13";
$stackDataStore = WQDStackDataStore::getInstance();

$fromDateAStr = date('Y/m/d  H:i:s',$fromDate);
$toDateAStr = date('Y/m/d  H:i:s',$toDate);

$dataArr = $stackDataStore->getChannelData($fromDateAStr,$toDateAStr,$folderSeq,$fromChNumbers,null);$lastDataSeq = 0;
foreach($dataArr as $data){
	$dataSeq = $data[0];
	$chValue = $data[2];
	$chStatus = $data[3];		
	$lastDataSeq = $dataSeq;
	try{
		//$stackDataStore->updateChValueAndStatus($chValue, $chStatus, $toChNumber,$dataSeq);		
	}catch (Exception $e){
		echo "Error During Migrate Data :- ".$e->getMessage();
	}		
}	echo "<br>updated row for till seq -".$lastDataSeq . "<br>Total Rows :- " . count($dataArr);
?>