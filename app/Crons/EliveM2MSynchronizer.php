<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/M2MSynchronizerDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
//Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
$m2mSDS = M2MSynchronizerDataStore::getInstance();
echo("Calling M2m for ELIVE<br>");
try{
	$m2mData = null;
	$m2mSites = null;
	$folderSeqs = null;
	$syncDates = null;
	$data = null;
	//For bhoomi call
    $m2mData = $m2mSDS->FindAll('elive');
    $m2mSites = $m2mData[0];
    $folderSeqs = $m2mData[1];
    $syncDates = $m2mData[2];
    $data = json_encode($m2mSites);
    var_dump("m2msites". $data . "<br>");
    $ch = curl_init();
    $CDS = ConfigurationDataStore::getInstance();
    $url = $CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUrl);
    $username = "aman.dubey@envirotechlive.com";//$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
    $password = "Password@123";//$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
    $auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
	var_dump("<BR>". $result . "<br><br>-----------------------------------------------------");
	curl_close($ch);
    $m2mDS = M2MSynchronizerDataStore::getInstance();
    $m2mDS->SaveSyncedWQDStackData($result,$folderSeqs,$syncDates);
    echo ("<br>Data Synced Successfully for Elive");
}catch(Exception $e){
    $message = $e->getMessage();
    MailerUtils::sendError($message,"Error During EliveM2MSynch");
}
echo "<br>Memory used :- ". memory_get_usage();
