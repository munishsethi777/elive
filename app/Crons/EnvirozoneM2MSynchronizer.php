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
echo("Calling M2m for ENVIROZONE<br>");
try{
    $m2mSDS = M2MSynchronizerDataStore::getInstance();
    $m2mData = $m2mSDS->FindAll();
    $m2mSites = $m2mData[0];
    $folderSeqs = $m2mData[1];
    $syncDates = $m2mData[2];
    $data = json_encode($m2mSites);
    //var_dump($data . "<br>");
    $ch = curl_init();
    $CDS = ConfigurationDataStore::getInstance();
    $url = $CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUrl);
    $username = $CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
    $password = $CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
    $auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    //var_dump("<BR>". $result . "<br><br>-----------------------------------------------------");
    $m2mDS = M2MSynchronizerDataStore::getInstance();
    $m2mDS->SaveSyncedWQDStackData($result,$folderSeqs,$syncDates);
    echo ("<br>Data Synced Successfully for Envirozone Stations");
}catch(Exception $e){
    $message = $e->getMessage();
    MailerUtils::sendError($message,"Error During EnvirozoneM2MSynchronizer");
}
echo "<br>Memory used :- ". memory_get_usage();
