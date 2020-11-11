<?php
  class APPCBGenerator{
    private static $appcbGenerator;
    private static $channelsList;
    public static function getInstance(){
        if (!self::$appcbGenerator)
        {
            self::$appcbGenerator = new APPCBGenerator();
            self::$channelsList = array();
            array_push(self::$channelsList,'CO');
            //array_push(self::$channelsList,'Ozone');
            array_push(self::$channelsList,'Ozone(O3)');
            array_push(self::$channelsList,'PM10');
            array_push(self::$channelsList,'PM2.5');
            array_push(self::$channelsList,'SO2');
            array_push(self::$channelsList,'NO2');
            array_push(self::$channelsList,'Pb');
            array_push(self::$channelsList,'NH3');
            array_push(self::$channelsList,'Benzene');
            array_push(self::$channelsList,'Arsenic');
            array_push(self::$channelsList,'Nickel');
            array_push(self::$channelsList,'BenzoPyrene');
            return self::$appcbGenerator;
        }
        return self::$appcbGenerator;        
    }
    private static function getChannelNumberNameArray($channelsList,$channelsInfo){
        $channelsNoArray = array();
        foreach($channelsInfo as $channelInfo){
            $chInfo = new ChannelConfiguration();
            $chInfo = $channelInfo;
            if(in_array($chInfo->getChannelName(), $channelsList)){
                $channelsNoArray[$chInfo->getChannelNumber()] = $chInfo->getChannelName();    
            }
        }
        return $channelsNoArray;
    }
    public function generateFile(){
        echo ("APPCB Generator starts...\n");
        $CCDS = ChannelConfigurationDataStore::getInstance();
        $FDS = FolderDataStore::getInstance();
        $WQDS = WQDDataDataStore::getInstance();
        $folders = $FDS->FindByLocationSeqs("7,10");
        echo "<br>--------TotalFolders:". count($folders);
        foreach($folders as $folder){
            $stationName = $folder->getFolderName();
            $stationNumber = substr($stationName,7);
            $districtCode = "kham";
            $industryCode = "ITCPBCM";
            //if($folder->getSeq() == 20){
			if($folder->getLocationSeq() == 10){
            	$stationNumber = 1;
            	if($folder->getSeq() == 26){
					$stationNumber = 2;
				}else if($folder->getSeq() == 27){
					$stationNumber = 3;
				}else if($folder->getSeq() == 28){
					$stationNumber = 4;
				}else if($folder->getSeq() == 29){
					$stationNumber = 5;
				}
				$districtCode = "hyde";
            	$industryCode = "APPCB";
            }
            $folSeq = $folder->getSeq();
            
            $latestDataRows = $WQDS->getChannelsLatestInfo($folSeq);
            echo ("FolderSeq:" . $folSeq ." FolderName:".$folder->getFolderName() ."<br>\n");
            //echo ("<br>\nTotal Rows found:". count($latestDataRows));
            echo ("<br>\n ToDateFromLatestData ". $latestDataRows['dated']);
            if(count($latestDataRows)>0){
                $channelConfigs = $CCDS->FindByFolder($folSeq);    
                $toDateStr = $latestDataRows['dated'];
                $toDate = new DateTime($toDateStr);
                $fromDate = new DateTime($toDateStr);
                $fromDate->setTime(0,0,0);
            	$fromDateStr = DateUtils::getSQLDateFromDateObj($fromDate);
            	echo ("<br>From:". $fromDateStr ."<br>\n");
            	echo ("To:". $toDateStr."<br>\n");
	            $dataArray = $WQDS->getAllDataByFol($fromDateStr, $toDateStr, $folSeq);
                $channelNoArr = self::getChannelNumberNameArray(self::$channelsList,$channelConfigs);
                $addDatesData = array();   
                foreach($dataArray as $data){
                    $channelData = array();
                    $dated = $data['wqdfiledatadated'];
                    $chValues = array();
                    $chStatuses = array();
                    foreach($channelNoArr as $key=>$value){
                        $vl = (float)$data["ch".$key."value"];
                        if($vl < 0 ){
                            $vl = "n.o";
                        }
                        array_push($chValues, $vl);
                        array_push($chStatuses, (float)$data["ch".$key."status"]);    
                    }
                    $allDatesData[strtotime($dated)]['channelValue'] = $chValues;
                    $allDatesData[strtotime($dated)]['channelStatuses'] = $chStatuses;
                }
                $channelArr = array();
                foreach($channelNoArr as $chName){
                    array_push($channelArr,$chName);    
                }
                
                $dataSlices = DateUtils::getDateSlicesByInterval($fromDate,$toDate,"1hour");
                $dataRows = $WQDS->getAverageDataByDataArrayDataSlices("1hour",$allDatesData,$dataSlices,$channelArr,"normal",array());
	        
            }else{
            	echo " \n<br>Null FromDate";
            }
            
            if(count($dataRows) > 1){
                $repStr = "";
                foreach($dataRows as $dateStr=>$row){
                    $chNameValueArr = array();
                    foreach($row as $rowId => $rowVal){
                        $chNameValueArr[$channelArr[$rowId]] = $rowVal;     
                    }
                    $repStr .= $districtCode ;
                    $repStr .= ",". $industryCode;
                    $repStr .= ",".  $stationNumber .",";
                    $dated = new DateTime($dateStr);
                    $repStr .= $dated->format("m/d/Y h:i:s A");
                    $channelsList = self::$channelsList;
                    foreach($channelsList as $chName){
                        if($chNameValueArr[$chName] != null){
                            $repStr .= ",". $chNameValueArr[$chName];
                        }else{
                            $repStr .= ",NA";
                        }      
                    }
                    $repStr .= "\r\n";              
                }

		echo $repStr;
		//$fileName = "khmmitcl".$folder->getFolderName(). date("Ymd") .".txt";
		//$fileName = "air". date("Ymd") .".txt";
		$fileName = "air". $fromDate->format("Ymd") .".txt";
		$repFileName = "/home/envirote/public_html/Generated/APPCB/". $folSeq ."/" . $fileName;
		//$repFileName ="d:". $folSeq ."/" . $fileName;
		if(!file_exists(dirname($repFileName))){
			mkdir(dirname($repFileName), 0777, true);
		}
		$fh = fopen($repFileName, 'w') or die("can't open file");
		fwrite($fh, $repStr);
		fclose($fh);
	    }
		unset($channelArr);
		unset($latestDataRows);
		unset($channelConfigs);
		unset($channelNoArr);
		unset($dataSlices);
		unset($dataRows);
		unset($chNameValueArr);
		unset($allDatesData);
		unset($channelsList);
	    
       }//for loop ends
    }//method generatefile ends
    
    
     
  }
?>