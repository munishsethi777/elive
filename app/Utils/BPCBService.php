<?php
class BPCBService {
    private static $bpcbService;
    private static $logger;
    private $chData;
    public static function getInstance() {
        if(empty(self::$hrpcbService)){
            self::$bpcbService = new BPCBService();
            self::$logger = Logger::getLogger("eliveLogger");
        }
        return self::$bpcbService;
    }

    public function pushData($station,$lastSeq,$isChild = false) {
        $stationSeq = $station->getSeq ();
        $fds = FolderDataStore::getInstance ();
        $ccds = ChannelConfigurationDataStore::getInstance ();
        $WQDDS = WQDStackDataStore::getInstance();
        $childStations = $fds->FindChildStations ( $stationSeq );
        $channels = array ();
        if (! empty ( $childStations )) {
            foreach ( $childStations as $childStation ) {
                $stationSeq = $childStation->getSeq ();
                $this->pushData ( $childStation, $lastSeq,true );
            }
            return;
        }
        $chDataArr = array();
        $parentStationSeq = $station->getSeq();
        if($isChild){
            $parentStationSeq = $station->getParentSeq();
            $chDataArr = $WQDDS->getChannelsInfo ( $parentStationSeq,null);
            $channels = $ccds->FindByChGroup ($stationSeq);
        }else{
            $chDataArr = $WQDDS->getChannelsInfo ( $parentStationSeq,null);
            $channels = $ccds->FindByFolderSeq($stationSeq);
        }
        if(empty($chDataArr)){
            echo "<br>No data found for $stationSeq after seq $lastSeq<br>";
            return;
        }
        $dataMainArr = array();
        $params = array();
        $lastSyncedSeq = 0;
        foreach ( $channels as $channel ) {
            $isCpcb = $channel->getIsCpcb ();
            if (empty ( $isCpcb )) {
                continue;
            }
            $paramsMainArr = array ();
            $deviceId = $channel->getDeviceId();
            $deviceId = str_replace(" ", "", $deviceId);
            foreach ( $chDataArr as $chData ) {
                $date = $chData ["dated"];
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
                array_push ( $paramsMainArr, $param );
                $lastSyncedSeq = $chData ["seq"];
            }
            $data = array (
                "deviceId" => $deviceId,
                "params" => $paramsMainArr,
            );
            array_push ( $dataMainArr, $data );
        }
        $mainArr["data"] = $dataMainArr;
        $mainArr["lastSeq"] = $lastSyncedSeq;
        $this->excuteApiCall($mainArr,$station,$parentStationSeq);
    }

    public function excuteApiCall($dataArr,$station,$parentStationSeq){
        $fds = FolderDataStore::getInstance();
        $locationSeq = $station->getLocationSeq();
        $stationSeq = $station->getSeq();
        $data = $dataArr["data"];
        $data = json_encode($data);
        //$apiKey = "MDUwNzIwMTlfZW52aXJvdGVjaF9saXZlX2NvXzA1MzY1Mw==";
        $apiKey = "MzExMjIwMTlfbWVkaWNhcmVfZW52aXJvbm1lbnRhbF9tYW5hZ2VtZW50X3B2dF9sdGRfMTUzMjE3";
        $auth_header = 'Authorization: Basic ' . $apiKey;
        //	echo "<br>". $auth_header . '<br>';
        $ch = curl_init();
        $url = "http://bpcbcems.nic.in/bpcpcb-api/api/industry/$locationSeq/station/$stationSeq/data";
        //$url = "http://164.100.160.248/hrcpcb-api/api/industry/$locationSeq/station/$stationSeq/data";
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $auth_header));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        echo "<br>Response  :- " . $result;
        if(!empty(curl_error($ch))){
            echo 'error: ' . curl_error($ch);
        }
        $resultJson = json_decode($result);
        $msg = $resultJson->msg;
        $status = $resultJson->status;
        curl_close($ch);
        if($status == 1 && $msg == "Success"){
            echo ("<br>Data Synced Successfully for Envirozone Station");
            $lastSeq = $dataArr["lastSeq"];
            if(!empty($lastSeq)){
                $fds->updateLastSpcbSyncedSeq($lastSeq, $parentStationSeq);
                echo "<br>LastSeq Updated ". $lastSeq . ' for station -' . $stationSeq . '<br>';
            }else{
                self::$logger->error("Failed to push data for bihar control board for station seq - " . $stationSeq .  ". Error - " . $msg);
            }
        }

    }
}