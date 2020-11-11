<?php
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/VirtualChannelConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/VirtualChannelConfiguration.php");
require_once($ConstantsArray['dbServerUrl'] ."DataStoreMgr/ChannelConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/ChannelConfiguration.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");

class VirtualParameterUtil{
	
	private static $CE = "ce";
	private static $virtualParameterUtil;
	
	public static function  getInstance(){
		if(empty(self::$virtualParameterUtil)){
			self::$virtualParameterUtil = new VirtualParameterUtil();
		}
		return self::$virtualParameterUtil;
	}
	
	public function getVirtualParameterValue($virtualChannelSeq,$lastWQDSeq){
		$vccDataStore = VirtualChannelConfigurationDataStore::getInstance();
		$vcc = $vccDataStore->FindBySeq($virtualChannelSeq);
		$channelName = $vcc->getChannelName();
		$folderSeq = $vcc->getFolderSeq();
		$data = array();
		$data["wqdfiledataseq"] = $lastWQDSeq;
		$data[1] = 0;
		//
		if(strtolower($channelName) == self::$CE){
			$data = $this->getCEValue($folderSeq,$lastWQDSeq);
		}
		return $data;
	}
	
	private function getCEValue($folderSeq,$lastWQDSeq){
		$ccds = ChannelConfigurationDataStore::getInstance();
		$wqdds = WQDStackDataStore::getInstance();
		$channelConfigs = $ccds->FindByFolder($folderSeq);
		$coChNo = 0;
		$co2ChNo = 0;
		foreach ($channelConfigs as $configs){
			$chName = $configs->getChannelName();
			if(strtolower($chName) == "co"){
				$coChNo = $configs->getChannelNumber();
				continue;
			}
			if(strtolower($chName) == "co2"){
				$co2ChNo = $configs->getChannelNumber();
			}
		}
		$wqdLastDataSeq = $lastWQDSeq;
		$CE = 0;
		$avgValue = 0;
		if(!empty($coChNo) && !empty($co2ChNo)){
			$paramsNos[0] = $coChNo;
			$paramsNos[1] = $co2ChNo;
			$dataArr = $wqdds->getHighValueOccurenciesByParameters($folderSeq, $lastWQDSeq, $paramsNos);
			$ceArr = array();
			$wqdLastDataSeq = $dataArr[0][0];
			foreach($dataArr as $data){
				$coValue = $data[1];
				$co2Value = $data[2];
				if($coValue > 0  && $co2Value > 0){
					$coValue = $coValue /1.25;
					$coValue = $coValue /10000;
					$CE = ($co2Value / ($co2Value + $coValue)) * 100;
					$CE = round($CE,2);
				}else{
					$CE = 0;
				}
				array_push($ceArr, $CE);
				if(!empty($ceArr)){
					$avgValue = array_sum ( $ceArr ) / count($ceArr);
				}
			}
			$data = array();
			$data["wqdfiledataseq"] = $wqdLastDataSeq;
			$data[1] = $avgValue;
			return $data;
		}
	}
}