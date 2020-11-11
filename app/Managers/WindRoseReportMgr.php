<?php
  class WindRoseReportMgr{
      
        private static $windRoseReportMgr;
        public static function getInstance(){
            if (!self::$windRoseReportMgr)
            {
                self::$windRoseReportMgr = new WindRoseReportMgr();
                return self::$windRoseReportMgr;
            }
            return self::$windRoseReportMgr;        
        }
        
        public function getWindRoseReport($GET){
            $infoType = $GET['infoTypeRadio'];
            $periodType = $GET['periodTypeRadio'];
            
            $fromDateStr = $GET['fromDate'];
            $toDateStr = $GET['toDate'];
            $timeBase = $GET['timeBase'];
            $timeBaseQuick = $GET['timeBaseQuick'];
            $quickReportType = $GET['quickReportType'];
            if($quickReportType != "null"){
              $timeBase = $timeBaseQuick; 
            }
            $timeBaseFromGet = $timeBase; 
            $folSeqArray = array(); //holds all folderseqs
            foreach($GET as $key=>$value){
                if(strpos($key, "channelNos_") !== false){
                    $folSeq = (float)substr($key, 11);
                    array_push($folSeqArray,$folSeq);
                }
            }
            if(count($folSeqArray) == 0){
                return null;   
            }
            $folSeq = $folSeqArray[0];
            //calculating from to dates from provided $_GET
            $fromToDates = DateUtils::getDatesArrayForStationReports($quickReportType, $fromDateStr, $toDateStr);
           // $dateSlices = DateUtils::getDateSlicesByInterval($fromDate,$toDate,$timeBase);
            $WSChannelNo = 0;
            $WDChannelNo = 0;
            $CCDS = ChannelConfigurationDataStore::getInstance();
            $ChannelsInfo = $CCDS->FindByFolder($folSeq);  
            foreach($ChannelsInfo as $channel){
                if($channel->getChannelName() == "Wind Speed"){
                    $WSChannelNo = $channel->getChannelNumber();  
                }else if($channel->getChannelName() == "Wind Direction"){
                    $WDChannelNo = $channel->getChannelNumber(); 
                }
            }
            
            $fromDate = new DateTime($fromToDates['fromDate']);
            $toDate = new DateTime($fromToDates['toDate']);
            $toDate->setTime(23,59,59);
            
            $fromDate =  $fromDate->format("Y/m/d  H:i:s");
            $toDate =  $toDate->format("Y/m/d  H:i:s");
            
            $WQDS = WQDDataDataStore::getInstance();
            if (strpos($timeBase,'hours') !== false) {
                $timeBase = "1hour";
            }
            $WSArrayDB = $WQDS->getChannel($fromDate,$toDate,$folSeq,$WSChannelNo,$timeBase);
            $WDArrayDB = $WQDS->getChannel($fromDate,$toDate,$folSeq,$WDChannelNo,$timeBase);
            if($WSArrayDB == null ||$WDArrayDB == null){
                return null;    
            }
            $WS = array();
            $WD = array();
            
            $i = 0;
            $WSDateInterval = $WSArrayDB[0][0];
            foreach($WSArrayDB as $ws){
                if(strtotime($ws[0]) == strtotime($WSDateInterval)){
                    $WS[$i++] = floatval($ws[1]);
                    $WSDateInterval = DateUtils::getIncrementedDateStr($WSDateInterval,$timeBaseFromGet);
                }
            }
            $i= 0;
            $WDDateInterval = $WDArrayDB[0][0];
            foreach($WDArrayDB as $wd){
                if(strtotime($wd[0]) == strtotime($WDDateInterval)){
                    $WD[$i++] = floatval($wd[1]);
                    $WDDateInterval = DateUtils::getIncrementedDateStr($WDDateInterval,$timeBaseFromGet);
                }
            }
            $direction_array["N"][] = null;
            $direction_array["NNE"][] = null;
            $direction_array["NE"][] = null;
            $direction_array["ENE"][] = null;
            $direction_array["E"][] = null;
            $direction_array["ESE"][] = null;
            $direction_array["SE"][] = null;
            $direction_array["SSE"][] = null;
            $direction_array["S"][] = null;
            $direction_array["SSW"][] = null;
            $direction_array["SW"][] = null;
            $direction_array["WSW"][] = null;
            $direction_array["W"][] = null;
            $direction_array["WNW"][] = null;
            $direction_array["NW"][] = null;
            $direction_array["NNW"][] = null;
            
            for ($ii = 0; $ii < count($WD); $ii++){
                switch($WD[$ii]){
                    case ($WD[$ii] >= 348.75 or $WD[$ii] < 11.25):
                        $direction_array["N"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 11.25 and $WD[$ii] < 33.75):
                        $direction_array["NNE"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 33.75 and $WD[$ii] < 56.25):
                        $direction_array["NE"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 56.25 and $WD[$ii] < 78.75):
                        $direction_array["ENE"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 78.75 and $WD[$ii] < 101.25):
                        $direction_array["E"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 101.25 and $WD[$ii] < 123.75):
                        $direction_array["ESE"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 123.75 and $WD[$ii] < 146.25):
                        $direction_array["SE"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 146.25 and $WD[$ii] < 168.75):
                        $direction_array["SSE"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 168.75 and $WD[$ii] < 191.25):
                        $direction_array["S"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 191.25 and $WD[$ii] < 213.75):
                        $direction_array["SSW"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 213.75 and $WD[$ii] < 236.25):
                        $direction_array["SW"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 236.25 and $WD[$ii] < 258.75):
                        $direction_array["WSW"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 258.75 and $WD[$ii] < 281.25):
                        $direction_array["W"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 281.25 and $WD[$ii] < 303.75):
                        $direction_array["WNW"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 303.75 and $WD[$ii] < 326.25):
                        $direction_array["NW"][] = $WS[$ii];
                        break;
                    case ($WD[$ii] >= 326.25 and $WD[$ii] < 348.75):
                        $direction_array["NNW"][] = $WS[$ii];
                        break;
                }
            }
            $direction_keys = array_keys($direction_array);
            $max_wind = round(max($WS),0);
            $num_data = count($WS);
            $wind_range_max = $max_wind < 20 ? 25:$max_wind;
            $data_range_array = array(1,5,10,15,20,$wind_range_max);
            foreach ($direction_keys as $direction) {
                    for ($ii = 0; $ii <= 5; $ii++){
                        $count_data[$ii] = 0;
                    }
                    $raw_data = $direction_array[$direction];
                    foreach ($raw_data as $temp_speed) {
                        if ($temp_speed >= 0 and $temp_speed < $data_range_array[0]) {
                            $count_data[0]++;
                        } elseif ($temp_speed >= $data_range_array[0] and $temp_speed < $data_range_array[1]) {
                            $count_data[1]++;
                        } elseif ($temp_speed >= $data_range_array[1] and $temp_speed < $data_range_array[2]) {
                            $count_data[2]++;
                        } elseif ($temp_speed >= $data_range_array[2] and $temp_speed < $data_range_array[3]) {
                            $count_data[3]++;
                        } elseif ($temp_speed >= $data_range_array[3] and $temp_speed < $data_range_array[4]) {
                            $count_data[4]++;
                        } elseif ($temp_speed >= $data_range_array[4]) {
                            $count_data[5]++;
                        }
                    }
                    for ($ii = 0; $ii <= 5; $ii++) {
                        $plot_data[$direction][$ii] = 0;   
                    }
                    for ($ii = 0; $ii <= 5; $ii++) {
                        $plot_data[$direction][$ii] = round( ($count_data[$ii] / $num_data) * 100,2);
                    }
            }
                
            return $plot_data;
        }
        
        
      
  }
?>
