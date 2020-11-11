<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/ChannelConfigurationDataStore.php");$fromDate = strtotime('2016-08-14 20:26:01');$toDate = strtotime('2016-08-15 18:10:01');
$folderSeq = 103;

$chNumber = "3";
$stackDataStore = WQDStackDataStore::getInstance();

$fromDateAStr = date('Y/m/d  H:i:s',$fromDate);
$toDateAStr = date('Y/m/d  H:i:s',$toDate);
try{	
	//$stackDataStore->removeChValueAndStatus($folderSeq, $chNumber,$fromDateAStr,$toDateAStr);
	echo("Removed Data for channel number " .$chNumber  ." From - ". $fromDateAStr ." To - " . $toDateAStr);
}catch (Exception $e){
	echo "Error During Remove Migrated Data :- ".$e->getMessage();
}

?>