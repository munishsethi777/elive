<?php
    
    require_once('../IConstants.inc');
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ExemptionDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDDataDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDOpacityFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/CommentsDataStore.php");
    
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDOpacityFileDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Managers/MultiStationReportMgr.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/ConvertorUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/StringUtils.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Utils/PrescribedLimitsUtils.php");
    $folderSeq = 49;
    $fromDate = new DateTime();
    $fromDate->setTime(0,0,0);
    $fromDate->setDate(2017,06,17);
    $toDate = new DateTime();
    
    $channelsDS = ChannelConfigurationDataStore::getInstance();
    $channelsInfo = $channelsDS->FindByFolder($folderSeq);
    $channelNos = array();
    foreach ($channelsInfo as $channel){
        array_push($channelNos,$channel->getChannelNumber());
    }
    
    
    $get = array();
    $get['infoTypeRadio'] = "grid";
    $get['exportTypeRadio'] = "";
    $get['valueTypeRadio'] = "normal";
    $get['stationsSelect'] = "49";
    $get['fromDate'] = $fromDate->format("Y-m-d H:s");
    $get['toDate'] = $toDate->format("Y-m-d H:s");
    $get['timeBase'] = "1hour";
    $get['timeBaseQuick']="1hour";
    $get['quickReportType'] = "null";
    $get['iscombustion'] = null;
    $get['isAverage'] = "true";
    $get['channelNos_49[]'] = array(1,2,3,4,5,6);
    $RepMgr = MultiStationReportMgr::getInstance();
    $data = $RepMgr->getMultiStationReport($get);
    var_dump($data);
    
    
    //   $WQDDS = WQDStackDataStore::getInstance();
    //   $date = $WQDDS->getChannels($fromDate->format("Y-m-d H:s"), $toDate->format("Y-m-d H:s"), $folderSeq, $channelNos, "1hr",false);
