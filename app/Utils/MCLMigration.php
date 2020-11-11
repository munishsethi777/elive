<?php
//48 200
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/ChannelConfigurationDataStore.php");
$fromFolderSeq = 200;
$toFolderSeq = 48;
$fromChNumbers = array(0=>5,1=>6);
$configurationDataStore = ConfigurationDataStore::getInstance();
$stackDataStore = WQDStackDataStore::getInstance();
$mclDataFromDateKeyName = ConfigurationDataStore::$mclDataInsertDateFrom;
$fromDateAStr = $configurationDataStore->getConfiguration($mclDataFromDateKeyName);
$toDateAStr = $configurationDataStore->getConfiguration(ConfigurationDataStore::$mclDataInsertDateTo);
echo "From Date for update is - ".$fromDateAStr;
$dataArr = $stackDataStore->getChannelData($fromDateAStr,$toDateAStr,$fromFolderSeq,$fromChNumbers,null);
echo "<br>Found Total Rows - " . count($dataArr);
$lastDataDate = 0;
foreach($dataArr as $data){
	$chArray = array();
	$dataDated  = $data["wqdfiledatadated"];
	$ch14Value  = $data["ch5value"];
	//$ch14tatus = $data["ch5status"];
	
	$ch15Value  = $data["ch6value"];
	//$ch15Status = $data["ch6status"];
	$lastDataDate = $dataDated;
	try{
		$update = "update wqdstackfiledata set ch14value=$ch14Value, ch14status=128, ";
		$update.= "ch15value=$ch15Value,ch15status=128";
		$update .= " where wqdfolderseq=$toFolderSeq and wqdfiledatadated like '$dataDated'";
		$stackDataStore->saveWithQuery($update);
		$lastDataDate = $dataDated;
	}catch (Exception $e){
		echo "Error During Migrate Data :- ".$e->getMessage();
	}		
}	
$configurationDataStore->saveConfig($mclDataFromDateKeyName, $lastDataDate);
echo "<br>updated row for till Date -".$lastDataDate . "<br>Total Rows :- " . count($dataArr);
?>