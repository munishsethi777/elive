<?php
require_once('../IConstants.inc');
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "/Utils/DateUtils.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
require_once ($ConstantsArray ['dbServerUrl'] . "StringConstants.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDDataDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDStackDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDAQMSPFileDataStore.php");
Logger::configure("/home/envirote/public_html/app/log4php/log4php.xml");
$logger = Logger::getLogger("CronHPPCBDailyLogger");
$WQDDS = WQDStackDataStore::getInstance();
$WDDS = WQDDataDataStore::getInstance();
$WAQMSPDS = WQDAQMSPFileDataStore::getInstance();
$ccds = ChannelConfigurationDataStore::getInstance();
$fds = FolderDataStore::getInstance();
$locationSeqs = "180,178";
//$locationSeqs = "178";
$limit = 3;
$stations = $fds->FindByLocationSeqs($locationSeqs);
$count = 1;
foreach ($stations as $station){
    $stationSeq = $station->getSeq();
    echo "<br>$count  - StationSeq = " . $stationSeq."<br>";
    $count++;
    $locationSeq = $station->getLocationSeq();
    $lastSeq = $station->getLastSpcbSyncedSeq();
    if($lastSeq == 0){
        echo ("Last seq for station ".$station->getStationName()." is not set<br>");
        $logger->info("Last seq for station ".$station->getStationName()." is not set");
        continue;
    }
    $channels = $ccds->FindByFolder($stationSeq);
    $data = array();
    if($station->getStationType() == "aqms"){
        $data = $WDDS->getChannelsInfo($stationSeq,$lastSeq,40);
    }else if($station->getStationType() == "aqmsp"){
        $data = $WAQMSPDS->getChannelsInfo($stationSeq,$lastSeq,$limit);
    }else{
        $data = $WQDDS->getChannelsInfo($stationSeq,$lastSeq,40);
    }
    $apiUrl = "https://jsac.jharkhand.gov.in/Pollution/WebService.asmx/";
    $indId = "ASIPL";
    $stationId = "ASIPL_STACK_1";
    $analyserId = "1603003";
    if($locationSeq == 180){
        $apiUrl .= "GET_PM_DATA";
        $indId = "BCL";
        $stationId = "Ambient";
        $analyserId = "BAM1";
        $limit = 3;
    }elseif($locationSeq == 178){
        $apiUrl .= "getdata";
        $limit = 15;
        $indId = "MEDICARE_LOHARDAGA";
        $stationId = "MEDICARE_LOHARDAGA_STACK_1";
        $analyserId = "5000744";
    }
    $postFields = array("vender_id"=>11,"industry_id"=>$indId,'stationId'=>$stationId,'analyserId'=>$analyserId,'flag'=>'U');
    $targetUrl = "http://www.satyainfopages.in/jspcb/JSPCPushDataApi.php";
    if(empty($data)){
        echo "Data not found for station id - $stationSeq after lastseq - $lastSeq <br>";
    }
    if(count($data) < $limit){
        echo count($data) . " rows found for station id - $stationSeq after lastseq - $lastSeq <br>";
        continue;
    }
    
    foreach ($channels as $ch){
        if($locationSeq == 178){
            if(empty($ch->getIsVisible())){
                echo "<br>" . $ch->getChannelName() . " - is not visible<br>";
                continue;
            }
        }
        $i = 0;
        $sumValue = 0;
        $parameterName = $ch->getChannelRealName();
        if(empty($parameterName)){
            $parameterName = $ch->getChannelName();
        }
        $chNo = $ch->getChannelNumber();
        $chUnit = $ch->getChannelUnit();
        foreach ($data as  $chData){
            $post_array_string = "";
            $i++;
            $channelInfo = $chData["channelsInfo"];
            $lastSeq = $chData ["seq"];
            $date = $chData ["dated"];
            $dateObj = DateUtils::StringToDateByGivenFormat("Y/m/d H:i:s", $date);
            $date = $dateObj->format("Y-m-d H:i A");//2018-03-05:2007:15:01.AM
            echo "Date:-" . $date . "<br>";
            $chValue = $channelInfo ["ch" . $chNo . "value"];
            $sumValue += $chValue;
            if($i < $limit){
                continue;
            }
            $mean_average = $sumValue / $i;
            $mean_average = number_format($mean_average,2,'.','');
            echo "Avg value - " . $mean_average .  " for station id - $stationSeq <br>";
            $postFields["parameter"]=$parameterName;
            $postFields["timestamp"]=$date;
            $postFields["unit"]=$chUnit;
            $postFields["processValue"]=$mean_average;
            $postFields["scaledValue"]=$mean_average;
            $postFields["targetUrl"]=$apiUrl;
            $postFields["call"]="pushJspcData";
            $ch = curl_init();
            foreach($postFields as $key=>$value)
            {
                $post_array_string .= $key.'='.$value.'&';
            }
            $post_array_string = rtrim($post_array_string,'&');
            echo "<br>Post Data :- <br>" . $post_array_string . "<br>";
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array_string);
            curl_setopt($ch, CURLOPT_URL, $targetUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result = curl_exec ($ch);
           
            if ($result === FALSE) {
                echo "Error sending  " . curl_error($ch) ."<br>";
                curl_close ($ch);
            }else{
                curl_close ($ch);
                //$fds->updateLastSpcbSyncedSeq($lastSeq, $stationSeq);
                echo  "Result: " . $result ."<br>\n";
                //echo  "Last Seq Updated : " . $lastSeq ." for station seq - $stationSeq <br>\n";
            }
            $i = 0;
            $sumValue = 0;
         }
     }
     
    if(!empty($data)){
        $fds->updateLastSpcbSyncedSeq($lastSeq, $stationSeq);
        echo  "Last Seq Updated : " . $lastSeq ." for station seq - $stationSeq  and last date is $date <br>\n";
    }
   
}