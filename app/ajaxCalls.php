<?php 
 require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl']. "/Utils/ExportUtils.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDFileDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ChannelConfigurationDataStore.php");
    require_once($ConstantsArray['dbServerUrl'] ."/Managers/CameraPTZActionMgr.php");

   
  $action = $_GET['action'];
  $fullArray=array();
  if($action == "exportCSV"){
        $fromDate = new DateTime($_GET['fromDate']);
        $toDate = new DateTime($_GET['toDate']);
        $channelNumbers = $_GET['channels'];
        //here comma separated folder seqs will come
        //add sheets for each selected one
        $folSeq = $_GET['folSeq'];
        $isPLimits = $_GET['isPLimits'];
        $CCDS = ChannelConfigurationDataStore::getInstance();
        $ChannelsInfo = $CCDS->FindByFolder($folSeq);
        
        $toDate->setTime(23,59,59);
        
        $fromDate =  $fromDate->format("Y/m/d  H:i:s");
        $toDate =  $toDate->format("Y/m/d  H:i:s");
        
        $interval = $_GET['interval'];
        $WQDS = WQDDataDataStore::getInstance();
        $arr = $WQDS->getExportData($fromDate,$toDate,$folSeq,$channelNumbers,$interval);
        
        $FDS = FolderDataStore::getInstance();
        $folder = $FDS->FindBySeq($folSeq);
        ExportUtils::exportCSV($arr,$channelNumbers,$folder->getFolderName(),$isPLimits,$ChannelsInfo);
  }
    
    if($action == "getCameraActions"){
        $fromSeq = $_GET["fromSeq"];
        $cameraPTZActionMgr = CameraPTZActionMgr::getInstance();
        $actions = $cameraPTZActionMgr->findAfterSeq($fromSeq);
        $mainArr = array();
        $mainArr['actions'] = $actions;
        echo json_encode($mainArr);

    }
?>
