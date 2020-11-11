<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDFile.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDData.php");
require_once($ConstantsArray['dbServerUrl'] ."/BusinessObjects/WQDChannel.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");

class ParserBoomiM2MCSV{
	public static function parse($filePath,$folderSeq){
		$file = fopen($filePath, "r");
		$rows = array();
		$CCDS = ChannelConfigurationDataStore::getInstance();
		$chNames = $CCDS->FindChNameByFolderSeq($folderSeq);
		$chNames = array_map('strtolower', $chNames);
		$colNames = fgetcsv($file);
		$scanDateArr = array();
		$channelData = array();
		$count= 0;
		$mainArray = array();
		$dataArray = array();
		$WQDArray = array();
        $valueIndex = 6;
        if(($folderSeq == 307) || ($folderSeq == 241)){ //vayunanda/indosponge processed value is higher than scaled value, so pick scaled value only
            $valueIndex = 7;
        }
        while(!feof($file))
        {
            $row = fgetcsv($file);
            if(!empty($row)){
                $dated = null;
                $channelName = null;
                $value = null;
                $status = null;
                if(array_key_exists(0,$row)){
                    $dated = $row[0];
                }
                if(array_key_exists(2,$row)){
                    $channelName = strtolower($row[2]);
                }
                if(array_key_exists($valueIndex,$row)){
                    $value = $row[$valueIndex];
                }
                if(array_key_exists(8,$row)){
                    $status = $row[8];
                }
                if(!array_key_exists($dated, $mainArray)){
                    $channelData = array();
                }else{
                    $channelData = $mainArray[$dated];
                }
                $channelInfo = array();
                if(!is_numeric($value)){
                    $value = null;
                }
                $channelInfo['value'] = $value;
                $channelInfo['status'] = $status;
                $channelNo = $key = array_search ($channelName, $chNames);
                if($channelNo){
                    $channelData[$channelNo] = $channelInfo;
                    $mainArray[$dated] = $channelData;
                }
            }
        }
        if(!empty($mainArray)){
            $WQDArray = self::getWQDArray($folderSeq,$mainArray);
        }
        return $WQDArray;
	}
	
	
	private static function getWQDArray($folderSeq,$mainArray){
		$WQDDataArray = array();
		foreach($mainArray as $dated=>$channels){
			$WQDData = new WQDData();
			$WQDData->setReportNo(1);
			$WQDData->setFolderSeq($folderSeq);
            $datedFormated = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $dated);
            if(empty($datedFormated)){
                throw new Exception("Bad date format ". $dated ." for folderSeq". $folderSeq);
            }
            $WQDData->setDataDate($datedFormated->format("Y-m-d H:i:s"));
			$WQDData->setChannels($channels);
			$WQDData->setTotalChannels(count($channels));
			$WQDData->setChecksum(0);
			array_push($WQDDataArray, $WQDData);
		}
		return $WQDDataArray;
	}
	
}
