<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/WQDDataDataStore.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
$logger = Logger::getLogger("CronAPIDailyLogger");
$fds = FolderDataStore::getInstance();
$folderSeqs = array(0=>32);
$folders = $fds->FindBySeqs($folderSeqs);
$wqdDataDS = WQDDataDataStore::getInstance();
$channelConfigDataStore = ChannelConfigurationDataStore::getInstance();
$rowsLimit = 10;
foreach ($folders as $folder){
 	$lastUploadedDate = $folder->getAQMSLastUplodgtedDate();
	$folderSeq = $folder->getSeq();
	$stationName = $folder->getStationName();
	try{
		$channels = $channelConfigDataStore->FindByFolder($folderSeq);
		$channelNos = getAllChNos($channels);
		$aqmsData = $wqdDataDS->getChannelInfoData($lastUploadedDate, $folderSeq, $channelNos, $rowsLimit);
		if(empty($aqmsData)){
			continue;
		}
		$startDate = $aqmsData[0]["wqdfiledatadated"];
		$toDate = end($aqmsData)["wqdfiledatadated"];
		$startDate = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $startDate);
		$toDate = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $toDate);
		$dateSlices = DateUtils::getDateSlicesByInterval($startDate, $toDate, "15min");
		$chData = array();
		foreach ($channels as $channel){
			$chNo  = $channel->getChannelNumber();
			$chName  = $channel->getChannelName();
			$dataArr = array();
			foreach ($aqmsData as $data){
				$dated = $data["wqdfiledatadated"];
				$chValue = $data["ch".$chNo."value"];
				$channeldata["date"] = $dated;
				$channeldata["chvalue"] = $chValue;
				array_push($dataArr, $channeldata);
			}
			$chData[$chName] = $dataArr;
		}
		$rowsData = getAvgData($chData, $dateSlices,$stationName);
		$fileName = $stationName . ".txt";
		$filePath = "files/".$fileName;
		$existingRowsData = array();
		if(file_exists($filePath)){
			$h = fopen($filePath, "r");
			$j = 0;
			while (($line = fgetcsv($h)) !== FALSE) {
				if($j > 1){
					array_push($existingRowsData, $line);
				}
				$j++;
			}
			$rowsData = array_merge($existingRowsData,$rowsData);
		}
		$rowCount = count($rowsData);
		if($rowCount > $rowsLimit){
			$deleteRowCount = $rowCount - $rowsLimit;
			$rowsData = array_slice($rowsData, $deleteRowCount);
		}
		$filedataArr[0] = $channelNos;
		$filedataArr[1] = getFileFieldNamesArr();
		$mainDataArr = array_merge($filedataArr,$rowsData);
		$fp = fopen ($filePath, "w");
		$count = count($mainDataArr);
		$i = 0;
		foreach ( $mainDataArr as $line ) {
			$i++;
			$val = implode($line, ',');
			if($i < $count){
				$val  = $val ."\n";		
			}
			fputs($fp, $val);
			
		}
		fclose($fp);
		$lastUploadedDate = end($rowsData)[3];
		$lastUploadedDate = DateUtils::StringToDateByGivenFormat("d-m-Y H:i", $lastUploadedDate);
		$lastUploadedDate = $lastUploadedDate->format("Y-m-d H:i:s");
		$fds->updateAQMSLastUploadedDate($folderSeq, $lastUploadedDate);
	}catch (Exception $e){
		$logger->error("Error During CronAPiAQMS for folderseq - $folderSeq",$e);
		echo "Error During CronAPiAQMS for folderseq - " .  $folderSeq . ". Error-" .$e->getMessage();
	}
}


function getAvgData($chDataArr,$dateSlices,$stationName){
	$rows = array();
	$calibrationFlag = 0;
	$maintFlag = 0;
	$remarks = 0;
	foreach ($chDataArr as $key=>$chData){
		$chValue = 0;
		$date = null;
		$count  = 0;
		$lastDate = null;
		$filteredData = $chData;
		foreach ($dateSlices as $dateS){
			$dateSObj = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $dateS);
			if(!empty($lastDate)){
				$data = array();
				$i = 0;
				foreach ($chData as $index=>$cData){
					$dateStr = $cData["date"];
					$date = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $dateStr);
					if($date < $dateSObj){
						$chValue += $cData["chvalue"];
						unset($filteredData[$index]);
						$i++;
					}else{
						break;
					}
				}
				$fromDateStr = $lastDate->format("d-m-Y H:i");
				$toDateStr = $dateSObj->format("d-m-Y H:i");
				$avgValue = $chValue / $i;
				$avgValue = number_format($avgValue, 2, '.', '');
				array_push($data, $stationName);
				array_push($data, $key);
				array_push($data, $fromDateStr);
				array_push($data, $toDateStr);
				array_push($data, $avgValue);
				array_push($data, $calibrationFlag);
				array_push($data, $maintFlag);
				array_push($data, $remarks);
				array_push($rows, $data);
				$chData = $filteredData;
			}
			$lastDate = $dateSObj;
		}
	}
	return $rows;
}


// function getAvgData($chDataArr,$startDateStr,$stationName){
// 	$rows = array();
// 	$calibrationFlag = 0;
// 	$maintFlag = 0;
// 	$remarks = 0;
// 	foreach ($chDataArr as $key=>$chData){
// 		$fromDate = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $startDateStr);
// 		$toDate = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $startDateStr);
// 		$toDate->modify('+15 minutes');
// 		$chValue = 0;
// 		$i = 0;
// 		$data = array();
// 		foreach ($chData as $cData){
// 			$dateStr = $cData["date"];
// 			$date = DateUtils::StringToDateByGivenFormat("Y-m-d H:i:s", $dateStr);
// 			if($date <= $toDate){
// 				$chValue += $cData["chvalue"];
// 				$i++;
// 				continue;
// 			}
// 			$fromDateStr = $fromDate->format("d-m-Y H:i");
// 			$toDateStr = $toDate->format("d-m-Y H:i");
// 			$avgValue = $chValue / $i;
// 			$avgValue = number_format($avgValue, 2, '.', '');
// 			array_push($data, $stationName);
// 			array_push($data, $key);
// 			array_push($data, $fromDateStr);
// 			array_push($data, $toDateStr);
// 			array_push($data, $avgValue);
// 			array_push($data, $calibrationFlag);
// 			array_push($data, $maintFlag);
// 			array_push($data, $remarks);
// 			array_push($rows, $data);
// 			$toDate->modify('+15 minutes');
// 			$fromDate->modify('+15 minutes');
// 			$chValue = $cData["chvalue"];
// 			$data = array();
// 			$i = 0;
// 		}
// 	}
// 	return $rows;
// }



function getFileFieldNamesArr(){
	$fields = array();
	array_push($fields, "Station name");
	array_push($fields, "Parameter");
	array_push($fields, "Date from");
	array_push($fields, "Date to");
	array_push($fields, "Value");
	array_push($fields, "calibrationflag");
	array_push($fields, "maint flag");
	array_push($fields, "Remarks");
	return $fields;
}


function getAllChNos($channels){
	$chNos = array();
	foreach ($channels as $ch){
		array_push($chNos, $ch->getChannelNumber());
	}
	return $chNos;
}