<?php
require_once('../IConstants.inc');
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/FolderDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/WQDStackDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/DataStoreMgr/ConfigurationDataStore.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/HRPCBService.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/BPCBService.php");
require_once($ConstantsArray['dbServerUrl'] ."/Utils/PPCBService.php");
require_once($ConstantsArray['dbServerUrl'] .'/log4php/Logger.php');
Logger::configure ( $ConstantsArray ['dbServerUrl'] . "/log4php/log4php.xml" );
$logger = Logger::getLogger("eliveLogger");
$fds = FolderDataStore::getInstance();

echo("Calling PUNJAB PCB Push API<br>");
$folderSeqs = array(49);
foreach ($folderSeqs as $folderSeq){
    try{
        $station = $fds->FindBySeq($folderSeq);
        $ppcb = PPCBService::getInstance();
        $lastSeq = $station->getLastSpcbSyncedSeq();
        $ppcb->pushData($station,$lastSeq);
    }catch(Exception $e){
        $message = $e->getMessage();
        MailerUtils::sendError($message,"Error Push API PUNJAB PCB");
    }
}

echo("Calling BIHAR PCB Push API<br>");
$folderSeqs = array(106);
foreach ($folderSeqs as $folderSeq){
    try{
        $station = $fds->FindBySeq($folderSeq);
        $lastSeq = $station->getLastSpcbSyncedSeq();
        $bpcb = BPCBService::getInstance();
        $bpcb->pushData($station,$lastSeq);
    }catch(Exception $e){
        $message = $e->getMessage();
        MailerUtils::sendError($message,"Error Push API BIHAR PCB");
    }
}
return;

echo("Calling HARYANA PCB Push API<br>");
$folderSeqs = array(34);
foreach ($folderSeqs as $folderSeq){
	try{
		$station = $fds->FindBySeq($folderSeq);
		$hrpcb = HRPCBService::getInstance();
		$lastSeq = $station->getLastSpcbSyncedSeq();
		$hrpcb->pushData($station,$lastSeq);
	}catch(Exception $e){
		$message = $e->getMessage();
		MailerUtils::sendError($message,"Error Push API HARYANA PCB");
	}
}


