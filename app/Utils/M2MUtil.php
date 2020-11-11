<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/M2MSynchronizerDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
class M2MUtil{
	public static function syncEliveStationsData(){
		$m2mSDS = M2MSynchronizerDataStore::getInstance();
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
			$ch = curl_init();
			$CDS = ConfigurationDataStore::getInstance();
			$url = $CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUrl);
			$username = "aman.dubey@envirotechlive.com";//$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiUsername);
			$password = "password";//$CDS->getConfiguration(ConfigurationDataStore::$m2mWebApiPassword);
			$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);
			curl_close($ch);
			$m2mDS = M2MSynchronizerDataStore::getInstance();
			$m2mDS->SaveSyncedWQDStackData($result,$folderSeqs,$syncDates);
			return true;
		}catch(Exception $e){
			$message = $e->getMessage();
			return false;
		}
	}
}