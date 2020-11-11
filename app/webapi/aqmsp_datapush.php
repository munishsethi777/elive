<?php
//String str="GET http://api.xxxxxxxxxx.com/update?api_key=KPXQXXXXXXXXYYYXXX&field1=" + String(temp)+"&field2=" + String(hum);.............

//http://www.envirotechlive.com/webapi/push_aqmsp_data.php?devicekey=12345678&stationkey=9876543210&ch1=12.5&ch2=100&datetime=30042019145900
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDAQMSPFileDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/DateUtils.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
$folderDS = FolderDataStore::getInstance();
$success = 1;
$message = "Successfully saved";

try{
	$locationSeq = $_GET['locationkey'];
	$folderSeq = $_GET['stationkey'];
	if((empty($locationSeq)) || (empty($folderSeq))){
		$success = 0;
		$message = "Invalid locationkey or stationkey";
		$json = array("success"=>$success,"message"=>$message);
		echo(json_encode($json));
		die;
	}
	$folder = $folderDS->FindBySeq($folderSeq);
	if(empty($folder)){
		$success = 0;
		$message = "Invalid station key provided";
		$json = array("success"=>$success,"message"=>$message);
		echo(json_encode($json));
		die;
	}else{
		if($folder->getLocationSeq() != $locationSeq){
			$success = 0;
			$message = "Invalid locationkey provided";
			$json = array("success"=>$success,"message"=>$message);
			echo(json_encode($json));
			die;
		}
	}
	
	$dated = $_GET['datetime'];
	$dateStr = date('m/d/Y H:i:s', $dated);
	$dated = DateUtils::StringToDateByGivenFormat('m/d/Y H:i:s', $dateStr);
	
	
	if($dated->format("Y") == "1970" || $dated == false){
		$success = 0;
		$message = "Invalid datetime stamp provided";
		$json = array("success"=>$success,"message"=>$message);
		echo(json_encode($json));
		die;
	}
	if($success = 1){
		$arrayCount = 0;
		$channels = array();
		$channelNo = 1;
		foreach ($_GET as $key => $value) {
			if(substr($key, 0,2) == "ch"){
				$channelNo = substr($key,2);
				$channelInfo = array();
				$channelInfo['value'] = $_GET[$key];
				$channelInfo['status'] = 128;
				$channels[$channelNo] = $channelInfo;
			}
		}
		$totalChannels = $channelNo - 1;
		$WQDData = new WQDData();
		$WQDData->setReportNo(0);
		$WQDData->setFolderSeq($folderSeq);
		$WQDData->setDataDate(DateUtils::getSQLDateFromDateObj($dated));
		$WQDData->setChannels($channels);
		$WQDData->setTotalChannels($totalChannels);
		$WQDData->setChecksum(0);
		$WQDDataArray[0]=$WQDData;
		$WAQSD = WQDAQMSPFileDataStore::getInstance();
		$res = $WAQSD->saveSyncedDataViaAPI($WQDDataArray);
		$success = $res["success"];
		$message = $res["message"];
		//var_dump($WQDDataArray);
		if($success == 0 && !empty($message)){
			if (strpos($message, 'Duplicate') !== false) {
				$message = "Database already has values for this timestamp, please try with different timestamp";   
			}
		}
	}
}catch(Exception $e){
	$message = $e->getMessage();
	$success = 0;
}
$json = array("success"=>$success,"message"=>$message);
echo(json_encode($json));	
