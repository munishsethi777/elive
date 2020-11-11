<?php
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/FolderDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/WQDStackDataStore.php");
require_once ($ConstantsArray ['dbServerUrl'] . "DataStoreMgr/ChannelConfigurationDataStore.php");
class CPCBService {
	private $postArray;
	private $stackDataList;
	private $channelArray;
	private $lastSyncedSeq;
	private static $cbcbService;
	private static $allStations;
	private static $totalChannelsForAll;
	private static $WQDDS;
	private $hasPendingCpcbData;
	private $isNotifyDelay;
    public static function getInstance($preloadStations = false) {
        self::$cbcbService = new CPCBService ();
        $ccds = ChannelConfigurationDataStore::getInstance ();
        $fds = FolderDataStore::getInstance ();
        if($preloadStations == true){
            self::$allStations = $fds->getAllStationsForCPCBApi ();
            self::$totalChannelsForAll = $ccds->getCountByAllStation ();
        }
        self::$WQDDS = WQDStackDataStore::getInstance ();
        return self::$cbcbService;
    }
	public function getCPCBJson($locationSeq, $vendorCode) {
		$this->hasPendingCpcbData = array ();
		$this->hoursDiff = array();
		$this->postArray = array ();
		$this->updatedTill = array();
		$lastSyncedArr = array ();
        //$stations = null;
        //if(array_key_exists($locationSeq,self::$allStations)){
            $stations = self::$allStations[$locationSeq];
        //}
		if (empty ( $stations )) {
			// echo("<br>\r\n No station found for location ". $locationSeq);
			return;
		}
		foreach ( $stations as $station ) {
			echo ("<br>\r\n Has Pending CPCB flag is " . $station->getHasPendingCpcbData () . " for station - " . $station->getSeq ());
			$lastSeq = $station->getLastSyncedSeq ();
			if(empty($lastSeq)){
			    echo "<br>lastsyncedseq not set for station " .  $station->getSeq() . "<br>";
			}
			$stationSeq = $station->getSeq ();
			$totalChannels = self::$totalChannelsForAll [$station->getSeq ()];
			$limit = floor ( 40 / $totalChannels );
            if ($limit == 0){
				$limit = 40;
            }
            $limit = 40;
            echo "<br>Limit :- " . $limit;
			$deviceId = $vendorCode . "_" . $locationSeq;
			$this->stackDataList = self::$WQDDS->getChannelsInfo ( $stationSeq, $lastSeq, $limit );
			if ((count ( $this->stackDataList )) == $limit) {
				$this->hasPendingCpcbData [$stationSeq] = 1;
			} else {
				$this->hasPendingCpcbData [$stationSeq] = 0;
			}
			if (empty ( $this->stackDataList )) {
				continue;
			}
			$this->getArray ( $station, $deviceId, false );
			$lastSyncedArr [$stationSeq] = $this->lastSyncedSeq;
			$hoursDiffArr = array("hoursDiff"=>0,"stationSeq"=>$stationSeq,"stationName"=>$station->getIndustryName());
			$this->updatedTill [$stationSeq] = null;
			if(!empty($this->postArray[0])){
				$hoursDiffArr['hoursDiff'] = $this->postArray[0]['hoursDiff'];
				$this->updatedTill [$stationSeq] = $this->postArray[0]['updatedTill'];
			}
			$this->hoursDiff [$stationSeq] = $hoursDiffArr;
			
			// $fds->updateLastSyncedSeq($this->lastSyncedSeq, $stationSeq);
		}
		$mainArr ["data"] = $this->postArray;
		$mainArr ["lastsynceddates"] = $lastSyncedArr;
		$mainArr ["hasPendingCpcbData"] = $this->hasPendingCpcbData;
		$mainArr ["hoursDiff"] = $this->hoursDiff;
		$mainArr ["updatedTill"] = $this->updatedTill;
		return $mainArr;
	}
    public function getCPCBJsonOLD_NO_LIMITS($locationSeq, $vendorCode, $lastSeq) {
        $fds = FolderDataStore::getInstance ();
        $ccds = ChannelConfigurationDataStore::getInstance ();
        $stations = $fds->FindByLocation ( $locationSeq );
        $this->postArray = array ();
        $lastSyncedArr = array ();
        $WQDDS = WQDStackDataStore::getInstance ();
        foreach ( $stations as $station ) {
            $stationSeq = $station->getSeq ();
            $deviceId = $vendorCode . "_" . $locationSeq;
            $this->stackDataList = $WQDDS->getChannelsInfo ( $stationSeq, $lastSeq, 10 );
            if (empty ( $this->stackDataList )) {
                continue;
            }
            $this->getArray ( $station, $deviceId, false );
            $lastSyncedArr [$stationSeq] = $this->lastSyncedSeq;
            // $fds->updateLastSyncedSeq($this->lastSyncedSeq, $stationSeq);
        }
        $mainArr ["data"] = $this->postArray;
        $mainArr ["lastsynceddates"] = $lastSyncedArr;
        return $mainArr;
    }
	private function getArray($station, $deviceId, $isChild) {
		$stationSeq = $station->getSeq ();
		$fds = FolderDataStore::getInstance ();
		$ccds = ChannelConfigurationDataStore::getInstance ();
		$childStations = $fds->FindChildStations ( $stationSeq );
		$channels = array ();
		if (! empty ( $childStations )) {
			foreach ( $childStations as $childStation ) {
				$stationSeq = $childStation->getSeq ();
				// if($stationSeq == 229 || $stationSeq ==230)
				// continue;
				$this->channelArray = $ccds->FindByChGroup ( $stationSeq );
				$this->getArray ( $childStation, $deviceId, true );
			}
			return;
		}
		$isNotifyDelay = false;
		$hoursDiff = 0;
		$dataMainArr = array ();
		if (! $isChild) {
			$channels = $ccds->FindByFolderSeq ( $stationSeq );
		} else {
			$channels = $this->channelArray;
		}
		$deviceId = $deviceId . "_" . $stationSeq;
		$chDataArr = $this->stackDataList;
		$diagnosticsMainArr = array ();
		$updatedTill=null;
		foreach ( $channels as $channel ) {
			$isVisible = $channel->getIsVisible ();
			if (empty ( $isVisible )) {
				continue;
			}
			$paramsMainArr = array ();
			$device = $channel->getDeviceId ();
			foreach ( $chDataArr as $chData ) {
				$date = $chData ["dated"];
				$updatedTill = $date;
				$date = DateTime::createFromFormat ( "Y/m/d H:i:s", $date );
				$timestamp = $date->getTimestamp ();
				
				$currDateTime = new DateTime ();
				$interval = $currDateTime->diff ( $date );
				$hoursDiff = ($interval->days * 24) + $interval->h;
				
				
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
				$diagnostics = array ();
				array_push ( $paramsMainArr, $param );
				$this->lastSyncedSeq = $chData ["seq"];
			}
			$data = array (
					"deviceId" => $device,
					"params" => $paramsMainArr,
					"diagnostics" => $diagnosticsMainArr 
			);
			array_push ( $dataMainArr, $data );
		}
		$post = array (
				"stationId" => $stationSeq,
				"data" => $dataMainArr,
				"hoursDiff" => $hoursDiff,
				"updatedTill" => $updatedTill
		);
		array_push ( $this->postArray, $post );
	}
}
?>
