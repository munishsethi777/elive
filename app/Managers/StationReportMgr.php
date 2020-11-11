<?php
  Class StationReportMgr{
        private static $stationReportMgr;
        public static function getInstance(){
            if (!self::$stationReportMgr)
            {
                self::$stationReportMgr = new StationReportMgr();
                return self::$stationReportMgr;
            }
            return self::$stationReportMgr;        
        }
        public function getStationReport($GET){
            $infoType = $GET['infoTypeRadio'];
            $periodType = $GET['periodTypeRadio'];
            $folSeq = $GET['stationsSelect'];
            $fromDateStr = $GET['fromDate'];
            $toDateStr = $GET['toDate'];
            $timeBase = $GET['timeBase'];
            $channelNoArray = $GET['channelNos'];
            if($channelNoArray == null){
                return null;
            }
            if ($periodType != "recent" && 
                        false === strtotime($fromDateStr)){
                return null;
            }
            if ($periodType == "period" && 
                        (false === strtotime($fromDateStr) || 
                        false === strtotime($toDateStr ))){
                return null;
            }
            $datesArr = self::getFromToDates($periodType, $fromDateStr, $toDateStr);
            
            if($infoType == "grid"){
                $gridDataJson = self::getGridData(
                    $datesArr['fromDate'],
                    $datesArr['toDate'],
                    $folSeq, 
                    $timeBase, 
                    $channelNoArray,$periodType);
                return $gridDataJson;
            }elseif($infoType == "graph"){
                $graphDataJson = self::getGridData($datesArr['fromDate'],$datesArr['toDate'],
                        $folSeq, $timeBase, $channelNoArray,$periodType);
                
                $graphChannelsJSON = array();
                $graphDataJSON = array();
                foreach($graphDataJson['channels'] as $key=>$value){
                    array_push($graphChannelsJSON, $key);
                    $graphDataJSON[$key] = array();
                }
                
                $graphDatesJSON = array();
                foreach($graphDataJson['data'] as $data){
                    array_push($graphDatesJSON, $data['dated']);
                    foreach($data['values'] as $key1=>$val1){
                        array_push($graphDataJSON[$graphChannelsJSON[$key1]] , (float)$val1);
                    }
                }
                $finalJSON = new ArrayObject();
                $finalJSON['dates'] = $graphDatesJSON;
                $finalJSON['values'] = $graphDataJSON; 
                return $finalJSON;
                
            }elseif($infoType == "export"){
               $gridDataJson = self::getGridData(
                    $datesArr['fromDate'],
                    $datesArr['toDate'],
                    $folSeq, 
                    $timeBase, 
                    $channelNoArray,$periodType);
               $FDS = FolderDataStore::getInstance();
               $folder = $FDS->FindBySeq($folSeq);
               ExportUtils::exportStationGridReport($gridDataJson,$folder->getFolderName());
               return null; 
            }
        }
        private function getGridData($fromDate, $toDate, $folSeq, $timeInterval, $channelNoArr,$periodType){
            return self::getDataJSON($fromDate, $toDate, $folSeq, $timeInterval, $channelNoArr,$periodType);
        }
        private function getDataJSON($fromDate, $toDate, $folSeq, $timeInterval, $channelNoArr,$periodType){
            $CCDS = ChannelConfigurationDataStore::getInstance();
            $ChannelsInfo = $CCDS->FindByFolderAndChannelNos($folSeq,$channelNoArr);
            $chArr = new ArrayObject();
            $chNumbersArr = array();
            foreach($ChannelsInfo as $channel){
                $channelUnit = $channel->getChannelUnit();
                $channelName = $channel->getChannelName();
                
                if(ConvertorUtils::getPrescribedUnit($channelName) != null){
                    $channelUnit = ConvertorUtils::getPrescribedUnit($channelName);
                }
                $channelUnit = mb_check_encoding($channelUnit, 'UTF-8') ? $channelUnit : utf8_encode($channelUnit);
                $chArr[$channelName] = $channelUnit;
                array_push($chNumbersArr,$channel->getChannelNumber()); 
            }
            $jsonData['channels'] = $chArr;
            $WQDS = WQDDataDataStore::getInstance();
            $allDatesData = array();
            if($periodType == "recent"){
                $latestInfo = $WQDS->getChannelsLatestInfo($folSeq);
                $channelData = array();
                $channelData['dated'] = $latestInfo['formatedDated'];
                $chValues = array();
                foreach($chNumbersArr as $chNumber){
                    array_push($chValues,(float)$latestInfo['channelsInfo']['ch'.$chNumber.'value']);
                }
                $channelData['values'] = $chValues;
                array_push($allDatesData,$channelData);
            }else{
                $channelsArray = $WQDS->getChannels($fromDate,$toDate,$folSeq,$channelNoArr,$timeInterval);
                foreach($channelsArray as $channels){
                    $channelData = array();
                    $channelData['dated'] = $channels['wqdfiledatadated'];
                    $chValues = array();
                    $cnt = count($channels)/2; // divided by 2 becos array produces both channelname and int values
                    for($i = 1;$i<$cnt;$i++){
                        array_push($chValues, (float)$channels[$i]);
                    }
                    $channelData['values'] = $chValues;
                    array_push($allDatesData,$channelData);
                }
            }
            
            $allDatesData = self::getPLConvertedValueByChannel($allDatesData,$chArr);
            
            $jsonData['data'] = $allDatesData;
            return $jsonData;
        }  
        
        private function getFromToDates($periodType, $fromDateStr, $toDateStr){
            $dateArr = null;
            $fromDate = new DateTime($fromDateStr);
            $toDate = new DateTime($fromDateStr);
            $fromDate->setTime(0,0,0);
            if($periodType == "daily"){
                $toDate = $toDate->setTime(23,59,59);
            }else if($periodType == "weekly"){
                $toDate->add(new DateInterval('P6D'));
                $toDate->setTime(23,59,59);
            }else if($periodType == "monthly"){
                $toDate->add(new DateInterval('P1M'));
                $toDate = $toDate->setTime(0,0,0);
            }else if($periodType == "period"){
                $fromDate = new DateTime($fromDateStr);
                $toDate = new DateTime($toDateStr);
            }
            
            if($periodType != "recent"){
                $fromDateAStr = $fromDate->format("Y/m/d  H:i:s");
                $toDateAStr = $toDate->format("Y/m/d  H:i:s");
                $dateArr = array('fromDate'=> $fromDateAStr, 'toDate'=> $toDateAStr);
            }
            return $dateArr;
            
        }
        
        private function getPLConvertedValueByChannel($dateDataArr,$channelsArray){
            //ArrStructure {"dated":xxxx,"values":[22,33,44]}
           $mainArray = array();
           $channelNamesArr = array();
           foreach($channelsArray as $chKey => $chUnit){
               array_push($channelNamesArr ,$chKey);
           }
           foreach($dateDataArr as $dateData){
                $arrItem = new ArrayObject();
                $arrItem['dated'] = $dateData['dated'];
                $arrItemValuesOnly = array();
                foreach($dateData['values'] as $key => $value){
                    $plValue = ConvertorUtils::getPrescribedValue($channelNamesArr[$key],$value);
                    array_push($arrItemValuesOnly,$plValue);
                }
                $arrItem['values'] = $arrItemValuesOnly;
                array_push($mainArray,$arrItem);
           }
           return $mainArray;
           $arrObj = new ArrayObject($rows);        
           $it = $arrObj->getIterator();
           $WQDChannelsInfo = array();
           $dateArr = array();
           $valArr = array(); 
           $jsonArry = array();
           while( $it->valid()){
             $key = $it->key();
                 $value = $it->current();
                 $date = new DateTime($value[0]);
                 $dateArr[$key] = "'" . $date->format("d-m H:i" ) . "'";    
                 if($isConvertPL == true){
                    $valArr[$key]  = ConvertorUtils::getPrescribedValue($chName,$value[1]);
                 }else{
                    $valArr[$key]  = $value[1];
                 }
                 $it->next();                                           
           } 

            $jsondataDate =  json_encode($dateArr);
            $josnDataValue = json_encode($valArr);
            $jsondataDate = str_replace("\"","",$jsondataDate); 
            $josnDataValue = str_replace("\"","",$josnDataValue);    
            $jsonArry[0] = $jsondataDate;
            $jsonArry[1] = $josnDataValue; 
            return $jsonArry;                            
      }
  }
?>