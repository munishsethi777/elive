<?php
  class ManikgarhGenerator{
    private static $manikGenerator;
    private static $channelsList;
    public static function getInstance(){
        if (!self::$manikGenerator)
        {
            self::$manikGenerator = new ManikgarhGenerator();
            self::$channelsList = array();
            array_push(self::$channelsList,'Rack Temperature');
            array_push(self::$channelsList,'SO2');
            array_push(self::$channelsList,'NO');
            array_push(self::$channelsList,'NO2');
            array_push(self::$channelsList,'NOx');
            array_push(self::$channelsList,'PM10');
            array_push(self::$channelsList,'PM2.5');
            return self::$manikGenerator;
        }
        return self::$manikGenerator;        
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
        echo ("MANIKGARH Generator starts...\n");
        $CCDS = ChannelConfigurationDataStore::getInstance();
        $FDS = FolderDataStore::getInstance();
        $WQDS = WQDDataDataStore::getInstance();
        $folders = $FDS->FindByLocationSeqs("11");
        echo "<br>--------TotalFolders:". count($folders);
        foreach($folders as $folder){
            $folSeq = $folder->getSeq();
            //$latestDataRows = $WQDS->getChannelsLatestInfo($folSeq);
            echo ("FolderSeq:" . $folSeq ." FolderName:".$folder->getFolderName() ."<br>\n");
            echo ("<br>\n ToDateFromLatestData ". $latestDataRows['dated']);
            $channelConfigs = $CCDS->FindByFolder($folSeq);
            $toDateStr = date('Y/m/d H:00:00');
            $fromDateStr = date('Y/m/d H:00:00', strtotime('-1 hour'));
            
            //$toDateStr = "2014/02/15 18:00:00";
//            $fromDateStr = "2014/02/15 17:00:00";
            
            $toDate = new DateTime($toDateStr);
            $fromDate = new DateTime($fromDateStr);
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
            $dataSlices = DateUtils::getDateSlicesByInterval($fromDate,$toDate,"15min");
            $dataRows = $WQDS->getAverageDataByDataArrayDataSlices("15min",$allDatesData,$dataSlices,$channelArr,"normal",array());
	        $repStr = "";
            if(count($dataRows) > 1){
                $repStr = "";
                foreach($dataRows as $dateStr=>$row){
                    $chNameValueArr = array();
                    foreach($row as $rowId => $rowVal){
                        $chNameValueArr[$channelArr[$rowId]] = $rowVal;     
                    }
                    $dated = new DateTime($dateStr);
                    $repStr .= $dated->format("Y-m-d H:i:s");
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
		        $currDate = new DateTime();
		        $fileName = "MHCHDMANIKGARHC011_". $dated->format("YmdHis") .".csv";
		        $repFileName = "/home/envirote/public_html/Generated/Manikgarh/". $folSeq ."/" . $fileName;
                //$repFileName = $fileName;
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