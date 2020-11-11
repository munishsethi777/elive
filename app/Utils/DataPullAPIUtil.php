<?php
//require_once('../IConstants.inc');
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr/ConfigurationDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDAQMSPFileDataStore.php");
//require($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDDataDataStore.php");
//require($ConstantsArray['dbServerUrl'] . "Utils/DateUtils.php");
class DataPullAPIUtil{
	
	private static $dataPullApiUtil;
	public static function getInstance(){
		if (!self::$dataPullApiUtil){
			self::$dataPullApiUtil = new DataPullAPIUtil();
		}
		return self::$dataPullApiUtil;
	}
	
	public function getData($fromDate,$folderSeq,$user){
		try{
			//$fromDate = $_GET["fromDate"];//21-11-2018 16:46
			//$folderSeq = $_GET["folderSeq"];
			if(empty($fromDate) || empty($folderSeq)){
				throw new RuntimeException("Invalid Parameters");
			}
			$userSeq = $user->getSeq();
			$fds = FolderDataStore::getInstance();
			$lds = LocationDataStore::getInstance();
			$folder = $fds->FindBySeq($folderSeq);
			$locationSeq = $folder->getLocationSeq();
			$isLocationExistForUser = $lds->isLocationExistForUser($userSeq, $locationSeq);
			if(!$isLocationExistForUser){
				throw new RuntimeException("Unauthorized Action");
			}
			$stationType = $folder->getStationType();
			$configurationDataStore = ConfigurationDataStore::getInstance();
			$channelConfigDataStore = ChannelConfigurationDataStore::getInstance();
			$rows = $configurationDataStore->getConfiguration(ConfigurationDataStore::$dataPullApiRowCount);
			$channels = $channelConfigDataStore->FindByFolder($folderSeq);
			$channelNos = $this->getAllChNos($channels);
			$fromDate = DateUtils::StringToDateByGivenFormat("d-m-Y H:i:s", $fromDate);
			if(!$fromDate){
				throw new RuntimeException("Invalid Date Format!.It should be DD-MM-YYYY HH:MM:SS");
			}
			$fromDate = $fromDate->format("Y-m-d H:i:s");
			$dataArr = array();
			if($stationType == "stack"){
				$WQDSDS = WQDStackDataStore::getInstance();
				$dataArr = $WQDSDS->getStackData($fromDate, $folderSeq, $channelNos, $rows);
			}else if($stationType == "aqmsp"){
				$WAQMPSDS = WQDAQMSPFileDataStore::getInstance();
				$dataArr = $WAQMPSDS->getAQMSPData($fromDate, $folderSeq, $channelNos, $rows);
			}else{
				$WQDS = WQDDataDataStore::getInstance();
				$dataArr = $WQDS->getChannelData($fromDate, $folderSeq, $channelNos, $rows);
			}
			$chDataArr = array();
			foreach ($dataArr as $data){
				$chData = array();
				$chNames = array();
				$dated = $data["wqdfiledatadated"];
				$dated = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $dated);
				$dated = $dated->format("d-m-Y H:i:s");
				foreach ($channels as $channel){
					$chNo  = $channel->getChannelNumber();
					$chName = $channel->getChannelName();
					array_push($chNames, $chName);
					$chValue = $data["ch".$chNo."value"];
					array_push($chData, $chValue);
				}
				$chDataArr[$dated] = $chData;
			}
			$mainArr["channels"] = $chNames;
			$mainArr["data"] = $chDataArr;
			return $mainArr;
		}catch (Exception $e){
			//log Exception here
			throw $e;	
		}
	}
	
	private function getAllChNos($channels){
		$chNos = array();
		foreach ($channels as $ch){
			array_push($chNos, $ch->getChannelNumber());
		}
		return $chNos;
	}	
}