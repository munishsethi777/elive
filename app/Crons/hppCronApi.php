<?php
require_once('../IConstants.inc');
//require_once($ConstantsArray['dbServerUrl'] . "/Utils/Aes.php");
//require_once($ConstantsArray['dbServerUrl'] . "/Utils/AesCtr.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Plugin/PHPExcel.php");
require_once($ConstantsArray['dbServerUrl'] . "/Plugin/PHPExcel/IOFactory.php");
require_once($ConstantsArray['dbServerUrl'] . "/Crons/Encryption.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/FileSystemUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/HPPCBService.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/HPPCBMetaUtil.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
require_once ($ConstantsArray ['dbServerUrl'] . "StringConstants.php");
Logger::configure("/home/envirote/public_html/app/log4php/log4php.xml");
$logger = Logger::getLogger("CronHPPCBDailyLogger");

   //echo "<br/>-----HPCB -----</br/>";
  $locationSeqs = "5,157";
  HPPCBSerive::pushData($logger,$locationSeqs,StringConstants::HPPCB);
	
  echo "<br/>-----MPCB -----</br/>";
  $locationSeqs = "11,163,181";
  HPPCBSerive::pushData($logger,$locationSeqs,StringConstants::MPCB);
  

  
  
  return;
 	
// 	$fr=fopen('data_org.csv','r');
// 	$fw=fopen('data.csv','w');
// 	if(!$fr){
// 		var_dump(error_get_last());
// 	}
// 	$crypt = new Encryption($key);
// 	while(!feof($fr))
// 		{
// 			$buffer=fread($fr,4128);
// 			$result= $crypt->encrypt($buffer);
// 			$dResult = $crypt->decrypt($result);
// 			false!==$result && fwrite($fw,$result);
// 		}
// 	fclose($fw);
// 	fclose($fr);
// 	return;
 	$key = 'c2l0ZV8xNDAwLHZlcl8zLjE=########';
 	
	$fname = 'site_1400_Stack_3_20170410043000.zip';
	$files = array("data.csv","metaData.csv");
	FileSystemUtils::createZip($fname, $files);
	$Date = new DateTime();
	$date = $Date->format("Y-m-d\TH:i:s\Z");
	$fileDate = $Date->format("Ymdhis");
	//$target_url = "http://hppcb.glensserver.com/HPPCBServer/testUpload";
	$target_url = "http://hppcb.glensserver.com/HPPCB/delayedUpload";
	$crypt = new Encryption($key);
	$cfile = new CURLFile($fname);
	$post = array (
		'file' => $cfile
	);
	$ch = curl_init();
	$authData = "site_1400,ver_3.1,$date,$key";
	$encryptedAuth = $crypt->encrypt($authData,$key);
	$auth = 'Authorization: Basic ' . $encryptedAuth;
	$dAuth = $crypt->decrypt($encryptedAuth);
	//echo "<br>Decrypted - " . $dAuth ."<br>";
	$timeStamp = 'Timestamp:'. $date;
	$siteKey = 'siteid:site_1400';
	$headerArr = array($timeStamp,$siteKey,$auth);
	var_dump($headerArr);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headerArr);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
	$result = curl_exec ($ch);
	
	if ($result === FALSE) {
		echo "Error sending" . $fname .  " " . curl_error($ch);
		curl_close ($ch);
	}else{
		curl_close ($ch);
		//echo  "Result: " . $result;
		
	}
