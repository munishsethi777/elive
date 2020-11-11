<?php
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDDataDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "StringConstants.php");
class HPPCBSerive {
	private static $key = 'c2l0ZV8xNDAwLHZlcl8zLjE=########';
	private static $STATE_BORAD_CODE = StringConstants::HPPCB;
	private static $objPHPExcel;
    private static function sum_arrays($currData, $nextData, $channelsCount){
		for($i=1;$i<=$channelsCount;$i++){
			$currVal = $currData["channelsInfo"]["ch" . $i . "value"];
			$nextVal = $nextData["channelsInfo"]["ch" . $i . "value"];
			$currVal = ($currVal + $nextVal);
			$currData["channelsInfo"]["ch" . $i . "value"] = $currVal;
		}
		return $currData;
	}
	private static function avr_arrays($currData,$channelsCount,$arrCount){
		for($i=1;$i<=$channelsCount;$i++){
			$currVal = $currData["channelsInfo"]["ch" . $i . "value"];
			$currVal = $currVal / $arrCount;
			$currData["channelsInfo"]["ch" . $i . "value"] = $currVal;
		}
		return $currData;
	}
	private static function getAggData($data,$channels){
		$dataArr = array();
		$lastData = array();
		$continue = false;
		$sameMinuteCounter = 1;
		for($i=0;$i<count($data);$i++){
			$currData = $data[$i];
			$currDataDated = DateTime::createFromFormat ( "Y/m/d H:i:s", $currData["dated"]);;
			$currDataDatedStr = $currDataDated->format("Y/m/d H:i");
			if($continue){ //if the scenario is aboute having same minute data
				$currData = $lastData;
				$continue = false;
			}
			if($i < count($data)-1){
				$nextData = $data[$i+1];
				$nextDataDated = DateTime::createFromFormat ( "Y/m/d H:i:s", $nextData["dated"]);;
				$nextDataDatedStr = $nextDataDated->format("Y/m/d H:i");
				
				if($currDataDatedStr != $nextDataDatedStr){
					if($sameMinuteCounter>1){ //if its a sameminute scenario then avg with total count of same minutes data
						$currData = self::avr_arrays($currData, count($channels), $sameMinuteCounter);
						$sameMinuteCounter = 1;
					}
					array_push($dataArr,$currData);
				}else{ //scenario is when next date is same minute, sum the next data also
					$lastData = self::sum_arrays($currData, $nextData, count($channels));
					$sameMinuteCounter++;
					$continue = true;
				}
			}else{
				if($sameMinuteCounter>1){ //if its a sameminute scenario then avg with total count of same minutes data
					$currData = self::avr_arrays($currData, count($channels), $sameMinuteCounter);
				}
				array_push($dataArr,$currData);
			}
		}
		return $dataArr;
	}
	
