<?php
require_once('IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] . "Utils/CPCBService.php");
require($ConstantsArray['dbServerUrl'] . "DataStoreMgr//LocationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] . "Utils/MailerUtils.php");
require_once($ConstantsArray['dbServerUrl'] . "DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDDataDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDStackDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDAQMSPFileDataStore.php");
Logger::configure('/home/envirote/public_html/app/log4php/log4php.xml');
$logger = Logger::getLogger("CronHPPCBDailyLogger");
$WQDDS = WQDStackDataStore::getInstance();
$WDDS = WQDDataDataStore::getInstance();
$WAQMSPDS = WQDAQMSPFileDataStore::getInstance();
$ccds = ChannelConfigurationDataStore::getInstance();
$locationSeqs = "33,36";//"33,34,36";
die;
$fds = FolderDataStore::getInstance();
    try{
        $stations = $fds->FindByLocationSeqs($locationSeqs);
        $dataMainArr = array();
        foreach ( $stations as $station ) {
            $lastSeq = $station->getLastSpcbSyncedSeq ();
            $locationSeq = $station->getLocationSeq();
            if($lastSeq == 0){
                echo ("Last seq for station ".$station->getStationName()." is not set<br>");
                $logger->info("Last seq for station ".$station->getStationName()." is not set");
                continue;
            }
            $stationSeq = $station->getSeq ();
            $channels = $ccds->FindByFolder($stationSeq);
            $chdata = array();
            if($station->getStationType() == "aqms"){
                $chdata = $WDDS->getChannelsInfo($stationSeq,$lastSeq,30);
            }else if($station->getStationType() == "aqmsp"){
                $chdata = $WAQMSPDS->getChannelsInfo($stationSeq,$lastSeq,30);
            }else{
                $chdata = $WQDDS->getChannelsInfo($stationSeq,$lastSeq,30);
            }
            if(empty($chdata)){
                echo "No Data found for station seq = $stationSeq";
                continue;
            }
            foreach ( $channels as $channel ) {
                $isVisible = $channel->getIsVisible ();
                if (empty ( $isVisible )) {
                    continue;
                }
                $device = $channel->getDeviceId ();
                $paramArr = array();
                foreach ( $chdata as $chData ) {
                    $date = $chData ["dated"];
                    $lastSeq = $chData ["seq"];
                    $date = DateTime::createFromFormat ( "Y/m/d H:i:s", $date );
                    $timestamp = $date->getTimestamp ();
                    $channelInfo = $chData ["channelsInfo"];
                    $chName = $channel->getChannelRealName ();
                    if (empty ( $chName )) {
                        $chName = $channel->getChannelName ();
                    }
                    $chName = strtolower ( $chName );
                    $chNo = $channel->getChannelNumber ();
                    $chUnit = $channel->getChannelUnit ();
                    $chValue = $channelInfo ["ch" . $chNo . "value"];
                    $param = array (
                        "parameter" => $chName,
                        "value" => $chValue,
                        "unit" => $chUnit,
                        "timestamp" => $timestamp . "000",
                        "flag" => "U"
                    );
                    array_push($paramArr,$param);
                }
                $data = array (
                    "deviceId" => $device,
                    "params" => $paramArr,
                );
                array_push ( $dataMainArr, $data );
            }
            $datajson = json_encode($dataMainArr);
            echo("<br>Request :- ".$datajson);
            //$apiKey = "RW52aXJvem9uZSBJbnN0cnVtZW50IGFuZCBFcXVpcG1lbnRz";
            //$apiKey = "MTgwNDIwMTlfZW52aXJvem9uZV9pbnN0cnVtZW50X2FuZF9lcXVpcG1lbnRzX3ZlbmRvcl8xMzMwMTI=";
            $apiKey = "MTEwMzIwMjBfZW52aXJvdGVjaF9saXZlXzEyNDU1MA==";
            //echo("<br>API Key :- ".base64_encode($apiKey));
            $auth_header = 'Authorization: Basic ' . $apiKey;
            $ch = curl_init();
            
            $stationSeqForURL = $stationSeq;
            if($stationSeq == 64){
                $stationSeqForURL = "ETP_OUTLET";
            }
            $url = "http://dpcccems.nic.in/dlcpcb-api/api/industry/". $locationSeq ."/station/$stationSeqForURL/data";
            echo("<br> \r\n URL :- " . $url . "<br>");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/Json', $auth_header));
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $datajson);
            curl_setopt($ch, CURLOPT_URL, $url);
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //echo("<br>CURLSTART". date("Y-m-d H:i:s"));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                echo "Error sending  " . curl_error($ch) ."<br>";
                curl_close ($ch);
            }else{
                curl_close ($ch);
                $fds->updateLastSpcbSyncedSeq($lastSeq, $stationSeq);
                echo  "Result: " . $result ."<br>\n";
                echo  "Last Seq Updated : " . $lastSeq ." for station seq - $stationSeq <br>\n";
            }
        }
        
    }catch(Exception $e){
        echo("<br>\r\n Error occured Cron API :- ". $e->getMessage());
    }
?>
