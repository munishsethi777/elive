<?php
  require_once('IConstants.inc');
  require_once($ConstantsArray['dbServerUrl']. "DataStoreMgr/FolderDataStore.php");
  require_once($ConstantsArray['dbServerUrl']. "DataStoreMgr/UserDataStore.php");
  require_once($ConstantsArray['dbServerUrl']. "DataStoreMgr/FolderUserDataStore.php");
  require_once($ConstantsArray['dbServerUrl']. "DataStoreMgr/ChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl']. "DataStoreMgr/VirtualChannelConfigurationDataStore.php");
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/Folder.php");
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/User.php");
  require_once($ConstantsArray['dbServerUrl'] ."BusinessObjects/FolderUser.php");
  require_once($ConstantsArray['dbServerUrl'] ."Utils/ConvertorUtils.php");
  require_once($ConstantsArray['dbServerUrl']. "DataStoreMgr/ChannelGroupDataStore.php");
  


  $actionGetAllFolders = "getAllFolders";
  $actionGetAllUsers = "getAllUsers";
  $actionGetAllUserFolders = "getAllUserFolders";
  $actionSaveFolderUsers = "saveFolderUsers";
  $actionGetUsersByFolder = "getUsersByFolder";

  $actionGetFoldersByUser = "getFoldersByUser";
  $actionSavePermission = "savePermissions";

  $actionGetAllChannelNames = "getAllChannelNames";
  $actionGetAllChannelNamesNotInGroups = "getAllChannelNamesNotInGroups";

  $FDUS = FolderUserDataStore::getInstance();
  $FDS = FolderDataStore::getInstance();



  $action = $_GET['action'];
  $fullArray=array();
  if($action == $actionGetAllFolders){

        $folders = $FDS->FindAll();

        foreach($folders as $folder){
            $array=array();
            $array['seq'] = $folder->getSeq();
            //$array['folderName'] = $folder->getFolderName();
            $array['location'] = $folder->getLocation();
            //$array['details'] = $folder->getDetails();

            $fullArray[$folder->getSeq()] = $array;

        }

        echo json_encode($fullArray);
  }
  else if($action == $actionGetAllUsers){
        $UDS = UserDataStore::getInstance();
        $users = $UDS->FindAll();
        foreach($users as $user){
            $array=array();
            $array['seq'] = $user->getSeq();
            $array['username'] = $user->getUserName();
            $fullArray[$user->getSeq()] = $array;
        }
        echo json_encode($fullArray);
  }
  else if($action == $actionSaveFolderUsers){
    $usersArray = $_GET['users'];
    $permissionsArray = $_GET['permissions'];
    $folderSeq = $_GET['folderSeq'];
    $userPermissions =  Array();
    $counter = count($usersArray);
    $msg = "Information Saved Successfully" ;
     if($usersArray[0]  <> ""){
     $FDUS->DeleteByFolder($folderSeq);
     for($i=0;$i<$counter;$i++){
         $userSeq = $usersArray[$i];
         $permission = $permissionsArray[$i];
         $folderUser = new FolderUser();
         $folderUser->setFolderSeq($folderSeq);
         $folderUser->setUserSeq($userSeq);
         $folderUser->setPermission($permission);
         $FDUS->Save($folderUser);
     }
     }else{
        $msg = "User is not selected";
     }
     echo json_encode($msg);
  }
  else if($action == $actionSavePermission){
    $folderArray = $_GET['folders'];
    $permissionsArray = $_GET['permissions'];
    $userSeq = $_GET['userSeq'];
    $userPermissions =  Array();
    $counter = count($folderArray);

     $msg = "Information Saved Successfully" ;
     if($folderArray[0]  <> ""){
     $FDUS->DeleteByUser($userSeq);
     for($i=0;$i<$counter;$i++){
         $folderSeq = $folderArray[$i];
         $permission = $permissionsArray[$i];
         $folderUser = new FolderUser();
         $folderUser->setFolderSeq($folderSeq);
         $folderUser->setUserSeq($userSeq);
         $folderUser->setPermission($permission);
         $FDUS->Save($folderUser);
     }

     }else{
         $msg = "Folder is not selected";
     }
     echo json_encode($msg);
  }
  else if($action == $actionGetUsersByFolder){
      $folderSeq = $_GET['folderSeq'];
      $folderUsers = $FDUS->FindByFolder($folderSeq);
     foreach($folderUsers as $folderUser){
            $array=array();
            $array['userseq'] = $folderUser->getUserSeq();
            $array['permission'] = $folderUser->getPermission();
            array_push($fullArray, $array);
     }
     echo json_encode($fullArray);
  }

  else if($action == $actionGetFoldersByUser)    {
     $userSeq = $_GET['userSeq'];
     $folderUsers = $FDUS->FindByUser($userSeq);
     foreach( $folderUsers as $folderUser){
            $array=array();
            $array['folderseq'] = $folderUser->getFolderSeq();
            $array['permission'] = $folderUser->getPermission();
            array_push($fullArray, $array);
     }
     echo json_encode($fullArray);
  }else if($action == $actionGetAllChannelNames){
    $folderSeq = $_GET['folSeq'];
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $VCCDS = VirtualChannelConfigurationDataStore::getInstance();
    $channelConfigs = $CCDS->FindByFolder($folderSeq);    
    $virtualChannelConfigs = $VCCDS->FindByFolder($folderSeq);
    if(!empty($virtualChannelConfigs)){
    	$channelConfigs = array_merge($channelConfigs,$virtualChannelConfigs);
    }
    $arr = array();
    foreach($channelConfigs as $key=>$channelConfig){
        if($channelConfig->getIsVisible() == "1"){
            $pLimit = $channelConfig->getPrescribedLimit();
            $channelInfo = $channelConfig->getChannelName() ." ". $channelConfig->getChannelStation();
            $channelInfo .= empty($pLimit) ? "" : " (Pres. ".$pLimit.")";
            $channelNo = $channelConfig->getChannelNumber();
            if(empty($channelNo)){
            	$channelNo = $key;
            }
            $arr[$channelNo] = $channelInfo;
        }
    }
    echo json_encode($arr);
  }else if($action == $actionGetAllChannelNamesNotInGroups){
    $folderSeq = $_GET['folSeq'];
    $CCDS = ChannelConfigurationDataStore::getInstance();
    $CGDS = ChannelGroupDataStore::getInstance();
    $channelConfigs = $CCDS->FindByFolder($folderSeq);
    $channelGroups = $CGDS->FindByFolder($folderSeq);
    $arr = array();
    $cgArr = array();
    foreach($channelConfigs as $channelConfig){    	
        if($channelConfig->getIsVisible() == "1"){        	
        	$ccSeq = $channelConfig->getSeq();
        	$disbale = false;
        	foreach ($channelGroups as $cg){
        		$seqs = $cg->getChannelSeqs();
        		if(in_array($ccSeq, $seqs)){
        			$disbale = true;
        		}
        	}
            $pLimit = $channelConfig->getPrescribedLimit();
            $channelInfo = $channelConfig->getChannelStation() . " - " . $channelConfig->getChannelName();           
            $ch["channelInfo"] = $channelInfo;
            $ch["disabled"] = $disbale;
            $arr[$channelConfig->getSeq()] = $ch;
        }
    }    
    echo json_encode($arr);
  }
  
?>