	public static function pushData($logger,$locationSeqs,$stateBoardCode = StringConstants::HPPCB){
		try{
    		self::$objPHPExcel = new PHPExcel();
    		self::$STATE_BORAD_CODE = $stateBoardCode; 
            $fds = FolderDataStore::getInstance();
    		$ccds = ChannelConfigurationDataStore::getInstance();
    		$stations = $fds->FindByLocationSeqs($locationSeqs);
    		$hppcdMetaUtil = HPPCBMetaUtils::getInstance();
    		foreach ( $stations as $station ) {
    			if(empty($station->getIsEnable())){
    				continue;
    			}
    			$stationSeq = $station->getSeq ();
    			$lastSeq = $station->getLastSpcbSyncedSeq();
    			if($lastSeq == 0){
    			    echo ("Last seq for station ".$station->getStationName()." is not set<br>");
                    $logger->info("Last seq for station ".$station->getStationName()." is not set");
    			    continue;
    			    
    			}
    			$locationSeq = $station->getLocationSeq();
    			$siteId = $hppcdMetaUtil->getSiteIdByLocation($locationSeq);
    			self::$key = $hppcdMetaUtil->getKeyByLocation($locationSeq);
    			$id = explode("_", $siteId)[1];
    			$channels = $ccds->FindByFolder($stationSeq);
    			$WQDDS = WQDStackDataStore::getInstance();
    			$WDDS = WQDDataDataStore::getInstance();
    			$actData = array();
    			
    			if($station->getStationType() == "aqms"){
    				$actData = $WDDS->getChannelsInfo($stationSeq,$lastSeq,40);
    			}else {
    				$actData = $WQDDS->getChannelsInfo($stationSeq,$lastSeq,40);
    			}
    			if(count($actData) == 0){
    			    echo ("No further data for station ".$station->getStationName()." after $lastSeq<br>");
                    $logger->info("No further data for station ".$station->getStationName()." after $lastSeq");
                    
    			    continue;
    			}
    			$data = self::getAggData($actData,$channels);
    			$lastSeqToUpdated = $lastSeq;
    			$count = 0;
    			$dataCounter = 1;
    			
    			foreach ($data as  $chData){
    				$channelInfo = $chData["channelsInfo"];
    				$lastSeqToUpdated = $chData ["seq"];
    				$date = $chData ["dated"];
    				$dateBefore15Min = new DateTime();
    				$dateBefore15Min = $dateBefore15Min->modify("-15 minutes");
    				$date = DateTime::createFromFormat ( "Y/m/d H:i:s", $date );
    				$urls = $hppcdMetaUtil->getTargetUrl($stateBoardCode);
    				$target_url = $urls[0]; //"http://hppcb.glensserver.com/HPPCB/realTimeUpload";
    				if($dateBefore15Min > $date){
    					$target_url = $urls[1];//"http://hppcb.glensserver.com/HPPCB/delayedUpload";
    				}
    				$timestamp = $date->getTimestamp ();
    				$dateForFileName = $date->format("YmdHis");
    				$totalChannels = 1;
    				if(self::$STATE_BORAD_CODE == StringConstants::MPCB){
    					$totalChannels = count($channels);
    				}
    				$i = 1;
    				foreach ($channels as $ch){
    					$metaData = $hppcdMetaUtil->getMetaByChannel($ch->getSeq());
    					if(empty($metaData) && $totalChannels > 1){
    						$totalChannels--;
    						continue;
    					}
    					$moniterId = $metaData["monitiring_id"];
    					$fname = $siteId."_".$moniterId.'_'. $dateForFileName .".csv";
                        $zipName = $siteId."_". $moniterId ."_". $dateForFileName .".zip";
    					$parameterName = $ch->getChannelRealName();
    					if(empty($parameterName)){
    						$parameterName = $ch->getChannelName();
    					}
    					$chNo = $ch->getChannelNumber();
    					$chValue = $channelInfo ["ch" . $chNo . "value"];
                        //New check to confirm bad default value of PM10 of Lloyds
                        if(($ch->getSeq() == 3791) && ($chValue == 985)){
                            $chValue = 0;
                        }
                        
    					self::createFile($i,$metaData, $id, $siteId, $parameterName, $chValue, $timestamp,$totalChannels);
    					if(self::$STATE_BORAD_CODE == StringConstants::HPPCB){
    						self::makeCurl($fname,$zipName, $target_url,$siteId,$stationSeq,$lastSeqToUpdated,$logger);
    					}else{
    						$i++;
    					}
    					
    				}
    				if(self::$STATE_BORAD_CODE == StringConstants::MPCB){
    					self::makeCurl($fname,$zipName, $target_url,$siteId,$stationSeq,$lastSeqToUpdated,$logger);
    				}
    				$count++;
    			}
    		}
		}catch(Exception $e){
		    var_dump($e);
		}
	}
	
