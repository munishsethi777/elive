<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/M2MSynchronizerDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
//Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
$m2mSDS = M2MSynchronizerDataStore::getInstance();
echo("<br><br><br>Calling M2m for Heat<br>");
$chunkSize = 40;
try{
	$m2mData = null;
	$m2mSites = null;
	$folderSeqs = null;
	$syncDates = null;
	$data = null;
	//For bhoomi heat
	$m2mData = $m2mSDS->FindAll('heat');
	$count = count($m2mData[0]['siteCode']);
	$m2mSiteCodesChunks = array_chunk($m2mData[0]['siteCode'],$chunkSize,true);
	$m2mLastSynchDatesChunks = array_chunk($m2mData[0]['lastSyncDate'],$chunkSize,true);
	
	$folderSeqsChunks = array_chunk($m2mData[1],$chunkSize,true);
	$synchDatesChunks = array_chunk($m2mData[2],$chunkSize,true);
	
	
	for($i=0;$i<count($m2mSiteCodesChunks);$i++){
        $m2mSites = array("siteCode"=>array_values($m2mSiteCodesChunks[$i]),"lastSyncDate"=>array_values($m2mLastSynchDatesChunks[$i]));
        $folderSeqs = $folderSeqsChunks[$i];
		$syncDates = $synchDatesChunks[$i];
		$data = json_encode($m2mSites);
		
		var_dump("<br>m2msites". $data . "<br>");
		$ch = curl_init();
		$CDS = ConfigurationDataStore::getInstance();
		$url = $CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUrl);
		echo "<br/>Api url - " .  $url . "<br/>";
		//For ezone call
		$username = "api_user@bhoomiltd.com"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
		$password = "api@1234"; //$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
		$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		//echo("Result - :". $result);
		curl_close($ch);
		$m2mDS = M2MSynchronizerDataStore::getInstance();
		$m2mDS->SaveSyncedWQDStackData($result,$folderSeqs,$syncDates);
		echo "<br>Saved chunk of ----- " . count($m2mSiteCodesChunks[$i]) ."<br>";
	}
	
	
	echo ("<br>Data Synced Successfully for heat");
}catch(Exception $e){
	$message = $e->getMessage();
	MailerUtils::sendError($message,"Error During HeatM2MSynchronizer");
    echo ("Exception occured". $e->getMessage());
}
echo "<br>Memory used :- ". memory_get_usage();