	private static function createFile($i,$metaData, $id, $siteId, $parameterName, $chValue, $timestamp,$totalChannels){
		if($i == 1){
			self::$objPHPExcel = new PHPExcel();
			self::$objPHPExcel->getProperties()->setCreator("Manager")
			->setLastModifiedBy("Manager")
			->setTitle("Office 2007 XLSX Test Document")
			->setSubject("Office 2007 XLSX Test Document")
			->setDescription("High Value Logs")
			->setKeywords("office 2007 openxml php")
			->setCategory("High Value Logs");
		}
		//$objPHPExcel = new PHPExcel();
       	self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $siteId);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, $siteId);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $metaData["monitiring_id"]);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $metaData["analyzer_id"]);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $metaData["parameter_id"]);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, $parameterName);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, $chValue);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, $metaData["unit_ID"]);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, "U");
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$i, $chValue);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K".$i, $timestamp);
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L".$i, "0");
        self::$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M".$i, "0");
        if($i == $totalChannels){
			self::$objPHPExcel->getActiveSheet()->setTitle('metadata');
			$fileName = "dataFile.csv";
			$objWriter = PHPExcel_IOFactory::createWriter(self::$objPHPExcel, 'CSV');
			$objWriter->save(str_replace(__FILE__,'data_org.csv',__FILE__));
        }
	}
	
	private static function makeCurl($fname,$zipName,$target_url,$siteId,$stationSeq,$lastSeq,$logger){
		$fds = FolderDataStore::getInstance();
		$hppcdMetaUtil = HPPCBMetaUtils::getInstance();
		$fr=fopen('data_org.csv','r');
		//$fw=fopen('data.csv','w');
        $fw=fopen($fname,'w');
		if(!$fr){
			var_dump(error_get_last());
		}
		$crypt = new Encryption(self::$key);
        $buffer;
        $dResult = "";
        
        if ($fr) {
		while(!feof($fr))
            {
                $buffer=fread($fr,4128);
                $buffer=str_replace('"', '', $buffer);
                if(self::$STATE_BORAD_CODE == StringConstants::HPPCB){
                	$buffer = str_replace(array("\r\n", "\n", "\r"), '', $buffer);
                }else{
                   // $buffer = str_replace(array("\n"), '\r', $buffer);
                    $buffer = substr($buffer, 0, -1);
                }
                $result= $crypt->encrypt($buffer,self::$key);
               // $dResult .= $crypt->decrypt($result,self::$key);
                $dResult .= $result;
                false!==$result && fwrite($fw,$result);
            }
        }
		fclose($fw);
		fclose($fr);
		$files = array($fname,"metadata.csv");
		FileSystemUtils::createZip($zipName, $files);
		//$ddResult .= $crypt->decryptN($result,self::$key);
		//echo "<br>dycrpt Result :- <br>" . $ddResult . "<br>";
		
		//return;
		$Date = new DateTime();
		$Date->modify('-60 seconds');
		$dateStr = $Date->format("Y-m-d\TH:i:s\Z");
		$crypt = new Encryption(self::$key);
		$cfile = new CURLFile($zipName);
		$post = array (
				'file' => $cfile
		);
		$ch = curl_init();
		$versionCode = $hppcdMetaUtil->getVersionCode(self::$STATE_BORAD_CODE);
		$authData = "$siteId,$versionCode,$dateStr,".self::$key;
		$encryptedAuth = $crypt->encrypt($authData,self::$key);
		$auth = 'Authorization: Basic ' . $encryptedAuth;
		//$dAuth = $crypt->decrypt($encryptedAuth,self::$key);
		echo "<br>$target_url<br>";
		$timeStamp = 'Timestamp:'. $dateStr;
		$siteKey = "siteid:$siteId";
		$headerArr = array($timeStamp,$siteKey,$auth);
		var_dump($headerArr);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headerArr);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_URL, $target_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec ($ch);
		if ($result === FALSE) {
			echo "Error sending" . $fname .  " " . curl_error($ch) ."<br>";
            $logger->error("Error sending" . $fname .  " " . curl_error($ch));
            curl_close ($ch);
		}else{
			curl_close ($ch);
			$jsonResult = json_decode($result);
			$status = $jsonResult->status;
			if($status == "Success"){
				$fds->updateLastSpcbSyncedSeq($lastSeq, $stationSeq);
			}
            echo  "<br>Result: " . $result ."<br>\n";
            $logger->info("Result :". $result);
		}
		 FileSystemUtils::deleteFile($zipName);
         FileSystemUtils::deleteFile($fname);
	}
}
